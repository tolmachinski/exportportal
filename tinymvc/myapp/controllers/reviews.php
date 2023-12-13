<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\ProductReview\ProductReviewStatus;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;
use App\Filesystem\FilePathGenerator;
use App\Filesystem\ProductReviewPathGenerator;
use App\Filesystem\UserFilePathGenerator;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Reviews_Controller extends TinyMVC_Controller {

	private function load_main() {
		$this->load->model('Items_Model', 'items');
		$this->load->model('User_Model', 'user');
		$this->load->model('ItemsReview_Model', 'reviews');
		$this->load->model('Category_Model', 'category');
	}

	public function modal_by_item() {
		checkIsAjax();

		$id_item = (int) $this->uri->segment(3);
		$order = $this->uri->segment(4);
		$this->load->model('Items_Model', 'items');

		if (!model(Items_Model::class)->item_exist($id_item)){
			messageInModal('The item does not exist.');
        }

		$this->load->model('ItemsReview_Model', 'itemreviews');
		$this->load->model('Orders_Model', 'orders');

		//get all user bought items viwh status 11
		if (logged_in() && have_right('write_reviews')) {
			$conditions = array();
			$conditions['id_buyer'] = id_session();
			$conditions['id_item'] = $id_item;
			$data['user_ordered_item'] = model(ItemsReview_Model::class)->get_orders_for_review($conditions);
        }

		$this->load->model('User_Model', 'user');
		$data['item'] = $this->items->get_item($id_item);
		$data['sold_counter'] = $this->items->soldCounter($id_item);
		$data['seller'] = $this->user->getUser($data['item']['id_seller']);
		$data['photos'] = $this->items->get_items_photo($id_item, 1);
		if ($order !== '' && in_array($order, array('raiting', 'date', 'likes'))) {
			$order = $order . "_desc";
		} else {
			$order = 'date_desc';
		}

		$this->load->model('External_feedbacks_Model', 'external_feedbacks');

		$rank_counters_ep = arrayByKey(model(ItemsReview_Model::class)->get_all_rating_counter($id_item), 'rating');
		$rank_counters_external = arrayByKey($this->external_feedbacks->get_all_rating_counter_review($id_item), 'rating');

		$data['rank_counters'] = array(
				'5' => array('count' => 0, 'name' => 'Excellent'),
				'4' => array('count' => 0, 'name' => 'Good'),
				'3' => array('count' => 0, 'name' => 'Average'),
				'2' => array('count' => 0, 'name' => 'Poor'),
				'1' => array('count' => 0, 'name' => 'Terrible')
			);

		foreach($data['rank_counters'] as $key => $rank_counter){
			$data['rank_counters'][$key]['count'] = $rank_counters_ep[$key]['count_rating'] + $rank_counters_external[$key]['count_rating'];
		}

		//reviews
		$data['reviews'] = model(ItemsReview_Model::class)->get_user_reviews(array('item' => $id_item, 'per_p' => 10));
		$data['reviews_count'] = model(ItemsReview_Model::class)->counter_by_conditions(array('item' => $id_item));
		foreach ($data['reviews'] as $item_review)
			$array_review_id[] = $item_review['id_review'];

		if (!empty($data['reviews']) && logged_in())
			$data['helpful_reviews'] = model(ItemsReview_Model::class)->get_helpful_by_review(implode(',', $array_review_id), id_session());

		$data['item_raiting'] = model(ItemsReview_Model::class)->getRaitingByItem($id_item);

		$this->view->assign($data);
        $this->view->display('new/item/reviews/byitem2_view');
	}

	public function administration() {
		checkAdmin('moderate_content');

		$this->load->model('Items_Model', 'items');
		$this->load->model('ItemsReview_Model', 'itemreviews');
		$data['item'] = $this->items->get_item(id_from_link($this->uri->segment(3)), '*');
		$data['last_reviews_id'] = $this->itemreviews->get_reviews_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'Reviews');
		$this->view->display('admin/header_view');
		$this->view->display('admin/users_reviews/index_view');
		$this->view->display('admin/footer_view');
	}

	public function ajax_list_dt() {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		checkPermisionAjaxDT('moderate_content');

		$type = cleanInput($this->uri->segment(3));
		switch ($type) {
			case "reviews":
				$this->load_main(); /* load main models */
				$conditions = array();

				if (isset($_POST['iDisplayStart'])) {
					$from = intval(cleanInput($_POST['iDisplayStart']));
					$till = intval(cleanInput($_POST['iDisplayLength']));
					$conditions['limit'] = $from . ',' . $till;
				}

				if (!empty($_POST['start_date'])) {
					$start_date = cleanInput($_POST['start_date']);
					$conditions['added_start'] = date('Y-m-d', strtotime($start_date));
				}

				if (!empty($_POST['finish_date'])) {
					$added_finish = cleanInput($_POST['finish_date']);
					$conditions['added_finish'] = date('Y-m-d', strtotime($added_finish));
				}

				if (!empty($_POST['replied']))
					$conditions['replied'] = $_POST['replied'];

				if (!empty($_POST['id_user']))
					$conditions['id_user'] = intval($_POST['id_user']);

				if (!empty($_POST['id_item']))
					$conditions['id_item'] = intval($_POST['id_item']);

				if (isset($_POST['status']))
					$conditions['rev_status'] = cleanInput($_POST['status']);

				if (isset($_POST['keywords']))
					$conditions['keywords'] = cleanInput(cut_str($_POST['keywords']));

				if ($_POST['iSortingCols'] > 0) {
					for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
						switch ($_POST["mDataProp_" . intval($_POST['iSortCol_' . $i])]) {
							case 'item': $conditions['sort_by'][] = 'title-' . $_POST['sSortDir_' . $i]; break;
							case 'author': $conditions['sort_by'][] = 'fullname-' . $_POST['sSortDir_' . $i]; break;
							case 'rev_title': $conditions['sort_by'][] = 'rev_title-' . $_POST['sSortDir_' . $i]; break;
							case 'rev_text': $conditions['sort_by'][] = 'rev_text-' . $_POST['sSortDir_' . $i]; break;
							case 'reply': $conditions['sort_by'][] = 'reply-' . $_POST['sSortDir_' . $i]; break;
							case 'rev_date': $conditions['sort_by'][] = 'rev_date-' . $_POST['sSortDir_' . $i]; break;
							case 'rev_status': $conditions['sort_by'][] = 'rev_status-' . $_POST['sSortDir_' . $i]; break;
							case 'rev_rating': $conditions['sort_by'][] = 'rev_raiting-' . $_POST['sSortDir_' . $i]; break;
							case 'plus': $conditions['sort_by'][] = 'count_plus-' . $_POST['sSortDir_' . $i]; break;
							case 'minus': $conditions['sort_by'][] = 'count_minus-' . $_POST['sSortDir_' . $i]; break;
							case 'seller': $conditions['sort_by'][] = 'sel_fullname-' . $_POST['sSortDir_' . $i]; break;
						}
					}
				}

				$reviews = $this->reviews->searchReviews($conditions);
				$records_total = $this->reviews->countReviews($conditions);

				$output = array(
					"sEcho" => intval($_POST['sEcho']),
					"iTotalRecords" => $records_total,
					"iTotalDisplayRecords" => $records_total,
					"aaData" => array()
				);

				if(empty($reviews)){
					jsonResponse('', 'success', $output);
				}

                /** @var Product_Reviews_Images_Model $reviewImagesModel */
                $reviewImagesModel = model(Product_Reviews_Images_Model::class);

                $reviewsImages = arrayByKey(
                    $reviewImagesModel->findAllBy([
                        'conditions' => [
                            'reviewsIds' => array_column(
                                $reviews,
                                'id_review'
                            )
                        ],
                    ]),
                    'review_id',
                    true
                );

				foreach ($reviews as $review) {
					$moderate_btn = '<a data-callback="moderate_review" class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog" data-message="Are you sure want to moderate this review?" title="Moderate review" data-review="' . $review['id_review'] . '"></a>';
					if ($review['rev_status'] == "moderated"){
						$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated review"/>';
					}

                    //TODO: admin chat hidden
                    $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $review['id_user'], 'recipientStatus' => $review['user_status'], 'module' => 25, 'item' => $review['id_review']], ['classes' => 'btn-chat-now', 'text' => '']);
                    $btnChat = $btnChatUser->button();

                    $imagesList = '';
                    if (!empty($reviewsImages[$review['id_review']])) {
                        foreach ($reviewsImages[$review['id_review']] as $key => $image) {
                            $key++;
                            $imagesList .= sprintf(
                                <<<IMAGE
                                    <div class="img-list-b pull-left mr-5 mb-5 relative-b" id="js-image-{$image['id']}">
                                        <a href="%s" class="fancyboxGallery" data-title="Image #{$key}">
                                            <img src="%s" alt="img" class="w-100"/>
                                        </a>
                                        <a class="ep-icon ep-icon_remove txt-red absolute-b pos-r0 m-0 bg-white confirm-dialog"
                                            data-message="Are you sure you want to delete this image?"
                                            title="Delete image" data-callback="removeReviewImage"
                                            data-image="{$image['id']}"
                                        ></a>
                                    </div>
                                IMAGE,
                                getDisplayImageLink(['{REVIEW_ID}' => $review['id_review'], '{FILE_NAME}' => $image['name']], 'product_reviews.main'),
                                getDisplayImageLink(['{REVIEW_ID}' => $review['id_review'], '{FILE_NAME}' => $image['name']], 'product_reviews.main', ['thumb_size' => 0]),
                            );
                        }
                    }

                    $dtDetails = [
                        <<<IMAGES
                            <tr>
                                <td class="w-100">Images:</td>
                                <td>{$imagesList}</td>
                            </tr>
                        IMAGES
                    ];

					$output['aaData'][] = array(
						"checkboxes" 	=> <<<CHECKBOXES
                            <input type="checkbox" class="check-review mr-5 pull-left" data-id-review="{$review['id_review']}">{$review['id_review']}<br>
                            <div title='View details' class="call-function ep-icon ep-icon_plus txt-green" data-callback="reviewDetails"></div>
                        CHECKBOXES,
						"title" 		=> '<div class="pull-left">
												<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by item" data-value-text="' . $review['title'] . '" data-value="' . $review['id_item'] . '" data-title="Item" data-name="id_item"></a>
												<a class="ep-icon ep-icon_item txt-orange" title="View item" href="' . __SITE_URL . 'item/' . strForURL($review['title']) .	'-' . $review['id_item'] . '"' . '></a>
											</div>
											<div class="clearfix"></div>
											<span>' . $review['title'] . '</span>',
						"id_order" 		=> '<a href="' . __SITE_URL . 'order/popups_order/order_detail/' . $review['id_order'] . '" class="fancybox fancybox.ajax" data-title="Order details">' . orderNumber($review['id_order']) . '</a>',
						"fullname" 		=> '<div class="pull-left">
												<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by user" data-value-text="' . $review['fullname'] . '" data-value="' . $review['id_user'] . '" data-title="Author" data-name="id_user"></a>
												<a class="ep-icon ep-icon_user" title="View user\'s profile" target="_blank" href="' . __SITE_URL . 'usr/' . strForURL($review['fullname']) . '-' . $review['id_user'] . '"' . '></a>'
												. $btnChat .
											'</div>
											<div class="clearfix"></div>
											<span>' . $review['fullname'] . '</span>',
						"rev_rating" 	=> $review['rev_raiting'],
						"review" 		=> '<p><strong>'.$review['rev_title'].'</strong></p>
											<p>'.$review['rev_text'].'</p>',
						"rev_date" 		=> getDateFormat($review['rev_date'], 'Y-m-d H:i:s'),
						"rev_status" 	=> '<div class="clearfix">
												<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by status"  data-value="' . $review['rev_status'] . '" data-value-text="' . $review['rev_status'] . '" data-title="Status" data-name="status"></a>
											</div>' .
											$review['rev_status'],
						"reply" 		=> "<p>{$review['reply']}</p>",
						"reply_date" 	=> validateDate($review['reply_date'], 'Y-m-d H:i:s')?getDateFormat($review['reply_date'], 'Y-m-d H:i:s'):'&mdash;',
						"plus" 			=> $review['count_plus'],
						"minus" 		=> $review['count_minus'],
                        "dt_details"    => $dtDetails,
						"actions" 		=> '<a class="ep-icon ep-icon_visible fancybox.ajax fancybox view-details" data-title="View details" href="reviews/popup_forms/view_review_by_item/' . $review['id_item'] . '"  title="View review" data-scroll="li-review-' . $review['id_review'] . '"></a>' .
											$moderate_btn . '</br>' .
											'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" data-title="Edit review" title="Edit review" href="reviews/popup_forms/edit_review/' . $review['id_review'] . '" data-id="' . $review['id_review'] . '"></a>
											<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_review" data-message="Are you sure want to delete this review?" title="Delete review" data-id="' . $review['id_review'] . '"></a>'
					);
				}

				jsonResponse('', 'success', $output);
			break;
		}
	}

	public function ajax_reviews_administration_operation() {
		if (!isAjaxRequest()){
			headerRedirect();
		}

		checkPermisionAjax('moderate_content');
		$operation = $this->uri->segment(3);
		$this->load_main();

		switch ($operation) {
			case 'moderate':
				if (!isset($_POST['checked_reviews']) || empty($checked_reviews = cleanInput($_POST['checked_reviews']))){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!$this->reviews->moderateReviews($checked_reviews)) {
					jsonResponse('systmess_internal_server_error');
				}

				jsonResponse(translate('systmess_success_moderated_review'), 'success');
			break;
			case 'delete':
				$id_review = (int)$_POST['checked_reviews'];
				$review_detail = $this->reviews->get_simple_review($id_review);
				if (empty($review_detail)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!$this->reviews->deleteReviews($id_review)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->load->model('Orders_Model', 'orders');
				$ordered_item = $this->orders->get_my_ordered_item($review_detail['id_ordered_item'], $review_detail['id_user']);
				$this->load->model('User_Statistic_Model', 'statistic');
				$statistic_array = array(
					$review_detail['id_user'] => array('item_reviews_wrote' => -1),
					$ordered_item['id_seller'] => array('item_reviews_received' => -1)
				);
				$this->items->down_item_rating($review_detail['id_item'], $review_detail['rev_raiting']);
				$this->statistic->set_users_statistic($statistic_array);
				jsonResponse(translate('systmess_success_deleted_review'), 'success');

			break;
			case 'edit':
				$validator = $this->validator;
				$validator_rules = array(
					array(
					'field' => 'text',
					'label' => 'Description',
					'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$data = array(
					'rev_text' => cleanInput($_POST['text'])
				);

				$title = cleanInput($_POST['title']);
				if (!empty($title))
					$data['rev_title'] = $title;

				$reply = cleanInput($_POST['reply']);
				if (!empty($reply))
					$data['reply'] = $reply;

				if ($this->reviews->update_review(intVal($_POST['review']), $data)) {
					jsonResponse("The review has been successfully updated!", 'success');
				} else {
					jsonResponse('Error: This review has not been updated. Please try again later.');
				}
			break;
            case 'remove_image':
                /** @var Product_Reviews_Images_Model $reviewsImagesModel */
                $reviewsImagesModel = model(Product_Reviews_Images_Model::class);

                if (
                    empty($imageId = request()->request->getInt('image'))
                    || empty($image = $reviewsImagesModel->findOne($imageId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var FilesystemProviderInterface */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $productReviewsDisk = $filesystemProvider->storage('public.storage');
                $imageThumbs = config('img.product_reviews.main.thumbs');

                try {
                    $productReviewsDisk->delete(ProductReviewPathGenerator::reviewImage((int) $image['review_id'], $image['name']));

                    foreach ($imageThumbs as $imageThumb) {
                        $productReviewsDisk->delete(ProductReviewPathGenerator::reviewImage((int) $image['review_id'], str_replace(
                            '{THUMB_NAME}',
                            $image['name'],
                            $imageThumb['name']
                        )));
                    }
                } catch (UnableToDeleteFile $e) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $reviewsImagesModel->deleteOne($imageId);

                jsonResponse('Image has been successfully deleted.', 'success');
            break;
		}
	}

	public function popup_forms() {
		checkIsAjax();
        checkIsLoggedAjaxModal();

		$id = (int) $this->uri->segment(4);
		$this->load_main();

		switch (uri()->segment(3)) {
			case 'details':
				$this->load->model('ItemsReview_Model', 'itemreviews');
				if (!empty($_GET['type']) && $_GET['type'] == 'item-ordered') {
					$review = $this->itemreviews->get_item_ordered_review($id);
				} else {
					$review = $this->itemreviews->get_review($id);
				}

				if (empty($review)) {
					messageInModal(translate('systmess_error_sended_data_not_valid'));
				}

                /** @var Product_Reviews_Images_Model $reviewsImagesModel */
                $reviewsImagesModel = model(Product_Reviews_Images_Model::class);

                $review['images'] = array_column(
                    $reviewsImagesModel->findAllBy([
                        'conditions'    => [
                            'reviewId'  => (int) $review['id_review'],
                        ],
                    ]),
                    'name'
                );

				if (logged_in() && !is_my($review['id_user'])) {
					$data['helpful_reviews'] = $this->itemreviews->get_helpful_by_review($id, id_session());
				}

				$data['reviews'][] = $review;
                $data['view_user_review'] = true;
                $data['isReviewDetails'] = true;
                $data['isReviewPopup'] = true;

				$this->view->assign($data);
				$this->view->display('new/users_reviews/details_view');
			break;
			case "view_review_by_ordered":
				$id_item = intVal($this->uri->segment(4));
				$id_ordered = intVal($_GET['ordered']);

				if (!$this->items->item_exist($id_item))
					messageInModal('Error: This item does not exist. Please close this window.');

				$this->load->model('ItemsReview_Model', 'itemreviews');
				$this->load->model('Orders_Model', 'orders');

				//get all user bought items viwh status 11
				$data['item'] = $this->items->get_item($id_item);
				$data['sold_counter'] = $this->items->soldCounter($id_item);
				$data['photos'] = $this->items->get_items_photo($id_item, 1);

				$this->load->model('External_feedbacks_Model', 'external_feedbacks');

				$rank_counters_ep = arrayByKey($this->itemreviews->get_all_rating_counter($id_item), 'rating');
				$rank_counters_external = arrayByKey($this->external_feedbacks->get_all_rating_counter_review($id_item), 'rating');

				$data['rank_counters'] = array(
						'5' => array('count' => 0, 'name' => 'Excellent'),
						'4' => array('count' => 0, 'name' => 'Good'),
						'3' => array('count' => 0, 'name' => 'Average'),
						'2' => array('count' => 0, 'name' => 'Poor'),
						'1' => array('count' => 0, 'name' => 'Terrible')
					);

				foreach($data['rank_counters'] as $key => $rank_counter){
					$data['rank_counters'][$key]['count'] = $rank_counters_ep[$key]['count_rating'] + $rank_counters_external[$key]['count_rating'];
				}

				//reviews
				$data['reviews'] = $this->itemreviews->get_user_reviews(array('id_ordered' => $id_ordered, 'per_p' => 10));
				foreach ($data['reviews'] as $item_review)
					$array_review_id[] = $item_review['id_review'];

				if (!empty($data['reviews']) && logged_in())
					$data['helpful_reviews'] = $this->itemreviews->get_helpful_by_review(implode(',', $array_review_id), id_session());

				$data['item_raiting'] = $this->itemreviews->getRaitingByItem($id_item);
				$this->view->assign($data);

				$this->view->display('new/item/reviews/byitem2_view');
			break;
            case "view_review_by_item":
                checkPermisionAjaxModal('moderate_content');

				$id_item = (int) $this->uri->segment(4);
                if (!model(Items_Model::class)->item_exist($id_item)){
                    messageInModal('The item does not exist.');
                }

                $this->load->model('ItemsReview_Model', 'itemreviews');
                $this->load->model('Orders_Model', 'orders');
                $this->load->model('User_Model', 'user');

                $data['item'] = model(Items_Model::class)->get_item($id_item);
                $data['sold_counter'] = model(Items_Model::class)->soldCounter($id_item);
                $data['seller'] = model(User_Model::class)->getUser((int) $data['item']['id_seller']);
                $data['photos'] = model(Items_Model::class)->get_items_photo($id_item, 1);

                $rank_counters_ep = arrayByKey(model(ItemsReview_Model::class)->get_all_rating_counter($id_item), 'rating');
                $rank_counters_external = arrayByKey(model(External_feedbacks_Model::class)->get_all_rating_counter_review($id_item), 'rating');

                $data['rank_counters'] = [
                    '5' => [
                        'count' => 0,
                        'name' => 'Excellent'
                    ],
                    '4' => [
                        'count' => 0,
                        'name' => 'Good'
                    ],
                    '3' => [
                        'count' => 0,
                        'name' => 'Average'
                    ],
                    '2' => [
                        'count' => 0,
                        'name' => 'Poor'
                    ],
                    '1' => [
                        'count' => 0,
                        'name' => 'Terrible'
                    ],
                ];

                foreach($data['rank_counters'] as $key => $rank_counter){
                    $data['rank_counters'][$key]['count'] = $rank_counters_ep[$key]['count_rating'] + $rank_counters_external[$key]['count_rating'];
                }

                $data['reviews'] = model(ItemsReview_Model::class)->get_user_reviews(array('item' => $id_item, 'per_p' => 10));
                $data['reviews_count'] = model(ItemsReview_Model::class)->counter_by_conditions(array('item' => $id_item));
                $data['item_raiting'] = model(ItemsReview_Model::class)->getRaitingByItem($id_item);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                foreach ($data['reviews'] as &$review) {
                    if (!empty($review['user_photo'])) {
                        $review['userImageUrl'] = $publicDisk->url(UserFilePathGenerator::imagesThumbUploadFilePath($review['id_user'], $review['user_photo']));
                    }
                }

                $this->view->assign($data);
                $this->view->display('admin/users_reviews/detail_view');
			break;
			case 'add_review':
				checkPermisionAjaxModal('write_reviews');

				$conditions = array(
					'id_buyer' => id_session()
				);

				if($id){
					$conditions['id_seller'] = $id;
				}

				$id_item = (int) $_GET['item'];
				if($id_item > 0){
					$conditions['id_item'] = $id_item;
				}

				$id_order = (int) $_GET['order'];
				if($id_order > 0){
					$conditions['id_item'] = $id_item;
					$data['ordered_item_for_review'] = $this->reviews->get_order_for_review($id_order, $conditions);
					if (empty($data['ordered_item_for_review'])){
						messageInModal(translate('systmess_error_not_items_available_to_write_a_review'));
					}

					$data['page_type'] = $this->uri->segment(4);
				} else{
					$orders = $this->reviews->get_orders_for_review($conditions);
					if (empty($orders)){
                        if (!isBackstopEnabled()) {
                            messageInModal(translate('systmess_error_not_items_available_to_write_a_review'));
                        }
					}

					$data['page_type'] = $this->uri->segment(4);

					$data['user_ordered_items_for_reviews'] = array();
					if (!empty($orders)) {
						foreach ($orders as $item) {
							$data['user_ordered_items_for_reviews'][$item['id_order']]['order'] = orderNumber($item['id_order']);
							$data['user_ordered_items_for_reviews'][$item['id_order']]['items'][] = $item;
						}
					}
				}

                $encryptedFolderName = encriptedFolderName();
                $imagesMimeProperties = getMimePropertiesFromFormats(config('img.product_reviews.main.rules.format'));

                $data['fileUploadConfigs'] = [
                    'encryptedFolderName'   => $encryptedFolderName,
                    'countUploadedImages'   => 0,
                    'uploadFileUrl'         => __CURRENT_SUB_DOMAIN_URL . "reviews/ajax_review_operation/upload_temp_image/{$encryptedFolderName}",
                    'removeFileUrl'         => __CURRENT_SUB_DOMAIN_URL . "reviews/ajax_review_operation/remove_temp_image/{$encryptedFolderName}",
                    'maxFileSize'           => config('img.product_reviews.main.rules.size'),
                    'mimetypes'             => $imagesMimeProperties['mimetypes'] ?: null,
                    'formats'               => $imagesMimeProperties['formats'] ?: null,
                    'accept'                => $imagesMimeProperties['accept'] ?: null,
                    'limit'                 => config('img.product_reviews.main.limit'),
                    'rules'                 => config('img.product_reviews.main.rules'),
                ];

				views('new/users_reviews/add_review_form_view', $data);
			break;
			case 'edit_review' :
				if (!have_right('moderate_content'))
					messageInModal(translate("systmess_error_rights_perform_this_action"));

				$data['review'] = $this->reviews->getReview($id);
				$this->view->display('admin/users_reviews/edit_review_form', $data);
			break;
			case 'leave_reply' :
				checkPermisionAjaxModal('reply_reviews,moderate_content');

				$data['review'] = $this->reviews->getReview($id);

				if (empty($data['review'])) {
					messageInModal(translate('systmess_error_invalid_data'));
				}

				if (!$this->reviews->isReviewForUserItem($id, privileged_user_id())) {
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				if ($data['review']['rev_status'] == "moderated" && $data['review']['reply'] != '') {
					messageInModal(translate('systmess_error_edit_review_already_moderated'));
				}

				$this->view->display('new/users_reviews/reply_reviews_view', $data);
			break;
			case 'edit_reply' :
				checkPermisionAjaxModal('reply_reviews,moderate_content');

				$data['review'] = $this->reviews->getReview($id);

				if (empty($data['review'])) {
					messageInModal(translate('systmess_error_invalid_data'));
				}

				if (!$this->reviews->isReviewForUserItem($id, privileged_user_id())) {
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				if ($data['review']['rev_status'] == "moderated" && $data['review']['reply'] != '') {
					messageInModal(translate('systmess_error_edit_review_already_moderated'));
                }

                $this->view->display('new/users_reviews/edit_review_reply_form_view', $data);
			break;
			case 'edit_user_review' :
				checkPermisionAjaxModal('write_reviews');

                /** @var Product_Reviews_Model $productReviewsModel */
                $productReviewsModel = model(Product_Reviews_Model::class);

                if (
                    empty($id)
                    || empty($productReview = $productReviewsModel->findOne($id, ['with' => ['images']]))
                    || id_session() != $productReview['id_user']
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if (!empty($productReview['reply'])) {
                    messageInModal(translate('systmess_error_edit_review_exist_reply'));
                }

                /** @var Product_Reviews_Images_Model $reviewsImagesModel */
                $reviewsImagesModel = model(Product_Reviews_Images_Model::class);

                $encryptedFolderName = encriptedFolderName();
                $imagesMimeProperties = getMimePropertiesFromFormats(config('img.product_reviews.main.rules.format'));

                views(
                    'new/users_reviews/edit_review_form_view',
                    [
                        'fileUploadConfigs'     => [
                            'encryptedFolderName'   => $encryptedFolderName,
                            'countUploadedImages'   => $reviewsImagesModel->countAllBy([
                                'conditions' => [
                                    'reviewId' => $id,
                                ],
                            ]),
                            'uploadFileUrl' => __CURRENT_SUB_DOMAIN_URL . "reviews/ajax_review_operation/upload_temp_image/{$encryptedFolderName}/{$id}",
                            'removeFileUrl' => __CURRENT_SUB_DOMAIN_URL . "reviews/ajax_review_operation/remove_temp_image/{$encryptedFolderName}",
                            'maxFileSize'   => config('img.product_reviews.main.rules.size'),
                            'mimetypes'     => $imagesMimeProperties['mimetypes'] ?: null,
                            'formats'       => $imagesMimeProperties['formats'] ?: null,
                            'accept'        => $imagesMimeProperties['accept'] ?: null,
                            'limit'         => config('img.product_reviews.main.limit'),
                            'rules'         => config('img.product_reviews.main.rules'),
                        ],
                        'review'                => $productReview,
                    ]
                );
			break;
		}
	}

	public function my() {
		if (!logged_in()) {
			$this->session->setMessages(translate("systmess_error_should_be_logged"), 'errors');
			headerRedirect(__SITE_URL . 'login');
		}

		if (!have_right('manage_personal_reviews')) {
			$this->session->setMessages(translate("systmess_error_page_permision"), 'errors');
			headerRedirect();
		}

		if (!i_have_company() && !have_right('buy_item')) {
			$this->session->setMessages(translate("systmess_error_must_have_company_to_access_page"), 'errors');
			headerRedirect();
		}

		checkGroupExpire();

		$data = array();

		$this->load_main();

		$uri = $this->uri->uri_to_assoc();
		if(!empty($uri['review'])){
			$data['id_review'] = (int)$uri['review'];
		}
		if(!empty($uri['item'])){
			$data['id_item'] = (int)$uri['item'];
		}
		if(!empty($uri['order'])){
			$data['id_order'] = (int)$uri['order'];
		}
		$this->view->assign('title', 'My reviews');

		$this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/users_reviews/my/index_view');
        $this->view->display('new/footer_view');
	}

	public function ajax_list_my_reviews_dt() {
        checkIsAjax();
		checkPermisionAjaxDT('manage_personal_reviews');

        $request = request()->request;
		$userId = privileged_user_id();

        $conditions = array_filter(array_merge(
            dtConditions($request->all(), [
                ['as' => 'id_item',         'key' => 'id_item',         'type' => 'toId'],
                ['as' => 'id_order',        'key' => 'id_order',        'type' => 'toId'],
                ['as' => 'id_user',         'key' => 'id_user',         'type' => 'int'],
                ['as' => 'id_seller',       'key' => 'id_seller',       'type' => 'int'],
                ['as' => 'review_number',   'key' => 'review_number',   'type' => 'toId'],
                ['as' => 'replied',         'key' => 'replied',         'type' => fn ($filter) => in_array($filter, ['yes', 'no']) ? $filter : null],
                ['as' => 'rev_status',      'key' => 'status',          'type' => 'cleanInput'],
                ['as' => 'keywords',        'key' => 'keywords',        'type' => 'cut_str|cleanInput'],
                ['as' => 'added_start',     'key' => 'start_from',      'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
                ['as' => 'added_finish',    'key' => 'start_to',        'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s, Y-m-d H:i:s'],
            ]),
            [
                'company_details'   => true,
                'id_seller'         => have_right('reply_reviews') ? $userId : null,
                'id_user'           => have_right('write_reviews') ? $userId : null,
                'limit'             => $request->getInt('iDisplayStart') . ',' . $request->getInt('iDisplayLength'),
                'sort_by'           => dtOrdering(
                    $request->all(),
                    [
                        'dt_item'    => 'title',
                        'dt_date'    => 'rev_date',
                        'dt_replied' => 'reply_date',
                    ],
                    fn ($orderBy) => $orderBy['column'] . '-' . $orderBy['direction']
                ),
            ],
        ));

        /** @var ItemsReview_Model $itemReviesModel */
        $itemReviesModel = model(ItemsReview_Model::class);

		$data['reviews'] = $itemReviesModel->searchReviews($conditions);
		$conditions['function_type'] = 'count';
        $totalReviews = $itemReviesModel->countReviews($conditions);

		$output = array(
			'iTotalDisplayRecords'  => $totalReviews,
			'iTotalRecords'         => $totalReviews,
			'aaData'                => [],
			'sEcho'                 => $request->getInt('sEcho'),
		);
		if (empty($data['reviews'])){
			jsonResponse('', 'success', $output);
		}

		$output['aaData'] = $this->_dt_reviews($data, $itemReviesModel);

		jsonResponse('', 'success', $output);
	}

	private function _dt_reviews($data, ItemsReview_Model $itemReviesModel) {
		$items_list = implode(',', array_column($data['reviews'], 'id_item'));

		//get ratings for all the items
		$result_ratings = $itemReviesModel->getRatingsByItems($items_list);
		foreach ($result_ratings as $key => $val) {
			$ratings[$val['id_item']] = !is_null($val['raiting'])?$val['raiting']:0;
		}

		// get counters for all the items
		$result_counters = $itemReviesModel->countersByItems($items_list);
		foreach ($result_counters as $key => $val) {
			$rev_counters[$val['id_item']] = !is_null($val['counter'])?$val['counter']:0;
		}

		foreach ($data['reviews'] as $review) {
			$actions = array();

			// the title for Edit reply/review button
			if(have_right('write_reviews')) {
				if(empty($review['reply']) && $review['rev_status'] == 'new'){
					$actions[] = '<a class="dropdown-item fancybox.ajax fancyboxValidateModal" ' . addQaUniqueIdentifier('page__my-reviews__table_dropdown-menu_edit-btn') . ' data-title="'.translate('general_button_edit_text').'" title="'.translate('general_button_edit_text').'" href="' . __SITE_URL . 'reviews/popup_forms/edit_user_review/'.$review['id_review'].'">
										<i class="ep-icon ep-icon_pencil"></i>
										<span>'.translate('general_button_edit_text').'</span>
									</a>';
					$actions[] = '<a class="dropdown-item confirm-dialog" ' . addQaUniqueIdentifier('page__my-reviews__table_dropdown-menu_delete-btn') . ' data-callback="delete_review" title="'.translate('general_button_delete_text').'" data-message="Are you sure you want to delete this review?" data-review="' . $review['id_review'] . '" href="#">
										<i class="ep-icon ep-icon_trash-stroke"></i>
										<span>'.translate('general_button_delete_text').'</span>
									</a>';
				}

				$user_info = 'sold by <a href="' . getCompanyURL($review) . '" target="_blank" ' . addQaUniqueIdentifier('page__my-reviews__table_item-seller-name') . '>'.$review['name_company'].'</a>';
			}

			if (have_right('reply_reviews')) {
				if ($review['rev_status'] == 'new') {
					$_reply_text_i18n = empty($review['reply'])?translate('general_button_add_text'):translate('general_button_edit_text');
					$actions[] = '<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="'.$_reply_text_i18n.'" title="'.$_reply_text_i18n.'" href="' . __SITE_URL . 'reviews/popup_forms/leave_reply/'.$review['id_review'].'">
										<i class="ep-icon ep-icon_pencil"></i>
										<span>'.$_reply_text_i18n.'</span>
									</a>';
				}

				$user_info = 'bought by <a href="' . __SITE_URL . 'usr/' . strForURL($review['fullname']) . '-' . $review['id_user'] . '" target="_blank">' . $review['fullname'] . '</a>';
			}

            $item_snapshot_img_link = getDisplayImageLink(array('{ID}' => $review['id_snapshot'], '{FILE_NAME}' => $review['snapshot_image']), 'items.snapshot', array( 'thumb_size' => 1 ));

			$output[] = array(
				"dt_item" 		=> '<div class="flex-card">
										<div class="flex-card__fixed main-data-table__item-img  main-data-table__item-img--h-auto image-card3">
											<span class="link">
												<img class="image" ' . addQaUniqueIdentifier('page__my-reviews__table_item-image') . ' src="' . $item_snapshot_img_link . '" alt="'.$review['snapshot_title'].'"/>
											</span>
										</div>
										<div class="flex-card__float">
											<div class="main-data-table__item-ttl">
												<a class="display-ib link-black txt-medium" href="' . __SITE_URL . 'items/ordered/' . strForURL($review['snapshot_title']) . '-' . $review['id_ordered_item'] . '" title="View item" target="_blank" ' . addQaUniqueIdentifier('page__my-reviews__table_item-title') . '>'.$review['snapshot_title'].'</a>
											</div>
											<div>
												<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-orange fs-16" data-empty="ep-icon ep-icon_star txt-gray-light fs-16" type="hidden" name="val" value="'.$review['item_ratting'].'" data-readonly ' . addQaUniqueIdentifier('page__my-reviews__table_item-rating') . '>
												<span class="txt-gray" ' . addQaUniqueIdentifier('page__my-reviews__table_item-counter') . '>('.$rev_counters[$review['id_item']].')</span>
											</div>
											<div>' . $user_info . '</div>
											<div class="txt-gray">Order: <a class="txt-gray" href="'.__SITE_URL.'order/my/order_number/'.orderNumberOnly($review['id_order']).'" target="_blank" ' . addQaUniqueIdentifier('page__my-reviews__table_item-order-number') . '>' . orderNumber($review['id_order']) . '</a></div>
										</div>
									</div>',
				"dt_title" 		=> 	'<span ' . addQaUniqueIdentifier('page__my-reviews__table_item-description') . '>'. $review['rev_title'] . '</span>'
									.'<div>
										<input class="rating-bootstrap" ' . addQaUniqueIdentifier('page__my-reviews__table_item-rating') . ' data-filled="ep-icon ep-icon_star txt-orange fs-16" data-empty="ep-icon ep-icon_star txt-gray-light fs-16" type="hidden" name="val" value="'.$review['rev_raiting'].'" data-readonly>
									</div>
									<div class="product-comments__actions">
										<div class="did-help ">
											<div class="did-help__txt">Did this review help?</div>
											<span class="didhelp-btn disabled">
												<span class="counter-b" ' . addQaUniqueIdentifier('page__my-reviews__table_item-review-counter') . '>'.$review['count_plus'].'</span>
												<span class="ep-icon ep-icon_arrow-line-up"></span>
											</span>
											<span class="didhelp-btn disabled">
												<span class="counter-b" ' . addQaUniqueIdentifier('page__my-reviews__table_item-review-counter') . '>'.$review['count_minus'].'</span>
												<span class="ep-icon ep-icon_arrow-line-down"></span>
											</span>
										</div>
									</div>',
				"dt_date" 		=> getDateFormat($review['rev_date'], 'Y-m-d H:i:s', 'j M, Y H:i'),
				"dt_replied" 	=> validateDate($review['reply_date'], 'Y-m-d H:i:s')?getDateFormat($review['reply_date'], 'Y-m-d H:i:s', 'j M, Y H:i'):'&mdash;',
				"dt_actions" 	=> '<div class="dropdown">
										<a class="dropdown-toggle" ' . addQaUniqueIdentifier('page__my-reviews__table_dropdown-menu') . ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="ep-icon ep-icon_menu-circles"></i>
										</a>

										<div class="dropdown-menu dropdown-menu-right">'.implode('', $actions).'
											<a class="dropdown-item fancybox.ajax fancyboxValidateModal" ' . addQaUniqueIdentifier('page__my-reviews__table_dropdown-menu_details-btn') . ' href="' . __SITE_URL . 'reviews/popup_forms/details/' . $review['id_review'] . '" data-mw="740" title="'.translate('general_button_details_text').'" data-title="'.translate('general_button_details_text').'">
												<i class="ep-icon ep-icon_info-stroke"></i>
												<span>'.translate('general_button_details_text').'</span>
											</a>
										</div>
									</div>'
			);
		}

		return $output;
	}

	function ajax_review_operation() {
		if (!isAjaxRequest()) {
			show_404();
		}

		$this->load_main();
		$this->load->model('ItemsReview_Model', 'itemreviews');

        $request = request()->request;

		$id = $request->getInt('id');
		$id_user = $userId = privileged_user_id();

		switch (uri()->segment(3)) {
			case 'add_review':
				checkIsLoggedAjax();
				checkPermisionAjax('write_reviews');

				$validator_rules = array(
					array(
						'field' => 'title',
						'label' => translate('edit_review_form_title_label'),
						'rules' => array('required' => '', 'max_len[200]' => '')
					),
					array(
						'field' => 'description',
						'label' => translate('edit_review_form_message_label'),
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'rev_raiting',
						'label' => translate('seller_ep_reviews_add_review_form_click_to_rate_label'),
						'rules' => array('required' => '', 'min[1]' => '', 'max[5]' => '', 'integer' => '')
					),
					array(
						'field' => 'item',
						'label' => 'Item information',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				list($id_item, $id_ordered_item) = explode('_', $_POST['item']);
				$this->load->model('Orders_Model', 'orders');

				if (!$this->orders->isMyOrderedItem($id_ordered_item, $id_user)){
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if ($this->itemreviews->iWroteReview($id_user, $id_ordered_item)){
					jsonResponse(translate('systmess_error_add_review_already_wrote_reply'));
				}

				$rating = intVal($_POST['rev_raiting']);

				$ordered_item = $this->orders->get_my_ordered_item($id_ordered_item, $id_user);
				if (empty($ordered_item)){
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				$review_title = cleanInput($_POST['title']);
				$review_description = cleanInput($_POST['description']);
				$insert = array(
					'rev_raiting' => $rating,
					'rev_title' => $review_title,
					'rev_text' => $review_description,
					'id_item' => $ordered_item['id_item'],
					'id_ordered_item' => $id_ordered_item,
					'id_user' => $id_user,
				);
				$review_last_id = $this->itemreviews->set_review($insert);

				if (!$review_last_id) {
					jsonResponse('systmess_internal_server_error');
				}

				$this->load->model('User_Statistic_Model', 'statistic');
				$statistic_array = array(
					$id_user => array('item_reviews_wrote' => 1),
					$ordered_item['id_seller'] => array('item_reviews_received' => 1)
				);
				$this->statistic->set_users_statistic($statistic_array);
				$this->items->up_item_rating($insert['id_item'], $insert['rev_raiting']);
				model('Elasticsearch_Items')->index($insert['id_item']);

                //region processing images
                if (!empty($newImages = (array) $request->get('images'))) {
                    /** @var Product_Reviews_Images_Model $reviewImagesModel */
                    $reviewImagesModel = model(Product_Reviews_Images_Model::class);

                    /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                    $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

                    /** @var FilesystemProviderInterface */
                    $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $productReviewsDisk = $filesystemProvider->storage('public.storage');
                    $tempStoragePrefixer = $filesystemProvider->prefixer('temp.storage');
                    $imagesConfig = config('img.product_reviews.main');
                    $productReviewsDisk->createDirectory(ProductReviewPathGenerator::reviewDirectory((int) $review_last_id));

                    foreach ($newImages as $newImage) {
                        try {
                            $imageProcessingResult = $interventionImageLibrary->image_processing(
                                [
                                    'tmp_name' => $tempStoragePrefixer->prefixPath(FilePathGenerator::uploadedFile($newImage)),
                                    'name' => pathinfo($newImage, PATHINFO_FILENAME)
                                ],
                                [
                                    'destination'   => getImgPath('product_reviews.main', ['{REVIEW_ID}' => $review_last_id]),
                                    'handlers'      => [
                                        'create_thumbs' => $imagesConfig['thumbs'] ?? [],
                                        'watermark'     => $imagesConfig['watermark'] ?? [],
                                    ],
                                    'use_original_name' => true,
                                ]
                            );

                            if (!empty($imageProcessingResult['errors'])) {
                                jsonResponse($imageProcessingResult['errors']);
                            }

                            $reviewImagesModel->insertOne([
                                'review_id' => $review_last_id,
                                'name'      => pathinfo($newImage, PATHINFO_FILENAME) . '.jpg',
                            ]);
                        } catch (ReadException $e) {
                            jsonResponse(translate('systmess_internal_server_error'));
                        } catch (UnableToWriteFile $e) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }
                    }
                }
                //endregion processing images

				switch($this->uri->segment(4)) {
					case 'my':
						jsonResponse(translate('systmess_success_saved_review'), 'success');
					break;
					case 'order':
						jsonResponse(translate('systmess_success_saved_review'), 'success', array('order' => $ordered_item['id_order']));
					break;
					default:
						jsonResponse(translate('systmess_success_saved_review'), 'success');
					break;
				}

			break;
			case 'edit_review':
				checkPermisionAjax('buy_item,moderate_content');

				$validator_rules = [
					[
						'field' => 'title',
						'label' => translate('edit_review_form_title_label'),
						'rules' => ['required' => '', 'max_len[200]' => '']
                    ],
					[
						'field' => 'description',
						'label' => translate('edit_review_form_message_label'),
						'rules' => ['required' => '', 'max_len[500]' => '']
                    ],
                ];

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

                if (empty($reviewId = $request->getInt('review'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

				// if the review is not moderated then update
				if (empty($review = $this->itemreviews->getReview($reviewId))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!have_right('moderate_content') && !is_my($review['id_user'])) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!have_right('moderate_content') && !empty($review['reply'])) {
					jsonResponse(translate('systmess_error_edit_review_exist_reply'));
				}

				if (!have_right('moderate_content') && $review['rev_status'] == "moderated") {
					jsonResponse(translate('systmess_error_edit_review_already_moderated'));
				}

				$review_title = cleanInput($_POST['title']);
				$review_description = cleanInput($_POST['description']);
				$update = array(
					'rev_text' => $review_description,
					'rev_title' => $review_title
				);

				if (!$this->itemreviews->update_review($reviewId, $update)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				//region processing images
                /** @var Product_Reviews_Images_Model $reviewImagesModel */
                $reviewImagesModel = model(Product_Reviews_Images_Model::class);

                /** @var FilesystemProviderInterface */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $productReviewsDisk = $filesystemProvider->storage('public.storage');
                $tempStoragePrefixer = $filesystemProvider->prefixer('temp.storage');
                $imagesConfig = config('img.product_reviews.main');
                $productReviewsDisk->createDirectory(ProductReviewPathGenerator::reviewDirectory((int) $reviewId));

                if (!empty($newImages = (array) $request->get('images'))) {
                    /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                    $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

                    foreach ($newImages as $newImage) {
                        try {
                            $imageProcessingResult = $interventionImageLibrary->image_processing(
                                [
                                    'tmp_name' => $tempStoragePrefixer->prefixPath(FilePathGenerator::uploadedFile($newImage)),
                                    'name'     => pathinfo($newImage, PATHINFO_FILENAME),
                                ],
                                [
                                    'destination'   => getImgPath('product_reviews.main', ['{REVIEW_ID}' => $reviewId]),
                                    'handlers'      => [
                                        'create_thumbs' => $imagesConfig['thumbs'] ?? [],
                                        'watermark'     => $imagesConfig['watermark'] ?? [],
                                    ],
                                    'use_original_name' => true,
                                ]
                            );

                            if (!empty($imageProcessingResult['errors'])) {
                                jsonResponse($imageProcessingResult['errors']);
                            }

                            $reviewImagesModel->insertOne([
                                'review_id' => $reviewId,
                                'name'      => pathinfo($newImage, PATHINFO_FILENAME) . '.jpg',
                            ]);
                        } catch (ReadException $e) {
                            jsonResponse(translate('systmess_internal_server_error'));
                        } catch (UnableToWriteFile $e) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }
                    }
                }

                if (!empty($removedImagesIdsRaw = (array) $request->get('images_remove'))) {
                    $removedImages = $reviewImagesModel->findAllBy([
                        'conditions' => [
                            'reviewId'  => $reviewId,
                            'ids'       => $removedImagesIdsRaw,
                        ],
                    ]);

                    if (!empty($removedImages)) {
                        $imageThumbs = $imagesConfig['thumbs'] ?: [];

                        foreach ($removedImages as $removedImage) {
                            try {
                                $productReviewsDisk->delete(ProductReviewPathGenerator::reviewImage($reviewId, $removedImage['name']));

                                foreach ($imageThumbs as $imageThumb) {
                                    $productReviewsDisk->delete(ProductReviewPathGenerator::reviewImage($reviewId, str_replace(
                                        '{THUMB_NAME}',
                                        $removedImage['name'],
                                        $imageThumb['name']
                                    )));
                                }
                            } catch (UnableToDeleteFile $e) {
                                //do nothing
                            }
                        }

                        $reviewImagesModel->deleteAllBy([
                            'conditions' => [
                                'reviewId'  => $reviewId,
                                'ids'       => array_column($removedImages, 'id'),
                            ],
                        ]);
                    }
                }
				//endregion processing images

				jsonResponse(
                    translate('systmess_success_edited_review'),
                    'success',
                    [
                        'id_review' => $reviewId,
                        'title'     => $update['rev_title'],
                        'text'      => $update['rev_text'],
                    ]
                );

			break;
			case 'add_reply':
				if (!logged_in()) {
					jsonResponse(translate('systmess_error_should_be_logged_in'));
				}

				checkPermisionAjax('reply_reviews');

				$validator = $this->validator;
				$validator_rules = array(
					array(
						'field' => 'text_reply',
						'label' => translate('reply_review_form_mesage_label'),
						'rules' => array('required' => '', 'max_len[500]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$id_review = (int) $_POST['review'];

				if (empty($id_review) || empty($review = $this->itemreviews->getReview($id_review))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!is_privileged('user', $review['id_seller'], 'reply_reviews')) {
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				if ($review['reply'] != '' && $review['rev_status'] == 'moderated') {
					jsonResponse(translate('systmess_error_edit_review_already_moderated'));
				}

				$review_text_reply = cleanInput($_POST['text_reply']);
				$update = array(
					'reply' => $review_text_reply,
					'reply_date' => date('Y-m-d H:i:s')
				);

				if (!$this->itemreviews->update_review($id_review, $update)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$data['review'] = $this->itemreviews->getReview($id_review);

				$resp = array(
					'id_review' => $id_review
				);

				$resp['reply'] = $this->view->fetch('new/users_reviews/reply_item_view', $data);

				jsonResponse(translate('systmess_success_add_reply_for_review'), 'success', $resp);

			break;
			case 'edit_reply':
				$view_type = $this->uri->segment(4);
				if (!logged_in()) {
					jsonResponse(translate('systmess_error_should_be_logged_in'));
				}

				checkPermisionAjax('reply_reviews');

				$validator = $this->validator;
				$validator_rules = array(
					array(
						'field' => 'text_reply',
						'label' => translate('reply_review_form_mesage_label'),
						'rules' => array('required' => '', 'max_len[500]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				if (empty($_POST['review'])) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$id_review = (int) $_POST['review'];

				$review = $this->itemreviews->getReview($id_review);
				if (empty($review)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!is_privileged('user', $review['id_seller'], 'reply_reviews')) {
					jsonResponse(translate('systmess_error_permission_not_granted'));
				}

				if ($review['reply'] != '' && $review['rev_status'] == 'moderated') {
					jsonResponse(translate('systmess_error_edit_review_already_moderated'));
				}

				$review_text_reply = cleanInput($_POST['text_reply']);
				$update = array(
					'reply' => $review_text_reply,
					'reply_date' => date('Y-m-d H:i:s')
				);

				if (!$this->itemreviews->update_review($id_review, $update)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$resp = array(
					'reply' => $update['reply'],
					'id_review' => $id_review
				);

				jsonResponse(translate('systmess_success_edit_reply_for_review'), 'success', $resp);

			break;
			case 'check_new':
				if (!have_right('manage_content'))
					jsonResponse(translate("systmess_error_rights_perform_this_action"));

				$lastId = $_POST['lastId'];
				$reviews_count = $this->itemreviews->get_count_new_reviews($lastId);

				if ($reviews_count) {
					$last_reviews_id = $this->itemreviews->get_reviews_last_id();
					jsonResponse('', 'success', array('nr_new' => $reviews_count, 'lastId' => $last_reviews_id));
				} else
					jsonResponse('Error: New reviews do not exist');
			break;
			case 'more':
				$id_item = intVal($_POST['id_i']);
				$start = ceil(intVal($_POST['start']) / 10) + 1;
				$reviews_count = $this->itemreviews->counter_by_conditions(array('item' => $id_item));
				$data['reviews'] = $this->itemreviews->get_user_reviews(array('item' => $id_item, 'count' => $reviews_count, 'page' => $start, 'per_p' => 10));

				$display = $this->view->fetch('item/reviews/item_view', $data);
				jsonResponse('', 'success', array('count' => $reviews_count, 'html' => $display));
			break;
			case 'help':
				if (!logged_in()) {
					jsonResponse(translate('systmess_error_should_be_logged_in'));
				}

				is_allowed('freq_allowed_review_helpfull');

				$type = cleanInput($_POST['type']);
				if (!in_array($type, array('y', 'n'))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$type = $type == 'y' ? 1 : 0;

				if (empty($id)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$review_info = $this->itemreviews->get_simple_review($id);
				if (empty($review_info)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$response_data = array(
					'counter_plus' => $review_info['count_plus'],
					'counter_minus' => $review_info['count_minus']
				);

				if ($id_user == $review_info['id_user']) {
					jsonResponse(translate('systmess_error_helpful_vote_for_yourself'));
				}

				$my_review_helpful = $this->itemreviews->exist_helpful($id, $id_user);
				$action = $type ? 'plus' : 'minus';
				if (empty($my_review_helpful['counter'])) {
					unset($my_review_helpful);
				}

				// If this is the first vote for this feedback
				if (empty($my_review_helpful)) {
					$insert = array(
						'id_review' 	=> $id,
						'id_user' 		=> $id_user,
						'help'			=> $type
					);

					$columns['count_' . $action] = '+';

					if (!$this->itemreviews->set_helpful($insert)) {
						jsonResponse(translate('systmess_internal_server_error'));
					}

					$this->itemreviews->modify_counter_helpfull($id, $columns);
					$response_data['counter_' . $action] = ++$response_data['counter_' . $action];
					$response_data['select_' . $action] = true;

					jsonResponse(translate('systmess_success_review_helpful_vote_successfully_saved'), 'success', $response_data);
				}

				// If it is a vote cancellation
				if ($my_review_helpful['help'] == $type) {
					$this->itemreviews->remove_user_helpful($id, $id_user);

					$columns['count_' . $action] = '-';
					$this->itemreviews->modify_counter_helpfull($id, $columns);

					$response_data['counter_' . $action] = --$response_data['counter_' . $action];
					$response_data['remove_' . $action] = true;

					jsonResponse(translate('systmess_success_review_helpful_vote_successfully_saved'), 'success', $response_data);
				}

				// If a vote has been changed
				$update['help'] = $type;
				$columns = array(
					'count_plus' => $type ? '+' : '-',
					'count_minus' => $type ? '-' : '+'
				);

				if (!$this->itemreviews->update_helpful($id, $update, $id_user)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->itemreviews->modify_counter_helpfull($id, $columns);

				$opposite_action = $action == 'plus' ? 'minus' : 'plus';

				$response_data['counter_' . $action] = ++$response_data['counter_' . $action];
				$response_data['counter_' . $opposite_action] = --$response_data['counter_' . $opposite_action];
				$response_data['select_' . $action] = true;
				$response_data['remove_' . $opposite_action] = true;

				jsonResponse(translate('systmess_success_review_helpful_vote_successfully_saved'), 'success', $response_data);

			break;
			case 'delete':
				checkPermisionAjax('moderate_content,write_reviews');

                if (empty($reviewId = request()->request->getInt('checked_reviews'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Product_Reviews_Model $productReviewsModel */
                $productReviewsModel = model(Product_Reviews_Model::class);

                $reviewQueryParams = [];

				if (have_right('write_reviews')) {
                    $reviewQueryParams['conditions'] = [
                        'isReplied' => false,
                        'userId'    => $userId,
                        'status'    => ProductReviewStatus::from('new'),
                    ];
				}

                $reviewQueryParams['conditions']['id'] = $reviewId;

                if (empty($review = $productReviewsModel->findOneBy($reviewQueryParams))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $productReviewsModel->deleteOne($reviewId);

				$this->load->model('Orders_Model', 'orders');
				$this->load->model('User_Statistic_Model', 'statistic');

				$ordered_item = $this->orders->get_my_ordered_item($review['id_ordered_item'], $review['id_user']);
				$statistic_array = [
					$review['id_user']         => ['item_reviews_wrote' => -1],
					$ordered_item['id_seller'] => ['item_reviews_received' => -1]
                ];
				$this->statistic->set_users_statistic($statistic_array);
				$this->items->down_item_rating($review['id_item'], $review['rev_raiting']);

                //region remove folder with review images
                /** @var FilesystemProviderInterface */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $productReviewsDisk = $filesystemProvider->storage('public.storage');

                try {
                    $productReviewsDisk->deleteDirectory(ProductReviewPathGenerator::reviewDirectory($reviewId));
                } catch (UnableToDeleteDirectory $e) {
                    //do nothing or notice it in error log
                }
                //endregion remove folder with review images

				jsonResponse(translate('systmess_success_deleted_review'), 'success', array('order' => $ordered_item['id_order']));

			break;
            case 'upload_temp_image':
                /** @var UploadedFile */
                $uploadedFile = request()->files->get('files');

                if (null === $uploadedFile) {
                    jsonResponse(translate('validation_image_required'));
                }

                if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
                    jsonResponse(translate('validation_invalid_file_provided'));
                }

                /** @var LegacyImageHandler $imageHandler */
                $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(LegacyImageHandler::class);
                // Given that we need to validate the file and it would take a long time
                // to write the new proper validation we can only use what we have.
                try {
                    $imageHandler->assertImageIsValid(
                        $imageHandler->makeImageFromFile($uploadedFile),
                        config("img.product_reviews.main.rules"),
                        $uploadedFile->getClientOriginalName()
                    );
                } catch (ValidationException $e) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                            \iterator_to_array($e->getValidationErrors()->getIterator())
                        )
                    );
                } catch (ReadException $e) {
                    jsonResponse(translate('validation_images_upload_fail'), 'error', withDebugInformation(
                        [],
                        ['exception' => throwableToArray($e)]
                    ));
                }

                // First we need to take our filesystem for temp directory
                /** @var FilesystemProviderInterface */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $tempDisk = $filesystemProvider->storage('temp.storage');
                // Next - write the file
                try {
                    $tempDisk->write(
                        $uploadDirectory = FilePathGenerator::uploadedFile(
                            $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), $uploadedFile->getClientOriginalExtension())
                        ),
                        $uploadedFile->getContent()
                    );
                } catch (UnableToWriteFile $e) {
                    jsonResponse(translate('validation_images_upload_fail'));
                }

                jsonResponse(
                    null,
                    'success',
                    [
                        'files' => [
                            [
                                'name'  => $fileName,
                                'path'  => asset("/public/temp/{$uploadDirectory}", 'legacy'),
                            ]
                        ]
                    ]
                );
            break;
            case 'remove_temp_image':
                if (empty($imageName = request()->request->get('file'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var FilesystemProviderInterface */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $tempDisk = $filesystemProvider->storage('temp.storage');

                try {
                    $tempDisk->delete(FilePathGenerator::uploadedFile($imageName));
                } catch (UnableToDeleteFile $e) {
                    //@todo Log this exception.
                }

                jsonResponse('','success');
            break;
		}
	}
}
