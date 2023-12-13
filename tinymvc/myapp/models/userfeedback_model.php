<?php

/**
 * userfeedback_model.php
 *
 * user's feedback system model
 *
 * @author Litra Andrei
 */
class UserFeedback_Model extends TinyMVC_Model {

    // hold the current controller instance
    private $services_table = "user_services";
    private $services_groups_table = "user_services_groups";
    private $feedbacks_helpful_table = "user_feedback_helpful";
    private $feedback_table = "user_feedbacks";
    private $users_table = "users";
    private $companies_table = "company_base";

    /* services */

    public function setService($data) {
        return empty($data) ? false : $this->db->insert($this->services_table, $data);
    }

    public function getService($id_service) {
        $this->db->where('id_service', $id_service);
        $this->db->limit(1);

        return $this->db->get_one($this->services_table);
    }

    public function existService($s_title) {
        $this->db->select('COUNT(*) as exist');
        $this->db->where('s_title', $s_title);
        $this->db->limit(1);

        return $this->db->get_one($this->services_table)['exist'];
    }

    public function getServices($list = array(),$columns = '*') {
        $this->db->select($columns);

        if (!empty($list)) {
            $list = getArrayFromString($list);
            $this->db->in('id_service', $list);
        }

        $this->db->orderby('s_title');

        return $this->db->get($this->services_table);
    }

    public function updateService($id_service, $data) {
		$this->db->where('id_service', $id_service);
		return $this->db->update($this->services_table, $data);
    }

    public function deleteService($id_service) {
		$this->db->where('id_service', $id_service);
		return $this->db->delete($this->services_table);
    }

    /* group's  services */

    public function setServiceGroupRelation($data) {
		return $this->db->insert($this->services_groups_table, $data);
    }

    public function getServiceGroupRelations() {
        $relations = $this->db->get($this->services_groups_table);

		$by_group = array();
		foreach ($relations as $rel) {
			$by_group[$rel['id_group']][] = $rel['id_service'];
        }

		return $by_group;
    }

    public function deleteServiceGroupRelation($id_group, $id_service) {
		$this->db->where('id_group =? AND id_service =?', array($id_group, $id_service));
		return $this->db->delete($this->services_groups_table);
    }

    public function getServiceByGroup($id_group) {
		$sql = "SELECT *
					FROM {$this->services_groups_table} sg
					INNER JOIN {$this->services_table} s ON sg.id_service = s.id_service
					WHERE sg.id_group = ?";
		return $this->db->query_all($sql, array($id_group));
    }

    public function delete_service_ratings($id_feedback) {
        $this->db->where('id_feedback', $id_feedback);
        return $this->db->update($this->feedback_table, ['services' => '']);
    }

    /* user feedback */

	function get_feedbacks_last_id(){
        $this->db->select('id_feedback');
        $this->db->orderby('id_feedback DESC');
        $this->db->limit(1);

        return $this->db->get_one($this->feedback_table)['id_feedback'] ?: 0;
	}

	function get_count_new_feedbacks($id_feedback){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_feedback', $id_feedback);
        $this->db->limit(1);

        return $this->db->get_one($this->feedback_table)['counter'];
	}

    public function set_feedback($data) {
		return $this->db->insert($this->feedback_table, $data);
    }

    public function up_company_rating_by_company($id_company, $rating) {
		$sql = "UPDATE company_base cb
				SET cb.rating_company = ROUND(((cb.rating_company * cb.rating_count_company + $rating)/(cb.rating_count_company + 1)), 1), cb.rating_count_company = cb.rating_count_company + 1
				WHERE cb.id_company = ? AND cb.type_company = 'company'";

		return $this->db->query($sql, array($id_company));
    }

    public function up_company_rating($id_item, $rating) {
		$sql = "UPDATE company_base cb
				LEFT JOIN item_orders io ON io.id_seller = cb.id_user
				SET cb.rating_company = ROUND(((cb.rating_company * cb.rating_count_company + ?)/(cb.rating_count_company + 1)), 1), cb.rating_count_company = cb.rating_count_company + 1
				WHERE io.id = ? AND cb.type_company = ?";

		return $this->db->query($sql, array($rating, $id_item, 'company'));
    }

    public function down_company_rating($id_item, $rating) {
		$sql = "UPDATE company_base cb
				LEFT JOIN item_orders io ON io.id_seller = cb.id_user
				SET cb.rating_company = ROUND(((cb.rating_company * cb.rating_count_company - ?)/(cb.rating_count_company - 1)), 1), cb.rating_count_company = cb.rating_count_company - 1
				WHERE io.id = ? AND cb.type_company = ?";

		return $this->db->query($sql, array($rating, $id_item, 'company'));
    }

    public function isMyFeedback($id_feedback, $id_user) {
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_feedback', $id_feedback);
        $this->db->where('id_poster', $id_user);
        $this->db->limit(1);

        return $this->db->get_one($this->feedback_table)['counter'];
    }

    public function iWroteFeedback($id_user, $id_order) {
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_order', $id_order);
        $this->db->where('id_poster', $id_user);
        $this->db->limit(1);

        return $this->db->get_one($this->feedback_table)['counter'];
    }

	public function get_simple_feedback($id_feedback){
        $this->db->where('id_feedback', $id_feedback);
        $this->db->limit(1);

        return $this->db->get_one($this->feedback_table);
	}

    public function getFeedback($id_feedback) {
		$sql = "SELECT 	f.*,
							CONCAT(us.fname, ' ', us.lname) as username,
							CONCAT(pos.fname, ' ', pos.lname) as postername
					FROM {$this->feedback_table} f
					INNER JOIN {$this->users_table} us ON f.id_user = us.idu
					INNER JOIN {$this->users_table} pos ON f.id_poster = pos.idu
					WHERE id_feedback = ?";
		return $this->db->query_one($sql, array($id_feedback));
    }

    public function getFeedbacks($conditions = array()) {
        $page = 1;
        $per_p = 20;
        $order_by = "create_date DESC";

        extract($conditions);

        $where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        if (isset($poster)) {
            $where[] = " f.id_poster = ? ";
            $params[] = $poster;
        }

        if (isset($user)) {
            $where[] = " f.id_user = ? ";
            $params[] = $user;
        }

        if (isset($added_start)) {
            $where[] = " DATE(f.create_date) >= ? ";
            $params[] = $added_start;
        }

        if (isset($added_finish)) {
            $where[] = " DATE(f.create_date) <= ? ";
            $params[] = $added_finish;
        }

        if (isset($moderated)) {
            $where[] = " f.status = ? ";
            $params[] = $moderated;
        }

        if (isset($id_order)) {
            $where[] = " id_order = ? ";
            $params[] = $id_order;
        }

        if (isset($feedback_number)) {
            $where[] = " id_feedback = ? ";
            $params[] = $feedback_number;
        }

        if (isset($keywords)) {
            $where[] = " MATCH (f.title,f.text) AGAINST (?)";
            $params[] = $keywords;
		}

        if (!empty($keywords)) {
            $words = explode(" ", $keywords);
            foreach ($words as $word) {
                if (strlen($word) > 3) {
                    $s_word[] = " (u.fname LIKE ? OR u.lname LIKE ? OR
                                    pos.fname LIKE ? OR pos.lname LIKE ? OR
                    f.title LIKE ? OR f.text LIKE ?)";

                    array_push($params, ...array_fill(0, 6, '%' . $word . '%'));
                }
            }

            if (!empty($s_word)) {
                $where[] = " (" . implode(" AND ", $s_word) . ")";
            }
        }

        $sql = "SELECT f.*,
						CONCAT(u.fname, ' ', u.lname) as username,
						CONCAT(pos.fname, ' ', pos.lname) as postername,
						pc.country as poster_country,
						pos.status as poster_status";

        if (isset($company_details)) {
            $sql .= ", c.name_company, c.id_company, c.id_user as has_company, c.index_name ";
        }

        $sql .= "FROM {$this->feedback_table} f
                INNER JOIN {$this->users_table} u ON f.id_user = u.idu
                INNER JOIN {$this->users_table} pos ON f.id_poster = pos.idu
                LEFT JOIN port_country pc ON pc.id = u.country";

        if (isset($company_details)) {
            $sql .= ' INNER JOIN ' . $this->companies_table . ' as c ON (c.id_user=f.id_user'
                . ' OR c.id_user=f.id_poster) AND c.type_company="company"';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND", $where);
        }

        $limit = $limit ?? ($page - 1) * $per_p . ',' . $per_p;

        $sql .= " ORDER BY {$order_by} LIMIT {$limit}";

        return $this->db->query_all($sql, $params);
    }

    public function countFeedbacks($conditions)
    {
        extract($conditions);

		$where = $params = [];

		if (isset($poster)) {
			$where[] = " f.id_poster = ? ";
			$params[] = $poster;
		}

		if (isset($user)) {
			$where[] = " f.id_user = ? ";
			$params[] = $user;
		}

		if (isset($added_start)) {
			$where[] = " DATE(f.create_date) >= ? ";
			$params[] = $added_start;
		}

		if (isset($added_finish)) {
			$where[] = " DATE(f.create_date) <= ? ";
			$params[] = $added_finish;
		}

		if (isset($moderated)) {
			$where[] = " f.status = ? ";
			$params[] = $moderated;
		}

		if (isset($id_order)) {
			$where[] = " id_order = ? ";
			$params[] = $id_order;
		}

		if (isset($feedback_number)) {
			$where[] = " id_feedback = ? ";
			$params[] = $feedback_number;
		}

		if (isset($keywords)) {
			$where[] = " MATCH (f.title,f.text) AGAINST (?)";
            $params[] = $keywords;
		}

		if (!empty($keywords)) {
            $words = explode(" ", $keywords);
            foreach ($words as $word) {
                if (strlen($word) > 3) {
                    $s_word[] = " (u.fname LIKE ? OR u.lname LIKE ? OR
                                    pos.fname LIKE ? OR pos.lname LIKE ? OR
                    f.title LIKE ? OR f.text LIKE ?)";

                    array_push($params, ...array_fill(0, 6, '%' . $word . '%'));
                }
            }

            if (!empty($s_word)) {
                $where[] = " (" . implode(" AND ", $s_word) . ")";
            }
        }

		$sql = "SELECT COUNT(*) as counter
				FROM $this->feedback_table f
				INNER JOIN $this->users_table u ON f.id_user = u.idu
				INNER JOIN $this->users_table pos ON f.id_poster = pos.idu
				LEFT JOIN port_country pc ON pc.id = u.country";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
    }

    public function get_user_feedback($id_feedback = 0) {
        $this->db->where('id_feedback', $id_feedback);
        $this->db->limit(1);

        return $this->db->get_one($this->feedback_table);
    }

    public function get_feedback_details($conditions = array()) {
		if(empty($conditions)){
			return false;
		}

		extract($conditions);

		$where = $params = [];

		if (isset($id_feedback)) {
			$where[] = " id_feedback = ? ";
			$params[] = $id_feedback;
		}

		if (isset($poster)) {
			$where[] = " id_poster = ? ";
			$params[] = $poster;
		}

		if (isset($id_user)) {
			$where[] = " id_user = ? ";
			$params[] = $id_user;
		}

		if (isset($id_order)) {
			$where[] = " id_order = ? ";
			$params[] = $id_order;
		}

		$sql = "SELECT * FROM {$this->feedback_table}";

		if (!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$feedback = $this->db->query_one($sql, $params);

		if(empty($feedback)){
			return false;
		}

		$sql = "SELECT u.user_photo, u.idu, u.lname, u.fname, pc.country as user_country
				FROM users u
				LEFT JOIN port_country pc ON u.country = pc.id
				WHERE u.idu IN (?,?)";

		$users = arrayByKey($this->db->query_all($sql, [$feedback['id_user'], $feedback['id_poster']]), 'idu');
		$feedback['user'] = $users[$feedback['id_user']];
		$feedback['poster'] = $users[$feedback['id_poster']];
		return $feedback;
    }

    public function get_user_feedbacks($conditions = array()) {
		$page = 1;
		$per_p = 10;
		$order_by = "create_date DESC";
		$db_keys = false;

		extract($conditions);

		$users = $where = $params = [];

        if (isset($sort_by)) {
			switch ($sort_by) {
				case 'rating_asc': $order_by = 'rating ASC';break;
				case 'rating_desc': $order_by = 'rating DESC';break;
				case 'date_asc': $order_by = 'create_date ASC';break;
				case 'date_desc': $order_by = 'create_date DESC';break;
				case 'rand': $order_by = ' RAND()';break;
			}
		}
		if (isset($id_feedback)) {
			$where[] = " id_feedback = ? ";
			$params[] = $id_feedback;
		}

		if (isset($poster)) {
			$where[] = " id_poster = ? ";
			$params[] = $poster;
		}

		if (isset($poster_list)) {
            $poster_list = getArrayFromString($poster_list);
			$where[] = " id_poster IN (" . implode(',', array_fill(0, count($poster_list), '?')) . ") ";
            array_push($params, ...$poster_list);
        }

		if (isset($id_user)) {
			$where[] = " id_user = ? ";
			$params[] = $id_user;
		}

		if (isset($id_order)) {
			$where[] = " id_order = ? ";
			$params[] = $id_order;
		}

		$sql = "SELECT * FROM {$this->feedback_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " GROUP BY id_feedback ORDER BY {$order_by}";
		/* for pagination */
		$start = ($page - 1) * $per_p;

		if ($start < 0) {
			$start = 0;
        }

		$sql .= " LIMIT " . $start;

		if ($per_p > 0) {
			$sql .= "," . $per_p;
        }

        $rez = $this->db->query_all($sql, $params);

		if ($db_keys != false) {
			$rez = arrayByKey($rez, $db_keys);
        }

		foreach ($rez as $feedback) {
            $users[$feedback['id_user']] = $feedback['id_user'];
            $users[$feedback['id_poster']] = $feedback['id_poster'];
		}

		if (!empty($users)) {
			$sql2 = "SELECT DISTINCT u.user_photo, u.idu, u.lname, u.fname, pc.country as user_country
					FROM users u
					LEFT JOIN port_country pc ON u.country = pc.id
					WHERE u.idu IN (" . implode(',', array_fill(0, count($users), '?')) . ")";

			$rez2 = arrayByKey($this->db->query_all($sql2, array_values($users)), 'idu');

			foreach ($rez as $key => $feedback) {
				$rez[$key]['user'] = $rez2[$feedback['id_user']];
				$rez[$key]['poster'] = $rez2[$feedback['id_poster']];
			}
		}
		return $rez;
    }

    public function get_order_feedbacks($id_order) {
        $this->db->where('id_order', $id_order);
        return $this->db->get($this->feedback_table);
    }

    public function update_feedback($id_feedback, $data) {
		$this->db->where('id_feedback', $id_feedback);
		return $this->db->update($this->feedback_table, $data);
    }

    public function count_feedbacks($list_id) {
        $list_id = getArrayFromString($list_id);

        $this->db->select('id_user, COUNT(*) as counter');
        $this->db->in('id_user', $list_id);
        $this->db->groupby('id_user');

        $list = $this->db->get($this->feedback_table);

        return array_column($list, 'counter', 'id_user');
    }

    public function counter_by_conditions($conditions = array()) {
        extract($conditions);

        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        if (!empty($user)) {
            $this->db->where('id_user', $user);
        }

        if (!empty($poster)) {
            $this->db->where('id_poster', $poster);
        }

        $this->db->select('COUNT(*) as counter');
        $this->db->limit(1);

        return $this->db->get_one($this->feedback_table)['counter'];
    }

    public function get_rating_by_user($id) {
        $this->db->select('AVG(rating) as rating');
        $this->db->where('id_user', $id);
        $this->db->limit(1);

        $rez = $this->db->get_one($this->feedback_table);

        return null === $rez['rating'] ? 5 : round($rez['rating']);
    }

    public function modify_counter_helpfull($id, $columns) {
        $sql = "UPDATE " . $this->feedback_table . " SET ";

        foreach ($columns as $column => $sign) {
            $set[] = $column . " = " . $column . " " . $sign . " 1 ";
        }

        $sql .= implode(',', $set) . " WHERE id_feedback = ?";

        return $this->db->query($sql, array($id));
    }

    public function moderate_feedbacks($ids){
        $ids = getArrayFromString($ids);

        $this->db->in('id_feedback', $ids);
        return $this->db->update($this->feedback_table, ['status' => 'moderated']);
    }

    public function delete_poster_feedbacks($ids, $id_poster){
        $ids = getArrayFromString($ids);

        $this->db->in('id_feedback', $ids);
        $this->db->where('id_poster', $id_poster);

        return $this->db->delete($this->feedback_table);
    }

	public function check_user_feedback($conditions = array()) {
		extract($conditions);

		$feedback = 'o.id_buyer';
        $where = ['uf.id_feedback IS null'];
		$params = [];

		if(isset($id_seller)){
			$where[] = ' o.id_seller = ? ';
			$params[] = $id_seller;
		}

		if(isset($id_buyer)){
			$where[] = ' o.id_buyer = ? ';
			$params[] = $id_buyer;
		}

		if(isset($status)){
			$where[] = ' o.status = ? ';
			$params[] = $status;
		}

		if(isset($order)){
			$where[] = ' o.id = ? ';
			$params[] = $order;
		}

		if($feedback_seller){
			$feedback = 'o.id_seller';
		}

		$sql = "SELECT o.id as id_order, uf.id_feedback
				FROM item_orders o
				LEFT OUTER JOIN user_feedbacks uf ON {$feedback} = uf.id_poster AND o.id = uf.id_order";

		if(!empty($where)){
		    $sql .= " WHERE " . implode(" AND", $where);
		}

		return $this->db->query_all($sql, $params);
    }

	public function check_reply($id_feedback){
        $this->db->select('SELECT COUNT(*) as counter');
        $this->db->where('id_feedback', $id_feedback);
        $this->db->where('reply_text', '');
        $this->db->limit(1);

        return $this->db->get_one('user_feedbacks')['counter'];
	}

	/**
	 * HELPFUL
	 */
    public function set_helpful($data) {
        return empty($data) ? false : $this->db->insert($this->feedbacks_helpful_table, $data);
    }

    public function exist_helpful($id_feedback, $id_user) {
        $this->db->select('COUNT(*) as counter, help');
        $this->db->where('id_feedback', $id_feedback);
        $this->db->where('id_user', $id_user);
        $this->db->limit(1);

        return $this->db->get_one($this->feedbacks_helpful_table);
    }

    public function update_helpful($id_feedback, $data, $id_user) {
	    $this->db->or_where('id_feedback = ? and id_user = ?', array($id_feedback, $id_user));
	    return $this->db->update($this->feedbacks_helpful_table, $data);
	}

	public function remove_helpful($id_feedback, $id_user) {
		$this->db->where('id_feedback', $id_feedback);
		$this->db->where('id_user', $id_user);

		return $this->db->delete($this->feedbacks_helpful_table);
	}

    public function get_helpful_by_feedback($list_feedback = '', $id_user = 0) {
		if (empty($list_feedback)){
			return false;
		}

        $list_feedback = getArrayFromString($list_feedback);

        $this->db->select('id_feedback, help');
        $this->db->in('id_feedback', $list_feedback);
        $this->db->where('id_user', $id_user);

        $records = $this->db->get($this->feedbacks_helpful_table);

        return array_column($records, 'help', 'id_feedback');
    }

    public function delete_helpful($id_feedback) {
        $this->db->in('id_feedback', $id_feedback);
        return $this->db->delete($this->feedbacks_helpful_table);
    }
}
