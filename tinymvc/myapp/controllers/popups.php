<?php

/**
 * Controller Popups
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */

use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\RestrictionType;

class Popups_Controller extends TinyMVC_Controller
{
    public function check_popup()
    {
        checkIsAjax();

        $active_popup = null;
        $page_hash = cleanInput(uri()->segment(3));
        $call_on_start = 1;
        $call_on_page_load = 0;
        if (isset($_POST['call_on_start'])) {
            $call_on_start = (int) $_POST['call_on_start'];
        }

        if (isset($_POST['call_on_page_load'])) {
            $call_on_page_load = (int) $_POST['call_on_page_load'];
        }

        /** @var Popup_pages_relation_Model $popupAndPagesPivot */
        $popupAndPagesPivot = model(Popup_pages_relation_Model::class);
        $popups = $popupAndPagesPivot->get_list([
            'call_on_start' => $call_on_start,
            'page_hash'     => $page_hash,
            'is_active'     => 1,
        ]);

        foreach ($popups as $popup) {
            if (!cookies()->exist_cookie("_ep_popup_{$popup['view_method']}")) {
                $active_popup = $popup;

                break;
            }
        }

        if (!empty($active_popup['view_method']) && method_exists($this, $active_popup['view_method'])) {
            jsonResponse('', 'success', [
                'popup_info' => $active_popup,
                'content'    => $this->{$active_popup['view_method']}($call_on_page_load),
            ]);
        }

        jsonResponse(null, 'success');
    }

    public function ajax_operations()
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'giveaway_contest':
                widgetPopupsSystemRemoveOneItem("giveaway_contest");

                $data = [
                    "image"     => !logged_in() ? asset("public/build/images/popups/giveaway_contest/unregistered.jpg") : asset("public/build/images/popups/giveaway_contest/registered.jpg"),
                    "title"     => !logged_in() ? "Register to join <br>our giveaway contest" : "Export Portal’s <br>first-ever giveaway contest",
                    "subTitle"  => !logged_in() ? "Want to enter Export Portal’s giveaway contest for a chance to win <strong>$1,000</strong>? Then register for free today!" : "We're hosting a contest to give away <strong>$1,000</strong> to one lucky winner! To enter, you must be registered and upload a video detailing how Export Portal has aided the growth of your company.",
                    "content"   => views()->fetch("new/popups/giveaway_contest_view"),
                ];

                jsonResponse("", "success", $data);

                break;
            case 'find_business_partners':
                widgetPopupsSystemRemoveOneItem("find_business_partners");

                $data = [
                    "image"     => asset("public/build/images/popups/find-business-partners.jpg"),
                    "title"     => translate('find_business_partners_popup_title'),
                    "subTitle"  => translate('find_business_partners_popup_subtitle'),
                    "content"   => views()->fetch("new/popups/find_business_partners_view"),
                ];

                jsonResponse("", "success", $data);

                break;
            case 'feedback_certification':
                $data = [
                    'popup_name' => 'feedback_certification',
                ];
                $content = views()->fetch('new/popups/feedback_register_view', $data);

                jsonResponse('', 'success', ['content' => $content]);

                break;
            case 'feedback_registration':
                $data = [
                    'popup_name' => 'feedback_registration',
                ];
                $content = views()->fetch('new/popups/feedback_register_view', $data);

                jsonResponse('', 'success', ['content' => $content]);

                break;
            case 'add_item':
                $content = views()->fetch('new/popups/add_item_view');

                jsonResponse('', 'success', ['content' => $content]);

                break;
            case 'hash_blog':
                $content = views()->fetch('new/popups/hash_blog_view');

                jsonResponse('', 'success', ['content' => $content]);

                break;
            case 'hash_about':
                $content = views()->fetch('new/popups/hash_about_view');

                jsonResponse('', 'success', ['content' => $content]);

                break;
            case 'cookies_accept':
                if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
                    $content = views()->fetch('new/popups/cookies_accept_epl_view');
                } else {
                    $content = views()->fetch('new/popups/cookies_accept_view');
                }

                jsonResponse('', 'success', ['content' => $content]);

                break;
            case 'complete_profile':
                checkIsLoggedAjax();

                /** @var Users_Model $usersRepository */
                $usersRepository = model(Users_Model::class);
                $user = $usersRepository->findOne(privileged_user_id());
                $complete_profile = session()->__get('completeProfile');

                jsonResponse('', 'success', [
                    'content'       => views()->fetch('new/complete_profile/complete_profile_list_view', [
                        'complete_profile' => $complete_profile,
                    ]),
                    'title'         => translate('complete_profile_title', ["{{STEPS_COUNT}}" => "({$complete_profile['countCompleteOptions']}/{$complete_profile['countOptions']})"]),
                    'subTitle'      => translate('complete_profile_description'),
                    'maxWidth'      => 530,
                    'titleImage'    => asset("public/build/images/popups/profile_completion/complete_profile_background.jpg"),
                ]);

                break;
            case 'update_profile_picture':
                widgetPopupsSystemRemoveOneItem('update_profile_picture');

                $data = [
                    'title'     => translate('update_profile_picture_popup_title'),
                    'subTitle'  => translate('update_profile_picture_popup_subtitle'),
                    'content'   => views()->fetch('new/popups/change_profile_photo_view'),
                ];

                jsonResponse('', 'success', $data);

                break;
            case 'certification_expire_soon':
                checkIsLoggedAjax();
                checkPermisionAjax('upgrade_group');

                /** @var Users_Model $usersRepository */
                $usersRepository = model(Users_Model::class);
                $user = $usersRepository->findOne(privileged_user_id());

                widgetPopupsSystemRemoveOneItem('certification_expire_soon');
                jsonResponse('', 'success', [
                    'title'     => translate('upgrade_modal_certify_expire_soon_title'),
                    'subTitle'  => translate('upgrade_modal_certify_expire_soon_subtitle', ['[DATE]' => getDateFormat(getPaidUntil(), 'Y-m-d', 'j M, Y')]),
                    'content'   => views()->fetch('new/popups/certification_expire_soon_view', [
                        'paidUntilCountdown' => $user['paid_until']->setTime(0, 0, 0, 0)->setTimezone(new DateTimeZone('UTC')),
                    ]),
                ]);

                break;
            case 'free_featured_items':
                checkIsLoggedAjax();

                /** @var Users_Model $usersRepository */
                $usersRepository = model(Users_Model::class);
                $userInfo = $usersRepository->findOne(id_session());
                if (1 !== (int) $userInfo['free_featured_items']) {
                    jsonResponse(translate('general_no_permission_message'));
                }

                widgetPopupsSystemRemoveOneItem('free_featured_items');
                jsonResponse('', 'success', [
                    'title'     => translate('featured_items_modal_title'),
                    'subTitle'  => translate('featured_items_modal_subtitle'),
                    'content'   => views()->fetch('new/popups/free_featured_items_view'),
                    'footer'    => views()->fetch('new/popups/free_featured_items_footer_view'),
                ]);

                break;
            case 'subscribe_popup':
                jsonResponse('', 'success', [
                    'title'     => translate('subscribe_popup_ttl'),
                    'subTitle'  => translate('subscribe_popup_subttl'),
                    'content'   => views()->fetch('new/popups/subscribe_popup_view', [
                        'benefits'    => [
                            [
                                'icon' => asset('public/build/images/subscribe/benefits/premium-quality.svg'),
                                'desc' => translate('subscribe_popup_recommendations_on_products'),
                            ],
                            [
                                'icon' => asset('public/build/images/subscribe/benefits/megaphone.svg'),
                                'desc' => translate('subscribe_popup_promotional_opportunities'),
                            ],
                            [
                                'icon' => asset('public/build/images/subscribe/benefits/blogs.svg'),
                                'desc' => translate('subscribe_popup_blogs_with_essential_advice'),
                            ],
                            [
                                'icon' => asset('public/build/images/subscribe/benefits/news.svg'),
                                'desc' => translate('subscribe_popup_ecommerce_news'),
                            ],
                        ],
                    ]),
                ]);

                break;
            case 'items_more_visible':
                checkIsLoggedAjax();
                checkPermisionAjax('upgrade_group');

                jsonResponse('', 'success', [
                    'title'     => translate('upgrade_modal_items_more_visible_title'),
                    'subTitle'  => translate('upgrade_modal_items_more_visible_subtitle'),
                    'content'   => views()->fetch('new/popups/upgrade_account_now_view'),
                ]);

                break;
            case 'upgrade_account_now':
                checkIsLoggedAjax();
                checkPermisionAjax('upgrade_group');

                widgetPopupsSystemRemoveOneItem('upgrade_account_now');
                jsonResponse('', 'success', [
                    'title'     => translate('upgrade_modal_upgrade_account_now_title'),
                    'subTitle'  => translate('upgrade_modal_upgrade_account_now_subtitle'),
                    'content'   => views()->fetch('new/popups/upgrade_account_now_view'),
                ]);

                break;
            case 'certification_upgrade':
                checkIsLoggedAjax();

                widgetPopupsSystemRemoveOneItem('certification_upgrade');
                jsonResponse('', 'success', [
                    'subTitle'  => translate('system_message_certification_has_expired', [
                        '{{TEXT}}' => session()->paid_price > 0 ? '' : translate('certification_has_expired_free_txt'),
                    ]),
                ]);

                break;
            case 'bulk_upload_items_promotion':
                checkIsLoggedAjax();

                widgetPopupsSystemRemoveOneItem('bulk_upload_items_promotion');
                jsonResponse('', 'success', [
                    'title'     => 'Try to upload items in bulk',
                    'subTitle'  => 'Add all your items with the easy bulk upload option.',
                    'content'   => views()->fetch('new/popups/bulk_upload_items_promotion_view'),
                ]);

                break;
            case 'account_restricted':
                checkIsLoggedAjax();

                widgetPopupsSystemRemoveOneItem("account_restricted");

                /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
                $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);

                $restrictionRecord = $usersBlockingStatisticsModel->findOneBy([
                    'conditions'    => [
                        'userId'            => id_session(),
                        'restrictionType'   => RestrictionType::RESTRICTION(),
                    ],
                    'order' => ["`{$usersBlockingStatisticsModel->getTable()}`.`id`" => "desc"]
                ]);

                $content = "<div class=\"pb-10\">"
                            . translate('popup_account_restricted_subtitle', ['[DATE]' => ($restrictionRecord['cancel_date'] ?? $restrictionRecord['blocking_date'])->add(new DateInterval('P30D'))->format('F j, Y')])
                            . "</div>"
                            . "<div class=\"info-alert-b\"><i class=\"ep-icon ep-icon_info-stroke\"></i> <span>" . translate('popup_account_restricted_message', ['[DAYS]' => 90]) . "</span></div>";

                $data = [
                    "title"    => translate('popup_account_restricted_ttl'),
                    "content"  => $content,
                ];

                jsonResponse("", "success", $data);
                break;
            case 'terms_of_use_updated':
                checkIsLoggedAjax();

                widgetPopupsSystemRemoveOneItem("terms_of_use_updated");

                $data = [
                    'title'    => translate('popup_terms_of_use_updated_ttl'),
                    'subTitle' => translate('popup_terms_of_use_updated_desc', [
                        '{{START_TAG}}' => '<a href="' . __SITE_URL. 'terms_and_conditions/tc_terms_of_use">',
                        '{{END_TAG}}'   => '</a>'
                    ]),
                ];

                jsonResponse("", "success", $data);
                break;
            case 'save':
                if (empty($popupHash = uri()->segment(4))) {
                    jsonResponse(translate('systmess_error_sended_data_not_valid'));
                }

                /** @var Popup_Model $popupsRepository */
                $popupsRepository = model(Popup_Model::class);
                if (
                    empty(
                        $popup = $popupsRepository->findOneBy([
                            'scopes' => [
                                'popupHash' => $popupHash,
                                'isActive'  => 1,
                            ],
                        ])
                    )
                ) {
                    jsonResponse(translate('systmess_error_sended_data_not_valid'));
                }

                /** @var Popups_data_Model $popupsDataRepository */
                $popupsDataRepository = model(Popups_data_Model::class);
                $dataMapping = $popup['data_mapping'];
                $popupsData = [
                    'id_popup' => $popup['id_popup'],
                    'id_user'  => logged_in() ? id_session() : session()->user_verification ?? null,
                    'data'     => null,
                ];

                if (!empty($dataMapping)) {
                    $validator = $this->validator;
                    $validator->set_rules($dataMapping);
                    if (!$validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }

                    foreach ($dataMapping as $item) {
                        $callback = arrayGet($item, 'field_callback');
                        $is_function = function_exists($callback['name']);
                        $is_method = method_exists($this, $callback['name']);
                        $field_value = $_POST[$item['field']];

                        $args = arrayGet($callback, 'args', []);
                        array_unshift($args, $field_value);

                        if ($is_function) {
                            $field_value = call_user_func_array($callback['name'], $args);
                        }

                        if ($is_method) {
                            $field_value = call_user_func_array([$this, $callback['name']], $args);
                        }

                        $popupsData['data'][$item['field']] = $field_value;
                    }
                    $popupsData['data'] = json_encode($popupsData['data']);
                    $popupsDataRepository->set($popupsData);
                }

                jsonResponse('', 'success');

                break;
            case 'viewed':
                $postData = request()->request->all();
                if (!isset($postData['popup']) || empty($postData['popup'])) {
                    jsonResponse(translate('systmess_error_sended_data_not_valid'));
                }

                /** @var Popup_Model $popupsModel */
                $popupsModel = model(Popup_Model::class);
                if (
                    empty(
                        $popup = $popupsModel->findOneBy([
                            'scopes' => [
                                'popupHash' => $postData['popup'],
                                'isActive'  => 1,
                            ],
                        ])
                    )
                ) {
                    jsonResponse(translate('systmess_error_sended_data_not_valid'));
                }
                if (isset($postData['viewed_type']) && !in_array($postData['viewed_type'], ['submit', 'cancel'])) {
                    jsonResponse(translate('systmess_error_sended_data_not_valid'));
                }

                /** @var User_Popups_Model $popupUsers */
                $popupUsers = model(User_Popups_Model::class);
                $viewedType = $postData['viewed_type'] ?? 'submit';
                $toInsert = true;
                $logType = logged_in() ? 'logged' : 'not_logged';
                $idUser = logged_in() ? id_session() : getEpClientIdCookieValue();

                //region if repeatable checkIfNeedToAddRecord
                if (null !== $popup["repeat_on_{$viewedType}"]) {
                    $toInsert = $popupUsers->checkIfNeedToAddRecord($logType, $idUser, $popup['id_popup'], (int) $popup["repeat_on_{$viewedType}"]);
                }
                //endregion if repeatable checkIfNeedToAddRecord

                //region check is viewed 0
                $filterByConditions = [
                    'id_popup'   => $popup['id_popup'],
                    'is_viewed'  => 0,
                ];
                if (logged_in()) {
                    $filterByConditions['id_user'] = $idUser;
                } else {
                    $filterByConditions['id_not_logged'] = $idUser;
                }
                $checkPopupIsViewed = $popupUsers->findOneBy([
                    'columns'    => 'id, is_viewed',
                    'conditions' => [
                        'filter_by' => $filterByConditions,
                    ],
                ]);
                if (!empty($checkPopupIsViewed)) {
                    if (0 === (int) $checkPopupIsViewed['is_viewed']) {
                        $popupUsers->updateOne($checkPopupIsViewed['id'], [
                            'is_viewed' => 1,
                            'show_date' => new DateTimeImmutable(date('Y-m-d H:i:s')),
                        ]);
                    }

                    //region check is viewed 0
                } elseif ($toInsert) {
                    $insertData = [
                        'id_popup'      => $popup['id_popup'],
                        'is_viewed'     => 1,
                        'viewed_type'   => $viewedType,
                        'show_date'     => new DateTimeImmutable(date('Y-m-d H:i:s')),
                    ];

                    if (logged_in()) {
                        $insertData['id_user'] = id_session();
                    } else {
                        $insertData['id_not_logged'] = getEpClientIdCookieValue();
                    }

                    $popupUsers->insertOne($insertData);
                }
                widgetPopupsSystemRemoveOneItem($postData['popup']);

                jsonResponse('', 'success');

                break;
            case 'event_feature_advertizing':
                $this->showEventAdvertisingPopup();

                break;
            case 'event_promotion':
                $this->showEventPromotionPopup(model(Ep_Events_Model::class));

                break;
        }
    }

    public function administration()
    {
        checkAdmin('manage_content');

        $pagePopups = (int) uri()->segment(3) ?: null;
        /** @var User_Groups_Model $userGroupsRepository */
        $userGroupsRepository = model(User_Groups_Model::class);
        $userGroups = $userGroupsRepository->findAllBy([
            'scopes' => [
                'groups' => [GroupType::BUYER(), GroupType::SELLER(), GroupType::SHIPPER()],
            ],
        ]);

        views(['admin/header_view', 'new/popups/admin/index_view', 'admin/footer_view'], [
            'title'     => 'Popups',
            'id_popup'  => in_array($pagePopups, [5, 6]) ? $pagePopups : 6,
            'statuses'  => $this->getPopupStatusesMeta(),
            'usergroup' => $userGroups,
        ]);
    }

    public function ajax_admin_dt()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonDTResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('manage_content')) {
            jsonDTResponse(translate('systmess_error_rights_perform_this_action'));
        }

        $page_popups = (int) uri()->segment(3);
        $popups_id = [5, 6];
        if (!in_array($page_popups, $popups_id)) {
            jsonDTResponse(translate('systmess_error_rights_perform_this_action'));
        }

        $params['id_popup'] = $page_popups;

        if (!empty($_POST['sSearch'])) {
            $params['condition_search'] = $_POST['sSearch'];
        }

        if (isset($_POST['user_status'])) {
            $params['user_status'] = cleanInput($_POST['user_status']);
        }

        if (isset($_POST['user_type'])) {
            $params['user_type'] = cleanInput($_POST['user_type']);
        }

        if (isset($_POST['start_date'])) {
            $params['start_date'] = getDateFormat($_POST['start_date'], 'm/d/Y', 'Y-m-d');
        }

        if (isset($_POST['finish_date'])) {
            $params['finish_date'] = getDateFormat($_POST['finish_date'], 'm/d/Y', 'Y-m-d');
        }

        if (isset($_POST['iDisplayStart'])) {
            $params['skip'] = intval(cleanInput($_POST['iDisplayStart']));
            $params['limit'] = intval(cleanInput($_POST['iDisplayLength']));
        }

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; ++$i) {
                switch ($_POST['mDataProp_' . intval($_POST['iSortCol_' . $i])]) {
                    case 'dt_id_data':
                        $params['sort_by'][] = 'id_data_' . $_POST['sSortDir_' . $i];

                    break;
                    case 'dt_users':
                        $params['sort_by'][] = 'username_' . $_POST['sSortDir_' . $i];

                    break;
                    case 'dt_status':
                        $params['sort_by'][] = 'status_' . $_POST['sSortDir_' . $i];

                    break;
                    case 'dt_gr_name':
                        $params['sort_by'][] = 'gr_name_' . $_POST['sSortDir_' . $i];

                    break;
                    case 'dt_gr_name_selected':
                        $params['sort_by'][] = 'gr_name_selected_' . $_POST['sSortDir_' . $i];

                    break;
                    case 'dt_date':
                        $params['sort_by'][] = 'date_created_' . $_POST['sSortDir_' . $i];

                    break;
                }
            }
        }

        /** @var Popups_data_Model $popupsDataRepository */
        $popupsDataRepository = model(Popups_data_Model::class);
        $popups = $popupsDataRepository->get_list($params);
        $popups_count = $popupsDataRepository->count_list($params);
        $output = [
            'sEcho'                => intval($_POST['sEcho']),
            'iTotalRecords'        => $popups_count,
            'iTotalDisplayRecords' => $popups_count,
            'aaData'               => [],
        ];

        if (empty($popups)) {
            jsonResponse('', 'success', $output);
        }

        /** @var User_Groups_Model $userGroupsRepository */
        $userGroupsRepository = model(User_Groups_Model::class);
        $userGroups = array_map(
            fn ($v) => trim(str_replace('Verified', '', $v)),
            array_column(
                $userGroupsRepository->findAllBy([
                    'scopes' => [
                        'groups' => [GroupType::BUYER(), GroupType::SELLER(), GroupType::SHIPPER()],
                    ],
                ]),
                'gr_name',
                'idgroup'
            )
        );
        $rate = [
            1 => '1-happy',
            2 => '2-confused',
            3 => '3-sad',
        ];
        $statuses = $this->getPopupStatusesMeta();
        foreach ($popups as $popups_item) {
            $userInfo = json_decode($popups_item['data'], true);

            $output['aaData'][] = [
                'dt_id_data'                => $popups_item['id_data'],
                'dt_users'                  => '<strong>' . $popups_item['username'] . '</strong>' . '<div>' . $popups_item['email'] . '</div>',
                'dt_status'                 => '<div>' . $statuses[$popups_item['status']]['label'] . '</div>',
                'dt_gr_name'                => $popups_item['gr_name'],
                'dt_rate'                   => '<img src="' . __SITE_URL . "public/img/rating_smyle/{$rate[$userInfo['rate']]}.png" . '" alt="rating">',
                'dt_gr_name_selected'       => $userGroups[$userInfo['user_type']],
                'dt_description'            => $userInfo['description'],
                'dt_date'                   => getDateFormat($popups_item['date_created']),
            ];
        }

        jsonResponse('', 'success', $output);
    }

    /**
     * Shows the popup for events feature advertising.
     */
    private function showEventAdvertisingPopup(): void
    {
        widgetPopupsSystemRemoveOneItem('event_feature_advertizing');
        jsonResponse(null, 'success', [
            'title'      => translate('event_feature_advertizing_popup_title'),
            'subTitle'   => translate('event_feature_advertizing_popup_subtitle'),
            'buttonText' => translate('event_feature_advertizing_popup_button_text'),
            'titleImage' => asset('public/build/images/popups/event_feature_advertizing/events_popup.jpg'),
            'content'    => views()->fetch('new/popups/event_feature_advertizing_view'),
        ]);
    }

    /**
     * Shows the popup for events feature advertising.
     */
    private function showEventPromotionPopup(Ep_Events_Model $eventsRepository): void
    {
        if (null === ($promotedEvent = $eventsRepository->findCurrentPromotedEvent())) {
            jsonResponse('The promoted event is not found', 'error');
        }

        widgetPopupsSystemRemoveOneItem('event_promotion');
        jsonResponse('', 'success', [
            'title'      => translate('event_promotion_popup_title'),
            'subTitle'   => translate('event_promotion_popup_subtitle', ['{{TITLE}}' => sprintf('<strong>%s</strong>', cleanOutput($promotedEvent['title']))]),
            'buttonUrl'  => getEpEventDetailUrl($promotedEvent),
            'buttonText' => translate('event_promotion_popup_button_text'),
            'titleImage' => getDisplayImageLink(['{ID}' => $promotedEvent['id'], '{FILE_NAME}' => $promotedEvent['main_image']], 'ep_events.main')
        ]);
    }

    private function getPopupStatusesMeta(): array
    {
        return [
            'new' => [
                'name'  => 'New',
                'label' => '<span class="label label-info">New</span>',
            ],
            'pending' => [
                'name'  => 'Pending',
                'label' => '<span class="label label-warning">Pending</span>',
            ],
            'active' => [
                'name'  => 'Active',
                'label' => '<span class="label label-success">Active</span>',
            ],
            'restricted' => [
                'name'  => 'Restricted',
                'label' => '<span class="label label-default">Restricted</span>',
            ],
            'deleted' => [
                'name'  => 'Deleted',
                'label' => '<span class="label label-danger">Deleted</span>',
            ],
            'blocked' => [
                'name'  => 'Blocked',
                'label' => '<span class="label label-danger">Blocked</span>',
            ],
        ];
    }
}

// End of file popups.php
// Location: /tinymvc/myapp/controllers/popups.php
