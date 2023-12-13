import $ from "jquery";

// Create the defaults, only once!
var defaults = {
    childCheck: false,
    parentCheck: false,
};

// The actual plugin constructor
class LimitedSelect {
    constructor(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this.init();
    }
    init() {
        var _ = this;

        _.initPlug();
        _.initListiners();
    }
    initPlug() {
        var _ = this;
        var el = $(_.element);

        _.options.$buyerSelectIndustries = el.find(".js-buyer-select-industries-select");
        _.options.$buyerSelectIndustriesForm = el.find(".js-buyer-select-industries-form");
        _.options.removeClass = "js-remove-buyer-select-industries";
    }
    initListiners() {
        var _ = this;
        var el = $(_.element);

        this.options.$buyerSelectIndustries.change(function () {
            var $this = $(this);
            var value = $this.val();
            var disabledOption = $this.find("option[disabled]");
            var disabledTotal = disabledOption.length;

            if (disabledTotal < 4) {
                var optionText = $this.find("option:selected").text();

                _.options.$buyerSelectIndustriesForm
                    .show()
                    .append(
                        '<li class="list-group-item register-industry-select__item">\
                    <span class="register-industry-select__inner">\
                        <input type="hidden" name="' +
                            _.options.inputName +
                            '[]" value="' +
                            value +
                            '">\
                        <span class="register-industry-select__name">' +
                            optionText +
                            '</span>\
                        <span class="ep-icon ep-icon_remove-stroke ' +
                            _.options.removeClass +
                            ' cur-pointer" data-callback="removeBuyerSelectIndustries" data-industry="' +
                            value +
                            '"></span>\
                    </span>\
                </li>'
                    );

                $this.find("option[value=" + value + "]").prop("disabled", true);

                if (disabledTotal == 3) {
                    $this.fadeOut("fast");
                }
            }

            $this.find("option").first().prop("selected", true);
        });

        el.on("click", "." + _.options.removeClass, function (e) {
            e.preventDefault();
            var $this = $(this);

            _.removeBuyerSelectIndustries($this);
        });
    }
    removeBuyerSelectIndustries($this) {
        var _ = this;
        var industry = $this.data("industry");

        $this.closest(".list-group-item").fadeOut("slow", function () {
            $(this).remove();
            _.options.$buyerSelectIndustries.find('option[value="' + industry + '"]').prop("disabled", false);
            var disabledOption = _.options.$buyerSelectIndustries.find("option[disabled]");

            if (disabledOption.length == 1) {
                _.options.$buyerSelectIndustriesForm.hide();
            }

            if (disabledOption.length == 3) {
                _.options.$buyerSelectIndustries.fadeIn();
            }
        });
    }
}

export default () => {
    $.fn.extend({
        limitedSelect: function (options) {
            // @ts-ignore
            return this.each(function () {
                if (!$.data(this, "limitedSelect")) {
                    $.data(this, "limitedSelect", new LimitedSelect(this, options));
                }
            });
        },
    });
};
