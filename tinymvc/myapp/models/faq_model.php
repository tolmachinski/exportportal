<?php

class Faq_Model extends TinyMVC_Model {

    var $obj;
    private $faq_table = 'faq';
    private $faq_i18n_table = 'faq_i18n';
    private $languages_table = 'translations_languages';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function set_faq($data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->insert($this->faq_table, $data);
		return $this->db->last_insert_id();
	}

	public function update_faq($id_faq = 0, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('id_faq', $id_faq);
		return $this->db->update($this->faq_table, $data);
	}

	public function get_faq($id_faq = 0, $lang = __SITE_LANG){
        if ($lang != 'en') {
			$sql = "SELECT
						f.id_faq,
						f.translations_data,
						if(f_i18n.id_faq_i18n is null, 'en', f_i18n.lang_faq) as lang_faq,
						if(f_i18n.id_faq_i18n is null, f.question, f_i18n.question) as question,
						if(f_i18n.id_faq_i18n is null, f.answer, f_i18n.answer) as answer
					FROM {$this->faq_table} f
                        LEFT JOIN {$this->faq_i18n_table} f_i18n
                            ON f.id_faq = f_i18n.id_faq AND f_i18n.lang_faq = ?
					WHERE f.id_faq = ?";

            $params = [$lang, $id_faq];
		} else{
			$sql = "SELECT *
					FROM {$this->faq_table}
					WHERE id_faq = ?";

            $params = [$id_faq];
		}

        return $this->db->query_one($sql, $params);
    }

	public function exist_faq($id_faq = 0){
		$sql = "SELECT COUNT(*) as exist
				FROM {$this->faq_table}
				WHERE id_faq = ?";
		$rez = $this->db->query_one($sql, array($id_faq));
		return $rez['exist'];
	}

	public function delete_faq($id_faq = 0){
		$this->db->where('id_faq', $id_faq);
        if(!$this->db->delete($this->faq_table)) {
            return false;
        }

        $this->db->where('id_faq', $id_faq);
        return $this->db->delete($this->faq_i18n_table);
	}

    public function get_faq_list($conditions = array())
    {
		$where = array();
        $params = array();
		$order_by = 'f.id_faq ASC';
		$rel = '';
		$lang = __SITE_LANG;

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        if(isset($translated_in)) {
            $where[] = 'JSON_CONTAINS_PATH(f.translations_data, "one", ?)';
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
            $where[] = 'NOT JSON_CONTAINS_PATH(f.translations_data, "one", ?)';
            $params[] = "$.{$not_translated_in}";
        }

		if ($lang != 'en') {
			if(!empty($keywords)){
				if(str_word_count_utf8($keywords) > 1){
					$order_by = ' REL_tags DESC ';
					$where[] = ' MATCH (f_i18n.question, f_i18n.answer) AGAINST (?)';
					$params[] = $keywords;
					$rel = ' , MATCH (f_i18n.question, f_i18n.answer) AGAINST ( ? ) as REL_tags';
                    array_unshift($params, $keywords);
				} else{
					$where[] = ' (f_i18n.question LIKE ? || f_i18n.answer LIKE ?) ';

                    array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
				}
			}

			$sql = "SELECT
			 			f.id_faq,
                        f.translations_data,
                        STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(f.translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at,
						if(f_i18n.id_faq_i18n is null, 'en', f_i18n.lang_faq) as lang_faq,
						if(f_i18n.id_faq_i18n is null, f.question, f_i18n.question) as question,
						if(f_i18n.id_faq_i18n is null, f.answer, f_i18n.answer) as answer
						{$rel}
					FROM {$this->faq_table} f
                        LEFT JOIN {$this->faq_i18n_table} f_i18n
                            ON f.id_faq = f_i18n.id_faq
                                AND f_i18n.lang_faq = ?";

            if (empty($rel)) {
                array_unshift($params, $lang);
            } else {
                array_splice($params, 1, 0, [$lang]);
            }
		} else {
			if(!empty($keywords)){
				if(str_word_count_utf8($keywords) > 1){
					$order_by = ' REL_tags DESC ';
					$where[] = ' MATCH (f.question, f.answer) AGAINST (?)';
					$params[] = $keywords;
					$rel = ' , MATCH (f.question, f.answer) AGAINST ( ? ) as REL_tags';
                    array_unshift($params, $keywords);
				} else{
					$where[] = ' (f.question LIKE ? OR f.answer LIKE ?) ';
                    array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
				}
			}

            $sql = "SELECT
                        f.*,
                        STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(f.translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at
                        {$rel}
                    FROM {$this->faq_table} f";
		}

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		$sql .= ' ORDER BY ' . $order_by;

		if(isset($limit)) {
			$sql .= ' LIMIT ' . $limit;
        }

		return $this->db->query_all($sql, $params);
    }

    public function count_faq_list($conditions)
    {
		$where = array();
        $params = array();
		$lang = __SITE_LANG;

        extract($conditions);

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

		if(!empty($keywords)){
            $words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = ' MATCH (question, answer) AGAINST (?)';
				$params[] = $keywords;
			} else{
				$where[] = ' (question LIKE ? || answer LIKE ?) ';
                array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
			}
        }

        $sql = "SELECT COUNT(*) as counter
                FROM {$this->faq_table}";

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		$rez = $this->db->query_one($sql,$params);
		return $rez['counter'];
    }

	// i18n
	public function set_faq_i18n($data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->insert($this->faq_i18n_table, $data);
		return $this->db->last_insert_id();
	}

    public function get_faq_i18n($id, array $params = array())
    {
		$with = array();
        $columns = array();
        $conditions = array();

        extract($params);
		extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select(empty($columns) ? "*" : (is_string($columns) ? $columns : implode(', ', $columns)));
        $this->db->from("{$this->faq_i18n_table} I18N");
        $this->db->where('I18N.id_faq_i18n = ? ', $id);

        if(isset($with_faq) && $with_faq) {
            $this->db->join("{$this->faq_table} F", 'F.id_faq = I18N.id_faq');
            if(is_callable($with_faq)) {
                $with_faq($this->db, $this);
            }
        }
        if(isset($with_language) && $with_language) {
            $this->db->join("{$this->languages_table} L", 'L.lang_iso2 = I18N.lang_faq');
            if(is_callable($with_language)) {
                $with_language($this->db, $this);
            }
        }

		if(isset($condition_faq)){
            $this->db->where('I18N.id_faq = ?', $condition_faq);
		}

		if(isset($condition_faq_list) && is_array($condition_faq_list) && !empty($condition_faq_list)){
            $this->db->in('I18N.id_faq', $condition_faq_list);
		}

		if(isset($condition_language)){
            $this->db->where('I18N.lang_faq = ?', $condition_language);
        }

		if(isset($condition_languages) && is_array($condition_languages) && !empty($condition_languages)){
            $this->db->in('I18N.lang_faq', $condition_languages);
        }

        $data = $this->db->query_one();

        return $data ? $data : null;
    }

    public function find_faq_i18n(array $params = array())
    {
        $with = array();
        $columns = array();
        $conditions = array();

        extract($params);
		extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select(empty($columns) ? '*' : (is_string($columns) ? $columns : implode(', ', $columns)));
        $this->db->from("{$this->faq_i18n_table} I18N");

        if(isset($with_faq) && $with_faq) {
            $this->db->join("{$this->faq_table} F", 'F.id_faq = I18N.id_faq');
            if(is_callable($with_faq)) {
                $with_faq($this->db, $this);
            }
        }
        if(isset($with_language) && $with_language) {
            $this->db->join("{$this->languages_table} L", 'L.lang_iso2 = I18N.lang_faq');
            if(is_callable($with_language)) {
                $with_language($this->db, $this);
            }
        }

		if(isset($condition_faq)){
            $this->db->where('I18N.id_faq = ?', $condition_faq);
		}

		if(isset($condition_faq_list) && is_array($condition_faq_list) && !empty($condition_faq_list)){
            $this->db->in('I18N.id_faq', $condition_faq_list);
		}

		if(isset($condition_language)){
            $this->db->where('I18N.lang_faq = ?', $condition_language);
        }

		if(isset($condition_languages) && is_array($condition_languages) && !empty($condition_languages)){
            $this->db->in('I18N.lang_faq', $condition_languages);
        }

        $data = $this->db->query_one();

        return $data ? $data : null;
    }

    public function has_faq_i18n($faq_id, $lang_code)
    {
        if(empty($faq_id) || empty($lang_code)) {
            return false;
        }

        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->faq_i18n_table);
        $this->db->where('id_faq = ?', $faq_id);
        $this->db->where('lang_faq = ?', $lang_code);
        if (!$this->db->query()) {
            return false;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return isset($data['AGGREGATE']) ? filter_var($data['AGGREGATE'], FILTER_VALIDATE_BOOLEAN) : false;
    }

    public function get_faq_i18n_list(array $params = array())
    {
        $with = array();
        $columns = array();
        $conditions = array();

        extract($params);
		extract($conditions, EXTR_PREFIX_ALL, 'condition');
        extract($with, EXTR_PREFIX_ALL, 'with');

        $this->db->select(empty($columns) ? '*' : (is_string($columns) ? $columns : implode(', ', $columns)));
        $this->db->from("{$this->faq_i18n_table} I18N");

        if(isset($with_faq) && $with_faq) {
            $this->db->join("{$this->faq_table} F", 'F.id_faq = I18N.id_faq');
            if(is_callable($with_faq)) {
                $with_faq($this->db, $this);
            }
        }
        if(isset($with_language) && $with_language) {
            $this->db->join("{$this->languages_table} L", 'L.lang_iso2 = I18N.lang_faq');
            if(is_callable($with_language)) {
                $with_language($this->db, $this);
            }
        }

		if(isset($condition_faq)){
            $this->db->where('I18N.id_faq = ?', $condition_faq);
		}

		if(isset($condition_faq_list) && is_array($condition_faq_list) && !empty($condition_faq_list)){
            $this->db->in('I18N.id_faq', $condition_faq_list);
		}

		if(isset($condition_language)){
            $this->db->where('I18N.lang_faq = ?', $condition_language);
        }

		if(isset($condition_languages) && is_array($condition_languages) && !empty($condition_languages)){
            $this->db->in('I18N.lang_faq', $condition_languages);
        }

        return $this->db->query_all();
    }

	public function update_faq_i18n($id_faq_i18n = 0, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('id_faq_i18n', $id_faq_i18n);
		return $this->db->update($this->faq_i18n_table, $data);
	}
}
