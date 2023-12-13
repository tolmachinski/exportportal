<div class="container-fluid-modal">
    <label class="input-label">Start on</label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init dt_filter" type="text" placeholder="From" data-title="Start date from" name="start_date_from" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init dt_filter" type="text" placeholder="To" data-title="Start date to" name="start_date_to" readonly>
        </div>
    </div>

    <label class="input-label">Finish on</label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init dt_filter" type="text" placeholder="From" data-title="Finish date from" name="finish_date_from" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init dt_filter" type="text" placeholder="To" data-title="Finish date to" name="finish_date_to" readonly>
        </div>
    </div>

    <label class="input-label">Type</label>
    <select class="dt_filter" id="id_country" name="type_filter" data-title="Type">
        <option value="">All</option>
        <option value="training">Training</option>
        <option value="webinar">Webinar</option>
    </select>

    <label class="input-label">Search by</label>
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