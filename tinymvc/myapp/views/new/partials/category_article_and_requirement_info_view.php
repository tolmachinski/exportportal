<?php if (!empty($articleInfo)) {?>
    <div class="ep-tinymce-text ep-links-break pt-50">
        <?php foreach ($articleInfo as $article) {?>
            <h3 class="tac pb-10"><?php echo((1 == $article['type']) ? 'Import to ' : 'Export from ') . $article['country']['country']; ?></h3>
            <?php if (!empty($article['photo']) && isset($article['photoLink'])) {?>
                <img src="<?php echo $article['photoLink']; ?>" alt="blog"/>
            <?php } ?>
            <?php echo $article['text']; ?>
        <?php }?>
    </div>
<?php }?>

<?php if (!empty($catArticle)) { ?>
    <div class="ep-tinymce-text ep-links-break pt-50">
        <?php if ($catArticle['photo'] && $catArticle['photoLink']) {?>
            <img src="<?php echo $catArticle['photoLink']; ?>" alt="<?php echo $category['name']; ?>"/>
        <?php }?>

        <?php echo $catArticle['text']; ?>
    </div>
<?php }?>

<?php if (!empty($requirementInfo)) {?>
    <div class="ep-tinymce-text ep-links-break pt-50">
        <h3 class="tac pb-10">Customs requirements of <?php echo $requirementInfo['country_name']; ?></h3>
        <?php echo $requirementInfo['customs_text']; ?>
    </div>
<?php }?>
