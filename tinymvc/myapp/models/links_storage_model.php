<?php

class Links_Storage_Model extends TinyMVC_Model {

	private $links_storage_table = 'links_storage';
	private $port_country_table = "port_country";
	private $links_storage_key = 'eplinkkey';

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function set_link_storage($data){
		$this->db->insert($this->links_storage_table, $data);
        return $this->db->last_insert_id();
	}

	public function set_encrypt_info($id_link, $data){
        if (!is_array($data) || empty($data)) {
            return false;
        }

        $columns = $params = [];

        foreach ($data as $field => $value) {
            $columns[] = "? = AES_ENCRYPT(?, ?) ";
            array_push($params, $field, $value, $this->links_storage_key);
        }

        $params[] = $id_link;

        $sql = "UPDATE $this->links_storage_table SET " . implode(',', $columns) . " WHERE id_links_storage = ?";

        return $this->db->query($sql, $params);
	}

	public function get_encrypt_info($id_link, $data){
        if (!is_array($data) || empty($data)) {
            return false;
        }

        $columns = $params = [];

        foreach ($data as $field) {
            $columns[] = "AES_ENCRYPT(?, ?) as ?";
            array_push($params, $field, $this->links_storage_key, $field);
        }

        $params[] = $id_link;

        $sql = "SELECT " . implode(',', $columns) . " FROM ".$this->links_storage_table." WHERE id_links_storage = ?";

        return $this->db->query_one($sql, $params);
    }

	public function enc_string($columns){
        $str = "";
        if(is_array($columns)){
            foreach($columns as $field => $value){
                $arr[] = " $field = AES_ENCRYPT('$value', '".$this->links_storage_key."')";
            }
            $str = implode(", ", $arr);
        }
        return $str;
    }

    public function dec_string($columns){
        $str = "";
        if(is_array($columns)){
            foreach($columns as $field){
                $arr[] = " AES_DECRYPT($field, '".$this->links_storage_key."') as $field";
            }
            $str = implode(", ", $arr);
        }
        return $str;
    }

	public function delete_link_storage($id_link) {
        $this->db->where('id_links_storage', $id_link);
        return $this->db->delete($this->links_storage_table);
    }

	public function get_link_storage($id_link){
		$sql = "SELECT ls.*, pc.country as country_name
				FROM  $this->links_storage_table ls
				LEFT JOIN  $this->port_country_table pc ON ls.id_country = pc.id
				WHERE ls.id_links_storage = ? ";
		return $this->db->query_one($sql, array($id_link));
	}

	public function exist_link_storage($id_link){
		$sql = "SELECT COUNT(*) as counter
				FROM  $this->links_storage_table
				WHERE id_links_storage = ? ";

		$rez = $this->db->query_one($sql, array($id_link));
		return $rez['counter'];
	}

	public function get_links_storage($conditions){
		$page = 0;
		$per_p = 20;
		$order_by = " ls.id_links_storage DESC";

		extract($conditions);
		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		if(isset($paid)){
			$where[] = " ls.paid = ? ";
            $params[] = $paid;
		}

		if(isset($country)){
			$where[] = " ls.id_country = ? ";
            $params[] = $country;
		}

		if(isset($keywords)){
			if(str_word_count_utf8($keywords) > 1){
				$order_by = $order_by . ", REL DESC";
				$where[] = " MATCH (ls.link, ls.title, ls.description) AGAINST (?)";
				$params[] = $keywords;
				$rel = " , MATCH (ls.link, ls.title, ls.description) AGAINST (?) as REL";
                array_unshift($params, $keywords);
			} else{
				$where[] = " (ls.link LIKE ? OR ls.title LIKE ? OR ls.description LIKE ?) ";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

		$sql = "SELECT ls.*, pc.country as country_name ".$rel."
                FROM $this->links_storage_table ls
				LEFT JOIN  $this->port_country_table pc ON ls.id_country = pc.id ";

        if(count($where))
        	$sql .= " WHERE " . implode(" AND ", $where);

		$sql .= " ORDER BY ".$order_by;

		if(isset($limit)){
		  $sql .= " LIMIT " . $limit ;
        } else{
			if(!isset($count))
				$count = $this->count_links_storage($conditions);

			$pages = ceil($count/$per_p);

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

	public function count_links_storage($conditions){
        extract($conditions);
        $where = $params = [];

		if(isset($paid)){
			$where[] = " ls.paid = ? ";
            $params[] = $paid;
		}

		if(isset($country)){
			$where[] = " ls.id_country = ? ";
            $params[] = $country;
		}

		if(isset($keywords)){
			$words = explode(' ', $keywords);
			if(count($words) > 1){
				$where[] = " MATCH (ls.link, ls.title, ls.description) AGAINST (?)";
				$params[] = $keywords;
			} else{
				$where[] = " (ls.link LIKE ? OR ls.title LIKE ? OR ls.description LIKE ?)";
                array_push($params, ...array_fill(0, 3, '%' . $keywords . '%'));
			}
        }

		$sql = "SELECT COUNT(*) as counter
                FROM $this->links_storage_table ls
				LEFT JOIN  $this->port_country_table pc ON ls.id_country = pc.id ";

        if(count($where))
        	$sql .= " WHERE " . implode(" AND ", $where);

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	public function update_link_storage($id_link, $data){
        $this->db->where('id_links_storage', $id_link);
        return $this->db->update($this->links_storage_table, $data);
    }
}

