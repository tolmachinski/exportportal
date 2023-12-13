<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>

		<table>
            <tr>
                <td>Search</td>
                <td>
                    <div class="form-group mb-0">
						<div class="input-group">
							<input type="search" name="search" class="dt_filter form-control" data-title="Search" placeholder="Keywords">
							<span class="input-group-btn">
								<a class="dt-filter-apply dt-filter-apply-buttons btn btn-primary mr-0 mb-0"><i class="ep-icon ep-icon_magnifier "></i></a>
							</span>
						</div>
					</div>
                </td>
            </tr>

            <tr>
                <td>Category</td>
                <td>
                    <select class="form-control dt_filter" data-title="Country" name="category" data-type="select" id="countries">
                        <option data-default="true">Any category</option>
                        <?php if (!empty($categories)) { ?>
                            <?php foreach ($categories as $category) { ?>
                                <option value="<?php echo cleanOutput($category['category_id']); ?>"><?php echo cleanOutput($category['name']); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Country (from)</td>
                <td>
                    <select class="form-control dt_filter" data-title="Country (from)" name="country_from" data-type="select" id="countries-from">
                        <option data-default="true">Any country</option>
                        <?php if (!empty($countries)) { ?>
                            <?php foreach ($countries as $country) { ?>
                                <option value="<?php echo cleanOutput($country['id']); ?>"><?php echo cleanOutput($country['country']); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Country (to)</td>
                <td>
                    <select class="form-control dt_filter" data-title="Country (to)" name="country_to" data-type="select" id="countries-to">
                        <option data-default="true">Any country</option>
                        <?php if (!empty($countries)) { ?>
                            <?php foreach ($countries as $country) { ?>
                                <option value="<?php echo cleanOutput($country['id']); ?>"><?php echo cleanOutput($country['country']); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Quantity</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="number" min="0" step="1" name="quantity_from" data-type="text" data-title="Qunatity from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="number" min="0" step="1" name="quantity_to" data-type="text" data-title="Quantity to" placeholder="To">
					</div>
                </td>
            </tr>

            <tr>
                <td>Price</td>
                <td>
                    <div class="input-group">
						<input class="form-control dt_filter" type="number" min="0" step="0.01" name="price_from" data-type="text" data-title="Price from" placeholder="From">
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter" type="number" min="0" step="0.01" name="price_to" data-type="text" data-title="Price to" placeholder="To">
					</div>
                </td>
            </tr>

            <tr>
                <td>Created</td>
                <td>
                    <div class="input-group">
                        <input
                            id="product-requests-dashboard--filters--created-from"
                            type="text"
                            name="created_from"
                            class="form-control dt_filter datepicker"
                            data-title="Created from"
                            placeholder="From"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#product-requests-dashboard--filters--created-to"
                            data-entry-pair-action="min">
						<div class="input-group-addon">-</div>
                        <input
                            id="product-requests-dashboard--filters--created-to"
                            type="text"
                            name="created_to"
                            class="form-control dt_filter datepicker"
                            data-title="Created to"
                            placeholder="To"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#product-requests-dashboard--filters--created-from"
                            data-entry-pair-action="max">
					</div>
                </td>
            </tr>

            <tr>
                <td>Updated</td>
                <td>
                    <div class="input-group">
                        <input
                            id="product-requests-dashboard--filters--updated-from"
                            class="form-control dt_filter datepicker"
                            type="text"
                            name="updated_from"
                            data-title="Updated from"
                            placeholder="From"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#product-requests-dashboard--filters--updated-to"
                            data-entry-pair-action="min">
						<div class="input-group-addon">-</div>
                        <input
                            id="product-requests-dashboard--filters--updated-to"
                            class="form-control dt_filter datepicker"
                            type="text"
                            name="updated_to"
                            data-title="Updated to"
                            placeholder="To"
                            data-entry-type="datepicker"
                            data-entry-pair="1"
                            data-entry-pair-selector="#product-requests-dashboard--filters--updated-from"
                            data-entry-pair-action="max">
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
