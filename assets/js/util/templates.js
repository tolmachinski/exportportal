/* eslint-disable no-cond-assign */
/* eslint-disable no-continue */
import dotIndex from "@src/util/common/dot-index";

/**
 * Renders the template.
 *
 * @param {string} template
 * @param {any} [context]
 * @param {any} [partials]
 *
 * @returns {string}
 */
const renderTemplate = function (template, context = {}, partials = {}) {
    let match;
    let interpolatedTemplate = template;
    const pattern = /\{\{([a-zA-Z0-9\.]+)\}\}/gi;
    const partialsPattern = /\{\{> ([a-zA-Z0-9]+)\}\}/gi;

    while ((match = pattern.exec(template)) !== null) {
        if (!match[1]) continue;

        const key = match[1];
        const value = dotIndex(context, key);
        if (typeof value !== "undefined") {
            interpolatedTemplate = interpolatedTemplate.replace(`{{${key}}}`, value);
        }
    }

    while ((match = partialsPattern.exec(interpolatedTemplate)) !== null) {
        if (!match[1]) continue;

        const key = match[1];
        const value = dotIndex(partials, key);
        if (value && typeof value.template === "string") {
            interpolatedTemplate = interpolatedTemplate.replace(`{{> ${key}}}`, renderTemplate(value.template, value.context, value.partials));
        } else {
            throw new Error(`The partial "${key}" is not found.`);
        }
    }

    return interpolatedTemplate;
};

// eslint-disable-next-line import/prefer-default-export
export { renderTemplate };
