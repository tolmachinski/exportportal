<?php

use App\Common\Buttons\ChatButton;
use App\Filesystem\FilePathGenerator;
use App\Filesystem\ItemPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Psr\Log\LoggerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Po_Controller extends TinyMVC_Controller {

    private $breadcrumbs = array();
    private $po_logs = array(
        'po_init' => 'Producing Requests has been initiated.',
        'prototype_created' => 'The prototype has been created.',
        'prototype_init' => 'The prototype has been created on the base of the item: '
    );

    private $producing_request_statuses = array(
        'initiated' => array(
            'icon' => 'new txt-green',
            'icon_new' => 'new-stroke',
            'title' => 'New Requests',
            'title_color' => '',
            'description' => 'Waiting for the seller to make changes to the prototype and activate it.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
						'mandatory' => '<p>The buyer must <strong>wait</strong> until the <strong>seller activates</strong> the <strong>Prototype</strong>.</p>',
						'optional' 	=> '<p>The buyer can <strong>send more details</strong> about the <strong>Prototype</strong> to the seller using the <strong>Discuss</strong> button.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
                        'mandatory' => '<p>The seller has to <strong>Edit the Prototype’s</strong> details according to the <strong>buyer’s request</strong>.</p>
                                        <p>The seller has to <strong>Activate the Prototype</strong> to make it available to the buyer. The Producing Request’s <strong>status</strong> will be changed to <strong>In Process</strong>.</p>',
						'optional' 	=> '<p>The seller can <strong>negotiate with the buyer</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'po_processing' => array(
            'icon' => 'hourglass-processing txt-blue',
            'icon_new' => 'clock-stroke2',
            'title' => 'In process',
            'title_color' => '',
            'description' => 'Waiting for the buyer to confirm the prototype.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
                        'mandatory' => '<p>The buyer has to <strong>check the Prototype</strong> by clicking <strong>View the Prototype</strong> button.</p>
                                        <p>The buyer has to <strong>Confirm</strong> or <strong>Decline</strong> the Prototype. The Producing Request’s <strong>status</strong> will be changed <strong>depending on the selection</strong>.</p>',
						'optional' 	=> '<p>The buyer can <strong>send more details</strong> about the <strong>Prototype</strong> to the seller using the <strong>Discuss</strong> button.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
                        'optional' 	=> '<p>The seller can <strong>check the Prototype</strong> by clicking <strong>View the Prototype</strong> button.</p>
                                        <p>The seller can <strong>negotiate with the buyer</strong> using the <strong>Discuss</strong> button until they <strong>come to an agreement</strong>.</p>
                                        <p>The seller can <strong>Edit the Prototype’s</strong> details according to the <strong>buyer’s request</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'prototype_confirmed' => array(
            'icon' => 'thumbup txt-green',
            'icon_new' => 'ok-circle',
            'title' => 'Prototype confirmed',
            'title_color' => '',
            'description' => 'Waiting for the buyer to add shipping to address and starting the order.',
            'whats_next' => array(
                'buyer' => array(
					'text' => array(
                        'mandatory' => '<p>The buyer has to <strong>start a New Order</strong> using the <strong>Start Order</strong> button, and the Producing Request’s <strong>status</strong> will be changed to <strong>Order initiated</strong>.</p>'
					),
					'video' => ''
				),
				'seller' => array(
					'text' => array(
                        'mandatory' => '<p>The seller has to <strong>wait</strong> until the buyer <strong>starts the New Order</strong>.</p>'
					),
					'video' => ''
				)
            )
        ),
        'order_initiated' => array(
            'icon' => 'file-confirm txt-green',
            'icon_new' => 'file',
            'title' => 'Order initiated',
            'title_color' => 'txt-green',
            'description' => 'The order has been initiated based on this Production Request.'
        ),
        'declined' => array(
            'icon' => 'minus-circle txt-red',
            'icon_new' => 'remove-circle',
            'title' => 'Declined',
            'title_color' => 'txt-red',
            'description' => 'The Production Request has been declined.'
        ),
        'archived' => array(
            'icon' => 'archive txt-blue',
            'icon_new' => 'folder',
            'title' => 'Archived',
            'title_color' => 'txt-blue',
            'description' => 'The Production Request has been archived.'
        ),
        'po_number' => array(
            'icon' => 'magnifier txt-blue',
            'icon_new' => 'folder',
            'title' => 'Search result',
            'title_color' => 'txt-blue'
        ),
    );

    function index() {
        headerRedirect();
    }

    private function _load_main() {
        $this->load->model('PO_Model', 'po');
        $this->load->model('Items_Model', 'items');
        $this->load->model('Category_Model', 'category');
        $this->load->model('User_Model', 'user');
    }

    public function my() {
        if (!logged_in()) {
            $this->session->setMessages(translate("systmess_error_should_be_logged"), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }

        if (!(have_right('manage_seller_po') || have_right('buy_item'))) {
            $this->session->setMessages('This page does not exist.', 'errors');
            headerRedirect();
        }

        if (!i_have_company() && !have_right('buy_item')) {
            $this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
            headerRedirect();
        }

		checkGroupExpire();

        $this->_load_main();

        // GET SELECTED STATUS FROM URI - IF EXIST
		$uri = $this->uri->uri_to_assoc();

        // GET SELECTED STATUS FROM URI - IF EXIST
		if(isset($uri['status'])){
        	$data['status_select'] = $uri['status'];
		}

        // ARRAY WITH FULL STATUSES DETAILS
        $data['status_array'] = $this->producing_request_statuses;

        // IF THE STATUS WAS NOT SETED IN THE URI - DEFAULT STATUS IS "NEW"
        if (!isset($data['status_array'][$data['status_select']])){
            $data['status_select'] = 'all';
        }

        $id_user = privileged_user_id();

        if (have_right('buy_item')) {
            $conditions = array('buyer' => $id_user);
            if ($data['status_select'] != 'archived') {
                $conditions['status'] = $data['status_select'];
            } else {
                $conditions['state_buyer'] = 1;
            }

            $count_conditions = array('id_buyer' => $id_user);
            $archived_conditions = array('id_buyer' => $id_user, 'state_buyer' => 1);
        } else {
            $conditions = array('seller' => $id_user);
            if ($data['status_select'] != 'archived') {
                $conditions['status'] = $data['status_select'];
            } else {
                $conditions['state_seller'] = 1;
            }

            $count_conditions = array('id_seller' => $id_user);
            $archived_conditions = array('id_seller' => $id_user, 'state_seller' => 1);
        }

        // GET SELECTED PO NUMBER FROM URI - IF EXIST
		if(isset($uri['po_number'])){
        	$data['id_po'] = $conditions['po_number'] = toId($uri['po_number']);
			$data['status_select'] = 'po_number';
			$conditions['status'] = 'all';
		}

        global $tmvc;
        $data['po_per_page'] = $conditions['limit'] = $tmvc->my_config['user_po_per_page'];

        // GET po DETAIL
        $data['users_po'] = $this->po->get_po($conditions);

        // GET po STATUSES COUNTERS
        $data['statuses'] = arrayByKey($this->po->count_po_by_statuses($count_conditions), 'status');

        // COUNT ARCHIVED po
        $archived_counters = $this->po->count_po_by_statuses($archived_conditions);

        // SET DEFAULT ARCHIVED COUNTER
        $data['statuses']['archived'] = array('status' => 'archived', 'counter' => 0);

        // SET ARCHIVED COUNTER NEW DATA - IF EXIST
        if (!empty($archived_counters)) {
            foreach ($archived_counters as $status_couter)
            $data['statuses']['archived']['counter'] += $status_couter['counter'];
        }

        if ($data['status_select'] != 'archived')
            $data['status_select_count'] = $this->po->counter_by_conditions($conditions);
        else
            $data['status_select_count'] = $data['statuses']['archived']['counter'];

        $items_id = array();
        $users_id = array();

        if (!empty($data['users_po'])) {
            foreach ($data['users_po'] as $item) {
                $items_id[$item['id_item']] = $item['id_item'];

                if (have_right('buy_item')) {
                    $users_id[$item['id_seller']] = $item['id_seller'];
                } elseif (have_right('manage_seller_po')) {
                    $users_id[$item['id_buyer']] = $item['id_buyer'];
                }
            }
        }

        foreach ($data['status_array'] as $key => $statuses_item){
            $data['status_array'][$key]['counter'] = (int)$data['statuses'][$key]['counter'];
        }

        // GET ITEMS INFO FOR ALL po
        if (!empty($items_id)) {
            $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
            $data['products_list'] = arrayByKey($data['products_list'], 'id');
        }

        if (!empty($users_id)) {
            $data['users_list'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
            $data['users_list'] = arrayByKey($data['users_list'], 'idu');
            if(have_right('buy_item')){
                $this->load->model('Company_Model', 'company');
                $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
            }
        }

        $this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display("new/po/index_view");
        $this->view->display("new/footer_view");
    }

    public function popup_forms() {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->_load_main();
        $id_user = privileged_user_id();

        $op = $this->uri->segment(3);
        switch ($op) {
            // ADD PO MODAL FORM
            case 'add_po_form':
                checkPermisionAjaxModal('buy_item');

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                if (
                    empty($itemId = (int) uri()->segment(4))
                    || empty($item = $productsModel->findOne($itemId, ['with' => ['productUnitType']]))
                ) {
                    messageInModal(translate('systmess_error_item_does_not_exist'));
                }

                if (!$item['po']) {
                    messageInModal(translate('systmess_error_create_po_without_producing_request_option'));
                }

                if ($item['is_out_of_stock']) {
                    messageInModal(translate('translations_out_of_stock_system_message'), 'info');
                }

                $quantityInStock = $item['quantity'];

                if ($item['has_variants']) {
                    /** @var Items_Variants_Model $itemVariantsModel */
                    $itemVariantsModel = model(Items_Variants_Model::class);

                    if (empty($variant = (array) request()->request->get('variant'))) {
                        messageInModal(translate('systmess_info_fill_all_specific_item_options'));
                    }

                    if (empty($variant['id']) || empty($variant['options'])) {
                        messageInModal(translate('systmess_error_invalid_data'));
                    }

                    if (empty($itemVariant = $itemVariantsModel->findOneBy([
                        'conditions'    => [
                            'itemId'    => $itemId,
                            'id'        => (int) $variant['id'],
                        ],
                        'with'  => [
                            'propertyOptions',
                        ],
                    ]))) {
                        messageInModal(translate('systmess_info_select_available_variation_item'));
                    }

                    $allVariantOptions = array_column($itemVariant['property_options']->toArray(), null, 'id');
                    $usedVariantOptions = [];

                    foreach ((array) $variant['options'] as $optionId) {
                        if (!isset($allVariantOptions[$optionId])) {
                            messageInModal(translate('systmess_error_invalid_data'));
                        }

                        $usedVariantOptions[] = $allVariantOptions[$optionId];
                    }

                    $quantityInStock = $itemVariant['quantity'];
                }

                if ($quantityInStock < $item['min_sale_q']) {
                    messageInModal(translate('translations_out_of_stock_system_message'), 'info');
                }

                views(
                    'new/po/item_po_form_view',
                    [
                        'availableQuantity' => $quantityInStock,
                        'variantOptions'    => $usedVariantOptions ?? null,
                        'sold_counter'      => $this->items->soldCounter($itemId),
                        'itemVariant'       => $itemVariant ?? null,
                        'photo'             => $this->items->get_items_photo($itemId, 1),
                        'item'              => $item,
                    ]
                );

            break;
            // RESEND PO MODAL FORM
            case 'resend_po':
                if (!(have_right('manage_seller_po') || have_right('buy_item')))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                $id_po = intVal($this->uri->segment(4));
                $params = array();

                if (have_right('manage_seller_po'))
                    $params['seller'] = $id_user;
                elseif (have_right('buy_item'))
                    $params['buyer'] = $id_user;

                $data['po'] = $this->po->get_po_one($id_po, $params);

                if (empty($data['po']))
                    messageInModal(translate('systmess_error_invalid_data'));

                if(!is_privileged('user', $data['po']['id_seller'], true) && !is_my($data['po']['id_buyer']))
                    messageInModal(translate('systmess_error_invalid_data'));

                $status_finished = array('declined', 'order_initiated');
                if (in_array($data['po']['status'], $status_finished))
                    messageInModal(translate('systmess_error_resend_completed_po'), 'info');

                $this->view->assign($data);

                $this->view->display('new/po/resend_po_form_view');
            break;
            // ADD SHIP-TO ADDRESS AND CREATE THE ORDER
            case 'ship_to':
                // CHECK USER FOR BUYER RIGHTS
                if (!have_right('buy_item'))
                    messageInModal(translate("systmess_error_rights_perform_this_action"));

                // LOAD ADDITIONAL MODELS - COUNTRY_MODEL
                $this->load->model('Country_Model', 'country');

                // GET PO ID FROM URI SEGMENT
                $data['id_po'] = (int) $this->uri->segment(4);

                // GET PO DETAIL
                $params['buyer'] = $id_user;
                $po_info = $this->po->get_po_one($data['id_po'], $params);

                // CHECK IF EXIST PO
                if (empty($po_info))
                    messageInModal(translate('systmess_error_invalid_data'));

                // CHECK PO STATUS - MUST BE "ACCEPTED"
                if ($po_info['status'] != 'prototype_confirmed')
                    messageInModal(translate('systmess_error_po_not_completed_ship_to_address'), 'info');

                // GET ADDITIONAL USER DATA
                $data['user_info'] = $this->user->getSimpleUser($id_user);

                // GET COUNTRIES LIST
                $data['port_country'] = $this->country->fetch_port_country();

                if ($data['user_info']['country'])
                    $data['states'] = $this->country->get_states($data['user_info']['country']);

				$data['city_selected'] = $this->country->get_city($data['user_info']['city']);

                $this->view->display('new/po/ship_view', $data);
            break;
        }
    }

    public function ajax_po_operation() {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->_load_main();
        $this->load->model('Notify_Model', 'notify');

        $id_user = privileged_user_id();
        $op = $this->uri->segment(3);

        switch ($op) {
            // CHECK NEW PO FOR ADMINS
            case 'check_new':
                if (!have_right('manage_content'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $lastId = $_POST['lastId'];
                $po_count = $this->po->get_count_new_po($lastId);

                if ($po_count) {
                    $last_po_id = $this->po->get_po_last_id();
                    jsonResponse('', 'success', array('nr_new' => $po_count, 'lastId' => $last_po_id));
                } else
                    jsonResponse('Error: New Producing Requests doesn\'t exist');
            break;
            // CREATE THE PO
            case 'create_po':
                if (!have_right('buy_item')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $this->validator->set_rules([
                    [
                        'field' => 'quantity',
                        'label' => 'Quantity',
                        'rules' => ['required' => '', 'natural' => '', 'min[1]' => '']
                    ],
                    [
                        'field' => 'changes',
                        'label' => 'The necessary changes',
                        'rules' => ['required' => '', 'max_len[1000]' => '']
                    ],
                    [
                        'field' => 'comment',
                        'label' => 'Comment',
                        'rules' => ['max_len[1000]' => '']
                    ],
                    [
                        'field' => 'item',
                        'label' => 'Item informaton',
                        'rules' => ['required' => '', 'natural' => '']
                    ]
                ]);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Products_Model $productsModel */
                $productsModel = model(Products_Model::class);

                $request = request()->request;

                if (
                    empty($itemId = $request->getInt('item'))
                    || empty($item = $productsModel->findOne($itemId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!$item['po']) {
                    jsonResponse(translate('systmess_error_create_po_without_producing_request_option'));
                }

                if ($item['is_out_of_stock']) {
                    jsonResponse(translate('translations_out_of_stock_system_message'));
                }
                // CHECK FOR LAST ITEM SNAPSHOT
                $this->load->model('Item_Snapshot_Model', 'item_snapshot');
                $item_info = $this->item_snapshot->get_last_item_snapshot($itemId);
                if(empty($item_info)){
                    jsonResponse(translate('systmess_error_create_po_empty_snapshot'));
                }

                $details = [];
                $finalPrice = moneyToDecimal($item['final_price']);
                $discount = $item['discount'];
                $quantityInStock = $item['quantity'];

                if ($item['has_variants']) {
                    /** @var Items_Variants_Model $itemVariantsModel */
                    $itemVariantsModel = model(Items_Variants_Model::class);

                    if (empty($variant = (array) $request->get('variant'))) {
                        jsonResponse(translate('systmess_info_fill_all_specific_item_options'));
                    }

                    if (empty($variant['id']) || empty($variant['options'])) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    if (empty($itemVariant = $itemVariantsModel->findOneBy([
                        'conditions'    => [
                            'itemId'    => $itemId,
                            'id'        => (int) $variant['id'],
                        ],
                        'with'  => [
                            'propertyOptions',
                        ],
                    ]))) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    $allVariantOptions = array_column($itemVariant['property_options']->toArray(), null, 'id');
                    $discount = $itemVariant['discount'];
                    $finalPrice = moneyToDecimal($itemVariant['final_price']);
                    $quantityInStock = $itemVariant['quantity'];

                    foreach ((array) $variant['options'] as $optionId) {
                        if (!isset($allVariantOptions[$optionId])) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $details[] = $allVariantOptions[$optionId]['propertyName'] . ' : ' . $allVariantOptions[$optionId]['name'];
                    }
                }

                $poQuantity = $request->getInt('quantity');

                if ($poQuantity > $quantityInStock) {
                    jsonResponse(translate('systmess_error_po_necessary_quantity_not_available'));
                }

                if ($poQuantity < $item['min_sale_q']) {
                    jsonResponse(translate('systmess_error_producing_request_quantity_less_than_min_sale_quantity', ['{{MIN_SALE_QUANTITY}}' => $item['min_sale_q']]));
                }

                if (!empty($discount)) {
                    array_unshift($details, "Discount: {$discount}%");
                }

                $date_created = date('m/d/Y H:i:s');
                $id_seller = $item_info['id_seller'];

                $changes = cleanInput($_POST['changes']);
                $log = array(
                    'date' => $date_created,
                    'user' => 'Buyer',
                    'message' => $this->po_logs['po_init'],
                    'changes' => $changes
                );

                $this->load->library('Cleanhtml', 'clean');

                $comment = '';
                if(!empty($_POST['comment'])){
                    $this->clean->defaultTextarea();
                    $comment = $this->clean->sanitize($_POST['comment']);
                    $comment = cleanInput($comment);
                    $log['comment'] = $comment;
                }
                $insert_po = array(
                    'id_item' => $itemId,
                    'quantity' => $poQuantity,
                    'id_seller' => $id_seller,
                    'id_buyer' => $id_user,
					'detail_item' => implode(', ', $details),
                    'comment' => $comment,
                    'changes' => $changes,
                    'log' => json_encode($log),
                    'date' => formatDate($date_created, 'Y-m-d H:i:s'),
                );

                $id_po = $this->po->set_po($insert_po);

                // CREATE DEFAULT PROTOTYPE
                $this->load->model('Prototype_Model', 'prototype');

                $price = array(
                    'old_price' => $finalPrice,
                    'current_price' => '',
                );

                $insert_prototype = array(
                    'id_item' => $itemId,
                    'id_seller' => $insert_po['id_seller'],
                    'id_buyer' => $insert_po['id_buyer'],
                    'id_request' => $id_po,
                    'type_prototype' => 'po',
                    'title' => $item_info['title'],
                    'ship_from' => $item_info['country'],
                    'country_abr' => $item_info['country_abr'],
                    'date' => formatDate($date_created, 'Y-m-d H:i:s'),
                    'image' => $item_info['main_image'],
                    'quantity' => $insert_po['quantity'],
                    'hs_tariff_number' => $item_info['hs_tariff_number'],
                    'prototype_weight' => $item_info['item_weight'],
                    'prototype_length' => $item_info['item_length'],
                    'prototype_width' => $item_info['item_width'],
                    'prototype_height' => $item_info['item_height'],
                    'description' => $item_info['description'],
                    'attributes' => $item_info['aditional_info'],
                    'unit_name' => $item_info['unit_name'],
                    'price_history' => serialize($price),
                    'price' => $finalPrice,
                    'detail_item' => implode(', ', $details),
                    'log' => '{"date":"' . $date_created . '","message":"' . $this->po_logs['prototype_created'] . '"}'
                );

                $id_prototype = $this->prototype->set_prototype($insert_prototype);

                // UPDATE THE PO
                $update_po = array(
                    'price' => $insert_prototype['price'],
                    'id_prototype' => $id_prototype
                );

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                /** @var LoggerInterface */
                $logger = $this->getContainer()->get(LoggerInterface::class);

                try {
                    $publicDisk->write(
                        ItemPathGenerator::prototypeDraftUpload($id_prototype, $item_info['main_image']),
                        $publicDisk->read(ItemPathGenerator::snapshotDraftUpload($item_info['id_snapshot'], $item_info['main_image']))
                    );
                    $publicDisk->write(
                        ItemPathGenerator::prototypeDraftUpload($id_prototype, '/thumb_1_' . $item_info['main_image']),
                        $publicDisk->read(ItemPathGenerator::snapshotDraftUpload($item_info['id_snapshot'], $item_info['main_image']))
                    );
                } catch (UnableToReadFile | UnableToWriteFile $e) {
                    $logger->error($e->getMessage(), ['operation' => $e->operation(), 'exception' => $e]);
                } catch (\Throwable $e) {
                    $logger->error($e->getMessage(), ['exception' => $e]);
                    /** @var Pos_Model $poModel */
                    $poModel = model(Pos_Model::class);
                    $poModel->deleteOne($id_po);

                    /** @var Pos_Model $prototypeModel */
                    $prototypeModel = model(Prototypes_Model::class);
                    $prototypeModel->deleteOne($id_prototype);

                    try {
                        $publicDisk->deleteDirectory(ItemPathGenerator::prototypeDirectory($id_prototype));
                    } catch (\Throwable $th) {
                        //NOTHIND TO DO
                    }

                    jsonResponse(
                        throwableToMessage($e, translate('systmess_error_create_product_request')),
                        'error',
                        withDebugInformation([], ['exception' => throwableToArray($e)])
                    );
                }

                $users_id = array($id_seller, $id_user);
                $users_info = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username, users.email");
                $users_info = arrayByKey($users_info, 'idu');

                $this->load->model('Company_Model', 'company');
                $company_info = $this->company->get_seller_base_company($id_seller, "cb.id_company, cb.name_company");

                $po_number = orderNumber($id_po);
                $update_po['for_search'] = $po_number . ", " . $item_info['title'] . ", " . $users_info[$id_user]['username'] . ", " . $users_info[$id_seller]['username'].', '.$company_info['name_company'];

                $this->po->update_po($id_po, $update_po);

				// NOTIFY THE SELLER
				$data_systmess = [
					'mess_code' => 'po_new_to_seller',
					'id_item'   => $id_po,
					'id_users'  => [$id_seller], //array
					'type'      => 'po',
					'replace'   => [
						'[PO_ID]'   => $po_number,
						'[PO_LINK]' => __SITE_URL . 'po/my/po_number/' . $id_po,
						'[ITEM]'    => cleanOutput($item_info['title']),
						'[USER]'    => cleanOutput(user_name_session()),
						'[LINK]'    => __SITE_URL . 'po/my'
					],
					'systmess' => true
				];

                $this->notify->send_notify($data_systmess);

				// ADD NOTICE TO THE BUYER CALENDAR
				$data_calendar = [
					'mess_code' => 'po_new_to_buyer',
					'id_item'   => $id_po,
					'id_users'  => [$id_user],
					'replace'   => [
						'[PO_ID]'   => $po_number,
						'[PO_LINK]' => __SITE_URL . 'po/my/po_number/' . $id_po,
						'[ITEM]'    => cleanOutput($item_info['title']),
						'[LINK]'    => __SITE_URL . 'po/my'
					],
					'systmess' => false
				];

                $this->notify->send_notify($data_calendar);

                $this->load->model('User_Statistic_Model', 'statistic');
                $this->statistic->set_users_statistic(
                    array(
                        $insert_po['id_seller'] => array('po_received' => 1),
                        $insert_po['id_buyer'] => array('po_sent' => 1)
                    )
                );

                jsonResponse(translate('systmess_success_create_po'), 'success');
            break;
            // DISCUSS ABOUT PO - BEETWEN SELLER AND BUYER
            case 'resend_po':
                if (!have_right('manage_seller_po') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $validator = $this->validator;
                $validator_rules = array(
                    array(
                        'field' => 'message',
                        'label' => 'Message',
                        'rules' => array('required' => '', 'max_len[500]' => '')
                    ),
                    array(
                        'field' => 'po',
                        'label' => 'Producing Requests information',
                        'rules' => array('required' => '', 'natural' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if (!$validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_po = intVal($_POST['po']);
                $params = array();

                if (have_right('manage_seller_po')){
                    $params['seller'] = $id_user;
                } else{
                    $params['buyer'] = $id_user;
                }

                $po_info = $this->po->get_po_one($id_po, $params);

                if (empty($po_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                $status_finished = array('declined', 'order_initiated');
                if (in_array($po_info['status'], $status_finished))
                    jsonResponse(translate('systmess_error_resend_completed_po'));

                $log = array(
                    "date" => date('Y-m-d H:i:s'),
                    "message" => cleanInput($_POST['message']),
                );

                if (have_right('manage_seller_po')) {
                    $user_send = array($po_info['id_buyer']);
                    $log['user'] = 'Seller';
                    $receiver = 'buyer';
                } else {
                    $user_send = array($po_info['id_seller']);
                    $log['user'] = 'Buyer';
                    $receiver = 'seller';
                }

				$po_number = orderNumber($id_po);
                if ($this->po->change_po_log($id_po, json_encode($log))) {

					$data_systmess = [
						'mess_code' => 'po_changed',
						'id_users'  => $user_send,
						'replace'   => [
							'[PO_ID]'   => $po_number,
							'[PO_LINK]' => __SITE_URL . 'po/my/po_number/' . $id_po,
							'[LINK]'    => __SITE_URL . 'po/my'
						],
						'systmess' => true
					];

                    $this->notify->send_notify($data_systmess);

                    jsonResponse(translate('systmess_success_resend_po', ['{RECEIVER_USER_GROUP}' => $receiver]), 'success');
                } else
                    jsonResponse(translate('systmess_internal_server_error'));
            break;
            // DECLINE THE PO
            case 'declined_po':
                if (!have_right('manage_seller_po') && !have_right('buy_item'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $id_po = intVal($_POST['po']);
                $params = array();

                if (have_right('manage_seller_po'))
                    $params['seller'] = $id_user;
                else
                    $params['buyer'] = $id_user;

                $po_info = $this->po->get_po_one($id_po, $params);

                if (empty($po_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                if(!is_privileged('user', $po_info['id_seller'], true) && !is_my($po_info['id_buyer']))
                    jsonResponse(translate('systmess_error_invalid_data'));

                $status_finished = array('declined', 'order_initiated');
                if (in_array($po_info['status'], $status_finished))
                    jsonResponse(translate('systmess_error_declined_completed_po'));

                if (have_right('manage_seller_po')) {
                    $id_user_send = $po_info['id_buyer'];
                    $status = 'po_declined_seller';
                } else {
                    $id_user_send = $po_info['id_seller'];
                    $status = 'po_declined_buyer';
                }

                $log = array(
                    "date" => date('Y-m-d H:i:s'),
                    "message" => 'The Producing Requests has been declined.',
                );
                if ($this->po->update_po($id_po, array('status' => 'declined', 'log' => $po_info['log'] . ',' . json_encode($log)))) {
                    $po_number = orderNumber($id_po);
                    $this->load->model('User_Statistic_Model', 'statistic');
                    $this->statistic->set_users_statistic(array(
                        $po_info['id_seller'] => array('po_declined' => 1),
                        $po_info['id_buyer'] => array('po_declined' => 1)
                    ));

					$data_systmess = [
						'mess_code' => $status,
						'id_item'   => $id_po,
						'id_users'  => [$id_user_send],
						'replace'   => [
							'[PO_ID]'   => $po_number,
							'[PO_LINK]' => __SITE_URL . 'po/my/po_number/' . $id_po,
							'[LINK]'    => __SITE_URL . 'po/my'
						],
						'systmess' => true
					];

                    $this->notify->send_notify($data_systmess);

                    jsonResponse(translate('systmess_success_decline_po'), 'success', array('new_status' => 'declined'));
                } else
                    jsonResponse(translate('systmess_internal_server_error'));
            break;
            // INITIATE THE ORDER IN BASE OF PO
            case 'create_order':
                // CHECK USER RIGHTS - MUST BE BUYER
                checkPermisionAjax('buy_item');

                $this->load->model('Item_Snapshot_Model', 'snapshot');
                $this->load->model('Orders_Model', 'orders');
                $this->load->model('Country_Model', 'country');

                // VALIDATE POST DATA
                $validator_rules = array(
                    array(
                        'field' => 'id_po',
                        'label' => 'Producing Requests detail',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'port_country',
                        'label' => 'Country',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'port_city',
                        'label' => 'City',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'zip',
                        'label' => 'ZIP',
						'rules' => array('required' => '', 'zip_code' => '', 'max_len[20]' => '')
                    ),
                    array(
                        'field' => 'address',
                        'label' => 'Address',
                        'rules' => array('required' => '', 'max_len[250]' => '')
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $id_po = (int) $_POST['id_po'];
                $po_info = $this->po->get_po_one($id_po, array('buyer' => $id_user));

                if (empty($po_info)){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #4');
                }

                if ($po_info['status'] != 'prototype_confirmed'){
                    jsonResponse(translate('systmess_error_invalid_data') . 'Code #5');
                }

                $new_status_info = $this->orders->get_status_by_alias('new_order');
				if(empty($new_status_info)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

                // PREPARING SHIPPING TO LOCATION ADDRESS
				$ship_to_country = (int) $_POST['port_country'];
				$ship_to_state = (int) $_POST['states'];
				$ship_to_city = (int) $_POST['port_city'];
				$ship_to_zip = cleanInput($_POST['zip']);
				$ship_to_address = cleanInput($_POST['address']);
				$location = model('country')->get_country_state_city($ship_to_city);
				$location['zip'] = $ship_to_zip;
				$location['address'] = $ship_to_address;
                $ship_to = implode(', ', array_filter($location));

                $prototype_info = model('prototype')->get_prototype($po_info['id_prototype']);

                $total_order_weight = $prototype_info['quantity'] * $prototype_info['prototype_weight'];
                $purchase_order = array(
					'shipping_to' => array(
						'country' => $ship_to_country,
						'state' => $ship_to_state,
						'city' => $ship_to_city,
						'zip' => $ship_to_zip,
						'address' => $ship_to_address,
						'full_address' => $ship_to
					),
                    'products_weight' => $total_order_weight
				);

                $po_number = orderNumber($id_po);
                $order_log = array(
                    'date' => date('m/d/Y h:i:s A'),
                    'user' => 'Buyer',
                    'message' => "The order has been initiated on the base of Producing Request: {$po_number}."
                );

                $order = array(
                    'id_buyer' => $id_user,
                    'id_seller' => $prototype_info['id_seller'],
                    'id_by_type' => $id_po,
                    'price' => $prototype_info['price'] * $prototype_info['quantity'],
                    'final_price' => $prototype_info['price'] * $prototype_info['quantity'],
                    'weight' => $total_order_weight,
                    'comment' => 'The order has been initiated on the base of Producing Requests: ' .$po_number. '.',
                    'order_type' => 'po',
                    'order_summary' => json_encode($order_log),
                    'ship_to_country' => $ship_to_country,
					'ship_to_state' => $ship_to_state,
					'ship_to_city' => $ship_to_city,
					'ship_to_zip' => $ship_to_zip,
					'ship_to_address' => $ship_to_address,
                    'ship_to' => $ship_to,
                    'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
                    'purchase_order' => json_encode($purchase_order),
					'purchase_order_timeline' => json_encode(array(array(
                        'date' => date('Y-m-d H:i:s'),
                        'user' => 'Buyer',
                        'message' => "The order has been initiated on the base of Producing Request: {$po_number}."
                    )))
                );

                $id_order = $this->orders->insert_order($order);
                $order_number = orderNumber($id_order);

                // PREPARE SEARCH INFO
                $users = $this->user->getSimpleUsers(implode(',', array($po_info['id_buyer'], $po_info['id_seller'])), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                $this->load->model('Company_Model', 'company');
                $company = $this->company->get_seller_base_company($po_info['id_seller'], 'cb.name_company');
                $search_info = $order_number . ', ' . $users[0]['username'] . ', ' . $users[1]['username'] .', '. $company['name_company'] .', '.$prototype_info['title'];

                // UPDATE SEARCH INFO - ADD ORDER ID
                $this->orders->change_order($id_order, array('search_info' => $search_info));

                $insert_snapshot = array(
                    'id_item' => $prototype_info['id_item'],
                    'id_seller' => $prototype_info['id_seller'],
                    'additional_id' => $prototype_info['id_prototype'],
                    'title' => $prototype_info['title'],
                    'hs_tariff_number' => $prototype_info['hs_tariff_number'],
                    'country' => $prototype_info['ship_from'],
                    'country_abr' => $prototype_info['country_abr'],
                    'price' => $prototype_info['price'],
                    'item_weight' => $prototype_info['prototype_weight'],
                    'item_length' => $prototype_info['prototype_length'],
                    'item_width' => $prototype_info['prototype_width'],
                    'item_height' => $prototype_info['prototype_height'],
                    'currency' => '$',
                    'description' => $prototype_info['description'],
                    'aditional_info' => $prototype_info['changes'],
                    'main_image' => $prototype_info['image'],
                    'unit_name' => $prototype_info['unit_name'],
                    'type' => 'prototype'
                );

				$this->snapshot->update_item_snapshots($prototype_info['id_item'], 'prototype', array('is_last_snapshot' => 0));
                $id_snapshot = $this->snapshot->insert_item_snapshot($insert_snapshot);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $prototypeDirectory = ItemPathGenerator::prototypeDraftUpload($prototype_info['id_prototype'], $prototype_info['image']);

                if ($publicDisk->fileExists($prototypeDirectory)) {
                    try {
                        $publicDisk->write(
                            ItemPathGenerator::snapshotDraftUpload($id_snapshot, $prototype_info['image']),
                            $publicDisk->read($prototypeDirectory)
                        );
                        $publicDisk->write(
                            ItemPathGenerator::snapshotDraftUpload($id_snapshot, '/thumb_1_' . $prototype_info['image']),
                            $publicDisk->read($prototypeDirectory)
                        );

                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_initiate_order'));
                    }
                }

                $ordered_item = array(
                    'id_order' => $id_order,
                    'id_item' => $prototype_info['id_item'],
                    'id_snapshot' => $id_snapshot,
                    'quantity_ordered' => $prototype_info['quantity'],
                    'price_ordered' => $prototype_info['price'],
                    'weight_ordered' => $prototype_info['prototype_weight'],
                    'detail_ordered' => 'The order has been initiated on the base of Producing Requests: ' .$po_number. '.'
                );
                $this->orders->set_ordered_item($ordered_item);

                model(User_Statistic_Model::class)->set_users_statistic(
                    array(
                        $po_info['id_seller'] => array('po_accepted' => 1),
                        $po_info['id_buyer'] => array('po_accepted' => 1)
                    )
                );

                $log = array(
                    "date" => date('Y-m-d H:i:s'),
                    'user' => 'Buyer',
                    "message" => 'The order '.$order_number.' has been initiated.',
                );

                $this->po->update_po($id_po, array('status' => 'order_initiated', 'id_order' => $id_order));
                $this->po->change_po_log($id_po, json_encode($log));

				$data_systmess = [
					'mess_code' => 'po_confirmed',
					'id_item'   => $id_po,
					'id_users'  => [$id_user, $po_info['id_seller']],
					'replace'   => [
						'[PO_ID]'   => $po_number,
						'[PO_LINK]' => __SITE_URL . 'po/my/po_number/' . $id_po,
						'[LINK]'    => __SITE_URL . 'po/my'
					],
					'systmess' => true
				];

                $this->notify->send_notify($data_systmess);

                jsonResponse(translate('systmess_success_po_create_order', ['{ORDER_NUMBER}' => orderNumber($id_order)]), 'success', array('order' => $id_order));

            break;
            // REMOVE THE PO
            case 'remove_po':
                // CHECK RIGHTS - ONLY BUYER, SELLER AND STAFF USERS
                if (!have_right('buy_item') && !have_right('manage_seller_po'))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                $id_po = intVal($_POST['po']);
                $po_info = $this->po->get_po_one($id_po);

                if (empty($po_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                // CHECK IS UDER IS PRIVILEGED TO CHANGE THIS PO
                if (!is_privileged('user', $po_info['id_seller'], true) && !is_my($po_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                //PERMIT TO REMOVE PO ONLY IF STATUS IS DECLINED OR PO STATE IS ARCHIVED
                if (have_right('buy_item')) {
                    $user_state = 'state_buyer';
                } else {
                    $user_state = 'state_seller';
                }
                if (!in_array($po_info['status'], array('order_initiated', 'declined')) && $po_info[$user_state] != 1)
                    jsonResponse(translate('systmess_error_remove_po_wrong_status'), 'info');

                // UPDATE PO BY USER TYPE
                $update_po = array($user_state => 2);

                if ($this->po->update_po($id_po, $update_po)) {
                    $status = 'declined';
                    if ($po_info['status'] == 'order_initiated')
                        $status = 'accepted';

                    if (have_right('buy_item')) {
                        $statistic = array(
                            $po_info['id_buyer'] => array('po_sent' => -1, 'po_' . $status => -1)
                        );
                    } elseif(have_right('manage_seller_po')){
                        $statistic = array(
                            $po_info['id_seller'] => array('po_received' => -1, 'po_' . $status => -1)
                        );
                    }

                    if(!empty($statistic)){
                        $this->load->model('User_Statistic_Model', 'statistic');
                        $this->statistic->set_users_statistic($statistic);
                    }

                    jsonResponse(translate('systmess_success_remove_po'), 'success');
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
            // ARCHIVE THE PO
            case 'archived_po':
                if (!have_right('manage_seller_po') && !have_right('buy_item')) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $id_po = intVal($_POST['po']);
                $po_info = $this->po->get_po_one($id_po);

                if (empty($po_info))
                    jsonResponse(translate('systmess_error_invalid_data'));

                if (!is_privileged('user', $po_info['id_seller'], true) && !is_my($po_info['id_buyer']))
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));

                if ($po_info['status'] != 'order_initiated')
                    jsonResponse(translate('systmess_error_archived_not_finished_po'));

                if (have_right('manage_seller_po')) {
                    $user_state = 'state_seller';
                }elseif (have_right('buy_item')) {
                    $user_state = 'state_buyer';
                }
                if ($po_info[$user_state])
                    jsonResponse(translate('systmess_error_archive_po_already_archived'));

                $archived_status = array($user_state => 1);

                if ($this->po->update_po($id_po, $archived_status)) {
                    jsonResponse(translate('systmess_success_archived_po'), 'success', array('new_status' => 'archived'));
                } else {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            break;
        }
    }

    function ajax_po_info() {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->_load_main();
        $id_user = privileged_user_id();

        switch ($_POST['type']) {
            // PO DETAILS
            case 'po':
                checkPermisionAjax('manage_seller_po,buy_item');

                //region Check if Producing Request exists and User permition
                $id_producing_request = (int) $_POST['po'];
                $data['po'] = $this->po->get_po_one($id_producing_request);

                if (empty($data['po']) || !in_array(privileged_user_id(), array((int) $data['po']['id_seller'], (int) $data['po']['id_buyer']))){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                //endregion Check if Producing Request exists and User permition

                //region Prototype
                $data['prototype'] = model('prototype')->get_prototype((int) $data['po']['id_prototype']);
                //endregion Prototype

                //region Prepare Producing Request timeline
                $data['po']['log'] = array_reverse(with(json_decode("[{$data['po']['log']}]", true), function($log) {
                    return is_array($log) ? $log: array();
                }));
                //endregion Prepare Producing Request timeline

                //region user information
                if(have_right('buy_item')){
                    $data['seller_info'] = model('company')->get_seller_base_company(
                        (int) $data['po']['id_seller'],
                        "cb.id_company, cb.name_company, cb.index_name, cb.id_user, cb.type_company, cb.logo_company, u.user_group",
                        true
                    );
                } else{
                    $data['buyer_info'] = $this->user->getSimpleUser((int) $data['po']['id_buyer'], "users.idu, CONCAT(users.fname, ' ', users.lname) as user_name, users.user_group, users.user_photo");
                }
                //endregion user information

                //region Prepare Producing Request status details
                $data['producing_request_status'] = $this->producing_request_statuses[$data['po']['status']];
                $data['producing_request_status_user'] = have_right('buy_item') ? 'buyer' : 'seller';
                //endregion Prepare Producing Request status details

                if(have_right('buy_item')){
					$btnChatSeller = new ChatButton(['recipient' => $data['po']['id_seller'], 'recipientStatus' => 'active', 'module' => 11, 'item' => $data['po']['id_po']], ['text' => 'Chat with seller']);
					$data['btnChatSeller'] = $btnChatSeller->button();

					$btnChatSeller2 = new ChatButton(['recipient' => $data['po']['id_seller'], 'recipientStatus' => 'active', 'module' => 11, 'item' => $data['po']['id_po']], ['classes' => 'link-ajax p-0 w-auto display-ib dropdown-item', 'icon' => '', 'text' => 'Chat with seller']);
					$data['btnChatSeller2'] = $btnChatSeller2->button();
				}else{
					$btnChatBuyer = new ChatButton(['recipient' => $data['po']['id_buyer'], 'recipientStatus' => 'active', 'module' => 11, 'item' => $data['po']['id_po']], ['text' => 'Chat with buyer']);
					$data['btnChatBuyer'] = $btnChatBuyer->button();

					$btnChatBuyer2 = new ChatButton(['recipient' => $data['po']['id_buyer'], 'recipientStatus' => 'active', 'module' => 11, 'item' => $data['po']['id_po']], ['classes' => 'link-ajax p-0 w-auto display-ib dropdown-item', 'icon' => '', 'text' => 'Chat with buyer']);
					$data['btnChatBuyer2'] = $btnChatBuyer2->button();
				}

                $content = $this->view->fetch('new/po/po_detail_view', $data);

                jsonResponse('', 'success', array('content' => $content));
            break;
            // PO LIST BY STATUSES
            case 'po_list':
                checkPermisionAjax('manage_seller_po,buy_item');

                $statuses = array('all', 'initiated', 'po_processing', 'prototype_confirmed', 'order_initiated', 'declined', 'archived');
                if (!in_array($_POST['status'], $statuses)) {
                    jsonResponse('Error: The status you selected is not correct.');
                }

                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $per_page = $tmvc->my_config['user_po_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                if (have_right('buy_item')) {
                    $conditions = array('buyer' => $id_user);
                    if ($_POST['status'] != 'archived') {
                        $conditions['status'] = cleanInput($_POST['status']);
                    } else {
                        $conditions['state_buyer'] = 1;
                    }
                } else {
                    $conditions = array('seller' => $id_user);
                    if ($_POST['status'] != 'archived') {
                        $conditions['status'] = cleanInput($_POST['status']);
                    } else {
                        $conditions['state_seller'] = 1;
                    }
                }

                $conditions['limit'] = $start_from . ", " . $per_page;
                $data['users_po'] = $this->po->get_po($conditions);
                if (empty($data['users_po'])) {
                    jsonResponse('0 Producing Requests found by this search.', 'info', array('total_po_by_status' => 0));
                }

                $total_po_by_status = $this->po->counter_by_conditions($conditions);

                $items_id = array();
                $users_id = array();

                foreach ($data['users_po'] as $item) {
                    $items_id[$item['id_item']] = $item['id_item'];

                    if (have_right('buy_item')) {
                        $users_id[$item['id_seller']] = $item['id_seller'];
                    } else{
                        $users_id[$item['id_buyer']] = $item['id_buyer'];
                    }
                }

                if (!empty($items_id)) {
                    $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
                    $data['products_list'] = arrayByKey($data['products_list'], 'id');
                }

                if (!empty($users_id)) {
                    $data['users_list'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                    $data['users_list'] = arrayByKey($data['users_list'], 'idu');
                    if(have_right('buy_item')){
                        $this->load->model('Company_Model', 'company');
                        $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
                    }
                }

                $data['status_array'] = $this->producing_request_statuses;

                $this->view->assign($data);

                $po_list = $this->view->fetch('new/po/po_list_view');

                jsonResponse('', 'success', array('po_list' => $po_list, 'total_po_by_status' => $total_po_by_status));
            break;
            // UPDATE SIDEBAR COUNTERS
            case 'update_sidebar_counters':
                checkPermisionAjax('manage_seller_po,buy_item');

                // PREPARING CONDITIONS
                if (have_right('buy_item')) {
                    $count_conditions = array('id_buyer' => $id_user);
                    $archived_conditions = array('id_buyer' => $id_user, 'state_buyer' => 1);
                } else {
                    $count_conditions = array('id_seller' => $id_user);
                    $archived_conditions = array('id_seller' => $id_user, 'state_seller' => 1);
                }

                // GET COUNTERS
                $statuses_counters = arrayByKey($this->po->count_po_by_statuses($count_conditions), 'status');
                $archived_counters = $this->po->count_po_by_statuses($archived_conditions);
                $statuses_counters['archived'] = array('status' => 'archived', 'counter' => 0);

                if (!empty($archived_counters)) {
                    foreach ($archived_counters as $status_couter)
                    $statuses_counters['archived']['counter'] += $status_couter['counter'];
                }

                // RETURN RESPONCE
                jsonResponse('', 'success', array('counters' => $statuses_counters));
            break;
            // SEARCH PO
            case 'search_po':
                checkPermisionAjax('manage_seller_po,buy_item');

                $keywords = cleanInput(cut_str($_POST['keywords']));
                if (empty($keywords))
                    jsonResponse('Error: Search keywords is required.');

                global $tmvc;
                $per_page = $tmvc->my_config['user_po_per_page'];
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

				$search_filter = cleanInput($_POST['search_filter']);
				if (!empty($search_filter)) {
					switch($search_filter){
						case 'po_number' :
							$conditions = array('po_number' => toId($keywords));
						break;
						case 'archived' :
							$conditions = array('keywords' => $keywords);
						break;
						default:
							$conditions = array(
                                'keywords' => $keywords,
                                'status' => $search_filter
                            );
						break;
					}
				} else{
					$conditions = array('keywords' => $keywords);
				}
                if (have_right('buy_item')) {
                    $conditions['buyer'] = $id_user;
					if($search_filter == 'archived'){
						$conditions['state_buyer'] = 1;
					}
                }else {
                    $conditions['seller'] = $id_user;
					if($search_filter == 'archived'){
						$conditions['state_seller'] = 1;
					}
                }

                $conditions['limit'] = $start_from . ", " . $per_page;
                $total_po_by_status = $this->po->counter_by_conditions($conditions);
                $data['users_po'] = $this->po->get_po($conditions);

                if (empty($data['users_po'])) {
                    jsonResponse('0 Producing Requests found by this search.', 'info');
                }

                $items_id = array();
                $users_id = array();

                foreach ($data['users_po'] as $item) {
                    $items_id[$item['id_item']] = $item['id_item'];
                    if (have_right('buy_item'))
                        $users_id[$item['id_seller']] = $item['id_seller'];
                    else
                        $users_id[$item['id_buyer']] = $item['id_buyer'];
                }

                if (!empty($items_id)) {
                    $data['products_list'] = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
                    $data['products_list'] = arrayByKey($data['products_list'], 'id');
                }

                if (!empty($users_id)) {
                    $data['users_list'] = $this->user->getSimpleUsers(implode(',', $users_id), "users.idu, CONCAT(users.fname, ' ', users.lname) as username");
                    $data['users_list'] = arrayByKey($data['users_list'], 'idu');
                    if(have_right('buy_item')){
                        $this->load->model('Company_Model', 'company');
                        $data['companies_info'] = arrayByKey($this->company->get_sellers_base_company(implode(',', $users_id), "id_company, name_company, index_name, id_user, type_company"), 'id_user');
                    }
                }

                $data['status_array'] = $this->producing_request_statuses;

                $this->view->assign($data);

                $po_list = $this->view->fetch('new/po/po_list_view');

                jsonResponse('', 'success', array('po_list' => $po_list, 'total_po_by_status' => $total_po_by_status, 'status' => $search_filter));
            break;
        }
    }

    public function administration() {
        checkAdmin('manage_content');

        $this->_load_main();

        $data['statuses'] = arrayByKey($this->po->count_po_by_statuses(), 'status');
        $data['last_po_id'] = $this->po->get_po_last_id();

        $this->view->assign($data);
        $this->view->assign('title', 'Producing Requests');
        $this->view->display('admin/header_view');
        $this->view->display('admin/po/index_view');
        $this->view->display('admin/footer_view');
    }

    function ajax_po_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        $this->_load_main();

        $params = array('per_p' => $_POST['iDisplayLength'], 'start' => $_POST['iDisplayStart']);

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
            switch ($_POST["mDataProp_" . intVal($_POST['iSortCol_' . $i])]) {
                case 'dt_id_po': $params['sort_by'][] = 'po.id_po-' . $_POST['sSortDir_' . $i];
                break;
                case 'dt_status': $params['sort_by'][] = 'po.status-' . $_POST['sSortDir_' . $i];
                break;
                case 'dt_prototype': $params['sort_by'][] = 'ip.title-' . $_POST['sSortDir_' . $i];
                break;
                case 'dt_quantity': $params['sort_by'][] = 'po.quantity-' . $_POST['sSortDir_' . $i];
                break;
                case 'dt_price': $params['sort_by'][] = 'po.price-' . $_POST['sSortDir_' . $i];
                break;
                case 'dt_date_created': $params['sort_by'][] = 'po.date-' . $_POST['sSortDir_' . $i];
                break;
                case 'dt_date_changed': $params['sort_by'][] = 'po.change_date-' . $_POST['sSortDir_' . $i];
                break;
            }
            }
        }

        if (isset($_POST['status']))
            $params['status'] = cleanInput($_POST['status']);

        if (isset($_POST['seller']))
            $params['seller'] = cleanInput($_POST['seller']);

        if (isset($_POST['buyer']))
            $params['buyer'] = cleanInput($_POST['buyer']);

        if (isset($_POST['item']))
            $params['item'] = cleanInput($_POST['item']);

        if (isset($_POST['start_from']))
            $params['start_from'] = formatDate(cleanInput($_POST['start_from']), 'Y-m-d');

        if (isset($_POST['start_to']))
            $params['start_to'] = formatDate(cleanInput($_POST['start_to']), 'Y-m-d');

        if (isset($_POST['update_from']))
            $params['update_from'] = formatDate(cleanInput($_POST['update_from']), 'Y-m-d');

        if (isset($_POST['update_to']))
            $params['update_to'] = formatDate(cleanInput($_POST['update_to']), 'Y-m-d');

        if (isset($_POST['keywords']))
            $params['keywords'] = cleanInput(cut_str($_POST['keywords']));

        $po = $this->po->get_po($params);
        $po_count = $this->po->counter_by_conditions($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $po_count,
            "iTotalDisplayRecords" => $po_count,
			'aaData' => array()
        );

		if(empty($po))
			jsonResponse('', 'success', $output);

        $items_id = array();
        $users_id = array();

        foreach ($po as $item) {
            $items_id[$item['id_item']] = $item['id_item'];
            $users_id[$item['id_seller']] = $item['id_seller'];
            $users_id[$item['id_buyer']] = $item['id_buyer'];
        }

        if (!empty($items_id)) {
            $products_list = $this->items->get_items(array('list_item' => implode(',', $items_id), 'main_photo' => 1));
            $products_list = arrayByKey($products_list, 'id');
        }

        if (!empty($users_id)) {
            $users_list = $this->user->getUsers(array('users_list' => implode(',', $users_id), 'company_info' => 1));
            $users_list = arrayByKey($users_list, 'idu');
        }

        if (!empty($po)) {
            foreach ($po as $po_item) {
                if (!empty($po_item['changes_prototype'])) {
                    $changes_prototype = with(json_decode($po_item['changes_prototype'], true), function ($changes) {
                        return is_array($changes) ? $changes : array();
                    });
                    $changes_html = '<table class="table table-bordered table-hover mb-0">
                    					<thead>
											<tr role="row">
												<th class="w-40pr tac">Property</th>
												<th class="w-30pr tac">Old value</th>
												<th class="w-30pr tac">New value</th>
											</tr>
                    					</thead>
                    					<tbody>';

                    foreach ($changes_prototype as $key => $changes_item) {
                        $changes_html .= '<tr class="odd">';
                        $old_values = (!empty($changes_item['old_values'])) ? '<span class="txt-gray-light">' . $changes_item['old_values'] . ' </span>' : '-';
                        $changes_html .= '<td class="tac"><span class="txt-bold fs-14">' . $key . '</span></td>
										<td class="tac">'.$old_values.'</td>
										<td class="tac">'.$changes_item['current_value'].'</td>
									</tr>';
                    }
                    $changes_html .= '</tbody>
                    	</table>';
                } else {
                    $changes_html = '<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> There are no changes for prototype.</div>';
                }

                if (!empty($po_item['log'])) {
                    $logs = array_reverse(with(json_decode("[{$po_item['log']}]", true), function($log) {
                        return is_array($log) ? $log: array();
                    }));
                    $logs_html = '<table class="table table-bordered table-hover mb-0">
                    				<thead>
                    					<tr role="row">
											<th class="w-90 tac">Date</th>
											<th class="w-90 tac">User</th>
											<th>Note(s)</th>
                    					</tr>
                    				</thead>
                    				<tbody>';

                    foreach($logs as $po_timeline){
                        $logs_html .= '<tr class="odd">
                        		<td class="tac">'.formatDate($po_timeline['date'], 'm/d/Y H:i:s').'</td>
                        		<td class="tac">';

                        if(isset($po_timeline['user'])){
                            $logs_html .= '<strong>'.$po_timeline['user'].'</strong>';
                        } else{
                            $logs_html .= '<strong>System</strong>';
                        }

                        $logs_html .= '</td>
                        				<td>';

                        if(isset($po_timeline['price'])){
                            $logs_html .= '<strong>Price: </strong> $ '.$po_timeline['price'].'<br>';
                        }

                        if(isset($po_timeline['quantity'])){
                            $logs_html .= '<strong>Quantity: </strong> '.$po_timeline['quantity'].'<br>';
                        }

                        $logs_html .= '<strong>Message: </strong>'.$po_timeline['message'].'<br>';
                        if(isset($po_timeline['changes'])){
                            $logs_html .= '<strong>Changes:</strong> '.cleanOutput($po_timeline['changes']).'<br>';
                        }

                        if(isset($po_timeline['comment'])){
                            $logs_html .= '<strong>Comment:</strong> '.cleanOutput($po_timeline['comment']);
                        }

                        $logs_html .= '</td>
                        			</tr>';
                    }
                    $logs_html .= '</tbody>
                    		</table>';
                } else{
                    $logs_html = '<div class="info-alert-b"><i class="ep-icon ep-icon_info"></i> This Producing Request does not have any log(s).</div>';
                }

                $seller_name = $users_list[$po_item['id_seller']]['user_name'];
                $buyer_name = $users_list[$po_item['id_buyer']]['user_name'];

                $status_array = array(
                    'declined' => 'Declined',
                    'initiated' => 'New Requests',
                    'order_initiated' => 'Order initiated',
                    'po_processing' => 'In process',
                    'prototype_confirmed' => 'Prototype confirmed',
                    'archived' => 'Archived'
                );
                $status_prototype_array = array('declined' => 'Declined', 'accepted' => 'Accepted', 'in_progress' => 'In progress');

                $company_link = __SITE_URL;
                if (!empty($users_list[$po_item['id_seller']]['index_name']))
                    $company_link .= $users_list[$po_item['id_seller']]['index_name'];
                elseif ($users_list[$po_item['id_seller']]['type_company'] == 'branch')
                    $company_link .= "branch/" . strForURL($users_list[$po_item['id_seller']]['name_company']) . "-" . $users_list[$po_item['id_seller']]['id_company'];
                else
                    $company_link .= "seller/" . strForURL($users_list[$po_item['id_seller']]['name_company']) . "-" . $users_list[$po_item['id_seller']]['id_company'];

                $company_icon = "<a class='ep-icon ep-icon_building' title='View page of company " . $users_list[$po_item['id_seller']]['name_company'] . "' target='_blank' href='" . $company_link . "'></a>";

                $prototype = "Prototype don't was created";
                if (!empty($po_item["title"])) {
                    $prototype = '<div class="tal">'
                        . '<a class="ep-icon ep-icon_item txt-orange" title="View Prototype" href="' . __SITE_URL . 'prototype/item/' . $po_item['id_prototype'] . '"></a>'
                        . '</div>'
                        . '<div>' . $po_item["title"] . '</div>';
                }

                $item_img_link = getDisplayImageLink(array('{ID}' => $po_item['id_item'], '{FILE_NAME}' => $products_list[$po_item['id_item']]['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
                $item_prototype_img_link = getDisplayImageLink(array('{ID}' => $po_item['id_prototype'], '{FILE_NAME}' => $po_item['image']), 'items.prototype', array( 'thumb_size' => 1 ));

                //TODO: admin chat hidden
                $btnChatSeller = new ChatButton(['hide' => true, 'recipient' => $po_item['id_seller'], 'recipientStatus' => $users_list[$po_item['id_seller']]['status'], 'module' => 11, 'item' => $po_item['id_po']], ['classes' => 'btn-chat-now', 'text' => '']);
                $btnChatSellerView = $btnChatSeller->button();

                //TODO: admin chat hidden
                $btnChatBuyer = new ChatButton(['hide' => true, 'recipient' => $po_item['id_buyer'], 'recipientStatus' => $users_list[$po_item['id_buyer']]['status'], 'module' => 11, 'item' => $po_item['id_po']], ['classes' => 'btn-chat-now', 'text' => '']);
                $btnChatBuyerView = $btnChatBuyer->button();


                $output['aaData'][] = array(
                    'dt_id_po' => $po_item['id_po'] .
                        "<br /><a rel='log_details' title='View log' class='ep-icon ep-icon_plus'></a>",
                    'dt_status' =>
                        '<div class="tal">'
                            . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Status" title="Filter by status" data-value-text="' . $status_array[$po_item['status']] . '" data-value="' . $po_item['status'] . '" data-name="status"></a>'
                        . '</div>'
                        . $status_array[$po_item['status']],
                    'dt_prototype' =>
                        '<div class="img-prod pull-left w-30pr">'
                            . '<img class="w-100pr" src="' . $item_prototype_img_link . '" alt="' . $po_item['title'] . '"/>'
                        . '</div>'
                        . '<div class="pull-right w-68pr">'
                            . $prototype .
                            '<div class="txt-blue">' . $status_prototype_array[$po_item['status_prototype']] . '</div>'
                        . '</div>',
                    'dt_item' =>
                        '<div class="img-prod pull-left w-30pr">'
                            . '<img
                                    class="w-100pr"
                                    src="' . $item_img_link . '"
                                    alt="' . $products_list[$po_item['id_item']]['title'] . '"
                                />'
                        . '</div>'
                        . '<div class="pull-right w-68pr">'
                            . '<div class="clearfix">'
                                . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Item" title="Filter by item" data-value-text="' . $products_list[$po_item['id_item']]['title'] . '" data-value="' . $po_item['id_item'] . '" data-name="item"></a>'
                                . '<a class="ep-icon ep-icon_item txt-orange pull-left" title="View Product" href="' . __SITE_URL . 'item/' . strForURL($products_list[$po_item['id_item']]['title']) . '-' . $products_list[$po_item['id_item']]['id'] . '"></a>'
                                . '<div class="pull-right">
										<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-blue fs-18 m-0" data-empty="ep-icon ep-icon_star-empty txt-blue fs-18 m-0" type="hidden" name="val" value="' . $products_list[$po_item['id_item']]['rating'] . '" data-readonly>
									</div>'
                            . '</div>'
                            . '<div>' . $products_list[$po_item['id_item']]['title'] . '</div>'
                        . '</div>',
                    'dt_buyer' =>
                        '<div class="tal">'
                            . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Buyer" title="Filter by ' . $buyer_name . '" data-value-text="' . $buyer_name . '" data-value="' . $po_item['id_buyer'] . '" data-name="buyer"></a>'
                            . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $buyer_name . '" href="' . __SITE_URL . 'usr/' . strForURL($buyer_name) . '-' . $po_item['id_buyer'] . '"></a>'
                            . $btnChatBuyerView . '</div>
                        <a href="usr/' . strForURL($buyer_name) . '-' . $po_item['id_buyer'] . '">' . $buyer_name . '</a> <br />'
                        . '<span>' . $users_list[$po_item['id_buyer']]['gr_name'] . '</span>',
                    'dt_seller' =>
                        '<div class="tal">'
                            . '<a class="ep-icon ep-icon_filter txt-green dt_filter" data-title="Seller" title="Filter by ' . $seller_name . '" data-value-text="' . $seller_name . '" data-value="' . $po_item['id_seller'] . '" data-name="seller"></a>'
                            . '<a class="ep-icon ep-icon_user" title="View personal page of ' . $users_list[$po_item['id_seller']]['fname'] . ' ' . $users_list[$po_item['id_seller']]['lname'] . '" href="' . __SITE_URL . 'usr/' . strForURL($seller_name) . '-' . $po_item['id_seller'] . '"></a>'
                            . $company_icon
                            . $btnChatSellerView . '</div>
                        <a href="usr/' . strForURL($seller_name) . '-' . $po_item['id_seller'] . '">' . $seller_name . '</a> (' . $users_list[$po_item['id_seller']]['name_company'] . ') <br />'
                        . '<span>' . $users_list[$po_item['id_seller']]['gr_name'] . '</span>',
                    'dt_quantity' => $po_item['quantity'],
                    'dt_price' => '$' . $po_item['price'],
                    'dt_date_created' => formatDate($po_item['date']),
                    'dt_date_changed' => formatDate($po_item['change_date']),
                    'dt_log' => $logs_html,
                    'dt_changes' => $changes_html,
                    'dt_comment' => $po_item['comment'],
                    'dt_po_changes' => $po_item['changes'],
                );
            }
        }

        jsonResponse('', 'success', $output);
    }
}
