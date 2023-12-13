const multilistToggle = btn => {
    btn.toggleClass("filter-options-multilist__toggler--rotate").closest(".js-filter-multilist-item").find(".js-filter-multilist-subitems").toggle();
};

export default multilistToggle;
