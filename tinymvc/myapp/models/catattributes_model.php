<?php
/**
 * catattributes_model.php
 *
 * category system model
 *
 * @author Litra Andrei
 */


class Catattributes_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    /** +
    * insert one attribute
    * $data - associative array with fields like in db
    */
    function insert_attr($data){
        $attributes = explode(';', $data['attributes']);
        $attr_type = $data['attr_type'];
        $category = $data['category'];
        $attr_type_value = 3;
        $attr_req = 1;
        $attr_sample = '';
        $params = [];

        if (isset($data['attr_value_type'])) {
            $attr_type_value = $data['attr_value_type'];
        }

        if (isset($data['attr_req'])) {
            $attr_req = $data['attr_req'];
        }

        if (isset($data['attr_sample'])) {
            $attr_sample = $data['attr_sample'];
        }

        $sql = "INSERT INTO item_cat_attr (`category`,`attr_name`, `attr_req`, `attr_type`, `attr_value_type`, `attr_sample`) VALUES ";

        foreach($attributes as $key => $attribute){
            $filtered = cleanInput($attribute);
            if(!empty($filtered)){
                $final_attributes[] = $filtered;
            }
        }

        if (empty($final_attributes)) {
            return false;
        }

        foreach($final_attributes as $key => $attribute) {
            $values[] = "( ?, ?, ?, ?, ?, ? )";
            array_push($params, ...[$category, $attribute, $attr_req, $attr_type, $attr_type_value, $attr_sample]);
        }

        $sql .= implode(', ', $values);

        $this->db->query($sql, $params);
        return $this->db->last_insert_id();
    }

    /** +
    * select one attribute
    * attr - id of the attribute
    */
    function get_attribute($attr){
        $sql = "SELECT *
		FROM item_cat_attr
		WHERE id = ?";
        return $this->db->query_one($sql, array($attr));
    }

	/** +
    * select attributes from list
    * $list - list of id of the attributes
    */
    function get_attributes_list($list){
        $list = getArrayFromString($list);

        $sql = "SELECT *
				FROM item_cat_attr
				WHERE id IN (" . implode(',', array_fill(0, count($list), '?')) . ")";
        return $this->db->query_all($sql, $list);
    }

    function get_attributes_by_conditions($conditions = array()){
        $where = array();
        $params = array();

        extract($conditions);

        if(!empty($id_category)){
            $id_category = getArrayFromString($id_category);
            $where[] = " ica.category IN (" . implode(',', array_fill(0, count($id_category), '?')) . ") ";
            array_push($params, ...$id_category);
        }

        if(!empty($attrs_list)){
            $attrs_list = getArrayFromString($attrs_list);

            if(!empty($attr_type)){
                $attr_type = getArrayFromString($attr_type);
                $where[] = "IF(
                    ica.attr_type IN (" . implode(',', array_fill(0, count($attr_type), '?')) . "),
                    ica.id IN (" . implode(',', array_fill(0, count($attrs_list), '?')) . "), true
                    )";

                array_push($params, ...$attr_type);
            } else{
                $where[] = " ica.id IN (" . implode(',', array_fill(0, count($attrs_list), '?')) . ") ";
            }

            array_push($params, ...$attrs_list);
        } elseif (!empty($attr_type)){
            $attr_type = getArrayFromString($attr_type);
            $where[] = " ica.attr_type IN (" . implode(',', array_fill(0, count($attr_type), '?')) . ") ";
            array_push($params, ...$attr_type);
        }

        $sql = "SELECT ica.* , iao.ord_place
                FROM item_cat_attr ica
                LEFT JOIN item_attr_order iao ON ica.id = iao.attribute";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY iao.ord_place, ica.attr_name ";
        return $this->db->query_all($sql, $params);
    }

    function get_attributes_values_by_conditions($conditions = array()){
        $where = array();
        $params = array();

        extract($conditions);

        if(!empty($attrs_values_list)){
            $attrs_values_list = getArrayFromString($attrs_values_list);
            $where[] = " id IN (" . implode(',', array_fill(0, count($attrs_values_list), '?')) . ") ";
            array_push($params, ...$attrs_values_list);
        }

        if(!empty($attrs_list)){
            $attrs_list = getArrayFromString($attrs_list);
            $where[] = " attribute IN (" . implode(',', array_fill(0, count($attrs_list), '?')) . ") ";
            array_push($params, ...$attrs_list);
        }

        $sql = "SELECT *
                FROM item_cat_attr_val";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return $this->db->query_all($sql, $params);
    }

    /** +
    * select all attributes for category and all its parents
    * cat - id of the category
    */
    function get_attributes($cat, $list_send = false){
        $params = [];

        if(!$list_send){
            $this->obj->load->model('Category_Model', 'category');
            $categs1 =  $this->obj->category->get_cat_parents($cat);

            if(strlen($categs1))
                $categs1 = $categs1 . $cat;
            else
                $categs1 = $cat;

            $cat_id = $cat;
        } else{
            $categs1 = $cat;
            $cat_id = explode(',', $categs1);
            $cat_id = end($cat_id);
        }

        $params[] = $cat_id;
        $categs1 = getArrayFromString($categs1);
        array_push($params, ...$categs1);

        $sql = "SELECT ca.* , ao.ord_place
                FROM item_cat_attr ca
                LEFT JOIN item_attr_order ao ON ( ca.id = ao.attribute AND ao.category = ? )
                WHERE ca.category IN ( " . implode(',', array_fill(0, count($categs1), '?')) . " )
                ORDER BY ord_place, attr_name";

        $attrs = $this->db->query_all($sql, $params);
		return arrayByKey($attrs, 'id');
    }

    /** +
    * select all attributes for categories
    * cat_list - ids list of the category
    */
    function get_categories_attr($cat_list){
        $cat_list = getArrayFromString($cat_list);

        $sql = "SELECT ca.id, ca.category, ca.attr_name, ca.attr_type, ca.translation_data, ao.ord_place
                FROM item_cat_attr ca
                LEFT JOIN item_attr_order ao ON ca.id = ao.attribute
                WHERE ca.category IN ( " . implode(',', array_fill(0, count($cat_list), '?')) . " )
                ORDER BY ISNULL(ao.ord_place), ao.ord_place ASC, ca.id ASC";

        $attrs = $this->db->query_all($sql, $cat_list);
		return arrayByKey($attrs, 'id');
    }
    function get_items_attr_values($items_list){
        $items_list = getArrayFromString($items_list);

        $sql = "SELECT ia.*, icav.value, icav.translation_data
                FROM item_attr ia
                LEFT JOIN item_cat_attr_val icav ON ia.attr_value = icav.id AND ia.attr = icav.attribute
                WHERE ia.item IN ( " . implode(',', array_fill(0, count($items_list), '?')) . " )
                ORDER BY ia.item, ia.attr";

		return $this->db->query_all($sql, $items_list);
    }

	function get_item_attr_full_values($id_item){
		$sql = "SELECT ia.id_attr, ia.attr, ia.attr_value, ica.attr_req, ica.attr_name, ica.attr_type, GROUP_CONCAT(icav.value SEPARATOR ', ') as attr_values, GROUP_CONCAT(icav.id) as vals_ids
				FROM item_attr ia
				INNER JOIN item_cat_attr ica ON ia.attr = ica.id
				LEFT JOIN item_cat_attr_val icav ON ia.attr_value = icav.id AND ia.attr = icav.attribute
				WHERE ia.item = ?
				GROUP BY attr
				ORDER BY ica.attr_name";
		return $this->db->query_all($sql, array($id_item));
	}

    function get_items_user_attr($items_list){
        $items_list = getArrayFromString($items_list);

        $sql = "SELECT *
                FROM item_user_attr
                WHERE id_item IN ( " . implode(',', array_fill(0, count($items_list), '?')) . " )";

		return $this->db->query_all($sql, $items_list);
    }

    /** +
     * update one attribute
     * info - associative array with fields like in db
     */
    function update_attr($info){
        $this->db->where('id', $info['id']);
        return $this->db->update('item_cat_attr', $info);
    }

    /** +
     * delete one attribute
     * attr - id of the attribute
     */
    function delete_attr($attr){
        $this->db->where('id', $attr);
        $this->db->delete('item_cat_attr');
	    $this->db->where('attr', $attr);

	    return $this->db->delete('item_attr');
    }

    /** +
    * insert attribute's values
    * $attr - id of the attribute
    * $vals - array with values for attribute
    */
    function insert_attr_values($attr, $vals){
        $params = [];
        $values = explode(';', $vals);

        if (empty($values = array_filter(array_map('cleanInput', $values)))) {
            return false;
        }

        foreach ($values as $key => $value) {
            $sql_vals[] = "(?, ?)";
            array_push($params, ...[$attr, $value]);
        }

        $sql = "INSERT IGNORE INTO item_cat_attr_val (`attribute`, `value`) VALUES ";
	    $sql .= implode(', ', $sql_vals);

	    return $this->db->query($sql, $params);
    }

    /** +
     * select all values for one attribute
     * attr - id of the attribute
     */
    function get_attr_values($attrs){
        $attrs = getArrayFromString($attrs);

        $sql = "SELECT *
			FROM item_cat_attr_val
			WHERE `attribute` IN ( " . implode(',', array_fill(0, count($attrs), '?')) . ")
			ORDER BY value";

        $rez = $this->db->query_all($sql, $attrs);
		return arrayByKey($rez, 'id');
    }

    function get_attrs_values($conditions = array()){
        $where = array();
        $params = array();

        extract($conditions);

        if(isset($id_attribute)){
            $id_attribute = getArrayFromString($id_attribute);
            $where[] = " av.attribute IN (" . implode(',', array_fill(0, count($id_attribute), '?')) . ") ";
            array_push($params, ...$id_attribute);
        }

        if(isset($attr_type)){
            $attr_type = getArrayFromString($attr_type);
            $where[] = " a.attr_type IN (" . implode(',', array_fill(0, count($attr_type), '?')) . ") ";
            array_push($params, ...$attr_type);
        }

        $sql = "SELECT av.*
                FROM item_cat_attr_val av
                INNER JOIN item_cat_attr a ON av.attribute = a.id";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY value ASC ";

        return $this->db->query_all($sql, $params);
    }

    /** delete
     * select all values and item counter for one attribute
     * attr - id of the attribute
     */
    function get_attr_values_counter($attr, $adittional_cond = array()){
        $params = [];
        $cat_cond = "";
        $search_condition = "";
        $loc_country = "";
        $loc_city = "";
        $from_price = "";
        $to_price = "";
        $search = "";
        $attr_condition = "";
        $year = 0;
        extract($adittional_cond);

        if($category != 0){
            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);

            $subcats = $categoryModel->get_cat_childrens($category) . $category;
            $subcats = getArrayFromString($subcats);
            $cat_cond = " AND it.id_cat IN (" . implode(',', array_fill(0, count($subcats), '?')) . ")";
            array_push($params, ...$subcats);
        }

        if(isset($country)) {
            $loc_country = " AND it.p_country = ?";
            $params[] = $country;
        }

        if(isset($city)) {
            $loc_city = " AND it.p_city = ?";
            $params[] = $city;
        }

        if ($year != 0) {
            $year_cond = " AND it.year = ?";
            $params[] = $year;
        }

        if(isset($min_price)) {
            $from_price = " AND it.price >= ?";
            $params[] = $min_price;
        }

        if(isset($max_price)) {
            $to_price = " AND it.price <= ?";
            $params[] = $max_price;
        }

        if(isset($keywords)){
            $search_condition = "AND MATCH (it.search_info) AGAINST (?) ";
            $params[] = $keywords;
        }

        if(isset($attrs)){
            foreach($attrs as $attrib => $vals){
                $str = array_values($vals);

                $attr_conditions .= " AND (
                    SELECT ia.attr_value
                    FROM item_attr ia
                    WHERE ia.item = it.id
                    AND ia.attr = ?
                    ) IN (" . implode(', ', array_fill(0, count($str), '?')) . ")";

                $params[] = $attrib;
                array_push($params, ...$str);
            }
        }

        $sql = "SELECT val. * , (
                    SELECT COUNT( ia.item )
                    FROM item_attr ia
                    LEFT JOIN items it ON it.id = ia.item
                    WHERE ia.attr = val.attribute
                    AND ia.attr_value = val.id
                    AND it.status < 4
                    $cat_cond
                    $loc_country $loc_city
                    $year_cond
                    $from_price $to_price
                    $search_condition
                    $attr_conditions
                ) AS counter
                FROM item_cat_attr_val val
                LEFT JOIN item_cat_attr attr ON attr.id = val.attribute
                WHERE val.attribute = $attr
                ORDER BY counter DESC, val.value";

        $values = $this->db->query_all($sql, $params);

        if ($this->db->numRows() > 0) {
            return $values;
        }

        return null;
    }

    /** +
    * select all values by list and other conditions
    * @param srting $attr_list
    * @param array $conditions
    *
    * @return array
    */
    function get_attr_values_by_list($attr_list, $conditions){
		$order_by = "create_date ASC";
		$where = array();
        $params = array();
		$item_status = [1, 2, 3];
		extract($conditions);

		if(isset($categories_list)){
            $categories_list = getArrayFromString($categories_list);
	    	$where[] = " it.id_cat IN (" . implode(',', array_fill(0, count($categories_list), '?')) . ") ";
            array_push($params, ...$categories_list);
		}

		if(isset($seller)){
	    	$where[] = " it.id_seller = ? ";
	        $params[] = $seller;
		}

		if(isset($accreditation)){
	    	$where[] = " cb.accreditation = ? ";
	        $params[] = $accreditation;
		}

		if(isset($country)){
            $where[] = " it.p_country = ? ";
            $params[] = $country;
		}

		if(isset($city)){
            $where[] = " it.p_city = ? ";
            $params[] = $city;
		}

		if(isset($status)){
            $status = getArrayFromString($status);
			$where[] = " it.status IN (" . implode(',', array_fill(0, count($status), '?')) . ")";
            array_push($params, ...$status);
		}

		if(isset($price_from)){
			$where[] = " it.price >= ?";
			$params[] = $price_from;
		}

		if(isset($price_to)){
			$where[] = " it.price <= ?";
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

        if(isset($blocked)){
            $where[] = " it.blocked = ?";
            $params[] = $blocked;
        }

		if(isset($keywords)){
          	$where[] = " MATCH (it.title, it.description, it.search_info) AGAINST (?)";
			$params[] = $keywords;
        }

        if(isset($attrs)){
            foreach($attrs as $attrib => $vals){
                $str = array_values((array) $vals);

                $where[] = " (
                    SELECT ia.attr_value
                    FROM item_attr ia
                    WHERE ia.item = it.id
                    AND ia.attr = ?
					GROUP BY ia.item
                    ) IN (" . implode(', ', array_fill(0, count($str), '?')) . ")";

                $params[] = $attrib;
                array_push($params, ...$str);
            }
        }

		$sql = "SELECT val.* , (
                    SELECT COUNT( ia.item )
                    FROM item_attr ia
                    LEFT JOIN items it ON it.id = ia.item
                    LEFT JOIN company_base cb ON cb.id_user = it.id_seller AND cb.type_company = 'company'
                    WHERE ia.attr = val.attribute
                    AND ia.attr_value = val.id
                    AND it.status IN ( " . implode(',', array_fill(0, count($item_status), '?')) . " )";

        array_push($params, ...$item_status);

		if (count($where)) {
			$sql .= " AND " . implode(' AND ', $where);
        }

        $attr_list = getArrayFromString($attr_list);

		$sql .=" ) AS counter
                FROM item_cat_attr_val val
                LEFT JOIN item_cat_attr attr ON attr.id = val.attribute
                WHERE val.attribute IN ( " . implode(',', array_fill(0, count($attr_list), '?')) . " )
                ORDER BY counter DESC, val.value";

        array_push($params, ...$attr_list);

		return $this->db->query_all($sql, $params);
	}

    /** +
     * select one value for one attribute
     * val - id of the value
     */
    function get_attr_value($id_value = 0){
        $sql = "SELECT *
                FROM item_cat_attr_val
                WHERE id = ?";

        return $this->db->query_one($sql, array($id_value));
    }

    /** +
     * update one value for one attribute
     * info - associative array with fields like in db
     */
    function update_attr_value($data = array()){
        $this->db->where('id', $data['id']);
        return $this->db->update('item_cat_attr_val', $data);
    }

    /** +
     * delete one value for one attribute
     * val - id of the value
     */
    function delete_attr_value($val){
        $this->db->where('id', $val);
        return $this->db->delete('item_cat_attr_val');
    }

    /** +
     * delete all values for one attribute
     * attr - id of the attribute
     */
    function delete_attr_values($attr){
        $this->db->where('attribute', $attr);
        return $this->db->delete('item_cat_attr_val');
    }

    /** +
    * attributes order
    *
    */
    function set_attr_order($cat, $data_array){
        if ($cat == 0) {
            return false;
        }

        $this->db->where('category', $cat);

        if ($this->db->delete('item_attr_order')) {
            $params = [];

            foreach($data_array as $ord => $attr){
                $vals[] = "(?, ?, ?)";
                array_push($params, ...[$cat, $attr, $ord]);
            }

            $sql = "INSERT INTO item_attr_order (category, attribute, ord_place) VALUES " . implode(', ', $vals);
            return $this->db->query($sql, $params);
        }
    }


    /** +
    * create list of attributes ids from string
    * @param string $str
    *
    * @return array
    */
    public function attrs_from_string($str){
	    $attr_in_array  = array();
        $attrs = explode('|', $str);
	    foreach($attrs as  $attr){
		    $attr_val = explode(':', $attr);
            $attr_val[0] = explode('_', $attr_val[0]);
            if(count($attr_val[0]) == 1)
                $attr_in_array[$attr_val[0][0]] = cleanInput(cut_str($attr_val[1]));
            elseif(count($attr_val[0]) == 2)
                $attr_in_array[$attr_val[0][0]][$attr_val[0][1]] = intVal(cut_str($attr_val[1], 7));
        }
	    return $attr_in_array;
    }

    /** +
    * List of attributes and selected values (used for report)
    * @param array $list
    *
    * @return array
    */
    function attr_values_from_list($list){
        $params = [];

	    $sql = "SELECT a.id, a.attr_name, GROUP_CONCAT(av.value) as attr_values
		    FROM item_cat_attr a
		    LEFT JOIN item_cat_attr_val av
		    ON a.id = av.attribute
		    WHERE a.id IN (" . implode(',', array_fill(0, count($list), '?')) . ")
		    AND av.id IN (" . implode(',', array_fill(0, count($list), '?')). ")
		    GROUP BY a.id";

        array_push($params, ...array_keys($list));
        array_push($params, ...array_values($list));

	    return $this->db->query_all($sql, $params);
    }

	function get_attributes_dt($conditions){
        $params = [];
        $where = array();
        $category_condition = "";
		extract($conditions);

        if(isset($id_category)){
            $this->obj->load->model('Category_Model', 'category');
            $categs =  $this->obj->category->get_cat_parents($id_category);

            if(strlen($categs)){
                $categs = $categs . $id_category;
            } else{
                $categs = $id_category;
            }

            $categs = getArrayFromString($categs);

            $category_condition = " AND ao.category = ?";
            $params[] = $id_category;
            $where[] = " ca.category IN (" . implode(',', array_fill(0, count($categs), '?')) . ") ";
            array_push($params, ...$categs);
        }

		if(!empty($keywords)){
			$where[] = " MATCH (ca.attr_name) AGAINST (?) ";
            $params[] = $keywords;
        }

        $sql = "SELECT ca.* , ao.ord_place
                FROM item_cat_attr ca
                LEFT JOIN item_attr_order ao ON ca.id = ao.attribute {$category_condition}";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY ord_place, attr_name";

        if(isset($limit, $start)){
            $limit = (int) $limit;
            $start = (int) $start;
            $sql .= " LIMIT {$start}, {$limit}";
        }

        return $this->db->query_all($sql, $params);
	}

	function get_attributes_dt_count($conditions){
        $where = array();
        $params = [];
		extract($conditions);

		if(isset($id_category)){
            $this->obj->load->model('Category_Model', 'category');
            $categs =  $this->obj->category->get_cat_parents($id_category);

            if(strlen($categs)){
                $categs = $categs . $id_category;
            } else{
                $categs = $id_category;
            }

            $categs = getArrayFromString($categs);
            $where[] = " ca.category IN (" . implode(',', array_fill(0, count($categs), '?')) . ") ";
            array_push($params, ...$categs);
        }

        if(!empty($keywords)){
			$where[] = " MATCH (ca.attr_name) AGAINST (?) ";
            $params[] = $keywords;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM item_cat_attr ca";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $temp = $this->db->query_one($sql, $params);
		return $temp['counter'];
	}
}
