import getElement from "@src/util/dom/get-element";

/**
 * When the user changes the source, show the target field
 *
 * @param {JQuery<HTMLElement>} block - The block that the user interacted with.
 * @param {String} fieldName - the name of the info field
 */
function onChangeSource(block, fieldName) {
    const option = this.find(":selected");
    const { target = null } = option.data();
    if (target) {
        const otherField = block.find(target);

        this.removeAttr("name");
        otherField.prop("name", fieldName).show();
    } else {
        this.attr("name", fieldName);
        this.children()
            .toArray()
            .forEach((/** @type {HTMLElement} */ e) => {
                const { target: listedTarget } = e.dataset;
                if (listedTarget) {
                    block.find(listedTarget).removeAttr("name").removeClass("validengine-border").hide();
                }
            });
    }
}

/**
 * When the user changes the type of the block, we need to hide all the sources and show the correct one.
 *
 * @param {JQuery<HTMLElement>} block - The block that contains the sources.
 * @param {JQuery<HTMLSelectElement|HTMLInputElement>} sources - The jQuery object of the source elements.
 * @param {String} fieldName - the name of the info field
 */
function onChangeType(block, sources, fieldName) {
    const option = this.find(":selected");
    const { placeholder, target = null } = option.data();

    // Here we will hide all elements in the sources wrapper
    sources.children().hide().removeClass("validengine-border").removeAttr("name").off("change");
    // If the option has the DOM target value, then we need to do
    // additional processing
    if (target) {
        const sourceType = block.find(target);
        sourceType
            .attr("placeholder", placeholder ?? "")
            .attr("name", fieldName)
            .show();

        if (sourceType.prop("tagName") === "SELECT") {
            sourceType.on("change", onChangeSource.bind(sourceType, block, fieldName));
        }
    }
}

/**
 * @param {JQuery} block the block element that contains the DOM for sccount source
 */
export default function accountSource(block) {
    const { target: listSelector, sources: sourcesSelector, fieldName = "find_info" } = block.data();
    const list = getElement(listSelector);
    const sources = getElement(sourcesSelector);

    list.on("change", onChangeType.bind(list, block, sources, fieldName));
}
