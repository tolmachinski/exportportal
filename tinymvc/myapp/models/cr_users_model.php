<?php
/**
 * Cr_users_Model.php
 *
 * model for countries representatives domains
 *
 * @author Cravciuc Andrei
 */

class Cr_users_Model extends TinyMVC_Model {
    private $users_table = "users";
    private $users_additional_table = "cr_users_additional";
    private $users_requests_table = "cr_users_requests";
    private $user_groups_table = "user_groups";
    private $cr_events_cr_users_table = 'cr_events_cr_users_assign';
    private $countries_table = "port_country";
    private $states_table = "states";
	private $cities_table = "zips";

	public $speak_language_proficiencies = array(
		'p1' => 'Elementary proficiency',
		'p2' => 'Limited working proficiency',
		'p3' => 'Professional working proficiency',
		'p4' => 'Full professional proficiency',
		'p5' => 'Native or bilingual proficiency',
	);

    function cr_get_users($conditions = array()) {
		$order_by = " u.registration_date DESC";
		$limit = 10;
		$where = array();
		$params = array();
		$joins = array(
            "LEFT JOIN {$this->users_additional_table} cua ON u.idu = cua.id_user",
            "LEFT JOIN {$this->user_groups_table} gr ON u.user_group = gr.idgroup",
            "LEFT JOIN {$this->countries_table} pc ON u.country = pc.id",
            "LEFT JOIN {$this->states_table} us ON u.state = us.id",
            "LEFT JOIN {$this->cities_table} uc ON u.city = uc.id"
        );
		$select = array("u.*, cua.*", "CONCAT_WS(' ',u.fname, u.lname) as user_name", "gr.gr_name", "gr.gr_type", "uc.city as user_city", "pc.country as user_country", "IF(uc.city = us.state, CONCAT_WS(', ',uc.city, pc.country), CONCAT_WS(', ',uc.city, us.state, pc.country)) as user_location");

		extract($conditions);

		if(isset($users_list)){
            $users_list = getArrayFromString($users_list);
			$where[] = " u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ") ";
            array_push($params, ...$users_list);
        }

		if(isset($not_users_list)){
            $not_users_list = getArrayFromString($not_users_list);
			$where[] = " u.idu NOT IN (" . implode(',', array_fill(0, count($not_users_list), '?')) . ") ";
            array_push($params, ...$not_users_list);
        }

		if(isset($group)){
            $group = getArrayFromString($group);
			$where[] = " u.user_group IN (" . implode(',', array_fill(0, count($group), '?')) . ") ";
            array_push($params, ...$group);
        }

		if(isset($group_type)){
            $group_type = getArrayFromString($group_type);
			$where[] = " gr.gr_type IN (" . implode(',', array_fill(0, count($group_type), '?')) . ") ";
            array_push($params, ...$group_type);
        }

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " u.status IN(" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
		}

		if(isset($email_status)){
            $email_status = getArrayFromString($email_status);
			$where[] = " u.email_status IN(" . implode(',', array_fill(0, count($email_status), '?')) . ") ";
            array_push($params, ...$email_status);
        }

		if(isset($logged)){
			$where[] = " u.logged = ? ";
			$params[] = $logged;
		}

		if(isset($ip)){
			$where[] = " u.user_ip = ? ";
			$params[] = $ip;
		}

		if(isset($registration_start_date)){
			$where[] = " DATE(u.registration_date) >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if(isset($registration_end_date)){
			$where[] = " DATE(u.registration_date) <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

		if(isset($activity_start_date)){
			$where[] = " DATE(u.last_active) >= ? ";
			$params[] = formatDate($activity_start_date, 'Y-m-d');
		}

		if(isset($activity_end_date)){
			$where[] = " DATE(u.last_active) <= ? ";
			$params[] = formatDate($activity_end_date, 'Y-m-d');
		}

        if(isset($country)){
            $where[] = " u.country = ?";
            $params[] = $country;
        }

        if(isset($state)){
            $where[] = " u.state = ?";
            $params[] = $state;
        }

        if(isset($city)){
            $where[] = " u.city = ?";
            $params[] = $city;
        }

		if(isset($domains) || isset($domains_info)){
			if(isset($domains)){
                $domains = getArrayFromString($domains);
				$where[] = "crdu.id_domain IN (" . implode(',', array_fill(0, count($domains), '?')) . ")";
                array_push($params, ...$domains);
			}

			if(isset($domains_info)){
				$select[] = "crdu.id_domain";
			}

			$joins[] = "INNER JOIN cr_domains_users crdu ON crdu.id_user = u.idu";
		}

        if(isset($events)){
            $events = getArrayFromString($events);
            $select[] = 'ea.id_event';
            $where[] = "ea.id_event IN (" . implode(',', array_fill(0, count($events), '?')) . ")";
            $joins[] = "INNER JOIN {$this->cr_events_cr_users_table} ea ON ea.id_user = u.idu";
            array_push($params, ...$events);
        }

		if(isset($keywords)){
			$words = explode(" ", $keywords);
			foreach($words as $word){
				if(mb_strlen($word) > 3){
					$s_word[] = " (u.fname LIKE ? OR u.lname  LIKE ? OR u.email LIKE ? OR CONCAT(phone_code,'',phone) LIKE ?)";
                    array_push($params, ...array_fill(0, 4, '%' . $word . '%'));
				}
			}

			if(!empty($s_word)){
				$where[] = "(" . implode(" AND ", $s_word) . ")";
            }
        }

        $joins = implode(' ', $joins);
        $select = implode(', ', $select);
        $where = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

		$sql = "
            SELECT $select
            FROM {$this->users_table} u
            $joins
            $where
        ";

        $sql .= " GROUP BY u.idu ";

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

            if(!empty($multi_order_by)){
                $order_by = implode(' , ', $multi_order_by);
                $sql .= " ORDER BY " . $order_by;
            }
		}


		if(isset($page)){
			if(!isset($count)){
				$count = $this->cr_count_users($conditions);
			}

			$limit = getDbQueryLimit($page, $count, $limit);
		}

        if ($limit !== 'all') {
            if (isset($start, $limit)) {
                $start = (int) $start;
                $limit = (int) $limit;
                $sql .=  " LIMIT {$start}, {$limit}";
            } else{
                $sql .=  " LIMIT {$limit}";
            }
        }

		return $this->db->query_all($sql, $params);
    }

	function cr_count_users($conditions = array()){
		$where = array();
		$params = array();
		$joins = array(
            "LEFT JOIN {$this->user_groups_table} gr ON u.user_group = gr.idgroup"
        );

		extract($conditions);

		if(isset($users_list)){
            $users_list = getArrayFromString($users_list);
			$where[] = " u.idu IN (" . implode(',', array_fill(0, count($users_list), '?')) . ") ";
            array_push($params, ...$users_list);
        }

		if(isset($not_users_list)){
            $not_users_list = getArrayFromString($not_users_list);
			$where[] = " u.idu NOT IN (" . implode(',', array_fill(0, count($not_users_list), '?')) . ") ";
            array_push($params, ...$not_users_list);
        }

		if(isset($group)){
            $group = getArrayFromString($group);
			$where[] = " u.user_group IN (" . implode(',', array_fill(0, count($group), '?')) . ") ";
            array_push($params, ...$group);
        }

		if(isset($group_type)){
            $group_type = getArrayFromString($group_type);
			$where[] = " gr.gr_type IN (" . implode(',', array_fill(0, count($group_type), '?')) . ") ";
            array_push($params, ...$group_type);
        }

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " u.status IN(" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
		}

		if(isset($email_status)){
            $email_status = getArrayFromString($email_status);
			$where[] = " u.email_status IN(" . implode(',', array_fill(0, count($email_status), '?')) . ") ";
            array_push($params, ...$email_status);
        }

		if(isset($logged)){
			$where[] = " u.logged = ? ";
			$params[] = $logged;
		}

		if(isset($ip)){
			$where[] = " u.user_ip = ? ";
			$params[] = $ip;
		}

		if(isset($registration_start_date)){
			$where[] = " DATE(u.registration_date) >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if(isset($registration_end_date)){
			$where[] = " DATE(u.registration_date) <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

		if(isset($activity_start_date)){
			$where[] = " DATE(u.last_active) >= ? ";
			$params[] = formatDate($activity_start_date, 'Y-m-d');
		}

		if(isset($activity_end_date)){
			$where[] = " DATE(u.last_active) <= ? ";
			$params[] = formatDate($activity_end_date, 'Y-m-d');
		}

        if(isset($country)){
            $where[] = " u.country = ?";
            $params[] = $country;
        }

        if(isset($state)){
            $where[] = " u.state = ?";
            $params[] = $state;
        }

        if(isset($city)){
            $where[] = " u.city = ?";
            $params[] = $city;
        }

        if(isset($domains)){
            $domains = getArrayFromString($domains);
            $where[] = "crdu.id_domain IN (" . implode(',', array_fill(0, count($domains), '?')) . ")";
            array_push($params, ...$domains);

            $joins[] = "INNER JOIN cr_domains_users crdu ON crdu.id_user = u.idu";
        }

		if(isset($keywords)){
			$words = explode(" ", $keywords);
			foreach($words as $word) {
				if(mb_strlen($word) > 3){
					$s_word[] = " (u.fname LIKE ? OR u.lname  LIKE ? OR u.email LIKE ? OR CONCAT(phone_code,'',phone) LIKE ?)";
                    array_push($params, ...array_fill(0, 4, '%' . $word . '%'));
				}
			}

			if(!empty($s_word)){
				$where[] = " (" . implode(" AND ", $s_word) . ")";
            }
		}

        $joins = implode(' ', $joins);
		$sql = "SELECT COUNT(DISTINCT u.idu) as counter
				FROM {$this->users_table} u
				$joins";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
		}

		$record = $this->db->query_one($sql, $params);
		return $record['counter'];
	}

	function cr_users_groups_counters($conditions = array()) {
		$where = array(" ug.gr_type = 'CR Affiliate' ");
		$params = array();

		extract($conditions);

		if(isset($id_domain)){
			$where[] = " crdu.id_domain = ? ";
			$params[] = $id_domain;
		}

		if(isset($user_status)){
			$where[] = " u.`status` = ? ";
            $params[] = $user_status;
		}

		$sql = "SELECT 	ug.*,
						COUNT(u.idu) as total_users
				FROM user_groups ug
				INNER JOIN users u on ug.idgroup = u.user_group
				INNER JOIN cr_domains_users crdu on crdu.id_user = u.idu";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$sql .= " GROUP BY ug.idgroup ";

		return $this->db->query_all($sql, $params);
	}

	// USER ADITIONAL
	function cr_set_user_additional($insert = 0){
		$this->db->insert($this->users_additional_table, $insert);
		return $this->db->last_insert_id();
	}

	function cr_get_user_additional($id_user = 0){
		$sql = "SELECT 	*
				FROM {$this->users_additional_table}
				WHERE id_user = ?";
		return $this->db->query_one($sql, array($id_user));
	}

	function cr_exist_user_additional($id_user = 0){
		$sql = "SELECT COUNT(*) as total_records
				FROM {$this->users_additional_table}
				WHERE id_user = ?";
		$record = $this->db->query_one($sql, array($id_user));
		return $record['total_records'];
	}

	function cr_update_user_additional($id_user = 0, $data = array()){
		if($this->cr_exist_user_additional($id_user)){
			$this->db->where('id_user', $id_user);
			return $this->db->update($this->users_additional_table, $data);
		} else{
			$data['id_user'] = $id_user;
			return $this->cr_set_user_additional($data);
		}
	}

	// USERS REQUESTS
	function cr_set_user_request($insert = array()){
		$this->db->insert($this->users_requests_table, $insert);
		return $this->db->last_insert_id();
	}

	function cr_update_user_request($id_request = 0, $update = array()){
		$this->db->where('id_request', $id_request);
		return $this->db->update($this->users_requests_table, $update);
	}

	function cr_delete_user_request($id_request = 0){
		$this->db->where('id_request', $id_request);
		return $this->db->delete($this->users_requests_table);
	}

	function cr_get_user_request($id_request = 0){
		$sql = "SELECT 	*
				FROM {$this->users_requests_table}
				WHERE id_request = ?";
		return $this->db->query_one($sql, array($id_request));
	}

	function cr_exist_user_request($conditions = array()){
		if(empty($conditions)){
			return false;
		}

		$where = array();
		$params = array();

		extract($conditions);

		if(isset($id_request)){
			$where[] = ' id_request = ? ';
			$params[] = $id_request;
		}

		if(isset($email)){
			$where[] = ' applicant_email = ? ';
			$params[] = $email;
		}

		$sql = "SELECT COUNT(*) as total_records
				FROM {$this->users_requests_table}
				WHERE " . implode(" AND ", $where);
		$record = $this->db->query_one($sql, $params);
		return $record['total_records'];
	}

    function cr_get_users_requests($conditions = array()) {
		$order_by = " ur.id_request DESC";
		$limit = 10;
		$where = array();
		$params = array();
		$joins = array(
            "LEFT JOIN {$this->countries_table} pc ON ur.id_country = pc.id",
            "LEFT JOIN {$this->states_table} us ON ur.id_state = us.id",
            "LEFT JOIN {$this->cities_table} uc ON ur.id_city = uc.id"
        );
		$select = array("ur.*", "CONCAT_WS(' ',ur.applicant_fname, ur.applicant_lname) as applicant_name", "uc.city as applicant_city", "pc.country as applicant_country", "IF(uc.city = us.state, CONCAT_WS(', ',uc.city, pc.country), CONCAT_WS(', ',uc.city, us.state, pc.country)) as applicant_location");

		extract($conditions);

		if(isset($id_request)){
            $id_request = getArrayFromString($id_request);
			$where[] = " ur.id_request IN (" . implode(',', array_fill(0, count($id_request), '?')) . ") ";
            array_push($params, ...$id_request);
        }

		if(isset($not_id_request)){
            $not_id_request = getArrayFromString($not_id_request);
			$where[] = " ur.id_request NOT IN (" . implode(',', array_fill(0, count($not_id_request), '?')) . ") ";
            array_push($params, ...$not_id_request);
        }

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " ur.applicant_status IN(" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
        }

		if(isset($registration_start_date)){
			$where[] = " DATE(ur.applicant_created) >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if(isset($registration_end_date)){
			$where[] = " DATE(ur.applicant_created) <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

        if(isset($country)){
            $where[] = " ur.id_country = ?";
            $params[] = $country;
        }

        if(isset($state)){
            $where[] = " ur.id_state = ?";
            $params[] = $state;
        }

        if(isset($city)){
            $where[] = " ur.id_city = ?";
            $params[] = $city;
        }

		if(isset($keywords)){
			$words = explode(" ", $keywords);
			foreach($words as $word){
				if(mb_strlen($word) > 3){
					$s_word[] = " (ur.applicant_fname LIKE ? OR ur.applicant_lname  LIKE ? OR ur.applicant_email LIKE ?)";
                    array_push($params, ...array_fill(0, 3, '%' . $word . '%'));
				}
			}

			if(!empty($s_word)){
				$where[] = "(" . implode(" AND ", $s_word) . ")";
            }
		}

        $joins = implode(' ', $joins);
        $select = implode(', ', $select);
        if(!empty($where)){
            $where = " WHERE " . implode(" AND ", $where);
        } else{
			$where = '';
		}

		$sql = "SELECT $select
				FROM {$this->users_requests_table} ur
            	$joins
				$where";

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(' , ', $multi_order_by);
		}

		$sql .= " ORDER BY " . $order_by;

		if(isset($page)){
			if(!isset($count)){
				$count = $this->cr_count_users_requests($conditions);
			}

			$limit = getDbQueryLimit($page, $count, $limit);
		}

        if ($limit !== 'all') {
            if (isset($start, $limit)) {
                $start = (int) $start;
                $limit = (int) $limit;
                $sql .=  " LIMIT {$start}, {$limit}";
            } else{
                $sql .=  " LIMIT {$limit}";
            }
		}

		return $this->db->query_all($sql, $params);
    }

	function cr_count_users_requests($conditions = array()){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($id_request)){
            $id_request = getArrayFromString($id_request);
			$where[] = " ur.id_request IN (" . implode(',', array_fill(0, count($id_request), '?')) . ") ";
            array_push($params, ...$id_request);
        }

		if(isset($not_id_request)){
            $not_id_request = getArrayFromString($not_id_request);
			$where[] = " ur.id_request NOT IN (" . implode(',', array_fill(0, count($not_id_request), '?')) . ") ";
            array_push($params, ...$not_id_request);
        }

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " ur.applicant_status IN(" . implode(',', array_fill(0, count($status), '?')) . ") ";
            array_push($params, ...$status);
        }

		if(isset($registration_start_date)){
			$where[] = " DATE(ur.applicant_created) >= ? ";
			$params[] = formatDate($registration_start_date, 'Y-m-d');
		}

		if(isset($registration_end_date)){
			$where[] = " DATE(ur.applicant_created) <= ? ";
			$params[] = formatDate($registration_end_date, 'Y-m-d');
		}

        if(isset($country)){
            $where[] = " ur.id_country = ?";
            $params[] = $country;
        }

        if(isset($state)){
            $where[] = " ur.id_state = ?";
            $params[] = $state;
        }

        if(isset($city)){
            $where[] = " ur.id_city = ?";
            $params[] = $city;
        }

		if(isset($keywords)){
			$words = explode(" ", $keywords);
			foreach($words as $word){
				if(mb_strlen($word) > 3){
					$s_word[] = " (ur.applicant_fname LIKE ? OR ur.applicant_lname  LIKE ? OR ur.applicant_email LIKE ?)";
                    array_push($params, ...array_fill(0, 3, '%' . $word . '%'));
				}
			}

			if(!empty($s_word)){
				$where[] = "(" . implode(" AND ", $s_word) . ")";
            }
		}

		$sql = "SELECT COUNT(*) as counter
				FROM {$this->users_requests_table} ur";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

		$record = $this->db->query_one($sql, $params);
		return $record['counter'];
	}
}
