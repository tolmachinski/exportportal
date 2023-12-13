<?php

class Meta_Model extends TinyMVC_Model {

	private $meta_table = 'meta_pages';
	private $translations_languages_table = 'translations_languages';

	public function get_meta( $conditions ){
		$select = '*';
		extract($conditions);

        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 10);

        $where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($page_key)){
			$where[] = " m.page_key = ? ";
			$params[] = $page_key;
		}

		if(isset($id_lang)){
			$where[] = " m.id_lang = ? ";
			$params[] = $id_lang;
		}

		if(isset($keywords)){
			$where[] = "m.page_key LIKE ? ";
            $params[] = '%' . $keywords . '%';
		}

		$sql = "SELECT $select
				FROM $this->meta_table as m
				LEFT JOIN $this->translations_languages_table as tl ON m.id_lang = tl.id_lang ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " ORDER BY ".$order_by;
		$sql .= ' LIMIT ' . $start . ',' . $per_p;

		return $this->db->query_all($sql, $params);
	}

	public function get_meta_counter( $conditions ){
        extract($conditions);
		$where = $params = [];

		if(isset($keywords)){
			$where[] = "m.page_key LIKE ? ";
            $params[] = '%' . $keywords . '%';
		}

		if(isset($page_key)){
			$where[] = " m.page_key = ? ";
			$params[] = $page_key;
		}

		if(isset($id_lang)){
			$where[] = " m.id_lang = ? ";
			$params[] = $id_lang;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM $this->meta_table as m
				LEFT JOIN $this->translations_languages_table as tl ON m.id_lang = tl.id_lang ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return $this->db->query_one($sql, $params)['counter'] ?? 0;
	}

	public function exist_meta($page_key, $lang){
		$sql = "SELECT COUNT(*) as counter
			FROM $this->meta_table as m
			WHERE page_key = ? AND id_lang = ? ";

		return $this->db->query_one($sql, array($page_key, $lang))['counter'];
	}

	public function get_one_meta($id){
		$sql = "SELECT *
			FROM $this->meta_table as m
			LEFT JOIN $this->translations_languages_table as tl ON m.id_lang = tl.id_lang
			WHERE id = ? ";
		return $this->db->query_one($sql, array($id));
	}

	public function get_meta_by_key($key){
        $this->db->from($this->meta_table);
		$this->db->where('page_key', $key);
		return $this->db->query_one();
	}

	public function delete_meta($id){
		$this->db->where('id', $id);
		return $this->db->delete($this->meta_table);
	}

	public function update_meta($id, $update){
		$this->db->where('id', $id);
		return $this->db->update($this->meta_table, $update);
	}

	public function insert_meta($insert){
		return $this->db->insert($this->meta_table, $insert);
	}

	function handle_meta($meta, $params = array()){
		$prepared_meta = array();
		if(!empty($params) && ($rules = json_decode($meta['rules'], true))){
			$types = array('image', 'title', 'description', 'keywords', 'h1');

			foreach($types as $name_meta){
				$cur_templates = array();
				if(isset($rules[$name_meta])){
					foreach($params as $key => $text){
                        if(!isset($rules[$name_meta][$key])) {
                            continue;
                        }

						$cur_templates[$key] = str_replace(
                            $key,
                            $text,
                            $rules[$name_meta][$key]
                        );
					}
                }

                if(isset($meta[$name_meta])) {
                    $prepared_meta[$name_meta] = preg_replace(
                        '~\[.*?\]~', '',
                        str_replace(
                            array_keys($cur_templates),
                            array_values($cur_templates),
                            $meta[$name_meta]
                        )
                    );
                }
			}

			if(empty($prepared_meta['image']))
				$prepared_meta['image'] = $meta['image'];
		}else{
			$prepared_meta = array(
				'image' => $meta['image'] ?? '',
				'title' => $meta['title'] ?? '',
				'keywords' => $meta['keywords'] ?? '',
				'description' => $meta['description'] ?? '',
			);
			$prepared_meta = preg_replace('~\[.*?\]~', '', $prepared_meta);
		}
		return $prepared_meta;
	}

	// FROM SEO_MODEL

	function get_seo($conditions){
        $lang_iso2 = 'en';

		extract($conditions);

        $lang_iso2 = getArrayFromString($lang_iso2);

        $this->db->where('page_key', $page_key);
        $this->db->in('lang_iso2', $lang_iso2);

        return $this->db->get('meta_pages');
	}
}
