var scrollTermsInit = function(termsModal){
    $('.js-scroll-terms .link').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);

        if (termsModal) {
            scrollToElement($this.attr('href'), 0, 500, "", '.fancybox-inner', 'position');
        } else {
            scrollToElement($this.attr('href'), 70, 500);
        }
    });
}

$(function() {
    if (ieDetection()) {
        $(".terms-tinymce-nav").stick_in_parent();
    }
});

