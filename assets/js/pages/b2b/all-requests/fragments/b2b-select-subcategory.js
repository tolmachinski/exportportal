import $ from "jquery";

/**
 * It shows the categories that belong to the selected industry
 */
const selectSubcategory = () => {
    const industryId = $("#js-search-b2b-industry-select option:selected").val();
    const categorySelect = $("#js-search-b2b-category-select");

    categorySelect.find("optgroup").css("display", "none");
    categorySelect.find("optgroup option").prop("selected", false).css("display", "none");

    if (industryId) {
        const categorySelectOptgroup = categorySelect.find(`optgroup[data-id=${industryId}]`);
        const appliedFilters = categorySelect.data("appliedFilters");

        categorySelect.removeAttr("disabled").children().eq(0).html("Select category");
        categorySelectOptgroup.show().find("option").show();

        if (appliedFilters) {
            categorySelectOptgroup.find(`option[data-id="${appliedFilters}"]`).prop("selected", true);
        }
    } else {
        categorySelect.prop("disabled", true).children().eq(0).html("Select industry first");
    }
};

export default selectSubcategory;
