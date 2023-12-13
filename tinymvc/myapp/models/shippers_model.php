<?php

use App\Common\Database\BaseModel;
use Doctrine\DBAL\ParameterType;

/**
 * @deprecated in favor of \Shipper_Companies_Model
 */
class Shippers_Model extends BaseModel
{
	private $orders_shippers_table = 'orders_shippers';
	private $orders_table = 'item_orders';
	private $shipper_users_table = 'shipper_users';
	private $orders_shippers_quotes_table = 'orders_shippers_quotes';
	private $shipping_type_table = 'shipping_type';
	private $orders_shippers_relation_industry_table = 'orders_shippers_relation_industry';
	private $orders_shippers_pictures_table = 'orders_shippers_pictures';
	private $user_saved_shippers_table = 'user_saved_shippers';
	private $users_table = "users";
	private $port_country_table = "port_country";
	private $shipper_countries_table = 'shipper_countries';
	private $seller_shipper_partners_table = 'seller_shipper_partners';
	private $shipping_types_map_table = "shipping_type_relation";
	private $category_table = "item_category";

    public $path_to_logo_img = "public/img/shippers/";

    /**
     * Get the shippers table name.
     */
    public function getShippersTable(): string
    {
        return $this->orders_shippers_table;
    }

    /**
     * Get the shippers table primary key.
     */
    public function getShippersTablePrimaryKey(): string
    {
        return 'id';
    }

	function get_shippers($conditions = array()){
        $visible = 1;
		extract($conditions);

		$where = $params = [];

		if (isset($shippers_list)) {
            $shippers_list = getArrayFromString($shippers_list);
			$where[] = " id_user IN (" . implode(',', array_fill(0, count($shippers_list), '?')) . ") ";
            array_push($params, ...$shippers_list);
		}

		if ($visible != 'all') {
			$where[] = " visible = ? ";
			$params[] = $visible;
		}

		$sql = "SELECT * FROM $this->orders_shippers_table";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND " , $where);
        }


		if (isset($order_by)) {
			$sql .= " ORDER BY " . $order_by;
        }

		if (isset($start, $per_p)) {
			$sql .= " LIMIT " . $start . "," . $per_p;
		}

		return $this->db->query_all($sql, $params);
	}

	function get_count_shippers($conditions = array()){
        $visible = 1;
		extract($conditions);

		$where = $params = [];

		if (isset($shippers_list)) {
            $shippers_list = getArrayFromString($shippers_list);
			$where[] = " id_user IN (" . implode(',', array_fill(0, count($shippers_list), '?')) . ") ";
            array_push($params, ...$shippers_list);
		}

		if ($visible != 'all') {
			$where[] = " os.visible = ? ";
			$params[] = $visible;
		}

		$sql = "SELECT COUNT(*) as counter FROM {$this->orders_shippers_table} ";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND " , $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
    }

	function get_shippers_detail_by_conditions($conditions){
		$order_by = "os.create_date ASC";
		$joins = "";
		$rel = "";
		$visible = 1;
		extract($conditions);

		$where = $params = [];

		if($visible != 'all'){
			$where[] = " os.visible = ? ";
			$params[] = $visible;
		}

		if (isset($work_in_country)) {
			$joins .= " INNER JOIN $this->shipper_countries_table sc ON os.id_user = sc.id_user";
			$where[] = " (sc.id_country = ? OR sc.id_country = 0) ";
			$params[] = $work_in_country;
		}

		if (isset($country)) {
			$where[] = " os.id_country = ? ";
			$params[] = $country;
		}

		if (isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$order_by .= ", REL DESC";
				$where[] = " MATCH (os.co_name, os.email) AGAINST (?) ";
				$params[] = $keywords;
				$rel = " , MATCH (os.co_name, os.email) AGAINST (?) as REL";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (os.co_name LIKE ? OR os.email LIKE ?) ";
                array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
			}
		}

		if (isset($id_user)) {
			$where[] = " os.id_user = ? ";
			$params[] = $id_user;
		}

		if (isset($fake_user)) {
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
		}

		$sql = "SELECT os.*, pc.country,
				CONCAT(u.fname, ' ', u.lname) as user_name, u.status as user_status $rel
				FROM $this->orders_shippers_table os
				INNER JOIN $this->users_table u ON os.id_user = u.idu
				LEFT JOIN $this->port_country_table pc ON os.id_country = pc.id
				$joins";

		if(!empty($where)){
			$sql = $sql . ' WHERE ' . implode(' AND ' , $where);
		}

        if(isset($sort_by)){
            $multi_order_by = array();

			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			if(!empty($multi_order_by)){
				$order_by = implode(',', $multi_order_by);
			}
		}

		if(!empty($order_by)){
			$sql .= ' ORDER BY ' . $order_by;
		}

		if (!isset($count))
			$count = $this->count_shippers_by_conditions($conditions);

		if (isset($page)) {
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
		}elseif(isset($start)) {
			$sql .= " LIMIT " . $start;

			if(isset($limit))
				$sql .= " , " . $limit;
		}

		return $this->db->query_all($sql, $params);
	}

	function count_shippers_by_conditions($conditions = array()){
        $joins = "";
		$visible = 1;

		extract($conditions);

		$where = $params = [];

		if($visible != 'all'){
			$where[] = " os.visible = ? ";
			$params[] = $visible;
		}

		if (isset($work_in_country)) {
			$joins .= " INNER JOIN $this->shipper_countries_table sc ON os.id_user = sc.id_user";
			$where[] = " (sc.id_country = ? OR sc.id_country = 0) ";
			$params[] = $work_in_country;
		}

		if (isset($country)) {
			$where[] = " os.id_country = ? ";
			$params[] = $country;
		}

		if (isset($keywords)) {
			if(str_word_count_utf8($keywords) > 1){
				$where[] = " MATCH (os.co_name, os.email) AGAINST (?) ";
				$params[] = $keywords;
			} else{
				$where[] = " (os.co_name LIKE ? OR os.email LIKE ?) ";
                array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
			}
		}

		if (isset($id_user)) {
			$where[] = " os.id_user = ? ";
			$params[] = $id_user;
		}

		if (isset($fake_user)) {
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM $this->orders_shippers_table os
				INNER JOIN $this->users_table u ON os.id_user = u.idu
				$joins";

		if (!empty($where)) {
			$sql = $sql . ' WHERE ' . implode(' AND ' , $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	function shippers_by_countries(){
		$sql = "SELECT pc.country,pc.id, pc.id_continent, count(*) as counter
				FROM {$this->orders_shippers_table} os
				LEFT JOIN {$this->port_country_table} pc ON os.id_country = pc.id
				WHERE os.visible = 1
				GROUP BY os.id_country
				ORDER BY counter DESC, pc.country ASC";
		return $this->db->query_all($sql);
	}

	function my_saved_shippers($id_user, $conditions){
		$order_by = " os.co_name ";
		$visible = 1;

		extract($conditions);

        $joins = "";
        $where = [" uss.id_user = ? "];
		$params = [$id_user];

		if(isset($countries_list)){
			$joins .= " INNER JOIN $this->shipper_countries_table sc ON os.id_user = sc.id_user ";

            $countries_list = getArrayFromString($countries_list);
			$where[] = " sc.id_country IN (" . implode(',', array_fill(0, count($countries_list), '?')) . ") ";
            array_push($params, ...$countries_list);
		}

		if($visible != 'all'){
			$where[] = " os.visible = ? ";
			$params[] = $visible;
		}

		$sql = "SELECT os.*
				FROM $this->user_saved_shippers_table uss
				INNER JOIN $this->orders_shippers_table os ON uss.id_shipper = os.id
				$joins";

		if (!empty($where)) {
			$sql = $sql . ' WHERE ' . implode(' AND ' , $where);
        }

		$sql .= " ORDER BY {$order_by}";

		return $this->db->query_all($sql, $params);
	}

    /**
     * @param int $userId
     */
    public function deleteAllUserShippersRelation(int $userId)
    {
        $this->db->where('id_user', $userId);
        return $this->db->delete($this->shipper_users_table);
    }

	function delete_shipper($id_shipper, $id_user){
		// DELETE SHIPPER RELATION INDUSTRY
		$this->db->where('id_shipper', $id_shipper);
		$this->db->delete($this->orders_shippers_relation_industry_table);

		// DELETE USER SHIPPER RELATION
		$this->db->where('id_shipper', $id_shipper);
		$this->db->delete($this->shipper_users_table);

		// DELETE SHIPPER
		$this->db->where('id', $id_shipper);
		$response = $this->db->delete($this->orders_shippers_table);

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

		$crmModel->create_or_update_record($id_user);

		return $response;
	}

	function insert_shipper($results){
		$this->db->insert($this->orders_shippers_table, $results);
		$last_inserted_id = $this->db->last_insert_id();

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

		$crmModel->create_or_update_record($results['id_user']);

		return $last_inserted_id;
	}

	function set_shipper_user_relation($data = array()) {
		return $this->db->insert($this->shipper_users_table, $data);
	}

	function update_shipper($data, $id){
		$this->db->where('id', $id);
		$response = $this->db->update($this->orders_shippers_table, $data);

		if ( ! empty($data['co_name'])) {
			$company = $this->get_shipper($id);

            /** @var Crm_Model $crmModel */
            $crmModel = model(Crm_Model::class);

			$crmModel->create_or_update_record($company['id_user']);
		}

		return $response;
	}

	function update_order_quote_request($data, $conditions = array()){
		if(empty($conditions)){
			return false;
		}

		extract($conditions);

		if(isset($id_user)){
			$this->db->where('id_user', $id_user);
		}

		if(isset($id_quote)){
			$this->db->where('id_quote', $id_quote);
		}

		if(isset($not_id_quote)){
			$this->db->where('id_quote !=', $not_id_quote);
		}

		if(isset($id_order)){
			$this->db->where('id_order', $id_order);
		}

		return $this->db->update($this->orders_shippers_quotes_table, $data);
	}

	function get_order_quote_requests($id_order = 0){
        $this->db->where('id_order', $id_order);
        return $this->db->get($this->orders_shippers_quotes_table);
	}

	function get_order_quote_request($id_request = 0){
		$this->db->select("osq.*, st.type_alias, st.type_name, st.type_description");
		$this->db->from("{$this->orders_shippers_quotes_table} osq");
		$this->db->join("{$this->orders_table} o", "osq.id_order = o.id", "inner");
		$this->db->join("{$this->shipping_type_table} st", "o.shipment_type = st.id_type", "inner");
		$this->db->where("osq.id_quote = ?", $id_request);

		return $this->db->query_one() ?: [];
	}

	function delete_order_quote_request($id_request){
		$this->db->where('id_quote', $id_request);
		return $this->db->delete($this->orders_shippers_quotes_table);
	}

	function get_order_quote_requests_shippers($id_order = 0){
		$this->db->select("os.id, os.id_user, os.co_name, os.logo, oqr.id_quote, oqr.shipping_price, oqr.delivery_date, oqr.delivery_days_from, oqr.delivery_days_to, oqr.comment_shipper, oqr.comment_user, oqr.quote_status, st.type_alias, st.type_name, st.type_description");
		$this->db->from("{$this->orders_shippers_quotes_table} oqr");
		$this->db->join("{$this->orders_shippers_table} os", "oqr.id_shipper = os.id_user", "inner");
		$this->db->join("{$this->orders_table} o", "oqr.id_order = o.id", "inner");
		$this->db->join("{$this->shipping_type_table} st", "o.shipment_type = st.id_type", "inner");
		$this->db->where("oqr.id_order = ?", (int) $id_order);
		$this->db->where("oqr.quote_status = ?", 'awaiting');
		$this->db->where("os.visible = ?", 1);
		$this->db->orderby('oqr.shipping_price asc, oqr.delivery_days_from asc, oqr.delivery_days_to asc');
		return $this->db->get();
	}

	function get_quotes($id_user, $conditions = array()){
		extract($conditions);

		$where = array('osq.id_user = ?');
		$params = array($id_user);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($status)){
			$params[] = $status;
			$where[] = 'osq.status = ?';
		}

		if(isset($order)){
			$params[] = $order;
			$where[] = 'osq.id_order = ?';
		}

		if(isset($ship_from)){
			$params[] = $ship_from;
			$where[] = 'io.ship_from_country = ?';
		}

		if(isset($ship_to)){
			$params[] = $ship_to;
			$where[] = 'io.ship_to_country = ?';
		}

		if(isset($beg_from)){
			$params[] = $beg_from;
			$where[] = 'io.order_date > ?';
		}

		if(isset($beg_to)){
			$params[] = $beg_to;
			$where[] = 'io.order_date < ?';
		}

		if(isset($active_from)){
			$params[] = $active_from;
			$where[] = 'io.update_date > ?';
		}

		if(isset($active_to)){
			$params[] = $active_to;
			$where[] = 'io.update_date < ?';
		}

		if(isset($countdown_from)){
			$params[] = $countdown_from;
			$where[] = 'io.status_countdown > ?';
		}

		if(isset($countdown_to)){
			$params[] = $countdown_to;
			$where[] = 'io.status_countdown < ?';
		}

		if(isset($start_from)){
			$params[] = $start_from;
			$where[] = 'osq.create_date > ?';
		}

		if(isset($start_to)){
			$params[] = $start_to;
			$where[] = 'osq.create_date < ?';
		}

		if(isset($request_number)){
			$params[] = $request_number;
			$where[] = 'osq.id_quote = ?';
		}

		$sql = "SELECT
					osq.*, if(osq.max_response_date > NOW(), 'active', 'expired') as current_countdown,
					os.alias as order_status_alias,
					io.status_countdown, io.weight, io.id_buyer, io.id_seller, io.ep_manager, io.package_detail,
					osc.co_name, osc.logo, osc.id as id_shipper_company
				FROM $this->orders_shippers_quotes_table osq
				LEFT JOIN item_orders io ON io.id = osq.id_order
				LEFT JOIN orders_status os ON io.status = os.id
				LEFT JOIN $this->orders_shippers_table osc ON osq.id_shipper = osc.id_user
				WHERE " . implode(" AND ", $where);

		if ($order_by) {
			$sql .= " ORDER BY " . $order_by;
        }


		if (isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
			$sql .= " LIMIT {$start},{$per_p}";
        }

		return $this->db->query_all($sql, $params);
	}

	function get_quotes_count($id_user, $configs){
		extract($configs);

		$where = array('osq.id_user = ?');
		$params = array($id_user);

		if(isset($status)){
			$params[] = $status;
			$where[] = 'osq.status = ?';
		}

		if(isset($order)){
			$params[] = $order;
			$where[] = 'osq.id_order = ?';
		}

		if(isset($ship_from)){
			$params[] = $ship_from;
			$where[] = 'io.ship_from_country = ?';
		}

		if(isset($ship_to)){
			$params[] = $ship_to;
			$where[] = 'io.ship_to_country = ?';
		}

		if(isset($beg_from)){
			$params[] = $beg_from;
			$where[] = 'io.order_date > ?';
		}

		if(isset($beg_to)){
			$params[] = $beg_to;
			$where[] = 'io.order_date < ?';
		}

		if(isset($active_from)){
			$params[] = $active_from;
			$where[] = 'io.update_date > ?';
		}

		if(isset($active_to)){
			$params[] = $active_to;
			$where[] = 'io.update_date < ?';
		}

		if(isset($countdown_from)){
			$params[] = $countdown_from;
			$where[] = 'io.status_countdown > ?';
		}

		if(isset($countdown_to)){
			$params[] = $countdown_to;
			$where[] = 'io.status_countdown < ?';
		}

		$sql = "SELECT COUNT(*) as counter
				FROM $this->orders_shippers_quotes_table osq
				LEFT JOIN item_orders io ON io.id = osq.id_order
				WHERE " . implode(" AND ", $where);

		return $this->db->query_one($sql, $params)['counter'];
	}

	function delete_quote($id){
        $this->db->where('id_quote', $id);
        return $this->db->delete($this->orders_shippers_quotes_table);
    }

	function get_shipper_details($id_shipper = 0, $conditions = array()){
		$this->db->select("os.*, CONCAT_WS(' ', u.fname, u.lname) as user_name, u.email, u.fake_user, u.`status` as user_status, pc.country");
		$this->db->from("{$this->orders_shippers_table} os");
		$this->db->join("{$this->users_table} u", "os.id_user = u.idu", "inner");
		$this->db->join("{$this->port_country_table} pc", "os.id_country = pc.id", "left");
		$this->db->where("os.id", (int) $id_shipper);
        $this->db->limit(1);

		$visible = 'all';
		extract($conditions);

		if($visible != 'all'){
			$this->db->where("os.visible", (int) $visible);
		}

		return $this->db->get_one();
	}

	public function get_shipper_by_user($userId = 0)
    {
        $query = $this->createQueryBuilder();
        $query
            ->select("os.*", "CONCAT_WS(' ', u.fname, u.lname) as user_name", "u.email")
            ->from($this->orders_shippers_table, 'os')
            ->innerJoin('os', $this->users_table, 'u', "os.id_user = u.idu")
            ->where(
                $query->expr()->eq("os.id_user", $query->createNamedParameter((int) $userId, ParameterType::INTEGER, ':user'))
            )
        ;
        /** @var Statement $statement */
        $statement = $query->execute();

        return $statement->fetchAssociative() ?: null;
	}

	function get_shippers_by_users(array $users_ids){
		$this->db->select("os.*, CONCAT_WS(' ', u.fname, u.lname) as user_name, os.id_user, u.email");
		$this->db->from("{$this->orders_shippers_table} os");
		$this->db->join("{$this->users_table} u", "os.id_user = u.idu", "inner");
		$this->db->in("os.id_user", $users_ids);

		return $this->db->get();
	}

	function get_shipper($id_shipper) {
		$this->db->select("*");
		$this->db->from("{$this->orders_shippers_table} os");
		$this->db->where("id", (int) $id_shipper);

		return $this->db->get_one();
	}

	function has_logo($shipper_id) {
		$this->db->select("IF(`SHIPPERS`.`logo` IS NULL OR `SHIPPERS`.`logo` = '', 0, 1) AS `AGGREGATE`");
		$this->db->from("{$this->orders_shippers_table} SHIPPERS");
		$this->db->where("id = ?", (int) $shipper_id);

		return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?: false);
	}

	public function get_user_shipper_details($id_user) {
		$sql = "SELECT s.*
                FROM $this->orders_shippers_table s
                INNER JOIN $this->shipper_users_table su ON s.id = su.id_shipper
                WHERE su.id_user = ? ";

		return $this->db->query_one($sql, array($id_user));
	}

	function exist_shipper_by_email($email){
        $this->db->where('email', $email);
        $this->db->limit(1);
        return $this->db->query_one($this->orders_shippers_table);
	}

	function set_relation_industry($id_shipper, $industries) {
		if (empty($industries)){
			return;
		}

        $params = $values = [];
		$sql = "INSERT INTO $this->orders_shippers_relation_industry_table (`id_shipper`, `id_industry`) VALUES ";

		foreach ($industries as $industry){
            $params[] = $id_shipper;
            $params[] = (int) $industry;
            $values[] = '(?,?)';
		}

		if (empty($values)) {
			return;
		}

		$sql .= implode(',', $values);

		$this->db->query($sql, $params);
	}

	function get_shipper_pictures($id_shipper){
		return $this->db->query_all("SELECT * FROM {$this->orders_shippers_pictures_table} WHERE id_shipper = ? ", array($id_shipper));
	}

	function get_email_shipper($id_shipper) {
        $this->db->select('email');
        $this->db->where('id', $id_shipper);
        $this->db->limit(1);

        return $this->db->get_one($this->orders_shippers_table)['email'];
	}

	function get_phone_shipper($id_shipper) {
        $this->db->select('phone');
        $this->db->where('id', $id_shipper);
        $this->db->limit(1);

        return $this->db->get_one($this->orders_shippers_table)['phone'];
	}

	function exist_shipper($conditions) {
        extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->limit(1);

        if (!empty($id_shipper)) {
            $this->db->where('id', $id_shipper);
        }

        return $this->db->get_one($this->orders_shippers_table)['counter'];
	}

	public function set_saved_shipper($data=array()){
        return empty($data) ? false : $this->db->insert($this->user_saved_shippers_table, $data);
	}

	function delete_saved_shipper($id_user, $id_shipper) {
		$this->db->where('id_user = ? AND id_shipper = ?', array($id_user, $id_shipper));
		return $this->db->delete($this->user_saved_shippers_table);
	}

	function get_saved_shippers($id_user){
        $this->db->select('GROUP_CONCAT(id_shipper) as id_saved');
        $this->db->where('id_user', $id_user);
        $this->db->limit(1);

        return $this->db->get_one($this->user_saved_shippers_table)['id_saved'];
	}

	function get_saved_counter($id_user){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_user', $id_user);
        $this->db->limit(1);

        return $this->db->get_one($this->user_saved_shippers_table)['counter'];
	}

	 function get_saved_shippers_by_conditions($id_user, $conditions){
		$page = 1;
		$per_p = 15;

		extract($conditions);

		$sql = "SELECT uss.date_save, os.*, pc.country, u.user_group, u.status, u.registration_date, CONCAT(u.fname, ' ', u.lname) as user_name
				FROM " . $this->user_saved_shippers_table . " uss
				LEFT JOIN " . $this->orders_shippers_table . " os ON uss.id_shipper = os.id
				INNER JOIN " . $this->users_table . " u ON os.id_user = u.idu
				LEFT JOIN " . $this->port_country_table . " pc ON os.id_country = pc.id
				WHERE uss.id_user = ?
				ORDER BY os.co_name ";

		if ($page != 'all') {
			$sql .= " LIMIT " . ($page-1) * $per_p . ", " . $per_p;
        }

		return $this->db->query_all($sql, array($id_user));
	}

	function get_saved_shippers_count($conditions){
        extract($conditions);

		$where = $params = [];

		if (isset($id_shipper)) {
			$where[] = " id_user = ? ";
			$params[] = $id_shipper;
		}

		$sql = "SELECT COUNT(*) as counter FROM {$this->user_saved_shippers_table} ";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
	}

	function insert_partnership($data){
		return $this->db->insert($this->seller_shipper_partners_table, $data);
	}

	function exist_partnership($conditions = array()){
        extract($conditions);

		$where = $params = [];

		if(isset($id_shipper)){
			$where[] = ' id_shipper = ? ';
			$params[] = $id_shipper;
		}

		if(isset($id_seller)){
			$where[] = ' id_seller = ? ';
			$params[] = $id_seller;
		}

		if(isset($id_partner)){
			$where[] = ' id_partner = ? ';
			$params[] = $id_partner;
		}

		$sql = "SELECT COUNT(*) as counter FROM {$this->seller_shipper_partners_table}";

		if (!empty($where)) {
			$sql = $sql . ' WHERE ' . implode(' AND ' , $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	function get_id_shippers_partnership($id_seller, $are_partners = 1){
        $this->db->select('group_concat(id_shipper) as shippers');
        $this->db->where('id_seller', $id_seller);
        $this->db->where('are_partners', $are_partners);

        return $this->db->get_one($this->seller_shipper_partners_table)['shippers'];
	}

	function get_seller_shipper_partner($conditions = array()){
        extract($conditions);

		$where = $params = [];

		if(isset($id_shipper)){
			$where[] = ' id_shipper = ? ';
			$params[] = $id_shipper;
		}

		if(isset($id_seller)){
			$where[] = ' id_seller = ? ';
			$params[] = $id_seller;
		}

		$sql = "SELECT * FROM {$this->seller_shipper_partners_table} ";

		if (!empty($where)) {
			$sql = $sql . ' WHERE ' . implode(' AND ' , $where);
        }

		return $this->db->query_one($sql, $params);
	}

	function get_seller_shipper_partners($conditions = array()){
		$order_by = " sp.date_partner DESC ";
		$start = 0;
		$per_p = 20;

		extract($conditions);

		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($id_shipper)){
			$where[] = ' sp.id_shipper = ? ';
			$params[] = $id_shipper;
		}

		if(isset($id_seller)){
			$where[] = ' sp.id_seller = ? ';
			$params[] = $id_seller;
		}

		if(isset($are_partners)){
			$where[] = ' sp.are_partners = ? ';
			$params[] = $are_partners;
		}

		$sql = "SELECT sp.*,
						os.logo, os.id, os.phone, os.email, os.address, os.id_country, os.id_state, os.id_city, os.id_user, os.co_name,
						pc.country, CONCAT(u.fname, ' ', u.lname) as user_name, u.status as user_status
				FROM $this->seller_shipper_partners_table sp
				LEFT JOIN $this->orders_shippers_table os ON sp.id_shipper = os.id_user
				INNER JOIN $this->users_table u ON sp.id_shipper = u.idu
				LEFT JOIN $this->port_country_table pc ON os.id_country = pc.id";

		if (!empty($where)) {
			$sql = $sql . ' WHERE ' . implode(' AND ' , $where);
        }

		$sql .= " ORDER BY {$order_by}";

		if(!isset($count)) {
			$count = $this->count_seller_shipper_partners($conditions);
        }

		$pages = ceil($count/$per_p);

		if($page > $pages) {
			$page = $pages;
        }

		if(!isset($start)){
			$start = ($page-1)*$per_p;

			if($start < 0)
				$start = 0;
		}

		if ($start >= 0) {
			$sql .=  " LIMIT " . $start;
        }

		if ($per_p > 0) {
			$sql .= ", " . $per_p;
        }

		return $this->db->query_all($sql, $params);
	}

	function count_seller_shipper_partners($conditions = array()){
        extract($conditions);

		$where = $params = [];

		if(isset($id_shipper)){
			$where[] = ' id_shipper = ? ';
			$params[] = $id_shipper;
		}

		if(isset($id_seller)){
			$where[] = ' id_seller = ? ';
			$params[] = $id_seller;
		}

		if(isset($are_partners)){
			$where[] = ' are_partners = ? ';
			$params[] = $are_partners;
		}

		$sql = "SELECT COUNT(*) as counter FROM {$this->seller_shipper_partners_table} ";

		if (!empty($where)) {
			$sql = $sql . ' WHERE ' . implode(' AND ' , $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	function delete_partnership($id_partner) {
		$this->db->where('id_partner', $id_partner);
		return $this->db->delete($this->seller_shipper_partners_table);
	}

	function get_relation_industry_by_company_id(int $company_shipper_id, bool $join_with_categories = false) {
        $this->db->from($this->orders_shippers_relation_industry_table);
        $this->db->where('id_shipper', $company_shipper_id);

        if ($join_with_categories) {
            $this->db->join($this->category_table, "{$this->category_table}.`category_id` = {$this->orders_shippers_relation_industry_table}.`id_industry`", 'left');
        }

        return $this->db->get();
	}

	function delete_relation_industry_by_company($id) {
		$this->db->where('id_shipper', $id);
		return $this->db->delete($this->orders_shippers_relation_industry_table);
	}

	public function get_seller_shippers($seller, $countries){
        $params = [$seller];
        $countries = getArrayFromString($countries);
        array_push($params, ...$countries);

		$sql = "SELECT u.idu
				FROM users u
				INNER JOIN $this->orders_shippers_table os ON os.id_user = u.idu
				INNER JOIN $this->seller_shipper_partners_table ssp ON ssp.id_shipper = u.idu
				INNER JOIN $this->shipper_countries_table sc ON sc.id_user = u.idu
				WHERE
					os.visible = 1
					AND ssp.id_seller = ?
					AND sc.id_country IN (" . implode(",", array_fill(0, count($countries), '?')) . ")
					AND ssp.are_partners = 1";

		return $this->db->query_all($sql, $params);
	}

	public function get_potential_shippers($shipping_type = null, $departure_country = null, $onlyFakeUsers = false)
	{
		$this->db->select("`SHIPPERS`.id_user");
		$this->db->from("{$this->orders_shippers_table} `SHIPPERS`");
		$this->db->join("{$this->users_table} `USERS`", "`SHIPPERS`.`id_user` = `USERS`.`idu`", 'left');
		$this->db->where("`USERS`.`status` = ?", 'active');

		if (null !== $shipping_type) {
			$this->db->where_raw(
				"SHIPPERS.`id_user` IN (
					SELECT `SHIPPING_TYPES`.`id_shipper`
					FROM {$this->shipping_types_map_table} `SHIPPING_TYPES`
					WHERE `SHIPPING_TYPES`.`id_type` = ?
				)",
				(int) $shipping_type
			);
		}

		if (null !== $departure_country) {
			$this->db->where_raw(
				"`SHIPPERS`.`id_user` IN (
					SELECT `SHIPPING_COUNTRIES`.`id_user`
					FROM {$this->shipper_countries_table} `SHIPPING_COUNTRIES`
					WHERE `SHIPPING_COUNTRIES`.`id_country` = 0 OR `SHIPPING_COUNTRIES`.`id_country` = ?
				)",
				array((int) $departure_country)
			);
		}

        if ($onlyFakeUsers) {
            $this->db->where_raw("(`USERS`.`fake_user` = 1 OR `USERS`.`is_model` = 1)");
        }

		return $this->db->query_all();
	}

	public function get_company_logo($id)
	{
		$this->db->select("logo");
		$this->db->from($this->orders_shippers_table);
		$this->db->where("id = ?", $id);

        return $this->db->get_one()['logo'];
	}

	function count_categories_by_conditions($conditions)
	{
        extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->limit(1);

        if (isset($parent)) {
            $this->db->where('parent = ?', $parent);
        }

        if (isset($parent_not)) {
            $this->db->where('parent != ?', $parent_not);
		}

		if (isset($category_list)) {
            $category_list = getArrayFromString($category_list);
            $this->db->in('category_id', $category_list);
		}

        return (int) $this->db->get_one($this->category_table)['counter'];
	}
}
