<?php

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\DecimalMoneyParser;
use App\Common\Exceptions\NotFoundException;
use App\Services\SampleServiceInterface;
use function GuzzleHttp\json_decode;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [02.12.2021]
 * library refactoring code style
 */
class TinyMVC_Library_Make_Pdf
{
    /**
     * The application controller.
     *
     * @var \TinyMVC_Controller
     */
    private $app;

    /**
     * The application configurations.
     *
     * @var array
     */
    private $app_config;

    /**
     * The PDF library.
     *
     * @var \TinyMVC_Library_mpdf
     */
    private $pdf;

    public function __construct()
    {
        $this->app = app();
        $this->pdf = library('mpdf', 'mpdf');
        $this->app_config = config(null, array());
    }

    /**
     * Makes PDF builder for bill invoices.
     *
     * @param array      $user_info
     * @param array      $bill_info
     * @param null|array $company_info
     *
     * @return \Mpdf\Mpdf
     */
    public function make_bill_invoice(array $user_info, array $bill_info, array $company_info = null)
    {
        $mpdf = $this->pdf->new_pdf();
        $mpdf->defaultfooterline = 0;

        $currencies = new ISOCurrencies();
        $money_parser = new DecimalMoneyParser($currencies);
        $money_formatter = new DecimalMoneyFormatter($currencies);

        $bill_info['pay_amount'] = $pay_amount = $money_parser->parse($bill_info['balance'], 'USD');
        if (!empty($bill_info['auxiliary'])) {
            foreach ($bill_info['auxiliary'] as &$auxiliary_bill) {
                $auxiliary_bill['pay_amount'] = $money_parser->parse($auxiliary_bill['balance'], 'USD');
            }
            $pay_amount = $pay_amount->add(...array_column($bill_info['auxiliary'], 'pay_amount'));
        }

        $mpdf->WriteHTML(views()->fetch('new/pdf_templates/bill_invoice_view', array_merge(array(
            'bills'                => array_merge(array($bill_info), $bill_info['auxiliary']),
            'amount'               => $pay_amount,
            'ep_address'           => dataGet($this->app_config, 'ep_address'),
            'ep_phone_number'      => dataGet($this->app_config, 'ep_phone_number'),
            'email_contact_us'     => dataGet($this->app_config, 'email_contact_us'),
            'ep_phone_whatsapp'    => dataGet($this->app_config, 'ep_phone_whatsapp'),
            'ep_phone_number_free' => dataGet($this->app_config, 'ep_phone_number_free'),
        ), compact('user_info', 'bill_info', 'company_info'))));
        $mpdf->setFooter(
            '<table width="100%" style="border:0;">
                <tr>
                    <td style="text-align: center;">&copy; Export Portal Team</td>
                </tr>
            </table>'
        );

        return $mpdf;
    }

    public function order_contract($id_order = 0)
    {
        $data['order'] = $this->_get_order($id_order);
        $data['order']['purchase_order'] = !empty($data['order']['purchase_order']) ? json_decode($data['order']['purchase_order'], true) : array();
        $data['order']['shipping_quote_details'] = !empty($data['order']['shipping_quote_details']) ? json_decode($data['order']['shipping_quote_details'], true) : array();
        $data['order']['shipping_insurance_details'] = !empty($data['order']['shipping_insurance_details']) ? json_decode($data['order']['shipping_insurance_details'], true) : array();

        $this->app->load->model('Company_Buyer_Model', 'company_buyer');
        $data['buyer_info'] = $this->_get_user((int) $data['order']['id_buyer'], "users.idu, IF(users.legal_name IS NULL or users.legal_name = '', CONCAT(users.fname, ' ', users.lname), users.legal_name) as buyer_name, users.fax, users.fax_code, users.phone, users.phone_code, users.email, users.zip as buyer_zip, users.city as buyer_city, users.address as buyer_address");
        $buyer_location = $this->_get_location((int) $data['buyer_info']['buyer_city']);
        $buyer_location[] = $data['buyer_info']['buyer_zip'];
        $buyer_location[] = $data['buyer_info']['buyer_address'];
        $data['buyer_info']['buyer_location'] = implode(', ', array_filter($buyer_location));
        $data['company_buyer_info'] = $this->app->company_buyer->get_company_by_user((int) $data['order']['id_buyer']);

        // GET SELLER COMPANY DETAIL
        $data['seller_info'] = $this->_get_user_company(
            (int) $data['order']['id_seller'],
            'cb.name_company,
            cb.index_name,
            cb.id_company,
            cb.type_company,
            cb.legal_name_company,
            cb.phone_code_company,
            cb.fax_code_company,
            cb.fax_company,
            cb.phone_company,
            cb.id_city,
            cb.zip_company,
            cb.address_company,
            u.idu,
            u.fname,
            u.lname,
            u.legal_name,
            u.email',
            true);
        $company_location = $this->_get_location((int) $data['seller_info']['id_city']);
        $company_location[] = $data['seller_info']['zip_company'];
        $company_location[] = $data['seller_info']['address_company'];

        $data['seller_info']['company_location'] = implode(', ', array_filter($company_location));

        $data['ship_from_exploded'] = $this->_explode_ship_address_full($data['order']['ship_from']);
        $data['ship_to_exploded'] = $this->_explode_ship_address_full($data['order']['ship_to']);

        // PREPARE ORDER & INVOICE DETAILS
        $data['invoice_info'] = $data['order']['purchase_order']['invoice'];
        $data['products'] = $data['order']['purchase_order']['invoice']['products'];

        // GET SHIPPING TYPE DETAILS
        $data['shipper_info'] = $this->_get_shipper((int) $data['order']['id_shipper'], $data['order']['shipper_type']);

        $spellout_final_amount = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $data['spellout_final_amount'] = $spellout_final_amount->format($data['order']['final_price']);

        $this->app->view->assign($data);
        $file_content = $this->app->view->fetch('new/order/contract/pdf_view');

        $this->app->mpdf->config(array(
            'dpi' => 300,
        ));
        $mpdf = $this->app->mpdf->new_pdf();
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultfooterline = 0;
        $mpdf->WriteHTML($file_content);

        return $mpdf;
    }

    public function sample_order_contract(int $id_order)
    {
        $data['order'] = model(Sample_Orders_Model::class)->findOneBy(array(
            'conditions' => array(
                'sample' => $id_order
            ),
            'with'  => array(
                'buyer',
            )
        ));

        if (empty($data['order'])) {
            throw new NotFoundException("The sample order with ID '{$id_order}' is not found.", SampleServiceInterface::ORDER_NOT_FOUND_ERROR);
        }

        $order_departure_address_parts = $this->_get_location($data['order']['ship_from_city']);
        $data['order']['purchase_order'] = $data['order']['purchase_order'] ?? [];
        $data['order']['departure_location'] = implode(', ', array_filter(array(
            $data['order']['ship_from_address'] ?? null,
            $data['order']['ship_from_zip'] ?? null,
            $order_departure_address_parts['city'] ?? null,
            $order_departure_address_parts['state'] ?? null,
            $order_departure_address_parts['country'] ?? null,
        )));

        $order_destination_address_parts = $this->_get_location($data['order']['ship_to_city']);
        $data['order']['destination_location'] = implode(', ', array_filter(array(
            $data['order']['ship_to_address'] ?? null,
            $data['order']['ship_to_zip'] ?? null,
            $order_destination_address_parts['city'] ?? null,
            $order_destination_address_parts['state'] ?? null,
            $order_destination_address_parts['country'] ?? null,
        )));

        $data['invoice'] = $data['order']['purchase_order']['invoice'] ?? array();
        $data['contract'] = $data['order']['purchase_order']['contract'] ?? array();
        $data['products'] = $data['order']['purchased_products'] ?? [];

        //region Buyer' data
        $data['buyer'] = $data['order']['buyer'];
        $data['buyer']['full_name'] = !empty($data['buyer']['legal_name']) ? $data['buyer']['legal_name'] : $data['buyer']['fname'] . ' ' . $data['buyer']['lname'];
        $buyer_address_parts = $this->_get_location($data['buyer']['city']);

        $data['buyer']['location'] = implode(', ', array_filter(array(
            $data['buyer']['address'] ?? null,
            $data['buyer']['zip'] ?? null,
            $buyer_address_parts['city'] ?? null,
            $buyer_address_parts['state'] ?? null,
            $buyer_address_parts['country'] ?? null,
        )));

        $data['buyer']['country_name'] = $buyer_address_parts['country'];

        $data['buyer_company'] = model(Company_Buyer_Model::class)->get_company_by_user((int) $data['buyer']['idu']);
        if (!empty($data['buyer_company'])) {
            $buyer_company_address_parts = $this->_get_location((int) $data['buyer_company']['company_id_city']);

            $data['buyer_company']['location'] = implode(', ', array_filter(array(
                $data['buyer_company']['company_address'] ?? null,
                $data['buyer_company']['company_zip'] ?? null,
                $buyer_company_address_parts['city'] ?? null,
                $buyer_company_address_parts['state'] ?? null,
                $buyer_company_address_parts['country'] ?? null,
            )));

            $data['buyer_company']['country_name'] = $buyer_company_address_parts['country'];
        }
        //endregion Buyer' data

        //region seller' data
        $data['seller'] = model(Company_Model::class)->get_seller_base_company((int) $data['order']['id_seller'], 'cb.name_company, cb.index_name, cb.id_company, cb.type_company, cb.legal_name_company, cb.phone_code_company, cb.fax_code_company, cb.fax_company, cb.phone_company, cb.id_city, cb.zip_company, cb.address_company, u.idu, u.fname, u.lname, u.legal_name, u.email', true);
        $seller_address_parts = $this->_get_location($data['seller']['id_city']);

        $data['seller']['location'] = implode(', ', array_filter(array(
            $data['seller']['address_company'] ?? null,
            $data['seller']['zip_company'] ?? null,
            $seller_address_parts['city'] ?? null,
            $seller_address_parts['state'] ?? null,
            $seller_address_parts['country'] ?? null,
        )));

        $data['seller']['country_name'] = $seller_address_parts['country'];
        //endregion seller' data

        $data['ship_from_exploded'] = $this->_explode_ship_address_full($data['order']['ship_from']);
        $data['ship_to_exploded'] = $this->_explode_ship_address_full($data['order']['ship_to']);

        $data['shipper'] = $this->_get_shipper((int) $data['order']['id_shipper'], 'ishipper');

        views()->assign($data);
        $file_content = $this->app->view->fetch('new/sample_orders/contract/pdf_view');

        $this->app->mpdf->config(array(
            'dpi' => 300,
        ));
        $mpdf = $this->app->mpdf->new_pdf();
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultfooterline = 0;
        $mpdf->WriteHTML($file_content);

        return $mpdf;
    }

    public function sample_order_invoice(int $id_order)
    {
        $data['order'] = model(Sample_Orders_Model::class)->findOneBy(array(
            'conditions' => array(
                'sample' => $id_order
            ),
            'with'  => array(
                'buyer',
            )
        ));

        if (empty($data['order'])) {
            throw new NotFoundException("The sample order with ID '{$id_order}' is not found.", SampleServiceInterface::ORDER_NOT_FOUND_ERROR);
        }

        $order_departure_address_parts = $this->_get_location($data['order']['ship_from_city']);
        $data['order']['departure_location'] = implode(', ', array_filter(array(
            $data['order']['ship_from_address'] ?? null,
            $data['order']['ship_from_zip'] ?? null,
            $order_departure_address_parts['city'] ?? null,
            $order_departure_address_parts['state'] ?? null,
            $order_departure_address_parts['country'] ?? null,
        )));

        $order_destination_address_parts = $this->_get_location($data['order']['ship_to_city']);
        $data['order']['destination_location'] = implode(', ', array_filter(array(
            $data['order']['ship_to_address'] ?? null,
            $data['order']['ship_to_zip'] ?? null,
            $order_destination_address_parts['city'] ?? null,
            $order_destination_address_parts['state'] ?? null,
            $order_destination_address_parts['country'] ?? null,
        )));

        $data['order']['purchase_order'] = $data['order']['purchase_order'] ?? [];
        $data['invoice'] = $data['order']['purchase_order']['invoice'] ?? array();

        //region Buyer' data
        $data['buyer'] = $data['order']['buyer'];
        $buyer_address_parts = $this->_get_location($data['buyer']['city']);

        $data['buyer']['location'] = implode(', ', array_filter(array(
            $data['buyer']['address'] ?? null,
            $data['buyer']['zip'] ?? null,
            $buyer_address_parts['city'] ?? null,
            $buyer_address_parts['state'] ?? null,
            $buyer_address_parts['country'] ?? null,
        )));

        $data['buyer_company'] = model(Company_Buyer_Model::class)->get_company_by_user((int) $data['buyer']['idu']);
        if (!empty($data['buyer_company'])) {
            $buyer_company_address_parts = $this->_get_location((int) $data['buyer_company']['company_id_city']);

            $data['buyer_company']['location'] = implode(', ', array_filter(array(
                $data['buyer_company']['company_address'] ?? null,
                $data['buyer_company']['company_zip'] ?? null,
                $buyer_company_address_parts['city'] ?? null,
                $buyer_company_address_parts['state'] ?? null,
                $buyer_company_address_parts['country'] ?? null,
            )));
        }
        //endregion Buyer' data

        //region seller' data
        $data['seller'] = model(Company_Model::class)->get_seller_base_company((int) $data['order']['id_seller'], 'cb.name_company, cb.legal_name_company, cb.phone_code_company, cb.fax_code_company, cb.fax_company, cb.phone_company, cb.id_city, cb.zip_company, cb.address_company, u.fname, u.lname, u.legal_name, u.email', true);
        $seller_address_parts = $this->_get_location($data['seller']['id_city']);

        $data['seller']['location'] = implode(', ', array_filter(array(
            $data['seller']['address_company'] ?? null,
            $data['seller']['zip_company'] ?? null,
            $seller_address_parts['city'] ?? null,
            $seller_address_parts['state'] ?? null,
            $seller_address_parts['country'] ?? null,
        )));
        //endregion seller' data

        $data['products'] = $data['order']['purchased_products'] ?? [];
        $data['shipper'] = $this->_get_shipper((int) $data['order']['id_shipper'], 'ishipper');
        views()->assign($data);

        $file_content = views()->fetch('new/sample_orders/invoice/pdf_view');
        library('mpdf')->config(array(
            'dpi' => 120,
        ));
        $mpdf = library('mpdf')->new_pdf();
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultfooterline = 0;
        $mpdf->WriteHTML($file_content);

        return $mpdf;
    }

    public function order_invoice($id_order = 0)
    {
        $data['order'] = $this->_get_order($id_order);
        $data['order']['purchase_order'] = !empty($data['order']['purchase_order']) ? json_decode($data['order']['purchase_order'], true) : array();

        $this->app->load->model('Company_Buyer_Model', 'company_buyer');
        $data['buyer_info'] = $this->_get_user((int) $data['order']['id_buyer'], "users.idu, IF(users.legal_name IS NULL or users.legal_name = '', CONCAT(users.fname, ' ', users.lname), users.legal_name) as buyer_name, users.fax, users.fax_code, users.phone, users.phone_code, users.email, users.zip as buyer_zip, users.city as buyer_city, users.address as buyer_address");
        $buyer_location = $this->_get_location((int) $data['buyer_info']['buyer_city']);
        $buyer_location[] = $data['buyer_info']['buyer_zip'];
        $buyer_location[] = $data['buyer_info']['buyer_address'];
        $data['buyer_info']['buyer_location'] = implode(', ', array_filter($buyer_location));
        $data['company_buyer_info'] = $this->app->company_buyer->get_company_by_user((int) $data['order']['id_buyer']);

        // GET SELLER COMPANY DETAIL
        $data['seller_info'] = $this->_get_user_company((int) $data['order']['id_seller'], 'cb.name_company, cb.legal_name_company, cb.phone_code_company, cb.fax_code_company, cb.fax_company, cb.phone_company, cb.id_city, cb.zip_company, cb.address_company, u.fname, u.lname, u.legal_name, u.email', true);
        $company_location = $this->_get_location((int) $data['seller_info']['id_city']);
        $company_location[] = $data['seller_info']['zip_company'];
        $company_location[] = $data['seller_info']['address_company'];
        $data['seller_info']['company_location'] = implode(', ', array_filter($company_location));

        $data['ship_from_exploded'] = $this->_explode_ship_address_full($data['order']['ship_from']);
        $data['ship_to_exploded'] = $this->_explode_ship_address_full($data['order']['ship_to']);

        // PREPARE ORDER & INVOICE DETAILS
        $data['invoice_info'] = $data['order']['purchase_order']['invoice'];
        $data['products'] = $data['order']['purchase_order']['invoice']['products'];

        // GET SHIPPING TYPE DETAILS
        $data['shipper_info'] = $this->_get_shipper((int) $data['order']['id_shipper'], $data['order']['shipper_type']);

        $this->app->view->assign($data);
        $file_content = $this->app->view->fetch('new/order/invoice/pdf_view');

        $this->app->mpdf->config(array(
            'dpi' => 120,
        ));
        $mpdf = $this->app->mpdf->new_pdf();
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultfooterline = 0;
        $mpdf->WriteHTML($file_content);

        return $mpdf;
    }

    private function _explode_ship_address_full($address = '')
    {
        $exploded_address = explode(',', $address);

        $return_address['country'] = $exploded_address['0'];
        unset($exploded_address['0']);
        $return_address['address'] = implode(',',$exploded_address);

        return $return_address;
    }

    private function _get_order($id_order = 0)
    {
        $this->app->load->model('Orders_Model', 'orders');

        return $this->app->orders->get_order($id_order);
    }

    private function _get_user($id_user = 0, $columns = '*')
    {
        $this->app->load->model('User_Model', 'user');

        return $this->app->user->getSimpleUser($id_user, $columns);
    }

    private function _get_user_company($id_user = 0, $columns = '*')
    {
        $this->app->load->model('Company_Model', 'company');

        return $this->app->company->get_seller_base_company($id_user, $columns, true);
    }

    private function _get_location($id_city = 0)
    {
        $this->app->load->model('Country_Model', 'country');

        return $this->app->country->get_country_state_city($id_city);
    }

    private function _get_shipper($id_shipper = 0, $type = '')
    {
        $shipper_info = array();
        switch ($type) {
            case 'ep_shipper':
                $this->app->load->model('Shippers_Model', 'shippers');
                $shipper_info = $this->app->shippers->get_shipper_by_user($id_shipper);
                $shipper_location = $this->_get_location((int) $shipper_info['id_city']);
                $shipper_location[] = $shipper_info['zip'];
                $shipper_location[] = $shipper_info['address'];
                $shipper_info['shipper_location'] = implode(', ', array_filter($shipper_location));

            break;
            case 'ishipper':
                $this->app->load->model('Ishippers_Model', 'ishippers');
                $shipper_info = $this->app->ishippers->get_shipper($id_shipper);
                if (empty($shipper_info)) {
                    return $shipper_info;
                }

                $shipper_info = array(
                    'co_name'       => $shipper_info['shipper_name'],
                    'legal_co_name' => $shipper_info['shipper_original_name'],
                    'contacts'      => $shipper_info['shipper_contacts'],
                );

            break;
        }

        return $shipper_info;
    }
}
