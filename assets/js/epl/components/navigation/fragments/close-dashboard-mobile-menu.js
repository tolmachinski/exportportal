import $ from "jquery";

export default async () => {
    const eplHeaderLine = $("#js-epl-header-line");

    if (eplHeaderLine.hasClass("active")) {
        $("body").removeClass("locked");
        eplHeaderLine.removeClass("active");

        const { default: toggleHeaderLineBackground } = await import("@src/epl/components/navigation/fragments/toggle-header-line-background");
        toggleHeaderLineBackground();
    }
};
