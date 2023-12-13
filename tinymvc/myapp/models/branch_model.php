<?php
/**
 * branch_model.php
 *
 * company model
 *
 * @author Andrei Cravciuc
 */

class Branch_Model extends TinyMVC_Model {

	var $obj;
    private $branch_photo_table = "seller_photo";
    private $company_base_table = "company_base";
	private $users_table = "users";
	private $country_table = "port_country";
	private $company_type_table = "company_type";

    private $partners_type = "partners_type";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	/**
	* company branches
	*/
	public function set_branch_photos($data){
		$this->db->insert_batch($this->branch_photo_table, $data);
		return array('inserted_rows' => $this->db->getAffectableRowsAmount(), 'first_id' => $this->db->last_insert_id());
	}

	// public function get_company_branches($id_company){
	// 	$sql = "SELECT *
	// 			FROM ".$this->company_base_table."
	// 			WHERE parent_company = ?";

    //     return $this->db->query_all($sql, array($id_company));
	// }

	function get_company_branches($id_company, $columns="cb.*") {
		$this->db->select("{$columns}");
		$this->db->from("$this->company_base_table cb");
		$this->db->where("cb.parent_company = ?", $id_company);

		return $this->db->get();
	}

	public function get_parteners_type()
    {
        return $this->db->query_all("SELECT * FROM {$this->partners_type}");
	}

	public function get_branch_image($id_image, $id_branch){
		$sql = "SELECT *
				FROM $this->branch_photo_table
				WHERE id_photo = ? AND id_company = ?";

        return $this->db->query_one($sql, array($id_image ,$id_branch));
	}

	public function get_branch_image_by_id($id_photo, $id_branch){
		$sql = "SELECT *
				FROM $this->branch_photo_table
				WHERE id_photo = ? AND id_company = ?";

        return $this->db->query_one($sql, array($id_photo ,$id_branch));
	}

	public function get_branch_image_by_name($path_photo, $id_branch){
		$sql = "SELECT *
				FROM $this->branch_photo_table
				WHERE path_photo = ? AND id_company = ?";

        return $this->db->query_one($sql, array($path_photo ,$id_branch));
	}

	public function delete_branch_photo($id_image, $id_branch){
		$this->db->where('id_photo', $id_image);
		$this->db->where('id_company', $id_branch);

        return $this->db->delete($this->branch_photo_table);
	}

	public function delete_branch_photos($id_branch){
		$this->db->where('id_company', $id_branch);

        return $this->db->delete($this->branch_photo_table);
	}

	public function delete_branch($id_branch){
		$this->db->where('id_company', $id_branch);
		$this->db->where('type_company', 'branch');
        return $this->db->delete($this->company_base_table);
	}

	public function is_my_branch($id_branch, $id_user){
		$sql = "SELECT count(*) as counter
				FROM ".$this->company_base_table."
				WHERE id_company = ? AND ( id_user = ? OR FIND_IN_SET(?, user_acces)) AND type_company = 'branch' ";

		$info = $this->db->query_one($sql, array($id_branch, $id_user, $id_user));
		return $info['counter'];
	}

	public function get_branch_images($conditions){
		$order_by = "sp.id_photo DESC";
		$where = array();
        $params = array();

		extract($conditions);

		if(isset($id_company)){
			$where[] = ' sp.id_company = ?';
			$params[] = $id_company;
		}

		$sql .= "SELECT sp.*
				 FROM " . $this->branch_photo_table . " sp";
		if(count($where))
		    $sql .= " WHERE " . implode(" AND", $where);
		$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}

	public function count_branch_images($conditions){
		$where = array();
        $params = array();

		extract($conditions);

		if(isset($id_company)){
			$where[] = ' sp.id_company = ?';
			$params[] = $id_company;
		}

		$sql .= "SELECT COUNT(*) as counter
				 FROM " . $this->branch_photo_table . " sp";
		if(count($where))
		    $sql .= " WHERE " . implode(" AND", $where);

		$rez = $this->db->query_one($sql, $params);//
		return $rez['counter'];
	}

	public function block_branches_by_sellers($sellers_list, $update = array()){
		$this->db->in('id_user', $sellers_list);
		$this->db->where('blocked', 0);
		return $this->db->update($this->company_base_table, $update);
	}

	/*
	public function set_branch($data){
		$this->db->insert($this->branch_table, $data);
		return $this->db->last_insert_id();
	}*/

	/*
	public function get_branch($id_branch){
		$sql = "SELECT *
				FROM ".$this->branch_table."
				WHERE id_branch = ?";

        return $this->db->query_one($sql, array($id_branch));
	}*/

	/*
	public function update_branch($id, $data){
        $this->db->where('id_branch', $id);
        return $this->db->update($this->branch_table, $data);
    }
	*/

	/*
	//branch industries relations
    function set_relation_industry($id_branch, $data){
		$sql = "INSERT INTO ".$this->relation_industry_table."
				(`id_branch`, `id_industry`) VALUES ";

		foreach($data as $ind)
			$inds[] = "(".$id_branch.",".$ind.")";

		$sql .= implode(',', $inds);

		$this->db->query($sql);
		return $this->db->last_insert_id();
    }

	function get_relation_industry_by_id($id, $full = false){
		$column = "";
		if($full){
			$column = ", ic.name, ic.category_id";
		}
		$sql = "SELECT ri.* ".$column."
				FROM ".$this->relation_industry_table." ri ";
		if($full)
			$sql .= " LEFT JOIN item_category ic ON ri.id_industry = ic.category_id";

		$sql .= " WHERE ri.id_branch = ".$id;

        return $this->db->query_all($sql);
	}

	function delete_relation_industry_by_branch($id){
		$this->db->where('id_branch', $id);
		return $this->db->delete($this->relation_industry_table);
	}
	//branch category relations
    function set_relation_category($id_branch, $data){
		$sql = "INSERT INTO ".$this->relation_category_table."
				(`id_branch`, `id_category`) VALUES ";

		foreach($data as $ind)
			$inds[] = "(".$id_branch.",".$ind.")";

		$sql .= implode(',', $inds);

		$this->db->query($sql);
		return $this->db->last_insert_id();
    }

    function get_relation_category_by_id($id){
		$sql = "SELECT *
			FROM ".$this->relation_category_table."
			WHERE id_branch = ".$id;

        return $this->db->query_all($sql);
	}
	function get_branch_industry_categories($conditions){
		$order_by = "name ASC";
        $where = array();
        $params = array();
		$columns = 'rc.*, ic.name, ic.category_id, ic.parent ';

		extract($conditions);

		if(isset($branch)){
	            $where[] = " rc.id_branch = " . $branch." ";
		}
		if(isset($parent)){
	            $where[] = " ic.parent IN ( " . $parent . ") ";
		}

		$sql = "SELECT " . $columns . "
				FROM ".$this->relation_category_table." rc
				LEFT JOIN item_category ic ON rc.id_category = ic.category_id";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);
		$sql .= " GROUP BY rc.id_category ";
		if($order_by != false)
			$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}
	function delete_relation_category_by_branch($id){
		$this->db->where('id_branch', $id);
		return $this->db->delete($this->relation_category_table);
	}*/

	function get_companies($conditions) {
		$order_by = "cb.registered_company ASC";
		$where = array();
		$params = array();
		$type_company = 'company';

		extract($conditions);

		if (isset($multiple_sort_by)) {
			foreach ($multiple_sort_by as $sort_item) {
			$sort_item = explode('-', $sort_item);
			$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if (isset($seller)) {
			$where[] = " cb.id_user=?";
			$params[] = $seller;
		}

		if ($type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($added_start)) {
			$where[] = " DATE(registered_company)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(registered_company)<=?";
			$params[] = $added_finish;
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

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
		}

		if (isset($type)) {
			$where[] = " cb.id_type = ? ";
			$params[] = $type;
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

		if (isset($get_administration_info) && $get_administration_info) {
			$administration_fields = " ,ct.name_type,(CONCAT_WS(', ',z.city,st.state)) as city ";
			$administration_tables = " LEFT JOIN zips z ON z.id = cb.id_city
                                       LEFT JOIN states st ON st.id = cb.id_state
									   LEFT JOIN $this->company_type_table ct ON cb.id_type = ct.id_type ";
		}

		$sql = "SELECT cb.*,
						pc.country, u.user_group, u.registration_date, CONCAT(u.fname, ' ', u.lname) as user_name
					$administration_fields
					$rel
				FROM $this->company_base_table cb
				INNER JOIN $this->users_table u ON cb.id_user = u.idu
				LEFT JOIN $this->country_table pc ON cb.id_country = pc.id
				$administration_tables";

		if (count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY cb.id_company";
		$sql .= " ORDER BY " . $order_by;

		if(isset($limit)) {
			$sql .= " LIMIT " . $limit;
		}

		return $this->db->query_all($sql, $params);
	}

	function count_companies($conditions) {
		$where = array();
		$params = array();
		$type_company = 'company';

		extract($conditions);

		if (isset($seller)) {
			$where[] = " cb.id_user=?";
			$params[] = $seller;
		}

		if ($type_company != "all") {
			$where[] = " cb.type_company = ? ";
			$params[] = $type_company;
		}

		if (isset($added_start)) {
			$where[] = " DATE(registered_company)>=?";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(registered_company)<=?";
			$params[] = $added_finish;
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

		if (isset($visibility)) {
			$where[] = " cb.visible_company = ? ";
			$params[] = $visibility;
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

        if (isset($type)) {
			$where[] = " cb.id_type = ? ";
			$params[] = $type;
		}

		$sql = "SELECT count(DISTINCT cb.id_company ) as counter
				FROM $this->company_base_table cb
				INNER JOIN $this->users_table u ON cb.id_user = u.idu";

		if (!empty($where)){
			$sql .= " WHERE " . implode(" AND", $where);
		}

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}
}
