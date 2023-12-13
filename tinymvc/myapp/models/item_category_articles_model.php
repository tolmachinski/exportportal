<?php

/**
 * catattributes_model.php
 *
 * category system model
 *
 * @author Litra Andrei
 */
class Item_Category_Articles_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function set_item_category_article_i18n($data = array()){
        if(empty($data)){
        	return false;
		}

        $this->db->insert("item_category_articles_i18n", $data);
        return $this->db->last_insert_id();
    }

    function get_item_categories($conditions = array()) {
        $order_by = "ic.name ASC";
        $where = array();
        $params = array();

        extract($conditions);

        if (isset($parent)) {
            $where[] = " parent = ? ";
            $params[] = $parent;
        }

//        $sql = "SELECT *
//				FROM item_category_articles as ica
//                LEFT JOIN item_category as ic ON ica.id_category = ic.category_id ";

        $sql = "SELECT ic.*, ica.id as id_article
				FROM item_category as ic
				LEFT JOIN item_category_articles as ica ON ic.category_id = ica.id_category ";

        if (!empty($where))
            $sql .= " WHERE " . implode(" AND", $where);

        if ($order_by != false)
            $sql .= " ORDER BY " . $order_by;

        return $this->db->query_all($sql, $params);
    }

	function get_categories_atr($conditions = array()) {
        $start = 0;
        $per_p = 20;
        $order_by = "ic.parent ASC";
        $columns = ' ica.translations_data, ica.id, ica.text, ica.visible as visib, ica.date, ic.name as name_cat, ica.photo as photo, ic.breadcrumbs, ica.id_category as id_cat ';

        extract($conditions);

        $where = $params = [];
        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 20);

        if (isset($cat_list)) {
            $cat_list = getArrayFromString($cat_list);
            $where[] = " ica.id_category IN (" . implode(',', array_fill(0, count($cat_list), '?')) . ")";
            array_push($params, ...$cat_list);
        }

        if (isset($id_cat)) {
            $where[] = " ica.id_category = ?";
            $params[] = $id_cat;
        }

        if (isset($visible)) {
            $where[] = " ica.visible = ? ";
            $params[] = $visible;
        }

        if (isset($parent)) {
            $where[] = " (ica.id_category = ? OR ic.parent = ?) ";
            $params[] = $parent;
            $params[] = $parent;
        }

        $sql = "SELECT " . $columns . "
                FROM item_category_articles as ica
                LEFT JOIN item_category as ic ON ica.id_category=ic.category_id ";

        if (!empty($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $order_split = explode("-", $order_by[0]);

        if ($order_by != false)
            $sql .= " ORDER BY " . $order_split[0] . " " .  $order_split[1];

        $sql .= " LIMIT " . $start . ", " . $per_p;

        return $this->db->query_all($sql, $params);
    }

    function get_one_cat_art($id = 0, $lang = __SITE_LANG){
        if($lang != 'en') {
			$sql = "SELECT
						ica.id, ica.id_category, ica.visible, ica.translations_data, ica.date,
						icai18n.photo as `photo`,
						icai18n.text as `text`
                    FROM item_category_articles ica
					INNER JOIN item_category_articles_i18n icai18n ON icai18n.id_article = ica.id AND icai18n.lang_article = ?
					WHERE ica.id_category = ? AND visible = 1";

            $params = [$lang, $id];
        } else {
            $sql = "SELECT * FROM item_category_articles WHERE id_category = ? AND visible = 1";
            $params = [$id];
        }

        return $this->db->query_one($sql , $params);
    }

    function get_details_cat_art_i18n($conditions = array()){
        $where = array();
        $params = array();

        $sql = "SELECT
                    ica18n.*, ica.id_category, ic.name as cat_name
                FROM item_category_articles_i18n ica18n
                LEFT JOIN item_category_articles ica ON ica18n.id_article = ica.id
                LEFT JOIN item_category ic ON ic.category_id = ica.id_category";

        if(!empty($conditions['id_article'])) {
            $where[] =  "ica.id = ?";
            $params[] = $conditions['id_article'];
        }

        if(!empty($conditions['id_article_i18n'])) {
            $where[] = "ica18n.id_article_i18n = ?";
            $params[] = $conditions['id_article_i18n'];
        }

        if(!empty($conditions['lang_article'])) {
            $where[] = "ica18n.lang_article = ?";
            $params[] = $conditions['lang_article'];
        }

        if(!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return $this->db->query_one($sql, $params);
    }

    function delete_cat_art_i18n($id_article_i18n){
        $this->db->where('id_article_i18n', $id_article_i18n);
        return $this->db->delete('item_category_articles_i18n');
    }

    function get_details_cat_art($id){
        $sql = "SELECT item_category_articles.*, ic.name as cat_name FROM item_category_articles  "
                . "INNER JOIN item_category ic ON category_id=id_category WHERE id=?";
        return $this->db->query_one($sql , array($id));
    }

    function delete_cat_art($id){
        $this->db->where('id', $id);
        return $this->db->delete('item_category_articles');
    }

    function update_cat_art_i18n($id, $data){
        $this->db->where('id_article_i18n', $id);
        return $this->db->update('item_category_articles_i18n', $data);
    }

    function update_cat_art($id, $data){
        $this->db->where('id', $id);
        return $this->db->update('item_category_articles', $data);
    }

    function get_count_categories_atr($conditions = array()){
		extract($conditions);
        $where = $params = [];

        if (isset($cat_list)) {
            $cat_list = getArrayFromString($cat_list);
            $where[] = " ica.id_category IN (" . implode(',', array_fill(0, count($cat_list), '?')) . ")";
            array_push($params, ...$cat_list);
        }

        if (isset($id_cat)) {
            $where[] = " ica.id_category = ?";
            $params[] = $id_cat;
        }

        if (isset($visible)) {
            $where[] = " ica.visible = ? ";
            $params[] = $visible;
        }

        if (isset($parent)) {
            $where[] = " ic.parent = ? ";
            $params[] = $parent;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM item_category_articles as ica
                LEFT JOIN item_category as ic ON ica.id_category=ic.category_id ";

		if (!empty($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function insert_cat_art($insert){
        if ($this->db->insert('item_category_articles', $insert))
            return $this->db->last_insert_id();
        return false;
    }

    function is_exist_cat_atr($id_cat){
        $temp = $this->db->query_one("SELECT COUNT(*) as counter FROM item_category_articles WHERE id_category=?", array($id_cat));
        return (boolean) $temp['counter'];
    }
}
