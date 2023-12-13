import $ from "jquery";

import { SITE_URL } from "@src/common/constants";
import EventHub from "@src/event-hub";

class SelectVariantImages {
    constructor(params) {
        this.global = globalThis;
        this.title = params.title;
        this.elements = {};
        this.$wrSelect = $(".js-select-variant-images");
        this.btnUpload = ".select-variant-images__btn-upload";
        this.select = ".select-variant-images__selected";
        this.option = ".select-variant-images__option";
        this.$select = this.$wrSelect.find(this.select);
        this.$dropdown = this.$wrSelect.find(".select-variant-images__dropdown");
        this.$dropdownInner = this.$wrSelect.find(".select-variant-images__dropdown-inner");

        this.init();
    }

    init() {
        const that = this;

        that.$wrSelect.on("click", that.select, () => {
            if ($("#js-add-item-variants-wr").height() > 140) {
                that.$dropdown.addClass("select-variant-images__dropdown--bottom");
            } else {
                that.$dropdown.removeClass("select-variant-images__dropdown--bottom").css({ top: `${-that.$dropdown.outerHeight()}px` });
            }

            that.$dropdown.toggle();
        });

        that.$wrSelect.on("click", that.btnUpload, function btnUpload(e) {
            e.preventDefault();
            that.global.popupAddItem.showTab(4);
        });

        that.$wrSelect.on("click", that.option, function optionItem(e) {
            e.preventDefault();
            const $this = $(this);
            const image = $this.data("image");
            const src = $this.find(".image").attr("src");
            const $select = that.$select.find(".image");

            $this.addClass("active").siblings().removeClass("active");

            $select.data("image", image).attr("src", src);

            if (image === "main") {
                $select.addClass("js-add-item-change-main-photo");
            } else {
                $select.removeClass("js-add-item-change-main-photo");
            }
        });

        $("body").on("click", function closeByBodyClick(e) {
            const $choice = that.$wrSelect;

            if ($(e.target)[0] === $choice[0] || $(e.target).parents(".js-select-variant-images")[0] === $choice[0]) {
                return;
            }

            if (
                ($(e.target)[0] === that.$dropdown[0] || $(e.target).parents(".select-variant-images__dropdown")[0] !== that.$dropdown[0]) &&
                that.$dropdown.is(":visible")
            ) {
                that.$dropdown.hide();
            }
        });

        EventHub.off("select-variant-images:remove-option");
        EventHub.on("select-variant-images:remove-option", (e, button) => that.removeOption(button));
        globalThis.removeEventListener("select-variant-images:remove-option", () => {});
        globalThis.addEventListener("select-variant-images:remove-option", button => that.removeOption(button), {});

        EventHub.off("select-variant-images:uppend-option");
        EventHub.on("select-variant-images:uppend-option", (e, button) => that.uppendOption(button));
        globalThis.removeEventListener("select-variant-images:uppend-option", () => {});
        globalThis.addEventListener("select-variant-images:uppend-option", button => that.uppendOption(button));
    }

    uppendOption(button) {
        const that = this;
        const path = button?.detail?.path ?? "";
        const name = button?.detail?.name ?? "";
        that.$dropdownInner.append(`<div\
                class="select-variant-images__option image-card3"\
                data-image="${name}"\
            >\
                <span class="link">\
                    <img\
                        class="image"\
                        src="${path}"\
                        alt="${that.title}"\
                    >\
                </span>\
            </div>`);
    }

    removeOption(button) {
        const that = this;
        const name = button?.detail?.name ?? "";

        const $optionRemove = that.$dropdownInner.find(`.select-variant-images__option[data-image="${name}"]`);
        const src = `${SITE_URL}public/img/no_image/group/noimage-other.svg`;
        const image = "noimage-other.svg";

        if (!$optionRemove.hasClass("active")) {
            $optionRemove.remove();
        } else {
            if (that.$dropdownInner.find(".select-variant-images__option").length > 1) {
                that.$dropdownInner.find(".select-variant-images__option:nth-child(1)").trigger("click");
            } else {
                that.$select.find(".image").data("image", image).attr("src", src);
            }

            $optionRemove.remove();
        }
    }
}

export default params => {
    // eslint-disable-next-line no-new
    return new SelectVariantImages(params);
};
