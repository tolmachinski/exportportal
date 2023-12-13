<?php

/**
 * @deprecated v2.30.6 at 2021-12-23 in favor of {@see \User_Cancellation_Requests_Model}
 */
class User_Cancel_Model extends TinyMVC_Model
{
	private $user_close_requests_table = "user_close_requests";

    public function set_close_request($data){
        return $this->db->insert($this->user_close_requests_table, $data);
    }

	function get_request_last_id(){
		$sql = "SELECT idreq
				FROM {$this->user_close_requests_table}
				ORDER BY idreq DESC
				LIMIT 0,1";

		return $this->db->query_one($sql)['idreq'] ?: 0;
	}

	function exist_request($id_request){
		$sql = "SELECT COUNT(*) as counter
				FROM {$this->user_close_requests_table}
				WHERE idreq = ?";

		return $this->db->query_one($sql, array($id_request))['counter'];
	}

	function get_count_new_requests($id_request){
		$sql = "SELECT COUNT(*) as counter
				FROM {$this->user_close_requests_table}
				WHERE idreq > ? ";

		return $this->db->query_one($sql, array($id_request))['counter'];
	}

    public function get_close_request($conditions){
		$this->db->select("cr.*, CONCAT(u.fname, ' ',u.lname) as fullname, u.email, u.logged, u.user_group, gr.gr_type, gr.gr_name");
		$this->db->from("{$this->user_close_requests_table} cr");
		$this->db->join("users u", "cr.user = u.idu", "inner");
		$this->db->join("user_groups gr", "u.user_group = gr.idgroup", "inner");

		extract($conditions);

		if(isset($idreq)){
			$this->db->where("cr.idreq = ?", $idreq);
        }

		if(isset($user)){
			$this->db->where("cr.user = ?", $user);
        }

		if(isset($status_list)){
            $status_list = getArrayFromString($status_list);

			$this->db->in("cr.status", $status_list);
        }

        if(isset($token)){
            $this->db->where("cr.confirmation_token", $token);
        }

		return $this->db->get_one();
    }

	public function get_user_close_by_status($conditions){
        extract($conditions);

		$where = $params = [];

		if(isset($close_date)){
			$where[] = " DATE(close_date) < CURDATE() ";
		}

		if(isset($status)){
            $where[] = " status = ? ";
            $params[] = $status;
        }

		$sql = "SELECT GROUP_CONCAT(user SEPARATOR ',') as users_list
                FROM {$this->user_close_requests_table}";

		if (!empty($where)) {
        	$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['users_list'];
	}

    public function get_close_requests($conditions){
		$page = 0;
		$per_p = 20;
		$order_by = "start_date DESC";

		extract($conditions);

		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($user)){
            $where[] = " req.user = ? ";
            $params[] = $user;
        }

		if(isset($status)){
            $where[] = " req.status = ? ";
            $params[] = $status;
        }

		if(isset($start_from)){
            $where[] = ' DATE(req.start_date) >= ?';
            $params[] = $start_from;
        }

        if(isset($start_to)){
            $where[] = ' DATE(req.start_date) <= ?';
            $params[] = $start_to;
        }

		if(isset($close_date)){
			$where[] = ' DATE(req.close_date) < CURDATE()';
		}

		if(isset($keywords)){
			$where[] = " us.lname LIKE ? OR us.fname LIKE ? OR us.email LIKE ? ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
        }

        $sql = "SELECT req.*, CONCAT(us.fname, ' ',us.lname) as fullname, us.email, us.status as user_status, us.logged, gr.gr_type, gr.gr_name
                FROM ".$this->user_close_requests_table." req
                INNER JOIN users us ON req.user = us.idu
                INNER JOIN user_groups gr ON us.user_group = gr.idgroup";

		if (!empty($where)) {
        	$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY ".$order_by;

		if (!isset($count)) {
			$count = $this->counter_close_requests_by_conditions($conditions);
        }

		$pages = ceil($count/$per_p);

		if(!isset($start)){
	        if ($page > $pages) $page = $pages;
	        $start = ($page-1)*$per_p;

	        if($start < 0) $start = 0;
		}

		$sql .= " LIMIT " . $start ;

		if($per_p > 0)
			$sql .= "," . $per_p;

        return $this->db->query_all($sql, $params);
    }

	public function counter_close_requests_by_conditions($conditions){
        extract($conditions);

        $where = $params = [];

		if(isset($status)){
            $where[] = " req.status = ? ";
            $params[] = $status;
        }

		if(isset($user)){
            $where[] = " req.user = ? ";
            $params[] = $user;
        }

		if(isset($start_from)){
            $where[] = ' DATE(req.start_date) >= ?';
            $params[] = $start_from;
        }

        if(isset($start_to)){
            $where[] = ' DATE(req.start_date) <= ?';
            $params[] = $start_to;
        }

		if(isset($keywords)){
			$where[] = " lname LIKE ? OR fname LIKE ? OR us.email LIKE ? ";
            array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
        }

		$sql = "SELECT COUNT(*) as counter
                FROM ".$this->user_close_requests_table." req
                INNER JOIN users us ON req.user = us.idu ";

        if (!empty($where)) {
        	$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

    public function update_close_request($idreq, $data){
        $this->db->where('idreq' , $idreq);
        return $this->db->update($this->user_close_requests_table, $data);
    }

	function update_users_status($data){
        $data['id_users_list'] = getArrayFromString($data['id_users_list']);
        $this->db->in('user', $data['id_users_list']);
        return $this->db->update($this->user_close_requests_table, ['status' => $data['status']]);
    }

    public function get_simple_request(int $id_request){
        $this->db->from($this->user_close_requests_table);
        $this->db->where('idreq', $id_request);

        return $this->db->get_one();
    }

}
