<?php

declare(strict_types=1);

use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
* @author Bendiucov Tatiana
* @todo Refactoring [15.12.2021]
* Controller Refactoring
 */
class Draft_Extend_Controller extends TinyMVC_Controller
{

    private const STATUS_NEW = 'new';
    private const STATUS_CONFIRMED = 'confirmed';
    private const STATUS_DECLINED = 'declined';

    /**
     * Administration page.
     */
    public function administration(): void
    {
        checkPermision('manage_content');

        views(['admin/header_view', 'admin/draft_extend/index_view', 'admin/footer_view']);
    }

    /**
     * Executes admin actions on personal documents by AJAX.
     */
    public function ajax_admin_operation(): void
    {
        checkIsAjax();
        checkIsLogged();
        checkPermisionAjax('moderate_content');

        $request = request();

        try {
            switch (uri()->segment(3)) {
                case 'list-requests':
                    $this->listAdminRequests(
                        $request,
                        $request->request->getInt('start') ?: $request->request->getInt('iDisplayStart') ?: 0,
                        $request->request->getInt('length') ?: $request->request->getInt('iDisplayLength') ?: 10
                    );

                    break;
                case 'change-status':
                    $this->changeRequestStatus($request);
                    break;
                default:
                    json(['message' => translate('systmess_error_route_not_found'), 'mess_type' => 'error'], 404);
            }
        } catch (NotFoundException $e) {
            jsonResponse(
                throwableToMessage($e, translate('systmess_error_invalid_data')),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        } catch (AccessDeniedException $e) {
            jsonResponse(
                throwableToMessage($e, translate('systmess_error_permission_not_granted')),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
    }

    /**
     * Lists requests.
     */
    protected function listAdminRequests(Request $request, int $offset, int $perPage): void
    {
        /** @var Draft_Extend_Requests_Model $extendModel */
        $extendModel = model(Draft_Extend_Requests_Model::class);

        list(
            'all'   => $allRequests,
            'total' => $totalRequests,
            'data'  => $requestsList
        ) = $extendModel->paginateForAdminGrid(
            ['is_requested' => true],
            \dtConditions($request->request->all() ?? [], [
                // ['as' => 'id',          'key' => 'id',              'type' => 'toId|intval:10'],
                ['as' => 'user',            'key' => 'user',            'type' => 'toId|intval:10'],
                ['as' => 'requested_from',  'key' => 'requested_from',  'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'requested_to',    'key' => 'requested_to',    'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'expiration_from', 'key' => 'expiration_from', 'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'expiration_to',   'key' => 'expiration_to',   'type' => 'getDateFormatIfNotEmpty:m/d/Y,Y-m-d H:i:s,'],
                ['as' => 'status',          'key' => 'status',          'type' => 'cleanInput'],
                ['as' => 'search',          'key' => 'search',          'type' => 'cut_str:200|trim'],
            ]),
            \array_column(
                \dtOrdering(
                    $request->request->all(),
                    [
                        'id'         => 'id',
                        'status'     => 'status',
                        'expiration' => 'expiration_date',
                        'extend'     => 'extend_date',
                        'requested'  => 'requested_date',
                    ],
                    null,
                    true
                ),
                'direction',
                'column'
            ),
            $perPage,
            $offset / $perPage + 1,
        );
        $requestsList = $requestsList ?? [];

        $requestsList = array_map(
            fn (array $request) => $this->renderEnvelopeAdminListEntry($request),
            $requestsList
        );

        jsonResponse(
            null,
            'success',
            [
                'sEcho'                => $request->request->getInt('draw', 0),
                'aaData'               => $requestsList ?? [],
                'iTotalRecords'        => $allRequests ?? 0,
                'iTotalDisplayRecords' => $totalRequests ?? 0,
            ]
        );
    }

    /**
     * Renders one entry for the envelope admin list grid.
     *
     * @param mixed $request
     */
    private function renderEnvelopeAdminListEntry($request): array
    {
        $userName = "{$request['user']['fname']} {$request['user']['lname']}";

        $userInfo = '<div class="pull-left">'
                        . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $userName . '" href="' . __SITE_URL . 'usr/' . strForURL($userName) . '-' . $request['id_user'] . '"></a>'
                        . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $userName . '" data-value-text="' . $userName . '" data-value="' . $request['id_user'] . '" data-name="seller"></a>'
                    . '</div>'
                    . '<div class="clearfix"></div>'
                    . '<span>' . $userName . '</span>';

        switch ($request['status']) {
            case static::STATUS_CONFIRMED:
                $color = 'success';
                break;
            case static::STATUS_NEW:
                $color = 'default';
                break;
            case static::STATUS_DECLINED:
                $color = 'danger';
                break;
            default:
            $color = 'warning';
        }

        $status = '<span class="label label-' . $color . '">' . $request['status'] . '</span><a
                    class="ep-icon ep-icon_filter txt-green dt_filter"
                    data-title="Status"
                    title="Filter by status"
                    data-value-text="' . $request['status'] . '"
                    data-value="' . $request['status'] . '"
                    data-name="status"></a>';

        $items = '<a
                    class="btn btn-primary"
                    title="View requested items"
                    href="' . __SITE_URL . 'items/administration?seller=' . $request['id_user'] . '&draft=1&expire=' . $request['expiration_date']->format('Y-m-d') . '"
                    target="_blank">View expiring items</a>';

        $actions = '';
        if($request['status'] == 'new')
        {
            $actions = '<div class="dropdown">
                <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"></a>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                    <li>
                        <a class="confirm-dialog"
                            data-callback="changeStatusRequest"
                            data-status="confirmed"
                            data-request="' . $request['id'] . '"
                            data-message="Are you sure you want to confirm this extend request?"
                            data-url="' . __SITE_URL .  'draft_extend/ajax_admin_operation/change-status' . '"
                            title="Confirm request">
                            <span class="ep-icon ep-icon_ok "></span>
                            Confirm
                        </a>
                    </li>
                    <li>
                        <a class="confirm-dialog"
                            data-callback="changeStatusRequest"
                            data-status="declined"
                            data-request="' . $request['id'] . '"
                            data-message="Are you sure you want to decline this extend request?"
                            data-url="' . __SITE_URL .  'draft_extend/ajax_admin_operation/change-status' . '"
                            title="Decline request">
                            <span class="ep-icon ep-icon_remove txt-red"></span>
                            Decline
                        </a>
                    </li>
                </ul>
            </div>';
        }

        return [
            'id'              => $request['id'],
            'user'            => $userInfo,
            'expiration'      => getDateFormat($request['expiration_date'], null, 'j M, Y'),
            'extend'          => getDateFormat($request['extend_date'], null, 'j M, Y'),
            'status'          => $status,
            'requested'       => getDateFormatIfNotEmpty($request['requested_date'] ?? null),
            'items'           => $items,
            'reason'          => cleanOutput($request['extend_reason']),
            'actions'         => $actions,
        ];
    }

    private function changeRequestStatus(Request $request)
    {
        $idRequest = $request->request->getInt('request');
        $status = $request->request->get('status');

        /** @var Draft_Extend_Requests_Model $extendModel */
        $extendModel = model(Draft_Extend_Requests_Model::class);

        if(!in_array($status, [static::STATUS_NEW, static::STATUS_CONFIRMED, static::STATUS_DECLINED])){
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $request = $extendModel->findOne($idRequest);

        if (empty($request)){
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        if($request['status'] == $status){
            jsonResponse('systmess_error_invalid_data');
        }

        if (!$extendModel->updateOne($idRequest, ['status' => $status])) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        if(static::STATUS_CONFIRMED == $status)
        {
            /** @var Items_Model $itemsModel */
            $itemsModel = model(Items_Model::class);

            if(!$itemsModel->extendDraftExpiration($request['id_user'], $request['expiration_date'], $request['extend_date'])){
                jsonResponse('The items were not extended');
            }
        }
        jsonResponse('Status changed successfully', 'success');

    }
}
