<?php
/**
 * systmess_model.php
 * model for systems messages
 * @author Litra Andrei
 */

class SystMess_Model extends TinyMVC_Model
{
	public $systmessages_table = "systmessages";
	private $user_systmessages_table = "user_systmessages";

	function set_message($data){
        return empty($data) ? false : $this->db->insert($this->systmessages_table, $data);
	}

	public function get_message($conditions, $fields = '*'){

        $this->db->select($fields);
        $this->db->from($this->systmessages_table);

        if(isset($conditions['mess_code'])){
            $this->db->where('mess_code', $conditions['mess_code']);
        }
        if(isset($conditions['id_mess'])){
            $this->db->where('idmess', $conditions['id_mess']);
        }

		return $this->db->query_one();
	}

    function existMessage($messCode, $idMess = null)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->systmessages_table);
        $this->db->where_raw("LOWER(mess_code) = '".strtolower($messCode)."'");

        if(isset($idMess)){
            $this->db->where('idmess !=', $idMess);
        }

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
	}

    function existMessageById($idMess)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->systmessages_table);
        $this->db->where('idmess !=', $idMess);

        return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
	}

	public function get_messages($conditions){
		$order_by = " mess_type DESC";
		$rel = "";
        $params = [];

        if (isset($conditions['sort_by'])) {
            $multi_order_by = array();
            foreach ($conditions['sort_by'] as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            if (!empty($multi_order_by)) {
                $order_by = implode(',', $multi_order_by);
            }
        }

        if(!empty($conditions['type'])){
			$this->db->where("mess_type = ?");
            $params[] = $conditions['type'];
        }

        if(!empty($conditions['module'])){
			$this->db->where("module");
            $params[] = $conditions['module'];
        }

        if(isset($conditions['is_proofread'])){
			$this->db->where("is_proofread");
            $params[] = $conditions['is_proofread'];
        }

        if(isset($conditions['is_not_used'])){
			$this->db->where("is_proofread");
            $params[] = $conditions['is_not_used'];
        }

        if(isset($conditions['date_changed_from'])){
            $this->db->where("DATE(date_changed) >= ?");
            $params[] = $conditions['date_changed_from'];
        }

        if(isset($conditions['date_changed_to'])){
            $this->db->where("DATE(date_changed) <= ?");
            $params[] = $conditions['date_changed_to'];
        }

        if(isset($conditions['proofreading_date_from'])){
            $this->db->where("DATE(date_proofreading) >= ?");
            $params[] = $conditions['proofreading_date_from'];
        }

        if(isset($conditions['proofreading_date_to'])){
            $this->db->where("DATE(date_proofreading) <= ?");
            $params[] = $conditions['proofreading_date_to'];
        }

        $keywords = $conditions['keywords'];
		if(!empty($conditions['keywords'])){
			$order_by =  $order_by . ", REL DESC";
			$this->db->where_raw(" MATCH (mess_code, message) AGAINST (?)");
            $params[] = $keywords;
			$rel = " , MATCH (mess_code, message) AGAINST (?) as REL";
            array_unshift($params, $keywords);
		}

        $this->db->select("* $rel");
        $this->db->from($this->systmessages_table);
        $this->db->join("ep_modules", "module = id_module", "left");

        $this->db->orderby($order_by);
        if(isset($conditions['per_p']) || isset($conditions['start'])){
            $this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);
        }

        return $this->db->query_all(null, $params ?: null);
	}

    function get_messages_count($conditions)
    {
        $this->db->select("COUNT(*) as counter");
        $this->db->from($this->systmessages_table);

		if(!empty($conditions['type'])){
			$this->db->where("mess_type", $conditions['type']);
        }

        if(!empty($conditions['module'])){
			$this->db->where("module", $conditions['module']);
        }

        if(isset($conditions['date_changed_from'])){
            $this->db->where("DATE(date_changed) >= ?", $conditions['date_changed_from']);
        }

        if(isset($conditions['date_changed_to'])){
            $this->db->where("DATE(date_changed) <= ?", $conditions['date_changed_to']);
        }

        if(isset($conditions['proofreading_date_from'])){
            $this->db->where("DATE(date_proofreading) >= ?", $conditions['proofreading_date_from']);
        }

        if(isset($conditions['proofreading_date_to'])){
            $this->db->where("DATE(date_proofreading) <= ?", $conditions['proofreading_date_to']);
        }

        $keywords = $conditions['keywords'];
		if(!empty($conditions['keywords'])){
			$this->db->where_raw(" MATCH (mess_code, message) AGAINST (?)", $keywords);
		}

		return $this->db->query_one()['counter'];
	}

	function update_message($idmess, $data){
		$this->db->where('idmess', $idmess);
        return $this->db->update($this->systmessages_table, $data);
	}

	function delete_message($idmess){
		$this->db->where('idmess', $idmess);
		return $this->db->delete($this->systmessages_table);
	}

	public function set_message_of_user($mess_code, $users, $replace = array()){
		$values = $params = [];
		$date = date('Y-m-d H:i:s');

		$message_info = $this->get_message(array('mess_code' => $mess_code));
		$message = $message_info['message'];
		$title = $message_info['title'];
		$mess_type = $message_info['mess_type'];
		$type = $message_info['type'];

		if (!empty($replace)) {
			foreach ($replace as $key => $value) {
				if (preg_replace('/\['.$key.'\]/i', $value, $title)) {
					$title = preg_replace('/\['.$key.'\]/i', $value, $title);
                }

				if (preg_replace('/\['.$key.'\]/i', $value, $message)) {
					$message = preg_replace('/\['.$key.'\]/i', $value, $message);
                }
			}
		}

		foreach($users as $user){
			$values[] = "(?,?,?,?,?,?,?)";
            array_push($params, ...[$date, $user, $message_info['idmess'], $title, $message, $mess_type, $type]);
		}

        if (empty($values)) {
            return false;
        }

		$sql = "INSERT INTO {$this->user_systmessages_table} (init_date, idu, idmess, title, message, mess_type, type) VALUES " . implode(',', $values);
		return $this->db->query($sql, $params);
	}

	public function get_user_message($id_um){
        $this->db->where('id_um', $id_um);
        $this->db->where('calendar_only', 0);
        $this->db->limit(1);

        return $this->db->get_one($this->user_systmessages_table);
	}

	public function get_user_messages($idu, $conditions){
		extract($conditions);

        $where = [' calendar_only = ? '];
		$params = [0];

		$order_by = " us.id_um DESC";
		$limit = $per_page * ($page-1).', ' . $per_page;

        if (!empty($status)) {
            $where[] = ' us.status ' . ($status == 'all' ? '!=' : '=') . ' ? ';
            $params[] = $status == 'all' ? 'deleted' : $status;
        }

		if(isset($status_active)){
			$where[] = " us.status != ? ";
            $params[] = 'deleted';
		}

		if(!empty($idu)){
			$where[] = " idu = ? ";
			$params[] = $idu;
		}

		if(isset($type) && !in_array($type, array('unread', 'all', 'deleted'))){
			$where[] = " us.mess_type = ? ";
			$params[] = $type;
		}

		$sql = "SELECT us.*, em.name_module, em.id_module
				FROM " . $this->user_systmessages_table . " us
				LEFT JOIN $this->systmessages_table s on us.idmess = s.idmess
				LEFT JOIN ep_modules em ON em.id_module = us.module";

		if (!empty($where)) {
			$sql .= ' WHERE ' . implode(" AND ", $where);
        }

		$sql .= " ORDER BY {$order_by} LIMIT {$limit}";
		return $this->db->query_all($sql, $params);
	}

	function update_user_message($id_um, $data){
		$this->db->where('id_um', $id_um);
		return $this->db->update($this->user_systmessages_table, $data);
	}

	function update_user_messages_status($id_messages, $status = 'deleted'){
        $id_messages = getArrayFromString($id_messages);

        $this->db->in('id_um', $id_messages);
        return $this->db->update($this->user_systmessages_table, ['status' => $status]);
	}

	function delete_user_message($id_um){
		$this->db->where('id_um', $id_um);
		return $this->db->delete($this->user_systmessages_table);
	}

	function delete_user_messages($id_messages){
        $id_messages = getArrayFromString($id_messages);

        $this->db->in('id_um', $id_messages);
        return $this->db->delete($this->user_systmessages_table);
	}

	function clear_user_systmessages($id_user){
		$this->db->where('idu', $id_user);
		return $this->db->delete($this->user_systmessages_table);
	}

	function delete_user_trash_messages($id_user){
		$this->db->where('status', 'deleted');
		$this->db->where('idu', $id_user);
		return $this->db->delete($this->user_systmessages_table);
	}

	function deleteMessageOfUserByStatus($status = "deleted"){
		$this->db->where('status = ?', $status);
		return $this->db->delete($this->user_systmessages_table);
	}

	function counter_user_notifications_by_type($conditions = array()){
		$type = 'all';
		$user = null;
		$status = 'all';

		extract($conditions);

        $where = ['calendar_only = ?'];
		$params = [0];

		if (isset($status_active)) {
			$where[] = ' us.status != ? ';
            $params[] = 'deleted';
		} else {
            $where[] = ' us.status ' . ($status == 'all' ? '!=' : '=') . ' ? ';
            $params[] = $status == 'all' ? 'deleted' : $status;
		}

		if(!in_array($type, array('unread', 'all', 'deleted'))){
			$where[] = " us.mess_type = ? ";
			$params[] = $type;
		}

		if(!empty($user)){
			$where[] = " idu = ? ";
			$params[] = $user;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM {$this->user_systmessages_table} us
				LEFT JOIN $this->systmessages_table s on us.idmess = s.idmess ";

		if (!empty($where)) {
			$sql .= ' WHERE ' . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	function counter_user_notifications_by_user($conditions = array()){
		extract($conditions);

		$mess_types = array('notice', 'warning', 'all');
		$rez = array();

		$count_params = array('user' => $user);

		if(isset($status)){
			$count_params['status'] = $status;
		}

		foreach($mess_types as $type){
			$count_params['type'] = $type;

			$count = $this->counter_user_notifications_by_type($count_params);

			if($count) {
                $rez[$type] = $count;
			}
		}

		return $rez;
	}

	function counter_user_notifications($id_user, $conditions = array()){
        extract($conditions);

		$sql = "SELECT COUNT(IF(us.status = 'new', us.status, null)) as count_new,
						COUNT(IF(us.mess_type = 'warning' && us.status = 'new', us.mess_type, null)) as count_warning,
						COUNT(*) as count_all
				FROM $this->user_systmessages_table us
				LEFT JOIN $this->systmessages_table s on us.idmess = s.idmess
				WHERE us.idu = ? AND calendar_only = 0 AND us.status IN ('new', 'seen') ";

		return $this->db->query_one($sql, [$id_user]);
	}

	function last_user_notification($id_user, $conditions = array()){
        extract($conditions);

        $this->db->select('MAX(id_um) as last_warning');
        $this->db->where('idu', $id_user);
        $this->db->where('calendar_only', 0);
        $this->db->limit(1);

        return $this->db->get_one($this->user_systmessages_table)['last_warning'];
	}

	function get_messages_multiple(array $messages_ids, $user_id){
        $this->db->select('id_um, mess_type, status');
        $this->db->where('idu', $user_id);
        $this->db->in('id_um', $messages_ids);

        return $this->db->get($this->user_systmessages_table);
	}

	function clear_syst_mess(){
		$sql = "DELETE
				FROM $this->user_systmessages_table
				WHERE (status = 'deleted' AND DATEDIFF(NOW(), update_date) > 10) OR (status = 'seen' AND DATEDIFF(NOW(), update_date) > 30)";
		return $this->db->query($sql);
	}

	function get_users_with_unreaded_mess($limit = 50, $time_range = 86400) {
		$this->db->select("DISTINCT(u.idu), CONCAT(u.lname, ' ', u.fname) as user_name, u.email");
		$this->db->from("{$this->user_systmessages_table} us");
		$this->db->join("users u", "u.idu = us.idu", "inner");
        $this->db->join("ep_modules em", "em.id_module = us.module", "inner");
        $this->db->join("users_systmess_settings uss", "(u.idu = uss.id_user AND uss.module = em.id_module)", 'inner');
		$this->db->where("us.sended = ?", 0);
		$this->db->where("us.status = ?", "new");
        $this->db->where("TIME_TO_SEC(TIMEDIFF(NOW(), us.init_date)) <= ?", $time_range);
		$this->db->where("TIME_TO_SEC(TIMEDIFF(NOW(), u.sent_systmess_date)) >= ?", $time_range);
		$this->db->where("u.notify_email = ?", 1);
		$this->db->in("u.status", array('active', 'pending'));
		$this->db->where("u.logged = ?", '0');
		$this->db->limit($limit);

		return $this->db->get();
	}

    function get_unsended_users_systmess($ids = array(), $time_range = 86400) {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        if (empty($ids = array_filter($ids))) {
            return false;
        }

        $this->db->select("us.title, us.message, us.mess_type, us.init_date, us.type, u.idu, em.key_module, em.id_module");
        $this->db->from("{$this->user_systmessages_table} us");
        $this->db->join("users u", "u.idu = us.idu", "inner");
        $this->db->join("ep_modules em", "em.id_module = us.module", "inner");
        $this->db->join("users_systmess_settings uss", "(u.idu = uss.id_user AND uss.module = em.id_module)", 'inner');
        $this->db->where("us.sended = ?", 0);
        $this->db->where("us.status = ?", 'new');
        $this->db->in("us.idu", $ids);
        $this->db->where("TIME_TO_SEC(TIMEDIFF(NOW(), us.init_date)) <= ?", $time_range);
        $this->db->orderby("us.init_date DESC");

        return $this->db->get();
    }

	function set_sended_by_user_ids($user_ids = array()){
		if (empty($user_ids)) {
			return false;
		}

        $user_ids = getArrayFromString($user_ids);

		$this->db->in("idu", $user_ids);
		$this->db->where("status = ?", "new");

		return $this->db->update($this->user_systmessages_table, array('sended' => 1));
	}
}
