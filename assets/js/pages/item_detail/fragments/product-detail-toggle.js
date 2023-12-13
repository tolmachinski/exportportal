const productDetailToggle = btn => {
    btn.toggleClass("active").closest(".js-detail-info-wrapper").find(".js-detail-info-toggle").slideToggle();
};

export default productDetailToggle;
