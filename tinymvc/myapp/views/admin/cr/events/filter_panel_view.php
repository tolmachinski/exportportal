<div class="wr-filter-admin-panel">
	<div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
		<table>
			<tr>
				<td>Search by</td>
				<td>
					<input class="dt_filter" type="text" data-title="Search for" name="keywords" maxlength="50" placeholder="Keywords">
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
				<td>Status:</td>
				<td>
                    <select class="dt_filter" data-title="Status" name="status">
                        <option value="">All</option>
                        <option value="init">Init</option>
                        <option value="approved">Approved</option>
                    </select>
				</td>
			</tr>
			<tr>
				<td>Country:</td>
				<td>
                    <select class="dt_filter" data-title="Country" id="filter-country" name="country">
                        <option value="">All</option>
                        <?php foreach($countries as $country){ ?>
                            <option value="<?php echo $country['id_country']?>">
                                <?php echo $country['country']?>
                            </option>
                        <?php } ?>
                    </select>
				</td>
			</tr>
			<tr>
				<td>State/Province:</td>
				<td>
                    <select class="dt_filter" data-title="State / Province" name="states" id="filter-state">
                        <option value="">All</option>
                    </select>
				</td>
			</tr>
			<tr>
				<td>City:</td>
				<td>
                    <select class="dt_filter filter-select-city" data-title="City" name="city">
                        <option value="">All</option>
                    </select>
				</td>
			</tr>
			<tr>
				<td>Start Date</td>
				<td>
				    <div class="input-group">
						<input class="form-control dt_filter date-picked" type="text" data-title="Start date from" name="date_start_from" placeholder="Start date from" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picked" type="text" data-title="Start date to" name="date_start_to" placeholder="Start date to" readonly>
					</div>
				</td>
			</tr>
			<tr>
				<td>End Date</td>
				<td>
				    <div class="input-group">
						<input class="form-control dt_filter date-picked" type="text" data-title="End date from" name="date_end_from" placeholder="End date from" readonly>
						<div class="input-group-addon">-</div>
						<input class="form-control dt_filter date-picked" type="text" data-title="End date to" name="date_end_to" placeholder="End date to" readonly>
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
    $('.date-picked').datepicker();

    var $selectCity = $('.filter-select-city');

    initSelectCity($selectCity);

    $('#filter-state').on('change', function(){
        window.selectState = $(this).val();
        $selectCity.empty().prop('disabled', false);

        var select_text = '';
        if(window.selectState !== '' || window.selectState !== 0){
            select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
        } else{
            select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
            $selectCity.prop('disabled', true);
        }

        $selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
    });

    $('#filter-country').on('change', function(){
        selectCountry($(this), 'select#filter-state');
        $selectCity.empty().prop('disabled', true);
    });
</script>
