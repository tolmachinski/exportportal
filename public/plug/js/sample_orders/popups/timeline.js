var SampleOrderTimeline = (function (global) {
    "use strict";

    //#region Variables
    /**
     * @typedef {{form: ?string, timeline: string}} Selectors
     * @typedef {{form: ?JQuery, timeline: ?JQuery}} JQueryElements
     */

    /**
     * @type {JQueryElements}
     */
    var defaultElements = { form: null, timeline: null };

    /**
     * @type {Selectors}
     */
    var defaultSelectors = { form: null, timeline: null };

    var TIMELINE_LAYOUT_CLASSES = "main-data-table--mobile order-detail__table--mobile";
    var TIMELINE_LAYOUT_TRESHOLD = 768;
    //#endregion Variables

    //#region Utility
    /**
     * Dispatches the listeners.
     *
     * @param {JQueryElements} elements
     * @param {Selectors} selectors
     */
    function dispatchListeners(elements, selectors) {
        var hasLodash = typeof _ !== "undefined";
        var resizeEvent = hasLodash ? "resize" : "resizetop";

        $(global).on(resizeEvent, onContentResize(resizeEvent, hasLodash, elements));
    }

    /**
     * Adds mobile support.
     *
     * @param {JQuery} wrapper
     * @param {JQuery} timeline
     */
    function addMobileSupport(wrapper, timeline) {
        [wrapper.find(timeline)].forEach(function (table) {
            if (table.length) {
                updateTimelineLayout(table);
                mobileDataTable(table);
            }
        });
    }

    /**
     * Updated items table.
     *
     * @param {JQuery} timeline
     */
    function updateTimelineLayout(timeline) {
        if (!timeline || !timeline.length) {
            return;
        }

        if (widthLessThan(TIMELINE_LAYOUT_TRESHOLD)) {
            timeline.addClass(TIMELINE_LAYOUT_CLASSES);
        } else {
            timeline.removeClass(TIMELINE_LAYOUT_CLASSES);
        }
    }
    //#endregion Utility

    //#region Handlers
    /**
     * Handles the resize event
     *
     * @param {string} resizeEvent
     * @param {boolean} hasLodash
     * @param {JQueryElements} elements
     */
    function onContentResize(resizeEvent, hasLodash, elements) {
        var onResize = function () {
            if (!$.contains(global.document, elements.form.get(0))) {
                $(global).off(resizeEvent, onResize);

                return;
            }

            updateTimelineLayout(elements.timeline);
        };
        if (hasLodash) {
            onResize = _.debounce(onResize, 250);
        }

        return onResize;
    }
    //#endregion Save

    //#region Module
    return {
        default: function (params) {
            /** @type Selectors */
            var selectors = Object.assign({}, defaultSelectors, params.selectors || {});
            /** @type JQueryElements */
            var elements = Object.assign({}, defaultElements, findElementsFromSelectors(selectors, Object.keys(defaultElements)));

            dispatchListeners(elements, selectors);
            addMobileSupport(elements.form, elements.timeline);
        },
    };
    //#endregion Module
})(globalThis);
