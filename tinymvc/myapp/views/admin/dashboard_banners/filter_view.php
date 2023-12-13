<script type="text/javascript" src="<?php echo __FILES_URL; ?>public/plug_admin/jquery-multiple-select-1-1-0/js/jquery.multiple.select.js"></script>

<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table>
            <tr>
				<td>Search by Title :</td>
				<td>
                <input class="keywords dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" id="keywords" placeholder="Title or subtitle">
				</td>
			</tr>
            <tr>
				<td>Create date :</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter datepicker" type="text" data-title="Create date from" name="create_date_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter datepicker" type="text" data-title="Create date to" name="create_date_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>
            <tr>
				<td>Is Visible :</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="is_visible" data-default="true" data-title="is Visible" data-value-text="All" value="">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="is_visible" data-title="Is Visible" data-value-text="Yes" value="1" >
							<span class="input-group__desc">Yes</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="is_visible" data-title="Is Visible" data-value-text="No" value="0">
							<span class="input-group__desc">No</span>
						</label>
					</div>
				</td>
            </tr>
            <tr>
                <td>Groups :</td>
				<td>
					<div class="form-group" >
						<select name="user_groups" data-title="Groups" class="dt_filter js-select-user-groups-list-filter" multiple="multiple">
                            <?php foreach ($userGroups as $userGroup) {?>
                                <option value="<?php echo $userGroup['idgroup']; ?>"><?php echo $userGroup['gr_name']; ?></option>
                            <?php }?>
						</select>
					</div>
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
    var selectGroups;
    $(document).ready(function() {
		$('.datepicker').datepicker();
        selectGroups = $('.js-select-user-groups-list-filter').multipleSelect({
			width: '100%',
			placeholder: translate_js({plug:'multipleSelect', text: 'placeholder_users'}),
			selectAllText: translate_js({plug:'multipleSelect', text: 'select_all_text'}),
			allSelected: translate_js({plug:'multipleSelect', text: 'all_selected'}),
			countSelected: translate_js({plug:'multipleSelect', text: 'count_selected'}),
			noMatchesFound: translate_js({plug:'multipleSelect', text: 'no_matches_found'})
		});
    })
</script>
