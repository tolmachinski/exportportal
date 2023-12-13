<?php

use App\Common\Buttons\ChatButton;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Saved_Controller extends TinyMVC_Controller
{
    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        $op = $this->uri->segment(3);

        switch ($op) {
            case 'saved':
                $this->load->model('Items_Model', 'items');
                $this->load->model('Contact_User_Model', 'contact');
                $this->load->model('Save_Search_Model', 'saved_search');
                $this->load->model('Shippers_Model', 'shippers');
                $id_user = id_session();

                //region SELLERS
                $id_companies = model(Company_Model::class)->getSavedCompanies($id_user);
                $id_companies = array_filter(array_map('intval', explode(',', $id_companies)));
                if (!empty($id_companies)) {
                    $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);
                    $elasticsearchCompanyModel->get_companies([
                        'list_company_id' => implode(',', $id_companies),
                        'per_p'           => 0,
                    ]);

                    $data['counter_company'] = $elasticsearchCompanyModel->count;
                }
                //endregion SELLERS

                //region B2B PARTNERS
                $id_partners = model(B2b_Model::class)->get_partners_saved(my_company_id());
                $id_partners = array_filter(array_map('intval', explode(',', $id_partners)));
                if (!empty($id_partners)) {
                    $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);
                    $elasticsearchCompanyModel->get_companies([
                        'list_company_id' => implode(',', $id_partners),
                        'per_p'           => 0,
                    ]);
                    $data['counter_b2b'] = $elasticsearchCompanyModel->count;
                }
                //endregion B2B PARTNERS

                $data['counter_product'] = $this->items->getSavedCounter($id_user);
                $data['counter_contact'] = $this->contact->get_count_contacts(['id_user' => $id_user]);
                $data['counter_search'] = $this->saved_search->get_count_saved_search($id_user);
                $data['counter_shippers'] = $this->shippers->get_saved_counter($id_user);

                $data['curr_page'] = 1;
                $data['per_page'] = 8;

                $params = [
                    'per_p'    => &$data['per_page'],
                    'from'     => ($data['curr_page'] - 1) * $data['per_page'],
                    'id_user'  => $id_user,
                    'order_by' => 'u.fname',
                ];
                $data['counter'] = $this->contact->get_count_contacts($params);
                $contacts = $this->contact->get_contacts($params);

                $data['contacts'] = [];
                if (!empty($contacts)) {
                    $data['contacts'] = array_map(
                        function ($contactsItem) {
                            $chatBtn = new ChatButton(['recipient' => $contactsItem['idu'], 'recipientStatus' => $contactsItem['status']]);
                            $contactsItem['btnChat'] = $chatBtn->button();

                            return $contactsItem;
                        },
                        $contacts
                    );
                }

                $this->view->assign($data);
                $this->view->display('new/nav_header/saved/saved2_view');

            break;
        }
    }
}
