import $ from "jquery";

/**
 * Loads the scrips by URL and puts it on the page. Promisified version of $.getScript().
 *
 * @param {string|URL} src
 * @param {boolean} cache
 *
 * @see {@link jQuery.getScript}
 *
 * @returns {Promise<any>}
 */
const getScript = function getScript(src, cache) {
    const isCached = typeof cache !== "undefined" ? Boolean(~~cache) : false;
    const hasGlobalCache = $.ajaxSetup({}).cache; // Remember global cache

    return new Promise((resolve, reject) => {
        $.ajaxSetup({ cache: isCached }); // Override global cache
        $.getScript(src.toString())
            .fail((xhr, status, error) => {
                const failure = { message: error, status, data: xhr.responseJSON ? xhr.responseJSON.data || null : null, isCustom: true, xhr };
                reject(failure);
            })
            .done((response, status, xhr) => {
                if (status !== "success") {
                    const failure = { message: "Failed to load the script", status, data: null, isCustom: true, xhr };
                    reject(failure);

                    return;
                }

                resolve(response);
            });

        $.ajaxSetup({ cache: hasGlobalCache }); // Restore global cache
    });
};

export default getScript;
