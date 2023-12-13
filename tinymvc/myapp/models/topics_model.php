<?php
/**
 * model for faq and event's categories
 *
 * @author
 */

class Topics_Model extends TinyMVC_Model
{
    private $topics_table = 'popular_topics';
    private $topics_i18n_table = 'popular_topics_i18n';

    /* methods for categories of events */
    public function set_topics($data = array()){
        if(empty($data)){
			return false;
		}

        return $this->db->insert($this->topics_table, $data);
    }

    public function get_topic($id_topic = 0, $lang = __SITE_LANG){
		if ($lang != 'en') {
			$sql = "SELECT
                        t.id_topic,
                        STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(t.translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at,
                        t.visible_topic,
                        t.translations_data,
						if(t_i18n.id_topic_i18n is null, 'en', t_i18n.lang_topic) as lang_topic,
						if(t_i18n.id_topic_i18n is null, t.title_topic, t_i18n.title_topic) as title_topic,
						if(t_i18n.id_topic_i18n is null, t.text_topic_small, t_i18n.text_topic_small) as text_topic_small,
						if(t_i18n.id_topic_i18n is null, t.text_topic, t_i18n.text_topic) as text_topic
					FROM {$this->topics_table} t
                        LEFT JOIN {$this->topics_i18n_table} t_i18n
                            ON t.id_topic = t_i18n.id_topic
                                AND t_i18n.lang_topic = '{$lang}'
					WHERE t.id_topic = ?";

            $params = [$lang, $id_topic];
		} else{
            $sql = "SELECT
                        *,
                        STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at
					FROM {$this->topics_table}
					WHERE id_topic = ?";

            $params = [$id_topic];
		}

        return $this->db->query_one($sql, $params);
    }

	public function update_topic($id_topic = 0, $data = array()){
		$this->db->where('id_topic', $id_topic);

		return $this->db->update($this->topics_table, $data);
	}

	public function delete_topic($id_topic = 0){
		$this->db->where('id_topic', $id_topic);
		$this->db->delete($this->topics_i18n_table);

		$this->db->where('id_topic', $id_topic);
		return $this->db->delete($this->topics_table);
	}

    public function get_topics($conditions = array()){
		$per_p = 10;
		$order_by = 'updated_at DESC';
		$lang = __SITE_LANG;

		extract($conditions);

		$rel = '';
		$where = $params = [];

        if(isset($translated_in)) {
            $where[] = 'JSON_CONTAINS_PATH(translations_data, "one", ?)';
            $params[] = "$.{$translated_in}";
        }

        if(isset($en_updated_from)) {
            $where[] = 'STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, \'$.en.updated_at\')), "%Y-%c-%d") > DATE(?)';
            $params[] = $en_updated_from;
        }

        if(isset($en_updated_to)) {
            $where[] = 'STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, \'$.en.updated_at\')), "%Y-%c-%d") <= DATE(?)';
            $params[] = $en_updated_to;
        }

        if(isset($not_translated_in)) {
            $where[] = 'NOT JSON_CONTAINS_PATH(translations_data, "one", ?)';
            $params[] = "$.{$not_translated_in}";
        }


		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($visible_topic)){
			$where[] = 't.visible_topic = ?';
			$params[] = $visible_topic;
		}

		if ($lang != 'en') {
			$sql = "SELECT
                        t.id_topic,
                        STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(t.translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at,
                        t.visible_topic,
                        t.translations_data,
						if(t_i18n.id_topic_i18n is null, 'en', t_i18n.lang_topic) as lang_topic,
						if(t_i18n.id_topic_i18n is null, t.title_topic, t_i18n.title_topic) as title_topic,
						if(t_i18n.id_topic_i18n is null, t.text_topic_small, t_i18n.text_topic_small) as text_topic_small,
						if(t_i18n.id_topic_i18n is null, t.text_topic, t_i18n.text_topic) as text_topic
						{$rel}
					FROM popular_topics t
                        LEFT JOIN popular_topics_i18n t_i18n
                            ON t.id_topic = t_i18n.id_topic
                                AND t_i18n.lang_topic = ?";

            array_unshift($params, $lang);

            if(!empty($keywords)){
                if(str_word_count_utf8($keywords) > 1){
                    $order_by = ' REL_tags DESC ';
                    $where[] = ' MATCH (t_i18n.title_topic, t_i18n.text_topic) AGAINST (?)';
                    $params[] = $keywords;
                    $rel = " , MATCH (t_i18n.title_topic, t_i18n.text_topic) AGAINST (?) as REL_tags";
                    array_unshift($params, $keywords);
                } else {
                    $where[] = " (t_i18n.title_topic LIKE ? OR t_i18n.text_topic LIKE ?) ";
                    array_push($params, ...['%' . $keywords . '%', '%' . $keywords . '%']);
                }
            }
		} else{
			if(!empty($keywords)){
				if(str_word_count_utf8($keywords) > 1){
					$order_by = ' REL_tags DESC ';
					$where[] = ' MATCH (t.title_topic, t.text_topic) AGAINST (?)';
					$params[] = $keywords;
					$rel = " , MATCH (t.title_topic, t.text_topic) AGAINST (?) as REL_tags";
                    array_unshift($params, $keywords);
				} else{
					$where[] = " (t.title_topic LIKE ? OR t.text_topic LIKE ?) ";
                    array_push($params, ...['%' . $keywords . '%', '%' . $keywords . '%']);
				}
			}

            $sql = "SELECT
                        *,
                        STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(t.translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at
                        {$rel}
					FROM {$this->topics_table} t";
		}

		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		$sql .= ' ORDER BY ' . $order_by;

  	    if (isset($limit)) {
            $sql .= " LIMIT {$limit}";
        } elseif(isset($start)){
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= " LIMIT {$start},{$per_p}";
        }

		return $this->db->query_all($sql, $params);
    }

    public function count_topics($conditions = array()){
        $lang = __SITE_LANG;

		extract($conditions);

		$where = $params = [];

		if(isset($translated_in)) {
            $where[] = 'JSON_CONTAINS_PATH(translations_data, "one", ?)';
            $params[] = "$.{$translated_in}";
        }

		if(isset($en_updated_from)) {
            $where[] = 'STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, \'$.en.updated_at\')), "%Y-%c-%d") > DATE(?)';
            $params[] = $en_updated_from;
        }

        if(isset($en_updated_to)) {
            $where[] = 'STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, \'$.en.updated_at\')), "%Y-%c-%d") <= DATE(?)';
            $params[] = $en_updated_to;
		}

		if(isset($not_translated_in)) {
            $where[] = 'NOT JSON_CONTAINS_PATH(translations_data, "one", ?)';
            $params[] = "$.{$not_translated_in}";
        }

		if(isset($visible_topic)){
			$where[] = 't.visible_topic = ?';
			$params[] = $visible_topic;
		}

		if ($lang != 'en') {
			if(!empty($keywords)){
				if(str_word_count_utf8($keywords) > 1){
					$where[] = ' MATCH (t_i18n.title_topic, t_i18n.text_topic) AGAINST (?)';
					$params[] = $keywords;
				} else{
					$where[] = " (t_i18n.title_topic LIKE ? OR t_i18n.text_topic LIKE ?) ";
                    array_push($params, ...['%' . $keywords . '%', '%' . $keywords . '%']);
				}
			}

            $sql = "SELECT COUNT(*) as counter
                    FROM popular_topics t
                    LEFT JOIN popular_topics_i18n t_i18n
                    ON t.id_topic = t_i18n.id_topic AND t_i18n.lang_topic = ?";

            array_unshift($params, $lang);
		} else {
			if(!empty($keywords)){
				if(str_word_count_utf8($keywords) > 1){
					$where[] = ' MATCH (t.title_topic, t.text_topic) AGAINST (?)';
					$params[] = $keywords;
				} else{
					$where[] = " (t.title_topic LIKE ? OR t.text_topic LIKE ?) ";
                    array_push($params, ...['%' . $keywords . '%', '%' . $keywords . '%']);
				}
			}

            $sql = "SELECT COUNT(*) as counter
					FROM {$this->topics_table} t";
		}

		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
    }

	public function set_topic_i18n($data = array()){
        return empty($data) ? false : $this->db->insert($this->topics_i18n_table, $data);
    }

	public function get_topic_i18n($conditions = array()){
		if(empty($conditions)){
			return false;
		}

		extract($conditions);

		$where = $params = [];

		if(isset($id_topic_i18n)){
			$where[] = ' t_i18n.id_topic_i18n = ? ';
			$params[] = $id_topic_i18n;
		}

		if(isset($id_topic)){
			$where[] = ' t_i18n.id_topic = ? ';
			$params[] = $id_topic;
		}

		if(isset($lang_topic)){
			$where[] = ' t_i18n.lang_topic = ? ';
			$params[] = $lang_topic;
		}

        $sql = "SELECT
                    t_i18n.*,
                    t.visible_topic,
                    STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(t.translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at,
                    t.translations_data
				FROM {$this->topics_i18n_table} t_i18n
                    LEFT JOIN {$this->topics_table} t
                        ON t_i18n.id_topic = t.id_topic
				WHERE " . implode(' AND ', $where);

		return $this->db->query_one($sql, $params);
	}

	public function update_topic_i18n($id_topic_i18n = 0, $data = array()){
		$this->db->where('id_topic_i18n', $id_topic_i18n);
		return $this->db->update($this->topics_i18n_table, $data);
	}
}
