<main>

    <!-- <div class="bloggers-preloader">
        <img src="<?php echo fileModificationTime('public/img/bloggers/preloader.gif', __IMG_URL); ?>" alt="Export Portal">
    </div> -->
    <div class="absolute-images"></div>
    <div class="page-content">
        <div class="container">
            <h1>Congratulations!
                <span>You’ve made it to the second round.</span>
            </h1>
            <div class="text">
                <p>
                    We’re impressed with your work, and we want to see more.
                    You’re one step closer to joining the global team of Export Portal influencers!
                    All you have to do is write an article!
                    Thank you in advance and happy writing!
                </p>
            </div>
            <div class="video">
                <?php if(!empty($url['video'])) { ?>
                    <iframe width="100%" height="100%" src="<?php echo $url['video']; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                <?php } ?>
            </div>
            <!-- <div class="text">
                <p>
                    This is your moment to show your talent and skill, while shining light on unknown heroes and heroines
                    by sharing their stories. Your submitted work will be reviewed and assessed from there on. Selected
                    entries will be brought on as part of our international writers, blogger, and vloggers and will be
                    the voice of Export Portal. The best articles and videos that promote inspirational figures will
                    be featured on Export Portal’s website to motivate other individuals or companies facing similar
                    issues around the world.
                </p>
                <br>
                <p>
                    PLEASE NOTE: By applying for the position and submitting your content as part of the process, you are
                    giving Export Portal legal permission to use the submitted content on our site at our discretion,
                    without citation or compensation, even if you are not chosen for the position. Only one submission will be accepted.
                </p>
            </div> -->
            <form action="<?php echo $url['validate_code']; ?>"
                class="content-form code-submit-form js-ep-self-autotrack"
                data-tracking-events="submit"
                data-tracking-alias="form-bloggers-access-code">
                <input class="input" type="text" name="code" placeholder="Enter Access Code">
                <button class="button" type="submit">Submit</button>
            </form>
        </div>
    </div>
    <div class="quotation">
        <div class="quotation__image">
            <img src="<?php echo fileModificationTime('public/img/bloggers/typewriter.jpg', __IMG_URL); ?>" alt="typewriter">
        </div>
        <div class="quotation__info">
            <div class="quotation__text">“As a writer you should not judge. You should
                <span>understand</span>.”</div>
            <div class="quotation__author">- Ernest Hemingway</div>
        </div>
    </div>
</main>
