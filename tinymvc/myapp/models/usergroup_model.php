<?php

/**
 * usergroup_model.php
 *
 * user's groups and rights system model
 *
 * @author Litra Andrei
 *
 * @deprecated in favor of \User_Groups_Model
 */
class UserGroup_Model extends TinyMVC_Model {

    // hold the current controller instance
    private $group_table = "user_groups";
    private $group_table_alias = "GROUPS";
    private $group_table_primary_key = "idgroup";
    private $rights_table = "rights";
    private $fields_table = "rights_fields";
    private $user_fields_table = "user_rights_info";
    private $ep_modules_table = "ep_modules";
    private $user_table = "users";
    private $relation_table = "usergroup_rights";
    private $user_rights_aditional = "user_rights_aditional";
    private $shipper_staff_group_rights_table = "shipper_staff_group_rights";
    private $shipper_staff_user_group_table = "shipper_staff_user_group";

    /**
	 * Returns the groups table name.
	 *
	 * @return string
	 */
    public function get_groups_table(): string
    {
        return $this->group_table;
    }

    /**
	 * Returns the groups table alias.
	 *
	 * @return string
	 */
    public function get_groups_table_alias(): string
    {
        return $this->group_table_alias;
    }

    /**
	 * Returns the groups table primary key.
	 *
	 * @return string
	 */
    public function get_groups_table_primary_key(): string
    {
        return $this->group_table_primary_key;
    }

    function setGroup($data){
        return empty($data) ? false : $this->db->insert($this->group_table, $data);
    }

    function set_aditional_rights($data){
        return empty($data) ? false : $this->db->insert($this->user_rights_aditional, $data);
    }

    function update_user_aditional_right($id_user, $id_right, $data){
        $this->db->where('id_user', $id_user);
        $this->db->where('id_right', $id_right);
        return $this->db->update($this->user_rights_aditional, $data);
    }

    function get_aditional_rights($id_user){
        $sql = "SELECT *
                FROM $this->user_rights_aditional ar
				LEFT JOIN $this->rights_table r ON ar.id_right = r.idright
                WHERE ar.id_user = ? ";

        return $this->db->query_all($sql, array($id_user));
    }

    function get_aditional_right($id_user, $id_right){
        $sql = "SELECT *
                FROM $this->user_rights_aditional ar
				LEFT JOIN $this->rights_table r ON ar.id_right = r.idright
                WHERE ar.id_user = ? AND ar.id_right = ? ";

        return $this->db->query_one($sql, array($id_user, $id_right));
    }

    function setRight($data){
        return empty($data) ? false : $this->db->insert($this->rights_table, $data);
    }

    function setRelation($data){
        return empty($data) ? false : $this->db->insert($this->relation_table, $data);
    }

    function getGroup($idgroup, $fields = '*'){
        $this->db->select($fields);
        $this->db->where('idgroup', $idgroup);
        $this->db->limit(1);

        return $this->db->get_one($this->group_table);
    }

    function existGroup($idgroup = null, $gr_name = null){
        if (null !== $idgroup){
            $this->db->where('idgroup', $idgroup);
        }

        if (null !== $gr_name) {
            $this->db->where_raw('LOWER(gr_name) = ?', strtolower($gr_name));
        }

        $this->db->select('COUNT(*) as exist');
        $this->db->limit(1);

        return $this->db->get_one($this->group_table)['exist'];
    }

    function existRight($idright = null, $r_name = null){
        if (null !== $idright) {
            $this->db->where('idright', $idright);
        }

        if (null !== $r_name) {
            $this->db->where_raw('LOWER(r_name) = ?', strtolower($r_name));
        }

        $this->db->select('COUNT(*) as exist');
        $this->db->limit(1);

        return $this->db->get_one($this->rights_table)['exist'];
    }

    function getGroups($conditions=array()){
    	$order_by = 'gr_priority';
    	$fields = '*';

    	extract($conditions);

        if (!empty($id_groups)) {
            $id_groups = getArrayFromString($id_groups);
            $this->db->in('idgroup', $id_groups);
        }

        if (!empty($gr_type)) {
            $gr_type = getArrayFromString($gr_type);
            $this->db->in('gr_type', $gr_type);
        }

        $this->db->select($fields);
        $this->db->orderby($order_by);

        return $this->db->get($this->group_table);
    }

    function getGroupsByType($conditions=array()){
        $order_by = 'gr_priority';
		$counter = true;

    	extract($conditions);

        $select_counter = "";
		$join_counter = "";
    	$where = $params = [];

    	if (isset($type)) {
            $type = getArrayFromString($type);
			$where[] = ' gr_type IN (' . implode(',', array_fill(0, count($type), '?')) . ') ';
            array_push($params, ...$type);
        }

		if ($counter) {
			$select_counter = ", COUNT(idu) as u_counter ";
			$join_counter = " LEFT JOIN users us ON us.user_group = ug.idgroup ";
		}

		$sql = "SELECT ug.* {$select_counter}
				FROM {$this->group_table} ug " .
				$join_counter;

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= "GROUP BY (ug.idgroup) ORDER BY {$order_by}";

        if (isset($limit)) {
        	$sql .= " LIMIT " . $limit;
        }

        return $this->db->query_all($sql, $params);
    }

	function countUsersByGroups($conditions = array()){
        $where_str = '';
		$company_info = false;
        $join = "";

		extract($conditions);

		$where = $params = [];

		if (isset($users_list)) {
            $users_list = getArrayFromString($users_list);
			$where[] = " u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ")";
            array_push($params, ...$users_list);
        }

        if (isset($group)) {
            $group = getArrayFromString($group);
			$where[] = " u.user_group IN (" . implode(',', array_fill(0, count($group), '?')) . ")";
            array_push($params, ...$group);
        }

		if (isset($status)) {
            $status = getArrayFromString($status);
			$where[] = " u.status IN(" . implode(', ', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

        if (isset($email_status)) {
            $email_status = getArrayFromString($email_status);
			$where[] = " u.email_status IN(" . implode(',', array_fill(0, count($email_status), '?')) . ")";
            array_push($params, ...$email_status);
        }

		if (isset($accreditation_files)) {
			$where[] = " u.accreditation_files = ? ";
			$params[] = $accreditation_files;
		}

		if (isset($logged)) {
			$where[] = " u.logged = ? ";
			$params[] = $logged;
		}

		if (isset($ip)) {
			$where[] = " u.user_ip = ? ";
			$params[] = $ip;
		}

		if (isset($user_find_type)) {
			$where[] = " u.user_find_type = ? ";
			$params[] = $user_find_type;

			if (isset($user_find_info)) {
				$where[] = " u.user_find_info = ? ";
				$params[] = $user_find_info;
			}
		}

		if (isset($fake_user)) {
			$where[] = " u.fake_user = ? ";
			$params[] = $fake_user;
		}

		if (isset($is_model)) {
			$where[] = " u.is_model = ? ";
			$params[] = $is_model;
		}

		if (isset($registration_start_date)) {
			$where[] = " DATE(u.registration_date) >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if (isset($registration_end_date)) {
			$where[] = " DATE(u.registration_date) <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

		if (isset($activity_start_date)) {
			$where[] = " DATE(u.last_active) >= ? ";
			$params[] = formatDate($activity_start_date, 'Y-m-d');
		}

		if (isset($activity_end_date)) {
			$where[] = " DATE(u.last_active) <= ? ";
			$params[] = formatDate($activity_end_date, 'Y-m-d');
        }

        if (isset($country)) {
            $where[] = " u.country = ?";
            $params[] = $country;
        }

        if (isset($country_list)) {
            $country_list = getArrayFromString($country_list);
            $where[] = " u.country IN (" . $country_list . ") ";
            array_push($params, ...$country_list);
        }

        if (isset($state)) {
            $where[] = " u.state = ?";
            $params[] = $state;
        }

        if (isset($city)) {
            $where[] = " u.city = ?";
            $params[] = $city;
        }

        if (isset($city_list)) {
            $city_list = getArrayFromString($city_list);
            $where[] = " u.city IN (" . implode(',', array_fill(0, count($city_list), '?')) . ") ";
            array_push($params, ...$city_list);
        }

		if (isset($keywords)) {
			$words = explode(" ", $keywords);
			foreach ($words as $word) {
				if (strlen($word) > 3) {
					$s_word[] = " (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ?)";
                    array_push($params, ...array_fill(0, 3, '%' . $word . '%'));
                }
			}

			if (!empty($s_word)) {
				$where[] = " (" . implode(" AND ", $s_word) . ")";
            }
		}

        if (!empty($where)) {
		    $where_str = " AND " . implode(" AND ", $where);
        }

        $where = array();

        if (isset($groups_list)) {
            $groups_list = getArrayFromString($groups_list);
			$where[] = " gr.gr_type IN (" . implode(',', array_fill(0, count($groups_list), '?')) . ")";
            array_push($params, ...$groups_list);
        }

        if ($additional) {
			$join .= " LEFT JOIN port_country pc ON u.country = pc.id ";

			if (isset($id_continent)) {
				$where[] = " pc.id_continent = ? ";
				$params[] = $id_continent;
			}
		}

		if ($company_info) {
			$join .= " LEFT JOIN company_base cb ON u.idu = cb.id_user ";
			$where[] = " cb.type_company = 'company' ";
		}

		if (!empty($gmap_bounds)) {
			$lng_operand = $gmap_bounds['nelng'] < $gmap_bounds['swlng']?" OR ":" AND ";
			$where[] = "(u.user_city_lng >= ? {$lng_operand} u.user_city_lng <= ?)";
			$where[] = "u.user_city_lat >= ? AND u.user_city_lat <= ?";
            array_push($params, ...[$gmap_bounds['swlng'], $gmap_bounds['nelng'], $gmap_bounds['swlat'], $gmap_bounds['nelat']]);
        }

        if (isset($on_crm)) {
			$where[] = 'u.zoho_id_record IS ' . ($on_crm ? 'NOT ' : '') . 'NULL';
        }

        if (isset($is_verified)) {
			$where[] = " u.is_verified = ? ";
			$params[] = $is_verified;
		}

        if (!empty($restrictedFrom) || !empty($restrictedTo)) {
            $joinWhere = [" users_blocking_statistics.`type` = 'restriction' "];

            if (!empty($restrictedFrom)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) >= '{$restrictedFrom}' ";
            }

            if (!empty($restrictedTo)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) <= '{$restrictedTo}' ";
            }

            $joinConditions = implode('AND', $joinWhere);

			$join .= " RIGHT JOIN (
                SELECT users_blocking_statistics.`id_user`
                FROM users_blocking_statistics
                WHERE {$joinConditions}
                GROUP BY users_blocking_statistics.`id_user`
            ) restrictions ON u.`idu` = restrictions.`id_user` ";
        }

        if (!empty($blockedFrom) || !empty($blockedTo)) {
            $joinWhere = [" users_blocking_statistics.`type` = 'blocking' "];

            if (!empty($blockedFrom)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) >= '{$blockedFrom}' ";
            }

            if (!empty($blockedTo)) {
                $joinWhere[] = " DATE(users_blocking_statistics.`blocking_date`) <= '{$blockedTo}' ";
            }

            $joinConditions = implode('AND', $joinWhere);

			$join .= " RIGHT JOIN (
                SELECT users_blocking_statistics.`id_user`
                FROM users_blocking_statistics
                WHERE {$joinConditions}
                GROUP BY users_blocking_statistics.`id_user`
            ) blocking ON u.`idu` = blocking.`id_user` ";
        }

		$sql = "SELECT gr.idgroup, gr.gr_name, COUNT(u.idu) as `counter`
                FROM user_groups gr
                LEFT JOIN users u on gr.idgroup = u.user_group {$where_str}
                {$join}";

        if(!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY gr.idgroup";

		return $this->db->query_all($sql, $params);
	}

    function updateGroup($idgroup, $data){
        $this->db->where('idgroup', $idgroup);
        return $this->db->update($this->group_table, $data);
    }

    function updateRight($idright, $data){
        $this->db->where('idright', $idright);
        return $this->db->update($this->rights_table, $data);
    }

    function deleteGroup($idgroup){
		$group = $this->getGroup($idgroup);
        $this->db->where('idgroup', $idgroup);
        if (!$this->db->delete($this->group_table)) {
            return false;
        }

        @unlink('public/img/groups/' . $group['stamp_pic']);
        $this->db->where('idgroup', $idgroup);
        return $this->db->delete($this->relation_table);
    }

    function getRModules($rights = true){
        $this->db->select('id_module, name_module');
        $this->db->from($this->ep_modules_table);
        $this->db->orderby('name_module');

        $categories = $this->db->query_all();
        $categories = arrayByKey($categories, 'id_module');

        if($rights){
            $allrights = $this->getRights();
            foreach($allrights as $r) {
                $categories[$r['r_module']]['rights'][] = $r;
            }
        }

        return $categories;
    }

    function getRights(){
        $this->db->orderby('r_name');
        return $this->db->get($this->rights_table);
	}

    function getRight($idright){
        $sql = "SELECT *
                FROM {$this->rights_table} r
				LEFT JOIN {$this->fields_table} f ON r.idright = f.id_right
                WHERE idright = ? ";

        return $this->db->query_one($sql, array($idright));
    }

    function get_right_by_alias($right_alias){
        $this->db->where('r_alias', $right_alias);
        $this->db->limit(1);

        return $this->db->get_one($this->rights_table);
    }

    function getSimpleRight($idright){
        $this->db->where('idright', $idright);
        $this->db->limit(1);

        return $this->db->get_one($this->rights_table);
    }

    function getSimpleRightForStaff($idright){
        $this->db->where('share_to_staff', 1);
        $this->db->where('idright', $idright);
        $this->db->limit(1);

        $this->db->get_one($this->rights_table);
    }
    // For admin rights administration page
    function getRightsByGroup($idgroup){
        $sql = "SELECT rig.*, em.name_module as name_module
                FROM {$this->rights_table} rig
                LEFT JOIN {$this->relation_table} rel
                    ON rel.idright = rig.idright
                LEFT JOIN {$this->ep_modules_table} em
                    ON rig.r_module = em.id_module
                WHERE idgroup = ?";
       return $this->db->query_all($sql, $idgroup);
    }

	function get_groups_rights($list_group = null){
        if(empty($list_group)){
            return [];
        }

        $list_group = getArrayFromString($list_group);

        $this->db->select("rig.*");
        $this->db->from("{$this->rights_table} rig");
        $this->db->join("{$this->relation_table} rel", "rel.idright = rig.idright", "inner");
        $this->db->in("rel.idgroup", $list_group, true);
        $this->db->groupby("rig.idright");
        $this->db->orderby("rig.r_name ASC");

        return $this->db->query_all() ?: [];
    }

    function getRightsByGroupForStaff($idgroup){
        $sql = "SELECT rig.*, em.name_module as name_module
                FROM {$this->rights_table} rig
                LEFT JOIN {$this->relation_table} rel
                    ON rel.idright = rig.idright
                LEFT JOIN {$this->ep_modules_table} em
                    ON rig.r_module = em.id_module
                WHERE rig.share_to_staff = 1 AND idgroup = ?";
       return $this->db->query_all($sql, [$idgroup]);
    }
    // For session array
    function getUserRights($idgroup, $condition = array()){
        $columns = "rig.r_alias";

        extract($condition);

        $this->db->select($columns);
        $this->db->from("{$this->rights_table} rig");
        $this->db->join("{$this->relation_table} as rel", "rel.idright = rig.idright", "left");
        $this->db->where("rel.idgroup = ? ", $idgroup);

        if(isset($for_pending_user)){
            $this->db->where("rig.for_pending_user = ? ", $for_pending_user);
        }

        $rights = $this->db->query_all();
        return array_column($rights, 'r_alias');
    }
    // For session array

    // For session array
    function getUserRightsByUserId($id_user, $condition = array()){
        $columns = "rig.r_alias";

        extract($condition);

        $this->db->select($columns);
        $this->db->from("{$this->rights_table} rig");
        $this->db->join("{$this->relation_table} as rel", "rel.idright = rig.idright", "left");
        $this->db->join("{$this->user_table} as u", "u.user_group = rel.idgroup", "left");
        $this->db->where("u.idu = ? ", (int) $id_user);

        if(isset($for_pending_user)){
            $this->db->where("rig.for_pending_user = ? ", $for_pending_user);
        }

        $rights = $this->db->query_all();
        return array_column($rights, 'r_alias');
    }

    // For session array
    function getCompanyStaffUserRights($id_user){
        $sql = "SELECT rig.r_alias
                FROM rights rig
                LEFT JOIN company_staff_group_rights sgr ON sgr.id_right = rig.idright
                LEFT JOIN company_staff_user_group sug ON sgr.id_sgroup = sug.id_group
                WHERE sug.id_user = ?";
        $rights = $this->db->query_all($sql, [$id_user]);

        return array_column($rights, 'r_alias');
    }

    function getRightGroupRelation($condition = array()){
        $optimize = true;

        $this->db->select("*");
        $this->db->from($this->relation_table);

		extract($condition);

		if(isset($groups)){
            $this->db->in("idgroup", $groups);
		}

        $results = $this->db->query_all();

        if(empty($results)){
            return array();
        }

		if($optimize){
			$optimized = array();
			foreach($results as $record){
				$optimized[$record['idgroup']][] = $record['idright'];
            }

			return $optimized;
        }

        return $results;
    }

    function deleteRights($list = []){
        if (empty($list)) {
            return false;
        }

        $list = getArrayFromString($list);
        $this->db->in('idright', $list);

        if ($rez = $this->db->delete($this->rights_table)) {
            $this->db->in('idright', $list);
            $this->db->delete($this->relation_table);

			$this->deleteFieldByRights($list);
        }

        return $rez;
    }

    function deleteRelation($idgroup, $idright){
        $this->db->where_raw('idgroup = ? AND idright = ?', array($idgroup, $idright));
        return $this->db->delete($this->relation_table);
    }

	/**
	* right's fields
	*/
	function getField($idright){
        $this->db->where('id_right', $idright);
        $this->db->limit(1);

        return $this->db->get_one($this->fields_table);
	}

	function getFiledsByGroup($idgroup, $type = null){
        $params = [$idgroup];

		$sql = "SELECT rf.*
	            FROM $this->fields_table rf
	            LEFT JOIN $this->relation_table rel ON rf.id_right = rel.idright
	            WHERE rel.idgroup = ? ";

		if ($type != null) {
            $type = getArrayFromString($type);
			$sql .= " AND rf.type IN (" . implode(',', array_fill(0, count($type), '?')) . ")";
            array_push($params, ...$type);
        }

		return $this->db->query_all($sql, $params);
	}

	function getAditionalFiledsByUser($id_user, $type = null){
        $params = [$id_user];

		$sql = "SELECT rf.*
	            FROM $this->fields_table rf
	            LEFT JOIN $this->user_rights_aditional ura ON rf.id_right = ura.id_right
	            WHERE ura.id_user = ? ";

		if (null !== $type) {
            $type = getArrayFromString($type);
			$sql .= " AND rf.type IN (" . implode(',', array_fill(0, count($type), '?')) . ")";
            array_push($params, ...$type);
        }

		return $this->db->query_all($sql, $params);
	}

	function setField($field){
        return $this->db->insert($this->fields_table, $field);
	}

	function updateField($id_right, $data){
        $this->db->where('id_right', $id_right);
        return $this->db->update($this->fields_table, $data);
    }

	function deleteField($id_right){
        $this->db->where('id_right', $id_right);
        return $this->db->delete($this->fields_table);
    }

	function deleteFieldByRights($right_list){
        $right_list = getArrayFromString($right_list);
        $this->db->in('id_right', $right_list);

        return $this->db->delete($this->fields_table);
    }

	/**
	* right fields of the user
	*/
	function getUsersRightFields($id_user){
		$sql = "SELECT urf.id_right ,rf.name_field, urf.value_field
	            FROM {$this->user_fields_table} urf
	            LEFT JOIN {$this->fields_table} rf ON rf.id_right = urf.id_right
	            WHERE urf.id_user = ?";

        return array_column($this->db->query_all($sql, [$id_user]), 'value_field', 'id_right');
	}

	function get_users_right($id_user){
		$sql = "SELECT urf.id_right, urf.value_field, r.r_name
				FROM $this->user_fields_table urf
				LEFT JOIN  $this->rights_table r ON r.idright = urf.id_right
				WHERE urf.id_user = ?";
       	return $this->db->query_all($sql, [$id_user]);
	}

	function get_user_right($id_user, $id_right){
		$sql = "SELECT urf.id_right, urf.value_field, r.r_name
				FROM $this->user_fields_table urf
				LEFT JOIN $this->rights_table r ON r.idright = urf.id_right
				WHERE urf.id_user = ? AND  urf.id_right = ?";
       	return $this->db->query_one($sql, [$id_user, $id_right]);
	}

	function setUsersRightFields($id_user, $fileds_values){

		$sql = "INSERT INTO {$this->user_fields_table} (`id_user`, `id_right`, `value_field`)
				VALUES ";

        $data = [];
		foreach($fileds_values as $id_right => $value){
			if (!empty($value)) {
                $data[] = [
                    'id_user'       => $id_user,
                    'id_right'      => $id_right,
                    'value_field'   => cleanInput(trim($value)),
                ];
            }
		}

        return empty($data) ? false : $this->db->insert_batch($this->user_fields_table, $data);
	}

	function updateUserRightValue($id_user, $id_right, $value){
		$this->db->where('id_user = ?', $id_user);
		$this->db->where('id_right = ?', $id_right);
		return $this->db->update($this->user_fields_table, array('value_field' => $value));
	}

    function deleteUserRightsFields($id_user){
        $this->db->where('id_user', $id_user);
        return $this->db->delete($this->user_fields_table);
    }

	function get_user_rights_fields_value($id_user, $conditions){
    	extract($conditions);

        $where = ["uf.id_user = ?"];
    	$params = [$id_user];

    	if (isset($type)) {
            $type = getArrayFromString($type);
			$where[] = " rf.type IN (" . implode(',', array_fill(0, count($type), '?')) . ") ";
            array_push($params, ...$type);
        }

		$sql = "SELECT uf.* , rf.name_field, rf.icon, rf.type
				FROM $this->user_fields_table uf
				LEFT JOIN $this->fields_table rf ON uf.id_right = rf.id_right ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        return $this->db->query_all($sql, $params);
    }

    function getShipperStaffUserRights($id_user)
    {
        $sql = "SELECT rig.r_alias
                FROM rights rig
                LEFT JOIN $this->shipper_staff_group_rights_table sgr ON sgr.id_right = rig.idright
                LEFT JOIN $this->shipper_staff_user_group_table sug ON sgr.id_sgroup = sug.id_group
                WHERE sug.id_user = ?";

        $rights = $this->db->query_all($sql, [$id_user]);

        return array_column($rights, 'r_alias');
    }
}

