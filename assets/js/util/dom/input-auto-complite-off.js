import $ from "jquery";

const inputAutocompliteOff = () => {
    $('input:not([type="hidden"])').each(function eachInput() {
        $(this).attr("autocomplete", `${$(this).attr("name") || "input"}_${Date.now()}${$(this).index()}`);
    });
};

export default inputAutocompliteOff;
