/* eslint-disable camelcase */
/* eslint-disable no-underscore-dangle */
const translate = function ({ plug = null, text = null, replaces = null }) {
    const i18n = globalThis.__i18n_vocabulary || {};
    const domain = i18n[plug] || {};
    let message = domain[globalThis.__site_lang] ? domain[globalThis.__site_lang][text] : domain[text];

    if (replaces) {
        Object.keys(replaces).forEach(key => {
            if (Object.prototype.hasOwnProperty.apply(replaces, [key])) {
                message = message.replace(key, replaces[key]);
            }
        });
    }

    return message;
};

const i18nDomain = function ({ plug = null }) {
    const i18n = globalThis.__i18n_vocabulary || {};
    const domain = i18n[plug];

    return domain || {};
};

export { translate };
export { i18nDomain };
export { translate as translate_js };
export { i18nDomain as translate_js_one };
