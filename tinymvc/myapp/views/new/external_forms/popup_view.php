<div class="report-problem-popup">
    <p class="report-problem-popup__info">
        <?php echo $popup_text;?>
    </p>

    <div class="container-fluid-modal">
        <div class="report-problem-list">
            <?php foreach($iframes as $iframes_item){?>
                <?php
                    if($iframes_item['logged'] && !logged_in()){
                        continue;
                    }
                ?>
                <?php
                    if(isset($iframes_item['rights']) && !empty($iframes_item['rights']) && !have_right_or($iframes_item['rights'])){
                        continue;
                    }
                ?>
                    <div class="report-problem-list__item">
                        <a
                            class="report-problem-list__item-inner flex-card fancybox fancybox.iframe"
                            title="<?php echo $popup_title_text;?>"
                            data-h="100%"
                            data-w="100%"
                            href="<?php echo $iframes_item['link'];?>"
                        >
                            <div class="report-problem-list__icon flex-card__fixed">
                                <i class="ep-icon ep-icon_arrow-right"></i>
                            </div>
                            <div class="report-problem-list__detail flex-card__float">
                                <h3 class="report-problem-list__ttl"><?php echo $iframes_item['title'];?></h3>
                                <p class="report-problem-list__txt"><?php echo $iframes_item['desc'];?></p>
                            </div>
                        </a>
                    </div>
            <?php }?>
        </div>
    </div>

</div>
