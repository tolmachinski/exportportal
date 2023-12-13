<?php if (!empty($vacancies_list)) { ?>
    <script>
        $(document).ready(function() {
            $(".vacancies-list__image").on("click", function(event) {
                var attr = $(this).parents().eq(1).find('.vacancies-list__name').attr('href');
                window.open(attr, '_blank');
            });
        });
    </script>

    <div class="vacancies-list">
        <ul class="row row-eq-height">
            <?php foreach ($vacancies_list as $vacancy) { ;?>
                <li class="vacancies-list__item col-md-12 col-lg-6">

                    <div class="vacancies-list__image-block">
                        <div class="vacancies-list__image">
                            <img src="<?php echo $vacancy['photoUrl']?>" alt="<?php echo $vacancy['post_vacancy']; ?>">
                        </div>
                        <a href="<?php echo __SITE_URL . 'about/vacancy/' . $vacancy['id_vacancy'] . '/' . strForURL($vacancy['post_vacancy']); ?>" class="btn btn-outline-dark btn-block mt-20">
                            <?php echo translate('footer_general_users_protection_link_more'); ?>
                        </a>
                    </div>

                    <div class="vacancies-list__content">
                        <a href="<?php echo __SITE_URL . 'about/vacancy/' . $vacancy['id_vacancy'] . '/' . strForURL($vacancy['post_vacancy']); ?>" class="vacancies-list__name">
                            <?php echo $vacancy['post_vacancy']; ?>
                        </a>

                        <div class="vacancies-list__country-block">
                            <?php if ((int) $vacancy['id_country'] > 0) { ?>
                                <img
                                    class="vacancies-list__country"
                                    width="24"
                                    height="24"
                                    src="<?php echo getCountryFlag($vacancy['country']); ?>"
                                    alt="<?php echo $vacancy['country']; ?>"
                                    title="<?php echo $vacancy['country']; ?>"
                                >
                                <span class="vacancies-list__country-name"><?php echo $vacancy['country']; ?></span>
                            <?php } else { ?>
                                <span class="vacancies-list__country-name"><?php echo translate('about_us_team_vacancies_list_country_origin'); ?></span>
                            <?php } ?>
                        </div>

                        <div class="vacancies-list__desc ep-middle-text">
                            <?php echo limit_words(cleanOutput($vacancy['description_vacancy']), 25); ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } else { ?>
    <div class="info-alert-b mb-15">
        <i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('about_us_team_vacancies_not_found'); ?>
    </div>
<?php } ?>
