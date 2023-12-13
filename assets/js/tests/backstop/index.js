import $ from "jquery";
import { SITE_URL } from "@src/common/constants";
import postRequest from "@src/util/http/post-request";

import "@scss/user_pages/backstop/index.scss";

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

// ALERT
const myAlert = text => {
    const alert = $(".alert");
    alert.text(text);
    alert.addClass("show");
    setTimeout(() => {
        alert.removeClass("show");
    }, 1500);
};

$(() => {
    $("#formTest").on("submit", async function onSubmit(e) {
        e.preventDefault();
        const media = [];
        const pages = [];

        // Media
        $(`.media-block [type="checkbox"]`).each((i, el) => {
            if (el.checked) {
                media.push(`${el.dataset.name}:${el.dataset.resolution}`);
            }
        });

        // Pages
        $(`.pages-block [type="checkbox"]`).each((i, el) => {
            if (el.checked) {
                pages.push(el.value);
            }
        });

        const asyncCaptureLimit = $("#asyncCaptureLimit").val();
        const asyncCompareLimit = $("#asyncCompareLimit").val();
        const debugMode = $("#debug").prop("checked");

        if (pages.length > 0) {
            // url, data, type
            const data = `
                media=${media.join(",")}&
                pages=${pages.join(",")}&
                asyncCaptureLimit=${asyncCaptureLimit}&
                asyncCompareLimit=${asyncCompareLimit}&
                debugMode=${debugMode}
            `;
            const response = await postRequest(`${SITE_URL}backstop/ajax_generate_test`, data.trim().replace(/\s/g, ""));
            const { mess_type: messType, message } = response;
            if (messType === "success") {
                myAlert(message);
            } else {
                myAlert("Error check console");
                // eslint-disable-next-line no-console
                console.error(response);
            }
        }
    });

    $(".search-pages").on("keyup", function onKeyUp() {
        const regexString = `^${this.value.toLowerCase()}`;
        const regex = new RegExp(regexString, "g");
        $(`${this.dataset.target} [type="checkbox"]`).each((i, checkbox) => {
            checkbox.parentElement.classList.remove("hidden");
            if (!checkbox.value.toLowerCase().match(regex)) {
                checkbox.parentElement.classList.add("hidden");
            }
        });
    });

    $(".checkboxers").on("click", function onClick() {
        $($(this).data("target"))
            .find(`[type="checkbox"]:visible`)
            .prop("checked", $(this).data("type") === "check-all");
    });

    const lemur = document.querySelector(".lemur");
    lemur.addEventListener("click", () => {
        lemur.classList.toggle("active");
    });
});
