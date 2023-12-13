<script>
    $(document).ready(function() {
        $(".ambassador-block__img").on("click", function(event) {
            var attr = $(this).parents().eq(0).find('.ambassador-block__name').attr('href');
            window.open(attr, '_blank');
        });
    });
</script>

<a class="btn btn-primary btn-panel-left fancyboxSidebar mb-20" data-title="EP Brand Ambassadors" href="#main-flex-card__fixed-left">
    <i class="ep-icon ep-icon_items"></i>
    Sidebar
</a>

<!-- <div class="minfo-search-counter pull-left">Found (<?php //echo $count;
                                                        ?>)</div>
<div class="minfo-save-search">
 	<div class="minfo-save-search__item">
 		<span class="minfo-save-search__ttl"> Status </span>
 		<div class="dropdown dropdown--select">
 			<a class="dropdown-toggle" href="#" id="ambasadorStatusLinks" role="button" data-toggle="dropdown">
                 <?php //echo $filter_ustatus['links'][$filter_ustatus['current']]['title'];
                    ?>
                 <i class="ep-icon ep-icon_arrow-down"></i>
 			</a>
 			<div class="dropdown-menu dropdown-menu-right" aria-labelledby="ambasadorStatusLinks" x-placement="bottom-start">
                 <?php //foreach($filter_ustatus['links'] as $filter_ustatus_key => $filter_ustatus_value){
                    ?>
                     <?php //if($filter_ustatus_key == $filter_ustatus['current']){continue;}
                        ?>
                     <a class="dropdown-item" href="<?php //echo $filter_ustatus_value['url'];
                                                    ?>">
                         <?php //echo $filter_ustatus_value['title'];
                            ?>
                     </a>
                 <?php //}
                    ?>
 			</div>
 		</div>
 	</div>

 	<div class="minfo-save-search__item">
 		<span class="minfo-save-search__ttl">Sort by</span>
 		<div class="dropdown show dropdown--select">
 			<a class="dropdown-toggle" href="#" role="button" id="ambasadorSortByLinks" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
 				<?php //echo $sort_by_links['items'][$sort_by_links['selected']]['title'];
                    ?>
 				<i class="ep-icon ep-icon_arrow-down"></i>
 			</a>

 			<div class="dropdown-menu" aria-labelledby="ambasadorSortByLinks">
 				<?php //foreach($sort_by_links['items'] as $sort_by_link_key => $sort_by_link){
                    ?>
 					<a class="dropdown-item" href="<?php //echo $sort_by_link['url'];
                                                    ?>"><?php //echo $sort_by_link['title'];
                                                                                            ?></a>
 				<?php // }
                    ?>
 			</div>
 		</div>
 	</div>
</div> -->
<?php if (!empty($cr_users)) { ?>
    <div class="title-public pt-0">
        <h2 class="title-public__txt w-100pr tac tt-uppercase"><?php echo $cr_domain['country']; ?> BRAND AMBASSADORS</h2>
    </div>
    <div class="ambassador-blocks">
        <?php foreach ($cr_users as $cr_user) {
            tmvc::instance()->controller->view->display('new/cr/representative_user_view', array(
                'cr_user' => $cr_user
            ));
        } ?>
    </div>
<?php } ?>

<div class="title-public">
    <h2 class="title-public__txt w-100pr tac">TYPES OF BRAND AMBASSADOR</h2>
</div>

<ul class="ambassador-types">
    <li class="ambassador-types__item">
        <i class="ep-icon ep-icon_card-user"></i>
        <div class="ambassador-types__name">Brand Ambassadors</div>
        <div class="ambassador-types__desc">Our Brand Ambassador is a versatile influencer in their respective country who promotes Export Portal and raises our platform’s awareness in their region or industry.</div>
    </li>

    <li class="ambassador-types__item">
        <i class="ep-icon ep-icon_balance"></i>
        <div class="ambassador-types__name">International Mediators</div>
        <div class="ambassador-types__desc">Export Portal International Mediators help resolve conflicts and moderate activities conducted by members in our platform.</div>
    </li>

    <li class="ambassador-types__item">
        <i class="ep-icon ep-icon_building-lead"></i>
        <div class="ambassador-types__name">Country Lead</div>
        <div class="ambassador-types__desc">Our Country Lead is responsible for the growth of Export Portal in their respective country. They oversee the Brand Ambassador in their country, international B2B transactions, and ensure Export Portal growth.</div>
    </li>

</ul>

<?php if (!empty($cr_domain['video_data'])) {
    $video_data = json_decode($cr_domain['video_data'], true); ?>
    <iframe class="ambassador-video" width="100%" height="500" src="<?php echo get_video_link($video_data['v_id'], $video_data['type'], 0); ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
<?php } ?>

<?php if (!empty($vacancies_list)) { ?>
    <div class="title-public">
        <h2 class="title-public__txt w-100pr tac">WANT A JOB AT EXPORT PORTAL?</h2>
    </div>
    <div class="vacancies-list">
        <ul class="row row-eq-height">
            <?php foreach ($vacancies_list as $vacancy) { ?>
                <li class="vacancies-list__item col-12 col-md-12 col-lg-6">

                    <div class="vacancies-list__image-block">
                        <div
                            class="vacancies-list__image call-function call-action"
                            data-callback="callMoveByLink"
                            data-js-action="link:move-by-link"
                            data-link="<?php echo __SITE_URL . 'about/vacancy/' . $vacancy['id_vacancy'] . '/' . strForURL($vacancy['post_vacancy']); ?>"
                        >
                            <img src="<?php echo $vacancy['imageUrl'] ?>" alt="<?php echo $vacancy['post_vacancy']; ?>">
                        </div>
                        <a class="btn btn-outline-dark btn-block mt-20 vacancies-list__btn-t2" href="<?php echo __SITE_URL . 'about/vacancy/' . $vacancy['id_vacancy'] . '/' . strForURL($vacancy['post_vacancy']); ?>">
                            <?php echo translate('footer_general_users_protection_link_more'); ?>
                        </a>
                    </div>

                    <div class="vacancies-list__content">
                        <a href="<?php echo __SITE_URL . 'about/vacancy/' . $vacancy['id_vacancy'] . '/' . strForURL($vacancy['post_vacancy']); ?>" class="vacancies-list__name">
                            <?php echo $vacancy['post_vacancy']; ?>
                        </a>

                        <div class="vacancies-list__desc">
                            <?php echo limit_words($vacancy['description_vacancy'], 25); ?>
                        </div>
                        <a class="btn btn-outline-dark vacancies-list__btn-t1" href="<?php echo __SITE_URL . 'about/vacancy/' . $vacancy['id_vacancy'] . '/' . strForURL($vacancy['post_vacancy']); ?>">
                            <?php echo translate('footer_general_users_protection_link_more'); ?>
                        </a>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>
