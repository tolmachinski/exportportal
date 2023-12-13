<?php
/**
*country_articles.php
*
*Country articles model
*
*@author
*/

class Country_Articles_Model extends TinyMVC_Model {

	private $country_articles_table = 'country_articles';
	private $country_articles_table_i18n = 'country_articles_i18n';
	private $items_table = 'items';
	private $users_table = 'users';
	private $port_country_table = 'port_country';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function set_article($data){
		$this->db->insert($this->country_articles_table, $data);
		return $this->db->last_insert_id();
	}

	public function get_article($id_article){
		$sql = "SELECT *
				FROM " . $this->country_articles_table .
				" WHERE id = ?";
		return $this->db->query_one($sql, array($id_article));
	}

	public function exist_article_by_condition( $conditions ){
		$where = array();
		$params = array();
		extract($conditions);

		if(isset($country)){
			$where[] = " id_country = ? ";
			$params[] = $country;
		}

		if(isset($type)){
			$where[] = " type = ? ";
			$params[] = $type;
		}

		if(isset($not_id_article)){
			$where[] = " id != ? ";
			$params[] = $not_id_article;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->country_articles_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$rez = $this->db->query_one($sql, $params);

		return $rez['counter'];
	}

	public function get_articles_by_condition($conditions){
		$where = array();
		$params = array();
		$order_by = "id";
		$where['visible'] = 1;
        $lang = __SITE_LANG;

		extract($conditions);

		if(isset($country)){
			$where[] = " id_country = ? ";
			$params[] = $country;
		}

		if(isset($type)){
			$where[] = " type = ? ";
			$params[] = $type;
		}

        if($lang == 'en') {
            $sql = "SELECT cat.*, pt.country
                    FROM " . $this->country_articles_table . " cat
                    LEFT JOIN port_country pt ON pt.id = cat.id_country ";
        } else {
            $sql = "SELECT
                        cat.id,
                        cat.id_country,
                        cat.`type`,
                        cati18n.meta_key as meta_key,
                        cati18n.meta_desc as meta_desc,
                        cati18n.`text` as `text`,
                        cati18n.photo as photo,
                        cat.visible,
                        cat.`date`,
                        cat.translations_data,
                        pt.country
                    FROM " . $this->country_articles_table . " cat
                    INNER JOIN " . $this->country_articles_table_i18n . " cati18n ON cati18n.id_article = cat.id AND cati18n.lang_article = ?
                    LEFT JOIN port_country pt ON pt.id = cat.id_country ";

            array_unshift($params, $lang);
        }

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " ORDER BY ". $order_by;

		return $this->db->query_all($sql, $params);
	}

	public function get_article_by_country( $conditions ){
		$page = 0;
		$per_p = 20;
		$where = array();
		$params = array();
		$order_by = "RAND()";
		$where['visible'] = 1;

		extract($conditions);

		if(isset($type)){
			$where[] = " type = ? ";
			$params[] = $type;
		}

		$sql = "SELECT ca.id_country, pc.country
				FROM " . $this->country_articles_table. " ca
				INNER JOIN " . $this->port_country_table  . " pc ON ca.id_country = pc.id";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " ORDER BY ".$order_by;

		if(!isset($count))
			$count = $this->counter_by_conditions($conditions);

		$pages = ceil($count/$per_p);

		if(!isset($no_limit)){
			if(!isset($start)){
				if ($page > $pages) $page = $pages;
				$start = ($page-1)*$per_p;

				if($start < 0) $start = 0;
			}

			$sql .= " LIMIT " . $start ;

			if($per_p > 0)
				$sql .= "," . $per_p;
		}
		return $this->db->query_all($sql, $params);
	}

	public function get_articles($conditions){
		$page = 0;
		$per_p = 20;
		$where = array();
		$params = array();
		$order_by = "date DESC";

        extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($country)){
			$where[] = " ca.id_country = ? ";
			$params[] = $country;
		}

		if(isset($type)){
			$where[] = " ca.type = ? ";
			$params[] = $type;
		}

		if(isset($visible)){
			$where[] = " ca.visible = ? ";
			$params[] = $visible;
		}

		if(isset($keywords)){
			$order_by = $order_by . ", REL DESC";
			$where[] = " MATCH (ca.text) AGAINST (?)";
			$params[] = $keywords;
			$rel = " , MATCH (ca.text) AGAINST (?) as REL";
            array_unshift($params, $keywords);
		}

		$sql = "SELECT ca.*, pc.country " . $rel . "
				FROM " . $this->country_articles_table . " ca
				INNER JOIN " . $this->port_country_table  . " pc ON ca.id_country = pc.id";

		if(count($where))
			$sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " ORDER BY ".$order_by;

		if(!isset($count))
			$count = $this->counter_by_conditions($conditions);

		$pages = ceil($count/$per_p);

		if(!isset($start)){
			if ($page > $pages) $page = $pages;
			$start = ($page-1)*$per_p;

			if($start < 0) $start = 0;
		}

		$sql .= " LIMIT " . $start ;

		if($per_p > 0)
			$sql .= "," . $per_p;

		return $this->db->query_all($sql, $params);
	}

	public function counter_by_conditions($conditions){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($country)){
			$where[] = " id_country = ? ";
			$params[] = $country;
        }

		if(isset($keywords)){
			$where[] = " MATCH (text) AGAINST (?)";
			$params[] = $keywords;
        }

        if(isset($type)){
			$where[] = " type = ? ";
			$params[] = $type;
		}

        if(isset($visible)){
			$where[] = " visible = ? ";
			$params[] = $visible;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->country_articles_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function set_article_i18n($data = array()){
        $this->db->insert($this->country_articles_table_i18n, $data);
        return $this->db->last_insert_id();
    }

	public function delete_article_i18n($id_article_i18n) {
		$this->db->where('id_article_i18n', $id_article_i18n);
		return $this->db->delete($this->country_articles_table_i18n);
	}

	public function get_article_i18n($condition = array()){
        $where = array();
        $params = array();

        if(!empty($condition['id_article'])) {
            $where[] = "id_article = ?";
            $params[] = $condition['id_article'];
        }

        if(!empty($condition['id_article_i18n'])) {
            $where[] = "id_article_i18n = ?";
            $params[] = $condition['id_article_i18n'];
        }

        if(!empty($condition['lang'])) {
            $where[] = "lang_article = ?";
            $params[] = $condition['lang'];
        }


		$sql = "SELECT *
				FROM {$this->country_articles_table_i18n} WHERE ".
				implode(" AND ", $where);

		return $this->db->query_one($sql, $params);
	}

	public function update_article($id_article, $data){
		$this->db->where('id', $id_article);
		return $this->db->update($this->country_articles_table, $data);
	}

	public function update_article_i18n($id_article_i18n, $data){
		$this->db->where('id_article_i18n', $id_article_i18n);
		return $this->db->update($this->country_articles_table_i18n, $data);
	}

	public function delete_article($id_article) {
		$this->db->where('id', $id_article);
		return $this->db->delete($this->country_articles_table);
	}
}


