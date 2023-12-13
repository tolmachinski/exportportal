import $ from "jquery";

import { addCounter } from "@src/plugins/textcounter/index";

import EventHub from "@src/event-hub";

export default () => {
    addCounter($(".js-textcounter"));

    EventHub.off("comments:form-action");
    EventHub.on("comments:form-action", async (_e, form) => {
        const { default: saveComment } = await import("@src/components/comments/fragments/save_comment");
        saveComment(form);
    });
};
