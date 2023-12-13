import handleRequestError from "@src/util/http/handle-request-error";

class MapHandler {
    constructor(map, geocoder) {
        this.markers = new Set();
        this.geocoder = geocoder;
        this.map = map;
    }

    addMarker(marker) {
        if (!(marker instanceof globalThis.google.maps.Marker)) {
            throw new ReferenceError('The marker must be instance of "google.maps.Marker"');
        }

        this.markers.add(marker);
        marker.setMap(this.map);
    }

    clearMarkers() {
        Array.from(this.markers.values()).forEach(marker => {
            marker.setMap(null);
        });

        this.markers.clear();
    }

    static async createMarkersFromLocation(geocoder, location) {
        const { country, city, cityAddress } = location;
        let markers = [];
        const formattedLocation = [];

        if (country !== null) {
            formattedLocation.push(country);
        }

        if (city !== null) {
            formattedLocation.push(city);
        }

        if (cityAddress !== null) {
            formattedLocation.push(cityAddress);
        }

        // get the formatted location
        try {
            const { results } = await geocoder.geocode({ address: formattedLocation.join(", ") });

            if (!globalThis.google.maps.GeocoderStatus.OK) {
                // Handle failed geocoding
                return [];
            }

            markers = results.map(res => MapHandler.createMarker(res.geometry.location.lat(), res.geometry.location.lng()));
        } catch (error) {
            handleRequestError(error);
        }

        return markers;
    }

    static createMarker(latitude, longitude, title) {
        const marker = new globalThis.google.maps.Marker({
            animation: globalThis.google.maps.Animation.DROP,
            position: new globalThis.google.maps.LatLng(latitude, longitude),
            draggable: true,
            map: null,
            title,
        });

        return marker;
    }
}

const defaultMapOptions = {
    zoom: 13,
    mapTypeControl: false,
    mapTypeId: globalThis.google.maps.MapTypeId.ROADMAP,
};

const renderGoogleMap = (selector, options) => {
    const geocoder = new globalThis.google.maps.Geocoder();
    const map = new globalThis.google.maps.Map(selector, { ...defaultMapOptions, ...options });
    const mapHandler = new MapHandler(map, geocoder);

    return mapHandler;
};

export { renderGoogleMap, MapHandler };
