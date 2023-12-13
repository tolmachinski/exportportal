<?php

use App\Common\Database\BaseModel;
use App\Common\Exceptions\QueryException;
use App\Plugins\EPDocs\NotFoundException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * company_model.php
 *
 * company model
 *
 * @author Litra Andrei
 *
 * @deprecated in favor of \Seller_Companies_Model
 */
class Company_Model extends BaseModel
{
	var $obj;

	private $company_base_table = "company_base";
	private $company_base_table_alias = "COMPANIES";
	private $company_base_primary_key = "id_company";
	private $company_type_table = "company_type";
	private $company_industries_table = "company_industries";
	/** @deprecated */
	private $company_categories_table = "company_categories_seo";
	private $relation_category_table = "company_relation_category";
	private $relation_industry_table = "company_relation_industry";
	private $company_attributes_table = "company_attributes";
	private $category_table = "item_category";
	private $company_feedbacks_table = "company_feedbacks";
	private $user_saved_companies = "user_saved_companies";
	private $country_table = "port_country";
	private $state_table = "states";
	private $city_table = "zips";
	private $branches_table = "company_branches";
	private $services_contacts_table = "company_services_contacts";
	private $users_table = "users";
	private $user_groups_table = "user_groups";

	public $path_to_photos = "public/img/seller_pictures";
    public $company_user_rel = "company_users";

    private $destibutorTypeId = 7;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	/**
	 * Returns the company table name.
	 *
	 * @return string
	 */
	public function get_company_table(): string
	{
		return $this->company_base_table;
	}

	/**
	 * Returns the company table alias.
	 *
	 * @return string
	 */
	public function get_company_table_alias(): string
	{
		return $this->company_base_table_alias;
	}

	/**
	 * Returns the company table primary key.
	 *
	 * @return string
	 */
	public function get_company_table_primary_key(): string
	{
		return $this->company_base_primary_key;
	}

    /**
     * Return the company relation industry table name
     *
     * @return string
     */
    public function getRelationIndustryTable(): string
    {
        return $this->relation_industry_table;
    }

	function set_company($company_info) {
		$this->db->insert($this->company_base_table, $company_info);
		$inserted_id = $this->db->last_insert_id();

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

		$crmModel->create_or_update_record($company_info['id_user']);

		return $inserted_id;
	}

	public function delete_company($id_company, $id_seller) {
		$this->db->where('id_company', $id_company);
		$response = $this->db->delete($this->company_base_table);

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

		$crmModel->create_or_update_record($id_seller);

		return $response;
	}

	function set_company_user_rel($data) {
		return $this->db->insert_batch($this->company_user_rel, $data);
	}

	function clear_company_user_rel($id_user) {
		$this->db->where('id_user', $id_user);
		return $this->db->delete($this->company_user_rel);
	}

	function clear_company_users_rel($id_company) {
		$this->db->where('id_company', $id_company);
		return $this->db->delete($this->company_user_rel);
	}

	function get_companies_simple($conditions = array()) {
		$order_by = "cb.registered_company ASC";
		$where = array();
		$params = array();
		$type_company = 'company';

		extract($conditions);

		if (isset($sort_by)) {
			$multi_order_by = array();
			foreach ($sort_by as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			if (!empty($multi_order_by)) {
				$order_by = implode(',', $multi_order_by);
			}
		}

		if (isset($logo_company_exist)) {
			$where[] = " cb.logo_company != '' ";
		}

		if (isset($users_list)) {
            $users_list = getArrayFromString($users_list);
			$where[] = " cb.id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
            array_push($params, ...$users_list);
		}

		if (isset($list_company_id)) {
            $list_company_id = getArrayFromString($list_company_id);
			$where[] = " cb.id_company IN (" . implode(',', array_fill(0, count($list_company_id), '?')) . ")";
            array_push($params, ...$list_company_id);
		}

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(isset($no_accreditation)){
            $where[] = " cb.accreditation IN (0,2) ";
        }

		if (isset($type)) {
			$where[] = " cb.id_type = ? ";
			$params[] = $type;
		}

		if ($type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($country)) {
			$where[] = " cb.id_country = ? ";
			$params[] = $country;
		}

		if (isset($state)) {
			$where[] = " cb.id_state = ? ";
			$params[] = $state;
		}

		if (isset($city)) {
			$where[] = " cb.id_city = ? ";
			$params[] = $city;
		}

		if (isset($seller)) {
			$where[] = " cb.id_user=?";
			$params[] = $seller;
		}

		if (isset($user_status)) {
			$where[] = " u.status = ? ";
            $params[] = $user_status;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($blocked)) {
			if($blocked == 0){
				$where[] = " cb.blocked = 0 ";
			} else{
				$where[] = " cb.blocked > 0 ";
			}
		}

		if(isset($fake_user)){
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
        }

		if(isset($is_verified_user)){
			$where[] = " u.is_verified = ? ";
			$params[] = $is_verified_user;
		}

		$sql = "SELECT
					cb.*,
					pc.country,
					ug.gr_name as user_group_name, ug.gr_alias as user_group_alias, u.user_group, u.registration_date, CONCAT(u.fname, ' ', u.lname) as user_name, u.status,
					ct.name_type
				FROM $this->company_base_table cb
				INNER JOIN $this->users_table u ON cb.id_user = u.idu
				INNER JOIN $this->company_type_table ct ON cb.id_type=ct.id_type
				INNER JOIN $this->country_table pc ON cb.id_country = pc.id
                INNER JOIN $this->user_groups_table ug ON u.user_group = ug.idgroup";

		if (!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$sql .= " ORDER BY {$order_by}";

		if(isset($start)) {
			$sql .= " LIMIT {$start}";

			if(isset($limit)){
				$sql .= " , {$limit}";
			}
		}

		return $this->db->query_all($sql, $params);
	}

	function get_companies($conditions) {
		$order_by = "cb.registered_company ASC";
		$where = array();
		$params = array();
		$type_company = 'company';

        $administration_fields = "";
        $administration_tables = "";

		extract($conditions);

		switch ($sort_by) {
			case 'title_asc': $order_by = 'cb.name_company ASC';
			break;
			case 'title_desc': $order_by = 'cb.name_company DESC';
			break;
			case 'date_asc': $order_by = 'cb.registered_company ASC';
			break;
			case 'date_desc': $order_by = 'cb.registered_company DESC';
			break;
			case 'rating_asc': $order_by = 'cb.rating_company ASC';
			break;
			case 'rating_desc': $order_by = 'cb.rating_company DESC';
			break;
			case 'rand': $order_by = ' RAND()';
			break;
		}

		if (isset($users_list)) {
            $users_list = getArrayFromString($users_list);
			$where[] = " cb.id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
            array_push($params, ...$users_list);
		}

		$companies_list = array();
		if (isset($industry)) {
			$companies_list = array_merge($companies_list, $this->get_companies_list_by_industries($industry));
		}

		if (isset($category)) {
			$companies_list = array_merge($companies_list, $this->get_companies_list_by_categories($category));
		}

		if(!empty($companies_list)){
			$list_company_id = implode(',', $companies_list);
		}

		if (isset($list_company_id)) {
            $list_company_id = getArrayFromString($list_company_id);
			$where[] = " cb.id_company IN (" . implode(',', array_fill(0, count($list_company_id), '?')) . ")";
            array_push($params, ...$list_company_id);
		}

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(isset($no_accreditation)){
            $where[] = " cb.accreditation IN (0,2) ";
        }

		if (isset($type)) {
			$where[] = " cb.id_type = ? ";
			$params[] = $type;
		}

		if (isset($parent)) {
			$where[] = " cb.parent_company = ? ";
			$params[] = $parent;
		}

		if ($type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($country)) {
			$where[] = " cb.id_country = ? ";
			$params[] = $country;
		}

		if (isset($state)) {
			$where[] = " cb.id_state = ? ";
			$params[] = $state;
		}

		if (isset($city)) {
			$where[] = " cb.id_city = ? ";
			$params[] = $city;
		}

		if (isset($seller)) {
			$where[] = " cb.id_user=?";
			$params[] = $seller;
		}

		if (isset($user_status)) {
			$where[] = " u.status = ? ";
            $params[] = $user_status;
		}

		if (isset($added_start)) {
			$where[] = " DATE(registered_company)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(registered_company)<=?";
			$params[] = $added_finish;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($blocked)) {
			if($blocked == 0){
				$where[] = " cb.blocked = 0 ";
			} else{
				$where[] = " cb.blocked > 0 ";
			}
		}

		if (isset($fake_user)) {
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
		}

		if (isset($featured)) {
			$where[] = " cb.is_featured = ? ";
			$params[] = $featured;
		}

		if (isset($be_featured)) {
			$where[] = " cb.blocked = 0 ";
			$where[] = " cb.visible_company = 1 ";
			$where[] = " cb.type_company = ? ";
			$params[] = 'company';
			$where[] = " u.fake_user = 0 ";
			$where[] = " u.is_verified = 1 ";
		}

		if (isset($multiple_sort_by)) {
			foreach ($multiple_sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		$rel = "";
		if (isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by .= ", REL DESC ";
				$where[] = " MATCH (cb.name_company, cb.description_company) AGAINST (?) ";
				$params[] = $keywords;
				$rel = " , MATCH (cb.name_company, cb.description_company) AGAINST ( ? ) as REL";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (cb.name_company LIKE ? OR cb.description_company LIKE ?) ";
                array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
			}
		}

		if (isset($company_name)){
            $where[] = " cb.name_company LIKE ? ";
            $params[] = '%' . $company_name . '%';
		}

		if (isset($get_administration_info) && $get_administration_info) {
			$administration_fields = " ,ct.name_type,(CONCAT_WS(', ',z.city,st.state)) as city ";
			$administration_tables = " LEFT JOIN zips z ON z.id = cb.id_city
                                       LEFT JOIN states st ON st.id = cb.id_state";
		}

        if (isset($search_by_username_email)) {
            $where[] = " cb.id_user IN (SELECT idu FROM users WHERE fname LIKE ? OR lname LIKE ? OR email = ?) ";
            array_push($params, ...array_fill(0, 2, '%' . $search_by_username_email . '%'));
            $params[] = $search_by_username_email;
        }

        if (isset($search_by_item)) {
            $where[] = " (cb.id_user IN (SELECT id_seller FROM items WHERE title LIKE ?)) ";
            $params[] = '%' . $search_by_item . '%';
        }

		$sql = "SELECT
					cb.*,
					pc.country,
					ug.gr_name as user_group_name, u.registration_date, CONCAT(u.fname, ' ', u.lname) as user_name, u.status, u.fake_user, u.is_verified,
					ct.name_type
					$administration_fields
					$rel
				FROM $this->company_base_table cb
				INNER JOIN $this->users_table u ON cb.id_user = u.idu
				INNER JOIN $this->company_type_table ct ON cb.id_type=ct.id_type
				LEFT JOIN $this->country_table pc ON cb.id_country = pc.id
                LEFT JOIN $this->user_groups_table ug ON u.user_group = ug.idgroup
                $administration_tables";

		if (!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		$sql .= " GROUP BY cb.id_company";
		$sql .= " ORDER BY " . $order_by;

		if (isset($page)) {
			if (!isset($count)){
				$count = $this->count_companies($conditions);
			}

			$pages = ceil($count / $per_p);

			if ($page > $pages)
			$page = $pages;

			$start = ($page - 1) * $per_p;

			if ($start < 0)
				$start = 0;

			$sql .= " LIMIT " . $start;

			if(isset($limit))
				$per_p = $limit;

			if ($per_p > 0)
				$sql .= "," . $per_p;
		} elseif(isset($start)) {
			$sql .= " LIMIT " . $start;

			if(isset($limit))
				$sql .= " , " . $limit;
        }

		return $this->db->query_all($sql, $params);
	}

	function count_companies($conditions) {
		$where = array();
		$params = array();
		$type_company = 'company';

		extract($conditions);

		if (isset($users_list)) {
            $users_list = getArrayFromString($users_list);
			$where[] = " cb.id_user IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
            array_push($params, ...$users_list);
		}

		$companies_list = array();
		if (isset($industry)) {
			$companies_list = array_merge($companies_list, $this->get_companies_list_by_industries($industry));
		}

		if (isset($category)) {
			$companies_list = array_merge($companies_list, $this->get_companies_list_by_categories($category));
		}

		if(!empty($companies_list)){
			$list_company_id = implode(',', $companies_list);
		}

		if (isset($list_company_id)) {
            $list_company_id = getArrayFromString($list_company_id);
			$where[] = " cb.id_company IN (" . implode(',', array_fill(0, count($list_company_id), '?')) . ")";
            array_push($params, ...$list_company_id);
		}

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if (isset($type)) {
			$where[] = " cb.id_type = ? ";
			$params[] = $type;
		}

		if (isset($parent)) {
			$where[] = " cb.parent_company = ? ";
			$params[] = $parent;
		}

		if ($type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($country)) {
			$where[] = " cb.id_country = ? ";
			$params[] = $country;
		}

		if (isset($state)) {
			$where[] = " cb.id_state = ? ";
			$params[] = $state;
		}

		if (isset($city)) {
			$where[] = " cb.id_city = ? ";
			$params[] = $city;
		}

		if (isset($seller)) {
			$where[] = " cb.id_user=?";
			$params[] = $seller;
		}

		if (isset($user_status)) {
			$where[] = " u.status = ? ";
            $params[] = $user_status;
		}

		if (isset($added_start)) {
			$where[] = " DATE(registered_company)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(registered_company)<=?";
			$params[] = $added_finish;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($blocked)) {
			if($blocked == 0){
				$where[] = " cb.blocked = 0 ";
			} else{
				$where[] = " cb.blocked > 0 ";
			}
		}

		if (isset($fake_user)) {
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
		}

		if (isset($model_user)) {
			$where[] = " u.is_model = ? ";
			$params[] = $model_user;
		}

		if (isset($featured)) {
			$where[] = " cb.is_featured = ? ";
			$params[] = $featured;
		}

		if (isset($be_featured)) {
			$where[] = " cb.blocked = 0 ";
			$where[] = " cb.visible_company = 1 ";
			$where[] = " cb.type_company = ? ";
			$params[] = 'company';
			$where[] = " u.fake_user = 0 ";
			$where[] = " u.is_verified = 1 ";
		}

		if (isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$where[] = " MATCH (cb.name_company, cb.description_company) AGAINST (?) ";
				$params[] = $keywords;
			} else{
				$where[] = " (cb.name_company LIKE ? OR cb.description_company LIKE ?) ";
                array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
			}
		}

		if (isset($company_name)){
			$where[] = " cb.name_company LIKE ? ";
            $params[] = '%' . $company_name . '%';
        }

        if (isset($search_by_username_email)) {
            $where[] = " cb.id_user IN (SELECT idu FROM users WHERE fname LIKE ? OR lname LIKE ? OR email = ?) ";
            array_push($params, ...array_fill(0, 2, '%' . $search_by_username_email . '%'));
            $params[] = $search_by_username_email;
        }

        if (isset($search_by_item)) {
            $where[] = " (cb.id_user IN (SELECT id_seller FROM items WHERE title LIKE ?)) ";
            $params[] = '%' . $search_by_item . '%';
        }

		$sql = "SELECT count(DISTINCT cb.id_company ) as `counter`
				FROM $this->company_base_table cb
				INNER JOIN $this->users_table u ON cb.id_user = u.idu
				INNER JOIN $this->company_type_table ct ON cb.id_type=ct.id_type";

		if (!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

    function get_companies_main_info($conditions = array())
    {
        $type_company = "all";
        $order_by = 'registered_company ASC';
        $columns = "*";
        extract($conditions);

        $this->db->select($columns);
        $this->db->from($this->company_base_table);

        if (isset($companies_list)) {
            $this->db->in("id_company", $companies_list);
        }

        if (isset($type)) {
            $this->db->where("id_type = ? ", $type);
		}

        if ($type_company != "all") {
            $this->db->where("type_company = ? ", $type_company);
        }

        if (isset($country)) {
            $this->db->where("id_country = ? ", $country);
        }

        if (isset($parent)) {
            $this->db->where("parent_company = ? ", $parent);
        }

        if (isset($id_user)) {
            $this->db->where("id_user = ? ", $id_user);
		}

        if (isset($id_users)) {
            $this->db->in("id_user", $id_users);
        }

        if (isset($visibility)) {
            $this->db->where("visible_company = ? ", $visibility);
        }

        $this->db->orderby($order_by);

        $records = $this->db->query_all();

        return !empty($records) ? $records : array();
    }

	function get_user_companies_rel($conditions) {
		$where = array();
		$params = array();
		$type_company = 'all';
		extract($conditions);

		if (isset($companies_list)) {
            $companies_list = getArrayFromString($companies_list);
			$where[] = " id_company IN (" . implode(',', array_fill(0, count($companies_list), '?')) . ")";
            array_push($params, ...$companies_list);
		}

		if ($type_company != 'all') {
			$where[] = " company_type = ? ";
			$params[] = $type_company;
		}

		if (isset($id_user)) {
			$where[] = " id_user = ? ";
			$params[] = $id_user;
		}

		$sql = "SELECT *
				FROM company_users ";

		if (count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		return $this->db->query_all($sql, $params);
	}

	function get_user_companies($id_user) {
		$sql = "SELECT GROUP_CONCAT(id_company) as my_companies
					FROM " . $this->company_base_table . "
					WHERE id_user = ?";
		$res = $this->db->query_one($sql, array($id_user));
		return $res['my_companies'];
	}

	function get_company_services_contacts($conditions = array()) {
		$where = array();
		$params = array();
		$order_by = "";
		extract($conditions);
		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}
		if(isset($id_service)){
            $id_service = getArrayFromString($id_service);
			$where[] = " id_service IN (" . implode(',', array_fill(0, count($id_service), '?')) . ") ";
			array_push($params, ...$id_service);
		}

		$rel = "";
		if (isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by .= ", REL DESC";
				$where[] = " MATCH (title_service, info_service) AGAINST (?) ";
				$params[] = $keywords;
				$rel = " , MATCH (title_service, info_service) AGAINST (?) as REL ";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (title_service LIKE ? OR title_service LIKE ?) ";
                array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
			}
		}

		$sql = "SELECT *
						$rel
				FROM " . $this->services_contacts_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		if($order_by != "")
		  $sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}

	function count_company_services_contacts($conditions = array()) {
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($id_service)){
            $id_service = getArrayFromString($id_service);
			$where[] = " id_service IN (" . implode(',', array_fill(0, count($id_service), '?')) . ") ";
			array_push($params, ...$id_service);
		}

		if(isset($keywords)){
			$where[] = " MATCH (title_service, info_service) AGAINST (?) ";
			$params[] = $keywords;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->services_contacts_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$rez = $this->db->query_one($sql, $params);

		return $rez['counter'];
	}

	function set_company_service($data) {
		$this->db->insert($this->services_contacts_table, $data);
		return $this->db->last_insert_id();
	}

	public function delete_company_services($id, $company) {
        $params = $id = getArrayFromString($id);
        $params[] = $company;

		$sql = 'DELETE FROM ' . $this->services_contacts_table . ' WHERE id_service IN (' . implode(',', array_fill(0, count($id), '?')) . ') AND id_company = ?';
		return $this->db->query($sql, $params);
	}

	function get_company_service($id_service, $id_company) {
		$sql = "SELECT *
				FROM " . $this->services_contacts_table . "
				WHERE id_service = ? AND id_company = ? ";

		return $this->db->query_one($sql, array($id_service, $id_company));
	}

	function update_company_service($id_service, $id_company, $data) {
		$this->db->where('id_service', $id_service);
		$this->db->where('id_company', $id_company);
		return $this->db->update($this->services_contacts_table, $data);
	}

    function get_company_service_email($id_service)
    {
        $sql = "SELECT *
                FROM " . $this->services_contacts_table . "
                WHERE id_service = ? ";

        $res = $this->db->query_one($sql, array($id_service));

        return $res['email_service'];
    }

    public function findCompaniesByName(string $companyName): array
    {
        $this->db->select('*');
        $this->db->from("{$this->company_base_table} cb");
        $this->db->where_raw('name_company LIKE ?', "%$companyName%");
        $this->db->or_where_raw('legal_name_company LIKE ?', "%$companyName%");

        return $this->db->query_all();
    }

	function get_company_service_phone($id_service) {
		$sql = "SELECT CONCAT_WS(' ', phone_code, phone_service) as full_phone
				FROM " . $this->services_contacts_table . "
				WHERE id_service = ? ";

		$res = $this->db->query_one($sql, array($id_service));
		return $res['full_phone'];
	}

    function get_company($conditions){
		$check_index_name_temp = false;
		$type_company = 'company';

        $this->db->select(
            "cb.* ,
            ct.name_type, CONCAT(cb.id_type, '_', ct.name_type) as id_type_name,
            pc.country, CONCAT(cb.id_country, '_', pc.country) as id_country_country,
            st.state,
            z.city,
            u.registration_date, CONCAT(u.fname, ' ', u.lname) as user_name, u.`status`, u.user_group, u.logged, u.email, u.fake_user, u.is_verified,
            ug.gr_name, ug.gr_name as user_group_name, ug.gr_type, ug.gr_alias, ug.stamp_pic,
            ct.group_name_suffix as user_group_name_sufix"
        );
        $this->db->from("{$this->company_base_table} cb");
        $this->db->join("{$this->company_type_table} ct", "cb.id_type = ct.id_type", "inner");
        $this->db->join("{$this->users_table} u", "cb.id_user = u.idu", "inner");
        $this->db->join("{$this->user_groups_table} ug", "u.user_group = ug.idgroup", "inner");
        $this->db->join("{$this->country_table} pc", "cb.id_country = pc.id", "inner");
        $this->db->join("{$this->state_table} st", "cb.id_state = st.id", "left");
        $this->db->join("{$this->city_table} z", "cb.id_city = z.id", "left");

        extract($conditions);

        if (isset($id_company)) {
            $this->db->where("cb.id_company = ?", $id_company);
        }

		if(isset($accreditation)){
            $this->db->where("cb.accreditation = ?", $accreditation);
        }


		if (isset($index_name)) {
			if($check_index_name_temp){
                $this->db->or_where_raw("(cb.index_name = ? OR cb.index_name_temp = ?)", array($index_name, $index_name));
			} else{
                $this->db->where("cb.index_name = ?", $index_name);
			}
		}

		if (isset($id_user)) {
            $this->db->where("cb.id_user = ?", $id_user);
		}

		if ($type_company != 'all') {
            $this->db->where("cb.type_company = ?", $type_company);
		}

		if (isset($fake_user)) {
            $this->db->where("u.fake_user = ?", $fake_user);
        }

        $record = $this->db->query_one();

        if(!empty($record) && $record['type_company'] === 'branch'){
            $this->db->select("id_company, index_name, name_company, type_company");
            $this->db->from("$this->company_base_table");
            $this->db->where("id_company = ?", (int) $record['parent_company']);
            $parentRecord = $this->db->query_one();

            if(empty($parentRecord)){
                return false;
            }

            $record['main_company'] = $parentRecord;
        }

		return $record;
	}

    /**
     * Get companies
     *
     * @param array $conditions
     *
     * @return array $companies
     */
    function getCompanies(array $conditions): array
    {
        return $this->findRecords(
            null,
            $this->get_company_table(),
            null,
            $conditions
        );
    }

    /**
     * Get the resource.
     *
     * @throws NotFoundException if resource is not found
     * @throws QueryException    if DB query failed
     */
    public function get_simple_company(int $id_company, ?string $columns = null)
    {
        try {
            $resource = $this->findRecord(
                null,
                $this->get_company_table(),
                null,
                null,
                null,
                [
                    'columns'    => $columns ?? '*',
                    'conditions' => [
                        'company_id' => $id_company,
                    ],
                ]
            );

            if (empty($resource)) {
                throw new NotFoundException('The resource is not found.');
            }

            return $resource;
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    function getSimpleCompanyByIdUser($idUser, $columns = "*", $idCompany = false)
    {
		$this->db->select("{$columns}");
		$this->db->from($this->company_base_table);
        $this->db->where("id_user", (int) $idUser);
        if($idCompany){
            $this->db->where("id_company", (int) $idCompany);
        }

		return $this->db->get_one();
	}

	function get_seller_base_company($id_seller, $columns="cb.*", $seller_info = false) {
		$this->db->select("{$columns}");
		$this->db->from("$this->company_base_table cb");

		if($seller_info == true){
			$this->db->join("users u", "cb.id_user = u.idu", "inner");
		}

		$this->db->where("cb.id_user = ?", $id_seller);
		$this->db->where("cb.parent_company = ?", 0);

		return $this->db->get_one();
	}

	public function get_company_location(int $company_id): array
	{
		$this->db->select(
			<<<COLUMNS
			`COMPANIES`.`address_company`, `COMPANIES`.`zip_company`, `COMPANIES`.`id_country` as `country_id`, `COMPANIES`.`id_state` as `region_id`, `COMPANIES`.`id_city` as `city_id`,
			`COUNTRIES`.`country` as `country`, `REGIONS`.`state` as `region`, `CITIES`.`city`
			COLUMNS
		);
		$this->db->from('`company_base` as `COMPANIES`');
		$this->db->join('`zips` AS `CITIES`', '`COMPANIES`.`id_city` = `CITIES`.`id`', 'left');
		$this->db->join('`states` AS `REGIONS`', '`COMPANIES`.`id_state` = `REGIONS`.`id`', 'left');
		$this->db->join('`port_country` AS `COUNTRIES`', '`COMPANIES`.`id_country` = `COUNTRIES`.`id`', 'left');
		$this->db->where('`COMPANIES`.`id_company` = ?', $company_id);

		return array_filter((array) $this->db->query_one());
	}

	function get_sellers_base_company($id_sellers, $columns='*') {
        $id_sellers = getArrayFromString($id_sellers);
		$sql = "SELECT ".$columns."
				FROM " . $this->company_base_table . "
				WHERE id_user IN (" . implode(',', array_fill(0, count($id_sellers), '?')) . ") AND parent_company=0";
		return $this->db->query_all($sql, $id_sellers);
	}

	//for Ajax unlock
    function get_email_company($id_company)
    {
		$sql = "SELECT cb.email_company
					FROM " . $this->company_base_table . " cb
					WHERE cb.id_company = ?";
        $res = $this->db->query_one($sql, array($id_company));

		return $res['email_company'];
	}

    function get_phone_company($id_company)
    {
		$sql = "SELECT CONCAT_WS(' ', cb.phone_code_company, cb.phone_company) as full_phone
					FROM " . $this->company_base_table . " cb
					WHERE cb.id_company = ?";
        $res = $this->db->query_one($sql, array($id_company));

		return $res['full_phone'];
	}

	function get_fax_company($id_company) {
		$sql = "SELECT CONCAT_WS(' ', cb.fax_code_company, cb.fax_company) as full_fax
					FROM " . $this->company_base_table . " cb
					WHERE cb.id_company = ?";
		$res = $this->db->query_one($sql, array($id_company));
		return $res['full_fax'];
	}

	public function has_company_logo($company_id)
	{
		$this->db->select("IF(`COMPANIES`.`logo_company` IS NULL OR `COMPANIES`.`logo_company` = '', 0, 1) AS `AGGREGATE`");
		$this->db->from("`{$this->company_base_table}` AS `COMPANIES`");
		$this->db->where("`COMPANIES`.`id_company` = ?", (int) $company_id);

		$counter = $this->db->query_one();
		if (empty($counter)) {
			return false;
		}

		return (bool) (int) arrayGet($counter, 'AGGREGATE');
	}

	function get_company_logo($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if (isset($id_company)) {
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}

		if (isset($id_user)) {
			$where[] = " id_user = ? ";
			$params[] = $id_user;
		}

		$sql = "SELECT logo_company
				FROM " . $this->company_base_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		return $this->db->query_one($sql, $params);
	}

	function have_i_company($id_user) {
		$sql = "SELECT id_company
				FROM $this->company_base_table
				WHERE id_user = ? AND type_company = ? ";

		$info = $this->db->query_one($sql, array($id_user, 'company'));

		if ($this->db->numRows() > 0)
			return (int) $info['id_company'];
		else
			return false;
	}

	function is_my_company($condition, $id_user) {
		$where = array();
		$params = array();

		extract($condition);

		if (isset($id_company)) {
			$where[] = " id_company=? AND id_user=? AND type_company = ? ";
            array_push($params, ...[$id_company, $id_user, 'company']);
		}

		if (isset($index_name)) {
			$where[] = " index_name=? AND id_user=? AND type_company = ? ";
            array_push($params, ...[$index_name, $id_user, 'company']);
		}

		$sql = "SELECT COUNT(*) as is_my
				FROM " . $this->company_base_table;

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$info = $this->db->query_one($sql, $params);
		return $info['is_my'];
	}

	function is_my_company_branch($condition, $id_user) {
		$where = array();
		$params = array();

		extract($condition);

		if (isset($id_company)) {
			$where[] = " id_company=? AND id_user=? ";
			$params[] = $id_company;
			$params[] = $id_user;
		}

		if (isset($index_name)) {
			$where[] = " index_name=? AND id_user=? ";
			$params[] = $index_name;
			$params[] = $id_user;
		}

		$sql = "SELECT COUNT(*) as is_my
				FROM " . $this->company_base_table;

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$info = $this->db->query_one($sql, $params);
		return $info['is_my'];
	}

	function is_visible_company($id_company = 0) {
		$this->db->select("COUNT(id_company) as is_visible");
		$this->db->from("{$this->company_base_table}");
		$this->db->where("id_company = ?", $id_company);
		$this->db->where("visible_company = ?", 1);
		$record = $this->db->get_one();

		return (int) $record['is_visible'];
	}

	function update_company($id_company = 0, $data = array()) {
		if(empty($data)){
			return FALSE;
		}

		$this->db->where('id_company', $id_company);
		$rez = $this->db->update($this->company_base_table, $data);

        $this->obj->load->model("Elasticsearch_Company_Model", "elasticcompanymodel");
		$this->obj->elasticcompanymodel->index_company($id_company);

		if ( ! empty($data['name_company'])) {
			$company = $this->get_simple_company((int) $id_company, 'id_user');

            /** @var Crm_Model $crmModel */
            $crmModel = model(Crm_Model::class);

			$crmModel->create_or_update_record($company['id_user']);
		}

        return $rez;
    }

    public function update_company_business_number(int $company_id, ?string $business_number = null): bool
    {
        $this->db->where('id_company', $company_id);

        try {
            return $result = $this->db->update($this->company_base_table, array('business_number' => $business_number));
        } finally {
            if ($result) {
                /** @var Elasticsearch_Company_Model $elasticsearchCompanyModel */
                $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);

                $elasticsearchCompanyModel->index_company($company_id);
            }
        }
    }

	//companies categories relations
    function set_relation_category($id_company, $data){
        $records = [];

        foreach ($data as $id_category){
            $records[] = [
                'id_company' => $id_company,
                'id_category' => (int) $id_category
            ];
        }

        $this->db->insert_batch($this->relation_category_table, $records);

        return $this->db->last_insert_id();
	}

    function get_relation_category_by_company_id(int $company_id)
    {
        $this->db->from($this->relation_category_table);
        $this->db->where('id_company', $company_id);

        return $this->db->get();
    }

    function get_relation_industry_by_company_id(int $company_id, bool $join_with_categories = false)
    {
        $this->db->from($this->relation_industry_table);
        $this->db->where('id_company', $company_id);

        if ($join_with_categories) {
            $this->db->join($this->category_table, "{$this->category_table}.`category_id` = {$this->relation_industry_table}.`id_industry`", 'left');
        }

        return $this->db->get();
	}

    function delete_relation_category_by_id($id)
    {
        $this->db->where('id_relation', $id);

        return $this->db->delete($this->relation_category_table);
	}

    function delete_relation_category_by_company($id)
    {
        $this->db->where('id_company', $id);

        return $this->db->delete($this->relation_category_table);
	}

    //companies industries relations
    function set_relation_industry($id_company, $data) {
        $records = [];

        foreach ($data as $id_industry){
            $records[] = [
                'id_company' => $id_company,
                'id_industry' => (int) $id_industry
            ];
        }

        $this->db->insert_batch($this->relation_industry_table, $records);

        return $this->db->last_insert_id();
    }

	function get_relation_industry_by_id($id, $full = false, $conditions = array()) {
		$where = array();
		$params = array();

		extract($conditions);

		$where[] = " ri.id_company = ?";
        $params[] = $id;

		if (isset($id_industries)) {
            $id_industries = getArrayFromString($id_industries);
			$where[] = " ri.id_industry IN ( " . implode(',', array_fill(0, count($id_industries), '?')) . ") ";
            array_push($params, ...$id_industries);
		}

		$column = "";
		if ($full) {
			$column = ", ic.name, ic.category_id, ic.p_or_m, ic.parent, ic.cat_childrens";
		}

		$sql = "SELECT ri.* " . $column . "
					FROM " . $this->relation_industry_table . " ri";

		if ($full)
			$sql .= " LEFT JOIN item_category ic ON ri.id_industry = ic.category_id";

		if (count($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		if (isset($limit))
			$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	function get_companies_list_by_industries($id_industry = 0){
        $id_industry = getArrayFromString($id_industry);

		$sql = "SELECT GROUP_CONCAT(DISTINCT(id_company)) as companies_list
				FROM {$this->relation_industry_table}
				WHERE id_industry IN (" . implode(',', array_fill(0, count($id_industry), '?')) . ")";

		$companies_list = $this->db->query_one($sql, $id_industry);

        return empty($companies_list['companies_list']) ? [0] : explode(',', $companies_list['companies_list']);
	}

	function get_companies_list_by_categories($id_category = 0){
        $id_category = getArrayFromString($id_category);

		$sql = "SELECT GROUP_CONCAT(DISTINCT(id_company)) as companies_list
				FROM {$this->relation_category_table}
				WHERE id_category IN (" . implode(',', array_fill(0, count($id_category), '?')) . ")";

		$companies_list = $this->db->query_one($sql, $id_category);
		if(empty($companies_list['companies_list'])){
			return array(0);
		}

		return explode(',', $companies_list['companies_list']);
	}

	function get_company_industry_categories($conditions) {
		$order_by = "ic.name ASC";
		$where = array();
		$params = array();
		$columns = 'rc.*, ic.name, ic.category_id, ic.parent ';

		extract($conditions);

		if (isset($company)) {
			$where[] = " rc.id_company = ? ";
            $params[] = $company;
		}

		if (isset($parent)) {
            $parent = getArrayFromString($parent);
			$where[] = " ic.parent IN ( " . implode(',', array_fill(0, count($parent), '?')). ") ";
            array_push($params, ...$parent);
		}

		if (isset($category_list)) {
            $category_list = getArrayFromString($category_list);
			$where[] = " ic.category_id IN ( " . implode(',', array_fill(0, count($category_list), '?')) . ") ";
            array_push($params, ...$category_list);
		}

		$sql = "SELECT " . $columns . "
					FROM " . $this->relation_category_table . " rc
					LEFT JOIN item_category ic ON rc.id_category = ic.category_id";

		if (count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " GROUP BY rc.id_category ";
		if ($order_by != false)
			$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}

	function delete_relation_industry_by_id($id) {
		$this->db->where('id_relation', $id);
		return $this->db->delete($this->relation_industry_table);
	}

	function delete_relation_industry_by_company($id) {
		$this->db->where('id_company', $id);
		return $this->db->delete($this->relation_industry_table);
	}

	//companies attributes
	function set_attributes($id_company, $data) {
		$sql = "INSERT INTO " . $this->company_attributes_table . "
					(`id_company`, `name_attribute`, `value_attribute`) VALUES ";
		$params = array();
		foreach ($data as $attr){
			$attrs[] = " (?, ?, ?) ";
			$params = array_merge($params, array($id_company, $attr['attr'], $attr['value']));
		}

		$sql .= implode(',', $attrs);

		$this->db->query($sql, $params);
		return $this->db->last_insert_id();
	}

	function get_attributes_by_id($id) {
		$sql = "SELECT *
				FROM $this->company_attributes_table
				WHERE id_company = ?";

		return $this->db->query_all($sql, array($id));
	}

	function delete_attributes_by_id($id) {
		$this->db->where('id_attribute', $id);
		return $this->db->delete($this->company_attributes_table);
	}

	function delete_attributes_by_company($id_company) {
		$this->db->where('id_company', $id_company);
		return $this->db->delete($this->company_attributes_table);
	}

	function exist_company($conditions) {
		$type_company = 'company';
		$where = array();
		$params = array();
		extract($conditions);

		if (isset($company)) {
			$where[] = " cb.id_company = ? AND type_company = ? ";
			$params[] = $company;
            $params[] = 'company';
		}

		if (isset($not_company)) {
			$where[] = " cb.id_company != ?";
			$params[] = (int) $not_company;
		}

		if (isset($company_or_branch)) {
			$where[] = " cb.id_company = ? ";
			$params[] = $company_or_branch;
		}

		if (isset($user)) {
			$where[] = " cb.id_user = ? AND type_company = ? ";
			$params[] = $user;
            $params[] = 'company';
		}

		if (isset($index_name)) {
			$where[] = " LOWER(cb.index_name) = LOWER(?) AND type_company = ? ";
			$params[] = $index_name;
            $params[] = 'company';
		}

		if (isset($index_name_temp)) {
			$where[] = " LOWER(cb.index_name_temp) = LOWER(?) AND type_company = ? ";
			$params[] = $index_name_temp;
            $params[] = 'company';
		}

		if (isset($email)) {
			$where[] = " cb.email_company = ? AND type_company = ? ";
			$params[] = $email;
            $params[] = 'company';
		}

		$sql = "SELECT count(*) as counter
					FROM " . $this->company_base_table . " cb";

		if (count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

    function exist_company_branch($conditions)
    {
        $where = array();
        $params = array();
        extract($conditions);

        if (isset($company)) {
            $where[] = " cb.id_company = ? ";
            $params[] = $company;
        }

        if (isset($user)) {
            $where[] = " cb.id_user = ? ";
            $params[] = $user;
        }

        if (isset($index_name)) {
            $where[] = " LOWER(cb.index_name) = LOWER(?) ";
            $params[] = $index_name;
        }

        $sql = "SELECT count(*) as counter
                    FROM " . $this->company_base_table . " cb";

        if (count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $rez = $this->db->query_one($sql, $params);

        return $rez['counter'];
	}

    function count_category($conditions = array())
    {
		extract($conditions);

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if (isset($type)) {
			$where[] = " cb.id_type = ? ";
			$params[] = $type;
		}

		if (isset($industry)) {
			$where[] = " ri.id_industry = ? ";
			$params[] = $industry;
		}

		if (isset($category)) {
			$where[] = " rc.id_category = ? ";
			$params[] = $category;
		}

		if (isset($country)) {
			$where[] = " cb.id_country = ? ";
			$params[] = $country;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($blocked)) {
			$where[] = " cb.blocked = ? ";
			$params[] = $blocked;
		}

		if (isset($keywords)) {
			$where[] = " MATCH (cb.name_company, cb.description_company) AGAINST (?)";
			$params[] = $keywords;
		}

		$sql = "SELECT rc.id_category, ic.name, ic.parent, count(DISTINCT cb.id_company) as counter, ri.*
					FROM " . $this->company_base_table . " cb
					LEFT JOIN " . $this->relation_category_table . " rc
					ON cb.id_company = rc.id_company
					INNER JOIN " . $this->relation_industry_table . " ri
					ON cb.id_company = ri.id_company
					INNER JOIN " . $this->category_table . " ic
					ON rc.id_category = ic.category_id";

		if (!empty($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY rc.id_category
					ORDER BY counter DESC";

		return $this->db->query_all($sql, $params);
	}

	/**
	 * types of the companies
	 */
    function get_company_type($id)
    {
        $sql = "SELECT *
                FROM " . $this->company_type_table . "
                WHERE id_type = ?";

        return $this->db->query_one($sql, array($id));
	}

	public function is_company_of_type($company_id, $type_id)
	{
		$this->db->select('COUNT(*) AS `AGGREGATE`');
		$this->db->from($this->company_base_table);
		$this->db->where("id_company = ?", (int) $company_id);
		$this->db->where('id_type = ?', (int) $type_id);

		$counter = $this->db->query_one();
		if (empty($counter)) {
			return false;
		}

		return (bool) (int) arrayGet($counter, 'AGGREGATE');
	}

	function get_company_types($user_group = '') {
        $params = [];

        $sql = "SELECT *
                FROM " . $this->company_type_table;

        if (!empty($user_group)) {
            $sql .= ' WHERE JSON_CONTAINS(`allowed_user_groups`, ?) = 1';
            $params[] = $user_group;
        }

		$sql .= ' ORDER BY name_type';

		return $this->db->query_all($sql, $params);
	}

	function set_company_type($data) {
        if (!count($data))
            return false;
        $this->db->insert($this->company_type_table, $data);
        return $this->db->last_insert_id();
	}

    public function exist_company_type($value) {
        $sql = "SELECT count(*) as exist
            FROM " . $this->company_type_table . "
            WHERE `id_type` = ?";
        $rez = $this->db->query_one($sql, array($value));
        return $rez['exist'];
	}

	public function update_company_type($id, $data) {
        $this->db->where('id_type', $id);
        $rez = $this->db->update($this->company_type_table, $data);

        /** @var Elasticsearch_Company_Model $elasticsearchCompanyModel */
        $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);

        $elasticsearchCompanyModel->index();

        return $rez;
	}

    public function delete_company_type($id)
    {
        $this->db->where('id_type', $id);

        return $this->db->delete($this->company_type_table);
	}

	function count_types($conditions = array()) {
		$where = array();
		$params = array();
		$type_company = 'company';

		extract($conditions);

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if (isset($type)) {
			$where[] = " cb.id_type = ? ";
			$params[] = $type;
		}

		if ($type_company != 'all') {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($industry)) {
			$where[] = " ri.id_industry = ? ";
			$params[] = $industry;
		}

		if (isset($category)) {
			$where[] = " rc.id_category = ? ";
			$params[] = $category;
		}

		if (isset($country)) {
			$where[] = " cb.id_country = ? ";
			$params[] = $country;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($blocked)) {
			$where[] = " cb.blocked = ? ";
			$params[] = $blocked;
		}

		if (isset($keywords)) {
			$where[] = " MATCH (cb.name_company, cb.description_company) AGAINST (?) ";
            $params[] = $keywords;
		}

		$sql = "SELECT cb.id_type, ct.name_type, count(DISTINCT cb.id_company) as counter
				FROM " . $this->company_base_table . " cb
				INNER JOIN " . $this->company_type_table . " ct ON cb.id_type=ct.id_type
				INNER JOIN " . $this->relation_category_table . " rc ON cb.id_company = rc.id_company
				INNER JOIN " . $this->relation_industry_table . " ri ON cb.id_company = ri.id_company";

		if (count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY cb.id_type
					ORDER BY ct.name_type";

		return $this->db->query_all($sql, $params);
	}

    function get_company_industry($id)
    {
        $sql = "SELECT i.*, ic.name
                    FROM " . $this->company_categories_table . " as i
                    LEFT JOIN " . $this->category_table . " ic
                    ON i.id_category = ic.category_id
					WHERE i.id_category = ?";

        return $this->db->query_one($sql, array($id));
	}

	function get_company_industries() {
		$order_by = 'ic.name';

		$sql = "SELECT i.*, ic.name
					FROM " . $this->company_categories_table . " as i
					LEFT JOIN " . $this->category_table . " ic ON i.id_category = ic.category_id
					WHERE ic.parent = 0 ";

		$sql .= ' ORDER BY ' . $order_by;

		return $this->db->query_all($sql);
	}

	function get_seller_industries($id_user = 0) {
		$this->db->select("cri.id_industry");
		$this->db->from("{$this->relation_industry_table} cri");
		$this->db->join("{$this->company_base_table} cb", "cri.id_company = cb.id_company AND cb.parent_company = 0", "inner");
        $this->db->where("cb.id_user = ?", $id_user);

		$records = $this->db->get();

		return array_column(array_filter((array) $records), 'id_industry');
	}

    /**
     *
     * @param array $conditions
     *
     * @return array
     */
    function get_report_sellers_per_industry(array $conditions = []): array
    {
        $conditions = array_merge(
            [
                'modelUser'         => 0,
                'fakeUser'          => 0,
            ],
            $conditions
        );
        return array_column(
            $this->findRecords(
                null,
                $this->relation_industry_table,
                null,
                [
                    'columns' => [
                        $this->relation_industry_table . '.id_industry',
                        'COUNT(*) AS countSellers'
                    ],
                    'joins' => [
                        'companies',
                        'users'
                    ],
                    'conditions' => $conditions,
                    'group' => [
                        $this->relation_industry_table . '.id_industry'
                    ],
                ]
            ),
            null,
            'id_industry'
        );
    }

	/** @deprecated */
    function set_company_industry($data)
    {
        if (!count($data))
            return false;
        $this->db->insert($this->company_categories_table, $data);
        return $this->db->last_insert_id();
	}

	/** @deprecated */
	public function exist_company_industry($value) {
        $sql = "SELECT count(*) as exist
            FROM " . $this->company_categories_table . "
            WHERE `id_category` = ?";
        $rez = $this->db->query_one($sql, array($value));

        return $rez['exist'];
	}

	/** @deprecated */
	public function update_company_industry($id, $data) {
        $this->db->where('id_category', $id);
        $rez = $this->db->update($this->company_categories_table, $data);

        /** @var Elasticsearch_Company_Model $elasticsearchCompanyModel */
        $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);

        $elasticsearchCompanyModel->index();

        return $rez;
	}

	function count_industry($conditions = array()) {

		extract($conditions);

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if (isset($type)) {
			$where[] = " cb.id_type = ? ";
			$params[] = $type;
		}

		if (isset($industry)) {
			$where[] = " ri.id_industry = ? ";
			$params[] = $industry;
		}

		if (isset($category)) {
			$where[] = " rc.id_category = ? ";
			$params[] = $category;
		}

		if (isset($country)) {
			$where[] = " cb.id_country = ? ";
			$params[] = $country;
		}

		if (isset($keywords)) {
			$where[] = " MATCH (cb.name_company, cb.description_company) AGAINST (?)";
			$params[] = $keywords;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($blocked)) {
			$where[] = " cb.blocked = ? ";
			$params[] = $blocked;
		}

		$sql = "SELECT ri.id_industry, ic.name, count(DISTINCT cb.id_company) as counter
					FROM $this->company_base_table cb
					INNER JOIN $this->relation_category_table rc ON cb.id_company = rc.id_company
					INNER JOIN $this->relation_industry_table ri	ON cb.id_company = ri.id_company
					LEFT JOIN $this->category_table ic ON ri.id_industry = ic.category_id
					WHERE ic.parent = 0";

		if (!empty($where))
			$sql .= " AND " . implode(" AND", $where);

		$sql .= " GROUP BY ri.id_industry
					ORDER BY counter DESC";

		return $this->db->query_all($sql, $params);
	}

	/** @deprecated */
	function get_simple_company_categories() {
		$sql = "SELECT *
				FROM " . $this->company_categories_table;

		return $this->db->query_all($sql);
	}

	/** @deprecated */
	function get_company_category($id) {
		$sql = "SELECT c.*, ic.name
				FROM " . $this->company_categories_table . " as c
				LEFT JOIN " . $this->category_table . " ic
				ON c.id_category = ic.category_id
				WHERE c.id_category = ?";
		return $this->db->query_one($sql, array($id));
	}

	/** @deprecated */
	function get_company_categories() {
		$order_by = 'ic.name';

		$sql = "SELECT i.*, ic.name
					FROM " . $this->company_categories_table . " as i
					LEFT JOIN " . $this->category_table . " ic ON i.id_category = ic.category_id
					WHERE ic.parent > 0";

		$sql .= ' ORDER BY ' . $order_by;

		return $this->db->query_all($sql);
	}

	function get_categories() {
		$sql = "SELECT ic.category_id, ic.name, ic.parent
					FROM " . $this->category_table . " ic
					WHERE parent != 0
					ORDER BY ic.name";
		return $this->db->query_all($sql);
	}

	function get_categories_by_conditions($conditions) {
		$where = array();
		$params = array();
		$select = " category_id, name, parent ";
		$order_by = 'name DESC';

		extract($conditions);

		if (isset($parent)) {
			$where[] = " parent = ? ";
            $params[] = $parent;
		}

		if (isset($category_list)) {
            $category_list = getArrayFromString($category_list);
			$where[] = " category_id IN ( " . implode(',', array_fill(0, count($category_list), '?')) . ") ";
            array_push($params, ...$category_list);
		}

		$sql = "SELECT " . $select ."
				FROM " . $this->category_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " ORDER BY ".$order_by;

		return $this->db->query_all($sql, $params);
	}

	function count_categories_by_conditions($conditions) {
		$where = array();
		$params = array();

		extract($conditions);

		if (isset($parent)) {
			$where[] = " parent = ?";
            $params[] = $parent;
		}

		if (isset($parent_not)) {
			$where[] = " parent != ?";
            $params[] = $parent_not;
		}

		if (isset($category_list)) {
            $category_list = getArrayFromString($category_list);
			$where[] = " category_id IN ( " . implode(',', array_fill(0, count($category_list), '?')) . ") ";
            array_push($params, ...$category_list);
		}

        if (isset($parent_list)) {
            $parent_list = getArrayFromString($parent_list);

			$where[] = " parent IN (" . implode(',', array_fill(0, count($parent_list), '?')) . ") ";
            array_push($params, ...$parent_list);
		}

		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->category_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$rez = $this->db->query_one($sql, $params);

		return $rez['counter'];
	}

	/** @deprecated */
    function set_company_category($data)
    {
        if (!count($data))
            return false;
        $this->db->insert($this->company_categories_table, $data);
        return $this->db->last_insert_id();
	}

	/** @deprecated */
	public function exist_company_category($value) {
        $sql = "SELECT count(*) as exist
                    FROM " . $this->company_categories_table . "
                    WHERE `id_category` = ?";
        $rez = $this->db->query_one($sql, array($value));

        return $rez['exist'];
	}

	/** @deprecated */
	public function update_company_category($id, $data) {
        $this->db->where('id_category', $id);
        $rez = $this->db->update($this->company_categories_table, $data);

        /** @var Elasticsearch_Company_Model $elasticsearchCompanyModel */
        $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);

        $elasticsearchCompanyModel->index();

        return $rez;
	}

	function update_companies($ids, $data) {
        $this->db->in('id_company', $ids);
        $rez = $this->db->update($this->company_base_table, $data);

        /** @var Elasticsearch_Company_Model $elasticsearchCompanyModel */
        $elasticsearchCompanyModel = model(Elasticsearch_Company_Model::class);

        $elasticsearchCompanyModel->index_companies($ids);

        return $rez;
	}

	public function get_company_statistics($id_company) {
		$sql = "SELECT cb.id_company, photos_nr, videos_nr,news_nr,branches_nr,items_nr
				FROM company_base cb
					LEFT JOIN (
						SELECT id_company, COUNT(*) as photos_nr
						FROM seller_photo
						GROUP BY id_company) sp ON sp.id_company = cb.id_company
					LEFT JOIN (
						SELECT id_company, COUNT(*) as videos_nr
						FROM seller_videos
						GROUP BY id_company) sv ON sv.id_company = cb.id_company
					LEFT JOIN (
						SELECT id_company, COUNT(*) as news_nr
						FROM seller_news
						GROUP BY id_company) sn ON sn.id_company = cb.id_company
					LEFT JOIN (
						SELECT COUNT(*) as items_nr, id_seller,id_company
						FROM items it
						LEFT JOIN company_base cb ON it.id_seller=cb.id_user
						WHERE type_company = 'company'
						GROUP BY it.id_seller) itm ON itm.id_company=cb.id_company
					LEFT JOIN (
						SELECT parent_company, COUNT(*) as branches_nr
						FROM company_base
						WHERE parent_company = ?
						GROUP BY parent_company) cba
						ON cba.parent_company=cb.id_company
				WHERE cb.id_company = ?";
		return $this->db->query_one($sql, [$id_company, $id_company]);
	}

	function get_companies_last_id(){
		$sql = "SELECT id_company
				FROM {$this->company_base_table}
				ORDER BY id_company DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

        return $rez['id_company'] ?: 0;
	}

	function get_count_new_companies($id_company){
		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->company_base_table."
				WHERE id_company > ? ";

		$rez = $this->db->query_one($sql, array($id_company));
		return $rez['counter'];
	}

	function get_photos_last_id(){
		$sql = "SELECT id_photo
				FROM seller_photo
				ORDER BY id_photo DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

		return $rez['id_photo'] ?: 0;
	}

	function get_count_new_photos($id_photo){
		$sql = "SELECT COUNT(*) as counter
				FROM seller_photo
				WHERE id_photo > ? ";

		$rez = $this->db->query_one($sql, array($id_photo));
		return $rez['counter'];
	}

	function get_updates_last_id(){
		$sql = "SELECT id_update
				FROM seller_updates
				ORDER BY id_update DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

		return $rez['id_update'] ?: 0;
	}

	function get_count_new_updates($id_update){
		$sql = "SELECT COUNT(*) as counter
				FROM seller_updates
				WHERE id_update > ? ";

		$rez = $this->db->query_one($sql, array($id_update));
		return $rez['counter'];
	}

	function get_videos_last_id(){
		$sql = "SELECT id_video
				FROM seller_videos
				ORDER BY id_video DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

		return $rez['id_video'] ?: 0;
	}

	function get_count_new_videos($id_video){
		$sql = "SELECT COUNT(*) as counter
				FROM seller_videos
				WHERE id_video > ? ";

		$rez = $this->db->query_one($sql, array($id_video));
		return $rez['counter'];
	}

	function get_news_last_id(){
		$sql = "SELECT id_news
				FROM seller_news
				ORDER BY id_news DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

		return $rez['id_news'] ?: 0;
	}

	function get_count_new_news($id_news){
		$sql = "SELECT COUNT(*) as counter
				FROM seller_news
				WHERE id_news > ? ";

		$rez = $this->db->query_one($sql, array($id_news));
		return $rez['counter'];
	}

	function get_libraries_last_id(){
		$sql = "SELECT id_file
				FROM seller_library
				ORDER BY id_file DESC
				LIMIT 0,1";

		$rez = $this->db->query_one($sql);

		return $rez['id_file'] ?: 0;
	}

	function get_count_new_libraries($id_library){
		$sql = "SELECT COUNT(*) as counter
				FROM seller_library
				WHERE id_file > ? ";

		$rez = $this->db->query_one($sql, array($id_library));
		return $rez['counter'];
	}

	public function get_companies_photos($conditions) {
        $params = [];
		$order_by = "sp.add_date_photo DESC";

		extract($conditions);

		if (isset($id_photo)) {
            $id_photo = getArrayFromString($id_photo);
			$where[] = " sp.id_photo IN (" . implode(',', array_fill(0, count($id_photo), '?')) . ")" ;
            array_push($params, ...$id_photo);
        }

		if (isset($added_start)) {
			$where[] = " DATE(sp.add_date_photo)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(sp.add_date_photo)<=?";
			$params[] = $added_finish;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($id_company)) {
			$where[] = " sp.id_company = ? ";
			$params[] = $id_company;
		}
		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($pictures_category)) {
			$where[] = " sp.id_category = ? ";
			$params[] = $pictures_category;
		}

		if (isset($multiple_sort_by)) {
			foreach ($multiple_sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($keywords)) {
			$where[] = " MATCH(sp.title_photo,sp.description_photo) AGAINST (?)";
            $params[] = $keywords;
        }

		$sql = "SELECT sp.*, cb.logo_company, cb.index_name, cb.name_company, cb.type_company, cb.visible_company, cb.id_user, CONCAT(u.fname,' ',u.lname) as user_name, u.status, spc.category_title
				FROM seller_photo sp
				LEFT JOIN seller_photo_categories spc ON sp.id_category = spc.id_category
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_company=sp.id_company
				LEFT JOIN users u ON u.idu=cb.id_user";

		if (!empty($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= ' ORDER BY ' . $order_by;

		if (isset($limit))
			$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	public function count_companies_photos($conditions) {
		$where = array();
		$params = array();
		extract($conditions);

		if (isset($id_photo)) {
            $id_photo = getArrayFromString($id_photo);
			$where[] = " sp.id_photo IN (" . implode(',', array_fill(0, count($id_photo), '?')) . ")" ;
            array_push($params, ...$id_photo);
        }

		if (isset($added_start)) {
			$where[] = " DATE(sp.add_date_photo)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(sp.add_date_photo)<=?";
			$params[] = $added_finish;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($id_company)) {
			$where[] = " sp.id_company = ? ";
			$params[] = $id_company;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($pictures_category)) {
			$where[] = " sp.id_category = ? ";
			$params[] = $pictures_category;
		}

		if (isset($keywords)) {
			$where[] = " MATCH(sp.title_photo,sp.description_photo) AGAINST (?)";
            $params[] = $keywords;
        }


		$sql = "SELECT COUNT(*) as counter
				FROM seller_photo sp
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_company=sp.id_company";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$result = $this->db->query_one($sql, $params);
		return  $result['counter'];
	}

	public function get_companies_videos($conditions) {
		$where = array();
		$params = array();
		$order_by = "sv.add_date_video DESC";

		extract($conditions);

		if (isset($id_video)) {
            $id_video = getArrayFromString($id_video);
			$where[] = " sv.id_video IN (" . implode(',', array_fill(0, count($id_video), '?')) . ")";
            array_push($params, ...$id_video);
        }

		if (isset($added_start)) {
			$where[] = " DATE(sv.add_date_video)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(sv.add_date_video)<=?";
			$params[] = $added_finish;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($id_company)) {
			$where[] = " sv.id_company = ? ";
			$params[] = $id_company;
		}
		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($multiple_sort_by)) {
			foreach ($multiple_sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($videos_category)) {
			$where[] = " sv.id_category = ? ";
			$params[] = $videos_category;
		}

		if (isset($keywords)) {
			$where[] = " MATCH(sv.title_video,sv.description_video) AGAINST (?)";
            $params[] = $keywords;
        }


		$sql = "SELECT sv.*, cb.logo_company, cb.index_name, cb.name_company, cb.type_company, cb.visible_company, cb.id_user, CONCAT(u.fname,' ',u.lname) as user_name, u.status, svc.category_title
				FROM seller_videos sv
				LEFT JOIN seller_video_categories svc ON sv.id_category = svc.id_category
				LEFT JOIN " . $this->company_base_table . " cb ON sv.id_company = cb.id_company
				LEFT JOIN users u ON u.idu = cb.id_user";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= ' ORDER BY ' . $order_by;

		if (isset($limit))
			$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	public function count_companies_videos($conditions) {
		$where = array();
		$params = array();
		extract($conditions);

		if (isset($id_video)) {
            $id_video = getArrayFromString($id_video);
			$where[] = " sv.id_video IN (" . implode(',', array_fill(0, count($id_video), '?')) . ")" ;
            array_push($params, ...$id_video);
        }

		if (isset($added_start)) {
			$where[] = " DATE(sv.add_date_video)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(sv.add_date_video)<=?";
			$params[] = $added_finish;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($id_company)) {
			$where[] = " sv.id_company = ? ";
			$params[] = $id_company;
		}

		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($videos_category)) {
			$where[] = " sv.id_category = ? ";
			$params[] = $videos_category;
		}

		if (isset($keywords)) {
			$where[] = " MATCH(sv.title_video,sv.description_video) AGAINST (?)";
            $params[] = $keywords;
        }

		$sql = "SELECT COUNT(*) as counter
				FROM seller_videos sv
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_company=sv.id_company";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$result = $this->db->query_one($sql, $params);
		return  $result['counter'];
	}

	public function get_companies_news($conditions) {
		$where = array();
		$params = array();
		$order_by = "sn.date_news DESC";

		extract($conditions);

		if (isset($id_news)) {
            $id_news = getArrayFromString($id_news);
			$where[] = " sn.id_news IN (" . implode(',', array_fill(0, count($id_news), '?')) . ")";
            array_push($params, ...$id_news);
        }

		if (isset($added_start)) {
			$where[] = " DATE(sn.date_news)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(sn.date_news)<=?";
			$params[] = $added_finish;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($id_company)) {
			$where[] = " sn.id_company = ? ";
			$params[] = $id_company;
		}
		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($multiple_sort_by)) {
			foreach ($multiple_sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($keywords)) {
			$where[] = " MATCH(sn.title_news,sn.text_news) AGAINST (?)";
            $params[] = $keywords;
        }


		$sql = "SELECT sn.*, cb.logo_company, cb.index_name, cb.name_company, cb.type_company, cb.visible_company, cb.id_user, CONCAT(u.fname,' ',u.lname) as user_name, u.status
				FROM seller_news sn
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_company=sn.id_company
				LEFT JOIN users u ON u.idu=cb.id_user";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= ' ORDER BY ' . $order_by;

		if (isset($limit))
			$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	public function count_companies_news($conditions) {
		$where = array();
		$params = array();
		extract($conditions);

		if (isset($id_news)) {
            $id_news = getArrayFromString($id_news);
			$where[] = " sn.id_news IN (" . implode(',', array_fill(0, count($id_news), '?')) . ")";
            array_push($params, ...$id_news);
        }

		if (isset($added_start)) {
			$where[] = " DATE(sn.date_news)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(sn.date_news)<=?";
			$params[] = $added_finish;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($id_company)) {
			$where[] = " sn.id_company = ? ";
			$params[] = $id_company;
		}

		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($keywords)) {
			$where[] = " MATCH(sn.title_news,sn.text_news) AGAINST (?)";
            $params[] = $keywords;
        }


		$sql = "SELECT COUNT(*) as counter
				FROM seller_news sn
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_company=sn.id_company";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$result = $this->db->query_one($sql, $params);
		return  $result['counter'];
	}

	public function get_companies_updates($conditions) {
		$where = array();
		$params = array();
		$order_by = "su.date_update DESC";

		extract($conditions);

		if (isset($id_updates)) {
            $id_updates = getArrayFromString($id_updates);
			$where[] = " su.id_update IN (" . implode(',', array_fill(0, count($id_updates), '?')) . ")" ;
            array_push($params, ...$id_updates);
        }

		if (isset($added_start)) {
			$where[] = " DATE(su.date_update)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(su.date_update)<=?";
			$params[] = $added_finish;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($id_company)) {
			$where[] = " su.id_company = ? ";
			$params[] = $id_company;
		}
		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($multiple_sort_by)) {
			foreach ($multiple_sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($keywords)) {
			$where[] = " MATCH(su.text_update) AGAINST (?)";
            $params[] = $keywords;
        }


		$sql = "SELECT su.*, cb.logo_company, cb.index_name, cb.name_company, cb.type_company, cb.visible_company, cb.id_user, CONCAT(u.fname,' ',u.lname) as user_name, u.status
				FROM seller_updates su
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_company=su.id_company
				LEFT JOIN users u ON u.idu=cb.id_user";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= ' ORDER BY ' . $order_by;

		if (isset($limit))
			$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	public function count_companies_updates($conditions) {
		$where = array();
		$params = array();
		extract($conditions);

		if (isset($id_updates)) {
            $id_updates = getArrayFromString($id_updates);
			$where[] = " su.id_update IN (" . implode(',', array_fill(0, count($id_updates), '?')) . ")" ;
            array_push($params, ...$id_updates);
        }

		if (isset($added_start)) {
			$where[] = " DATE(su.date_update)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(su.date_update)<=?";
			$params[] = $added_finish;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($id_company)) {
			$where[] = " su.id_company = ? ";
			$params[] = $id_company;
		}

		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($keywords)) {
			$where[] = " MATCH(su.text_update) AGAINST (?)";
            $params[] = $keywords;
        }


		$sql = "SELECT COUNT(*) as counter
				FROM seller_updates su
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_company=su.id_company";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$result = $this->db->query_one($sql, $params);
		return  $result['counter'];
	}

	public function get_companies_library($conditions) {
		$where = array();
		$params = array();
		$order_by = "sl.add_date_file DESC";

		extract($conditions);

		if (isset($id_file)) {
            $id_file = getArrayFromString($id_file);
			$where[] = " sl.id_file IN (" . implode(',', array_fill(0, count($id_file), '?')) . ")" ;
            array_push($params, ...$id_file);
        }

		if (isset($added_start)) {
			$where[] = " DATE(sl.add_date_file)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(sl.add_date_file)<=?";
			$params[] = $added_finish;
		}

		if (isset($id_company)) {
			$where[] = " cb.id_company = ? ";
			$params[] = $id_company;
		}

		if (isset($access)) {
			$where[] = " sl.type_file = ? ";
			$params[] = $access;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($seller)) {
			$where[] = " u.idu = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($library_category)) {
			$where[] = " sl.id_category = ? ";
			$params[] = $library_category;
		}

		if (isset($multiple_sort_by)) {
			foreach ($multiple_sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($keywords)) {
			$where[] = " MATCH(sl.title_file, sl.description_file) AGAINST (?)";
            $params[] = $keywords;
        }

		$sql = "SELECT sl.*, slc.category_title, cb.logo_company, cb.index_name, cb.name_company, cb.type_company, cb.visible_company, cb.id_user, CONCAT(u.fname,' ',u.lname) as user_name, u.status
				FROM seller_library sl
				LEFT JOIN seller_library_categories slc ON sl.id_category = slc.id_category
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_user=sl.id_seller
				LEFT JOIN users u ON u.idu=sl.id_seller";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= " GROUP BY sl.id_file ";

		$sql .= ' ORDER BY ' . $order_by;

		if (isset($limit))
			$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	public function count_companies_library($conditions) {
		$where = array();
		$params = array();
		extract($conditions);

		if (isset($id_file)) {
            $id_file = getArrayFromString($id_file);
			$where[] = " sl.id_file IN (" . implode(',', array_fill(0, count($id_file), '?')) . ")" ;
            array_push($params, ...$id_file);
        }

		if (isset($added_start)) {
			$where[] = " DATE(sl.add_date_file)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(sl.add_date_file)<=?";
			$params[] = $added_finish;
		}

		if (isset($id_company)) {
			$where[] = " cb.id_company = ? ";
			$params[] = $id_company;
		}

		if (isset($access)) {
			$where[] = " sl.type_file = ? ";
			$params[] = $access;
		}

		if (isset($type_company) && $type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($seller)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $seller;
		}

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($keywords)) {
			$where[] = " MATCH(sl.title_file, sl.description_file) AGAINST (?)";
            $params[] = $keywords;
        }

		$sql = "SELECT COUNT(*) as counter
				FROM seller_library sl
				LEFT JOIN " . $this->company_base_table . " cb ON cb.id_user=sl.id_seller";

		if (count($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$result = $this->db->query_one($sql, $params);
		return  $result['counter'];
	}


	public function moderatePhotos($ids) {
        $ids = getArrayFromString($ids);
		$sql = "UPDATE seller_photo
				SET moderated=1
				WHERE id_photo IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
		return $this->db->query($sql, $ids);
	}

	public function delete_company_photo($id) {
        $id = getArrayFromString($id);
		$sql = 'DELETE FROM seller_photo WHERE id_photo IN (' . implode(',', array_fill(0, count($id), '?')) . ')';
		return $this->db->query($sql, $id);
	}

	public function delete_company_photo_comments($id) {
        $id = getArrayFromString($id);
		$sql = 'DELETE FROM seller_photo_comments WHERE id_photo IN (' . implode(',', array_fill(0, count($id), '?')) . ')';
		return $this->db->query($sql, $id);
	}

    /**
     * Get the resource.
     *
     * @throws NotFoundException if resource is not found
     * @throws QueryException    if DB query failed
     */
    public function moderateVideos($ids)
    {
        try {
            $this->updateRecords( null, "seller_videos", null,
                [
                    'moderated' => 1,
                ],
                [
                    'conditions' => [
                        'seller_videos' => $ids,
                    ],
                ]
            );

            return true;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);

            return false;
        }
    }

	public function delete_company_video($id) {
        $id = getArrayFromString($id);
		$sql = 'DELETE FROM seller_videos WHERE id_video IN (' . implode(',', array_fill(0, count($id), '?')) . ')';
		return $this->db->query($sql, $id);
	}

	public function delete_company_video_comments($id) {
        $id = getArrayFromString($id);
		$sql = 'DELETE FROM seller_video_comments WHERE id_video IN (' . implode(',', array_fill(0, count($id), '?')) . ')';
		return $this->db->query($sql, $id);
	}

	public function moderateNews($ids) {
        $ids = getArrayFromString($ids);

		$sql = "UPDATE seller_news
				SET moderated=1
				WHERE id_news IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
		return $this->db->query($sql, $ids);
	}

	public function delete_company_news($id) {
        $id = getArrayFromString($id);

		$sql = 'DELETE FROM seller_news WHERE id_news IN (' . implode(',', array_fill(0, count($id), '?')) . ')';
		return $this->db->query($sql, $id);
	}

	public function delete_company_news_comments($id) {
        $id = getArrayFromString($id);

		$sql = 'DELETE FROM seller_news_comments WHERE id_news IN (' . implode(',', array_fill(0, count($id), '?')) . ')';
		return $this->db->query($sql, $id);
	}

	public function delete_company_updates($id) {
        $id = getArrayFromString($id);

		$sql = 'DELETE FROM seller_updates WHERE id_update IN (' . implode(',', array_fill(0, count($id), '?')) . ')';
		return $this->db->query($sql, $id);
	}

	public function delete_company_documents($id) {
        $id = getArrayFromString($id);

		$sql = 'DELETE FROM seller_library WHERE id_file IN (' . implode(',', array_fill(0, count($id), '?')) . ')';
		return $this->db->query($sql, $id);
	}

	// User saved companies actions

	public function set_company_saved($data=array()){
		if(empty($data))
			return false;
		$this->db->insert($this->user_saved_companies, $data);
		return $this->db->last_insert_id();
	}

	function delete_saved_company($id_user, $id_company) {
		$this->db->where('user_id = ? AND company_id = ?', array($id_user, $id_company));
		return $this->db->delete($this->user_saved_companies);
	}

	function getSavedCompanies($id_user){
        $this->db->select("GROUP_CONCAT(company_id) as id_saved");
        $this->db->from($this->user_saved_companies);
        $this->db->where("user_id = ?", (int) $id_user);

		$record = $this->db->query_one();
		return $record['id_saved'];
	}

	function get_index_name_by_user($user){
		$sql = "SELECT index_name FROM company_base WHERE id_user=?";
		$temp = $this->db->query_one($sql, array($user));
		return $temp['index_name'];
	}

	function get_saved_sellers($conditions){
		extract($conditions);

		$where = array("usc.user_id = ?");
		$params = array($user_id);

		if(!empty($country)){
			$where[] = ' u.country=? ';
			$params[] = $country;

			if(!empty($state)){
				$where[] = ' u.state=? ';
				$params[] = $state;
			}

			if(!empty($city)){
				$where[] = ' u.city=? ';
				$params[] = $city;
			}
		}

		if(!empty($not_user_id)){
            $not_user_id = getArrayFromString($not_user_id);
			$where[] = " cb.id_user NOT IN (" . implode(',', array_fill(0, count($not_user_id), '?')) . ") ";
            array_push($params, ...$not_user_id);
		}

		$rel = '';
		if(!empty($keywords)){
			$order_by = " REL_tags DESC ";
			$where[] = " MATCH (u.fname, u.lname, u.email) AGAINST (?)";
			$params[] = $keywords;
			$rel = " , MATCH (u.fname, u.lname, u.email) AGAINST (?) as REL_tags";
            array_unshift($params, $keywords);
		}

		$sql = "SELECT  CONCAT(u.fname, ' ', u.lname) as user_name, u.idu as user_id, u.user_group, cb.name_company as company_name, u.user_photo, u.logged $rel
				FROM $this->user_saved_companies usc
				LEFT JOIN $this->company_base_table cb ON usc.company_id = cb.id_company
				LEFT JOIN $this->users_table u ON cb.id_user = u.idu";


		$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= " GROUP BY u.idu ";

		if($order_by)
			$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
    }

    public function get_count_sellers_by_industries()
    {
        $this->db->select("{$this->category_table}.category_id, {$this->category_table}.name, COUNT(*) AS count_sellers");
        $this->db->from($this->category_table);

        $this->db->join($this->relation_industry_table, "{$this->category_table}.category_id = {$this->relation_industry_table}.id_industry");
        $this->db->join($this->company_base_table, "{$this->relation_industry_table}.id_company = {$this->company_base_table}.id_company");
        $this->db->join($this->users_table, "{$this->users_table}.idu = {$this->company_base_table}.id_user");

        $this->db->where("{$this->category_table}.parent", 0);
        $this->db->where("{$this->users_table}.status", 'active');
        $this->db->where("{$this->users_table}.fake_user", '0');

        $this->db->groupby("{$this->category_table}.category_id");
        $this->db->orderby("{$this->category_table}.name ASC");

        return $this->db->get();
    }

    /**
     *
     * @param array $params
     *
     * @return int
     */
    public function countCompanies(array $params): int
    {
        $params = array_merge($params, ['columns' => 'COUNT(*) as countCompanies']);

        $queryResult = $this->findRecord(
            null,
            $this->company_base_table,
            null,
            null,
            null,
            $params
        );

        return (int) $queryResult['countCompanies'];
    }

    /************************************************ Scopes ************************************************/

    protected function scopeCompanyId(QueryBuilder $builder, int $id_company): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_company',
                $builder->createNamedParameter($id_company, ParameterType::INTEGER, $this->nameScopeParameter('id_company'))
            )
        );
    }

    /**
     * Scope query by company user id
     *
     * @param QueryBuilder $builder
     * @param int $userId
     *
     * @return void
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->get_company_table()}`.`id_user`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope seller videos query by resource.
     */
    protected function scopeSellerVideos(QueryBuilder $builder, array $identifiers): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                'id_video',
                $identifiers
            )
        );
    }

    /**
     * Scope query by company blocked status.
     *
     * @param QueryBuilder $builder
     * @param int $companyBlocked
     *
     * @return void
     */
    protected function scopeCompanyBlocked(QueryBuilder $builder, int $companyBlocked): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->company_base_table . '.`blocked`',
                $builder->createNamedParameter($companyBlocked, ParameterType::INTEGER, $this->nameScopeParameter('companyBlocked'))
            )
        );
    }

    /**
     * Scope query by user demo.
     *
     * @param QueryBuilder $builder
     * @param int $isFakeUser
     *
     * @return void
     */
    protected function scopeFakeUser(QueryBuilder $builder, int $isFakeUser): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->users_table . '.`fake_user`',
                $builder->createNamedParameter($isFakeUser, ParameterType::INTEGER, $this->nameScopeParameter('fakeUser'))
            )
        );
    }

    /**
     * Scope query by user model.
     *
     * @param QueryBuilder $builder
     * @param int $isModelUser
     *
     * @return void
     */
    protected function scopeModelUser(QueryBuilder $builder, int $isModelUser): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->users_table . '.`is_model`',
                $builder->createNamedParameter($isModelUser, ParameterType::INTEGER, $this->nameScopeParameter('modelUser'))
            )
        );
    }

    /**
     * Scope query by company type
     *
     * @param QueryBuilder $builder
     * @param string $companyType
     *
     * @return void
     */
    protected function scopeCompanyType(QueryBuilder $builder, string $companyType): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->get_company_table()}`.`type_company`",
                $builder->createNamedParameter($companyType, ParameterType::STRING, $this->nameScopeParameter('companyType'))
            )
        );
    }

    /**
     * Scope query by sellers ids
     *
     * @param QueryBuilder $builder
     * @param int[] $sellersIds
     *
     * @return void
     */
    protected function scopeSellersIds(QueryBuilder $builder, array $sellersIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->get_company_table()}`.`id_user`",
                array_map(
                    fn ($index, $sellerId) => $builder->createNamedParameter(
                        (int) $sellerId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("sellerID{$index}")
                    ),
                    array_keys($sellersIds),
                    $sellersIds
                )
            )
        );
    }

    /**
     * Scope query by user status.
     *
     * @param QueryBuilder $builder
     * @param string $userStatus
     *
     * @return void
     */
    protected function scopeUserStatus(QueryBuilder $builder, string $userStatus): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->users_table . '.`status`',
                $builder->createNamedParameter($userStatus, ParameterType::STRING, $this->nameScopeParameter('userStatus'))
            )
        );
    }

    /**
     * Scope matchmaking by certified
     *
     * @param QueryBuilder $builder
     * @param int $isCertified
     *
     * @return void
     */
    protected function scopeMatchmakingCertifiedSellers(QueryBuilder $builder, int $isCertified): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'IF(`users`.`user_group` IN (3, 6), 1, 0)',
                $builder->createNamedParameter($isCertified, ParameterType::INTEGER, $this->nameScopeParameter('isCertified'))
            )
        );
    }

    /**
     * Scope matchmaking by b2b requests
     *
     * @param QueryBuilder $builder
     * @param int $hasB2bRequests
     *
     * @return void
     */
    protected function scopeHasB2bRequests(QueryBuilder $builder, int $hasB2bRequests): void
    {
        $builder->andWhere(
            $hasB2bRequests ? $builder->expr()->isNotNull('`b2bRequests`.`hasB2bRequests`')
                            : $builder->expr()->isNull('`b2bRequests`.`hasB2bRequests`')
        );
    }


    /**
     * Scope query by user status.
     *
     * @param QueryBuilder $builder
     * @param array $industriesIds
     *
     * @return void
     */
    protected function scopeMatchmakingJoinB2bRequests(QueryBuilder $builder, array $industriesIds): void
    {
        $b2bRequestsQueryBuilder = $this->createQueryBuilder();
        $b2bRequestsQueryBuilder
            ->select(
                '`b2b_request`.`id_user`',
                'IF(`b2b_request`.`id_request` IS NULL, 0, 1) as hasB2bRequests',
            )
            ->from('b2b_request')
            ->leftJoin(
                'b2b_request',
                'b2b_request_relation_industry',
                'b2b_request_relation_industry',
                '`b2b_request`.`id_request` = `b2b_request_relation_industry`.`id_request`'
            )
            ->where(
                $b2bRequestsQueryBuilder->expr()->and(
                    $b2bRequestsQueryBuilder->expr()->eq(
                        '`b2b_request`.`b2b_active`',
                        $builder->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('b2bActive'))
                    ),
                    $b2bRequestsQueryBuilder->expr()->eq(
                        '`b2b_request`.`status`',
                        $builder->createNamedParameter('enabled', ParameterType::STRING, $this->nameScopeParameter('b2bStatus'))
                    ),
                    $b2bRequestsQueryBuilder->expr()->eq(
                        '`b2b_request`.`blocked`',
                        $builder->createNamedParameter(0, ParameterType::INTEGER, $this->nameScopeParameter('b2bBlocked'))
                    ),
                    $b2bRequestsQueryBuilder->expr()->in(
                        "`b2b_request_relation_industry`.`id_industry`",
                        array_map(
                            fn ($index, $industryId) => $builder->createNamedParameter(
                                (int) $industryId,
                                ParameterType::INTEGER,
                                $this->nameScopeParameter("b2bRequestIndustryId{$index}")
                            ),
                            array_keys($industriesIds),
                            $industriesIds
                        )
                    )
                )
            )
            ->groupBy('`b2b_request`.`id_user`')
        ;

        $builder
            ->leftJoin(
                $this->users_table,
                "({$b2bRequestsQueryBuilder->getSQL()})",
                'b2bRequests',
                "`{$this->users_table}`.`idu` = `b2bRequests`.`id_user`"
            )
        ;
    }

    /**
     * Scope for join with companies.
     */
    protected function bindCompanies(QueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                $this->relation_industry_table,
                $this->company_base_table,
                $this->company_base_table,
                "`{$this->company_base_table}`.`{$this->company_base_primary_key}` = `{$this->relation_industry_table}`.`id_company`"
            )
        ;
    }

    /**
     * Scope for join with users.
     */
    protected function bindUsers(QueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                $this->company_base_table,
                $this->users_table,
                $this->users_table,
                "`{$this->users_table}`.`idu` = `{$this->company_base_table}`.`id_user`"
            )
        ;
    }

    /**
     * Scope for join with users country.
     */
    protected function bindUsersCountry(QueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                $this->users_table,
                $this->country_table,
                $this->country_table,
                "`{$this->users_table}`.`country` = `{$this->country_table}`.`id`"
            )
        ;
    }

    /**
     * Scope for join with company relation industry
     */
    protected function bindRelationIndustry(QueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                $this->get_company_table(),
                $this->relation_industry_table,
                $this->relation_industry_table,
                "`{$this->get_company_table()}`.`id_company` = `{$this->relation_industry_table}`.`id_company`"
            )
        ;
    }
}
