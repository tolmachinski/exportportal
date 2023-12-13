<div class="container-fluid-modal">
	<label class="input-label">Created date</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input class="datepicker-init start_from dt_filter" id="start_from" type="text" placeholder="From" data-title="Created from" name="start_from" placeholder="From" readonly>
		</div>
		<div class="col-12 col-lg-6">
			<input class="datepicker-init start_to dt_filter" id="start_to" type="text" placeholder="To" data-title="Created to" name="start_to" readonly>
		</div>
	</div>

	<label class="input-label">Search by</label>
	<input class="dt_filter" type="text" data-title="Search by" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">

	<label class="input-label">Category</label>
	<select class="dt_filter" data-title="Category" name="category">
		<option data-default="true" value="">All categories</option>
		<?php foreach ($question_categories as $question_category) { ?>
			<option value="<?php echo $question_category['idcat']; ?>"><?php echo $question_category['title_cat']; ?></option>
		<?php } ?>
	</select>
	<label class="input-label">Country</label>
	<select class="dt_filter" data-title="Country" name="country">
		<option data-default="true" value="">All countries</option>
		<?php echo getCountrySelectOptions($question_countries, 0, array('include_default_option' => false));?>
	</select>

	<!-- HIDDEN FILTERS -->
	<div class="display-n">
		<input class="dt_filter" type="text" data-title="Feedback number" name="feedback_number" placeholder="Feedback number" value="<?php if(isset($id_feedback)) echo orderNumber($id_feedback);?>">
	</div>
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

    function beforeSetFilters(callerObj){
        
    }

    function onDeleteFilters(filter){
        
    }
</script>