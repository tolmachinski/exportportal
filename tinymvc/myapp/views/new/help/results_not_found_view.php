<div class="faq-empty">
    <div class="faq-empty__information">
        <h2 class="faq-empty__title">SORRY!</h2>
        <p class="faq-empty__txt">Your search did not match any results.</p>
        <p class="faq-empty__txt faq-empty__txt--gray">
            Please, access the below listed pages for 
            a better answerregarding your issue.
        </p>
    </div>
    <div class="faq-empty__help">
        <div class="faq-empty__help-options">
            <p>Other type of help:</p>
            <?php if ('faq' !== $current_page) { ?>
                <a class="faq-empty__option" href="<?php echo __SITE_URL . 'faq'; ?>">Faq</a>
            <?php } ?>
            <?php //if ('user_guide' !== $current_page) { ?>
                <!-- <a class="faq-empty__option" href="<?php //echo __SITE_URL . 'user_guide'; ?>">User Guide</a> -->
            <?php //} ?>
            <?php if ('topics' !== $current_page) { ?>
                <a class="faq-empty__option" href="<?php echo __SITE_URL . 'topics/help'; ?>">Topics</a>
            <?php } ?>
            <?php if ('questions' !== $current_page) { ?>
                <a class="faq-empty__option" href="<?php echo __SITE_URL . 'questions'; ?>">Community help</a>
            <?php } ?>
        </div>
        <div class="faq-empty__question">
            <p>Question still open?</p>
            <a class="btn btn-primary w-200" href="<?php echo __SITE_URL . 'contact'; ?>">Submit Question</a>
        </div>
    </div>
</div>