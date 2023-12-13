<?php

use App\Common\Serializer\Context\AggregatedContext;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Common\Validation\ConstraintListInterface;
use App\Common\Validation\Constraints\AbstractAmount;
use App\Common\Validation\NestedValidationData;
use App\Common\Validation\Serializer\ConstraintSerializerAdapter;
use App\Common\Validation\Serializer\Context\AmountConstraintContext;
use App\Common\Validation\Validator;
use App\Payments\Serializer\MoneySerializerAdapter;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToReadFile;
use Money\Currencies\ISOCurrencies;
use Money\Exception\ParserException;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;
use Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Ramsey\Uuid\Uuid;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\OAuth\InvalidRequestException as OAuthInvalidRequestException;
use Stripe\Exception\RateLimitException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use const App\Logger\Activity\OperationTypes\BILLING_PAYMENT;
use const App\Logger\Activity\ResourceTypes\BILLING;

use function GuzzleHttp\json_decode;

/**
 * payments.php.
 *
 * Payments Controller
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \Tinymvc_Library_Cleanhtml $clean
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 * @property \SystMess_Model            $systmess
 * @property \Category_Model            $category
 * @property \Orders_model              $orders
 * @property \User_model                $user
 */
class Payments_Controller extends TinyMVC_Controller
{
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    public function administration()
    {
        checkIsLogged();
        checkAdmin('manage_bills,manage_translations');

        views(
            array('admin/header_view', 'admin/payments/index_view', 'admin/footer_view'),
            array(
                'languages' => $this->translations->get_allowed_languages(array('skip' => array('en'))),
            )
        );
    }

    public function success()
    {
        views(array('new/header_view', 'new/payment/payment_success_view', 'new/footer_view'), array(
            'title' => 'Payment success',
        ));
    }

    public function cancel()
    {
        views(array('new/header_view', 'new/payment/payment_cancel_view', 'new/footer_view'), array(
            'title' => 'Payment canceled',
        ));
    }

    public function failed()
    {
        views(array('new/header_view', 'new/payment/payment_failed_view', 'new/footer_view'), array(
            'title' => 'Payment failed',
        ));
    }

    public function bank_requisites()
    {
        $path = 'public/bank_requisites/';
        $file_name = 'instruction.pdf';
        $file = $path . $file_name;

        if (file_exists($file)) {
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $file_name . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            echo file_get_contents($file);
        }
    }

    public function process_credit_card()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        //region Token check
        if (!session()->isset_payment($token = (int) arrayGet($_POST, 'token'))) {
            jsonResponse('The wrong data was sent.', 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Token check

        //region Payment check
        $payment = session()->get_payment($token);
        if (
            empty($payment)
            || empty($bill_id = (int) arrayGet($payment, 'bill'))
            || empty($method = (int) arrayGet($payment, 'pay_method'))
            || empty($types = arrayGet($payment, 'bills_type'))
        ) {
            jsonResponse('The wrong data was sent.', 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Payment check

        //region Methods check
        if (!model('orders')->is_enable_pay_metod($method)) {
            jsonResponse('The requested payment method does not exist or it is not available at the moment.', 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Methods check

        //region User
        $user_id = privileged_user_id();
        $user = model('user')->getSimpleUser($user_id);
        //endregion User

        //region Bill
        $user_id = (int) privileged_user_id();
        if (empty($bill = model('user_bills')->get_user_bill($bill_id, array('id_user' => $user_id, 'bills_type' => $types)))) {
            jsonResponse("This bill doesn't exist.", 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Bill

        //region Bills
        $bills = array();
        $item_id = (int) $bill['id_item'];
        $bills_list = implode(',', array_merge(array($bill_id), arrayGet($payment, 'additional_bill', array())));
        if (
            empty($bills = model('user_bills')->get_user_bills(array(
                'status'     => "'init'",
                'id_item'    => $item_id,
                'id_user'    => $user_id,
                'hash_code'  => arrayGet($_POST, 'hash_code'),
                'bills_list' => $bills_list,
            )))
        ) {
            jsonResponse('The bills are not found.', 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Bills

        //region Order
        $order_id = (int) $bill['id_item'];
        $is_order_or_shipping = in_array($bill['id_type_bill'], array(1, 2));
        $order = $is_order_or_shipping ? model('orders')->get_order($order_id) : null;
        if ($is_order_or_shipping) {
            if (empty($order)) {
                jsonResponse('The order is not found.');
            }
            if (!in_array((int) $order['status'], array(4, 5))) {
                jsonResponse('Order has incorrect or not supported status.');
            }
        }
        //endregion Order

        //region Payment
        //region Amount
        $pay_amount = $this->get_bills_total_amount($bills, Money::USD(50));
        //endregion Amount

        try {
            //region Charge
            \Stripe\Stripe::setApiKey(config('env.STRIPE_SECRET_KEY'));
            $charge = \Stripe\Charge::create(array(
                'amount'      => $pay_amount->getAmount(),
                'currency'    => strtolower($pay_amount->getCurrency()->getCode()),
                'description' => sprintf('The bill %s', orderNumber($bill_id)),
                'source'      => arrayGet($_POST, 'stripe_token'),
                'metadata'    => array(
                    'bills_list' => $bills_list,
                    'notes'      => cleanInput(arrayGet($_POST, 'note')),
                ),
            ));

            if ('succeeded' !== $charge['status']) {
                throw new Exception('Failed to charge your credit card.');
            }
            //endregion Charge

            //region Log
            $log_notes = array();
            foreach ($bills as $bill) {
                $log_notes[$bill['id_bill']] = array(
                    'type'      => $bill['name_type'],
                    'id_bill'   => $bill['id_bill'],
                    'type_name' => $bill['show_name'],
                );
            }

            $bill_log = $this->bill_log_generator($log_notes, $bill_id);
            //endregion Log

            //region Bill processing
            //region User's notice
            $notice = '';
            if (!empty($_POST['note'])) {
                $notice = '<br /><strong>User note: </strong> ' . cleanInput($_POST['note']);
            }
            //endregion User's notice

            //region Bill data
            $pay_date = date('Y-m-d H:i:s');
            $full_note = array('date_note' => $pay_date, 'note' => $bill_log . $notice);
            $bill_data = array(
                'id_charge'      => array('label' => 'Charge ID',         'value' => $charge['id']),
                'id_transaction' => array('label' => 'Transaction ID',    'value' => $charge['balance_transaction']),
                'order_name'     => array('label' => 'Bill ID',           'value' => $charge['description']),
                'buyer_email'    => array('label' => 'Buyer email',       'value' => $user['email']),
                'payment_date'   => array('label' => 'Payment date',      'value' => $pay_date),
                'id_card'        => array('label' => 'Card ID',           'value' => $charge['source']['id']),
                'card_brand'     => array('label' => 'Card brand',        'value' => $charge['source']['brand']),
                'card_country'   => array('label' => 'Card country',      'value' => $charge['source']['country']),
                'card_exp_month' => array('label' => 'Card expire month', 'value' => $charge['source']['exp_month']),
                'card_exp_year'  => array('label' => 'Card expire year',  'value' => $charge['source']['exp_year']),
                'card_last4'     => array('label' => 'Card last 4',       'value' => $charge['source']['last4']),
            );
            //endregion Bill data

            $bills_ids = array();
            $bills_number = array();
            if (count($bills) > 1) {
                foreach ($bills as $bill) {
                    $update_info = array(
                        'pay_method' => 2,
                        'pay_date'   => $pay_date,
                        'amount'     => $bill['balance'],
                        'status'     => 'paid',
                        'note'       => json_encode($full_note),
                    );

                    $bill_id = (int) $bill['id_bill'];
                    $bills_ids[] = $bill_number = orderNumber($bill_id);
                    $bills_number[] = sprintf('<a href="%s">%s</a>', __SITE_URL . "billing/my/bill/{$bill_id}", $bill_number);
                    model('user_bills')->change_user_bill($bill_id, $update_info);
                    model('user_bills')->set_encrypt_data($bill_id, array('pay_detail' => serialize($bill_data)));
                }
            } else {
                $update_info = array(
                    'pay_method' => 2,
                    'pay_date'   => $pay_date,
                    'amount'     => moneyToDecimal($pay_amount),
                    'status'     => 'paid',
                    'note'       => json_encode($full_note),
                );

                $bill_id = (int) $bill['id_bill'];
                $bills_ids[] = $bill_number = orderNumber($bill_id);
                $bills_number[] = sprintf('<a href="%s">%s</a>', __SITE_URL . "billing/my/bill/{$bill_id}", $bill_number);
                model('user_bills')->change_user_bill($bill_id, $update_info);
                model('user_bills')->set_encrypt_data($bill_id, array('pay_detail' => serialize($bill_data)));
            }
            //endregion Bill processing

            //region Order update
            if ($is_order_or_shipping && null !== $order && 4 === (int) $order['status']) {
                $payment_processing_id = 5;
                $new_status_info = model('orders')->get_status_detail($payment_processing_id);
                model('orders')->change_order($order_id, array(
                    'status'           => $payment_processing_id,
                    'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
                ));
            }
            //endregion Order update

            //region Notifications
            if ($is_order_or_shipping && null !== $order) {
                $order_number = orderNumber($order_id);

                //region Administration notification
                $manager_id = (int) $order['ep_manager'];
                if (!empty($manager_id)) {

                    model('notify')->send_notify([
                        'systmess'  => true,
                        'mess_code' => 'bill_payment',
                        'id_users'  => [$manager_id],
                        'replace'   => [
                            '[BILLS]'      => implode(', ', $bills_number),
                            '[BILLS_IDS]'  => implode(', ', $bills_ids),
                            '[ORDER_NAME]' => $order_number,
                            '[ORDER_LINK]' => getUrlForGroup("order/admin_assigned/order_number/{$order_id}", 'admin'),
                            '[LINK]'       => getUrlForGroup("order/admin_assigned/order_number/{$order_id}", 'admin'),
                        ],
                    ]);

                }
                //endregion Administration notification

				//region User notification
				model('notify')->send_notify([
					'systmess'      => true,
					'mess_code'     => 'bill_payment',
					'id_item'       => $order_id,
					'id_users'      => [$user_id],
					'replace'       => [
						'[BILLS]'      => implode(', ', $bills_number),
						'[BILLS_IDS]'  => implode(', ', $bills_ids),
						'[ORDER_NAME]' => $order_number,
						'[ORDER_LINK]' => getUrlForGroup("order/my/order_number/{$order_id}", snakeCase($user['gr_type'])),
						'[LINK]'       => getUrlForGroup("order/my/order_number/{$order_id}", snakeCase($user['gr_type'])),
					],
				]);
                //endregion User notification
            } else {
                //region User notification

				model('notify')->send_notify([
					'systmess'      => true,
					'mess_code'     => 'bill_type_payment',
					'id_users'      => [$user_id],
					'replace'       => [
						'[BILLS]'     => implode(', ', $bills_number),
						'[BILLS_IDS]' => implode(', ', $bills_ids),
						'[BILL_TYPE]' => "\"{$bill['show_name']}\"",
						'[LINK]'      => getUrlForGroup('billing/my', snakeCase($user['gr_type'])),
					],
				]);

                //endregion User notification
            }
            //endregion Notifications

            //region Session clear
            session()->remove_payment($token);
            //endregion Session clear

            //region Update Activity Log
            $this->update_activity_log(
                $bill_id,
                $bills,
                $method,
                $pay_amount,
                $pay_date
            );
            //endregion Update Activity Log
        } catch (CardException $e) {
            jsonResponse($e->getMessage());
        } catch (InvalidRequestException | OAuthInvalidRequestException | ApiConnectionException $e) {
            jsonResponse('The payment method is unavailable due to connection error. Please try again later.');
        } catch (RateLimitException | AuthenticationException $e) {
            jsonResponse('The payment method is temporary unavailable. Please try again later.');
        } catch (ApiErrorException $e) {
            jsonResponse('Failed to make a transaction. Please try again later');
        } catch (Exception $e) {
            jsonResponse($e->getMessage());
        }
        //endregion Payment

        jsonResponse('Your payment has been received. After checking the transaction you will be notified about the payment status.', 'success', array('payment_data' => $payment));
    }

    public function ajax_payment_info()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        //region Token check
        if (!session()->isset_payment($token = (int) arrayGet($_POST, 'token'))) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Token check

        //region Payment check
        $payment = session()->get_payment($token);
        if (
            empty($payment)
            || empty($bill_id = (int) arrayGet($payment, 'bill'))
            || empty($method = (int) arrayGet($payment, 'pay_method'))
            || empty($types = arrayGet($payment, 'bills_type'))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Payment check

        //region Methods check
        if (!model('orders')->is_enable_pay_metod($method)) {
            jsonResponse(translate('systmess_payment_does_not_exist_message'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Methods check

        //region Validation
        $validator = $this->validator;
        switch ($method) {
            case 3: // Western Union
                $validator_rules = array(
                    array(
                        'field' => 'fname',
                        'label' => 'First name',
                        'rules' => array('required' => '', 'valid_user_name' => ''),
                    ),
                    array(
                        'field' => 'lname',
                        'label' => 'Last name',
                        'rules' => array('required' => '', 'valid_user_name' => ''),
                    ),
                    array(
                        'field' => 'transaction_id',
                        'label' => 'Transaction ID',
                        'rules' => array('required' => ''),
                    ),
                    // array(
                    //     'field' => 'pay_amount',
                    //     'label' => 'Pay amount',
                    //     'rules' => array('required' => '')
                    // ),
                    array(
                        'field' => 'document',
                        'label' => 'Payment form',
                        'rules' => array('required' => ''),
                    ),
                );

                break;
            case 4: // Money Gram
                $validator_rules = array(
                    array(
                        'field' => 'fname',
                        'label' => 'First name',
                        'rules' => array('required' => '', 'valid_user_name' => ''),
                    ),
                    array(
                        'field' => 'lname',
                        'label' => 'Last name',
                        'rules' => array('required' => '', 'valid_user_name' => ''),
                    ),
                    array(
                        'field' => 'transaction_id',
                        'label' => 'Transaction ID',
                        'rules' => array('required' => ''),
                    ),
                    // array(
                    //     'field' => 'pay_amount',
                    //     'label' => 'Pay amount',
                    //     'rules' => array('required' => '')
                    // ),
                    array(
                        'field' => 'document',
                        'label' => 'Payment form',
                        'rules' => array('required' => ''),
                    ),
                );

                break;
            case 6: // Wire Transfer
                $validator_rules = array(
                    array(
                        'field' => 'fname',
                        'label' => 'First name',
                        'rules' => array('required' => '', 'valid_user_name' => ''),
                    ),
                    array(
                        'field' => 'lname',
                        'label' => 'Last name',
                        'rules' => array('required' => '', 'valid_user_name' => ''),
                    ),
                    array(
                        'field' => 'transaction_id',
                        'label' => 'Transaction ID',
                        'rules' => array('required' => ''),
                    ),
                    // array(
                    //     'field' => 'pay_amount',
                    //     'label' => 'Pay amount',
                    //     'rules' => array('required' => '')
                    // ),
                    array(
                        'field' => 'document',
                        'label' => 'Payment form',
                        'rules' => array('required' => ''),
                    ),
                );

                break;
            default:
                jsonResponse(translate('systmess_payment_does_not_exist_message'));

                break;
        }

        $validator->set_rules($validator_rules);
        if (!$validator->validate()) {
            jsonResponse($validator->get_array_errors());
        }
        //endregion Validation

        //region User
        $user_id = (int) privileged_user_id();
        $user = model('user')->getSimpleUser($user_id);
        //endregion User

        //region Bill
        if (empty($bill = model('user_bills')->get_user_bill($bill_id, array('id_user' => $user_id, 'bills_type' => $types)))) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Bill

        //region Bills
        $bills = array();
        $item_id = (int) $bill['id_item'];
        $bills_list = implode(',', array_merge(array($bill_id), arrayGet($payment, 'additional_bill', array())));
        if (
            empty($bills = model('user_bills')->get_user_bills(array(
                'status'     => "'init'",
                'id_user'    => $user_id,
                'id_item'    => $item_id,
                'hash_code'  => arrayGet($_POST, 'hash_code'),
                'bills_list' => $bills_list,
            )))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Bills

        //region Order
        $order_id = (int) $bill['id_item'];
        $is_order_or_shipping = in_array($bill['id_type_bill'], array(1, 2));
        $order = $is_order_or_shipping ? model('orders')->get_order($order_id) : null;
        if ($is_order_or_shipping) {
            if (empty($order)) {
                jsonResponse(translate('systmess_error_invalid_data'));
            }
            if (!in_array((int) $order['status'], array(4, 5))) {
                jsonResponse(translate('systmess_error_invalid_data'));
            }
        }
        //endregion Order

        //region Payment
        //region Amount
        $pay_amount = $this->get_bills_total_amount($bills, Money::USD(50));
        //endregion Amount

        //region Log
        $log_notes = array();
        foreach ($bills as $bill) {
            $log_notes[$bill['id_bill']] = array(
                'type'      => $bill['name_type'],
                'id_bill'   => $bill['id_bill'],
                'type_name' => $bill['show_name'],
            );
        }

        $bill_log = $this->bill_log_generator($log_notes, $bill_id);
        //endregion Log

        //region Bill processing
        //region User's notice
        $notice = '';
        if (!empty($_POST['note'])) {
            $notice = '<br /><strong>User note: </strong> ' . cleanInput($_POST['note']);
        }
        //endregion User's notice

        //region Bill data
        //region Notes
        $name_post = array();
        switch ($method) {
            case 3: // Western Union
                $name_post = array(
                    'lname'          => 'Last name',
                    'fname'          => 'First name',
                    'pay_method'     => 'Payment method',
                    'transaction_id' => 'Transaction ID',
                );

                break;
            case 4: // Money Gram
                $name_post = array(
                    'lname'          => 'Last name',
                    'fname'          => 'First name',
                    'pay_method'     => 'Payment method',
                    'transaction_id' => 'Transaction ID',
                );

                break;
            case 6: // Wire Transfer
                $name_post = array(
                    'lname'          => 'Last name',
                    'fname'          => 'First name',
                    'pay_method'     => 'Payment method',
                    'transaction_id' => 'Transaction ID',
                );

                break;
        }
        //endregion Notes

        $pay_date = date('Y-m-d H:i:s');
        $full_note = array('date_note' => $pay_date, 'note' => $bill_log . $notice);
        $bill_data = array(
            'payment_date' => array('label' => 'Payment date', 'value' => $pay_date),
        );
        foreach ($_POST as $key => $post_item) {
            if (!empty($name_post[$key]) && 'note' != $key) {
                $bill_data[$key] = array('label' => $name_post[$key], 'value' => cleanInput($post_item));
            }
        }
        //endregion Bill data

        //region Payment form
        $document = null;
        if (!empty($documentPath = arrayGet($_POST, 'document'))) {
            $documentName = pathinfo($documentPath, PATHINFO_BASENAME);
            /** @var FilesystemProviderInterface */
            $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
            $usersDisk = $storageProvider->storage('users.legacy.storage');
            $tempDisk = $storageProvider->storage('temp.legacy.storage');
            $billPath = "{$user_id}/bills/{$bill_id}";
            $targetFile = "{$billPath}/{$documentName}";

            try {
                if ($usersDisk->fileExists($targetFile)) {
                    $usersDisk->delete($targetFile);
                }
                $usersDisk->writeStream($targetFile, $tempDisk->readStream($documentPath));
            } catch (UnableToReadFile $e) {
                try {
                    if (!str_starts_with($documentPath, 'temp')) {
                        throw $e;
                    }
                    $usersDisk->writeStream($targetFile, $tempDisk->readStream(substr($documentPath, 4)));
                } catch (UnableToReadFile $e) {
                    jsonResponse(throwableToMessage($e, translate('systmess_error_payment_upload_document')));
                }
            }
            $document = $documentName;
        }
        //endregion Payment form

        $bills_ids = array();
        $bills_number = array();
        if (count($bills) > 1) {
            foreach ($bills as $bill) {
                $update_info = array(
                    'payment_form' => $document,
                    'pay_method'   => $method,
                    'pay_date'     => $pay_date,
                    'amount'       => $bill['balance'],
                    'status'       => 'paid',
                    'note'         => json_encode($full_note),
                );

                $bill_id = (int) $bill['id_bill'];
                $bills_ids[] = $bill_number = orderNumber($bill_id);
                $bills_number[] = sprintf('<a href="%s">%s</a>', __SITE_URL . "billing/my/bill/{$bill_id}", $bill_number);
                model('user_bills')->change_user_bill($bill_id, $update_info);
                model('user_bills')->set_encrypt_data($bill_id, array('pay_detail' => serialize($bill_data)));
            }
        } else {
            $update_info = array(
                'payment_form' => $document,
                'pay_method'   => $method,
                'pay_date'     => $pay_date,
                'amount'       => moneyToDecimal($pay_amount),
                'status'       => 'paid',
                'note'         => json_encode($full_note),
            );

            $bill_id = (int) $bill['id_bill'];
            $bills_ids[] = $bill_number = orderNumber($bill_id);
            $bills_number[] = sprintf('<a href="%s">%s</a>', __SITE_URL . "billing/my/bill/{$bill_id}", $bill_number);
            model('user_bills')->change_user_bill($bill_id, $update_info);
            model('user_bills')->set_encrypt_data($bill_id, array('pay_detail' => serialize($bill_data)));
        }
        //endregion Bill processing

        //region Order update
        if ($is_order_or_shipping && null !== $order && 4 === (int) $order['status']) {
            $payment_processing_id = 5;
            $new_status_info = model('orders')->get_status_detail($payment_processing_id);
            model('orders')->change_order($order_id, array(
                'status'           => $payment_processing_id,
                'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
            ));
        }
        //endregion Order update

        //region Notifications
        if ($is_order_or_shipping && null !== $order) {
            $order_number = orderNumber($order_id);

            //region Administration notification
            $manager_id = (int) $order['ep_manager'];
            if (!empty($manager_id)) {
				model('notify')->send_notify([
					'systmess'  => true,
					'mess_code' => 'bill_payment',
					'id_users'  => [$manager_id],
					'replace'   => [
						'[BILLS]'      => implode(', ', $bills_number),
						'[BILLS_IDS]'  => implode(', ', $bills_ids),
						'[ORDER_NAME]' => $order_number,
						'[ORDER_LINK]' => getUrlForGroup("order/admin_assigned/order_number/{$order_id}", 'admin'),
						'[LINK]'       => getUrlForGroup("order/admin_assigned/order_number/{$order_id}", 'admin'),
					],
				]);
            }
            //endregion Administration notification

			//region User notification
			model('notify')->send_notify([
				'systmess'      => true,
				'mess_code'     => 'bill_payment',
				'id_item'       => $order_id,
				'id_users'      => [$user_id],
				'replace'       => [
					'[BILLS]'      => implode(', ', $bills_number),
					'[BILLS_IDS]'  => implode(', ', $bills_ids),
					'[ORDER_NAME]' => $order_number,
					'[ORDER_LINK]' => getUrlForGroup("order/my/order_number/{$order_id}", snakeCase($user['gr_type'])),
					'[LINK]'       => getUrlForGroup("order/my/order_number/{$order_id}", snakeCase($user['gr_type'])),
				],
			]);
			//endregion User notification

        } else {
            //region User notification

			model('notify')->send_notify([
				'systmess'      => true,
				'mess_code'     => 'bill_type_payment',
				'id_users'      => [$user_id],
				'replace'       => [
					'[BILLS]'     => implode(', ', $bills_number),
					'[BILLS_IDS]' => implode(', ', $bills_ids),
					'[BILL_TYPE]' => "\"{$bill['show_name']}\"",
					'[LINK]'      => getUrlForGroup('billing/my', snakeCase($user['gr_type'])),
				],
			]);

            //endregion User notification
        }
        //endregion Notifications

        //region Session clear
        session()->remove_payment($token);
        //endregion Session clear

        //endregion Payment

        //region Update Activity Log
        $this->update_activity_log(
            $bill_id,
            $bills,
            $method,
            $pay_amount,
            $pay_date
        );
        //endregion Update Activity Log

        jsonResponse(translate('systmess_success_pay_order_bill'), 'success', array('payment_data' => $payment));
    }

    public function ajax_payment_method()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        //region Token check
        $token = (int) arrayGet($_POST, 'token');
        if (!session()->isset_payment($token)) {
            jsonResponse(translate('systmess_error_permission_not_granted'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Token check

        //region Methods check
        $step = (int) arrayGet($_POST, 'step');
        $method = (int) arrayGet($_POST, 'method');
        if ($step >= 3 && (empty($method) || !model('orders')->is_enable_pay_metod($method))) {
            jsonResponse(translate('systmess_payment_does_not_exist_message'), 'error', array(
                'close_modal' => true,
            ));
        }

        session()->update_payment($token, array('pay_method' => $method));
        //endregion Methods check

        //region Payment
        if (empty($payment = session()->get_payment($token))) {
            jsonResponse(translate('systmess_incorrect_token_error'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Payment

        //region Bill
        $user_id = (int) privileged_user_id();
        if (
            empty($bill_id = arrayGet($payment, 'bill'))
            || empty($bill = model('user_bills')->get_user_bill($bill_id, array('id_user' => $user_id)))
        ) {
            jsonResponse(translate('systmess_billing_bill_not_exist_message'), 'error', array(
                'close_modal' => true,
            ));
        }
        $bill['token'] = $token;
        //endregion Bill

        //region Response data
        $response_data = array(
            'token'       => $token,
            'method'      => !empty($method) ? model('orders')->get_pay_method_with_i18n($method) : null,
            'pay_methods' => 2 === $step ? model('orders')->get_pay_methods_with_i18n(true) : null,
        );

        //region Additional information
        $handler_name = snakeCase("handler_bill_{$bill['name_type']}");
        if (method_exists($this, $handler_name)) {
            $handler_payload = $this->{$handler_name}($bill, $step);
            if (2 !== $step && empty($handler_payload)) {
                jsonResponse(translate('systmess_payment_does_not_exist_message'), 'error', array(
                    'close_modal' => true,
                ));
            }

            $response_data = array_replace_recursive($response_data, $handler_payload);
        }
        //endregion Additional information

        //region Payment methods filtering
        if (!empty($response_data['pay_methods'])) {
            $response_data['pay_methods'] = array_filter($response_data['pay_methods'], function ($method) use ($bill, $response_data) {
                $amount = priceToUsdMoney($bill['balance']);
                if (!empty($response_data['bills'])) {
                    $amount = $amount->add(...array_map(
                        function ($amount) { return priceToUsdMoney($amount); },
                        array_column($response_data['bills'], 'balance')
                    ));
                }

                $constraints_serializer = new ConstraintSerializerAdapter(null, new AggregatedContext(array(
                    new AmountConstraintContext(new MoneySerializerAdapter()),
                )));

                try {
                    if (
                        null !== ($raw_sconstraints = arrayGet($method, 'constraints'))
                        && null !== ($constraints = $constraints_serializer->deserialize(
                            $raw_sconstraints,
                            ConstraintListInterface::class,
                            'json'
                        ))
                    ) {
                        $data_set = new NestedValidationData(array(AbstractAmount::AMOUNT_ALIAS => $amount));
                        $validator = new Validator();

                        return $validator->validate($data_set, $constraints);
                    }
                } catch (\Exception $exception) {
                    return false;
                }

                return true;
            });
        }
        //endregion Payment methods filtering
        //endregion Response data

        //region Content
        $content = null;
        if (2 === $step) {
            $content = views()->fetch('new/payment/step_2_payment_view', $response_data);
        } elseif (3 === $step) {
            $response_data['notations'] = array(translate('billing_documents_step3_notations1'), translate('billing_documents_step3_notations2'));

            switch ($method) {
                case 1:
                    $response_data['content_step'] = views()->fetch('new/payment/step_3/visa_view', $response_data);

                    break;
                case 2:
                    $response_data['content_step'] = views()->fetch('new/payment/step_3/credit_card_view', $response_data);

                    break;
                case 3:
                    $response_data['fileupload'] = $this->get_fileupload_options((int) config('payments_wire_images_limit', 1));
                    $response_data['content_step'] = views()->fetch('new/payment/step_3/western_union_view', $response_data);

                    break;
                case 4:
                    $response_data['fileupload'] = $this->get_fileupload_options((int) config('payments_wire_images_limit', 1));
                    $response_data['content_step'] = views()->fetch('new/payment/step_3/money_gram_view', $response_data);

                    break;
                case 5:
                    $response_data['client_id'] = config('env.PAYPAL_CLIENT');
                    $response_data['content_step'] = views()->fetch('new/payment/step_3/paypal_view', $response_data);

                    break;
                case 6:
                    $response_data['fileupload'] = $this->get_fileupload_options((int) config('payments_wire_images_limit', 1));
                    $response_data['content_step'] = views()->fetch('new/payment/step_3/wire_transfer_view', $response_data);

                    break;
            }

            $content = views()->fetch('new/payment/step_3_payment_view', $response_data);
        }
        //endregion Content

        jsonResponse('Select method.', 'success', array('content' => $content));
    }

    public function popups_payment()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $this->_load_main();
        $this->load->model('User_Bills_Model', 'user_bills');

        $type = $this->uri->segment(3);

        switch ($type) {
            case 'pay_bill':
                $this->show_pay_bill_popup((int) privileged_user_id(), (int) uri()->segment(4));

                break;

            case 'payment_detail_admin':
                checkAdminAjaxModal('manage_bills');

                $this->show_admin_payment_details_popup((int) uri()->segment(4));

                break;
            case 'manage_bills_by_type':
                checkPermisionAjaxModal('administrate_orders');

                $bill_type = (int) uri()->segment(4);
                $bill_id_item = (int) uri()->segment(5);

                switch ($bill_type) {
                    case 7:
                        $involved_types = '7';

                        break;
                    case 1:
                    case 2:
                        $involved_types = '1,2';

                        break;
                    default:
                        $involved_types = '1,2,3,4,5,6,7';

                        break;
                }

                $params = array('id_item' => $bill_id_item, 'encript_detail' => 1, 'bills_type' => $involved_types, 'pagination' => false);
                $status = $this->uri->segment(6);
                if (!empty($status)) {
                    $params['status'] = "'" . cleanInput($status) . "'";
                }

                $data['bills'] = model('user_bills')->get_user_bills($params);
                if (empty($data['bills'])) {
                    messageInModal('There are no bills for this status.', 'info');
                }

                $data['status'] = model('user_bills')->get_bills_statuses();

                views()->assign($data);
                views()->display('admin/payments/popup_bills_list_view');

                break;
            case 'edit_payment':
                checkAdminAjaxModal('manage_bills');

                $id_payment = intval($this->uri->segment(4));
                $data['method'] = $this->orders->get_pay_method($id_payment);

                $this->view->display('admin/payments/form_view', $data);

                break;
            case 'add_payment':
                checkAdminAjaxModal('manage_bills');

                $this->view->display('admin/payments/form_view');

                break;
            case 'add_payment_i18n':
                checkAdminAjaxModal('manage_bills,manage_translations');
                $method_id = (int) $this->uri->segment(4);
                if (
                    empty($method_id) ||
                    empty($method = $this->orders->get_pay_method($method_id))
                ) {
                    messageInModal('Payment method with this ID is not found on this server');
                }

                $this->view->display('admin/payments/add_method_i18n_form_view', array(
                    'method'       => $method,
                    'action'       => __SITE_URL . "payments/ajax_methods/add_method_i18n/{$method_id}",
                    'translations' => array_column($this->orders->get_pay_methods_i18n_list(array($method_id)), 'id_lang'),
                    'languages'    => array_column($this->translations->get_allowed_languages(array('skip' => array('en'))), 'lang_name', 'id_lang'),
                ));

                break;
            case 'edit_payment_i18n':
                checkAdminAjaxModal('manage_bills,manage_translations');

                $method_i18n_id = (int) $this->uri->segment(4);
                if (
                    empty($method_i18n_id) ||
                    empty($method_i18n = $this->orders->get_pay_method_i18n($method_i18n_id))
                ) {
                    messageInModal('Payment method translation with this ID is not found on this server');
                }

                $language = $this->translations->get_language($method_i18n['id_lang']);
                if (
                    $this->session->group_lang_restriction &&
                    !in_array($language['id_lang'], $this->session->group_lang_restriction_list)
                ) {
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $this->view->display('admin/payments/edit_method_i18n_form_view', array(
                    'method'       => array(
                        'id'           => $method_i18n_id,
                        'original'     => array(
                            'instructions' => $method_i18n['instructions'],
                            'method'       => $method_i18n['method'],
                        ),
                        'instructions' => !empty($method_i18n['instructions_i18n']) ? $method_i18n['instructions_i18n'] : $method_i18n['instructions'],
                        'method'       => !empty($method_i18n['method_i18n']) ? $method_i18n['method_i18n'] : $method_i18n['method'],
                    ),
                    'action'       => __SITE_URL . "payments/ajax_methods/edit_method_i18n/{$method_i18n_id}",
                    'language'     => $language['lang_name'],
                ));

                break;
            default:
                messageInModal('The provided path is not found on this server.');

                break;
        }
    }

    public function download_payment_form()
    {
        if (!have_right('manage_bills')) {
            return false;
        }

        //region Bill
        if (
            empty($bill_id = (int) uri()->segment(3))
            || empty($bill = model('user_bills')->get_user_bill($bill_id))
            || empty($bill['payment_form'])
        ) {
            return false;
        }
        //endregion Bill

        //region File
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $disk = $storageProvider->storage('users.legacy.storage');
        $user_id = (int) $bill['id_user'];
        $file_name = $bill['payment_form'];
        $extension = end(explode('.', $file_name));
        if (!$disk->fileExists($file_path = "{$user_id}/bills/{$bill_id}/{$file_name}")) {
            return false;
        }
        //endregion File

        fileDownloadFromStream(
            $disk->readStream($file_path),
            sprintf('bill_%s_payment_form_%s', orderNumber($bill_id), date('Y_m_d_H_i_s')),
            $extension,
            $disk->mimeType($file_path)
        );
    }

    public function administration_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkAdminAjaxDT('manage_bills,manage_translations');

        $this->_load_main();

        $params = array(
            'limit' => $_POST['iDisplayLength'],
            'start' => $_POST['iDisplayStart'],
        );

        if ($_POST['iSortingCols'] > 0) {
            for ($i = 0; $i < $_POST['iSortingCols']; ++$i) {
                switch ($_POST['mDataProp_' . intval($_POST['iSortCol_0'])]) {
                    case 'dt_id':
                        $params['sort_by'][] = "id {$_POST["sSortDir_{$i}"]}";

                        break;
                    case 'dt_method':
                        $params['sort_by'][] = "method {$_POST["sSortDir_{$i}"]}";

                        break;
                }
            }
        }

        if (isset($_POST['lang'])) {
            $params['i18n_with_lang'] = cleanInput($_POST['lang']);
        }
        if (isset($_POST['not_lang'])) {
            $params['i18n_without_lang'] = cleanInput($_POST['not_lang']);
        }

        $methods = $this->orders->get_pay_methods($params);
        $methods_count = $this->orders->count_pay_methods($params);
        $output = array(
            'sEcho'                => (int) $_POST['sEcho'],
            'iTotalRecords'        => $methods_count,
            'iTotalDisplayRecords' => $methods_count,
            'aaData'               => array(),
        );

        if (empty($methods)) {
            jsonDTResponse(null, $output, 'success');
        }

        $languages = arrayByKey($this->translations->get_allowed_languages(array('skip' => array('en'))), 'id_lang');
        $methods_ids = array_flip(array_flip(array_column($methods, 'id')));
        $methods_i18n = arrayByKey($this->orders->get_pay_methods_i18n_list($methods_ids, array_column($languages, 'id_lang')), 'id_method', true);
        foreach ($methods as $method) {
            $method_id = $method['id'];
            $method_slug = strForURL($method['method']);
            $method_edit_url = __SITE_URL . "payments/popups_payment/edit_payment/{$method_id}";
            $method_activate_url = __SITE_URL . 'payments/ajax_methods/activate';
            $method_translations_add_url = __SITE_URL . "payments/popups_payment/add_payment_i18n/{$method_id}";
            $method_status = filter_var((int) $method['enable'], FILTER_VALIDATE_BOOLEAN);
            $method_status_button_icon = $method_status ? 'ep-icon_visible' : 'ep-icon_invisible';
            $method_status_button_title = $method_status ? 'Deactivate method' : 'Activate method';
            $method_text_updated_date = null !== $method['text_updated_at'] ? new DateTime($method['text_updated_at']) : null;
            $method_status_button_action = $method_status ? 0 : 1;

            $method_i18n_list = array();
            $method_i18n_used = array();
            $method_translations = array();
            if (isset($methods_i18n[$method_id])) {
                $method_i18n_list = arrayByKey($methods_i18n[$method_id], 'id_method_i18n');
                $method_i18n_used = array_column($methods_i18n[$method_id], 'id_lang');
                foreach ($method_i18n_list as $i18n_id => $method_i18n) {
                    $lang_id = $method_i18n['id_lang'];
                    $lang_name = 'Unknown';
                    $lang_code = 'UNKNOWN';
                    if (isset($languages[$lang_id])) {
                        $lang_name = $languages[$lang_id]['lang_name'];
                        $lang_code = strtoupper($languages[$lang_id]['lang_iso2']);
                    }

                    $method_i18n_edit_url = __SITE_URL . "payments/popups_payment/edit_payment_i18n/{$i18n_id}";
                    $method_i18n_update_date = null;
                    $method_i18n_label_color = 'btn-primary';
                    $method_i18n_update_notice = "Translated in language: '{$lang_name}'";

                    if (null !== $method_i18n['updated_i18n_at']) {
                        $method_i18n_update_date = new DateTime($method_i18n['updated_i18n_at']);
                    }
                    if (null !== $method_i18n_update_date) {
                        $method_i18n_update_notice = "{$method_i18n_update_notice}. Last update: {$method_i18n_update_date->format('Y-m-d H:i:s')}";
                    }
                    if (
                        null !== $method_text_updated_date &&
                        (
                            null === $method_i18n_update_date || $method_i18n_update_date < $method_text_updated_date
                        )
                    ) {
                        $method_i18n_label_color = 'btn-danger';
                        $method_i18n_update_notice = "{$method_i18n_update_notice}. Update required";
                    }

                    $method_translations[] = "
                        <a href=\"{$method_i18n_edit_url}\"
                            class=\"btn btn-xs {$method_i18n_label_color} mnw-30 w-30 mb-5 fancyboxValidateModalDT fancybox.ajax\"
                            data-title=\"Edit translation\"
                            title=\"{$method_i18n_update_notice}\">
                            {$lang_code}
                        </a>
                    ";
                }
            }

            $method_translations = implode('', $method_translations);
            $method_edit_button = '';
            $method_delete_button = '';
            $method_translate_button = '';
            if (have_right('manage_bills')) {
                $method_edit_button = "
                    <a class=\"ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT\"
                        data-title=\"Edit payment\"
                        data-fancybox-href=\"{$method_edit_url}\"
                        title=\"Edit payment\">
                    </a>
                ";
                $method_delete_button = "
                    <a href=\"#\"
                        class=\"ep-icon {$method_status_button_icon} call-function\"
                        data-callback=\"activateMethod\"
                        data-action=\"{$method_status_button_action}\"
                        data-method=\"{$method_id}\"
                        data-href=\"{$method_activate_url}\"
                        title=\"{$method_status_button_title}\">
                    </a>
                ";
            }
            if (have_right('manage_translations') && !empty(array_diff_key($languages, $method_i18n_used))) {
                $method_translate_button = "
                    <a href=\"{$method_translations_add_url}\"
                        data-title=\"Add translation\"
                        title=\"Add translation\"
                        class=\"fancyboxValidateModalDT fancybox.ajax\">
                        <i class=\"ep-icon ep-icon_globe-circle\"></i>
                    </a>
                ";
            }

            $output['aaData'][] = array(
                'dt_id'           => $method['id'],
                'dt_method'       => "
                    <div>
                        <i class=\"ico-pay-method i-{$method_slug} mr-10\" title=\"{$method['method']}\"></i>
                        <span class=\"name-b\">{$method['method']}</span>
                    </div>
                ",
                'dt_translations' => "
                    <div>
                        {$method_translations}
                    </div>
                ",
                'dt_actions'      => "
                    {$method_translate_button}
                    {$method_edit_button}
                    {$method_delete_button}
                ",
            );
        }

        jsonResponse(null, 'success', $output);
    }

    public function ajax_paypal_checkout()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        //region Token check
        if (!session()->isset_payment($token = (int) arrayGet($_POST, 'token'))) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Token check

        //region Payment check
        $payment = session()->get_payment($token);
        if (
            empty($payment)
            || empty($bill_id = (int) arrayGet($payment, 'bill'))
            || empty($method = (int) arrayGet($payment, 'pay_method'))
            || empty($types = arrayGet($payment, 'bills_type'))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Payment check

        //region Methods check
        if (!model('orders')->is_enable_pay_metod($method)) {
            jsonResponse(translate('systmess_payment_does_not_exist_message'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Methods check

        //region User
        $user_id = privileged_user_id();
        $user = model('user')->getSimpleUser($user_id);
        //endregion User

        //region Bill
        $user_id = (int) privileged_user_id();
        if (empty($bill = model('user_bills')->get_user_bill($bill_id, array('id_user' => $user_id, 'bills_type' => $types)))) {
            jsonResponse(translate('systmess_billing_bill_not_exist_message'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Bill

        //region Bills
        $bills = array();
        $item_id = (int) $bill['id_item'];
        $bills_list = implode(',', array_merge(array($bill_id), arrayGet($payment, 'additional_bill', array())));
        if (
            empty($bills = model('user_bills')->get_user_bills(array(
                'status'     => "'init'",
                'id_item'    => $item_id,
                'id_user'    => $user_id,
                'hash_code'  => arrayGet($_POST, 'hash_code'),
                'bills_list' => $bills_list,
            )))
        ) {
            jsonResponse(translate('systmess_billing_bills_not_found_message'), 'error', array(
                'close_modal' => true,
            ));
        }
        //endregion Bills

        //region Order
        $order_id = (int) $bill['id_item'];
        $is_order_or_shipping = in_array($bill['id_type_bill'], array(1, 2));
        $order = $is_order_or_shipping ? model('orders')->get_order($order_id) : null;
        if ($is_order_or_shipping) {
            if (empty($order)) {
                jsonResponse(translate('systmess_bill_order_not_found'));
            }
            if (!in_array((int) $order['status'], array(4, 5))) {
                jsonResponse(translate('systmess_bill_order_status_error_message'));
            }
        }
        //endregion Order

        //region Payment
        //region Amount
        $pay_amount = $this->get_bills_total_amount($bills, Money::USD(50));
        //endregion Amount

        switch (uri()->segment(3)) {
            case 'purchase':
                $this->create_paypal_purchase($pay_amount, $bill, $bills, $is_order_or_shipping);

                break;
            case 'complete_purchase':
                $this->complete_paypal_purchase(
                    arrayGet($_POST, 'order_id'),
                    $user_id,
                    $bill_id,
                    $item_id,
                    $method,
                    $pay_amount,
                    $user,
                    $bills,
                    $token,
                    $is_order_or_shipping,
                    $order_id,
                    $order
                );

                break;

            default:
                headerRedirect('/404');

                break;
        }
        //endregion Payment
    }

    public function ajax_methods()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $this->_load_main();
        $type = $this->uri->segment(3);
        $id_method = intval($_POST['method']);

        // Load html clean library
        $this->load->library('Cleanhtml', 'clean');
        $this->clean->defaultTextarea(array('style' => 'height,width,color,background-color', 'attribute' => 'class'));
        $this->clean->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br><table><thead><tbody><tfoot><tr><td><th><caption><colgroup><col>');

        switch ($type) {
            case 'edit':
                checkAdminAjax('manage_bills');

                $validator_rules = array(
                    array(
                        'field' => 'name_method',
                        'label' => 'Name method',
                        'rules' => array('required' => '', 'max_len[255]' => ''),
                    ),
                    array(
                        'field' => 'instructions',
                        'label' => 'Instructions',
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $method_id = (int) $_POST['method'];
                if (
                    empty($method_id) ||
                    empty($method = $this->orders->get_pay_method($method_id))
                ) {
                    jsonResponse('Payment method with this ID is not found on this server');
                }

                $update = array(
                    'method'       => trim(cleanInput($_POST['name_method'])),
                    'instructions' => trim($this->clean->sanitize($_POST['instructions'])),
                );
                if (
                    $update['instructions'] !== $method['instructions'] ||
                    $update['method'] !== $method['method']
                ) {
                    $update['text_updated_at'] = date('Y-m-d H:i:s');
                }

                if ($this->orders->change_pay_method($id_method, $update)) {
                    $data['methods'] = $this->orders->get_pay_methods(array('enable' => 1));

                    $path = 'public/bank_requisites/';
                    if (!is_dir($path)) {
                        mkdir($path, 0777);
                    }

                    $content = $this->view->fetch('new/pdf_templates/payments_methods_view', $data);
                    $file_pdf = 'instruction.pdf';

                    $this->load->library('mpdf', 'mpdf');
                    $mpdf = $this->mpdf->new_pdf();
                    $mpdf->defaultfooterline = 0;
                    $mpdf->WriteHTML($content);
                    $mpdf->Output($path . '/' . $file_pdf, 'F');

                    jsonResponse('Successfully changed.', 'success', array('name' => $update['method'], 'id_method' => $id_method));
                } else {
                    jsonResponse('Error: You cannot save these changes now. Please try again later.');
                }

                break;
            case 'insert':
                checkAdminAjax('manage_bills');

                $validator_rules = array(
                    array(
                        'field' => 'name_method',
                        'label' => 'Name method',
                        'rules' => array('required' => '', 'max_len[255]' => ''),
                    ),
                    array(
                        'field' => 'instructions',
                        'label' => 'Instructions',
                        'rules' => array('required' => ''),
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $insert = array(
                    'method'          => trim(cleanInput($_POST['name_method'])),
                    'instructions'    => trim($this->clean->sanitize($_POST['instructions'])),
                    'text_updated_at' => date('Y-m-d H:i:s'),
                );
                if ($id_method = $this->orders->set_pay_method($insert)) {
                    jsonResponse('Successfully saved.', 'success', array('name' => $insert['method'], 'id_method' => $id_method));
                }

                jsonResponse('Error: You cannot save these changes now. Please try again later.');

                break;
            case 'activate':
                checkAdminAjax('manage_bills');

                if ($this->orders->change_pay_method($id_method, array('enable' => intval($_POST['action'])))) {
                    jsonResponse('Successfully changed.', 'success');
                } else {
                    jsonResponse('Error: You cannot save these changes now. Please try again later.');
                }

                break;
            case 'add_method_i18n':
                checkAdminAjax('manage_bills');

                $validation_rules = array(
                    array(
                        'field' => 'id',
                        'label' => 'ID',
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'language',
                        'label' => 'Language',
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'method',
                        'label' => 'Name',
                        'rules' => array('required' => '', 'max_len[255]' => ''),
                    ),
                    array(
                        'field' => 'instructions',
                        'label' => 'Instructions',
                        'rules' => array('required' => ''),
                    ),
                );

                if (!empty($validation_rules)) {
                    $this->validator->set_rules($validation_rules);
                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }

                $method_id = (int) $_POST['id'];
                if (
                    empty($method_id) ||
                    empty($method = $this->orders->get_pay_method($method_id))
                ) {
                    jsonResponse('Payment method with this ID is not found on this server');
                }

                $lang_id = (int) $_POST['language'];
                if (
                    empty($lang_id) ||
                    !$this->translations->has_language($lang_id)
                ) {
                    jsonResponse('Language with this ID is not found on this server');
                }

                if (
                    $this->session->group_lang_restriction &&
                    !in_array($lang_id, $this->session->group_lang_restriction_list)
                ) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                if ($this->orders->has_pay_method_i18n($method_id, $lang_id)) {
                    jsonResponse('Payment method already has a translation in this language');
                }

                $method_i18n = array(
                    'id_method'         => $method_id,
                    'id_lang'           => $lang_id,
                    'method_i18n'       => trim(cleanInput($_POST['method'])),
                    'instructions_i18n' => trim($this->clean->sanitize($_POST['instructions'])),
                );

                if (!$this->orders->create_pay_method_i18n($method_id, $method_i18n)) {
                    jsonResponse('Failed to add payment method translation due to error on save');
                }

                jsonResponse('New translation for payment method was added', 'success');

                break;
            case 'edit_method_i18n':
                checkAdminAjax('manage_bills');

                $validation_rules = array(
                    array(
                        'field' => 'id',
                        'label' => 'ID',
                        'rules' => array('required' => ''),
                    ),
                    array(
                        'field' => 'method',
                        'label' => 'Name',
                        'rules' => array('required' => '', 'max_len[250]' => ''),
                    ),
                    array(
                        'field' => 'instructions',
                        'label' => 'Instructions',
                        'rules' => array('required' => ''),
                    ),
                );

                if (!empty($validation_rules)) {
                    $this->validator->set_rules($validation_rules);
                    if (!$this->validator->validate()) {
                        jsonResponse($this->validator->get_array_errors());
                    }
                }

                $method_i18n_id = (int) $_POST['id'];
                if (
                    empty($method_i18n_id) ||
                    empty($method = $this->orders->get_pay_method_i18n($method_i18n_id))
                ) {
                    jsonResponse('Payment method translation with this ID is not found on this server');
                }

                if (
                    $this->session->group_lang_restriction &&
                    !in_array($method['id_lang'], $this->session->group_lang_restriction_list)
                ) {
                    jsonResponse(translate("systmess_error_rights_perform_this_action"));
                }

                $method_i18n = array(
                    'method_i18n'       => trim(cleanInput($_POST['method'])),
                    'instructions_i18n' => trim($this->clean->sanitize($_POST['instructions'])),
                    'updated_i18n_at'   => date('Y-m-d H:i:s'),
                );

                if (!$this->orders->update_pay_method_i18n($method_i18n_id, $method_i18n)) {
                    jsonResponse('Failed to add payment method translation due to error on save');
                }

                jsonResponse('Translation for payment method was updated', 'success');

                break;
            case 'remove_method_i18n':
                checkAdminAjax('manage_bills,manage_translations');

                $method_i18n_id = (int) $this->uri->segment(4);
                if (
                    empty($method_i18n_id) ||
                    !$this->orders->is_pay_method_i18n($method_i18n_id)
                ) {
                    jsonResponse('Payment method translation with this ID is not found on this server');
                }

                if (!$this->orders->remove_pay_method_i18n($method_i18n_id)) {
                    jsonResponse('Failed to remove payment method translation due to database error');
                }

                jsonResponse('Translation for payment method was removed', 'success');

                break;
            default:
                jsonResponse('The provided path is not found on this server.');

                break;
        }
    }

    public function ajax_payment_upload_file()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        if (empty($_FILES['files'])) {
            jsonResponse('Please select file to upload.');
        }

        $upload_folder = $this->uri->segment(3);
        if (!($upload_folder = checkEncriptedFolder($upload_folder))) {
            jsonResponse('File upload path is not correct.');
        }

        $path = config('default_temp_payment_doc') . DS . id_session() . DS . $upload_folder;
        create_dir($path);

        $fi = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        $count_temp = iterator_count($fi);

        $disponible = 1 - $count_temp;
        if ($disponible <= 0) {
            jsonResponse('You cannot upload more than 1 file(s).');
        }

        if (count($_FILES['files']['name']) > $disponible) {
            jsonResponse('You cannot upload more than 1 file(s).');
        }

        $max_file_size = (int) config('fileupload_max_file_size', 10 * 1024 * 1024);
        $allowed_formats = array('pdf', 'jpg', 'jpeg', 'png');

        $allowed_mimes_raw = array_filter(array_map(function($format){
            return (new MimeTypes())->getMimeTypes($format);
        }, $allowed_formats));

        $allowed_mimes = array_reduce($allowed_mimes_raw, 'array_merge', array());

        $files = request()->files->all();
        foreach ($files['files'] as $file) {
            $f = @finfo_open(FILEINFO_MIME_TYPE);
            $file_mime = finfo_file($f, $file);
            finfo_close($f);

            // $file_mime = $file->getClientMimeType(); //returns invalid data
            $file_size = $file->getSize();
            $file_extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            $new_file_name = uniqid() . '.' . $file_extension;
            if (!in_array($file_extension, $allowed_formats)) {
                jsonResponse('File type not allowed.');
            }

            if (!in_array($file_mime, $allowed_mimes)) {
                jsonResponse('File has not available mime-type (' . $file_mime . ')');
            }

            if ($file_size > $max_file_size) {
                jsonResponse('The maximum file size has to be ' . floor($file_size / 1048576) . ' MB.');
            }

            try {
                $file->move($path, $new_file_name);

                $result['files'][] = array('path'=> $path . DS . $new_file_name, 'name' => $new_file_name, 'type' => $file_extension);
            } catch (FileException $e) {
                jsonResponse('Some errors occurred while uploading the payment form.');
            }
        }

        jsonResponse('', 'success', $result);
    }

    public function ajax_payment_delete_file()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        $upload_folder = $this->uri->segment(3);
        if (!($upload_folder = checkEncriptedFolder($upload_folder))) {
            jsonResponse('Error: File upload path is not correct.');
        }

        $user_id = (int) id_session();
        $basepath = config('default_temp_payment_doc');
        $path = $basepath . DS . $user_id . DS . $upload_folder;
        if (!is_dir($path)) {
            jsonResponse('Upload path does not exist.');
        }

        removeFileIfExists($path . DS . $_POST['file']);
        jsonResponse('', 'success');
    }

    public function save_bill_document()
    {
        checkAdmin('manage_bills');

        $this->load->model('User_Bills_Model', 'user_bills');

        $id_bill = intval($this->uri->segment(3));
        $bill_info = $this->user_bills->get_user_bill($id_bill);

        if (empty($bill_info)) {
            $this->session->setMessages('Error: This bill doesn\'t exist.', 'errors');
            headerRedirect(__SITE_URL);
        }

        file_force_download('public/img/users/' . $bill_info['id_user'] . '/bills/' . $bill_info['id_bill'] . '/' . $bill_info['payment_form']);
    }

    protected function create_paypal_purchase(
        Money $pay_amount,
        array $bill,
        array $bills = array(),
        $is_order_or_shipping = false
    ) {
        //region Items
        $items = array_values(
            array_map(
                function ($bill) use ($is_order_or_shipping) {
                    return array(
                        'name'        => $bill['bill_description'],
                        'quantity'    => 1,
                        'category'    => $is_order_or_shipping ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS',
                        'unit_amount' => array(
                            'value'         => $bill['balance'],
                            'currency_code' => 'USD',
                        ),
                    );
                },
                arrayByKey(array_merge(array($bill), $bills), 'id_bill')
            )
        );
        //endregion Items

        //region Payment reference
        try {
            $payment_reference_id = Uuid::uuid1()->toString();
        } catch (UnableToBuildUuidException | RuntimeException $exception) {
            $payment_reference_id = str_replace('.', '-', uniqid('', true));
        }
        //endregion Payment reference

        //region Request
        $environment = 'sandbox' === config('env.PAYPAL_MODE', 'sandbox')
            ? new SandboxEnvironment(config('env.PAYPAL_CLIENT'), config('env.PAYPAL_SECRET'))
            : new ProductionEnvironment(config('env.PAYPAL_CLIENT'), config('env.PAYPAL_SECRET'));
        $client = new PayPalHttpClient($environment);
        $request = new OrdersCreateRequest();
        $request->body = array(
            'intent'         => 'CAPTURE',
            'purchase_units' => array(array(
                'reference_id' => "EP-PAYMENT-{$payment_reference_id}",
                'items'        => $items,
                'amount'       => array(
                    'value'         => $subtotal = moneyToDecimal($pay_amount),
                    'currency_code' => $currency_code = $pay_amount->getCurrency()->getCode(),
                    'breakdown'     => array(
                        'item_total' => array(
                            'value'         => $subtotal,
                            'currency_code' => $currency_code,
                        ),
                    ),
                ),
            )),
            'application_context' => array(
                'cancel_url' => getUrlForGroup('payments/cancel'),
                'return_url' => getUrlForGroup('payments/success'),
            ),
        );
        //endregion Request

        //region Purchase
        try {
            // Call API with your client and get a response for your call
            $purchase = (object) $client->execute($request)->result;
            // Return payment ID
            jsonResponse(translate('systmess_bill_paypal_payment_success_message'), 'success', array(
                'data' => array(
                    'id' => $purchase->id,
                ),
            ));
        } catch (HttpException $exception) {
            try {
                $this->handle_paypal_error(json_decode($exception->getMessage(), true), $exception->statusCode, $exception->headers);
            } catch (\Exception $exception) {
                jsonResponse(translate('systmess_billing_failed_transaction_error_message'));
            }
        }
        //endregion Purchase
    }

    protected function complete_paypal_purchase(
        $authorized_order_id,
        $user_id,
        $bill_id,
        $item_id,
        $method,
        $pay_amount,
        $user,
        $bills,
        $token,
        $is_order_or_shipping = false,
        $order_id = null,
        $order = null
    ) {
        //region Request
        $environment = 'sandbox' === config('env.PAYPAL_MODE', 'sandbox')
            ? new SandboxEnvironment(config('env.PAYPAL_CLIENT'), config('env.PAYPAL_SECRET'))
            : new ProductionEnvironment(config('env.PAYPAL_CLIENT'), config('env.PAYPAL_SECRET'));
        $client = new PayPalHttpClient($environment);
        $request = new OrdersCaptureRequest($authorized_order_id);
        $request->prefer('return=representation');
        //endregion Request

        //region Charge
        try {
            // Call API with your client and get a response for your call
            $response = $client->execute($request);
            $purchase = (object) $response->result;
        } catch (HttpException $exception) {
            try {
                $this->handle_paypal_error(json_decode($exception->getMessage(), true), $exception->statusCode, $exception->headers);
            } catch (\Exception $exception) {
                jsonResponse(translate('systmess_billing_failed_transaction_error_message'));
            }
        }
        //endregion Charge

        //region Log
        $log_notes = array();
        foreach ($bills as $bill) {
            $log_notes[$bill['id_bill']] = array(
                'type'      => $bill['name_type'],
                'id_bill'   => $bill['id_bill'],
                'type_name' => $bill['show_name'],
            );
        }

        $bill_log = $this->bill_log_generator($log_notes, $bill_id);
        //endregion Log

        //region Bill processing
        //region User's notice
        $notice = '';
        if (!empty($_POST['note'])) {
            $notice = '<br /><strong>User note: </strong> ' . cleanInput($_POST['note']);
        }
        //endregion User's notice

        //region Bill data
        $pay_date = date('Y-m-d H:i:s');
        $full_note = array('date_note' => $pay_date, 'note' => $bill_log . $notice);
        $bill_data = array(
            'id_order'        => array('label' => 'Order ID',        'value' => $purchase->id),
            'id_payer'        => array('label' => 'Payer ID',        'value' => $purchase->payer->payer_id),
            'id_merchant'     => array('label' => 'Merchant ID',     'value' => $purchase->purchase_units[0]->payee->merchant_id),
            'id_transaction'  => array('label' => 'Transaction ID',  'value' => $purchase->purchase_units[0]->reference_id),
            'id_payment'      => array('label' => 'Payment ID',      'value' => $purchase->purchase_units[0]->payments->captures[0]->id),
            'payment_date'    => array('label' => 'Payment date',    'value' => $purchase->purchase_units[0]->payments->captures[0]->create_time),
            'payment_context' => array('label' => 'Payment context', 'value' => json_encode($purchase, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
        );
        //endregion Bill data

        $bills_ids = array();
        $bills_number = array();
        if (count($bills) > 1) {
            foreach ($bills as $bill) {
                $update_info = array(
                    'pay_method' => $method,
                    'pay_date'   => $pay_date,
                    'amount'     => $bill['balance'],
                    'status'     => 'paid',
                    'note'       => json_encode($full_note),
                );

                $bill_id = (int) $bill['id_bill'];
                $bills_ids[] = $bill_number = orderNumber($bill_id);
                $bills_number[] = sprintf('<a href="%s">%s</a>', __SITE_URL . "billing/my/bill/{$bill_id}", $bill_number);
                model('user_bills')->change_user_bill($bill_id, $update_info);
                model('user_bills')->set_encrypt_data($bill_id, array('pay_detail' => serialize($bill_data)));
            }
        } else {
            $update_info = array(
                'pay_method' => $method,
                'pay_date'   => $pay_date,
                'amount'     => moneyToDecimal($pay_amount),
                'status'     => 'paid',
                'note'       => json_encode($full_note),
            );

            $bill_id = (int) $bill['id_bill'];
            $bills_ids[] = $bill_number = orderNumber($bill_id);
            $bills_number[] = sprintf('<a href="%s">%s</a>', __SITE_URL . "billing/my/bill/{$bill_id}", $bill_number);
            model('user_bills')->change_user_bill($bill_id, $update_info);
            model('user_bills')->set_encrypt_data($bill_id, array('pay_detail' => serialize($bill_data)));
        }
        //endregion Bill processing

        //region Order update
        if ($is_order_or_shipping && null !== $order && 4 === (int) $order['status']) {
            $payment_processing_id = 5;
            $new_status_info = model('orders')->get_status_detail($payment_processing_id);
            model('orders')->change_order($order_id, array(
                'status'           => $payment_processing_id,
                'status_countdown' => date_plus($new_status_info['countdown'], 'days', false, true),
            ));
        }
        //endregion Order update

        //region Notifications
        if ($is_order_or_shipping && null !== $order) {
            $order_number = orderNumber($order_id);

            //region Administration notification
            $manager_id = (int) $order['ep_manager'];
            if (!empty($manager_id)) {

				model('notify')->send_notify([
					'systmess'  => true,
					'mess_code' => 'bill_payment',
					'id_users'  => [$manager_id],
					'replace'   => [
						'[BILLS]'      => implode(', ', $bills_number),
						'[BILLS_IDS]'  => implode(', ', $bills_ids),
						'[ORDER_NAME]' => $order_number,
						'[ORDER_LINK]' => getUrlForGroup("order/admin_assigned/order_number/{$order_id}", 'admin'),
						'[LINK]'       => getUrlForGroup("order/admin_assigned/order_number/{$order_id}", 'admin'),
					],
				]);

            }
            //endregion Administration notification

			//region User notification
			model('notify')->send_notify([
				'systmess'      => true,
				'mess_code'     => 'bill_payment',
				'id_item'       => $order_id,
				'id_users'      => [$user_id],
				'replace'       => [
					'[BILLS]'      => implode(', ', $bills_number),
					'[BILLS_IDS]'  => implode(', ', $bills_ids),
					'[ORDER_NAME]' => $order_number,
					'[ORDER_LINK]' => getUrlForGroup("order/my/order_number/{$order_id}", snakeCase($user['gr_type'])),
					'[LINK]'       => getUrlForGroup("order/my/order_number/{$order_id}", snakeCase($user['gr_type'])),
				],
			]);
			//endregion User notification

        } else {
            //region User notification

			model('notify')->send_notify([
				'systmess'      => true,
				'mess_code'     => 'bill_type_payment',
				'id_users'      => [$user_id],
				'replace'       => [
					'[BILLS]'     => implode(', ', $bills_number),
					'[BILLS_IDS]' => implode(', ', $bills_ids),
					'[BILL_TYPE]' => "\"{$bill['show_name']}\"",
					'[LINK]'      => getUrlForGroup('billing/my', snakeCase($user['gr_type'])),
				],
			]);

            //endregion User notification
        }
        //endregion Notifications

        //region Session clear
        session()->remove_payment($token);
        //endregion Session clear

        //region Update Activity Log
        $this->update_activity_log(
            $bill_id,
            $bills,
            $method,
            $pay_amount,
            $pay_date
        );
        //endregion Update Activity Log

        jsonResponse(translate('systmess_bill_payment_recieved_message'), 'success', array('payment_data' => $purchase));
    }

    protected function handle_paypal_error($response, $status_code, array $headers = array())
    {
        $get_debug_information = function ($response, $headers, $status_code) {
            return !DEBUG_MODE ? array() : array(
                'code'     => $status_code,
                'error'    => $response,
                'debug_id' => isset($headers['Paypal-Debug-Id']) ? $headers['Paypal-Debug-Id'] : null,
            );
        };

        if (in_array($status_code, array(401, 403, 404, 405, 406))) {
            jsonResponse(
                translate('payments_paypal_generic_error_messages_unresponsive_service', null, true),
                'error',
                $get_debug_information($response, $headers, $status_code)
            );
        }
        if (in_array($status_code, array(500, 503))) {
            jsonResponse(
                translate('payments_paypal_generic_error_messages_unavailable_service', null, true),
                'error',
                $get_debug_information($response, $headers, $status_code)
            );
        }
        if (in_array($status_code, array(429))) {
            jsonResponse(
                translate('payments_paypal_generic_error_messages_reached_rate_limits', null, true),
                'error',
                $get_debug_information($response, $headers, $status_code)
            );
        }
        if (in_array($status_code, array(422))) {
            $is_declined = in_array('INSTRUMENT_DECLINED', dataGet($response, 'details.*.issue', array()));
            if (
                empty($response)
                || empty($details = arrayGet($response, 'details', array()))
                || !is_array($details)
                || empty(
                    $messages = array_map(
                        function ($error) {
                            return with(translate("payments_paypal_error_messages_{$error['issue']}"), function ($message) use ($error) {
                                return !empty($message) ? $message : $error['description'];
                            });
                        },
                        $details
                    )
                )
            ) {
                $messages = array(translate('payments_paypal_generic_error_messages_validation_issues', null, true));
            }

            jsonResponse(
                $messages,
                'error',
                array_merge(
                    array('is_declined' => $is_declined),
                    $get_debug_information($response, $headers, $status_code)
                )
            );
        }

        jsonResponse(
            translate('payments_paypal_generic_error_messages_undefined_error'),
            'error',
            $get_debug_information($response, $headers, $status_code)
        );
    }

    protected function handler_bill_order(array $bill, $step)
    {
        $token = $bill['token'];
        $bill_id = (int) $bill['id_bill'];
        $user_id = (int) privileged_user_id();
        $order_id = (int) $bill['id_item'];
        $order = model('orders')->get_full_order($order_id, array(
            'id_buyers' => $user_id,
        ));

        if (1 === (int) $step) {
            $payments = arrayByKey(
                array_filter((array) model('user_bills')->get_user_bills(array(
                    'status'     => "'init'",
                    'id_item'    => $order_id,
                    'id_user'    => $user_id,
                    'bills_type' => '1,2', // bills_type: 1 - order, 2 - ship
                    'pagination' => false,
                ))),
                'id_bill'
            );

            $bill_info = array(
                'bill_description' => $bill['bill_description'],
                'name_type'        => 'Order',
                'id_bill'          => $bill_id,
                'title'            => orderNumber($bill_id),
                'price'            => $bill['balance'],
            );

            $content_payments = views()->fetch('new/payment/additional_payments_view', compact('order', 'payments', 'bill_info'));
        }

        if (2 === (int) $step || 3 === (int) $step) {
            $bills = array();
            $bills_ids = array();
            $additional_bills_ids = array();
            $hash_code = get_sha1_token(random_bytes(64) . "-{$user_id}");
            if (!empty($additional_payments = arrayGet($_POST, 'additionalPay', array()))) {
                $bills_ids = array_filter(array_map(
                    function ($payment) { return arrayGet(explode('-', $payment), 2); },
                    $additional_payments
                ));
            }

            if (!empty($bills_ids)) {
                $bills = model('user_bills')->get_user_bills(array(
                    'status'     => "'init'",
                    'id_user'    => $user_id,
                    'id_item'    => $order_id,
                    'bills_type' => '1,2',
                    'bills_list' => implode(',', $bills_ids),
                    'pagination' => false,
                ));
            }

            if (3 === (int) $step) {
                $additional_bills = implode(',', $additional_bills_ids = array_map('intval', array_column($bills, 'id_bill')));
                $total_amount = moneyToDecimal(priceToUsdMoney($bill['balance'])->add(...array_map(
                    function ($bill) { return priceToUsdMoney($bill['balance']); },
                    $bills
                )));

                session()->update_payment($token, array('additional_bill' => $additional_bills_ids, 'bills_type' => '1,2'));
                model('user_bills')->change_user_bills(
                    implode(',', array_merge(array($bill_id), $additional_bills_ids)),
                    array('hash_code' => $hash_code)
                );
            }
        }

        return array_filter(
            compact(
                'order',
                'bills',
                'payments',
                'bill_info',
                'hash_code',
                'total_amount',
                'content_payments',
                'additional_bills'
            ),
            function ($entry) { return null !== $entry; }
        );
    }

    protected function handler_bill_sample_order (array $bill, int $step): array
    {
        $token = $bill['token'];
        $bill_id = (int) $bill['id_bill'];
        $user_id = (int) privileged_user_id();
        $order_id = (int) $bill['id_item'];
        $type_id = (int) $bill['id_type_bill'];
        $order = model(Sample_Orders_Model::class)->find($order_id);

        if (1 === (int) $step) {
            $payments = array_filter(
                arrayByKey(
                    array_filter((array) model('user_bills')->get_user_bills(array(
                        'status'     => "'init'",
                        'id_item'    => $order_id,
                        'id_user'    => $user_id,
                        'bills_type' => $type_id,
                        'pagination' => false,
                    ))),
                    'id_bill'
                ),
                function ($key) use ($bill_id) { return (int) $bill_id !== (int) $key; },
                ARRAY_FILTER_USE_KEY
            );

            $bill_info = array(
                'bill_description' => $bill['bill_description'],
                'name_type'        => $bill['show_name'] ?? 'Sample Order',
                'id_bill'          => $bill_id,
                'title'            => orderNumber($bill_id),
                'price'            => $bill['balance'],
            );

            $content_payments = views()->fetch('new/payment/additional_payments_view', compact('order', 'payments', 'bill_info'));
        }

        if (2 === (int) $step || 3 === (int) $step) {
            $bills = array();
            $bills_ids = array();
            $additional_bills_ids = array();
            $hash_code = get_sha1_token(random_bytes(64) . "-{$user_id}");
            if (!empty($additional_payments = arrayGet($_POST, 'additionalPay', array()))) {
                $bills_ids = array_filter(array_map(
                    function ($payment) { return arrayGet(explode('-', $payment), 2); },
                    $additional_payments
                ));
            }

            if (!empty($bills_ids)) {
                $bills = model('user_bills')->get_user_bills(array(
                    'status'     => "'init'",
                    'id_user'    => $user_id,
                    'id_item'    => $order_id,
                    'bills_type' => $type_id,
                    'bills_list' => implode(',', $bills_ids),
                    'pagination' => false,
                ));
            }

            if (3 === (int) $step) {
                $additional_bills = implode(',', $additional_bills_ids = array_map('intval', array_column($bills, 'id_bill')));
                $total_amount = moneyToDecimal(priceToUsdMoney($bill['balance'])->add(...array_map(
                    function ($bill) { return priceToUsdMoney($bill['balance']); },
                    $bills
                )));

                session()->update_payment($token, array('additional_bill' => $additional_bills_ids, 'bills_type' => $type_id));
                model('user_bills')->change_user_bills(
                    implode(',', array_merge(array($bill_id), $additional_bills_ids)),
                    array('hash_code' => $hash_code)
                );
            }
        }

        return array_filter(
            compact(
                'order',
                'bills',
                'payments',
                'bill_info',
                'hash_code',
                'total_amount',
                'content_payments',
                'additional_bills'
            ),
            function ($entry) { return null !== $entry; }
        );
    }

    protected function handler_bill_ship($bill, $step)
    {
        $token = $bill['token'];
        $bill_id = (int) $bill['id_bill'];
        $user_id = (int) privileged_user_id();
        $order_id = (int) $bill['id_item'];
        $order = model('orders')->get_full_order($order_id, array(
            'id_buyers' => $user_id,
        ));

        if (1 === (int) $step) {
            $payments = arrayByKey(
                array_filter((array) model('user_bills')->get_user_bills(array(
                    'status'     => "'init'",
                    'id_item'    => $order_id,
                    'id_user'    => $user_id,
                    'bills_type' => '1,2', // bills_type: 1 - order, 2 - ship
                    'pagination' => false,
                ))),
                'id_bill'
            );

            $bill_info = array(
                'bill_description' => $bill['bill_description'],
                'name_type'        => 'Shipping',
                'id_bill'          => $bill_id,
                'title'            => orderNumber($bill_id),
                'price'            => $bill['balance'],
            );

            $content_payments = views()->fetch('new/payment/additional_payments_view', compact('order', 'payments', 'bill_info'));
        }

        if (2 === (int) $step || 3 === (int) $step) {
            $bills = array();
            $bills_ids = array();
            $additional_bills_ids = array();
            $hash_code = get_sha1_token(random_bytes(64) . "-{$user_id}");
            if (!empty($additional_payments = arrayGet($_POST, 'additionalPay', array()))) {
                $bills_ids = array_filter(array_map(
                    function ($payment) { return arrayGet(explode('-', $payment), 2); },
                    $additional_payments
                ));
            }

            if (!empty($bills_ids)) {
                $bills = model('user_bills')->get_user_bills(array(
                    'status'     => "'init'",
                    'id_user'    => $user_id,
                    'id_item'    => $order_id,
                    'bills_type' => '1,2',
                    'bills_list' => implode(',', $bills_ids),
                    'pagination' => false,
                ));
            }

            if (3 === (int) $step) {
                $additional_bills = implode(',', $additional_bills_ids = array_map('intval', array_column($bills, 'id_bill')));
                $total_amount = moneyToDecimal(priceToUsdMoney($bill['balance'])->add(...array_map(
                    function ($bill) { return priceToUsdMoney($bill['balance']); },
                    $bills
                )));

                session()->update_payment($token, array('additional_bill' => $additional_bills_ids, 'bills_type' => '1,2'));
                model('user_bills')->change_user_bills(
                    implode(',', array_merge(array($bill_id), $additional_bills_ids)),
                    array('hash_code' => $hash_code)
                );
            }
        }

        return array_filter(
            compact(
                'order',
                'bills',
                'payments',
                'bill_info',
                'hash_code',
                'total_amount',
                'content_payments',
                'additional_bills'
            ),
            function ($entry) { return null !== $entry; }
        );
    }

    protected function handler_bill_feature_item($bill, $step)
    {
        $token = $bill['token'];
        $bill_id = (int) $bill['id_bill'];
        $item_id = (int) $bill['id_item'];
        $user_id = (int) privileged_user_id();

        if (1 === (int) $step) {
            $bill_info = array(
                'bill_description' => $bill['bill_description'],
                'name_type'        => 'Feature item',
                'id_bill'          => $bill_id,
                'title'            => orderNumber($bill_id),
                'price'            => $bill['balance'],
            );
        }

        if (3 === (int) $step) {
            $hash_code = get_sha1_token(random_bytes(64) . "-{$user_id}");
            $total_amount = moneyToDecimal(priceToUsdMoney($bill['balance']));

            session()->update_payment($token, array('bills_type' => '3'));
            model('user_bills')->change_user_bills($bill_id, array('hash_code' => $hash_code));
        }

        return array_filter(
            compact(
                'payments',
                'bill_info',
                'hash_code',
                'total_amount',
                'content_payments'
            ),
            function ($entry) { return null !== $entry; }
        );
    }

    protected function handler_bill_highlight_item($bill, $step)
    {
        $token = $bill['token'];
        $bill_id = (int) $bill['id_bill'];
        $item_id = (int) $bill['id_item'];
        $user_id = (int) privileged_user_id();

        if (1 === (int) $step) {
            $bill_info = array(
                'bill_description' => $bill['bill_description'],
                'name_type'        => 'Highlight item',
                'id_bill'          => $bill_id,
                'title'            => orderNumber($bill_id),
                'price'            => $bill['balance'],
            );
        }

        if (3 === (int) $step) {
            $hash_code = get_sha1_token(random_bytes(64) . "-{$user_id}");
            $total_amount = moneyToDecimal(priceToUsdMoney($bill['balance']));

            session()->update_payment($token, array('bills_type' => '4'));
            model('user_bills')->change_user_bills($bill_id, array('hash_code' => $hash_code));
        }

        return array_filter(
            compact(
                'payments',
                'bill_info',
                'hash_code',
                'total_amount',
                'content_payments'
            ),
            function ($entry) { return null !== $entry; }
        );
    }

    protected function handler_bill_group($bill, $step)
    {
        $token = $bill['token'];
        $bill_id = (int) $bill['id_bill'];
        $item_id = (int) $bill['id_item'];
        $user_id = (int) privileged_user_id();

        if (1 === (int) $step) {
            $bill_info = array(
                'bill_description' => $bill['bill_description'],
                'name_type'        => 'Account upgrade',
                'id_bill'          => $bill_id,
                'title'            => orderNumber($bill_id),
                'price'            => $bill['balance'],
            );
        }

        if (3 === (int) $step) {
            $hash_code = get_sha1_token(random_bytes(64) . "-{$user_id}");
            $total_amount = moneyToDecimal(priceToUsdMoney($bill['balance']));

            session()->update_payment($token, array('bills_type' => '5'));
            model('user_bills')->change_user_bills($bill_id, array('hash_code' => $hash_code));
        }

        return array_filter(
            compact(
                'payments',
                'bill_info',
                'hash_code',
                'total_amount',
                'content_payments'
            ),
            function ($entry) { return null !== $entry; }
        );
    }

    protected function handler_bill_right($bill, $step)
    {
        $token = $bill['token'];
        $bill_id = (int) $bill['id_bill'];
        $item_id = (int) $bill['id_item'];
        $user_id = (int) privileged_user_id();

        if (1 === (int) $step) {
            $bill_info = array(
                'bill_description' => $bill['bill_description'],
                'name_type'        => 'Right package',
                'id_bill'          => $bill_id,
                'title'            => orderNumber($bill_id),
                'price'            => $bill['balance'],
            );
        }

        if (3 === (int) $step) {
            $hash_code = get_sha1_token(random_bytes(64) . "-{$user_id}");
            $total_amount = moneyToDecimal(priceToUsdMoney($bill['balance']));

            session()->update_payment($token, array('bills_type' => '6'));
            model('user_bills')->change_user_bills($bill_id, array('hash_code' => $hash_code));
        }

        return array_filter(
            compact(
                'payments',
                'bill_info',
                'hash_code',
                'total_amount',
                'content_payments'
            ),
            function ($entry) { return null !== $entry; }
        );
    }

    protected function show_pay_bill_popup($user_id, $bill_id)
    {
        //region Bill
        if (
            empty($bill_id)
            || !model('user_bills')->exist_user_bill($bill_id, $user_id, array('status' => "'init'"))
        ) {
            messageInModal(translate('systmess_bill_does_not_exist_message'), 'info');
        }

        $bill = model('user_bills')->get_user_bill($bill_id);
        //endregion Bill

        //region Session token
        $token = time();
        session()->clean_payments();
        if (!session()->isset_payment($token)) {
            session()->set_payment($token, array('bill' => $bill_id));
        }
        //endregion Session token

        //region Method information
        $handler_name = snakeCase("handler_bill_{$bill['name_type']}");
        $handler_payload = array();
        if (method_exists($this, $handler_name)) {
            $handler_payload = $this->{$handler_name}($bill, 1);
        }
        //endregion Method information

        //region Assign vars
        views()->assign(array_replace_recursive(
            array(
                'token'       => $token,
                'bill_id'     => $bill_id,
                'bill_info'   => $bill,
                'paypal_key'  => config('env.PAYPAL_CLIENT'),
                'stripe_key'  => config('env.STRIPE_PUBLIC_KEY'),
            ),
            $handler_payload
        ));
        //endregion Assign vars

        $this->view->display('new/payment/popup_pay_view');
    }

    protected function show_admin_payment_details_popup($bill_id)
    {
        //region Bill
        if (
            empty($bill_id)
            || empty($bill = model('user_bills')->get_user_bill($bill_id))
        ) {
            messageInModal('This bill does not exist.');
        }
        //endregion Bill

        //region Payment details
        $payment_methods = null;
        $payment_details = model('user_bills')->get_encrypt_data($bill_id, array('pay_detail'));
        $payment_details = !empty($payment_details['pay_detail']) ? unserialize($payment_details['pay_detail']) : null;
        if (
            !empty($method_id = (int) arrayGet($bill, 'pay_method', arrayGet($payment_details, 'pay_method.value')))
            && !empty($payment_methods = model('orders')->get_pay_method_with_i18n($method_id))
        ) {
            if (isset($payment_details['pay_method'])) {
                unset($payment_details['pay_method']);
            }

            $payment_details = array_merge(
                array(
                    'pay_method' => array(
                        'label' => 'Payment method',
                        'value' => cleanOutput(payment_method_i18n($payment_methods, 'method', 'en')),
                    ),
                ),
                $payment_details
            );
        }
        //endregion Payment details

        //region Assign vars
        views()->assign(array(
            'file'        => empty($bill['payment_form']) ? null : getUrlForGroup("/payments/download_payment_form/{$bill_id}"),
            'pay_detail'  => $payment_details,
            'pay_method'  => $payment_methods,
            'bill_detail' => $bill,
        ));
        //endregion Assign vars

        views('admin/order/popup_payment_detail_view');
    }

    private function _load_main()
    {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Orders_model', 'orders');
        $this->load->model('User_model', 'user');
    }

    private function bill_log_generator($bill_log, $id_bill)
    {
        $final_log = cleanInput('User paid: "' . $bill_log[$id_bill]['type_name'] . '" bill - ' . orderNumber($bill_log[$id_bill]['id_bill']));
        unset($bill_log[$id_bill]);
        $additional_log = array();

        if (!empty($bill_log)) {
            $final_log .= '<br/>Additional user paid:';
            foreach ($bill_log as $log) {
                $additional_log[] = cleanInput('"' . $log['type_name'] . '" bill - ' . orderNumber($log['id_bill']));
            }
            $final_log .= implode(',', $additional_log);
        }

        return $final_log;
    }

    /**
     * Returns the bills total amount.
     *
     * @param array $bills
     * @param Money $minimal_amount
     *
     * @return Money
     */
    private function get_bills_total_amount(array $bills, Money $minimal_amount)
    {
        $amount = Money::USD(0);
        $currencies = new ISOCurrencies();
        $parser = new DecimalMoneyParser($currencies);
        $formatter = new DecimalMoneyFormatter($currencies);

        try {
            foreach ($bills as $bill) {
                $amount = $amount->add($parser->parse($bill['balance'], 'USD'));
            }
        } catch (ParserException $exception) {
            jsonResponse('Some of your bills contain errors. Please contact administration.');
        }

        if ($amount->lessThan($minimal_amount)) {
            jsonResponse(translate('payments_minimal_charge_amount_error_message', array('{amount}' => $formatter->format($minimal_amount))));
        }

        return $amount;
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
        return $this->getFormattedFileuploadOptions(
            explode(',', config('fileupload_billing_document_formats', 'pdf,jpg,jpeg,png')),
            $total,
            $total >= $current ? $total - $current : 0,
            (int) config('fileupload_max_file_size', 10 * 1024 * 1024),
            config('fileupload_max_file_size_placeholder', '10MB'),
            array(),
            getUrlForGroup('payments/ajax_payment_upload_file'),
            getUrlForGroup('payments/ajax_payment_delete_file')
        );
    }

    private function update_activity_log(
        $bill_id,
        $bills,
        $method,
        $pay_amount,
        $pay_date
    ) {
        $items = array_map(
            function ($bill) {
                return array(
                    'id'     => $bill['id_item'],
                    'amount' => array(
                        'value'         => $bill['balance'],
                        'currency_code' => 'USD',
                    ),
                    'bill'   => array(
                        'id'          => $bill['id_bill'],
                        'type'        => $bill['id_type_bill'],
                        'description' => $bill['bill_description'],
                    )
                );
            },
            $bills
        );
        $context = array_merge(
            array(
                'payment' => array(
                    'items'      => $items,
                    'method'     => array('id' => $method),
                    'amount'     => array(
                        'value'         => moneyToDecimal($pay_amount),
                        'currency_code' => $pay_amount->getCurrency()->getCode(),
                    ),
                    'created_at' => $pay_date,
                ),
            ),
            get_user_activity_context()
        );

        $this->activity_logger->setResource($bill_id);
        $this->activity_logger->setResourceType(BILLING);
        $this->activity_logger->setOperationType(BILLING_PAYMENT);
        $this->activity_logger->info(model('activity_log_messages')->get_message(BILLING, BILLING_PAYMENT), $context);
    }
}
