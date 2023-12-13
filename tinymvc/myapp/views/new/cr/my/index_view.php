<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-datatables-1-10-12/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-dtfilters/jquery.dtFilters.js');?>"></script>

<script>
    var dtTrainingsList;
    var trainingsFilters;

    $(document).ready(function(){
        var fnDrawFirst = 0;

        dataT = dtTrainingsList = $('#dtTrainingsList').dataTable( {
            "sDom": '<"top"i>rt<"bottom"lp><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL?>cr_training/ajax_my_trainings",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                {"sClass": "vam", "aTargets": ['dt_title'], "mData": "dt_title"},
                {"sClass": "w-130 vam", "aTargets": ['dt_start_date'], "mData": "dt_start_date" },
                {"sClass": "w-130 vam", "aTargets": ['dt_finish_date'], "mData": "dt_finish_date" },
                {"sClass": "w-70 vam", "aTargets": ['dt_type'], "mData": "dt_type", "bSortable": false },
            ],
            "sorting" : [ [2,'desc'], [1,'asc'],],
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
                if (!trainingsFilters) {
                    //view template initDtFilter in scripts_new
                    trainingsFilters = initDtFilter();
                }


                aoData = aoData.concat(trainingsFilters.getDTFilter());
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
        dataTableScrollPage(dataT);
    });

    var trainingInformationFancybox = function($this){
        var nTr = $this.parents('tr')[0];

        var aData = dtTrainingsList.fnGetData(nTr);
        var sOut = '<div class="flex-card">\
                        <div class="flex-card__float">'
                        + aData['dt_description']
                        + '</div>\
				    </div>';

        $.fancybox.open({
            title: 'Training information',
            content: sOut
        },{
            width		: fancyW,
            height		: 'auto',
            maxWidth	: 700,
            autoSize	: false,
            loop : false,
            helpers : {
                title: {
                    type: 'inside',
                    position: 'top'
                },
                overlay: {
                    locked: true
                }
            },
            modal: true,
            closeBtn : true,
            padding : fancyP,
            closeBtnWrapper: '.fancybox-skin .fancybox-title',
            lang : __site_lang,
            i18n : translate_js_one({plug:'fancybox'}),
            beforeShow : function() {

            },
            beforeLoad : function() {
                this.width = fancyW;
                this.padding = [fancyP,fancyP,fancyP,fancyP];
            },
            onUpdate : function() {}
        });
    }

</script>

<?php tmvc::instance()->controller->view->display('new/filter_panel_main_view', array('filter_panel' => 'new/cr/my/filter_panel_view')); ?>

<div class="container-center dashboard-container">

    <div class="dashboard-line">
        <h1 class="dashboard-line__ttl">My Trainings</h1>

        <div class="dashboard-line__actions">
            <a class="btn btn-dark fancybox btn-filter" href="#dtfilter-hidden" data-mw="740" data-title="Filter panel">
                <i class="ep-icon ep-icon_filter"></i> Filter
            </a>
        </div>
    </div>

    <table class="main-data-table" id="dtTrainingsList">
        <thead>
            <tr>
                <th class="dt_title">Title</th>
                <th class="dt_start_date">Start at</th>
                <th class="dt_finish_date">Finish at</th>
                <th class="dt_type">Type</th>
            </tr>
        </thead>
        <tbody class="tabMessage"></tbody>
    </table>
</div>


