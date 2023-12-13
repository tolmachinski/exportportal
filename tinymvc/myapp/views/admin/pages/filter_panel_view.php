<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
            <tr>
				<td>Search by title</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search by title" name="title" placeholder="Title">
				</td>
            </tr>
            <tr>
				<td>Controller</td>
				<td>
					<input class="dt_filter" type="text" data-title="Controller" name="controller" placeholder="Controller">
				</td>
            </tr>
            <tr>
				<td>Action</td>
				<td>
					<input class="dt_filter" type="text" data-title="Action" name="action" placeholder="Action">
				</td>
			</tr>
            <tr>
				<td>Search by URL</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search by URL" name="url" placeholder="URL">
				</td>
			</tr>
            <tr>
				<td>Assigned to module</td>
				<td>
					<select class="dt_filter" data-title="Assigned to module" name="module">
						<option data-default="true" value="">All modules</option>
						<?php foreach($modules as $module){ ?>
							<option value="<?php echo $module['id_module'];?>"><?php echo cleanOutput($module['name_module']);?></option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Creation date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter datetime-filter" type="text" data-title="Page creted from" name="created_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter datetime-filter" type="text" data-title="Page created to" name="created_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Update date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter datetime-filter" type="text" data-title="Page updated from" name="updated_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter datetime-filter" type="text" data-title="Page updated to" name="updated_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>Ready for translation</td>
				<td>
					<select class="dt_filter" data-title="Assigned to module" name="translation_status">
						<option data-default="true" value="">All</option>
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				</td>
			</tr>
        </table>
		<div class="wr-filter-list clearfix mt-10"></div>
    </div>
    <div class="btn-display ">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
	</div>

	<div class="wr-hidden"></div>
</div>

<script>
$(document).ready(function(){
	$(".filter-admin-panel").find(".datetime-filter").datepicker();
})
</script>
