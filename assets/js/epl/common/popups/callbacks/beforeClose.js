import $ from "jquery";

const beforeClose = instance => {
    // @ts-ignore
    if ($.fn.validate) {
        // @ts-ignore
        const validator = instance.current.$content.find("form").validate();

        if (validator) {
            validator.destroy();
        }
    }
};

export default beforeClose;
