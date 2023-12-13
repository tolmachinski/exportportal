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
				<td>Target</td>
				<td>
					<select class="dt_filter" data-title="Target" name="id_target">
						<option value="" >All</option>
						<?php foreach($targets as $target){?>
							<option value="<?php echo $target['id_target'];?>"><?php echo $target_types[$target['target_type']]['name'] .' - '. $target['target_name'];?></option>						
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
})
</script>
