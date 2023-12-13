<div class="display-n">
    <div id="halloween-popup">
        <h1 id="js-gif-text" class="mb-15 txt-red tac">text</h1>
        <div style="width: 540px; background: #fff; display: flex; align-items: flex-end; justify-content: center;">
            <img id="js-gif" src="<?php echo __IMG_URL;?>public/img/halloween2019/f.jpg" style="height: 500px;">
        </div>

        <div id="js-gif-btns-close" class="mt-15" style="display: none;">
            <button id="js-gif-btn-dshow" class="btn btn-dark pull-left w-30pr btn-block call-function" data-callback="dshowHalloween2019">Don't show again</button>
            <button class="btn btn-outline-dark pull-left mt-0 ml-10 w-30pr btn-block call-function" data-callback="replayHalloweenPopup">Watch again</button>
            <button class="btn btn-primary pull-right mt-0 w-30pr btn-block call-function" data-callback="closeHalloween2019">Close</button>
        </div>
    </div>
</div>

<script>
    var imgs = {
        1: {
            src: '1.gif',
            t: 2300 ,
            txt: 'How to increase EP transactions?'
        },
        2: {
            src: '2.gif',
            t: 1920 ,
            txt: 'EUREKA! Mr. Bones has an ideal recipe.'
        },
        3: {
            src: '3.gif',
            t: 3000  ,
            txt: 'Add a piece of yourself in every deal'
        },
        4: {
            src: '4.gif',
            t: 2300  ,
            txt: 'Find a verified Tramadol supplier and verify its quality'
        },
        5: {
            src: '5.gif',
            t: 960  ,
            txt: 'Lost your had? Then the product is good!'
        },
        6: {
            src: '6.gif',
            t: 1920  ,
            txt: 'Find trustworthy partners with the same ideas'
        },
        7: {
            src: '7.gif',
            t: 960  ,
            txt: 'Take a cup of tea while the order is processing'
        },
        8: {
            src: '8.gif',
            t: 960  ,
            txt: '... still processing...'
        },
        9: {
            src: '9.gif',
            t: 960  ,
            txt: 'It Took just 7 years!'
        },
        10: {
            src: '10.gif',
            t: 2880   ,
            txt: 'Hah.....no product, no money - Catch me the next year, Honey!'
        },
        11: {
            src: 'end.gif',
            t: 3200  ,
            txt: 'Moldova EP team wishes you a Spooky Halloween!'
        },
    };

    var dscounter = 0;

    $(function(){
        openHalloweenPopup();
        $("#js-gif-btn-dshow").mouseover(function() {
            dscounter++;

            if(dscounter < 3){
                $(this).css({opacity: 0, cursor: 'default'});
            }
        }).mouseout(function() {
            $(this).css({opacity: 1, cursor: 'pointer'});
        });
    })

    function openHalloweenPopup(a){
        $.fancybox.open({
            href        : '#halloween-popup',
            width		: fancyW,
            height		: 'auto',
            maxWidth	: 540,
            autoSize	: false,
            loop : false,
            helpers : {
                title: {
                    type: 'inside',
                    position: 'top'
                },
                overlay: {
                    locked: false
                }
            },
            lang : __site_lang,
            i18n : translate_js_one({plug:'fancybox'}),
            modal: true,
            padding: fancyP,
            closeBtn : true,
            closeBtnWrapper: '.fancybox-skin .fancybox-title',
            beforeLoad : function() {

            },
            afterShow: function() {
                showHalloweenPopup();
            }
        });
    }

    function replayHalloweenPopup(){
        $('#js-gif-btns-close').hide();
        dscounter = 0;
        showHalloweenPopup(1);
    }

    function showHalloweenPopup(a){
        var a = a || 1;
        var t = imgs[a].t;
        var total = Object.keys(imgs).length;

        if(t < 1000){
            t = t*5;
        }else if(t < 2000){
            t = t*3;
        }

        t = 5000;

        $('#js-gif-text').text(imgs[a].txt);
        $('#js-gif').attr('src', __site_url + 'public/img/halloween2019/'+imgs[a].src);
        
        if(a == 10){
            setTimeout(function(){
                $('#js-gif').css({opacity: 0});
            }, imgs[a].t);
        }else{
            $('#js-gif').css({opacity: 1});
        }

        if(a == total){
            $('#js-gif-btns-close').show();
        }
        
        a++;
        setTimeout(function(){
            $.fancybox.update();
        }, 100);

        if( a <= total ){
            setTimeout(function(){
                showHalloweenPopup(a);
            }, t);
        }
    }

    function dshowHalloween2019($this){
        
        if(dscounter >= 3){
            setCookie('_ep_halloween_popup', 1, 7);
            closeFancyBox();
        }
    }

    function closeHalloween2019(){
        closeFancyBox();
    }
</script>

