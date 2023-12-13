<div class="container-fluid-modal">
    <label class="input-label">Partnership created</label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init start_from dt_filter" <?php echo addQaUniqueIdentifier('b2b-my-partners__filter-panel_partnership-from-input')?> type="text" placeholder="From" data-title="Created from" name="start_from" id="start_from" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init start_to dt_filter" <?php echo addQaUniqueIdentifier('b2b-my-partners__filter-panel_partnership-to-input')?> type="text" placeholder="To" data-title="Created to" name="start_to" id="start_to" readonly>
        </div>
    </div>

    <label class="input-label">Country</label>
    <select class="dt_filter" <?php echo addQaUniqueIdentifier('b2b-my-partners__filter-panel_country-select')?> id="id_country" name="id_country" data-title="Country">
        <option data-default="true" value=""><?php echo translate('form_placeholder_select_country');?></option>
        <?php foreach($port_country as $mcountry){ ?>
            <option value="<?php echo $mcountry['id']?>">
                <?php echo $mcountry['country'];?>
            </option>
        <?php } ?>
    </select>

    <label class="input-label">Search</label>
    <input class="dt_filter" <?php echo addQaUniqueIdentifier('b2b-my-partners__filter-panel_search-input')?> type="text" data-title="Search" name="keywords" maxlength="50" id="keywords" placeholder="Keywords">
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
