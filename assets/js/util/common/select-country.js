import $ from "jquery";
import { SITE_URL } from "@src/common/constants";

const selectCountry = (country, statesSelectElement, placeholder = "") => {
    return $.ajax({
        type: "POST",
        dataType: "JSON",
        // eslint-disable-next-line no-underscore-dangle
        url: `${SITE_URL}location/ajax_get_states`,
        data: { country: $(country).val(), placeholder },
        success: resp => {
            $(statesSelectElement).html(resp.states);
        },
    });
};

export default selectCountry;
