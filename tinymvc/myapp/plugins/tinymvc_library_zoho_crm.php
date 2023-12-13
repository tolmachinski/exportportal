<?php

/**
 * Library Zoho_crm
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 *
 */

use App\ZohoCRM\Record;
use App\ZohoCRM\Constants;
use zcrmsdk\crm\exception\ZCRMException;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring code style, old models, optimizations
 */
class TinyMVC_Library_Zoho_crm
{
    /**
     * @var Record $crm
     */
    private $crm = null;

    public function __construct()
    {
        $this->crm = new Record(
            config('env.ZOHO_CLIENT_ID'),
            config('env.ZOHO_CLIENT_SECRET'),
            config('env.ZOHO_REDIRECT_URI'),
            config('env.ZOHO_USER_EMAIL'),
            null,
            config('env.ZOHO_AUTH_CACHE_PATH')
        );
    }

    public function get_users_data_to_export($users_ids, $action = 'export')
    {
        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $users = $userModel->get_users_data_for_crm($users_ids);
        if (empty($users)) {
            return array();
        }

        $users_group_by_group_type = arrayByKey($users, 'group_type', true);

        $buyers_ids = isset($users_group_by_group_type['Buyer']) ? array_column($users_group_by_group_type['Buyer'], 'idu') : array();
        if ( ! empty($buyers_ids)) {
            $buyers_companies_raw = model('company_buyer')->get_company_by_users($buyers_ids);
            $buyers_companies = empty($buyers_companies_raw) ? array() : array_column($buyers_companies_raw, null, 'id_user');
        }

        $sellers_ids = isset($users_group_by_group_type['Seller']) ? array_column($users_group_by_group_type['Seller'], 'idu') : array();
        if ( ! empty($sellers_ids)) {
            $sellers_companies_raw = model('company')->get_companies_main_info(array('id_users' => $sellers_ids));
            $sellers_companies = empty($sellers_companies_raw) ? array() : array_column($sellers_companies_raw, null, 'id_user');

            $sellers_items_statistics_raw = model(Items_Model::class)->get_items_statistics_for_crm($sellers_ids);
            $sellers_items_statistics = arrayByKey($sellers_items_statistics_raw, 'id_seller', true);
        }

        $shippers_ids = isset($users_group_by_group_type['Shipper']) ? array_column($users_group_by_group_type['Shipper'], 'idu') : array();
        if ( ! empty($shippers_ids)) {
            $shippers_companies_raw = model('shippers')->get_shippers_by_users($shippers_ids);
            $shippers_companies = empty($shippers_companies_raw) ? array() : array_column($shippers_companies_raw, null, 'id_user');
        }

        foreach ($users as $key => &$user) {
            $profile_completion = 20;
            $profile_completion_detail = array();

            $profile_options = model('complete_profile')->get_user_profile_options($user['idu']);

            if ( ! empty($profile_options)) {
                foreach ($profile_options as $profile_option) {
                    $profile_completion_detail[] = $profile_option['option_name'] . ' : ' . ($profile_option['option_completed'] ? 'Completed' : 'Not completed');

                    if ($profile_option['option_completed'] == 1) {
                        $profile_completion += (int) $profile_option['option_percent'];
                    }
                }
            }

            $user['profile_completion'] = $profile_completion;
            $user['profile_completion_detail'] = empty($profile_completion_detail) ? array() : implode("\n", $profile_completion_detail);
            $user['is_certified'] = is_certified($user['user_group']);

            switch ($user['group_type']) {
                case 'Buyer':
                    if (isset($buyers_companies[$user['idu']])) {
                        $user['original_company_name'] = $buyers_companies[$user['idu']]['company_legal_name'];
                        $user['displayed_company_name'] = $buyers_companies[$user['idu']]['company_name'];
                    }

                    /** @var \Buyer_Item_Categories_Stats_Model $buyerStats */
                    $buyerStats = model(\Buyer_Item_Categories_Stats_Model::class);

                    $user_industries = $buyerStats->getUserRelationIndustries((int) $user['idu']);

                break;
                case 'Seller':
                    if (isset($sellers_companies[$user['idu']])) {
                        $user['original_company_name'] = $sellers_companies[$user['idu']]['legal_name_company'];
                        $user['displayed_company_name'] = $sellers_companies[$user['idu']]['name_company'];

                        $user['company_annual_revenue'] = $sellers_companies[$user['idu']]['revenue_company'];
                        $user['number_of_employees'] = $sellers_companies[$user['idu']]['employees_company'];

                        $user_industries = model(Company_Model::class)->get_relation_industry_by_company_id((int) $sellers_companies[$user['idu']]['id_company'], true);
                    }

                    if (isset($sellers_items_statistics[$user['idu']])) {
                        $not_visible_items = $blocked_items = $draft_items = $active_items = 0;
                        $seller_items_statistics = $sellers_items_statistics[$user['idu']];

                        foreach ($seller_items_statistics as $items_combination) {
                            if ($items_combination['draft']) {
                                $draft_items += $items_combination['count_items'];
                            }

                            if (!$items_combination['visible']) {
                                $not_visible_items += $items_combination['count_items'];
                            }

                            if ($items_combination['blocked']) {
                                $blocked_items += $items_combination['count_items'];
                            }

                            if ($items_combination['active']) {
                                $active_items += $items_combination['count_items'];
                            }
                        }

                        $user['draft_items'] = $draft_items;
                        $user['not_visible_items'] = $not_visible_items;
                        $user['blocked_items'] = $blocked_items;
                        $user['active_items'] = $active_items;
                    }

                break;
                case 'Shipper':
                    if (isset($shippers_companies[$user['idu']])) {
                        $user['original_company_name'] = $shippers_companies[$user['idu']]['legal_co_name'];
                        $user['displayed_company_name'] = $shippers_companies[$user['idu']]['co_name'];

                        $user_industries = model(Shippers_Model::class)->get_relation_industry_by_company_id((int) $shippers_companies[$user['idu']]['id'], true);
                    }

                break;
            }

            $user['industries'] = empty($user_industries) ? null : implode("\n", array_column($user_industries, 'name'));

            if ('export' == $action) {
                $user['lead_source'] = 'ExportPortal API';
                $user['registration_status'] = 'Registered';
            }

            if (!empty($user['timezone'])) {
                try {
                    $user['utcOffset'] = (new DateTime())->setTimezone(new DateTimeZone($user['timezone']))->format('Z') / 3600;
                } catch (\Throwable $th) {
                    //do nothing
                }
            }
        }

        return $users;
    }

    public function getContactsByUserEmail(string $email)
    {
        return $this->crm->get_records_by_email($email, Constants::MODULE_CONTACTS);
    }

    public function getLeadsByUserEmail(string $email)
    {
        return $this->crm->get_records_by_email($email, Constants::MODULE_LEADS);
    }

    public function updateContacts(array $contacts)
    {
        return $this->crm->update_records($contacts, Constants::MODULE_CONTACTS);
    }

    public function updateLeads(array $leads)
    {
        return $this->crm->update_records($leads, Constants::MODULE_LEADS);
    }

    public function export_user_to_contact($user)
    {
        $response = $this->crm->createBulk(array($user), Constants::MODULE_CONTACTS);

        return array_shift($response->getEntityResponses());
    }

    public function export_users_to_contacts($users_ids = array())
    {
        if (empty($users = $this->get_users_data_to_export($users_ids, (int) config('env.ZOHO_EXPORT_BULK_LIMIT', 20)))) {
            return false;
        }

        $response = $this->crm->createBulk($users, Constants::MODULE_CONTACTS);

        return $response->getEntityResponses();
    }

    public function update_contacts_by_users_ids($users_ids = array())
    {
        $users_raw = $this->get_users_data_to_export($users_ids, 'update');
        $users = array_column($users_raw, null, 'zoho_id_record');

        $response = $this->crm->update_records($users, Constants::MODULE_CONTACTS);

        return $response->getEntityResponses();
    }

    public function convert_lead_to_contact_by_email($user_email)
    {
        $records = $this->crm->get_records_by_email($user_email, Constants::MODULE_LEADS);

        if (empty($records)) {
            return array('status' => 'error', 'message' => 'Lead with email: ' . $user_email . ' doesn\'t found', 'code' => 'Custom');
        }

        return $this->crm->convert_lead($records[0]->getEntityId());
    }

    public function convert_lead_to_contact_by_lead_id($lead_id, bool $preserve_owner = false)
    {
        return $this->crm->convert_lead($lead_id, $preserve_owner);
    }

    public function create_leads($users)
    {
        $response = $this->crm->createBulk($users, Constants::MODULE_LEADS);

        return $response->getEntityResponses();
    }

    public function createLead(array $lead)
    {
        return $this->crm->create($lead, Constants::MODULE_LEADS);
    }

    /**
     * @deprecated
     * @see createLead
     */
    public function create_lead(array $user)
    {
        $response = $this->crm->createBulk(array($user), Constants::MODULE_LEADS);

        return array_shift($response->getEntityResponses());
    }

    public function remove_lead_by_email_if_exist(string $email)
    {
        if (empty($records = $this->crm->get_records_by_email($email, Constants::MODULE_LEADS))) {
            return true;
        }

        $response = $this->crm->delete_record_by_id_entity((int) $records[0]->getEntityId(), Constants::MODULE_LEADS);

        return 'success' === $response->getStatus();
    }

    public function remove_contact_by_email_if_exist(string $email)
    {
        if (empty($records = $this->crm->get_records_by_email($email, Constants::MODULE_CONTACTS))) {
            return true;
        }

        $response = $this->crm->delete_record_by_id_entity((int) $records[0]->getEntityId(), Constants::MODULE_CONTACTS);

        return 'success' === $response->getStatus();
    }

    /**
     *
     * @param int $contactId
     *
     * @return bool
     */
    public function remove_contact(int $contactId): bool
    {
        if (empty($contactId)) {
            return false;
        }

        try {
            $response = $this->crm->delete_record_by_id_entity($contactId, Constants::MODULE_CONTACTS);
        } catch (ZCRMException $e) {
            return false;
        }

        return 'success' === $response->getStatus();
    }

    public function upload_photo(){
        //return $this->crm->uploadPhoto(publicPath('img/users/1402/5e947eb3cc95b.jpg'), '4142953000008785008');
    }
}

/* End of file tinymvc_library_zoho_crm.php */
/* Location: /tinymvc/myapp/plugins/tinymvc_library_zoho_crm.php */
