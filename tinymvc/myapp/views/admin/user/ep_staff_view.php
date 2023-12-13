<script type="text/javascript">
	var usersFilters; //obj for filters
	var dtUsersList; //obj of datatable
	var banDrawTable; //obj (address of datatable) for bann user
	$(document).ready(function(){

		//var usersFilters; //obj for filters
		dtUsersList = $('#dtUsersList').dataTable( {
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"bSortCellsTop": true,
			"sAjaxSource": "<?php echo __SITE_URL?>users/ajax_staff_dt",
			"sServerMethod": "POST",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 25, 50, 100, 0],
				[10, 25, 50, 100, 'All']
			],
			"aoColumnDefs": [
				{ "sClass": "w-50 tac vam", "aTargets": ["dt_idu"], "mData": "dt_idu", "bSortable": false }, //id
				{ "sClass": "w-100 tac vam", "aTargets": ["dt_fullname"], "mData": "dt_fullname" }, // names
				{ "sClass": "vam w-150", "aTargets": ["dt_email"], "mData": "dt_email" }, //email
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_gr_name"], "mData": "dt_gr_name" , "bSortable": false},//group
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_ip"], "mData": "dt_ip" , "bSortable": false},//IP
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_online"], "mData": "dt_online" , "bSortable": false},//online
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_ip"], "mData": "dt_ip" , "bSortable": false},//IP
				{ "sClass": "w-80 tac vam", "aTargets": ["dt_registered"], "mData": "dt_registered" }, // registered
				{ "sClass": "w-90 tac vam", "aTargets": ["dt_activity"], "mData": "dt_activity" }, // last active
				{ "sClass": "w-90 tac vam", "aTargets": ["dt_status"], "mData": "dt_status", "bSortable": false }, // status
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_actions"], "mData": "dt_actions", "bSortable": false },

			],
			"sorting" : [[4,'desc']],
			"fnServerData": function ( sSource, aoData, fnCallback ) {
				if(!usersFilters){
					usersFilters = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						callBack: function(filter){
							dtUsersList.fnDraw();
						},
						onSet: function(callerObj, filterObj){
							if(filterObj.name == 'group'){
								$('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li')
									.addClass('active').siblings().removeClass('active');

							}
						},
						onDelete: function(filterObj){
							if(filterObj.name == 'group'){
								$('a[data-value="' + filterObj.default + '"]').parent('li')
									.addClass('active').siblings().removeClass('active');
							}
						}
					});
				}

				aoData = aoData.concat(usersFilters.getDTFilter());

				$.ajax( {
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
					if(data.mess_type == 'error')
						systemMessages(data.message, 'message-' + data.mess_type);

					fnCallback(data, textStatus, jqXHR);
					},

				} );
			},
			"sPaginationType": "full_numbers",
			"fnDrawCallback": function( oSettings ) {}
		});
	});

	var explore_user = function(obj){
		var $this = $(obj);
		var user = $this.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL;?>login/explore_user',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
                if (resp.mess_type == 'success') {
					window.location.href = resp.redirect;
				} else{
					systemMessages(resp.message, 'message-' + resp.mess_type );
				}
			}
		});
	}

    var deleteEpStaff = function(obj){
        var $this = $(obj);
        var user = $this.data('user');
        $.ajax({
            url: '<?php echo __SITE_URL;?>users/ajax_operations/delete_ep_staff',
            type: 'POST',
            data:  {user:user},
            dataType: 'json',
            success: function(resp){
                if (resp.mess_type == 'success') {
                    dtUsersList.fnDraw(true);
                } else{
                    systemMessages(resp.message, 'message-' + resp.mess_type );
                }
            }
        });
    }

    var unblockUser = function(element) {
        var onRequestSuccess = function (data) {
            systemMessages(data.message, data.mess_type);
            if ('success' === data.mess_type) {
                dtUsersList.fnDraw(false);
            }
        };

        postRequest(__site_url + 'users/ajax_operations/unblock_ep_staff', { user: $(element).data('user')})
        .then(onRequestSuccess)
        .catch(onRequestError);
    }
</script>
<div class="row">
    <div class="col-xs-12">
		<?php tmvc::instance()->controller->view->display('admin/user/filter_bar_view')?>
		<div class="titlehdr h-30">
			<span>ExportPortal staff</span>
            <?php if (have_right('add_ep_staff')) {?>
                <a class="fancyboxValidateModalDT fancybox.ajax pull-right ep-icon ep-icon_plus-circle txt-green" data-table="dtUsersList" title="Add new member of EP staff" href="user/popup_forms/add_ep_staff/1/" data-title="Add new member of EP staff"></a>
            <?php }?>
		</div>
		<div class="wr-filter-list clearfix mt-10"></div>

        <ul class="menu-level3 mb-10 clearfix">
			<li  class="<?php echo (isset($group))?'':'active'?>">
				<a class="dt_filter" data-title="Group" data-name="group" data-value="" data-value-text="All" <?php echo (isset($group))?'':'data-current="true"'?>>All</a>
			</li>
			<?php foreach($groups as $gr){ ?>
			<li class="<?php echo equals($gr['idgroup'], $group, 'active')?>">
				<a class="dt_filter" data-name="group" data-value="<?php echo $gr['idgroup']?>" data-value-text="<?php echo $gr['gr_name']?>"  <?php echo equals($group, $gr['idgroup'], 'data-current="true"') ?>><?php echo $gr['gr_name']?>(<?php echo $gr['u_counter']?>)</a>
			</li>
			<?php } ?>
		</ul>
        <table class="data table-bordered table-striped w-100pr " id="dtUsersList">
            <thead>
                <tr>
                    <th class="dt_idu">#</th>
                    <th class="tac dt_fullname">Full name</th>
                    <th class="tac dt_online">Online</th>
                    <th class="tac dt_email">Email</th>
                    <th class="tac dt_gr_name">Group</th>
                    <th class="dt_ip">IP</th>
                    <th class="dt_registered">Registered</th>
                    <th class="dt_activity">Last active</th>
                    <th class="dt_status">Status</th>
                    <th class="dt_actions">Actions</th>
                </tr>
            </thead>
            <tbody class="tabMessage" id="pageall">
            </tbody>
        </table>
    </div>
</div>


