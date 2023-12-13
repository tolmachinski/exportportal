$(function(){
    $(".fancybox").fancybox({
        width		: fancyW,
        height		: fancyH,
        maxWidth	: 700,
        autoSize	: false,
        loop : false,
        helpers : {
            title: {
                type: 'inside',
                position: 'top'
            },
            overlay: {
                locked: false
            }
        },
        lang : __site_lang,
        i18n : translate_js_one({plug:'fancybox'}),
        modal: true,
        padding: fancyP,
        closeBtn : true,
        closeBtnWrapper: '.fancybox-skin .fancybox-title',
        beforeShow : function() {
            var $elem = this.element;

            if($elem.data('dashboard-class') != undefined){
                $('.fancybox-inner').addClass($elem.data('dashboard-class'));
            }
        },
        afterShow : function() {
            var $fancyboxContent = $('.fancybox-inner .modal-flex__content');
            var $fancyboxContent2 = $('.fancybox-inner .modal-b__content');

            setTimeout(function(){
                if($fancyboxContent.length && $fancyboxContent.hasScrollBar()){
                    $fancyboxContent.addClass('pr-15');
                }

                if($fancyboxContent2.length && $fancyboxContent2.hasScrollBar()){
                    $fancyboxContent2.addClass('pr-15');
                }
            }, 100);
        },
        beforeLoad : function() {
            var $elem = this.element;

            if($elem.data("before-callback") != undefined){
                window[$elem.data("before-callback")](this);
            }

            if($elem.data('title'))
                this.title = htmlEscape($elem.data('title'));

            if($elem.data('h')){
                this.autoHeight = false;
                this.height = $elem.data('h');
            }

            if($elem.data('w')){
                this.width = $elem.data('w')
                this.autoWidth = false;
            }else{
                this.width = fancyW;
            }

            if($elem.data('mw')){
                this.maxWidth = $elem.data('mw')
            }

            if($elem.data('mnh')){
                this.minHeight = $elem.data('mnh')
            }

            if($elem.data('p') != undefined){
                this.padding = [$elem.data('p'),$elem.data('p'),$elem.data('p'),$elem.data('p')]

                if( $elem.data('p') == 0){
                    this.wrapCSS = 'fancybox-title--close';
                }
            }else{
                this.padding = [fancyP,fancyP,fancyP,fancyP];
            }
        },
        onUpdate :  function() {
            //myRepositionFancybox();
        }
    },".fancybox");

    $(".fancyboxValidateModal").fancybox({
        width		: fancyW,
        height		: fancyH,
        maxWidth	: 700,
        autoSize	: false,
        loop : false,
        helpers : {
            title: {
                type: 'inside',
                position: 'top'
            },
            overlay: {
                locked: true
            }
        },
        lang : __site_lang,
        i18n : translate_js_one({plug:'fancybox'}),
        modal: true,
        closeBtn : true,
        padding : fancyP,
        closeBtnWrapper: '.fancybox-skin .fancybox-title',
        beforeShow : function() {
            var $elem = this.element;

            if($elem.data('dashboard-class') != undefined){
                $('.fancybox-inner').addClass($elem.data('dashboard-class'));
            }
        },
        beforeLoad : function() {
            var $elem = this.element;
            if($elem.data("before-callback") != undefined){
                if(window[$elem.data("before-callback")](this) == false)
                    return false;
            }

            if($elem.data('title') != undefined){
                this.title = htmlEscape($elem.data('title'))+"&emsp;";
            }

            if($elem.data('title-type') != undefined){
                this.title = $elem.data('title')+"&emsp;";
            }

            if($elem.data('h')){
                this.autoHeight = false;
                this.height = $elem.data('h');
            }

            if($elem.data('w')){
                this.width = $elem.data('w')
                this.autoWidth = false;
            }else{
                this.width = fancyW;
            }

            if($elem.data('mw')){
                this.maxWidth = $elem.data('mw')
            }

            if($elem.data('p') != undefined){
                this.padding = [$elem.data('p'),$elem.data('p'),$elem.data('p'),$elem.data('p')]

                if( $elem.data('p') == 0){
                    this.wrapCSS = 'fancybox-title--close';
                }
            }else{
                this.padding = [fancyP,fancyP,fancyP,fancyP];
            }

            this.ajax.caller_btn = $elem;
        },
        ajax: {
            complete: function(jqXHR, textStatus) {
                var $caller_btn = this.caller_btn;

                $(".validateModal").validationEngine('attach', {
                    promptPosition : "topLeft:0",
                    autoPositionUpdate : true,
                    focusFirstField: false,
                    scroll: false,
                    showArrow : false,
                    addFailureCssClassToField : 'validengine-border',
                    onValidationComplete: function(form, status){
                        if(status){
                            if($(form).data("callback") != undefined)
                                window[$(form).data("callback")](form, $caller_btn);
                            else
                                modalFormCallBack(form, $caller_btn);
                        }else{
                            systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
                        }
                    }
                });
            }
        },
        onUpdate :  function() { }
    }, ".fancyboxValidateModal");

    $('body').on('click', '.js-close-fancybox', function(){
        closeFancyBox();
    });
});

function closeFancyBox(){
    if($('.fancybox-skin .dtfilter-popup .nav-tabs').length){
        var navTabs = $('.fancybox-skin .dtfilter-popup .nav-tabs');
        var firstLi = navTabs.find('li:first-child');

        firstLi.find('.nav-link').addClass('active').end()
            .siblings().find('.nav-link').removeClass('active');

        var tabContent = $('.fancybox-skin .dtfilter-popup .tab-content');

        tabContent.find('.tab-pane:first-child').addClass('active')
            .siblings().removeClass('active');
    }

    $('.validateModal').validationEngine('detach');
    $.fancybox.close();
}
