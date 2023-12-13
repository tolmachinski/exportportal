<ul class="js-community-list community-user-list">
	<?php if (empty($questions)) { ?>
		<?php app()->view->display('new/help/results_not_found_view');?>
	<?php } else { ?>
        <?php
            $questions_params = array('questions' => $questions);
            if(isset($hide_user_info)){
                $questions_params['hide_user_info'] = $hide_user_info;
            }

            app()->view->display('new/user/community_help/item_question_view', $questions_params);
        ?>
	<?php } ?>
</ul>
