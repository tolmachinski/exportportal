<?php

class Mass_media_Model extends TinyMVC_Model {

	var $obj;
	public $per_page = 10;
    private $media_table = "mass_media";
    private $news_table = "mass_media_news";
    private $country_table = "port_country";
    public $path_to_img_media = "public/img/media/";
    public $path_to_img_news = "public/img/news/";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function get_one_news($id){
        $sql = "SELECT n.*, m.title_media, m.logo_media, c.country, c.id_continent
				FROM ".$this->news_table." n
				LEFT JOIN ".$this->media_table." m ON n.id_media = m.id_media
				LEFT JOIN ".$this->country_table." c ON n.id_country = c.id
				WHERE id_news = ? ";
        return $this->db->query_one($sql, array($id));
    }

	public function get_news($conditions = array()){
        $order_by = 'id_news ASC';
		$rel = "";

		extract($conditions);
		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        if(isset($lang)) {
            $where[] = "n.lang = ?";
            $params[] = __SITE_LANG;
        }

		if(isset($id_continent)){
			$where[] = 'c.id_continent = ?';
			$params[] = $id_continent;
		}

		if(isset($type_media)){
			$where[] = 'm.type_media = ?';
			$params[] = $type_media;
		}

		if(isset($id_channel)){
			$where[] = 'n.id_media = ?';
			$params[] = $id_channel;
		}

		if(isset($published)){
			$where[] = 'published_news = ?';
			$params[] = $published;
		}

        if(isset($not_id_news)){
            $where[] = "n.id_news != ?";
            $params[] = $not_id_news;
        }

		if(isset($articles_list)){
            $articles_list = getArrayFromString($articles_list);
			$where[] = "n.id_news IN (" . implode(',', array_fill(0, count($articles_list), '?')) . ")";
            array_push($params, ...$articles_list);
		}

		if(!empty($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$order_by = " REL_tags DESC ";
				$where[] = " MATCH (n.title_news, n.description_news, n.fulltext_news) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (n.title_news, n.description_news, n.fulltext_news) AGAINST (?) as REL_tags";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (n.title_news LIKE ? || n.description_news LIKE ? || n.fulltext_news LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

		$sql = "SELECT n.*, m.title_media, m.logo_media, c.country, c.id_continent $rel
				FROM ".$this->news_table." n
				LEFT JOIN ".$this->media_table." m ON n.id_media = m.id_media
				LEFT JOIN ".$this->country_table." c ON n.id_country = c.id ";

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		if(isset($limit)) {
			$sql .= " LIMIT ". $limit;
        }

		return $this->db->query_all($sql, $params);
    }

	public function count_news($conditions = array()){
        extract($conditions);
		$where = $params = array();

        if(isset($lang)) {
            $where[] = "n.lang = ?";
            $params[] = __SITE_LANG;
        }


		if(isset($id_continent)){
			$where[] = 'c.id_continent = ?';
			$params[] = $id_continent;
		}

		if(isset($type_media)){
			$where[] = 'm.type_media = ?';
			$params[] = $type_media;
		}

		if(isset($id_channel)){
			$where[] = 'n.id_media = ?';
			$params[] = $id_channel;
		}

		if(isset($published)){
			$where[] = 'published_news = ?';
			$params[] = $published;
		}

        if(isset($articles_list)){
            $articles_list = getArrayFromString($articles_list);
			$where[] = "n.id_news IN (" . implode(',', array_fill(0, count($articles_list), '?')) . ")";
            array_push($params, ...$articles_list);
        }

		if(!empty($keywords)){
            $words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (n.title_news, n.description_news, n.fulltext_news) AGAINST (?)";
				$params[] = $keywords;
			} else{
				$where[] = " (n.title_news LIKE ? || n.description_news LIKE ? || n.fulltext_news LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

		$sql = "SELECT COUNT(*) as counter
				FROM ".$this->news_table." n
				LEFT JOIN ".$this->media_table." m ON n.id_media = m.id_media
				LEFT JOIN ".$this->country_table." c ON n.id_country = c.id ";

		if(count($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
    }

	public function set_news($data){
        if(!count($data)) {
        	return false;
        }

        $this->db->insert($this->news_table, $data);
        return $this->db->last_insert_id();
    }

	public function exist_news($value){
		$sql = "SELECT COUNT(*) as exist
				FROM " . $this->news_table . "
				WHERE `id_news` = ?";
		$rez = $this->db->query_one($sql, array($value));
		return $rez['exist'];
	}

	public function update_news($id, $data){
        $this->db->where('id_news', $id);
        return $this->db->update($this->news_table, $data);
    }

	public function delete_news($id){
		$this->db->where('id_news', $id);
		return $this->db->delete($this->news_table);
	}

	public function get_one_media($id){
        $sql = "SELECT *
                FROM " . $this->media_table . "
                WHERE id_media = ? ";
        return $this->db->query_one($sql, array($id));
    }

	public function get_media($conditions = array()){
		$where = array();
		$params = array();
		$order_by = 'id_media DESC';

		extract($conditions);

		$sql = "SELECT *
				FROM " . $this->media_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$sql .= " ORDER BY " . $order_by;

		if(isset($limit))
			$sql .= " LIMIT ". $limit;

		return $this->db->query_all($sql, $params);
    }

	public function count_media($conditions = array()){
		$where = array();
		$params = array();

		extract($conditions);

		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->media_table;

		if(count($where))
			$sql .= " WHERE " . implode(" AND", $where);

		$rez = $this->db->query_one($sql, $params);

		return $rez['counter'];
    }

	public function set_media($data){
        if(!count($data))
        	return false;
        $this->db->insert($this->media_table, $data);
        return $this->db->last_insert_id();

    }

	public function delete_media($id, $name_img=null){

		$this->db->where('id_media', $id);
		$media = $this->get_one_media($id);

		if($this->db->delete($this->media_table)){

			if(is_null($name_img)){
				$img = $media['logo_media'];
			}else
				$img = $name_img;

			@unlink($this->path_to_logo_img.$img);
			return true;

		}else
			return false;

	}

	public function exist_media($value){
		$sql = "SELECT COUNT(*) as exist
				FROM " . $this->media_table . "
				WHERE `id_media` = ?";
		$rez = $this->db->query_one($sql, array($value));
		return $rez['exist'];
	}

	public function update_media($id, $data){
        $this->db->where('id_media', $id);
        return $this->db->update($this->media_table, $data);
    }
}
