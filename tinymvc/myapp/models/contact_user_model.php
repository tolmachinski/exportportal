<?php

/**
 * @deprecated in favor of \User_Contacts_Model
 */
class Contact_User_Model extends TinyMVC_Model
{

	var $obj;
	private $contact_user_table = "contact_user";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function set_contact($data){
		if(!count($data))
			return false;
		$this->db->insert($this->contact_user_table, $data);
		return $this->db->last_insert_id();
	}

	public function get_contacts_by_id($id){

		$sql = "SELECT GROUP_CONCAT(id_contact_user) as id_contact
				FROM " . $this->contact_user_table . "
				WHERE id_user = ?";

		return $this->db->query_one($sql, $id);
	}

	public function is_in_contact($id_user, $id_contact_user){
		$sql = "SELECT id_contact
				FROM contact_user
				WHERE id_user = ? AND id_contact_user = ?";
		$temp = $this->db->query_one($sql, array($id_user, $id_contact_user));
		return (($temp['id_contact']) ? $temp['id_contact'] : false);
	}

	public function delete_contact($id_user, $id_contact_user){
		$sql = 'DELETE FROM '.$this->contact_user_table.' WHERE id_user = ? AND id_contact_user = ?';
		return $this->db->query($sql, array($id_user, $id_contact_user));
	}

	public function can_add_user($user){
		$sql = "SELECT COUNT(*) as counter
				FROM users
				WHERE idu = ? AND user_type in ('user', 'shipper')";
		$temp = $this->db->query_one($sql, array($user));
		return (bool)$temp['counter'];
	}

	public function get_contacts($conditions){
		$order_by = "cu.id_user DESC";

		extract($conditions);

        $from = (int) ($from ?? 0);
        $per_p = (int) ($per_p ?? 5);

		$sql = "SELECT u.idu, u.user_photo, u.status, CONCAT(u.fname,' ',u.lname) as user_name, ug.gr_name
				FROM $this->contact_user_table cu
				LEFT JOIN users u ON cu.id_contact_user = u.idu
				LEFT JOIN user_groups ug ON u.user_group = ug.idgroup
				WHERE cu.id_user = ?";

		$sql .= " ORDER BY " . $order_by;

		$sql .= " LIMIT " . $from . ", " . $per_p;

		return $this->db->query_all($sql, array($id_user));
	}

	public function get_count_contacts($conditions){
		extract($conditions);

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->contact_user_table." cu
				WHERE cu.id_user = ?";

		$temp = $this->db->query_one($sql, array($id_user));
		return $temp['counter'];
	}

	public function get_all_contacts($conditions){
		extract($conditions);

		$where = array("cu.id_user = ?");
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
			$where[] = " u.idu NOT IN (" . implode(',', array_fill(0, count($not_user_id), '?')) . ") ";
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

		$sql = "SELECT u.idu as user_id, u.user_group, CONCAT(u.fname,' ',u.lname) as user_name, u.user_photo, cb.name_company as company_name, u.logged $rel
				FROM " . $this->contact_user_table . " cu
				INNER JOIN users u ON cu.id_contact_user = u.idu
				LEFT JOIN company_base cb ON cb.id_user = cu.id_contact_user ";

		if(!empty($where))
			$sql .= " WHERE " . implode(' AND ', $where);

		$sql .= ' GROUP BY u.idu';

		if($order_by)
			$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}
}
