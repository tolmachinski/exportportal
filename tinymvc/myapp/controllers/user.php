<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Entities\CountryCodeInterface;
use App\Common\Contracts\Group\GroupType;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\DataProvider\AccountProvider;
use App\Email\ChangeEmail;
use App\Email\ChangeNotificationEmail;
use App\Email\ChangePassword;
use App\Email\EmailFriendAboutUser;
use App\Filesystem\EpEventFilePathGenerator;
use App\Filesystem\UserFilePathGenerator;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedEmailEvent;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedLogoEvent;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedPhotoEvent;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedProfileEvent;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedRightsEvent;
use App\Renderer\UserProfileEditViewRenderer;
use App\Services\EditRequest\ProfileEditRequestProcessingService;
use App\Services\PhoneCodesService;
use App\Validators\AdditionalSocialFieldsValidator;
use App\Validators\LegalNameValidator;
use App\Validators\UserProfilePreferencesValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteFile;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 * @property \User_Model                $user
 */
class User_Controller extends TinyMVC_Controller
{
    private $breadcrumbs = [];
    private $_id_user;

    public function index()
    {
        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        $this->_id_user = id_from_link($this->uri->segment(3));
        $main['user_main'] = $usersRepository->getUser($this->_id_user);

        if (
            empty($main['user_main'])
            || (
                1 === (int) $main['user_main']['fake_user']
                && !(
                    is_privileged('user', (int) $main['user_main']['idu'])
                        || have_right('moderate_content')
                )
            )
            ) {
            show_404();
        }

        if (1 === (int) $main['user_main']['fake_user']) {
            header('X-Robots-Tag: noindex');
        }

        if (!in_array($main['user_main']['gr_type'], ['Buyer', 'Seller', 'Shipper'])) {
            show_404();
        }

        $isActiveAccount = 'active' === $main['user_main']['status'];
        $isNotBlockedPublicPage = 0 === (int) $main['user_main']['user_page_blocked'];
        if (
            !(
                $isActiveAccount && $isNotBlockedPublicPage
                || is_privileged('user', (int) $main['user_main']['idu'])
                || have_right('moderate_content')
            )
        ) {
            show_blocked();
        }

        $main['user_country'] = $usersRepository->getUserCountry($this->_id_user);

        if ($this->_id_user == id_session() && !$this->session->country && have_right('manage_personal_info')) {
            $this->session->setMessages('Error: You must complete registration.', 'errors');
            headerRedirect(__SITE_URL . 'user/preferences');
        }

        $data = null;

        $this->load->model('Contact_User_Model', 'contact');

        if (__CACHE_ENABLE) {
            $this->load->model('Cache_Config_Model', 'cache_config');

            $c_config = $this->cache_config->get_cache_options('user');

            if (!empty($c_config) && $c_config['enable']) {
                $this->load->library('Cache', 'cache');
                $this->cache->init(['securityKey' => $c_config['folder']]);
                $data = $this->cache->get('user' . $this->_id_user);
            }
        }

        if (null == $data) {
            $this->load->model('User_photo_Model', 'user_photo');
            $this->load->model('Country_model', 'country');
            $this->load->model('UserGroup_Model', 'user_group');
            $this->load->model('User_Statistic_Model', 'user_statistic');

            $data['user_photo'] = $this->user_photo->get_photos(['id_user' => $this->_id_user]);

            switch ($main['user_main']['gr_type']) {
                case 'Seller':
                    $this->_get_seller_page_data($data);

                break;
                case 'Buyer':
                    $this->_get_buyer_page_data($data);

                break;
                case 'Shipper':
                    $this->_get_shipper_page_data($data);

                break;
            }

            // foreach($user_photo as $key => $photo){
            //  $data['user_photo'][$key]['main'] = $photo['name_photo'];
            // }

            if ($main['user_main']['state']) {
                $data['state'] = $this->country->get_state($main['user_main']['state'], 'state');
            }
            if ($main['user_main']['city']) {
                $data['city'] = $this->country->get_city($main['user_main']['city']);
            }

            $data['user_statistic'] = $this->user_statistic->get_user_statistic_simple($this->_id_user, ' item_questions_wrote, item_questions_answered, item_reviews_wrote, item_reviews_received, feedbacks_wrote,feedbacks_received, ep_questions_wrote, ep_answers_wrote');
            $data['user_rights'] = $this->user_group->get_users_right($this->_id_user);

            $user_group = $this->user_group->getGroups(['fields' => 'gr_name, idgroup, stamp_pic']);
            foreach ($user_group as $group) {
                $data['user_group'][$group['idgroup']] = [
                    'gr_name'   => $group['gr_name'],
                    'stamp_pic' => $group['stamp_pic'], ];
            }

            //seo
            $data['meta_params']['[USER_NAME]'] = $main['user_main']['fname'] . ' ' . $main['user_main']['lname'];
            $data['meta_params']['[image]'] = getDisplayImageLink(['{ID}' => $main['user_main']['idu'], '{FILE_NAME}' => $main['user_main']['user_photo']], 'users.main', ['no_image_group' => $main['user_main']['user_group']]);

            if (!empty($main['user_main']['description'])) {
                $data['meta_data']['description'] = truncWords($main['user_main']['description'], 20);
            }

            if (__CACHE_ENABLE && $c_config['enable']) {
                $this->cache->set('user' . $this->_id_user, $data, $c_config['cache_time']);
            }
        }

        $data['iFollow'] = false;
        if (logged_in()) {
            $data['followed'] = i_follow();
            $data['is_in_contact'] = $this->contact->is_in_contact($this->session->id, $this->_id_user);

            if (in_array($this->_id_user, $data['followed'])) {
                $data['iFollow'] = true;
            }
        }

        $this->breadcrumbs[] = [
            'link'  => __SITE_URL . 'usr/' . strForUrl($main['user_main']['fname'] . ' ' . $main['user_main']['lname']) . '-' . $main['user_main']['idu'],
            'title' => $main['user_main']['fname'] . ' ' . $main['user_main']['lname'],
        ];

        $chatBtn = new ChatButton(['recipient' => $main['user_main']['idu'], 'recipientStatus' => $main['user_main']['status']]);
        $data['chatBtn'] = $chatBtn->button();

        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['sidebar_left_content'] = 'new/user/simple_user/sidebar_view';
        $data['main_content'] = 'new/user/simple_user/index_view';

        $this->view->assign($main);
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function ajax_more_load()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $this->_load_main();
        $this->load->model('Itemcomments_Model', 'itemcomments');
        $this->load->model('Itemquestions_Model', 'itemquestions');
        $this->load->model('ItemsReview_Model', 'itemreviews');
        $this->load->model('UserFeedback_Model', 'userfeedbacks');
        $type = $this->uri->segment(3);

        $id_user = intval($_POST['user']);
        if (!$id_user) {
            return false;
        }
        $start = intval($_POST['start']);
        switch ($type) {
            case 'comments':
                $page = ceil($start / 10) + 1;
                $count = $this->itemcomments->count_comments(['user' => $id_user]);
                $data['comments'] = $this->itemcomments->get_comments(['user' => $id_user, 'page' => $page, 'per_p' => 10, 'count' => $count, 'map_tree' => false]);

                $display = $this->view->fetch('new/items_comments/item_view', $data);

            break;
            case 'questions':
                $page = ceil($start / 10) + 1;
                $count = $this->itemquestions->count_questions(['questioner' => $id_user]);
                $data['questions'] = $this->itemquestions->get_questions(['questioner' => $id_user, 'page' => $page, 'per_p' => 10, 'count' => $count]);
                foreach ($data['questions'] as $item) {
                    $array_id[] = $item['id_q'];
                }

                if (!empty($data['questions']) && $this->session->loggedIn) {
                    $data['helpful'] = $this->itemquestions->get_helpful_by_question(implode(',', $array_id), $this->session->id);
                }

                $display = $this->view->fetch('new/items_questions/item_view', $data);

            break;
            case 'community_questions':
                $question_categories_method = __SITE_LANG === 'en' ? 'getCategories' : 'getCategories_i18n';
                $data['quest_cats'] = arrayByKey(model('questions')->{$question_categories_method}(['visible' => 1]), 'idcat');

                $community_questions = $this->_get_community_questions($id_user, intval(ceil($start / 10) + 1), true);

                $data['hide_user_info'] = true;
                $data['questions'] = $community_questions['questions'];
                $count = $community_questions['count'];

                $display = $this->view->fetch('new/user/community_help/item_question_view', $data);

            break;
            case 'community_answers':
                $question_categories_method = __SITE_LANG === 'en' ? 'getCategories' : 'getCategories_i18n';
                $data['quest_cats'] = arrayByKey(model('questions')->{$question_categories_method}(['visible' => 1]), 'idcat');

                $community_questions_answers = $this->_get_community_answers($id_user, intval(ceil($start / 10) + 1), true);

                $data['questions'] = $community_questions_answers['questions'];
                $count = $community_questions_answers['count'];

                $display = $this->view->fetch('new/user/community_help/item_question_view', $data);

            break;
            case 'reviews':
                $count = $this->itemreviews->counter_by_conditions(['user' => $id_user]);
                $page = ceil($start / 10) + 1;

                $data['reviews'] = $this->itemreviews->get_user_reviews(['user' => $id_user, 'page' => $page, 'per_p' => 10, 'count' => $count]);
                if (!empty($data['reviews']) && logged_in()) {
                    $reviews_ids = array_column($data['reviews'], 'id_review', 'id_review');
                    $data['helpful_reviews'] = $this->itemreviews->get_helpful_by_review(implode(',', $reviews_ids), $this->session->id);
                }

                $display = $this->view->fetch('new/users_reviews/item_view', $data);

            break;
            case 'feedbacks_written':
                $data['feedback_written'] = true;
                $start = ceil(intval($_POST['start']) / 10) + 1;
                $count = $this->userfeedbacks->counter_by_conditions(['poster' => $id_user]);
                $data['feedbacks'] = array_filter((array) $this->userfeedbacks->get_user_feedbacks(['poster' => $id_user, 'db_keys' => 'id_feedback', 'count' => $count, 'page' => $start, 'per_p' => 10]));
                $feedbacks_keys = implode(',', array_keys($data['feedbacks']));

                // unserialize services
                foreach ($data['feedbacks'] as $key=>$value) {
                    if (!empty($data['feedbacks'][$key]['services'])) {
                        $data['feedbacks'][$key]['services'] = unserialize($data['feedbacks'][$key]['services']);
                    }
                }

                if (!empty($data['feedbacks']) && $this->session->loggedIn) {
                    $data['helpful_feedbacks'] = $this->userfeedbacks->get_helpful_by_feedback($feedbacks_keys, $this->session->id);
                }

                $display = $this->view->fetch('new/users_feedbacks/item_view', $data);

            break;
            case 'feedbacks':
                $data['feedback_written'] = false;
                $start = ceil(intval($_POST['start']) / 10) + 1;
                $count = $this->userfeedbacks->counter_by_conditions(['user' => $id_user]);
                $data['feedbacks'] = $this->userfeedbacks->get_user_feedbacks(['id_user' => $id_user, 'db_keys' => 'id_feedback', 'count' => $count, 'page' => $start, 'per_p' => 10]);
                $feedbacks_written_keys = implode(',', array_keys($data['feedbacks']));

                // unserialize services
                foreach ($data['feedbacks'] as $key=>$value) {
                    if (!empty($data['feedbacks'][$key]['services'])) {
                        $data['feedbacks'][$key]['services'] = unserialize($data['feedbacks'][$key]['services']);
                    }
                }

                if (!empty($data['feedbacks']) && $this->session->loggedIn) {
                    $data['helpful_feedbacks'] = $this->userfeedbacks->get_helpful_by_feedback($feedbacks_written_keys, $this->session->id);
                }

                $display = $this->view->fetch('new/users_feedbacks/item_view', $data);

            break;
        }

        $resp = [
            'count' => $count,
            'html'  => $display,
        ];
        echo json_encode($resp);
    }

    public function photo()
    {
        checkDomainForGroup();
        checkIsLogged();
        checkGroupExpire();
        if (check_group_type(['Admin', 'EP_Staff'])) {
            session()->setMessages('Error: You have no rights to access this action.', 'errors');
            headerRedirect(__SITE_URL . 'admin');
        }

        //region User
        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        $user_id = (int) id_session();
        $user = $usersRepository->getUser($user_id);
        // $user_name = trim("{$user['fname']} {$user['lname']}");
        //endregion User

        //region Photos
        /** @var User_photo_Model */
        $photosRepository = model(User_photo_Model::class);
        $photos = $photosRepository->get_photos(['id_user' => $user_id]);
        // $photos_all = $photosRepository->get_photos(array('id_user' => $user_id));
        // $photos = array();

        // foreach($photos_all as $photos_item) {
        //  $photos_item['thumb_photo'] = unserialize($photos_item['thumb_photo']);

        //  if($photos_item['name_photo'] != $user['user_photo']){
        //      $photos[] = $photos_item;
        //  }
        // }

        $main_photo = $user['user_photo'];
        $total_photos = !empty($photos) ? count($photos) : 0;
        //endregion Photos

        //region Fileupload options
        $mime_main_properties = getMimePropertiesFromFormats(config('img.users.main.rules.format'));
        $mime_properties = getMimePropertiesFromFormats(config('img.users.photos.rules.format'));

        $fileupload_options = [
            'limits'    => [
                'amount'            => [
                    'total'   => (int) config('img.users.photos.limit'),
                    'current' => (int) $total_photos,
                ],
                'accept'            => arrayGet($mime_properties, 'accept'),
                'formats'           => arrayGet($mime_properties, 'formats'),
                'mimetypes'         => arrayGet($mime_properties, 'mimetypes'),
            ],
            'rules'             => config('img.users.photos.rules'),
            'url'               => [
                'upload' => getUrlForGroup('user/ajax_user_upload_photo'),
                'delete' => getUrlForGroup('user/ajax_user_delete_db_photo'),
            ],
        ];

        $fileupload_main_photo_options = [
            'link_main_image'          => getDisplayImageLink(['{ID}' => $user_id, '{FILE_NAME}' => $main_photo]),
            'link_thumb_main_image'    => getDisplayImageLink(['{ID}' => $user_id, '{FILE_NAME}' => $main_photo], 'users.main', ['thumb_size' => 1, 'no_image_group' => group_session()]),
            'title_text_popup'         => 'Profile picture',
            'btn_text_save_picture'    => 'Save picture',
            'image_circle_preview'     => true,
            'rules'                    => config('img.users.main.rules'),
            'url'                      => [
                'upload' => __CURRENT_SUB_DOMAIN_URL . 'user/ajax_user_upload_logo',
            ],
            'accept' => arrayGet($mime_main_properties, 'accept'),
        ];
        //endregion Fileupload options

        //region Views vars
        $data = [
            'user'                     => $user,
            'isEditProfilePicture'     => true,
            'photos'                   => $photos,
            'breadcrumbs'              => $this->breadcrumbs,
            'fileupload'               => $fileupload_options,
            'fileupload_crop'          => $fileupload_main_photo_options,
            'total_uploaded'           => arrayGet($fileupload_options, 'limits.amount.current'), // @deprecated
            'max_upload_limit'         => arrayGet($fileupload_options, 'limits.amount.total'), // @deprecated
            'fileupload_max_file_size' => arrayGet($fileupload_options, 'rules.size'), // @deprecated
        ];
        //endregion Views vars

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->photoEpl($data);
        } else {
            $this->photoAll($data);
        }
    }

    public function change_email_pass()
    {
        checkDomainForGroup();
        checkPermision('manage_personal_info');

        checkGroupExpire();

        $this->load->model('Category_Model', 'category');

        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        $id_user = id_session();
        $data['user'] = $usersRepository->getUser($id_user);
        $data['notification_email_change'] = $usersRepository->get_user_info_change_by_user($id_user, 'email');
        $data['notification_password_change'] = $usersRepository->get_user_info_change_by_user($id_user, 'password');

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->changeEmailPassEpl($data);
        } else {
            $this->changeEmailPassAll($data);
        }
    }

    public function email_delivery_settings()
    {
        checkPermision('manage_personal_info');
        checkDomainForGroup();
        checkGroupExpire();

        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        /** @var Users_systmess_settings_Model */
        $userMessageSettignsRepository = model(Users_systmess_settings_Model::class);
        /** @var Ep_Modules_Model */
        $epModulesRepository = model(Ep_Modules_Model::class);
        $id_user = (int) id_session();
        $notification_email_change = $usersRepository->get_user_info_change_by_user($id_user, 'notification_email');

        $data = [
            'title'                     => 'Account Preferences',
            'notification_email_change' => $notification_email_change,
            'user'                      => $usersRepository->getUser($id_user),
            'systmess_settings'         => arrayByKey($userMessageSettignsRepository->get_settings($id_user), 'module'),
            'modules'                   => $epModulesRepository->get_ep_modules([
                'email_notification' => 1,
                'by_rights'          => session()->rights,
                'group_by_module'    => true,
                'order_by'           => 'm.name_module',
            ]),
        ];

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->emailDeliverySettingsEpl($data);
        } else {
            $this->emailDeliverySettingsAll($data);
        }
    }

    public function preferences()
    {
        checkPermision('manage_personal_info');
        checkDomainForGroup();
        checkGroupExpire();

        /** @var UserProfileEditViewRenderer */
        $pageRenderer = $this->getContainer()->get(UserProfileEditViewRenderer::class);
        if (userGroupType() === GroupType::BUYER() || userGroupType() === GroupType::SELLER()) {
            /** @var AccountProvider  */
            $accountProvider = $this->getContainer()->get(AccountProvider::class);

            return $pageRenderer->renderForBuyerOrSeller(
                $this->getContainer()->get(AccountProvider::class),
                $userId = (int) id_session(),
                userGroupType() === GroupType::BUYER(),
                $accountProvider->getRelatedAccounts($userId, (int) principal_id())
            );
        }

        return $pageRenderer->renderBasePage(
            $this->getContainer()->get(PhoneCodesService::class),
            model(User_Model::class),
            model(Category_Model::class),
            model(Country_Model::class),
            model(Complete_Profile_Model::class),
            (int) id_session()
        );
    }

    public function additional_fields()
    {
        checkIsLogged();
        checkGroupExpire();
        checkPermision('manage_additional_personal_information,moderate_content');
        if (user_type('users_staff')) {
            headerRedirect(__SITE_URL);
        }

        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        $id_user = id_session();

        $this->load->model('UserGroup_Model', 'groups');

        $data['user'] = $usersRepository->getUser($id_user);

        if (!have_right('manage_content')) {
            $fields = $this->groups->getFiledsByGroup($data['user']['user_group'], "'simple' ,'social'");
            $additional_fields = $this->groups->getAditionalFiledsByUser($id_user, "'simple' ,'social'");
            $fields = array_merge($fields, $additional_fields);

            $data['simple_fields'] = [];
            $data['social_fields'] = [];

            foreach ($fields as $filed_item) {
                if (!isset($data['simple_fields'][$filed_item['id_right']]) && !isset($data['social_fields'][$filed_item['id_right']])) {
                    if ('social' == $filed_item['type']) {
                        $data['social_fields'][$filed_item['id_right']] = $filed_item;
                    } else {
                        $data['simple_fields'][$filed_item['id_right']] = $filed_item;
                    }
                }
            }

            $data['fields_values'] = $this->groups->getUsersRightFields($id_user);
        }

        $this->view->assign('title', 'Account additional fields');
        $this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/user/additional_fields_view');
        $this->view->display('new/footer_view');
    }

    public function ajax_preferences_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkGroupExpire('ajax');

        $this->_load_main();
        $idUser = id_session();

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'save_email':
                $idUser = id_session();
                /** @var User_Model user */
                $user = model(User_Model::class);

                //region Email notifications once day
                $idCheck = (int) (!empty($_POST['email_notifications_once_day']));
                $user->updateUserMain($idUser, ['notify_email' => $idCheck]);
                $this->session->notify_email = $idCheck;
                //endregion Email notifications once day

                $validator_rules = [
                    [
                        'field' => 'email',
                        'label' => 'Email field',
                        'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                //region Update email
                $userInfo = $user->getSimpleUser($idUser, 'users.email');
                $newEmail = cleanInput($_POST['email']);
                $messageResponce = [];

                if ($userInfo['email'] != $newEmail) {
                    $typeInfoChange = 'notification_email';
                    $changeNotifyEmailInfo = $user->get_user_info_change_by_user($idUser, $typeInfoChange);

                    if ($changeNotifyEmailInfo['email'] == $newEmail) {
                        $messageResponce[] = 'An email already has been sent to <strong>' . $newEmail . '</strong> to make sure it is a valid address.';
                    } else {
                        $user->delete_user_info_change($idUser, $typeInfoChange);

                        if ('dev' === config('env.APP_ENV')) {
                            $emailStatus = 'Bad';
                        } else {
                            $emailStatus = checkEmailDeliverability($newEmail);
                            if ('Bad' == $emailStatus) {
                                jsonResponse(translate('register_error_undeliverable_email', ['[USER_EMAIL]' => $newEmail]));
                            }
                        }

                        $updateEmail = [
                            'confirmation_code' => get_sha1_token($userInfo['email']),
                            'id_user'           => $idUser,
                            'email'             => $newEmail,
                            'email_status'      => $emailStatus ?? null,
                            'type'              => $typeInfoChange,
                        ];

                        $user->set_user_info_change($updateEmail);

                        try {
                            /** @var MailerInterface $mailer */
                            $mailer = $this->getContainer()->get(MailerInterface::class);
                            $mailer->send(
                                (new ChangeNotificationEmail(session()->fname . ' ' . session()->lname, $updateEmail['confirmation_code']))
                                    ->to(new RefAddress((string) $idUser, new Address($newEmail)))
                            );
                        } catch (\Throwable $th) {
                            jsonResponse(translate('email_has_not_been_sent'));
                        }

                        $messageResponce[] = 'An email has been sent to <strong>' . $newEmail . '</strong> to make sure it is a valid address.';
                    }
                }
                //endregion Update email

                // Send event about email update
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedEmailEvent((int) $idUser));

                $messageResponce[] = 'The information has been successfully changed.';
                jsonResponse(implode('<br>', $messageResponce), 'success');

            break;
            case 'save_right_field':
                /** @var UserGroup_Model $userGroupsModel */
                $userGroupsModel = model(UserGroup_Model::class);

                $fields = $userGroupsModel->getFiledsByGroup(group_session(), "'simple' ,'social'");
                $additionalFields = $userGroupsModel->getAditionalFiledsByUser($idUser, "'simple' ,'social'");
                $fields = array_merge($fields, $additionalFields);
                $fields = arrayByKey($fields, 'id_right');

                $userGroupsModel->deleteUserRightsFields($idUser);
                $insert = $rules = [];
                $postFields = request()->request->all();
                if (!empty($postFields)) {
                    foreach ($postFields as $key => $value) {
                        $keyArray = explode('_', $key);
                        $id = $keyArray[1];
                        $rules[] = [
                            'field' => "{$key}",
                            'label' => $fields[$id]['name_field'],
                            'rules' => array_fill_keys(explode(',', $fields[$id]['valid_rule']), ''),
                        ];

                        if (array_key_exists($id, $fields)) {
                            $insertValue = cleanInput(trim($value));
                            if ('' != $insertValue) {
                                $insert[$id] = $insertValue;
                            }
                        }
                    }
                }

                //region Validation
                $validator = new AdditionalSocialFieldsValidator(new ValidatorAdapter(library(TinyMVC_Library_validator::class)), $rules);
                if (!$validator->validate(request()->request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) { return $violation->getMessage(); },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }
                //endregion Validation

                if (!empty($insert)) {
                    $userGroupsModel->setUsersRightFields($idUser, $insert);

                    // Sene event about rights update
                    $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedRightsEvent((int) $idUser));
                }

                jsonResponse('Updated successfully', 'success');

            break;
            case 'change_email':
                $validator_rules = [
                    [
                        'field' => 'email_new',
                        'label' => 'Email New field',
                        'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
                    ],
                    [
                        'field' => 'pwd_current',
                        'label' => 'Password Current field',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }
                $emailNew = cleanInput($_POST['email_new'], true);
                $password_current = $_POST['pwd_current'];
                $type_info_change = 'email';

                /** @var Auth_Model */
                $authRepository = model(Auth_Model::class);
                $user_hash = $authRepository->get_hash_by_id_principal(session()->__get('id_principal'));

                if (!$user_hash) {
                    jsonResponse('No such user!');
                }

                if (!checkPassword($password_current, $user_hash['token_password'])) {
                    jsonResponse(translate('systmess_error_password_is_incorrect'));
                }

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $change_email_info = $usersRepository->get_user_info_change_by_user($idUser, $type_info_change);

                if (!empty($change_email_info)) {
                    if ($change_email_info['email'] == $emailNew) {
                        jsonResponse('An email already has been sent to <strong>' . $emailNew . '</strong> to make sure it is a valid address.');
                    } else {
                        $usersRepository->delete_user_info_change($idUser, $type_info_change);
                    }
                }

                $new_hash_email = getEncryptedEmail($emailNew);
                if ($new_hash_email == $user_hash['token_email']) {
                    jsonResponse('The current email and new email is same.');
                }

                if ($authRepository->exists_hash($new_hash_email)) {
                    jsonResponse('The email you entered already exists in our database. Please try another one.');
                }

                if ('dev' === config('env.APP_ENV')) {
                    $email_status = 'Bad';
                } else {
                    $email_status = checkEmailDeliverability($emailNew);
                    if ('Bad' == $email_status) {
                        jsonResponse(translate('register_error_undeliverable_email', ['[USER_EMAIL]' => $emailNew]));
                    }
                }

                $update = [
                    'confirmation_code' => get_sha1_token($emailNew),
                    'id_user'           => $idUser,
                    'email'             => $emailNew,
                    'email_status'      => $email_status ?? null,
                    'type'              => 'email',
                ];

                $usersRepository->set_user_info_change($update);

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ChangeEmail(session()->fname . ' ' . session()->lname, $update['confirmation_code']))
                            ->to(new RefAddress((string) $idUser, new Address($emailNew)))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }
                // Send event about email update
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedEmailEvent((int) $idUser));

                jsonResponse('An email has been sent to <strong>' . $emailNew . '</strong> to make sure it is a valid address.', 'success');

            break;
            case 'change_password':
                $validator_rules = [
                    [
                        'field' => 'pwd_current',
                        'label' => 'Password',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'pwd_new',
                        'label' => 'New password',
                        'rules' => ['required' => '', 'valid_password' => ''],
                    ],
                    [
                        'field' => 'pwd_new_confirm',
                        'label' => 'New confirm password',
                        'rules' => ['required' => '', 'matches[pwd_new]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $password_current = $_POST['pwd_current'];
                $password_new = $_POST['pwd_new'];
                /** @var Auth_Model */
                $authRepository = model(Auth_Model::class);
                $user_hash = $authRepository->get_hash_by_id_principal(session()->__get('id_principal'));

                if (!$user_hash) {
                    jsonResponse('No such user!');
                }

                if (!checkPassword($password_current, $user_hash['token_password'])) {
                    jsonResponse('The current password you entered is incorrect.');
                }

                if (checkPassword($password_new, $user_hash['token_password'])) {
                    jsonResponse('The old password is the same as the new one!');
                }

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $user = $usersRepository->getUser($idUser);
                $type_info_change = 'password';
                $change_password_info = $usersRepository->get_user_info_change_by_user($idUser, $type_info_change);

                $users = $usersRepository->get_simple_users_by_id_principal($user['id_principal']);
                $emails = array_unique(array_column($users, 'email', 'idu'));
                $emails_list = '<strong>' . implode('</strong>, <strong>', $emails) . '</strong>';

                if (checkPassword($password_new, $change_password_info['password'])) {
                    jsonResponse('An email has already been sent to <strong>' . $emails_list . '</strong> to confirm your new password.');
                } else {
                    $usersRepository->delete_user_info_change($idUser, $type_info_change);
                }

                $update = [
                    'confirmation_code' => get_sha1_token($user['email']),
                    'password'          => getEncryptedPassword($password_new),
                    'id_user'           => $idUser,
                    'email'             => $user['email'],
                    'type'              => 'password',
                ];
                $usersRepository->set_user_info_change($update);

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ChangePassword(session()->fname . ' ' . session()->lname, $update['confirmation_code']))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($users, 'idu', 'idu'), array_column($users, 'email', 'email')))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse('An email has been sent to <strong>' . $emails_list . '</strong> to confirm your new password.', 'success');

            break;
        }
    }

    public function ajax_user_upload_logo()
    {
        checkIsAjax();
        checkIsLogged();
        checkGroupExpire('ajax');
        $files = arrayGet($_FILES, 'files');
        if (null === $files) {
            jsonResponse('Error: Please select file to upload.');
        }

        if (is_array($files['name'])) {
            jsonResponse('Invalid file provided.');
        }

        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        $user_id = id_session();
        $user_info = $usersRepository->getSimpleUser($user_id);

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicDiskPrefixer = $storageProvider->prefixer('public.storage');

        // remove files
        /** @var LoggerInterface */
        $logger = $this->getContainer()->get('logger.filesystem');

        if (!empty($user_info['user_photo'])) {
            try {
                $publicDisk->delete($imagePath = UserFilePathGenerator::imagesUploadPath($user_id) . $user_info['user_photo']);
            } catch (UnableToDeleteFile $e) {
                $logger->error("Failed to delete directory due to error: {$e->getMessage()}", ['directory' => $imagePath]);
            }

            try {
                foreach ($publicDisk->listContents(UserFilePathGenerator::imagesUploadPath($user_id)) as $image) {
                    $publicDisk->delete($imagePath = $image->path());
                }
            } catch (UnableToDeleteFile $e) {
                $logger->error("Failed to delete directory due to error: {$e->getMessage()}", ['directory' => $imagePath ?? null]);
            }
        }

        $ImagePath = UserFilePathGenerator::imagesUploadPath($user_id);
        $path = $publicDiskPrefixer->prefixPath($ImagePath);

        $publicDisk->createDirectory($ImagePath);

        $config = [
            'destination'   => $path,
            'rules'         => config('img.users.main.rules'),
            'handlers'      => [
                'create_thumbs' => config('img.users.main.thumbs'),
                'resize'        => config('img.users.main.resize'),
            ],
        ];

        if ('Seller' == user_group_type()) {
            $config['handlers']['watermark'] = config('img.users.main.watermark');
        }

        $copy_result = library(TinyMVC_Library_Image_intervention::class)->image_processing($files, $config);

        if (!empty($copy_result['errors'])) {
            jsonResponse($copy_result['errors']);
        }

        $insert_photo = [];
        foreach ($copy_result as $item) {
            $insert_photo = ['user_photo' => $item['new_name']];
        }

        if (empty($insert_photo)) {
            jsonResponse('Error: You not select any pictures.');
        }

        $insert_photo['user_photo_with_badge'] = 0;
        session()->__set('user_photo_with_badge', 0);

        if (!$usersRepository->updateUserMain($user_id, $insert_photo)) {
            jsonResponse('Failed to upload image(s)');
        }

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->sync((int) $user_id);

        $this->update_user_questions_in_elastic($user_id);
        $this->session->__set('user_photo', $insert_photo['user_photo']);
        $filePath = $publicDiskPrefixer->prefixPath(UserFilePathGenerator::imagesUploadFilePath($user_id, $insert_photo['user_photo']));
        $image = [
            'file_path' => $filePath,
            'type'      => 'user_main_photo',
            'context'   => ['id_user' => $user_id],
        ];

        /** @var Image_optimization_Model */
        $optimizationReposiotory = model(Image_optimization_Model::class);
        $optimizationReposiotory->add_record($image);

        $accounts = session()->__get('accounts');
        if (!empty($accounts)) {
            $accounts = arrayByKey($accounts, 'idu');
            $accounts[$user_id]['user_photo'] = $insert_photo['user_photo'];
            session()->__set('accounts', $accounts);
        }
        // Send event about logo update
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedLogoEvent((int) $user_id));

        jsonResponse('', 'success', [
            'path'  => $publicDisk->url(UserFilePathGenerator::imagesUploadFilePath($user_id, $insert_photo['user_photo'])),
            'thumb' => $publicDisk->url(UserFilePathGenerator::imagesThumbUploadFilePath($user_id, $insert_photo['user_photo'])),
        ]);
    }

    public function ajax_user_upload_photo()
    {
        checkIsAjax();
        checkIsLogged();
        checkGroupExpire('ajax');
        checkPermisionAjax('manage_personal_pictures');

        ini_set('max_execution_time', 0);
        ini_set('request_terminate_timeout', 0);

        $files = arrayGet($_FILES, 'files');
        if (null === $files) {
            jsonResponse('Error: Please select file to upload.');
        }

        if (is_array($files['name'])) {
            jsonResponse('Invalid file provided.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicDiskPrefixer = $storageProvider->prefixer('public.storage');

        $user_id = id_session();
        $path = UserFilePathGenerator::imagesUploadPath($user_id);
        $publicDisk->createDirectory($path);

        /** @var User_photo_Model */
        $photosRepository = model(User_photo_Model::class);
        $photo_names = [];
        $count_photo = (int) $photosRepository->count_photos(['id_user' => $user_id]);
        $disponible = (int) config('img.users.photos.limit') - $count_photo;
        if ($disponible <= 0) {
            jsonResponse('Error: You cannot upload more than ' . ($disponible + $count_photo) . ' photo(s).');
        }

        $copy_result = library(TinyMVC_Library_Image_intervention::class)->image_processing($files, [
            'destination'   => $publicDiskPrefixer->prefixPath($path),
            'rules'         => config('img.users.photos.rules'),
            'handlers'      => [
                'create_thumbs' => config('img.users.photos.thumbs'),
                'resize'        => config('img.users.photos.resize'),
            ],
        ]);

        if (!empty($copy_result['errors'])) {
            jsonResponse($copy_result['errors']);
        }

        $insert_photo = $images_to_optimization = [];
        foreach ($copy_result as $item) {
            $photo_names[] = $item['new_name'];
            $insert_photo[] = [
                'name_photo'     => $item['new_name'],
                'id_user'        => $user_id,
                'type_photo'     => $item['image_type'],
            ];

            $images_to_optimization[] = [
                'file_path' => $publicDiskPrefixer->prefixPath(UserFilePathGenerator::imagesUploadFilePath($user_id, $item['new_name'])),
                'context'   => ['id_user' => $user_id],
                'type'      => 'user_photos',
            ];
        }

        if (empty($insert_photo)) {
            jsonResponse('Error: You not select any pictures.');
        }

        if (!$photosRepository->set_multi_photo($insert_photo)) {
            jsonResponse('Failed to upload image(s)');
        }

        /** @var User_Statistic_Model */
        $statisticRepository = model(User_Statistic_Model::class);
        $statisticRepository->set_users_statistic([
            $user_id => [
                'user_photo' => count($insert_photo),
            ],
        ]);

        $files = array_map(
            function ($file) use ($user_id) {
                $file['path'] = getDisplayImageLink(['{ID}' => $user_id, '{FILE_NAME}' => $file['name_photo']], 'users.photos');
                $file['thumb'] = getDisplayImageLink(['{ID}' => $user_id, '{FILE_NAME}' => $file['name_photo']], 'users.photos', ['thumb_size' => 2]);

                return $file;
            },
            $photosRepository->get_photos(['id_user' => $user_id, 'photo_names' => implode('","', $photo_names)])
        );

        if (!empty($images_to_optimization)) {
            /** @var Image_optimization_Model */
            $optimizationReposiotory = model(Image_optimization_Model::class);
            $optimizationReposiotory->add_records($images_to_optimization);
        }
        // Send event about photo update
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedPhotoEvent((int) $user_id));

        jsonResponse('', 'success', ['files' => $files]);
    }

    public function ajax_user_delete_db_photo()
    {
        checkIsAjax();
        checkIsLogged();
        checkGroupExpire('ajax');

        if (empty($_POST['file'])) {
            jsonResponse('Error: File name is not correct.');
        }

        /** @var User_photo_Model */
        $photosRepository = model(User_photo_Model::class);
        $user_id = id_session();
        $photo_id = (int) $_POST['file'];
        if (
            empty($photo_id)
            || empty($photo = $photosRepository->get_photo(['id_photo' => $photo_id, 'id_user' => $user_id]))
        ) {
            jsonResponse('Error: Photo not exist.');
        }

        if (!$photosRepository->delete_photo($photo['id_photo'])) {
            jsonResponse('Error: This image can\'t be deleted.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $image_path_info = pathinfo($photo['name_photo']);

        if ($publicDisk->fileExists(UserFilePathGenerator::imagesUploadPath($user_id).$photo['name_photo'])) {
            try {
                $publicDisk->delete(UserFilePathGenerator::imagesUploadPath($user_id).$photo['name_photo']);
            } catch (\Throwable $th) {
                jsonResponse(translate('validation_images_delete_fail'));
            }
        }

        if (!empty($imageThumbs = config("img.users.photos.thumbs"))) {

            foreach ($imageThumbs as $imageThumb) {
                $thumbName = str_replace('{THUMB_NAME}', $image_path_info['filename'], $imageThumb['name']);

                try {
                    $publicDisk->delete(UserFilePathGenerator::imagesUploadFilePath($user_id, $thumbName) . '.jpg');
                } catch (\Throwable $th) {
                    jsonResponse(translate('validation_images_delete_fail'));
                }

            }
        }

        /** @var User_Statistic_Model */
        $statisticRepository = model(User_Statistic_Model::class);
        $statisticRepository->set_users_statistic([
            $user_id => [
                'user_photo' => -1,
            ],
        ]);
        // Send event about photo update
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedPhotoEvent((int) $user_id));

        jsonResponse('Image was deleted.', 'success');
    }

    public function ajax_user_operation()
    {
        checkIsAjax();

        $op = $this->uri->segment(3);
        if (empty($op)) {
            jsonResponse('Error: you cannot perform this action. Please try again later.');
        }

        $this->_load_main();
        switch ($op) {
            case 'share_statistic':
                $request = request()->request;
                $sourceType = $request->get('type');

                if (!in_array($sourceType, ['item', 'company', 'ep_event'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $typeSharing = $request->get('typeSharing');

                if (!in_array($typeSharing, ['facebook', 'twitter', 'linkedin', 'pinterest'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (empty($sourceId = (int) $request->get('id'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                switch ($sourceType) {
                    case 'item':
                        /** @var Items_Model $itemsRepository */
                        $itemsRepository = model(Items_Model::class);

                        if (empty($itemsRepository->get_item_simple($sourceId))) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        break;

                    case 'company':
                        /** @var Company_Model $companyRepository */
                        $companyRepository = model(Company_Model::class);

                        if (empty($companyRepository->get_company(['id_company' => $sourceId, 'type_company' => 'all']))) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        break;
                    case 'ep_event':
                        /** @var Elasticsearch_Ep_Events_Model $elasticsearchEventsModel */
                        $elasticsearchEventsModel = model(Elasticsearch_Ep_Events_Model::class);

                        $event = array_shift($elasticsearchEventsModel->getEvents(['id' => $sourceId]));
                        if (empty($event)) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        break;
                }

                $data = [
                    'type'          => $sourceType,
                    'type_sharing'  => $typeSharing,
                    'id_item'       => $sourceId,
                    'id_user'       => logged_in() ? id_session() : 0,
                ];

                /** @var Share_Statistic_Model $shareStatisticRepository */
                $shareStatisticRepository = model(Share_Statistic_Model::class);
                $shareStatisticRepository->add($data);
                jsonResponse('', 'success');

                break;
            case 'save_menu':
                checkIsLoggedAjax();

                $menu = $_POST['menu'];
                $menu = json_decode($menu, true);
                $menu_for_db = [];
                $menu_for_session = [];
                $menu_rights = $this->session->menu_full;

                foreach ($menu as $menu_item) {
                    $menu_rights_select = $menu_rights[$menu_item['tab']]['items'][$menu_item['name']];

                    if (empty($menu_rights_select)) {
                        continue;
                    }

                    if (!empty($menu_rights_select['right']) && !have_right_or($menu_rights_select['right'])) {
                        continue;
                    }

                    // switch ($menu_item['name']) {
                    //     case 'company_page':
                    //         $menu_rights_select['link'] = getMyCompanyURL(false);
                    //     break;
                    //     case 'company_info':
                    //         $menu_rights_select['link'] = "company/edit/".strForURL(my_company_name()).'-'.my_company_id();
                    //     break;
                    //     case 'ff_company_page':
                    //         $menu_rights_select['link'] = 'shipper/'.strForURL(my_shipper_company_name().' '.my_shipper_company_id());
                    //     break;
                    //     case 'my_page':
                    //     case 'cr_my_page':
                    //         $menu_rights_select['link'] = getMyProfileLink();
                    //     break;
                    //     default:
                    //         $menu_rights_select['link'] = getUrlForGroup($menu_rights_select['link']);
                    //     break;
                    // }

                    $menu_for_session[] = [
                        'col'           => $menu_item['col'],
                        'cell'          => $menu_item['cell'],
                        'tab'           => $menu_item['tab'],
                        'name'          => $menu_item['name'],
                        'title'         => $menu_rights_select['title'],
                        'popup'         => $menu_rights_select['popup'],
                        'popup_width'   => $menu_rights_select['popup_width'],
                        'link'          => $menu_rights_select['link'],
                        'external_link' => $menu_rights_select['external_link'] ?? null,
                        'target'        => $menu_rights_select['target'] ?? null,
                        'icon'          => $menu_rights_select['icon'],
                        'icon_color'    => $menu_rights_select['icon_color'],
                    ];

                    $menu_for_db[] = [
                        'col'  => $menu_item['col'],
                        'cell' => $menu_item['cell'],
                        'tab'  => $menu_item['tab'],
                        'name' => $menu_item['name'],
                    ];
                }

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                if ($usersRepository->updateUserMain(id_session(), ['menu' => !empty($menu_for_db) ? json_encode($menu_for_db) : null])) {
                    if (empty($menu_for_db)) {
                        $user = $usersRepository->getUser(id_session());

                        $this->load->library('Auth', 'auth');
                        $this->auth->user = $user;
                        if ('ep_staff' == $user['user_type']) {
                            $this->auth->_get_admin_menu(); // GET admin MENU
                        } else {
                            $this->auth->_get_user_menu(); // GET USER MENU
                        }
                    } else {
                        $this->session->menu = json_encode($menu_for_session, JSON_HEX_APOS);
                    }

                    jsonResponse('Your changes were saved successfully.', 'success');
                } else {
                    jsonResponse('Error: Your changes do not saved.');
                }

            break;
            case 'check_new':
                if (!logged_in()) {
                    jsonResponse(translate('systmess_error_should_be_logged'));
                }

                $lastId = intval($_POST['lastId']);
                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $users_count = $usersRepository->get_count_new_users($lastId);
                if (!empty($users_count)) {
                    $last_users_id = $usersRepository->get_users_last_id();
                    jsonResponse('', 'success', ['nr_new' => $users_count, 'lastId' => $last_users_id]);
                } else {
                    jsonResponse('Error: New users do not exist');
                }

            break;
            case 'change_status':
                if (!logged_in()) {
                    jsonResponse(translate('systmess_error_should_be_logged'));
                }

                $validator_rules = [
                    [
                        'field' => 'text',
                        'label' => 'Status text',
                        'rules' => ['max_len[500]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $status_user = cleanInput($_POST['text']);
                $date_user = date('Y-m-d H:i:s');

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                if ($usersRepository->updateUserMain(id_session(), ['showed_status' => $status_user, 'showed_status_date' => $date_user])) {
                    $resp = [
                        'text' => cleanInput($_POST['text']),
                        'date' => '',
                    ];

                    if (!empty($status_user)) {
                        $resp['date'] = formatDate($date_user, 'F j, Y h:iA');
                    }

                    jsonResponse('', 'success', $resp);
                } else {
                    jsonResponse('Error: you cannot update your status now. Please try again later.');
                }

            break;
            case 'update_ep_staff':
                checkPermisionAjax('edit_ep_staff');

                $validator_rules = [
                    [
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
                    ],
                    [
                        'field' => 'fname',
                        'label' => 'First Name',
                        'rules' => ['required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => ''],
                    ],
                    [
                        'field' => 'lname',
                        'label' => 'Last Name',
                        'rules' => ['required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => ''],
                    ],
                    [
                        'field' => 'group',
                        'label' => 'GRoup',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $id_user = intval($_POST['idu']);
                $user = $usersRepository->getUser($id_user);

                if (empty($user)) {
                    jsonResponse(translate('systmess_error_user_does_not_exist'));
                }

                $email = cleanInput($_POST['email'], true);
                if ($email != $user['email'] && $usersRepository->exist_user_by_email($email)) {
                    jsonResponse('Error: The email already exists in the database. Please choose another one!');
                }

                $languages = arrayByKey($this->translations->get_languages(), 'id_lang');
                $update_main = [
                    'email'      => $email,
                    'fname'      => cleanInput($_POST['fname']),
                    'lname'      => cleanInput($_POST['lname']),
                    'user_group' => intval($_POST['group']),
                ];

                $user_langs = array_filter(
                    array_map(
                        function ($item) use ($languages) {
                            $item = (int) $item;
                            if (!isset($languages[$item])) {
                                return null;
                            }

                            return $item;
                        },
                        !empty($_POST['lang']) ? $_POST['lang'] : []
                    )
                );

                if ($usersRepository->updateUserMain($id_user, $update_main)) {
                    $usersRepository->replace_user_lang_restriction($id_user, $user_langs);
                    jsonResponse('User\'s info updated successfully', 'success');
                } else {
                    jsonResponse('Error: you cannot save the user\s info now. Please try again later');
                }

            break;
            case 'add_ep_staff':
                checkPermisionAjax('add_ep_staff');

                $validator_rules = [
                    [
                        'field' => 'fname',
                        'label' => 'First Name',
                        'rules' => ['required' => '', 'valid_user_name' => ''],
                    ], [
                        'field' => 'lname',
                        'label' => 'Last Name',
                        'rules' => ['required' => '', 'valid_user_name' => ''],
                    ], [
                        'field' => 'group',
                        'label' => 'GRoup',
                        'rules' => ['required' => ''],
                    ], [
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
                    ],
                    [
                        'field' => 'pwd',
                        'label' => 'Password',
                        'rules' => ['required' => '', 'valid_password' => ''],
                    ],
                    [
                        'field' => 'pwd_confirm',
                        'label' => 'Retype password',
                        'rules' => ['required' => '', 'matches[pwd]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }
                $this->load->model('Auth_model', 'auth_hash');
                $encrypted_email = getEncryptedEmail(cleanInput($_POST['email'], true));

                if ($this->auth_hash->exists_hash($encrypted_email)) {
                    jsonResponse('Error: The email already exists in the database. Please choose another one!');
                }

                $languages = arrayByKey($this->translations->get_languages(), 'id_lang');
                $data = [
                    'fname'             => cleanInput($_POST['fname']),
                    'lname'             => cleanInput($_POST['lname']),
                    'email'             => cleanInput($_POST['email'], true),
                    'user_group'        => intval($_POST['group']),
                    'user_ip'           => getVisitorIP(),
                    'registration_date' => date('Y-m-d H:i:s'),
                    'activation_code'   => get_sha1_token(cleanInput($_POST['email'], true)),
                    'status'            => 'active',
                    'user_type'         => 'ep_staff',
                    'email_confirmed'   => 1
                ];

                $user_langs = array_filter(
                    array_map(
                        function ($item) use ($languages) {
                            $item = (int) $item;
                            if (!isset($languages[$item])) {
                                return null;
                            }

                            return $item;
                        },
                        !empty($_POST['lang']) ? $_POST['lang'] : []
                    )
                );
                //region add hash
                /** @var Principals_Model */
                $principalsRepository = model(Principals_Model::class);
                $hash_insert = [
                    'token_email'    => $encrypted_email,
                    'token_password' => getEncryptedPassword($_POST['pwd']),
                ];
                $data['id_principal'] = $principalsRepository->insert_last_id();
                $this->auth_hash->add_hash($data['id_principal'], $hash_insert);
                //endregion add hash

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $user_id = $usersRepository->setUserMain($data);
                if ($user_id) {
                    if (!empty($user_langs)) {
                        $usersRepository->create_user_lang_restriction($user_id, $user_langs);
                    }

                    jsonResponse('EP staff was added successfully', 'success');
                } else {
                    jsonResponse('Error: you cannot add EP staff. Please try again later');
                }

            break;
            case 'unlock_right':
                if (!logged_in()) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                $this->load->model('UserGroup_Model', 'groups');
                $id_user = intval($_POST['id']);
                $id_right = intval($_POST['right']);
                $user_right = $this->groups->get_user_right($id_user, $id_right);

                if (empty($user_right)) {
                    jsonResponse('Error: This right doesn\'t exist');
                }

                $right_val = $user_right['value_field'];
                if (empty($right_val)) {
                    jsonResponse('Error: This right doesn\'t exist');
                }

                $links_connect = [
                    'YouTube'  => 'YouTube link',
                    'Twitter'  => 'Twitter link',
                    'Facebook' => 'Facebook link',
                    'LinkedIn' => 'LinkedIn link',
                ];
                $right_name = $user_right['r_name'];

                if (isset($links_connect[$right_name])) {
                    $right_val = '<a href="' . $right_val . '" target="_blank">' . $links_connect[$right_name] . '</a>';
                }

                if ('Website' == $right_name) {
                    $right_val = '<a href="' . $right_val . '" target="_blank">Website link</a>';
                }

                jsonResponse('', 'success', ['block_info' => $right_val]);

            break;
            case 'unlock_email':
                if (!logged_in()) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $user = $usersRepository->getUser((int) $_POST['id']);
                if (!empty($user)) {
                    jsonResponse('', 'success', ['block_info' => '<span>' . $user['email'] . '</span>']);
                } else {
                    jsonResponse(translate('systmess_error_user_does_not_exist'));
                }

            break;
            case 'unlock_phone':
                checkIsLoggedAjax();

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $user = $usersRepository->getUser((int) $_POST['id']);
                if (!empty($user)) {
                    jsonResponse(null, 'success', [
                        'block_info' => sprintf('<a href="tel:%s">%s</a>', $user_phone = trim("{$user['phone_code']} {$user['phone']}"), $user_phone),
                    ]);
                } else {
                    jsonResponse(translate('systmess_error_user_does_not_exist'));
                }

                break;
            case 'logged_in':
                $resp = false;

                if (logged_in()) {
                    $resp = true;
                }

                jsonResponse('', 'success', ['logged' => $resp]);

                break;
            case 'badge_image':
                checkIsLoggedAjax();

                if (!is_certified()) {
                    jsonResponse(translate('systmess_error_rights_perform_this_action'));
                }
                checkGroupExpire('ajax');

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $idUser = id_session();
                $user = $usersRepository->getUser($idUser);

                if (empty($user)) {
                    jsonResponse(translate('systmess_error_user_does_not_exist'));
                }

                if (empty($user['user_photo']) || !file_exists(getImagePath(['{ID}' => $idUser, '{FILE_NAME}' => $user['user_photo']]))) {
                    jsonResponse(translate('upload_picture_to_use_badge_systmess'));
                }

                $checked = request()->request->getInt('checked');
                $usersRepository->updateUserMain(id_session(), ['user_photo_with_badge' => $checked]);
                session()->__set('user_photo_with_badge', $checked);

                switchBadgeImages($idUser, $user['user_photo']);

                jsonResponse('', 'success');

            break;
            }
    }

    /*
    public function ajax_save_user(){
        if(!isAjaxRequest())
            headerRedirect();

        if(!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $user = (int)$_POST['user'];

        if(is_null($user))
            jsonResponse('Incorrect user information.');

        if(is_my($user))
            jsonResponse('You can\'t save yourself.');

        $this->load->model('User_Model', 'users');

        switch($_POST['op']){
            case 'save':
                if(in_array($user, $this->session->user_saved)){
                    $resp = array(
                        'mess_type' => 'error',
                        'message' => "Error: You have already saved this user."
                    );

                    echo json_encode($resp);
                    return false;
                }
                if($this->users->set_user_saved($this->session->id, $user)){
                    $this->session->__push('user_saved', $user);
                    $resp = array(
                        'mess_type' => 'success',
                        'message' => 'Seller saved successfully.'
                    );
                    echo json_encode($resp);
                } else {
                    $resp = array(
                        'mess_type' => 'error',
                        'message' => 'Error: You could not save this seller now. Please try again late.'
                    );
                    echo json_encode($resp);
                    return false;
                }
            break;
            case 'unsave':
                if(!in_array($user, $this->session->user_saved)){
                    $resp = array(
                        'mess_type' => 'error',
                        'message' => "Error: You have not saved this user before."
                    );

                    echo json_encode($resp);
                    return false;
                }
                if($this->users->delete_user_saved($this->session->id, $user)){
                    $this->session->clear_val('user_saved', $user);

                    $resp = array(
                        'mess_type' => 'success',
                        'message' => 'The seller  was removed successfuly.'
                    );
                    echo json_encode($resp);
                } else {
                    $resp = array(
                        'mess_type' => 'error',
                        'message' => 'Error: You could not remove this seller from you contacts now. Please try again late.'
                    );
                    echo json_encode($resp);
                    return false;
                }
            break;
        }
    }
    */

    public function popup_forms()
    {
        checkIsAjax();

        $this->_load_main();

        switch (uri()->segment(3)) {
            case 'share':
                $request = request()->request;
                $sourceType = $request->get('type');

                if (!in_array($sourceType, ['item', 'company', 'ep_event'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (empty($sourceId = $request->getInt('itemId'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                switch ($sourceType) {
                    case 'item':
                        /** @var Items_Model */
                        $productsRepository = model(Items_Model::class);
                        $subTitleText = 'item';
                        $itemDetail = $productsRepository->get_item_simple($sourceId);

                        if (empty($itemDetail)) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $itemPhoto = $productsRepository->get_item_main_photo($sourceId);

                        $dataView = [
                            'itemId'        => $sourceId,
                            'type'          => $sourceType,
                            'shareUrl'      => __CURRENT_SUB_DOMAIN_URL . makeItemUrl($sourceId, $itemDetail['title'], false),
                            'shareTitle'    => cleanOutput($itemDetail['title']),
                            'sharePhoto'    => !empty($itemPhoto['photo_name']) ? getDisplayImageLink(['{ID}' => $sourceId, '{FILE_NAME}' => $itemPhoto['photo_name']], 'items.photos') : '',
                        ];

                        break;

                    case 'company':
                        /** @var Company_Model */
                        $companyRepository = model(Company_Model::class);
                        $subTitleText = 'page';
                        $itemDetail = $companyRepository->get_company(['id_company' => $sourceId, 'type_company' => 'all']);

                        if (empty($itemDetail)) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $dataView = [
                            'itemId'        => $sourceId,
                            'type'          => $sourceType,
                            'shareUrl'      => __CURRENT_SUB_DOMAIN_URL . getCompanyURL($itemDetail, false),
                            'shareTitle'    => cleanOutput($itemDetail['name_company']),
                            'sharePhoto'    => getDisplayImageLink(['{ID}' => $itemDetail['id_company'], '{FILE_NAME}' => $itemDetail['logo_company']], 'companies.main'),
                        ];

                        break;

                    case 'ep_event':
                        /** @var Elasticsearch_Ep_Events_Model $elasticsearchEventsModel */
                        $elasticsearchEventsModel = model(Elasticsearch_Ep_Events_Model::class);

                        $event = $elasticsearchEventsModel->getEvents(['id' => $sourceId]);
                        $event = array_shift($event);
                        if (empty($event)) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        /** @var FilesystemProviderInterface */
                        $storageProvider = container()->get(FilesystemProviderInterface::class);
                        $storage = $storageProvider->storage('public.storage');

                        $dataView = [
                            'itemId'        => $sourceId,
                            'type'          => $sourceType,
                            'shareUrl'      => getEpEventDetailUrl($event),
                            'shareTitle'    => cleanOutput($event['title']),
                            'sharePhoto'    => $storage->url(EpEventFilePathGenerator::mainImagePath((string) $event['id'], $event['main_image'])),
                        ];

                        break;
                }

                $data = [
                    'title'     => translate('modal_general_share_title'),
                    'subTitle'  => translate('modal_general_share_subtitle', ['[TEXT]' => $subTitleText]),
                    'content'   => views()->fetch('new/user/share/popup_share_view', $dataView),
                ];

                jsonResponse('', 'success', $data);

                break;
            case 'change_user_status':
                checkIsLoggedAjaxModal();

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $id_user = privileged_user_id();
                $data['user_info'] = $usersRepository->getSimpleUser($id_user);

                $this->view->assign($data);
                $this->view->display('new/user/simple_user/popup_change_status_view');

            break;
            case 'share_user':
                checkIsLoggedAjaxModal();

                $id = (int) $this->uri->segment(4);
                if (!$id) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $data['id_user'] = $id;

                $this->view->assign($data);
                $this->view->display('new/user/simple_user/popup_share_view');

            break;
            case 'email_user':
                checkIsLoggedAjaxModal();

                $id = (int) $this->uri->segment(4);
                if (!$id) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $data['id_user'] = $id;
                $this->view->assign($data);
                $this->view->display('new/user/simple_user/popup_email_view');

            break;
            case 'edit_ep_staff':
                checkPermisionAjaxModal('edit_ep_staff');
                if (empty($id = (int) uri()->segment(4))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $this->load->model('UserGroup_Model', 'groups');

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $user = $usersRepository->getUser($id);
                $groups = arrayByKey($this->groups->getGroupsByType(['type' => "'EP Staff','Admin'"]), 'idgroup');

                $data['user'] = $user;
                $data['groups'] = $groups;
                $data['languages'] = $this->translations->get_languages();
                $data['language_restriction'] = $usersRepository->get_user_lang_restriction($user['idu'], ['columns' => ['languages']]);
                $data['language_restriction'] = json_decode($data['language_restriction']['languages']);
                $data['show_languages'] = isset($groups[$user['user_group']]) && ((bool) (int) $groups[$user['user_group']]['gr_lang_restriction_enabled']);
                $this->view->display('admin/user/ep_staff_form_view', $data);

            break;
            case 'add_ep_staff':
                checkPermisionAjaxModal('add_ep_staff');
                if (empty($id = (int) uri()->segment(4))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $this->load->model('UserGroup_Model', 'groups');
                $data['groups'] = $this->groups->getGroupsByType(['type' => "'EP Staff','Admin'"]);
                $data['languages'] = $this->translations->get_languages();
                $data['language_restriction'] = [];
                $data['show_languages'] = false;
                $this->view->display('admin/user/ep_staff_form_view', $data);

            break;
        }
    }

    public function ajax_send_email()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        checkIsLoggedAjax();

        is_allowed('freq_allowed_send_email_to_user');

        $this->_load_main(); // load main models

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'email':
                $validator_rules = [
                    [
                        'field' => 'message',
                        'label' => translate('email_user_form_message_label'),
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                    [
                        'field' => 'email',
                        'label' => translate('email_user_form_email_label'),
                        'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_emails_count[' . config('email_this_max_email_count') . ']' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $filteredEmails = filter_email(cleanInput($_POST['email'], true));

                if (empty($filteredEmails)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $idUser = (int) $_POST['user'];
                if (!$idUser || empty($user = $usersRepository->getUser($idUser))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutUser($userName, cleanInput(request()->request->get('message')), $user))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('systmess_successfully_sent_email'), 'success');

            break;
            case 'share':
                checkPermisionAjax('share_users');

                $validator_rules = [
                    [
                        'field' => 'message',
                        'label' => translate('share_user_form_message_label'),
                        'rules' => ['required' => '', 'max_len[1000]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var User_Model */
                $usersRepository = model(User_Model::class);
                $idUser = (int) $_POST['user'];
                if (!$idUser || empty($user = $usersRepository->getUser($idUser))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Followers_Model */
                $followersRepository = model(Followers_Model::class);
                $filteredEmails = $followersRepository->getFollowersEmails(privileged_user_id());

                if (empty($filteredEmails)) {
                    jsonResponse(translate('systmess_error_share_user_no_followers'));
                }

                $filteredEmails = array_column($filteredEmails, 'idu', 'email');
                $userName = user_name_session();
                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutUser($userName, cleanInput(request()->request->get('message')), $user))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_values($filteredEmails), array_keys($filteredEmails)))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('systmess_successfully_sent_email'), 'success');

            break;
        }
    }

    public function ajax_user_unsubscribe()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $validator_rules = [
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
            ],
        ];

        $this->validator->set_rules($validator_rules);

        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $user_email = cleanInput(arrayGet($_POST, 'email'));
        $password = arrayGet($_POST, 'password');

        $this->load->model('Auth_model', 'auth_hash');
        $encrypted_email = getEncryptedEmail($user_email);
        if (!$this->auth_hash->exists_hash($encrypted_email)) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }

        $hashed = $this->auth_hash->get_hash($encrypted_email);

        if (!checkPassword($password, $hashed['token_password'], (bool) $hashed['is_legacy'], $user_email)) {
            jsonResponse(translate('systmess_error_password_is_incorrect'));
        }

        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        $users_info = $usersRepository->get_simple_users_by_id_principal($hashed['id_principal']);

        $erorrs_unsubscribed = 0;
        foreach ($users_info as $user_info) {
            if (!$user_info['notify_email'] && !$user_info['subscription_email']) {
                ++$erorrs_unsubscribed;
            } else {
                /** @var User_Popups_Model $userPopups */
                $userPopups = model(User_Popups_Model::class);

                $userPopups->deleteAllBy([
                    'conditions' => [
                        'idPopup' => 13,
                        'idUser'  => (int) $user_info['idu'],
                    ],
                ]);
            }
        }

        if ($erorrs_unsubscribed == count($users_info)) {
            jsonResponse(translate('systmess_info_you_have_already_unsubscribed'), 'info');
        }

        if ($usersRepository->unsubscribe_users($hashed['id_principal'])) {
            if (logged_in()) {
                $this->session->subscription_email = $this->session->notify_email = 0;
            }

            jsonResponse(translate('systmess_success_unsubscribe'), 'success');
        }
    }

    public function ajax_user_unsubscribe_sender()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!ajax_validate_google_recaptcha()) {
            jsonResponse(translate('systmess_error_you_didnt_pass_bot_check'));
        }

        $validator_rules = [
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
            ],
        ];

        $this->validator->set_rules($validator_rules);

        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $userEmail = cleanInput($_POST['email'], true) . PHP_EOL;
        /** @var Subscribe_Model $subscribeModel */
        $subscribeModel = model(Subscribe_Model::class);
        if (!$subscribeModel->check_unsubscriber_exists($userEmail)) {
            if (!$subscribeModel->unsubscribe_email($userEmail)) {
                jsonResponse(translate('systmess_internal_server_error'), 'error');
            } else {
                $subscriber = $subscribeModel->getSubscriberByEmail(str_replace("\r\n", '', $userEmail));

                if (!empty($subscriber)) {
                    $subscribeModel->deleteSubscriber($subscriber['subscriber_id']);

                    $idNotLogged = cookies()->getCookieParam('_ep_client_id');

                    if (!empty($idNotLogged)) {
                        /** @var User_Popups_Model $userPopups */
                        $userPopups = model(User_Popups_Model::class);

                        $userPopups->deleteAllBy([
                            'conditions' => [
                                'idPopup'      => 13,
                                'idNotLogged'  => $idNotLogged,
                            ],
                        ]);
                    }
                }

                jsonResponse(translate('systmess_success_unsubscribe'), 'success');
            }
        }

        jsonResponse(translate('systmess_info_email_isn_subscribed'), 'info');
	}

    public function unsubscribe_zohocrm()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!ajax_validate_google_recaptcha()) {
            jsonResponse(translate('systmess_error_you_didnt_pass_bot_check'));
        }

        $validatorRules = [
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
            ],
            [
                'field' => 'reason',
                'label' => 'Unsubscribe reason',
                'rules' => ['required' => ''],
            ],
        ];

        $reason = request()->get('reason');

        if (!empty($reason) && 'other' === $reason) {
            array_push($validatorRules, [
                'field' => 'message',
                'label' => 'Reason message',
                'rules' => ['required' => '', 'max_len[200]' => ''],
            ]);
        }

        $this->validator->set_rules($validatorRules);

        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $email = request()->get('email');

        httpPost('https://crm.zoho.com/crm/Unsubscribe?encoding=UTF-8', [
            'form_params' => [
                'xnQsjsdp'   => 'a2d0af2eff825a8c1158edacb7bf47f6a952ecdf02aa78b5d28db6120324644a',
                'actionType' => 'dW5zdWJzY3JpYmU=',
                'email'      => $email,
            ],
        ]);

        /** @var Subscribe_Model $subscribeModel */
        $subscribeModel = model(Subscribe_Model::class);
        $reasonMessages = $subscribeModel->getUnsubscribeReasons();
        $reasonMsg = '';

        if (empty($reasonMessages[$reason])) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        if ('other' === $reason) {
            $reasonMsg = cleanInput(request()->get('message'));
            $isCustomText = 1;
        } else {
            $reasonMsg = $reasonMessages[$reason];
        }

        $subscribeModel->insertSubscriberFeedback([
            'email'          => $email,
            'feedback_text'  => $reasonMsg,
            'is_custom_text' => $isCustomText ?: 0,
        ]);

        jsonResponse(translate('systmess_success_unsubscribe'), 'success');
    }

    public function unsubscribe()
    {
        $this->unsubscribeTemplate([
            'unsubscribeMap'    => 'main',
        ]);
    }

    public function unsubscribe_sender()
    {
        $this->unsubscribeTemplate([
            'unsubscribeMap'        => 'sender',
        ]);
    }

    public function unsubscribe_zoho()
    {
        /** @var Subscribe_Model $subscribeModel */
        $subscribeModel = model(Subscribe_Model::class);

        $this->unsubscribeTemplate([
            'unsubscribeMap' => 'zoho',
            'reasonMessages' => $subscribeModel->getUnsubscribeReasons(),
        ]);
    }

    /**
     * Shows user edit profile popup.
     *
     * @deprecated `v2.32.0` It is unused. No replacement provided.
     *
     * @todo Remove `[20.01.2022]`
     * Reason: Deprecated and not used
     *
     * @param mixed $user_id
     */
    protected function show_user_edit_popup($user_id)
    {
        //region User
        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        if (
            empty($user_id)
            || empty($user = $usersRepository->getUser($user_id))
        ) {
            messageInModal(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Location
        /** @var Country_Model */
        $locationsRepository = model(Country_Model::class);
        $user_country_id = !empty($user['country']) ? (int) $user['country'] : null;
        $user_region_id = !empty($user['state']) ? (int) $user['state'] : null;
        $user_city_id = !empty($user['city']) ? (int) $user['city'] : null;
        $countries = $locationsRepository->fetch_port_country();
        $regions = null !== $user_country_id ? $locationsRepository->get_states($user_country_id) : [];
        $country = null !== $user_country_id ? $locationsRepository->get_country($user_country_id) : null;
        $region = null !== $user_region_id ? $locationsRepository->get_state($user_region_id) : null;
        $city = null !== $user_city_id ? $locationsRepository->get_city($user_city_id) : null;
        //endregion Location

        //region Phone & Fax codes
        $phone_codes_service = new PhoneCodesService(model(Phone_Codes_Model::class));
        $phone_codes = $fax_codes = $phone_codes_service->getCountryCodes();

        //region Phone code
        $phone_code = $phone_codes_service->findAllMatchingCountryCodes(
            !empty($user['phone_code_id']) ? (int) $user['phone_code_id'] : null,
            !empty($user['phone_code']) ? (string) $user['phone_code'] : null, // Fallback to old phone code system
            $user_country_id, // Or falling back to user country
            PhoneCodesService::SORT_BY_PRIORITY
        )->first();
        //endregion Phone code

        //region Fax code
        $fax_code = $phone_codes_service->findAllMatchingCountryCodes(
            !empty($user['fax_code_id']) ? (int) $user['fax_code_id'] : null,
            !empty($user['fax_code']) ? (string) $user['fax_code'] : null, // Fallback to old phone code system
            $user_country_id, // Or falling back to user country
            PhoneCodesService::SORT_BY_PRIORITY
        )->first();
        //endregion Fax code
        //endregion Phone & Fax codes

        //region Groups
        /** @var UserGroup_Model */
        $groupsRepository = model(UserGroup_Model::class);
        $fields = $groupsRepository->getFiledsByGroup($user['user_group'], "'simple' ,'social'");
        $right_fields = $groupsRepository->getUsersRightFields($user_id);
        //endregion Groups

        //region Assign vars
        views()->assign([
            'user'                => $user,
            'fields'              => $fields,
            'regions'             => $regions,
            'countries'           => $countries,
            'fax_codes'           => $fax_codes,
            'phone_codes'         => $phone_codes,
            'fields_values'       => $right_fields,
            'selected_city'       => $city,
            'selected_region'     => $region,
            'selected_country'    => $country,
            'selected_phone_code' => $phone_code,
            'selected_fax_code'   => $fax_code,
        ]);
        //endregion Assign vars

        views()->display('admin/user/edit_user_form_view');
    }

    /**
     * Saves user profile.
     *
     * @deprecated `v2.32.0` at `[20.01.2022]` in favor of `Profile_Controller::saveProfile()`, `Profile_Controller::saveLegacyProfile()`
     * and `Profile_Controller::saveProfileAddendum()`
     *
     * @todo Remove `[20.01.2022]`
     * Reason: Deprecated and not used
     *
     * @param int  $user_id
     * @param bool $is_administrator
     */
    protected function save_profile($user_id, array $postdata = [], $is_administrator = false)
    {
        //region Validation
        $validator_fields = [
            'address' => ['city' => 'port_city', 'state' => 'states', 'postalCode' => 'zip'],
            'phone'   => ['phone' => 'phone', 'code' => 'phone_code'],
        ];

        $validator_messages = [
            'address' => [],
            'phone'   => [
                'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
            ],
        ];

        $validator_labels = [
            'address' => [],
            'phone'   => ['phone' => 'Phone', 'code'  => 'Phone Code'],
            'fax'     => ['phone' => 'Fax', 'code'  => 'Fax code'],
        ];

        if (!empty(cleanInput($postdata['fax']))) {
            $validator_fields['fax'] = ['phone' => 'fax', 'code'  => 'fax_code'];
            $validator_messages['fax'] = [
                'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
            ];
            $validator_labels['fax'] = ['phone' => 'Fax', 'code'  => 'Fax code'];
        }

        $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new UserProfilePreferencesValidator(
            $adapter,
            $validator_fields,
            $validator_messages,
            $validator_labels,
            !have_right('manage_content')
        );

        if (isset($postdata['checkbox_legal_name'])) {
            $validator = new AggregateValidator([$validator, new LegalNameValidator($adapter)]);
        }

        if (!$validator->validate($postdata)) {
            \jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        //region Update
        //region Profile information
        $update = [
            'fname'         => $first_name = cleanInput($postdata['fname']),
            'lname'         => $last_name = cleanInput($postdata['lname']),
            'legal_name'    => $legal_name = isset($postdata['checkbox_legal_name']) ? cleanInput($postdata['legal_name']) : '',
        ];

        if (!$is_administrator) {
            $phone_codes_service = new PhoneCodesService(model('country'));
            /** @var CountryCodeInterface $phone_code */
            $phone_code = $phone_codes_service->findAllMatchingCountryCodes((int) $postdata['phone_code'])->first();
            /** @var CountryCodeInterface $fax_code */
            $fax_code = $phone_codes_service->findAllMatchingCountryCodes((int) $postdata['fax_code'])->first();

            $update = array_merge(
                $update,
                [
                    'zip'            => cleanInput($postdata['zip']),
                    'city'           => $city_id = (int) $postdata['port_city'],
                    'state'          => $region_id = (int) $postdata['states'],
                    'country'        => $country_id = (int) $postdata['country'],
                    'address'        => cleanInput($postdata['address']),
                    'description'    => cleanInput($postdata['description']),
                    'phone_code_id'  => $phone_code ? $phone_code->getId() : null,
                    'phone_code'     => $phone_code ? $phone_code->getName() : null,
                    'phone'          => cleanInput($postdata['phone']),
                    'fax_code_id'    => $fax_code ? $fax_code->getId() : null,
                    'fax_code'       => $fax_code ? $fax_code->getName() : null,
                    'fax'            => cleanInput($postdata['fax']),
                    'user_find_type' => cleanInput($postdata['find_type']),
                    'user_find_info' => cleanInput($postdata['find_info']),
                ]
            );

            if (!empty($city = model('country')->get_city($city_id))) {
                $update['user_city_lat'] = $city['city_lat'];
                $update['user_city_lng'] = $city['city_lng'];
            }
        }
        //endregion Profile information

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        //region Update profile
        if (!$userModel->updateUserMain($user_id, $update)) {
            jsonResponse(translate('systmess_error_db_insert_error'));
        }
        //endregion Update profile

        $this->update_user_questions_in_elastic($user_id);

        //region Update profile completion
        /** @var Complete_Profile_Model $completeProfileModel */
        $completeProfileModel = model(Complete_Profile_Model::class);

        $completeProfileModel->update_user_profile_option($user_id, 'account_preferences');

        /** @var TinyMVC_Library_Auth $authenticationLibrary */
        $authenticationLibrary = library(TinyMVC_Library_Auth::class);
        $authenticationLibrary->setUserCompleteProfile((int) $user_id);
        //endregion Update profile completion

        //region Update session
        $session = session();
        $session->fname = $first_name;
        $session->lname = $last_name;
        $session->legal_name = $legal_name;
        $session->country = $country_id;
        //endregion Update session

        //region Update matrix profile
        // Wake up, Neo
        $this->updateUserMatrixProfile((int) $user_id);
        //endregion Update matrix profile

        //region sync with related accounts
        $allRelatedAccounts = $userModel->get_simple_users_by_id_principal(principal_id());
        //endregion sync with related accounts

        if (count($allRelatedAccounts) > 1) {
            $allRelatedAccounts = array_column($allRelatedAccounts, null, 'idu');

            $syncWithRelatedAccounts = null === $allRelatedAccounts[$user_id]['sync_with_related_accounts'] ? [] : json_decode($allRelatedAccounts[$user_id]['sync_with_related_accounts'], true);
            $currentPersonalInfoSyncSettings = $syncWithRelatedAccounts['personal_info'] ?: [];
            $incommingPersonalInfoSyncSettings = (array) ($postdata['sync_with_accounts'] ?? []);

            //region update related accounts
            $updateForRelatedAccount = array_intersect_key(
                $allRelatedAccounts[$user_id],
                array_fill_keys(
                    [
                        'fname',
                        'lname',
                        'legal_name',
                        'country',
                        'state',
                        'city',
                        'address',
                        'zip',
                        'phone_code_id',
                        'phone_code',
                        'phone',
                        'fax_code_id',
                        'fax_code',
                        'fax',
                        'description',
                        'user_find_type',
                        'user_find_info',
                    ],
                    ''
                )
            );

            foreach ($incommingPersonalInfoSyncSettings as $relatedAccountId) {
                $userModel->updateUserMain($relatedAccountId, $updateForRelatedAccount);

                $this->update_user_questions_in_elastic($relatedAccountId);

                // The buyer's account contains data that cannot be copied from other accounts, so we do not change the status of the buyer's complete profile.
                // if (!is_buyer((int) $allRelatedAccounts[$relatedAccountId]['user_group'])) {
                //     $completeProfileModel->update_user_profile_option($relatedAccountId, 'account_preferences');
                // }

                // Even if this is the buyer's account, the buyer chooses the industry at the time of registration or add another account
                $completeProfileModel->update_user_profile_option($relatedAccountId, 'account_preferences');

                /** @var TinyMVC_Library_Auth $authenticationLibrary */
                $authenticationLibrary = library(TinyMVC_Library_Auth::class);
                $authenticationLibrary->setUserCompleteProfile((int) $relatedAccountId);

                //region Update matrix profile
                // Wake up, Neo
                $this->updateUserMatrixProfile((int) $relatedAccountId);
                //endregion Update matrix profile
            }
            //endregion update related accounts

            //update current user only if the sync settings have been changed
            if (!empty(array_diff($currentPersonalInfoSyncSettings, $incommingPersonalInfoSyncSettings)) || !empty(array_diff($incommingPersonalInfoSyncSettings, $currentPersonalInfoSyncSettings))) {
                $newSyncSettings = $syncWithRelatedAccounts;

                unset($newSyncSettings['personal_info']);

                foreach ($incommingPersonalInfoSyncSettings as $syncAccountId) {
                    $newSyncSettings['personal_info'][$syncAccountId] = [];
                }

                $userModel->updateUserMain($user_id, ['sync_with_related_accounts' => empty($newSyncSettings) ? null : json_encode($newSyncSettings)]);
            }

            //region update session accounts
            /** @var TinyMVC_Library_Auth $authLibrary */
            $authLibrary = library(TinyMVC_Library_Auth::class);

            $authLibrary->set_accounts_in_session($user_id);
            //endregion update session accounts
        }
        //endregion Update

        jsonResponse(translate('systmess_information_successfully_changed'), 'success', [
            'url' => check_group_type('Seller,Buyer,CR Affiliate,Shipper') ? getMyProfileLink() : null,
        ]);
    }

    /**
     * Edits the user profile.
     *
     * @deprecated `v2.32.0` It is unused. No replacement provided.
     *
     * @todo Remove `[20.01.2022]`
     * Reason: Deprecated and not used
     *
     * @param int $user_id
     */
    protected function edit_user_profile($user_id, array $postdata = [])
    {
        //region Validation
        /** @var Country_Model */
        $locationsRepository = model(Country_Model::class);
        $validator_rules = [
            [
                'field' => 'idu',
                'label' => 'User info',
                'rules' => ['required' => '', 'integer' => ''],
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
            ],
            [
                'field' => 'fname',
                'label' => 'First Name',
                'rules' => ['required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => ''],
            ],
            [
                'field' => 'lname',
                'label' => 'Last Name',
                'rules' => ['required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => ''],
            ],
            [
                'field' => 'country',
                'label' => 'Country',
                'rules' => [
                    'required' => '',
                    function ($attr, $country_id, $fail) use ($locationsRepository) {
                        if (empty($country_id) || !$locationsRepository->has_country($country_id)) {
                            $fail(sprintf('Field "%s" contains unknown country.', $attr));
                        }
                    },
                ],
            ],
            [
                'field' => 'states',
                'label' => 'Region',
                'rules' => [
                    'required' => '',
                    function ($attr, $region_id, $fail) use ($locationsRepository) {
                        if (empty($region_id) || !$locationsRepository->has_state($region_id)) {
                            $fail(sprintf('Field "%s" contains unknown region.', $attr));
                        }
                    },
                ],
            ],
            [
                'field' => 'port_city',
                'label' => 'City',
                'rules' => [
                    'required' => '',
                    function ($attr, $city_id, $fail) use ($locationsRepository) {
                        if (empty($city_id) || !$locationsRepository->has_city($city_id)) {
                            $fail(sprintf('Field "%s" contains unknown city.', $attr));
                        }
                    },
                ],
            ],
            [
                'field' => 'address',
                'label' => 'Address',
                'rules' => ['required' => '', 'min_len[3]' => '', 'max_len[255]' => ''],
            ],
            [
                'field' => 'zip',
                'label' => 'ZIP',
                'rules' => ['required' => '', 'zip_code' => '', 'max_len[20]' => ''],
            ],
            [
                'field' => 'phone',
                'label' => 'Phone',
                'rules' => ['required' => '',
                    function ($attr, $phone, $fail) use ($locationsRepository) {
                        $phone_util = PhoneNumberUtil::getInstance();
                        $phone_code_id = arrayGet($_POST, 'phone_code');
                        $phone_code = $locationsRepository->get_country_code($phone_code_id)['ccode'] ?? null;
                        $raw_number = trim("{$phone_code} {$phone}");

                        try {
                            if (!$phone_util->isViablePhoneNumber($raw_number)) {
                                $fail(translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]));
                            }

                            $phone_number = $phone_util->parse($raw_number);
                            if (!$phone_util->isValidNumber($phone_number)) {
                                $fail(translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]));
                            }
                        } catch (NumberParseException $exception) {
                            $fail(translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]));
                        }
                    }, ],
            ],
            [
                'field' => 'phone_code',
                'label' => 'Phone code',
                'rules' => [
                    'required' => '',
                    function ($attr, $phone_code_id, $fail) use ($locationsRepository) {
                        if (empty($phone_code_id) || !$locationsRepository->has_country_code($phone_code_id)) {
                            $fail(sprintf('Field "%s" contains unknown value.', $attr));
                        }
                    },
                ],
            ],
            [
                'field' => 'fax',
                'label' => 'Fax',
                'rules' => [
                    function ($attr, $phone, $fail) use ($locationsRepository) {
                        $phone_util = PhoneNumberUtil::getInstance();
                        $fax_code_id = arrayGet($_POST, 'fax_code');
                        $fax_code = $locationsRepository->get_country_code($fax_code_id)['ccode'] ?? null;
                        $raw_number = trim("{$fax_code} {$phone}");

                        try {
                            if (!$phone_util->isViablePhoneNumber($raw_number)) {
                                $fail(translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]));
                            }

                            $phone_number = $phone_util->parse($raw_number);
                            if (!$phone_util->isValidNumber($phone_number)) {
                                $fail(translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]));
                            }
                        } catch (NumberParseException $exception) {
                            $fail(translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]));
                        }
                    }, ],
            ],
            [
                'field' => 'fax_code',
                'label' => 'Fax code',
                'rules' => [
                    function ($attr, $fax_code_id, $fail) use ($locationsRepository) {
                        if (empty($fax_code_id) || !$locationsRepository->has_country_code($fax_code_id)) {
                            $fail(sprintf('Field "%s" contains unknown value.', $attr));
                        }
                    },
                ],
            ],
            [
                'field' => 'description',
                'label' => 'Description',
                'rules' => ['max_len[1000]' => ''],
            ],
        ];

        $this->validator->reset_postdata();
        $this->validator->clear_array_errors();
        $this->validator->validate_data = $postdata;
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region User
        /** @var User_Model */
        $usersRepository = model(User_Model::class);
        if (
            empty($user_id)
            || empty($user = $usersRepository->getUser($user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }

        //region Email check
        $new_email = cleanInput($postdata['email']);
        // if ($new_email !== $user['email'])
        // {
        //  $this->load->model('Auth_Model', 'auth');
        //  $user_encrypt_new = userLoginEncryptData($new_email);
        //  if($this->auth_hash->exists_hash($user_encrypt_new['email'])){
        //      jsonResponse(translate('systmess_error_email_already_registered'));
        //  }
        //  $encrypted = userLoginEncryptData($new_email);
        //  //region hash update
        //  $hash_update = array(
        //      'token_email' => $encrypted['email']
        //  );
        //  $this->load->model('Auth_model', 'auth');
        //  $this->auth->change_hash($user_id, $hash_update);
        //  //endregion hash update
        // }
        //endregion Email check
        //endregion User

        //region Update
        //region Profile information
        $first_name = cleanInput($postdata['fname']);
        $last_name = cleanInput($postdata['lname']);
        $phone_codes_service = new PhoneCodesService(model('country'));
        /** @var CountryCodeInterface $phone_code */
        $phone_code = $phone_codes_service->findAllMatchingCountryCodes((int) $postdata['phone_code'])->first();
        /** @var CountryCodeInterface $fax_code */
        $fax_code = $phone_codes_service->findAllMatchingCountryCodes((int) $postdata['fax_code'])->first();
        $update = [
            'email'         => $new_email,
            'fname'         => $first_name,
            'lname'         => $last_name,
            'zip'           => cleanInput($postdata['zip']),
            'city'          => (int) $postdata['port_city'],
            'state'         => (int) $postdata['states'],
            'country'       => (int) $postdata['country'],
            'address'       => cleanInput($postdata['address']),
            'description'   => cleanInput($postdata['description']),
            'phone_code_id' => $phone_code ? $phone_code->getId() : null,
            'phone_code'    => $phone_code ? $phone_code->getName() : null,
            'phone'         => cleanInput($postdata['phone']),
            'fax_code_id'   => $fax_code ? $fax_code->getId() : null,
            'fax_code'      => $fax_code ? $fax_code->getName() : null,
            'fax'           => cleanInput($postdata['fax']),
        ];
        //endregion Profile information

        //region Update profile
        if (!$usersRepository->updateUserMain($user_id, $update)) {
            jsonResponse('Error: You cannot change the user info now. Please try again later');
        }
        //endregion Update profile

        //region Add notice
        if ($new_email !== $user['email']) {
            $usersRepository->set_notice($user_id, [
                'add_date' => date('Y/m/d H:i:s'),
                'add_by'   => user_name_session(),
                'notice'   => "The email has been changed from: {$user['email']} to {$new_email}.",
            ]);
        }
        //endregion Add notice
        // Send event about profile update
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedProfileEvent((int) $user_id));
        //endregion Update

        jsonResponse('Changes has been saved successfully.', 'success');
    }

    private function _load_main()
    {
        $this->load->model('Category_Model', 'category');
        $this->load->model('User_Model', 'user');
        $this->load->model('Company_Model', 'directory');
    }

    private function _get_main_data(&$data, $user_group)
    {
        $this->load->model('Itemcomments_Model', 'itemcomments');
        $this->load->model('Items_Model', 'items');
        $this->load->model('Followers_model', 'followers');
        $this->load->model('Ep_News_Model', 'ep_news');
        $this->load->model('ItemsReview_Model', 'itemreviews');
        $this->load->model('UserFeedback_Model', 'userfeedbacks');
        $this->load->model('Itemquestions_Model', 'itemquestions');

        $user_followers = $this->followers->get_user_followers($this->_id_user);
        $user_followed = $this->followers->get_users_followed($this->_id_user);

        $data['user_followers'] = [];
        $data['user_followed'] = [];
        if ((!empty($user_followers) || !empty($user_followed)) && logged_in()) {
            $data['user_followers'] = array_map(
                function ($userFollowers) {
                    $chatBtn = new ChatButton(['recipient' => $userFollowers['idu'], 'recipientStatus' => $userFollowers['status']]);
                    $userFollowers['btnChat'] = $chatBtn->button();

                    return $userFollowers;
                },
                $user_followers
            );

            $data['user_followed'] = array_map(
                function ($userFollowed) {
                    $chatBtn = new ChatButton(['recipient' => $userFollowed['id_user'], 'recipientStatus' => $userFollowed['status']]);
                    $userFollowed['btnChat'] = $chatBtn->button();

                    return $userFollowed;
                },
                $user_followed
            );
        } elseif (!empty($user_followers) || !empty($user_followed)) {
            $data['user_followers'] = $user_followers;
            $data['user_followed'] = $user_followed;
        }

        //feedbacks
        $data['feedbacks_user'] = $this->userfeedbacks->get_user_feedbacks(['id_user' => $this->_id_user, 'db_keys' => 'id_feedback']);

        if (!empty($data['feedbacks_user'])) {
            // unserialize services
            foreach ($data['feedbacks_user'] as $key => $value) {
                $data['feedbacks_user'][$key]['services_count'] = 0;
                if (!empty($value['services'])) {
                    $data['feedbacks_user'][$key]['services'] = unserialize($value['services']);

                    foreach ($data['feedbacks_user'][$key]['services'] as $service_value) {
                        $data['feedbacks_user'][$key]['services_count'] += $service_value;
                    }
                }

                if (!empty($value['order_summary'])) {
                    $data['feedbacks_user'][$key]['order_summary'] = unserialize($value['order_summary']);
                }
            }
        }

        //written feedback
        $data['feedback_written'] = true;
        $data['feedbacks_written_user'] = $this->userfeedbacks->get_user_feedbacks(['poster' => $this->_id_user, 'db_keys' => 'id_feedback']);

        if (!empty($data['feedbacks_written_user'])) {
            // unserialize services
            foreach ($data['feedbacks_written_user'] as $key => $value) {
                if (!empty($value['services'])) {
                    $data['feedbacks_written_user'][$key]['services'] = unserialize($value['services']);
                }
                if (!empty($value['order_summary'])) {
                    $data['feedbacks_written_user'][$key]['order_summary'] = unserialize($value['order_summary']);
                }
            }
        }

        if (logged_in()) {
            $saved_list = $this->items->get_items_saved(id_session());
            $data['saved_items'] = explode(',', $saved_list);

            $id_session = privileged_user_id();
            if (have_right('buy_item')) {
                if (is_my($this->_id_user)) {
                    $params = ['id_buyer' => id_session()];
                } else {
                    $params = ['id_seller' => $this->_id_user, 'id_buyer' => id_session()];
                }
            } elseif (have_right('sell_item') || (user_type('users_staff') && have_right('leave_feedback'))) {
                $params = ['id_seller' => $id_session, 'id_buyer' => $this->_id_user, 'feedback_seller' => 1];
            }

            //check feedback
            if (have_right('buy_item') || have_right('sell_item') || (user_type('users_staff') && have_right('leave_feedback'))) {
                $params['status'] = 11;
                $data['user_ordered_for_feedback'] = $this->userfeedbacks->check_user_feedback($params);
            }

            //check reviews
            if (have_right('buy_item')) {
                $data['user_ordered_items_for_reviews'] = $this->itemreviews->check_user_review(['id_buyer'=>id_session(), 'id_seller'=>$this->_id_user]);
            }

            //helpfull received feedback
            if (!empty($data['feedbacks_user'])) {
                $feedbacks_keys = implode(',', array_keys($data['feedbacks_user']));
                $data['helpful_feedbacks'] = $this->userfeedbacks->get_helpful_by_feedback($feedbacks_keys, $id_session);
            }

            //helpfull written feedback
            if (!empty($data['feedbacks_written_user'])) {
                $feedbacks_written_keys = implode(',', array_keys($data['feedbacks_written_user']));
                $data['helpful_feedbacks_written'] = $this->userfeedbacks->get_helpful_by_feedback($feedbacks_written_keys, $id_session);
            }

            //helpfull reviews
            if (!empty($data['reviews'])) {
                foreach ($data['reviews'] as $item_review) {
                    $array_review_id[] = $item_review['id_review'];
                }

                $data['helpful_reviews'] = $this->itemreviews->get_helpful_by_review(implode(',', $array_review_id), $id_session);
            }

            //helpfull questions
            if (!empty($data['questions'])) {
                foreach ($data['questions'] as $item) {
                    $array_id[] = $item['id_q'];
                }

                $data['helpful'] = $this->itemquestions->get_helpful_by_question(implode(',', $array_id), $id_session);
            }
        }

        $data['ep_news'] = $this->ep_news->get_list_ep_news_public(['limit' => 3]);

        //questions
        $data['questions'] = $this->itemquestions->get_questions(['questioner' => $this->_id_user, 'per_p' => 10, 'count' => $data['user_statistic']['item_questions_wrote']]);

        $data['user_services_form'] = $this->userfeedbacks->getServiceByGroup($user_group);
    }

    private function _get_buyer_page_data(&$data)
    {
        $this->_get_main_data($data, 'Buyer');

        $this->load->model('Company_Buyer_Model', 'company_buyer');
        $data['company_buyer'] = $this->company_buyer->get_company_by_user($this->_id_user);
        $data['meta_params']['[COMPANY_NAME]'] = $data['company_buyer']['company_name'];

        //reviews
        $data['reviews'] = array_column(
            $this->itemreviews->get_user_reviews([
                'user'  => $this->_id_user,
                'per_p' => 10,
            ]),
            null,
            'id_review'
        );

        if (!empty($data['reviews'])) {
            /** @var Product_Reviews_Images_Model $reviewImagesModel */
            $reviewImagesModel = model(Product_Reviews_Images_Model::class);

            $reviewsImages = $reviewImagesModel->findAllBy([
                'conditions' => [
                    'reviewsIds' => array_column($data['reviews'], 'id_review'),
                ],
            ]);

            foreach ($reviewsImages as $reviewImage) {
                $data['reviews'][$reviewImage['review_id']]['images'][] = $reviewImage['name'];
            }
        }
    }

    private function _get_shipper_page_data(&$data)
    {
        /** @var Shippers_Model */
        $shippersRepository = model(Shippers_Model::class);
        $question_categories_method = __SITE_LANG === 'en' ? 'getCategories' : 'getCategories_i18n';
        $data['quest_cats'] = arrayByKey(model('questions')->{$question_categories_method}(['visible' => 1]), 'idcat');

        $data['company_shipper'] = $shippersRepository->get_shipper_by_user($this->_id_user);

        //region COMMUNITY QUESTIONS
        $data['community_questions'] = $this->_get_community_questions($this->_id_user, 0);

        //endregion COMMUNITY QUESTIONS

        //region COMMUNITY ANSWERS
        $community_answers = $this->_get_community_answers($this->_id_user, 0, true, true);
        $data['community_questions_answers'] = $community_answers['questions'];
        $data['count_current_answers'] = $community_answers['count_current_answers'];
        $data['count_questions_with_answers'] = $community_answers['count'];
        //endregion COMMUNITY ANSWERS
    }

    private function _get_seller_page_data(&$data)
    {
        $this->_get_main_data($data, 'Seller');

        $this->load->model('Company_Model', 'company');
        $data['company'] = $this->company->get_company(['id_user' => $this->_id_user]);
        $data['meta_params']['[COMPANY_NAME]'] = $data['company']['name_company'];

        $data['reviews'] = array_column(
            $this->itemreviews->get_user_reviews([
                'id_seller' => $this->_id_user,
                'per_p'     => 10,
            ]),
            null,
            'id_review'
        );

        if (!empty($data['reviews'])) {
            /** @var Product_Reviews_Images_Model $reviewImagesModel */
            $reviewImagesModel = model(Product_Reviews_Images_Model::class);

            $reviewsImages = $reviewImagesModel->findAllBy([
                'conditions' => [
                    'reviewsIds' => array_column($data['reviews'], 'id_review'),
                ],
            ]);

            foreach ($reviewsImages as $reviewImage) {
                $data['reviews'][$reviewImage['review_id']]['images'][] = $reviewImage['name'];
            }
        }
    }

    private function _get_community_questions($id_user, $start, $count = false)
    {
        $this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');

        $cond_es_questions = [
            'id_user'  => $id_user,
            'page'     => $start,
            'per_p'    => config('company_community_questions_per_page', 10),
            'order_by' => 'date_question-desc',
        ];

        $this->elasticquestions->getQuestions($cond_es_questions);

        if (!$count) {
            return $this->elasticquestions->questions_records;
        }

        $data['questions'] = $this->elasticquestions->questions_records;
        $data['count'] = $this->elasticquestions->questions_count;

        return $data;
    }

    private function _get_community_answers($id_user, $page, $count = false, $count_current = false)
    {
        $data = [
            'questions' => [],
        ];

        $this->load->model('Elasticsearch_Questions_Model', 'elasticquestions');

        $conditions_answers = [
            'by_id_user' => $id_user,
            'page'       => $page,
            'per_p'      => config('user_question_answers_per_page', 10),
            'order_by'   => 'answers.date_answer-desc',
        ];

        $this->elasticquestions->getAnswers($conditions_answers);

        $community_questions = $this->elasticquestions->question_answers_records;
        $community_answers = $this->elasticquestions->inner_answers_records;

        if (!empty($community_questions)) {
            $community_questions = array_column($community_questions, null, 'id_question');

            foreach ($community_answers as $key => $value) {
                $community_questions[$key]['answers'] = $value;
            }

            $data['questions'] = $community_questions;
        }

        if (!$count && !$count_current) {
            return $data['questions'];
        }

        if ($count_current) {
            $data['count_current_answers'] = array_sum(array_map('count', $community_answers));
        }

        if ($count) {
            $data['count'] = $this->elasticquestions->question_answers_count;
        }

        return $data;
    }

    private function photoEpl($data)
    {
        $data['templateViews'] = [
            'mainOutContent'    => 'user/photo_new_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect'   => 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function photoAll($data)
    {
        views(['new/header_view', 'new/user/photo_new_view', 'new/footer_view'], $data);
    }

    private function changeEmailPassEpl($data)
    {
        $data['templateViews'] = [
            'mainOutContent'    => 'user/change_email_pass_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect'   => 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function changeEmailPassAll($data)
    {
        views(['new/header_view', 'new/user/change_email_pass_view', 'new/footer_view'], $data);
    }

    private function emailDeliverySettingsEpl($data)
    {
        $data['templateViews'] = [
            'mainOutContent'    => 'user/email_delivery_settings_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect'   => 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function emailDeliverySettingsAll($data)
    {
        views(['new/header_view', 'new/user/email_delivery_settings_view', 'new/footer_view'], $data);
    }

    private function unsubscribeTemplate($data = [])
    {
        views(
            [
                'new/header_view',
                'new/unsubscribe/template_view',
                'new/footer_view',
            ],
            $data
        );
    }

    private function update_user_questions_in_elastic($id_user)
    {
        /** @var Questions_Model */
        $questionsRepository = model(Questions_Model::class);
        /** @var Elasticsearch_Questions_Model */
        $elasticQuestionsRepository = model(Elasticsearch_Questions_Model::class);
        $user_questions = $questionsRepository->getQuestions(['id_user' => $id_user]);
        $user_answers = $questionsRepository->getAnswers(['id_user' => $id_user]);

        if (!empty($user_questions)) {
            $ids = array_column($user_questions, 'id_question');
            foreach ($ids as $qid) {
                $elasticQuestionsRepository->updateQuestion($qid);
            }
        }

        if (!empty($user_answers)) {
            $ids = array_column($user_questions, 'id_answer');
            foreach ($user_answers as $answer) {
                $elasticQuestionsRepository->indexAnswer($answer['id_answer'], $answer['id_question'], 'update');
            }
        }
    }
}
