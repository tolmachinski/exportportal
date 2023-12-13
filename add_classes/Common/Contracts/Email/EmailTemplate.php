<?php

declare(strict_types=1);

namespace App\Common\Contracts\Email;

use App\Email\AbuseAlert;
use App\Email\AccountIsNowBlocked;
use App\Email\AccountRestriction;
use App\Email\ActivateAccount;
use App\Email\AddProductsRemind;
use App\Email\AnswerItemQuestion;
use App\Email\BlockResources;
use App\Email\BloggersAddArticle;
use App\Email\BloggersContact;
use App\Email\CalendarEpEventReminder;
use App\Email\ChangeDroplistPrice;
use App\Email\ChangeEmail;
use App\Email\ChangeNotificationEmail;
use App\Email\ChangePassword;
use App\Email\CheckYourAccountOnEp;
use App\Email\CleanSession;
use App\Email\CompleteProfileRemind;
use App\Email\ConfirmDeleteAccount;
use App\Email\ConfirmEmail;
use App\Email\ConfirmEventAttend;
use App\Email\ConfirmFeedback;
use App\Email\ConfirmReview;
use App\Email\ConfirmSubscription;
use App\Email\ConfirmUserCancel;
use App\Email\ContactAdmin;
use App\Email\CrDeleteRequest;
use App\Email\CrSendActivationLink;
use App\Email\DemoWebinarEp2WeeksAfter;
use App\Email\DemoWebinarEpComingSoon;
use App\Email\DemoWebinarEpNext;
use App\Email\DemoWebinarEpRegistered;
use App\Email\DemoWebinarEpRequesting;
use App\Email\DemoWebinarEpThanksForParticipatedBuyers;
use App\Email\DemoWebinarEpThanksForParticipatedSellers;
use App\Email\DemoWebinarEpTomorrow;
use App\Email\DemoWebinarEpUnRegistered;
use App\Email\DownloadableMaterialsData;
use App\Email\DownloadableMaterialsShare;
use App\Email\DraftItemExpirationFirstEmail;
use App\Email\DraftItemIncentiveOfferEmail;
use App\Email\DraftItemWarningDeleteEmail;
use App\Email\EmailFriendAboutB2b;
use App\Email\EmailFriendAboutCompany;
use App\Email\EmailFriendAboutCompanyUpdates;
use App\Email\EmailFriendAboutEpEvent;
use App\Email\EmailFriendAboutLibrary;
use App\Email\EmailFriendAboutNews;
use App\Email\EmailFriendAboutPicture;
use App\Email\EmailFriendAboutShipperCompany;
use App\Email\EmailFriendAboutUser;
use App\Email\EmailFriendAboutVideo;
use App\Email\EmailOrderCancelledByBuyer;
use App\Email\EmailOrderCancelledByManager;
use App\Email\EmailOrderCancelledBySeller;
use App\Email\EmailUser;
use App\Email\EmailUserAboutBill;
use App\Email\EmailUserAboutFreeFeaturedItems;
use App\Email\EnvelopeExpiresSoonForSender;
use App\Email\EnvelopeExpiresSoonForSigner;
use App\Email\EplClearSession;
use App\Email\EplConfirmEmail;
use App\Email\EplResetPassword;
use App\Email\EpReviewThanks;
use App\Email\FeaturedCompany;
use App\Email\FriendInvite;
use App\Email\InviteCustomers;
use App\Email\InviteFeedback;
use App\Email\LastViewedMonthly;
use App\Email\MatchmakingActiveBuyer;
use App\Email\MatchmakingActiveSeller;
use App\Email\MatchmakingNewBuyer;
use App\Email\MatchmakingNewSeller;
use App\Email\MatchmakingPendingBuyer;
use App\Email\MatchmakingPendingSeller;
use App\Email\OutOfStockBackInStock;
use App\Email\OutOfStockItem;
use App\Email\OutOfStockSoon;
use App\Email\PoShippingMethod;
use App\Email\PromoPackageCertified;
use App\Email\RegisterBrandAmbassador;
use App\Email\ResetPasswordEmail;
use App\Email\RestoreAccountEmail;
use App\Email\SellerItemsViews;
use App\Email\ShareCrEvent;
use App\Email\ShareItem;
use App\Email\StartUsingEpAgain;
use App\Email\StayActiveOnEp;
use App\Email\SubscribeToNewsletter;
use App\Email\Systmessages;
use App\Email\UnblockResources;
use App\Email\UnreadNotifications;
use App\Email\VerificationDocumentsRemind;
use App\Email\WelcomeToExportPortal;
use ExportPortal\Enum\EnumCase;
use Money\Money;
use ValueError;

/**
 * @author Anton Zencenco
 *
 * @method static self ABUSE_ALERT()
 * @method static self ACCOUNT_NOW_BLOCKED()
 * @method static self ACCOUNT_RESTRICTION()
 * @method static self ACCOUNT_ACTIVATION()
 * @method static self ADD_PRODUCTS_REMIND()
 * @method static self ANSWER_ITEM_QUESTION()
 * @method static self BLOCKED_RESOURCES()
 * @method static self BLOGGERS_ADD_ARTICLE()
 * @method static self BLOGGERS_CONTACT()
 * @method static self CHANGE_EMAIL()
 * @method static self CHANGE_NOTIFICATION_EMAIL()
 * @method static self CHANGE_PASSWORD()
 * @method static self CHECK_YOUR_ACCOUNT_ON_EP()
 * @method static self CLEAN_SESSION()
 * @method static self COMPLETE_PROFILE_REMIND()
 * @method static self CONFIRM_DELETE_ACCOUNT()
 * @method static self CONFIRM_EMAIL()
 * @method static self CONFIRM_EVENT_ATTEND()
 * @method static self CONFIRM_FEEDBACK()
 * @method static self CONFIRM_REVIEW()
 * @method static self CONFIRM_SUBSCRIPTION()
 * @method static self CONFIRM_USER_CANCEL()
 * @method static self CONTACT_ADMIN()
 * @method static self CR_DELETE_REQUEST()
 * @method static self CR_SEND_ACTIVATION_LINK()
 * @method static self DEMO_WEBINAR_EP_2_WEEKS_AFTER()
 * @method static self DEMO_WEBINAR_EP_COMING_SOON()
 * @method static self DEMO_WEBINAR_EP_NEXT()
 * @method static self DEMO_WEBINAR_EP_REGISTERED()
 * @method static self DEMO_WEBINAR_EP_REQUESTING()
 * @method static self DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_BUYERS()
 * @method static self DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_SELLERS()
 * @method static self DEMO_WEBINAR_EP_TOMORROW()
 * @method static self DEMO_WEBINAR_EP_UNREGISTERED()
 * @method static self DOWNLOADABLE_MATERIALS_DATA()
 * @method static self DOWNLOADABLE_MATERIALS_SHARE()
 * @method static self DRAFT_ITEM_EXPIRATION_FIRST_EMAIL()
 * @method static self DRAFT_ITEM_INCENTIVE_OFFER_EMAIL()
 * @method static self DRAFT_ITEM_WARNING_DELETE_EMAIL()
 * @method static self EMAIL_FRIEND_ABOUT_B2B()
 * @method static self EMAIL_FRIEND_ABOUT_COMPANY()
 * @method static self EMAIL_FRIEND_ABOUT_COMPANY_UPDATES()
 * @method static self EMAIL_FRIEND_ABOUT_LIBRARY()
 * @method static self EMAIL_FRIEND_ABOUT_NEWS()
 * @method static self EMAIL_FRIEND_ABOUT_PICTURE()
 * @method static self EMAIL_FRIEND_ABOUT_SHIPPER_COMPANY()
 * @method static self EMAIL_FRIEND_ABOUT_USER()
 * @method static self EMAIL_FRIEND_ABOUT_VIDEO()
 * @method static self EMAIL_FRIEND_ABOUT_EP_EVENT()
 * @method static self EMAIL_USER()
 * @method static self EMAIL_USER_ABOUT_BILL()
 * @method static self EMAIL_USER_ABOUT_FREE_FEATURED_ITEMS()
 * @method static self ENVELOPE_EXPIRES_SOON_FOR_SENDER()
 * @method static self ENVELOPE_EXPIRES_SOON_FOR_SIGNER()
 * @method static self EPL_CLEAR_SESSION()
 * @method static self EPL_CONFIRM_EMAIL()
 * @method static self EPL_RESET_PASSWORD()
 * @method static self EP_REVIEW_THANKS()
 * @method static self FEATURED_COMPANY()
 * @method static self FRIEND_INVITE()
 * @method static self INVITE_CUSTOMERS()
 * @method static self INVITE_FEEDBACK()
 * @method static self LAST_VIEWED_MONTHLY()
 * @method static self MATCHMAKING_ACTIVE_BUYER()
 * @method static self MATCHMAKING_ACTIVE_SELLER()
 * @method static self MATCHMAKING_NEW_BUYER()
 * @method static self MATCHMAKING_NEW_SELLER()
 * @method static self MATCHMAKING_PENDING_BUYER()
 * @method static self MATCHMAKING_PENDING_SELLER()
 * @method static self OUT_OF_STOCK_BACK_IN_STOCK()
 * @method static self OUT_OF_STOCK_ITEM()
 * @method static self OUT_OF_STOCK_SOON()
 * @method static self PROMO_PACKAGE_CERTIFIED()
 * @method static self REGISTER_BRAND_AMBASSADOR()
 * @method static self RESET_PASSWORD_EMAIL()
 * @method static self RESTORE_ACCOUNT_EMAIL()
 * @method static self SHARE_CR_EVENT()
 * @method static self SHARE_ITEM()
 * @method static self START_USING_EP_AGAIN()
 * @method static self STAY_ACTIVE_ON_EP()
 * @method static self SUBSCRIBE_TO_NEWSLETTER()
 * @method static self SYSTMESSAGES()
 * @method static self UNBLOCK_RESOURCES()
 * @method static self UNREAD_NOTIFICATIONS()
 * @method static self VERIFICATION_DOCUMENTS_REMIND()
 * @method static self WELCOME_TO_EXPORT_PORTAL()
 * @method static self PO_SHIPPING_METHOD()
 * @method static self EMAIL_ORDER_CANCELLED_BY_BUYER()
 * @method static self EMAIL_ORDER_CANCELLED_BY_SELLER()
 * @method static self EMAIL_ORDER_CANCELLED_BY_MANAGER()
 * @method static self SELLER_ITEMS_VIEWS()
 * @method static self CHANGED_DROPLIST_PRICE()
 * @method static self CALENDAR_EP_EVENT_REMINDER()
 */
final class EmailTemplate extends EnumCase
{
    public const ABUSE_ALERT = 'moderation_abuse_alert';
    public const ACCOUNT_NOW_BLOCKED = 'account_is_now_blocked';
    public const ACCOUNT_RESTRICTION = 'account_restriction';
    public const ACCOUNT_ACTIVATION = 'account_activation';
    public const ADD_PRODUCTS_REMIND = 'add_products_remind';
    public const ANSWER_ITEM_QUESTION = 'email_answer_item_question';
    public const BLOCKED_RESOURCES = 'moderation_block_resource';
    public const BLOGGERS_ADD_ARTICLE = 'bloggers_add_article';
    public const BLOGGERS_CONTACT = 'contact_bloggers';
    public const CHANGE_EMAIL = 'email_change';
    public const CHANGE_NOTIFICATION_EMAIL = 'notification_email_change';
    public const CHANGE_PASSWORD = 'password_change';
    public const CHECK_YOUR_ACCOUNT_ON_EP = 'check_your_account_on_ep';
    public const CLEAN_SESSION = 'clean_session';
    public const CONFIRM_DELETE_ACCOUNT = 'delete_account_confirmation';
    public const COMPLETE_PROFILE_REMIND = 'complete_profile_remind';
    public const CONFIRM_EMAIL = 'confirm_email';
    public const CONFIRM_EVENT_ATTEND = 'confirm_event_attend';
    public const CONFIRM_FEEDBACK = 'confirm_feedback';
    public const CONFIRM_REVIEW = 'confirm_review';
    public const CONFIRM_SUBSCRIPTION = 'confirm_subscription';
    public const CONFIRM_USER_CANCEL = 'confirmation_user_cancel';
    public const CONTACT_ADMIN = 'contact_admin';
    public const CR_DELETE_REQUEST = 'cr_delete_request';
    public const CR_SEND_ACTIVATION_LINK = 'cr_send_activation_link';
    public const DEMO_WEBINAR_EP_2_WEEKS_AFTER = '2weeks_after_demo_webinar';
    public const DEMO_WEBINAR_EP_COMING_SOON = 'coming_soon_ep_webinar_demo';
    public const DEMO_WEBINAR_EP_NEXT = 'next_demo_webinar';
    public const DEMO_WEBINAR_EP_REGISTERED = 'thanks_for_participated_ep_demo_webinar_registered';
    public const DEMO_WEBINAR_EP_REQUESTING = 'requesting_a_demo';
    public const DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_BUYERS = 'thanks_for_participated_ep_demo_webinar_buyers';
    public const DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_SELLERS = 'thanks_for_participated_ep_demo_webinar_sellers';
    public const DEMO_WEBINAR_EP_TOMORROW = 'tomorrow_ep_webinar_demo';
    public const DEMO_WEBINAR_EP_UNREGISTERED = 'thanks_for_participated_ep_demo_webinar_unregistered';
    public const DOWNLOADABLE_MATERIALS_DATA = 'downloadable_materials_data';
    public const DOWNLOADABLE_MATERIALS_SHARE = 'downloadable_materials_share';
    public const DRAFT_ITEM_EXPIRATION_FIRST_EMAIL = 'draft_item_expiration_first_email';
    public const DRAFT_ITEM_INCENTIVE_OFFER_EMAIL = 'draft_item_incentive_offer_email';
    public const DRAFT_ITEM_WARNING_DELETE_EMAIL = 'draft_item_warning_delete_email';
    public const EMAIL_FRIEND_ABOUT_B2B = 'email_friend_about_b2b';
    public const EMAIL_FRIEND_ABOUT_COMPANY = 'email_friend_about_company';
    public const EMAIL_FRIEND_ABOUT_COMPANY_UPDATES = 'email_friend_about_company_updates';
    public const EMAIL_FRIEND_ABOUT_LIBRARY = 'email_friend_about_library';
    public const EMAIL_FRIEND_ABOUT_NEWS = 'email_friend_about_news';
    public const EMAIL_FRIEND_ABOUT_PICTURE = 'email_friend_about_picture';
    public const EMAIL_FRIEND_ABOUT_SHIPPER_COMPANY = 'email_friend_about_shipper_company';
    public const EMAIL_FRIEND_ABOUT_USER = 'email_friend_about_user';
    public const EMAIL_FRIEND_ABOUT_VIDEO = 'email_friend_about_video';
    public const EMAIL_FRIEND_ABOUT_EP_EVENT = 'email_friend_about_ep_event';
    public const EMAIL_USER = 'email_user';
    public const EMAIL_USER_ABOUT_BILL = 'email_user_about_bill';
    public const EMAIL_USER_ABOUT_FREE_FEATURED_ITEMS = 'email_user_about_free_featured_items';
    public const ENVELOPE_EXPIRES_SOON_FOR_SENDER = 'envelope_expires_soon_for_sender';
    public const ENVELOPE_EXPIRES_SOON_FOR_SIGNER = 'envelope_expires_soon_for_signer';
    public const EPL_CLEAR_SESSION = 'epl_clear_session';
    public const EPL_CONFIRM_EMAIL = 'epl_confirm_email';
    public const EPL_RESET_PASSWORD = 'epl_reset_password';
    public const EP_REVIEW_THANKS = 'thanks_for_ep_review';
    public const FEATURED_COMPANY = 'directory_change_featured_status';
    public const FRIEND_INVITE = 'friend_invite';
    public const INVITE_CUSTOMERS = 'invite_customers';
    public const INVITE_FEEDBACK = 'invite_feedback';
    public const LAST_VIEWED_MONTHLY = 'last_viewed_monthly';
    public const MATCHMAKING_ACTIVE_BUYER = 'matchmaking_active_buyer';
    public const MATCHMAKING_ACTIVE_SELLER = 'matchmaking_active_seller';
    public const MATCHMAKING_NEW_BUYER = 'matchmaking_new_buyer';
    public const MATCHMAKING_NEW_SELLER = 'matchmaking_new_seller';
    public const MATCHMAKING_PENDING_BUYER = 'matchmaking_pending_buyer';
    public const MATCHMAKING_PENDING_SELLER = 'matchmaking_pending_seller';
    public const OUT_OF_STOCK_BACK_IN_STOCK = 'out_of_stock_back_in_stock';
    public const OUT_OF_STOCK_ITEM = 'out_of_stock_item';
    public const OUT_OF_STOCK_SOON = 'out_of_stock_soon';
    public const PROMO_PACKAGE_CERTIFIED = 'promo_package_certified';
    public const REGISTER_BRAND_AMBASSADOR = 'register_brand_ambassador';
    public const RESET_PASSWORD_EMAIL = 'authenticate_reset_password';
    public const RESTORE_ACCOUNT_EMAIL = 'restore_user_reset_password';
    public const SHARE_CR_EVENT = 'share_cr_event';
    public const SHARE_ITEM = 'share_item';
    public const START_USING_EP_AGAIN = 'start_using_ep_again';
    public const STAY_ACTIVE_ON_EP = 'stay_active_on_ep';
    public const SUBSCRIBE_TO_NEWSLETTER = 'subscribe_to_newsletter';
    public const SYSTMESSAGES = 'email_systmessages';
    public const UNBLOCK_RESOURCES = 'moderation_unblock_resource';
    public const UNREAD_NOTIFICATIONS = 'unread_notifications';
    public const VERIFICATION_DOCUMENTS_REMIND = 'verification_documents_remind';
    public const WELCOME_TO_EXPORT_PORTAL = 'welcome_to_export_portal';
    public const PO_SHIPPING_METHOD = 'po_shipping_method';
    public const EMAIL_ORDER_CANCELLED_BY_BUYER = 'order_cancel_by_buyer';
    public const EMAIL_ORDER_CANCELLED_BY_SELLER = 'order_cancel_by_seller';
    public const EMAIL_ORDER_CANCELLED_BY_MANAGER = 'order_cancel_by_manager';
    public const SELLER_ITEMS_VIEWS = 'seller_items_views';
    public const CHANGED_DROPLIST_PRICE = 'changed_droplist_price';
    public const CALENDAR_EP_EVENT_REMINDER = 'calendar_ep_event_reminder';

    /**
     * Get the class name for current enum case.
     */
    public function className(): string
    {
        return static::getClassName($this);
    }

    /**
     * Get the template data for current enum case.
     */
    public function templateData(): array
    {
        return static::getTemplateData($this);
    }

    /**
     * Creates the enum case from class name.
     *
     * @throws ValueError for invalid class names
     */
    public static function fromClassName(string $className)
    {
        switch ($className) {
            case AbuseAlert::class: return static::ABUSE_ALERT();
            case AccountIsNowBlocked::class: return static::ACCOUNT_NOW_BLOCKED();
            case AccountRestriction::class: return static::ACCOUNT_RESTRICTION();
            case ActivateAccount::class: return static::ACCOUNT_ACTIVATION();
            case AddProductsRemind::class: return static::ADD_PRODUCTS_REMIND();
            case AnswerItemQuestion::class: return static::ANSWER_ITEM_QUESTION();
            case BlockResources::class: return static::BLOCKED_RESOURCES();
            case BloggersAddArticle::class: return static::BLOGGERS_ADD_ARTICLE();
            case BloggersContact::class: return static::BLOGGERS_CONTACT();
            case ChangeEmail::class: return static::CHANGE_EMAIL();
            case ChangeNotificationEmail::class: return static::CHANGE_NOTIFICATION_EMAIL();
            case ChangePassword::class: return static::CHANGE_PASSWORD();
            case CheckYourAccountOnEp::class: return static::CHECK_YOUR_ACCOUNT_ON_EP();
            case CleanSession::class: return static::CLEAN_SESSION();
            case CompleteProfileRemind::class: return static::COMPLETE_PROFILE_REMIND();
            case ConfirmDeleteAccount::class: return static::CONFIRM_DELETE_ACCOUNT();
            case ConfirmEmail::class: return static::CONFIRM_EMAIL();
            case ConfirmEventAttend::class: return static::CONFIRM_EVENT_ATTEND();
            case ConfirmFeedback::class: return static::CONFIRM_FEEDBACK();
            case ConfirmReview::class: return static::CONFIRM_REVIEW();
            case ConfirmSubscription::class: return static::CONFIRM_SUBSCRIPTION();
            case ConfirmUserCancel::class: return static::CONFIRM_USER_CANCEL();
            case ContactAdmin::class: return static::CONTACT_ADMIN();
            case CrDeleteRequest::class: return static::CR_DELETE_REQUEST();
            case CrSendActivationLink::class: return static::CR_SEND_ACTIVATION_LINK();
            case DemoWebinarEp2WeeksAfter::class: return static::DEMO_WEBINAR_EP_2_WEEKS_AFTER();
            case DemoWebinarEpComingSoon::class: return static::DEMO_WEBINAR_EP_COMING_SOON();
            case DemoWebinarEpNext::class: return static::DEMO_WEBINAR_EP_NEXT();
            case DemoWebinarEpRegistered::class: return static::DEMO_WEBINAR_EP_REGISTERED();
            case DemoWebinarEpRequesting::class: return static::DEMO_WEBINAR_EP_REQUESTING();
            case DemoWebinarEpThanksForParticipatedBuyers::class: return static::DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_BUYERS();
            case DemoWebinarEpThanksForParticipatedSellers::class: return static::DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_SELLERS();
            case DemoWebinarEpTomorrow::class: return static::DEMO_WEBINAR_EP_TOMORROW();
            case DemoWebinarEpUnRegistered::class: return static::DEMO_WEBINAR_EP_UNREGISTERED();
            case DownloadableMaterialsData::class: return static::DOWNLOADABLE_MATERIALS_DATA();
            case DownloadableMaterialsShare::class: return static::DOWNLOADABLE_MATERIALS_SHARE();
            case DraftItemExpirationFirstEmail::class: return static::DRAFT_ITEM_EXPIRATION_FIRST_EMAIL();
            case DraftItemIncentiveOfferEmail::class: return static::DRAFT_ITEM_INCENTIVE_OFFER_EMAIL();
            case DraftItemWarningDeleteEmail::class: return static::DRAFT_ITEM_WARNING_DELETE_EMAIL();
            case EmailFriendAboutB2b::class: return static::EMAIL_FRIEND_ABOUT_B2B();
            case EmailFriendAboutCompany::class: return static::EMAIL_FRIEND_ABOUT_COMPANY();
            case EmailFriendAboutCompanyUpdates::class: return static::EMAIL_FRIEND_ABOUT_COMPANY_UPDATES();
            case EmailFriendAboutLibrary::class: return static::EMAIL_FRIEND_ABOUT_LIBRARY();
            case EmailFriendAboutNews::class: return static::EMAIL_FRIEND_ABOUT_NEWS();
            case EmailFriendAboutPicture::class: return static::EMAIL_FRIEND_ABOUT_PICTURE();
            case EmailFriendAboutShipperCompany::class: return static::EMAIL_FRIEND_ABOUT_SHIPPER_COMPANY();
            case EmailFriendAboutUser::class: return static::EMAIL_FRIEND_ABOUT_USER();
            case EmailFriendAboutVideo::class: return static::EMAIL_FRIEND_ABOUT_VIDEO();
            case EmailFriendAboutEpEvent::class: return static::EMAIL_FRIEND_ABOUT_EP_EVENT();
            case EmailUser::class: return static::EMAIL_USER();
            case EmailUserAboutBill::class: return static::EMAIL_USER_ABOUT_BILL();
            case EmailOrderCancelledByBuyer::class: return static::EMAIL_ORDER_CANCELLED_BY_BUYER();
            case EmailOrderCancelledBySeller::class: return static::EMAIL_ORDER_CANCELLED_BY_SELLER();
            case EmailOrderCancelledByManager::class: return static::EMAIL_ORDER_CANCELLED_BY_MANAGER();
            case EmailUserAboutFreeFeaturedItems::class: return static::EMAIL_USER_ABOUT_FREE_FEATURED_ITEMS();
            case EnvelopeExpiresSoonForSender::class: return static::ENVELOPE_EXPIRES_SOON_FOR_SENDER();
            case EnvelopeExpiresSoonForSigner::class: return static::ENVELOPE_EXPIRES_SOON_FOR_SIGNER();
            case EplClearSession::class: return static::EPL_CLEAR_SESSION();
            case EplConfirmEmail::class: return static::EPL_CONFIRM_EMAIL();
            case EplResetPassword::class: return static::EPL_RESET_PASSWORD();
            case EpReviewThanks::class: return static::EP_REVIEW_THANKS();
            case FeaturedCompany::class: return static::FEATURED_COMPANY();
            case FriendInvite::class: return static::FRIEND_INVITE();
            case InviteCustomers::class: return static::INVITE_CUSTOMERS();
            case InviteFeedback::class: return static::INVITE_FEEDBACK();
            case LastViewedMonthly::class: return static::LAST_VIEWED_MONTHLY();
            case MatchmakingActiveBuyer::class: return static::MATCHMAKING_ACTIVE_BUYER();
            case MatchmakingActiveSeller::class: return static::MATCHMAKING_ACTIVE_SELLER();
            case MatchmakingNewBuyer::class: return static::MATCHMAKING_NEW_BUYER();
            case MatchmakingNewSeller::class: return static::MATCHMAKING_NEW_SELLER();
            case MatchmakingPendingBuyer::class: return static::MATCHMAKING_PENDING_BUYER();
            case MatchmakingPendingSeller::class: return static::MATCHMAKING_PENDING_SELLER();
            case OutOfStockBackInStock::class: return static::OUT_OF_STOCK_BACK_IN_STOCK();
            case OutOfStockItem::class: return static::OUT_OF_STOCK_ITEM();
            case OutOfStockSoon::class: return static::OUT_OF_STOCK_SOON();
            case PromoPackageCertified::class: return static::PROMO_PACKAGE_CERTIFIED();
            case RegisterBrandAmbassador::class: return static::REGISTER_BRAND_AMBASSADOR();
            case ResetPasswordEmail::class: return static::RESET_PASSWORD_EMAIL();
            case RestoreAccountEmail::class: return static::RESTORE_ACCOUNT_EMAIL();
            case ShareCrEvent::class: return static::SHARE_CR_EVENT();
            case ShareItem::class: return static::SHARE_ITEM();
            case StartUsingEpAgain::class: return static::START_USING_EP_AGAIN();
            case StayActiveOnEp::class: return static::STAY_ACTIVE_ON_EP();
            case SubscribeToNewsletter::class: return static::SUBSCRIBE_TO_NEWSLETTER();
            case Systmessages::class: return static::SYSTMESSAGES();
            case UnblockResources::class: return static::UNBLOCK_RESOURCES();
            case UnreadNotifications::class: return static::UNREAD_NOTIFICATIONS();
            case VerificationDocumentsRemind::class: return static::VERIFICATION_DOCUMENTS_REMIND();
            case WelcomeToExportPortal::class: return static::WELCOME_TO_EXPORT_PORTAL();
            case PoShippingMethod::class: return static::PO_SHIPPING_METHOD();
            case SellerItemsViews::class: return static::SELLER_ITEMS_VIEWS();
            case ChangeDroplistPrice::class: return static::CHANGED_DROPLIST_PRICE();
            case CalendarEpEventReminder::class: return static::CALENDAR_EP_EVENT_REMINDER();
        }

        throw new ValueError(\sprintf('"%s" is not a valid class name value for enum "%s"', $className, static::class));
    }

    /**
     * Creates the enum case from class name or return NULL if there is none,.
     */
    public static function tryFromClassName(string $className)
    {
        try {
            return static::fromClassName($className);
        } catch (ValueError $e) {
            return null;
        }
    }

    /**
     * Get the class name for enum case.
     */
    public static function getClassName(self $value): string
    {
        switch ($value) {
            case static::ABUSE_ALERT(): return AbuseAlert::class;
            case static::ACCOUNT_NOW_BLOCKED(): return AccountIsNowBlocked::class;
            case static::ACCOUNT_RESTRICTION(): return AccountRestriction::class;
            case static::ACCOUNT_ACTIVATION(): return ActivateAccount::class;
            case static::ADD_PRODUCTS_REMIND(): return AddProductsRemind::class;
            case static::ANSWER_ITEM_QUESTION(): return AnswerItemQuestion::class;
            case static::BLOCKED_RESOURCES(): return BlockResources::class;
            case static::BLOGGERS_ADD_ARTICLE(): return BloggersAddArticle::class;
            case static::BLOGGERS_CONTACT(): return BloggersContact::class;
            case static::CHANGE_EMAIL(): return ChangeEmail::class;
            case static::CHANGE_NOTIFICATION_EMAIL(): return ChangeNotificationEmail::class;
            case static::CHANGE_PASSWORD(): return ChangePassword::class;
            case static::CHECK_YOUR_ACCOUNT_ON_EP(): return CheckYourAccountOnEp::class;
            case static::CLEAN_SESSION(): return CleanSession::class;
            case static::COMPLETE_PROFILE_REMIND(): return CompleteProfileRemind::class;
            case static::CONFIRM_DELETE_ACCOUNT(): return ConfirmDeleteAccount::class;
            case static::CONFIRM_EMAIL(): return ConfirmEmail::class;
            case static::CONFIRM_EVENT_ATTEND(): return ConfirmEventAttend::class;
            case static::CONFIRM_FEEDBACK(): return ConfirmFeedback::class;
            case static::CONFIRM_REVIEW(): return ConfirmReview::class;
            case static::CONFIRM_SUBSCRIPTION(): return ConfirmSubscription::class;
            case static::CONFIRM_USER_CANCEL(): return ConfirmUserCancel::class;
            case static::CONTACT_ADMIN(): return ContactAdmin::class;
            case static::CR_DELETE_REQUEST(): return CrDeleteRequest::class;
            case static::CR_SEND_ACTIVATION_LINK(): return CrSendActivationLink::class;
            case static::DEMO_WEBINAR_EP_2_WEEKS_AFTER(): return DemoWebinarEp2WeeksAfter::class;
            case static::DEMO_WEBINAR_EP_COMING_SOON(): return DemoWebinarEpComingSoon::class;
            case static::DEMO_WEBINAR_EP_NEXT(): return DemoWebinarEpNext::class;
            case static::DEMO_WEBINAR_EP_REGISTERED(): return DemoWebinarEpRegistered::class;
            case static::DEMO_WEBINAR_EP_REQUESTING(): return DemoWebinarEpRequesting::class;
            case static::DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_BUYERS(): return DemoWebinarEpThanksForParticipatedBuyers::class;
            case static::DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_SELLERS(): return DemoWebinarEpThanksForParticipatedSellers::class;
            case static::DEMO_WEBINAR_EP_TOMORROW(): return DemoWebinarEpTomorrow::class;
            case static::DEMO_WEBINAR_EP_UNREGISTERED(): return DemoWebinarEpUnRegistered::class;
            case static::DOWNLOADABLE_MATERIALS_DATA(): return DownloadableMaterialsData::class;
            case static::DOWNLOADABLE_MATERIALS_SHARE(): return DownloadableMaterialsShare::class;
            case static::DRAFT_ITEM_EXPIRATION_FIRST_EMAIL(): return DraftItemExpirationFirstEmail::class;
            case static::DRAFT_ITEM_INCENTIVE_OFFER_EMAIL(): return DraftItemIncentiveOfferEmail::class;
            case static::DRAFT_ITEM_WARNING_DELETE_EMAIL(): return DraftItemWarningDeleteEmail::class;
            case static::EMAIL_FRIEND_ABOUT_B2B(): return EmailFriendAboutB2b::class;
            case static::EMAIL_FRIEND_ABOUT_COMPANY(): return EmailFriendAboutCompany::class;
            case static::EMAIL_FRIEND_ABOUT_COMPANY_UPDATES(): return EmailFriendAboutCompanyUpdates::class;
            case static::EMAIL_FRIEND_ABOUT_LIBRARY(): return EmailFriendAboutLibrary::class;
            case static::EMAIL_FRIEND_ABOUT_NEWS(): return EmailFriendAboutNews::class;
            case static::EMAIL_FRIEND_ABOUT_PICTURE(): return EmailFriendAboutPicture::class;
            case static::EMAIL_FRIEND_ABOUT_SHIPPER_COMPANY(): return EmailFriendAboutShipperCompany::class;
            case static::EMAIL_FRIEND_ABOUT_USER(): return EmailFriendAboutUser::class;
            case static::EMAIL_FRIEND_ABOUT_VIDEO(): return EmailFriendAboutVideo::class;
            case static::EMAIL_FRIEND_ABOUT_EP_EVENT(): return EmailFriendAboutEpEvent::class;
            case static::EMAIL_USER(): return EmailUser::class;
            case static::EMAIL_USER_ABOUT_BILL(): return EmailUserAboutBill::class;
            case static::EMAIL_ORDER_CANCELLED_BY_BUYER(): return EmailOrderCancelledByBuyer::class;
            case static::EMAIL_ORDER_CANCELLED_BY_SELLER(): return EmailOrderCancelledBySeller::class;
            case static::EMAIL_ORDER_CANCELLED_BY_MANAGER(): return EmailOrderCancelledByManager::class;
            case static::EMAIL_USER_ABOUT_FREE_FEATURED_ITEMS(): return EmailUserAboutFreeFeaturedItems::class;
            case static::ENVELOPE_EXPIRES_SOON_FOR_SENDER(): return EnvelopeExpiresSoonForSender::class;
            case static::ENVELOPE_EXPIRES_SOON_FOR_SIGNER(): return EnvelopeExpiresSoonForSigner::class;
            case static::EPL_CLEAR_SESSION(): return EplClearSession::class;
            case static::EPL_CONFIRM_EMAIL(): return EplConfirmEmail::class;
            case static::EPL_RESET_PASSWORD(): return EplResetPassword::class;
            case static::EP_REVIEW_THANKS(): return EpReviewThanks::class;
            case static::FEATURED_COMPANY(): return FeaturedCompany::class;
            case static::FRIEND_INVITE(): return FriendInvite::class;
            case static::INVITE_CUSTOMERS(): return InviteCustomers::class;
            case static::INVITE_FEEDBACK(): return InviteFeedback::class;
            case static::LAST_VIEWED_MONTHLY(): return LastViewedMonthly::class;
            case static::MATCHMAKING_ACTIVE_BUYER(): return MatchmakingActiveBuyer::class;
            case static::MATCHMAKING_ACTIVE_SELLER(): return MatchmakingActiveSeller::class;
            case static::MATCHMAKING_NEW_BUYER(): return MatchmakingNewBuyer::class;
            case static::MATCHMAKING_NEW_SELLER(): return MatchmakingNewSeller::class;
            case static::MATCHMAKING_PENDING_BUYER(): return MatchmakingPendingBuyer::class;
            case static::MATCHMAKING_PENDING_SELLER(): return MatchmakingPendingSeller::class;
            case static::OUT_OF_STOCK_BACK_IN_STOCK(): return OutOfStockBackInStock::class;
            case static::OUT_OF_STOCK_ITEM(): return OutOfStockItem::class;
            case static::OUT_OF_STOCK_SOON(): return OutOfStockSoon::class;
            case static::PROMO_PACKAGE_CERTIFIED(): return PromoPackageCertified::class;
            case static::REGISTER_BRAND_AMBASSADOR(): return RegisterBrandAmbassador::class;
            case static::RESET_PASSWORD_EMAIL(): return ResetPasswordEmail::class;
            case static::RESTORE_ACCOUNT_EMAIL(): return RestoreAccountEmail::class;
            case static::SHARE_CR_EVENT(): return ShareCrEvent::class;
            case static::SHARE_ITEM(): return ShareItem::class;
            case static::START_USING_EP_AGAIN(): return StartUsingEpAgain::class;
            case static::STAY_ACTIVE_ON_EP(): return StayActiveOnEp::class;
            case static::SUBSCRIBE_TO_NEWSLETTER(): return SubscribeToNewsletter::class;
            case static::SYSTMESSAGES(): return Systmessages::class;
            case static::UNBLOCK_RESOURCES(): return UnblockResources::class;
            case static::UNREAD_NOTIFICATIONS(): return UnreadNotifications::class;
            case static::VERIFICATION_DOCUMENTS_REMIND(): return VerificationDocumentsRemind::class;
            case static::WELCOME_TO_EXPORT_PORTAL(): return WelcomeToExportPortal::class;
            case static::PO_SHIPPING_METHOD(): return PoShippingMethod::class;
            case static::SELLER_ITEMS_VIEWS(): return SellerItemsViews::class;
            case static::CHANGED_DROPLIST_PRICE(): return ChangeDroplistPrice::class;
            case static::CALENDAR_EP_EVENT_REMINDER(): return CalendarEpEventReminder::class;
        }

        return "";
    }

    /**
     * Get template data for enum case.
     */
    public static function getTemplateData(self $value): array
    {
        switch ($value) {
            case static::ABUSE_ALERT(): return ["User Name Test", "Type Test", "Title Test", "Link Test", "Content Test", "Abuse Test", "Date Test"];
            case static::ACCOUNT_NOW_BLOCKED(): return ["User Name Test"];
            case static::ACCOUNT_RESTRICTION(): return ["User Name Test"];
            case static::ACCOUNT_ACTIVATION(): return ["User Name Test", "Seller",];
            case static::ADD_PRODUCTS_REMIND(): return ["User Name Test"];
            case static::ANSWER_ITEM_QUESTION(): return ["User Name Test","Seller Name Test",['title_question' => "Title question Test",'question' => "Question Test"],"Title Test", 0,];
            case static::BLOCKED_RESOURCES(): return ["User Name Test", "Abuse Test", "Type Test", "Type Test", "Title Test"];
            case static::BLOGGERS_ADD_ARTICLE(): return [];
            case static::BLOGGERS_CONTACT(): return ["User Name Test", "Message Test"];
            case static::CHANGE_EMAIL(): return ["User Name Test", "Code Test"];
            case static::CHANGE_NOTIFICATION_EMAIL(): return ["User Name Test", "Code Test"];
            case static::CHANGE_PASSWORD(): return ["User Name Test", "Code Test"];
            case static::CHECK_YOUR_ACCOUNT_ON_EP(): return ["User Name Test"];
            case static::CLEAN_SESSION(): return ["User Name Test", "Url Test"];
            case static::COMPLETE_PROFILE_REMIND(): return ["User Name Test"];
            case static::CONFIRM_DELETE_ACCOUNT(): return ["User Name Test"];
            case static::CONFIRM_EMAIL(): return ["User Name Test", "Token Test"];
            case static::CONFIRM_EVENT_ATTEND(): return ["User Name Test", [ "event_name" => "Name test", "id_event" => "Id test"],"Attendance id Test","Attend Email Test"];
            case static::CONFIRM_FEEDBACK(): return ["User Name Test", "Company Name Test", "Company Url Test", "Code Test"];
            case static::CONFIRM_REVIEW(): return ["User Name Test", ["id" => "Test", "title" => "Title Test" ], [], "Code Test"];
            case static::CONFIRM_SUBSCRIPTION(): return ["Token Test"];
            case static::CONFIRM_USER_CANCEL(): return ["User Name Test", "Code Test"];
            case static::CONTACT_ADMIN(): return ["User Name Test", "Email Test", "Phone Test", "Message Test"];
            case static::CR_DELETE_REQUEST(): return ["User Name Test", "Notice Test"];
            case static::CR_SEND_ACTIVATION_LINK(): return ["User Name Test", "Abuse Name Test", "Type Name Test", "Title Name Test"];
            case static::DEMO_WEBINAR_EP_2_WEEKS_AFTER(): return ["User Name Test", '2021-12-19'];
            case static::DEMO_WEBINAR_EP_COMING_SOON(): return ["User Name Test", "Date Test", "Time Test"];
            case static::DEMO_WEBINAR_EP_NEXT(): return ["User Name Test", "Date Test", "Time Test", "Code Test"];
            case static::DEMO_WEBINAR_EP_REGISTERED(): return ["User Name Test"];
            case static::DEMO_WEBINAR_EP_REQUESTING(): return ["User Name Test"];
            case static::DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_BUYERS(): return ["User Name Test"];
            case static::DEMO_WEBINAR_EP_THANKS_FOR_PARTICIPATED_SELLERS(): return ["User Name Test"];
            case static::DEMO_WEBINAR_EP_TOMORROW(): return ["User Name Test", "Date Test", "Time Test", "Link Test"];
            case static::DEMO_WEBINAR_EP_UNREGISTERED(): return ["User Name Test"];
            case static::DOWNLOADABLE_MATERIALS_DATA(): return ["User Name Test", "Title Name Test", "Slug Name Test", "Signature Name Test"];
            case static::DOWNLOADABLE_MATERIALS_SHARE(): return ["User Name Test", "Slug Test"];
            case static::DRAFT_ITEM_EXPIRATION_FIRST_EMAIL(): return ["User Name Test", "Counter Test", "Expire Days Test", "Date Name Test", "Id Test"];
            case static::DRAFT_ITEM_INCENTIVE_OFFER_EMAIL(): return ["User Name Test", "Counter Test", "Expire Days Test", "Date Name Test", "Id Test"];
            case static::DRAFT_ITEM_WARNING_DELETE_EMAIL(): return ["User Name Test", "Counter Test", "Date Name Test", "Id Test"];
            case static::EMAIL_FRIEND_ABOUT_B2B(): return ["Message Test", [ "b2b_title" => "Title Test", "id_request" => "id test", "id_company" => "id test", "logo_company" => "logo test"]];
            case static::EMAIL_FRIEND_ABOUT_COMPANY(): return ["User Name Test", "Message Test", [ "name_company" => "Test", "id_company" => "Test", "logo_company" => "Test", "description_company" => "Test"]];
            case static::EMAIL_FRIEND_ABOUT_COMPANY_UPDATES(): return ["User Name Test", "Message Test", [ "name_company" => "Test", "id_company" => "Test", "logo_company" => "Test", "description_company" => "Test"], "Description Test", "Image Test"];
            case static::EMAIL_FRIEND_ABOUT_LIBRARY(): return ["User Name Test", "Message Test", ["name_company" => "Test", "id_company" => "Test", "logo_company" => "Test", "description_company" => "Test"], ["id_file" => "Test","description_file" => "Test","extension_file" => "Test"]];
            case static::EMAIL_FRIEND_ABOUT_NEWS(): return ["User Name Test", "Message Test", [ "name_company" => "Test", "id_company" => "Test", "logo_company" => "Test", "description_company" => "Test"], [ "id_news" => "Test", "title_news" => "Test", "text_news" => "Test", "id_company" => "Test", "image_thumb_news" => "Test" ]];
            case static::EMAIL_FRIEND_ABOUT_PICTURE(): return ["User Name Test", "Message Test", [ "name_company" => "Test", "id_company" => "Test", "logo_company" => "Test", "description_company" => "Test"], ["id_photo" => "Test", "title_photo" => "Test", "description_photo" => "Test", "id_company" => "Test", "path_photo" => "Test"]];
            case static::EMAIL_FRIEND_ABOUT_SHIPPER_COMPANY(): return ["User Name Test", "Message Test", ["id" => "Test", "logo" => "Test", "co_name" => "Test", "description" => "Test"]];
            case static::EMAIL_FRIEND_ABOUT_USER(): return ["User Name Test", "Message Test", ["user_name" => "Test", "idu" => "Test", "gr_type" => "Test", "description" => "Test", "user_photo" => "Test"]];
            case static::EMAIL_FRIEND_ABOUT_VIDEO(): return ["User Name Test", "Message Test", [ "user_name" => "Test", "idu" => "Test", "gr_type" => "Test", "description" => "Test", "user_photo" => "Test"], ["id_video" => "Test", "title_video" => "Test", "description_video" => "Test", "id_company" => "Test", "image_video" => "Test"]];
            case static::EMAIL_FRIEND_ABOUT_EP_EVENT(): return ["User Name", "Lorem Ipsum Dolor Amet...", ['id' => 123, 'title' => 'Event title']];
            case static::EMAIL_USER(): return ["User Name Test", "Message Test"];
            case static::EMAIL_USER_ABOUT_BILL(): return ["User Name Test", "Message Test", "Url Test"];
            case static::EMAIL_USER_ABOUT_FREE_FEATURED_ITEMS(): return ["User Name Test"];
            case static::ENVELOPE_EXPIRES_SOON_FOR_SENDER(): return ["User Name Test", "Order Number Test", "Id Order Test", "Due Date Test"];
            case static::ENVELOPE_EXPIRES_SOON_FOR_SIGNER(): return ["User Name Test", "Id Order Test"];
            case static::EPL_CLEAR_SESSION(): return ["User Name Test", "Token Test"];
            case static::EPL_CONFIRM_EMAIL(): return ["User Name Test", "Token Test"];
            case static::EPL_RESET_PASSWORD(): return ["User Name Test", "Token Test"];
            case static::EP_REVIEW_THANKS(): return ["User Name Test"];
            case static::FEATURED_COMPANY(): return ["User Name Test"];
            case static::FRIEND_INVITE(): return ["User Name Test", "Message Test"];
            case static::INVITE_CUSTOMERS(): return [[ "name_company"  => "Test"], "Message Test"];
            case static::INVITE_FEEDBACK(): return [[ "name_company"  => "Test"], "Hash Test", "Message Test"];
            case static::LAST_VIEWED_MONTHLY(): return ["User Name Test", "Links Test"];
            case static::MATCHMAKING_ACTIVE_BUYER(): return ["User Name Test", "Count Test", "Count Test"];
            case static::MATCHMAKING_ACTIVE_SELLER(): return ["User Name Test", "Count Test"];
            case static::MATCHMAKING_NEW_BUYER(): return ["User Name Test", "Count Test", "Count Test"];
            case static::MATCHMAKING_NEW_SELLER(): return ["User Name Test", "Count Test"];
            case static::MATCHMAKING_PENDING_BUYER(): return ["User Name Test", "Count Test", "Count Test"];
            case static::MATCHMAKING_PENDING_SELLER(): return ["User Name Test", "Count Test"];
            case static::OUT_OF_STOCK_BACK_IN_STOCK(): return ["User Name Test", "Company Name Test", ["id" => "Test", "title" => "Test"]];
            case static::OUT_OF_STOCK_ITEM(): return ["User Name Test", ["id" => "Test", "title" => "Test"]];
            case static::OUT_OF_STOCK_SOON(): return ["User Name Test", ["id" => "Test", "title" => "Test"]];
            case static::PROMO_PACKAGE_CERTIFIED(): return ["User Name Test", "Token Test"];
            case static::REGISTER_BRAND_AMBASSADOR(): return [];
            case static::RESET_PASSWORD_EMAIL(): return ["User Name Test", "Url Test"];
            case static::RESTORE_ACCOUNT_EMAIL(): return ["User Name Test", "Url Test"];
            case static::SHARE_CR_EVENT(): return ["User Name Test", "Message Test", "Link Test", [ "id_event" => "Test", "event_name" => "Test", "event_short_description" => "Test", "event_image" => "Test"]];
            case static::EMAIL_ORDER_CANCELLED_BY_BUYER(): return ["User Name Test", "Message Test", "Test Name", "Test Description"];
            case static::EMAIL_ORDER_CANCELLED_BY_SELLER(): return ["User Name Test", "Message Test", "Test Name", "Test Description"];
            case static::EMAIL_ORDER_CANCELLED_BY_MANAGER(): return ["User Name Test", "Test Name", "Test Description"];
            case static::SHARE_ITEM(): return ["User Name Test", "Message Test", ["id" => "Test", "title" => "Test"], "Photo Test"];
            case static::START_USING_EP_AGAIN(): return ["User Name Test"];
            case static::STAY_ACTIVE_ON_EP(): return ["User Name Test"];
            case static::SUBSCRIBE_TO_NEWSLETTER(): return [];
            case static::SYSTMESSAGES(): return ["User Name Test", "Date Test", "Title Test", "Additional Content Test"];
            case static::UNBLOCK_RESOURCES(): return ["User Name Test", "Type Test", "Type Test", "Title Test"];
            case static::UNREAD_NOTIFICATIONS(): return ["User Name Test", "Count Test", "warning Message Test", "notice Message Test"];
            case static::VERIFICATION_DOCUMENTS_REMIND(): return ["User Name Test"];
            case static::WELCOME_TO_EXPORT_PORTAL(): return ["User Name Test"];
            case static::PO_SHIPPING_METHOD(): return ["User Name Test", "Order Number Test", ["type_name" => "Test Name", "type_description" => "Test Description"]];
            case static::SELLER_ITEMS_VIEWS(): return ['Usinevici Alexandr', 25, []];
            case static::CHANGED_DROPLIST_PRICE(): return ['http://exportportal.loc/item/msi-radeon-r9-290-gaming-4g-922', 'MSI Radeon R9 290 GAMING 4G', '$19.99', '$3.99'];
            case static::CALENDAR_EP_EVENT_REMINDER(): return ['John Doe', 'The Second Coming of Christ', 1];
        }

        return [];
    }
}
