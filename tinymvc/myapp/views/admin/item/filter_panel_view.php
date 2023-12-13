<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel">
        <div class="title-b">Filter panel</div>

        <?php if (!empty($filters['id_item'])) { ?>
            <input
                class="form-control dt_filter"
                type="hidden"
                name="id_item"
                value="<?php echo cleanOutput($filters['id_item']); ?>"
                data-title="ID item"
                data-value-text="<?php echo cleanOutput($filters['id_item']); ?>">
        <?php }
        if (!empty($filters['expire'])) { ?>
            <input
                class="form-control dt_filter"
                type="hidden"
                name="expire"
                value="<?php echo $filters['expire']; ?>"
                data-title="Draft expire"
                data-value-text="<?php echo $filters['expire']; ?>">
        <?php }
        if (!empty($filters['seller'])) { ?>
            <input
                class="form-control dt_filter"
                type="hidden"
                name="seller"
                value="<?php echo $filters['seller']; ?>"
                data-title="ID seller"
                data-value-text="<?php echo (int) $filters['seller']; ?>">
        <?php } ?>

		<table>
			<tr>
				<td>Categories</td>
				<td class="select_category">
                    <select id="test-select-category" class="dt_filter display-n" data-title="Category" name="parent">
                        <option data-default="true" value="0">All</option>
                    </select>

					<select class="categ1" data-title="Category" level="1" name="parent">
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
				<td>Create date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter start_from" type="text" data-title="Create date from" name="start_from" id="start_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter start_to" type="text" data-title="Create date to" name="start_to" id="start_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>

			<tr>
				<td>Update date</td>
				<td>
					<div class="input-group">
						<input class="form-control dt_filter update_from" type="text" data-title="Update date from" name="update_from" id="update_from" placeholder="From" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter update_to" type="text" data-title="Update date to" name="update_to" id="update_to" placeholder="To" readonly>
					</div>
				</td>
			</tr>

            <tr>
				<td>Draft</td>
				<td>
					<select class="dt_filter" data-title="Draft" name="draft">
						<option value="" data-default="true">All</option>
						<option value="0" <?php echo $filters['draft'] == 0 ? 'selected="selected"' : '';?>>Not draft (published)</option>
						<option value="1" <?php echo $filters['draft'] == 1 ? 'selected="selected"' : '' ;?>>Only draft</option>
					</select>
				</td>
			</tr>

            <tr>
				<td>Labels</td>
				<td>
					<select class="dt_filter" data-title="Label" name="label">
                        <option data-default="true" value="">All</option>
						<option value="1" <?php echo selected($filters['label'], 1);?>>Blocked</option>
						<option value="2" <?php echo selected($filters['label'], 2);?>>Pending</option>
						<option value="3" <?php echo selected($filters['label'], 3);?>>Published</option>
					</select>
				</td>
			</tr>

			<tr>
				<td>Highlight:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="highlight" data-title="Highlight" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="highlight" data-title="Highlight" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_unhighlight txt-lblue-darker input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="highlight" data-title="Highlight" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_highlight txt-lblue-darker input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>

			<tr>
				<td>Featured:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="featured" data-title="Featured" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="featured" data-title="Featured" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_unfeatured txt-orange input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="featured" data-title="Featured" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_featured txt-orange input-group__desc"></i>
						</label>
					</div>
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
				<td>Locked:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon" title="All">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon" title="Not blocked">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_unlocked txt-blue input-group__desc"></i>
						</label>
						<label class="input-group-addon" title="Blocked">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_locked txt-blue input-group__desc"></i>
						</label>
						<label class="input-group-addon display-n" title="Blocked by downgrade">
							<input class="dt_filter" type="radio" name="blocked" data-title="Locked" data-value-text="Yes" value="2">
							<i class="ep-icon ep-icon_locked txt-blue input-group__desc"></i>
						</label>
					</div>
				</td>
			</tr>
            <tr>
				<td>Partners:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="partnered_item" data-title="Item of partner" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="partnered_item" data-title="Item of partner" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_partners txt-red input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="partnered_item" data-title="Item of partner" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_partners txt-green input-group__desc"></i>
						</label>
					</div>
				</td>
            </tr>
            <tr>
				<td>Translation:</td>
				<td>
                    <select class="dt_filter" data-title="Translation" name="translation_status">
                        <option data-default="true" value="0">All</option>
                        <option value="need_translate">Need translate</option>
                        <option value="translated">Translated</option>
                        <option value="removed">Removed</option>
                    </select>
				</td>
            </tr>
            <tr>
				<td>Fake:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="fake_item" data-title="Fake items" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="fake_item" data-title="Fake items" data-value-text="No" value="0">
							<i class="ep-icon ep-icon_smile txt-green input-group__desc"></i>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="fake_item" data-title="Fake items" data-value-text="Yes" value="1">
							<i class="ep-icon ep-icon_minus-circle txt-red input-group__desc"></i>
						</label>
					</div>
				</td>
            </tr>

            <tr>
                <td>By username or <br> email</td>
                <td>
                    <input type="text"
                           name="search_by_username_email"
                           class="dt_filter form-control"
                           value=""
                           data-title="Search by username or email"
                           placeholder="Search by username or email">
                </td>
            </tr>

            <tr>
                <td>By company <br> name</td>
                <td>
                    <input type="text"
                           name="search_by_company"
                           class="dt_filter form-control"
                           value=""
                           data-title="Search by company name"
                           placeholder="Search by company">
                </td>
            </tr>

            <tr>
                <td>By keywords</td>
                <td>
                    <input type="text"
                           name="search_by_keywords"
                           class="dt_filter form-control"
                           value=""
                           data-title="Search by keywords"
                           placeholder="Search by keywords">
                </td>
            </tr>

            <tr>
                <td>By Name</td>
                <td>
                    <input type="text"
                           name="search_by_title"
                           class="dt_filter form-control"
                           value=""
                           data-title="Search by title"
                           placeholder="Search by title">
                </td>
            </tr>

            <tr>
				<td>Archived:</td>
				<td>
					<div class="input-group input-group--checks">
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="archived" data-title="Archived" data-value-text="All" value="" checked="checked">
							<span class="input-group__desc">All</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="archived" data-title="Archived" data-value-text="Yes" value="1">
							<span class="input-group__desc">Yes</span>
						</label>
						<label class="input-group-addon">
							<input class="dt_filter" type="radio" name="archived" data-title="Archived" data-value-text="No" value="0">
							<span class="input-group__desc">No</span>
						</label>
					</div>
				</td>
			</tr>
		</table>
		<div class="clearfix">
			<div class="title-b pull-left">Category filters</div>
			<a href="#" id="toggle_category_filters" class="ep-icon ep-icon_arrows-up fs-25 pull-right"></a>
		</div>
		<div id="category_filters"></div>
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
	$( ".start_from, .start_to, .update_from, .update_to" ).datepicker();

	$('body').on('change', '.select_category select', function(){
		$('#category_filters').html('');
		$('a#toggle_category_filters').removeClass('ep-icon_arrows-down').addClass('ep-icon_arrows-up');
	});

	$('body').on('click','a#toggle_category_filters', function(e){
		e.preventDefault();
		var categories = [];
		$('.select_category select').each(function(){
			if((parseInt($(this).val()) > 0) && ($.inArray($(this).val(), categories) < 0)){
				categories.push($(this).val());
			}
		});
		if(categories.length > 0){
			var category = categories.join();
			link = 'items/ajax_item_operation/get_category_attrs';
			$.ajax({
				type: 'POST',
				async: false,
				context: $(this),
				url: link,
				data: {categories: category},
				success: function(data){ //alert(data);
					resp = JSON.parse(data);
					if(resp.mess_type == 'success'){
						$('#category_filters').html(resp.content);
						$(this).removeClass('ep-icon_arrows-up').addClass('ep-icon_arrows-down');
					}else{
						systemMessages( resp.message, 'message-' + resp.mess_type );
					}
				}
			});
		} else{
			systemMessages( 'Please select categories first.', 'message-info' );
		}
	});
})
</script>
