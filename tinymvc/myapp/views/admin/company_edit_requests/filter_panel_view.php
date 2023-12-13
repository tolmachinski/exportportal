<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>

        <?php if (null !== ($filters['request'] ?? null)) { ?>
            <input
                type="hidden"
                name="request"
                class="dt_filter form-control"
                value="<?php echo cleanOutput($filters['request']['value'] ?? null); ?>"
                data-title="Request"
                data-value-text="<?php echo cleanOutput($filters['request']['text'] ?? null); ?>"
            >
        <?php } ?>

        <table>
            <tr>
                <td>Search</td>
                <td>
                    <div class="form-group mb-0">
						<div class="input-group">
							<input type="search" name="search" class="dt_filter form-control" data-title="Search" placeholder="Keywords" max="200">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
                </td>
            </tr>

            <tr>
				<td>Status</td>
				<td>
					<select class="dt_filter" data-title="Status" name="status">
						<option data-default="true" value="">All statuses</option>
						<?php foreach ($statuses as list($status, $statusLabel)) { ?>
							<option value="<?php echo cleanOutput((string) $status->value); ?>"><?php echo cleanOutput($statusLabel); ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>

            <tr>
				<td>Created</td>
				<td>
					<div class="input-group">
						<input
                            id="profile-edit-requests-dashboard--filters--created-from"
                            type="text"
                            name="created_from"
                            class="form-control dt_filter datetime-input"
                            placeholder="From"
                            data-title="Created from"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#profile-edit-requests-dashboard--filters--created-to"
                            data-entry-pair-action="min"
                            readonly
                        >
						<div class="input-group-addon">-</div>
						<input
                            id="profile-edit-requests-dashboard--filters--created-to"
                            type="text"
                            name="created_to"
                            class="form-control dt_filter datetime-input"
                            placeholder="To"
                            data-title="Created to"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#profile-edit-requests-dashboard--filters--created-from"
                            data-entry-pair-action="max"
                            readonly
                        >
					</div>
				</td>
			</tr>

            <tr>
				<td>Updated</td>
				<td>
					<div class="input-group">
						<input
                            id="profile-edit-requests-dashboard--filters--updated-from"
                            type="text"
                            name="updated_from"
                            class="form-control dt_filter datetime-input"
                            placeholder="From"
                            data-title="Updated from"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#profile-edit-requests-dashboard--filters--updated-to"
                            data-entry-pair-action="min"
                            readonly
                        >
						<div class="input-group-addon">-</div>
						<input
                            id="profile-edit-requests-dashboard--filters--updated-to"
                            type="text"
                            name="updated_to"
                            class="form-control dt_filter datetime-input"
                            placeholder="To"
                            data-title="Updated to"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#profile-edit-requests-dashboard--filters--updated-from"
                            data-entry-pair-action="max"
                            readonly
                        >
					</div>
				</td>
            </tr>

            <tr>
				<td>Accepted</td>
				<td>
					<div class="input-group">
						<input
                            id="profile-edit-requests-dashboard--filters--accepted-from"
                            type="text"
                            name="accepted_from"
                            class="form-control dt_filter datetime-input"
                            placeholder="From"
                            data-title="Accepted from"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#profile-edit-requests-dashboard--filters--accepted-to"
                            data-entry-pair-action="min"
                            readonly
                        >
						<div class="input-group-addon">-</div>
						<input
                            id="profile-edit-requests-dashboard--filters--accepted-to"
                            type="text"
                            name="accepted_to"
                            class="form-control dt_filter datetime-input"
                            placeholder="To"
                            data-title="Accepted to"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#profile-edit-requests-dashboard--filters--accepted-from"
                            data-entry-pair-action="max"
                            readonly
                        >
					</div>
				</td>
			</tr>

            <tr>
				<td>Declined</td>
				<td>
					<div class="input-group">
						<input
                            id="profile-edit-requests-dashboard--filters--declined-from"
                            type="text"
                            name="declined_from"
                            class="form-control dt_filter datetime-input"
                            placeholder="From"
                            data-title="Declined from"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#profile-edit-requests-dashboard--filters--declined-to"
                            data-entry-pair-action="min"
                            readonly
                        >
						<div class="input-group-addon">-</div>
						<input
                            id="profile-edit-requests-dashboard--filters--declined-to"
                            type="text"
                            name="declined_to"
                            class="form-control dt_filter datetime-input"
                            placeholder="To"
                            data-title="Declined to"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#profile-edit-requests-dashboard--filters--declined-from"
                            data-entry-pair-action="max"
                            readonly
                        >
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
    $(document).ready(function(){
        $(".filter-admin-panel").find("input.datetime-input").datepicker();
    })
</script>
