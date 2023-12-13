<?php

/**
 * company_staff_model.php
 *
 * company staff model
 *
 * @author Cravciuc Andrei
 */
class Company_Staff_Model extends TinyMVC_Model {

	var $obj;
	private $company_sgroups = "company_staff_groups";
	private $sgroup_rights = "company_staff_group_rights";
	private $users_table = "users";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	function set_company_sgroup($data=array()){
		if(empty($data))
			return false;
		$this->db->insert($this->company_sgroups, $data);
		return $this->db->last_insert_id();
	}

	public function get_staff_group($id_sgroup, $id_company){
		$sql = "SELECT 	*
				FROM ".$this->company_sgroups."
				WHERE id_sgroup = ? AND id_company = ?";
		return $this->db->query_one($sql, array($id_sgroup, $id_company));
	}

	public function exist_staff_group($id_sgroup, $id_company){
		$sql = "SELECT 	COUNT(*) as counter
				FROM ".$this->company_sgroups."
				WHERE id_sgroup = ? AND id_company = ? LIMIT 1";
		$query = $this->db->query_one($sql, array($id_sgroup, $id_company));
		return $query['counter'];
	}

	public function exist_sgroup_rights($id_sgroup){
		$sql = "SELECT 	COUNT(*) as counter
				FROM ".$this->sgroup_rights."
				WHERE id_sgroup = ? LIMIT 1";
		$query = $this->db->query_one($sql, array($id_sgroup));
		return $query['counter'];
	}

	public function exist_relation($id_sgroup, $id_right){
		$sql = "SELECT 	COUNT(*) as counter
				FROM ".$this->sgroup_rights."
				WHERE id_sgroup = ? AND id_right = ? LIMIT 1";
		$query = $this->db->query_one($sql, array($id_sgroup, $id_right));
		return $query['counter'];
	}

	public function exist_in_sgroup_users($id_sgroup){
		$sql = "SELECT COUNT(csug.id_user) as counter
				FROM " . $this->company_sgroups . " csg
				LEFT JOIN company_staff_user_group csug ON csg.id_sgroup = csug.id_group
				WHERE csg.id_sgroup = ? ";

		$query = $this->db->query_one($sql, array($id_sgroup));
		return $query['counter'];
	}

	function set_relation_gr($id_sgroup, $id_right){
		return $this->db->insert($this->sgroup_rights, array('id_sgroup' => $id_sgroup,'id_right' => $id_right));
	}

	public function delete_relation_gr($id_sgroup, $id_right){
		$this->db->where('id_sgroup', $id_sgroup);
		$this->db->where('id_right', $id_right);
		return $this->db->delete($this->sgroup_rights);
	}

	public function update_company_sgroup($id_sgroup, $id_company, $data=array()){
		$this->db->where('id_sgroup', $id_sgroup);
		$this->db->where('id_company', $id_company);
		return $this->db->update($this->company_sgroups, $data);
	}

	public function delete_company_sgroup($id_sgroup, $id_company){
        $params = $id_sgroup = getArrayFromString($id_sgroup);
        $params[] = $id_company;

		$sql = "DELETE FROM ".$this->company_sgroups."
				WHERE id_sgroup IN ( " . implode(',', array_fill(0, count($id_sgroup), '?')) . " ) AND id_company = ? ";

		return $this->db->query($sql, $params);
	}

	public function delete_company_suser($id_user, $id_company){
		$sql = "DELETE FROM users
				WHERE idu = ? AND id_company = ? ";
		return $this->db->query($sql, array($id_user, $id_company));
	}

	public function delete_company_suser_group_relation($id_user){
		$sql = "DELETE FROM company_staff_user_group
				WHERE id_user = ? ";
		return $this->db->query($sql, array($id_user));
	}

	public function get_staff_groups($conditions){
		$page = 0;
		$per_p = 20;
		$order_by = " id_sgroup ASC ";
		$use_limit = false;
		$where = array();
		$params = array();

		extract($conditions);
		switch($sort_by){
			case 'title_asc': $order_by = 'name_sgroup ASC'; break;
			case 'title_desc': $order_by = 'name_sgroup DESC'; break;
			case 'rand': $order_by = ' RAND()'; break;
		}

		if(isset($id_company)){
			$where[] = ' id_company = ?';
			$params[] = $id_company;
		}

		if(isset($keywords)){
			$order_by .= ", REL DESC";
			$where[] = " MATCH (name_sgroup, description_sgroup) AGAINST (?) ";
			$params[] = $keywords;
			$rel = " , MATCH (name_sgroup, description_sgroup) AGAINST (?) as REL ";
            array_unshift($params, $keywords);
		}

		$sql .= "SELECT *
						$rel, COUNT(csug.id_user) as users_count
				 FROM " . $this->company_sgroups . ' csg ';

		$sql .= " LEFT JOIN company_staff_user_group csug ON csg.id_sgroup = csug.id_group ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY csg.id_sgroup";
		$sql .= " ORDER BY " . $order_by;
		if($use_limit){
			$pages = ceil($count/$per_p);

			if ($page > $pages)
				$page = $pages;

			$start = ($page-1)*$per_p;

			if($start < 0) $start = 0;

			$sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
		}

		return $this->db->query_all($sql, $params);
	}

	public function count_staff_groups($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($id_company)){
			$where[] = ' id_company = ?';
			$params[] = $id_company;
		}

		if(isset($keywords)){
			$where[] = " MATCH (name_sgroup, description_sgroup) AGAINST (?) ";
			$params[] = $keywords;
		}
		$sql .= "SELECT COUNT(*) as counter
				 FROM " . $this->company_sgroups . ' csg ';

		$sql .= " LEFT JOIN company_staff_user_group csug ON csg.id_sgroup = csug.id_group ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " GROUP BY csg.id_sgroup";

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function get_staff_group_rights($id_sgroup){
		$sql = "SELECT 	*
				FROM ".$this->sgroup_rights."
				WHERE id_sgroup = ?";
		return $this->db->query_all($sql, array($id_sgroup));
	}

	function set_user_group($id_user, $id_group){
		$sql = "INSERT INTO company_staff_user_group (id_user, id_group)
				VALUES (?, ?)
				ON DUPLICATE KEY UPDATE
				id_group = ?";
		return $this->db->query($sql, [$id_user, $id_group, $id_group]);
	}

	function get_staffs_of_company($id_company){
		$sql = "SELECT u.idu
				FROM company_base cb
				INNER JOIN company_users cu ON cu.id_company = cb.id_company
				INNER JOIN users u ON cu.id_user = u.idu AND u.user_type = 'users_staff'
				WHERE cb.id_company = ?";
		return $this->db->query_all($sql, array($id_company));
	}

	function get_staffs_with_rights($id_company, $rights_aliases){
        $params = [$id_company];

        $rights_aliases = getArrayFromString($rights_aliases);
        array_push($params, ...$rights_aliases);

		$sql = "SELECT u.fname, u.lname, u.idu
				FROM company_staff_groups csg
				INNER JOIN company_staff_group_rights usgr ON usgr.id_sgroup = csg.id_sgroup
				INNER JOIN rights r ON r.idright = usgr.id_right
				INNER JOIN company_staff_user_group csug ON csug.id_group = usgr.id_sgroup
				INNER JOIN users u ON u.idu = csug.id_user
				WHERE csg.id_company = ? AND u.logged = '1' AND r.r_alias IN ('" . implode(',', array_fill(0, count($rights_aliases), '?')) . "')";

		return $this->db->query_all($sql, $params);
	}
}
