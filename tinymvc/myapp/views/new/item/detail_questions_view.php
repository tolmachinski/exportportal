<?php views()->display('new/items_questions/scripts_questions_view'); ?>

<script type="text/javascript">
	var reUploadQuestion = true;
	var id_item = intval('<?php echo $item['id'];?>');

	function getQuestions() {

		if (!reUploadQuestion) {
			return false;
		}

		reUploadQuestion = false;
		showLoader('.questions-load');
		$.ajax({
			url: 'items_questions/ajax_question_operation/show',
			type: 'POST',
			dataType: 'JSON',
			data: {item: id_item},
			success: function (data) {
//				if(data.count == 0)
//					$('.questions-b .show-page').hide();
//				else
//					$('.questions-b .show-page').show();

				$('.questions-load').html(data.html);
				hideLoader('.questions-load');
			}
		});
	}
</script>

<?php if (!empty($item['description'])) { ?>
<div class="display-n" itemprop="description">
	<?php echo strip_tags(truncWords($item['description'])); ?>
</div>
<?php } ?>

<a class="questions-f" name="questions-f"></a>

<div class="product-detail__comments-page">
	<div class="title-public pt-0">
		<h2 class="title-public__txt">Questions</h2>

		<?php if (have_right('write_questions_on_item') || !logged_in()) { ?>
			<div class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#" <?php echo addQaUniqueIdentifier("items-questions__ask-question__open-dropdown") ?>>
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<div class="dropdown-menu">
					<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="Add question" href="<?php echo __SITE_URL;?>items_questions/popup_forms/add_question/<?php echo $item["id"]; ?>" title="Add question" <?php echo addQaUniqueIdentifier("items-questions__ask-question") ?>>
						<i class="ep-icon ep-icon_question-circle"></i>
						Ask Question
					</a>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="questions-load">
		<?php views()->display('new/items_questions/list_questions_view'); ?>
	</div>
</div>
