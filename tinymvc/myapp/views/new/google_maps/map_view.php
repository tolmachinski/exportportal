<?php views()->display('new/js_global_vars_view'); ?>
<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyAko8g1LP9autKH12-8d1VkUZn3UaIZB8E"></script>
<script src="<?php echo fileModificationTime('public/plug/infobubble-0-8/infobubble-compiled.js');?>" type="text/javascript"></script>
<script src="<?php echo fileModificationTime('public/plug/js/google.map.functions.js');?>" type="text/javascript"></script>
<script type="text/javascript">
	/**
	* Initialization of Google map
	* There is described all vars
	* @return
	*/
	var markers_type = 'old';
	<?php if(!empty($markers_type)){?>
        markers_type = '<?php echo $markers_type;?>';
	<?php } ?>
	/**
	* Element 'id' where the map will be asigned
	*/
	var map_block = 'map-google-b';
	/**
	* Markers array, by default should be {}
	*/
	var markers = new Object();

	/**
	* Map zoom, default 1
	*/
	var myZoom = 5;
	/**
	* Initialization of global variables
	*/
	var infoBubble = null;
	var map = null;
	/**
	* By default is null, but can be 'edit', 'direction'
	*/
	var mapType = null;
    <?php if(!empty($myMapConfig['mapType'])){?>
        mapType = '<?php echo $myMapConfig['mapType'];?>';
    <?php } ?>
	/**
	* If mupType is for editable mode alert message
	* if input's with id 'lat' and 'long' wos not found
	* on this page
	*/
	if(mapType == 'edit'){
		$.fn.elementExists = function()
        {
        	return jQuery(this).length > 0;
        };
		if(!$("#lat, #long").elementExists()){
			alert('Please add two input type texts \n 1) with id="lat" \n 2) with id="long".');
		}
	}
	var directionDisplay = null;
	var directionsService = new google.maps.DirectionsService();
	var geocoder = null;
	var newMarker = null;
	var startPosition = null;
	/**
	* Latitude and longitude of center of the map, default will be new google.maps.LatLng(0, 0)
	*/
	var myLatLng = null;
	<?php if(!empty($myMapConfig)){
		if(!empty($myMapConfig['centerLat']) && !empty($myMapConfig['centerLng'])){?>
			myLatLng = new google.maps.LatLng(<?php echo $myMapConfig['centerLat'];?>,<?php echo $myMapConfig['centerLng'];?>);
		<?php }
		if(!empty($myMapConfig['zoom'])){?>
			myZoom = <?php echo $myMapConfig['zoom'];?>;
		<?php }?>


		markers = <?php echo $myMapConfig['markers'];?>;
	<?php } ?>

	if(myLatLng == null){
		var count = Object.keys(markers).length;

		if(count == 1){
			if(markers[0].type == 'coords'){
				myLatLng = new google.maps.LatLng(markers[0].lat, markers[0].lng);
				$(document).ready(function () { mapInit(); });
			}
			if(markers[0].type == 'address'){
				geocoder = new google.maps.Geocoder();
				geocoder.geocode({ 'address': markers[0].ad }, function (results, status) {
                    // console.log(google.maps.GeocoderStatus.OK);
					if( status == google.maps.GeocoderStatus.OK ) {
						myLatLng = new google.maps.LatLng(results[0].geometry.location.lat() + 3, results[0].geometry.location.lng()+4);
						$(document).ready(function () { mapInit(); });
					}
				});
			}
		} else {
			$(document).ready(function () { mapInit(); });
		}
	}else {
		$(document).ready(function () { mapInit(); });
	}
	/**
	* On document ready initialize google map
	*/
</script>

<div id="map-google-b" <?php echo addQaUniqueIdentifier("global__google-map")?>></div>
