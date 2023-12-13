var key = $('.js-template-content-json .js-content-data-item').length;

var addEmailElements = function ($this) {
    var content = $('.js-template-content-json');
    var select = $('.js-select-email-elements');
    var templateContentJsonElement = $('#js-template-content-json-element');
    var templateContentJsonElementText = templateContentJsonElement.text();
    var selectValue = select.val();
    var html = "";
    var params = "";
    var selected;

    $.each(emailElements, function(index, value) {
        if(index === selectValue){
            selected = value;
        }
    });

    if ( selected === undefined ){
        select.prop('selectedIndex', 0);
        systemMessages("Select other element.", "warning");
        return true;
    }

    if (selected.used === "once") {
        select.prop('selectedIndex', 0);
        select.find(`option[value=${selected.name}]`).prop('disabled', true);
    }

    if (selected.params !== undefined && Object.keys(selected.params).length) {
        var templateContentJsonElementParams = $('#js-template-content-json-element-parameter');
        var templateContentJsonElementParamsText = templateContentJsonElementParams.text();
        var keyParam = 0;

        $.each(selected.params, function(index, value) {
            var input,
                inputName = `content_template_data[${key}][params][${index}]`;

            switch(value.type) {
                case 'textarea':
                    input = `<textarea class="w-70pr mnh-95" type="text" name="${inputName}"></textarea>`;
                    break;
                case 'radio':
                    input = `<div class="w-70pr lh-30">
                                <label><input type="radio" name="${inputName}" value="1" checked> Yes</label>
                                <label><input type="radio" name="${inputName}" value="0"> No</label>
                            </div>`;
                    break;
                default:
                    input = `<input class="w-70pr" type="text" name="${inputName}" value="">`;
                    break;
            }

            params += templateContentJsonElementParamsText
                .replace(new RegExp("{{paramName}}", "g"), index)
                .replace(new RegExp("{{input}}", "g"), input);

            keyParam += 1;
        });
    }

    html = templateContentJsonElementText
        .replace(new RegExp("{{elementDisplayName}}", "g"), selected.displayName)
        .replace(new RegExp("{{elementName}}", "g"), selectValue)
        .replace(new RegExp("{{key}}", "g"), key)
        .replace(new RegExp("{{params}}", "g"), params);

    content.append(html);
    key += 1;

    $.fancybox.reposition();
}

var upContentTemplateData = function ($this) {
    var el = $this.closest(".js-content-data-item");

    if (!el.prev().length) {
        systemMessages("No prev elements.", "warning");
    } else {
        el.slideUp( function() {
            el.insertBefore(el.prev()).slideDown();
        } );
    }
}

var downContentTemplateData = function ($this) {
    var el = $this.closest(".js-content-data-item");

    if(!el.next().length){
        systemMessages("No next elements.", "warning");
    } else {
        el.slideUp( function() {
            el.insertAfter(el.next()).slideDown();
        } );
    }
}

var removeContentTemplateData = function ($this) {
    var item = $this.closest(".js-content-data-item");
    var element = item.data("element");
    var option = $(`.js-select-email-elements option[value=${element}][data-used="once"]`);

    if (option.length) {
        option.prop('disabled', false);
    }

    item.remove();
}

$(function(){
    if ($(".js-content-data-item").length) {
        $('.js-select-email-elements option[data-used="once"]').each(function() {
            var that = $(this);
            var name = that.val();
            var itemUsed = $(`.js-content-data-item[data-element="${name}"]`);

            if (itemUsed.length) {
                that.prop("disabled", true);
            }
        });
    }
});
