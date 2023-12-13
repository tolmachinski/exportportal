<?php

use App\Common\Contracts\Bill\BillStatus;
use App\Common\Contracts\Bill\BillTypes;
use App\Common\Contracts\FeaturedProduct\FeaturedStatus;
use App\Common\Contracts\HighlightedProduct\HighlightedStatus;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Highlight_Controller extends TinyMVC_Controller
{
	function ajax_highlight_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('highlight_item');

        $request = request()->request;

		switch (uri()->segment(3)) {
			case 'rehighlight_item':
				is_allowed("freq_allowed_user_operations");

				$this->load->model('User_Bills_Model', 'user_bills');
				$this->load->model('Notify_Model', 'notify');
				$this->load->model('Items_Model', 'items');
				$this->load->model('Category_Model', 'category');
				$this->load->model('Items_Highlight_Model', 'item_high');

                $id_high = intval($this->uri->segment(4));
                $id_seller = privileged_user_id();
				$item_detail = $this->item_high->get_highlight_item_id($id_high);

				if(empty($item_detail)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!is_privileged('user', $item_detail['id_seller'], 'highlight_item'))
					jsonResponse(translate('systmess_error_invalid_data'));

				if ($item_detail['extend'] == 1) {
					jsonResponse(translate('systmess_error_already_requested_to_extend_highlight_item'), 'info');
                }

                $is_free_highlight_items_process = (int) config('is_free_highlight_items');
                if ($is_free_highlight_items_process && 'active' === $item_detail['status']) {
                    jsonResponse(translate('systmess_error_extend_free_highlighted_item'), 'info');
                }

                $price = $is_free_highlight_items_process ? 0 : $this->category->get_cat_highlight_price($item_detail['id_cat']);

                if ($is_free_highlight_items_process) {
                    $data = array(
                        'status'    => 'active',
                        'end_date'  => date_plus((int) config('item_highlight_default_period', 10)),
                        'paid'      => 1,
                        'price'     => $price,
                    );
                } else {
                    $data = array('price' => $price);

                    if ($item_detail['status'] == 'active') {
                        $data['extend'] = 1;
                    }

                    if ($item_detail['status'] != 'active') {
                        $data['status'] = 'init';
                    }
                }

                $this->item_high->update_highlight_item($id_high, $data);

                if ($is_free_highlight_items_process) {
                    model(Items_Model::class)->update_item(array('id' => $item_detail['id_item'], 'highlight' => 1));
                    model(Elasticsearch_Items_Model::class)->index($item_detail['id_item']);
                    model(User_Statistic_Model::class)->set_users_statistic(array($id_seller => array('total_highlight_items' => 1)));
                }

				$json_notice = json_encode(
								array(
									'add_date' => getDateFormat(date('Y-m-d H:i:s')),
									'add_by' => $this->session->lname . ' ' . $this->session->fname,
									'notice' =>  $is_free_highlight_items_process ? 'Free extend/renew highlighted item' : 'The "Highlight item" request has been extended and is waiting for payment.'
								)
							);
                $this->item_high->set_notice($id_high, $json_notice);

                $user_bill_data = array(
                    'bill_description' => 'This bill is for payment of highlight item - '.$item_detail['title'].' request.',
                    'id_user' => $id_seller,
                    'id_type_bill' => 4,
                    'id_item' => $id_high,
                    'balance' => $price,
                    'due_date' => date('Y-m-d', strtotime("+" . config('item_highlight_bill_period') . " days"))
                );

				$id_bill = $is_free_highlight_items_process ? $this->user_bills->set_free_user_bill($user_bill_data) : $this->user_bills->set_user_bill($user_bill_data);

                if (!$id_bill) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                if ($is_free_highlight_items_process) {

					$data_systmess = [
						'mess_code' => 'free_highlight_item',
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
						'mess_code' => 'request_highlight_item',
						'id_item'   => $id_bill,
						'id_users'  => [$id_seller],
						'replace'   => [
							'[ITEM_TITLE]' => cleanOutput($item_detail['title']),
							'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_detail['title'] . ' ' . $item_detail['id_item']),
							'[BILL_ID]'    => orderNumber($id_bill),
							'[BILL_LINK]'  => __SITE_URL . 'billing/my/bill/' . $id_bill,
							'[LINK]'       => __SITE_URL . 'billing/my'
						],
						'systmess' => true
					];

                }

                $this->notify->send_notify($data_systmess);

                if ($is_free_highlight_items_process) {
                    jsonResponse(translate('systmess_succes_free_highlight_item'), 'success');
                } else {
                    jsonResponse(translate('systmess_success_requested_highlight_item'), 'success');
                }

            break;
            case 'cancel':
                if (empty($highlightedItemId = $request->getInt('item'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Highlighted_Products_Model $highlightedProductsModel */
                $highlightedProductsModel = model(Highlighted_Products_Model::class);

                if (empty($highlightedItem = $highlightedProductsModel->findOneBy([
                    'columns'   => [
                        "{$highlightedProductsModel->getTable()}.*",
                    ],
                    'scopes' => [
                        'id'        => $highlightedItemId,
                        'sellerId'  => privileged_user_id(),
                    ],
                    'joins' => [
                        'items'
                    ],
                ]))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (HighlightedStatus::INIT() !== $highlightedItem['status']) {
                    jsonResponse(translate('systmess_error_cancel_highlight_item_wrong_status'));
                }

                /** @var Bills_Model $billsModel */
                $billsModel = model(Bills_Model::class);

                $userBill = $billsModel->findOneBy([
                    'scopes'    => [
                        'itemId'    => $highlightedItemId,
                        'status'    => BillStatus::INIT(),
                        'type'      => BillTypes::getId(BillTypes::HIGHLIGHT_ITEM()),
                    ],
                    'order'     => [
                        "`{$billsModel->getTable()}`.`id_bill`" => 'DESC',
                    ],
                ]);

                if (empty($userBill)) {
                    jsonResponse(translate('systmess_error_cancel_highlight_item_wrong_bill_status'));
                }

                $billsModel->deleteOne($userBill['id_bill']);

                //If the product has never been featured before, then delete it from the table
                if ($highlightedItem['end_date'] == (new DateTime('0000-00-00'))) {
                    $highlightedProductsModel->deleteOne($highlightedItemId);
                } else {
                    $highlightedProductsModel->updateOne($highlightedItemId, [
                        'status'    => $highlightedItem['end_date'] < (new DateTime()) ? HighlightedStatus::EXPIRED() : HighlightedStatus::ACTIVE(),
                        'notice'    => array_merge(
                            [
                                [
                                    'add_date'  => (new \DateTime())->format('Y-m-d H:i:s'),
                                    'add_by'    => user_name_session(),
                                    'notice'    => 'The highlight item process has been canceled.'
                                ]
                            ],
                            (array) $highlightedItem['notice']
                        )
                    ]);
                }

                jsonResponse(translate('systmess_success_cancel_highlight_item_process'), 'success');

            break;
		}
	}

	function my(){
		checkPermision('highlight_item');

		checkGroupExpire();

		$this->load->model('Items_Highlight_Model', 'items_hi');

		$data = array(
			'title' => 'Highlight',
			'item_statuses' => array(
				1 => 'New',
				2 => 'Active',
				3 => 'Featured',
				4 => 'Expired',
				5 => 'Ordered',
				6 => 'Sold'
			),
			'counter_categories' => $this->items_hi->get_cat_tree(array('seller' => privileged_user_id()))
		);

		$uri = $this->uri->uri_to_assoc();
		if(!empty($uri['highlight_number'])){
			$data['id_highlight'] = (int)$uri['highlight_number'];
		}
		if(!empty($uri['item'])){
			$data['id_item'] = (int)$uri['item'];
		}

		if(!empty($uri['status'])){
			$data['selected_status'] = cleanInput($uri['status']);
		}

        $this->view->assign($data);

        $this->view->display('new/header_view');
        $this->view->display('new/highlight/my/index_view');
        $this->view->display('new/footer_view');
	}

	function ajax_highlight_my_dt(){
		checkIsAjax();
		checkPermisionAjax('highlight_item');

        /** @var Items_Highlight_Model $highlightedItemsModel */
        $highlightedItemsModel = model(Items_Highlight_Model::class);

		/** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);

        $request = request()->request;

        $sortBy = dtOrdering($request->all(), [
            'dt_create_date' => 'ih.create_date',
			'dt_update_date' => 'ih.update_date',
			'dt_end_date'    => 'ih.end_date',
			'dt_price'       => 'ih.price',
			'dt_paid'        => 'ih.paid',
        ], fn ($ordering) => $ordering['column'] . '-' . $ordering['direction']);

        $dtFilters = dtConditions($request->all(), [
            ['as' => 'keywords',                'key' => 'keywords',            'type' => 'cleanInput|cut_str:200'],
            ['as' => 'visible',                 'key' => 'visible',             'type' => 'int'],
            ['as' => 'paid',                    'key' => 'paid',                'type' => 'int'],
            ['as' => 'expire_start_date',       'key' => 'start_expire',        'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'expire_finish_date',      'key' => 'finish_expire',       'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'create_date_from',        'key' => 'create_date_from',    'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'create_date_to',          'key' => 'create_date_to',      'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'start_date_update',       'key' => 'start_last_update',   'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'finish_date_update',      'key' => 'finish_last_update',  'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'id_item',                 'key' => 'id_item',             'type' => 'toId'],
            ['as' => 'highlight_number',        'key' => 'highlight_number',    'type' => 'toId'],
            ['as' => 'expire_days',     'key' => 'status',    'type' => function($status){
                return 'expire_soon' === $status ? (int) config('count_days_before_expire_highlighted_items_to_notify_seller', 5) : null;
            }],
            ['as' => 'status',     'key' => 'status',    'type' => function($status){
                return !empty($status) && 'expire_soon' !== $status ? cleanInput($status) : null;
            }],
            ['as' => 'categories_list',     'key' => 'category',    'type' => function($idCategory) use ($categoryModel){
                $idCategory = (int) $idCategory;

                if (empty($idCategory) || empty($category = $categoryModel->get_category($idCategory))) {
                    return null;
                }

                return empty($category['cat_childrens']) ? $idCategory : $category['cat_childrens'] . ',' . $idCategory;
            }],
        ]);

        $params = array_merge(
            [
                'sort_by'   => $sortBy ?: ['ih.create_date-desc'],
                'id_user'   => privileged_user_id(),
                'per_p'     => $request->getInt('iDisplayLength'),
                'start'     => $request->getInt('iDisplayStart'),
            ],
            $dtFilters
        );

		$highlightedItems = $highlightedItemsModel->get_items_highlight($params);
		$records_total = $highlightedItemsModel->get_items_highlight_count($params);

		$output = [
			"iTotalDisplayRecords"  => $records_total,
			"iTotalRecords"         => $records_total,
			"aaData"                => [],
			"sEcho"                 => $request->getInt('sEcho'),
        ];

		if (empty($highlightedItems)) {
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

        if (!empty($highlightedItems)) {
            $highlightedItemsByStatus = arrayByKey($highlightedItems, 'status', true);

            if (!empty($highlightedItemsByStatus[HighlightedStatus::INIT])) {
                /** @var Bills_Model $billsModel */
                $billsModel = model(Bills_Model::class);

                $highlightedItemsPaidBills = array_column(
                    $billsModel->findAllBy([
                        'scopes'    => [
                            'itemIds'  => array_column($highlightedItemsByStatus[HighlightedStatus::INIT], 'id_highlight'),
                            'status'    => BillStatus::PAID(),
                            'type'      => BillTypes::getId(BillTypes::HIGHLIGHT_ITEM()),
                        ],
                    ]),
                    null,
                    'id_item'
                );
            }
        }

        foreach ($highlightedItems as $item) {
            $cancelHighlightItem = '';
            $cat_breadcrumbs = array();
            $item_breadcrumbs = json_decode('[' . $item['breadcrumbs'] . ']', true);

            if (count($item_breadcrumbs)) {
                foreach ($item_breadcrumbs as $bread) {
                    foreach ($bread as $cat_id => $cat_title)
                        $cat_breadcrumbs[] = '<a class="link" href="'.__SITE_URL.'category/'. strForURL($cat_title) . '/'.$cat_id.'" target="_blank">' . $cat_title . '</a>';
                }
            }

            $paid = 'No';
            if ($item['paid']) {
                $paid = 'Yes';
            }

            if ('init' === $item['status']) {
                $expire = '<span class="txt-red">You should pay the bill</span>';

                if (isset($highlightedItemsPaidBills[$item['id_highlight']])) {
                    $expire = '<span class="txt-blue2">Paid, waiting confirmation</span>';
                }
            } else {
                $expire_time = strtotime($item['end_date'].' 23:59:59') - time();

                if($expire_time > 0){
                    $expire = "<span class='countdown-dt' data-expire='".($expire_time*1000)."'></span>";
                } else{
                    $expire = '<span class="txt-red">Expired</span>';
                }
            }

            $reinit =  '<a class="dropdown-item" href="'.__SITE_URL.'billing/my/type/highlight_item/highlight/' . $item['id_highlight'] . '" title="View bill status.">
							<i class="ep-icon ep-icon_dollar-circle"></i>
							<span>View bill status</span>
						</a>';

            if($item['status'] != 'init' && $item['extend'] != 1){
                if($item['status'] == 'expired')
                    $reinit = '<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to renew the highlighted status for this item?" data-callback="rehighlight_item" data-item="'.$item['id_highlight'].'" title="Renew the highlighted status">
								<i class="ep-icon ep-icon_refresh"></i>
								<span>Renew the highlighted status</span>
								</a>';

                if($item['status'] == 'active')
                    $reinit = '<a class="dropdown-item confirm-dialog" data-message="Are you sure you want to extend the active highlight status for this item?" data-callback="rehighlight_item" data-item="'.$item['id_highlight'].'" title="Extend the active highlight status">
								<i class="ep-icon ep-icon_hourglass-plus"></i>
								<span>Extend the active highlight status</span>
								</a>';
			}

            $item_img_link = getDisplayImageLink(array('{ID}' => $item['id_item'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));

            if (HighlightedStatus::INIT === $item['status']) {
               if (isset($item['id_item']) && HighlightedStatus::INIT === $item['status']) {
                   ($highlightedItemsPaidBills[$item['id_highlight']])
                        ?: $cancelHighlightItem = sprintf(
                            <<<CANCEL_BTN
                                    <a class="dropdown-item confirm-dialog" data-message="%s" data-callback="cancelHighlightItem" data-item="{$item['id_highlight']}" title="%s">
                                        <i class="ep-icon ep-icon_remove-circle"></i>
                                        <span>%s</span>
                                    </a>
                                CANCEL_BTN,
                            translate('systmess_confirm_cancel_highlight_item_process', null, true),
                            translate('cancel_highlight_item_btn', null, true),
                            translate('cancel_highlight_item_btn', null, true)
                        );

                   $itemInfo = sprintf(
                       <<<'ITEM_INFO'
                                        <div class="main-data-table__item-action bg-gray">
                                            <a href="%s" class="text" target="_blank">%s</a>
                                        </div>
                                    ITEM_INFO,
                       __SITE_URL . 'billing/my/type/highlight_item/highlight/' . $item['id_highlight'],
                       isset($highlightedItemsPaidBills[$item['id_highlight']])
                           ? translate('highlighted_item_waiting_to_confirm_bill', null, true)
                           : translate('highlighted_items_need_to_pay_bill', null, true)
                   );
               }

            } elseif (HighlightedStatus::ACTIVE === $item['status']) {
                $endDate =  \DateTimeImmutable::createFromFormat('Y-m-d', $item['end_date']);
                $endDate->modify('+1 day');
                $expire = '<span class="txt-green">' . $endDate->format('j M, Y') . '</span>';

                if ($endDate > new \DateTime()) {
                    $itemInfo = sprintf(<<<HIGHLIGHTED_TILL
                            <div class="main-data-table__item-action bg-blue2">
                                <div class="text">
                                    Highlighted till %s
                                </div>
                            </div>
                        HIGHLIGHTED_TILL,
                        $endDate->format('j M, Y')
                    );
                }
            }

            $output['aaData'][] = [
                'dt_highlight_number' => '<div class="flex-card relative-b">
                    <div class="main-data-table__item-actions">' . $itemInfo . '</div>
                    <div class="flex-card__fixed main-data-table__item-img main-data-table__item-img--h-auto image-card3 ml-5">
                        <span class="link">
                            ' . orderNumber($item['id_highlight']) . '
                        </span>
                    </div>
                </div>',
                'dt_item' => '<div class="flex-card">
								<div class="flex-card__fixed main-data-table__item-img main-data-table__item-img--h-auto image-card3">
									<span class="link">
										<img
											class="image"
											src="' . $item_img_link . '"
											alt="' . $item['title'] . '"
										/>
									</span>
								</div>
								<div class="flex-card__float">
									<div class="main-data-table__item-ttl">
									<a class="display-ib link-black txt-medium" title="View item" href="' . __SITE_URL . 'item/' . strForURL($item['title']).'-'.$item['id_item'] . '" target="_blank">'
										. $item['title'].
									'</a>
									</div>
									<div class="main-data-table__item-ttl">'.orderNumber($item['id_item']).'</div>
									<div class="links-black">'. implode('<span class=""> / </span>', $cat_breadcrumbs). '</div>
								</div>
							  </div>',
                'dt_update_date' => getDateFormat($item['update_date']),
                'dt_end_date' => $expire,
                'dt_create_date' => getDateFormat($item['create_date'], 'Y-m-d'),
                'dt_status' => $statuses[$item['status']]['title'],
                'dt_price' => '$' . $item['price'],
                'dt_paid' => $paid,
                'dt_actions' => <<<DT_ACTIONS
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ep-icon ep-icon_menu-circles"></i>
                        </a>
                        <div class="dropdown-menu">
                            {$reinit}
                            {$cancelHighlightItem}
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
}
