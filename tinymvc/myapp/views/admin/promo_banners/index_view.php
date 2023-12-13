<div class="row">
    <div class="col-xs-12">
        <div class="titlehdr h-30">
            <span>Promo banners</span>

            <?php if(have_right('manage_content')) { ?>
                <a
                    class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green"
                    href="<?php echo __SITE_URL; ?>promo_banners/popupForms/add_banner"
                    data-title="Add promo banners"
                    data-table="dtPromoBanners"
                ></a>
            <?php } ?>
        </div>

        <?php views()->display('admin/promo_banners/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table
            id="dtPromoBanners"
            class="data table-striped table-bordered w-100pr"
            cellspacing="0"
            cellpadding="0"
        >
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_image">Image</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_link">Link</th>
                    <th class="dt_page_position_name">Page position</th>
                    <th class="dt_order_banner">Order</th>
                    <th class="dt_date_added">Added</th>
                    <th class="dt_date_updated">Updated</th>
                    <th class="dt_visible">Visible</th>
                    <th class="dt_actions"></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?php tmvc::instance()->controller->view->display('admin/file_upload_scripts'); ?>

<script>
    var dtPromoBanners,
        requirementFilters;

    var deleteBanner = function(element){
        var $this = $(element);
        var url = __site_url + 'promo_banners/ajaxOperation/delete';
        var banner = $this.data('banner');

        $.ajax({
            url: url,
            type: 'POST',
            data:  {banner: banner},
            dataType: 'json',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if ('success' == resp.mess_type) {
                    dtPromoBanners.fnDraw();
                }
            }
        });
    }

    var visibleBanner = function(element){
        var $this = $(element);
        var url = __site_url + 'promo_banners/ajaxOperation/visible';
        var banner = $this.data('banner');

        $.ajax({
            url: url,
            type: 'POST',
            data:  {banner: banner},
            dataType: 'json',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if ('success' == resp.mess_type) {
                    dtPromoBanners.fnDraw();
                }
            }
        });
    }

    $(document).ready(function(){
        dtPromoBanners = $('#dtPromoBanners').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL;?>promo_banners/ajaxDtAdministration",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac vam w-50", "aTargets": ['dt_id'], "mData": "dt_id"},
                { "sClass": "tac vam", "aTargets": ['dt_title'], "mData": "dt_title"},
                { "sClass": "tac vam", "aTargets": ['dt_link'], "mData": "dt_link", "bSortable": false },
                { "sClass": "tac vam", "aTargets": ['dt_image'], "mData": "dt_image", "bSortable": false },
                { "sClass": "vam", "aTargets": ['dt_page_position_name'], "mData": "dt_page_position_name", "bSortable": false },
                { "sClass": "tac vam w-30", "aTargets": ['dt_order_banner'], "mData": "dt_order_banner"},
                { "sClass": "tac vam w-80", "aTargets": ['dt_date_added'], "mData": "dt_date_added"},
                { "sClass": "tac vam w-80", "aTargets": ['dt_date_updated'], "mData": "dt_date_updated"},
                { "sClass": "tac vam w-30", "aTargets": ['dt_visible'], "mData": "dt_visible"},
                { "sClass": "tac vam w-100", "aTargets": ['dt_actions'], "mData": "dt_actions", "bSortable": false },
            ],
            "sorting": [[0, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtPromoBanners.fnDraw(); },
                        onSet: function(callerObj, filterObj){
							if (filterObj.name == 'added_from') {
								$('input[name="added_to"]').datepicker("option", "minDate", $('input[name="added_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'added_to') {
								$('input[name="added_from"]').datepicker("option", "maxDate", $('input[name="added_to"]').datepicker("getDate"));
							}

							if (filterObj.name == 'updated_from') {
								$('input[name="updated_to"]').datepicker("option", "minDate", $('input[name="updated_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'updated_to') {
								$('input[name="updated_from"]').datepicker("option", "maxDate", $('input[name="updated_to"]').datepicker("getDate"));
                            }

                            if (filterObj.name == 'page_selection') {
								$('.dt-filter__param-remove[data-parent="page_position"]').trigger('click');
                            }
						},
                        onDelete: function(callerObj, filterObj){
                            if (filterObj.name == 'added_to') {
								$('input[name="added_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'added_from') {
								$('input[name="added_to"]').datepicker( "option" , {minDate: null});
							}

                            if (filterObj.name == 'updated_to') {
								$('input[name="updated_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'updated_from') {
								$('input[name="updated_to"]').datepicker( "option" , {minDate: null});
                            }

                            if (filterObj.name == 'page_selection') {
								$('.dt-filter__param-remove[data-parent="page_position"]').trigger('click');
                            }
                        },
						onReset: function(){
							$('.dt_filter .hasDatepicker').datepicker( "option" , {
								minDate: null,
								maxDate: null
							});
						}
                    });
                }

                aoData = aoData.concat(requirementFilters.getDTFilter());
                $.ajax( {
                    "dataType": 'JSON',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function (data, textStatus, jqXHR) {
                        if(data.mess_type == 'error' || data.mess_type == 'info')
                            systemMessages(data.message, 'message-' + data.mess_type);

                        fnCallback(data, textStatus, jqXHR);

                    }
                });
            },
            "sPaginationType": "full_numbers",
            "lengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
            "fnDrawCallback": function( oSettings ) {}
        });
    });
</script>
