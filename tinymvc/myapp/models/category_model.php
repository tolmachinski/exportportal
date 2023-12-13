<?php
/**
 * category_model.php
 * category system model
 * @author Litra Andrei
 */

/**
 * @deprecated in favor of the \Item_Category_Model
 */
class Category_Model extends TinyMVC_Model {

	public $category_table = 'item_category';
	public $category_table_primary_key = 'category_id';
	public $item_category_i18n_table = 'item_category_i18n';
	public $category_sync_table = 'item_category_sync';
	public $featured_cat_array = array();

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);
    }

	public function _categories_map(array $categories){
        $categories = arrayByKey($categories, 'category_id');

		$categoriesTree = array();
		foreach($categories as $categoryId => &$category){
			if((int) $category['parent'] === 0) {
				$categoriesTree[$categoryId] = &$category;
            } else {
				$categories[$category['parent']]['subcats'][$categoryId] = &$category;
            }
        }

		return $categoriesTree;
	}

	/**
	 * Returns the categories table name
	 */
	public function get_categories_table(): string
	{
		return $this->category_table;
	}

	/**
	 * Returns the categories table primary key
	 */
	public function get_categories_table_primary_key(): string
	{
		return $this->category_table_primary_key;
	}

	function validate_category_id($category_id) {
		$category_id = (int)$category_id;
		$sql = "SELECT COUNT(*) as exist_cat
				FROM item_category
				WHERE category_id = ?";
		$rez = $this->db->query_one($sql, array($category_id));
		return $rez['exist_cat'];
	}

	function fetch_country_by_category($conditions){
		$where = array();
		$params = array();
		$status = " 1, 2, 3 ";
		extract($conditions);

		if(isset($blocked)){
            $where[] = " it.blocked = ? ";
            $params[] = $blocked;
        }

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(isset($category)){
			$categories = $this->get_cat_childrens($category) . $category;
            $categories = getArrayFromString($categories);
			$where[] = " id_cat IN (" . implode(',', array_fill(0, count($categories), '?')) . ")";
			array_push($params, ...$categories);
		} elseif(isset($categories_list)) {
            $categories_list = getArrayFromString($categories_list);
			$where[] = " id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ")";
			array_push($params, ...$categories_list);
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($price_from)){
			$where[] = " it.final_price >= ? ";
			$params[] = $price_from;
		}

		if(isset($price_to)){
			$where[] = " it.final_price <= ? ";
			$params[] = $price_to;
		}

		if(isset($year_from)){
			$where[] = " it.year >= ?";
			$params[] = $year_from;
		}

		if(isset($year_to)){
			$where[] = " it.year <= ?";
			$params[] = $year_to;
		}

		if(isset($visible)){
			$where[] = " it.visible = ?";
			$params[] = $visible;
		}

		if(isset($keywords)){
			$where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
			$params[] = $keywords;
		}

		if(isset($attrs)){
			foreach($attrs as $attr => $vals){
				$vals = getArrayFromString($vals);

				$where[] = " (
					SELECT ia.attr_value
					FROM item_attr ia
					WHERE ia.item = it.id AND ia.attr = ?
					GROUP BY ia.item
				) IN (" . implode(', ', array_fill(0, count($vals), '?')) . ")";

                $params[] = $attr;
                array_push($params, ...$vals);
			}
		}

		$sql = "SELECT p_country as loc_id, COUNT(p_country) AS loc_count, c.country as loc_name,  'country' as loc_type
				FROM items it
				INNER JOIN port_country c ON c.id = it.p_country
				LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company' ";

		if(count($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$sql .= " GROUP BY loc_id";

		return $this->db->query_all($sql, $params);
	}

	function fetch_city_by_category(array $conditions){
		$where = array();
		$params = array();
		$status = " 1, 2, 3 ";
		$visible = 1;
		extract($conditions);

		$where[] = " it.visible = ? ";
		$params[] = $visible;

		if(isset($blocked)){
			$where[] = " it.blocked = ? ";
			$params[] = $blocked;
        }

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(isset($category)){
			$categories = $this->get_cat_childrens($category) . $category;
            $categories = getArrayFromString($categories);
			$where[] = " id_cat IN (" . implode(',', array_fill(0, count($categories), '?')) . ")";
            array_push($params, ...$categories);
		} elseif(isset($categories_list)) {
            $categories_list = getArrayFromString($categories_list);
			$where[] = " id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ")";
            array_push($params, ...$categories_list);
		}

		if(isset($country)){
			$where[] = " it.p_country = ? ";
			$params[] = $country;
		}

		if(isset($price_from)){
			$where[] = " it.final_price >= ? ";
			$params[] = $price_from;
		}

		if(isset($price_to)){
			$where[] = " it.final_price <= ? ";
			$params[] = $price_to;
		}

		if(isset($year_from)){
			$where[] = " it.year >= ?";
			$params[] = $year_from;
		}

		if(isset($year_to)){
			$where[] = " it.year <= ?";
			$params[] = $year_to;
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($keywords)){
			$where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
			$params[] = $keywords;
		}

		if(isset($attrs)){
			foreach($attrs as $attr => $vals){
				$vals = getArrayFromString($vals);

				$where[] = " (
					SELECT ia.attr_value
						FROM item_attr ia
						WHERE ia.item = it.id
						AND ia.attr = ?
						GROUP BY ia.item
						) IN (" . implode(', ', array_fill(0, count($vals), '?')) . ")";

                $params[] = $attr;
                array_push($params, ...$vals);
			}
		}

		$sql = "SELECT it.p_city as loc_id, COUNT(it.p_city) AS loc_count, 'city' as loc_type, z.city, s.state, CONCAT(z.city, ', ', s.state) as loc_name
				FROM items it
				LEFT JOIN port_country c ON c.id = it.p_country
				LEFT JOIN states s ON it.state = s.id
				LEFT JOIN zips z ON it.p_city = z.id
				LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company' ";

		if(count($where)) {
			$sql .= " WHERE " . implode(' AND ', $where);
        }

		$sql .= " GROUP BY loc_id";

		return $this->db->query_all($sql, $params);
	}

	function count_search_last_categories($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($children_no)){
			$where[] = " cat_childrens = ''";
		}

		if(!empty($search)){
			if(strlen($search) > 3){
				$where[] = " MATCH (ic.name, ic.keywords) AGAINST (?)";
				$params[] = $search;
			} else{
				$where[] = " ( ic.name LIKE ? OR ic.keywords LIKE ? ) ";
                array_push($params, ...array_fill(0, 2, '%' . $search . '%'));
			}
		}

		$sql = "SELECT count(*) as counter
				FROM item_category ic ";

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	function search_last_categories($conditions){
		$order_by = "ic.name ASC";
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($children_no)){
			$where[] = " cat_childrens = ''";
		}

		$rel = "";
		if(!empty($search)){
			if(strlen($search) > 3){
				$order_by = " REL DESC, ".$order_by;
				$where[] = " MATCH (ic.name, ic.keywords) AGAINST (?)";
				$params[] = $search;
				$rel = " , MATCH (ic.name, ic.keywords) AGAINST (?) as REL ";
                array_unshift($params, $search);
			} else{
				$order_by = " ic.parent ASC, ".$order_by;
				$where[] = " ( ic.name LIKE ? OR ic.keywords LIKE ? ) ";
                array_push($params, ...array_fill(0, 2, '%' . $search . '%'));
			}
		}

		$sql = "SELECT * " . $rel. "
				FROM item_category ic ";

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		if (isset($limit)) {
			$sql .= " LIMIT " . $limit;
        }

		return $this->db->query_all($sql, $params);
	}

	private function get_categories_params($conditions = array()){
		$this->db->from("item_category");

		extract($conditions);

		if (isset($category) || isset($cat_list)) {
			$categories = array();
			if(isset($category)){
				$categories = $this->get_cat_childrens($category);
				$categories = explode(',', $categories);
				$categories[] = $category;
			} else {
				if (!empty($cat_list) && is_string($cat_list)) {
					$cat_list = explode(',', $cat_list);
				}

				$categories = is_array($cat_list) ? $cat_list : array_filter((array) $cat_list);
			}

            $categories = array_map('intval', $categories);
			$categories = array_filter($categories);

			if(empty($categories)){
				$categories = array(0);
			}

			$this->db->in("category_id", $categories);
		}

		if(isset($category_cond)){
			$this->db->where_raw("category_id {$category_cond}");
		}

		if(isset($parent)){
			if(!is_array($parent)){
				$parent = explode(',', $parent);
			}

			$parent = array_map('intval', $parent);

			$this->db->in("parent", $parent);
		}

		if(isset($categories_only)){
			$this->db->where("parent > ?", 0);
		}

		if(isset($industries_only)){
			$this->db->where("parent = ?", 0);
		}

        if (isset($item_articles_only)) {
            $this->db->join('item_category_articles ica', 'item_category.category_id = ica.id_category', 'left');
            $this->db->where_raw('ica.id_category IS NULL');
        }

		if(!empty($search)){
			if(str_word_count_utf8($search) > 1){
				$this->db->where_raw("MATCH (name, keywords) AGAINST (?)", $search);
			} else{
				$this->db->where_raw("(name LIKE ? OR keywords LIKE ?)", array_fill(0, 2, "%{$search}%"));
			}
		}

		if(isset($cat_type)){
			$this->db->where("cat_type = ?", $cat_type);
		}

		if(isset($vin)){
			$this->db->where("vin = ?", $vin);
		}

		if(isset($p_or_m)){
			$this->db->where("p_or_m = ?", $p_or_m);
		}

		if(isset($actualized)){
			$this->db->where("actualized = ?", $actualized);
		}
	}

	function getCategories($conditions = array()){
		$columns = '*';
		$order_by = "name ASC";

		extract($conditions);

		$this->db->select("{$columns}");

		$this->get_categories_params($conditions);

		if(isset($conditions['sort_by'])){
			foreach($conditions['sort_by'] as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			if (!empty($multi_order_by)) {
				$order_by = implode(',', $multi_order_by);
			}
		}

		if($order_by !== false) {
			$this->db->orderby($order_by);
		}

		if(isset($limit)) {
			$this->db->limit($limit);
		} elseif(isset($start) && isset($per_p)) {
			$this->db->limit($per_p, $start);
		}

        return $this->db->query_all();
	}

	function getCategoriesCounter($conditions = array()){
		$this->db->select("COUNT(*) as total_records");

		$this->get_categories_params($conditions);

        $record = $this->db->get_one();

		return (int) $record['total_records'];
	}

	function get_subcategories($parent = 0, $cat_type = false){
		$where = " WHERE parent = ?";
        $params = [$parent];

		if(false !== $cat_type){
			$where .= " AND cat_type = ?";
            $params[] = $cat_type;
		}
		$sql = "SELECT * FROM item_category $where ORDER BY name";
		$categories = $this->db->query_all($sql, $params);

        if($this->db->numRows() > 0) {
            return $categories;
        }

        return null;
	}

	function get_makes($cat){
		$list = $this->get_cat_parents($cat) . $cat;
        $list = getArrayFromString($list);

		$sql = "SELECT category_id, name
				FROM item_category
				WHERE parent IN (" . implode(',', array_fill(0, count($list), '?')) .")
				AND cat_type = 1
				ORDER BY name";
		return $this->db->query_all($sql, $list);
	}

	function get_subcat_counter($conditions){
		$where = array();
		$params = array();
		$status = " 1,2,3 ";
		$category = 0;
		extract($conditions);

		if(isset($blocked)){
			$where[] = " it.blocked = ? ";
			$params[] = $blocked;
        }

		if(isset($accreditation)){
            $where[] = " cb.accreditation = ? ";
            $params[] = $accreditation;
        }

		if(isset($categories_list)){
            $categories_list = getArrayFromString($categories_list);
			$where[] = " id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ")";
            array_push($params, ...$categories_list);
		} elseif(isset($category)) {
			$categories = $this->get_cat_childrens($category)  . $category;
            $categories = getArrayFromString($categories);
			$where[] = " id_cat IN (" . implode(',', array_fill(0, count($categories), '?')) . ")";
            array_push($params, ...$categories);
		}

		if(isset($country)){
			$where[] = " it.p_country = ? ";
			$params[] = $country;
		}

		if(isset($city)){
			$where[] = " it.p_city = ? ";
			$params[] = $city;
		}

		if(isset($price_from)){
			$where[] = " it.final_price >= ? ";
			$params[] = $price_from;
		}

		if(isset($price_to)){
			$where[] = " it.final_price <= ? ";
			$params[] = $price_to;
		}

		if(isset($year_from)){
			$where[] = " it.year >= ?";
			$params[] = $year_from;
		}

		if(isset($year_to)){
			$where[] = " it.year <= ?";
			$params[] = $year_to;
		}
		if(isset($seller)){
			$where[] = " it.id_seller = ?";
			$params[] = $seller;
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($visible)){
			$where[] = " it.visible = ?";
			$params[] = $visible;
		}

		//if(isset($keywords)){
		//	$where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
		//	$params[] = $keywords;
		//}

        if(! empty($items_list_elasticsearch) && is_array($items_list_elasticsearch)) {
            $items_list_elasticsearch = getArrayFromString($items_list_elasticsearch);
            $where[] = " it.id in (" . implode(',', array_fill(0, count($items_list_elasticsearch), '?')) . ")";
            array_push($params, ...$items_list_elasticsearch);
        }

		if(isset($attrs)){
			foreach($attrs as $attr => $vals){
                $vals = getArrayFromString($vals);

				$where[] = " (
					SELECT ia.attr_value
						FROM item_attr ia
						WHERE ia.item = it.id
						AND ia.attr = ?
						GROUP BY ia.item
						) IN (" . implode(', ', array_fill(0, count($vals), '?')) . ")";

                $params[] = $attr;
                array_push($params, ...$vals);
			}
		}

		$sql = "SELECT ic.category_id, ic.name, ic.p_or_m, ic.cat_type, COUNT(it.id) as counter
				FROM item_category ic
				INNER JOIN items it ON FIND_IN_SET(it.id_cat, CONCAT_WS(',',ic.cat_childrens, ic.category_id))
				LEFT JOIN company_base cb ON cb.id_user= it.id_seller AND cb.type_company = 'company'
				WHERE ic.parent = ?";

        array_unshift($params, (int) $category);

		if(count($where)) {
			$sql .= ' AND ' . implode(' AND ', $where);
        }

		$sql .= " GROUP BY ic.category_id";
		$sql .= " ORDER BY ic.name";

		return $this->db->query_all($sql, $params);
	}

	function get_cat_tree($conditions){
		$where = array();
		$params = array();
		$tree = true;
		$status = " 1,2,3 ";
		extract($conditions);

		if(isset($keywords)){
			$where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
			$params[] = $keywords;
		}

		if(isset($country)){
			$where[] = " it.p_country = ? ";
			$params[] = $country;
		}

		if(isset($seller)){
			$where[] = " it.id_seller = ? ";
			$params[] = $seller;
		}

		if(isset($featured)){
			$where[] = " it.featured = ? ";
			$params[] = $featured;
		}

		if(isset($highlight)){
			$where[] = " it.highlight = ? ";
			$params[] = $highlight;
		}

		if(isset($parent)){
			$where[] = " c.parent = ? ";
			$params[] = $parent;
		}

		if(isset($visible)){
			$where[] = " it.visible = ?";
			$params[] = $visible;
		}

		if(isset($blocked)){
			$where[] = " it.blocked = ?";
			$params[] = $blocked;
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		$sql .= "SELECT c.category_id, c.parent, c.name, c.cat_childrens, c.breadcrumbs,  COUNT(c.category_id) as counter
				FROM item_category c
				INNER JOIN items it
					ON FIND_IN_SET(it.id_cat, c.cat_childrens)
						OR  it.id_cat = c.category_id";

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		$sql .= " GROUP BY c.category_id";

		$rez = $this->db->query_all($sql, $params);

        return $tree ? $this->_categories_map($rez) : $rez;
	}

	function get_category($id, $columns = "*"){
		$sql = "SELECT $columns
				FROM item_category
				WHERE category_id = ?";

		return $this->db->query_one($sql, array($id));
	}

	function get_category_i18n($conditions = array()){
		if(empty($conditions)){
			return false;
		}

		$where = array();
		$params = array();

		extract($conditions);

		if(isset($id_category_i18n)){
			$where[] = ' c_i18n.category_id_i18n = ? ';
			$params[] = $id_category_i18n;
		}

		if(isset($id_category)){
			$where[] = ' c_i18n.category_id = ? ';
			$params[] = $id_category;
		}

		if(isset($lang_category)){
			$where[] = ' c_i18n.category_lang = ? ';
			$params[] = $lang_category;
		}

		$sql = "SELECT c_i18n.*, c.breadcrumbs, c.translations_data
				FROM {$this->item_category_i18n_table} c_i18n
				LEFT JOIN {$this->category_table} c ON c_i18n.category_id = c.category_id
				WHERE " . implode(' AND ', $where);

		return $this->db->query_one($sql, $params);
	}

	function exist_category($id){
		$sql = 'SELECT COUNT(*) as counter
				FROM item_category
				WHERE category_id = ?';
		$rez = $this->db->query_one($sql, array($id));
		return $rez['counter'];
	}

	function get_category_by_link($link){
		$sql = "SELECT category_id
				FROM item_category
				WHERE link = ?";
		$category = $this->db->query_one($sql, array($link));

        return $this->db->numRows() > 0 ? $category['category_id'] : null;
	}

	function set_category($data){
		$this->db->insert('item_category', $data);
		return $this->db->last_insert_id();
	}

	public function set_category_i18n($data){
		$this->db->insert($this->item_category_i18n_table, $data);
		return $this->db->last_insert_id();
	}

	function update_category($data){
		$this->db->where('category_id', $data['category_id']);
		$this->db->update('item_category', $data);
		return true;
	}

	public function update_category_i18n($id_category_i18n = 0, $data = array()){
		if(empty($data)){
			return false;
		}

		$this->db->where('category_id_i18n', $id_category_i18n);
		return $this->db->update($this->item_category_i18n_table, $data);
	}

	function update_categories($data){
		$this->db->update('item_category', $data );
	}

	function simple_update_category($id, $data){
		$this->db->where('category_id', $id);
		$this->db->update('item_category', $data );
		return true;
	}

	public function get_industries($conditions = array())
	{
		$order_by = '`CATEGORIES`.`name`';

		extract($conditions);

		$this->db->select("`CATEGORIES`.*, iF('' != `CATEGORIES`.`cat_childrens`, 1, 0) AS `has_children`");
		$this->db->from("`{$this->category_table}` AS `CATEGORIES`");
		$this->db->where("`CATEGORIES`.`parent` = ?", 0);
		if (isset($has_children)) {
			if ($has_children) {
				$this->db->where("`CATEGORIES`.`cat_childrens` != ?", '');
			} else {
				$this->db->where("`CATEGORIES`.`cat_childrens` = ?", '');
			}
		}
		$this->db->orderby($order_by);

		return array_filter((array) $this->db->query_all());
	}

	function delete_category($id){
		$category = $this->get_category($id);
		$this->delete_from_parents($id);
		if(strlen($category['cat_childrens'])) {
			$this->delete_categories(array('list_id' => $category['cat_childrens']));
        }

		$this->db->where('category_id', $id);
		return $this->db->delete('item_category');

	}

	function delete_category_i18n($id_category_i18n = 0){
		$this->db->where('category_id_i18n', $id_category_i18n);
		return $this->db->delete($this->item_category_i18n_table);

	}

	function append_children_to_parents($id_list, $parents_list){
        $parents_list = getArrayFromString($parents_list);

		$sql = "UPDATE item_category
			SET cat_childrens =  if(STRCMP(cat_childrens,''), (CONCAT_WS(',', cat_childrens, ?)), ? )
			WHERE category_id IN ( " . implode(',', array_fill(0, count($parents_list), '?')) . " )";

        $params = array_fill(0, 2, "'" . $id_list . "'");
        array_push($params, ...$parents_list);

		$this->db->query($sql, $params);
	}

	function delete_from_parents($id){
		$parents = $this->get_cat_parents($id) . $id ;
        $parents = getArrayFromString($parents);

		$sql = "UPDATE item_category
			SET cat_childrens = (CASE
				WHEN LOCATE(? , cat_childrens) THEN REPLACE(cat_childrens, ?, '')
				WHEN LOCATE(? , cat_childrens) THEN REPLACE(cat_childrens, ?, '')
				ELSE cat_childrens
			END)
			WHERE category_id IN (" . implode(',', array_fill(0, count($parents), '?')) . ")";

        $params = [
            "'" . $id . ",'",
            "'" . $id . ",'",
            "'," . $id . "'",
            "'," . $id . "'",
        ];

        array_push($params, ...$parents);
		$this->db->query($sql, $params);
	}

	function delete_categories($params){
		$queryParams = [];
        extract($params);

		$sql = "DELETE FROM item_category ";

		if(!isset($list_id) && !isset($parent)){
			return false;
		}

		if(isset($list_id)) {
            $list_id = getArrayFromString($list_id);
			$sql .= " WHERE category_id IN (" . implode(',', array_fill(0, count($list_id), '?')) . ")";
            array_push($queryParams, ...$list_id);
		} elseif(isset($parent)) {
			$sql .= " WHERE parent = ?";
            $queryParams[] = $parent;
        }

		return $this->db->query($sql, $queryParams);
	}

	function get_cat_parents($cat){
		$sql = "SELECT parent FROM item_category WHERE category_id = ?";
		$rez = $this->db->query_one($sql, array($cat));

		$parrent = $rez['parent'];
        $parrents = "";
		if($parrent != 0){
			$parrents .= $parrent . ",";
			$parrents .= $this->get_cat_parents($parrent);
		}
		return $parrents;
	}

	function get_cat_feature_price($cat){
		$sql = "SELECT parent, feature_price FROM item_category WHERE category_id = ?";
		$rez = $this->db->query_one($sql, array($cat));

		$parrent = $rez['parent'];
		if($rez['feature_price'] > 0){
			return $rez['feature_price'];
		}

        if($parrent != 0){
            return $this->get_cat_feature_price($parrent);
        }

        return config('item_feature_default_price');
	}

	function get_cat_highlight_price($cat){
		$sql = "SELECT parent, highlight_price FROM item_category WHERE category_id = ?";
		$rez = $this->db->query_one($sql, array($cat));

		$parrent = $rez['parent'];
		if($rez['highlight_price'] > 0){
			return $rez['highlight_price'];
		}

        if($parrent != 0){
            return $this->get_cat_feature_price($parrent);
        }

        return config('item_highlight_default_price');
	}

	function get_cat_childrens($cat){
		$sql = "SELECT cat_childrens
				FROM item_category
				WHERE category_id = ?";
		$rez = $this->db->query_one($sql, array($cat));

        return strlen($rez['cat_childrens']) ? $rez['cat_childrens'] . ", " : null;
	}

	function change_children_data($cat, $data, $children_list = ''){
		if(empty($data)) {
			return true;
        }

		if(empty($children_list)){
			$category = $this->get_category($cat, 'cat_childrens');
			$children_list = $category['cat_childrens'];
		}

		$this->db->in('category_id', $children_list);
		$this->db->update('item_category', $data);
	}

	function replace_cat_breadcrumbs_part($old_breadcrumbs, $new_breadcrumbs, $children_list){
        $params = [$old_breadcrumbs, $new_breadcrumbs, $new_breadcrumbs];
        $children_list = getArrayFromString($children_list);
        array_push($params, ...$children_list);
		$sql = 'UPDATE item_category
				SET breadcrumbs = (if(breadcrumbs != "", REPLACE(breadcrumbs, ?, ?), ?))
				WHERE category_id IN (' . implode(',', array_fill(0, count($children_list), '?')) . ')';

		$this->db->query($sql, $params);
	}

	function breadcrumbs($cat, $prefix = 'category/', $delimiter = '/'){
		$list = $this->get_cat_parents($cat);
		$list = array_reverse(array_diff(explode(',', $list), array(' ','')));
		$list[] = $cat;

		$categories = array_column(
            $this->getCategories(['cat_list' => $list, 'columns' => 'category_id, name, cat_type, parent', 'order_by' => false]),
            null,
            'category_id'
        );

        $crumbs = [];
        foreach ($list as $categoryId) {
            if (empty($category = $categories[$categoryId])) {
                continue;
            }

            if ($category['cat_type'] == 2){
				$link = __SITE_URL . $prefix . strForURL("{$categories[$category['parent']]['name']}-{$category['name']}") . $delimiter . $category['category_id'];
			} else{
				$link = __SITE_URL . $prefix . strForURL($category['name']) . $delimiter . $category['category_id'];
			}

            $crumbs[] = [
                'id_cat'   => $categoryId,
                'title'    => $category['name'],
                'cat_type' => $category['cat_type'],
                'link'     => $link,
            ];
        }

		return $crumbs;
	}

	function breadcrumbs_tpl($cat, $template = '', $delimiter = '/'){
		$list = $this->get_cat_parents($cat);
		$list = array_reverse(array_diff(explode(',', $list), array(' ','')));
        $list[] = $cat;
		$categories = array_column(
            $this->getCategories(['cat_list' => $list, 'columns' => 'category_id, name, cat_type, parent', 'order_by' => false]),
            null,
            'category_id'
        );

        $crumbs = [];
        foreach ($list as $categoryId) {
            if (empty($category = $categories[$categoryId])) {
                continue;
            }

            if ($category['cat_type'] == 2){
				$link = strForURL("{$categories[$category['parent']]['name']}-{$category['name']}") . $delimiter . $category['category_id'];
			} else{
				$link = strForURL($category['name']) . $delimiter . $category['category_id'];
			}

            $crumbs[] = [
                'id_cat'   => $categoryId,
                'title'    => $category['name'],
                'cat_type' => $category['cat_type'],
                'link'     => replace_dynamic_uri($link, $template),
            ];
        }

		return $crumbs;
	}

	function parents_from_breadcrumbs($breadcrumbs, $id_cat = 0, $list = true){
		$parents_dirty = json_decode('[' . $breadcrumbs . ']', true);
		$parents_clean = array();
		foreach ($parents_dirty as $parent) {
			foreach($parent as $id => $name){
				if($id != $id_cat) {
					$parents_clean[] = $id;
                }
			}
		}

        return $list ? implode(',' ,$parents_clean) : $parents_clean;
	}

	function get_cat_names($cat, $type = 'parents', $separator = ' '){
		switch($type){
			case 'parents':
				$list = $this->get_cat_parents($cat) . $cat;
			break;
			case 'childerens':
				$list = $this->get_cat_childrens($cat) . $cat;
			break;
		}
		return $this->get_catnames_from_list($list, $separator);
	}

	function get_catnames_from_list($list,  $separator = ' '){
        $params = [$separator];

        $list = getArrayFromString($list);
        array_push($params, ...$list);

        $sql = "SELECT GROUP_CONCAT(name SEPARATOR ?) as cnames FROM item_category WHERE category_id IN ( " . implode(',', array_fill(0, count($list), '?')) . ")";

		$res = $this->db->query_one($sql, $params);

		return $res['cnames'];
	}

    function get_sub_categories($conditions = array()) {
        $params = [];
        $order_by = "name ASC";
        $group_by = "parent";
        $limit = 10;
		extract($conditions);

        $sql = "SELECT GROUP_CONCAT(category_id) as ids
				FROM {$this->category_table}";

        if (!empty($categories)) {
            $categories = getArrayFromString($categories);
            $sql .= " WHERE parent IN (" . implode(',', array_fill(0, count($categories), '?')) . ") ";
            array_push($params, ...$categories);
        }

        $sql .= " GROUP BY " . $group_by;
        $sql .= " ORDER BY " . $order_by;

        $result = $this->db->query_all($sql, $params);

        $output = $temp_categories = $categories_id = array();
        if (!empty($result)) {
        	foreach ($result as $item) {
        		$temp_items = explode(',', $item['ids']);
        		$temp_items = array_slice($temp_items, 0, $limit);
                $categories_id = array_merge($temp_items, $categories_id);
			}

			$categories_id_list = "'" . implode("','", $categories_id) . "'";
            $temp_categories = $this->getCategories(array('cat_list' => $categories_id_list));

            if (!empty($get_counter)) {
                $categories_id_list = getArrayFromString($categories_id_list);

                $counter_sql = "SELECT c.category_id, COUNT(c.category_id) as counter
					FROM item_category c
					INNER JOIN items it ON FIND_IN_SET(it.id_cat, c.cat_childrens) OR it.id_cat = c.category_id
					WHERE
						c.category_id IN (" . implode(',', array_fill(0, count($categories_id_list), '?')) . ")
					AND
						it.status IN ('1', '2', '3')
					AND
						it.visible = 1
					AND
						it.blocked = 0
					GROUP BY c.category_id";
                $output['counters'] = $this->db->query_all($counter_sql, $categories_id_list);
                $output['counters'] = arrayByKey($output['counters'], 'category_id');
			}

            $output['categories'] = arrayByKey($temp_categories, 'parent', true);
		}

		return $output;
    }

	/** @deprecated version */
	function set_company_category_seo($data){
		$this->db->insert('company_categories_seo', $data);
		return $this->db->last_insert_id();
	}

	/** @deprecated version */
	function delete_company_category_seo($id_category){
		$this->db->where('id_category', $id_category);
		return $this->db->delete('company_categories_seo');
	}

	function update_category_breadcrumbs($id, &$glob, &$categories){
		if(isset($glob[$id])) {
			return;
        }

		if($categories[$id]['parent'] == 0){
			$glob[$id][] = json_encode(array($id => $categories[$id]['name']));
		}elseif(isset($glob[$categories[$id]['parent']])){

			$glob[$id] = $glob[$categories[$id]['parent']];
			$glob[$id][] = json_encode(array($id => $categories[$id]['name']));

		}else{
			if(in_array($categories[$id]['parent'], array_keys($categories))){
				$this->update_category_breadcrumbs($categories[$id]['parent'], $glob, $categories);
				$glob[$id] = $glob[$categories[$id]['parent']];
				$glob[$id][] = json_encode(array($id => $categories[$id]['name']));
			}else{
				$parent = $this->get_category($categories[$id]['parent'], "category_id, breadcrumbs, actualized");
				if(!empty($parent)){
					$glob[$id][] = $parent['breadcrumbs'];
					$glob[$id][] = json_encode(array($id => $categories[$id]['name']));
				}else{
					$this->simple_update_category($id, array('actualized' => 2));
				}
			}
		}
	}

	function update_breadcrumbs_batch($categories){
		foreach($categories as $cat => $crumbs)
			$this->simple_update_category($cat,array('breadcrumbs' => implode(',', $crumbs), 'actualized' => 1));
	}

	function get_cats_children_recursive($cat_list, &$global){
        $cat_list = getArrayFromString($cat_list);
		$sql = "SELECT category_id, parent, breadcrumbs FROM  " . $this->category_table . " WHERE parent IN (" . implode(',', array_fill(0, count($cat_list), '?')) . ")";
		$rez = $this->db->query_all($sql, $cat_list);

		if(count($rez) > 0){
			$new_list = array();
			foreach($rez as $child){
				$new_list[] = $child['category_id'];
				$parents = json_decode("[" . $child['breadcrumbs'] . "]", true);

				foreach($parents as $par){
					foreach($par as $id => $name){
                        if($id == $child['category_id']) {
                            continue;
                        }

						$global[$id][] = $child['category_id'];
					}
				}
			}
			$this->get_cats_children_recursive(implode(',',$new_list), $global);
		}
	}

	function update_children_batch($categories){
		foreach($categories as $cat => $children)
			$this->simple_update_category($cat, array('cat_childrens' => implode(',', $children)));
    }

    //region Sync Categories
    public function categoriesSyncMeta($categories, $parents){
        $tempParents = [];
        foreach ($categories as $category) {
            if(in_array($category['parent'], $parents)){
                $fullBreadcrumbs = [];
                $fullBreadcrumbsObject = [];
                $categoryParents = [];
                if($category['parent'] > 0){
					$fullBreadcrumbs[] = $categories[$category['parent']]['full_breadcrumbs'];
					$fullBreadcrumbsObject = $categories[$category['parent']]['full_breadcrumbs_object'];
					$categoryParents = array_column($categories[$category['parent']]['full_breadcrumbs_object'], 'category_id');

                    $category['parents'] = $categoryParents;
                    foreach ($categoryParents as $categoryParent) {
                        $categories[$categoryParent]['children'][] = $category['category_id'];
                    }
                }

                $fullBreadcrumbsObject[] = $category['temp_category_breadcrumbs_full'];
                $fullBreadcrumbs[] = $category['temp_category_breadcrumbs'];

                $category['full_breadcrumbs'] = implode(',', $fullBreadcrumbs);
                $category['full_breadcrumbs_object'] = $fullBreadcrumbsObject;
                $tempParents[] = $category['category_id'];
                $categories[$category['category_id']] = $category;
            }
        }

        return [
            'parents' => $tempParents,
            'categories' => $categories
        ];
    }
    public function insertSyncCategories(?array $data = null)
    {
        if(empty($data)){
            return;
        }

        $is_inserted = $this->db->insert_batch($this->category_sync_table, $data);

        return !$is_inserted ? false : $this->db->affectedRows();
    }

    public function updateMainCategoriesWithSyncCategories()
    {
        $updateSql = "UPDATE {$this->category_table} ic
                      LEFT JOIN {$this->category_sync_table} ics ON ic.category_id = ics.category_id
                      SET
                        ic.cat_childrens = ics.children,
                        ic.breadcrumbs = ics.breadcrumbs,
                        ic.actualized = 1";
        return $this->db->query($updateSql);
    }

    public function emptySyncCategories()
    {
        $emptySql = "TRUNCATE {$this->category_sync_table}";
        return $this->db->query($emptySql);
    }
}
