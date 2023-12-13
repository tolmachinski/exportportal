<div class="cft-nav">
    <nav class="cft-nav__container">
        <a class="cft-nav__logo" href="<?php echo __SITE_URL;?>">
            <img src="<?php echo __IMG_URL;?>public/img/ep-logo/ep-logo.png" alt="exportportal">
        </a>
        <div class="cft-burger">
            <div class="cft-burger__line bar1"></div>
            <div class="cft-burger__line bar2"></div>
            <div class="cft-burger__line bar3"></div>
        </div>
        <ul class="cft-nav__list">
            <li>
                <a class="cft-nav__link" href="<?php echo get_static_url('register/co', __SITE_URL, $campaign['id_campaign']);?>"><?php echo translate('register_button_text');?></a>
            </li>
            <li>
                <a class="cft-nav__link" href="<?php echo __SITE_URL;?>buying">Buying</a>
            </li>
            <li>
                <a class="cft-nav__link" href="<?php echo __SITE_URL;?>selling">Selling</a>
            </li>
            <li>
                <a class="cft-nav__link" href="<?php echo __SITE_URL;?>faq">Faq</a>
            </li>
        </ul>
    </nav>
</div>

<header class="cft-header">
    <div class="cft-header__container">
        <h1 class="cft-header__title">Are you an artist or know an artist whose handmade products can fascinate the world?</h1>
        <a class="btn btn-primary cft-header__link" href="<?php echo get_static_url('register/co', __SITE_URL, $campaign['id_campaign']);?>">Join Export Portal!</a>
    </div>
</header>

<div class="cft-title-fixed">
    <div class="cft-bullets">
        <button class="cft-bullets__point active"></button>
        <button class="cft-bullets__point"></button>
    </div>
    <div class="cft-title-fixed__container">

    </div>
</div>

<div>
    <section class="cft-title 1">
        <div class="cft-title__container">
            <div class="cft-title-fixed__block">
                <p>Join a creative marketplace with <span>millions of buyers around the world.</span></p>
                <p>Open your doors to <span>new markets.</span></p>
                <p>Craft locally, sell <span>globally!</span></p>
            </div>
        </div>
    </section>

    <!-- <section class="cft-title 2">
        <div class="cft-title__container">
            <div class="cft-title-fixed__block">
                <p>Open your doors to <span>new markets.</span></p>
            </div>
        </div>
    </section>

    <section class="cft-title 3">
        <div class="cft-title__container">
            <div class="cft-title-fixed__block">
                <p>Craft locally, sell <span>globally!</span></p>
            </div>
        </div>
    </section> -->

    <section class="cft-title 2">
        <div class="cft-title__container">
            <div class="cft-title-fixed__block last">
                <p class="white">Set-up your Shop Today!</p>
                <a class="btn btn-light cft-title-btn" href="<?php echo get_static_url('register/co', __SITE_URL, $campaign['id_campaign']);?>">Join Export Portal!</a>
            </div>
            <img src="<?php echo __IMG_URL;?>public/img/pages/crafting/red_bck.jpg" alt="exportportal">
        </div>
    </section>
</div>

<section class="cft-benefits">
    <h2 class="cft-benefits__headline">Join a creative marketplace with millions of buyers around the world.</h2>
    <div class="cft-benefits__container">
        <div class="cft-benefits__block">
            <i class="ep-icon ep-icon_card-lock"></i>
            <div></div>
            <div class="cft-benefits__title">Secure Selling</div>
            <div class="cft-benefits__text">Our blockchain-enabled marketplace tracks & protects all transactions.</div>
            <a class="cft-benefits__link" href="<?php echo __SITE_URL;?>security"><?php echo translate('langing_button_learn_more');?></a>
        </div>
        <div class="cft-benefits__block">
            <i class="ep-icon ep-icon_basket-stroke"></i>
            <div></div>
            <div class="cft-benefits__title">Listing is easy!</div>
            <div class="cft-benefits__text">Boost your handmade products’ online visibility in three easy steps.</div>
            <a class="cft-benefits__link" href="<?php echo __SITE_URL;?>learn_more">Learn more</a>
        </div>
        <div class="cft-benefits__block">
            <i class="ep-icon ep-icon_comment-stroke"></i>
            <div></div>
            <div class="cft-benefits__title">24/7 Support</div>
            <div class="cft-benefits__text">Contact our world-class customer service team anytime you need help.</div>
            <a class="cft-benefits__link" href="<?php echo __SITE_URL;?>contact">Learn more</a>
        </div>
    </div>
</section>

<section class="cft-tiles">
    <div class="cft-tiles__row cft-tiles__row--left">
        <div class="cft-tiles__image">
            <img src="<?php echo __IMG_URL;?>public/img/pages/crafting/jug.jpg" alt="exportportal">
        </div>
        <div class="cft-tiles__info cft-tiles__info--right">
            <div class="cft-tiles__title">About Export Portal</div>
            <i class="ep-icon ep-icon_sheild-ok-stroke"></i>
            <div class="cft-tiles__text">
                <p>We’re a platform where people around the world connect to create, sell, and buy fascinating handcrafted products.</p>
                <p>Let us help you make global transactions easier, safer, and faster</p>
            </div>
        </div>
    </div>
    <div class="cft-tiles__row cft-tiles__row--right">
        <div class="cft-tiles__info cft-tiles__info--left">
            <div class="cft-tiles__title">Find exciting handmade products worldwide!</div>
            <div class="cft-tiles__text">
                <p>Discover one-of-a-kind goods by crafters that you’ll only find on Export Portal.</p>
            </div>
        </div>
        <div class="cft-tiles__image">
            <img src="<?php echo __IMG_URL;?>public/img/pages/crafting/chair.jpg" alt="exportportal">
        </div>
    </div>
</section>

<section class="cft-selling">
    <h2 class="cft-selling__title">Are you ready to start selling?</h2>
    <div class="cft-selling__subtitle">It only takes a few steps to open up your virtual showroom.</div>
    <a class="btn btn-outline-dark cft-selling__link" href="<?php echo get_static_url('register/co', __SITE_URL, $campaign['id_campaign']);?>">Set-up your Shop Today</a>
    <div class="cft-selling__image">
        <img src="<?php echo __IMG_URL;?>public/img/pages/crafting/jewelery.jpg" alt="exportportal">
    </div>
</section>

<section class="cft-join footer-connect">
    <h2 class="cft-join__title">Are you a writer or know a writer whose talents can spark the interest of our global readers?</h2>
    <div class="cft-join__subtitle">Please, use access code "platon" and:</div>
    <a class="btn btn-outline-dark cft-join__link" href="<?php echo __SITE_URL;?>bloggers">Join us</a>
    <div class="cft-join__image">
        <img src="<?php echo __IMG_URL;?>public/img/pages/crafting/typewriter.jpg" alt="exportportal">
    </div>
</section>

<script src="//cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.5/ScrollMagic.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.5/plugins/debug.addIndicators.min.js"></script>

<script>

    $(document).ready(function() {
        burgerOnClick();
        clickOnBullet();
        scrollSlider();
    });

    $(document).on('scroll', function() {
        scrollSlider();
    });

    function burgerOnClick() {
        $('.cft-burger').on('click', function() {
            $(this).toggleClass('change');
            $(this).parent().find('.cft-nav__list').toggleClass('show');
        });
    }

    function clickOnBullet() {
        $('.cft-bullets__point').on('click', function() {
            var $this = $(this);
            var bulletIndex = $this.index();

            $('.cft-bullets__point').removeClass('active');
            $this.addClass('active');

            scrollSlider(bulletIndex);
        });
    }

    function checkBullet( slide ) {
        var bullet_elem = $('.cft-bullets__point');

        $(bullet_elem).each(function() {
            var $this = $(this);
            slide === $this.index() ? $this.addClass('active') : $this.removeClass('active');
        });
    }

    function scrollSlider( bulletIndex ) {
        var prev_elem_pos = parseInt($('.cft-title-fixed').prev().offset().top);
        var prev_elem_height = parseInt($('.cft-title-fixed').prev().height());
        var prev_elem_bottom = prev_elem_pos + prev_elem_height;

        var window_scroll = parseInt($(window).scrollTop());
        var count = parseInt($('.cft-title').length) - 1;

        var last_title_pos = parseInt($('.cft-title').eq(count).offset().top);
        var last_title_height = parseInt($('.cft-title').eq(count).outerHeight());
        var last_title_bottom = last_title_pos + last_title_height;

        if(window_scroll >= last_title_pos){
            var minusShow = $('.cft-header').outerHeight() * 0.25;
        }else{
            var minusShow = -$('.cft-header').outerHeight() * 0.35;
        }

        if((window_scroll > (prev_elem_bottom + minusShow)) && !$('.cft-title-fixed').hasClass('active')) {
            $('.cft-title-fixed').addClass('active');

            if(window_scroll >= last_title_pos){
                var $ttl = $('.cft-title:eq(' + count + ')');
            }else{
                var $ttl = $('.cft-title:eq(0)');
            }
            initTtl($ttl, bulletIndex);
        }

        if($('.cft-title-fixed').hasClass('active')) {

            if(bulletIndex !== undefined) {
                var target_top_offset = $('.cft-title').eq(bulletIndex).offset().top;
                $('html, body').animate({scrollTop: target_top_offset + 10}, 100);
            }

            $('.cft-title').each(function() {

                var $this = $(this);
                var this_title_top = $this.offset().top;
                var this_title_bottom = this_title_top + $this.outerHeight();

                if(window_scroll >= this_title_top && window_scroll <= this_title_bottom) {
                    initTtl($this, bulletIndex);
                }
            });
        }

        if (window_scroll > last_title_bottom + minusShow || window_scroll < prev_elem_bottom + minusShow) {
            $('.cft-title').removeClass('passed');
            $('.cft-title-fixed').removeClass('active').find('.cft-title-fixed__container').html('');
        }
    }

var initTtl = function($this, bulletIndex){
    var par_text = String($this.find('.cft-title__container').html());

    if(!$this.hasClass('passed') && bulletIndex === undefined) {
        var active_slide_index = $this.index();
        checkBullet(active_slide_index);
        $('.cft-title-fixed').find('.cft-title-fixed__block').animate({ left: "-=200" }, 100);
        $('.cft-title-fixed').find('.cft-title-fixed__container').html(par_text).find('.cft-title-fixed__block').addClass('blurred');

        // $('.cft-title-fixed').find('.cft-title-fixed__container').html(par_text).find('.cft-title-fixed__block').addClass('blurred').animate({
        //     left: "0",
        //     transform: "scale(1)"
        // }, 100);

        setTimeout(function(){
            $('.cft-title-fixed').find('.cft-title-fixed__block').removeClass('blurred');
        }, 400);

        $('.cft-title').removeClass('passed');

        $this.addClass('passed');
    }
}

</script>
