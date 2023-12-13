import loadFancybox, { open } from "@src/plugins/fancybox/v2/index";
import { TEMPLATES } from "@src/common/popups/templates";
import { calculateModalBoxSizes } from "@src/plugins/fancybox/v2/util";
import { i18nDomain } from "@src/i18n";
import { LANG } from "@src/common/constants";
import sentPopupViewed from "@src/util/common/send-popup-viewed";

const openOnboardingSurveyPopup = async () => {
    await loadFancybox().then(() => {
        const adjustments = calculateModalBoxSizes();

        open(
            {
                // @ts-ignore
                title: "Complete the survey",
                type: "iframe",
                href: "https://survey.zohopublic.com/zs/OlCsVS",
                closeBtn: TEMPLATES.closeBtn,
            },
            {
                tpl: TEMPLATES,
                // eslint-disable-next-line no-underscore-dangle
                lang: LANG,
                i18n: i18nDomain({ plug: "fancybox" }),
                width: "100%",
                height: "95%",
                maxWidth: 700,
                autoSize: true,
                closeBtn: true,
                closeClick: false,
                nextClick: false,
                arrows: false,
                mouseWheel: false,
                keys: null,
                loop: false,
                helpers: {
                    title: { type: "inside", position: "top" },
                    overlay: { locked: true, closeClick: false },
                },
                padding: adjustments.gutter,
                closeBtnWrapper: ".fancybox-skin .fancybox-title",
                beforeShow() {
                    sentPopupViewed("onboarding_survey");
                },
            }
        );
    });
};

export default openOnboardingSurveyPopup;
