var scrollTermsInit = function(termsModal){
    $('.js-scroll-terms .link').on('click', function(e){
        e.preventDefault();
        var $this = $(this);

        if (termsModal) {
            scrollToElement($this.attr('href'), 0, 500, "", '.fancybox-inner', 'position');
        } else {
            const callback = $(window).width() < 768 ? "closeFancyBox" : "";
            scrollToElement($this.attr('href'), 70, 500, callback);
        }
    });
}

$(function(){
    if(ieDetection()){
        $(".terms-tinymce-nav").stick_in_parent();
    }
});
