function mapInit(){
	/**
	* If latitude and longitude is not gived by user we set them to (0, 0)
	*/
	if(typeof(myLatLng) !== 'undefined' && myLatLng != null)
		var latLng = myLatLng;
	else
		var latLng = new google.maps.LatLng(0, 0);
	/**
	* Defining map options
	*/

    directionsDisplay = new google.maps.DirectionsRenderer();
	var mapOptions = {
		center: latLng,
		zoom: (typeof(myZoom) !== 'undefined' && myZoom != '') ? myZoom : 1,
		mapTypeControl: false,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	map = new google.maps.Map(document.getElementById(map_block), mapOptions);

    directionsDisplay.setMap(map);
	geocoder = new google.maps.Geocoder();
	infoBubble = new InfoBubble({
        shadowStyle: 1,
		padding: 0,
		backgroundColor: 'rgb(255,255,255)',
		borderRadius: 0,
		minWidth: 220,
		minHeight: 80,
		arrowSize: 10,
		borderWidth: 0,
		borderColor: '#ddd',
		disableAutoPan: true,
		hideCloseButton: false,
		arrowPosition: 10,
		arrowStyle: 2
    });
	markersInit(map, geocoder, markers);

	if(mapType == 'edit') {
		newMarker = new google.maps.Marker({ map:map });
		addNewMarker();
		setInfoMap();
		$('body').on("change", '#lat, #long', function(){
			setInfoMap();
		});
	}
	if(mapType == 'direction') {
		var endPosition = null;
		if(markers[0].type == 'coords'){
			startPosition = markers[0].position;
		} else {
			startPosition = markers[0].ad;
		}
		newMarker = new google.maps.Marker({ map:map });
		addNewMarker();
	}
};

function markersInit(map, geocoder, markers){
    var bounds = new google.maps.LatLngBounds();
	for (var i in markers) {
		var marker = markers[i];
		if (marker.type == 'coords') {
			marker.position = new google.maps.LatLng(marker.lat, marker.lng);
            bounds.extend(marker.position);
			addMarkerInfoBubble(marker);
			if('radius' in marker){
				var populationOptions = {
	                strokeColor: '#05fc21',
	                strokeOpacity: 0.5,
	                strokeWeight: 2,
	                fillColor: '#64fc76',
	                fillOpacity: 0.35,
	                map: map,
	                center: marker.position,
	                radius: marker.radius * 1000
	            };
	            var cityCircle = new google.maps.Circle(populationOptions);
                bounds.union(cityCircle.getBounds());
	        }
		} else {
            geocodeSite(marker);
		}
	}

    google.maps.event.addListener(map, 'zoom_changed', function() {
        var zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function(event) {
			if (this.getZoom() > myZoom && this.initialZoom === true) {
				this.setZoom(myZoom);
				this.initialZoom = false;
			}
			google.maps.event.removeListener(zoomChangeBoundsListener);
		});
    });
    map.initialZoom = true;
	map.fitBounds(bounds);
}
function geocodeSite(marker) {
	geocoder = new google.maps.Geocoder();
	geocoder.geocode( { 'address': marker.ad}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			marker.position = results[0].geometry.location;
			addMarkerInfoBubble(marker);
		} else {
			alert("Geocode was not successful for the following reason: " + status);
		}
	});
}

var html_content = function(main_info, type_info){
	var block_content = '';

	if(markers_type == 'new'){
		switch(type_info){
			case 'company':
				var c_state = (main_info.c_state > 0)?', '+main_info.state_name:'';
				var zip_company = (main_info.zip_company != '')?', '+main_info.zip_company:'';

				block_content = '<div class="marker-content">\
					<div class="flex-card">\
						<div class="marker-content__img image-card2 flex-card__fixed">\
							<span class="link">\
								<img class="image" src="' + main_info.company_logo + '" alt="'+main_info.name_company+'"/>\
							</span>\
						</div>\
						<div class="marker-content__desc flex-card__float">\
							<div class="marker-content__ttl">\
								<a class="link" href="'+main_info.company_link+'">'+main_info.name_company+'</a>\
							</div>\
							<div class="marker-content__flag">\
								<img class="image" src="' + main_info.company_flag + '" alt="'+main_info.country_name+'" width="24" height="24"/>'
								+ main_info.city_name
								+ c_state
								+ ', '
								+ main_info.country_name
								+ zip_company
							+'</div>\
						</div>\
					</div>\
					<div class="marker-content__address">\
						Adress: '+main_info.address_company+
					'</div>\
				</div>';
			break;
			case 'international_standards':
				block_content = '<div class="marker-content">\
					<div class="flex-card">\
						<div class="marker-content__img image-card2 flex-card__fixed">\
							<span class="link">\
								<img class="image" src="' + main_info.company_flag + '" alt="'+main_info.country+'"/>\
							</span>\
						</div>\
						<div class="marker-content__desc flex-card__float">\
							<div class="marker-content__flag">'
								+ main_info.country +
							'</div>\
						</div>\
					</div>\
				</div>';
			break;
			case 'office':
				block_content = '<div class="marker-content">\
					<div class="marker-content__ttl">'+main_info.name_office+'</div>\
					<div class="marker-content__address">'
						+main_info.address_office+'<br/>'+main_info.country
					+'</div>\
				</div>';
			break;
			case 'event':
				block_content = '<div class="marker-content"">\
					<div class="flex-card">\
						<div class="marker-content__img image-card2 flex-card__fixed">\
							<span class="link">\
								<img class="image" src="' + main_info.event_logo + '" alt="'+main_info.title_event+'"/>\
							</span>\
						</div>\
						<div class="marker-content__desc flex-card__float">\
							<div class="marker-content__ttl">'+main_info.title_event+'</div>\
							<div class="marker-content__date">Date: '+main_info.date_event+'</div>\
						</div>\
					</div>\
					<div class="marker-content__address">Adress: '+main_info.address+'</div>\
				</div>';
			break;
			case 'b2b':
				block_content = '<div class="marker-content">\
					<div class="flex-card">\
						<div class="marker-content__img image-card2 flex-card__fixed">\
							<span class="link">\
								<img class="image" src="' + main_info.company_logo + '" alt="'+main_info.name_company+'"/>\
							</span>\
						</div>\
						<div class="marker-content__desc flex-card__float">\
							<div class="marker-content__ttl">\
								<a href="'+main_info.company_link+'">'+main_info.name_company+'</a>\
							</div>\
							<div class="marker-content__flag">\
								<img class="image" src="' + main_info.company_flag + '" alt="'+main_info.company_loc.country+'" width="24" height="24" />'
								+ main_info.company_loc.city +
							'</div>\
						</div>\
						<div class="marker-content__address">\
							Adress: '+main_info.address_company+
						'</div>\
					</div>\
				</div>';
			break;
		}
	}else{
		switch(type_info){
			case 'company':
				var c_state = (main_info.c_state > 0)?', '+main_info.state_name:'';
				var zip_company = (main_info.zip_company != '')?', '+main_info.zip_company:'';

				block_content = '<div class="marker-content clearfix">\
					<div class="img-b">\
						<img src="' + main_info.company_logo + '" alt="'+main_info.name_company+'"/>\
					</div>\
					<div class="ttl-b">\
						<a href="'+main_info.company_link+'">'+main_info.name_company+'</a>\
						<br />\
						<h5 style="font-size: 11px;font-weight: normal;line-height: 16px;">\
							<img height="16px" class="pull-left mr-5" src="' + main_info.company_flag + '" alt="'+main_info.country_name+'"/>'
							+ main_info.city_name
							+ c_state
							+ ', '
							+ main_info.country_name
							+ zip_company
						+'</h5>\
					</div>\
					<div class="clearfix"></div>\
					<div class="address-b">\
						Adress: '+main_info.address_company+
					'</div>\
				</div>';
			break;
			case 'international_standards':
				block_content = '<div class="marker-content clearfix">\
					<table class="table mb-0 w-100pr">\
						<tr>\
							<td class="bdt-none tac w-65">\
								<img height="48px" src="' + main_info.company_flag + '" alt="'+main_info.country+'"/>\
							</td>\
							<td class="vam bdt-none pl-0">\
								<strong>'+main_info.country+'</strong>\
							</td>\
						</tr>\
					</table>\
				</div>';
			break;
			case 'office':
				block_content = '<div class="marker-content">\
					<strong>'+main_info.name_office+'</strong>\
					<div>'
						+main_info.address_office+'<br/>'+main_info.country
					+'</div>\
				</div>';
			break;
			case 'event':
				block_content = '<div class="marker-content">\
							<div class="img-b">\
								<img src="' + main_info.event_logo + '" alt="'+main_info.title_event+'"/>\
							</div>\
							<div class="ttl-b">'+main_info.title_event+'</div>\
							<div class="clearfix"></div>\
							<div class="text-b">\
								<div class="date-b">Date: '+main_info.date_event+'</div>\
								<div class="address-b">Adress: '+main_info.address+'</div>\
							</div>\
						</div>';
			break;
			case 'b2b':
				block_content = '<div class="marker-content clearfix">\
						<div class="img-b w-50">\
							<img src="' + main_info.company_logo + '" alt="'+main_info.name_company+'"/>\
						</div>\
						<div class="ttl-b">\
							<a href="'+main_info.company_link+'">'+main_info.name_company+'</a>\
							<br />\
							<h5 style="font-size: 11px;font-weight: normal;line-height: 16px;">\
								<img height="16px" class="pull-left mr-5" src="' + main_info.company_flag + '" alt="'+main_info.company_loc.country+'" />\
								'+main_info.company_loc.city+
							'</h5>\
						</div>\
						<div class="clearfix"></div>\
						<div class="address-b">\
							Adress: '+main_info.address_company+
						'</div>\
					</div>';
			break;
		}
	}

	return block_content;
}

function addMarkerInfoBubble(marker) {

	var image = '';

	if(markers_type == 'new'){
		image = __img_url + 'public/img/marker.png';
		alt = 'marker';
	}

	Marker = new google.maps.Marker({
		map: map,
		position: marker.position,
		title: marker.title,
		icon: image,
		alt: alt,
	});
	if(mapType == 'edit') {
		Marker.setDraggable(true);
		putListenerToMarker(Marker);
	}
	google.maps.event.addListener(Marker, 'click', function () {
//		infoBubble.setContent(marker.content);
		if(marker.infoBubble == undefined)
			marker.infoBubble = html_content(marker.main_info, marker.type_info, marker);

		infoBubble.setContent(marker.infoBubble)
		infoBubble.open(map, this);
	});
	google.maps.event.addListener(map, 'click', function () {
		infoBubble.close(map, this);
	});
}
function addNewMarker(){
	google.maps.event.addListener(map, 'click', function(event){
		var lat = event.latLng.lat();
		var lng = event.latLng.lng();
		//alert(c_marker);
		if(mapType == 'edit'){
			$('#lat').val(lat);
			$('#long').val(lng);
		}
		var map_latlng = new google.maps.LatLng(lat, lng, false);


		newMarker.setMap(null);
		newMarker = new google.maps.Marker({
			animation: google.maps.Animation.DROP,
			position: map_latlng,
			map: map,
			draggable: true,
			icon: "http://www.google.com/mapfiles/marker_green.png",
		}); // carte marker

		putListenerToMarker(newMarker);
		if(mapType == 'direction')
			calcRoute(startPosition,newMarker.position);
	});
}
function putListenerToMarker(marker){
	google.maps.event.addListener(marker, 'drag', function(event){
		if(mapType == 'edit'){
			$("#lat").val(marker.position.lat());
			$("#long").val(marker.position.lng());
		}
	});

	google.maps.event.addListener(marker, 'dragend', function(event){;
		if(mapType == 'edit'){
			$('#lat').val(marker.position.lat());
			$('#long').val(marker.position.lng());
		}
		if(mapType == 'direction')
			calcRoute(startPosition,marker.position);
	});
}

function setInfoMap(){
	lat = $('#lat').val();
	longt = $('#long').val();

	latLng = new google.maps.LatLng(lat,longt);

	if(lat != '' && longt != ''){

		newMarker.setMap(null);
		newMarker = new google.maps.Marker({
			animation: google.maps.Animation.DROP,
			position: latLng,
			map: map,
			draggable: true,
		});
		map.setCenter(latLng);
		map.setZoom(6);
		newMarker.setMap(map);
		putListenerToMarker(newMarker);
	}
};

function calcRoute(start,end) {
	var request = {
		origin:start,
		destination:end,
		travelMode: google.maps.DirectionsTravelMode.DRIVING,
        unitSystem: google.maps.UnitSystem.METRIC
	};
	directionsService.route(request, function(response, status) {
	  if (status == google.maps.DirectionsStatus.OK) {
		directionsDisplay.setDirections(response);
	  }
	});
}
