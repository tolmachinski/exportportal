import makeLocationBlock from "@src/components/location/location-block";
import { renderGoogleMap, MapHandler } from "@src/common/google-maps/google";
import getElement from "@src/util/dom/get-element";

// Add drag event for marker
function attachDragListeners(marker, latitudeNode, longitudeNode) {
    globalThis.google.maps.event.addListener(marker, "dragend", () => {
        latitudeNode.val(marker.position.lat());
        longitudeNode.val(marker.position.lng());
    });
}

// Create marker based on geodata and override coordinates on input fields
async function updateLocation(mapHandle, latitudeNode, longitudeNode, { country = null, city = null, cityAddress = null }, zoom) {
    const markers = await MapHandler.createMarkersFromLocation(mapHandle.geocoder, { country, city, cityAddress });
    const marker = MapHandler.createMarker(markers[0].position.lat(), markers[0].position.lng());

    mapHandle.clearMarkers();
    mapHandle.addMarker(marker);
    mapHandle.map.setCenter(marker.position);
    mapHandle.map.setZoom(zoom);
    latitudeNode.val(marker.position.lat());
    longitudeNode.val(marker.position.lng());
    attachDragListeners(marker, latitudeNode, longitudeNode);
}

export default async (wrapper, locationInfo) => {
    const mapSelector = getElement(wrapper.data("map"));
    const address = getElement(wrapper.data("address"));
    const latitudeNode = getElement(wrapper.data("lat"));
    const longitudeNode = getElement(wrapper.data("lng"));
    const { latitude, longitude, title } = locationInfo;
    const { countries, cities } = makeLocationBlock(wrapper);
    const mapOptions = {
        center: new globalThis.google.maps.LatLng(latitude, longitude),
        title,
    };
    const mapHandle = renderGoogleMap(mapSelector[0], mapOptions);
    const onUpdateLocation = updateLocation.bind(null, mapHandle, latitudeNode, longitudeNode);

    let country = countries.find("option:selected").text().trim();
    let city = cities.siblings(".select2-container").find(".select2-selection__rendered").text().trim();
    let cityAddress = address.val().toString().trim();

    try {
        // Create marker based on latitude and longitude
        if (latitude && longitude) {
            const marker = MapHandler.createMarker(latitude, longitude, title);
            mapHandle.addMarker(marker);
            attachDragListeners(marker, latitudeNode, longitudeNode);
        } else {
            // Create marker based on geodata if there is no latitude and longitude
            const markers = await MapHandler.createMarkersFromLocation(mapHandle.geocoder, { country, city, cityAddress });
            const marker = MapHandler.createMarker(markers[0].position.lat(), markers[0].position.lng());
            mapHandle.addMarker(marker);
            mapHandle.map.setCenter(marker.position);
            attachDragListeners(marker, latitudeNode, longitudeNode);
        }
    } catch (error) {
        // Do nothing
        // eslint-disable-next-line no-console
        console.error(error);
    }
    // Handle change contries event.
    countries.on("change", () => {
        country = countries.find("option:selected").text().trim();
        onUpdateLocation({ country, city: null, cityAddress: null }, 6);
    });

    // Handle change cities event.
    cities.on("select2:select", e => {
        city = e.params?.data?.name.toString().trim();
        onUpdateLocation({ country, city, cityAddress: null }, 13);
    });

    // Handle change address event.
    address.on("change", () => {
        cityAddress = address.val().toString().trim();
        onUpdateLocation({ country, city, cityAddress }, 16);
    });

    // Handle click map event.
    mapHandle.map.addListener("click", event => {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();

        const marker = MapHandler.createMarker(lat, lng, title);
        mapHandle.clearMarkers();
        mapHandle.addMarker(marker);
        latitudeNode.val(lat);
        longitudeNode.val(lng);
        attachDragListeners(marker, latitudeNode, longitudeNode);
    });
};
