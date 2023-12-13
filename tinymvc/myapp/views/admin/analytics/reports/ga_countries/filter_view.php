<div class="wr-filter-admin-panel">
    <div class="filter-admin-panel">
		<div class="title-b">Filter panel</div>
        <table class="w-100pr">
			<tr>
				<td>Date</td>
				<td>
					<input class="form-control dt_filter" type="text" data-title="Date" name="analytic_date" readonly value="<?php echo date("m/d/Y", strtotime("yesterday"));?>">
				</td>
			</tr>
			<tr>
				<td>Country</td>
				<td>
					<select class="dt_filter" data-title="Country" name="ga_country">
						<option value="">All</option>
						<?php foreach($countries as $country){?>
							<option value="<?php echo $country['ga_country'];?>"><?php echo $country['ga_country'];?></option>						
						<?php }?>
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
	$('input[name="analytic_date"]').datepicker({
		maxDate: '<?php echo date("m/d/Y", strtotime("yesterday"));?>',
		minDate: '09/01/2018',
	});

	$ga_countries = $('select[name="ga_country"]').select2({
		theme: "default ep-select2-h30",
		width: '100%',
		dropdownAutoWidth : true
	});
})
</script>
