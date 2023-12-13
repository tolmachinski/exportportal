<?php if (!empty($articleInfo)) { ?>
    <div class="ep-tinymce-text ep-links-break pt-25">
        <h2 class="tac pb-10"><?php echo $articleInfo['country'] . ' ' . ((1 == $articleInfo['type']) ? 'Imports' : 'Exports'); ?></h2>
        <?php if (!empty($articleInfo['photo']) && isset($articleInfo['photoLink'])) {?>
            <img src="<?php echo $articleInfo['photoLink']; ?>" alt="article info"/>
        <?php }
            echo $articleInfo['text']; ?>
    </div>
<?php }?>

<?php if (!empty($requirementInfo)) {?>
    <div class="ep-tinymce-text ep-links-break pt-25">
        <h2 class="tac pb-10">Customs requirements of <?php echo $requirementInfo['country_name']; ?></h2>

        <?php echo $requirementInfo['customs_text']; ?>
    </div>
<?php }?>
