<tr>
	<td class="first tal">
		<div class="manage_column">
			<strong class="w-130 text-nowrap pull-left lh-30" title="<?php echo $comment?>"><?php echo $comment?></strong>
			<a class="ep-icon ep-icon_pencil pull-right mt-7" title="Edit parameter" data-column="<?php echo $column?>"></a>
			<a class="ep-icon ep-icon_trash txt-red-dark pull-right mt-7 confirm-dialog" data-message="Are you sure want to delete this user statistic?" title="Delete parameter" data-column="<?php echo $column?>" data-callback="delete_statisctic"></a>
		</div>
		<div class="edit_column display-n">
			<input type="text" value="<?php echo $comment?>" class="w-130" />
			<a class="ep-icon ep-icon_remove txt-red pull-right mt-7" title="Cancel editing"></a>
			<a class="ep-icon ep-icon_ok txt-green pull-right mt-7" title="Confirm editing" data-column="<?php echo $column?>"></a>
		</div>
		<span class="pull-left w-100pr">(<?php echo $column?>)</span>
		<?php //echo $column . " | " . $comment;?>
	</td>
	<?php foreach($groups as $group){?>
	<td class="tac vam w-200" id="td-<?php echo $group['idgroup']?>-<?php echo $column?>">
		<a data-type="to_en" data-group="<?php echo $group['idgroup']?>" data-column="<?php echo $column?>" class="btn-change ep-icon ep-icon_remove txt-red" title="Change to enable"></a>
	</td>
	<?php } ?>
</tr>

