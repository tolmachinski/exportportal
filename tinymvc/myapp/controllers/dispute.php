<?php

use App\Common\Buttons\ChatButton;
use App\Common\Contracts\Media\DisputePhotoThumb;
use App\Common\Traits\DatatableRequestAwareTrait;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\ValidationException;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Filesystem\DisputeFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;
/**
 * Dispute controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 *
 */
class Dispute_Controller extends TinyMVC_Controller
{
    use DatatableRequestAwareTrait;
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    /**
     * Shows user's disputes dashboard page.
     */
    public function my()
    {
        checkDomainForGroup();
        checkIsLogged();
        checkPermision('manage_disputes');
        checkGroupExpire();

        $uri = uri()->uri_to_assoc();

        $data = [
            'title'         => 'Disputes',
            'statuses'      => model('dispute')->statuses,
            'video_tour'    => model('video_tour')->get_video_tour(['page' => 'dispute/my', 'user_group' => user_group_type()]),
            'upload_folder' => encriptedFolderName(),
            'filters'       => [
                'order'    => with(arrayGet($uri, 'order_number'), function ($order_id) {
                    return null !== $order_id ? ['value' => (int) $order_id, 'placeholder' => orderNumber((int) $order_id)] : null;
                }),
                'dispute'  => with(arrayGet($uri, 'dispute_number'), function ($dispute_id) {
                    return null !== $dispute_id ? ['value' => (int) $dispute_id, 'placeholder' => orderNumber((int) $dispute_id)] : null;
                }),
            ],
        ];

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->disputesEpl($data);
        } else {
            $this->disputesAll($data);
        }
    }

    private function disputesEpl($data){
        $data['templateViews'] = [
            'mainOutContent'    => 'disputes/my/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    private function disputesAll($data){
		views(['new/header_view', 'new/disputes/my/index_view', 'new/footer_view'], $data);
    }

    /**
     * Shows administrator's dashboard page.
     */
    public function administration()
    {
        checkDomainForGroup();
        checkPermision('dispute_administration');
        $this->load->model('Dispute_Model', 'dispute');

        $uri = $this->uri->uri_to_assoc();

        if (!($cur_disp = $uri['id'])) {
            $cur_disp = '';
        }

        if (!($cur_order = $uri['order'])) {
            $cur_order = '';
        }

        $data = array(
            'title'         => 'Disputes',
            'cur_disput'    => $cur_disp,
            'cur_order'     => $cur_order,
            'statuses'      => $this->dispute->statuses,
        );
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/disputes/index_view');
        $this->view->display('admin/footer_view');
    }

    public function all()
    {
        if (!have_right('read_all_disputes')) {
            show_404();
        }

        /** @var Dispute_Model $disputeModel */
        $disputeModel = model(Dispute_Model::class);

        $uri = uri()->uri_to_assoc();

        views(
            [
                'admin/header_view',
                'admin/disputes/all_disputes/index_view',
                'admin/footer_view'
            ],
            [
                'title'         => 'All disputes',
                'statuses'      => $disputeModel->statuses,
                'cur_disput'    => $uri['id'] ?? null,
                'cur_order'     => $uri['order'] ?? null,
            ]
        );
    }

    public function popup_forms()
    {
        checkDomainForGroup();
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkGroupExpire('modal');

        switch (uri()->segment(3)) {
            case 'add':
                checkPermisionAjaxModal('buy_item');

                $this->show_add_dispute_form((int) privileged_user_id(), (int) uri()->segment(5), uri()->segment(4));

                break;
            case 'edit':
                checkPermisionAjaxModal('manage_disputes,dispute_administration');

                $this->show_edit_dispute_form((int) privileged_user_id(), (int) uri()->segment(4));

                break;
            case 'init':
                checkPermisionAjaxModal('dispute_administration');

                $this->show_open_dispute_form((int) privileged_user_id(), (int) uri()->segment(4));

                break;
            case 'close':
                checkPermisionAjaxModal('dispute_administration');

                $this->show_close_dispute_form((int) privileged_user_id(), (int) uri()->segment(4));

                break;
            case 'cancel':
                checkPermisionAjaxModal('buy_item,dispute_administration');

                $this->show_cancel_dispute_form((int) privileged_user_id(), (int) uri()->segment(4));

                break;
            case 'details':
                checkPermisionAjaxModal('manage_disputes,dispute_administration');

                $this->show_dispute_details_form((int) privileged_user_id(), (int) uri()->segment(4));

                break;
            case 'add_notice':
                checkPermisionAjaxModal('manage_disputes,dispute_administration');

                $this->show_add_notice_form((int) privileged_user_id(), (int) uri()->segment(4));

                break;
            default:
                messageInModal('The provided path is not found.');

                break;
        }
    }

    public function ajax_operation()
    {
        checkDomainForGroup();
        checkIsAjax();
        checkIsLoggedAjax();
        checkGroupExpire('ajax');

        $this->load->model('Dispute_Model', 'dispute');

        switch (uri()->segment(3)) {
            case 'add_item_dispute':
                checkPermisionAjax('buy_item');
                checkPermisionAjax('manage_disputes');

                $this->create_dispute((int) privileged_user_id(), (int) arrayGet($_POST, 'order_id'), (int) arrayGet($_POST, 'ordered_id'));

                break;
            case 'add_order_dispute':
                checkPermisionAjax('buy_item');
                checkPermisionAjax('manage_disputes');

                $this->create_dispute((int) privileged_user_id(), (int) arrayGet($_POST, 'order_id'));

                break;
            case 'edit':
                checkPermisionAjax('manage_disputes,dispute_administration');

                $this->edit_dispute((int) privileged_user_id(), (int) arrayGet($_POST, 'dispute'));

                break;
            case 'init':
                checkPermisionAjax('dispute_administration');

                $this->open_dispute((int) privileged_user_id(), (int) arrayGet($_POST, 'dispute'));

                break;
            case 'resolve':
                checkPermisionAjax('buy_item');
                checkPermisionAjax('manage_disputes');

                $this->resolve_dispute((int) privileged_user_id(), (int) arrayGet($_POST, 'dispute'));

                break;
            case 'cancel':
                checkPermisionAjax('buy_item,dispute_administration');

                $this->cancel_dispute((int) privileged_user_id(), (int) arrayGet($_POST, 'disput'));

                break;

            case 'close':
                checkPermisionAjax('dispute_administration');

                $this->close_dispute((int) privileged_user_id(), (int) arrayGet($_POST, 'disput'));

                break;
            case 'add_notice':
                checkPermisionAjax('manage_disputes,dispute_administration');

                $this->add_dispute_notice((int) privileged_user_id(), (int) arrayGet($_POST, 'disput'));

                break;
            case 'upload_photo':
                checkPermisionAjax('manage_disputes,dispute_administration');

                $this->upload_temporary_files();

                break;
            case 'delete_temp_photo':
                checkPermisionAjax('manage_disputes,dispute_administration');

                $this->delete_temporary_file(request()->request->get('file'));

                break;
            case 'delete_photo':
                checkPermisionAjaxModal('manage_disputes,dispute_administration');

                $this->delete_photo((int) privileged_user_id(), uri()->segment(4), uri()->segment(5));

                break;
            case 'delete_video':
                checkPermisionAjaxModal('manage_disputes,dispute_administration');

                $this->delete_video((int) privileged_user_id(), uri()->segment(4), uri()->segment(5));

                break;
            default:
                jsonResponse('The provided path is not found.');

                break;
        }
    }

    public function ajax_my_disputes_dt()
    {
        checkDomainForGroup();
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkGroupExpire('dt');
        checkPermisionAjaxDT('manage_disputes');

        // Get the request
        $request = request();

        //region Query conditions
        $sort_by = flat_dt_ordering($request->request->all(), array(
            'dt_id'                => 'od.id',
            'dt_order'             => 'od.id_order',
            'dt_money_back'        => 'od.money_back_request',
            'dt_status'            => 'od.status',
            'dt_date_time_changed' => 'od.change_date',
            'dt_date_time'         => 'od.date_time',
            'dt_created'           => 'od.date_time',
            'dt_updated'           => 'od.change_date',
            'dt_details'           => 'od.id',
        ));
        $user_id = (int) privileged_user_id();
        $user_group = user_group_type();
        $params = array_merge(
            array(
                'per_p' => $request->request->getInt('iDisplayLength', 0),
                'start' => $request->request->getInt('iDisplayStart', 0),
            ),
            array_filter(array(
                'sort_by'    => !empty($sort_by) ? $sort_by : null,
                'status'     => 'Buyer' !== $user_group ? array('processing', 'resolved', 'closed') : null,
                'id_buyer'   => 'Buyer' === $user_group ? $user_id : null,
                'id_seller'  => 'Seller' === $user_group || 'Company Staff' === $user_group ? $user_id : null,
                'id_shipper' => 'Shipper' === $user_group || 'Shipper Staff' === $user_group ? $user_id : null,
            )),
            dtConditions($_POST, array(
                array('as' => 'id_buyer',            'key' => 'buyer',        'type' => fn ($buyer_id) => 'Buyer' !== $user_group ? (int) $buyer_id : null),
                array('as' => 'id_seller',           'key' => 'seller',       'type' => fn ($seller_id) => !in_array($user_group, ['Seller', 'Company Staff']) ? (int) $seller_id : null),
                array('as' => 'id_shipper',          'key' => 'shipper',      'type' => fn ($shipper_id) => !in_array($user_group, ['Shipper', 'Shipper Staff']) ? (int) $shipper_id : null),
                array('as' => 'ep_manager',          'key' => 'manager',      'type' => 'toId|intval:10'),
                array('as' => 'id_snapshot',         'key' => 'snapshot',     'type' => 'toId|intval:10'),
                array('as' => 'id_disput',           'key' => 'dispute',      'type' => 'toId|intval:10'),
                array('as' => 'id_order',            'key' => 'order',        'type' => 'toId|intval:10'),
                array('as' => 'search',              'key' => 'keywords',     'type' => 'cleanInput|cut_str:200'),
                array('as' => 'start_date',          'key' => 'created_from', 'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'finish_date',         'key' => 'created_to',   'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'start_date_changed',  'key' => 'updated_from', 'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'finish_date_changed', 'key' => 'updated_to',   'type' => fn (string $v) => $this->parametrifyFilterDateAsString($v, 'm/d/Y', 'Y-m-d')),
                array('as' => 'status',              'key' => 'status',       'type' => function ($status) use ($user_group) {
                    if (!isset(model('dispute')->statuses[$status])) {
                        return null;
                    }

                    return 'Buyer' !== $user_group && !in_array($status, array('processing', 'closed', 'resolved')) ? null : cleanInput($status);
                }),
            ))
        );
        //endregion Query conditions

        //region Prepare output
        /** @var Dispute_Model $model */
        $model = model(Dispute_Model::class);
        $list = array();
        $disputes = $model->get_disputes($params);
        $records_total = $model->get_disputes_count($params);
        if (!empty($disputes)) {
            $buyers_ids = array_filter(array_column($disputes, 'id_buyer', 'id_buyer'));
            $sellers_ids = array_filter(array_column($disputes, 'id_seller', 'id_seller'));
            $shippers_ids = array_filter(array_column($disputes, 'id_shipper', 'id_shipper'));
            $managers_ids = array_filter(array_column($disputes, 'id_ep_manager', 'id_ep_manager'));
            $users_ids = array_replace($buyers_ids, $sellers_ids, $shippers_ids, $managers_ids);
            $users = arrayByKey($model->get_users($users_ids), 'idu');
            $sellers = arrayByKey($model->get_sellers_companies($sellers_ids), 'id_user');
            $shippers = arrayByKey($model->get_shippers_companies($shippers_ids), 'id_user');
            $list = $this->get_user_disputes_list(
                $disputes,
                $users,
                $sellers,
                $shippers
            );
        }
        //endregion Prepare output

        jsonResponse(null, 'success', array(
            'sEcho'                => $request->request->getInt('sEcho', 0),
            'aaData'               => $list,
            'iTotalRecords'        => $records_total,
            'iTotalDisplayRecords' => $records_total,
        ));
    }

    public function ajax_admin_disputes_dt()
    {
        checkDomainForGroup();
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('dispute_administration');

        $id_user = (int) privileged_user_id();
        $statuses = model('dispute')->statuses;

        $params = ['ep_manager' => $id_user];

        $perPage = (int) $_POST['iDisplayLength'];
        if ($perPage > 0) {
            $params['per_p'] = $perPage;
            $params['start'] = (int) $_POST['iDisplayStart'];
        }

        $sort_by = flat_dt_ordering($_POST, array(
            'dt_id'                => 'od.id',
            'dt_order'             => 'od.id_order',
            'dt_money_back'        => 'od.money_back_request',
            'dt_status'            => 'od.status',
            'dt_date_time_changed' => 'od.change_date',
            'dt_date_time'         => 'od.date_time',
        ));

        if (!empty($sort_by)) {
            $params['sort_by'] = $sort_by;
        }

        if (isset($_POST['buyer'])) {
            $params['id_buyer'] = (int) $_POST['buyer'];
        }

        if (isset($_POST['seller'])) {
            $params['id_seller'] = (int) $_POST['seller'];
        }

        if (isset($_POST['shipper'])) {
            $params['id_shipper'] = (int) $_POST['shipper'];
        }

        if (isset($_POST['status'])) {
            $status = cleanInput($_POST['status']);
            if (isset($statuses[$status])) {
                $params['status'] = $status;
            }
        }

        if (isset($_POST['start'])) {
            $params['start_date'] = $this->parametrifyFilterDateAsString((string) $_POST['start'], 'm/d/Y', 'Y-m-d');
        }

        if (isset($_POST['finish'])) {
            $params['finish_date'] = $this->parametrifyFilterDateAsString((string) $_POST['finish'], 'm/d/Y', 'Y-m-d');
        }

        if (isset($_POST['start_changed'])) {
            $params['start_date_changed'] = $this->parametrifyFilterDateAsString((string) $_POST['start_changed'], 'm/d/Y', 'Y-m-d');
        }

        if (isset($_POST['finish_changed'])) {
            $params['finish_date_changed'] = $this->parametrifyFilterDateAsString((string) $_POST['finish_changed'], 'm/d/Y', 'Y-m-d');
        }

        if (isset($_POST['keywords'])) {
            $params['search'] = cleanInput(cut_str($_POST['keywords']));
        }

        if (isset($_POST['snapshot'])) {
            $params['id_snapshot'] = (int) $_POST['snapshot'];
        }

        if (isset($_POST['order'])) {
            $params['id_order'] = (int) $_POST['order'];
        }

        if (isset($_POST['id_disput'])) {
            $params['id_disput'] = (int) $_POST['id_disput'];
        }

        /** @var Dispute_Model $disputeModel */
        $disputeModel = model(Dispute_Model::class);

        $disputes = $disputeModel->get_disputes($params);
        $records_total = $disputeModel->get_disputes_count($params);

        $output = array(
            'sEcho'                => intval($_POST['sEcho']),
            'iTotalRecords'        => $records_total,
            'iTotalDisplayRecords' => $records_total,
            'aaData'               => array(),
        );

        if (empty($disputes)) {
            jsonDTResponse('', $output, 'success');
        }

        $users = array();
        $users_roles = array();
        $sellers = array();
        $shippers = array();
        foreach ($disputes as $dispute) {
            $sellers[$dispute['id_seller']] = $dispute['id_seller'];
            $users[$dispute['id_buyer']] = $dispute['id_buyer'];
            $users[$dispute['id_seller']] = $dispute['id_seller'];
            $users[$dispute['id_ep_manager']] = $dispute['id_ep_manager'];

            $users_roles[$dispute['id_buyer']] = 'buyer';
            $users_roles[$dispute['id_seller']] = 'seller';
            $users_roles[$dispute['id_ep_manager']] = 'ep_manager';

            if ($dispute['id_shipper'] > 0) {
                $shippers[$dispute['id_shipper']] = $dispute['id_shipper'];
                $users[$dispute['id_shipper']] = $dispute['id_shipper'];
                $users_roles[$dispute['id_shipper']] = 'shipper';
            }
        }

        if (!empty($users)) {
            $users = arrayByKey($disputeModel->get_users($users), 'idu');
        }

        if (!empty($sellers)) {
            $sellers_companies = arrayByKey($disputeModel->get_sellers_companies($sellers), 'id_user');
        }

        if (!empty($shippers)) {
            $shippers_companies = arrayByKey($disputeModel->get_shippers_companies($shippers), 'id_user');
        }
        /** @var FilesystemProviderInterface $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $storageProvider->storage('public.storage');
        foreach ($disputes as $dispute) {
            $photos_prepared = array(
                'seller'     => array('photos' => array()),
                'buyer'      => array('photos' => array()),
                'shipper'    => array('photos' => array()),
                'ep_manager' => array('photos' => array()),
            );

            $photos = json_decode($dispute['photos'], true);
            if (!empty($photos)) {
                foreach ($photos as $user => $photo_arr) {
                    $photos_prepared[$users_roles[$user]]['user_name'] = $users[$user]['user_name'];

                    foreach ($photo_arr as $key => $photo) {
                        $imgThumb = $storage->url(DisputeFilePathGenerator::thumbImage($dispute['id_order'], $photo, DisputePhotoThumb::SMALL()));
                        $imgLink = $storage->url(DisputeFilePathGenerator::imagePath($dispute['id_order'], $photo));
                        $photos_prepared[$users_roles[$user]]['photos'][] = '<div class="img-list-b pull-left mr-5 mb-5 relative-b one-image">
                                                                                ' . (($user == $id_user) ? '<a title="Delete image" class="ep-icon ep-icon_remove txt-red fs-14 confirm-dialog" data-message="Are you sure want to delte this image?" data-callback="delete_my_image"></a>' : '') . '
                                                                                <a class="fancyboxGallery display-b w-80  mr-5" href="' . $imgLink . '" rel="gallery' . $dispute['id_order'] . '_' . $users_roles[$user] . '"><img class="photo" src="' . $imgThumb . '" alt="photo" data-file="' . $photo . '" data-id="' . $dispute['id'] . '"></a>
                                                                            </div>';
                    }
                }
            }

            $videos_prepared = array(
                'seller'     => array('videos' => array()),
                'buyer'      => array('videos' => array()),
                'shipper'    => array('videos' => array()),
                'ep_manager' => array('videos' => array()),
            );

            $videos = json_decode($dispute['videos'], true);
            if (!empty($videos)) {
                foreach ($videos as $user => $video_arr) {
                    $videos_prepared[$users_roles[$user]]['user_name'] = $users[$user]['user_name'];

                    foreach ($video_arr as $key => $video) {
                        $imgUrl = $storage->url(DisputeFilePathGenerator::videoImage($dispute['id_order'], $video['thumb']));
                        $videos_prepared[$users_roles[$user]]['videos'][] = '<div class="img-b video-container img-list-b pull-left mr-5 mb-5 relative-b">
                                                                                ' . (($user == $id_user) ? '<a title="Delete video" class="ep-icon ep-icon_remove txt-red fs-14 confirm-dialog" data-message="Are you sure want to delte this video?" data-callback="delete_my_video" data-id="' . $video['id'] . '" data-id_dispute="' . $dispute['id'] . '"></a>' : '') . '
                                                                                <a href="/video/popup_forms/view_video/' . $video['type'] . '/' . $video['id'] . '" data-title="View video" class="fancybox.ajax fancyboxValidateModalDT wr-video-link w-80">
                                                                                    <div class="bg"><i class="ep-icon ep-icon_play"></i></div>
                                                                                    <div class="img-b">
                                                                                        <img src="' . $imgUrl . '" alt="View video"/>
                                                                                    </div>
                                                                                </a>
                                                                            </div>';
                    }
                }
            }

            $reason = '';
            $reason_arr = json_decode($dispute['reason'], true);
            if (!empty($reason_arr)) {
                $reason = 'By <strong>' . $reason_arr['add_by'] . '</strong>, ' . $reason_arr['add_date'] . '<br />' . $reason_arr['reason'];
            }

            $item = '<div class="clearfix mb-10">
                        <a class="txt-green ep-icon ep-icon_filter dt_filter mb-0" data-title="Order" title="Filter by order" data-value-text="' . orderNumber($dispute['id_order']) . '" data-value="' . $dispute['id_order'] . '" data-name="order"></a> Dispute on Order:
                        <a href="' . getUrlForGroup('order/popups_order/order_detail/' . $dispute['id_order']) . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($dispute['id_order']) . '</a>
                    </div>';
            if ($dispute['item_title'] && $dispute['id_snapshot']) {
                $item .= '<div class="clearfix">
                            <a class="txt-green ep-icon ep-icon_filter dt_filter mb-0" data-title="Item" title="Filter by item" data-value-text="' . $dispute['item_title'] . '" data-value="' . $dispute['id_snapshot'] . '" data-name="snapshot"></a>
                            About item: <a href="' . getUrlForGroup('items/ordered/' . strForURL($dispute['item_title']) . '-' . $dispute['ordered_id']) . '" target="_blank">' . $dispute['item_title'] . '</a>
                        </div>';
            }

            $actions = array();
            if ('init' == $dispute['status']) {
                $actions[] = '<li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="' . getUrlForGroup('dispute/popup_forms/init/' . $dispute['id']) . '" data-title="Start dispute">
                                    <span class="ep-icon ep-icon_low"></span> Start dispute
                                </a>
                            </li>
                            <li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="' . getUrlForGroup('dispute/popup_forms/cancel/' . $dispute['id']) . '" data-title="Start dispute">
                                    <span class="ep-icon ep-icon_remove-circle"></span> Cancel dispute
                                </a>
                            </li>';
            }

            if ('processing' === $dispute['status'] || 'resolved' === $dispute['status']) {
                $actions[] = '<li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="' . getUrlForGroup('dispute/popup_forms/close/' . $dispute['id']) . '" data-title="Close dispute">
                                    <span class="ep-icon ep-icon_ok-circle"></span> Close dispute
                                </a>
                            </li>';
            }
            if ('processing' === $dispute['status']) {
                $actions[] = '<li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="' . getUrlForGroup('dispute/popup_forms/edit/' . $dispute['id']) . '" data-title="Update dispute">
                                    <span class="ep-icon ep-icon_pencil"></span> Update dispute
                                </a>
                            </li>';
            }

            $actions[] = '<li>
                            <a class="fancyboxValidateModalDT fancybox.ajax" href="' . getUrlForGroup('dispute/popup_forms/add_notice/' . $dispute['id']) . '" data-title="Dispute notices">
                                <span class="ep-icon ep-icon_comments-stroke"></span> Dispute notices
                            </a>
                        </li>';

            $shipper_info = '';
            if ($dispute['id_shipper'] > 0) {
                //TODO: admin chat hidden
                $btnChatShipper = new ChatButton(['hide' => true, 'recipient' => $dispute['id_shipper'], 'recipientStatus' => $users[$dispute['id_shipper']]['user_status'], 'module' => 4, 'item' => $dispute['id']], ['classes' => 'btn-chat-now', 'text' => '']);
                $btnChatShipperView = $btnChatShipper->button();

                $shipper_info = '<div class="pull-left w-100pr">
                                    <a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Freight Forwarder" title="Filter by ' . $shippers_companies[$dispute['id_shipper']]['co_name'] . '" data-value-text="' . $shippers_companies[$dispute['id_shipper']]['co_name'] . '" data-value="' . $dispute['id_shipper'] . '" data-name="shipper"></a>
                                    <a class="ep-icon ep-icon_building" title="View freight forwarder page" href="' . getUrlForGroup('shipper/' . strForURL($shippers_companies[$dispute['id_shipper']]['co_name'] . ' ' . $shippers_companies[$dispute['id_shipper']]['id'])) . '" target="_blank"></a>
                                    '.$btnChatShipperView.'
                                </div>
                                <div class="pull-left w-100pr">
                                    ' . $shippers_companies[$dispute['id_shipper']]['co_name'] . '<br>(' . $users[$dispute['id_shipper']]['user_name'] . ')
                                </div>';
            }

            $request_money = array();
            if (compareFloatNumbers($dispute['money_back_request'], 0, '>')) {
                $request_money[] = '<span class="fs-10">Requested:</span><br>' . get_price($dispute['money_back_request']);
            }

            if (compareFloatNumbers($dispute['money_back'], 0, '>')) {
                $request_money[] = '<span class="fs-10">Confirmed:</span><br>' . get_price($dispute['money_back']);
            }

            //TODO: admin chat hidden
            $btnChatSeller = new ChatButton(['hide' => true, 'recipient' => $dispute['id_seller'], 'recipientStatus' => $users[$dispute['id_seller']]['user_status'], 'module' => 4, 'item' => $dispute['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatSellerView = $btnChatSeller->button();

            //TODO: admin chat hidden
            $btnChatBuyer = new ChatButton(['hide' => true, 'recipient' => $dispute['id_buyer'], 'recipientStatus' => $users[$dispute['id_buyer']]['user_status'], 'module' => 4, 'item' => $dispute['id']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatBuyerView = $btnChatBuyer->button();

            $output['aaData'][] = array(
                'dt_id'                 => $dispute['id'] . "<br/><a rel='user_details' title='View details' class='ep-icon ep-icon_plus'></a>",
                'dt_dispute'            => $item,
                'dt_buyer'              => '<div class="pull-left w-100pr">
                                                <a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Buyer" title="Filter by ' . $users[$dispute['id_buyer']]['user_name'] . '" data-value-text="' . $users[$dispute['id_buyer']]['user_name'] . '" data-value="' . $dispute['id_buyer'] . '" data-name="buyer"></a>
                                                <a class="ep-icon ep-icon_user" title="View personal page of ' . $users[$dispute['id_buyer']]['user_name'] . '" href="' . getUrlForGroup('usr/' . strForURL($users[$dispute['id_buyer']]['user_name']) . '-' . $dispute['id_buyer']) . '"></a>
                                                '.$btnChatSellerView.'
                                            </div>
                                            <div class="pull-left w-100pr">' . $users[$dispute['id_buyer']]['user_name'] . '</div>',
                'dt_date_time'          => getDateFormat($dispute['date_time'], 'Y-m-d H:i:s'),
                'dt_date_time_changed'  => getDateFormat($dispute['change_date'], 'Y-m-d H:i:s'),
                'dt_seller'             => '<div class="pull-left w-100pr">
                                                <a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Seller" title="Filter by ' . $users[$dispute['id_seller']]['user_name'] . '" data-value-text="' . $users[$dispute['id_seller']]['user_name'] . '" data-value="' . $dispute['id_seller'] . '" data-name="seller"></a>
                                                <a class="ep-icon ep-icon_building" title="View company page of ' . $users[$dispute['id_seller']]['user_name'] . '" href="' . getCompanyURL($sellers_companies[$dispute['id_seller']]) . '" target="_blank"></a>
                                                <a class="ep-icon ep-icon_user" title="View personal page of ' . $users[$dispute['id_seller']]['user_name'] . '" href="' . getUrlForGroup('usr/' . strForURL($users[$dispute['id_seller']]['user_name']) . '-' . $dispute['id_seller']) . '"></a>
                                                '.$btnChatBuyerView.'
                                            </div>
                                            <div class="pull-left w-100pr">
                                                ' . $users[$dispute['id_seller']]['user_name'] . '<br>(' . $sellers_companies[$dispute['id_seller']]['name_company'] . ')
                                            </div>',
                'dt_shipper'            => $shipper_info,
                'dt_comment'            => $dispute['comment'],
                'dt_money_back'         => !empty($request_money) ? implode('<br>', $request_money) : '&mdash;',
                'dt_status'             => '<div class="tal">
                                                <a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Status" title="Filter by status" data-value-text="' . $statuses[$dispute['status']]['title'] . '" data-value="' . $dispute['status'] . '" data-name="status"></a>
                                            </div>
                                            <span>
                                                <i class="ep-icon ' . $statuses[$dispute['status']]['icon'] . ' fs-30 mr-0"></i><br>
                                                ' . $statuses[$dispute['status']]['title'] . '
                                            </span>',
                'dt_actions'            => '<div class="dropup">
                                                <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                                    ' . implode('', $actions) . '
                                                </ul>
                                            </div>',
                'dt_photos'             => $photos_prepared,
                'dt_videos'             => $videos_prepared,
                'dt_reason'             => $reason,
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_admin_all_disputes_dt()
    {
        checkDomainForGroup();
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('read_all_disputes');

        /** @var Dispute_Model $disputeModel */
        $disputeModel = model(Dispute_Model::class);

        $statuses = $disputeModel->statuses;
        $request = request()->request;

        $disputeConditions = array_merge(
            dtConditions($request->all(), [
                ['as' => 'id_buyer',                'key' => 'buyer',               'type' => 'int'],
                ['as' => 'id_seller',               'key' => 'seller',              'type' => 'int'],
                ['as' => 'id_shipper',              'key' => 'shipper',             'type' => 'int'],
                ['as' => 'status',                  'key' => 'status',              'type' => fn ($status) => $statuses[$status] ? $status : null],
                ['as' => 'start_date',              'key' => 'start',               'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'finish_date',             'key' => 'finish',              'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'start_date_changed',      'key' => 'start_changed',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'finish_date_changed',     'key' => 'finish_changed',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'search',                  'key' => 'keywords',            'type' => 'cut_str|trim'],
                ['as' => 'id_snapshot',             'key' => 'snapshot',            'type' => 'int'],
                ['as' => 'id_order',                'key' => 'order',               'type' => 'int'],
                ['as' => 'id_disput',               'key' => 'id_disput',           'type' => 'int'],
            ]),
            array_filter(
                [
                    'per_p'     => $request->getInt('iDisplayLength', 10),
                    'start'     => abs($request->getInt('iDisplayStart')),
                    'sort_by'   => dtOrdering(
                                        $request->all(),
                                        [
                                            'dt_id'                => 'od.id',
                                            'dt_order'             => 'od.id_order',
                                            'dt_money_back'        => 'od.money_back_request',
                                            'dt_status'            => 'od.status',
                                            'dt_date_time_changed' => 'od.change_date',
                                            'dt_date_time'         => 'od.date_time',
                                        ],
                                        fn ($ordering) => $ordering['column'] . '-' . $ordering['direction']
                                    ) ?: null,
                ]
            ),
        );

        $disputes = $disputeModel->get_disputes($disputeConditions);
        $totalDisputes = $disputeModel->get_disputes_count($disputeConditions);

        $output = [
            'iTotalDisplayRecords' => $totalDisputes,
            'iTotalRecords'        => $totalDisputes,
            'aaData'               => [],
            'sEcho'                => $request->getInt('sEcho'),
        ];

        if (empty($disputes)) {
            jsonDTResponse('', $output, 'success');
        }

        $users = array();
        $users_roles = array();
        $sellers = array();
        $shippers = array();
        foreach ($disputes as $dispute) {
            $sellers[$dispute['id_seller']] = $dispute['id_seller'];
            $users[$dispute['id_buyer']] = $dispute['id_buyer'];
            $users[$dispute['id_seller']] = $dispute['id_seller'];
            $users[$dispute['id_ep_manager']] = $dispute['id_ep_manager'];

            $users_roles[$dispute['id_buyer']] = 'buyer';
            $users_roles[$dispute['id_seller']] = 'seller';
            $users_roles[$dispute['id_ep_manager']] = 'ep_manager';

            if ($dispute['id_shipper'] > 0) {
                $shippers[$dispute['id_shipper']] = $dispute['id_shipper'];
                $users[$dispute['id_shipper']] = $dispute['id_shipper'];
                $users_roles[$dispute['id_shipper']] = 'shipper';
            }
        }

        if (!empty($users)) {
            $users = array_column($disputeModel->get_users($users), null, 'idu');
        }

        if (!empty($sellers)) {
            $sellers_companies = array_column($disputeModel->get_sellers_companies($sellers), null, 'id_user');
        }

        if (!empty($shippers)) {
            $shippers_companies = array_column($disputeModel->get_shippers_companies($shippers), null, 'id_user');
        }
        /** @var FilesystemProviderInterface $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $storageProvider->storage('public.storage');

        foreach ($disputes as $dispute) {
            $isAssignedWithMe = is_my($dispute['id_ep_manager']);
            $photos_prepared = [
                'seller'     => ['photos' => []],
                'buyer'      => ['photos' => []],
                'shipper'    => ['photos' => []],
                'ep_manager' => ['photos' => []],
            ];

            $photos = json_decode($dispute['photos'], true);
            if (!empty($photos)) {
                foreach ($photos as $user => $photo_arr) {
                    $photos_prepared[$users_roles[$user]]['user_name'] = $users[$user]['user_name'];

                    foreach ($photo_arr as $key => $photo) {
                        $imgThumb = $storage->url(DisputeFilePathGenerator::thumbImage($dispute['id_order'], $photo, DisputePhotoThumb::SMALL()));
                        $imgLink = $storage->url(DisputeFilePathGenerator::imagePath($dispute['id_order'], $photo));
                        $photos_prepared[$users_roles[$user]]['photos'][] = '<div class="img-list-b pull-left mr-5 mb-5 relative-b one-image">
                                                                                <a class="fancyboxGallery display-b w-80 mr-5" href="' . $imgLink . '" rel="gallery' . $dispute['id_order'] . '_' . $users_roles[$user] . '"><img class="photo" src="' . $imgThumb . '" alt="photo" data-file="' . $photo . '" data-id="' . $dispute['id'] . '"></a>
                                                                            </div>';
                    }
                }
            }

            $videos_prepared = array(
                'seller'     => ['videos' => []],
                'buyer'      => ['videos' => []],
                'shipper'    => ['videos' => []],
                'ep_manager' => ['videos' => []],
            );

            $videos = json_decode($dispute['videos'], true);
            if (!empty($videos)) {
                foreach ($videos as $user => $video_arr) {
                    $videos_prepared[$users_roles[$user]]['user_name'] = $users[$user]['user_name'];

                    foreach ($video_arr as $key => $video) {
                        $imgUrl = $storage->url(DisputeFilePathGenerator::videoImage($dispute['id_order'], $video['thumb']));
                        $videos_prepared[$users_roles[$user]]['videos'][] = '<div class="img-b video-container img-list-b pull-left mr-5 mb-5 relative-b">
                                                                                <a href="/video/popup_forms/view_video/' . $video['type'] . '/' . $video['id'] . '" data-title="View video" class="fancybox.ajax fancyboxValidateModalDT wr-video-link w-80">
                                                                                    <div class="bg"><i class="ep-icon ep-icon_play"></i></div>
                                                                                    <div class="img-b">
                                                                                        <img src="' . $imgUrl . '" alt="View video"/>
                                                                                    </div>
                                                                                </a>
                                                                            </div>';
                    }
                }
            }

            $reason = '';
            $reason_arr = json_decode($dispute['reason'], true);
            if (!empty($reason_arr)) {
                $reason = 'By <strong>' . $reason_arr['add_by'] . '</strong>, ' . $reason_arr['add_date'] . '<br />' . $reason_arr['reason'];
            }

            $item = '<div class="clearfix mb-10">
                        <a class="txt-green ep-icon ep-icon_filter dt_filter mb-0" data-title="Order" title="Filter by order" data-value-text="' . orderNumber($dispute['id_order']) . '" data-value="' . $dispute['id_order'] . '" data-name="order"></a> Dispute on Order:
                        <a href="' . getUrlForGroup('order/popups_order/order_detail/' . $dispute['id_order']) . '" class="fancybox.ajax fancybox" data-title="Order details">' . orderNumber($dispute['id_order']) . '</a>
                    </div>';
            if ($dispute['item_title'] && $dispute['id_snapshot']) {
                $item .= '<div class="clearfix">
                            <a class="txt-green ep-icon ep-icon_filter dt_filter mb-0" data-title="Item" title="Filter by item" data-value-text="' . $dispute['item_title'] . '" data-value="' . $dispute['id_snapshot'] . '" data-name="snapshot"></a>
                            About item: <a href="' . getUrlForGroup('items/ordered/' . strForURL($dispute['item_title']) . '-' . $dispute['ordered_id']) . '" target="_blank">' . $dispute['item_title'] . '</a>
                        </div>';
            }

            $shipper_info = '';
            if ($dispute['id_shipper'] > 0) {
                $shipper_info = '<div class="pull-left w-100pr">
                                    <a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Freight Forwarder" title="Filter by ' . $shippers_companies[$dispute['id_shipper']]['co_name'] . '" data-value-text="' . $shippers_companies[$dispute['id_shipper']]['co_name'] . '" data-value="' . $dispute['id_shipper'] . '" data-name="shipper"></a>
                                    <a class="ep-icon ep-icon_building" title="View freight forwarder page" href="' . getUrlForGroup('shipper/' . strForURL($shippers_companies[$dispute['id_shipper']]['co_name'] . ' ' . $shippers_companies[$dispute['id_shipper']]['id'])) . '" target="_blank"></a>
                                </div>
                                <div class="pull-left w-100pr">
                                    ' . $shippers_companies[$dispute['id_shipper']]['co_name'] . '<br>(' . $users[$dispute['id_shipper']]['user_name'] . ')
                                </div>';
            }

            $request_money = array();
            if (compareFloatNumbers($dispute['money_back_request'], 0, '>')) {
                $request_money[] = '<span class="fs-10">Requested:</span><br>' . get_price($dispute['money_back_request']);
            }

            if (compareFloatNumbers($dispute['money_back'], 0, '>')) {
                $request_money[] = '<span class="fs-10">Confirmed:</span><br>' . get_price($dispute['money_back']);
            }

            $actionsBtnTemplates = [
                'ajax' => <<<ACTION_BTN
                    <li>
                        <a class="fancyboxValidateModalDT fancybox.ajax" href="%s" data-title="%s">
                            <span class="%s"></span> %s
                        </a>
                    </li>
                ACTION_BTN,
                'systmess' => <<<SYSTEM_MESSAGE_BTN
                    <li>
                        <a class="call-systmess txt-gray-light" data-message="Only assigned Order Manager can perform this action" data-type="info" title="%s">
                            <span class="%s"></span> %s
                        </a>
                    </li>
                SYSTEM_MESSAGE_BTN,
            ];

            $actions = array_filter([
                'start_dispute' => 'init' !== $dispute['status']
                    ? null
                    : ($isAssignedWithMe
                        ? sprintf($actionsBtnTemplates['ajax'], __SITE_URL . 'dispute/popup_forms/init/' . $dispute['id'], 'Start dispute', 'ep-icon ep-icon_low', 'Start dispute')
                        : sprintf($actionsBtnTemplates['systmess'], 'Start dispute', 'ep-icon ep-icon_low', 'Start dispute')
                    ),
                'cancel_dispute' => 'init' !== $dispute['status']
                    ? null
                    : ($isAssignedWithMe
                        ? sprintf($actionsBtnTemplates['ajax'], __SITE_URL . 'dispute/popup_forms/cancel/' . $dispute['id'], 'Cancel dispute', 'ep-icon ep-icon_remove-circle', 'Cancel dispute')
                        : sprintf($actionsBtnTemplates['systmess'], 'Cancel dispute', 'ep-icon ep-icon_remove-circle', 'Cancel dispute')
                    ),
                'close_dispute' => !in_array($dispute['status'], ['processing', 'resolved'])
                    ? null
                    : ($isAssignedWithMe
                        ? sprintf($actionsBtnTemplates['ajax'], __SITE_URL . 'dispute/popup_forms/close/' . $dispute['id'], 'Close dispute', 'ep-icon ep-icon_ok-circle', 'Close dispute')
                        : sprintf($actionsBtnTemplates['systmess'], 'Close dispute', 'ep-icon ep-icon_ok-circle', 'Close dispute')
                    ),
                'update_dispute' => 'processing' !== $dispute['status']
                    ? null
                    : ($isAssignedWithMe
                        ? sprintf($actionsBtnTemplates['ajax'], __SITE_URL . 'dispute/popup_forms/edit/' . $dispute['id'], 'Update dispute', 'ep-icon ep-icon_pencil', 'Update dispute')
                        : sprintf($actionsBtnTemplates['systmess'], 'Update dispute', 'ep-icon ep-icon_pencil', 'Update dispute')
                    ),
                'disput_notices' => sprintf($actionsBtnTemplates['ajax'], __SITE_URL . 'dispute/popup_forms/add_notice/' . $dispute['id'], 'Dispute notices', 'ep-icon ep-icon_comments-stroke', 'Dispute notices'),
            ]);

            $output['aaData'][] = array(
                'dt_id'                 => $dispute['id'] . "<br/><a rel='user_details' title='View details' class='ep-icon ep-icon_plus'></a>",
                'dt_dispute'            => $item,
                'dt_buyer'              => '<div class="pull-left w-100pr">
                                                <a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Buyer" title="Filter by ' . $users[$dispute['id_buyer']]['user_name'] . '" data-value-text="' . $users[$dispute['id_buyer']]['user_name'] . '" data-value="' . $dispute['id_buyer'] . '" data-name="buyer"></a>
                                                <a class="ep-icon ep-icon_user" title="View personal page of ' . $users[$dispute['id_buyer']]['user_name'] . '" href="' . getUrlForGroup('usr/' . strForURL($users[$dispute['id_buyer']]['user_name']) . '-' . $dispute['id_buyer']) . '"></a>
                                            </div>
                                            <div class="pull-left w-100pr">' . $users[$dispute['id_buyer']]['user_name'] . '</div>',
                'dt_date_time'          => getDateFormat($dispute['date_time'], 'Y-m-d H:i:s'),
                'dt_date_time_changed'  => getDateFormat($dispute['change_date'], 'Y-m-d H:i:s'),
                'dt_seller'             => '<div class="pull-left w-100pr">
                                                <a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Seller" title="Filter by ' . $users[$dispute['id_seller']]['user_name'] . '" data-value-text="' . $users[$dispute['id_seller']]['user_name'] . '" data-value="' . $dispute['id_seller'] . '" data-name="seller"></a>
                                                <a class="ep-icon ep-icon_building" title="View company page of ' . $users[$dispute['id_seller']]['user_name'] . '" href="' . getCompanyURL($sellers_companies[$dispute['id_seller']]) . '" target="_blank"></a>
                                                <a class="ep-icon ep-icon_user" title="View personal page of ' . $users[$dispute['id_seller']]['user_name'] . '" href="' . getUrlForGroup('usr/' . strForURL($users[$dispute['id_seller']]['user_name']) . '-' . $dispute['id_seller']) . '"></a>
                                            </div>
                                            <div class="pull-left w-100pr">
                                                ' . $users[$dispute['id_seller']]['user_name'] . '<br>(' . $sellers_companies[$dispute['id_seller']]['name_company'] . ')
                                            </div>',
                'dt_shipper'            => $shipper_info,
                'dt_comment'            => $dispute['comment'],
                'dt_money_back'         => empty($request_money) ? '&mdash;' : implode('<br>', $request_money),
                'dt_status'             => '<div class="tal">
                                                <a class="txt-green ep-icon ep-icon_filter dt_filter" data-title="Status" title="Filter by status" data-value-text="' . $statuses[$dispute['status']]['title'] . '" data-value="' . $dispute['status'] . '" data-name="status"></a>
                                            </div>
                                            <span>
                                                <i class="ep-icon ' . $statuses[$dispute['status']]['icon'] . ' fs-30 mr-0"></i><br>
                                                ' . $statuses[$dispute['status']]['title'] . '
                                            </span>',
                'dt_photos'             => $photos_prepared,
                'dt_videos'             => $videos_prepared,
                'dt_reason'             => $reason,
                'dt_actions'            => sprintf(
                    <<<DROPDOWN_ACTIONS
                        <div class="dropup">
                            <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                %s
                            </ul>
                        </div>
                    DROPDOWN_ACTIONS,
                    implode('', $actions)
                ),
            );
        }

        jsonResponse('', 'success', $output);
    }

    public function download_image()
    {
        checkDomainForGroup();
        if (!logged_in()) {
            return false;
        }

        $id_user = privileged_user_id();
        $id_disput = (int) $this->uri->segment(3);
        $file = cleanInput($this->uri->segment(4));

        $this->load->model('Dispute_Model', 'dispute');

        $dispute = $this->dispute->get_disput($id_disput);
        if (!in_array($id_user, array($dispute['id_buyer'], $dispute['id_seller'], $dispute['id_shipper'], $dispute['id_ep_manager']))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $dispute['photos'] = json_decode($dispute['photos'], true);
        $photos = array();
        if (!empty($dispute['photos'])) {
            foreach ($dispute['photos'] as $temp_protos) {
                $photos = array_merge($photos, $temp_protos);
            }
        }

        if (false === ($index = array_search($file, $photos))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var FilesystemProviderInterface $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $storageProvider->storage('public.storage');
        $fullpath = $storage->url(DisputeFilePathGenerator::imagePath($dispute['id_order'], $file));;
        file_force_download($fullpath);
    }

    private function get_user_disputes_list(
        array $disputes,
        array $users = array(),
        array $sellers = array(),
        array $shippers = array()
    ) {
        //region Vars
        //region Statuses
        $statuses = model('dispute')->statuses;
        //endregion Statuses

        //region Money tools
        $zero_price = Money::USD(0);
        //endregion Money tools
        //endregion Vars

        $output = array();
        foreach ($disputes as $dispute) {
            //region Dispute vars
            $order_id = (int) $dispute['id_order'];
            $buyer_id = (int) $dispute['id_buyer'];
            $seller_id = (int) $dispute['id_seller'];
            $shipper_id = (int) $dispute['id_shipper'];
            $manager_id = (int) $dispute['id_buyer'];
            $dispute_id = (int) $dispute['id'];
            $snapshot_id = (int) $dispute['id_snapshot'];
            $ordered_item_id = (int) $dispute['id_ordered'];
            $has_snapshot = !empty($ordered_item_id) && !empty($snapshot_id);
            $dispute_status = arrayGet($dispute, 'status', 'init');
            //endregion Dispute vars

            //region Details
            //region Order details
            $order_url = getUrlForGroup("order/popups_order/order_detail/{$order_id}");
            $order_number = orderNumber($order_id);
            $order_details_title = translate('order_disputes_dashboard_dt_order_details_link_title', null, true);
            $order_details_modal_title = translate('order_disputes_dashboard_dt_order_details_modal_title', null, true);
            $order_details_label = "
                <a class=\"fancybox.ajax fancybox\"
                    data-fancybox-href=\"{$order_url}\"
                    data-title=\"{$order_details_modal_title}\"
                    title=\"{$order_details_title}\">
                    {$order_number}
                </a>
            ";
            //endregion Order details

            if ($has_snapshot) {
                //region Snapshot
                $snapshot_url = getUrlForGroup('items/ordered/' . strForURL("{$dispute['item_title']} {$ordered_item_id}"));
                $snapshot_text = cleanOutput($dispute['item_title']);
                $snapshot_title = cleanOutput(sprintf('Show item "%s"', $dispute['item_title']));
                $snapshot_title = translate('order_disputes_dashboard_dt_snapshot_link_title', array('[ITEM]' => $dispute['item_title']), true);
                $snapshot_link = "
                    <a href=\"{$snapshot_url}\" title=\"{$snapshot_title}\" target=\"_blank\">
                        {$snapshot_text}
                    </a>
                ";
                //endregion Snapshot

                $dispute_details = translate('order_disputes_dashboard_details_with_snapshot_label_text', array(
                    '[SNAPSHOT]'       => $snapshot_link,
                    '[BREAK LINE]'     => '<br>',
                    '[ORDER_NUMBER]'   => $order_details_label,
                    '[DISPUTE_NUMBER]' => orderNumber($dispute_id),
                ));
            } else {
                $dispute_details = translate('order_disputes_dashboard_details_label_text', array(
                    '[BREAK LINE]'     => '<br>',
                    '[ORDER_NUMBER]'   => $order_details_label,
                    '[DISPUTE_NUMBER]' => orderNumber($dispute_id),
                ));
            }
            //endregion Details

            //region Participants
            $dispute_users = array();
            $participants_details = null;
            if (
                !empty($buyer_id)
                || !empty($seller_id)
                || !empty($shipper_id)
            ) {
                //region Buyer
                if (!empty($buyer_id)) {
                    $buyer_label_text = 'Buyer';
                    $buyer_name = cleanOutput($buyer_raw_name = arrayGet($users, "{$buyer_id}.user_name", arrayGet($dispute, 'buyer_name')));
                    $buyer_profile_url = getUserLink($buyer_raw_name, $buyer_id, 'buyer');
                    $dispute_users[] = "
                        <div class=\"flex-card\">
                            <div class=\"main-data-table__item-ttl\">
                                {$buyer_label_text}:
                                <a href=\"{$buyer_profile_url}\" class=\"display-ib link-black txt-medium\" target=\"_blank\">
                                    {$buyer_name}
                                </a>
                            </div>
                        </div>
                    ";
                }
                //endregion Buyer

                //region Seller
                if (!empty($seller_id)) {
                    $seller_label_text = 'Seller';
                    $seller_company_name = cleanOutput(arrayGet($sellers, "{$seller_id}.name_company", arrayGet($users, "{$seller_id}.user_name")));
                    $seller_company_url = getCompanyURL(arrayGet($sellers, $seller_id, array()));
                    $dispute_users[] = "
                        <div class=\"flex-card\">
                            <div class=\"main-data-table__item-ttl\">
                                {$seller_label_text}:
                                <a href=\"{$seller_company_url}\" class=\"display-ib link-black txt-medium\" target=\"_blank\">
                                    {$seller_company_name}
                                </a>
                            </div>
                        </div>
                    ";
                }
                //endregion Seller

                //region Shipper
                if (!empty($shipper_id)) {
                    $shipper_label_text = 'Freight Forwarder';
                    $shipper_company_name = cleanOutput(arrayGet($shippers, "{$shipper_id}.co_name", arrayGet($users, "{$shipper_id}.user_name")));
                    $shipper_company_url = getShipperURL(arrayGet($shippers, $shipper_id, array()));
                    $dispute_users[] = "
                        <div class=\"flex-card\">
                            <div class=\"main-data-table__item-ttl\">
                                {$shipper_label_text}:
                                <a href=\"{$shipper_company_url}\" class=\"display-ib link-black txt-medium\" target=\"_blank\">
                                    {$shipper_company_name}
                                </a>
                            </div>
                        </div>
                    ";
                }
                //endregion Shipper

                if (!empty($dispute_users)) {
                    $participants_details = implode('', $dispute_users);
                }
            }
            //endregion Participants

            //region Refund
            $refund_details = null;
            $request_money = array();
            $refund = priceToUsdMoney(arrayGet($dispute, 'money_back', 0));
            $amount = priceToUsdMoney(arrayGet($dispute, 'money_back_request', 0));
            if ($amount->greaterThan($zero_price)) {
                $refund_request_price = get_price($amount);
                $refund_request_label_title = 'Requested:';
                $request_money[] = "
                    <span class=\"fs-10\">{$refund_request_label_title}</span><br>{$refund_request_price}
                ";
            }
            if ($refund->greaterThan($zero_price)) {
                $refund_price = get_price($refund);
                $refund_label_title = 'Confirmed:';
                $request_money[] = "
                    <span class=\"fs-10\">{$refund_label_title}</span><br>{$refund_price}
                ";
            }

            if (!empty($request_money)) {
                $refund_details = implode('<br>', $request_money);
            }
            //endregion Refund

            //region Status
            $status_details = '&mdash';
            $status_icon = arrayGet($statuses, "{$dispute_status}.icon");
            $status_title = arrayGet($statuses, "{$dispute_status}.title");
            if (!empty($status_icon) && !empty($status_title)) {
                $status_details = "
                    <span>
                        <i class=\"ep-icon {$status_icon} fs-30 mr-0\"></i><br>
                        {$status_title}
                    </span>
                ";
            }
            //endregion Status

            //region Actions
            //region Edit button
            $edit_button = null;
            if (!in_array($dispute_status, array('resolved', 'closed', 'canceled'))) {
                $edit_button_url = getUrlForGroup("dispute/popup_forms/edit/{$dispute_id}");
                $edit_button_title = "Discuss on dispute";
                $edit_button_modal_title = "Discuss on dispute";
                $edit_button_text = translate('general_button_discuss_text');
                $edit_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModalDT\"
                        data-fancybox-href=\"{$edit_button_url}\"
                        data-title=\"{$edit_button_modal_title}\"
                        title=\"{$edit_button_title}\">
                        <i class=\"ep-icon ep-icon_pencil\"></i>
                        <span>{$edit_button_text}</span>
                    </a>
                ";
            }
            //endregion Edit button

            //region Resolve button
            $resolve_button = null;
            if (
                !in_array($dispute_status, array('resolved', 'closed', 'canceled', 'init'))
                && have_right('buy_item')
            ) {
                $resolve_button_text = translate('general_button_close_text');
                $resolve_button_title = 'Close dispute';
                $resolve_button_message = 'Do you really want to submit the request to close this dispute?';
                $resolve_button = "
                    <a class=\"dropdown-item confirm-dialog\"
                        data-callback=\"resolveDispute\"
                        data-message=\"{$resolve_button_message}\"
                        data-dispute=\"{$dispute_id}\"
                        title=\"{$resolve_button_title}\">
                        <i class=\"ep-icon ep-icon_ok-stroke\"></i>
                        <span>{$resolve_button_text}</span>
                    </a>
                ";
            }

            //endregion Resolve button

            //region Close button
            $close_button = null;
            if ('init' === $dispute_status && is_my($buyer_id)) {
                $close_button_modal_title = translate('general_button_cancel_text');
                $close_button_title = translate('general_button_cancel_text');
                $close_button_text = translate('general_button_cancel_text');
                $close_button_url = getUrlForGroup("dispute/popup_forms/cancel/{$dispute_id}");
                $close_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModalDT\"
                        data-fancybox-href=\"{$close_button_url}\"
                        data-title=\"{$close_button_modal_title}\"
                        title=\"{$close_button_title}\">
                        <i class=\"ep-icon ep-icon_remove-circle\"></i>
                        <span>{$close_button_text}</span>
                    </a>
                ";
            }
            //endregion Close button

            //region Notices button
            $notices_button_modal_title = translate('general_button_notices_text');
            $notices_button_title = translate('general_button_notices_text');
            $notices_button_text = translate('general_button_notices_text');
            $notices_button_url = getUrlForGroup("dispute/popup_forms/add_notice/{$dispute_id}");
            $notices_button = "
                <a class=\"dropdown-item fancybox.ajax fancyboxValidateModalDT\"
                    data-fancybox-href=\"{$notices_button_url}\"
                    data-title=\"{$notices_button_modal_title}\"
                    title=\"{$notices_button_title}\">
                    <i class=\"ep-icon ep-icon_comments-stroke\"></i>
                    <span>{$notices_button_text}</span>
                </a>
            ";
            //endregion Notices button

            //region Details button
            $details_buttons_modal_title = translate('general_button_details_text');
            $details_buttons_title = translate('general_button_details_text');
            $details_buttons_text = translate('general_button_details_text');
            $details_buttons_url = getUrlForGroup("dispute/popup_forms/details/{$dispute_id}");
            $details_buttons = "
                <a class=\"dropdown-item fancybox.ajax fancybox\"
                    data-fancybox-href=\"{$details_buttons_url}\"
                    data-title=\"{$details_buttons_modal_title}\"
                    title=\"{$details_buttons_title}\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$details_buttons_text}</span>
                </a>
            ";
            //endregion Details button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$edit_button}
                        {$resolve_button}
                        {$close_button}
                        {$notices_button}
                        {$details_buttons}
                        {$all_button}
                    </div>
                </div>
            ";
            //endregion Actions

            $output[] = array(
                'dt_details'    => $dispute_details,
                'dt_users'      => $participants_details,
                'dt_created'    => getDateFormatIfNotEmpty($dispute['date_time']),
                'dt_updated'    => getDateFormatIfNotEmpty($dispute['change_date']),
                'dt_money_back' => $refund_details,
                'dt_status'     => $status_details,
                'dt_actions'    => $actions,
            );
        }

        return $output;
    }

    /**
     * Returns the user' name.
     *
     * @return string
     */
    private function get_user_name()
    {
        if (user_type('users_staff') || user_type('shipper_staff')) {
            return arrayGet(
                model('user')->getSimpleUser((int) privileged_user_id(), 'CONCAT(users.lname," ", users.fname) as user_name'),
                'user_name'
            );
        }

        return user_name_session();
    }

    /**
     * Returns the fileupload options prepared for disputes.
     *
     * @param int $total
     * @param int $current
     *
     * @return array
     */
    private function get_fileupload_options($total = 0, $current = 0)
    {
        $imageConfig = config('img.disputes.main.rules');
        return $this->getFormattedFileuploadOptions(
            explode(',', config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp')),
            $total,
            $total >= $current ? $total - $current : 0,
            (int) config('fileupload_max_file_size', 10 * 1024 * 1024),
            config('fileupload_max_file_size_placeholder', '10MB'),
            array(
                'width'  => (int) $imageConfig['min_width'],
                'height' => (int) $imageConfig['min_height'],
            ),
            getUrlForGroup('dispute/ajax_operation/upload_photo'),
            getUrlForGroup('dispute/ajax_operation/delete_temp_photo')
        );
    }

    /**
     * Shows the popup where user can add the dispute.
     *
     * @param int   $user_id
     * @param int   $order_id
     * @param mixed $type
     */
    private function show_add_dispute_form($user_id, $order_id, $type)
    {
        //region Order
        if (
            empty($order_id)
            || empty($order = model('orders')->get_order($order_id, array(), array('id_buyer' => $user_id)))
        ) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        //endregion Order

        switch ($type) {
            case 'item':
                $this->show_add_item_dispute_form($user_id, $order_id, (int) uri()->segment(6), $order);

                break;
            case 'order':
                $this->show_add_order_dispute_form($user_id, $order_id, $order);

                break;
            default:
                messageInModal('The provided path is not found.');

                break;
        }
    }

    /**
     * Shows the form where user can add dispute about the item.
     *
     * @param int   $user_id
     * @param int   $order_id
     * @param int   $ordered_item_id
     * @param array $order
     */
    private function show_add_item_dispute_form($user_id, $order_id, $ordered_item_id, array $order)
    {
        //region Ordered item
        if (
            empty($ordered_item_id)
            || empty($ordered_item = model('dispute')->get_ordered_item((int) $ordered_item_id))
        ) {
            messageInModal('Ordered item is not found.');
        }
        //endregion Ordered item

        //region Access
        if (
            model('dispute')->is_disputed_order(
                $order_id,
                array(
                    'status'       => "'init','processing','resolved','closed'",
                    'id_user'      => (int) $user_id,
                    'id_ordered'   => "0,{$ordered_item_id}",
                    'order_status' => 10,
                )
            )
        ) {
            messageInModal('You already opened dispute at this order.', 'info');
        }
        if ('shipping_completed' !== $order['status_alias']) {
            messageInModal('You cannot open an dispute for this item now.');
        }
        //endregion Access

        //region Final amount
        $discount = arrayGet($ordered_item, 'discount', 0);
        $price_per_item = priceToUsdMoney($ordered_item['price_ordered']);
        $ordered_amount = $price_per_item->multiply((int) arrayGet($ordered_item, 'quantity_ordered', 1));
        list($final_amount) = $ordered_amount->allocate(array(100 - $discount, $discount));
        //endregion Final amount

        //region Assign vars
        views()->assign(array(
            'action'            => getUrlForGroup('dispute/ajax_operation/add_item_dispute'),
            'item_id'           => $ordered_item_id,
            'order_id'          => $order_id,
            'order_detail'      => $order,
            'ordered_item'      => $ordered_item,
            'final_amount'      => $final_amount,
            'fileupload'        => $this->get_fileupload_options((int) config('dispute_images_limit', 10)),
            'money_formatter'   => new DecimalMoneyFormatter(new ISOCurrencies()),
            'can_upload_photos' => true,
        ));
        //endregion Assign vars

        views()->display('new/disputes/my/add_dispute_form_view');
    }

    /**
     * Shows the form where user can add dispute about the order.
     *
     * @param int   $user_id
     * @param int   $order_id
     * @param array $order
     */
    private function show_add_order_dispute_form($user_id, $order_id, array $order)
    {
        //region Access check
        if (!in_array($order['status_alias'], array('shipping_in_progress', 'shipping_completed'))) {
            $status = ucfirst(str_replace(array('-', '_'), ' ', $order['order_status']));

            messageInModal(translate('systmess_error_order_open_dispute_wrong_status', ['{{ORDER_STATUS}}' => $status]));
        }

        if ('shipping_in_progress' == $order['status_alias']) {
            if (
                model('dispute')->is_disputed_order(
                    $order_id,
                    array(
                        'id_user'      => $user_id,
                        'status'       => "'init','processing','resolved','closed'",
                        'id_ordered'   => 0,
                        'order_status' => 9,
                    )
                )
            ) {
                messageInModal(translate('systmess_success_order_add_dispute_already_exists'), 'info');
            }
        } elseif ('shipping_completed' == $order['status_alias']) {
            if (
                model('dispute')->is_disputed_order(
                    $order_id,
                    array(
                        'id_user'      => $user_id,
                        'status'       => "'init','processing','resolved','closed'",
                        'order_status' => 10,
                    )
                )
            ) {
                messageInModal(translate('systmess_success_order_add_dispute_already_exists'), 'info');
            }
        }
        //endregion Access check

        //region Final amount
        $ordered_amount = priceToUsdMoney($order['final_price']);
        $shipping_amount = priceToUsdMoney($order['ship_price']);
        $final_amount = $ordered_amount->add($shipping_amount);
        //endregion Final amount

        //region Assign vars
        views()->assign(array(
            'action'            => getUrlForGroup('dispute/ajax_operation/add_order_dispute'),
            'order_id'          => $order_id,
            'order_detail'      => $order,
            'final_amount'      => $final_amount,
            'fileupload'        => $this->get_fileupload_options((int) config('dispute_images_limit', 10)),
            'money_formatter'   => new DecimalMoneyFormatter(new ISOCurrencies()),
            'can_upload_photos' => true,
        ));
        //endregion Assign vars

        views()->display('new/disputes/my/add_dispute_form_view');
    }

    /**
     * Shows the popup where user can edit the dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function show_edit_dispute_form($user_id, $dispute_id)
    {
        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'seller', 'shipper', 'manager'))) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        if ('init' === $dispute['status'] && !is_my($dispute['id_buyer'])) {
            messageInModal(translate('systmess_error_permission_not_granted'));
        }
        if ('resolved' === $dispute['status']) {
            messageInModal(translate('systmess_error_order_edit_dispute_already_resolved'));
        }
        if (in_array($dispute['status'], array('closed', 'canceled'))) {
            messageInModal(translate('systmess_error_order_edit_dispute_already_canceled_or_completed'));
        }
        //endregion Access

        //region Photos
        $dispute['photos'] = with(json_decode($dispute['photos'], true), function ($photos) {
            return null === $photos || !is_array($photos) ? array() : $photos;
        });
        //endregion Photos

        //region Ordered item
        $ordered_item = null;
        $ordered_item_id = (int) arrayGet($dispute, 'id_ordered');
        if (!empty($ordered_item_id)) {
            $ordered_item = model('orders')->get_ordered_item($ordered_item_id);
        }
        //endregion Ordered item

        //region Photo coutners
        $total_photos = (int) config('dispute_images_limit', 10);
        $current_photos = count(arrayGet($dispute, "photos.{$user_id}", array()));
        //endregion Photo coutners

        //region Assign vars
        views()->assign(array(
            'action'            => getUrlForGroup('dispute/ajax_operation/edit'),
            'dispute'           => $dispute,
            'fileupload'        => $this->get_fileupload_options($total_photos, $current_photos),
            'ordered_item'      => $ordered_item,
            'can_upload_photos' => $total_photos > $current_photos,
        ));
        //endregion Assign vars

        if (have_right('dispute_administration')) {
            views()->display('admin/disputes/form_view');
        } else {
            views()->display('new/disputes/my/edit_dispute_form_view');
        }
    }

    /**
     * Shows the popup where administrator can open the dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function show_open_dispute_form($user_id, $dispute_id)
    {
        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            messageInModal('The dispute with such ID is not found on this server.');
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('manager'))) {
            messageInModal('You are not participating in this disput.');
        }
        if ('init' !== $dispute['status']) {
            messageInModal("Disput must be in 'Initiated' status.");
        }
        //endregion Access

        //region Assign vars
        views()->assign(array(
            'dispute'    => $dispute,
            'id_dispute' => $dispute_id,
        ));
        //endregion Assign vars

        views()->display('admin/disputes/init_form_view');
    }

    /**
     * Shows the popup where user can cancel the dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function show_cancel_dispute_form($user_id, $dispute_id)
    {
        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'manager'))) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        if ('init' !== $dispute['status']) {
            messageInModal(translate('systmess_error_order_cancel_dispute_wrong_status'));
        }
        //endregion Access

        //region Assign vars
        views()->assign(array(
            'dispute'    => $dispute,
            'id_dispute' => $dispute_id,
        ));
        //endregion Assign vars

        if (have_right('dispute_administration')) {
            views()->display('admin/disputes/cancel_form_view');
        } else {
            views()->display('new/disputes/my/cancel_form_view');
        }
    }

    /**
     * Shows the popup where administrator can close active dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function show_close_dispute_form($user_id, $dispute_id)
    {
        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('manager'))) {
            messageInModal('You are not participating in this disput.');
        }
        if (in_array($dispute['status'], array('closed', 'canceled'))) {
            messageInModal('Disput is already closed');
        }
        //endregion Access

        //region Order
        $order_id = (int) $dispute['id_order'];
        $order = model('orders')->get_order($order_id);
        $order_status = $order['status_alias'];
        $order_finishing_statuses = array(
            'order_completed',
            'late_payment',
            'canceled_by_buyer',
            'canceled_by_seller',
            'canceled_by_ep',
        );
        //endregion Order

        //region Bills check
        $bills = null;
        $bill_statuses = null;
        $confirmed_bills = null;
        if (in_array(
            $order_status,
            array(
                'preparing_for_shipping',
                'shipping_in_progress',
                'payment_processing',
                'shipping_completed',
                'payment_confirmed',
                'order_paid',
            )
        )) {
            $bills = model('user_bills')->get_user_bills(array(
                'encript_detail' => 1,
                'bills_type'     => '1,2',
                'pagination'     => false,
                'id_item'        => $order_id,
            ));
            $bill_statuses = model('user_bills')->get_bills_statuses();
            $confirmed_bills = model('user_bills')->summ_bills_by_order($order_id, "'confirmed'", '1,2');
        }
        //endregion Bills check

        //region Assign vars
        views()->assign(array(
            'bills'             => $bills,
            'status'            => $bill_statuses,
            'dispute'           => $dispute,
            'id_dispute'        => $dispute_id,
            'order_detail'      => $order,
            'amount_confirmed'  => $confirmed_bills,
            'finished_statuses' => $order_finishing_statuses,
        ));
        //endregion Assign vars

        views()->display('admin/disputes/close_form_view');
    }

    /**
     * Shows the popup where user can view the dispute details.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function show_dispute_details_form($user_id, $dispute_id)
    {
        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'seller', 'shipper', 'manager'))) {
            messageInModal('You are not participating in this disput.');
        }
        if ('init' == $dispute['status'] && !in_array($user_id, array((int) $dispute['id_buyer'], (int) $dispute['id_ep_manager']))) {
            messageInModal(translate('systmess_error_permission_not_granted'));
        }
        //endregion Access

        //region Subresources
        $dispute['photos'] = with(json_decode($dispute['photos'], true), function ($photos) {
            return null === $photos || !is_array($photos) ? null : $photos;
        });
        $dispute['videos'] = with(json_decode($dispute['videos'], true), function ($videos) {
            return null === $videos || !is_array($videos) ? null : $videos;
        });
        $dispute['timeline'] = with(json_decode("[{$dispute['timeline']}]", true), function ($timeline) {
            return null === $timeline || !is_array($timeline) ? null : $timeline;
        });
        //endregion Subresources

        //region photos links
        if(!empty($dispute['photos'])){
            /** @var FilesystemProviderInterface $storageProvider */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $storage = $storageProvider->storage('public.storage');
            $preparedPhotos = [];
            foreach($dispute['photos'] as $userId => $photosUser){
                $preparedPhotos[$userId] = [];
                foreach($photosUser as $keyPhoto => $photo){
                    $preparedPhotos[$userId][$keyPhoto] = [
                        'name'       => $photo,
                        'imageLink'  => $storage->url(DisputeFilePathGenerator::imagePath($dispute['id_order'], $photo)),
                        'imageThumb' => $storage->url(DisputeFilePathGenerator::thumbImage($dispute['id_order'], $photo, DisputePhotoThumb::SMALL())),
                    ];
                }
            }
            $dispute['photos'] = $preparedPhotos;
        }
        //endregion photos links

        //region videos links
        if(!empty($dispute['videos'])){
            /** @var FilesystemProviderInterface $storageProvider */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $storage = $storageProvider->storage('public.storage');
            foreach($dispute['videos'] as $userId => $videoUser){
                foreach($videoUser as $keyVideo => $video){
                    $dispute['videos'][$userId][$keyVideo]['imageLink'] = $storage->url(DisputeFilePathGenerator::videoImage($dispute['id_order'], $video['thumb']));
                }
            }
        }
        //endregion videos links

        //region Users
        $buyer_id = (int) $dispute['id_buyer'];
        $seller_id = (int) $dispute['id_seller'];
        $shipper_id = (int) $dispute['id_shipper'];
        $manager_id = (int) $dispute['id_ep_manager'];
        $users = arrayByKey(
            model('dispute')->get_users(array_filter(array(
                $buyer_id,
                $seller_id,
                $shipper_id,
                $manager_id,
            ))),
            'idu'
        );
        $users_roles = array_filter(array(
            $buyer_id   => 'buyer',
            $seller_id  => 'seller',
            $shipper_id => 'shipper',
            $manager_id => 'ep_manager',
        ));
        //endregion Users

        //region Ordered item
        $ordered_item = null;
        if (!empty($dispute['id_ordered'])) {
            $ordered_item_id = (int) $dispute['id_ordered'];
            $ordered_item = model('orders')->get_ordered_item($ordered_item_id);
        }
        //endregion Ordered item

        //region Assign vars
        views()->assign(array(
            'users'        => $users,
            'dispute'      => $dispute,
            'users_roles'  => $users_roles,
            'ordered_item' => $ordered_item,
        ));
        //endregion Assign vars

        views()->display('new/disputes/my/details_view');
    }

    /**
     * Shows the popup where user can add a new notice to dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function show_add_notice_form($user_id, $dispute_id)
    {
        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!have_right('read_order_details') && !model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'seller', 'shipper', 'manager'))) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        //endregion Access

        //region Timeline
        $dispute['timeline'] = with(json_decode("[{$dispute['timeline']}]", true), function ($timeline) {
            return null === $timeline || !is_array($timeline) ? array() : $timeline;
        });
        //endregion Timeline

        //region Assign vars
        views()->assign(array(
            'dispute'   => $dispute,
            'can_write' => !in_array($dispute['status'], array('resolved', 'canceled', 'closed')),
        ));
        //endregion Assign vars

        if (have_right('dispute_administration')) {
            views('admin/disputes/notice_form_view');
        } else {
            views('new/disputes/my/notice_form_view');
        }
    }

    /**
     * Creates the new dispute.
     *
     * @param int $user_id
     * @param int $order_id
     * @param int $ordered_item_id
     */
    private function create_dispute($user_id, $order_id, $ordered_item_id = null)
    {
        //region Validation
        $validator_rules = array(
            array(
                'field' => 'comment',
                'label' => 'Comment',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'money_count',
                'label' => 'How much money',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'upload_folder',
                'rules' => array(
                    'required' => translate('systmess_error_file_upload_path_not_correct'),
                    function ($attr, $value, $fail) {
                        if (empty($value)) {
                            return;
                        }

                        if (false === checkEncriptedFolder($value)) {
                            $fail(translate('systmess_error_file_upload_path_not_correct'));
                        }
                    },
                ),
            ),
        );

        if ($request_refund = filter_var(arrayGet($_POST, 'refund_money', false), FILTER_VALIDATE_BOOLEAN)) {
            $validator_rules[] = array(
                'field' => 'money_count',
                'label' => 'How much money',
                'rules' => array('required' => '', 'positive_number' => ''),
            );
        }

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Order
        if (
            empty($user_id) ||
            empty($order = model('orders')->get_order($order_id, array(), array('id_buyer' => $user_id)))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Order

        //region Access check
        if ($is_item_dispute = null !== $ordered_item_id) {
            if ('shipping_completed' != $order['status_alias']) {
                jsonResponse(translate('systmess_error_order_open_dispute_wrong_status', ['{{ORDER_STATUS}}' => $order['order_status']]));
            }

            if (model('dispute')->is_disputed_order($order_id, array(
                'status'       => "'init','processing','resolved','closed'",
                'id_user'      => $user_id,
                'id_ordered'   => implode(',', array_flip(array_flip(array(0, $ordered_item_id)))),
                'order_status' => 10,
            ))) {
                jsonResponse(translate('systmess_success_order_add_dispute_already_exists'), 'info');
            }
        } else {
            if (!in_array($order['status_alias'], array('shipping_in_progress', 'shipping_completed'))) {
                jsonResponse(translate('systmess_error_order_open_dispute_wrong_status', ['{{ORDER_STATUS}}' => $order['order_status']]));
            }

            if (model('dispute')->is_disputed_order($order_id, array_filter(
                array(
                    'status'       => "'init','processing','resolved','closed'",
                    'id_user'      => $user_id,
                    'id_ordered'   => 'shipping_in_progress' === $order['status_alias'] ? 0 : null,
                    'order_status' => 'shipping_in_progress' === $order['status_alias'] ? 9 : 10,
                ),
                function ($value) { return null !== $value; }
            ))) {
                jsonResponse(translate('systmess_success_order_add_dispute_already_exists'), 'info');
            }
        }
        //endregion Access check

        //region Ordered item check
        $ordered_item = null;
        if ($is_item_dispute) {
            if (
                empty($ordered_item_id)
                || empty($ordered_item = model('dispute')->get_ordered_item($ordered_item_id))
            ) {
                jsonResponse(translate('systmess_error_invalid_data'));
            }
        }
        //endregion Ordered item check

        //region Amount check
        //region Amounts
        $requested_amount = priceToUsdMoney(arrayGet($_POST, 'money_count', 0));
        if ($is_item_dispute) {
            $discount = arrayGet($ordered_item, 'discount', 0);
            $price_per_item = priceToUsdMoney($ordered_item['price_ordered']);
            $ordered_amount = $price_per_item->multiply((int) arrayGet($ordered_item, 'quantity_ordered', 1));
            list($final_amount) = $ordered_amount->allocate(array(100 - $discount, $discount));
        } else {
            $ordered_amount = priceToUsdMoney($order['final_price']);
            $shipping_amount = priceToUsdMoney($order['ship_price']);
            $final_amount = $ordered_amount->add($shipping_amount);
        }
        //endregion Amounts

        if ($request_refund && $final_amount->lessThan($requested_amount)) {
            jsonResponse(translate('systmess_error_order_open_dispute_requested_amount_greater_than_final_price'));
        }
        //endregion Amount check

        //region Dispute
        //region Users
        $buyer_id = (int) $order['id_buyer'];
        $seller_id = (int) $order['id_seller'];
        $manager_id = (int) $order['ep_manager'];
        $shipper_id = 0;
        if ('ep_shipper' == $order['shipper_type']) {
            $shipper_id = (int) $order['id_shipper'];
        }
        $users_list = array($buyer_id, $seller_id, $manager_id, $shipper_id);
        $dispute_users = arrayByKey(model('user')->get_simple_users(array('users_list' => implode(',', $users_list))), 'idu');
        //endregion Users

        //region Names
        $buyer_name = trim(arrayGet($dispute_users, "{$buyer_id}.fname") . ' ' . arrayGet($dispute_users, "{$buyer_id}.lname"));
        $seller_name = trim(arrayGet($dispute_users, "{$seller_id}.fname") . ' ' . arrayGet($dispute_users, "{$seller_id}.lname"));
        $manager_name = trim(arrayGet($dispute_users, "{$manager_id}.fname") . ' ' . arrayGet($dispute_users, "{$manager_id}.lname"));
        $shipper_name = 0 !== $shipper_id
            ? trim(arrayGet($dispute_users, "{$shipper_id}.fname") . ' ' . arrayGet($dispute_users, "{$shipper_id}.lname"))
            : null;
        //endregion Names

        //region Notice
        $items = !$is_item_dispute ? model('dispute')->get_ordered_items_titles($order_id) : null;
        $comment = cleanInput($_POST['comment']);
        $order_number = orderNumber($order_id);
        $ordered_item_title = $is_item_dispute && null !== $ordered_item ? arrayGet($ordered_item, 'title') : null;
        $items_title = !$is_item_dispute && null !== $items ? arrayGet($items, 'title_items') : null;
        $notice = views()->fetch('new/disputes/my/partials/notice_view', compact(
            'comment',
            'buyer_name',
            'seller_name',
            'manager_name',
            'shipper_name',
            'order_number',
            'items_title',
            'is_item_dispute',
            'ordered_item_title'
        ));
        //endregion Notice

        $insert = array(
            'id_order'           => (int) $order_id,
            'id_buyer'           => (int) $buyer_id,
            'id_seller'          => (int) $seller_id,
            'id_shipper'         => (int) $shipper_id,
            'id_ordered'         => (int) $ordered_item_id,
            'id_ep_manager'      => (int) $manager_id,
            'date_time'          => date('Y-m-d H:i:s'),
            'comment'            => $comment,
            'order_status'       => $order['status'],
            'disput_on_order'    => (int) !$is_item_dispute,
            'max_price'          => moneyToDecimal($final_amount),
            'money_back_request' => moneyToDecimal($requested_amount),
            'timeline'           => json_encode(
                array(
                    'notice'   => trim($notice),
                    'title'    => 'The dispute has been initiated',
                    'add_date' => formatDate(date('Y-m-d H:i:s')),
                    'add_by'   => $buyer_name,
                )
            ),
        );
        //endregion Dispute

        //region Video
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicPrefixer = $storageProvider->prefixer('public.storage');

        $video_link = request()->request->get('video_link');
        if (!empty($video_link)) {
            $video = library('videothumb')->process($video_link);
            if (!empty($video['error'])) {
                jsonResponse($video['error']);
            }

            $path = DisputeFilePathGenerator::videoFolder($order_id);
            $publicDisk->createDirectory($path);

            $copy_result = $this->upload->copy_images_new(array(
                'images'      => array($video['image']),
                'destination' => $publicPrefixer->prefixPath($path),
                'resize'      => config('dispute_video_image_size', '130x130'),
            ));
            if (!empty($copy_errors = arrayGet($copy_result, 'errors'))) {
                //remove here is from temp (need to refactor the use of videothumb library first.)
                //removeFileIfExists($video['image']);
                jsonResponse($copy_errors);
            }
            //todo refactor videothumb library to work with filesytem
            //removeFileIfExists($video['image']);
            $insert['videos'] = json_encode(array(
                $user_id => array(array(
                    'thumb' => arrayGet($copy_result, '0.new_name'),
                    'type'  => $video['type'],
                    'id'    => $video['v_id'],
                )),
            ));
        }
        //endregion Video

        //region Pictures
        if (!empty($newImages = (array) request()->request->get('images')))
        {
            $prefixerTemp = $storageProvider->prefixer('temp.storage');
            $prefixerPublic = $storageProvider->prefixer('public.storage');
            /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
            $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
            $uploadedImages = [];
            $configImage = config("img.disputes.main");
            foreach ($newImages as $image)
            {
                $publicDisk->createDirectory(DisputeFilePathGenerator::imageFolder($order_id));
                $imageProcessingResult = $interventionImageLibrary->image_processing(
                    [
                        'tmp_name' => $prefixerTemp->prefixPath($image),
                        'name'     => pathinfo($image, PATHINFO_FILENAME)
                    ],
                    [
                        'destination'   => $prefixerPublic->prefixPath(DisputeFilePathGenerator::imageFolder($order_id)),
                        'handlers'      => [
                            'resize'        => $configImage["resize"],
                            'create_thumbs' => $configImage["thumbs"],
                        ]
                    ]
                );
                $uploadedImages[] = $imageProcessingResult[0]['new_name'];
                if (!empty($imageProcessingResult['errors'])) {
                    jsonResponse($imageProcessingResult['errors']);
                }

            }
            $insert['photos'] = json_encode([$user_id => $uploadedImages]);
        }
        //endregion Pictures

        //region Create record
        if (!($dispute_id = model('dispute')->insert_disput($insert))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }
        //endregion Create record

        //region Updates dependencies
        model('orders')->change_order($order_id, array('dispute_opened' => 1));
        model('user_statistic')->set_users_statistic(array($user_id => array('dispute_init' => 1)));
        //endregion Updates dependencies

        //region Notifications
        $this->send_notifications($dispute_id, 'create');
        //endregion Notifications

        jsonResponse(translate('systmess_success_order_open_dispute'), 'success', ['order' => $order_id]);
    }

    /**
     * Opens the disptue.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function open_dispute($user_id, $dispute_id)
    {
        //region Validation
        $validator_rules = array(
            array(
                'field' => 'message',
                'label' => 'Message',
                'rules' => array('required' => ''),
            ),
        );

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            messageInModal('The dispute with such ID is not found on this server.');
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('manager'))) {
            messageInModal('You are not participating in this disput.');
        }
        if ('init' !== $dispute['status']) {
            messageInModal("Disput must be in 'Initiated' status.");
        }
        //endregion Access

        //region Notice
        $dispute_notice = array(
            'add_by'   => $this->get_user_name(),
            'add_date' => getDateFormat(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
            'notice'   => cleanInput(arrayGet($_POST, 'message')),
            'title'    => 'The dispute has been started.',
        );
        //endregion Notice

        //region Timeline
        $dispute_timeline = !empty($dispute['timeline']) ? ",{$dispute['timeline']}" : '';
        $dispute_timeline = json_encode($dispute_notice, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . $dispute_timeline;
        //endregion Timeline

        //region Update
        if (!model('dispute')->dispute_update($dispute_id, array(
            'status'   => 'processing',
            'timeline' => $dispute_timeline,
        ))) {
            jsonResponse('Failed to initialize the dispute. Please try again later.');
        }
        //endregion Update

        //region Update order log
        $order_id = (int) $dispute['id_order'];
        $dispute_number = orderNumber($dispute_id);
        $ordered_item_id = (int) arrayGet($dispute, 'id_ordered');
        $ordered_item_title = null;
        if (
            !empty($ordered_item_id)
            && !empty($ordered_item = model('dispute')->get_ordered_item($ordered_item_id, 'ios.title'))
        ) {
            $ordered_item_title = arrayGet($ordered_item, 'title');
        }

        model('orders')->change_order_log($order_id, json_encode(array(
            'date'    => date('m/d/Y h:i:s A'),
            'user'    => 'Buyer',
            'message' => null !== $ordered_item_title
                ? sprintf('The dispute %s has been opened on this order about item "%s".', $dispute_number, $ordered_item_title)
                : sprintf('The dispute %s has been opened on this order.', $dispute_number),
        )));
        //endregion Update order log

        //region Update statistic
        model('user_statistic')->set_users_statistic(array_fill_keys(
            array_filter(array(
                (int) $dispute['id_seller'],
                (int) $dispute['id_shipper'],
            )),
            array('dispute_init' => 1)
        ));
        //endregion Update statistic

        //region Send notifications
        $this->send_notifications($dispute_id, 'open');
        //endregion Send notifications

        jsonResponse('The dispute has been started.', 'success');
    }

    /**
     * Edits the dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function edit_dispute($user_id, $dispute_id)
    {
        //region Validation
        $validator_rules = array(
            array(
                'field' => 'notice',
                'label' => 'Notice',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
            array(
                'field' => 'upload_folder',
                'rules' => array(
                    'required' => translate('systmess_error_file_upload_path_not_correct'),
                    function ($attr, $value, $fail) {
                        if (empty($value)) {
                            return;
                        }

                        if (false === checkEncriptedFolder($value)) {
                            $fail(translate('systmess_error_file_upload_path_not_correct'));
                        }
                    },
                ),
            ),
        );

        if ($is_refund_request = filter_var(arrayGet($_POST, 'refund_money', false), FILTER_VALIDATE_BOOLEAN)) {
            $validator_rules[] = array(
                'field' => 'money_count',
                'label' => 'How much money',
                'rules' => array('required' => '', 'positive_number' => ''),
            );
        }

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Dispute
        if (
            empty($dispute_id) ||
            empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access check
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'seller', 'shipper', 'manager'))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        if ('init' === $dispute['status'] && !is_my((int) $dispute['id_buyer'])) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        if ('resolved' === $dispute['status']) {
            jsonResponse(translate('systmess_error_order_edit_dispute_already_resolved'));
        }
        if (in_array($dispute['status'], array('closed', 'canceled'))) {
            jsonResponse(translate('systmess_error_order_edit_dispute_already_canceled_or_completed'));
        }
        //endregion Access check

        //region Misc vars
        $user_id = (int) privileged_user_id();
        $order_id = (int) $dispute['id_order'];
        $buyer_id = (int) $dispute['id_buyer'];
        $manager_id = (int) $dispute['id_ep_manager'];
        //endregion Misc vars

        //region Dispute price delta
        $max_price = priceToUsdMoney($dispute['max_price']);
        $dispute_price = null;
        if (in_array($user_id, array($buyer_id, $manager_id))) {
            $dispute_price = Money::USD(0);
            if ($is_refund_request) {
                $dispute_price = priceToUsdMoney($_POST['money_count']);
                if ($max_price->lessThan($dispute_price)) {
                    jsonResponse(translate('systmess_error_order_open_dispute_requested_amount_greater_than_final_price'));
                }
            }
        }
        //endregion Dispute price delta

        //region Dispute videos
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicPrefixer = $storageProvider->prefixer('public.storage');
        $dispute_videos = null;
        if (!empty(request()->request->get('video_link'))) {
            $video = library('videothumb')->process(request()->request->get('video_link'));

            if (isset($video['error'])) {
                jsonResponse($video['error']);
            }

            $path = DisputeFilePathGenerator::videoFolder($order_id);
            $publicDisk->createDirectory($path);

            $copy_result = $this->upload->copy_images_new(array(
                'destination' => $publicPrefixer->prefixPath($path),
                'images'      => array($video['image']),
                'resize'      => '130x130',
            ));
            if (!empty($copy_result['errors'])) {
                jsonResponse($copy_result['errors']);
            }

            // Remove video image
           // removeFileIfExists($video['image']);

            $dispute_videos = with(json_decode($dispute['videos'], true), function ($videos) use ($user_id) {
                if (null === $videos) {
                    $videos = array();
                }

                if (empty($videos[$user_id])) {
                    $videos[$user_id] = array();
                }

                return $videos;
            });

            $dispute_videos[$user_id][] = array(
                'thumb' => arrayGet($copy_result, '0.new_name'),
                'type'  => $video['type'],
                'id'    => $video['v_id'],
            );
        }
        //endregion Dispute videos

        //region Dispute photos
        //region Pictures
        $dispute_photos = null;
        if (!empty($newImages = (array) request()->request->get('images')))
        {
            $configImage = config("img.disputes.main");
            $total = (int) $configImage['limit'];
            if (count($newImages) > $total) {
                jsonResponse(
                    translate('order_disputes_dashboard_images_limit_error_message', array('{amount}' => $total))
                );
            }
            $prefixerTemp = $storageProvider->prefixer('temp.storage');
            $prefixerPublic = $storageProvider->prefixer('public.storage');
            /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
            $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
            $uploadedImages = [];
            foreach ($newImages as $image)
            {
                $publicDisk->createDirectory(DisputeFilePathGenerator::imageFolder($order_id));
                $imageProcessingResult = $interventionImageLibrary->image_processing(
                    [
                        'tmp_name' => $prefixerTemp->prefixPath($image),
                        'name'     => pathinfo($image, PATHINFO_FILENAME)
                    ],
                    [
                        'destination'   => $prefixerPublic->prefixPath(DisputeFilePathGenerator::imageFolder($order_id)),
                        'handlers'      => [
                            'resize'        => $configImage["resize"],
                            'create_thumbs' => $configImage["thumbs"],
                        ]
                    ]
                );
                $uploadedImages[] = $imageProcessingResult[0]['new_name'];
                if (!empty($imageProcessingResult['errors'])) {
                    jsonResponse($imageProcessingResult['errors']);
                }

            }
            $dispute_photos = with(json_decode($dispute['photos'], true), function ($photos) use ($user_id) {
                if (null === $photos) {
                    $photos = [];
                }

                if (empty($photos[$user_id])) {
                    $photos[$user_id] = [];
                }

                return $photos;
            });

            $dispute_photos[$user_id] = array_merge($dispute_photos[$user_id], $uploadedImages);
        }
        //endregion Dispute photos

        //region Dispute notice
        $notice_text = cleanInput($_POST['notice']);
        $dispute_notice = null;
        if (!empty($notice_text)) {
            $dispute_notice = array(
                'add_by'   => $this->get_user_name(),
                'add_date' => getDateFormat(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
                'notice'   => $notice_text,
                'title'    => 'New notice.',
            );
        }

        if (null !== $dispute_price) {
            $refund_amount = priceToUsdMoney($dispute['money_back_request']);
            if (!$refund_amount->equals($dispute_price)) {
                $dispute_notice['title'] = 'The dispute has been changed.';
                $dispute_notice['add_by'] = arrayGet($dispute_notice, 'add_by', function () { return $this->get_user_name(); });
                $dispute_notice['add_date'] = arrayGet($dispute_notice, 'add_date', function () { return getDateFormat(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'); });
                $dispute_notice['notice'] = sprintf(
                    'The price has been changed: from $%s to $%s.%s',
                    get_price($refund_amount, false),
                    get_price($dispute_price, false),
                    empty($dispute_notice['notice']) ? '' : sprintf(
                        '<div class="txt-medium">%s</div>%s',
                        'New notice.',
                        $dispute_notice['notice']
                    )
                );
            }

            $dispute_price = moneyToDecimal($dispute_price);
        }
        //endregion Dispute notice

        //region Dispute timeline
        $dispute_timeline = null;
        if (null !== $dispute_notice) {
            $dispute_timeline = !empty($dispute['timeline']) ? ",{$dispute['timeline']}" : '';
            $dispute_timeline = json_encode($dispute_notice, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . $dispute_timeline;
        }
        //endregion Dispute timeline

        //region Dispute update
        $update = array_filter(
            array(
                'videos'             => null !== $dispute_videos ? json_encode($dispute_videos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_SLASHES) : null,
                'photos'             => null !== $dispute_photos ? json_encode($dispute_photos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_SLASHES) : null,
                'timeline'           => $dispute_timeline,
                'money_back_request' => $dispute_price,
            ),
            function ($item) {
                return null !== $item;
            }
        );
        if (empty($update)) {
            jsonResponse(translate('systmess_error_order_edit_dispute_nothing_to_change'), 'info');
        }
        if (!model('dispute')->dispute_update($dispute_id, $update)) {
            jsonResponse(translate('systmess_internal_server_error'));
        }
        //endregion Dispute update

        //region Dispute update notification
        $this->send_notifications($dispute_id, 'update');
        //endregion Dispute update notification

        jsonResponse(translate('systmess_success_order_edit_dispute'), 'success');
    }

    /**
     * Resolves the dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function resolve_dispute($user_id, $dispute_id)
    {
        //region Dispute check
        if (
            empty($dispute_id) ||
            empty($dispute = $this->dispute->get_disput($dispute_id))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute check

        //region Access check
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer'))) {
            jsonResponse('You are not participating in this disput.');
        }
        if ('processing' !== $dispute['status']) {
            jsonResponse('Dispute must be on the "Processing" status.');
        }
        //endregion Access check

        //region Dispute notice
        $dispute_notice = array(
            'add_by'   => $this->get_user_name(),
            'add_date' => getDateFormat(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
            'notice'   => 'The dispute is deemed as resolved and the request to close it has been submitted.',
            'title'    => 'Dispute is closing.',
        );
        //endregion Dispute notice

        //region Dispute timeline
        $dispute_timeline = !empty($dispute['timeline']) ? ",{$dispute['timeline']}" : '';
        $dispute_timeline = json_encode($dispute_notice, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . $dispute_timeline;
        //endregion Dispute timeline

        //region Dispute update
        if (!model('dispute')->dispute_update($dispute_id, array(
            'status'   => 'resolved',
            'timeline' => $dispute_timeline,
        ))) {
            jsonResponse('Failed to send request to close the dispute. Please try again later.');
        }
        //endregion Dispute update

        //region Resolve notification
        $this->send_notifications($dispute_id, 'resolve');
        //endregion Resolve notification

        jsonResponse('The request to close the dispute has been submitted. Please wait confirmation of administration.', 'success');
    }

    /**
     * Cancels the not initialized dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function cancel_dispute($user_id, $dispute_id)
    {
        //region Validation
        $this->validator->set_rules(array(
            array(
                'field' => 'reason',
                'label' => 'Reason',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
        ));
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'manager'))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        if ('init' !== $dispute['status']) {
            jsonResponse(translate('systmess_error_order_cancel_dispute_wrong_status'));
        }
        //endregion Access

        //region Notice
        $dispute_notice = array(
            'add_by'   => $user_name = $this->get_user_name(),
            'add_date' => $current_date = getDateFormat(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
            'notice'   => $reason = cleanInput(arrayGet($_POST, 'reason')),
            'title'    => 'The dispute has been canceled.',
        );
        //endregion Notice

        //region Timeline
        $dispute_timeline = !empty($dispute['timeline']) ? ",{$dispute['timeline']}" : '';
        $dispute_timeline = json_encode($dispute_notice, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . $dispute_timeline;
        //endregion Timeline

        //region Reason
        $close_reason = json_encode(array(
            'reason'   => $reason,
            'add_by'   => $user_name,
            'add_date' => $current_date,
        ));
        //endregion Reason

        //region Update
        if (!model('dispute')->dispute_update($dispute_id, array(
            'status'   => 'canceled',
            'reason'   => $close_reason,
            'timeline' => $dispute_timeline,
        ))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }
        //endregion Update

        //region Update order log
        model('orders')->change_order((int) $dispute['id_order'], array('dispute_opened' => 2));
        //endregion Update order log

        //region Update statistics
        model('user_statistic')->set_users_statistic(array((int) $dispute['id_buyer'] => array('dispute_init' => -1)));
        //endregion Update statistics

        //region Send notifications
        if (have_right('buy_item')) {
            $this->send_notifications($dispute_id, 'cancel');
        } else {
            $this->send_notifications($dispute_id, 'decline');
        }
        //endregion Send notifications

        jsonResponse(translate('systmess_success_order_cancel_dispute'), 'success');
    }

    /**
     * Closes the initialized dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function close_dispute($user_id, $dispute_id)
    {
        //region Validation
        $validator_rules = array(
            array(
                'field' => 'reason',
                'label' => 'Reason',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
            array(
                'field' => 'disput',
                'label' => 'Dispute',
                'rules' => array('required' => ''),
            ),
            array(
                'field' => 'action',
                'label' => 'Action',
                'rules' => array('required' => ''),
            ),
        );

        if (isset($_POST['external_bill_buyer'])) {
            $validator_rules[] = array(
                'field' => 'external_bill_buyer_amount',
                'label' => 'Amount to refund the buyer',
                'rules' => array('required' => '', 'min[0.01]' => ''),
            );
        }

        if (isset($_POST['external_bill_seller'])) {
            $validator_rules[] = array(
                'field' => 'external_bill_seller_amount',
                'label' => 'Amount to pay the seller',
                'rules' => array('required' => '', 'min[0.01]' => ''),
            );
        }

        if (isset($_POST['external_bill_shipper'])) {
            $validator_rules[] = array(
                'field' => 'external_bill_shipper_amount',
                'label' => 'Amount to pay the freight forwarder',
                'rules' => array('required' => '', 'min[0.01]' => ''),
            );
        }

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Order
        $order_id = (int) $dispute['id_order'];
        if (
            empty($order_id)
            || empty($order = model('orders')->get_order($order_id))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Order

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('manager'))) {
            jsonResponse('You are not participating in this disput.');
        }
        if (!in_array($dispute['status'], array('processing', 'resolved'))) {
            jsonResponse('You cannot close this dispute now. Please try again late.');
        }
        if (!in_array($action = arrayGet($_POST, 'action'), array('continue', 'cancel'))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Access

        //region Vars
        $status = $order['status_alias'];
        $reason = cleanInput($_POST['reason']);
        $user_name = $this->get_user_name();
        $zero_amount = Money::USD(0);
        $is_canceling = 'cancel' === $action;
        $current_date = date('Y-m-d H:i:s');
        $external_bills = array();
        $dispute_number = orderNumber($dispute_id);
        $can_refund_buyer = isset($_POST['external_bill_buyer']);
        $can_refund_seller = $is_canceling && isset($_POST['external_bill_seller']);
        $can_refund_shipper = $is_canceling && isset($_POST['external_bill_shipper']) && 'ep_shipper' === $order['shipper_type'];
        $buyer_refund_amount = $can_refund_buyer ? priceToUsdMoney(arrayGet($_POST, 'external_bill_buyer_amount')) : Money::USD(0);
        $seller_refund_amount = $can_refund_seller ? priceToUsdMoney(arrayGet($_POST, 'external_bill_seller_amount')) : Money::USD(0);
        $shipper_refund_amount = $can_refund_shipper ? priceToUsdMoney(arrayGet($_POST, 'external_bill_shipper_amount')) : Money::USD(0);
        $refund_notice = $can_refund_buyer ? sprintf('The buyer will be refunded with: $%s', get_price($buyer_refund_amount, false)) : null;
        $timeline = with(json_decode("[{$dispute['timeline']}]", true), function ($timeline) {
            return null === $timeline || !is_array($timeline) ? null : $timeline;
        });

        //region Notice
        $cancel_notice = array(
            'notice'   => "<strong>Reason</strong>: {$reason}",
            'add_date' => getDateFormat($current_date, 'Y-m-d H:i:s'),
            'add_by'   => $user_name,
            'title'    => 'The dispute has been closed.',
        );
        $timeline[] = $cancel_notice;
        //endregion Notice

        //region Reason
        $close_reason = array(
            'reason'   => $reason,
            'add_by'   => $user_name,
            'add_date' => getDateFormat($current_date, 'Y-m-d H:i:s'),
        );
        //endregion Reason

        //region Order
        //region Order log
        $order_bills = empty($order['external_bills']) ? array() : with(json_decode("[{$order['external_bills']}]", true), function ($bills) {
            if (null === $bills || !is_array($bills)) {
                jsonResponse('Failed to cancel the dispute. Please try again later or contact administration.');
            }

            return $bills;
        });
        $order_summary = empty($order['order_summary']) ? array() : with(json_decode("[{$order['order_summary']}]", true), function ($log) {
            if (null === $log || !is_array($log)) {
                jsonResponse('Failed to cancel the dispute. Please try again later or contact administration.');
            }

            return $log;
        });
        $order_number = orderNumber($order_id);
        $order_log_record = array(
            'date'    => getDateFormat($current_date, 'Y-m-d H:i:s'),
            'user'    => 'EP Manager',
            'message' => sprintf('The dispute %s has been closed. %s<br><strong>Reason: </strong>%s', $dispute_number, $refund_notice, $reason),
        );
        $order_summary[] = &$order_log_record;
        //endregion Order log

        $order_update = array(
            'reason'         => $reason,
            'dispute_opened' => 2,
        );
        //endregion Order
        //endregion Vars

        //region Refund check
        if (
            ($can_refund_buyer && $buyer_refund_amount->lessThanOrEqual(Money::USD(0)))
            || ($can_refund_seller && $seller_refund_amount->lessThanOrEqual(Money::USD(0)))
            || ($can_refund_shipper && $shipper_refund_amount->lessThanOrEqual(Money::USD(0)))
        ) {
            jsonResponse('Please write a correct amount for refund.');
        }
        //endregion Refund check

        //region Update
        if (!model('dispute')->dispute_update($dispute_id, array(
            'status'     => 'closed',
            'reason'     => json_encode($close_reason),
            'timeline'   => implode(',', array_map(function ($entry) { return json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); }, $timeline)),
            'money_back' => moneyToDecimal($buyer_refund_amount),
        ))) {
            jsonResponse('Failed to cancel the dispute. Please try again later.');
        }
        //endregion Update

        //region Users
        $buyer_id = (int) $dispute['id_buyer'];
        $seller_id = (int) $dispute['id_seller'];
        $shipper_id = (int) $dispute['id_shipper'];
        $users_list = array_filter(array($buyer_id, $seller_id));
        $dispute_users = arrayByKey(model('user')->get_simple_users(array('users_list' => implode(',', $users_list))), 'idu');
        $shipper = empty($shipper_id) ? null : with($shipper_id, function ($shipper_id) use ($order) {
            return 'ishipper' == $order['shipper_type']
                ? model('ishippers')->get_shipper($shipper_id)
                : model('shippers')->get_shipper_by_user($shipper_id);
        });
        $buyer_name = trim(arrayGet($dispute_users, "{$buyer_id}.fname") . ' ' . arrayGet($dispute_users, "{$buyer_id}.lname"));
        $seller_name = trim(arrayGet($dispute_users, "{$seller_id}.fname") . ' ' . arrayGet($dispute_users, "{$seller_id}.lname"));
        $shipper_name = empty($shipper) ? null : with($shipper, function ($shipper) use ($order) {
            return 'ishipper' == $order['shipper_type']
                ? trim(arrayGet($shipper, 'shipper_original_name'))
                : trim(arrayGet($shipper, 'co_name'));
        });
        $buyer_email = arrayGet($dispute_users, "{$buyer_id}.email");
        $seller_email = arrayGet($dispute_users, "{$seller_id}.email");
        $shipper_email = arrayGet($shipper, 'email');
        //endregion Users

        //region Update statistics
        model('user_statistic')->set_users_statistic(array_fill_keys(
            array_filter(array((int) $buyer_id, (int) $seller_id, (int) $shipper_id)),
            array('dispute_init' => -1, 'dispute_finished' => 1)
        ));
        //endregion Update statistics

        //region Bills
        $order_url = getUrlForGroup("order/popups_order/order_detail/{$order_id}");
        $dispute_url = getUrlForGroup("dispute/administration/order/{$order_id}");
        $dispute_link_label = "<a href=\"{$dispute_url}\" title=\"Dispute details\" target=\"_blank\">{$dispute_number}</a>";
        $order_link_label = "<a href=\"{$order_url}\" class=\"fancybox.ajax fancybox\" data-title=\"Order details\" title=\"Order details\">{$order_number}</a>";
        if (
            in_array(
                $status,
                array('payment_processing', 'order_paid', 'payment_confirmed', 'preparing_for_shipping', 'shipping_in_progress', 'shipping_completed')
            )
            && $can_refund_buyer
        ) {
            $comment = "
                To refund the buyer {$buyer_name} ( {$buyer_id} ), {$buyer_email} in base of dispute {$dispute_link_label}.
                <br>
                The order {$order_link_label} has been canceled.
            ";
            $external_bills[] = array(
                'to_user'   => $buyer_id,
                'user_type' => 'buyer',
                'money'     => moneyToDecimal($buyer_refund_amount),
                'comment'   => trim($comment),
                'date_time' => $current_date,
                'add_by'    => "EP Manager - {$user_name}",
            );
        }
        if (in_array($status, array('shipping_in_progress', 'shipping_completed'))) {
            if ($can_refund_seller) {
                if ($seller_refund_amount->greaterThan($zero_amount)) {
                    $comment = "To pay the seller {$seller_name} ( {$seller_id} ), {$seller_email} in base of dispute {$dispute_link_label}.";
                    if ('ishipper' == $order['shipper_type']) {
                        $comment = "{$comment} The payment include amount for shipping with {$shipper_name}.";
                    }
                } else {
                    $refund_amount = get_price($buyer_refund_amount, false);
                    $comment = "
                        <strong class=\"txt-red\">
                            To request from the seller {$seller_name} ( {$seller_id} ), {$seller_email}.
                            The buyer requested refund of $ {$refund_amount}, in base of dispute {$dispute_link_label}.
                        </strong>
                    ";
                }
                $comment = "{$comment}<br>The order {$order_link_label} has been canceled.';";

                $external_bills[] = array(
                    'to_user'   => $seller_id,
                    'user_type' => 'seller',
                    'money'     => moneyToDecimal($seller_refund_amount),
                    'comment'   => trim($comment),
                    'date_time' => $current_date,
                    'add_by'    => "EP Manager - {$user_name}",
                );
            }

            if ($can_refund_shipper) {
                $comment = "
                    To pay the freight forwarder {$shipper_name} ( {$shipper_id} ), {$shipper_email} in base of dispute {$dispute_link_label}.
                    <br>
                    The order {$order_link_label} has been canceled.
                ";
                $external_bills[] = array(
                    'to_user'   => $shipper_id,
                    'user_type' => 'shipper',
                    'money'     => moneyToDecimal($shipper_refund_amount),
                    'comment'   => trim($comment),
                    'date_time' => $current_date,
                    'add_by'    => "EP Manager - {$user_name}",
                );
            }
        }

        //region Add bills
        if (!empty($external_bills)) {
            $order_update['external_bills'] = implode(',', array_map(function ($entry) { return json_encode($entry); }, array_merge($order_bills, $external_bills)));
        }
        //endregion Add bills
        //endregion Bills

        switch ($action) {
            case 'continue':
                //region Update
                model('orders')->change_order($order_id, array_merge($order_update, array(
                    'order_summary' => implode(',', array_map(function ($entry) { return json_encode($entry); }, $order_summary)),
                )));
                //endregion Update

                //region Sent notifications
                $this->send_notifications($dispute_id, 'close_and_continue');
                //endregion Sent notifications

                jsonResponse('Dispute has been closed. The order will continue.', 'success');

                break;
            case 'cancel':
                //region Message
                $order_log_record['message'] = trim("
                    Order has been canceled by EP Manager in base of dispute {$dispute_number}. The dispute has been closed. {$refund_notice}
                    <br><strong>Reason: </strong>
                    {$reason}
                ");
                //endregion Message

                //region Update
                model('orders')->change_order($order_id, array_merge($order_update, array(
                    'status'        => 15,
                    'order_summary' => implode(',', array_map(function ($entry) { return json_encode($entry); }, $order_summary)),
                )));
                //endregion Update

                //region Sent notifications
                $this->send_notifications($dispute_id, 'close_and_cancel');
                //endregion Sent notifications

                jsonResponse('Dispute has been closed. The order has been canceled.', 'success');

                break;
        }

        jsonResponse(translate('systmess_error_invalid_data'));
    }

    /**
     * Adds a notice to dispute.
     *
     * @param int $user_id
     * @param int $dispute_id
     */
    private function add_dispute_notice($user_id, $dispute_id)
    {
        //region Validation
        $this->validator->set_rules(array(
            array(
                'field' => 'notice',
                'label' => 'Notice',
                'rules' => array('required' => '', 'max_len[500]' => ''),
            ),
        ));
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'seller', 'shipper', 'manager'))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        if (in_array($dispute['status'], array('canceled', 'closed'))) {
            jsonResponse(translate('systmess_error_order_add_dispute_notice_wrong_status'));
        }
        if (!have_right('dispute_administration')) {
            if ('processing' != $dispute['status'] && !have_right('buy_item')) {
                jsonResponse(translate('systmess_error_permission_not_granted'));
            }
        }
        //endregion Access

        //region Add notice
        if (!model('dispute')->append_timeline($dispute_id, json_encode($notice = array(
            'add_by'   => $this->get_user_name(),
            'add_date' => getDateFormat(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
            'notice'   => cleanInput($_POST['notice']),
            'title'    => 'New notice.',
        )))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }
        //endregion Add notice

        //region Send notifications
        $this->send_notifications($dispute_id, 'update');
        //endregion Send notifications

        jsonResponse(translate('systmess_success_order_add_dispute_notice'), 'success', $notice);
    }

    /**
     * Uploads the temporary files.
     */
    private function upload_temporary_files()
    {
        $request = request();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files')[0];
        if (null === $uploadedFile) {
            jsonResponse(translate('validation_image_required'));
        }
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse(translate('validation_invalid_file_provided'));
        }
        $config = 'img.disputes.main.rules';
        /** @var LegacyImageHandler $imageHandler */
        $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(LegacyImageHandler::class);
        // Given that we need to validate the file and it would take a long time
        // to write the new proper validation we can only use what we have.
        try {
            $imageHandler->assertImageIsValid(
                $imageHandler->makeImageFromFile($uploadedFile),
                config($config)
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
        // But first we need to get the full path to the file
        $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), $uploadedFile->getClientOriginalExtension());
        $pathToFile = FilePathGenerator::uploadedFile($fileName);
        //filesystem profider
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        // And write file there
        try {
            $tempDisk->write($pathToFile, $uploadedFile->getContent());
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('validation_images_upload_fail'));
        }
        //refactor the way full path is returned
        jsonResponse(null, 'success', [
            'files' => ['path' => $pathToFile, 'name' => $fileName, 'fullPath' => $tempDisk->url($pathToFile)]
        ]);
    }

    /**
     * Deletes the temporary file.
     * @param mixed  $file
     */
    private function delete_temporary_file($file = null)
    {
        if (empty($file)) {
            jsonResponse('File name is not correct.');
        }
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $pathToFile = FilePathGenerator::uploadedFile($file);

        if (!$tempDisk->fileExists($pathToFile)) {
            jsonResponse('Upload path is not correct.');
        }
        try{
            $tempDisk->delete($pathToFile);
        } catch (UnableToDeleteFile $e) {
            //silent fail
        }
        jsonResponse(null, 'success');
    }

    /**
     * Deletes the photo from dispute.
     *
     * @param int    $user_id
     * @param int    $dispute_id
     * @param string $file
     */
    private function delete_photo($user_id, $dispute_id, $file)
    {
        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'seller', 'shipper', 'manager'))) {
            jsonResponse('You are not participating in this disput.');
        }
        if (in_array($dispute['status'], array('canceled', 'closed'))) {
            jsonResponse("Photo cannot be deleted from the disputes that are in 'Closed' or 'Canceled' status.");
        }
        //endregion Access

        //region Photos
        $photos = with(json_decode($dispute['photos'], true), function ($photos) {
            return null === $photos || !is_array($photos) ? array() : $photos;
        });
        if (
            empty($user_photos = arrayGet($photos, $user_id, array()))
            || false === ($index = array_search($file, $user_photos))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Photos

        //region Remove file
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $remove_queue = [
            DisputeFilePathGenerator::imagePath($dispute['id_order'], $file).
            DisputeFilePathGenerator::thumbImage($dispute['id_order'], $file, DisputePhotoThumb::SMALL())
        ];
        foreach ($remove_queue as $fileToDelete) {
            try{
                $publicDisk->delete($fileToDelete);
            }catch (UnableToDeleteFile $e) {
                jsonResponse('Failed to remove the photo. Please try again later.');
            }
        }
        unset($photos[$user_id][$index]);
        //endregion Remove file

        //region Update
        if (!model('dispute')->dispute_update($dispute_id, array('photos' => json_encode($photos)))) {
            jsonResponse('Failed to remove the photo. Please try again later.');
        }
        //endregion Update

        jsonResponse('Dispute image has been successfully deleted.', 'success');
    }

    /**
     * Deletes the video from dispute.
     *
     * @param int   $user_id
     * @param int   $dispute_id
     * @param mixed $video
     */
    private function delete_video($user_id, $dispute_id, $video)
    {
        //region Dispute
        if (
            empty($dispute_id)
            || empty($dispute = model('dispute')->get_disput($dispute_id))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Dispute

        //region Access
        if (!model('dispute')->is_in_disput($user_id, $dispute_id, array('buyer', 'seller', 'shipper', 'manager'))) {
            jsonResponse('You are not participating in this disput.');
        }
        if (in_array($dispute['status'], array('canceled', 'closed'))) {
            jsonResponse("Video cannot be deleted from the disputes that are in 'Closed' or 'Canceled' status.");
        }
        //endregion Access

        //region Videos
        $videos = with(json_decode($dispute['videos'], true), function ($videos) {
            return null === $videos || !is_array($videos) ? array() : $videos;
        });
        if (
            empty($user_videos = arrayGet($videos, $user_id, array()))
            || false === ($index = array_search($video, array_column($user_videos, 'id')))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Videos

        //region Remove file
        /** @var FilesystemProviderInterface  $storageProvider */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        try{
            $publicDisk->delete(DisputeFilePathGenerator::videoImage($dispute['id_order'], $user_videos[$index]['thumb']));
        }catch (UnableToDeleteFile $e) {
            jsonResponse('Failed to remove the video. Please try again later.');
        }
        unset($videos[$user_id][$index]);
        //endregion Remove file

        //region Update
        if (!model('dispute')->dispute_update($dispute_id, array('videos' => json_encode($videos)))) {
            jsonResponse('Failed to remove the video. Please try again later.');
        }
        //endregion Update

        jsonResponse('Dispute video has been successfully deleted.', 'success');
    }

    private function send_notifications($dispute_id, $type)
    {
        $this->load->model('Dispute_Model', 'dispute');
        $this->load->model('Notify_Model', 'notify');

        if (!empty($data)) {
            extract($data);
        }

        $dispute = $this->dispute->get_disput($dispute_id);
        if (empty($dispute)) {
            return false;
        }

        $user = $this->get_user_name();
        $order_id = (int) $dispute['id_order'];
        $buyer_id = (int) arrayGet($dispute, 'id_buyer');
        $seller_id = (int) arrayGet($dispute, 'id_seller');
        $manager_id = (int) arrayGet($dispute, 'id_ep_manager');
        $shipper_id = (int) arrayGet($dispute, 'id_shipper');
        $message_key = 'dispute_' . strtolower($type);
        $order_number = orderNumber($order_id);
        $dispute_number = orderNumber($dispute_id);
        $make_notification_params = function (
            $full_url,
            $order_url,
            $dispute_url
        ) use ($user, $order_number, $dispute_number) {
            return array(
                '[DISPUTE_ID]'    => $dispute_number,
                '[DISPUTE_LINK]'  => $dispute_url,
                '[ORDER_ID]'      => $order_number,
                '[ORDER_LINK]'    => $order_url,
                '[LINK]'          => $full_url,
                '[USER]'          => $user,
            );
        };

        if (!empty($buyer_id)) {

			model('notify')->send_notify([
				'id_users'  => [$buyer_id],
				'mess_code' => $message_key,
				'replace'   => $make_notification_params(
					getUrlForGroup("dispute/my/dispute_number/{$dispute_id}/order_number/{$order_id}", 'buyer'),
					getUrlForGroup("order/my/order_number/{$order_id}", 'buyer'),
					getUrlForGroup("dispute/my/dispute_number/{$dispute_id}", 'buyer')
				),
				'systmess' => true
			]);

        }

        if (!empty($seller_id)) {

			model('notify')->send_notify([
				'id_users'  => [$seller_id],
				'mess_code' => $message_key,
				'replace'   => $make_notification_params(
					getUrlForGroup("dispute/my/dispute_number/{$dispute_id}/order_number/{$order_id}"),
					getUrlForGroup("order/my/order_number/{$order_id}"),
					getUrlForGroup("dispute/my/dispute_number/{$dispute_id}")
				),
				'systmess' => true
			]);

        }

        if (!empty($manager_id)) {

			model('notify')->send_notify([
				'id_users'  => [$manager_id],
				'mess_code' => $message_key,
				'replace'   => $make_notification_params(
					getUrlForGroup("dispute/administration/id/{$dispute_id}/order/{$order_id}", 'admin'),
					getUrlForGroup("order/admin_assigned/order/{$order_id}", 'admin'),
					getUrlForGroup("dispute/administration/id/{$dispute_id}", 'admin')
				),
				'systmess' => true
			]);

        }

        if (
            !empty($shipper_id) &&
            !empty(model('shippers')->get_shipper_by_user($shipper_id))
        ) {

			model('notify')->send_notify([
				'id_users'  => [$shipper_id],
				'mess_code' => $message_key,
				'replace'   => $make_notification_params(
					getUrlForGroup("dispute/my/dispute_number/{$dispute_id}/order_number/{$order_id}", 'shipper'),
					getUrlForGroup("order/my/order_number/{$order_id}", 'shipper'),
					getUrlForGroup("dispute/my/dispute_number/{$dispute_id}", 'shipper')
				),
				'systmess' => true
			]);

        }

        return true;
    }
}
