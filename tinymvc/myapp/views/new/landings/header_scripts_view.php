        <script src="<?php echo fileModificationTime('public/plug/js/lang_new.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-1-12-0/jquery-1.12.0.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/js/js.cookie.min2.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/textcounter-0-3-6/textcounter.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-validation-engine-2-6-2/js/jquery.validationEngine.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-bxslider-4-2-12/jquery.bxslider.min.js');?>"></script>
        <script src="<?php echo fileModificationTime("public/plug/lazyloading/index.js"); ?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-fancybox-2-1-7/js/jquery.fancybox.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-fancybox-2-1-7/js/_jquery.fancybox.scripts.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/popper-1-11-0/popper.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/util.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/tooltip.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/popover.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/modal.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/tab.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-dialog-1-35-4/js/bootstrap-dialog.js');?>"></script>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/jquery.fancybox.css');?>" />

        <script>
            var scrollupState = true;
            var widthBrowser = $(window).width();
            var heightBrowser = $(window).height();
            var fancyW = '70%';
            var fancyH = 'auto';
            var fancyWAlter = 0.7;
            var fancyWPr = 0.7;
            var fancyP = 30;
            var fancyMW = 700;

            $(function(){
                //	validationEngine
                $.fn.validationEngineLanguage = function(){};
                $.validationEngineLanguage = {
                    newLang: function(){
                        $.validationEngineLanguage.allRules = translate_js_lang({plug:'validationEngine'});
                    }
                };
                $.validationEngineLanguage.newLang();
                //	END validationEngine

                $('body').on('click', ".call-function:not(.disabled)", function(e){
                    e.preventDefault();
                    var $thisBtn = $(this);
                    var callBack = $thisBtn.data('callback');
                    callFunction(callBack, $thisBtn);
                    return false;
                });

                lazyLoadingInstance(".js-lazy", { threshhold: "10px" });
            });

            var showLoader = function (selector, text, position, index) {
                if(text == 'default' || text == undefined){
                    text = 'Sending...';
                }

                var index = index || 0;
                var position = position || 'absolute';
                var wrapper = $(selector);
                var positionWrapper = wrapper.css('position');
                var loader = wrapper.children('.ajax-loader');
                var positionClass = (position=='fixed')?' ajax-loader__fixed':' ajax-loader__absolute';

                if(index > 0){
                    index = 'style="z-index: ' + index + '"';
                }else{
                    index = "";
                }

                var template = '<div class="ajax-loader'+positionClass+'" '+index+'><i class="ajax-loader__icon"></i><span class="ajax-loader__text">'+text+'</span></div>';

                if(positionWrapper == 'static'){
                    wrapper.addClass('relative-b');
                }

                if(position == 'fixed'){
                    $('html').addClass('ajax-loader-lock');
                }

                if (0 === loader.length) {
                    loader = $(template);
                    wrapper.prepend(loader);
                }

                loader.css({display: 'flex'});
            }

            var hideLoader = function (selector) {
                var wrapper = $(selector);
                var loader = wrapper.children('.ajax-loader');
                $('html').removeClass('ajax-loader-lock');

                if (loader.length > 0) {
                    loader.hide();
                }
            }

            Object.size = function(obj) {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) size++;
                }
                return size;
            };
        </script>
        <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/scripts_general.js');?>"></script>
        <script>

    function open_modal_dialog(params){
        var btn                 = params.btn || null,
            title               = params.title || undefined,
            subTitle            = params.subTitle || undefined,
            footerContent       = params.footerContent || '',
            content             = params.content || '',
            isAjax              = params.isAjax || 0,
            btnSubmitText       = params.btnSubmitText || 'Submit',
            btnSubmitCallBack   = params.btnSubmitCallBack || undefined,
            btnCancelText       = params.btnCancelText || translate_js({plug:'BootstrapDialog', text: 'cancel'}),
            buttons             = params.buttons || [
                                    {
                                        label: btnCancelText,
                                        cssClass: 'btn-dark',
                                        action: function(dialogRef){
                                            dialogRef.close();
                                        }
                                    },
                                    {
                                        label: btnSubmitText,
                                        cssClass: 'btn-primary',
                                        action: function(dialogRef){

                                            if(btnSubmitCallBack == undefined){
                                                var $form = dialogRef.getModalBody().find('form');

                                                if($form.length){
                                                    $form.submit();
                                                }

                                            }else{
                                                var callBack = btnSubmitCallBack;
                                                var $button = this;
                                                $button.disable();

                                                window[callBack]($thisBtn);
                                                dialogRef.close();
                                            }
                                        }
                                    }
                                ],
            validate            = Boolean(~~params.validate) || false,
            classes             = params.classes || '',
            closeByBg           = params.closeByBg || undefined,
            closableModal       = Boolean(~~params.closable) || true,
            closeCallBack       = params.closeCallBack || undefined,
            keepModal           = params.keepModal;

        if(closeByBg !== undefined && closeByBg == true){
            closeByBg = true;
        }else{
            closeByBg = false;
        }

        BootstrapDialog.show({
            cssClass: 'info-bootstrap-dialog bootstrap-dialog--form inputs-40 ' + classes,
            title: title,
            type: 'type-light',
            size: 'size-wide',
            message: $('<div>'),
            onhide: function(){
                if(typeof closeCallBack === "function" && closeCallBack != undefined){
                    closeCallBack();
                }
            },
            onshow: function(dialog) {
                var $dialogHeader = dialog.getModalHeader().find('.bootstrap-dialog-header');
                var $modal_dialog = dialog.getModalDialog();
                var addValidationIfPossible = function () {
                    if(!validate){
                        return;
                    }

                    enableFormValidation(dialog.getMessage().find(".validateModal"));
                }

                $modal_dialog.addClass('modal-dialog-scrollable modal-dialog-centered');

                if (btn) {
                    $modal_dialog.addClass($(btn).data('classes'));
                }

                if(subTitle != undefined){
                    $dialogHeader.append('<h6 class="bootstrap-dialog-sub-title">' + subTitle + '</h6>');
                }

                if(!keepModal) {
                    $('.modal').modal('hide');
                }

                if(isAjax){
                    showLoader($modal_dialog.find('.modal-content'), 'Loading...');

                    $.get(content).done(function( html_resp ) {
                        setTimeout(function(){
                            hideLoader($modal_dialog.find('.modal-content'));

                            if(html_resp.length > 0){
                                dialog.getMessage().append(html_resp);
                                addValidationIfPossible();
                            }

                        }, 200);
                    });
                } else {
                    setTimeout(function(){
                        if(content.length > 0){
                            dialog.getMessage().append('<div class="modal-tinymce-text">' + content + '</div>');
                            addValidationIfPossible();
                        }
                    }, 200);
                }

                if (footerContent != '') {
                    dialog.getModalFooter().html(footerContent).show();
                } else if(Object.size(buttons) > 0){
                    $modal_dialog.addClass('bootstrap-dialog--footer-submit');
                    dialog.getModalFooter().show();
                }

            },
            buttons: buttons,
            closable: closableModal,
            closeByBackdrop: closeByBg,
            closeByKeyboard: false,
            draggable: false,
            animate: true,
            nl2br: false
        });
    }

        </script>
