<?php
class ModelShippingDadata extends Model {

	protected $registry;
	protected $token;
	protected $secret;
	protected $mode; // влияет на isBalance(), isLimitRequest(), getDistance()
	protected $status_module;
	protected $google_key;
	protected $min_chars;

	public function __construct($registry) {
		$this->registry = $registry;

	    $this->LIMIT_BALANCE = 5;
	    $this->LIMIT_REQUEST_BASE = 9900;
	    $this->LIMIT_REQUEST_EXTENDED = 99900;

		$this->token = $this->config->get('dadata_token');
		$this->secret = $this->config->get('dadata_secret');
		$this->google_key = $this->config->get('dadata_google_key');
		$this->min_chars = $this->config->get('dadata_min_chars');
		$this->mode = $this->config->get('dadata_mode');
		$this->status_module = $this->config->get('dadata_status');
	}
	
	public function isEnabled() {
        // проверяет баланс для использования стандартизации (расчета МКАД)
		
		if ( !$this->getToken() || !$this->secret || !$this->isBalance() ) {
			return false;
		} 

		if ($this->mode == 'google' && !$this->google_key) {
			return false;
		}

		return true;		
	}
	
	public function getToken() {
        // отдает токен если нет лимита использованных сообщений (для подсказок)
		//if ($this->customer->isLogged()) return false; //TODO: remove after fix LK

		if (!$this->token || !$this->status_module || !$this->isLimitRequest() ) {
			return false;
		} 

		return $this->token;		
	}


	protected function getDefaultCost() {
		// устанвливаем стандратную цену как доставка по Москве
		return $this->config->get('flat_cost');
	}


	public function getDadataJs() {
		$dadata_token = $this->getToken();
		$dadata_chars = is_numeric($this->min_chars) ? intval($this->min_chars) : 5;
		
		if ( !$dadata_token ) {
			// the js func below is called in account/register and catalog/view/javascript/d_quickcheckout/view/shipping_address.js
			return '<script>function getDadataJsInit(mainAddressFieldId, cityAddressFieldId) {return;}</script>';
		}
		
		$js = '
<link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.8.0/dist/css/suggestions.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.8.0/dist/js/jquery.suggestions.min.js"></script>
<script>
function getDadataJsInit(mainAddressFieldId, cityAddressFieldId) {
	options = {
		token: "' . $dadata_token . '",
		type: "ADDRESS",
		count: "5", // how many items in list
		minChars: "' . $dadata_chars . '",
		constraints: [
			{locations: { region: "Москва" }},
			{locations: { kladr_id: "50" }}],
			noSuggestionsHint: false,
			hint: "",
		onSelect: function(suggestion) {
			// split city to separate field
			addressArr = suggestion.value.split(", ");
			addressArr.forEach(function(item, i, arr){ 
				if (item.indexOf("г ") == 0){
					var c = item.replace("г ", "");
					$(cityAddressFieldId).attr("value", c)
					arr.splice(0, i);
				}
			});
			$(mainAddressFieldId).val(addressArr.join(", "));
			$(mainAddressFieldId).attr("checksum", 1);
			if ($("#shipping_address_form").length) $("#shipping_address_form").valid();
			preloaderStart();
			setTimeout(function() { 
				$(mainAddressFieldId).trigger("change");			
				preloaderStop();
			}, 800);

		},
		onSelectNothing: function() {
			 /* $(cityAddressFieldId).trigger("change");
			$(mainAddressFieldId).trigger("change"); */
			if ($(mainAddressFieldId).val().trim().length < 3) return;
			if ($(mainAddressFieldId).val().trim().length > 127) return;
			$(mainAddressFieldId).attr("checksum", 0);
			if ($("#shipping_address_form").length) $("#shipping_address_form").valid();
		}
	}
	
	try {
		$(mainAddressFieldId).suggestions(options);
		$(cityAddressFieldId).parents(".form-group").css("display", "none");
		
		jQuery.validator.addMethod("validAddress", function(value, element) {
			return $(mainAddressFieldId).attr("checksum") == 1;
		}, "Пожалуйста, поправьте ошибку в адресе, выберите адрес из выпадающего списка");
		$(mainAddressFieldId).rules("add", {validAddress: true});
	} catch(err) {
		console.log(err);
		return;
	}
};
</script>';
		return $js;
	}



	public function getQuote($address_post) {

		$this->load->language('shipping/dadata');

		$method_data = array();

		if ( !$this->isEnabled() || ($this->cart->getSubTotal() < MIN_ORDER_SUM && !ADMIN_USER) || $this->bcart->isSamovivoz()) {
			return $method_data;
		}
	
		$adress = isset($this->session->data['shipping_address']['address_2']) ? $this->session->data['shipping_address']['address_2'] : ''; 
		//$adress = $address_post['address_2']; // берем адрес который пришел

		if (!empty($adress)) {
			//берем город из адреса
			$city_format = str_replace('г ', '', $adress);
			$city_format = stristr($city_format, ',', true) != false ? stristr($city_format, ',', true) : $city_format;
			$city = trim($city_format);
		} else {
			$city = 'Москва';
		}

		// рассчет дистации от МКАД
		$title_dist = '';

		if ((isset($this->session->data['old_address_2']) && $adress == $this->session->data['old_address_2']) || $adress == '') {	
			$dist = isset($this->session->data['beltway_distance']) ? $this->session->data['beltway_distance'] : 0;
			$pereraschet = 'нет, не считали';
		} else {
			//fix на поселение первомайское
			if (strpos($adress, 'ервомайское') !== false) {
				$dist = 23;
				$city = 'Первомайское';
			} else if (strpos($adress, 'язановское') !== false) {
				$dist = 16;
				$city = 'Рязановское';
			} else if (strpos($adress, 'роицк') !== false) { 
				$dist = 18;
				$city = 'Троицк';
			} else {
				$dist = $this->getDistance($adress, $city);
			}
				
			$pereraschet = 'да, рассчитали';
		}
			

		// рассчет стоимости доставки 
		if ($dist > 0) {
			if (ADMIN_USER) {
				$title_dist = ' (' . $dist . ' км от МКАД)';
			}
			$cost = $this->calucateCostStatic($dist);
		} else {
			$dist = 0;
			$cost = $this->getDefaultCost();		
		}

		$cost = $this->bcart->shippingCalc('dadata', $cost);

		//log && session write
		$this->session->data['beltway_distance'] = $dist;
		$this->session->data['shipping_address']['city'] = $city;
		$this->session->data['old_address_2'] = $adress;

		//hack на метро так как шипинг адресс чистится при апдейте, если метро существует то берем его
		$metro = isset($this->session->data['metro']) ? $this->session->data['metro'] : '';
		$this->session->data['shipping_address']['firstname'] = $metro;
		
		$log['mode'] 	 	= $this->mode;
		$log['city'] 	 	= $city;
		$log['address']  	= $adress;
		$log['distance'] 	= $dist;
		$log['price'] 	 	= $cost . ' (учтено время доставки)';
		$log['metro'] 	    = isset($this->session->data['shipping_address']['firstname']) ? $this->session->data['shipping_address']['firstname'] : '';
		$log['pereraschet'] = $pereraschet;

		// file_put_contents('log_shipping_dadata.txt', date('[Y-m-d H:i:s] ') . print_r($log, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
		// end log

		$quote_data = array();

		$quote_data['dadata'] = array(
			'code'         => 'dadata.dadata',
			'title'        => 'Доставка по городу ' . $city . $title_dist,
			'cost'         => $cost,
			'tax_class_id' => $this->config->get('flat_tax_class_id'),
			'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('flat_tax_class_id'), $this->config->get('config_tax')))
		);
		$method_data = array(
			'code'       => 'dadata',
			'title'      => $this->language->get('text_title'),
			'quote'      => $quote_data,
			'sort_order' => $this->config->get('flat_sort_order'),
			'error'      => false
		);

		return $method_data;
	}


	private function calucateCost($dist) {
		//рассчет доставки по киллометражу
		
		$cost_r = $this->config->get('dadata_cost');
		$cost_r  = str_replace('&quot;', '"', $cost_r);
		$cost_arr = json_decode($cost_r, true);
	
		$karr = (array_keys($cost_arr));
		foreach ($karr as $key => $value) {

			if ($dist <= $value) {
				$k_arr =  $karr[$key];
				break;
			} else {
				$k_arr =  end($karr);
			}
		}
		if (isset($k_arr)) {
			$cost_r = $cost_arr[$k_arr];
		} else {
			$cost_r = 0;
		}
			
		$cost = $dist * $cost_r + $this->getDefaultCost();
		
		return $cost;
	}


	private function calucateCostStatic($dist) {
		// статический рассчет доставки по максимальному километру
		
		$cost_r = $this->config->get('dadata_cost');
		$cost_r  = str_replace('&quot;', '"', $cost_r);
		$cost_arr = json_decode($cost_r, true);
	
		$karr = (array_keys($cost_arr));
		foreach ($karr as $key => $value) {

			if ($dist <= $value) {
				$k_arr =  $karr[$key];
				$max_dist = $value;
				break;
			} else {
				$k_arr =  end($karr);
				$max_dist = $dist;
			}
		}
		if (isset($k_arr)) {
			$cost_r = $cost_arr[$k_arr];
		} else {
			$cost_r = 0;
		}
			
		$cost = $max_dist * $cost_r + $this->getDefaultCost();
		
		return $cost;
	}


	private function isLimitRequest() {

		if ($this->mode == 'clue') {
			$limit_request = $this->LIMIT_REQUEST_EXTENDED;
		} else {
			$limit_request = $this->LIMIT_REQUEST_BASE;
		}

		$fields = "";
		$url = "https://dadata.ru/api/v2/stat/daily";
		$data = $this->executeRequest($url, $fields);

		if (!isset($data['services']['suggestions']) || $data['services']['suggestions'] > $limit_request) {
			return false;
		}		

		return true;
	}


	private function isBalance() {

		if ($this->mode == 'standart' || $this->mode == 'google') {
			//проверяем баланс, создаем кеш
			if (!$this->cache->get('dadata_balance')) $this->cache->set('dadata_balance', $this->getBalance());
			$balance = $this->cache->get('dadata_balance');
			if ($balance < $this->LIMIT_BALANCE) return false;
		}
		return true;
	}


	private function getBalance() {
		$fields = "";
		$url = "https://dadata.ru/api/v2/profile/balance";
		$data = $this->executeRequest($url, $fields);

		if (!isset($data['balance'])) return false;
		return $data['balance'];  //выводим баланс
	}


	private function getDistance($address, $city = 'Москва') {

		$beltway_distance = 0;

		if ($this->mode == 'standart') {

			$url = "https://cleaner.dadata.ru/api/v1/clean/address";
			$fields = array($address);
			$data = $this->executeRequest($url, $fields);

			//находим ближайшее метро или город
			$this->session->data['metro'] = !empty($data[0]['metro']) ? 'НОВ ' . $this->getMetro($data[0]['metro']) : 'НОВ ' . $city;

			$beltway_distance = isset($data[0]['beltway_distance']) ? $data[0]['beltway_distance'] : 0;
		
		} else if ($this->mode == 'clue') {

			$url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address";
			$fields = array(
				'query' => $address
			);
			$data = $this->executeRequest($url, $fields, false);

			$beltway_distance = isset($data['suggestions'][0]['beltway_distance']) ? $data['suggestions'][0]['beltway_distance'] : 0;
		} else if ($this->mode == 'google') {

			$url = "https://cleaner.dadata.ru/api/v1/clean/address";
			$fields = array($address);
			$data = $this->executeRequest($url, $fields);

			if (isset($data[0]['geo_lat']) && isset($data[0]['geo_lon'])) {

				//координаты точки
				$point = [$data[0]['geo_lat'], $data[0]['geo_lon']];

				$this->load->model('shipping/google_api');
				$beltway_distance = $this->model_shipping_google_api->getGoogleDistance($point);
			} 
		}

		if (empty($beltway_distance) || $beltway_distance == 0) {
			return 0;
		}

		return $beltway_distance; //выводим дистанцию

	}


	private function getMetro($metro_info) {

		$distance = $metro_info[0]['distance'];
		$distance_text = $distance >= 2 ? ' (' . $distance . ' км)' : '';
		$metro_name = $metro_info[0]['name'] . $distance_text;

		foreach ($metro_info as $metro) {

			if ($metro['distance'] < $distance) {
				$distance = $metro['distance'];
				$distance_text = $distance >= 2 ? ' (' . $distance . ' км)' : '';
				$metro_name = $metro['name'] . $distance_text;
			}
		}

		return mb_strtolower($metro_name);
	}


	private function executeRequest($url, $fields) {

		$header_params = array(
			"Content-Type: application/json",
			"Accept: application/json",
			"Authorization: Token " . $this->token,
			"X-Secret: " . $this->secret,
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_params);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		if (!empty($fields)) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		} else {
			curl_setopt($ch, CURLOPT_POST, 0);
		}

		$result = curl_exec($ch);
		$result = json_decode($result, true);

		return $result;
	}
}

