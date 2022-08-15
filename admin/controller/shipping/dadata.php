<?php
class ControllerShippingDadata extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('shipping/dadata');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('dadata', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_none'] = $this->language->get('text_none');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['help_total'] = $this->language->get('help_total');
		$data['entry_cost'] = $this->language->get('entry_cost');
		$data['entry_cost2'] = $this->language->get('entry_cost2');
		$data['entry_cost3'] = $this->language->get('entry_cost3');
		$data['entry_token'] = $this->language->get('entry_token');
		$data['entry_secret'] = $this->language->get('entry_secret');
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_shipping'),
			'href' => $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('shipping/dadata', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('shipping/dadata', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['dadata_cost'])) {
			$data['dadata_cost'] = $this->request->post['dadata_cost'];
		} else {
			$data['dadata_cost'] = $this->config->get('dadata_cost');
		}
		if (isset($this->request->post['dadata_mode'])) {
			$data['dadata_mode'] = $this->request->post['dadata_mode'];
		} else {
			$data['dadata_mode'] = $this->config->get('dadata_mode');
		}

		if (isset($this->request->post['dadata_limit_request'])) {
			$data['dadata_limit_request'] = $this->request->post['dadata_limit_request'];
		} else {
			$data['dadata_limit_request'] = $this->config->get('dadata_limit_request');
		}

		if (isset($this->request->post['dadata_token'])) {
			$data['dadata_token'] = $this->request->post['dadata_token'];
		} else {
			$data['dadata_token'] = $this->config->get('dadata_token');
		}
		if (isset($this->request->post['dadata_secret'])) {
			$data['dadata_secret'] = $this->request->post['dadata_secret'];
		} else {
			$data['dadata_secret'] = $this->config->get('dadata_secret');
		}
		if (isset($this->request->post['dadata_google_key'])) {
			$data['dadata_google_key'] = $this->request->post['dadata_google_key'];
		} else {
			$data['dadata_google_key'] = $this->config->get('dadata_google_key');
		}
		if (isset($this->request->post['dadata_min_chars'])) {
			$data['dadata_min_chars'] = $this->request->post['dadata_min_chars'];
		} else {
			$data['dadata_min_chars'] = $this->config->get('dadata_min_chars');
		}
		if (isset($this->request->post['dadata_status'])) {
			$data['dadata_status'] = $this->request->post['dadata_status'];
		} else {
			$data['dadata_status'] = $this->config->get('dadata_status');
		}

		if (isset($this->request->post['dadata_sort_order'])) {
			$data['dadata_sort_order'] = $this->request->post['dadata_sort_order'];
		} else {
			$data['dadata_sort_order'] = $this->config->get('dadata_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/dadata.tpl', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/dadata')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}