import $ from "jquery";

class TagsInput {
    constructor(selector, options) {
        this.selector = selector;
        this.delimiter = [];
        this.tags_callbacks = [];
        this.settings = $.extend(
            {
                interactive: true,
                defaultText: "add a tag",
                minChars: 0,
                maxChars: 20,
                width: "300px",
                height: "100px",
                autocomplete: { selectFirst: false },
                hide: true,
                delimiter: ",",
                unique: true,
                removeWithBackspace: true,
                placeholderColor: "#9e9e9e",
                autosize: true,
                comfortZone: 20,
                inputPadding: 6 * 2,
            },
            options
        );

        this.init();
    }

    init() {
        const that = this;
        let uniqueIdCounter = 0;

        $(that.selector).each(function eachElements() {
            const element = $(this);
            // If we have already initialized the field, do not do it again
            if (typeof element.attr("data-tagsinput-init") !== "undefined") {
                return;
            }

            // Mark the field as having been initialized
            element.attr("data-tagsinput-init", true);

            if (that.settings.hide) {
                element.hide();
            }

            let id = element.attr("id");

            if (!id || that.delimiter[element.attr("id")]) {
                uniqueIdCounter += 1;
                id = element.attr("id", `tags${new Date().getTime()}${uniqueIdCounter}`).attr("id");
            }

            that.settings = $.extend(
                {
                    pid: id,
                    real_input: `#${id}`,
                    holder: `#${id}_tagsinput`,
                    input_wrapper: `#${id}_addTag`,
                    fake_input: `#${id}_tag`,
                },
                that.settings
            );

            that.delimiter[id] = that.settings.delimiter;

            if (that.settings.onAddTag || that.settings.onRemoveTag || that.settings.onChange) {
                that.tags_callbacks[id] = [];
                that.tags_callbacks[id].onAddTag = that.settings.onAddTag;
                that.tags_callbacks[id].onRemoveTag = that.settings.onRemoveTag;
                that.tags_callbacks[id].onChange = that.settings.onChange;
            }

            let markup = `<div id="${id}_tagsinput" class="tagsinput"><div class="tagsinput__add-tag" id="${id}_addTag">`;

            if (that.settings.interactive) {
                markup = `${markup}<input id="${id}_tag" value="" maxlength="${that.settings.maxChars}" data-default="${that.settings.defaultText}" />`;
            }

            markup += '</div><div class="tags_clear"></div></div>';

            $(markup).insertAfter(this);

            $(that.settings.holder).css("min-height", that.settings.height);
            $(that.settings.holder).css("height", that.settings.height);

            if ($(that.settings.real_input).val() !== "") {
                that.importTags($(that.settings.real_input), $(that.settings.real_input).val());
            }

            if (that.settings.interactive) {
                $(that.settings.fake_input).val($(that.settings.fake_input).attr("data-default"));
                $(that.settings.fake_input).css("color", that.settings.placeholderColor);
                that.resetAutosize($(that.settings.fake_input), that.settings);

                $(that.settings.holder).on("click", () => {
                    $(that.settings.fake_input).focus();
                });

                $(that.settings.fake_input).on("focus", () => {
                    const focusFakeInput = $(that.settings.fake_input);
                    if (focusFakeInput.val() === focusFakeInput.attr("data-default")) {
                        focusFakeInput.val("");
                    }

                    focusFakeInput.css("color", "#000000");
                });

                if (that.settings.autocomplete_url !== undefined) {
                    const autocomplete_options = { source: that.settings.autocomplete_url };
                    for (const attrname in that.settings.autocomplete) {
                        autocomplete_options[attrname] = that.settings.autocomplete[attrname];
                    }

                    // if ($.Autocompleter !== undefined) {
                    //     $(that.settings.fake_input).autocomplete(that.settings.autocomplete_url, that.settings.autocomplete);
                    //     $(that.settings.fake_input).bind("result", function (event) {
                    //         that.addTag($(`#${id}`), `${data[0]}`, { focus: true, unique: that.settings.unique });
                    //     });
                    // } else if ($.ui.autocomplete !== undefined) {
                    //     $(that.settings.fake_input).autocomplete(autocomplete_options);
                    //     $(that.settings.fake_input).bind("autocompleteselect", function (event, ui) {
                    //         that.addTag($(event.that.settings.real_input), ui.item.value, { focus: true, unique: that.settings.unique });
                    //         return false;
                    //     });
                    // }
                } else {
                    // if a user tabs out of the field, create a new tag
                    // this is only available if autocomplete is not used.
                    $(that.settings.fake_input).on("blur", function blurFakeInput() {
                        const d = $(this).attr("data-default");
                        if ($(that.settings.fake_input).val() !== "" && $(that.settings.fake_input).val() !== d) {
                            if (
                                that.settings.minChars <= $(that.settings.fake_input).val().length &&
                                (!that.settings.maxChars || that.settings.maxChars >= $(that.settings.fake_input).val().length)
                            ) {
                                that.addTag($(that.settings.real_input), $(that.settings.fake_input).val(), { focus: true, unique: that.settings.unique });
                            }
                        } else {
                            $(that.settings.fake_input).val($(that.settings.fake_input).attr("data-default"));
                            $(that.settings.fake_input).css("color", that.settings.placeholderColor).removeClass("not_valid");
                        }
                        return false;
                    });
                }

                // if user types a default delimiter like comma,semicolon and then create a new tag
                $(that.settings.fake_input).on("keypress", function keyPressFakeInput(event) {
                    if (that.checkDelimiter(event)) {
                        event.preventDefault();
                        if (
                            that.settings.minChars <= $(that.settings.fake_input).val().length &&
                            (!that.settings.maxChars || that.settings.maxChars >= $(that.settings.fake_input).val().length)
                        ) {
                            that.addTag($(that.settings.real_input), $(that.settings.fake_input).val(), { focus: true, unique: that.settings.unique });
                        }

                        that.resetAutosize($(that.settings.fake_input), that.settings);
                    } else if (that.settings.autosize) {
                        that.doAutosize($(that.settings.fake_input), that.settings);
                    }
                });

                // Delete last tag on backspace
                if (that.settings.removeWithBackspace) {
                    $(that.settings.fake_input).on("keydown", function keyDownFakeInput(event) {
                        const fakeInputElement = $(this);

                        if (event.keyCode === 8 && fakeInputElement.val() === "") {
                            event.preventDefault();
                            let lastTag = fakeInputElement.closest(".tagsinput").find(".tag:last").text();
                            const idThis = fakeInputElement.attr("id").replace(/_tag$/, "");
                            lastTag = lastTag.replace(/[\s]+x$/, "");
                            that.removeTag($(`#${idThis}`), escape(lastTag));
                            fakeInputElement.trigger("focus");
                        }
                    });
                }

                $(that.settings.fake_input).trigger("blur");

                // Removes the not_valid class when user changes the value of the fake input
                if (that.settings.unique) {
                    $(that.settings.fake_input).keydown(function (event) {
                        if (event.keyCode === 8 || String.fromCharCode(event.which).match(/\w+|[áéíóúÁÉÍÓÚñÑ,/]+/)) {
                            $(this).removeClass("not_valid");
                        }
                    });
                }
            } // if settings.interactive
        });
    }

    doAutosize(element, o) {
        const that = this;
        const minWidth = element.data("minwidth");
        const maxWidth = element.data("maxwidth");
        let val = "";
        const testSubject = $(`#${element.data("tester_id")}`);

        if (val === element.val()) {
            return;
        }

        val = element.val();
        // Enter new content into testSubject
        const escaped = val.replace(/&/g, "&amp;").replace(/\s/g, " ").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        testSubject.html(escaped);
        // Calculate new width + whether to change
        const testerWidth = testSubject.width();
        const newWidth = testerWidth + o.comfortZone >= minWidth ? testerWidth + o.comfortZone : minWidth;
        const currentWidth = element.width();
        const isValidWidthChange = (newWidth < currentWidth && newWidth >= minWidth) || (newWidth > minWidth && newWidth < maxWidth);

        // Animate width
        if (isValidWidthChange) {
            element.width(newWidth);
        }
    }

    resetAutosize(element, options) {
        const that = this;
        const minWidth = element.data("minwidth") || options.minInputWidth || element.width();
        const maxWidth = element.data("maxwidth") || options.maxInputWidth || element.closest(".tagsinput").width() - options.inputPadding;
        const testSubject = $("<tester/>").css({
            position: "absolute",
            top: -9999,
            left: -9999,
            width: "auto",
            fontSize: element.css("fontSize"),
            fontFamily: element.css("fontFamily"),
            fontWeight: element.css("fontWeight"),
            letterSpacing: element.css("letterSpacing"),
            whiteSpace: "nowrap",
        });
        const testerId = `${element.attr("id")}_autosize_tester`;

        if (!$(`#${testerId}`).length > 0) {
            testSubject.attr("id", testerId);
            testSubject.appendTo("body");
        }

        element.data("minwidth", minWidth);
        element.data("maxwidth", maxWidth);
        element.data("tester_id", testerId);
    }

    addTag(element, value, options) {
        const that = this;
        options = $.extend({ focus: false, callback: true }, options);

        element.each(function () {
            const eachElement = $(this);
            const id = eachElement.attr("id");
            let tagslist = eachElement.val().split(that.delimiter[id]);

            if (tagslist[0] === "") {
                tagslist = [];
            }

            value = $.trim(value);
            let skipTag = false;

            if (options.unique) {
                skipTag = that.tagExist(eachElement, value);
                if (skipTag === true) {
                    // Marks fake input as not_valid to let styling it
                    $(`#${id}_tag`).addClass("not_valid");
                }
            }

            if (value !== "" && skipTag !== true) {
                $("<span>")
                    .addClass("tag")
                    .append(
                        $("<span>").text(value).append("&nbsp;&nbsp;"),
                        $("<a>", {
                            href: "#",
                            title: "Removing tag",
                            text: "x",
                        }).click(function () {
                            return that.removeTag($(`#${id}`), escape(value));
                        })
                    )
                    .insertBefore(`#${id}_addTag`);

                tagslist.push(value);

                $(`#${id}_tag`).val("");
                if (options.focus) {
                    $(`#${id}_tag`).focus();
                } else {
                    $(`#${id}_tag`).blur();
                }

                that.updateTagsField(this, tagslist);

                if (options.callback && that.tags_callbacks[id] && that.tags_callbacks[id].onAddTag) {
                    const f = that.tags_callbacks[id].onAddTag;
                    f.call(this, value);
                }
                if (that.tags_callbacks[id] && that.tags_callbacks[id].onChange) {
                    const i = tagslist.length;
                    const f = that.tags_callbacks[id].onChange;
                    f.call(this, eachElement, tagslist[i - 1]);
                }
            }
        });
    }

    removeTag(element, value) {
        const that = this;
        value = unescape(value);

        element.each(function () {
            const eachElement = $(this);
            const id = eachElement.attr("id");
            const old = eachElement.val().split(that.delimiter[id]);

            $(`#${id}_tagsinput .tag`).remove();
            let str = "";

            for (let i = 0; i < old.length; i += 1) {
                if (old[i] !== value) {
                    str = str + that.delimiter[id] + old[i];
                }
            }

            that.importTags(this, str);

            if (that.tags_callbacks[id] && that.tags_callbacks[id].onRemoveTag) {
                const f = that.tags_callbacks[id].onRemoveTag;
                f.call(this, value);
            }
        });

        return false;
    }

    tagExist(element, val) {
        const that = this;
        const id = element.attr("id");
        const tagslist = element.val().split(that.delimiter[id]);
        return $.inArray(val, tagslist) >= 0; // true when tag exists, false when not
    }

    updateTagsField(obj, tagslist) {
        const that = this;
        const id = $(obj).attr("id");
        $(obj).val(tagslist.join(that.delimiter[id]));
    }

    clearTags(obj, str) {
        const that = this;
        const id = $(obj).attr("id");
        $(`#${id}_tagsinput .tag`).remove();
        that.importTags(obj, str);
    }

    importTags(obj, val) {
        const that = this;

        $(obj).val("");
        const id = $(obj).attr("id");
        const tags = val.split(that.delimiter[id]);
        let key = 0;

        for (let i = 0; i < tags.length; i += 1) {
            that.addTag($(obj), tags[i], { focus: false, callback: false });
            key = i;
        }

        if (that.tags_callbacks[id] && that.tags_callbacks[id].onChange) {
            const f = that.tags_callbacks[id].onChange;
            f.call(obj, obj, tags[key]);
        }
    }

    checkDelimiter(event) {
        const that = this;
        let found = false;

        if (event.which === 13) {
            return true;
        }

        if (typeof that.settings.delimiter === "string") {
            if (event.which === that.settings.delimiter.charCodeAt(0)) {
                found = true;
            }
        } else {
            $.each(that.settings.delimiter, function (index, delimiter) {
                if (event.which === delimiter.charCodeAt(0)) {
                    found = true;
                }
            });
        }
        return found;
    }
}

export default (params, options) => {
    // eslint-disable-next-line no-new
    return new TagsInput(params, options);
};
