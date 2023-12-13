/**
 * It create and append a new option group item to the wrapper element
 */
const renderOptionGroup = ({ wrapper, optionId, optionText, inputName }) => {
    wrapper.append(
        `<div class="option-group__item" data-option="${optionId}">
            <p class="option-group__text">
                <span>${optionText}</span>
            </p>
            <button class="option-group__btn btn btn-light btn-new16 call-action" data-js-action="option-group:delete">
                <i class="ep-icon ep-icon_trash-stroke"></i>
            </button>
            <input type="hidden" name="${inputName}[]" value="${optionId}">
        </div>`
    );
};

export default renderOptionGroup;
