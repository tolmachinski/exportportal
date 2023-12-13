<div class="container-fluid content-dashboard display-i">
	<div class="row">
		<div class="col-xs-12 initial-b">
			<div class="titlehdr h-30">
				<span><?php echo $title; ?></span>
			</div>

			<?php tmvc::instance()->controller->view->display('admin/moderation/filter_panel_view'); ?>
			<div class="wr-filter-list mt-10 clearfix"></div>
            <ul class="menu-level3 mb-10 clearfix resource-counters-wrapper">
                <li class="active">
                    <a class="dt_filter" data-title="Accessibility" data-name="blocked" data-value="" data-value-text="All">
                        All records (<span class="log-counter counter-all"><?php echo isset($accessibility['all']) ? $accessibility['all'] : 0; ?></span>)
                    </a>
                </li>
                <li>
					<a class="dt_filter" data-title="Accessibility" data-name="blocked" data-value="1" data-value-text="Blocked">
                        Blocked records (<span class="log-counter counter-blocked"><?php echo isset($accessibility['blocked']) ? $accessibility['blocked'] : 0; ?></span>)
                    </a>
				</li>
                <li>
					<a class="dt_filter" data-title="Accessibility" data-name="blocked" data-value="2" data-value-text="Locked">
                        Locked records (<span class="log-counter counter-locked"><?php echo isset($accessibility['locked']) ? $accessibility['locked'] : 0; ?></span>)
                    </a>
				</li>
                <li>
					<a class="dt_filter" data-title="Accessibility" data-name="blocked" data-value="0" data-value-text="Accessible">
                        Accessible records (<span class="log-counter counter-accessible"><?php echo isset($accessibility['accessible']) ? $accessibility['accessible'] : 0; ?></span>)
                    </a>
				</li>
            </ul>

			<table id="dtModeratedEntities" class="data table-bordered table-striped w-100pr dataTable no-footer">
                <thead>
                <tr>
                    <th class="dt_id">#</th>
                    <th class="dt_name">Title</th>
                    <th class="dt_acctivation_account_date">Account activated on</th>
                    <th class="dt_created_at">Created</th>
                    <th class="dt_updated_at">Updated</th>
                    <th class="dt_accessibility">Accessibility</th>
                    <th class="dt_actions">Actions</th>
                </tr>
                </thead>
                <tbody class="tabMessage" id="moderated-targets"></tbody>
            </table>
		</div>
	</div>
</div>
<script type="application/javascript">
	var dtModeratedEntities;
    var myFilters;

	$(document).ready(function(){
        var onSetFilters = function(caller, filter) {
            switch (filter.name) {
                case 'created_from':
                    $("#filter-created-to").datepicker("option", "minDate", $("#filter-created-from").datepicker("getDate"));

                    break;
                case 'created_to':
                    $("#filter-created-from").datepicker("option","maxDate", $("#filter-created-to").datepicker("getDate"));

                    break;
                case 'updated_from':
                    $("#filter-updated-to").datepicker("option", "minDate", $("#filter-updated-from").datepicker("getDate"));

                    break;
                case 'updated_to':
                    $("#filter-updated-from").datepicker("option","maxDate", $("#filter-updated-to").datepicker("getDate"));

                    break;
                case 'activated_from':
                    $('input[name="activated_to"]').datepicker("option", "minDate", $('input[name="activated_from"]').datepicker("getDate"));

                    break;
                case 'activated_to':
                    $('input[name="activated_from"]').datepicker("option","maxDate", $('input[name="activated_to"]').datepicker("getDate"));

                    break;
                case 'blocked':
                    $('.menu-level3').find('a[data-value="'+filter.value+'"]').parent('li').addClass('active').siblings('li').removeClass('active');

                    break;
            }
        };

        var onReset = function(caller, filter){
            $('.filter-admin-panel .hasDatepicker').datepicker( "option" , {
                minDate: null,
                maxDate: null
            });
        }

        var onDelete = function(caller, filter){
            switch (filter.name) {
                case 'created_from':
                    $('input[name="created_to"]').datepicker( "option" , {minDate: null});

                    break;
                case 'created_to':
                    $('input[name="created_from"]').datepicker( "option" , {maxDate: null});

                    break;
                case 'updated_from':
                    $('input[name="updated_to"]').datepicker( "option" , {minDate: null});

                    break;
                case 'updated_to':
                    $('input[name="updated_from"]').datepicker( "option" , {maxDate: null});

                    break;
            }
        }

        var moderateResource = function (caller) {
            var button = $(caller);
            var url = button.data('url') || null;
            var type = button.data('type') || null;
            var resource_id = button.data('resource');

            var onRequestSuccess = function(resposne) {
                systemMessages(resposne.message, resposne.mess_type);
                if(type !== null && type == 'items'){
                    $.post(__site_url + 'items/ajax_item_operation/prepare_item', { id: resource_id }, null, 'json').done(function(resp) {
                        systemMessages(resp.message, resp.mess_type);
                    });
                }
                if(resposne.mess_type === 'success') {
                    dtModeratedEntities.draw();
                }
            }

            if(null !== url) {
                $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };
        var unblockResource = function (caller) {
            var button = $(caller);
            var url = button.data('url') || null;
            var onRequestSuccess = function(resposne) {
                systemMessages(resposne.message, resposne.mess_type);
                if(resposne.mess_type === 'success') {
                    dtModeratedEntities.draw(false);
                }
            }

            if(null !== url) {
                $.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        };

        var fetchServerData = function(source, data, onLoad) {
            var activeFilters = null;

            if (existCookie('dtPage') && getCookie('dtPage') === "moderation") {
                activeFilters = JSON.parse(getCookie('dtFilters'));

                removeCookie('dtPage');
                removeCookie('dtFilters');
                removeCookie('dtStart');
                removeCookie('dtLength');
                removeCookie('dtOrder');
            }

            var self = this;
            var onRequestSuccess = function(responseBody, textStatus, handle) {
                if (responseBody.mess_type === 'error') {
                    systemMessages(responseBody.message, responseBody.mess_type);
                }

                if(responseBody.aoAccessibility) {
                    for (var key in responseBody.aoAccessibility) {
                        if (responseBody.aoAccessibility.hasOwnProperty(key)) {
                            var value = responseBody.aoAccessibility[key];
                            var label = $('.resource-counters-wrapper .counter-' + key.replace(/\_/, '-'));
                            if(label.length) {
                                label.text(value)
                            }
                        }
                    }
                }

                onLoad(responseBody, textStatus, handle);
            };
            if(!myFilters) {
                myFilters = $('.dt_filter').dtFilters('.dt_filter', {
                    container: '.wr-filter-list',
                    onSet: onSetFilters,
                    onReset: onReset,
                    onDelete: onDelete,
                    callBack: function() {
                        self.fnDraw();
                    }
                }, activeFilters);
            }

            if (null === activeFilters) {
                activeFilters = myFilters.getDTFilter();
            }

            $.post(source, data.concat(activeFilters), null, 'json').done(onRequestSuccess).fail(onRequestError);
        };
        var onResourceBlock = function(response) {
            dtModeratedEntities.draw(false);
        };
        var onSendAbuseAlert = function(response) {
        };

        var iDisplayStart = 0;
        var iDisplayLength = 10;
        var sorting = [[3, 'desc']];

        if (existCookie("dtPage") && getCookie("dtPage") === "moderation") {
            iDisplayStart = existCookie('dtStart') ? parseInt(getCookie('dtStart')) : iDisplayStart;
            iDisplayLength = existCookie('dtLength') ? parseInt(getCookie('dtLength')) : iDisplayLength;
            sorting = existCookie('dtOrder') ? JSON.parse(getCookie('dtOrder')) : sorting;
        }

		dtModeratedEntities = $('#dtModeratedEntities').DataTable({
			sDom: '<"top"lp>rt<"bottom"ip><"clear">',
			bProcessing: true,
			bServerSide: true,
            sAjaxSource: __site_url + "<?php echo "{$url}/{$type}"; ?>",
            iDisplayLength: iDisplayLength,
            iDisplayStart: iDisplayStart,
			aoColumnDefs: [
				{ sClass: "vam w-50 tac",  aTargets: ['dt_id'],                         mData: "id",                        bSortable: false },
				{ sClass: "vam",           aTargets: ['dt_name'],                       mData: "name",                      bSortable: false },
				{ sClass: "w-140 tac vam", aTargets: ['dt_acctivation_account_date'],   mData: "acctivation_account_date",  bSortable: true },
				{ sClass: "w-120 tac vam", aTargets: ['dt_created_at'],                 mData: "created_at",                bSortable: true },
				{ sClass: "w-120 tac vam", aTargets: ['dt_updated_at'],                 mData: "updated_at",                bSortable: true },
				{ sClass: "w-120 tac vam", aTargets: ['dt_accessibility'],              mData: "accessibility",             bSortable: false },
				{ sClass: "w-80 tac vam",  aTargets: ['dt_actions'],                    mData: "actions",                   bSortable: false },
			],
			fnServerData: fetchServerData,
			sPaginationType: "full_numbers",
            sorting : sorting,
        });

        window.unblockResource = unblockResource;
        window.moderateResource = moderateResource;
        window.onResourceBlock = onResourceBlock;
        window.onSendAbuseAlert = onSendAbuseAlert;
    });

    var explore_user = function(obj){
        setCookie("dtPage", "moderation");
        setCookie('dtFilters', myFilters.getDTFilter(), 1);
        setCookie('dtStart', dtModeratedEntities.page.info().start, 1);
        setCookie('dtLength', dtModeratedEntities.page.info().length, 1);
        setCookie('dtOrder', dtModeratedEntities.order(), 1);
        setCookie('exitExploreRedirectUrl', '<?php echo __SITE_URL . 'moderation/administration/' . $type;?>');

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
	};
</script>
