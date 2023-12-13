<?php

/**
 *
 *
 * model for text block
 *
 * @author
 */

class Text_block_Model extends TinyMVC_Model
{
	private $textual_blocks_table = 'textual_blocks';
	private $textual_blocks_i18n_table = 'textual_blocks_i18n';

	public function set_text_block($data){
		return $this->db->insert($this->textual_blocks_table, $data);
	}

	public function set_text_block_i18n($data){
		return $this->db->insert($this->textual_blocks_i18n_table, $data);
	}

	public function exist_text_block($id_block = 0){
        $this->db->select('count(*) as exist');
        $this->db->where('id_block', $id_block);
        $this->db->limit(1);

        return $this->db->get_one($this->textual_blocks_table)['exist'];
	}

	public function exist_text_block_i18n($id_block_i18n = 0){
        $this->db->select('count(*) as exist');
        $this->db->where('id_block_i18n', $id_block_i18n);
        $this->db->limit(1);

        return $this->db->get_one($this->textual_blocks_i18n_table)['exist'];
	}

	public function update_text_block($id_block = 0, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('id_block', $id_block);
		return $this->db->update($this->textual_blocks_table, $data);
	}

	public function update_text_block_i18n($id_block_i18n = 0, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('id_block_i18n', $id_block_i18n);
		return $this->db->update($this->textual_blocks_i18n_table, $data);
	}

	public function delete_text_block($id_block = 0){
		$this->db->where('id_block', $id_block);
		$this->db->delete($this->textual_blocks_i18n_table);

		$this->db->where('id_block', $id_block);
		return $this->db->delete($this->textual_blocks_table);
	}

	public function get_text_block($id_block = 0){
        $this->db->where('id_block', $id_block);
        $this->db->limit(1);

        return $this->db->get_one($this->textual_blocks_table);
	}

	public function get_text_block_i18n($conditions = array()){
		if(empty($conditions)){
			return false;
		}

		extract($conditions);

		$where = $params = [];

		if(isset($id_block_i18n)){
			$where[] = ' t_i18n.id_block_i18n = ? ';
			$params[] = $id_block_i18n;
		}

		if(isset($id_block)){
			$where[] = ' t_i18n.id_block = ? ';
			$params[] = $id_block;
		}

		if(isset($lang_block)){
			$where[] = ' t_i18n.lang_block = ? ';
			$params[] = $lang_block;
		}

        $sql = "SELECT
                    t_i18n.*,
                    t.short_name
				FROM {$this->textual_blocks_i18n_table} t_i18n
                    LEFT JOIN {$this->textual_blocks_table} t
                        ON t_i18n.id_block = t.id_block
				WHERE " . implode(" AND ", $where);

		return $this->db->query_one($sql, $params);
	}

	public function get_text_block_langs($id_block = 0){
        $sql = "SELECT
                    GROUP_CONCAT(lang_block SEPARATOR ',') as langs_block
				FROM {$this->textual_blocks_i18n_table}
				WHERE id_block = ? ";

		$result = $this->db->query_one($sql, array($id_block));
		$langs = array_filter(explode(',', $result['langs_block']));
		$langs[] = 'en';
		return $langs;
	}

	public function get_text_block_by_shortname($short_name = '', $lang = __SITE_LANG){
		if($lang == 'en'){
			$sql = "SELECT *
					FROM {$this->textual_blocks_table}
					WHERE short_name = ?";
            $params = [$short_name];
		} else{
			$sql = "SELECT
						t.id_block, t.short_name,
						if(t_i18n.id_block_i18n is null, t.description_block, t_i18n.description_block) as description_block,
						if(t_i18n.id_block_i18n is null, t.title_block, t_i18n.title_block) as title_block,
						if(t_i18n.id_block_i18n is null, t.text_block, t_i18n.text_block) as text_block
					FROM {$this->textual_blocks_table} t
                        LEFT OUTER JOIN {$this->textual_blocks_i18n_table} t_i18n
                            ON t.id_block = t_i18n.id_block
                                AND t_i18n.lang_block = ?
					WHERE t.short_name = ?";

            $params = [$lang, $short_name];
		}

		return $this->db->query_one($sql, $params);
	}

	public function get_text_block_by_shortnames($short_names = [], $lang = __SITE_LANG){
		if( empty($short_names) ){
			return false;
		}

        $params = $short_names = getArrayFromString($short_names);

		if($lang == 'en'){
			$sql = "SELECT *
					FROM {$this->textual_blocks_table}
					WHERE short_name IN (" . implode(',', array_fill(0, count($short_names), '?')) . ")";
		} else{
			$sql = "SELECT
						t.id_block, t.short_name,
						if(t_i18n.id_block_i18n is null, 'en', t_i18n.lang_block) as lang_block,
						if(t_i18n.id_block_i18n is null, t.description_block, t_i18n.description_block) as description_block,
						if(t_i18n.id_block_i18n is null, t.title_block, t_i18n.title_block) as title_block,
						if(t_i18n.id_block_i18n is null, t.text_block, t_i18n.text_block) as text_block
					FROM {$this->textual_blocks_table} t
                    LEFT JOIN {$this->textual_blocks_i18n_table} t_i18n
                        ON t.id_block = t_i18n.id_block
                            AND t_i18n.lang_block = ?
					WHERE t.short_name IN (" . implode(',', array_fill(0, count($short_names), '?')) . ")";

            array_unshift($params, $lang);
		}

		return $this->db->query_all($sql, $params);
	}

	public function get_text_blocks($conditions){
		$start = 1;
		$per_p = 2;
		$rel = '';

		extract($conditions);

		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($list_id)){
            $list_id = getArrayFromString($list_id);
			$where[] = " t.id_block IN(" . implode(',', array_fill(0,  count($list_id), '?')) . ") ";
            array_push($params, ...$list_id);
		}

		if(isset($short_name)){
			$where[] = " t.short_name LIKE ? ";
            $params[] = $short_name . '%';
		}

		if(!empty($keywords)){
			$order_by = ' REL DESC , ' . $order_by;
			$where[] = " MATCH (t.description_block, t.title_block, t.text_block) AGAINST (?)";
            $params[] = $keywords;
			$rel = " , MATCH (t.description_block, t.title_block, t.text_block) AGAINST (?) as REL";
            array_unshift($params, $keywords);
		}

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

		$sql = "SELECT
                    t.*,
                    STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(translations_data, '$.en.updated_at')), '%Y-%c-%d %H:%i:%s') as updated_at
                    {$rel}
				FROM {$this->textual_blocks_table} t
                LEFT JOIN {$this->textual_blocks_i18n_table} t_i18n ON t.id_block = t_i18n.id_block ";

		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		$sql .= ' GROUP BY t.id_block ';

		if($order_by) {
			$sql .= " ORDER BY {$order_by} ";
        }

		$sql .= " LIMIT {$start}, {$per_p}";
		return $this->db->query_all($sql, $params);
	}

	public function get_text_blocks_count($conditions){
        extract($conditions);

		$where = $params = [];

		if(isset($list_id)){
            $list_id = getArrayFromString($list_id);
			$where[] = " id_block IN(" . implode(',', array_fill(0,  count($list_id), '?')) . ") ";
            array_push($params, ...$list_id);
		}

		if(isset($short_name)){
			$where[] = " short_name LIKE ? ";
            $params[] = $short_name . '%';
		}

		if(!empty($keywords)){
			$where[] = ' MATCH (description_block,title_block,text_block) AGAINST (?)';
			$params[] = $keywords;
		}

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

		$sql = "SELECT COUNT(*) as counter FROM {$this->textual_blocks_table} ";

		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}
}
