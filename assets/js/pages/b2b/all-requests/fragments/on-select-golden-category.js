const onSelectGoldenCategory = () => {
    // @ts-ignore
    const goldenCategoriesSelectValue = document.getElementById("js-search-b2b-golden-categories-select").value;
    const goldenCategoriesInput = document.getElementById("js-golden-cateories-hidden-input");
    const industriesSelectOptions = Array.from(document.querySelectorAll("#js-search-b2b-industry-select option"));

    // @ts-ignore
    goldenCategoriesInput.value = "";

    if (goldenCategoriesSelectValue) {
        // @ts-ignore
        goldenCategoriesInput.value = goldenCategoriesSelectValue;

        industriesSelectOptions.forEach(industry => {
            // @ts-ignore
            if (industry.value && industry.getAttribute("data-id-group") !== goldenCategoriesInput.value) {
                industry.setAttribute("disabled", "disabled");
            } else {
                industry.removeAttribute("disabled");
            }
        });
    } else {
        industriesSelectOptions.forEach(industry => {
            industry.removeAttribute("disabled");
        });
    }
};

export default onSelectGoldenCategory;
