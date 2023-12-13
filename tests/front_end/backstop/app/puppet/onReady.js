module.exports = async (page, scenario, vp) => {
    const ENV = require('./variables/env');

    if (ENV.debugMode) {
        console.log("onReady, start...");
    }
    // console.log('SCENARIO > ' + scenario.label);
    await require('./clickAndHoverHelper')(page, scenario);

    if (ENV.debugMode) {
        console.log("onReady, clickAndHoverHelpers, done");
    }

    // WAIT FONTS LOADING
    await require('./modules/fonts')(page);

    if (ENV.debugMode) {
        console.log("onReady, fonts, done");
    }

    // LOGIN
    if(scenario.authentication){
        await require('./modules/auth')(page, scenario);
    }

    if (ENV.debugMode) {
        if (scenario.authentication) {
            console.log("onReady, login, done");
        } else {
            console.log("onReady, login, skip");
        }
    }

    // WAIT LAZY LOADING ON SCROLL
    await require('./modules/lazyLoadingOnScroll')(page);

    if (ENV.debugMode) {
        console.log("onReady, lazy loading, done");
    }

    // REMOVE ANIMATION TRANSITION FROM ALL ELEMENTS
    await require('./modules/removeTransition')(page);

    if (ENV.debugMode) {
        console.log("onReady, remove transition animation, done");
    }

    // PAGE LOGIC
    if(scenario.jsFileName){
        await require(`./pages/${scenario.jsFileName}`)(page, scenario);
        await page.waitForTimeout(500);
    }

    if (ENV.debugMode) {
        if (scenario.jsFileName) {
            console.log("onReady, page logic, done");
        } else {
            console.log("onReady, page logic, skip");
        }
    }

    if (scenario.rightSidebarPopup) {
        await require("./modules/rightSidebarPopups")(page, scenario);
    }

    if (ENV.debugMode) {
        if (scenario.rightSidebarPopup) {
            console.log("onReady, rightSidebarPopup, done");
        } else {
            console.log("onReady, rightSidebarPopup, skip");
        }
    }

    // DISABLE SLIDERS
    await require("./modules/disableSliders")(page);

    if (ENV.debugMode) {
        console.log("onReady, disableSliders, done");
    }

    // FIX CSS FOR POPUPS
    await require("./modules/fixPopups")(page);

    if (ENV.debugMode) {
        console.log("onReady, fixPopups, done");
    }

    await page.waitForFunction(() => {
        return (async () => {
            const overlayTopMenu = document.getElementById("js-shadow-header-top");
            const overlayMenu = document.querySelector(".header-main-overlay2");
            const bootstrapDialog = document.querySelector(".modal-backdrop");
            const fancyboxInnerNode = ".fancybox-inner";
            const fancyboxInner = document.querySelector(fancyboxInnerNode);
            let pageHeight = "";
            if (
                    (overlayTopMenu && overlayTopMenu.style.display === "block")
                    || (overlayMenu && overlayMenu.style.display === "block")
                    || fancyboxInner
                    || bootstrapDialog
                    || document.getElementById("js-sidebar-categories")?.clientHeight > 0
                    || document.getElementById("js-epl-header-line-background")?.clientHeight > 0
                    || document.querySelector(".fancybox-bg")?.clientHeight > 0
                    || document.querySelector(".sidebar__bg")?.clientHeight > 0
                    || document.querySelector(".calendar-info-popup__overlay")?.clientHeight > 0
                ) {
                pageHeight = "html,body{height:3000px!important; overflow: hidden!important;padding: 0!important;margin: 0!important;}";
            }

            const style = document.createElement("style");
            const scrollHeight = 3000; //document.body.scrollHeight;

            const css = `
                ${pageHeight}

                .grecaptcha-badge,
                .grecaptcha {
                    visibility: hidden!important;
                }

                ${fancyboxInnerNode} {
                    height: auto!important;
                }

                @media(max-width: 767px) {
                    ${fancyboxInnerNode} {
                        height: ${scrollHeight - 10}px!important;
                    }
                }

                #js-shadow-header-top,
                .fancybox-overlay,
                .fancybox-bg {
                    opacity: 1!important;
                    background: black!important;
                    margin: 0!important;
                }

                #js-epl-header-line-background,
                .modal-backdrop {
                    width: calc(100% + 10px)!important;
                    height: ${scrollHeight}px!important;
                    opacity: 1!important;
                }

                .fancybox-overlay,
                .fancybox-bg,
                #js-shadow-header-top,
                .header-main-overlay2 {
                    bottom: initial!important;
                    height: ${scrollHeight}px!important;
                    opacity: 1!important;
                }

                #js-epl-header-line-background,
                #js-sidebar-categories {
                    bottom: initial!important;
                    height: ${scrollHeight}px!important;
                    background: black!important;
                }

                .fancybox-container {
                    height: ${scrollHeight}px!important;
                }

                .calendar-info-popup__overlay {
                    opacity: 1!important;
                }

                @media(max-width: 1200px) {
                    .sidebar__bg {
                        background: black!important;
                    }

                    #js-ep-sidebar:not(.sidebar--show-md) {
                        height: ${scrollHeight}px!important;
                    }
                }
            `;
            style.textContent = css;
            document.body.appendChild(style);

            return true;
        })();
    });

    if (ENV.debugMode) {
        console.log("onReady, push CSS for fix popups, done");
    }

    // console.log(scenario.label);

    await page.waitForFunction(() => {
        return (async () => {
            // console.log(window.innerWidth);

            setInterval(() => {
                if (window.scrollY !== 0) {
                    window.scrollTo({
                        top: 0,
                    });
                }
            }, 15);

            return true;
        })()
    });

    if (ENV.debugMode) {
        console.log("onReady, Interval scroll top, done");
    }

    await page.waitForNetworkIdle();

    if (ENV.debugMode) {
        console.log("onReady, NetworkIdle, done");
    }

    await page.waitForFunction(() => {
        return true;
    });

    if (ENV.debugMode) {
        console.log("onReady, done");
    }
};
