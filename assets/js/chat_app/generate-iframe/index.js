/**
 * Normalizes the element ID value
 *
 * @param {string} id
 * @returns {string}
 */
const normalizeElementId = id => (id.charAt(0) === "#" ? id.slice(1) : id);

/**
 * @param {string} link
 * @param {string} id
 * @param {array} classes
 * @param {string} appendId
 */
const generateIframe = (link, id, classes, appendId = null) => {
    const chatId = normalizeElementId(id);
    const containerId = appendId ? normalizeElementId(appendId) : null;
    let appendWr = document.body;
    if (containerId !== null && document.getElementById(containerId)) {
        appendWr = document.getElementById(containerId);
    }
    // eslint-disable-next-line consistent-return
    return new Promise(resolve => {
        if (document.getElementById(chatId)) {
            return resolve(document.getElementById(chatId));
        }

        const frame = document.createElement("iframe");
        frame.setAttribute("frameBorder", "0");
        frame.src = `${link}?domain=${origin}`;
        frame.id = chatId;
        frame.onload = () => {
            resolve(document.getElementById(chatId));
        };
        classes.forEach(c => frame.classList.add(c));
        appendWr.appendChild(frame);
    });
};

export default generateIframe;
