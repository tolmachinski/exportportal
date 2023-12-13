<link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset("public/build/styles_popup_show_preactivation.css");?>" />

<div class="inputs-40">
	<ul class="vertical-circle-list">
		<?php
            foreach($view_messages as $view_messages_item){
				if($view_messages_item['has_link']){
					$view_messages_item_link_start = '<a class="link" href="'.__SITE_URL.$view_messages_item['link'].'">';
					$view_messages_item_link_end = '</a>';
					$view_messages_item_link_icon = '<i class="ep-icon ep-icon_arrow-line-right"></i>';
				}else{
					$view_messages_item_link_start = '<span class="not-link">';
					$view_messages_item_link_end = '</span>';
					$view_messages_item_link_icon = '';
				}

				$view_messages_optional = '';
				if($view_messages_item['is_optional']){
					$view_messages_optional = '<span class="txt-gray txt-normal">(Optional)</span>';
				}
		?>

			<li class="vertical-circle-list__item">
				<div class="vertical-circle-list__ttl">
                    <?php
                    echo "{$view_messages_item_link_start}
                            {$view_messages_item['title']}
                            {$view_messages_optional}
                            {$view_messages_item_link_icon}
					    {$view_messages_item_link_end}";
                    ?>
				</div>

				<?php if(!empty($view_messages_item['text'])){?>
					<div class="vertical-circle-list__additional-txt"><?php echo $view_messages_item['text'];?></div>
				<?php }?>
			</li>
		<?php }?>
    </ul>

    <?php if(
            $progress == 'uncompleted'
            || (have_right('upgrade_group') && filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN))
        ){ ?>
        <div class="info-alert-b">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <?php if($progress == 'uncompleted'){ ?>
                <span>
                    <strong><?php echo translate('upgrade_what_next_modal_info_verify_text');?></strong>
                    <?php echo translate('upgrade_what_next_modal_info_verify_text_questions');?>
                    <button
                        class="call-action btn-link"
                        data-js-action="modal:call-show-main-chat"
                        type="button"
                    ><?php echo translate('upgrade_what_next_modal_info_btn_support');?></button>.
                </span>
            <?php }else if(have_right('upgrade_group') && filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN)){ ?>
                <div>
                    <div><strong><?php echo translate('upgrade_what_next_modal_info_free_title');?></strong></div>
                    <?php echo translate('upgrade_what_next_modal_info_free_desc', ['{{DATE}}' => $freePeriodDate]);?>
                </div>
                <a class="btn btn-primary mt-8" href="<?php echo __SITE_URL;?>upgrade"><?php echo translate('upgrade_what_next_modal_info_free_btn_txt');?></a>
            <?php } ?>
        </div>
    <?php } ?>

	<div class="what-next__learn-more-b">
		<h3 class="what-next__ttl"><?php echo translate('upgrade_what_next_modal_text_learn_more');?></h3>
		<a class="btn btn-primary" <?php echo addQaUniqueIdentifier("whats-next_popup_learn-more_btn")?> href="<?php echo __SITE_URL;?>learn_more"><?php echo translate('upgrade_what_next_modal_btn_learn_more');?></a>
	</div>

	<div class="what-next__delimiter"></div>

	<div class="what-next__topics-b">
		<h3 class="what-next__ttl"><?php echo translate('upgrade_what_next_modal_title_topics');?></h3>

		<ul class="what-next__topics-list">
			<?php foreach($topics as $topics_item){ ?>
				<li class="what-next__topics-list-item">
					<a class="link" href="<?php echo $topics_item['link']; ?>">
						<img
							class="image"
							src="<?php echo __SITE_URL;?>public/img/what_next/topics/<?php echo $topics_item['image']; ?>"
							alt="<?php echo $topics_item['title']; ?>"
						>
						<span class="what-next__topics-list-name"><?php echo $topics_item['title']; ?></span>
					</a>
				</li>
			<?php } ?>
		</ul>
	</div>
</div>
