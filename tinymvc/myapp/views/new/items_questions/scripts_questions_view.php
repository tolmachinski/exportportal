<script type="text/javascript">
    var deleteQuestion = function(callerObj){
        var button = $(callerObj);
        var url = button.data('href') || (location.origin + '/items_questions/ajax_question_operation/delete');
        var question = button.data('question') || null;
        var onRequestSuccess = function(response) {
            systemMessages(response.message, response.mess_type );
            if(response.mess_type == 'success'){
                deleteQuestionCallback(button, response);
            }
        };
        if(null === url || null === question) {
            return;
        }

        $.post(url, { question: question }, null, 'json').done(onRequestSuccess).fail(onRequestError);
    };
    var deleteQuestionCallback = function(button, response) {
        button.closest('.product-comments__item').fadeOut('slow', function(){
            $(this).remove();
        });
    };
    var addQuestionCallback = function (response){
        callFunction('_notifyContentChangeCallback');
    };
    var addQuestionReplyCallback = function(response) {
        callFunction('_notifyContentChangeCallback');
	};
    var editQuestionCallback = function (response){
        callFunction('_notifyContentChangeCallback');
    };
    var editQuestionReplyCallback = function(response) {
        callFunction('_notifyContentChangeCallback');
	};
</script>
<?php if(have_right('moderate_content')) { ?>
    <script type="text/javascript">
        var moderateQuestion = function(obj) {
            var $this = $(obj);
            var question = $this.data('question');

            $.ajax({
                type: 'POST',
                url: __site_url + '/items_questions/ajax_questions_administration_operation/moderate',
                data: { question : question },
                dataType: 'json',
                success: function(data){
                    systemMessages( data.message, data.mess_type );

                    if(data.mess_type == 'success'){
                        $this.closest('.dropdown').remove();
                    }
                }
            });
        };
    </script>
<?php } ?>
