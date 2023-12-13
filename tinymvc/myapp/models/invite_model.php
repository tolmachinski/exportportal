<?php

class Invite_Model extends TinyMVC_Model {

    var $obj;
    private $users_invites_table = "users_invites";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function get_invite_by_condition($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if (isset($type_invite)) {
			$where[] = " type_invite = ? ";
			$params[] = $type_invite;
		}

		if (isset($id_user)) {
			$where[] = " id_user = ? ";
			$params[] = $id_user;
		}

		if (isset($date_invite)) {
			$where[] = " DATE_FORMAT(date_invite, '%Y-%m-%d') = ? ";
			$params[] = $date_invite;
		}

		$sql = "SELECT *
				FROM $this->users_invites_table ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		return $this->db->query_one($sql, $params);
    }

	function set_user_invite($id_user, $type_invite, $count_invite){
		$sql = "INSERT INTO $this->users_invites_table ( id_user, type_invite, count_invite, date_invite)
				VALUES (?, ?, ?, curdate())
				ON DUPLICATE KEY UPDATE
				count_invite = ?, date_invite = curdate()";
		return $this->db->query($sql, [$id_user, $type_invite, $count_invite, $count_invite]);
	}

	public function exist_user_invite($type_invite, $id_user){

        $sql = "SELECT COUNT(*) as exist
			    FROM " . $this->users_invites_table . "
			    WHERE id_invite = ? AND id_user = ? ";

        $rez = $this->db->query_one($sql, array($type_invite, $id_user));
		return $rez['exist'];
    }
}
