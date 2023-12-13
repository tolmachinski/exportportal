/* eslint-disable no-console */
import $ from "jquery";
import EventHub from "@src/event-hub";
import { hideLoader, showLoader } from "@src/util/common/loader";
import { createPopupBannerWrapper } from "@src/components/popups_system/popup_util";
import { DEBUG, DISABLE_POPUP_SYSTEM } from "@src/common/constants";

class PopupsSystem {
    constructor(popups = [], params = []) {
        this.params = params;
        this.popups = popups;
        // @ts-ignore
        this.loggedIn = this.params.loggedIn;
        // eslint-disable-next-line camelcase
        this.typePopups = { banner_bottom: "bannersBottom", modal: "modals" };
        this.popupsAll = {
            all: [],
            sleep: [],
            modals: [],
            bannersBottom: [],
            needCall: [],
        };
        this.popupsAll.all = this.popups;
        this.managePopupsClasses = ".bootstrap-dialog, .fancybox-overlay, .fancybox-container, #js-mep-header-dashboard:visible, #js-epuser-subline:visible";
        this.manageBannerClasses = ".js-popup-system-banner-item";
        this.managePopupsMaxSnoozTime = 5000;

        if (!DISABLE_POPUP_SYSTEM) {
            this.init();
        }
    }

    async init() {
        const that = this;

        await createPopupBannerWrapper();
        await that.managePopupsPrepare();
        Object.entries(that.typePopups).forEach(([, valueType]) => {
            that.managePopupsInit(valueType);
        });
    }

    managePopupsPrepare() {
        const that = this;
        // eslint-disable-next-line no-underscore-dangle
        const currentPage = globalThis.__page_hash;

        const chechPages = pages => {
            return pages.includes(currentPage);
        };

        const pushByType = popupsAllItem => {
            if (popupsAllItem.type_popup === "banner_bottom") {
                that.popupsAll.bannersBottom.push(popupsAllItem);
            } else {
                that.popupsAll.modals.push(popupsAllItem);
            }

            return true;
        };

        that.popupsAll.all.forEach(popupsAllItem => {
            if ((popupsAllItem.pages && !chechPages(popupsAllItem.pages)) || (popupsAllItem.not_pages && chechPages(popupsAllItem.not_pages))) {
                that.popupsAll.sleep.push(popupsAllItem);
                return;
            }

            const callOnStart = parseInt(popupsAllItem.call_on_start, 10);
            if (!callOnStart || callOnStart === 2) {
                that.popupsAll.needCall.push(popupsAllItem);

                if (callOnStart === 2) {
                    pushByType(popupsAllItem);
                }
            } else {
                pushByType(popupsAllItem);
            }
        });
    }

    managePopupsInit(type) {
        const that = this;

        if (!that.popupsAll[type].length) {
            return true;
        }

        that.managePopupsStart(that.popupsAll[type][0]);

        return true;
    }

    managePopupsStart(popup) {
        const that = this;
        let time = this.loggedIn ? 100 : 5000;

        const managePopupsInterval = setInterval(() => {
            if (that.checkShowModal(popup.type_popup)) {
                if (time === 100) {
                    time = 2000;
                }
                return true;
            }

            clearInterval(managePopupsInterval);
            that.managePopupsStartVerify(popup);

            return true;
        }, time);
    }

    managePopupsStartVerify(popup) {
        const that = this;
        const typeArray = that.typePopups[popup.type_popup];
        let snoozetimeOut = 500;

        that.popupsAll[typeArray].splice(0, 1);
        if (!popup.snooze_time || popup.snooze_time < that.managePopupsMaxSnoozTime) {
            that.managePopupsShow(popup);
        } else {
            setTimeout(() => {
                that.managePopupsSnooze(popup);
                that.managePopupsInit(typeArray);
                snoozetimeOut === 500 ? (snoozetimeOut = 1000) : 500;
            }, snoozetimeOut);
        }
    }

    managePopupsShow(popup) {
        const that = this;
        const typeArray = that.typePopups[popup.type_popup];

        if (typeArray === "modals") {
            showLoader($("body"), "", "absolute", 1005);
        }

        that.managePopupsCall(popup).then(() => {
            that.managePopupsInit(typeArray);

            if (typeArray === "modals") {
                hideLoader($("body"));
            }
        });
    }

    managePopupsSnooze(popup) {
        const that = this;
        let managePopupsSnoozeInterval;

        setTimeout(() => {
            managePopupsSnoozeInterval = setInterval(() => {
                if (that.checkShowModal(popup.type_popup)) {
                    return true;
                }

                clearInterval(managePopupsSnoozeInterval);
                that.managePopupsCall(popup);
                return true;
            }, 1000);
        }, popup.snooze_time);

        return true;
    }

    async managePopupsCall({ popup_hash: popupHash }) {
        // console.log("+++Call", popupHash);

        try {
            let module;
            try {
                module = await import(`@src/components/popups_system/popups/${popupHash}`);
            } catch (e) {
                console.warn(`The popup with name "${popupHash}" is not found`);

                return;
            }

            const { default: open } = module;
            await open(this.params[popupHash]);
        } catch (error) {
            if (DEBUG) {
                console.error(error);
            }
        }
    }

    checkShowModal(type) {
        const that = this;

        switch (type) {
            case "modal":
                if ($(that.managePopupsClasses).length) {
                    return true;
                }
                break;
            case "banner_bottom":
                if (that.checkShowBanners()) {
                    return true;
                }
                break;
            default:
        }

        return false;
    }

    checkShowBanners() {
        const that = this;
        const banners = $(that.manageBannerClasses);
        let exitBanners = false;

        if (banners.length > 1) {
            exitBanners = true;
        } else if (banners.length === 1 && !$("#js-widget-cookie-container").length) {
            exitBanners = true;
        }
        return exitBanners;
    }

    call(button) {
        const that = this;
        const namePopup = button?.detail?.name ?? button[0]?.dataset?.popup ?? "";
        const callType = button[0]?.dataset?.callType ?? "";

        if (namePopup === "") {
            return true;
        }

        if (callType !== "" && callType === "global") {
            that.managePopupsCall({ popup_hash: namePopup });
        } else {
            const existCallItem = that.popupsAll.needCall.find(needCallItem => needCallItem.popup_hash === namePopup);
            if (existCallItem) {
                const managePopupsInterval = setInterval(() => {
                    if (that.checkShowModal(existCallItem.type_popup)) {
                        return true;
                    }

                    clearInterval(managePopupsInterval);
                    // eslint-disable-next-line camelcase
                    that.managePopupsCall({ popup_hash: existCallItem.popup_hash });

                    return true;
                }, 1000);
            }
        }

        return true;
    }
}

export default (popups, params) => {
    const validPopups = Object.values(popups);
    if (validPopups && validPopups.length) {
        $(() => {
            /* eslint-disable no-new */
            const popupsSystem = new PopupsSystem(validPopups, params);

            EventHub.off("popup:call-popup");
            EventHub.on("popup:call-popup", (e, button) => popupsSystem.call(button));
            // @ts-ignore
            globalThis.addEventListener("popup:call-popup", e => popupsSystem.call(e), {});
        });
    }
};
