<div class="container-fluid-modal">
	<label class="input-label">Created</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init start_from dt_filter" type="text" placeholder="From" data-title="Created from" name="start_from" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init start_to dt_filter" type="text" placeholder="To" data-title="Created to" name="start_to" readonly>
		</div>
	</div>

	<label class="input-label">Updated</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init update_from dt_filter" type="text" placeholder="From" data-title="Updated from" name="update_from" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init update_to dt_filter" type="text" placeholder="To" data-title="Updated to" name="update_to" readonly>
		</div>
	</div>

	<label class="input-label">Status</label>
	<select class="dt_filter" data-title="Status" name="status_filter">
		<option value="" data-value-text="">All</option>
		<?php foreach($ereports_statuses as $status_key => $status){?>
			<option value="<?php echo $status_key;?>" data-value-text="<?php echo $status['title'];?>"><?php echo $status['title'];?></option>
		<?php }?>
	</select>

	<label class="input-label">Refund amount</label>
	<div class="row">
		<div class="col-6">
			<input class="dt_filter" type="text" placeholder="From" data-title="Refund amount from" name="refund_amount_from">
		</div>
		<div class="col-6">
			<input class="dt_filter" type="text" placeholder="To" data-title="Refund amount to" name="refund_amount_to">
		</div>
	</div>

	<label class="input-label">Search</label>
	<input class="dt_filter" type="text" data-title="Search by" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">
</div>

<script>
	$(function(){
        $(".datepicker-init").datepicker({
            beforeShow: function (input, instance) {
                $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
            },
        });

		window.onpopstate = function(event) {
			location.reload(true);
		};
	});
</script>
