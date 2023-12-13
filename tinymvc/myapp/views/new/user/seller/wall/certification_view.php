<?php $userGroup = $data['groupId'] == 3 ? 'seller' : 'manufacturer';?>
<div class="detail-info">
    <div class="spersonal-history">
        <div class="spersonal-history__top">
            <div class="spersonal-history__top-ttl">
                <a href="<?php echo $base_company_url;?>"><strong><?php echo cleanOutput($company['name_company']);?></strong></a>
                <span>is</span>
                <span class="link display-ib"><?php echo $data['groupName'];?></span>
            </div>
            <div class="spersonal-history__top-param">
                <div class="spersonal-history__top-date">since <?php echo getDateFormat($wall_item['date'])?></div>
            </div>
        </div>
        <div class="spersonal-history__content">
            <div class="spersonal-history-certificate">
                <div class="spersonal-history-certificate__image">
                    <picture>
                        <source media="(max-width: 425px)" srcset="<?php echo asset("public/build/images/user/wall/certificate-mobile.png"); ?> 1x, <?php echo asset("public/build/images/user/wall/certificate-mobile@2x.png"); ?> 2x">
                        <source media="(min-width: 768px) and (max-width: 1200px)" srcset="<?php echo asset("public/build/images/user/wall/certificate-tablet.png"); ?> 1x, <?php echo asset("public/build/images/user/wall/certificate-tablet@2x.png"); ?> 2x">
                        <img class="image" width="238" height="227" src="<?php echo getLazyImage(238, 227); ?>" srcset="<?php echo asset("public/build/images/user/wall/certificate.png"); ?> 1x, <?php echo asset("public/build/images/user/wall/certificate@2x.png"); ?> 2x" alt="cetrificate">
                    </picture>
                </div>
                <div class="spersonal-history-certificate__body">
                    <h3 class="spersonal-history-certificate__title"><?php echo 'This ' . $userGroup . ' is certified';?></h3>
                    <div class="spersonal-history-certificate__text"><?php echo 'This user is a guaranteed credible ' . $userGroup;?></div>
                    <a class="spersonal-history-certificate__btn btn btn-primary" href="<?php echo __SITE_URL . 'about/certification_and_upgrade_benefits';?>">Learn more</a>
                </div>
            </div>
        </div>
    </div>
</div>
