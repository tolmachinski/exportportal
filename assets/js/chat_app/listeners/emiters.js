import offResizeCallback from "@src/util/dom/off-resize-callback";
import onResizeCallback from "@src/util/dom/on-resize-callback";
import mix from "@src/util/common/mix";

// Отправляем размер окна
const emiterResizeParentFrame = (emitter, emitterName) => {
    emitter.emit(emitterName, { width: window.innerWidth, height: window.innerHeight });

    offResizeCallback(null, emitterName);
    onResizeCallback(
        () => {
            emitter.emit(emitterName, { width: window.innerWidth, height: window.innerHeight });
        },
        null,
        emitterName
    );
};

// Logout
export const emitterLogout = (listener, emitter) => {
    // This need for pages without webpack and without chat (unloggined user on page wihtout webpack);
    mix(globalThis, { matrixLogoutEmitter: true });

    const fn = e => {
        let callbackTriggered = false;
        const callback = e?.detail?.callback;
        listener.off("logoutDone");
        listener.on("logoutDone", () => {
            callback();
            callbackTriggered = true;
        });
        emitter.emit("logout");
        // Check if for some reason logout has not done call callback()
        setTimeout(() => {
            if (!callbackTriggered) {
                callback();
            }
        }, 3000);
    };
    window.removeEventListener("matrixLogout", fn);
    window.addEventListener("matrixLogout", fn);
};

export default emiterResizeParentFrame;
