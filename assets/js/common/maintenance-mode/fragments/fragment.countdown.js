import $ from "jquery";
import "jquery-countdown";
import "@scss/user_pages/general/maintenance.scss";

const startCountdownMaintenance = function (timeStart) {
    if ($(".maintenance-mode").length) {
        $(".community-header").addClass("community-header--maintenance");
    }

    const sdate = new Date(timeStart);
    const cdate = new Date();
    const ddiff = Math.floor(
        (Date.UTC(sdate.getFullYear(), sdate.getMonth(), sdate.getDate()) - Date.UTC(cdate.getFullYear(), cdate.getMonth(), cdate.getDate())) /
            (1000 * 60 * 60 * 24)
    );

    if (Math.trunc(ddiff) !== 0) {
        $("#js-maintenance-starte-date-client-text").text(`on ${sdate.toLocaleString("en-US", { weekday: "long", month: "long", day: "numeric" })}`);
    }

    const countDown = function (event) {
        const daysLeft = event.strftime("%D");
        const hoursLeft = event.strftime("%H");
        const minutesLeft = event.strftime("%M");
        const secondsLeft = event.strftime("%S");

        if (daysLeft === "01") {
            $(".maintenance-mode__days").text("Day");
        } else if (daysLeft === "00") {
            $(".maintenance-mode__days").addClass("display-n");
            $("#js-days-left").addClass("display-n");
        }

        $("#js-days-left").html(daysLeft);
        $("#js-hours-left").html(hoursLeft);
        $("#js-minutes-left").html(minutesLeft);
        $("#js-seconds-left").html(secondsLeft);
    };

    const onFinish = function () {
        globalThis.location.reload(true);
    };

    $("#js-getting-started").countdown(timeStart, countDown).on("finish.countdown", onFinish);
};

export default dateStart => {
    startCountdownMaintenance(new Date(dateStart));
};
