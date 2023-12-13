<?php

declare(strict_types=1);

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Validators\ProductOrderCommentValidator;

/**
 * Controller Product_order_comments
 */
class Product_order_comments_Controller extends TinyMVC_Controller
{
    /**
     * Index page
     */
    public function index(): void
    {
        // Here be dragons
    }

    public function popup_forms()
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_comment':
                checkPermisionAjaxModal('add_order_comments');

                /** @var Product_Orders_Model $productOrdersModel */
                $productOrdersModel = model(Product_Orders_Model::class);

                if (empty($orderId = (int) uri()->segment(4)) || !$productOrdersModel->has($orderId)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                views(
                    'admin/order/comments/form_view',
                    [
                        'formAction'    => __SITE_URL . 'product_order_comments/ajax_operations/add_comment',
                        'orderId'       => $orderId,
                    ]
                );

                break;
            default: show_404();
        }
    }

    public function ajax_operations()
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_comment':
                checkPermisionAjax('add_order_comments');

                $request = request()->request;

                //region Validation
                $validator = new ProductOrderCommentValidator(new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)));

                if (!$validator->validate($request->all())) {
                    jsonResponse(
                        array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }
                //endregion Validation

                /** @var Product_Order_Comments_Model $productOrderCommentsModel */
                $productOrderCommentsModel = model(Product_Order_Comments_Model::class);

                $productOrderCommentsModel->insertOne([
                    'order_id'  => $request->getInt('order'),
                    'user_id'   => id_session(),
                    'message'   => $request->get('message'),
                ]);

                jsonResponse('Your comment has been successfully added.', 'success');

                break;
            default: show_404();
        }
    }
}

// End of file product_order_comments.php
// Location: /tinymvc/myapp/controllers/product_order_comments.php
