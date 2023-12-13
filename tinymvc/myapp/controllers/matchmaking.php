<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Common\Exceptions\MatchmakingException;
use App\Services\MatchmakingService;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Matchmaking_Controller extends TinyMVC_Controller
{
    public function user(): void
    {
        if (!have_right('manage_matchmaking')) {
            show_404();
        }

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        if (
            empty($userId = uri()->segment(3))
            || empty($user = $userModel->getSimpleUser($userId, 'users.idu, users.user_group'))
            || !in_array(strtolower($user['gr_type']), ['buyer', 'seller'])
        ) {
            show_404();
        }

        views(
            [
                'admin/header_view',
                is_buyer((int) $user['user_group']) ? 'admin/matchmaking/sellers_list_view' : 'admin/matchmaking/buyers_list_view',
                'admin/footer_view'
            ],
            [
                'userId'    => $userId,
                'title'     => 'Matchmaking',
            ]
        );
    }

    public function ajax_operations(): void
    {
        checkIsAjax();

        $operation = uri()->segment(3);

        switch ($operation) {
            case 'dt_sellers_list':
                checkPermisionAjaxDT('manage_matchmaking');

                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);

                $request = request()->request;
                $userId = $request->getInt('userId');

                if (
                    empty($userId)
                    || empty($user = $userModel->getSimpleUser($userId, 'users.idu, users.user_group'))
                    || !is_buyer((int) $user['user_group'])
                ) {
                    jsonDTResponse(translate('systmess_error_invalid_data'));
                }

                $conditions = dtConditions($request->all(), [
                    ['as' => 'matchmakingCertifiedSellers',     'key' => 'certifiedSellers',    'type' => fn ($filter) => in_array($filter, ['0', '1']) ? (int) $filter : null],
                    ['as' => 'hasB2bRequests',                  'key' => 'hasB2bRequests',      'type' => fn ($filter) => in_array($filter, ['0', '1']) ? (int) $filter : null],
                ]);

                $page = (int) abs($request->getInt('iDisplayStart')) ?: 0;
                $perPage = (int) abs($request->getInt('iDisplayLength')) ?: 10;

                try {
                    list($sellers, $countSellers) = (new MatchmakingService())->getSellers($userId, $conditions, $page, $perPage);
                } catch (MatchmakingException $e) {
                    switch ($e->getCode()) {
                        case MatchmakingException::BUYER_WITHOUT_INDUSTRIES_CODE:
                            jsonDTResponse(translate('systmess_matchmaking_buyer_without_industries'));
                            break;
                        case MatchmakingException::EMPTY_SELLERS_LIST_CODE:
                            jsonDTResponse(translate('systmess_matchmaking_empty_sellers_list'));
                            break;
                        case MatchmakingException::PAGE_GREATER_THAN_TOTAL_SELLERS_CODE:
                            $countSellers = $e->getTotalMatchMakingRecords();

                            $output = [
                                'iTotalDisplayRecords'  => 0,
                                'iTotalRecords'         => $countSellers,
                                'aaData'                => [],
                                'sEcho'                 => request()->request->getInt('sEcho'),
                            ];

                            jsonDTResponse(translate('systmess_error_invalid_data'), $output);

                        default:
                            # code...
                            break;
                    }
                }

                $output = [
                    'iTotalDisplayRecords'  => $countSellers,
                    'iTotalRecords'         => $countSellers,
                    'aaData'                => [],
                    'sEcho'                 => request()->request->getInt('sEcho'),
                ];

                foreach ($sellers as $seller) {
                    $output['aaData'][] = [
                        'dt_b2b_requests'   => $seller['hasB2bRequests'] ? '<i class="ep-icon ep-icon_ok txt-green"></i>' : '<i class="ep-icon ep-icon_remove txt-red"></i>',
                        'dt_count_items'    => '<strong>' . $seller['countItems'] . '</strong>',
                        'dt_company'        => ($seller['isCertified'] ? '<i class="ep-icon ep-icon_star txt-orange mr-10" title="Certified"></i>' : '') . '<a href="' . getCompanyURL($seller) . '" target="_blank">' . $seller['name_company'] . '</a>',
                        'dt_country'        => $seller['userCountry'],
                        'dt_phone'          => $seller['userPhoneCode'] . ' ' . $seller['userPhone'],
                        'dt_email'          => $seller['userEmail'],
                        'dt_name'           => '<a href="' . getUserLink($seller['fullName'], (int) $seller['userId'], 'seller') . '" target="_blank">' . $seller['fullName'] . '</a>',
                    ];
                }

                jsonDTResponse('', $output, 'success');

                break;
            case 'dt_buyers_list':
                checkPermisionAjaxDT('manage_matchmaking');

                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);
                $usersTable = $userModel->get_users_table();

                $request = request()->request;
                $userId = $request->getInt('userId');

                if (
                    empty($userId)
                    || empty($user = $userModel->getSimpleUser($userId, 'users.idu, users.user_group'))
                    || strtolower($user['gr_type']) != 'seller'
                ) {
                    jsonDTResponse(translate('systmess_error_invalid_data'));
                }

                $conditions = dtConditions($request->all(), [
                    ['as' => 'leftProductRequests',     'key' => 'leftProductRequests',     'type' => fn ($filter) => in_array($filter, ['0', '1']) ? (int) $filter : null],
                    ['as' => 'industriesOfInterest',    'key' => 'industriesOfInterest',    'type' => fn ($filter) => in_array($filter, ['0', '1']) ? (int) $filter : null],
                    ['as' => 'lastViewedItems',         'key' => 'lastViewedItems',         'type' => fn ($filter) => in_array($filter, ['0', '1']) ? (int) $filter : null],
                ]);

                $page = (int) abs($request->getInt('iDisplayStart')) ?: 0;
                $perPage = (int) abs($request->getInt('iDisplayLength')) ?: 10;

                try {
                    list($buyers, $countBuyers) = (new MatchmakingService())->getBuyers($userId, $conditions, $page, $perPage);
                } catch (MatchmakingException $e) {
                    switch ($e->getCode()) {
                        case MatchmakingException::SELLER_WITHOUT_INDUSTRIES_CODE:
                            jsonDTResponse(translate('systmess_matchmaking_buyers_list_no_seller_industries'));
                            break;
                        case MatchmakingException::EMPTY_BUYERS_LIST_CODE:
                            jsonDTResponse(translate('systmess_matchmaking_empty_buyers_list'), [], 'info');
                            break;
                        case MatchmakingException::PAGE_GREATER_THAN_TOTAL_BUYERS_CODE:
                            $countBuyers = $e->getTotalMatchMakingRecords();

                            $output = [
                                'iTotalDisplayRecords'  => 0,
                                'iTotalRecords'         => $countBuyers,
                                'aaData'                => [],
                                'sEcho'                 => request()->request->getInt('sEcho'),
                            ];

                            jsonDTResponse(translate('systmess_error_invalid_data'), $output);
                            break;

                        default:
                            jsonDTResponse(translate('systmess_error_invalid_data'));
                            break;
                    }
                }

                $output = [
                    'iTotalDisplayRecords'  => $countBuyers,
                    'iTotalRecords'         => $countBuyers,
                    'aaData'                => [],
                    'sEcho'                 => request()->request->getInt('sEcho'),
                ];

                foreach ($buyers as $buyer) {
                    $output['aaData'][] = [
                        'dt_industries_of_interest' => $buyer['hasIndustryOfInterest'] ? '<i class="ep-icon ep-icon_ok txt-green"></i>' : '<i class="ep-icon ep-icon_remove txt-red"></i>',
                        'dt_product_requests'       => $buyer['hasProductRequest'] ? '<i class="ep-icon ep-icon_ok txt-green"></i>' : '<i class="ep-icon ep-icon_remove txt-red"></i>',
                        'dt_viewed_items'           => $buyer['hasLastViewedItems'] ? '<i class="ep-icon ep-icon_ok txt-green"></i>' : '<i class="ep-icon ep-icon_remove txt-red"></i>',
                        'dt_country'                => $buyer['country'],
                        'dt_email'                  => $buyer['email'],
                        'dt_phone'                  => $buyer['phone_code'] . ' ' . $buyer['phone'],
                        'dt_name'                   => '<a href="' . getUserLink($buyer['fname'] . ' ' . $buyer['lname'], $buyer['idu'], 'buyer') . '" target="_blank">' . $buyer['fname'] . ' ' . $buyer['lname'] . '</a>',
                    ];
                }

                jsonDTResponse('', $output, 'success');

                break;
            case 'validate_export_form':
                checkPermisionAjax('manage_matchmaking');

                $request = request()->request;

                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);

                if (
                    empty($userId = $request->getInt('userId'))
                    || empty($user = $userModel->getSimpleUser($userId, 'users.idu, users.user_group'))
                    || !in_array(strtolower($user['gr_type']), ['buyer', 'seller'])
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $validFilters = ['userId' => $userId];

                if (strtolower($user['gr_type']) == 'seller') {
                    if (in_array($request->get('leftProductRequests'), ['0', '1'])) {
                        $validFilters['leftProductRequests'] = $request->getInt('leftProductRequests');
                    }

                    if (in_array($request->get('industriesOfInterest'), ['0', '1'])) {
                        $validFilters['industriesOfInterest'] = $request->getInt('industriesOfInterest');
                    }

                    if (in_array($request->get('lastViewedItems'), ['0', '1'])) {
                        $validFilters['lastViewedItems'] = $request->getInt('lastViewedItems');
                    }
                } else {
                    if (in_array($request->get('certifiedSellers'), ['0', '1'])) {
                        $validFilters['matchmakingCertifiedSellers'] = $request->getInt('certifiedSellers');
                    }

                    if (in_array($request->get('hasB2bRequests'), ['0', '1'])) {
                        $validFilters['hasB2bRequests'] = $request->getInt('hasB2bRequests');
                    }
                }

                jsonResponse('', 'success', ['validFilters' => http_build_query($validFilters)]);
                break;
            default:
                jsonResponse(translate('systmess_error_invalid_data'));

                break;
        }
    }

    public function popup_forms():void
    {
        checkIsAjax();
        checkPermisionAjaxModal('manage_matchmaking');

        $action = uri()->segment(3);

        switch ($action) {
            case 'export':
                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);

                if (
                    empty($userId = (int) uri()->segment(4))
                    || empty($user = $userModel->getSimpleUser($userId, 'users.idu, users.user_group'))
                    || !in_array(strtolower($user['gr_type']), ['buyer', 'seller'])
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                views(
                    is_buyer((int) $user['user_group']) ? 'admin/matchmaking/export_sellers_list_view' : 'admin/matchmaking/export_buyers_list_view',
                    [
                        'userId' => $userId,
                    ]
                );

                break;
            default:
                messageInModal(translate('systmess_error_invalid_data'));
                break;
        }
    }

    public function download_matchmaking_records(): void
    {
        if (!have_right('manage_matchmaking')) {
            return;
        }

        $query = request()->query;

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        if (
            empty($userId = $query->getInt('userId'))
            || empty($user = $userModel->getSimpleUser($userId, 'users.idu, users.fname, users.lname, users.user_group'))
            || !in_array(strtolower($user['gr_type']), ['buyer', 'seller'])
        ) {
            return;
        }

        if ('seller' === strtolower($user['gr_type'])) {
            $conditions = array_filter(
                [
                    'leftProductRequests' => in_array($query->get('leftProductRequests'), ['0', '1']) ? $query->getInt('leftProductRequests') : null,
                    'industriesOfInterest' => in_array($query->get('industriesOfInterest'), ['0', '1']) ? $query->getInt('industriesOfInterest') : null,
                    'lastViewedItems' => in_array($query->get('lastViewedItems'), ['0', '1']) ? $query->getInt('lastViewedItems') : null,
                ],
                fn ($value) => $value !== null
            );
        } else {
            $conditions = array_filter(
                [
                    'matchmakingCertifiedSellers'   => in_array($query->get('matchmakingCertifiedSellers'), ['0', '1']) ? $query->getInt('matchmakingCertifiedSellers') : null,
                    'hasB2bRequests'                => in_array($query->get('hasB2bRequests'), ['0', '1']) ? $query->getInt('hasB2bRequests') : null,
                ],
                fn ($value) => $value !== null
            );
        }

        $matchmakingService = new MatchmakingService();

        $fileName = date('m-d-Y H:i') . " matchmaking for {$user['fname']} {$user['lname']} {$userId}.xlsx";
        $excelFile = 'buyer' === strtolower($user['gr_type']) ? $matchmakingService->getExcelWithSellers($userId, $conditions) : $matchmakingService->getExcelWithBuyers($userId, $conditions);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
		header('Cache-Control: max-age=0');
		$objWriter = IOFactory::createWriter($excelFile, 'Xlsx');
        $objWriter->save('php://output');
    }
}

// End of file matchmaking.php
// Location: /tinymvc/myapp/controllers/matchmaking.php
