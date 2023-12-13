<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-grid-a-licious-3-0-1/jquery.grid-a-licious.js');?>"></script>
<script type="text/javascript">
    // $(document).ready(function() {
    //     $(".categories-row").gridalicious({
    //         gutter: 0,
    //         width: 340,
    //         selector: '.categories-block',
    //         animate: false
    //     });
    // });
</script>
<div class="container-center-sm all-categories">
    <div class="minfo-title">
        <h1 class="minfo-title__name"><?php echo translate("export_import_title");?></h1>
        <p class="pt-10"><?php echo translate("export_import_header_text");?></p>
    </div>

    <div class="all-categories__all-results">
        <div class="countries-columns">
            <?php foreach($country_articles as $key => $char){?>
            <div class="countries-columns__item">
                <div class="categories-list__container">

                    <div class="categories-list__header">
                        <span class="categories-list__letter"><?php echo $key ?></span>
                    </div>

                    <ul class="categories-list">
                        <?php foreach($char as $export){?>
                        <li class="categories-list__item">
                            <a class="categories-list__ttl" href="<?php echo __SITE_URL;?>search/country/<?php echo strForURL($export['country'].' '.$export['id']);?>/?keywords=<?php echo strForURL($export['country'], '+');?>" title="<?php echo $export['country'];?>" target="_blank">
                                <img
                                    class="image"
                                    src="<?php echo getCountryFlag($export['country']); ?>"
                                    alt="<?php echo $export['country']; ?>"
                                    width="32"
                                    height="32"
                                >
                                <span class="text-nowrap"><?php echo $export['country'];?></span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <p class="pt-20"><?php echo translate("export_import_footer_text");?></p>
</div>
