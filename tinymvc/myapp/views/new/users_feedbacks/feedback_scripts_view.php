<script>
	function addFeedbackCallback(resp){
		if(typeof dtFeedbacksList === 'undefined'){
			_notifyContentChangeCallback();
		}
	}

	function editFeedbackCallback(resp){
		if(typeof dtFeedbacksList === 'undefined'){
			_notifyContentChangeCallback();
		}
	}

	<?php if(have_right('moderate_content') || have_right('leave_feedback')){?>
		function addReplyFeedbackCallback(resp){
			if(typeof dtFeedbacksList === 'undefined'){
				_notifyContentChangeCallback();
			}
		}

		function editReplyFeedbackCallback(resp){
			if(typeof dtFeedbacksList === 'undefined'){
				_notifyContentChangeCallback();
			}
		}
	<?php }?>

<?php if(have_right('moderate_content')){?>
	var moderate_feedback = function(obj){
		var $this = $(obj);
		var ftype = $this.data('type');
		var feedback = [];
		feedback[0] = $this.data('feedback');

		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL . 'feedbacks/ajax_feedbacks_administration_operation/moderate';?>',
			data: { checked_feedbacks: feedback },
			beforeSend: function(){  },
			dataType: 'json',
			success: function(data){
				systemMessages( data.message, data.mess_type );

				if(data.mess_type == 'success'){
					$this.remove();
				}
			}
		});
	}
<?php }?>
</script>
