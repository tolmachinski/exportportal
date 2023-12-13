<div class="row">
    <div class="col-xs-12">
        <?php views()->display('admin/comments/filter_panel_view'); ?>
        <div class="wr-filter-list clearfix mt-10"></div>

        <table class="data table-striped table-bordered w-100pr" id="dtComments" cellspacing="0" cellpadding="0" >
            <thead>
                <tr>
                    <th class="dt_id tac w-30 vam">#</th>
                    <th class="dt_text tac vam">Text</th>
                    <th class="dt_type tac w-150 vam">On</th>
                    <th class="dt_state tac w-120 vam">State</th>
                    <th class="dt_author tac w-200 vam">Author</th>
                    <th class="dt_email tac w-200 vam">Email</th>
                    <th class="dt_created_date tac w-110 vam">Created date</th>
                    <th class="dt_published_date tac w-110 vam">Published date</th>
                    <th class="dt_actions w-20 tac vam">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
    var requirementFilters;
    var dtComments;

    $(document).ready(function(){
        dtComments = $('#dtComments').dataTable( {
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL . "comments/ajax_dt_administration";?>",
            "sServerMethod": "POST",
            "aoColumnDefs": [
                { "sClass": "tac vam w-30",  "aTargets": ['dt_id'], "mData": "dt_comment_id"},
                { "sClass": "vam", "aTargets": ['dt_text'], "mData": "dt_comment_text", "bSortable": false },
                { "sClass": "tac vam w-150", "aTargets": ['dt_type'], "mData": "dt_comment_type", "bSortable": false },
                { "sClass": "vam w-120", "aTargets": ['dt_state'], "mData": "dt_comment_state", "bSortable": false },
                { "sClass": "vam w-200", "aTargets": ['dt_author'], "mData": "dt_comment_author", "bSortable": false},
                { "sClass": "vam w-200", "aTargets": ['dt_email'], "mData": "dt_author_email", "bSortable": false},
                { "sClass": "vam w-110", "aTargets": ['dt_created_date'], "mData": "dt_comment_created_date"},
                { "sClass": "vam w-110", "aTargets": ['dt_published_date'], "mData": "dt_comment_published_date"},
                { "sClass": "tar vam w-20", "aTargets": ['dt_actions'], "mData": "dt_comment_actions", "bSortable": false },
            ],
            "sorting": [[0, "desc"]],
            "fnServerData": function ( sSource, aoData, fnCallback ) {

                if(!requirementFilters){
                    requirementFilters = $('.dt_filter').dtFilters('.dt_filter',{
                        'container': '.wr-filter-list',
                        'debug':false,
                        callBack: function(){ dtComments.fnDraw(); },
                        onSet: function(callerObj, filterObj){
							if (filterObj.name == 'created_from') {
								$('input[name="created_to"]').datepicker("option", "minDate", $('input[name="created_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'created_to') {
								$('input[name="created_from"]').datepicker("option", "maxDate", $('input[name="created_to"]').datepicker("getDate"));
							}

							if (filterObj.name == 'published_from') {
								$('input[name="published_to"]').datepicker("option", "minDate", $('input[name="published_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'published_to') {
								$('input[name="published_from"]').datepicker("option", "maxDate", $('input[name="published_to"]').datepicker("getDate"));
							}
						},
                        onDelete: function(callerObj, filterObj){
                            if (filterObj.name == 'created_to') {
								$('input[name="created_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'created_from') {
								$('input[name="created_to"]').datepicker( "option" , {minDate: null});
							}

                            if (filterObj.name == 'published_to') {
								$('input[name="published_from"]').datepicker( "option" , {maxDate: null});
                            }

                            if (filterObj.name == 'published_from') {
								$('input[name="published_to"]').datepicker( "option" , {minDate: null});
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
            "fnDrawCallback": function( oSettings ) {

            }
        });
    });

    var deleteComment = function(element){
        var $this = $(element);
        var url = $this.data('href');
        var comment = $this.data('comment');

        $.ajax({
            url: url,
            type: 'POST',
            data:  {comment:comment},
            dataType: 'json',
            success: function(resp){
                systemMessages(resp.message, resp.mess_type);

                if ('success' == resp.mess_type) {
                    dtComments.fnDraw();
                }
            }
        });
    }

    var blockComment = function(element){
        var $this = $(element);
        var url = $this.data('href');
        var comment = $this.data('comment');

        $.ajax({
            url: url,
            type: 'POST',
            data:  {comment:comment},
            dataType: 'json',
            success: function(resp){
                systemMessages(resp.message, resp.mess_type);

                if ('success' == resp.mess_type) {
                    dtComments.fnDraw();
                }
            }
        });
    }

    var publishComment = function(element){
        var $this = $(element);
        var url = $this.data('href');
        var comment = $this.data('comment');

        $.ajax({
            url: url,
            type: 'POST',
            data:  {comment:comment},
            dataType: 'json',
            success: function(resp){
                systemMessages(resp.message, resp.mess_type);

                if ('success' == resp.mess_type) {
                    dtComments.fnDraw();
                }
            }
        });
    }

    var unpublishComment = function(element){
        var $this = $(element);
        var url = $this.data('href');
        var comment = $this.data('comment');

        $.ajax({
            url: url,
            type: 'POST',
            data:  {comment:comment},
            dataType: 'json',
            success: function(resp){
                systemMessages(resp.message, resp.mess_type);

                if ('success' == resp.mess_type) {
                    dtComments.fnDraw();
                }
            }
        });
    }

    var openCommentResource = function(element){
        var $this = $(element);
        var url = '<?php echo __SITE_URL . 'comments/ajax_operations/link_to_resource';?>';
        var resource = $this.data('resource');

        $.ajax({
            url: url,
            type: 'POST',
            data:  {resource:resource},
            dataType: 'json',
            success: function(resp){
                if ('success' != resp.mess_type) {
                    systemMessages(resp.message, resp.mess_type);
                } else {
                    window.open(resp.url,'_blank');
                }
            }
        });
    }
</script>
