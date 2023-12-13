<?php

declare(strict_types=1);

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Share_statistic_Controller extends TinyMVC_Controller
{
    private $iconsShare = [
        'facebook' => [
            'icon' => 'facebook',
            'color' => 'facebook',
        ],
        'twitter' => [
            'icon' => 'twitter',
            'color' => 'twitter',
        ],
        'linkedin' => [
            'icon' => 'linkedin',
            'color' => 'linkedin',
        ],
        'pinterest' => [
            'icon' => 'pinterest',
            'color' => 'pinterest',
        ],
        'share this' => [
            'icon' => 'reply',
            'color' => 'blue',
        ],
        'email this' => [
            'icon' => 'envelope',
            'color' => 'blue',
        ],
    ];

    /**
     * Index page
     */
    public function index(): void
    {
        headerRedirect();
    }

    public function administration() {
        checkPermision('moderate_content');

        views(
            [
                'admin/header_view',
                'admin/share_statistic/index_view',
                'admin/footer_view',
            ]
        );
    }

    public function ajaxDtAdministration()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('moderate_content');

        try {
            $paginator = $this->getTableContent();

            jsonResponse('', 'success', [
                'sEcho'                => request()->request->getInt('sEcho', 0),
                'iTotalRecords'        => $paginator['total'] ?? 0,
                'iTotalDisplayRecords' => $paginator['total'] ?? 0,
                'aaData'               => $paginator['data'] ?? [],
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getTableContent(): array
    {
        $request = request();
        $parameters = $request->request;
        $limit = $parameters->getInt('iDisplayLength', 10);
        $skip = $parameters->getInt('iDisplayStart', 0);
        $page = $skip / $limit + 1;
        $with = [];
        $joins = ['Users'];

        $conditions = dtConditions($parameters->all(), [
            ['as' => 'created_from',     'key' => 'created_from',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'created_to',       'key' => 'created_to',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'type_sharing',     'key' => 'type_sharing',    'type' => 'cleaninput|trim'],
            ['as' => 'type',             'key' => 'type',            'type' => 'cleaninput|trim'],
        ]);

        /** @var Share_Statistic_Model $shareStatisticRepository */
        $shareStatisticRepository = model(Share_Statistic_Model::class);

        $shareStatisticTableAlias = $shareStatisticRepository->getTable();

        $order = array_column(dt_ordering($parameters->all(), [
            'dt_id'             => "`{$shareStatisticTableAlias}`.`id`",
            'dt_type'           => "`{$shareStatisticTableAlias}`.`type`",
            'dt_type_sharing'   => "`{$shareStatisticTableAlias}`.`type_sharing`",
            'dt_date'           => "`{$shareStatisticTableAlias}`.`date_created`",
        ]), 'direction', 'column');

        $shareStatistic = $shareStatisticRepository->get_share_statistcs(compact('conditions', 'with', 'joins', 'limit', 'skip', 'order'));
        $countShareStatistic = $shareStatisticRepository->get_count_share_statistcs(compact('conditions', 'joins'));

        $response = [
            'total' => $countShareStatistic,
            'data' => []
        ];

        if (null === $shareStatistic || $shareStatistic->isEmpty()) {
            return $response;
        }

        $companyIds = [];
        $itemsIds = [];
        foreach ($shareStatistic as $shareStatisticItem) {
            if ($shareStatisticItem['type'] === "company") {
                $companyIds[] = $shareStatisticItem['id_item'];
            } else {
                $itemsIds[] = $shareStatisticItem['id_item'];
            }
        }

        $detailCompany = [];
        if (!empty($companyIds)) {
            /** @var Elasticsearch_Company_Model $companyRepository */
            $companyRepository = model(Elasticsearch_Company_Model::class);
            $companyRepository->get_companies(['list_company_id' => implode(",", $companyIds), 'per_p' => 100]);
		    $detailCompany = arrayByKey($companyRepository->records, 'id_company');
        }

        $detailItems = [];
        if (!empty($itemsIds)) {
            /** @var Elasticsearch_Items_Model $itemsRepository */
            $itemsRepository = model(Elasticsearch_Items_Model::class);
            $itemsRepository->get_items(['list_item' => $itemsIds, 'per_p' => 100]);
		    $detailItems = arrayByKey($itemsRepository->items, 'id');
        }

        foreach ($shareStatistic as $shareStatisticItem) {
            $idItem = $shareStatisticItem['id_item'];
            $nameItem = "";
            $linkItem = "";

            if ($shareStatisticItem['type'] === "company" && isset($detailCompany[$idItem])) {
                $linkItem = getCompanyURL($detailCompany[$idItem]);
                $nameItem = $detailCompany[$idItem]['name_company'];
            } else if ($shareStatisticItem['type'] === "item" && isset($detailItems[$idItem])){
                $linkItem = makeItemUrl($idItem, $detailItems[$idItem]['title']);
                $nameItem = $detailItems[$idItem]['title'];
            }

            $linkItem = !empty($nameItem) ? "<a href=\"{$linkItem}\" target=\"_blank\">{$nameItem}</a>" : "";
            $response['data'][] = [
                'dt_id'             => $shareStatisticItem['id'],
                'dt_type'           => $shareStatisticItem['type'],
                'dt_type_sharing'   => "<i class=\"ep-icon ep-icon_{$this->iconsShare[$shareStatisticItem['type_sharing']]['icon']} txt-{$this->iconsShare[$shareStatisticItem['type_sharing']]['color']} mb-0\"></i>{$shareStatisticItem['type_sharing']}",
                'dt_item'           => $linkItem,
                'dt_user'           => (int)$shareStatisticItem['id_user'] === 0 ? "Guest" : $shareStatisticItem['fname'] . " " . $shareStatisticItem['lname'],
                'dt_date'           => $shareStatisticItem['date_created'],
            ];
        }

        return $response;
    }
}

// End of file share_statistic.php
// Location: /tinymvc/myapp/controllers/share_statistic.php
