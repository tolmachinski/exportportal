<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table class="w-100pr">
			<tr>
				<td>Search</td>
				<td>
                    <input class="dt_filter pull-left" type="text" name="search" value="" data-title="Keywords" placeholder="Keywords">
				</td>
            </tr>

            <tr>
                <td>Status</td>
                <td>
                    <select class="form-control dt_filter" data-title="Status" name="status" data-type="select">
                        <option value="" data-default="true">All statuses</option>
                        <option value="new" data-value-text="New">New</option>
                        <option value="confirmed" data-value-text="Confirmed">Confirmed</option>
                        <option value="declined" data-value-text="Declined">Declined</option>
                    </select>
                </td>
            </tr>
<!--
            <tr>
				<td>Order ID</td>
				<td>
					<div class="form-group mb-0">
						<div class="input-group">
                            <input type="text"
                                name="order"
                                id="documents--order--filter-order"
                                class="dt_filter form-control"
                                placeholder="Order ID"
                                data-title="Order ID">
							<span class="input-group-btn">
								<a class="dt_filter-apply dt_filter-apply-buttons btn btn-primary mr-0 mb-0">
                                    <i class="ep-icon ep-icon_magnifier"></i>
                                </a>
							</span>
						</div>
					</div>
				</td>
            </tr> -->

			<tr>
				<td>Requested</td>
				<td>
                    <div class="input-group">
                        <input type="text"
                            name="requested_from"
                            class="form-control date-picker dt_filter"
                            placeholder="From"
                            data-title="Requested from"
                            readonly>
                        <div class="input-group-addon">-</div>
                        <input type="text"
                            name="requested_to"
                            class="form-control date-picker dt_filter"
                            placeholder="To"
                            data-title="Requested to"
                            readonly>
                    </div>
				</td>
            </tr>

            <tr>
				<td>Expires on</td>
				<td>
                    <div class="input-group">
                        <input type="text"
                            name="expiration_from"
                            class="form-control date-picker dt_filter"
                            placeholder="From"
                            data-title="Expires from"
                            readonly>
                        <div class="input-group-addon">-</div>
                        <input type="text"
                            name="expiration_to"
                            class="form-control date-picker dt_filter"
                            placeholder="To"
                            data-title="Expires to"
                            readonly>
                    </div>
				</td>
            </tr>
		</table>
		<div class="wr-filter-list clearfix mt-10"></div>
	</div>
	<div class="btn-display">
		<div class="i-block"><i class="ep-icon ep-icon_filter"></i></div>
		<span>&laquo;</span>
    </div>

	<div class="wr-hidden"></div>
</div>

<script>
    $(function(){
        $(".date-picker").datepicker();
    });
</script>
