<?php
    $total_completed = $complete_profile['total_completed'];
?>
<div class="uinfo-complete<?php echo $is_dashboard ? "" : " uinfo-complete--popup"?>">
    <div class="uinfo-complete__header">
        <?php if ($is_dashboard) { ?>
            <div class="uinfo-complete__ttl"><?php echo translate('complete_profile_title', ["{{STEPS_COUNT}}" => "({$complete_profile['countCompleteOptions']}/{$complete_profile['countOptions']})"]); ?></div>
            <div class="uinfo-complete__desc"><?php echo translate('complete_profile_description'); ?></div>
        <?php } ?>
    </div>

    <div class="uinfo-complete__list">
        <?php foreach ($complete_profile['options'] as $optionIndex => $profile_completion_option) { ?>
            <?php $isCompleted = (int) $profile_completion_option['option_completed'] === 1; ?>
            <div class="uinfo-complete__list-item <?php echo $isCompleted ? ' uinfo-complete__list-item--completed' : ''; ?>" <?php echo addQaUniqueIdentifier('ff-complete-profile__' . str_replace('_', '-', $profile_completion_option['option_alias']));?>>
                <a href="<?php echo __SITE_URL . $profile_completion_option['option_url']; ?>">
                    <div class="uinfo-complete__list-img">
                        <?php $imgName = $profile_completion_option['option_image'] . ($is_dashboard ? "" : "_popup"); ?>
                        <img class="image" src="<?php echo asset("public/build/images/profile_completion/" . $imgName . ".jpg"); ?>" alt="<?php echo translate("translate_profile_option_alt_{$profile_completion_option['option_alias']}", null, true); ?>">
                        <?php echo $isCompleted ? "<div class=\"uinfo-complete__list-complete-badge\">COMPLETED</div>": ""?>
                    </div>
                    <div class="uinfo-complete__list-ttl<?php if (!$is_dashboard) { echo ' uinfo-complete__list-ttl--popup'; } ?><?php echo $profile_completion_option['option_alias'] === "company_items" ? " uinfo-complete__list-ttl--item" : "" ?>">
                        <?php echo $profile_completion_option['option_name']; ?>
                    </div>
                    <?php ?>
                    <?php if ($is_dashboard) { ?>
                        <div class="uinfo-complete__list-desc">
                            <?php echo $profile_completion_option['option_description']; ?>
                        </div>
                    <?php } ?>
                </a>
            </div>
        <?php } ?>
    </div>
</div>
