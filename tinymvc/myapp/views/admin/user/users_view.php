<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyAko8g1LP9autKH12-8d1VkUZn3UaIZB8E"></script>
<script type="text/javascript" src="<?php echo __FILES_URL;?>public/plug_admin/marker-clusterer-plus/js/markerclusterer.min.js"></script>
<?php views()->display('new/download_script'); ?>
<script>
	var usersFilters; //obj for filters
	var dtUsersList; //obj of datatable
	var banDrawTable; //obj (address of datatable) for bann user
	var $selectCity;
	var selectState;
	var $selectCcodePhone, $selectCcodeFax;
	var users_groups_counters = {};

	//region GMAP USERS
	var gmap_users = {},
		gmap_zoom = 3,
		gmap_zoom_max = 3,
		gmap_zoom_min = 18,
		gmap_center = null,
		gmap = null,
		gmap_bounds = [],
		gmap_markers = [],
		get_gmap_users = false,
		gmap_marker_cluster = null,
		gmap_show = false,
		gmap_initialized = false,
		infowindow = new google.maps.InfoWindow();

	var _gmap_toggle = function(obj){
		var $this = $(obj);
		var $gmap = $("#google-map-block");
		if ( $gmap.is( ":hidden" ) ) {
			$gmap.slideDown(500);
			gmap_show = true;
			_get_gmap_users(true);
			if(!gmap_initialized){
				gmap_users_init();
			}
			dtUsersList.fnDraw();
		} else {
			$gmap.slideUp(500);
			gmap_show = false;
		}
	}

	var show_google_map = function (obj) {
		var googleMapBlock = $("#google-map-block");
		if ( googleMapBlock.is( ":hidden" ) ) {
			googleMapBlock.slideDown(500);
		} else {
			googleMapBlock.slideUp(500);
		}
	}

	function gmap_users_init() {
		if(!gmap_show){
			return;
		}

		var googleMapElement = document.getElementById('map');
			gmap_center = new google.maps.LatLng(26.1996088, -21.3061758);
		var style = [
			{
				"featureType": "road",
				"elementType": "geometry",
				"stylers": [
					{
						"saturation": -100
					},
					{
						"lightness": 45
					}
				]
			},
			{
				"featureType": "poi",
				"elementType": "geometry",
				"stylers": [
					{
						"visibility": "off"
					}
				]
			},
			{
				"featureType": "landscape",
				"elementType": "geometry",
				"stylers": [
					{
						"color": "#fffffa"
					}
				]
			},
			{
				"featureType": "water",
				"stylers": [
					{
						"lightness": 50
					}
				]
			},
			{
				"featureType": "road",
				"elementType": "labels",
				"stylers": [
					{
						"visibility": "off"
					}
				]
			},
			{
				"featureType": "transit",
				"stylers": [
					{
						"visibility": "off"
					},
					{
						"lightness": 40
					}
				]
			},
			{
				"featureType": "administrative",
				"elementType": "geometry",
				"stylers": [
					{
						"lightness": 40
					}
				]
			},
			{
				"featureType": "administrative",
				"elementType": "labels.text.fill",
				"stylers": [
					{
						"color": "#444444"
					}
				]
			},
		];

		var mapOptions = {
			zoom: gmap_zoom,
			center: gmap_center,
			styles: style,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			mapTypeControl: false,
			streetViewControl: false,
		};

		gmap = new google.maps.Map(googleMapElement, mapOptions);
		gmap_initialized = true;

		google.maps.event.addListener(gmap, 'zoom_changed', function(event) {
			gmap_zoom = gmap.getZoom();
			if (gmap.getZoom() > gmap_zoom_min){
				gmap.setZoom(gmap_zoom_min);
				gmap_zoom = gmap_zoom_min;
			}

			if (gmap.getZoom() < gmap_zoom_max) {
				gmap.setZoom(gmap_zoom_max);
				gmap_zoom = gmap_zoom_max;
			}

			_get_gmap_users(true);
			boundaryChange();
		});

		google.maps.event.addListener(gmap, 'dragend', function(event) {
			_get_gmap_users(true);
			boundaryChange();
		});

		init_gmap_marker_cluster();
	}

	function gmap_set_zoom(zoom_level){
		if (gmap === null) {
			return true;
		}

		if (zoom_level > gmap_zoom_min){
			gmap.setZoom(gmap_zoom_min);
			gmap_zoom = gmap_zoom_min;
		} else if (zoom_level < gmap_zoom_max) {
			gmap.setZoom(gmap_zoom_max);
			gmap_zoom = gmap_zoom_max;
		} else{
			gmap.setZoom(zoom_level);
			gmap_zoom = zoom_level;
		}
	}

	function init_gmap_marker_cluster(){
		gmap_marker_cluster = new MarkerClusterer(gmap, gmap_markers, {imagePath: '<?php echo __FILES_URL;?>public/plug_admin/marker-clusterer-plus/images/m', minimumClusterSize: 1});
		google.maps.event.addListener(gmap_marker_cluster, "mouseover", function (c) {
			var m = c.getMarkers();
			var p = [];
			for (var i = 0; i < m.length; i++ ){
				p.push(m[i].city);
			}


			var formdata = [];
			formdata = formdata.concat({ "name": "id_city", "value":p.join(',')}, usersFilters.getDTFilter(),gmap_bounds,{"name": "get_gmap_users", "value": intval(get_gmap_users)});

			$.ajax({
				type: "POST",
				data: formdata,
				url: "users/ajax_operations/marker_users_details",
				dataType: "JSON",
				success: function(resp) {
					if (resp.mess_type == 'success'){
						if(Object.keys(resp.groups_users_count).length > 0){
							var groups = [];
							$.each(resp.groups_users_count, function(index, users_group){
								if(users_group.counter > 0){
									groups.push('<tr><td class="p-3">'+users_group.gr_name+'</td><td class="mnw-60 tac p-3">'+users_group.counter+'</td></tr>');
								}
							});

							if(groups.length > 0){
								var infotemplate = '<table class="table-bordered table-striped w-100pr">\
														<tbody>'+groups.join('')+'</tbody>\
													</table>';

								// Convert the coordinates to an MVCObject
								var info = new google.maps.MVCObject;
								info.set('position', c.center_);

								infowindow.close(); // closes previous open ifowindows
								infowindow.setContent(infotemplate);
								infowindow.open(gmap, info);
								google.maps.event.addListener(gmap, 'zoom_changed', function() {
									infowindow.close();
								});
							}

						}
					} else{
						systemMessages(resp.message, 'message-' + resp.mess_type);
					}
				}
			});
		});
	}

	function boundaryChange(){
		var bounds = gmap.getBounds();
		gmap_bounds = [];

		gmap_bounds.push({"name": "swlat", "value": bounds.getSouthWest().lat()});
		gmap_bounds.push({"name": "swlng", "value": bounds.getSouthWest().lng()});
		gmap_bounds.push({"name": "nelat", "value": bounds.getNorthEast().lat()});
		gmap_bounds.push({"name": "nelng", "value": bounds.getNorthEast().lng()});
		dtUsersList.fnDraw();
	};

	function set_gmap_markers(){
		gmap_markers = [];
		$.each(gmap_users, function(index, gmap_user){
			var latLng = new google.maps.LatLng(gmap_user.latitude, gmap_user.longitude);
			var marker = new google.maps.Marker({ position: latLng, city:gmap_user.id_city});
			gmap_markers.push(marker);
		});

		gmap_marker_cluster.clearMarkers();
		gmap_marker_cluster.addMarkers(gmap_markers);
	}

	function _get_gmap_users(value){
		if(gmap_show){
			get_gmap_users = value;
		} else{
			get_gmap_users = false;
		}
	}
	//endregion GMAP USERS

	$(function(){
        gmap_users_init();

        var iDisplayStart = existCookie('dtStart') ? parseInt(getCookie('dtStart')) : 0;
        var iDisplayLength = existCookie('dtLength') ? parseInt(getCookie('dtLength')) : 10;
        var sorting = existCookie('dtOrder') ? JSON.parse(getCookie('dtOrder')) : [[9,'desc']];

		dtUsersList = $('#dtUsersList').dataTable( {
			"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
			"bProcessing": true,
			"bServerSide": true,
			"bSortCellsTop": true,
			"sAjaxSource": "<?php echo __SITE_URL . 'users/ajax_admin_dt';?>",
			"sServerMethod": "POST",
            "iDisplayLength": iDisplayLength,
            "iDisplayStart": iDisplayStart,
			"aLengthMenu": [
				[10, 25, 50, 100],
				[10, 25, 50, 100]
			],
			"aoColumnDefs": [
				{ "sClass": "vam w-30 tac", "aTargets": ['dt_idu'], "mData": "dt_idu", "bSortable": false},
				{ "sClass": "w-50 tac vam", "aTargets": ["dt_photo"], "mData": "dt_photo", "bSortable": false }, //avatar
				{ "sClass": "w-100 tac vam", "aTargets": ["dt_fullname"], "mData": "dt_fullname" }, // names
				{ "sClass": "vam w-150", "aTargets": ["dt_email"], "mData": "dt_email" }, //email
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_country"], "mData": "dt_country" , "bSortable": false},//country
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_gr_name"], "mData": "dt_gr_name" , "bSortable": false},//group
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_reset_pass_date"], "mData": "dt_reset_pass_date"},//group
				{ "sClass": "w-80 tac vam", "aTargets": ["dt_resend_email_date"], "mData": "dt_resend_email_date" }, // registered
				{ "sClass": "w-80 tac vam", "aTargets": ["dt_registered"], "mData": "dt_registered" }, // registered
				{ "sClass": "w-90 tac vam", "aTargets": ["dt_activity"], "mData": "dt_activity" }, // last active
				{ "sClass": "w-90 tac vam", "aTargets": ["dt_status"], "mData": "dt_status", "bSortable": false }, // status
				{ "sClass": "w-70 tac vam", "aTargets": ["dt_records"], "mData": "dt_records", "bSortable": false },// notes and statistic
				{ "sClass": "w-30 tar vam", "aTargets": ["dt_actions"], "mData": "dt_actions", "bSortable": false },

			],
			"sorting" : sorting,
			"fnServerData": function ( sSource, aoData, fnCallback ) {
                var activeFilters = null;
                if (existCookie('dtPage') && getCookie('dtPage') === "users") {
                    activeFilters = JSON.parse(getCookie('dtFilters'));

                    removeCookie('dtPage');
                    removeCookie('dtFilters');
                    removeCookie('dtStart');
                    removeCookie('dtLength');
                    removeCookie('dtOrder');
                }

				if(!usersFilters){
					usersFilters = $('.dt_filter').dtFilters('.dt_filter',{
						'container': '.wr-filter-list',
						'debug'	: false,
						'autoApply': true,
						callBack: function(filter){
                            _get_gmap_users(true);
							dtUsersList.fnDraw();
						},
						onSet: function(callerObj, filterObj){
							if(filterObj.name == 'group'){
								$('.users-groups-counters a[data-value="' + filterObj.value + '"]').addClass('active').siblings().removeClass('active');
								$('.menu-level3 a[data-value="' + filterObj.value + '"]').parent('li').addClass('active').siblings().removeClass('active');
							}

							if (filterObj.name == 'reg_info') {
                                if (undefined !== usersFilters) {
                                    usersFilters.removeFilter('campaign');
                                    usersFilters.removeFilter('user');
                                }

								switch (filterObj.value) {
									case 'campaign':
										$('#campaign-list').show();
										$('#brand_ambassador-list').hide();
									break;
									case 'user':
										$('#campaign-list').hide();
										$('#brand_ambassador-list').show();
									break;
									default:
										$('#campaign-list').hide();
										$('#brand_ambassador-list').hide();
									break;
								}
							}

							if (filterObj.name == 'resend_date_from') {
								$('input[name="resend_date_to"]').datepicker("option", "minDate", $('input[name="resend_date_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'resend_date_to') {
								$('input[name="resend_date_from"]').datepicker("option", "maxDate", $('input[name="resend_date_to"]').datepicker("getDate"));
							}

							if (filterObj.name == 'document_upload_date_from') {
								$('input[name="document_upload_date_to"]').datepicker("option", "minDate", $('input[name="document_upload_date_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'document_upload_date_to') {
								$('input[name="document_upload_date_from"]').datepicker("option", "maxDate", $('input[name="document_upload_date_to"]').datepicker("getDate"));
							}

							if (filterObj.name == 'reg_date_from') {
								$('input[name="reg_date_to"]').datepicker("option", "minDate", $('input[name="reg_date_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'reg_date_to') {
								$('input[name="reg_date_from"]').datepicker("option", "maxDate", $('input[name="reg_date_to"]').datepicker("getDate"));
							}

							if (filterObj.name == 'activity_date_from') {
								$('input[name="activity_date_to"]').datepicker("option", "minDate", $('input[name="activity_date_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'activity_date_to') {
								$('input[name="activity_date_from"]').datepicker("option", "maxDate", $('input[name="activity_date_to"]').datepicker("getDate"));
							}

                            if (filterObj.name == 'restricted_from') {
								$('input[name="restricted_to"]').datepicker("option", "minDate", $('input[name="restricted_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'restricted_to') {
								$('input[name="restricted_from"]').datepicker("option", "maxDate", $('input[name="restricted_to"]').datepicker("getDate"));
							}

                            if (filterObj.name == 'blocked_from') {
								$('input[name="blocked_to"]').datepicker("option", "minDate", $('input[name="blocked_from"]').datepicker("getDate"));
							}

							if (filterObj.name == 'blocked_to') {
								$('input[name="blocked_from"]').datepicker("option", "maxDate", $('input[name="blocked_to"]').datepicker("getDate"));
							}
						},
						onDelete: function(filterObj){
                            switch (filterObj.name) {
                                case 'group':
                                    $('.users-groups-counters a[data-value="' + filterObj.default + '"]').addClass('active').siblings().removeClass('active');
								    $('.menu-level3 a[data-value="' + filterObj.default + '"]').parent('li').addClass('active').siblings().removeClass('active');

                                    break;
                                case 'continent':
                                    get_countries(0);

                                    break;
                                case 'industry':
                                    get_industries();

                                    break;
                                case 'reg_info':
                                    usersFilters.removeFilter('campaign');
                                    usersFilters.removeFilter('user');
                                    $('#campaign-list').hide();
                                    $('#brand_ambassador-list').hide();

                                    break;
                                case 'document_upload_date_from':
                                    $('input[name="document_upload_date_to"]').datepicker( "option" , {minDate: null});

                                    break;
                                case 'document_upload_date_to':
                                    $('input[name="document_upload_date_from"]').datepicker( "option" , {maxDate: null});

                                    break;
                                case 'resend_date_from':
                                    $('input[name="resend_date_to"]').datepicker( "option" , {minDate: null});

                                    break;
                                case 'resend_date_to':
                                    $('input[name="resend_date_from"]').datepicker( "option" , {maxDate: null});

                                    break;
                                case 'reg_date_from':
                                    $('input[name="reg_date_to"]').datepicker( "option" , {minDate: null});

                                    break;
                                case 'reg_date_to':
                                    $('input[name="reg_date_from"]').datepicker( "option" , {maxDate: null});

                                    break;
                                case 'activity_date_from':
                                    $('input[name="activity_date_to"]').datepicker( "option" , {minDate: null});

                                    break;
                                case 'activity_date_to':
                                    $('input[name="activity_date_from"]').datepicker( "option" , {maxDate: null});

                                    break;
                                case 'restricted_from':
                                    $('input[name="restricted_to"]').datepicker( "option" , {minDate: null});

                                    break;
                                case 'restricted_to':
                                    $('input[name="restricted_from"]').datepicker( "option" , {maxDate: null});

                                    break;
                                case 'blocked_from':
                                    $('input[name="blocked_to"]').datepicker( "option" , {minDate: null});

                                    break;
                                case 'blocked_to':
                                    $('input[name="blocked_from"]').datepicker( "option" , {maxDate: null});

                                    break;
                            }
						},
						onReset: function(){
							$('.filter-admin-panel .hasDatepicker').datepicker( "option" , {
								minDate: null,
								maxDate: null
							});

							gmap_set_zoom(gmap_zoom_max);
						}
					}, activeFilters);
                }

                if (null === activeFilters) {
                    activeFilters = usersFilters.getDTFilter();
                }

				aoData = aoData.concat(activeFilters, gmap_bounds,{"name": "get_gmap_users", "value": intval(get_gmap_users)});

				$.ajax( {
					"dataType": 'json',
					"type": "POST",
					"url": sSource,
					"data": aoData,
					"success": function (data, textStatus, jqXHR) {
						if(data.mess_type == 'success'){
							fnCallback(data, textStatus, jqXHR);

							if(get_gmap_users){
								gmap_users = data.gmap_data;

								set_gmap_markers();
							}

							set_groups_counters(data.groups_users_count);
						} else{
							systemMessages(data.message, 'message-' + data.mess_type);
						}

					}
				} );
			},
			"sPaginationType": "full_numbers",
			"fnDrawCallback": function( oSettings ) {
				infowindow.close();
			}
		});

		$('body').on('change', "select#states", function(){
			selectState = this.value;
			$selectCity.empty().trigger("change").prop("disabled", false);

			if(selectState != '' || selectState != 0){
				var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_city'});
			} else{
				var select_text = translate_js({plug:'general_i18n', text:'form_placeholder_select2_state_first'});
				$selectCity.prop("disabled", true);
			}
			$selectCity.siblings('.select2').find('.select2-selection__placeholder').text(select_text);
		});

		$('body').on('change', "#country", function(){
			selectCountry($(this), 'select#states');
			selectState = 0;
			$selectCity.empty().trigger("change").prop("disabled", true);
		});

		// add 3
		$("#check_all").on("click",function(){
			if($("#check_all").prop("checked")){
				$(".ch_input").prop("checked",true);
			} else{
				$(".ch_input").prop("checked",false);
			}
		});
		//end add 3

		$("a#del").click(function(){
			if(confirm('You are sure?')) $(this).closest("form").submit();
			return false;
		});

		$("a#save").click(function(){
			$(this).closest("form").submit();
			return false;
		});

		$("a#edit").click(function(){
			var tr = $(this).closest("tr");
			if(!$(this).hasClass("cancel")){
				tr.find("span").hide(0);
				tr.find("input,select").show(200);
				tr.find("a#save").css("display","block");
				$(this).addClass("cancel");
				$(this).html("Cancel");
			}else{
				tr.find("span").show(200);
				tr.find("input,select,a#save").hide(0);
				$(this).removeClass("cancel");
				$(this).html("Edit");
			}
			return false;
		});

		$('.check-all-users').on('click', function() {
			var value = $(this).is(":checked") ? 1 : 0;
			if (value) {
				$('.check-user').prop("checked", true);
				$('.btns-actions-all').show();
			}
			else {
				$('.check-user').prop("checked", false);
				$('.btns-actions-all').hide();
			}
		});

		$('body').on('click', '.check-user', function() {
			if ($(this).prop("checked")) {
				$('.btns-actions-all').show();
			} else {
				var hideBlock = true;
				$('.check-user').each(function() {
					if ($(this).prop("checked")) {
						hideBlock = false;
						return false;
					}
				})
				if (hideBlock)
					$('.btns-actions-all').hide();
			}
		});

		banDrawTable = dtUsersList; //save new addres of the datatable

		$('body').on('click', 'a[rel=user_details]', function() {
			var $aTd = $(this);
			var nTr = $aTd.parents('tr')[0];

			if (dtUsersList.fnIsOpen(nTr))
				dtUsersList.fnClose(nTr);
			else
				dtUsersList.fnOpen(nTr, fnFormatDetails(nTr), 'details');

			$aTd.toggleClass('ep-icon_plus ep-icon_minus');
		});

		function fnFormatDetails(nTr){
			var aData = dtUsersList.fnGetData(nTr);
			var sOut = '<div class="dt-details"><table class="dt-details__table">';
				sOut += aData['dt_detail'];
				sOut += '</table> </div>';
			return sOut;
		}

		idStartItemNew = <?php echo $last_users_id;?>;
		startCheckAdminNewItems('user/ajax_user_operation/check_new', idStartItemNew);

		$('body').on('mouseover mouseenter', '.completion-tooltip', function(){
			if(!$(this).hasClass('tooltipstered')){
				$(this).tooltipster();
			}
			$(this).tooltipster('show');
        });

        $(globalThis).on('company:edit-name', function (e) {
            dtUsersList.fnDraw(false);
        });
	});

	var activateUser = function($this){
		var user = $this.data('user');

		$.ajax({
			type: "POST",
			data: { user: user},
			url: "<?php echo __SITE_URL;?>users/ajax_operations/activate_user",
			dataType: "JSON",
			success: function(resp) {
				if (resp.mess_type == 'success'){
                    dtUsersList.fnDraw(false);
				}

				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}

	var delete_user_image = function(btn){
		var $this = $(btn);
		var image = $this.data('image');
		var user = $this.data('user');

		$.ajax({
			type: "POST",
			data: { user:user, image: image},
			url: "users/ajax_admin_delete_user_photo",
			dataType: "JSON",
			success: function(resp) {
				if (resp.mess_type == 'success')
					dtUsersList.fnDraw(false);

				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}

	var resend_activation_link = function(opener){
		var $this = $(opener);
		var user = $this.data('user');
		$.ajax({
			type: "POST",
			url: "<?php echo __SITE_URL;?>users/ajax_operations/send_activation_link",
			dataType: "JSON",
			data: {user:user},
			success: function(resp) {
				if (resp.mess_type == 'success'){
					dtUsersList.fnDraw(false);
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}

	var delete_user = function(obj) {
		var button = $(obj);
		var user = button.data('user');
        var row = button.closest('tr');
        var loader = showLoader($("body"));
        var loaderStyle = loader.attr("style") +
            "width:" + row.innerWidth() +
            "px; height:" + (row.innerHeight() - 3) +
            "px; top:" + (row.offset().top + 1) +
            "px; left:" + row.offset().left + "px;"
        ;
        loader.attr("style", loaderStyle);
        loader.find("i").attr("style", "width: 40px; background-size: 100%;");

		$.ajax({
			url: '<?php echo __SITE_URL;?>users/ajax_operations/delete_user',
			type: 'POST',
			data:  { user:user },
			dataType: 'json',
			success: function (resp) {
				systemMessages(resp.message, 'message-' + resp.mess_type);
                if (resp.mess_type == 'success') {
					dtUsersList.fnDraw(false);
				}
			},
            complete: function () {
                hideLoader($("body"));
            },
		});
	}

	//region Block users
	var unblockUser = function(element){
		var $this = $(element);
		var user = $this.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL;?>users/ajax_operations/unblock_user',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, resp.mess_type );
				if(resp.mess_type == 'success'){
					dtUsersList.fnDraw(false);
				}
			}
		});
	}
	//endregion Block users

    //region demo user
	var fakeUser = function(element){
		var $this = $(element);
		var user = $this.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL;?>users/ajax_operations/fake_user',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, resp.mess_type );
				if(resp.mess_type == 'success'){
					dtUsersList.fnDraw(false);
				}
			}
		});
	}
	//endregion demo user

    //region demo users
    var fakeUsers = function(){
		var checkedUsers = new Array();

		$(".check-user:checked").each(function() {
			checkedUsers.push($(this).data('user'));
		});

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL ?>users/fake_users',
			dataType: "JSON",
			data: {checkedUsers: checkedUsers},
			success: function(data) {
				systemMessages(data.message, 'message-' + data.mess_type);
				if (data.mess_type == 'success') {
					dtUsersList.fnDraw();
					$('.check-all-users').prop("checked", false);
				}
			}
		});
	}
	//endregion demo users

    //region Model users
	var modelUser = function(element){
		var $this = $(element);
		var user = $this.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL;?>users/ajax_operations/model_user',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, resp.mess_type );
				if(resp.mess_type == 'success'){
					dtUsersList.fnDraw(false);
				}
			}
		});
	}
	//endregion Model users

	//region Restrict users
	var unRestrictUser = function(element){
		var $this = $(element);
		var user = $this.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL;?>users/ajax_operations/unrestrict_user',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, resp.mess_type );
                if(resp.mess_type == 'success'){
					dtUsersList.fnDraw(false);
				}
			}
		});
	}
	//endregion Restrict users

	var confirm_user = function (obj) {
		var button = $(obj);
		var user = button.data('user') || null;
		var url = __group_site_url + 'users/ajax_operations/confirm_user';
		var onRequestSuccess = function (data) {
			systemMessages(data.message, data.mess_type);
			if(data.mess_type == 'success'){
				dtUsersList.fnDraw(false);
			}
		};

		if (null === user) {
			return;
		}

		return $.post(url, { user: user }, null, 'json')
			.done(onRequestSuccess)
			.fail(onRequestError);
	}

    //region sending matchmaking email
	var sendingMatchmakingEmail = function(btn){
		var element = $(btn);
		var user = element.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL . 'users/ajax_operations/sending_matchmaking_emails';?>',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, resp.mess_type);

				if ('success' == resp.mess_type) {
					dtUsersList.fnDraw(false);
				}
			}
		});
	}

    //region sending matchmaking email
	var sendMatchmakingEmail = function(btn){
		var element = $(btn);
		var user = element.data('user');
		$.ajax({
			url: '<?php echo __SITE_URL . 'users/ajax_operations/send_matchmaking_email';?>',
			type: 'POST',
			data:  {user:user},
			dataType: 'json',
			success: function(resp){
				systemMessages(resp.message, resp.mess_type);
			}
		});
	}

	var explore_user = function(obj){
        setCookie("dtPage", "users");
        setCookie('dtFilters', usersFilters.getDTFilter(), 1);
        setCookie('dtStart', dtUsersList.api().page.info().start, 1);
        setCookie('dtLength', dtUsersList.api().page.info().length, 1);
        setCookie('dtOrder', dtUsersList.api().order(), 1);

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

	function set_groups_counters(users_groups_counters){
		$('.users-groups-counters span.users_counter').text('0');
		$('.menu-level3 li > a span.users_counter').text('0');
		var total_count = 0;
		$.each(users_groups_counters, function(id_group, group_obj){
			$('.users-groups-counters a[data-group="'+id_group+'"] span.users_counter').text(group_obj.counter);
			$('.menu-level3 li > a[data-group="'+id_group+'"] span.users_counter').text(group_obj.counter);
			total_count += intval(group_obj.counter);
		});
		$('.users-groups-counters a[data-group="all"] span.users_counter').text(total_count);
		$('.menu-level3 li > a[data-group="all"] span.users_counter').text(total_count);
    }
</script>
<div class="row">
	<div class="col-xs-12">
		<?php app()->view->display('admin/user/filter_bar_view')?>
		<div class="titlehdr h-30">
			<span>Users</span>
			<a class="ep-icon ep-icon_envelope-send pull-right ml-10 txt-green fancyboxValidateModalDT fancybox.ajax" href="<?php echo __SITE_URL . 'users/popup_forms/send_multi_email';?>" data-title="Email users" title="Email users"></a>
			<a class="ep-icon ep-icon_connection pull-right ml-10 fancybox fancybox.ajax" href="<?php echo __SITE_URL;?>categories/popup_forms/industries_of_interest" data-title="Industries of interest" title="Industries of interest"></a>
            <?php if(have_right('export_users')){?>
                <a class="ep-icon ep-icon_file-text pull-right ml-10 fancyboxValidateModal fancybox.ajax" href="<?php echo __SITE_URL . 'users/popup_forms/export';?>" data-title="Export users" title="Export users"></a>
                <iframe src="" id="download_exported_file" class="display-n"></iframe>
            <?php }?>
            <a class="ep-icon ep-icon_globe-stroke pull-right ml-10 txt-gray call-function" data-callback="_gmap_toggle" title="Google Map"></a>
            <a class="ep-icon ep-icon_minus-circle pull-right ml-10 txt-red confirm-dialog" data-message="Are you sure want to make fake these users?" data-callback="fakeUsers" title="Make users as Demo"></a>
        </div>
        <div class="row" style="display: none" id="google-map-block">
			<div class="col-sm-12 col-md-10">
            	<div style="width:100%;height:800px;" id="map"></div>
			</div>
			<div class="col-sm-12 col-md-2">
				<div class="list-group users-groups-counters">
					<a href="#" class="list-group-item dt_filter active" data-group="all" data-title="Group" data-name="group" data-value="" data-value-text="All">All <span class="badge users_counter">0</span></a>
					<?php foreach($groups as $gr){ ?>
						<a href="#" class="list-group-item dt_filter" data-group="<?php echo $gr['idgroup']?>" data-name="group" data-value="<?php echo $gr['idgroup']?>" data-value-text="<?php echo $gr['gr_name']?>"><?php echo $gr['gr_name']?> <span class="badge users_counter"><?php echo $gr['u_counter']?></span></a>
                    <?php } ?>
				</div>
				<div class="wr-filter-list clearfix mt-10"></div>
			</div>
        </div>
		<div class="wr-filter-list clearfix mt-10"></div>

		<ul class="menu-level3 mb-10 clearfix">
			<li class="active">
				<a class="dt_filter" data-group="all" data-title="Group" data-name="group" data-value="" data-value-text="All">All (<span class="users_counter">0</span>)</a>
			</li>
			<?php foreach($groups as $gr){ ?>
				<li class="<?php echo equals($gr['idgroup'], $group, 'active')?>">
					<a class="dt_filter" <?php echo addQaUniqueIdentifier("admin-users__filter-by-group-" . $gr['idgroup'])?> data-group="<?php echo $gr['idgroup']?>" data-name="group" data-value="<?php echo $gr['idgroup']?>" data-value-text="<?php echo $gr['gr_name']?>"><?php echo $gr['gr_name']?> (<span class="users_counter"><?php echo $gr['u_counter']?></span>)</a>
				</li>
			<?php } ?>
		</ul>

		<table class="data table-bordered table-striped w-100pr" id="dtUsersList">
			<thead>
				<tr>
					<th class="dt_idu tac"><input type="checkbox" class="check-all-users mt-1"></th>
					<th class="tac dt_photo">Avatar</th>
					<th class="tac dt_fullname">Full name</th>
					<th class="tac dt_email">Email</th>
					<th class="tac dt_gr_name">Group</th>
					<th class="dt_country"><span class="ep-icon ep-icon_globe fs-22 m-0"></span></th>
					<th class="dt_reset_pass_date">Last reset password</th>
					<th class="dt_resend_email_date">Last resend date</th>
					<th class="dt_registered">Registered</th>
					<th class="dt_activity">Last active</th>
					<th class="dt_status">Status</th>
					<th class="dt_records">Records</th>
					<th class="dt_actions">Actions</th>
				</tr>
			</thead>
			<tbody class="tabMessage" id="pageall">
			</tbody>
		</table>
	</div>
</div>
