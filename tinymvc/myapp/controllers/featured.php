<?php

use App\Common\Contracts\Bill\BillStatus;
use App\Common\Contracts\Bill\BillTypes;
use App\Common\Contracts\FeaturedProduct\FeaturedStatus;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Services\SearchProductsFastService;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Featured_Controller extends TinyMVC_Controller
{
    private const PRODUCTS_PER_SEARCH = 10;

    private const FEATURED_ITEMS_BILL_TYPE = 3;

	function my(){
		if (!logged_in()) {
            $this->session->setMessages(translate('systmess_error_should_be_logged_in'), 'errors');
			headerRedirect();
        }

		if (!have_right('feature_item')) {
			$this->session->setMessages(translate("systmess_error_page_permision"), 'errors');
			headerRedirect();
		}

        checkGroupExpire();

		$data = array(
			'title' => 'Featured',
			'item_statuses' => array(
				1 => 'New',
				2 => 'Active',
				3 => 'Featured',
				4 => 'Expired',
				5 => 'Ordered',
				6 => 'Sold'
            ),
			'counter_categories' => model(Items_Featured_Model::class)->get_cat_tree(array('seller' => privileged_user_id()))
        );

        $uri = uri()->uri_to_assoc();

		if (!empty($uri['featured_number'])) {
			$data['id_featured'] = (int) $uri['featured_number'];
		}

		if (!empty($uri['item'])) {
			$data['id_item'] = (int) $uri['item'];
		}

		if (!empty($uri['status'])) {
			$data['selected_status'] = cleanInput($uri['status']);
		}

		views()->assign($data);
        views()->display('new/header_view');
        views()->display('new/featured/my/index_view');
        views()->display('new/footer_view');
	}

	function ajax_featured_my_dt() {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('feature_item');

        $request = request()->request;

        $dtFilters = dtConditions($request->all(), [
            ['as' => 'keywords',            'key' => 'keywords',            'type'  => 'cleanInput'],
            ['as' => 'id_item',             'key' => 'id_item',             'type'  => 'toId'],
            ['as' => 'featured_number',     'key' => 'featured_number',     'type'  => 'toId'],
            ['as' => 'status',              'key' => 'status',              'type'  => fn ($filter) => in_array($filter, ['init', 'active', 'expired']) ? $filter : null],
            ['as' => 'expire_days',         'key' => 'status',              'type'  => fn ($filter) => 'expire_soon' === $filter ? (int) config('count_days_before_expire_featured_items_to_notify_seller', 5) : null],
            ['as' => 'paid',                'key' => 'paid',                'type'  => 'int'],
            ['as' => 'expire_start_date',   'key' => 'start_expire',        'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'expire_finish_date',  'key' => 'finish_expire',       'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'create_date_from',    'key' => 'create_date_from',    'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'create_date_to',      'key' => 'create_date_to',      'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'start_date_update',   'key' => 'start_last_update',   'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'finish_date_update',  'key' => 'finish_last_update',  'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'categories_list',     'key' => 'category',            'type' => function ($categoryId) {
                /** @var Category_Model $categoryModel */
                $categoryModel = model(Category_Model::class);
                $category = $categoryModel->get_category((int) $categoryId);

                return empty($category) ? null : implode(',', array_filter([$category['cat_childrens'], $categoryId]));
            }],
        ]);

        $orderBy = dtOrdering($request->all(), [
            'dt_item'        => 'i.title',
			'dt_update_date' => 'i_f.update_date',
			'dt_create_date' => 'i_f.create_date',
			'dt_end_date'    => 'i_f.end_date',
			'dt_status'      => 'i_f.status',
			'dt_price'       => 'i_f.price',
			'dt_paid'        => 'i_f.paid',
        ], fn ($ordering) => $ordering['column'] . '-' . $ordering['direction']);

        $featuredItemsParams = array_merge(
            $dtFilters,
            array_filter(
                [
                    'per_p'     => $request->getInt('iDisplayLength'),
                    'start'     => $request->getInt('iDisplayStart'),
                    'id_user'   => privileged_user_id(),
                    'sort_by'   => $orderBy ?: null,
                ]
            ),
        );

        /** @var Items_Featured_Model $featuredItemsModel */
        $featuredItemsModel = model(Items_Featured_Model::class);

        $featuredItems = $featuredItemsModel->get_items_featured($featuredItemsParams);
        $totalItems = $featuredItemsModel->get_items_featured_count($featuredItemsParams);

		$output = [
			'iTotalDisplayRecords'  => $totalItems,
			'iTotalRecords'         => $totalItems,
			'aaData'                => [],
			'sEcho'                 => $request->getInt('sEcho'),
        ];

		if (empty($featuredItems)) {
			jsonResponse('', 'success', $output);
        }

        $statuses = [
            'init' => [
                'title' => 'New',
            ],
            'active' => [
                'title' => 'Active',
            ],
            'expired' => [
                'title' => 'Expired',
            ],
        ];

        foreach ($featuredItems as $featuredItem) {
            $cancelFeaturedItemBtn = '';
            $categoryBreadcrumbs = [];
            $itemBreadcrumbs = json_decode('[' . $featuredItem['breadcrumbs'] . ']', true);
            if (!empty($itemBreadcrumbs)) {
                foreach ($itemBreadcrumbs as $bread) {
                    foreach ($bread as $catId => $catTitle) {
                        $categoryBreadcrumbs[] = '<a class="link" href="' . __SITE_URL . 'category/' . strForURL($catTitle) . '/' . $catId . '" target="_blank">' . $catTitle . '</a>';
                    }
                }
            }

            $paid = $featuredItem['paid'] ? 'Yes' : 'No';

            $reinit = '<a class="dropdown-item" href="'. __SITE_URL . 'billing/my/type/feature_item/featured/' . $featuredItem['id_featured'] . '" title="View bill status.">
							<i class="ep-icon ep-icon_dollar-circle"></i>
							<span>View bill status</span>
						</a>';

            if ($featuredItem['status'] != 'init' && $featuredItem['extend'] != 1 ) {
                if ($featuredItem['status'] == 'expired') {
                    $reinit = '<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to renew the featured status for this item?" data-callback="refeature_item" data-item="' . $featuredItem['id_featured'] . '" title="Renew the feature status">
                                    <i class="ep-icon ep-icon_refresh"></i>
                                    <span>Renew the feature status</span>
								</a>';
                }

                if ($featuredItem['status'] == 'active') {
                    $reinit = '<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to extend the active feature status for this item?" data-callback="refeature_item" data-item="' . $featuredItem['id_featured'] . '" title="Extend the active feature status">
                                    <i class="ep-icon ep-icon_hourglass-plus"></i>
                                    <span>Extend the active feature status</span>
								</a>';
                }
			}

            $itemImageLink = getDisplayImageLink(['{ID}' => $featuredItem['id_item'], '{FILE_NAME}' => $featuredItem['photo_name']], 'items.main', ['thumb_size' => 1]);
            $itemInfo = '';
            $expire = '<span class="txt-red">Expired</span>';

            if ('init' === $featuredItem['status']) {
                $expire = "&mdash;";

                /** @var User_Bills_Model $userBillsModel */
                $userBillsModel = model(User_Bills_Model::class);

                $itemBill = $userBillsModel->get_simple_bill([
                    'id_type_bill'  => self::FEATURED_ITEMS_BILL_TYPE,
                    'id_item'       => (int) $featuredItem['id_featured'],
                    'columns'       => 'id_item, status'
                ]);

                if ('init' === $itemBill['status']) {
                    $cancelFeaturedItemBtn = sprintf(
                        <<<CANCEL_BTN
                            <a class="dropdown-item confirm-dialog" data-message="%s" data-callback="cancelFeatureItem" data-item="{$featuredItem['id_featured']}" title="%s">
                                <i class="ep-icon ep-icon_remove-circle"></i>
                                <span>%s</span>
                            </a>
                        CANCEL_BTN,
                        translate('systmess_confirm_cancel_feature_item_process', null, true),
                        translate('cancel_feature_item_btn', null, true),
                        translate('cancel_feature_item_btn', null, true)
                    );

                    $itemInfo = sprintf(
                        <<<ITEM_INFO
                            <div class="main-data-table__item-action bg-gray">
                                <a href="%s" class="text" target="_blank">%s</a>
                            </div>
                        ITEM_INFO,
                        __SITE_URL . 'billing/my/type/feature_item/featured/' . $featuredItem['id_featured'],
                        translate('featured_items_need_to_pay_bill', null, true)
                    );
                } elseif ('paid' === $itemBill['status']) {
                    $itemInfo = sprintf(
                        <<<ITEM_INFO
                            <div class="main-data-table__item-action bg-gray">
                                <a href="%s" class="text" target="_blank">%s</a>
                            </div>
                        ITEM_INFO,
                        __SITE_URL . 'billing/my/type/feature_item/featured/' . $featuredItem['id_featured'],
                        translate('featured_item_waiting_to_confirm_bill', null, true)
                    );
                }
            } elseif ('active' === $featuredItem['status']) {
                $endDate =  \DateTimeImmutable::createFromFormat('Y-m-d', $featuredItem['end_date']);
                $endDate->modify('+1 day');
                $expire = '<span class="txt-green">' . $endDate->format('j M, Y') . '</span>';

                if ($endDate > (new \DateTime())) {
                    $itemInfo = sprintf(
                        <<<FEATURED_TILL
                            <div class="main-data-table__item-action bg-orange">
                                <div class="text">
                                    Featured till %s
                                </div>
                            </div>
                        FEATURED_TILL,
                        $endDate->format('j M, Y')
                    );
                }
            }

            $output['aaData'][] = [
                'dt_featured_number' => '<div class="flex-card relative-b">
                    <div class="main-data-table__item-actions">' . $itemInfo . '</div>
                    <div class="flex-card__fixed main-data-table__item-img main-data-table__item-img--h-auto image-card3 ml-5">
                        <span class="link">
                            ' . orderNumber($featuredItem['id_featured']) . '
                        </span>
                    </div>
                </div>',
                'dt_item' => '<div class="flex-card">
                                <div class="flex-card__fixed main-data-table__item-img main-data-table__item-img--h-auto image-card3">
                                    <span class="link">
										<img
											class="image"
											src="' . $itemImageLink . '"
											alt="' . cleanOutput($featuredItem['title']) . '"
										/>
                                    </span>
                                </div>
                                <div class="flex-card__float">
                                    <div class="main-data-table__item-ttl">
										<a class="display-ib link-black txt-medium" title="View item" href="' . __SITE_URL . 'item/' . strForURL($featuredItem['title'] . ' ' . $featuredItem['id_item']) . '" target="_blank">'
											. $featuredItem['title'].
										'</a>
                                    </div>
                                    <div class="main-data-table__item-ttl">' . orderNumber($featuredItem['id_item']) . '</div>
                                    <div class="links-black">' . implode('<span class=""> / </span>', $categoryBreadcrumbs) . '</div>
                                </div>
                              </div>',
                'dt_update_date' => getDateFormat($featuredItem['update_date']),
                'dt_end_date' => $expire,
                'dt_create_date' => getDateFormat($featuredItem['create_date']),
                'dt_status' => $statuses[$featuredItem['status']]['title'],
                'dt_price' => get_price($featuredItem['price']),
                'dt_paid' => $paid,
                'dt_actions' => <<<DT_ACTIONS
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ep-icon ep-icon_menu-circles"></i>
                        </a>
                        <div class="dropdown-menu">
                            {$reinit}
                            {$cancelFeaturedItemBtn}
                            <a class="dropdown-item d-none d-md-block d-lg-block d-xl-none call-function" data-callback="dataTableAllInfo" href="#" target="_blank">
                                <i class="ep-icon ep-icon_info-stroke"></i>
                                <span>All info</span>
                            </a>
                        </div>
                    </div>
                DT_ACTIONS,
            ];
        }

		jsonResponse('', 'success', $output);
	}

	function ajax_featured_operation(){
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		if(!have_right('feature_item'))
			jsonResponse(translate("systmess_error_rights_perform_this_action"));

        $request = request();

		switch (uri()->segment(3)) {
			case 'refeature_item':
				is_allowed("freq_allowed_user_operations");

				$this->load->model('User_Bills_Model', 'user_bills');
				$this->load->model('Items_Featured_Model', 'items_feat');
				$this->load->model('Items_Model', 'items');
				$this->load->model('Notify_Model', 'notify');
				$this->load->model('Category_Model', 'category');

				$id_featured = intVal($this->uri->segment(4));
				$item_detail = $this->items_feat->get_featured_item_id($id_featured);

				if(empty($item_detail)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!is_privileged('user', $item_detail['id_seller'], 'feature_item'))
					jsonResponse(translate('systmess_error_invalid_data'));

				if ($item_detail['extend'] == 1) {
					jsonResponse(translate('systmess_error_already_requested_to_extend_feature_item'), 'info');
                }

                $is_free_featured_items_process = (int) config('is_free_featured_items');
                if ($is_free_featured_items_process && 'active' === $item_detail['status']) {
                    jsonResponse(translate('systmess_info_extend_free_featured_item'), 'info');
                }

                $id_seller = privileged_user_id();

                $price = $is_free_featured_items_process ? 0 : $this->category->get_cat_feature_price($item_detail['id_cat']);

                if ($is_free_featured_items_process) {
                    $data = array(
                        'featured_from_date'    => (new \DateTime())->format('Y-m-d H:i:s'),
                        'end_date'              => date_plus((int) config('item_featured_default_period', 10)),
                        'status'                => 'active',
                        'price'                 => $price,
                        'paid'                  => 1,
                    );
                } else {
                    $data = array(
                        'price' => $price
                    );

                    if ($item_detail['status'] == 'active') {
                        $data['extend'] = 1;
                    } else {
                        $data['status'] = 'init';
                    }
                }

                $this->items->update_feature_request($id_featured, $data);

                if ($is_free_featured_items_process) {
                    model(Items_Model::class)->update_item(array('id' => $item_detail['id_item'], 'featured' => 1));
                    model(Elasticsearch_Items_Model::class)->index($item_detail['id_item']);
                    model(User_Statistic_Model::class)->set_users_statistic(array($id_seller => array('total_featured_items' => 1)));
                }

				$json_notice = json_encode(array(
                        'add_date' => getDateFormat(date('Y-m-d H:i:s')),
                        'add_by' => $this->session->lname . ' ' . $this->session->fname,
                        'notice' => $is_free_featured_items_process ? 'Free extend/renew featured item' : 'The "Feature item" request has been extended and is waiting for payment.'
                    ));

				$this->items_feat->set_notice($id_featured, $json_notice);

                $user_bill_data = array(
                    'bill_description' => 'This bill is for payment of feature item - '.$item_detail['title'].' request.', 'id_user' => $id_seller,
                    'id_type_bill' => 3,
                    'id_item' => $id_featured,
                    'balance' => $price,
                    'due_date' => date('Y-m-d', strtotime("+".config('item_featured_bill_period', 10)." days"))
                );

                $id_bill = $is_free_featured_items_process ? $this->user_bills->set_free_user_bill($user_bill_data) : $this->user_bills->set_user_bill($user_bill_data);

                if (!$id_bill) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                if ($is_free_featured_items_process) {

					$data_systmess = [
						'mess_code' => 'free_feature_item',
						'id_users'  => [$id_seller],
						'replace'   => [
							'[ITEM_TITLE]' => $item_detail['title'],
							'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_detail['title']) . '-' . $item_detail['id_item'],
							'[END_DATE]'   => getDateFormat($data['end_date'], null, 'j M, Y')
						],
						'systmess' => true
					];

                } else {

					$data_systmess = [
						'mess_code' => 'request_feature_item',
						'id_item'   => $id_bill,
						'id_users'  => [$id_seller],
						'replace'   => [
							'[ITEM_TITLE]' => cleanOutput($item_detail['title']),
							'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_detail['title'] . ' ' . $item_detail['id_item']),
							'[BILL_ID]'    => orderNumber($id_bill),
							'[BILL_LINK]'  => __SITE_URL . 'billing/my/bill/' . $id_bill,
							'[LINK]'       => __SITE_URL . 'billing/my'
						],
						'systmess' => true,
					];

                }

                $this->notify->send_notify($data_systmess);

                jsonResponse(translate('systmess_success_requested_feature_item'), 'success');
			    break;
            case 'find_products':
                $params = [];

                if(!empty($request->request->get('excludeItems'))){
                    $filteredItems = array_filter($request->request->get('excludeItems'), function($el){
                        return (int) $el;
                    });

                    $params['list_exclude_item'] = $filteredItems;
                }

                $this->find_products((int) privileged_user_id(), $request->request->get('search'), $params);

                break;
            case 'save_products':
                $idSeller = (int) privileged_user_id();
                $userInfo = model(User_Model::class)->getUser($idSeller);

                if (1 !== (int) $userInfo['free_featured_items']) {
                    jsonResponse(translate('general_no_permission_message'));
                }

                $filteredItems = array_filter($request->request->get('items'), function ($el) {
                    return (int) $el;
                });

                $maxItems = (int)config('max_free_featured_items_select');
                if (count($filteredItems) > $maxItems) {
                    jsonResponse(translate('general_no_permission_message'));
                }

                $itemsList = implode(',', $filteredItems);
                if (empty($itemsDetail = model(Items_Model::class)->get_items([
                    'list_item' => $itemsList,
                    'seller'    => $idSeller,
                    'draft'     => 0,
                ])
                )) {
                    jsonResponse(translate('general_no_permission_message'));
                }

                $existFeaturedItems = [];
                if((int) model(Items_Featured_Model::class)->get_items_featured_count(['id_item' => $filteredItems]) > 0){
                    $existFeaturedItems = model(Items_Featured_Model::class)->get_items_featured(['id_item' => $filteredItems, 'per_p' => $maxItems]);
                    $existFeaturedItems = arrayByKey($existFeaturedItems, 'id_item');
                }

                $featureItemsFreePeriod = config('feature_items_free_period');
                $endDate = date_plus($featureItemsFreePeriod);
                $createDate = date('Y-m-d');
                $insert = [];
                foreach ($itemsDetail as $itemsDetailItem) {

                    if(isset($existFeaturedItems[$itemsDetailItem['id']])){
                        $exitFeaturedItem =  $existFeaturedItems[$itemsDetailItem['id']];

                        $extendFromDate = isDateExpired($exitFeaturedItem['end_date']) ? false : $exitFeaturedItem['end_date'];
                        $endDateExitFeaturedItem = date_plus($featureItemsFreePeriod, 'days', $extendFromDate);
                        $jsonNotice = json_encode(
                            [
                                'add_date'  => (new \DateTime())->format('Y-m-d H:i:s'),
                                'add_by'    => session()->lname . ' ' . session()->fname,
                                'notice'    => 'The "Feature item" request has been extended till ' . getDateFormat($endDateExitFeaturedItem, null, 'j M, Y') . '.'
                            ]
                        );

                        $updateFeatured = [
                            'featured_from_date'    => (new \DateTime())->format('Y-m-d H:i:s'),
                            'end_date'              => $endDate,
                            'create_date' 	        => $createDate,
                            'notice'                => $jsonNotice . ', ' . $exitFeaturedItem['notice'],
                            'extend'                => 0,
                            'status'                => 'active',
                            'price'                 => 0,
                            'paid'                  => 1,
                        ];
                        model(Items_Featured_Model::class)->update_featured_item($exitFeaturedItem['id_featured'], $updateFeatured);
                    }else{
                        $insert[] = [
                            'featured_from_date'    => (new \DateTime())->format('Y-m-d H:i:s'),
                            'auto_extend'	        => 0,
                            'create_date' 	        => $createDate,
                            'end_date' 		        => $endDate,
                            'id_item' 		        => $itemsDetailItem['id'],
                            'status' 		        => 'active',
                            'extend' 		        => 0,
                            'price' 		        => 0,
                            'paid' 			        => 1,
                            'notice' 		        => json_encode([
                                'add_date' => (new \DateTime())->format('Y-m-d H:i:s'),
                                'add_by'   => session()->lname . ' ' . session()->fname,
                                'notice'   => translate('featured_items_notice_has_featured'),
                            ]),
                        ];
                    }
                }

                if(!empty($insert)){
                    if (!(int) model(Items_Featured_Model::class)->insert_feature_request_batch($insert)) {
                        jsonResponse(translate('featured_items_cannot_send_request'));
                    }
                }

                $featuredItems = model(Items_Featured_Model::class)->get_items_featured(['id_item' => $filteredItems, 'create_date' => $createDate, 'per_p' => $maxItems]);
                $featuredItems = arrayByKey($featuredItems, 'id_item');

                $userBillData = [
                    'bill_description' => '',
                    'id_user'          => $idSeller,
                    'id_type_bill'     => 3,
                    'id_item'          => 0,
                    'balance'          => 0,
                    'total_balance'    => 0,
                    'due_date'         => date('Y-m-d', strtotime("+{$featureItemsFreePeriod} days")),
                ];

                foreach ($itemsDetail as $itemsDetailItem) {
                    model(Items_Model::class)->update_item(['id' => $itemsDetailItem['id'], 'featured' => 1]);

                    $userBillData['bill_description'] = translate('featured_items_bill_payment', ['[TITLE]' => $itemsDetailItem['title']]);
                    $userBillData['id_item'] = $featuredItems[$itemsDetailItem['id']]['id_featured'];
                    model(User_Bills_Model::class)->set_free_user_bill($userBillData);
                }

                $totalFeatured = count($itemsDetail);
                model(Elasticsearch_Items_Model::class)->index($filteredItems);
                model(User_Statistic_Model::class)->set_users_statistic([$idSeller => ['total_featured_items' => $totalFeatured]]);

                $popupUsers = model(User_Popups_Model::class);
                $checkPopups = $popupUsers->findOneBy([
                    'columns'    => 'id, is_viewed',
                    'conditions' => [
                        'filter_by' => [
                            'id_user'    => session()->id,
                            'id_popup'   => 11,
                            'is_viewed'  => 0,
                        ],
                    ],
                ]);

                if (!empty($checkPopups)) {
                    // viewed free_featured_items
                    $popupUsers->updateOne($checkPopups['id'], [
                        'is_viewed' => 1,
                        'show_date' => new DateTimeImmutable(date('Y-m-d H:i:s')),
                    ]);
                }

                model(User_Model::class)->updateUserMain($idSeller, ['free_featured_items' => 2]);

                jsonResponse(translate('featured_items_save_products_success_mess', ['[DAYS]' => config('feature_items_free_period')]), 'success');

                break;
            case 'cancel':
                if (empty($featuredItemId = $request->request->getInt('item'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Featured_Products_Model $featuredProductsModel */
                $featuredProductsModel = model(Featured_Products_Model::class);

                if (empty($featuredItem = $featuredProductsModel->findOneBy([
                    'columns'   => [
                        "{$featuredProductsModel->getTable()}.*",
                    ],
                    'scopes' => [
                        'id'        => $featuredItemId,
                        'sellerId'  => privileged_user_id(),
                    ],
                    'joins' => [
                        'items'
                    ],
                ]))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (FeaturedStatus::INIT() !== $featuredItem['status']) {
                    jsonResponse(translate('systmess_error_cancel_feature_item_wrong_status'));
                }

                /** @var Bills_Model $billsModel */
                $billsModel = model(Bills_Model::class);

                $userBill = $billsModel->findOneBy([
                    'scopes'    => [
                        'itemId'    => $featuredItemId,
                        'status'    => BillStatus::INIT(),
                        'type'      => BillTypes::getId(BillTypes::FEATURE_ITEM()),
                    ],
                    'order'     => [
                        "`{$billsModel->getTable()}`.`id_bill`" => 'DESC',
                    ],
                ]);

                if (empty($userBill)) {
                    jsonResponse(translate('systmess_error_cancel_feature_item_wrong_bill_status'));
                }

                $billsModel->deleteOne($userBill['id_bill']);

                //If the product has never been featured before, then delete it from the table
                if (null === $featuredItem['featured_from_date']) {
                    $featuredProductsModel->deleteOne($featuredItemId);
                } else {
                    $featuredProductsModel->updateOne($featuredItemId, [
                        'status'    => $featuredItem['end_date'] < (new DateTime()) ? FeaturedStatus::EXPIRED() : FeaturedStatus::ACTIVE(),
                        'notice'    => array_merge(
                            [
                                [
                                    'add_date'  => (new \DateTime())->format('Y-m-d H:i:s'),
                                    'add_by'    => user_name_session(),
                                    'notice'    => 'The feature item process has been canceled.'
                                ]
                            ],
                            (array) $featuredItem['notice']
                        )
                    ]);
                }

                jsonResponse(translate('systmess_success_cancel_feature_item_process'), 'success');
            break;
		}
	}

    /**
     * Finds products by given search text.
     */
    private function find_products(int $user_id, ?string $search_text, array $paramsAdditional): void
    {
        try {
            $params = $paramsAdditional;

            if(
                false !== strpos($search_text, __SITE_URL . 'item/')
                && 0 < $itemId = id_from_link($search_text)
            ){
                $params['list_item'] = [$itemId];
            }else{
                $params['keywords'] = $search_text;
            }

            $paginator = (new SearchProductsFastService(static::PRODUCTS_PER_SEARCH))->findElasticProducts($user_id, $params);
            $delimiter = '<!-- delimiter -->';
            $products = array_filter(array_map(
                'trim',
                preg_split('/' . preg_quote($delimiter, '/') . '/', views()->fetch('new/sample_orders/products_list_view', array(
                    'products'  => arrayPull($paginator, 'data', array()),
                    'delimiter' => $delimiter,
                )))
            ));

            jsonResponse(null, 'success', array('data' => $products, 'paginator' => arrayCamelizeAssocKeys($paginator)));
        } catch (NotFoundException | OutOfBoundsException | OwnershipException $exception) {
            jsonResponse(throwableToMessage($exception, translate('systmess_error_permission_not_granted')));
        }
    }
}
