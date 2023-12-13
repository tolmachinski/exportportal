<div class="container-fluid-modal">
    <label class="input-label">Search</label>
    <input class="search dt_filter" type="text" data-title="Search" placeholder="Keywords" name="keywords">

    <label class="input-label">Categories</label>
    <select class="dt_filter minfo-form__input2 mb-0" data-title="Category" level="1" name="category">
        <option data-categories="" data-default="true" value="0">All</option>
        <?php foreach($counter_categories as $category){?>
            <option data-categories="<?php echo $category['category_id'];?>" value="<?php echo $category['category_id'];?>"><?php echo capitalWord($category['name']); ?> <span>(<?php echo $category['counter']?>)</span></option>
            <?php if(!empty($category['subcats'])){
                recursive_ctegories_product($category['subcats'], ' &mdash; ');
            }?>
        <?php } ?>
    </select>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label class="input-label text-nowrap">Featured number</label>
            <input class="dt_filter" type="text" data-title="Featured number" placeholder="Featured number" name="featured_number" value="<?php if(isset($id_featured)) echo orderNumber($id_featured);?>">
        </div>

        <div class="col-12 col-lg-6">
            <label class="input-label">Item number</label>
            <input class="dt_filter" type="text" data-title="Item number" placeholder="Item number" name="id_item" value="<?php if(isset($id_item)) echo orderNumber($id_item);?>">
        </div>

        <div class="col-12 col-lg-6">
            <label class="input-label">Status</label>
            <select class="dt_filter" data-title="Feature status" level="1" name="status">
                <option data-default="true"  value="">All</option>
                <option value="init" <?php if($selected_status == 'init'){echo 'selected="selected"';}?>>New</option>
                <option value="active" <?php if($selected_status == 'active'){echo 'selected="selected"';}?>>Active</option>
                <option value="expired" <?php if($selected_status == 'expired'){echo 'selected="selected"';}?>>Expired</option>
                <option value="expire_soon" <?php if($selected_status == 'expire_soon'){echo 'selected="selected"';}?>>Expire soon</option>
            </select>
        </div>

        <div class="col-12 col-lg-6">
            <label class="input-label">Paid</label>
            <select class="dt_filter" data-title="Paid" name="paid">
                <option value="">All</option>
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>

    <label class="input-label">Created</label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init start_from dt_filter" id="start_from" type="text" placeholder="From" data-title="Created from" name="create_date_from" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init start_to dt_filter" id="start_to" type="text" placeholder="To" data-title="Created to" name="create_date_to" readonly>
        </div>
    </div>

    <label class="input-label">Updated</label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init dt_filter" id="update_from" type="text" placeholder="From" data-title="Updated from" name="start_last_update" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init dt_filter" id="update_to" type="text" placeholder="To" data-title="Updated to" name="finish_last_update" readonly>
        </div>
    </div>

    <label class="input-label">Expired</label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init dt_filter" id="end_from" type="text" placeholder="From" data-title="Expired from" name="start_expire" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init dt_filter" id="end_to" type="text" placeholder="To" data-title="Expired to" name="finish_expire" readonly>
        </div>
    </div>
</div>
