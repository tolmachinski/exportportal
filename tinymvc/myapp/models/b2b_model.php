<?php
/**
 * branch_model.php
 *
 * company model
 *
 * @author Andrei Cravciuc
 */

use App\Common\Database\BaseModel;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

class B2b_Model extends BaseModel {

	var $obj;
	private $b2b_request = "b2b_request";
	private $b2bRequestPrimaryKey = "id_request";
	private $b2b_responses = "b2b_responses";
	private $b2b_partners = "b2b_partners";
	private $b2b_advices = "b2b_request_advices";
    private $b2bAvicesPrimaryKey = "id_advice";
	private $b2b_advice_helpful = "b2b_advice_user_helpful";
	private $b2b_followers_table = "b2b_followers";
	private $relation_category_table = "company_relation_category";
	private $relation_industry_table = "company_relation_industry";
	private $b2b_relation_category_table = "b2b_request_relation_category";
	private $b2b_relation_industry_table = "b2b_request_relation_industry";
	private $b2bRelationIndustryPrimaryKey = "id_relation";
	private $category_table = "item_category";
	private $country_table = "port_country";
	private $users_table = "users";
	private $users_groups_table = "user_groups";
	private $city_table = "zips";
	private $states_table = "states";
	private $zips_table = "zips";
	private $user_groups_table = "user_groups";
	private $seller_shipper_partners_table = 'seller_shipper_partners';
    private $b2bPartnersTypeTable = "partners_type";
    private $b2bPartnersTypePrimaryKey = "id_type";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    public function getB2bRequestTable(): string
    {
        return $this->b2b_request;
    }

    public function getB2bRequestPrimaryKey(): string
    {
        return $this->b2bRequestPrimaryKey;
    }

    public function getB2bRequestRelationIndustryTable(): string
    {
        return $this->b2b_relation_industry_table;
    }

    public function getB2bRequestRelationIndustryPrimaryKey(): string
    {
        return $this->b2bRelationIndustryPrimaryKey;
    }

    public function getB2bAdvicesTable(): string
    {
        return $this->b2b_advices;
    }

    public function getB2bAdvicesPrimaryKey(): string
    {
        return $this->b2bAvicesPrimaryKey;
    }

    public function getB2bPartnersTypeTable(): string
    {
        return $this->b2bPartnersTypeTable;
    }

    public function getB2bPartnersTypePrimaryKey(): string
    {
        return $this->b2bPartnersTypePrimaryKey;
    }

	function set_b2b_request($data){
		$this->db->insert($this->b2b_request, $data);
		return $this->db->last_insert_id();
	}

	function update_b2b_request($id_request, $data){
		$this->db->where('id_request', $id_request);
		return $this->db->update($this->b2b_request, $data);
	}

	function update_response($company, $partner, $data){
		$this->db->where('id_company', $company);
		$this->db->where('id_partner', $partner);
		return $this->db->update($this->b2b_responses, $data);
	}

	function get_request_id_from_response($company, $partner){
		$sql = "SELECT id_request
				FROM $this->b2b_responses
				WHERE id_company = ? AND id_partner = ?";
		$res = $this->db->query_one($sql, array($company, $partner));
		return $res['id_request'];
	}

	public function set_partners($data){
		$this->db->insert_batch($this->b2b_partners, $data);
		return $this->db->getAffectableRowsAmount();
	}

	public function update_request_viewed_counter($id){
		$sql = "UPDATE $this->b2b_request
				SET viewed_count = viewed_count + 1
				WHERE id_request = ?";
		return $this->db->query($sql, array($id));
	}

	public function get_partners($conditions){
		$where = array();
		$params = array();
		$page = 0;
		$per_p = 20;
		$order_by = "bp.date_partnership DESC";
		extract($conditions);

		if (isset($added_start)) {
			$where[] = " DATE(bp.date_partnership) >= ?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(bp.date_partnership) <= ?";
			$params[] = $added_finish;
		}

		if(isset($id_partner)){
			$where[] = " bp.id_partner = ? ";
			$params[] = $id_partner;
		}

		if(isset($id_country)){
			$where[] = " cb.id_country = ? ";
			$params[] = $id_country;
		}

		if(isset($id_city)){
			$where[] = " cb.id_city = ? ";
			$params[] = $id_city;
		}

		if (isset($companies_list)) {
            $companies_list = getArrayFromString($companies_list);
			$where[] = ' bp.id_company IN ( '. implode(',', array_fill(0, count($companies_list), '?')) . ' ) ';
            array_push($params, ...$companies_list);
        }

		$rel = "";
		if(isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by = $order_by . ", REL DESC";
				$where[] = " MATCH(for_search) AGAINST ( ? ) ";
				$params[] = $keywords;
				$rel = " , MATCH (for_search) AGAINST ( ? ) as REL ";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (for_search LIKE ?) ";
                $params[] = '%' . $keywords . '%';
			}
		}

		if (isset($sort_by)) {
			foreach ($sort_by as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		$sql = "SELECT
						bp.*,
						cb.name_company, cb.logo_company, cb.index_name, cb.address_company, cb.email_company, cb.phone_company, cb.zip_company,
						cb.id_user, cb.id_country, cb.id_city, cb.id_state, cb.type_company, cb.description_company,
						cb.rating_count_company, cb.rating_company, cb.registered_company, cb.logo_company,
						u.user_group, u.status as user_status, u.registration_date, CONCAT(u.fname, ' ', u.lname) as user_name,
						pc.country
						$rel
				FROM $this->b2b_partners bp
				LEFT JOIN company_base cb ON bp.id_partner = cb.id_company
				LEFT JOIN $this->country_table pc ON cb.id_country = pc.id
				LEFT JOIN $this->users_table u ON cb.id_user = u.idu ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		if(isset($group_by) && $group_by)
			$sql .= " GROUP BY bp.id_partner ";

		$sql .= " ORDER BY " . $order_by;

		if(isset($count)){
			$pages = ceil($count/$per_p);

			if ($page > $pages) $page = $pages;
			$start = ($page-1)*$per_p;

			if($start < 0) $start = 0;

			$sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
		}

		if(isset($limit)){
			$sql .= " LIMIT " . $limit ;
		}elseif(isset($from)){
			$sql .= " LIMIT " . (int) $from . ',' . (int) $per_p ;
		}

		return $this->db->query_all($sql, $params);
	}

	public function get_count_partners($conditions){
		$where = array();
		$params = array();
		extract($conditions);

		if (isset($added_start)) {
			$where[] = " DATE(bp.date_partnership) >= ?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(bp.date_partnership) <= ?";
			$params[] = $added_finish;
		}

		if(isset($id_country)){
			$where[] = " cb.id_country = ? ";
			$params[] = $id_country;
		}

		if(isset($id_city)){
			$where[] = " cb.id_city = ? ";
			$params[] = $id_city;
		}

		if(isset($id_partner)){
			$where[] = " bp.id_partner = ? ";
			$params[] = $id_partner;
		}

		if (isset($companies_list)) {
            $companies_list = getArrayFromString($companies_list);
			$where[] = ' bp.id_company IN ( '. implode(',', array_fill(0, count($companies_list), '?')) . ' ) ';
            array_push($params, ...$companies_list);
        }

		if (isset($keywords)) {
			$where[] = " MATCH(for_search) AGAINST ( ? ) ";
			$params[] = $keywords;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->b2b_partners." bp
				LEFT JOIN company_base cb ON bp.id_partner = cb.id_company";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$rez = $this->db->query_one($sql, $params);

		return $rez['counter'];
	}

	function get_b2b_partners($conditions = array())
	{
		$order_by = " sp.date_partner DESC ";
		$limit = 10;
		$offset = 0;
		$params = array();

		extract($conditions);

		$this->db->select('sp.*, cb.rating_company, cb.id_company, cb.logo_company, cb.name_company, cb.address_company, cb.email_company, cb.phone_company, cb.id_country, cb.id_state, cb.id_city,
						u.user_group, u.status as user_status');
		$this->db->from("{$this->seller_shipper_partners_table} sp");
		$this->db->join("company_base cb", 'sp.id_seller = cb.id_user', 'left');
		$this->db->join("{$this->users_table} u", 'cb.id_user = u.idu', 'left');

		if (isset($sort_by)) {
			foreach ($sort_by as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($added_start)) {
			$this->db->where('DATE(sp.date_partner) >= ?', $added_start);
		}

		if (isset($added_finish)) {
			$this->db->where('DATE(sp.date_partner) <= ?', $added_finish);
		}

		if (isset($id_country)) {
			$this->db->where('cb.id_country', $id_country);
		}

		if (isset($id_partner)) {
			$this->db->where('bp.id_partner', $id_partner);
		}

		if (isset($keywords)) {
			$this->db->where_raw('cb.name_company LIKE ?', "%{$keywords}%");
		}

		if (isset($id_shipper)) {
			$this->db->where('id_shipper', $id_shipper);
		}

		if (isset($id_seller)) {
			$this->db->where('id_seller', $id_seller);
		}

		if (isset($are_partners)) {
			$this->db->where('are_partners', $are_partners);
		}

		$this->db->where('cb.type_company', 'company');

		$this->db->orderby($order_by);

        $limit = (int) ($per_p ?? $limit);
        $offset = (int) ($start ?? $offset);

		$this->db->limit($limit, $offset);

		return $this->db->query_all();
	}

	function count_b2b_partners($conditions = array())
	{
		extract($conditions);

		$this->db->select('COUNT(*) as counter');
		$this->db->from("{$this->seller_shipper_partners_table} sp");
		$this->db->join("company_base cb", 'sp.id_seller = cb.id_user', 'left');
		$this->db->join("{$this->users_table} u", 'cb.id_user = u.idu', 'left');

		if (isset($added_start)) {
			$this->db->where('DATE(sp.date_partner) >= ?', $added_start);
		}

		if (isset($added_finish)) {
			$this->db->where('DATE(sp.date_partner) <= ?', $added_finish);
		}

		if (isset($id_country)) {
			$this->db->where('cb.id_country', $id_country);
		}

		if (isset($id_partner)) {
			$this->db->where('bp.id_partner', $id_partner);
		}

		if (isset($keywords)) {
			$this->db->where_raw('cb.name_company LIKE ?', "%{$keywords}%");
		}

		if (isset($id_shipper)) {
			$this->db->where('id_shipper', $id_shipper);
		}

		if (isset($id_seller)) {
			$this->db->where('id_seller', $id_seller);
		}

		if (isset($are_partners)) {
			$this->db->where('are_partners', $are_partners);
		}

		$this->db->where('cb.type_company', 'company');

		$rez = $this->db->query_one();

		return $rez['counter'];
	}

    /**
     * Method to get the b2b requests
     *
     * @param array $conditions
     *
     * @return array
     */
    public function getB2bRequests(array $conditions = []): array
    {
        return $this->findRecords(
            null,
            $this->getB2bRequestTable(),
            $this->getB2bRequestTable(),
            $conditions
        );
    }
    /**
     * @deprecated
     */
	public function get_b2b_request($id_request, array $conditions = [])
    {
        $where = [];
        $params = [];

        $where[] = " b.id_request = ? ";
        $params[] = $id_request;

        extract($conditions);

        if (isset($id_user)) {
			$where[] = " b.id_user = ? ";
			$params[] = $id_user;
		}

        $sql = "SELECT b.*,
					cb.accreditation, cb.name_company,cb.description_company, cb.latitude, cb.longitude, cb.parent_company, cb.logo_company, cb.index_name, cb.type_company, cb.id_company, cb.address_company, cb.zip_company,
					cb.id_user, cb.id_country as c_country, cb.id_state as c_state, cb.id_city as c_city,cb.video_company as c_video, pt.name as p_type, u.lname, u.fname, bp.photo
				FROM ".$this->b2b_request." b
				LEFT JOIN b2b_request_photos bp ON b.id_request = bp.request_id
				LEFT JOIN company_base cb ON b.id_company = cb.id_company
				LEFT JOIN partners_type pt ON b.id_type = pt.id_type
				LEFT JOIN users u ON u.idu = cb.id_user";

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params);
	}

	public function get_simple_b2b_request($id_request){
		$sql = "SELECT *
				FROM ".$this->b2b_request."
				WHERE id_request = ?";
		return $this->db->query_one($sql, array($id_request));
	}

	public function is_my_request($id_request, $id_user){
		$sql = "	SELECT COUNT(*) as counter
					FROM ".$this->b2b_request."
					WHERE id_request = ? AND id_user = ?";
		$res = $this->db->query_one($sql, array($id_request, $id_user));
		return $res['counter'];
	}

	public function request_status($id_request, $id_user){
		$sql = "	SELECT status
					FROM ".$this->b2b_request."
					WHERE id_request = ? AND id_user = ?";
		$res = $this->db->query_one($sql, array($id_request, $id_user));
		return $res['status'];
	}

	public function update_request($id_request, $data){
		$this->db->where('id_request', $id_request);
		return $this->db->update($this->b2b_request, $data);
	}

	public function delete_request($conditions){
        if (empty($conditions['id_request']) && empty($conditions['requests_list']) && empty($conditions['id_user'])) {
            return false;
        }

		if (!empty($conditions['id_request'])) {
			$this->db->where('id_request', $conditions['id_request']);
        }

		if (!empty($conditions['requests_list'])) {
			$this->db->in('id_request', $conditions['requests_list'], true);
        }

		if (!empty($conditions['id_user'])) {
			$this->db->where('id_user', $conditions['id_user']);
        }

		return $this->db->delete($this->b2b_request);
	}

	public function exist_request($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}
		if(isset($id_request)){
			$where[] = " id_request = ? ";
			$params[] = $id_request;
		}

		$sql = "	SELECT COUNT(*) as counter
					FROM ".$this->b2b_request;
		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$res = $this->db->query_one($sql, $params);
		return $res['counter'];
	}

	public function isMyPartner($id_company, $id_partner){
		$sql = "	SELECT COUNT(*) as counter
					FROM ".$this->b2b_partners."
					WHERE id_company = ? AND id_partner = ?";
		$res = $this->db->query_one($sql, array($id_company, $id_partner));
		return $res['counter'];
	}

	public function delete_response($conditions){
		extract($conditions);
		if(isset($response))
			$this->db->where('id_response', $response);

		if(isset($id_request))
			$this->db->where('id_request', $id_request);

		if(isset($requests_list))
			$this->db->in('id_request', $requests_list, true);

		if(isset($company) && isset($partner))
			$this->db->where('id_company=? && id_partner=?', array($company, $partner));

		return $this->db->delete($this->b2b_responses);
	}

	public function delete_partner($company, $partner){
		$this->db->where('id_company=? AND id_partner=?', array($company, $partner));
		$this->db->or_where('id_company=? AND id_partner=?', array($partner, $company));
		return $this->db->delete($this->b2b_partners);
	}

	public function get_company_partners($company){
		$sql = "SELECT bp.*, cb.id_user as user_partner
				FROM $this->b2b_partners bp
				LEFT JOIN company_base cb ON bp.id_partner = cb.id_company
				WHERE bp.id_company=?";
		return $this->db->query_all($sql, array($company));
	}

	public function delete_company_partners($company){
		$this->db->where('id_company=? OR id_partner=?', array($company, $company));
		return $this->db->delete($this->b2b_partners);
	}

	public function exist_response($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_response)){
			$where[] = " id_response = ? ";
			$params[] = $id_response;
		}

		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($id_request)){
			$where[] = " id_request = ? ";
			$params[] = $id_request;
		}

		if(isset($id_partner)){
			$where[] = " id_partner = ? ";
			$params[] = $id_partner;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->b2b_responses;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$res = $this->db->query_one($sql, $params);

		return $res['counter'];
	}

	public function exist_partnership($company, $partner){
		$sql = "	SELECT COUNT(*) as counter
					FROM ".$this->b2b_partners."
					WHERE id_company = ? AND id_partner =? ";
		$res = $this->db->query_one($sql, array($company,$partner));
		return $res['counter'];
	}

	function exist_shipper_partnership($conditions = array()) {
		extract($conditions);

		$this->db->select('COUNT(*) as counter');
		$this->db->from($this->seller_shipper_partners_table);

		if (isset($id_shipper)) {
			$this->db->where('id_shipper', $id_shipper);
		}

		if (isset($id_seller)) {
			$this->db->where('id_seller', $id_seller);
		}

		if (isset($id_partner)) {
			$this->db->where('id_partner', $id_partner);
		}

		$result = $this->db->query_one();
		return $result['counter'];
	}

	public function update_shipper_partner($id_partner, $data) {
		$this->db->where('id_partner', $id_partner);
		return $this->db->update($this->seller_shipper_partners_table, $data);
	}

	public function delete_shipper_partner($id_partner)
	{
		$this->db->where('id_partner', $id_partner);
		return $this->db->delete($this->seller_shipper_partners_table);
	}

	public function is_for_me_response($id_response, $id_user){
		$sql = "	SELECT COUNT(*) as counter
					FROM ".$this->b2b_responses." bp
					LEFT JOIN company_base cb ON bp.id_company = cb.id_company
					WHERE cb.id_user = ? AND bp.id_response = ? ";
		$res = $this->db->query_one($sql, array($id_user, $id_response));
		return $res['counter'];
	}

	public function get_response($id_response, array $conditions = []){
        $where = [];
        $params = [];

        $where[] = " b.id_response = ? ";
        $params[] = $id_response;

        extract($conditions);

        if (isset($id_user)) {
			$where[] = " cb.id_user = ? ";
			$params[] = $id_user;
		}

		$sql = "SELECT b.*,
                    cb.id_user
                FROM ".$this->b2b_responses." b
                LEFT JOIN company_base cb ON b.id_partner = cb.id_company";

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params);
	}

	public function get_response_by_partnership($company, $partner){
		$sql = "	SELECT *
					FROM ".$this->b2b_responses."
					WHERE id_company = ? AND id_partner";
		return $this->db->query_one($sql, array($company, $partner));
	}

	public function get_popular_requests($conditions){
		$page = 0;
		$per_p = 20;
		$order_by = "b.viewed_count DESC";
		$where = array();
		$params = array();
		$status = 'enabled';

		extract($conditions);

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if($status != 'all'){
			$where[] = " b.status = ? ";
			$params[] = $status;
		}

		if(isset($blocked)){
            $where[] = " b.blocked = ? ";
            $params[] = $blocked;
        }

		$sql = "SELECT
					b.*, cb.name_company
				FROM {$this->b2b_request} b
				LEFT JOIN company_base cb ON b.id_company = cb.id_company";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$sql .= " GROUP BY b.id_request ORDER BY {$order_by} ";

		$pages = ceil($count/$per_p);

		if ($page > $pages){
			$page = $pages;
		}

		$start = ($page-1)*$per_p;

		if($start < 0){
			$start = 0;
		}

		$sql .= " LIMIT {$start} ";

		if($per_p > 0){
			$sql .= " , {$per_p}";
		}

		return $this->db->query_all($sql, $params);
	}

	public function get_company_b2b_requests($id_company){
		$sql = "SELECT *
				FROM $this->b2b_request
				WHERE id_company = ?";

		return $this->db->query_all($sql, array($id_company));

	}

	public function count_b2b_response($requests){
		$where = array();
		$params = array();

        $requests = getArrayFromString($requests);

        $where[] = " breq.id_request IN (" . implode(',', array_fill(0, count($requests), '?')) . ") ";
        array_push($params, ...$requests);

		$sql = "SELECT breq.id_request, COUNT(*) as counters
			FROM b2b_responses bres
			LEFT JOIN b2b_request breq ON breq.id_request = bres.id_request";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY breq.id_request ";
		return $this->db->query_all($sql, $params);
	}

	public function get_b2b_requests($conditions){
		$page = 0;
		$per_p = 20;
		$order_by = "b.id_request DESC";
		$where = array();
		$params = array();
		$status = 'enabled';

		extract($conditions);

		if(isset($blocked)){
            $where[] = " cb.blocked = ? ";
            $params[] = $blocked;
        }

		if(isset($company_visible)){
            $where[] = " cb.visible_company = ? ";
            $params[] = $company_visible;
        }

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(!empty($sort_by)){
			switch($sort_by){
				case 'c_name_asc': $order_by = 'cb.name_company ASC'; break;
				case 'c_name_desc': $order_by = 'cb.name_company DESC'; break;
				case 'date_reg_asc': $order_by = 'b.b2b_date_register ASC'; break;
				case 'date_reg_desc': $order_by = 'b.b2b_date_register DESC'; break;
				case 'rand': $order_by = ' RAND()'; break;
			}
		}

		if($status != 'all'){
			$where[] = " b.status = ? ";
			$params[] = $status;
		}

		if(isset($requests_list)){
            $requests_list = getArrayFromString($requests_list);
			$where[] = " b.id_request IN (" . implode(',', array_fill(0, count($requests_list), '?')) . ") ";
            array_push($params, ...$requests_list);
		}

        if(isset($id_user)){
			$where[] = " b.id_user = ? ";
			$params[] = $id_user;
		}

		if(isset($id_company)){
			$where[] = " b.id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($country)){
			$where[] = " b.id_country = ? ";
			$params[] = $country;
		}

		if(isset($city)){
			$where[] = " b.id_city = ? ";
			$params[] = $city;
		}

		if(isset($type)){
			$where[] = " b.id_type = ?";
			$params[] = $type;
		}

		if(isset($b2b_active)){
			$where[] = " b.b2b_active = ?";
			$params[] = $b2b_active;
		}

		$rel = "";
		if(isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by .= ", REL DESC";
				$where[] = " MATCH (b.b2b_title, b.b2b_message, b.b2b_tags) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (b.b2b_title, b.b2b_message, b.b2b_tags) AGAINST (?) as REL ";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (b.b2b_title LIKE ? OR b.b2b_message LIKE ? OR b.b2b_tags LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
		}

		if(isset($radius)){
			$where[] = " b.b2b_radius <= ?";
			$params[] = $radius;
		}
		if(isset($zip)){
			$where[] = " b.b2b_zip = ? ";
			$params[] = $zip;
		}

		$sql = "SELECT b.*,
					cb.name_company, cb.type_company, cb.latitude, cb.longitude, cb.parent_company, cb.logo_company, cb.index_name, cb.id_company, cb.address_company, cb.zip_company,
					cb.id_user, cb.id_country as c_country, cb.id_state as c_state, cb.id_city as c_city,
					COUNT(ba.id_advice) as count_advices
					$rel
				FROM ".$this->b2b_request." b
				LEFT JOIN company_base cb ON b.id_company = cb.id_company
				LEFT JOIN ".$this->b2b_advices." ba ON b.id_request = ba.id_request";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		$sql .= " GROUP BY b.id_request ORDER BY " . $order_by;

		$pages = ceil($count/$per_p);

		if ($page > $pages) $page = $pages;
		$start = ($page-1)*$per_p;

		if($start < 0) $start = 0;

		$sql .= " LIMIT " . $start ;

		if($per_p > 0)
			$sql .= "," . $per_p;

		return $this->db->query_all($sql, $params);

	}

	public function count_b2b_requests($conditions){
		$where = array();
		$params = array();
		$status = 'enabled';

		extract($conditions);

		if(isset($blocked)){
            $where[] = " cb.blocked = ? ";
            $params[] = $blocked;
        }

		if(isset($company_visible)){
            $where[] = " cb.visible_company = ? ";
            $params[] = $company_visible;
        }

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if($status != 'all'){
			$where[] = " b.status = ? ";
			$params[] = $status;
		}

		if(isset($requests_list)){
            $requests_list = getArrayFromString($requests_list);
			$where[] = " b.id_request IN (" . implode(',', array_fill(0, count($requests_list), '?')) . ") ";
            array_push($params, ...$requests_list);
		}

		if(isset($id_user)){
			$where[] = " b.id_user = ? ";
			$params[] = $id_user;
		}

		if(isset($id_company)){
			$where[] = " b.id_company = ? ";
			$params[] = $id_company;
		}

		if(isset($country)){
			$where[] = " b.id_country = ? ";
			$params[] = $country;
		}

		if(isset($city)){
			$where[] = " b.id_city = ? ";
			$params[] = $city;
		}

		if(isset($type)){
			$where[] = " b.id_type = ?";
			$params[] = $type;
		}

		if(isset($b2b_active)){
			$where[] = " b.b2b_active = ?";
			$params[] = $b2b_active;
		}

		if(isset($keywords)){
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (b.b2b_title, b.b2b_message, b.b2b_tags) AGAINST (?)";
				$params[] = $keywords;
			} else{
				$where[] = " (b.b2b_title LIKE ? OR b.b2b_message LIKE ? OR b.b2b_tags LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
		}

		if(isset($radius)){
			$where[] = " b.b2b_radius <= ?";
			$params[] = $radius;
		}

		if(isset($zip)){
			$where[] = " b.b2b_zip = ? ";
			$params[] = $zip;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->b2b_request." b
				LEFT JOIN company_base cb ON cb.id_company = b.id_company ";

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		$rez = $this->db->query_one($sql, $params);

		return $rez['counter'];

	}

	public function get_request_responses($conditions){
		$where = array();
		$params = array();
		$order_by = 'b.date_partner DESC';
		extract($conditions);

		if(isset($id_request)){
			$where[] = " b.id_request = ? ";
			$params[] = $id_request;
		}

		if(isset($status)){
			$where[] = " b.status = ? ";
			$params[] = $status;
		}

		$sql = "SELECT b.*,
					cb.id_user, cb.name_company, cb.parent_company, cb.logo_company, cb.index_name, cb.type_company, cb.id_country as c_country,
					pc.country
				FROM ".$this->b2b_responses." b
				LEFT JOIN company_base cb ON b.id_partner = cb.id_company
				LEFT JOIN port_country pc ON cb.id_country = pc.id";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		if(isset($group_by))
			$sql .= " GROUP BY ".$group_by;

		$sql .= " ORDER BY ".$order_by;

		if(isset($limit)){
		  $sql .= " LIMIT " . $limit ;
		} else{
			if(!isset($count))
				$count = $this->count_request_responses($conditions);

			$pages = ceil($count/$per_p);

			if(!isset($start)){
				if ($page > $pages) $page = $pages;
				$start = ($page-1)*$per_p;

				if($start < 0) $start = 0;
			}

			$sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
		}

		return $this->db->query_all($sql, $params);
	}

	public function count_request_responses($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($id_request)){
			$where[] = " id_request = ? ";
			$params[] = $id_request;
		}

		if(isset($status)){
			$where[] = " status = ? ";
			$params[] = $status;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->b2b_responses;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function set_request_relation_industry($id_request,$data){
		$sql = "INSERT INTO " . $this->b2b_relation_industry_table . "
				(`id_request`, `id_industry`) VALUES ";

		foreach ($data as $ind)
			$inds[] = "(" . (int) $id_request . "," . (int) $ind . ")";

		$sql .= implode(',', $inds);

		$this->db->query($sql);
		return $this->db->getAffectableRowsAmount();
	}

	public function set_request_relation_category($id_request,$data){
		$sql = "INSERT INTO " . $this->b2b_relation_category_table . "
				(`id_request`, `id_category`) VALUES ";

		foreach ($data as $ind)
			$inds[] = "(" . (int) $id_request . "," . (int) $ind . ")";

		$sql .= implode(',', $inds);

		$this->db->query($sql);
		return $this->db->getAffectableRowsAmount();
	}

	public function delete_request_relation_industry($id_request) {
		$this->db->in('id_request',$id_request, true);

		return $this->db->delete($this->b2b_relation_industry_table);
	}

	public function delete_request_relation_category($id_request) {
		$this->db->in('id_request', $id_request, true);

		return $this->db->delete($this->b2b_relation_category_table);
	}

    /**
     *
     * @deprecated 2022-05-31
     */
	public function count_b2b_category($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($blocked)){
            $where[] = " cb.blocked = ? ";
            $params[] = $blocked;
        }

		if(isset($company_visible)){
            $where[] = " cb.visible_company = ? ";
            $params[] = $company_visible;
        }

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(isset($b2b_active)){
            $where[] = " b2b.b2b_active = ? ";
            $params[] = $b2b_active;
        }

		if(isset($type)){
			$where[] = " b2b.id_type = ? ";
			$params[] = $type;
		}

		if(isset($industry)){
			$where[] = " ic.parent = ? AND ri.id_industry = ? ";
            array_push($params, ...array_fill(0, 2, $industry));
		}
		if(isset($category)){
			$where[] = " rc.id_category = ? ";
			$params[] = $category;
		}

		if(isset($country)){
			$where[] = " b2b.id_country = ? ";
			$params[] = $country;
		}
		if(isset($state)){
			$where[] = " b2b.id_state = ? ";
			$params[] = $state;
		}
		if(isset($city)){
			$where[] = " b2b.id_city = ? ";
			$params[] = $city;
		}

		if(isset($keywords)){
			$where[] = " MATCH (b2b.b2b_title, b2b.b2b_message, b2b.b2b_tags) AGAINST (?)";
			$params[] = $keywords;
		}

		if(isset($radius)){
			$where[] = " b2b.b2b_radius <= ?";
			$params[] = $radius;
		}
		if(isset($zip)){
			$where[] = " b2b.b2b_zip = ? ";
			$params[] = $zip;
		}
		$sql = "SELECT rc.id_category, ic.name, ic.parent, ici.name as industry_name, count(DISTINCT b2b.id_request) as counter
				FROM ".$this->b2b_request." b2b
				LEFT JOIN ".$this->b2b_relation_category_table." rc ON b2b.id_request = rc.id_request
				LEFT JOIN ".$this->b2b_relation_industry_table." ri ON b2b.id_request = ri.id_request
				LEFT JOIN ".$this->category_table." ic ON rc.id_category = ic.category_id
				LEFT JOIN ".$this->category_table." ici ON ic.parent = ici.category_id
				LEFT JOIN company_base cb ON cb.id_company = b2b.id_company AND cb.type_company = 'company' ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY rc.id_category
				ORDER BY counter DESC, industry_name ASC";

		return $this->db->query_all($sql, $params);
	}

    /**
     *
     * @deprecated 2022-05-31
     */
	public function count_b2b_industry($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($blocked)){
            $where[] = " cb.blocked = ? ";
            $params[] = $blocked;
        }

		if(isset($company_visible)){
            $where[] = " cb.visible_company = ? ";
            $params[] = $company_visible;
        }

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(isset($b2b_active)){
            $where[] = " b2b.b2b_active = ? ";
            $params[] = $b2b_active;
        }

		if(isset($type)){
			$where[] = " b2b.id_type = ? ";
			$params[] = $type;
		}

		if(isset($industry)){
			$where[] = " ri.id_industry = ? ";
			$params[] = $industry;
		}
		if(isset($category)){
			$where[] = " rc.id_category = ? ";
			$params[] = $category;
		}

		if(isset($country)){
			$where[] = " b2b.id_country = ? ";
			$params[] = $country;
		}
		if(isset($state)){
			$where[] = " b2b.id_state = ? ";
			$params[] = $state;
		}
		if(isset($city)){
			$where[] = " b2b.id_city = ? ";
			$params[] = $city;
		}

		if(isset($keywords)){
			$where[] = " MATCH (b2b.b2b_title, b2b.b2b_message, b2b.b2b_tags) AGAINST (?)";
			$params[] = $keywords;
		}

		if(isset($radius)){
			$where[] = " b2b.b2b_radius <= ?";
			$params[] = $radius;
		}
		if(isset($zip)){
			$where[] = " b2b.b2b_zip = ? ";
			$params[] = $zip;
		}

		$sql = "SELECT ri.id_industry, ic.name, ic.parent, count(DISTINCT b2b.id_request) as counter
				FROM ".$this->b2b_request." b2b
				LEFT JOIN ".$this->b2b_relation_category_table." rc ON b2b.id_request = rc.id_request
				LEFT JOIN ".$this->b2b_relation_industry_table." ri	ON b2b.id_request = ri.id_request
				LEFT JOIN ".$this->category_table." ic ON ri.id_industry = ic.category_id
				LEFT JOIN company_base cb ON cb.id_company = b2b.id_company AND cb.type_company = 'company'
				WHERE ic.parent = 0 ";

		if(count($where))
			$sql .= " AND " . implode(" AND", $where);

		$sql .= " GROUP BY ri.id_industry
				ORDER BY counter DESC, ic.name ASC ";
		//echo $sql;
		return $this->db->query_all($sql, $params);
	}

    /**
     *
     * @deprecated 2022-05-31
     */
	public function count_b2b_countries($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($blocked)){
            $where[] = " cb.blocked = ? ";
            $params[] = $blocked;
        }

		if(isset($company_visible)){
            $where[] = " cb.visible_company = ? ";
            $params[] = $company_visible;
        }

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(isset($b2b_active)){
            $where[] = " b2b.b2b_active = ? ";
            $params[] = $b2b_active;
        }

		if(isset($type)){
			$where[] = " b2b.id_type = ? ";
			$params[] = $type;
		}

		if(isset($country)){
			$where[] = " b2b.id_country = ? ";
			$params[] = $country;
		}
		if(isset($state)){
			$where[] = " b2b.id_state = ? ";
			$params[] = $state;
		}
		if(isset($city)){
			$where[] = " b2b.id_city = ? ";
			$params[] = $city;
		}
		if(isset($zip)){
			$where[] = " b2b.b2b_zip = ? ";
			$params[] = $zip;
		}

		if(isset($keywords)){
			$where[] = " MATCH (b2b.b2b_title, b2b.b2b_message, b2b.b2b_tags) AGAINST (?)";
			$params[] = $keywords;
		}

		if(isset($radius)){
			$where[] = " b2b.b2b_radius <= ?";
			$params[] = $radius;
		}

		$sql = "SELECT c.*, count(DISTINCT b2b.id_country) as counter
				FROM ".$this->b2b_request." b2b
				LEFT JOIN ".$this->country_table." c ON b2b.id_country = c.id
				LEFT JOIN company_base cb ON cb.id_company = b2b.id_company AND cb.type_company = 'company' ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY c.id
				  ORDER BY c.country ASC";

		return $this->db->query_all($sql, $params);
	}

    /**
     * @deprecated 2022-05-31
     */
	public function b2b_states_search($country){
		$sql = "SELECT DISTINCT s.*
				FROM ".$this->b2b_request." b2b
				LEFT JOIN ".$this->states_table." s ON b2b.id_state > 0 AND b2b.id_state = s.id
				WHERE s.id_country = $country ";
		return $this->db->query_all($sql);
	}

    /**
     * @deprecated 2022-05-31
     */
	public function b2b_city_by_country_search($country){
		$sql = "SELECT DISTINCT c.*
				FROM ".$this->b2b_request." b2b
				LEFT JOIN ".$this->city_table." c ON b2b.id_city = c.id
				WHERE c.id_country = $country ";
		return $this->db->query_all($sql);
	}

    /**
     * @deprecated 2022-05-31
     */
	public function b2b_city_by_state_search($state){
		$sql = "SELECT c.*
				FROM ".$this->b2b_request." b2b
				LEFT JOIN ".$this->zips_table." c ON b2b.id_state = c.state and b2b.id_city = c.id
				WHERE c.state = $state
				GROUP BY city";
		return $this->db->query_all($sql);
	}

	public function set_b2b_partner($data){
		$this->db->insert($this->b2b_responses, $data);
		return $this->db->last_insert_id();
	}

	public function iFollowed($id_request, $id_user){
		$sql = "SELECT COUNT(*) as counter
				   FROM b2b_followers
				 WHERE id_request = ? AND id_user = ?";
		$counter = $this->db->query_one($sql, array($id_request,$id_user));
		return $counter['counter'];
	}

	public function iRequested($id_request, $id_company, $id_partner){
		$sql = "SELECT COUNT(*) as counter
				   FROM ".$this->b2b_responses."
				 WHERE id_request = ? AND id_company = ? AND id_partner = ?";
		$counter = $this->db->query_one($sql, array($id_request, $id_company, $id_partner));
		return $counter['counter'];
	}

	public function isMyFollow($id_follower, $id_user){
		$sql = "SELECT COUNT(*) as counter
				   FROM b2b_followers
				 WHERE id_follower = ? AND id_user = ?";
		$counter = $this->db->query_one($sql, array($id_follower,$id_user));
		return $counter['counter'];
	}

	public function setFollower($data){
		if(!count($data))
			return false;
		$this->db->insert($this->b2b_followers_table, $data);
		return $this->db->last_insert_id();
	}

	public function updateFollower($conditions){
		if(is_array($conditions))
			extract($conditions);

		$this->db->where('id_follower', $id_follower);
		$this->db->where('id_user', $id_user);
		return $this->db->update($this->b2b_followers_table, array('notice_follower' => $notice_follower));
	}

	public function deleteFollowed($id_request, $id_user){
		$this->db->in('id_request', $id_request, true);
		$this->db->where('id_user', $id_user);
		return $this->db->delete($this->b2b_followers_table);
	}

	public function delete_followed_requests($requests_list){
		$this->db->in('id_request', $requests_list, true);
		return $this->db->delete($this->b2b_followers_table);
	}

	public function lastFollowersByDate($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_request)){
			$params[] = $id_request;
			$where[] = 'id_request = ?';
		}
		if(isset($date)){
			$params[] = $date;
			$where[] = 'DATE(date_follow) >= DATE(?)';
		}
		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->b2b_followers_table;
		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$counter = $this->db->query_one($sql, $params);
		return $counter['counter'];
	}

	public function getFollowers($conditions){
		$params = array();
		$where = array();
		$page = 0;
		$per_p = 3;

		extract($conditions);

		if(isset($id_request)){
			$params[] = $id_request;
			$where[] = 'id_request = ?';
		}
		if(isset($date)){
			$params[] = $date;
			$where[] = 'DATE(date_follow) >= DATE(?)';
		}
		$sql = "SELECT  b2bf.*,
						u.idu, u.fname, u.lname, u.email, u.`status`, u.user_group, u.logged, u.registration_date, u.user_photo,
						pc.country,ug.gr_name
				FROM $this->b2b_followers_table b2bf
				LEFT JOIN $this->users_table u ON b2bf.id_user = u.idu
				LEFT JOIN $this->users_groups_table ug ON ug.idgroup = u.user_group
				LEFT JOIN $this->country_table pc ON u.country = pc.id";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		if(isset($orderby)){
			switch($orderby){
				case 'date':
					$sql .= " ORDER BY b2bf.date_follow ASC";
					break;
				case 'country':
					$sql .= " ORDER BY pc.country ASC";
					break;
			}
		}

		if(isset($count)){
			$pages = ceil($count/$per_p);

			if ($page > $pages) $page = $pages;
			$start = ($page-1)*$per_p;

			if($start < 0) $start = 0;

			$sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
		}

		if(isset($from)){
			$sql .= " LIMIT " . $from . ',' . $per_p ;
		}

		return $this->db->query_all($sql, $params);
	}

	public function count_followers($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_request)){
			$params[] = $id_request;
			$where[] = 'id_request = ?';
		}

		if(isset($date)){
			$params[] = $date;
			$where[] = 'DATE(date_follow) >= DATE(?)';
		}

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->b2b_followers_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function getFollower($id_follower){
		$sql = "SELECT *
				   FROM ".$this->b2b_followers_table."
				 WHERE id_follower = ?";

		 return $this->db->query_one($sql, array($id_follower));
	}

	public function setFollowerModerated($conditions){
		$data = array();
		if(is_array($conditions))
			extract($conditions);

		$this->db->where('id_follower', $id_follower);
		$data['moderated'] = 1;
		return $this->db->update($this->b2b_followers_table, $data);
	}

	// B2B advices

	public function set_advice($data){
		$this->db->insert($this->b2b_advices, $data);
		return $this->db->last_insert_id();
	}

	public function update_advice($id_advice, $data){
		$this->db->where('id_advice', $id_advice);
		return $this->db->update($this->b2b_advices, $data);
	}

	public function get_advice($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_user)){
			$where[] = " ba.id_user = ? ";
			$params[] = $id_user;
		}
		if(isset($id_advice)){
			$where[] = " ba.id_advice = ? ";
			$params[] = $id_advice;
		}

		$sql = "SELECT ba.*, CONCAT(u.fname, ' ', u.lname) as username, u.user_photo
				FROM $this->b2b_advices ba
				LEFT JOIN users u ON ba.id_user = u.idu";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		return $this->db->query_one($sql, $params);
	}

	public function get_advices($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($id_user)){
			$where[] = " ba.id_user = ? ";
			$params[] = $id_user;
		}
		if(isset($id_request)){
			$where[] = " ba.id_request = ? ";
			$params[] = $id_request;
		}

		if(isset($requests_list))
			$this->db->in(' ba.id_request', $requests_list, true);

		$sql = "SELECT ba.*, CONCAT(u.fname, ' ', u.lname) as username, u.user_photo, status
				FROM $this->b2b_advices ba
				LEFT JOIN users u ON ba.id_user = u.idu";
		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);
		return $this->db->query_all($sql, $params);
	}

	public function get_advices_simple($conditions){
		$params = array();
		$where = array();

		extract($conditions);

		if(isset($requests_list))
			$this->db->in(' id_request', $requests_list, true);

		$sql = "SELECT *
				FROM $this->b2b_advices";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		return $this->db->query_all($sql, $params);
	}

	public function exist_request_advice($id_request, $id_user){
		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->b2b_advices."
				WHERE id_request = ? AND id_user = ?";
		$res = $this->db->query_one($sql, array($id_request, $id_user));
		return $res['counter'];
	}

	public function exist_advice($id_advice, $id_user){
		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->b2b_advices."
				WHERE id_advice= ? AND id_user = ?";
		$res = $this->db->query_one($sql, array($id_advice, $id_user));
		return $res['counter'];
	}

	public function advice_moderated($id_advice, $id_user){
		$sql = "SELECT moderated
				FROM ".$this->b2b_advices."
				WHERE id_advice= ? AND id_user = ?";
		$res = $this->db->query_one($sql, array($id_advice, $id_user));
		return $res['moderated'];
	}

	public function exist_helpful($id_advice, $id_user){
		$sql = "SELECT count(*) as counter, help
				FROM ".$this->b2b_advice_helpful."
				WHERE id_advice = ? AND id_user = ?";
		return $this->db->query_one($sql, array($id_advice, $id_user));
	}

	public function update_helpful($id_advice, $data, $id_user){
		$this->db->where('id_advice = ? AND id_user = ?',array($id_advice, $id_user));
		return $this->db->update($this->b2b_advice_helpful, $data);
	}

	public function delete_advices_helpful($advices_list){
		$this->db->in('id_advice',$advices_list, true, 'AND');
		return $this->db->delete($this->b2b_advice_helpful);
	}

	public function modify_counter_helpfull($id, $columns){
		$sql = "UPDATE " . $this->b2b_advices . " SET ";
		foreach($columns as $column => $sign)
			$set[] = $column ." = ". $column." ".$sign." 1 ";

		$sql .= implode(',',$set). " WHERE id_advice = ?";
		return $this->db->query($sql, array($id));
	}

	public function delete_advice($conditions){
		extract($conditions);
		if(isset($id_request))
			$this->db->where('id_request', $id_request);

		if(isset($requests_list))
			$this->db->in('id_request', $requests_list, true);

		if(isset($id_advice))
			$this->db->where('id_advice = ? ', $id_advice);

		return $this->db->delete($this->b2b_advices);
	}

	public function set_helpful($data){
		if(!count($data))
			return false;
		$this->db->insert($this->b2b_advice_helpful, $data);
		return $this->db->last_insert_id();
	}

	public function get_helpful_by_advice($list_advice, $id_user){
        $params = $list_advice = getArrayFromString($list_advice);

        $sql = "	SELECT id_advice, help
                    FROM " . $this->b2b_advice_helpful."
                    WHERE id_advice IN (" . implode(',', array_fill(0, count($list_advice), '?')) . ") AND id_user = ?";

        $params[] = $id_user;

		$rez = $this->db->query_all($sql, $params);

		foreach($rez as $item)
			$list[$item['id_advice']] = $item['help'];

		return $list;
	}

	public function get_all_partners($conditions){
		extract($conditions);

		$params = array();

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

		if(!empty($company_id)){
			$where[] = ' bp.id_company=? ';
			$params[] = $company_id;
		}

		if(!empty($not_user_id)){
            $not_user_id = getArrayFromString($not_user_id);
			$where[] = " bp.id_partner NOT IN (" . implode(',', array_fill(0, count($not_user_id), '?')) . ") ";
            array_push($params, ...$not_user_id);
		}

		$rel = "";
		if(isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by .= ", REL DESC";
				$where[] = " MATCH (u.fname, u.lname, u.email) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (u.fname, u.lname, u.email) AGAINST (?) as REL_tags";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
		}

		$sql = "SELECT u.idu as user_id, CONCAT(u.fname, ' ', u.lname) as user_name, u.user_group, bp.id_partner, cb.name_company as company_name, u.user_photo, u.logged $rel
				FROM $this->b2b_partners bp
				LEFT JOIN company_base cb ON bp.id_partner = cb.id_company
				LEFT JOIN $this->users_table u ON cb.id_user = u.idu ";


		if(!empty($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " GROUP BY u.idu";

		if($order_by)
			$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}

    function get_partners_saved($id_company){
        $this->db->select("GROUP_CONCAT(id_partner) as id_saved");
        $this->db->from($this->b2b_partners);
        $this->db->where("id_company = ?", (int) $id_company);

		$record = $this->db->query_one();
		return $record['id_saved'];
    }

	function get_b2b_requests_dt($conditions){
		$page = 0;
		$per_p = 20;
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		$rel = "";
		if(isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by .= ", REL DESC";
				$where[] = " MATCH (br.b2b_title, br.b2b_message, br.b2b_tags) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (br.b2b_title, br.b2b_message, br.b2b_tags) AGAINST (?) as REL";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (br.b2b_title LIKE ? OR br.b2b_message LIKE ? OR br.b2b_tags LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
		}

		if(isset($status)){
			$where[] = " br.status = ? ";
			$params[] = $status;
		}

		if(isset($blocked)){
			if($blocked == 0){
				$where[] = " br.blocked = 0 ";
			} else{
				$where[] = " br.blocked > 0 ";
			}
		}

		if(isset($start_date_from)){
			$where[] = ' DATE(br.b2b_date_register) >= ? ';
			$params[] = $start_date_from;
		}

		if(isset($start_date_to)){
			$where[] = ' DATE(br.b2b_date_register) <= ? ';
			$params[] = $start_date_to;
		}

		$sql = "SELECT br.*, cb.index_name, cb.type_company, cb.id_company, cb.logo_company, cb.name_company $rel
				FROM b2b_request br
				LEFT JOIN company_base cb ON cb.id_company = br.id_company ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		if($order_by)
			$sql .= " ORDER BY ".$order_by;

		$sql .= " LIMIT " . $start . "," . $per_p ;
		return $this->db->query_all($sql, $params);
	}

	function get_b2b_requests_dt_count($conditions){
		$page = 0;
		$per_p = 20;
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by .= ", REL DESC";
				$where[] = " MATCH (br.b2b_title, br.b2b_message, br.b2b_tags) AGAINST (?)";
				$params[] = $keywords;
			} else{
				$where[] = " (br.b2b_title LIKE ? OR br.b2b_message LIKE ? OR br.b2b_tags LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
		}

		if(isset($status)){
			$where[] = " br.status = ? ";
			$params[] = $status;
		}

		if(isset($blocked)){
			if($blocked == 0){
				$where[] = " br.blocked = 0 ";
			} else{
				$where[] = " br.blocked > 0 ";
			}
		}

		if(isset($start_date_from)){
			$where[] = ' DATE(br.b2b_date_register) >= ? ';
			$params[] = $start_date_from;
		}

		if(isset($start_date_to)){
			$where[] = ' DATE(br.b2b_date_register) <= ? ';
			$params[] = $start_date_to;
		}
		$sql = "SELECT COUNT(*) as counter
				FROM b2b_request br ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$temp = $this->db->query_one($sql, $params);
		return $temp['counter'];
	}

    /**
     * Scope event by demo user
     *
     * @var int $fakeUser
     *
     * @return void
     */
    protected function scopeFakeUser(QueryBuilder $builder, int $fakeUser): void
    {
        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "`{$userModel->get_users_table()}`.`fake_user`",
                $builder->createNamedParameter($fakeUser, ParameterType::INTEGER, $this->nameScopeParameter('fakeUser'))
            )
        );
    }

    /**
     * Scope event by model user
     *
     * @var int $modelUser
     *
     * @return void
     */
    protected function scopeModelUser(QueryBuilder $builder, int $modelUser): void
    {
        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "`{$userModel->get_users_table()}`.`is_model`",
                $builder->createNamedParameter($modelUser, ParameterType::INTEGER, $this->nameScopeParameter('modelUser'))
            )
        );
    }

    /**
     * Scope for join with users
     */
    protected function bindUsers(QueryBuilder $builder): void
    {
        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);
        $userTable = $userModel->get_users_table();

        $builder
            ->leftJoin(
                $this->getB2bRequestTable(),
                $userTable,
                $userTable,
                "`{$userTable}`.`{$userModel->get_users_table_primary_key()}` = `{$this->getB2bRequestTable()}`.`id_user`"
            );
    }

    /**
     * Scope for join with companies
     */
    protected function bindCompanies(QueryBuilder $builder): void
    {
        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);
        $companyTable = $companyModel->get_company_table();

        $builder
            ->leftJoin(
                $this->getB2bRequestTable(),
                $companyTable,
                $companyTable,
                "`{$companyTable}`.`{$companyModel->get_company_table_primary_key()}` = `{$this->getB2bRequestTable()}`.`id_company`"
            );
    }

    /**
     * Scope for join with partners types
     */
    protected function bindPartnersTypes(QueryBuilder $builder): void
    {
        /** @var Partners_Types_Model $partnersTypesModel */
        $partnersTypesModel = model(Partners_Types_Model::class);
        $partnersTypesTable = $partnersTypesModel->getTable();

        $builder
            ->leftJoin(
                $this->getB2bRequestTable(),
                $partnersTypesTable,
                $partnersTypesTable,
                "`{$partnersTypesTable}`.`{$partnersTypesModel->getPrimaryKey()}` = `{$this->getB2bRequestTable()}`.`id_type`"
            );
    }

    /**
     * Scope for join with countries
     */
    protected function bindCountries(QueryBuilder $builder): void
    {
        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        $countryTable = $countryModel->get_countries_table();

        $builder
            ->leftJoin(
                $this->getB2bRequestTable(),
                $countryTable,
                $countryTable,
                "`{$countryTable}`.`{$countryModel->get_countries_table_primary_key()}` = `{$this->getB2bRequestTable()}`.`id_country`"
            );
    }

    /**
     * Scope for join with states
     */
    protected function bindStates(QueryBuilder $builder): void
    {
        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        $stateTable = $countryModel->get_regions_table();

        $builder
            ->leftJoin(
                $this->getB2bRequestTable(),
                $stateTable,
                $stateTable,
                "`{$stateTable}`.`{$countryModel->get_regions_table_primary_key()}` = `{$this->getB2bRequestTable()}`.`id_state`"
            );
    }

    /**
     * Scope for join with cities
     */
    protected function bindCities(QueryBuilder $builder): void
    {
        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        $cityTable = $countryModel->get_cities_table();

        $builder
            ->leftJoin(
                $this->getB2bRequestTable(),
                $cityTable,
                $cityTable,
                "`{$cityTable}`.`{$countryModel->get_cities_table_primary_key()}` = `{$this->getB2bRequestTable()}`.`id_city`"
            );
    }

    protected function b2bRequestIndustries(): RelationInterface
    {
        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);

        $relationIndustryTable = $this->getB2bRequestRelationIndustryTable();
        $categoriesTable = $categoryModel->get_categories_table();

        $relation = $this->hasMany(
            new PortableModel($this->getHandler(), $relationIndustryTable, 'id_relation'),
            'id_request',
            'id_request'
        );
        $relation->disableNativeCast();
        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$relationIndustryTable}`.`id_request`",
                "`{$categoriesTable}`.`name`",
            )
            ->leftJoin(
                $relationIndustryTable,
                $categoriesTable,
                $categoriesTable,
                "`{$categoriesTable}`.`{$categoryModel->get_categories_table_primary_key()}` = `{$relationIndustryTable}`.`id_industry`"
            )
        ;

        return $relation;
    }

    /**
     * Creates new related instance of the model for relation.
     *
     * @param BaseModel|Model|string $source
     */
    protected function resolveRelatedModel($source): Model
    {
        if ($source === $this) {
            return new PortableModel($this->getHandler(), $this->getB2bRequestTable(), $this->getB2bRequestPrimaryKey());
        }

        return parent::resolveRelatedModel($source);
    }
}
