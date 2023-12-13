import $ from "jquery";
import "jquery-countdown";
import EventHub from "../../../event-hub";

const initLogoutCountdown = function (countdownEl, logoutCountdownInSeconds) {
    const logoutTime = new Date().getTime() + logoutCountdownInSeconds * 1000;
    $(countdownEl)
        .countdown(logoutTime, event => {
            $(countdownEl).html(event.strftime("%M:%S"));
        })
        .on("finish.countdown", () => {
            EventHub.trigger("idleWorker:logout");
        });
};

export default (countdownEl, logoutCountdownInSeconds) => {
    initLogoutCountdown(countdownEl, logoutCountdownInSeconds);
};
