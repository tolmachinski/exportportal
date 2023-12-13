<?php
/**
*country_articles.php
*
*Country blogs model
*
*@author
*/

class Ep_Updates_Model extends TinyMVC_Model {
	public $per_page = 10;
	private $ep_updates_table = 'ep_updates';
	private $ep_updates_table_i18n = 'ep_updates_i18n';
    public $files_path = 'public/img/ep_updates/';

	public function insert_ep_update_i18n($data){
		$this->db->insert($this->ep_updates_table_i18n, $data);
		return $this->db->last_insert_id();
	}

	public function insert_ep_update($data){
		$this->db->insert($this->ep_updates_table, $data);
		return $this->db->last_insert_id();
	}

	public function get_one_ep_update($id, $columns = '*'){
		$sql = "SELECT {$columns}
				FROM {$this->ep_updates_table}
				WHERE id = ?";
		return $this->db->query_one($sql, array($id));
	}

	public function get_ep_updates( $conditions ){
		$where = array();
		$params = array();
		$select = '*';
		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($visible)){
			$where[] = " visible=? ";
			$params[] = $visible;
		}

		if(isset($keywords)){
			$where[] = " MATCH (title,content,description) AGAINST (?) ";
			$params[] = $keywords;
		}

		if(isset($date_to)){
			$where[] = " DATE(date_time) <= ? ";
			$params[] = $date_to;
		}

		if(isset($date_from)){
			$where[] = " DATE(date_time) >= ? ";
			$params[] = $date_from;
		}

		$sql = "SELECT ".$select."
				FROM " . $this->ep_updates_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " ORDER BY " . $order_by;
		$sql .= ' LIMIT ' . intval($start) . ',' . intval($per_p);

		return $this->db->query_all($sql, $params);
	}

	public function get_list_ep_update_public($conditions){
		$where = array();
		$params = array();
		$visible = 1;
		$order_by = ' id DESC ';
		$select = "id, title, description, date_time, url";

		extract($conditions);

		if(!empty($visible)){
			$where[] = ' visible = ? ';
			$params[] = $visible;
		}

        if(isset($not_id_record)){
            $where[] = " id != ?";
            $params[] = $not_id_record;
        }

		if(!empty($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$order_by = " REL_tags DESC ";
				$where[] = " MATCH (title, content, description) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (title, content, description) AGAINST (?) as REL_tags";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (title LIKE ? || content LIKE ? || description LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

        if(__SITE_LANG == "en")  {
            $sql = "SELECT $select $rel FROM $this->ep_updates_table ";
        } else {
            $select = explode(",", $select);
            foreach($select as &$select_el) {
                $select_el = trim($select_el);
                if(in_array($select_el, array("title", "description", "content", "url"))) {
                    $select_el = "ep_update_i18n_" . $select_el . " as " . $select_el;
                }
            }
            $select = implode(",", $select);

            $sql = "SELECT $select $rel
                        FROM {$this->ep_updates_table} eut
                        INNER JOIN {$this->ep_updates_table_i18n} euti18n ON eut.id = euti18n.id_ep_update AND euti18n.ep_update_i18n_lang = '".__SITE_LANG."'
                    ";
        }


		if(count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		if(isset($limit)) {
			$sql .= " LIMIT " . $limit;
		} else {
			$sql .= " LIMIT " . intVal($from) . "," . intVal($per_p);
        }

		return $this->db->query_all($sql, $params);
	}

	public function get_list_ep_update_public_count($conditions){
		$where = array();
		$params = array();
		$visible = 1;

		extract($conditions);

		if(!empty($visible)){
			$where[] = ' visible = ? ';
			$params[] = $visible;
		}

		if(!empty($keywords)){
            $words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (title, content, description) AGAINST (?)";
				$params[] = $keywords;
			} else{
				$where[] = " (title LIKE ? || content LIKE ? || description LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

        if(__SITE_LANG == "en")  {
            $sql = "SELECT COUNT(*) as counter FROM $this->ep_updates_table ";
        } else {
            $sql = "SELECT COUNT(*) as counter
                        FROM {$this->ep_updates_table} eut
                        INNER JOIN {$this->ep_updates_table_i18n} euti18n ON eut.id = euti18n.id_ep_update AND euti18n.ep_update_i18n_lang = '".__SITE_LANG."'
                    ";
        }

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function get_one_ep_update_public($id){
        if(__SITE_LANG == "en") {
            $sql = "SELECT id, title, content, date_time, description, url
                    FROM  " . $this->ep_updates_table . "
                    WHERE visible=1 AND id=? ";
        } else {
            $sql = "SELECT
                            id,
                            ep_update_i18n_title as title,
                            ep_update_i18n_content as content,
                            date_time,
                            ep_update_i18n_description as description,
                            ep_update_i18n_url as url
                    FROM  {$this->ep_updates_table} eut
                    INNER JOIN {$this->ep_updates_table_i18n} euti18n ON euti18n.id_ep_update = eut.id AND euti18n.ep_update_i18n_lang = '".__SITE_LANG."'
                    WHERE visible=1 AND id=? ";
        }
		return $this->db->query_one($sql, array($id));
	}

	public function get_ep_updates_counter($conditions){
		$where = array();
		$params = array();
		extract($conditions);

		if(isset($visible)){
			$where[] = " visible=? ";
			$params[] = $visible;
		}

		if(isset($keywords)){
			$where[] = " MATCH (title,content,description) AGAINST (?) ";
			$params[] = $keywords;
		}

		if(isset($date_to)){
			$where[] = " DATE(date_time) <= ? ";
			$params[] = $date_to;
		}

		if(isset($date_from)){
			$where[] = " DATE(date_time) >= ? ";
			$params[] = $date_from;
		}

		$sql = "SELECT COUNT(*)  as counter
				FROM " . $this->ep_updates_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function get_one_ep_update_i18n($condition = array()){
        $where = array();
        $params = array();

        if(!empty($condition["id_ep_update"])) {
            $where[] = "id_ep_update = ?";
            $params[] = $condition["id_ep_update"];
        }

        if(!empty($condition['id_ep_update_i18n'])) {
            $where[] = "id_ep_update_i18n = ?";
            $params[] = $condition['id_ep_update_i18n'];
        }

        if(!empty($condition['ep_update_i18n_lang'])) {
            $where[] = "ep_update_i18n_lang = ?";
            $params[] = $condition['ep_update_i18n_lang'];
        }

		$sql = "SELECT *
				FROM {$this->ep_updates_table_i18n} WHERE ".
				implode(" AND ", $where);

		return $this->db->query_one($sql, $params);
	}

	public function change_ep_update($id_ep_update, $data){
		$this->db->where('id', $id_ep_update);
		return $this->db->update($this->ep_updates_table, $data);
	}

	public function change_ep_update_i18n($id_ep_update_i18n, $data){
		$this->db->where('id_ep_update_i18n', $id_ep_update_i18n);
		return $this->db->update($this->ep_updates_table_i18n, $data);
	}

	public function delete_ep_update($id_ep_update) {
		$this->db->where('id', $id_ep_update);
		return $this->db->delete($this->ep_updates_table);
	}

	public function delete_ep_update_i18n($id_ep_update_i18n) {
		$this->db->where('id_ep_update_i18n', $id_ep_update_i18n);
		return $this->db->delete($this->ep_updates_table_i18n);
	}
}
