<?php
/**
 * seller_videos_model.php
 * model for seller videos
 * @author Cravciuc Andrei
 */

class Seller_About_Model extends TinyMVC_Model
{
	private $about_page_table = "seller_about";
	private $about_page_aditional_table = "seller_about_aditional";

	function getPageAbout($id_seller){
        $this->db->where('id_seller', $id_seller);
        $this->db->limit(1);
        return $this->db->get_one($this->about_page_table);
	}

	public function updateAboutBlock($id_seller, $id_company, $data){
		$sql = "INSERT INTO ".$this->about_page_table." (id_seller, id_company, {$data['block_name']}) VALUES (?,?,?)
				  ON DUPLICATE KEY UPDATE {$data['block_name']}=?";
		return $this->db->query($sql, array($id_seller, $id_company, $data['value'], $data['value']));
	}

	public function setAboutAditionalBlock($data){
        return empty($data) ? false : $this->db->insert($this->about_page_aditional_table, $data);
	}

	public function updateAboutAditionalBlock($id_block,$id_seller, $data){
		$this->db->where('id_block', $id_block);
		$this->db->where('id_seller', $id_seller);
		return $this->db->update($this->about_page_aditional_table, $data);
	}

	public function deleteAboutAditionalBlock($id_block,$id_seller){
		$this->db->in('id_block', $id_block);
		$this->db->where('id_seller', $id_seller);
		return $this->db->delete($this->about_page_aditional_table);
	}

	function getPageAboutAditional($conditions){
		$order_by = 'date_added';

		extract($conditions);

		$where = $params = [];

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

		if(isset($id_block)){
            $id_block = getArrayFromString($id_block);
			$where[] = " id_block IN (" . implode(',', array_fill(0, count($id_block), '?')) . ") ";
            array_push($params, ...$id_block);
		}

		if(isset($id_seller)){
			$where[] = " id_seller = ? ";
			$params[] = $id_seller;
		}
		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}

 		$sql = "SELECT * FROM {$this->about_page_aditional_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY {$order_by}";

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

	function countPageAboutAditional($conditions){
        extract($conditions);

		$where = $params = [];

        if(isset($id_block)){
            $id_block = getArrayFromString($id_block);
			$where[] = " id_block IN (" . implode(',', array_fill(0, count($id_block), '?')) . ") ";
            array_push($params, ...$id_block);
		}

		if(isset($id_seller)){
			$where[] = " id_seller = ? ";
			$params[] = $id_seller;
		}

		if(isset($id_company)){
			$where[] = " id_company = ? ";
			$params[] = $id_company;
		}

		$sql = "SELECT 	COUNT(*) as counter FROM {$this->about_page_aditional_table}";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
	}

	function getAboutAditionalBlock($id_seller, $id_block){
		$sql = "SELECT 	*
				FROM ".$this->about_page_aditional_table."
				WHERE id_seller = ? AND id_block = ?";
		return $this->db->query_one($sql, array($id_seller, $id_block));
	}
}
