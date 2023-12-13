<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyAko8g1LP9autKH12-8d1VkUZn3UaIZB8E"></script>

<script>
	function fnFormatDetails(nTr) {
		var aData = dtDirectoriesList.fnGetData(nTr);

		var template = '<div class="dt-details">\
							<table class="dt-details__table">\
								<tr>\
									<td class="w-100">' + aData['logo'] + '</td>\
									<td>' + aData['description'] + '</td>\
								</tr>\
								<tr>\
									<td class="w-100">Details</td>\
									<td>\
										<p class="mb-10"><strong>Rating: </strong>' + aData['add_rating'] + '</p>\
										<p class="mb-10"><strong>Email: </strong>' + aData['email'] + '</p>\
										<p class="mb-10"><strong>Phone: </strong>' + aData['phone'] + '</p>\
										<p class="mb-10"><strong>Fax: </strong>' + aData['fax'] + '</p>\
										<p class="mb-10"><strong>Address: </strong>' + aData['address'] + '</p>\
										<p class="mb-10"><strong>Address: </strong>' + aData['address'] + '</p>\
										<p class="mb-10"><strong>Zip: </strong>' + aData['zip'] + '</p>\
										<p class="mb-10"><strong>Longitude: </strong>' + aData['longitude'] + '</p>\
										<p class="mb-10"><strong>Latitude: </strong>' + aData['latitude'] + '</p>\
										<p class="mb-10"><strong>Number of employees: </strong>' + aData['employees'] + '</p>\
										<p class="mb-10"><strong>Revenue of the company: </strong>' + aData['revenue'] + '</p>\
									</td>\
								</tr>\
								<tr>\
									<td class="w-100">Social networks</td>\
									<td>' + aData['social'] + '</td>\
								</tr>\
								<tr>\
									<td class="w-100">Profile competion</td>\
									<td>' + aData['profile_completion'] + '</td>\
								</tr>\
							</table>\
						</div>';
		return template;
	}
	// profile_completion

	var dtDirectoriesList;
	var $selectCity;
	var selectState;

$(document).ready(function() {
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
		showGoogleMapsAddress($(this).find("option:selected").text(),4);

		selectCountry($(this), 'select#states');
		selectState = 0;
		$selectCity.empty().trigger("change").prop("disabled", true);

	});

	var myFilters;

	dtDirectoriesList = $('#dtDirectoriesList').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "<?php echo __SITE_URL; ?>directory/ajax_list_directory_dt",
		"aoColumnDefs": [
			{"sClass": "w-50 vam tac", "aTargets": ['id_dt'], "mData": "id", "bSortable": false},
			{"sClass": "w-70 tac", "aTargets": ['type_dt'], "mData": "type"},
			{"sClass": "", "aTargets": ['company_dt'], "mData": "company"},
			{"sClass": "w-250", "aTargets": ['seller_dt'], "mData": "seller"},
			{"sClass": "w-200", "aTargets": ['profile_completion_percent'], "mData": "profile_completion_percent", "bSortable": false},
			{"sClass": "w-140", "aTargets": ['country_dt'], "mData": "country"},
			{"sClass": "w-110 tac vam", "aTargets": ['registered_dt'], "mData": "registered"},
			{"sClass": "w-40 tac vam", "aTargets": ['rating_dt'], "mData": "rating"},
			{"sClass": "w-60 tac", "aTargets": ['actions_dt'], "mData": "actions", "bSortable": false}
		],
		"sPaginationType": "full_numbers",
		"sDom": '<"top"lp>rt<"bottom"ip><"clear">',
		"sorting": [[7, "desc"]],
		"fnServerData": function(sSource, aoData, fnCallback) {
		if (!myFilters) {
			myFilters = $('.dt_filter').dtFilters('.dt_filter',{
				'container': '.wr-filter-list',
				callBack: function() {
					dtDirectoriesList.fnDraw();
				},
				onSet: function(callerObj, filterObj) {
					if (filterObj.name == 'start_date') {
						$("#finish_date").datepicker("option", "minDate", $("#start_date").datepicker("getDate"));
					}
					if (filterObj.name == 'finish_date') {
						$("#start_date").datepicker("option", "maxDate", $("#finish_date").datepicker("getDate"));
					}
				},
				onReset: function(){
					$('.filter-admin-panel .hasDatepicker').datepicker( "option" , {
						minDate: null,
						maxDate: null
					});
				}
			});
		}

		aoData = aoData.concat(myFilters.getDTFilter());
		$.ajax({
			"dataType": 'json',
			"type": "POST",
			"url": sSource,
			"data": aoData,
			"success": function(data, textStatus, jqXHR) {
			if (data.mess_type == 'error')
				systemMessages(data.message, 'message-' + data.mess_type);

			fnCallback(data, textStatus, jqXHR);

			}
		});
		},
		"fnDrawCallback": function(oSettings) {}
	});

	$("#industry-select").change(function() {
        idSelected = $(this).val() || null;
        if(null !== idSelected) {
            $('#category-select').show();
            $('#category-select optgroup').css('display', 'none');
            $('#category-select optgroup option').css('display', 'none');

            var selectedGroup = $('#category-select optgroup#' + idSelected);
            if(selectedGroup.length) {
                selectedGroup.css('display', '');
                selectedGroup.children('option').css('display', '');
            }
        }
	});

	// initialization of checkboxes
	$('.check-all-companies').on('click', function() {
		var value = $(this).is(":checked") ? 1 : 0;
		if (value) {
			$('.check-company').prop("checked", true);
			$('.btns-actions-all').show();
		} else{
			$('.check-company').prop("checked", false);
			$('.btns-actions-all').hide();
		}
	});

	$('body').on('click', '.check-company', function() {
		if ($(this).prop("checked")) {
			$('.btns-actions-all').show();
		} else{
			var hideBlock = true;
			$('.check-company').each(function() {
				if ($(this).prop("checked")) {
					hideBlock = false;
					return false;
				}
			})
			if (hideBlock)
				$('.btns-actions-all').hide();
		}
	});

	idStartItemNew = <?php echo $last_companies_id;?>;
	startCheckAdminNewItems('directory/ajax_company_administration/check_new', idStartItemNew);

	$('body').on('click', '#industry-list .ep-icon_arrows-right', function(){
		//this btn send ALL
		var $thisBtn = $(this);
		//industry li ALL
		var $thisLi = $thisBtn.closest('li');
		//remove this btn li ALL
		$thisBtn.addClass('display-n_i');
		//hide industry li ALL
		$thisLi.fadeOut();
		//append industry li SELECTED
		$('#industry-list-selected').append('<li>'+$thisLi.children('span').text()+' <input type="hidden" value="'+$thisLi.data('value')+'" name="industriesSelected[]"> <i class="ep-icon ep-icon_remove"></i></li>');
		//append category group li ALL
		categoryActualization($thisLi.data('value'));
		return false;
	});

	$('body').on('click', '#industry-list-selected .ep-icon_remove', function(){
		//this btn remove SELECTED
		var $thisBtn = $(this);
		//industry li SELECTED
		var $thisLi = $thisBtn.closest('li');
		//industry ID SELECTED
		var thisId = $thisLi.find('input').val();
		//show industry li ALL
		$('#industry-list').find('li[data-value="'+thisId+'"]').fadeIn().end()
				.find('.ep-icon_arrows-right').removeClass('display-n_i');
		//remove industry li SELECTED
		$thisLi.remove();
		//remove category group li ALL
		$('#category-list').find('li[data-value="'+thisId+'"]').remove();
		//remove category group li SELECTED
		$("#category-list-selected").find('li[data-value="'+thisId+'"]').remove();

		return false;
	});

	$('body').on('click', '#category-list .ep-icon_arrows-right', function(){
		var $thisBtn = $(this);
		var $thisLi = $thisBtn.closest('li');
		var $groupBlock = $thisBtn.closest('.group-b');
		var groupBlockId = $groupBlock.data('value');
		//add class invesible for btn send ALL
		$thisBtn.addClass('invisible');

		//if not send group ALL
		if(!$thisBtn.parent('.ttl-b').length){
			//category ul ALL
			var $thisUl = $thisLi.closest('ul');

			//hide this li ALL
			$thisLi.fadeOut('normal', function(){
				//if not exist visible li in the group ALL
				if(!$thisUl.find('li:visible').length){
					//hide li group ALL
					$thisUl.closest('li').fadeOut();
				}
			});

			//find li group SELECTED
			var $findLi = $('#category-list-selected').find('li[data-value="'+groupBlockId+'"]');

			//if exist group li SELECTED
			if($findLi.length){
				//add category li in the group li SELECTED
				$findLi.find('ul').append('<li>'+$thisLi.children('span').text()+' <input type="hidden" value="'+$thisLi.data('value')+'" name="categoriesSelected[]"> <i class="ep-icon ep-icon_remove"></i></li>')
					.closest('li').fadeIn();
			}else{
				// title group li ALL
				var $ttlGroup = $groupBlock.find('.ttl-b');
				//add group li and category li SELECTED
				$('#category-list-selected').append('<li data-value="'+groupBlockId+'">'+$ttlGroup[0].outerHTML+'<ul><li>'+$thisLi.children('span').text()+' <input type="hidden" value="'+$thisLi.data('value')+'" name="categoriesSelected[]"> <i class="ep-icon ep-icon_remove"></i></li></ul></li>')
				// title group li change btn icon SELECTED
				$('#category-list-selected').find('li[data-value="'+groupBlockId+'"]').find('.ttl-b').find('.ep-icon').toggleClass('ep-icon_arrows-right ep-icon_remove');
			}
		}else{

			//find li group SELECTED
			var $findLi = $('#category-list-selected').find('li[data-value="'+groupBlockId+'"]');

			//if exist group li SELECTED
			if($findLi.length){
				//find ul group SELECTED
				var $findUl = $findLi.find('ul');
				//each all category li in the group li for send ALL
				$thisLi.find('li:visible').each(function(){
					//hide this category li ALL
					$(this).hide();
					//append this category li to group li SELECTED
					$findUl.append('<li>'+$(this).children('span').text()+' <input type="hidden" value="'+$(this).data('value')+'" name="categoriesSelected[]"> <i class="ep-icon ep-icon_remove"></i></li>')
						.closest('li').fadeIn();
				});
			}else{
				// title group li ALL
				var $ttlGroup = $groupBlock.find('.ttl-b');
				//add group li SELECTED
				$('#category-list-selected').append('<li data-value="'+groupBlockId+'">'+$ttlGroup[0].outerHTML+'<ul></ul></li>')
				// title group li change btn icon SELECTED
				$('#category-list-selected').find('li[data-value="'+groupBlockId+'"]').find('.ttl-b').find('.ep-icon').toggleClass('ep-icon_arrows-right ep-icon_remove');
				//find li group SELECTED
				$findLi = $('#category-list-selected').find('li[data-value="'+groupBlockId+'"]');
				//find ul group SELECTED
				var $findUl = $findLi.find('ul');

				//each all category li in the group li for send ALL
				$thisLi.find('li').each(function(){
					//hide this category li ALL
					$(this).hide();
					//append this category li to group li SELECTED
					$findUl.append('<li>'+$(this).children('span').text()+' <input type="hidden" value="'+$(this).data('value')+'" name="categoriesSelected[]"> <i class="ep-icon ep-icon_remove"></i></li>');
				});
			}
			//hide group li ALL
			$thisLi.fadeOut();
		}
		return false;
	});

	$('body').on('click', '#category-list-selected .ep-icon_remove', function(){
		//btn remove SELECTED
		var $thisBtn = $(this);
		//category li or group li SELECTED
		var $thisLi = $thisBtn.closest('li');

		//if not remove group ALL
		if(!$thisBtn.parent('.ttl-b').length){
			//category ul SELECTED
			var $thisUl = $thisLi.closest('ul');
			//category li ID SELECTED
			var thisId = $thisLi.find('input').val();
			//category li ALL
			var $showLi = $('#category-list').find('li[data-value="'+thisId+'"]');
			//category ul ALL
			var $showUl = $showLi.closest('ul');

			//if not visible category group ALL
			if(!$showUl.closest('li').is(':visible')){
				//show category li and remove class invesible to action btn ALL
				$showLi.fadeIn().find('.ep-icon_arrows-right').removeClass('invisible');
				//show group li ALL
				$showUl.closest('li').fadeIn();
			}else{
				//show category li and remove class invesible to action btn ALL
				$showLi.fadeIn().find('.ep-icon_arrows-right').removeClass('invisible');
			}

			//remove category li SELECTED
			$thisLi.remove();

			//if not find li in category li SELECTED
			if(!$thisUl.find('li').length){
				//hide category li SELECTED
				$thisUl.closest('li').fadeOut();
			}
		}else{
			//category group ul SELECTED
			var $thisUl = $thisLi.find('ul');
			var thisIdLast;
			//each group li SELECTED
			$thisUl.find('li').each(function(){
				//category li ID SELECTED
				var thisId = thisIdLast = $(this).find('input').val();
				//show category li ALL
				$('#category-list').find('li[data-value="'+thisId+'"]').show().find('.ep-icon_arrows-right').removeClass('invisible');
				//remove this category li SELECTED
				$(this).remove();
			});
			console.log(thisIdLast);
			$('#category-list').find('li[data-value="'+thisIdLast+'"]').closest('ul').closest('li').fadeIn();
			//hide group ul ALL
			$thisUl.closest('li').fadeOut();
		}
		return false;
    });

    $(globalThis).on('company:edit-name', function (e) {
        dtDirectoriesList.fnDraw(false);
    });

	var unblockResource = function (caller) {
		var button = $(caller);
		var url = button.data('url') || null;
		var onRequestSuccess = function(resposne) {
			systemMessages(resposne.message, resposne.mess_type);
			if(resposne.mess_type === 'success') {
				dtDirectoriesList.fnDraw(false);
			}
		}

		if(null !== url) {
			$.post(url, null, null, 'json').done(onRequestSuccess).fail(onRequestError);
		}
	};

	var onResourceBlock = function() {
		dtDirectoriesList.fnDraw(false);
	};

	mix(window, {
		unblockResource: unblockResource,
		onResourceBlock: onResourceBlock,
	});

});

	// Google map functions
	var geocoder;
	var map;
	var marker;

	function init(){
		geocoder = new google.maps.Geocoder();

		var latLng = new google.maps.LatLng(0,0);
		var mapOptions = {
			center: latLng,
			zoom: 1,
			mapTypeControl: false,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		map = new google.maps.Map(document.getElementById('google-map-b'), mapOptions);

		 marker = new google.maps.Marker({ map:map });

		google.maps.event.addListener(map, 'click', function(event){
			//alert(132);
			var lat = event.latLng.lat();
			var lng = event.latLng.lng();
			//alert(c_marker);
			$('#lat').val(lat);
			$('#long').val(lng);

			var map_latlng = new google.maps.LatLng(lat, lng, false);

			$('#lat').val(lat);
			$('#long').val(lng);

			marker.setMap(null);
			marker = new google.maps.Marker({
				animation: google.maps.Animation.DROP,
				position: map_latlng,
				map: map,
				draggable: true,
			}); // carte marker
			putListenerToMarker(marker, map);
		});
	};

	function showGoogleMapsAddress(address,zoom) {
		geocoder.geocode( { 'address': address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			//alert(address);
			map.setCenter(results[0].geometry.location);
			map.setZoom(zoom);
		}
	  });
	};

	function putListenerToMarker(marker, map){

		google.maps.event.addListener(marker, 'drag', function(event){
			$("#lat").val(marker.position.lat());
			$("#long").val(marker.position.lng());
		});

		google.maps.event.addListener(marker, 'dragend', function(event){;
			$('#lat').val(marker.position.lat());
			$('#long').val(marker.position.lng());
		});
	}

	function setInfoMap(){
		lat = $('#lat').val();
		longt = $('#long').val();

		latLng = new google.maps.LatLng(lat,longt);

		if(lat != '' && longt != ''){

			marker.setMap(null);
			marker = new google.maps.Marker({
				animation: google.maps.Animation.DROP,
				position: latLng,
				map: map,
				draggable: true,
			});
			map.setCenter(latLng);
			map.setZoom(6);
			marker.setMap(map);
			putListenerToMarker(marker, map);
		}
	};

	$('body').on('click', 'a[rel=company_details]', function() {
		var $thisBtn = $(this);
		var nTr = $thisBtn.parents('tr')[0];

		if (dtDirectoriesList.fnIsOpen(nTr))
			dtDirectoriesList.fnClose(nTr);
		else{
			dtDirectoriesList.fnOpen(nTr, fnFormatDetails(nTr), 'details');
			$('.rating-bootstrap').rating();
			$('.rating-bootstrap').each(function () {
				var $this = $(this);
				ratingBootstrap($this);
			});
		}
		$thisBtn.toggleClass('ep-icon_plus ep-icon_minus');
	});

	var change_visibility = function(opener){
		var $this = $(opener);
		var id = $this.data('id');
		var state = $this.data('state');
		$.ajax({
			type: "POST",
			url: "directory/ajax_company_administration/change_visibility",
			data: {id: id, state: state},
			dataType: "JSON",
			success: function(resp) {
				if (resp.mess_type == 'success') {
					dtDirectoriesList.fnDraw(false);
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);

			}
		});
	}

	var change_featured_status = function (opener) {
		var company_id = $(opener).data('id-company');
		var state = $(opener).data('current-state');
		$.ajax({
			type: "POST",
			url: "directory/ajax_company_administration/change_featured_status",
			data: {id: company_id, current_state: state},
			dataType: "JSON",
			success: function(resp) {
				if (resp.mess_type == 'success') {
					dtDirectoriesList.fnDraw(false);
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}

	var change_visibility_multiple = function(opener){
		var $this = $(opener);
		var checked_comp = '';
		$.each($('.check-company:checked'), function(){
			checked_comp += $(this).data('id-company') + ',';
		});
		checked_comp = checked_comp.substring(0, checked_comp.length - 1);
		var state = $this.data('state');
		$.ajax({
			type: "POST",
			url: "directory/ajax_company_administration/change_visibility",
			data: {id: checked_comp, state: state},
			dataType: "JSON",
			success: function(resp) {
				if (resp.mess_type == 'success') {
					dtDirectoriesList.fnDraw();
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);

			}
		});
	}

	var change_locked_multiple = function(opener){

		var $this = $(opener);
        var checked_comp = '';

		$.each($('.check-company:checked'), function(){
			checked_comp += $(this).data('id-company') + ',';
		});

		checked_comp = checked_comp.substring(0, checked_comp.length - 1);
        var state = $this.data('state');

		$.ajax({
			type: "POST",
			url: "directory/ajax_company_administration/change_blocked",
			data: {id: checked_comp, state: state},
			dataType: "JSON",
			success: function(resp) {
				if (resp.mess_type == 'success') {
					dtDirectoriesList.fnDraw();
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);

			}
		});
	}

	var delete_logo = function(opener){
		var $this = $(opener);
		var id = $this.data('id');
		$.ajax({
			type: "POST",
			context: $(this),
			url: "directory/ajax_company_branch_delete_logo",
			data: {id: id},
			dataType: "JSON",
			success: function(resp) {
				if (resp.mess_type == 'success') {
				$this.parent('div').fadeOut('slow', function() {
					$(this).remove();
				});
				}
				systemMessages(resp.message, 'message-' + resp.mess_type);
			}
		});
	}
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="titlehdr h-30">
			<span>Directories list</span>
			<div class="pull-right btns-actions-all display-n">
				<a class="ep-icon ep-icon_visible pull-right confirm-dialog" data-state="invisible" data-message="Are you sure want to change status of company to invisible?" data-callback="change_visibility_multiple" title="Set checked companies visible"></a>
				<a class="ep-icon ep-icon_invisible pull-right mr-5 confirm-dialog" data-state="visible" data-message="Are you sure want to change status of company to visible?" data-callback="change_visibility_multiple" title="Set checked companies invisible"></a>

				<a class="ep-icon ep-icon_locked pull-right mr-5 confirm-dialog" data-state="1" data-message="Are you sure want to change status of company to invisible?" data-callback="change_locked_multiple" title="Set checked companies locked"></a>
				<a class="ep-icon ep-icon_unlocked pull-right mr-5 confirm-dialog" data-state="0" data-message="Are you sure want to change status of company to visible?" data-callback="change_locked_multiple" title="Set checked companies unlocked"></a>
			</div>
		</div>

		<?php tmvc::instance()->controller->view->display('admin/directory/filter_panel'); ?>

		<div class="wr-filter-list mt-10 clearfix"></div>

		<table id="dtDirectoriesList" class="data table-bordered table-striped w-100pr">
			<thead>
				<tr>
					<th class="id_dt"><input type="checkbox" class="check-all-companies pull-left">#</th>
					<th class="type_dt">Type</th>
					<th class="company_dt">Company</th>
					<th class="seller_dt">Seller</th>
					<th class="profile_completion_percent">Profile completion</th>
					<th class="country_dt">Country/City/State</th>
					<th class="registered_dt">Registered</th>
					<th class="rating_dt"><span class="ep-icon ep-icon_star txt-orange" title="Rating"></span></th>
					<th class="actions_dt">Actions</th>
				</tr>
			</thead>
			<tbody class="tabMessage" id="pageall"></tbody>
		</table>
	</div>
</div>
