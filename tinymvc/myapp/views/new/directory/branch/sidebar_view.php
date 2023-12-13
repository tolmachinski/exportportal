
<div class="hide-767">
    <?php if (logged_in() && is_privileged('company', $company['id_company'], 'manage_branches')) { ?>
        <div class="dropdown pull-right">
            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                <i class="ep-icon ep-icon_menu-circles"></i>
            </a>

            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="<?php echo __SITE_URL; ?>company_branches/my" data-title="Edit" title="Edit">
                    <i class="ep-icon ep-icon_pencil"></i> Edit
                </a>
            </div>
        </div>
    <?php } ?>

    <div class="spersonal-logo">
        <img
            class="image"
            <?php echo addQaUniqueIdentifier('seller__sidebar_company-logo'); ?>
            src="<?php echo getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main'); ?>"
            alt="<?php echo $company['name_company'] . ' Image'; ?>" />
    </div>

    <h2 class="spersonal-name" <?php echo addQaUniqueIdentifier('seller__sidebar_company-name'); ?>><?php echo $company['name_company']; ?></h2>
    <?php if (!empty($company_main)) { ?>
        <div>
            <span class="ppersonal-header__name-txt display-ib">branch of</span>
            <a class="ppersonal-header__name-additional display-ib link-black" <?php echo addQaUniqueIdentifier('page__branch__headquarter_user-name'); ?> href="<?php echo __SITE_URL . ($company_main['index_name'] != '' ? $company_main['index_name'] : 'seller/' . strForUrl($company_main['name_company']) . '-' . $company_main['id_company']); ?>">
                <?php echo $company_main['name_company']; ?>
            </a>
        </div>
    <?php } ?>
</div>

<div class="dropdown mt-20">
    <a class="dropdown-toggle btn btn-light btn-block txt-blue2" id="dropdownMenuButton" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        More actions on branch
        <i class="ep-icon ep-icon_menu-circles pl-10"></i>
    </a>

    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <?php if (logged_in()) { ?>

            <?php echo !empty($user_main['btnChat']) ? $user_main['btnChat'] : ''; ?>

            <?php if (in_session('company_saved', $company['id_company'])) { ?>
                <button
                    class="dropdown-item call-function"
                    data-callback="remove_company"
                    data-company="<?php echo $company['id_company']; ?>"
                    title="Remove from Favorites"
                    type="button"
                >
                    <i class="ep-icon ep-icon_favorite"></i><span class="txt">Favorited</span>
                </button>
            <?php } else { ?>
                <button
                    class="dropdown-item call-function"
                    data-callback="add_company"
                    data-company="<?php echo $company['id_company']; ?>"
                    title="Add to Favorites"
                    type="button"
                >
                    <i class="ep-icon ep-icon_favorite-empty"></i><span class="txt">Favorite</span>
                </button>
            <?php } ?>

            <?php if (in_session('followed', $company['id_user'])) { ?>
                <button
                    class="dropdown-item call-function follow-<?php echo $company['id_user'];?>"
                    data-user="<?php echo $company['id_user'];?>"
                    data-callback="unfollow_user"
                    title="Unfollow user"
                    type="button"
                >
                    <i class="ep-icon ep-icon_unfollow"></i><span class="txt">Unfollow user</span>
                </button>
            <?php } else { ?>
                <button
                    class="dropdown-item fancybox.ajax fancyboxValidateModal follow-<?php echo $company['id_user'];?>"
                    data-title="Follow user"
                    data-fancybox-href="followers/popup_followers/follow_user/<?php echo $company['id_user'];?>"
                    title="Follow user"
                    type="button"
                >
                    <i class="ep-icon ep-icon_follow"></i><span class="txt">Follow user</span>
                </button>
            <?php } ?>

            <button
                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                data-title="Share this company with your followers"
                data-fancybox-href="<?php echo __SITE_URL;?>company/branch_popup_forms/share_company/<?php echo $company['id_company'];?>"
                type="button"
            >
                <i class="ep-icon ep-icon_share-stroke"></i><span class="txt">Share this</span>
            </button>
            <button
                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                data-title="Send info about this company to your contacts by email"
                data-fancybox-href="<?php echo __SITE_URL;?>company/branch_popup_forms/email_company/<?php echo $company['id_company'];?>"
                type="button"
            >
                <i class="ep-icon ep-icon_envelope-send"></i><span class="txt">Email this</span>
            </button>
            <button
                class="dropdown-item fancyboxValidateModal fancybox.ajax"
                data-fancybox-href="<?php echo __SITE_URL;?>complains/popup_forms/add_complain/company/<?php echo $company['id_company'];?>/<?php echo $company['id_user'];?>/<?php echo $company['id_company'];?>"
                data-title="Report this company"
                type="button"
            >
                <i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt">Report this</span>
            </button>
        <?php } else { ?>
            <button
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Chat with seller"
                type="button"
            >
                <i class="ep-icon ep-icon_chat"></i><span class="txt">Chat with seller</span>
            </button>
            <button
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Contact this seller"
                type="button"
            >
                <i class="ep-icon ep-icon_envelope"></i><span class="txt">Contact this seller</span>
            </button>
            <button
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Save this seller"
                type="button"
            >
                <i class="ep-icon ep-icon_favorite-empty"></i><span class="txt">Save this seller</span>
            </button>
            <button
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Follow this seller"
                type="button"
            >
                <i class="ep-icon ep-icon_follow"></i><span class="txt">Follow this seller</span>
            </button>
            <button
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Share this company with your followers"
                type="button"
            >
                <i class="ep-icon ep-icon_share-stroke"></i><span class="txt">Share this</span>
            </button>
            <button
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Email this"
                type="button"
            >
                <i class="ep-icon ep-icon_envelope-send"></i><span class="txt">Email this</span>
            </button>
            <button
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Report this"
                type="button"
            >
                <i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt">Report this</span>
            </button>
        <?php } ?>
    </div>
</div>


<h3 class="minfo-sidebar-ttl mt-50">
    <span class="minfo-sidebar-ttl__txt">Additional info</span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <ul class="minfo-sidebar-box__list">
            <li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<i class="ep-icon ep-icon_user"></i>
				</span>
                <a class="ppersonal-user-detail__desc tar text-nowrap link-black" <?php echo addQaUniqueIdentifier('global__additional-info__user'); ?> href="<?php echo __SITE_URL . 'usr/' . strForURL($user_main['fname'] . ' ' . $user_main['lname']) . '-' . $user_main['idu']; ?>"><?php echo $user_main['fname'] . ' ' . $user_main['lname']; ?></a>
            </li>
            <li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<img
                        width="24"
                        height="24"
                        <?php echo addQaUniqueIdentifier('global__additional-info__country-flag'); ?>
                        src="<?php echo getCountryFlag($company['country']); ?>"
                        alt="<?php echo $company['country']; ?>"
                    >
				</span>
                <span <?php echo addQaUniqueIdentifier('global__additional-info__country-name'); ?>><?php echo $company['country']; ?></span>
            </li>
            <li class="minfo-sidebar-box__list-item">
				<span class="minfo-sidebar-box__list-ico">
					<i class="ep-icon ep-icon_marker-stroke"></i>
				</span>
                <span <?php echo addQaUniqueIdentifier('global__additional-info__location-city'); ?>>
                    <?php
                    echo empty($company['state']) ? '' : "{$company['state']}, ";
                    echo empty($company['city']) ? '' : "{$company['city']}, ";
                    echo $company['address_company'];
                    ?>
                </span>
            </li>
        </ul>
    </div>
</div>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Headquarter</span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <div class="mb-5">
            <a class="ppersonal-header__name-additional display-ib link-black" <?php echo addQaUniqueIdentifier('page__branch__headquarter_user-name'); ?> href="<?php echo __SITE_URL . ($company_main['index_name'] != '' ? $company_main['index_name'] : 'seller/' . strForUrl($company_main['name_company']) . '-' . $company_main['id_company']); ?>">
                <?php echo $company_main['name_company']; ?>
            </a>
        </div>

        <div class="ppersonal-header__rating" <?php echo addQaUniqueIdentifier('page__branch__headquarter_user-experience'); ?>>
            <div>
                <?php if(($time_ago = timeAgo($company_main['registered_company'], 'Y,m', false, false)) == 'recently'){?>
                    Recently registered
                <?php }else{
                    echo $time_ago; ?> of experience
                <?php }?>
            </div>
        </div>

        <div class="ppersonal-header__group pt-8<?php echo userGroupNameColor($user_main['gr_name']);?>"
            <?php echo addQaUniqueIdentifier('page__branch__headquarter_user-group'); ?>
        >
            <span class="ppersonal-header__group-txt"><?php echo $company['user_group_name'].$company['user_group_name_sufix'];?></span>
        </div>
    </div>
</div>
