<div class="warning-alert-b"><i class="ep-icon ep-icon_warning-circle-stroke"></i> <span>We found some errors in the uploaded file.</span></div>
<?php if (!empty($unvalidated_rows)) { ?>
	<ul class="list-group pt-20">
		<?php foreach($unvalidated_rows as $row_key => $unvalidated_row) { ?>
			<li class="list-group-item">
				<div class="txt-medium"><?php echo $row_key;?> row</div>
				<div class="fs-14 txt-red"><?php echo implode('<br>', $unvalidated_row); ?></div>
			</li>
		<?php } ?>
	</ul>
<?php } ?>