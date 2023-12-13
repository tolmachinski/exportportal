<?php
/**
 * followers_model.php
 * followers model
 * @author
 *
 * @deprecated in favor of \User_Followers_Model
 */
class Followers_Model extends TinyMVC_Model {

	var $obj;
	private $user_followers_table = "user_followers";
	private $users_table = "users";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	function set_follower($data){
		$this->db->insert($this->user_followers_table, $data);
		return $this->db->last_insert_id();
	}

	function get_user_followed($user){
		$sql = "SELECT GROUP_CONCAT(f.id_user) as user_followed
				FROM ".$this->user_followers_table." f
				WHERE id_user_follower = ?";

		$followers = $this->db->query_one($sql, array($user));
		return $followers['user_followed'];
	}

	function get_users_followed($id_user, $conditions = array()){
		extract($conditions);

        $params = [$id_user];

		$sql = "SELECT  uf.*,
						u.*, CONCAT_WS(' ',u.fname,u.lname) as user_name,
						ug.gr_name
						$rel
			FROM user_followers uf
			LEFT JOIN users u ON uf.id_user = u.idu
			LEFT JOIN user_groups ug ON ug.idgroup = u.user_group
			WHERE uf.id_user_follower = ?";

		if(!empty($keywords)){
			$sql .= " AND (u.lname LIKE ? OR u.fname LIKE ? OR u.email LIKE ?) ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
		}

		if(isset($limit))
			$sql .= " LIMIT " . $limit;

		return $this->db->query_all($sql, $params);
	}

	function get_user_followers($id_user, $conditions = array()){
		extract($conditions);

        $params = [$id_user];

		$sql = "SELECT  uf.*,
						u.*,CONCAT_WS(' ',u.fname,u.lname) as user_name, u.user_group,
						ug.gr_name
			FROM user_followers uf
			LEFT JOIN users u ON uf.id_user_follower = u.idu
			LEFT JOIN user_groups ug ON ug.idgroup = u.user_group
			WHERE uf.id_user = ? ";

		if(!empty($keywords)){
			$sql .= " AND (u.lname LIKE ? OR u.fname LIKE ? OR u.email LIKE ?) ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
		}

		if (isset($limit)) {
			$sql .= " LIMIT " . $limit;
        }

		return $this->db->query_all($sql, $params);
	}

	function get_count_user_followed($id_user, $conditions = array()){
		extract($conditions);

        $params = [$id_user];

		$sql = "SELECT COUNT(*) as counter
			FROM user_followers uf
			LEFT JOIN users u ON uf.id_user = u.idu
			WHERE uf.id_user_follower = ?";

		if(!empty($keywords)){
			$sql .= " AND (u.lname LIKE ? OR u.fname LIKE ? OR u.email LIKE ?) ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
		}

		$users = $this->db->query_one($sql, $params);
		return $users['counter'];
	}

	function get_count_user_followers($id_user, array $conditions = array()){
		extract($conditions);

        $params = [$id_user];

		$sql = "SELECT COUNT(*) as counter
			FROM user_followers uf
			LEFT JOIN users u ON uf.id_user_follower = u.idu
			WHERE uf.id_user = ?";

		if(!empty($keywords)){
			$sql .= " AND (u.lname LIKE ? OR u.fname LIKE ? OR u.email LIKE ?) ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
		}

		$users = $this->db->query_one($sql, array($id_user));
		return $users['counter'];
	}

	function delete_followed($follower, $followed){
		$this->db->where('id_user_follower = ? AND id_user = ?', array( $follower, $followed));
		return $this->db->delete($this->user_followers_table);
	}

    public function get_followers_emails($user)
    {
        $this->db->select('u.email as email');
        $this->db->from("{$this->user_followers_table} f");
        $this->db->join("{$this->users_table} u", "f.id_user_follower = u.idu", 'left');
        $this->db->where('f.id_user = ?', (int) $user);
        $data = $emails = $this->db->query_all();
        if(empty($data)) {
            return "";
        }

        return implode(\App\Common\EMAIL_DELIMITER, array_column($data, 'email'));
    }

    /**
     * Get followers id and email by id user
     *
     * @param $user - id of the user
     */
    public function getFollowersEmails(int $user):array
    {
        $this->db->select('u.idu, u.email as email');
        $this->db->from("{$this->user_followers_table} f");
        $this->db->join("{$this->users_table} u", "f.id_user_follower = u.idu", 'left');
        $this->db->where('f.id_user = ?', (int) $user);
        $data = $this->db->query_all();

        return empty($data) ? [] : $data;
	}

	/*function get_already_follow($he, $i){
		$sql = "SELECT COUNT(*) as counter
			FROM ".$this->user_followers_table."
			WHERE id_user = ? AND id_user_follower = ?";

		$counter = $this->db->query_one($sql, array($he, $i));
		return $counter['counter'];
	}*/

	function get_followers_for_messages($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(!empty($id_user)){
			$where[] = "uf.id_user = ?";
			$params[] = $id_user;
		}

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

		$rel = "";
		if(!empty($keywords)){
			$order_by = " REL_tags DESC ";
			$where[] = " MATCH (u.fname, u.lname, u.email) AGAINST (?)";
			$params[] = $keywords;
			$rel = " , MATCH (u.fname, u.lname, u.email) AGAINST (?) as REL_tags";
            array_unshift($params, $keywords);
		}

		$sql = "SELECT  uf.id_user_follower as user_id,
						CONCAT_WS(' ',u.fname,u.lname) as user_name, u.user_photo, u.user_group, u.logged,
						cb.name_company as company_name
						$rel
				FROM user_followers uf
				INNER JOIN users u ON uf.id_user_follower = u.idu
				LEFT JOIN company_base cb ON uf.id_user_follower = cb.id_user";

		if(count($where))
			$sql .= " WHERE " . implode(' AND ', $where);


		$sql .= ' GROUP BY uf.id_user_follower';

		if($order_by)
			$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
	}

	function get_followers($conditions){
		$page = 1;
		$where = array();
		$params = array();
		$left_join = 'LEFT JOIN users u ON uf.id_user = u.idu
						LEFT JOIN user_groups ug ON ug.idgroup = u.user_group';

		extract($conditions);

		if(!empty($id_user_follower)){
			$where[] = 'uf.id_user_follower = ?';
			$params[] = $id_user_follower;
		}

		if(!empty($not_user_id)){
			$where[] = 'uf.id_user_follower <> ?';
			$params[] = $not_user_id;
		}

		if(!empty($id_user)){
			$where[] = 'uf.id_user = ?';
			$params[] = $id_user;
			$left_join = ' LEFT JOIN users u ON uf.id_user_follower = u.idu
							LEFT JOIN user_groups ug ON ug.idgroup = u.user_group';
		}

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

		$sql = "SELECT  uf.*,
						u.*, CONCAT_WS(' ',u.fname,u.lname) as user_name, u.idu as user_id,
						ug.gr_name
				FROM user_followers uf
				$left_join";

		if(!empty($keywords)){
			$where[] = " (u.lname LIKE ? OR u.fname LIKE ? OR u.email LIKE ?) ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
		}

		$sql .= " WHERE " . implode(' AND ', $where);

		if(isset($per_p)){
			if(!isset($count)) {
				$count = $this->count_followers($conditions);
            }

			$start = ($page-1)*$per_p;
			if($start < 0) {
				$start = 0;
            }

			$sql .= " LIMIT " . $start ;

			if($per_p > 0) {
				$sql .= ',' . $per_p;
            }
		}

		return $this->db->query_all($sql, $params);
	}

	function count_followers($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(!empty($id_user_follower)){
			$where[] = "uf.id_user_follower = ?";
			$params[] = $id_user_follower;
		}

		if(!empty($id_user)){
			$where[] = "uf.id_user = ?";
			$params[] = $id_user;
		}

		if(!empty($country)){

			$where[] = ' ua.country=? ';
			$params[] = $country;

			if(!empty($state)){
				$where[] = ' ua.state=? ';
				$params[] = $state;
			}

			if(!empty($city)){
				$where[] = ' ua.city=? ';
				$params[] = $city;
			}
		}

		$sql = "SELECT COUNT(*) as counter
			FROM user_followers uf
			LEFT JOIN users u ON uf.id_user = u.idu";

		if(!empty($keywords)){
			$where[] = " (u.lname LIKE ? OR u.fname LIKE ? OR u.email LIKE ?) ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
		}
		$sql .= " WHERE " . implode(' AND ', $where);

		$rez = $this->db->query_one($sql, $params);

		return $rez['counter'];
	}
}

