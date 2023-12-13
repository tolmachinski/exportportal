<script type="text/template" id="js-wr-terms-add-item-policy">
    <form id="js-form-adult-items-policy" class="validateModal">
        <div class="ep-large-text2">
            <p>Adult, weapons, and drug-related items must be listed in the correct category and must follow our guidelines, included in the linked policy.</p>
        </div>

        <label class="custom-checkbox">
            <input class="js-add-terms validate[required]" type="checkbox" name="terms">
            <span class="custom-checkbox__text-agreement">
                By adding item to the chosen category you agree to the
                <a class="txt-underline" href="<?php echo __SITE_URL; ?>terms_and_conditions/tc_restricted_adult_items_policy" target="_blank">Adult items policy</a>
            </span>
        </label>
    </form>
</script>

<script>
    var termsSubmit = false;

    $('.form-categories__item').click(function() {
        if ($(this).data('adult')) {
            openAdultItemsTerms();
        }
    })

    $('body').on('submit', '#js-form-adult-items-policy', function(e){
        e.preventDefault();
        BootstrapDialog.closeAll();
    })

    var openAdultItemsTerms =  function(){
        var content = $('#js-wr-terms-add-item-policy').html();
        var buttons = [{
                label: 'Submit',
                cssClass: 'btn-primary mnw-130',
                action: function(){
                    termsSubmit = true;
                    $('body').find('#js-form-adult-items-policy').submit();
                }
            }];

        BootstrapDialog.show({
            cssClass: 'info-bootstrap-dialog info-bootstrap-dialog--mw-570',
            title: 'Adult items policy',
            message: $('<div></div>'),
            onhide: function(){
                        if(!termsSubmit){
                            closeFancyBox();
                        }
                    },
            onshow: function(dialog) {
                var $modal_dialog = dialog.getModalDialog();
                var addValidationIfPossible = function () {
                    enableFormValidation(dialog.getMessage().find(".validateModal"));
                }
                $modal_dialog.addClass('modal-dialog-centered');
                dialog.getMessage().append(content);
                $('.modal-content').addClass('mr-0');

                addValidationIfPossible();
            },
            buttons:buttons,
            type: 'type-light',
            size: 'size-wide',
            closable: true,
            closeByBackdrop: false,
            closeByKeyboard: false,
            draggable: false,
            animate: true,
            nl2br: false
        });
    }
</script>
