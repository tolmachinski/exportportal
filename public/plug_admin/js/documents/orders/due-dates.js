/* eslint-disable */
var ExtendDueDateModule = (function () {
    "use strict";

    /**
     * Check if dates are in order
     *
     * @param {any} recipients
     */
    function checkSortedDueDate(recipients) {
        var filteredDates = [];
        Object.keys(recipients).forEach(function(key) {
            if (recipients[key]['expiresAt'] !== "") {
                filteredDates.push(recipients[key]['expiresAt']);
            }
        });

        for (let key = 1; key < filteredDates.length; key++){
            if(new Date(filteredDates[key-1]).getTime() > new Date(filteredDates[key]).getTime()){
                return false;
            }
        }

        return true;
    }

    /**
     * Send request to change due dates
     *
     * @param {URL} url
     * @param {number} envelopeId
     * @param {HTMLElement} recpientsTable
     * @param {HTMLElement} datepickerSelector
     */
    function saveDueDates(url, envelopeId, recpientsTable, datepickerSelector) {

        if($(recpientsTable).find(datepickerSelector).length == 0){
            systemMessages("No recipients found", "warning");

            return;
        }

        var newDueDatesData = [];
        $(recpientsTable).find(datepickerSelector).each(function(val){
            var element = {};
            element.id = $(this).data('id');
            element.expiresAt = $(this).val();
            newDueDatesData.push(element);
        });

        if(!checkSortedDueDate(newDueDatesData)){
            systemMessages("The due dates are not ordered right in the list", "warning");

            return;
        }

        showLoader($(recpientsTable).parent());

        // @ts-ignore
        return postRequest(url, {'dates': JSON.stringify(newDueDatesData), 'envelope': envelopeId}, "json")
            .then(function (data) {
                systemMessages(data.message, data.mess_type);
                if (data.mess_type === "success") {
                    closeFancyBox();
                }
            })
            .catch(function (e) {
                onRequestError(e);
            })
            .finally(function () {
                hideLoader($(recpientsTable).parent());
            });
    }

    /**
     * Module entrypoint.
     *
     * @param {number} envelopeId
     * @param {{[x: string]: string }} selectors
     * @param {string} saveUrl
     */
    function entrypoint(envelopeId, selectors, saveUrl, maxDays) {
        var selectorsList = selectors || {};
        var datepickerSelector = selectorsList.datepicker;
        var recpientsTable = selectorsList.table;

        createDatepicker(datepickerSelector, {
            'minDate': new Date(),
            'maxDate': maxDays + "d"
        });

        mix(
            window,
            {
                modalFormCallBack: function () {
                    saveDueDates(new URL(saveUrl), envelopeId, recpientsTable, datepickerSelector);
                }
            },
            false
        );
    }

    return { default: entrypoint };
})();
