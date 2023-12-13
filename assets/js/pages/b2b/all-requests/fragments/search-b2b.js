import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import { validateElement } from "@src/plugins/validation-engine/index";

/**
 * It takes the values from the form and creates a URI based on them
 */
const searchB2b = async () => {
    const form = $("#js-b2b-search-form");
    const isValidForm = await validateElement(form, {
        promptPosition: "topLeft",
        autoPositionUpdate: true,
        showArrow: false,
        addFailureCssClassToField: "validengine-border",
    });

    if (!isValidForm) {
        return false;
    }

    const uriParams = [];

    $("#js-search-b2b-uri-info")
        .find("select option:selected")
        .each(function eachUriElements() {
            const uriElement = $(this);
            const selectName = uriElement.closest("select").attr("name");

            if (uriElement.val() !== "" && selectName !== "golden_categories") {
                uriParams.push(`${selectName}/${uriElement.data("name")}-${uriElement.val()}`);
            }
        });

    let formAction = `${SITE_URL}b2b/all/`;
    if (uriParams.length > 0) {
        formAction += uriParams.join("/");
    }

    form.find("*[name]").each(function eachFormElements() {
        const el = $(this);

        if (el.val() === "") {
            el.removeAttr("name");
        }
    });

    form.attr("action", String(formAction)).trigger("submit");
    return true;
};

export default searchB2b;
