<?php
/**
*seo_static_pages.php
*
*seo static pages model
*
*@author
*/

class Seo_static_pages_Model extends TinyMVC_Model {

	private $seo_static_pages_table = 'seo_static_pages';

	public function set_seo($data){
		return $this->db->insert($this->seo_static_pages_table, $data);
	}

	public function get_seo_one($id_seo){
        $this->db->where('id', $id_seo);
        $this->db->limit(1);
        return $this->db->get_one($this->seo_static_pages_table);
	}

	public function get_seo_by_key($key){
        $this->db->where('short_key', $key);
        $this->db->limit(1);
		return $this->db->get_one($this->seo_static_pages_table);
	}

	public function get_seo($conditions){
		$page = 0;
		$per_p = 20;
		$order_by = "id DESC";

        extract($conditions);

		$where = $params = [];

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

		$sql = "SELECT * FROM {$this->seo_static_pages_table}";

		if (!empty($where)) {
        	$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY {$order_by}";

		if (!isset($count)) {
			$count = $this->counter_by_conditions($conditions);
        }

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

	public function counter_by_conditions($conditions = []){
        $this->db->select('COUNT(*) as counter');
        $this->db->limit(1);
        return $this->db->get_one($this->seo_static_pages_table)['counter'];
	}

	public function update_seo($id_seo, $data){
        $this->db->where('id', $id_seo);
        return $this->db->update($this->seo_static_pages_table, $data);
    }

	public function delete_seo($id_seo){
        $this->db->where('id', $id_seo);
        return $this->db->delete($this->seo_static_pages_table);
    }
}

