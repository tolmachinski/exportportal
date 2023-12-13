import $ from "jquery";

$.fn.extend({
    setValHookType(type) {
        // @ts-ignore
        this.each(function setType() {
            this.type = type;
        });

        return this;
    },
});
