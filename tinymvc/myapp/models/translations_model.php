<?php

/**
 *
 *
 * model
 *
 * @author
 */

class Translations_Model extends TinyMVC_Model {

    public $lang_folder_js;

	private $lang_folder;
	private $lang_table = 'translations_languages';
	private $routes_table = 'translations_routes';
    private $translations_files_table = 'translations_files';
    private $keys_pages_relation_table = 'translations_keys_pages_relation';
    private $pages_modules_relation_table = 'page_modules_relation';
    private $translations_log_table = 'translations_log';
    private $translationsKeysUsageLogTable = 'translations_keys_usage_log';
    private $pages_table = 'pages';
    private $modules_table = 'ep_modules';

	public $translation_type = array(
		'google_hash' => array(
			'title' => 'Google'
		),
		'get_variable' => array(
			'title' => 'GET variable'
		),
		'domain' => array(
			'title' => 'Domain'
		)
	);

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

		$this->lang_folder = config('default_lang_files_folder');
		$this->lang_folder_js = config('default_lang_files_folder_js');
	}

	// LANGUAGES
	function insert_language($data = array()){
        return empty($data) ? false : $this->db->insert($this->lang_table, $data);
    }

    function log($data = array()){
        return empty($data) ? false : $this->db->insert($this->translations_log_table, $data);
    }

    function log_batch($data){
        return empty($data) ? false : $this->db->insert_batch($this->translations_log_table, $data);
    }

	function update_language($id_lang = 0, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('id_lang', $id_lang);
		$this->db->update($this->lang_table, $data);
	}

	function get_language($id_lang = 0){
        $this->db->where('id_lang', $id_lang);
        $this->db->limit(1);
        return $this->db->get_one($this->lang_table);
    }

    public function has_language($lang_id){
        if(empty($lang_id)) {
            return false;
        }

        $this->db->select("COUNT(*) AS AGGREGATE");
        $this->db->from($this->lang_table);
        $this->db->where('id_lang = ?', $lang_id);
        if (!$this->db->query()) {
            return false;
        }
        $data = $this->db->getQueryResult()->fetchAssociative();

        return isset($data['AGGREGATE']) ? filter_var($data['AGGREGATE'], FILTER_VALIDATE_BOOLEAN) : false;
    }

	function get_language_by_iso2($lang_iso2 = 'en', $conditions = array()){
        $this->db->select("*");
        $this->db->from("{$this->lang_table}");
        $this->db->where("lang_iso2 = ?", $lang_iso2);

		extract($conditions);

		if(isset($lang_active)){
            $this->db->where("lang_active = ?", $lang_active);
		}

		if(isset($lang_url_type)){
            if(!is_array($lang_url_type)){
                $lang_url_type = array_map('trim', explode(',', $lang_url_type));
            }

            $this->db->in("lang_url_type", $lang_url_type);
        }

        return $this->db->get_one();
	}

	function get_languages($conditions = array()){
        $order_by = "lang_weight ASC";

        extract($conditions);

        $this->db->select("*");
        $this->db->from($this->lang_table);

		if (isset($lang_active)) {
            $this->db->where("lang_active = ?", $lang_active);
        }

        if (isset($lang_default)) {
            $this->db->where("lang_default = ?", $lang_default);
        }

        if (isset($domain_or_iso2)) {
            $this->db->where_raw("(lang_url_type = ? OR lang_iso2 = ?)", ['domain', $domain_or_iso2]);
        }

		if (isset($lang_url_type)) {
            $lang_url_type = getArrayFromString($lang_url_type);
            $this->db->where_raw("lang_url_type IN (" . implode(',', array_fill(0, count($lang_url_type), '?')) . ")", $lang_url_type);
        }

        if (isset($not_domain)) {
            $this->db->where('lang_url_type != ?', 'domain');
        }

        if (isset($lang_iso2)) {
            if(!is_array($lang_iso2)){
				$lang_iso2 = explode(',', $lang_iso2);
            }

            $this->db->in("lang_iso2", $lang_iso2);
        }

        if (isset($sort_by)) {
			$multi_order_by = array();
			foreach ($sort_by as $sort_item) {
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
			}

			if(!empty($multi_order_by)){
				$order_by = implode(',', $multi_order_by);
			}
		}
		$this->db->orderby($order_by);

		return $this->db->get();
    }

    public function get_allowed_languages(array $conditions = array())
    {
        $skip = null;
        $columns = null;
        $order_by = 'lang_weight ASC';

        extract($conditions);

        $app = tmvc::instance()->controller;
        $allowed = $app->session->group_lang_restriction ? $app->session->group_lang_restriction_list : null;
        $columns = empty($columns) ? "*" : (!is_array($columns) ? $columns : implode(', ', $columns));
        $where = [];
        $params = [];

        if(null !== $allowed) {
            $where[] = sprintf("id_lang IN (%s)", implode(',', array_fill(0, count($allowed), '?')));
            $params = array_merge($params, $allowed);
        }

        if(!empty($skip)) {
            $skip = !is_string($skip) ? $skip : array_map(
                function($item) { return trim($item); },
                explode(',', $skip)
            );
            $where[] = sprintf("lang_iso2 NOT IN (%s)", implode(',', array_fill(0, count($skip), '?')));
            $params = array_merge($params, $skip);
        }

        if(isset($lang_url_type)){
            $lang_url_type = !is_string($lang_url_type) ? $lang_url_type : array_map(
                function($item) { return trim($item); },
                explode(',', $lang_url_type)
            );

            $where[] = sprintf("lang_url_type IN (%s)", implode(',', array_fill(0, count($lang_url_type), '?')));
            $params = array_merge($params, $lang_url_type);
        }

		if(isset($lang_active)){
            $where[] = 'lang_active = ?';
            $params[] = $lang_active;
		}

        if(isset($id_lang)) {
            $where[] = 'id_lang = ?';
            $params[] = $id_lang;
        }

        if(isset($lang_iso2)) {
            $where[] = 'lang_iso2 = ?';
            $params[] = $lang_iso2;
        }

        if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		$sql = "SELECT {$columns} FROM {$this->lang_table}";

		if(count($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY {$order_by}";

        return $this->db->query_all($sql, $params);
    }

	function get_active_languages($column = ""){
        $column = !empty($column) ? $column : "`lang_name`, `lang_iso2`";
        $sql = "SELECT {$column}
				FROM {$this->lang_table}
				WHERE `lang_active` = 1";
        $output = $this->db->query_all($sql);
        if ($column == 'lang_iso2') {
        	return array_column($output, 'lang_iso2');
		}

		return $output;
	}

	function count_languages($conditions = array()){
        extract($conditions);

		$where = $params = [];

		if(isset($lang_active)){
			$where[] = " lang_active = ? ";
			$params[] = $lang_active;
		}

		if(isset($lang_url_type)){
            $lang_url_type = getArrayFromString($lang_url_type);
			$where[] = " lang_url_type IN (" . implode(',', array_fill(0, count($lang_url_type), '?')) . ") ";
            array_push($params, ...$lang_url_type);
		}

		$sql = "SELECT COUNT(*) as counter FROM {$this->lang_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
	}

	// ROUTINGS
	function insert_routing($data = array()){
        return empty($data) ? false : $this->db->insert($this->routes_table, $data);
	}

	function update_routing($id_route = 0, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('id_route', $id_route);
		$this->db->update($this->routes_table, $data);
	}

	function get_routing($id_route = 0){
        $this->db->where('id_route', $id_route);
        $this->db->limit(1);

        return $this->db->get_one($this->routes_table);
	}

	function get_routing_by_key($route_key = ''){
		if(empty($route_key)){
			return false;
		}

        $this->db->where('route_key', $route_key);
        $this->db->limit(1);

        return $this->db->get_one($this->routes_table);
	}

	function get_route_weight($conditions = array()){
		$direction = 'down';
		$order_by = 'route_weight DESC';
        extract($conditions);

		$this->db->select('*');
		$this->db->from($this->routes_table);
        if(isset($route_weight)){
            if($direction == 'down'){
                $this->db->where('route_weight >= ', $route_weight + 1);
				$order_by = 'route_weight ASC';
            } else{
                $this->db->where('route_weight <= ', $route_weight - 1);
            }
        }

		$this->db->orderby($order_by);
		$this->db->limit(1);

        return $this->db->query_one();
	}

	function get_routings($conditions = array()){
        $order_by = "route_weight ASC";

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		$sql = "SELECT * FROM {$this->routes_table} ORDER BY {$order_by}";

		return $this->db->query_all($sql);
	}

	function count_routings($conditions = array()){
        extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->limit(1);

        return $this->db->get_one($this->routes_table)['counter'];
	}

	function insert_translations_files_batch($data = array()){
        return empty($data) ? false : $this->db->insert_batch($this->translations_files_table, $data);
	}

	function insert_translation_key($data = array()){
        return empty($data) ? false : $this->db->insert($this->translations_files_table, $data);
	}

    public function insert_translations_batch($data)
    {
        if(empty($data)){
			return;
        }

		$this->db->insert_batch($this->translations_files_table, $data);
		return $this->db->getAffectableRowsAmount();
    }

	function update_translation_key($id_key, $data = array()){
		if(empty($data)){
			return;
		}

		$this->db->where('id_key', $id_key);

        return $this->db->update($this->translations_files_table, $data);
	}

	function update_translation_key_alias($translation_key, $data = array()){
		if(empty($data)){
			return;
		}

		$this->db->where('translation_key', $translation_key);
		$this->db->update($this->translations_files_table, $data);
    }

    public function replace_translation_keys_file_entries($usage_entries)
    {
        $now = new \DateTime();
        foreach ($usage_entries as &$entry) {
            if(!isset($entry['id_key'])) {
                $entry = null;

                continue;
            }

            if(empty($entry['translation_file_entries'])) {
                $entry['translation_file_entries']['list'] = array();
            }
            $entry['translation_file_entries']['updated_at'] = $now->format('Y-m-d H:i:s');
            $entry['translation_file_entries'] = json_encode($entry['translation_file_entries'], JSON_UNESCAPED_SLASHES);
        }

        $usage_entries = array_filter($usage_entries, function($entry) {return !empty($entry); });
        if(empty($usage_entries)) {
            return false;
        }

        try {
            $this->db->getConnection()->beginTransaction();
            $result = $this->db->getConnection()->prepare("UPDATE {$this->translations_files_table} SET `translation_file_entries` = ? WHERE `id_key` = ?");
            foreach ($usage_entries as $entry) {
                $params = array();
                $params[] = $entry['translation_file_entries'];
                $params[] = $entry['id_key'];
                $result->execute($params);
            }

            return $this->db->getConnection()->commit();
        } catch (\PDOException $exception) {
            $this->db->getConnection()->rollBack();

            return false;
        }
    }

	function delete_translation_key($id_key){
        $this->db->where('id_key', $id_key);

		return $this->db->delete($this->translations_files_table);
	}

	function get_translation_file_key($translation_key){
        $this->db->where('translation_key', $translation_key);
        $this->db->limit(1);

        return $this->db->get_one($this->translations_files_table);
	}

	function exist_translation_file_key($translation_key){
        $this->db->select('COUNT(*) as total_result');
        $this->db->where('translation_key', $translation_key);
        $this->db->limit(1);

        return $this->db->get_one($this->translations_files_table)['total_result'] > 0;
    }

	function exist_translation_file($translation_id){
        $this->db->select('COUNT(*) as total_result');
        $this->db->where('id_key', $translation_id);
        $this->db->limit(1);

        return $this->db->get_one($this->translations_files_table)['total_result'] > 0;
	}

	function get_translation_file($id_key = 0){
        $this->db->where('id_key', $id_key);
        $this->db->limit(1);

        return $this->db->get_one($this->translations_files_table);
	}

	function get_translation_files($conditions = array()){
        $order_by = "id_key ASC";
        // $file_type = "php";

		extract($conditions);

		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($translation_file)){
			$where[] = " file_name = ? ";
			$params[] = $translation_file;
        }

		if(isset($translation_key)){
			$where[] = " translation_key = ? ";
			$params[] = $translation_key;
		}

		if(isset($translation_updated_from)){
			$where[] = " translation_text_updated_at > ? ";
			$params[] = $translation_updated_from;
        }

		if(isset($translation_updated_to)){
			$where[] = " translation_text_updated_at < ? ";
			$params[] = $translation_updated_to;
        }

        if(isset($file_type)){
            $where[] = " file_type = ? ";
            $params[] = $file_type;
        }

        if(isset($module)) {
            $where[] = " id_key IN (
                SELECT id_key
                FROM {$this->keys_pages_relation_table} KPR
                LEFT JOIN {$this->pages_modules_relation_table} PRM ON PRM.id_page = KPR.id_page
                WHERE id_module = ?
            ) ";
            $params[] = $module;
        }

        if(isset($page)) {
            $where[] = " id_key IN (SELECT id_key FROM {$this->keys_pages_relation_table} WHERE id_page = ?) ";
            $params[] = $page;
        }

        if(isset($tag)) {
            $where[] = " id_key IN (SELECT id_translation FROM `translation_tag_relation` WHERE id_tag = ?) ";
            $params[] = $tag;
        }

        if(!empty($with_lang)) {
            $where[] = "
                (
                    JSON_CONTAINS_PATH(translation_localizations, 'all', ?, ?, ?) AND
                    translation_localizations->>? IS NOT NULL AND
                    translation_localizations->>? != ''
                )
            ";

            array_push(
                $params,
                ...[
                    "$.{$with_lang}",
                    "$.{$with_lang}.text",
                    "$.{$with_lang}.text.value",
                    "$.{$with_lang}.text.value",
                    "$.{$with_lang}.text.value"
                ]
            );
        }

        if(!empty($without_lang)) {
            $where[] = "
                (
                    translation_localizations IS NULL OR
                    NOT JSON_CONTAINS_PATH(translation_localizations, 'all', ?, ?, ?) OR
                    translation_localizations->>? IS NULL OR
                    translation_localizations->>? = ''
                )
            ";

            array_push(
                $params,
                ...[
                    "$.{$without_lang}",
                    "$.{$without_lang}.text",
                    "$.{$without_lang}.text.value",
                    "$.{$without_lang}.text.value",
                    "$.{$without_lang}.text.value"
                ]
            );
        }

        if(!empty($need_review_lang)) {
            $where[] = "
                (
                    translation_localizations IS NULL OR
                    NOT JSON_CONTAINS_PATH(translation_localizations, 'all', ?, ?, ?) OR
                    translation_localizations->>? IS NULL OR
                    translation_localizations->>? = '' OR
                    translation_localizations->>? <= translation_text_updated_at
                )
            ";

            array_push(
                $params,
                ...[
                    "$.{$need_review_lang}",
                    "$.{$need_review_lang}.text",
                    "$.{$need_review_lang}.text.value",
                    "$.{$need_review_lang}.text.value",
                    "$.{$need_review_lang}.text.value",
                    "$.{$need_review_lang}.text.updated_at",
                ]
            );
        }

        if(isset($page_url)) {
            $where[] = " id_key IN (
                SELECT R.id_key
                FROM {$this->keys_pages_relation_table} R
                LEFT JOIN {$this->pages_table} P ON R.id_page = P.id_page
                WHERE ? REGEXP P.page_url_pattern = 1
            ) ";

            $params[] = $page_url;
        }

		if(isset($keywords)){
			$where[] = " (translation_key LIKE ? OR translation_text LIKE ?) ";
            array_push($params, ...['%' . $keywords . '%', '%' . $keywords . '%']);
        }

        if(isset($is_systmess)){
			$where[] = " is_systmess = ? ";
			$params[] = $is_systmess;
        }

        if(isset($is_reviewed)){
			$where[] = " is_reviewed = ? ";
			$params[] = $is_reviewed;
		}

		$sql = "SELECT * FROM {$this->translations_files_table}";

		if(!empty($where)){
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$sql .= " ORDER BY {$order_by}";

		if(isset($limit, $start)){
            $start = (int) $start;
            $limit = (int) $limit;
			$sql .= " LIMIT {$start},  {$limit}";
        }

		return $this->db->query_all($sql, $params);
	}

	function count_translation_files($conditions = array()){
        extract($conditions);

		$where = $params = [];

		if(isset($translation_file)){
			$where[] = ' file_name = ? ';
			$params[] = $translation_file;
		}

		if(isset($translation_key)){
			$where[] = ' translation_key = ? ';
			$params[] = $translation_key;
        }

        if(isset($is_systmess)){
			$where[] = " is_systmess = ? ";
			$params[] = $is_systmess;
        }

        if(isset($is_reviewed)){
			$where[] = " is_reviewed = ? ";
			$params[] = $is_reviewed;
		}

        if(isset($file_type)){
            $where[] = ' file_type = ? ';
            $params[] = $file_type;
        }

        if(isset($module)) {
            $where[] = " id_key IN (
                SELECT id_key
                FROM {$this->keys_pages_relation_table} KPR
                LEFT JOIN {$this->pages_modules_relation_table} PRM ON PRM.id_page = KPR.id_page
                WHERE id_module = ?
            ) ";
            $params[] = $module;
        }

        if(isset($page)) {
            $where[] = " id_key IN (SELECT id_key FROM {$this->keys_pages_relation_table} WHERE id_page = ?) ";
            $params[] = $page;
        }

        if(isset($tag)) {
            $where[] = " id_key IN (SELECT id_translation FROM `translation_tag_relation` WHERE id_tag = ?) ";
            $params[] = $tag;
        }

        if(!empty($with_lang)) {
            $where[] = "
                (
                    JSON_CONTAINS_PATH(translation_localizations, 'all', ?, ?, ?) AND
                    JSON_TYPE(translation_localizations->?) != 'NULL' AND
                    translation_localizations->? != ''
                )
            ";

            array_push(
                $params,
                ...[
                    "$.{$with_lang}",
                    "$.{$with_lang}.text",
                    "$.{$with_lang}.text.value",
                    "$.{$with_lang}.text.value",
                    "$.{$with_lang}.text.value",
                ]
            );
        }

        if(!empty($without_lang)) {
            $where[] = "
                (
                    translation_localizations IS NULL OR
                    NOT JSON_CONTAINS_PATH(translation_localizations, 'all', ?, ?, ?) OR
                    JSON_TYPE(translation_localizations->?) = 'NULL' OR
                    translation_localizations->? = ''
                )
            ";

            array_push(
                $params,
                ...[
                    "$.{$without_lang}",
                    "$.{$without_lang}.text",
                    "$.{$without_lang}.text.value",
                    "$.{$without_lang}.text.value",
                    "$.{$without_lang}.text.value",
                ]
            );
        }

        if(isset($page_url)) {
            $where[] = " id_key IN (
                SELECT R.id_key
                FROM {$this->keys_pages_relation_table} R
                LEFT JOIN {$this->pages_table} P ON R.id_page = P.id_page
                WHERE ? REGEXP P.page_url_pattern = 1
            ) ";

            $params[] = $page_url;
        }

		if(isset($keywords)){
			$where[] = " (translation_key LIKE ? OR lang_en LIKE ?) ";
            array_push($params, ...['%' . $keywords . '%', '%' . $keywords . '%']);
		}

		$sql = "SELECT COUNT(*) as counter FROM {$this->translations_files_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	function get_files_translations($type = ""){
		$where = "";
        $params = [];
		if (!empty($type) && in_array($type, array('php', 'js'))) {
            $where = "WHERE `file_type` = ?";
            $params[] = $type;
		}

        $sql = "SELECT `file_name`
        		FROM {$this->translations_files_table}
        		{$where}
        		GROUP BY `file_name`";
        $translations_files = $this->db->query_all($sql, $params);

        return array_column($translations_files, 'file_name');
	}

	function save_lang_file($lang, $file_name, $data = array()){
		$file_path = $this->lang_folder.'/'.$lang;
		create_dir($file_path);

		$file_path .= '/'.$file_name;
		$f = fopen($file_path, "w");
		fwrite($f, '<?php '."\r\n");
		foreach($data as $lang_key => $lang_value){
			fwrite($f, '$lang[\''.$lang_key.'\'] = \''.$lang_value.'\';'."\r\n");
		}
		fclose($f);
    }

    /**
     * Returns relationship records between translation keys and pages.
     * Query can be specified by providing an array with parameters
     *
     * @param int|int[] $keys
     * @param array $params
     *
     * @return array
     */
    public function get_related_pages($keys, array $params = array())
    {
        $columns = "*";
        $with = array();
        $conditions = array();
        $order = array();
        $group = array();
        $limit = null;
        $skip = null;
        $keys = arrayable_to_array($keys);
        if(empty($keys)) {
            return array();
        }

        extract($params);
        extract($with, EXTR_PREFIX_ALL, 'with');
        extract($conditions, EXTR_PREFIX_ALL, 'condition');
        if(!empty($columns)) {
            $columns = is_string($columns) ? $columns : implode(', ', $columns);
        } else {
            $columns = '*';
        }

        $this->db->select($columns);
        $this->db->from("{$this->keys_pages_relation_table} kpr");

        // Resolve joins
        if(!empty($with)) {
            if(isset($with_keys) && $with_keys) {
                $this->db->join("{$this->translations_files_table} k", "k.id_key = kpr.id_key", 'left');
            }
            if(isset($with_pages) && $with_pages) {
                $this->db->join("{$this->pages_table} p", "p.id_page = kpr.id_page", 'left');
            }
            if(isset($with_modules) && $with_modules) {
                $this->db->join("{$this->pages_modules_relation_table} pmr", "pmr.id_page = kpr.id_page", 'left');
                $this->db->join("{$this->modules_table} m", "m.id_module = pmr.id_module", 'left');
            }
        }

        //Resolve conditions
        $this->db->in("kpr.id_key", $keys);
        if(!empty($conditions)) {
            // Here be dragons
        }

        // Resolve group by
        foreach ($group as $column) {
            $this->db->groupby($column);
        }

        // Resolve order by
        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $this->db->orderby("{$column} {$direction}");
            } else {
                $this->db->orderby($column);
            }
        }

        // Resolve limit
        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }

        // Fetch data
        if (!$this->db->query()) {
            return array();
        }
        $data = $this->db->getQueryResult()->fetchAllAssociative();

        return $data ? $data : array();
    }

    public function create_pages_relationship($key_id, $pages)
    {
        if (empty($pages)) {
            return false;
        }

        $insert_data = array();
        $pages = arrayable_to_array($pages);
        foreach ($pages as $page) {
            $insert_data[] = array(
                'id_key'  => $key_id,
                'id_page' => (int) $page,
            );
        }
        $insert_data = array_filter($insert_data);

        return $this->db->insert_batch($this->keys_pages_relation_table, $insert_data);
    }

    public function createTagsRelationship($keyId, $tags)
    {
        if (empty($tags)) {
            return false;
        }

        $insertData = [];
        $tags = arrayable_to_array($tags);
        foreach ($tags as $tag) {
            $insertData[] = [
                'id_translation'  => $keyId,
                'id_tag'          => (int) $tag,
            ];
        }
        $insertData = array_filter($insertData);

        $this->db->insert_batch('translation_tag_relation', $insertData);

        return true;
    }

    public function create_pages_relationship_multiple_keys($key_ids, $pages)
    {
        if (empty($pages)) {
            return false;
        }

        $insert_data = array();
        $pages = arrayable_to_array($pages);
        foreach ($pages as $page) {
            foreach($key_ids as $key_id){
                $insert_data[] = array(
                    'id_key'  => $key_id,
                    'id_page' => (int) $page,
                );
            }

        }

        return $this->db->insert_batch($this->keys_pages_relation_table,  array_filter($insert_data));
    }

    public function get_translations_by_keys($keys)
    {
        $this->db->select('id_key, translation_key, translation_text');
        $this->db->from($this->translations_files_table);
        $this->db->in("translation_key", $keys);
        return $this->db->get();
    }

    public function remove_pages_relationship($key_id, $pages = null)
    {
        $this->db->where('id_key = ?', (int) $key_id);
        $pages = null !== $pages ? arrayable_to_array($pages) : $pages;
        if (!empty($pages)) {
            $this->db->in('id_page', $pages);
        }

        return $this->db->delete($this->keys_pages_relation_table);
    }

    public function removeTagsRelationship($key_id, $tags = null)
    {
        $this->db->where('id_translation = ?', (int) $key_id);
        $tags = null !== $tags ? arrayable_to_array($tags) : $tags;
        if (!empty($tags)) {
            $this->db->in('id_tag', $tags);
        }

        return $this->db->delete('translation_tag_relation');
    }

    public function replace_pages_relationship($key_id, $pages)
    {
        $removed = $this->remove_pages_relationship($key_id);
        if (empty($pages)) {
            return $removed;
        }

        return $removed && $this->create_pages_relationship($key_id, $pages);
    }

    public function replaceTagsRelationship($keyId, $tags)
    {
        $removed = $this->removeTagsRelationship($keyId);
        if (empty($tags)) {
            return $removed;
        }
        return $removed && $this->createTagsRelationship($keyId, $tags);
    }

    public function morph_translation_data_format($languages)
    {
        $languages = arrayByKey($languages, 'lang_iso2');
        $database = $this->db->query_one("SELECT DATABASE() as name");
        $columns = array_column($this->db->query_all(
            "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND COLUMN_NAME LIKE 'lang_%';
            AND CHAR_LENGTH(COLUMN_NAME) = 7",
            array(
                $database['name'],
                $this->translations_files_table
            )
        ), 'COLUMN_NAME');
        $column_languages = array_combine(array_map(function($lang_name) {
            return str_replace("lang_", '', $lang_name);
        }, $columns), $columns);

        $query_components = array();
        foreach ($column_languages as $lang_code => $column) {
            if(!isset($languages[$lang_code]) || 'en' === $lang_code) {
                continue;
            }

            $now = new \DateTime();
            $created_at = $updated_at = $now->format('Y-m-d H:i:s');
            $lang = $languages[$lang_code];
            $query_components[] = "
                '{$lang_code}', JSON_OBJECT(
                    'text',
                    JSON_OBJECT('value', IF({$column} = '', NULL, {$column}), 'created_at', IF({$column} = '', NULL, '{$created_at}'), 'updated_at', IF({$column} = '', NULL, '{$updated_at}')),
                    'lang',
                    JSON_OBJECT('id', {$lang['id_lang']}, 'lang_name', '{$lang['lang_name']}', 'abbr_iso2', '{$lang['lang_iso2']}')
                )";
        }
        $query_components = implode(",\n", $query_components);

        return $this->db->query(
            "UPDATE {$this->translations_files_table}
            SET translation_text = lang_en, translation_localizations = JSON_OBJECT({$query_components})"
        );
    }

    public function translation_file_column_exists($column_name)
    {
        $database = $this->db->query_one("SELECT DATABASE() as name");
        $columns = $this->db->query_one(
            "SELECT COUNT(*) AS AGGREGATE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?",
            array(
                $database['name'],
                $this->translations_files_table,
                $column_name
            )
        );

        return !isset($columns['AGGREGATE']) ? $columns['AGGREGATE'] : (bool) ((int) $columns['AGGREGATE']);
    }

    public function update_translation_file_lang_entry($id, $translation, $lang_code, array $localization)
    {
        $now = new \DateTime();
        $localization = json_encode($localization, JSON_UNESCAPED_UNICODE);
        $query = "UPDATE {$this->translations_files_table} SET
            lang_{$lang_code} = ?,
            translation_localizations = CASE
                WHEN IFNULL(JSON_TYPE(translation_localizations), 'NULL') = 'NULL'
                THEN '{\"{$lang_code}\": {$localization}}'
                ELSE JSON_MERGE_PATCH(
                    translation_localizations,
                    '{\"{$lang_code}\": {$localization}}'
                )
                END
            WHERE id_key = ?
        ";

        $value = $translation;
        $params = array(
            $translation,
            $localization,
            $id
        );

        return $this->db->query($query, $params);
    }

    public function update_translation_file_lang_entry_by_key($key, $translation, $lang_code, array $localization)
    {
        $now = new \DateTime();
        $localization = json_encode($localization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $params = array($localization, $key);
        $set = array(
            "translation_localizations = JSON_SET(COALESCE(IF(JSON_TYPE(translation_localizations) = 'NULL', NULL, translation_localizations), JSON_OBJECT()), '$.{$lang_code}', JSON_EXTRACT(?, '$'))"
        );
        if($this->translation_file_column_exists("lang_{$lang_code}")) {
            array_unshift($set, "lang_{$lang_code} = ?");
            array_unshift($params, $translation);
        }
        $set = implode(', ', $set);

        return $this->db->query("UPDATE {$this->translations_files_table} SET {$set} WHERE translation_key = ?", $params);
    }

    public function get_page_data($controller, $action, array $additional = array()){
        if (empty($controller) || empty($action)) {
            return false;
        }

        $this->db->from($this->pages_table);
        $this->db->where('page_controller', $controller);
        $this->db->where('page_action', $action);

        if (!empty($additional)) {
            foreach ($additional as $column => $value) {
                $this->db->where($column, $value);
            }
        }

        return $this->db->query_one();
    }

    public function insertTranslationsKeysUsageLog(array $records) {
        return empty($records) ? false : $this->db->insert_batch($this->translationsKeysUsageLogTable, $records);
    }

    public function removeDuplicatesFromTranslationsKeysUsageLog() {
        return $this->db->query(<<<QUERY
            DELETE FROM {$this->translationsKeysUsageLogTable}
            WHERE `id` NOT IN (
                SELECT *
                FROM (
                    SELECT MIN(`id`)
                    FROM {$this->translationsKeysUsageLogTable}
                    GROUP BY `translation_key`,
                            `controller`,
                            `action`
                ) AS subQueryAlias
            )
            QUERY
        );
    }

    /**
     * Get all tags if not set array with ids
     *
     * @param array $tagsIds - the ids of the tags to filter
     *
     * @return array - all tags or filtered
     */
    public function getTags($tagsIds = [], $orderBy = '')
    {
        $this->db->select('*');
        $this->db->from('translation_tags');
        if(!empty($tagsIds)){
            $this->db->in('id', $tagsIds);
        }

        if(!empty($orderBy)){
            $this->db->orderby($orderBy);
        }
        return $this->db->query_all();
    }

    public function getSelectedTags($idTranslation)
    {
        $this->db->select('id_translation, id_tag, name');
        $this->db->from('translation_tags t');
        $this->db->join('translation_tag_relation tr', 'tr.id_tag = t.id', 'left');
        $this->db->where('tr.id_translation', $idTranslation);

        return $this->db->query_all();
    }
}
