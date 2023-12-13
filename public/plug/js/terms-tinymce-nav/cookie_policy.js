var cookiesExplanationInfo;
var cookiesExplanationThirdPartyInfo;

$(function() {
    cookiesExplanationInfo = $('#js-cookies-explanation-info');
    cookiesExplanationThirdPartyInfo = $('#js-3rd-party-cookies-explanation-info');
    mobileDataTable(cookiesExplanationInfo);
    mobileDataTable(cookiesExplanationThirdPartyInfo);
    cookiesExplanationInfoInit();
});

jQuery(window).on('resizestop', function() {
    cookiesExplanationInfoInit();
})


function cookiesExplanationInfoInit() {
    if ((cookiesExplanationInfo.length > 0) && ($(window).width() < 1100)) {
        cookiesExplanationInfo.addClass('main-data-table--mobile');
    }

    if ((cookiesExplanationThirdPartyInfo.length > 0) && ($(window).width() < 1100)) {
        cookiesExplanationThirdPartyInfo.addClass('main-data-table--mobile');
    }
}
