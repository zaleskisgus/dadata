<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" form="form-flat" data-toggle="tooltip" title="<?php echo $button_save; ?>"
					class="btn btn-primary"><i class="fa fa-save"></i></button>
				<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
					class="btn btn-default"><i class="fa fa-reply"></i></a></div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($error_warning) { ?>
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
			</div>
			
			<div class="panel-body">

				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-flat"
					class="form-horizontal">

					<div class="form-group" style="margin-top:30px;">
						<label class="col-sm-2 control-label" for="input-total"><?php echo $entry_token; ?></label>
						<div class="col-sm-10">
							<input type="text" name="dadata_token" value="<?php echo $dadata_token; ?>"
								placeholder="<?php echo $entry_token; ?>" id="input-total" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-total"><?php echo $entry_secret; ?></label>
						<div class="col-sm-10">
							<input type="text" name="dadata_secret" value="<?php echo $dadata_secret; ?>"
								placeholder="<?php echo $entry_secret; ?>" id="input-total" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-soogle">Google Api key</label>
						<div class="col-sm-10">
							<input type="text" name="dadata_google_key" value="<?php echo $dadata_google_key; ?>"
								placeholder="?????????????? google maps key" id="input-google" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-chars">Min Chars</label>
						<div class="col-sm-10">
							<input type="text" name="dadata_min_chars" value="<?php echo $dadata_min_chars; ?>"
								placeholder="?????????????? ?????????????????????? ??????-???? ????????????????" id="input-chars" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-cost"><span data-toggle="tooltip" title="???????????? 10:20, 15:22 ???????????????? ?????? - ???? 0 ???? ???? 10 ???? ?????????????????? ???? ???? 20 ??????., ?? ???? 10 ???? 15 ???? ?????????????????? 22 ?????????? ???? ????"><?php echo $entry_cost; ?></label>
						<div class="col-sm-10">
							<input type="text" name="dadata_cost" value="<?php echo $dadata_cost; ?>"
								placeholder="<?php echo $entry_cost; ?>" id="input-cost" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">?????????? ???????????? API</label>
						<div class="col-sm-10">
							<label class="radio-inline">
								<?php if ($dadata_mode == 'clue') { ?>
								<input type="radio" name="dadata_mode" value="clue" checked="checked" />
								???????????????????????? API ??????????????????
								<?php } else { ?>
								<input type="radio" name="dadata_mode" value="clue" />
								???????????????????????? API ??????????????????
								<?php } ?>
							</label>
							<p style="margin-top: 5px; opacity: .4;">?????????? ???????????????? ?????? ???????????????? "????????????????????"</p>
							<label class="radio-inline" style="display:block; margin-left:0px">
								<?php if ($dadata_mode == 'standart') { ?>
								<input type="radio" name="dadata_mode" value="standart" checked="checked" />
								???????????????????????? API ????????????????????????????
								<?php } else { ?>
								<input type="radio" name="dadata_mode" value="standart" />
								???????????????????????? API ????????????????????????????
								<?php } ?>
							</label>
							<p style="margin-top: 5px; opacity: .4;">???????????????????????? ???????????????? - 10 ???????????? ????????????</p>
							<label class="radio-inline" style="display:block; margin-left:0px">
								<?php if ($dadata_mode == 'google') { ?>
								<input type="radio" name="dadata_mode" value="google" checked="checked" />
								???????????????????????? API Google Maps
								<?php } else { ?>
								<input type="radio" name="dadata_mode" value="google" />
								???????????????????????? API Google Maps
								<?php } ?>
							</label>
							<p style="margin-top: 5px; opacity: .4;">?????????? ???????????????????? API ???????????????????????????? ?????? ?????????????????? ??????????????????</p>
						</div>
					</div>

					<!-- <div class="form-group">
						<label class="col-sm-2 control-label" for="input-limit"><span data-toggle="tooltip" title="???????????????? ???????? ???????????? ???????? ?????????? ???? ??????????">???????????????? ?????????? <br>??????????????????</label>
						<div class="col-sm-10">
							<input type="text" name="dadata_limit_request" value="<?php echo $dadata_limit_request; ?>"
								placeholder="???????????????? ???????? ???????????? ???????? ?????????? ???? ??????????" id="input-limit" class="form-control" />
									<a href="https://dadata.ru/suggestions/#pricing" target="_blank">?????????????????? ?? ?????????????? ?? ??????????</a>
						</div>
					</div> -->
					

					<div class="form-group">
						<label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
						<div class="col-sm-10">
							<select name="dadata_status" id="input-status" class="form-control">
								<?php if ($dadata_status) { ?>
								<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
								<option value="0"><?php echo $text_disabled; ?></option>
								<?php } else { ?>
								<option value="1"><?php echo $text_enabled; ?></option>
								<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"
							for="input-sort-order"><?php echo $entry_sort_order; ?></label>
						<div class="col-sm-10">
							<input type="text" name="dadata_sort_order" value="<?php echo $dadata_sort_order; ?>"
								placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order"
								class="form-control" />
								<h4 style="margin-top:15px;">?????????????? ???????? ???????????????? ???? ???????????? ???? ?????????? ???? ???????????? ?????????????????????????? ???????????????? ???? ?????? (flat.flat)</h4>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php echo $footer; ?>