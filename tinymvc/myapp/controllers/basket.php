<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */

use App\Email\OutOfStockItem;
use App\Email\OutOfStockSoon;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Notifier\SystemChannel;
use ExportPortal\Bridge\Matrix\Notifier\Recipient\RoomType;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;

use App\DataProvider\IndexedProductDataProvider;
use App\Messenger\Message\Event\Product\ProductOutOfStockEvent;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class Basket_Controller extends TinyMVC_Controller
{
    /**
     * The notifier instance.
     */
    private NotifierInterface $notifier;

    private IndexedProductDataProvider $indexedProductDataProvider;

    private MessageBusInterface $eventBus;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->indexedProductDataProvider = $container->get(IndexedProductDataProvider::class);
        $this->notifier = $container->get(NotifierInterface::class);

        $messenger = $container->get(MessengerInterface::class);
        $this->eventBus = $messenger->bus('event.bus');
    }

	private function _load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('Basket_Model', 'basket');
		$this->load->model('Country_Model', 'country');
		$this->load->model('Items_Model', 'items');
		$this->load->model('User_Model', 'user');
	}

    function index(){
        $this->my();
    }

	function my(){
		if(!logged_in()){
			$this->session->setMessages(translate("systmess_error_should_be_logged"), 'errors');
			headerRedirect(__SITE_URL.'login');
		}

		if(!have_right('buy_item')){
			$this->session->setMessages(translate("systmess_error_page_permision"),'errors');
			headerRedirect(__SITE_URL);
		}

		$this->_load_main();
		$this->load->model('Company_Model', 'company');
		$this->load->model('Video_Tour_model', 'video_tour');

		$data['select_item'] = $this->uri->segment(3);
		$data['items'] = $this->basket->get_basket(array('user' => id_session(), 'by_seller' => true));

		if(!empty($data['items'])){
			$this->load->model('Shippers_Model', 'shippers');

			$sellers_list = array_keys($data['items']);
			$companies = $this->company->get_companies_simple(array('users_list' => implode(',', $sellers_list)));
			$data['companies'] = [];

			if (!empty($companies)) {
				$data['companies'] = array_map(
					function ($companiesItem) {
						$chatBtn = new ChatButton(['recipient' => $companiesItem['id_user'], 'recipientStatus' => $companiesItem['status']]);
						$companiesItem['btnChat'] = $chatBtn->button();
						return $companiesItem;
					},
					$companies
				);
			}

			$data['shipping_estimates'] = arrayByKey(model('shipping_estimates')->get_buyer_estimates(id_session(), $sellers_list), 'id_seller', true);
		}

		$data['video_tour'] = $this->video_tour->get_video_tour(array("page" => "basket/my", "user_group" => user_group_type()));

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $savedList = $itemsModel->get_items_saved(id_session());
        $data['savedItems'] = explode(',', $savedList);

        if(isset($sellers_list) && !empty($sellers_list))
        {
            foreach($sellers_list as $sellerId)
            {
                $items = $this->indexedProductDataProvider->getBacketItems($data['items'][$sellerId], 8);
                $itemsCount = count($items);

                if ($itemsCount > 4 && $itemsCount % 2 !== 0) {
                    array_pop($items);
                    $itemsCount--;
                }

                $data['similarItems'][$sellerId] = views()->fetch('new/item/similar_by_seller_view',
                [
                    'similarItems' => $items,
                    'itemsCount'   => $itemsCount,
                    'savedItems'   => $data['savedItems']
                ]);
            }
        }

		$this->view->assign($data);
		$this->view->display('new/header_view');
		$this->view->display('new/basket/basket_view');
		$this->view->display('new/footer_view');
	}

	public function ajax_basket_operation(){
		checkIsAjax();
		checkIsLoggedAjax();

		checkPermisionAjax('buy_item');

		$this->_load_main();
		$this->load->model('Orders_Model', 'orders');
		$this->load->model('Company_Model', 'company');

		$op = $this->uri->segment(3);
		switch($op){
			case 'delete_one':
				$id_basket_item = intval($_POST['id']);

				$id_user = id_session();

				$basket = $this->basket->get_basket_item($id_basket_item);

				if(empty($id_basket_item) || $id_user != $basket['id_user'])
					jsonResponse(translate("systmess_error_sended_data_not_valid"));

				if ($this->basket->delete_basket_item($id_basket_item)) {
                    if($this->basket->is_last_from_seller($id_user, $basket['id_seller'])){ // if is last basket from this seller - delete shipping estimates
                        model('shipping_estimates')->delete_estimates_from_basket((int) $id_user, (int) $basket['id_seller']);
                    }

					$this->session->__set('basket', (intVal($this->session->basket) - 1));

					$data['items'] = $this->basket->get_basket(array('user' => $id_user, 'by_seller' => true));
                    $items_total = $this->basket->count_basket_items($id_user);

                    if(!empty($data['items'])){
                        $data['companies'] = $this->company->get_companies_simple(array('users_list' => implode(',', array_keys($data['items']))));
                    }

                    $this->load->model('Items_Model', 'items');
                    $saved_list = $this->items->get_items_saved(id_session());
                    $data['saved_items'] = explode(',', $saved_list);

                    $basket_list = $this->view->fetch('new/nav_header/basket/basket_list_view', $data);

                    jsonResponse('Remove from the basket', 'success', array('basket' => $basket_list, 'items_total' => $items_total));

				} else {
					jsonResponse(translate("systmess_internal_server_error"));
				}
			break;
			case 'start_order':
				$validator_rules = array(
					array(
						'field' => 'port_country',
						'label' => 'Country',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'states',
						'label' => 'State / Region',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'port_city',
						'label' => 'City',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'zip',
						'label' => 'Zip',
						'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => '')
					),
					array(
						'field' => 'address',
						'label' => 'Address',
						'rules' => array('required' => '', 'max_len[255]' => '')
					),
					array(
						'field' => 'seller',
						'label' => 'Seller info',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_seller = (int) $_POST['seller'];
				$basket_params = array('id_seller' => $id_seller);

                $company_info = $this->company->get_company(array('id_user' => $id_seller));
                $sellerUserInfo = model(User_Model::class)->getSimpleUser($id_seller);
                if ($company_info['blocked']) {
                    jsonResponse(translate('systmess_error_company_blocked'));
                }

				$id_basket_item = (int) $_POST['id_basket_item'];
				if($id_basket_item > 0){
					$basket_params['id_basket_item'] = $id_basket_item;
				}

				$basket_items = $this->basket->get_basket_by_user(id_session(), $basket_params);
				if(empty($basket_items)){
					jsonResponse(translate('systmess_error_invalid_data') . 'Code #5');
				}

                $basket_items = array_column($basket_items, null, 'id_basket_item');

                /** @var Items_Variants_Model $itemsVariantsModel */
                $itemsVariantsModel = model(Items_Variants_Model::class);

                $itemsVariants = [];
                $itemsVariantsRaw = $itemsVariantsModel->findAllBy([
                    'conditions'    => [
                        'itemIds'   => array_column($basket_items, 'id_item')
                    ],
                    'with'          => ['propertyOptions'],
                ]);

                foreach ($itemsVariantsRaw as $itemVariant) {
                    $itemsVariants[$itemVariant['id_item']][$itemVariant['id']] = $itemVariant;
                }

				$order_log = array(
					'date' => date('m/d/Y h:i:s A'),
					'user' => 'Buyer',
					'message' => 'Order has been initiated.'
				);

				$location = array();
				$ship_to_country = (int) $_POST['port_country'];
				$ship_to_state = (int) $_POST['states'];
				$ship_to_city = (int) $_POST['port_city'];
				$ship_to_zip = cleanInput($_POST['zip']);
				$ship_to_address = cleanInput($_POST['address']);
				$location = model('country')->get_country_state_city($ship_to_city);
				$location[] = $ship_to_zip;
				$location[] = $ship_to_address;
				$ship_to = implode(', ', array_filter($location));

				$purchase_order = array(
					'shipping_to' => array(
						'country' => $ship_to_country,
						'state' => $ship_to_state,
						'city' => $ship_to_city,
						'zip' => $ship_to_zip,
						'address' => $ship_to_address,
						'full_address' => $ship_to
					)
				);

				$order = array(
					'id_buyer' => id_session(),
					'id_seller' => $id_seller,
					'order_summary' => json_encode($order_log),
					'ship_to' => $ship_to,
					'ship_to_country' => $ship_to_country,
					'ship_to_state' => $ship_to_state,
					'ship_to_city' => $ship_to_city,
					'ship_to_zip' => $ship_to_zip,
					'ship_to_address' => $ship_to_address,
					'status_countdown' => date_plus(2, 'days', false, true),
					'purchase_order_timeline' => json_encode(array(array(
						'date' => date('Y-m-d H:i:s'),
						'user' => 'Buyer',
						'message' => 'Order has been initiated.'
					)))
				);

				$titles_products = '';
				$quantityErrors = [];

				foreach ($basket_items as $basketItem) {
                    if (empty($basketItem['id_variant']) && isset($itemsVariants[$basketItem['id_item']])) {
                        jsonResponse(translate(
                            'system_message_error_basket_item_outdated',
                            [
                                '{{ITEM_PAGE}}' => sprintf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    makeItemUrl($basketItem['id_item'], $basketItem['title']),
                                    $basketItem['title']
                                )
                            ]
                        ));
                    }

                    $quantityInStock = (int) $basketItem['quantity_item'];
                    if (!empty($basketItem['id_variant'])) {
                        $quantityInStock = $itemsVariants[$basketItem['id_item']][$basketItem['id_variant']]['quantity'];
                    }

                    $maxQuantityAvailablePerOrder = (int) min($quantityInStock, (int) $basketItem['max_sale_q']);

                    if ((bool) (int) $basketItem['is_out_of_stock']) {
                        continue;
                    }

					if ((int) $basketItem['quantity'] > $maxQuantityAvailablePerOrder) {
                        $quantityErrors[] = translate(
                            'systmess_error_order_quantity_greater_than_available_quantity',
                            [
                                '{{AVAILABLE_QUANTITY}}'    => $maxQuantityAvailablePerOrder,
                                '{{ITEM_TITLE}}'            => cleanOutput($basketItem['title']),
                            ]
                        );

						continue;
					}

					if((int) $basketItem['quantity'] < (int) $basketItem['min_sale_q']){
						$quantityErrors[] = translate(
                            'systmess_error_order_quantity_less_than_available_quantity',
                            [
                                '{{MIN_QUANTITY_PER_ORDER}}'    => $basketItem['min_sale_q'],
                                '{{ITEM_TITLE}}'                => cleanOutput($basketItem['title']),
                            ]
                        );

						continue;
					}

					$snapshot = model(Item_Snapshot_Model::class)->get_last_item_snapshot($basketItem['id_item']);

                    $ordered_items[] = [
                        'basket_item'        => $basketItem['id_basket_item'],
                        'id_item'            => $basketItem['id_item'],
                        'id_snapshot'        => $snapshot['id_snapshot'],
                        'price_ordered'      => $basketItem['price_item'],
                        'quantity_ordered'   => $basketItem['quantity'],
                        'weight_ordered'     => $basketItem['weight'],
                        'detail_ordered'     => $basketItem['detail'],
                        'notice_ordered'     => $basketItem['notice'],
                        'insurance_shipping' => $basketItem['shipping_insurance']
                    ];

					$ordered_items_qty[$basketItem['id_basket_item']] = $quantityInStock;

					$order['price']  += $basketItem['quantity'] * $basketItem['price_item'];
					$order['final_price']  += $basketItem['quantity'] * $basketItem['price_item'];
					$order['weight'] += $basketItem['quantity'] * $basketItem['weight'];
					$order['comment'] .= $basketItem['notice'] . " \n" ;

					$titles_products .= $snapshot['title'].', ';
				}

				if(!empty($quantityErrors)){
					jsonResponse($quantityErrors);
				}

				if (empty($ordered_items)) {
					jsonResponse(
						translate(
							'translations_out_of_stock_cannot_add_to_order',
							[
                                '[[LINK_START]]' => '<a
                                                        class="confirm-dialog"
                                                        data-message="' . translate("systmess_confirm_get_email_items_available") . '"
                                                        data-callback="notifyOutOfStock"
                                                        data-resource="' . implode(',', array_column($basket_items, 'id_item')) . '"
                                                        data-href="' . __SITE_URL . 'items/ajax_item_operation/email_when_available">',
								'[[LINK_END]]'   => '</a>'
							]
						)
					);
				}

				$purchase_order['products_weight'] = $order['weight'];
				$order['purchase_order'] = json_encode($purchase_order);
				$id_order = $this->orders->insert_order($order);
				if(!$id_order){
					jsonResponse(translate("systmess_error_db_insert_error"));
				}

                /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
                $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                foreach ($ordered_items as $ordered_item) {
                    $isItemWithVariants = isset($itemsVariants[$ordered_item['id_item']]);
                    $newItemQuantity = (int) $ordered_items_qty[$ordered_item['basket_item']] - (int) $ordered_item['quantity_ordered'];
                    $basketId = $ordered_item['basket_item'];

                    $itemInfo = model(Items_Model::class)->get_item($ordered_item['id_item']);

                    if (!$isItemWithVariants) {
                        if($newItemQuantity == 0 || $newItemQuantity < $itemInfo['min_sale_q']){
                            $this->sendOutOfStockNotify($itemInfo, $sellerUserInfo, false);

                            $this->eventBus->dispatch(
                                new ProductOutOfStockEvent($itemInfo['id'])
                            );
                        } elseif((int) $newItemQuantity <= (int) $itemInfo['out_of_stock_quantity']) {
                            $this->sendOutOfStockNotify($itemInfo, $sellerUserInfo);
                        }
                    }

					$this->basket->delete_basket_item($ordered_item['basket_item']);
					unset($ordered_item['basket_item']);

					$ordered_item['id_order'] = $id_order;
					$this->orders->set_ordered_item($ordered_item);
					$sold_counter = $this->items->soldCounter($ordered_item['id_item']);

					//change quantity item
                    $itemUpdates = [
                        'total_sold'    => $sold_counter
                    ];

                    if (!$isItemWithVariants) {
                        $itemUpdates['quantity'] = $newItemQuantity;
                        $itemUpdates['is_out_of_stock'] = (int) ($newItemQuantity < $itemInfo['min_sale_q']);
                        $itemUpdates['date_out_of_stock'] = $newItemQuantity < $itemInfo['min_sale_q'] ? new DateTimeImmutable() : null;
                    } else {
                        $itemsVariantsModel->updateOne(
                            $basket_items[$basketId]['id_variant'],
                            [
                                'quantity' => $newItemQuantity,
                            ]
                        );
                    }

                    $productsModel->updateOne((int) $ordered_item['id_item'], $itemUpdates);
                    $elasticsearchItemsModel->index($ordered_item['id_item']);
				}

				$users_info = $this->user->getUsers(array('users_list' => id_session().','.$id_seller, 'company_info' => 1));
				$users_info = arrayByKey($users_info, 'idu');
				//add order search info
				$_order_number = orderNumber($id_order);
				$update_order['search_info'] = $_order_number;
				$update_order['search_info'] .= ', '.$users_info[$id_seller]['fname'].' '.$users_info[$id_seller]['lname'].', '.$users_info[$id_seller]['name_company'];
				$update_order['search_info'] .= ', '.$users_info[id_session()]['fname'].' '.$users_info[id_session()]['lname'];
				$update_order['search_info'] .= ', '.$order['ship_to'].', '.$titles_products.'$'.get_price($order['price'], false);
				$this->orders->change_order($id_order, $update_order);

				$count_items = !empty($ordered_items) ? count($ordered_items) : 0;
				$count_basket = (int) $this->session->basket;
				$this->session->__set('basket', ($count_basket - $count_items));

                $this->notifier->send(
                    (new SystemNotification('order_created', [
						'[ORDER_ID]'   => $_order_number,
						'[ORDER_LINK]' => sprintf('%sorder/my/order_number/%s', __SITE_URL, $id_order),
						'[LINK]'       => sprintf('%sorder/my', __SITE_URL),
                    ]))->channels([(string) SystemChannel::STORAGE(), (string) SystemChannel::MATRIX()]),
                    (new Recipient((int) $id_seller))->withRoomType(RoomType::CARGO())
                );

                if(count($basket_items) > count($ordered_items)){
                    jsonResponse(translate('translations_out_of_stock_order_started'), 'success', array('company' => $company_info['id_company'], 'id_order' => $id_order));
                }

				jsonResponse(
                    translate("systmess_success_order_created", ["[NUMBER]" => $_order_number]),
                    'success',
                    array('company' => $company_info['id_company'], 'id_order' => $id_order)
                );
			break;
			case 'check_item_quantity':
				$validator_rules = array(
					array(
						'field' => 'item',
						'label' => 'Item',
						'rules' => array('required' => '', 'integer' => '')
					),
					array(
						'field' => 'quantity',
						'label' => 'Quantity',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

                $request = request()->request;
                $userId = id_session();

                /** @var User_Basket_Model $userBasketModel */
                $userBasketModel = model(User_Basket_Model::class);

                if (
                    empty($basketId = $request->getInt('item'))
                    || empty($basket = $userBasketModel->findOne($basketId))
                    || $userId != $basket['id_user']
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                if (empty($item = $productsModel->findOne((int) $basket['id_item']))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Items_Variants_Model $itemsVariantsModel */
                $itemsVariantsModel = model(Items_Variants_Model::class);

                $itemVariants = array_column(
                    $itemsVariantsModel->findAllBy([
                        'conditions'    => [
                            'itemId'    => (int) $basket['id_item'],
                        ],
                        'with'  => ['propertyOptions'],
                    ]),
                    null,
                    'id'
                );

                //The case in which the product at the time of adding to the basket was without options, but later they appeared
                if (empty($basket['id_variant']) && !empty($itemVariants)) {
                    $itemUrl = makeItemUrl($basket['id_item'], $item['title']);

                    jsonResponse(
                        translate(
                            'system_message_error_basket_item_outdated',
                            [
                                '{{ITEM_PAGE}}' => sprintf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    $itemUrl,
                                    $item['title']
                                )
                            ]
                        )
                    );
                }

                //If the product variant added to the basket no longer exists
                if (!empty($basket['id_variant']) && !isset($itemVariants[$basket['id_variant']])) {
                    $itemUrl = makeItemUrl($basket['id_item'], $item['title']);

                    jsonResponse(
                        translate(
                            'system_message_error_basket_item_outdated',
                            [
                                '{{ITEM_PAGE}}' => sprintf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    $itemUrl,
                                    $item['title']
                                )
                            ]
                        )
                    );
                }

                $quantityInStock = (int) ($itemVariants[$basket['id_variant']]['quantity'] ?? $item['quantity']);
                $orderedQuantity = $request->getInt('quantity');

				$saleQuantityLimits = [
                    'max_quantity' => (int) $item['max_sale_q'],
					'min_quantity' => (int) $item['min_sale_q']
                ];

				if ($quantityInStock < $item['min_sale_q']) {
                    $userBasketModel->updateOne($basketId, ['quantity' => (int) $item['min_sale_q']]);

					jsonResponse(translate('systmess_warning_cant_buy_item_now'), 'warning', $saleQuantityLimits);
				}

				if ($orderedQuantity < $item['min_sale_q']) {
                    $userBasketModel->updateOne($basketId, ['quantity' => (int) $item['min_sale_q']]);

                    jsonResponse(
                        translate(
                            'systmess_warning_minimal_sale_quantity',
                            [
                                '[QUANTITY]'    => $item['min_sale_q'],
                                '[UNIT_NAME]'   => $item['unit_name'],
                            ]
                        ),
                        'warning',
                        $saleQuantityLimits
                    );
				}

				if ($orderedQuantity > $item['max_sale_q']) {
                    $userBasketModel->updateOne($basketId, ['quantity' => (int) $item['max_sale_q']]);

                    jsonResponse(
                        translate(
                            'systmess_warning_maximal_sale_quantity',
                            [
                                '[QUANTITY]'    => $item['max_sale_q'],
                                '[UNIT_NAME]'   => $item['unit_name'],
                            ]
                        ),
                        'warning',
                        $saleQuantityLimits
                    );
				}

                $userBasketModel->updateOne($basketId, ['quantity' => $orderedQuantity]);

				jsonResponse('', 'success', $saleQuantityLimits);
			break;
		}
	}

    /**
     * Sends emails and notifications to the seller
     * when item is out of stock or is soon to be out of stock
     *
     * @param $itemInfo - item data
     * @param $userInfo - seller data
     * @param $soonOutOfStock - is it soon out of stock (true) or already out of stock (false)
     */
    private function sendOutOfStockNotify($itemInfo, $userInfo, $soonOutOfStock = true)
    {
        /** @var Notify_Model $notifyModel*/
        $notifyModel = model(Notify_Model::class);

        $notifyModel->send_notify([
            'mess_code' => $soonOutOfStock ? 'item_out_of_stock_soon' : 'item_out_of_stock',
            'id_users'  => [$itemInfo['id_seller']] ,
            'replace'   => [
                '[ITEM_NAME]' => cleanOutput($itemInfo['title']),
                '[ITEM_LINK]' => __SITE_URL . 'item/' . strForURL($itemInfo['title']) . '-' . $itemInfo['id']
            ],
            'systmess'  => true
        ]);

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);

        if ($soonOutOfStock) {
            $mailer->send(
                (new OutOfStockSoon($userInfo['fname'] . " " . $userInfo['lname'], $itemInfo))
                    ->to(new RefAddress((string) $userInfo['idu'], new Address($userInfo['email'])))
                    ->subjectContext([
                        '[itemName]' => $itemInfo['title'],
                    ])
            );
        } else {
            $mailer->send(
                (new OutOfStockItem($userInfo['fname'] . " " . $userInfo['lname'], $itemInfo))
                    ->to(new RefAddress((string) $userInfo['idu'], new Address($userInfo['email'])))
                    ->subjectContext([
                        '[itemName]' => $itemInfo['title'],
                    ])
            );
        }
    }

	public function ajax_add_to_basket(){
		checkIsLoggedAjax();
        checkPermisionAjax('buy_item');

        $request = request()->request;

        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);

		if (
            empty($itemId = $request->getInt('item'))
            || empty($item = $productsModel->findOne($itemId))
            || (int) $item['draft']
        ) {
			jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var Items_Variants_Model $itemsVariantsModel */
        $itemsVariantsModel = model(Items_Variants_Model::class);
        $orderedVariant = $request->get('variant');
        $itemVariants = array_column(
            $itemsVariantsModel->findAllBy([
                'conditions'    => [
                    'itemId'    => $itemId,
                ],
                'with'          => [
                    'propertyOptions'
                ],
            ]),
            null,
            'id'
        );

        if (!empty($itemVariants) && !isset($itemVariants[$orderedVariant['id']])) {
            jsonResponse(translate('systmess_info_select_available_variation_item'), 'info');
        }

        if (!empty($orderedVariant) && (empty($itemVariants) || !is_array($orderedVariant['options']))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

		if (1 != $item['order_now']) {
			jsonResponse(translate("systmess_info_item_is_not_available"), 'info');
        }

		if (1 == $item['changed']) {
			jsonResponse(translate("systmess_warning_item_has_been_modified"), 'warning');
        }

        $itemVariant = empty($orderedVariant) ? null : $itemVariants[$orderedVariant['id']];
        $quantityInStock = (int) ($itemVariant['quantity'] ?? $item['quantity']);
        $orderedQuantity = $request->getInt('quantity');

		if ($orderedQuantity > $quantityInStock) {
			jsonResponse(translate("systmess_warning_requested_quantity_not_available"), 'warning');
        }

		if ($orderedQuantity < (int) $item['min_sale_q']){
            jsonResponse(translate("systmess_warning_not_enought_quantity_order", ["[QUANTITY]" => $item['min_sale_q']]), 'warning');
        }

        if ($orderedQuantity > (int) $item['max_sale_q']){
            jsonResponse(translate('systmess_error_add_to_basket_ordered_quantity_more_than_max_sale_quantity', ['{{MAX_SALE_QUANTITY}}' => $item['max_sale_q']]), 'warning');
        }

        $basketKeyParts = ["item:{$itemId}"];
        $itemDiscount = $itemVariant['discount'] ?? $item['discount'];

        if (!empty($itemDiscount)) {
            $detail[] = "Discount: {$itemDiscount}&#37;";
        }

        if (!empty($orderedVariant)) {
            $itemVariantOptions = array_column($itemVariant['property_options']->toArray(), null, 'id');

            foreach ((array) $orderedVariant['options'] as $optionId) {
                if (!isset($itemVariantOptions[$optionId])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $detail[] = "{$itemVariantOptions[$optionId]['propertyName']}: {$itemVariantOptions[$optionId]['name']}";
                $basketKeyParts[] = "{$itemVariantOptions[$optionId]['id_property']} - {$optionId}";
            }
        }

        $userId = id_session();
		$basketKey = sha1(implode('|', $basketKeyParts));
        $countBasketItems = (int) session()->get('basket', 0);

        /** @var User_Basket_Model $userBasketModel */
        $userBasketModel = model(User_Basket_Model::class);

        if (!empty($basketItem = $userBasketModel->findOneBy([
            'conditions'    => [
                'userId'    => (int) $userId,
                'basketKey' => (string) $basketKey,
            ],
        ]))) {
            $countBasketItems--;

            $userBasketModel->deleteOne((int) $basketItem['id_basket_item']);
        }

        $userBasketModel->insertOne([
            'detail'	        => implode(', ', $detail),
            'id_user'           => $userId,
            'id_item'           => $itemId,
            'quantity'          => $orderedQuantity,
            'id_variant'        => $orderedVariant['id'] ?? null,
            'price_item'        => empty($itemVariant) ? $item['final_price'] : $itemVariant['final_price'],
            'basket_item_key'   => $basketKey,
        ]);

        session()->__set('basket', ++$countBasketItems);

		jsonResponse(translate("systmess_success_added_your_basket"), 'success', ['count_basket' => $countBasketItems]);
	}

	function popup_forms() {
		checkIsAjax();
		checkIsLoggedAjaxModal();

		$id_user = privileged_user_id();

		$op = $this->uri->segment(3);
		switch ($op) {
			//for popup basket
			case 'show_basket_list':
				$this->load->model('Basket_Model', 'basket');
				$this->load->model('Company_Model', 'company');

				$data['items'] = $this->basket->get_basket(array('user' => id_session(), 'by_seller' => true));

				if(!empty($data['items'])){
					$data['companies'] = $this->company->get_companies_simple(array('users_list' => implode(',', array_keys($data['items']))));
				}

				$this->load->model('Items_Model', 'items');
				$saved_list = $this->items->get_items_saved(id_session());
				$data['saved_items'] = explode(',', $saved_list);
                $data['webpackData'] = "webpack" === request()->headers->get("X-Script-Mode", "legacy");

				$this->view->assign($data);
				$this->view->display("new/nav_header/basket/show_basket_list_view");
			break;
			case 'ship_to':
				checkPermisionAjaxModal('buy_item');

				$this->load->model('Country_Model', 'country');
				$this->load->model('User_Model', 'user');
				$this->load->model('Basket_Model', 'basket');

				$data['type'] = $this->uri->segment(4);
				if (!in_array($data['type'], array('all', 'one'))) {
					messageInModal(translate('systmess_error_invalid_data') . 'Code #4');
				}
				$id_on_segment = (int) $this->uri->segment(5);

				if($data['type'] == 'one'){
					$basket_item = model('basket')->get_basket_item($id_on_segment);
					if (empty($basket_item)) {
						messageInModal(translate('systmess_error_invalid_data') . 'Code #5');
					}

					$data['id_basket_item'] = $id_on_segment;
					$data['id_seller'] = $basket_item['id_seller'];
				} else{
					$data['id_seller'] = $id_on_segment;
				}

				$data['user_info'] = model('user')->getSimpleUser($id_user);
				$data['port_country'] = model('country')->fetch_port_country();

				if((int) $data['user_info']['country'] > 0){
					$data['states'] = model('country')->get_states($data['user_info']['country']);
					$data['city_selected'] = model('country')->get_city($data['user_info']['city']);
				}

				$this->view->assign($data);
				$this->view->display('new/basket/ship_view');
			break;
			case 'estimates':
                /**
                 * @deprecated 2.21.0, cant find ajax with this basket/popup_forms/estimates
                 */
				$this->load->model('Shippers_Model', 'shippers');
				$id_seller = intval($this->uri->segment(4));

				$data['shipping_estimates'] = model('shipping_estimates')->get_buyer_estimates($id_user, array($id_seller));

				if(empty($data['shipping_estimates']))
					messageInModal('There are no shipping estimate requests.', 'info');

				$this->view->assign($data);
                $this->view->display('new/basket/popup_estimates_view');
			break;
		}
	}
}
