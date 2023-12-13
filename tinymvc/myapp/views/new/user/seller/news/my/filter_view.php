<div class="container-fluid-modal">
	<label class="input-label">Created</label>
	<div class="row">
		<div class="col-12 col-lg-6 mb-15-sm-max">
			<input
                id="start_from"
                class="datepicker-init start_from dt_filter"
                type="text"
                placeholder="From"
                data-title="Created from"
                name="start_from"
                placeholder="From"
                readonly
                <?php echo addQaUniqueIdentifier('seller-news-my__filter-panel_created-from-input_popup'); ?>
            >
		</div>
		<div class="col-12 col-lg-6">
			<input
                id="start_to"
                class="datepicker-init start_to dt_filter"
                type="text"
                placeholder="To"
                data-title="Created to"
                name="start_to"
                readonly
                <?php echo addQaUniqueIdentifier('seller-news-my__filter-panel_created-to-input_popup'); ?>
            >
		</div>
	</div>

	<label class="input-label">Search</label>
	<input
        id="keywords"
        class="dt_filter"
        type="text"
        data-title="Search"
        name="keywords"
        maxlength="50"
        placeholder="Keywords"
        <?php echo addQaUniqueIdentifier('seller-news-my__filter-panel_keywords-input_popup'); ?>
    >
</div>

<script>
	$(function(){
        $(".datepicker-init").datepicker({
            beforeShow: function (input, instance) {
                $('#ui-datepicker-div').addClass('dtfilter-ui-datepicker');
            },
        });
	});
</script>
