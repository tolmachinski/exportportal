<?php if(logged_in()){?>
    <script>
        var addCommentCallback = function (response){
            if(typeof _notifyContentChangeCallback !== 'undefined') {
                _notifyContentChangeCallback();
            }
        };
        var editCommentCallback = function (response){
            if(typeof _notifyContentChangeCallback !== 'undefined') {
                _notifyContentChangeCallback();
            }
        };
        var addCommentReplyCallback = function (response){
            if(typeof _notifyContentChangeCallback !== 'undefined') {
                _notifyContentChangeCallback();
            }
        };
        var editCommentReplyCallback = function (response){
            if(typeof _notifyContentChangeCallback !== 'undefined') {
                _notifyContentChangeCallback();
            }
        };
        /* var deleteComment = function(caller){
            var button = $(caller);
            var url = __site_url  + '/items_comments/ajax_comment_operation/delete_comment'
            var item = button.data('item') || null;
            var comment = button.data('comment') || null;
            var onRequestSuccess = function(response) {
                if(typeof window.deleteCommentCallback !== 'undefined') {
                    deleteCommentCallback(response, button);
                }
            };

            if(null !== url && null !== comment && null !== item) {
                $.post(url, { comment: comment, item: item }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        }; */
        var deleteCommentCallback = function(response, button) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if(response.mess_type == 'success'){
                button.closest('.product-comments__item').fadeOut('normal', function(){
                    $(this).remove();
                });
            }
        };
    </script>
<?php } ?>

<?php if(have_right('moderate_content')) { ?>
    <script>
        var moderateCommentCallback = function(button, response) {
            systemMessages(response.message, response.mess_type);
            if (response.mess_type == 'success') {
                button.remove();
            }
        };
        var moderateComment = function(opener){
            var button = $(opener);
            var comment = button.data('comment') || null;
            var url = __site_url + "items_comments/ajax_comment_operation/moderate_comment";
            var onRequestSuccess = function(response) {
                if(typeof window.moderateCommentCallback !== 'undefined') {
                    moderateCommentCallback(button, response);
                }
            };

            if(null !== comment) {
                $.post(url, { comment: comment }, null, 'json').done(onRequestSuccess).fail(onRequestError);
            }
        }
    </script>
<?php } ?>

<script>
    $(function(){
        var hasHash = location.hash !== '';
        var scrollToView = function() {
            var commentId = location.hash || null;
            var commentBlock = null !== commentId ? $(commentId) : $();
            if(null === commentId || 0 === commentBlock.length) {
                return;
            }

            var unfoldButton = commentBlock.closest('.product-comments__item:has(.product-comments__more-reply)').find('.product-comments__more-reply');
            if(unfoldButton.length) {
                unfoldButton.click();
            }

            setTimeout(function() {
                scrollToElement(commentBlock, 70, 200);
            }, 900);
        }

        $(window).on('hashchange', scrollToView);
        if(hasHash) {
            scrollToView();
        }

        btnShowMoreReply();
    });

    var showMoreReply = function(obj){
        var $this = $(obj);

        $this.hide().closest('.product-comments--replies').find('.product-comments--hide .product-comments__item').slideDown();
    }

    var btnShowMoreReply = function(obj){
        $('.product-comments--first').each(function(){
            var commentsTotal = 0;
            $(this).find('> .product-comments--hide').each(function(){
                if($(this).find('.product-comments__item').length){
                    commentsTotal++;
                }
            });

            // console.log(commentsTotal);
            if(!commentsTotal){
                $(this).find('.product-comments__more-reply').hide();
            }
        });
        
    }
</script>