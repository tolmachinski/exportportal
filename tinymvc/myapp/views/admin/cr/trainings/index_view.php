<script>
	var dtTrainingsList;
	var $checked_trainings = [];

	var delete_training = function(opener){
		var $this = $(opener);
		$checked_trainings = [];
		$checked_trainings.push($this.data('training'));

		delete_trainings_callback();
	}

	var delete_trainings = function(){
		$checked_trainings = [];
		$(".check-training:checked").each(function() {
			$checked_trainings.push($(this).data('id-training'));
		});

		delete_trainings_callback();
	}

	function delete_trainings_callback(){
		if ($checked_trainings.length == 0) {
			systemMessages('Error: There are no training(s) to be deleted.', 'message-error');
			return false;
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>cr_training/ajax_trainings_operation/delete_trainings',
			dataType: "JSON",
			data: {training: $checked_trainings.join(',')},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type != 'error') {
					dtTrainingsList.fnDraw();
				}

			}
		});
	}

    $(document).ready(function() {
		var myFilters;
		dtTrainingsList = $('#dtTrainingsList').dataTable({
            "sDom": '<"top"lp>rt<"bottom"ip><"clear">',
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo __SITE_URL;?>cr_training/ajax_administration_dt/trainings",
            "sServerMethod": "POST",
            "iDisplayLength": 10,
            "aoColumnDefs": [
                {"sClass": "w-50 tac vam", "aTargets": ["dt_id"], "mData": "dt_id"},
                {"sClass": "vam", "aTargets": ["dt_title"], "mData": "dt_title" },
                {"sClass": "w-120 tac vam", "aTargets": ["dt_start_date"], "mData": "dt_start_date" },
                {"sClass": "w-120 tac vam", "aTargets": ["dt_finish_date"], "mData": "dt_finish_date" },
                {"sClass": "w-120 tac vam", "aTargets": ["dt_date"], "mData": "dt_date" },
                {"sClass": "w-40 tac vam", "aTargets": ["dt_type"], "mData": "dt_type" },
                {"sClass": "w-70 tac vam", "aTargets": ["dt_actions"], "mData": "dt_actions" , 'bSortable': false}
            ],
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                if(!myFilters){
					myFilters = $('.dt_filter').dtFilters('.dt_filter',{
						container: ".wr-filter-list",
						callBack: function(){ dtTrainingsList.fnDraw(); },
					});
			    }

			    aoData = aoData.concat(myFilters.getDTFilter());

				$.ajax( {
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
						if(data.mess_type == 'error')
							systemMessages(data.message, 'message-' + data.mess_type);

						fnCallback(data, textStatus, jqXHR);

					}
				});
			},
			"sorting" : [[0,'desc']],
            "sPaginationType": "full_numbers",
            "fnDrawCallback": function(oSettings) { $(".check-all-trainings").attr("checked", false); }
		});

        $('div.dataTables_filter input').addClass('search-training');

		$('.check-all-trainings').on('click', function(e){
			if($(this).prop("checked")){
				$('.btns-actions-all').show();
				$('.check-training').prop("checked", true);
			}else {
				$('.check-training').prop("checked", false);
				$('.btns-actions-all').hide();
			}
		});

		$('body').on('click', '.check-training', function(){
			if($(this).prop("checked")){
				$('.btns-actions-all').show();
			}else {
				var hideBlock = true;
				$('.check-training').each(function(){
					if($(this).prop("checked")){
						hideBlock = false;
						return false;
					}
				});
				if(hideBlock)
					$('.btns-actions-all').hide();
			}
		});

        $('body').on('click', 'a[rel=training_details]', function() {
            var $aTd = $(this);
            var nTr = $aTd.parents('tr')[0];

            if (dtTrainingsList.fnIsOpen(nTr))
                dtTrainingsList.fnClose(nTr);
            else
                dtTrainingsList.fnOpen(nTr, fnFormatDetails(nTr) , 'details');
            $aTd.toggleClass('ep-icon_plus ep-icon_minus');
        });

        function fnFormatDetails(nTr){
            var aData = dtTrainingsList.fnGetData(nTr);
            var sOut = '<div class="dt-details"><table class="dt-details__table">';
            sOut += aData['dt_description'];
            sOut += '</table> </div>';
            return sOut;
        }
    });

    function on_users_selected(data) {
        $.ajax({
        type: 'POST',
            url: '<?php echo __SITE_URL?>cr_users/ajax_operations/assign_users',
            data: {
            	id_list: data.dataIds,
                id_item: data.idItem,
                type: data.type
            },
            dataType: 'json',
            success: function (data) {
                if (data.mess_type === 'success') {
                    dtTrainingsList.fnDraw(false);
                    closeFancyBox();
                }

                systemMessages(data.message, 'message-' + data.mess_type);
            }
        });
    }
</script>

<div class="row">
    <div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Trainings list</span>
            <a class="ep-icon ep-icon_plus-circle fancyboxValidateModalDT fancybox.ajax pull-right" title="Add new Training" data-table="dtTrainingsList" title="Add new training" href="cr_training/popup_forms/add_training_admin" data-title="Add new training"></a>
			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_remove txt-red pull-right confirm-dialog mr-5" data-message="Are you sure want delete selected trainings?" data-callback="delete_trainings" title="Delete trainings"></a>
			</div>
		</div>
		<?php tmvc::instance()->controller->view->display('admin/cr/trainings/trainings_filter_bar'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

        <table id="dtTrainingsList" class="data table-bordered table-striped w-100pr" >
            <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_title">Title</th>
                    <th class="dt_start_date">Date From</th>
                    <th class="dt_finish_date">Date To</th>
                    <th class="dt_date">Date Registered</th>
                    <th class="dt_type">Type</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
