<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel" >
		<div class="title-b">Filter panel</div>
		<table>
			<tr>
				<td>Order status:</td>
				<td>
					<select class="dt_filter" data-title="Order status" name="order_status">
						<option value="" data-default="true">All</option>
                        <?php foreach ($sample_order_statuses as $status) {?>
						  <option data-value-text="<?php echo $status['name'];?>" value="<?php echo $status['id'];?>"><?php echo $status['name'];?></option>
                        <?php }?>
					</select>
				</td>
            </tr>

			<tr>
				<td>Shipper:</td>
				<td>
					<select class="dt_filter" data-title="Freight Forwarder" name="id_ishipper">
						<option value="" data-default="true">All</option>
						<option value="0">Not assigned</option>
                        <?php foreach ($international_shippers as $shipper) {?>
						  <option data-value-text="<?php echo $shipper['shipper_original_name'];?>" value="<?php echo $shipper['id_shipper'];?>"><?php echo $shipper['shipper_original_name'];?></option>
                        <?php }?>
					</select>
				</td>
            </tr>

            <tr>
				<td>Buyer ID</td>
				<td>
                    <input class="form-control dt_filter" type="text" data-title="Buyer ID" name="id_buyer" placeholder="Buyer ID">
				</td>
            </tr>

            <tr>
				<td>Seller ID</td>
				<td>
                    <input class="form-control dt_filter" type="text" data-title="Seller ID" name="id_seller" placeholder="Seller ID">
				</td>
			</tr>

			<tr>
				<td>Search</td>
				<td>
                    <input class="form-control dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
				</td>
            </tr>

			<tr>
				<td>Price</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Price from" name="price_from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Price to" name="price_to" placeholder="To">
					</div>
				</td>
			</tr>

			<tr>
				<td>Create date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Create from" name="created_from" id="created_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Create to" name="created_to" id="created_to" placeholder="To" readonly>
					</div>
				</td>
            </tr>

			<tr>
				<td>Update date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter" type="text" data-title="Updated from" name="updated_from" id="updated_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="text" data-title="Updated to" name="updated_to" id="updated_to" placeholder="To" readonly>
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
    $(document).ready(function() {
		$("#created_from, #created_to, #updated_from, #updated_to").datepicker();
    });
</script>
