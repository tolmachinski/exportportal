<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
		<table>
			<tr>
				<td>Search</td>
				<td>
					<input type="text" name="sSearch" class="dt_filter pull-left" value=""  data-title="Search" placeholder="Keywords">
					<a class="dt-filter-apply dt-filter-apply-buttons">>></a>
				</td>
			</tr>
			<tr>
				<td>Modules</td>
				<td>
					<select class="form-control dt_filter w-180" data-title="Module" name="module" data-type="select">
						<option value="" data-default="true">All modules</option>
						<?php foreach($modules as $module){?>
							<option value="<?php echo $module['id_module']?>"><?php echo $module['name_module']?></option>
						<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Type</td>
				<td>
					<select class="form-control dt_filter w-180" data-title="Type" name="type_mess">
						<option value="" selected="selected" data-default="true">All types</option>
						<option data-value-text="Notice" value="notice">Notice</option>
						<option data-value-text="Warning" value="warning">Warning</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Is proofread</td>
				<td>
					<select class="form-control dt_filter w-180" data-title="Proofread" name="is_proofread">
						<option value="" selected="selected">All types</option>
						<option data-value-text="Yes" value="1">Proofread</option>
						<option data-value-text="No" value="0">Not proofread</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Date changed</td>
				<td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Date it was last changed from" name="date_changed_from" id="date_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Date it was last changed to" name="date_changed_to" id="date_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Date proofread</td>
				<td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Date it was proofread from" name="date_proofread_from" id="date_from_proof" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Date it was proofread to" name="date_proofread_to" id="date_to_proof" placeholder="To" readonly>
					</div>
				</td>
			</tr>
		</table>
		<div class="wr-filter-list clearfix mt-10 "></div>
	</div>
	<div class="btn-display ">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>

	<div class="wr-hidden"></div>
</div>
<script>
$(document).ready(function() {
	$("#date_from, #date_to, #date_from_proof, #date_to_proof").datepicker();
})
</script>
