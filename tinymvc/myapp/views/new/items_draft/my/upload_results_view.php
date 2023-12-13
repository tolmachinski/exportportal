<?php if ($upload_count > 0) { ?>
	<div class="success-alert-b"><i class="ep-icon ep-icon_ok-circle"></i> <span>There are <?php echo $upload_count; ?> product(s) imported.</span></div>
<?php } else { ?>
	<div class="warning-alert-b"><i class="ep-icon ep-icon_warning-circle-stroke"></i> <span>There are no product(s) imported.</span></div>
<?php } ?>

<?php if (!empty($unvalidated_rows)) { ?>
    <div class="pt-10 pb-10">We found some errors in the uploaded file.</div>
	<ul class="list-group">
		<?php foreach($unvalidated_rows as $row_key => $unvalidated_row) { ?>
			<li class="list-group-item">
				<div class="txt-medium"><?php echo $row_key;?> row</div>
				<div class="fs-14 txt-red"><?php echo implode('<br>', $unvalidated_row); ?></div>
			</li>
		<?php } ?>
	</ul>
<?php } ?>