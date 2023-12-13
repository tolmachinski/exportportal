<ul class="questions <?php echo $search_params ? 'mt-5' : ''; ?>" id="community-questions-list">
	<?php if (empty($questions)) { ?>
		<?php app()->view->display('new/questions/results_not_found_view');?>
	<?php } else { ?>
		<?php app()->view->display('new/questions/item_question_view', array('questions' => $questions));?>
	<?php } ?>
</ul>
<?php
    encoreEntryLinkTags('questions_index');

	if ($current_page === 'all') {
		encoreLinks();
	}
?>
