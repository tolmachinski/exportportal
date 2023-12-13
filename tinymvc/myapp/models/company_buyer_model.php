<?php

use App\Common\Database\BaseModel;

/**
 * @deprecated in favor of \Buyer_Companies_Model
 */
class Company_Buyer_Model extends BaseModel
{
	var $obj;

	private $company_buyer_table = "company_buyer";
	private $company_buyer_table_alias = "BUYERS_COMPANIES";
	private $company_buyer_table_peimary_key = "id";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	/**
	 * Returns the buyers companies table name.
	 *
	 * @return string
	 */
	public function get_company_table(): string
	{
		return $this->company_buyer_table;
	}

	/**
	 * Returns the buyers companies table alias.
	 *
	 * @return string
	 */
	public function get_company_table_alias(): string
	{
		return $this->company_buyer_table_alias;
	}

	/**
	 * Returns the buyers companies table primary key.
	 *
	 * @return string
	 */
	public function get_company_table_primary_key(): string
	{
		return $this->company_buyer_table_peimary_key;
	}

	function set_company($company_info) {
		$response = $this->db->insert($this->company_buyer_table, $company_info);

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

		$crmModel->create_or_update_record($company_info['id_user']);

		return $response;
	}

	public function delete_company($id_user) {
		$this->db->where('id_user', $id_user);
		$response = $this->db->delete($this->company_buyer_table);

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

		$crmModel->create_or_update_record($id_user);

		return $response;
	}

    public function get_company($id_user = 0): ?array
    {
		$this->db->select("*");
		$this->db->from("{$this->company_buyer_table}");
		$this->db->where("id = ?", $id_user);

		return $this->db->get_one();
    }

    /**
     * Find companies for the list of users.
     */
    public function find_users_companies(array $users): array
    {
        if (empty($users)) {
            return [];
        }

        $this->db->select("*");
        $this->db->from("{$this->company_buyer_table}");
        $this->db->where_raw("id_user IN (" . implode(',', array_fill(0, count($users), '?')) . ")", $users);

        return $this->db->query_all();
    }

	function get_company_by_user($id_user = 0) {
		$this->db->select("*");
		$this->db->from("{$this->company_buyer_table}");
		$this->db->where("id_user = ?", $id_user);

		return $this->db->get_one();
	}

	function get_company_by_users($id_users = array()) {
		$this->db->select("*");
		$this->db->from("{$this->company_buyer_table}");
		$this->db->in("id_user", $id_users);

		return $this->db->query_all();
	}

	function count_company_by_user($id_user = 0) {
		$this->db->select("COUNT(*) as counter");
		$this->db->from("{$this->company_buyer_table}");
		$this->db->where("id_user = ?", $id_user);
		$rez = $this->db->get_one();

		return (int) $rez['counter'];
	}

	function update_company($id_user = 0, $data = array()) {
		if(empty($data)){
			return FALSE;
		}

		$this->db->where('id_user', $id_user);
		$response = $this->db->update($this->company_buyer_table, $data);

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

		$crmModel->create_or_update_record($id_user);

		return $response;
    }

    public function update_company_business_number(int $company_id, ?string $business_number = null): bool
    {
        $this->db->where('id_company', $company_id);

        return $this->db->update($this->company_base_table, array('business_number' => $business_number));
    }
}
