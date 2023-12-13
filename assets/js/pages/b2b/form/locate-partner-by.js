import $ from "jquery";
import EventHub from "@src/event-hub";
import { systemMessages } from "@src/util/system-messages/index";
import renderOptionGroup from "@src/components/option-group/index";
import loadBootstrapDialog, { openResultModal } from "@src/plugins/bootstrap-dialog/index";
import { translate } from "@src/i18n";

const currentOptions = $("input[name='countries[]']")
    .map(function mapCountryItem() {
        return Number($(this).val());
    })
    .get();
let selectedOptions = currentOptions.length ? currentOptions : [];
let isInitSelectCountriesListeners = false;

/**
 * It renders an option group for the selected option in the select element
 * @param {JQuery} optionGroupWrapper - the wrapper element for the option group.
 * @param {JQuery} select - the select element that the user selected an option from.
 */
const renderCountryOption = async (optionGroupWrapper, select) => {
    const option = select.find("option:selected");
    const optionId = Number(option.val());

    if (!optionId) {
        systemMessages("Choose country first.", "danger");
        return;
    }

    if (selectedOptions.includes(optionId)) {
        systemMessages("You have already selected this country.", "danger");
    } else {
        selectedOptions.push(optionId);
        renderOptionGroup({ wrapper: optionGroupWrapper, optionId, optionText: option.text(), inputName: "countries" });
        select.val("").removeClass("validate[required]");
    }
};

/**
 * It adds a listener to the event hub to add a new country option when the event is triggered
 * @param {JQuery} select - the select element that the user selected an option from.
 */
const initSelectCountriesListeners = async select => {
    const optionGroupWrapper = $("#js-option-group-wr");

    EventHub.off("b2b-form:countries.add");
    EventHub.on("b2b-form:countries.add", () => {
        renderCountryOption(optionGroupWrapper, select);
    });
};

/**
 * It opens a modal dialog asking the user to confirm a change of location type
 * @param {JQuery} select - the select element that the user selected an option from.
 */
const confirmChangeLocationType = async select => {
    const prevVal = select.data("value");

    await loadBootstrapDialog();

    openResultModal({
        subTitle: select.data("message"),
        buttons: [
            {
                label: translate({ plug: "BootstrapDialog", text: "ok" }),
                cssClass: "btn-success",
                action(dialogRef) {
                    this.disable();
                    selectedOptions = [];
                    EventHub.trigger(select.data("jsAction"));
                    dialogRef.close();
                },
            },
            {
                label: translate({ plug: "BootstrapDialog", text: "cancel" }),
                cssClass: "btn-light",
                action(dialogRef) {
                    select.val(prevVal);
                    dialogRef.close();
                },
            },
        ],
    });
};

/**
 * It shows/hides the country and radius inputs based on the value of the location select
 * @param {JQuery} locationSelect - the select element that contains the options for the location type
 * @param {JQuery} countryWrapper - the wrapper for the country input
 * @param {JQuery} radiusWrapper - the wrapper of the radius input
 * @param {JQuery} countrySelect - the country select element
 * @param {JQuery} radiusInput - the input field for the radius
 */
const onChangeLocationSelect = async (locationSelect, countryWrapper, radiusWrapper, countrySelect, radiusInput) => {
    $("#js-option-group-wr").html("");
    countrySelect.val("").addClass("validate[required]");
    radiusInput.val("");

    switch (locationSelect.val()) {
        case "country":
            if (!isInitSelectCountriesListeners) {
                await initSelectCountriesListeners(countrySelect);
                isInitSelectCountriesListeners = true;
            }

            EventHub.trigger("b2b-form:init-select-countries-listeners");
            radiusWrapper.addClass("display-n_i");
            radiusInput.prop("disabled", true);
            countrySelect.prop("disabled", false);
            countryWrapper.removeClass("display-n_i");
            break;
        case "radius":
            countryWrapper.addClass("display-n_i");
            countrySelect.prop("disabled", true);
            radiusInput.prop("disabled", false);
            radiusWrapper.removeClass("display-n_i");
            break;
        default:
            countrySelect.prop("disabled", true);
            radiusInput.prop("disabled", true);
            countryWrapper.addClass("display-n_i");
            radiusWrapper.addClass("display-n_i");
            break;
    }
};

/**
 * It removes the option from the DOM
 * @param {JQuery} option - the option that will be deleted
 */
const deleteOptionGroup = async option => {
    const optionIdx = selectedOptions.indexOf(option.data("option"));

    if (optionIdx !== -1) {
        selectedOptions.splice(optionIdx, 1);
        option.remove();
    }
};

export default () => {
    const locationSelect = $("#js-b2b-request-location-select");
    const countryWrapper = $("#js-b2b-request-country-wrapper");
    const radiusWrapper = $("#js-b2b-request-radius-wrapper");
    const countrySelect = $("#js-b2b-request-country-select");
    const radiusInput = $("#js-b2b-request-radius-input");

    // region listeners
    EventHub.on("b2b-form:locate-partner.change", () => {
        onChangeLocationSelect(locationSelect, countryWrapper, radiusWrapper, countrySelect, radiusInput);
    });

    EventHub.on("option-group:delete", async (_e, btn) => {
        const option = btn.parent();
        await deleteOptionGroup(option);

        if (!selectedOptions.length) {
            countrySelect.addClass("validate[required]");
        }
    });

    locationSelect
        .on("mousedown", function onFocus() {
            locationSelect.data("value", locationSelect.val());
        })
        .on("change", function onChange(e) {
            e.preventDefault();

            if (locationSelect.data("value")) {
                confirmChangeLocationType(locationSelect);
            } else {
                onChangeLocationSelect(locationSelect, countryWrapper, radiusWrapper, countrySelect, radiusInput);
            }
        });

    if (locationSelect.val() === "country") {
        initSelectCountriesListeners(countrySelect);
    }
    // endregion listeners
};
