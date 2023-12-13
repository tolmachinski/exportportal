<script type="text/javascript">

<?php if(have_right_or('manage_community_questions,community_questions_administration')){?>
	var delete_comment = function(obj) {
		var $this = $(obj),
			comment = $this.data('comment');

		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'community_questions/ajax_comments_operation/delete',
			data: {comment: comment},
			dataType: 'json',
			success: function(data) {
				systemMessages(data.message, data.mess_type);

				if(data.mess_type === 'success') {
					var $ulBlock = $this.closest('ul.questions-comments');
					$this.closest('li.questions-comments__item').fadeOut('normal', function() {
						$(this).remove();
						if($ulBlock.find('li.questions-comments__item').length < 1) {
							$ulBlock.html('<li class="mt-15 info-alert-b"><i class="ep-icon ep-icon_info-stroke ml-10"></i> ' + translate_js({plug:'general_i18n', text: 'community_be_the_first_text'}) + '</li>');
						}
					});

					var $nrCommentsElement = $('.js-count-comments');
					var nrComments = $nrCommentsElement.data('count');
					nrComments--;

					$nrCommentsElement.text(nrComments);
					$nrCommentsElement.data('count', nrComments);
				}
			}
		});
	};

	var delete_answer = function(obj) {
		var $this = $(obj),
			answer = $this.data('answer');

		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'community_questions/ajax_answers_operation/delete',
			data: {answer: answer},
			dataType: 'json',
			success: function(data) {
				systemMessages(data.message, data.mess_type);

				if(data.mess_type === 'success') {
					$this.closest('li.questions-answers__item').fadeOut('normal', function() {
						$(this).remove();
					});

					var $nrAnswersB = $this.closest('.questions__item').find('.questions__item-answer-line-count'),
						nrAnswersB = parseInt($nrAnswersB.text());

					$nrAnswersB.text(--nrAnswersB + ' ' + translate_js({plug:'general_i18n', text: 'community_answers_word'}));
					if(typeof dtQuestions !== 'undefined'){
						dtQuestions.fnDraw(false);
					}
				}
			}
		});
	};

	var delete_question = function(obj) {
		var $this = $(obj),
			question = $this.data('question');

		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'community_questions/ajax_questions_operation/delete',
			data: {question: question},
			dataType: 'json',
			success: function(data) {
				systemMessages(data.message, data.mess_type);

				if(data.mess_type === 'success') {
					if(typeof dtQuestions === 'undefined'){
						$this.closest('li.questions__item').fadeOut('normal', function() {
							$(this).remove();
						});
					} else{
						dtQuestions.fnDraw(false);
					}
				}
			}
		});
	};
<?php } ?>

<?php if (have_right('community_questions_administration')) { ?>
	var moderate_question = function(obj) {
		var $this = $(obj);
		var question = $this.data('question');

		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'community_questions/ajax_questions_operation/moderate',
			data: {question: question},
			dataType: 'json',
			success: function(data) {
				systemMessages(data.message, data.mess_type);

				if(data.mess_type === 'success') {
					$this.closest('li').remove();
				}
			}
		});
	};

	var moderate_comment = function(obj) {
		var $this = $(obj),
            comment = $this.data('comment');

		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'community_questions/ajax_comments_operation/moderate',
			data: {comment: comment},
			dataType: 'json',
			success: function(data) {
				systemMessages(data.message, data.mess_type);

				if(data.mess_type === 'success') {
					$this.remove();
				}
			}
		});
	};

	var moderate_answer = function(obj) {
		var $this = $(obj),
            answer = $this.data('answer');

		$.ajax({
			type: 'POST',
			url: __current_sub_domain_url + 'community_questions/ajax_answers_operation/moderate',
			data: {answer: answer},
			dataType: 'json',
			success: function(data) {
				systemMessages(data.message, data.mess_type);

				if(data.mess_type === 'success') {
					$this.closest('li').remove();
				}
			}
		});
	};
<?php } ?>
</script>
