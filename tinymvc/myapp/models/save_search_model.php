<?php
/**
 * company_model.php
 *
 * company model
 *
 * @author Litra Andrei
 */

class Save_Search_Model extends TinyMVC_Model
{
	private $save_search_table = "save_search";

	public function set_save_search($data){
		return $this->db->insert($this->save_search_table, $data);
	}

	function get_saved_search($id_user, $limit){
		$sql = "SELECT * FROM {$this->save_search_table} WHERE id_user = ?  ORDER BY id_search DESC LIMIT {$limit}";
		return $this->db->query_all($sql, array($id_user));
	}

	function get_count_saved_search($id_user){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_user', $id_user);

        return $this->db->get_one($this->save_search_table)['counter'];
	}

	function i_save_it($id_user, $id_search){
        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_search', $id_search);
        $this->db->where('id_user', $id_user);
        $this->db->limit(1);
        return $this->db->get_one($this->save_search_table)['counter'];
	}

	function unsave_search($id_search){
		$this->db->where('id_search', $id_search);
		return $this->db->delete($this->save_search_table);
	}
}
