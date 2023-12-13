import $ from "jquery";
import throttle from "lodash/throttle";

import { isSafari } from "@src/util/platform";
import { AUTOCOMPLETE_ENTRIES_MAX_AMOUNT, AUTOCOMPLETE_ENTRIES_SHOWN_AMOUNT, DEBUG, GROUP_SITE_URL } from "@src/common/constants";
import findElementsFromSelectors from "@src/util/common/find-elements-from-selectors";
import postRequest from "@src/util/http/post-request";
import getRequest from "@src/util/http/get-request";
import htmlEscape from "@src/util/common/html-escape";
import EventHub from "@src/event-hub";
import Storage, { types } from "@src/plugins/autocomplete/history-storage";
import delay from "@src/util/async/delay";

// Styles
import "@scss/components/navbar-search-form/autocomplete.scss";

/**
 * @typedef {Object} CustomElements
 * @property {JQuery} form
 * @property {JQuery} input
 * @property {JQuery} wrapper
 * @property {JQuery} container
 * @property {JQuery} recentSearchList
 * @property {JQuery} suggestionsList
 * @property {JQuery} suggestionsListItem
 * @property {JQuery} suggestionsOption
 * @property {JQuery} resetBtn
 */

let getSuggestions = false;
let autocompleteEntriesShownCount = 5;
let autocompleteEntriesMaxAmount = 5;
let activeElement = null;
let staticMode = true;
let originalMode = true;
let searchUrl = null;
let storage = null;

/** @type {CustomElements} */
let elements = {
    form: null,
    input: null,
    wrapper: null,
    container: null,
    recentSearchList: null,
    suggestionsList: null,
    suggestionsListItem: null,
    suggestionsOption: null,
    resetBtn: null,
};

/**
 * Get completion type for input
 */
const getCompletionType = () => {
    const inputType = elements.input.data("autocomplete-type") || null;
    const completionType = types[inputType] || null;

    if (!completionType) {
        throw new TypeError("This type of autocompletion is not supported");
    }

    return completionType;
};

/**
 * If the list is enabled, show the list.
 */
const showList = () => {
    elements.container.show();
};

/**
 * It hides the list if it's enabled.
 */
const hideList = () => {
    elements.container.hide();
};

/**
 * If the reset button doesn't have the class "active", add it.
 */
const showResetBtn = () => {
    if (!elements.resetBtn.hasClass("active")) {
        elements.resetBtn.addClass("active");
    }
};

/**
 * If the reset button has the class "active", remove the class "active"
 */
const hideResetBtn = () => {
    if (elements.resetBtn.hasClass("active")) {
        elements.resetBtn.removeClass("active");
    }
};

/**
 * It removes all the suggestions from the suggestions list and removes the active class from the
 * suggestions list
 */
const clearSuggestionsList = () => {
    elements.suggestionsList.empty().parent().removeClass("active");
};

/**
 * It sends a POST request to the server, and if the response is successful, it appends the response
 * content to the suggestions list
 * @param {JQuery} form
 */
const getSuggestionsList = async form => {
    const data = form.serialize();
    const url = form.data("suggestionsUrl");

    try {
        const content = await postRequest(url, data, "html");

        if (content && getSuggestions) {
            elements.suggestionsList.html(content).parent().addClass("active");

            showList();
        } else {
            clearSuggestionsList();
        }

        return true;
    } catch (error) {
        if (DEBUG) {
            // eslint-disable-next-line no-console
            console.error(error);
        }

        return false;
    }
};

/**
 * It changes the completion type of the input
 */
const changeInput = () => {
    storage.changeType(getCompletionType());
};

/**
 * Reads the state from remote storage
 * @param {string} text - The text to search for.
 * @returns {Promise<{read: array, meta: any}>}
 */
const getHistoryList = async text => {
    const emptyResponse = { read: [], meta: {} };
    const url = searchUrl || new URL(GROUP_SITE_URL);

    if (!text) {
        return emptyResponse;
    }

    url.searchParams.set("q", text || "");

    try {
        const response = await getRequest(url);

        return { read: response.list || [], meta: response.meta || {} };
    } catch (error) {
        if (DEBUG) {
            // eslint-disable-next-line no-console
            console.error(error);
        }

        return emptyResponse;
    }
};

/**
 * It makes a request to the given URL and returns true if the request was successful, and false
 * otherwise
 * @returns A function that takes a url as an argument and returns a boolean.
 */
const removeHistoryItem = async url => {
    try {
        await getRequest(new URL(url), "text");

        return true;
    } catch (error) {
        if (DEBUG) {
            // eslint-disable-next-line no-console
            console.error(error);
        }

        return false;
    }
};

/**
 * It fetches the history search results from the storage
 * @param {JQuery} node - The input element that the user is typing in.
 * @returns An array of the last 5 entries in the history list.
 */
const fetchHistorySearch = async node => {
    const searchQuery = String(node.val()).trim() || "";

    try {
        const result = staticMode ? await storage.get(searchQuery) : await getHistoryList(searchQuery);

        return (result.read || []).slice(0, autocompleteEntriesShownCount);
    } catch (error) {
        if (DEBUG) {
            // eslint-disable-next-line no-console
            console.error(error);
        }

        return [];
    }
};

/**
 * It takes a list of rendered HTML elements and appends them to the autocomplete list
 */
const fillAutocompleteList = renderedList => {
    elements.recentSearchList.empty().parent().hide();

    if (renderedList.length) {
        elements.recentSearchList.append(renderedList).parent().show();
    }
};

/**
 * It creates a list item for the search history
 * @returns A jQuery object
 */
const createHistorySearchItem = record => {
    return $(
        `<li class="autocomplete-recent-search-list__item" role="presentation">
            <button
                class="autocomplete-recent-search-list__text js-search-autocomplete-recent-search-option call-action"
                data-js-action="autocomplete:history-item.click"
                type="button"
                role="option"
            >
                {{text}}
            </button>
            <button
                class="autocomplete-recent-search-list__remove-btn call-action"
                data-js-action="autocomplete:history-item.remove"
                type="button"
            >
                <i class="ep-icon ep-icon_remove-stroke"></i>
            </button>
        </li>`.replace("{{text}}", record[4] ?? htmlEscape(record[0].trim() || ""))
    );
};

/**
 * It fetches the history search records from the storage, renders them as HTML elements, and then
 * fills the autocomplete list with them
 * @param {JQuery} node - The input element that the user is typing in.
 */
const renderAutocompleteList = async node => {
    const list = await fetchHistorySearch(node);
    const renderedList = list.map(record => createHistorySearchItem(record));

    await fillAutocompleteList(renderedList);

    if (elements.recentSearchList.children().length && node.closest("form").data("type") === "items") {
        showList();
    } else if (!elements.suggestionsList.children().length) {
        hideList();
    }
};

/**
 * It renders the autocomplete list when the user focuses on the input field
 * @param {JQuery} node - The input element that the user is typing in.
 * the user or by the script.
 */
let isFocused = false;
const onFocus = async function (node) {
    if (!isFocused) {
        return;
    }

    const nodeVal = String(node.val()).trim();
    const isInternalEvent = Boolean(~~node.data("internalFocus") || 0);

    if (!isInternalEvent && node.closest("form").data("type") === "items") {
        staticMode = true;

        changeInput();
        await renderAutocompleteList(node).then(() => {
            staticMode = originalMode;
        });
    } else {
        $(elements.recentSearchList).empty().parent().hide();
    }

    if (nodeVal && nodeVal.length > 2) {
        await getSuggestionsList(node.closest("form"));
    }

    node.data("internalFocus", null);
};

/**
 * It's an event handler for the input event on the search input
 */
const onInput = async function () {
    await delay(100);

    const node = $(this);
    const nodeVal = String(node.val()).trim();

    getSuggestions = false;

    if (nodeVal) {
        showResetBtn();

        if (nodeVal.length > 2) {
            getSuggestions = true;
            await getSuggestionsList(node.closest("form"));
        } else {
            clearSuggestionsList();
        }
    } else {
        hideResetBtn();
        clearSuggestionsList();
    }

    if (node.is(":focus") && node.closest("form").data("type") === "items") {
        await renderAutocompleteList(node);
        changeInput();
    } else {
        $(elements.recentSearchList).empty().parent().hide();
    }

    if (!elements.recentSearchList.children().length && !elements.suggestionsList.children().length) {
        hideList();
    }
};

/**
 * Check if the user clicks outside of the list, hide the list
 * @param e - The event object
 * @param {JQuery} node - The input element that the user is typing in.
 */
const onFocusOut = function (e, node) {
    const wrapper = node.closest(elements.wrapper);
    const activeElementSelector = e.relatedTarget || activeElement || node.find(":focus");
    let currentActiveElement = $(e.relatedTarget || activeElement || node.find(":focus"));

    if (!currentActiveElement.length) {
        currentActiveElement = !isSafari() ? currentActiveElement : null;
    }

    if (currentActiveElement === null || !wrapper.has(activeElementSelector).length) {
        hideList();
    }

    activeElement = null;
};

/**
 * It handles the click event on a suggestion item
 * @param e - The event object.
 * @param {string} suggestionsOptionSelector - The selector for the element that contains the text to be used as
 * the value of the input.
 */
const onClickSugestionItem = (e, suggestionsOptionSelector) => {
    if (e.target.tagName.toLowerCase() === "a") {
        globalThis.location.href = $(e.target).attr("href");
        return;
    }

    elements.input.val($(e.currentTarget).find(suggestionsOptionSelector).text().trim());
    hideList();

    $(e.target).closest("form").trigger("submit");
};

/**
 * It takes a record from the history list and puts it in the input field
 */
const onClickHistoryItem = record => {
    const text = record[0].trim() || "";

    elements.input.val(text);
    hideList();
    $(elements.form).trigger("submit");
};

/**
 * It removes a history item from the storage and the DOM
 */
const onRemoveHistoryItem = async record => {
    await storage.remove(record);

    removeHistoryItem(record[3].dl);
    changeInput();
    renderAutocompleteList(elements.input);

    elements.input.trigger("focus");
};

/**
 * It clears the suggestions list, clears the input field, and hides the reset button
 */
const onClickResetBtn = () => {
    clearSuggestionsList();
    if ($(".js-search-autocomplete-recent-search-option").length === 0) {
        $(".js-search-autocomplete-container").css("display", "none");
    }
    elements.input.val("").trigger("focus");
    hideResetBtn();
};

/**
 * It disables the list and changes the input field
 */
const onSubmitForm = node => {
    const searchText = node.val() || null;

    if (searchText) {
        changeInput();
    }
};

/**
 * If the user clicks anywhere on the page, and the target of the click is not the wrapper or the
 * input, then hide the list
 */
const onClickDocument = e => {
    const target = $(e.target);
    const wrapper = elements.wrapper ? target.closest(elements.wrapper) : elements.input.parent();

    if (!wrapper.is(target) && !wrapper.has(e.target)) {
        hideList();
    }
};

/**
 * @property {string} [type]
 * @property {Boolean} [clear]
 * @property {Object} [records]
 * @property {string|URL} [url]
 * @property {Selectors} [selectors]
 * @property {number} [entriesShownCount]
 * @property {number} [entriesMaxAmount]
 * @property {Boolean} [isStatic]
 */
export default async function initAutocomplete(params) {
    const { type, clear, records, url, selectors, entriesShownCount = 5, entriesMaxAmount = 5, isStatic = true } = params;

    elements = { ...findElementsFromSelectors(selectors, Object.keys(selectors)) };

    if (selectors.input === null || elements.input === null) {
        throw new TypeError("The search input is required");
    }
    if (selectors.container === null || elements.container === null) {
        throw new TypeError("The autocomplete container is required");
    }
    if (elements.form === null) {
        elements.form = elements.input.closest("form");
    }

    staticMode = Boolean(~~isStatic);
    originalMode = Boolean(~~isStatic);
    autocompleteEntriesShownCount = entriesShownCount || AUTOCOMPLETE_ENTRIES_SHOWN_AMOUNT;
    autocompleteEntriesMaxAmount = entriesMaxAmount || AUTOCOMPLETE_ENTRIES_MAX_AMOUNT;

    if (url) {
        searchUrl = url instanceof URL ? url : new URL(url);
    }

    // #region Prepend onload
    const insetClear = Boolean(~~(clear || 0));
    const insetRecords = records || [];
    const insetRecordsType = types[type || null] || null;
    const storageKey = Storage.getStorageKey(insetRecordsType);
    const typeStorage = new Storage(storageKey, autocompleteEntriesMaxAmount, insetRecordsType);

    (insetClear ? typeStorage.clear() : Promise.resolve()).then(() => {
        if (insetRecords.length) {
            typeStorage.prepend(insetRecords);
        }
    });
    // #endregion Prepend onload

    storage = new Storage(storageKey, autocompleteEntriesMaxAmount);

    // #region Dispatch listners
    $(document).on("click", e => onClickDocument(e));
    elements.container.on("mousedown", function onClickContainer() {
        activeElement = $(this);
    });
    elements.input
        .on("focus", async function onFocusAutocompleteInput() {
            isFocused = true;

            await delay(50);
            onFocus($(this));
        })
        .on("input", throttle(onInput, 200))
        .on("focusout", function onFocusOutAutocompleteInput(e) {
            e.stopImmediatePropagation();
            isFocused = false;

            onFocusOut(e, $(this));
        });

    EventHub.on("autocomplete:history-item.click", (_e, btn) => {
        /* Finding the record that matches the button text. */
        const thisRecord = records.find(record => record[0].trim() === btn.text().trim());

        onClickHistoryItem(thisRecord);
    });
    EventHub.on("autocomplete:history-item.remove", (e, btn) => {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        /* Finding the record that matches the text of the button's parent's recent search option. */
        const thisRecord = records.find(record => record[0].trim() === btn.parent().find(selectors.recentSearchOption).text().trim());

        onRemoveHistoryItem(thisRecord);
    });
    EventHub.on("autocomplete:suggestions-item.click", (...args) => {
        const [, , , realEvent] = args;

        onClickSugestionItem(realEvent, selectors.suggestionsOption);
    });
    EventHub.on("autocomplete:form.submit", (_e, form) => onSubmitForm(form.find(selectors.input)));
    EventHub.on("autocomplete:reset-btn.click", () => onClickResetBtn());
    // #endregion Dispatch listners
}
