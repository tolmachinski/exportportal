const toggleAccordion = button => {
    button.toggleClass("active").find(".js-accordion-text-wr").slideToggle("fast");
};

export default toggleAccordion;
