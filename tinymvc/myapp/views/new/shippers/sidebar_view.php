<?php if (logged_in()) { ?>
    <script>
        //remove shipper
        remove_shipper_company = function (opener) {
            var $this = $(opener);
            var id_company = intval($this.data('company'));
            $.ajax({
                url: 'shipper/ajax_shipper_operation/remove_shipper_saved',
                type: 'POST',
                dataType: 'JSON',
                data: {company: $this.data('company')},
                success: function (resp) {
                    systemMessages(resp.message, resp.mess_type);
                    if (resp.mess_type === 'success') {
                        $this.replaceWith('<a class="dropdown-item call-function" data-callback="add_shipper_company" data-company="'+ id_company +'" href="#" title="Add to Favorites">\
                                                <i class="ep-icon ep-icon_favorite-empty"></i>\
                                                <span class="txt">Favorite</span>\
                                            </a>');
                    }
                }
            });
        };

        //save shipper
        add_shipper_company = function (opener) {
            var $this = $(opener);
            var id_company = intval($this.data('company'));
            $.ajax({
                url: 'shipper/ajax_shipper_operation/add_shipper_saved',
                type: 'POST',
                dataType: 'JSON',
                data: {company: id_company},
                success: function (resp) {
                    systemMessages(resp.message, resp.mess_type);
                    if (resp.mess_type === 'success') {
                        $this.replaceWith('<a class="dropdown-item call-function" data-callback="remove_shipper_company" data-company="'+ id_company +'" href="#" title="Remove from Favorites">\
                                                <i class="ep-icon ep-icon_favorite"></i>\
                                                <span class="txt">Favorited</span>\
                                            </a>');
                    }
                }
            });
        };

        var makeShipperPartner = function (obj) {
            var $this = $(obj);
            var shipper = $this.data('shipper');

            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL?>shippers/ajax_shippers_operation/partnership',
                data: {shipper: shipper},
                beforeSend: function () {
                },
                dataType: 'json',
                success: function (resp) {
                    systemMessages(resp.message, resp.mess_type);

                    if (resp.mess_type === 'success') {
                        $this.toggleClass('call-function call-systmess').addClass('txt-gray-light').html(
                            '<i class="ep-icon ep-icon_hourglass-processing"></i><span class="txt">{{text}}</span>'.replace('{{text}}', "Waiting partnership approval")
                        );
                    }
                }
            });
        };
    </script>
<?php } ?>

<div class="hide-767">
    <div class="spersonal-logo">
        <img
            class="image"
            <?php echo addQaUniqueIdentifier('seller__sidebar_company-logo'); ?>
            src="<?php echo getDisplayImageLink(array('{ID}' => $shipper['id'], '{FILE_NAME}' => $shipper['logo']), 'shippers.main'); ?>"
            alt="<?php echo $shipper['co_name'] . ' Image'; ?>"/>
    </div>

    <h2 class="spersonal-name" <?php echo addQaUniqueIdentifier('seller__sidebar_company-name'); ?>>
        <?php echo $shipper['co_name']; ?>
    </h2>
</div>

<div class="dropdown mt-20">
    <a class="dropdown-toggle btn btn-light btn-block txt-blue2" <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu'); ?> id="dropdownMenuButton" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        More actions on shipper
        <i class="ep-icon ep-icon_menu-circles pl-10"></i>
    </a>

    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <?php if (logged_in()) { ?>
            <?php echo !empty($chatBtn) ? $chatBtn : ''; ?>

            <?php if (in_session('shippers_saved', $shipper['id'])) { ?>
                <button
                    <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_favorite'); ?>
                    class="dropdown-item call-function"
                    data-callback="remove_shipper_company"
                    data-company="<?php echo $shipper['id']; ?>"
                    title="Remove from Favorites"
                    type="button"
                >
                    <i class="ep-icon ep-icon_favorite"></i>
                    <span class="txt">Favorited</span>
                </button>
            <?php } else { ?>
                <button
                    <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_favorite'); ?>
                    class="dropdown-item call-function"
                    data-callback="add_shipper_company"
                    data-company="<?php echo $shipper['id']; ?>"
                    title="Add to Favorites"
                    type="button"
                >
                    <i class="ep-icon ep-icon_favorite-empty"></i>
                    <span class="txt">Favorite</span>
                </button>
            <?php } ?>

            <?php if (have_right('sell_item')) { ?>
                <?php if (!empty($partnership) && $partnership['are_partners']) { ?>
                    <button
                        <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_partners'); ?>
                        class="dropdown-item txt-green call-systmess"
                        data-message="You are already the partner of this Freight Forwarder."
                        data-type="info"
                        title="Partner"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_partners"></i>
                        <span class="txt">Already Partners</span>
                    </button>
                <?php } else if (!empty($partnership) && $partnership['are_partners'] == 0) { ?>
                    <button
                        <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_partnership'); ?>
                        class="dropdown-item txt-gray-light call-systmess"
                        data-message="You already sent a partnership request to this freight forwarder. Please wait until the freight forwarder will approve it." data-type="info"
                        title="Waiting partnership approval"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_hourglass-processing"></i>
                        <span class="txt">Waiting partnership approval</span>
                    </button>
                <?php } else { ?>
                    <button
                        <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_become-a-partner'); ?>
                        class="dropdown-item call-function"
                        data-callback="makeShipperPartner"
                        data-shipper="<?php echo $shipper['id_user']; ?>"
                        title="Become a partner"
                        data-message="You already sent a partnership request to this freight forwarder. Please wait until the freight forwarder will approve it." data-type="info"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_plus-circle"></i>
                        <span class="txt">Become a partner</span>
                    </button>
                <?php } ?>
            <?php } ?>

            <?php if (!is_my($shipper['id'])) { ?>
                <button
                    <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_share'); ?>
                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                    data-title="Share this company with your followers"
                    data-fancybox-href="<?php echo __SITE_URL;?>shipper/popup_forms/share_company/<?php echo $shipper['id'];?>"
                    type="button"
                >
                    <i class="ep-icon ep-icon_share-stroke"></i>
                    <span class="txt">Share this</span>
                </button>
                <button
                    <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_send'); ?>
                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                    data-title="Email this company with your email contacts"
                    data-fancybox-href="<?php echo __SITE_URL;?>shipper/popup_forms/email_company/<?php echo $shipper['id'];?>"
                    type="button"
                >
                    <i class="ep-icon ep-icon_envelope-send"></i>
                    <span class="txt">Email this</span>
                </button>
                <button
                    <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_report'); ?>
                    class="dropdown-item fancyboxValidateModal fancybox.ajax"
                    data-fancybox-href="<?php echo __SITE_URL;?>complains/popup_forms/add_complain/shipper/<?php echo $shipper['id'];?>/<?php echo $shipper['id_user'];?>"
                    data-title="Report this shipper"
                    type="button"
                >
                    <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                    <span class="txt">Report this</span>
                </button>
            <?php } ?>
        <?php } else { ?>
            <button
                <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_contact_forwarder'); ?>
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Contact this freight forwarder"
                type="button"
            >
                <i class="ep-icon ep-icon_envelope"></i>
                <span class="txt">Chat now</span>
            </button>
            <button
                <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_save_forwarder'); ?>
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Save this freight forwarder"
                type="button"
            >
                <i class="ep-icon ep-icon_favorite-empty"></i>
                <span class="txt">Save this freight forwarder</span>
            </button>
            <button
                <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_share'); ?>
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Share this company with your followers"
                type="button"
            >
                <i class="ep-icon ep-icon_share-stroke"></i>
                <span class="txt">Share this</span>
            </button>
            <button
                <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_send'); ?>
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Email this company with your email contacts"
                type="button"
            >
                <i class="ep-icon ep-icon_envelope-send"></i>
                <span class="txt">Email this</span>
            </button>
            <button
                <?php echo addQaUniqueIdentifier('shipper_sidebar_more_actions_menu_report'); ?>
                class="dropdown-item call-systmess"
                data-message="<?php echo translate("systmess_error_should_be_logged", null, true); ?>"
                data-type="error"
                title="Report this"
                type="button"
            >
                <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                <span class="txt">Report this</span>
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
            <li class="minfo-sidebar-box__list-item" <?php echo addQaUniqueIdentifier('shipper_sidebar_additional-info_country'); ?>>
				<span class="minfo-sidebar-box__list-ico">
					<img
                        <?php echo addQaUniqueIdentifier('shipper_sidebar_additional-info_flag'); ?>
                        src="<?php echo getCountryFlag($address['country']); ?>"
                        alt="<?php echo $address['country']; ?>"
                        width="24"
                        height="24"
                    >
				</span>
                <?php echo $address['country']; ?>
            </li>
            <li class="minfo-sidebar-box__list-item" <?php echo addQaUniqueIdentifier('shipper_sidebar_additional-info_address'); ?>>
				<span class="minfo-sidebar-box__list-ico">
					<i <?php echo addQaUniqueIdentifier('shipper_sidebar_additional-info_icon'); ?> class="ep-icon ep-icon_marker-stroke"></i>
				</span>
                <?php
                echo empty($address['state']) ? '' : "{$address['state']}, ";
                echo empty($address['city']) ? '' : "{$address['city']}, ";
                echo empty($shipper['address']) ? '' : "{$shipper['address']}";
                ?>
            </li>
        </ul>
    </div>
</div>
