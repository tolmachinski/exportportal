<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700 h-430">
		<table cellspacing="0" cellpadding="0" class="data table-bordered table-striped w-100pr" >
			<tr>
				<td class="w-150">Name</td>
				<td><input type="text" name="name" class="w-100pr validate[required]" value="<?php echo $office_info['name_office']?>" /></td>
			</tr>
			<tr>
				<td>Phone</td>
				<td>
					<input type="text" name="tel" class="w-100pr validate[required]" value="<?php echo $office_info['phone_office']?>" />
				</td>
			</tr>
			<tr>
				<td>Fax</td>
				<td>
					<input type="text" name="fax" class="w-100pr validate[required]" value="<?php echo $office_info['fax_office']?>" />
				</td>
			</tr>
			<tr>
				<td>Email</td>
				<td>
					<input type="text" name="email" class="w-100pr validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]" value="<?php echo $office_info['email_office']?>" />
				</td>
			</tr>
			<tr>
				<td>Information</td>
				<td>
					<textarea class="validate[required]" data-max="255" id="office_description" name="info"><?php echo $office_info['text_office']?></textarea>
				</td>
			</tr>
			<tr>
				<td>Country</td>
				<td>
					<select class="w-100pr validate[required]" name="country" id="choose-country">
					<?php if(!isset($office_info)){?> <option selected="selected" disabled="disabled">Select</option> <?php } ?>
					<?php if(isset($countries)){ ?>
						<?php foreach($countries as $country_item){?>
							<option value="<?php echo $country_item['id']?>" <?php echo selected($office_info['id_country'], $country_item['id'])?>><?php echo $country_item['country']?></option>
						<?php }?>
					<?php }?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Address</td>
				<td>
					<input type="text" name="address" class="w-100pr validate[required]" value="<?php echo $office_info['address_office']?>" />
				</td>
			</tr>
			<tr>
				<td>
					Map coordinates
					<p class="map-coord ">Please indicate the location of this office on the map, or write the coordinates manually.</p>
				</td>
				<td class="latlong-google-map-b">
					<div class="input-group">
						<div class="input-group-addon">Latitude:</div>
						<input class="form-control" type="text" name="lat" id="lat" value="<?php echo $office_info['latitude']?>"/>
						<div class="input-group-addon">Longitude:</div>
						<input class="form-control" type="text" name="long" id="long" value="<?php echo $office_info['longitude']?>"/>
					</div>
					<div id="google-map-b" class="h-400"></div>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($office_info)){?>
			<input type="hidden" name="id_office" value="<?php echo $office_info['id_office'];?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		tinymce.init({
			selector:'#office_description',
			menubar: false,
			statusbar : true,
			height : 100,
			plugins: ["autolink lists link charactercount"],
            style_formats: [
                {title: 'H3', block: 'h3'},
                {title: 'H4', block: 'h4'},
                {title: 'H5', block: 'h5'},
                {title: 'H6', block: 'h6'},
            ],
			toolbar: "styleselect | bold italic underline | link | numlist bullist ",
			resize: false
		});

		init();

		$('#choose-country').on("change", function(){
			var country = $(this).find('option').filter( ":selected" ).text();
			showGoogleMapsAddress(country);
		});

		$('#lat, #long').on("change", function(){
			setInfoMap();
		});

		setInfoMap();

	});

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

	function showGoogleMapsAddress(address) {
		geocoder.geocode( { 'address': address}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			//alert(address);
			map.setCenter(results[0].geometry.location);
			map.setZoom(6);
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

	function modalFormCallBack(form){
		var $form = $(form);
		var fdata = $form.serialize();

		<?php if(isset($office_info)){?>
			var url = 'offices/ajax_offices_operation/update_office';
		<?php }else{?>
			var url = 'offices/ajax_offices_operation/create_office';
		<?php }?>

		$.ajax({
			type: 'POST',
			url: url,
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				$form.find('button[type=submit]').addClass('disabled');
			},
			success: function(resp){
				systemMessages( resp.message, 'message-' + resp.mess_type );

				if(resp.mess_type == 'success'){
					closeFancyBox();

					<?php if(isset($office_info)){?>
						callbackUdateOffices(resp);
					<?php }else{?>
						callbackCreateOffices(resp);
					<?php }?>
				}else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
</script>
