/**
 * Opens new window.
 *
 * @param {string|URL} [mylink]
 * @param {string} [title]
 * @param {any} [options]
 */
const winPopup = function (mylink, title, options) {
    // open the window with blank url
    const mywin = globalThis.open(mylink.toString(), title, options);
    mywin.focus();

    // return the window
    return mywin;
};

export default winPopup;
