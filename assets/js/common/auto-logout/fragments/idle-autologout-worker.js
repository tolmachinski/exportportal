import IdleWorker from "@src/common/auto-logout/fragments/IdleWorker";
import EventHub from "../../../event-hub";

export default warnTime => {
    const idleWorker = new IdleWorker(warnTime);

    EventHub.off("idleWorker:logout");
    EventHub.off("idleWorker:closeWarning");
    EventHub.on("idleWorker:logout", () => idleWorker.logout());
    EventHub.on("idleWorker:closeWarning", (e, ...args) => idleWorker.closeWarning(...args));
};
