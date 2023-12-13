<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table>
            <tr>
                <td>Categories</td>
                <td class="select_category">
                    <select class="categ1 dt_filter" data-title="Category" level="1" name="parent">
                        <option data-default="true"  value="0">All</option>
                        <?php
                        if(isset($categories) && is_array($categories) && count($categories) > 0){
                            foreach($categories as $category){?>
                            <option  value="<?php echo $category['category_id']?>"><?php echo $category['name']?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <select class="dt_filter" data-title="Featured status" level="1" name="itf_status">
                        <option data-default="true"  value="">All</option>
                        <?php foreach($statuses as $key=>$status){?>
                            <option  value="<?php echo $key;?>"><?php echo $status;?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Auto-extend</td>
                <td>
                    <select class="dt_filter" data-title="Auto-extend" level="1" name="auto_extend">
                        <option data-default="true"  value="">All</option>
                        <option data-default="true"  value="1">Enabled</option>
                        <option data-default="true"  value="0">Dissabled</option>
                    </select>
                </td>
            </tr>

			<tr>
				<td>Visible:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_invisible txt-blue input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="visible" data-title="Visible" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_visible txt-blue input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>Start date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter start_from" type="text" data-title="Start date from" name="start_from" id="start_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter start_to" type="text" data-title="Start date to" name="start_to" id="start_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>

			<tr>
				<td>Expire date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter end_from" type="text" data-title="Expire date from" name="end_from" id="end_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter end_to" type="text" data-title="Expire date to" name="end_to" id="end_to" placeholder="To" readonly>
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
$(document).ready(function(){
	$( ".start_from, .start_to, .end_from, .end_to, #date_from, #date_to" ).datepicker();

	$('body').on('change', 'select.categ1', function(){
        var select = this;
        var cat = select.value;
        var sClass = select.className;
        var control = select.id; //alert(cat + '-- '+ control);
        var level = $(select).attr('level');
        $('td.select_category div.subcategories').each(function (){
            thislevel = $(this).attr('level');
            if(thislevel > level) $(this).remove();
        });
        if(cat != 0){
            if(cat != control){
                $.ajax({
                    type: 'POST',
                    url: '/categories/getcategories',
					dataType: 'JSON',
                    data: { op : 'select', cat: cat, level : level, cl : sClass},
					beforeSend: function(){ showLoader('.full_block'); },
                    success: function(json){
					if(json.mess_type == 'success'){
						$('.select_category').append(json.content);
						$('select.categ1').css('color', 'black');
						$(select).css('color', 'red');
					}else{
						systemMessages(json.message,  'message-' + json.mess_type);
					}
						hideLoader('.full_block');
                    },
                    error: function(){alert('ERROR')}
                });
            }else{
                $('select.categ1').css('color', 'black');
                $('select.categ1[level='+(level-1)+']').css('color', 'red');
            }
        } else{
            $('.subcategories').remove();
        }

    });
})
</script>
