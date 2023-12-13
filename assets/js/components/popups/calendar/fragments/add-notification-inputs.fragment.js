import { translate } from "@src/i18n";
import { updateFancyboxPopup } from "@src/plugins/fancybox/v2/util";
import checkNotificationsLength from "@src/components/popups/calendar/fragments/check-notification-length.fragment";

const addNotificationInputs = (wrapper, addNotificationBtn, maxDays) => {
    // TODO change SVG to import
    wrapper.append(`
    <div class="notifications-settings-form__row">
        <select
            name="types[]"
            class="validate[required] ep-select ep-select--popup notifications-settings-form__select js-notifications-settings-select"
        >
            <option selected disabled>
            ${translate({ plug: "general_i18n", text: "calendar_notifacation_settings_choose_type" })}
            </option>
            <option value="system">
            ${translate({ plug: "general_i18n", text: "calendar_notifacation_settings_notification" })}
            </option>
            <option value="email">
            ${translate({ plug: "general_i18n", text: "calendar_notifacation_settings_email" })}
            </option>
        </select>
        <div class="notifications-settings-form__row-input">
            <input
                class="ep-input ep-input--popup notifications-settings-form__input js-notifications-settings-input validate[required,max[${maxDays}], min[0]]; ?>"
                type="number"
                name="notifications[]"
                value="1"
            >
            <span class="notifications-settings-form__row-text">${translate({
                plug: "general_i18n",
                text: "calendar_notifacation_settings_days_before",
            })}</span>
            <button class="notifications-settings-form__remove-btn call-action" type="button" data-js-action="calendar-notifications:remove-notification">
            <!-- TODO change SVG to import -->
                <svg xmlns="http://www.w3.org/2000/svg" width="14.061" height="14.063" viewBox="0 0 14.061 14.063">
                    <g transform="translate(-855.435 -733.434)">
                        <path d="M.75,18.385H-.75V0H.75Z" transform="translate(868.964 733.964) rotate(45)"/>
                        <path d="M.75,18.385H-.75V0H.75Z" transform="translate(868.964 746.964) rotate(135)"/>
                    </g>
                </svg>
            </button>
        </div>
    </div>
    `);
    updateFancyboxPopup();
    checkNotificationsLength(wrapper, addNotificationBtn);
};

export default addNotificationInputs;
