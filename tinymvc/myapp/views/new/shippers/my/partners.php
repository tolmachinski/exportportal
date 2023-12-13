<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>
<script type="text/template" id="international-partner--add">
    <a class="dropdown-item confirm-dialog" href="#" data-message="Are you sure you want to add this partner?" data-callback="ishipper_partnership" data-shipper="{{shipper}}" title="Mark as partner">
        <i class="ep-icon ep-icon_plus-circle"></i><span class="txt">Mark as partner</span>
    </a>
</script>
<script type="text/template" id="international-partner--remove">
    <a class="dropdown-item confirm-dialog" href="#" data-message="Are you sure you want to remove this partner?" data-callback="ishipper_partnership" data-shipper="{{shipper}}" title="Remove partner">
        <i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Remove partner</span>
    </a>
</script>
<script>
    var dtShippersList;

    var	decline_request = function(obj) {
        var $this = $(obj);
        var shipper = $this.data('shipper');
        var partner = $this.data('partner');
        var url = __site_url + 'shippers/ajax_shippers_operation/remove_partnership';
        var data = { shipper: shipper, partner: partner };
        var onRequestSuccess = function(resp) {
            systemMessages(resp.message, resp.mess_type );
            if (resp.mess_type == 'success') {
                dtShippersList.fnDraw();
            }
        };

        return $.post(url, data, null, 'json')
            .done(onRequestSuccess)
            .fail(onRequestError);
    }

    var	ishipper_partnership = function(obj){
        var $this = $(obj);
        var shipper = $this.data('shipper');
        var url = __site_url + 'shippers/ajax_shippers_operation/ishipper_partnership';
        var data = { shipper: shipper };
        var addButtonTemplate = $('#international-partner--add').text();
        var removeButtonTemplate = $('#international-partner--remove').text();
        var onRequestSuccess = function(resp) {
            systemMessages(resp.message, resp.mess_type);
            if (resp.mess_type == 'success') {
                $parent_tr = $this.closest('tr');
                switch (resp.action) {
                    case 'confirm':
                        $parent_tr.find('.js-dt-partners').html('<span class="ep-icon ep-icon_plus-stroke fs-24 lh-24" title="Are partners"></span>');
                        $this.replaceWith(removeButtonTemplate.replace('{{shipper}}', shipper));

                        break;
                    case 'cancel':
                        $parent_tr.find('.js-dt-partners').html('<span class="ep-icon ep-icon_minus-stroke fs-24 lh-24" title="Not a partner"></span>');
                        $this.replaceWith(addButtonTemplate.replace('{{shipper}}', shipper));

                        break;
                }
            }
        };

        return postRequest(url, data)
            .then(onRequestSuccess)
            .catch(onRequestError);
    }

    $(document).ready(function(){
        dtShippersList = $('#dtShippersList').dataTable( {
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL; ?>shippers/ajax_partners_list_dt",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "vam tal", "aTargets": ['dt_seller'], "mData": "seller_dt", "bSortable": false},
                {"sClass": "w-130 vam", "aTargets": ['dt_contact'], "mData": "contact_dt", "bSortable": false},
                {"sClass": "w-100 vam", "aTargets": ['dt_date'], "mData": "date_dt"},
                {"sClass": "w-30 tac vam dt-actions", "aTargets": ['dt_actions'], "mData": "actions_dt", "bSortable": false}
            ],
            "sorting" : [[2,'desc']],
            "sPaginationType": "full_numbers",
            "language": {
                "paginate": {
                    "first": "<i class='ep-icon ep-icon_arrow-left'></i>",
                    "previous": "<i class='ep-icon ep-icon_arrows-left'></i>",
                    "next": "<i class='ep-icon ep-icon_arrows-right'></i>",
                    "last": "<i class='ep-icon ep-icon_arrow-right'></i>"
                }
            },
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": function(data, textStatus, jqXHR) {
                        if ( data.mess_type == 'error' || data.mess_type == 'info' ) {
                            systemMessages(data.message, data.mess_type);
                        }

                        fnCallback(data, textStatus, jqXHR);
                    }
                });
            },
            "fnDrawCallback": function(oSettings) {
                hideDTbottom(this);
            }
        });

        $('a[data-toggle="tab"]').on( 'shown.bs.tab', function (e) {
            dataTableDrawHidden();
        });
    });
</script>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My Freight Forwarders</h1>

        <!-- <div class="dashboard-line__actions">
            <a class="btn btn-light fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>user_guide/popup_forms/show_doc/11" title="View B2B Freight Forwarders user guide" data-title="View B2B Freight Forwarders user guide" target="_blank">User guide</a>
        </div> -->
    </div>

	<div class="info-alert-b">
		<i class="ep-icon ep-icon_info-stroke"></i>
		<span><?php echo translate('shippers_my_partners_description'); ?></span>
	</div>

    <ul class="nav nav-tabs nav--borders" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo ($tab == 'international_shippers')?'active':'';?>" href="#ishippers" aria-controls="ishippers" role="tab" data-toggle="tab">International freight forwarders</a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo ($tab == 'ep_shippers')?'active':'';?>" href="#ep_shippers" aria-controls="ep_shippers" role="tab" data-toggle="tab">Export Portal freight forwarders</a>
        </li>
    </ul>

    <div class="tab-content tab-content--borders">
        <div role="tabpanel" class="tab-pane fade <?php echo ($tab == 'international_shippers')?'show active':'';?>" id="ishippers">
            <table class="main-data-table">
                <thead>
                    <tr>
                        <th class="dt_shipper tal">Company name</th>
                        <th class="dt_partners w-120 tac">Partners</th>
                        <th class="dt_actions w-30 tac"></th>
                    </tr>
                </thead>
                <tbody class="tabMessage">
                    <?php foreach($ishippers as $ishipper){?>
                        <tr>
                            <td class="vam">
                                <div class="vam mr-10 h-20 display-i">
                                    <img class="h-20 vam" src="<?php echo __IMG_URL.'public/img/ishippers_logo/'.$ishipper['shipper_logo']; ?>" alt="<?php echo $ishipper['shipper_original_name']; ?>"/>
                                </div>
                                <?php echo $ishipper['shipper_original_name']; ?>
                            </td>
                            <td class="tac vam js-dt-partners">
                                <?php if(isset($ishippers_partners[$ishipper['id_shipper']])) { ?>
                                    <span class="ep-icon ep-icon_plus-stroke fs-24 lh-24" title="Are partners"></span>
                                <?php } else { ?>
                                    <span class="ep-icon ep-icon_minus-stroke fs-24 lh-24" title="Not a partner"></span>
                                <?php } ?>
                            </td>
                            <td class="tac vam">
                                <div class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                        <i class="ep-icon ep-icon_menu-circles"></i>
                                    </a>
                                    <div class="dropdown-menu">

                                        <a class="dropdown-item" data-title="Contact partner" title="Website" target="_blank" href="<?php echo $ishipper['shipper_website']; ?>">
                                            <i class="ep-icon ep-icon_link"></i><span class="txt">Website</span>
                                        </a>
                                        <a class="dropdown-item" data-title="Contact partner" title="Contact partner" target="_blank" href="<?php echo $ishipper['shipper_contacts']; ?>">
                                            <i class="ep-icon ep-icon_envelope"></i><span class="txt">Contact page</span>
                                        </a>
                                        <?php if(isset($ishippers_partners[$ishipper['id_shipper']])) { ?>
                                            <a class="dropdown-item confirm-dialog" href="#" data-message="Are you sure you want to remove this partner?" data-callback="ishipper_partnership" data-shipper="<?php echo $ishipper['id_shipper']; ?>" title="Remove partner">
                                                <i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Remove partner</span>
                                            </a>
                                        <?php } else { ?>
                                            <a class="dropdown-item confirm-dialog" href="#" data-message="Are you sure you want to add this partner?" data-callback="ishipper_partnership" data-shipper="<?php echo $ishipper['id_shipper']; ?>" title="Mark as partner">
                                                <i class="ep-icon ep-icon_plus-circle"></i><span class="txt">Mark as partner</span>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div role="tabpanel" class="tab-pane fade <?php echo ($tab == 'ep_shippers') ? 'show active' : ''; ?>" id="ep_shippers">
            <table class="main-data-table" id="dtShippersList">
                <thead>
                    <tr>
                        <th class="dt_seller">Company</th>
                        <th class="dt_contact">Contact</th>
                        <th class="dt_date">Date</th>
                        <th class="dt_actions"></th>
                    </tr>
                </thead>
                <tbody class="tabMessage"></tbody>
            </table>
        </div>
    </div>
</div>


