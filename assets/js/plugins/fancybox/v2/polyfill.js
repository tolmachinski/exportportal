import $ from "jquery";

import { closeFancyBoxConfirm } from "@src/plugins/fancybox/v2/util";
import lazyLoadingInstance from "@src/plugins/lazy/index";

const W = $(globalThis);
const D = $(document);

/**
 * Polyfill afterZoomIn action.
 *
 * @param {any} F
 */
const afterZoomIn = function (F) {
    const { current } = F;

    if (!current) {
        return;
    }

    F.isOpen = F.isOpened = true;

    F.wrap.css("overflow", "visible").addClass("fancybox-opened").hide().show(0);

    F.update();

    // Assign a click event
    if (current.closeClick || (current.nextClick && F.group.length > 1)) {
        F.inner.css("cursor", "pointer").bind("click.fb", function (e) {
            if (!$(e.target).is("a") && !$(e.target).parent().is("a")) {
                e.preventDefault();

                F[current.closeClick ? "close" : "next"]();
            }
        });
    }

    // Create a close button
    if (current.closeBtn) {
        const closeBtn = $(current.tpl.closeBtn.replace("{{CLOSE}}", current.i18n.close).replace("{{CLOSE_MESSAGE}}", current.i18n.close_message));

        closeBtn.appendTo(F.wrap.find(current.closeBtnWrapper));
        closeBtn.on("click", closeFancyBoxConfirm.bind(closeBtn, F));
    }

    // Create navigation arrows
    if (current.arrows && F.group.length > 1) {
        if (current.loop || current.index > 0) {
            const prevBtn = current.tpl.prev.replace("{{PREVIOUS}}", current.i18n.prev);
            $(prevBtn).appendTo(F.outer).bind("click.fb", F.prev);
        }

        if (current.loop || current.index < F.group.length - 1) {
            const nextBtn = current.tpl.next.replace("{{NEXT}}", current.i18n.next);
            $(nextBtn).appendTo(F.outer).bind("click.fb", F.next);
        }
    }

    F.trigger("afterShow");

    // Stop the slideshow if this is the last item
    if (!current.loop && current.index === current.group.length - 1) {
        F.play(false);
    } else if (F.opts.autoPlay && !F.player.isActive) {
        F.opts.autoPlay = false;

        F.play(true);
    }
    lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
};

const beforeShow = function (opts, obj) {
    if (obj.locked && !this.el.hasClass("fancybox-lock")) {
        if (this.fixPosition !== false) {
            $("*:not(object)")
                .filter(function () {
                    return $(this).css("position") === "fixed" && !$(this).hasClass("fancybox-overlay") && !$(this).hasClass("fancybox-wrap");
                })
                .addClass("fancybox-margin");
        }

        this.el.addClass("fancybox-margin");

        this.scrollV = W.scrollTop();
        this.scrollH = W.scrollLeft();

        this.el.addClass("fancybox-lock");

        W.scrollTop(this.scrollV).scrollLeft(this.scrollH);
    } else if (!obj.locked && !this.el.hasClass("fancybox-lock")) {
        this.el.addClass("fancybox-lock");
        this.el.addClass("fancybox-margin");
    }

    this.open(opts);
};

const slideIn = function (F) {
    const endPos = F._getPosition(true);

    endPos.left = `${parseInt(endPos.left, 10) - 200}px`;
    endPos.opacity = 0;

    F.wrap.css(endPos).show().animate(
        {
            left: "+=200px",
            opacity: 1,
        },
        {
            duration: F.current.nextSpeed,
            complete: F._afterZoomIn,
        }
    );
};

const onAfterClose = function () {
    $(".fancybox-lock").removeClass("fancybox-lock");
    $(".fancybox-margin").removeClass("fancybox-margin");
};

/**
 * Polyfill factory.
 *
 * @param {any} F
 */
export default function (F) {
    $.extend(F, {
        beforeShow(...args) {
            return beforeShow.apply(F, ...args);
        },
        afterClose() {
            return onAfterClose();
        },
        // eslint-disable-next-line no-underscore-dangle
        _afterZoomIn(...args) {
            return afterZoomIn.apply(F, [F, ...args]);
        },
        // eslint-disable-next-line no-underscore-dangle
        _error(type) {
            $.extend(F.coming, {
                type: "html",
                autoWidth: true,
                autoHeight: true,
                minWidth: 0,
                minHeight: 0,
                scrolling: "no",
                hasError: type,
                content: F.coming.tpl.error.replace("{{ERROR}}", F.coming.i18n.error),
            });
            // eslint-disable-next-line no-underscore-dangle
            F._afterLoad();
        },
    });

    F.transitions.slideIn = () => {
        slideIn(F);
    };
}
