import $ from "jquery";
import "jquery-countdown";

const startCountdownExpireCertification = dateCountdown => {
    const sdate = new Date(dateCountdown);
    const $countdown = $("#js-countdown-expire");
    const $days = $countdown.find(".js-days");
    const $hours = $countdown.find(".js-hours");
    const $minutes = $countdown.find(".js-min");
    const $seconds = $countdown.find(".js-sec");

    $countdown
        .countdown(sdate, event => {
            $days.html(event.strftime("%D"));
            $hours.html(event.strftime("%H"));
            $minutes.html(event.strftime("%M"));
            $seconds.html(event.strftime("%S"));
        })
        .on("finish.countdown", () => {
            globalThis.location.reload(true);
        });
};

export default dateCountdown => {
    startCountdownExpireCertification(dateCountdown);
};
