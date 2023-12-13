import $ from "jquery";

/**
 * Handles search form submit event.
 *
 * @param {JQuery.TriggeredEvent} e
 * @param {any} element
 */
const searchQuestions = async function (e, element, $form) {
    e.preventDefault();
    const form = element.closest($form);
    if (!$(form).validationEngine("validate")) {
        return;
    }

    let formAction = form.prop("action").replace(/\/$/, "").split("?");
    const uriParams = [formAction[0]];
    let getParams = [];
    if (formAction[1]) {
        getParams.push(formAction[1]);
    }

    $.each(form.find(".js-type-url"), (i, { value, name }) => {
        if (value !== "") {
            uriParams.push(`${name}/${value}`);
        }
    });
    formAction = uriParams.join("/");

    const keywords = encodeURIComponent(form.find("input[name=keywords]").val()).trim().replace(/%20/g, "+");
    if (keywords !== "" && keywords !== "+") {
        getParams.push(`keywords=${keywords}`);
    }

    if (getParams.length > 0) {
        // @ts-ignore
        getParams = getParams.join("&");
        formAction += `?${getParams}`;
    }

    globalThis.location.href = formAction;
};

export default searchQuestions;
