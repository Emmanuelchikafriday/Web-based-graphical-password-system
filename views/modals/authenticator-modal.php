<?php
$_fade = true;
$_static = true;
//$_close = false;
$_centered = true;
$_scrollable = true;

$_modal_title = '<i class="far fa-1x fa-lock-alt"></i> Auth Selector';
$_modal_id = 'authenticator-modal';
$_color_classes = ['bg-dark', 'text-white'];

$body = '
	<div class="d-flex flex-column">
		<div id="picfuse-wrapper"></div>
		<hr>
		<div class="text-danger">
		Make sure that nobody is watching you as you are selecting your segment
		</div>
		<button type="button" data-bs-dismiss="modal" class="btn btn-outline-primary">
			Save
			<i class="ms-1 fa fa-1x fa-spin fa-spinner-third button-loader" style="display: none"></i>
		</button>
	</div>
';

require dirname(__DIR__, 2) . '/app/components/modal-struct.php';
