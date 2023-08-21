<?php
$fade = $_fade ? ' fade' : NULL;
$close = $_close ?? true;
$static = (!empty($_static) && ($_static === true)) ? 'static' : 'true';
$centered = $_centered ? ' modal-dialog-centered' : '';
$scrollable = !empty($_scrollable) ? ' modal-dialog-scrollable' : '';

$modal_title = $_modal_title ?? 'My Modal';
$modal_id = !empty($_modal_id) ? $_modal_id : 'myModal';
$modal_dialog_class = !empty($_modal_dialog_class) ? " $_modal_dialog_class" : NULL;
$color_classes = (!empty($_color_classes) && is_array($_color_classes)) ?
	implode(' ', $_color_classes) :
	((!empty($_color_classes) && !is_array($_color_classes)) ?
		die('Array required for Modal colors class') : 'text-body');

define('modal_classes', "modal$fade");
define('modal_title_id', "$modal_id-title");
?>

<div class="<?= modal_classes; ?>" id="<?= $modal_id ?>" data-bs-backdrop="<?= $static ?>" tabindex="-1" aria-labelledby="<?= $modal_id ?>">
	<div class="modal-dialog<?= $modal_dialog_class . $centered . $scrollable ?>">
		<div class="modal-content <?= $color_classes ?>">
			
			<div class="modal-header mb-4">
				<div class="modal-title h4" id="<?php echo modal_title_id ?>"><?php echo $modal_title ?></div>
				<?php if ($close) : ?>
					<a type="button" class="link-danger close-modal" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></a>
				<?php endif; ?>
			</div>
			
			<div class="modal-body">
				<?php echo $body ?? 'Your Modal is ready' ?>
			</div>
		</div>
	</div>
</div>
