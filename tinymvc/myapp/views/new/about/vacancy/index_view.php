<script>
$(function(){
    $(document).on('click', '.dropdown-menu.js-dropdown-menu--off-close', function (e) {
        e.stopPropagation();
    });
});
</script>
<div class="mt-40">
    <div class="row">
        <div class="col-md-4">
            <div class="vacancy">
                <div class="vacancy__image-block">
                    <div class="vacancy__image">
                        <img src="<?php echo $vacancy['photoUrl']?>" alt="<?php echo $vacancy['post_vacancy'];?>" title="<?php echo $vacancy['post_vacancy'];?>">
                    </div>
                    <a class="btn btn-primary mt-20" href="<?php echo $vacancy['link_vacancy'];?>" target="_blank">
                        Apply now
                    </a>

                    <div class="dropdown">

                        <a class="dropdown-toggle btn btn-light mt-20" id="dropdownMenuButton" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            More actions <i class="ep-icon ep-icon_menu-circles"></i>
                        </a>

                        <div class="our-team__dropdown dropdown-menu js-dropdown-menu--off-close" aria-labelledby="dropdownMenuButton">

                            <div class="our-team__office dropdown-item">
                                <i class="ep-icon ep-icon_marker "></i> <?php echo $vacancy['address_office']?>
                            </div>

                            <div class="our-team__phone dropdown-item">
                                <i class="ep-icon ep-icon_phone"></i>
                                <a href="tel:<?php echo get_only_number($vacancy['phone_office']);?>" target="_blank"><?php echo $vacancy['phone_office']?></a>
                            </div>

                            <div class="dropdown-item" href="#">
                                <i class="ep-icon ep-icon_envelope"></i>
                                <a href="mailto:<?php echo $vacancy['email_office'];?>" target="_blank"><?php echo $vacancy['email_office']?></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="minfo-title">
                <h3 class="minfo-title__name">
                    <?php echo $vacancy['post_vacancy'];?>
                </h3>
            </div>

            <div class="vacancies-list__content">

                <div class="vacancies-list__country-block mb-30">
                    <img
                        class="vacancies-list__country"
                        width="24"
                        height="24"
                        src="<?php echo getCountryFlag($vacancy['country']);?>"
                        alt="<?php echo $vacancy['country'];?>"
                        title="<?php echo $vacancy['country'];?>"
                    />
                    <span class="vacancies-list__country-name">
                        <?php echo $vacancy['country'];?>
                    </span>
                </div>

                <div class="vacancy__desc ep-middle-text">
                    <?php echo $vacancy['description_vacancy'];?>
                </div>

            </div>
        </div>
    </div>

    <div class="minfo-title clearfix pt-70">
        <h3 class="minfo-title__name">Other Vacancies</h3>
    </div>

    <?php tmvc::instance()->controller->view->display('new/hiring/vacancies_list_view'); ?>
</div>
