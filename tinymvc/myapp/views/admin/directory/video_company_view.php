<div class="wr-form-content w-800 mh-600">
    <?php if (!empty($video)) { ?>
        <?php echo generate_video_html(cleanOutput($video['urlId']), cleanOutput($video['source']), '100%', 443, false); ?>
     <?php } else { ?>
        <p>There is no video</p>
    <?php } ?>
</div>
