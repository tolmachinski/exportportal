import tinymce from "tinymce";
import "tinymce/plugins/autolink";
import "tinymce/plugins/link";
import "tinymce/plugins/lists";
import "tinymce/plugins/fullpage";
import "tinymce/plugins/image";
import "tinymce/themes/modern";

import "@src/plugins/tinymce/plugins";

/**
 * Create new TinyMCE editor.
 *
 * @param {string} selector
 * @param {boolean} removeOnStart
 *
 * @returns {Promise<any[]>}
 */
export default async function createEditor(selector, options = {}, removeOnStart = true) {
    if (typeof selector !== "string") {
        throw new TypeError("The selector must be of the type 'string'.");
    }
    if (removeOnStart) {
        // @ts-ignore
        tinymce.remove(selector);
    }

    // Get editor options
    const {
        font = "Roboto, Segoe UI, Arial, sans-serif",
        height = 300,
        plugins = ["autolink lists link image charactercount"],
        toolbar = "styleselect | bold italic underline | link | numlist bullist",
        menubar = false,
        statusbar = true,
        branding = false,
        resize = false,
        // eslint-disable-next-line camelcase
        init_instance_callback = false,
    } = options;

    // @ts-ignore
    return tinymce.init({
        selector,

        // Styles
        skin_url: "/public/build/plugins/tinymce/skins/lightgray",

        // Misc configurations
        // @ts-ignore, eslint-disable-next-line camelcase
        fullpage_default_font_family: font,
        // eslint-disable-next-line camelcase
        style_formats: [
            { title: "H3", block: "h3" },
            { title: "H4", block: "h4" },
            { title: "H5", block: "h5" },
            { title: "H6", block: "h6" },
        ],

        // Other options
        height,
        resize,
        plugins,
        toolbar,
        menubar,
        branding,
        statusbar,
        // eslint-disable-next-line camelcase
        init_instance_callback,
    });
}
