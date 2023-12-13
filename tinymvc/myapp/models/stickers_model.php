<?php
/**
 * stickers_model.php
 *
 * stickers model
 *
 * @author
 */


class Stickers_Model extends TinyMVC_Model
{
	private $stickers_table = "stickers";
	private $users_table = "users";

	public function set_stickers($data){
        if (empty($data)) {
            return false;
        }

		$users_info = $data['users_info'];
        $params = $values = [];
		foreach($data['users_list'] as $user){
			$for_search = "{$data['for_search']} {$users_info[$user]['fname']} {$users_info[$user]['lname']} {$data['name_user_sender']}";

            $values[] = "(?, ?, ?, ?, ?, ?, ?)";
            array_push($params, ...[$data['id_user_sender'], $user, $data['subject'], $data['message'], $data['create_date'], $for_search, $data['priority']]);
		}

        if (empty($values)) {
            return false;
        }

		$sql = "INSERT INTO {$this->stickers_table} (id_user_sender, id_user_recipient, subject, message, create_date, for_search, priority ) VALUES " . implode(',', $values);

		$this->db->query($sql);
		return $this->db->last_insert_id();
	}

	public function update_sticker($id, $data){
        $this->db->where('id_sticker', $id);
        return $this->db->update($this->stickers_table, $data);
    }

	function get_counter_by($select, $group, $conditions)
    {
        extract($conditions);

		$where = $params = [];

		if(isset($id_user)){
			$where[] = " id_user_recipient = ? ";
			$params[] = $id_user;
		}

        if(isset($not)){
            $not = getArrayFromString($not);
            $where[] = " status NOT IN (" . implode(',', array_fill(0, count($not), '?')) . ")";
            array_push($params, ...$not);
		}

		$sql = "SELECT COUNT(*) as counter {$select} FROM {$this->stickers_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$sql .= " GROUP BY {$group}";

		return $this->db->query_all($sql, $params);
	}

	public function get_sticker($conditions){
        extract($conditions);

		$where = $params = [];

		if(isset($id_sticker)){
			$where[] = " id_sticker = ? ";
			$params[] = $id_sticker;
		}

		$sql = "SELECT * FROM {$this->stickers_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		return $this->db->query_one($sql, $params);
	}

	public function get_stickers($conditions){
        $order_by = "s.priority DESC, s.create_date DESC";

		extract($conditions);

        $rel = "";
		$where = $params = [];

		if(isset($id_user)){
			$where[] = " s.id_user_recipient = ? ";
			$params[] = $id_user;
		}

		if(isset($status)){
			$where[] = " s.status = ? ";
			$params[] = $status;
		}

		if(isset($priority)){
			$where[] = " s.priority = ? ";
			$params[] = $priority;
		}

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = 's.'.$sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$order_by =  $order_by . ", REL DESC";
				$where[] = " MATCH (s.subject, s.message, s.for_search) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (s.subject, s.message, s.for_search) AGAINST (?) as REL";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (s.subject LIKE ? OR s.message LIKE ? OR s.for_search LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

        if (isset($not)) {
            $not = getArrayFromString($not);
            $where[] = ' s.status NOT IN (' . implode(',', array_fill(0, count($not), '?')) . ') ';
            array_push($params, ...$not);
        }

        $sql = "SELECT s.*, u.fname, u.lname, u.user_photo $rel
				FROM $this->stickers_table s
				LEFT JOIN $this->users_table u ON s.id_user_sender = u.idu";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$sql .= " ORDER BY {$order_by}";

		if(isset($limit)){
		  $sql .= " LIMIT {$limit}";
        } else{
			if(!isset($count))
				$count = $this->counter_by_conditions($conditions);

			$pages = ceil($count/$per_p);

			if(!isset($start)){
				if ($page > $pages) $page = $pages;
				$start = ($page-1)*$per_p;

				if($start < 0) $start = 0;
			}

			$sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
		}

		return $this->db->query_all($sql, $params);
    }

	public function counter_by_conditions($conditions){
        extract($conditions);

		$where = $params = [];

		if(isset($id_user)){
			$where[] = " id_user_recipient = ? ";
			$params[] = $id_user;
		}

		if(isset($status)){
			$where[] = " status = ? ";
			$params[] = $status;
		}

		if(isset($priority)){
			$where[] = " priority = ? ";
			$params[] = $priority;
		}

		if(isset($keywords)){
			$words = explode(' ', $keywords);
			if (count($words) > 1) {
				$where[] = " MATCH (subject, message, for_search) AGAINST (?)";
				$params[] = $keywords;
			} else {
				$where[] = " (subject LIKE ? OR message LIKE ? OR for_search LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

        if (isset($not)) {
            $not = getArrayFromString($not);
            $where[] = ' status NOT IN (' . implode(',', array_fill(0, count($not), '?')) . ') ';
            array_push($params, ...$not);
        }

        $sql = "SELECT COUNT(*)	as counter FROM {$this->stickers_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
    }

	public function is_my_sticker($id_sticker){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_sticker', $id_sticker);
        $this->db->where('id_user_recipient', id_session());
        $this->db->limit(1);

        return $this->db->get_one($this->stickers_table)['counter'];
	}

	public function delete_sticker($id_sticker){
        $this->db->where('id_sticker', $id_sticker);
		return $this->db->delete($this->stickers_table);
    }

	public function clear_stickers(){
        $this->db->where('status', 'trash');
        $this->db->where_raw('DATEDIFF(NOW(), create_date) > 30');
        return $this->db->delete($this->stickers_table);
	}
}
