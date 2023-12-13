<?php
/**
*country_articles.php
*
*Country blogs model
*
*@author
*/

class Ep_News_Model extends TinyMVC_Model {

	private $ep_news_table = "ep_news";
	private $ep_news_table_i18n = "ep_news_i18n";
	public $per_page = 5;

	public function insert_ep_news($data){
		$this->db->insert($this->ep_news_table, $data);
		return $this->db->last_insert_id();
	}

	public function get_one_ep_news($id, $select = '*'){
		$sql = "SELECT ".$select."
				FROM " . $this->ep_news_table .
				" WHERE id = ?";
		return $this->db->query_one($sql, array($id));
	}

	public function get_one_ep_news_i18n($condition = array()){
        $where = array();
        $params = array();

        if(!empty($condition["id_ep_news"])) {
            $where[] = "id_ep_news = ?";
            $params[] = $condition["id_ep_news"];
        }

        if(!empty($condition['id_ep_news_i18n'])) {
            $where[] = "id_ep_news_i18n = ?";
            $params[] = $condition['id_ep_news_i18n'];
        }

        if(!empty($condition['ep_news_i18n_lang'])) {
            $where[] = "ep_news_i18n_lang = ?";
            $params[] = $condition['ep_news_i18n_lang'];
        }


		$sql = "SELECT *
				FROM {$this->ep_news_table_i18n} WHERE ".
				implode(" AND ", $where);

		return $this->db->query_one($sql, $params);
	}

	public function set_ep_news_i18n($data = array()){
        $this->db->insert($this->ep_news_table_i18n, $data);
        return $this->db->last_insert_id();
    }

	public function get_ep_news( $conditions ){
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
				FROM " . $this->ep_news_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

        $start = (int) $start;
        $per_p = (int) $per_p;

		$sql .= " ORDER BY " . $order_by;
		$sql .= ' LIMIT ' . $start . ',' . $per_p;

		return $this->db->query_all($sql, $params);
	}

	public function get_list_ep_news_public($conditions){
		$where = array();
		$params = array();
		$visible = 1;
		$order_by = " id DESC ";
		$select = "id, title, description, date_time, main_image, url";
        $lang = __SITE_LANG;

		extract($conditions);

		if(!empty($visible)){
			$where[] = " visible = ? ";
			$params[] = $visible;
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

        if($lang == 'en') {
            $sql = "SELECT $select $rel FROM $this->ep_news_table ";
        } else {
            $select = explode(",", $select);
            foreach($select as &$select_element) {
                $select_element = trim($select_element);

                if(in_array($select_element, array("title", "content", "description", "url"))) {
                    $select_element = "ep_news_i18n_{$select_element} as $select_element";
                }
            }

            $select = implode(",", $select);

            $sql = "SELECT $select $rel FROM {$this->ep_news_table} ent INNER JOIN {$this->ep_news_table_i18n} enti18n ON ent.id = enti18n.id_ep_news AND enti18n.ep_news_i18n_lang = '$lang'";
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

	public function get_list_ep_news_public_count($conditions){
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

		$sql = "SELECT COUNT(*) as counter
				FROM $this->ep_news_table ";

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function get_one_ep_news_public($id_ep_news, $lang = 'en'){
        $where = array(
            "visible = 1",
            "id = ?"
        );

        if($lang == 'en') {
            $sql = "SELECT id, title, description, content, date_time, main_image, url
                    FROM  " . $this->ep_news_table;
        } else {
            $sql = "SELECT
                        id,
                        ep_news_i18n_description as description,
                        ep_news_i18n_title as title,
                        ep_news_i18n_content as content,
                        date_time,
                        main_image,
                        ep_news_i18n_url as url
                    FROM  {$this->ep_news_table} ent
                    INNER JOIN {$this->ep_news_table_i18n} enti18n ON enti18n.id_ep_news = ent.id AND enti18n.ep_news_i18n_lang = '{$lang}'";
        }

        $sql = $sql . " WHERE " . implode(" AND ", $where);
		return $this->db->query_one($sql, $id_ep_news);
	}

	public function get_other_ep_news($id_ep_news, $lang = 'en', $limit = 5){
        $where = array(
            "visible = 1",
            "id != ?"
        );

        if($lang == 'en') {
            $sql = "SELECT id, title, description, content, date_time, main_image, url
                    FROM  " . $this->ep_news_table;
        } else {
            $sql = "SELECT
                        id,
                        ep_news_i18n_description as description,
                        ep_news_i18n_title as title,
                        ep_news_i18n_content as content,
                        date_time,
                        main_image,
                        ep_news_i18n_url as url
                    FROM  {$this->ep_news_table} ent
                    INNER JOIN {$this->ep_news_table_i18n} enti18n ON enti18n.id_ep_news = ent.id AND enti18n.ep_news_i18n_lang = '{$lang}'";
        }

        $sql = $sql . " WHERE ". implode(" AND ", $where) . " ORDER BY id DESC LIMIT $limit";
		return $this->db->query_all($sql, array($id_ep_news));
	}

	public function get_ep_news_counter($conditions){
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
				FROM " . $this->ep_news_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function update_ep_news($id_ep_news, $data){
		$this->db->where('id', $id_ep_news);
		return $this->db->update($this->ep_news_table, $data);
	}

	public function update_ep_news_i18n($id_ep_news_i18n, $data) {
		$this->db->where("id_ep_news_i18n", $id_ep_news_i18n);
		return $this->db->update($this->ep_news_table_i18n, $data);
	}

	public function delete_ep_news($id_ep_news) {
		$this->db->where('id', $id_ep_news);
		return $this->db->delete($this->ep_news_table);
	}

	public function delete_ep_news_i18n($id_ep_news_i18n) {
		$this->db->where("id_ep_news_i18n", $id_ep_news_i18n);
		return $this->db->delete($this->ep_news_table_i18n);
	}
}
