import $ from "jquery";
import selectCountry from "@src/util/common/select-country";
import initSelectCity from "@src/util/common/init-select-city";
import { translate } from "@src/i18n";

class GoogleMaps {
    constructor(parameters) {
        this.self = this;
        this.geocoder = null;
        this.map = null;
        this.marker = null;
        this.zoom = 6;
        this.params = JSON.parse(parameters);
        this.latitude = parseFloat(this.params.markerLatitude);
        this.longitude = parseFloat(this.params.markerLongitude);
        this.mapTitle = this.params.markerTitle;
        this.cityName = "";
        this.countryName = "";
        this.addressName = "";
        this.selectState = "";
        this.selectCity = $(".select-city");
        this.countryName = $("select#country").find("option:selected").text().trim();
        this.cityName = this.selectCity.next(".select2-container").find("#select2-port_city-container").text().trim();
        this.addressName = $('input[name="address"]').val().trim();

        this.initPlug();
        this.initListiners();
        this.initGoogle();
        this.setInfoMap();
    }

    initPlug() {
        initSelectCity(this.selectCity);
    }

    initListiners() {
        const that = this;
        $("#company-edit--form-field--address").on("change", function () {
            that.addressName = $(this).val().trim();
            that.showGoogleMapsAddress();
        });

        that.selectCity.on("select2:select", function (e) {
            console.log(e.params);
            const { data } = e.params;
            that.cityName = data.name.trim();
            that.addressName = "";
            that.self.showGoogleMapsAddress();
        });
        that.selectCity
            .data("select2")
            .$container.attr("id", "select-сity--formfield--tags-container")
            .addClass("validate[required]")
            .setValHookType("selectselectCitySeller");

        $.valHooks.selectselectCitySeller = {
            get() {
                return that.selectCity.val() || [];
            },
        };

        $("body").on("change", "select#country_states", event => {
            that.selectState = event.target.value;
            that.selectCity.empty().trigger("change").prop("disabled", false);
            let selectText = "";
            if (that.selectState !== "") {
                selectText = translate({ plug: "general_i18n", text: "form_placeholder_select2_city" });
            } else {
                selectText = translate({ plug: "general_i18n", text: "form_placeholder_select2_state_first" });
                that.selectCity.prop("disabled", true);
            }
            that.selectCity.siblings(".select2").find(".select2-selection__placeholder").text(selectText);
        });

        $("body").on("change", "#country", function () {
            selectCountry($(this), "select#country_states");
            // that.selectState = 0;
            that.countryName = $(this).find("option:selected").text().trim();
            that.cityName = "";
            that.addressName = "";
            that.showGoogleMapsAddress();

            that.selectCity.empty().trigger("change").prop("disabled", true);
        });

        that.selectCity.empty().trigger("change").prop("disabled", false);

        $(globalThis).on("resize", () => {
            that.setInfoMap();
        });
    }

    initGoogle() {
        const that = this;
        that.geocoder = new google.maps.Geocoder();

        const latLng = new google.maps.LatLng(that.latitude, that.longitude);
        const mapOptions = {
            center: latLng,
            zoom: 13,
            mapTypeControl: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
        };

        that.map = new google.maps.Map(document.getElementById("google-map-b"), mapOptions);
        that.marker = new google.maps.Marker({ map: that.map });

        google.maps.event.addListener(that.map, "click", event => {
            // UPDATE LAT, LNG VALUES
            that.latitude = event.latLng.lat();
            that.longitude = event.latLng.lng();
            $("#lat").val(that.latitude);
            $("#long").val(that.longitude);

            const mapLatlng = new google.maps.LatLng(that.latitude, that.longitude, false);

            // CLEAR MAP MARKERS
            that.marker.setMap(null);

            // PUT MARKER ON THE MAP
            that.marker = new google.maps.Marker({
                animation: google.maps.Animation.DROP,
                position: mapLatlng,
                map: that.map,
                draggable: true,
                title: that.mapTitle,
            });
            that.putListenerToMarker(that.marker, that.map);
        });
    }

    showGoogleMapsAddress() {
        const that = this;
        const formattedAddress = [];
        if (that.addressName !== "") {
            formattedAddress.push(that.addressName);
            that.zoom = 16;
        }
        if (that.cityName !== "" && that.cityName !== translate({ plug: "general_i18n", text: "form_placeholder_select2_state_first" })) {
            formattedAddress.push(that.cityName);
            that.zoom = 13;
        }
        if (that.countryName !== "") {
            formattedAddress.push(that.countryName);
        }

        if (formattedAddress.length > 0) {
            that.geocoder.geocode({ address: formattedAddress.join(", ") }, (results, status) => {
                if (status === google.maps.GeocoderStatus.OK) {
                    // CLEAR MAP MARKERS
                    that.marker.setMap(null);

                    // UPDATE LAT, LNG VALUES
                    that.latitude = results[0].geometry.location.lat();
                    that.longitude = results[0].geometry.location.lng();
                    $("#lat").val(that.latitude);
                    $("#long").val(that.longitude);

                    // PUT MARKER ON THE MAP
                    that.marker = new google.maps.Marker({
                        animation: google.maps.Animation.DROP,
                        position: results[0].geometry.location,
                        map: that.map,
                        draggable: true,
                        title: that.mapTitle,
                    });
                    that.map.setCenter(results[0].geometry.location);
                    that.map.setZoom(that.zoom);
                }
            });
        }
    }

    putListenerToMarker(marker, map) {
        const that = this;
        google.maps.event.addListener(that.marker, "drag", () => {
            $("#lat").val(that.marker.position.lat());
            $("#long").val(that.marker.position.lng());
        });

        google.maps.event.addListener(that.marker, "dragend", () => {
            $("#lat").val(that.marker.position.lat());
            $("#long").val(that.marker.position.lng());
        });
    }

    setInfoMap() {
        const that = this;
        // GET LAT, LNG VALUES
        that.latitude = $("#lat").val();
        that.longitude = $("#long").val();
        const latLng = new google.maps.LatLng(that.latitude, that.longitude);
        if (that.latitude !== "" && that.longitude !== "") {
            // CLEAR MAP MARKERS
            that.marker.setMap(null);

            // PUT MARKER ON THE MAP
            that.marker = new google.maps.Marker({
                animation: google.maps.Animation.DROP,
                position: latLng,
                map: that.map,
                draggable: true,
                title: that.mapTitle,
            });
            that.map.setCenter(latLng);
            that.map.setZoom(13);
            that.marker.setMap(that.map);
            that.self.putListenerToMarker(that.marker, that.map);
        }
        that.map.setCenter(latLng);
    }
}

export default GoogleMaps;
