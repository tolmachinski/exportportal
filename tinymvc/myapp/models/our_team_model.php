<?php

/**
 *
 *
 * model for text block
 *
 * @author
 */

class Our_team_Model extends TinyMVC_Model
{
    private $our_team_table = "epteam_people";
    private $team_office_table = "epteam_offices";
    private $country_table = "port_country";

	public function set_person($data){
		return empty($data) ? false : $this->db->insert($this->our_team_table, $data);
	}

	public function exist_person($value){
		$sql = "SELECT COUNT(*) as exist
				FROM " . $this->our_team_table . "
				WHERE `id_person` = ?";
		return $this->db->query_one($sql, array($value))['exist'];
	}

	public function update_person($id, $data){
        $this->db->where('id_person', $id);
        return $this->db->update($this->our_team_table, $data);
    }

	public function delete_person($id, $name_img = null){
        $img = $name_img ?? $this->get_person($id)['img_person'];

		$this->db->where('id_person', $id);
        if (!$this->db->delete($this->our_team_table)) {
            return false;
        }

        @unlink($this->path_to_person_img . $img);
        return ($this->path_to_person_img . $img);

	}

	public function get_person($id){
		$sql = "SELECT *
				FROM " . $this->our_team_table . " o
				WHERE id_person = ?";
		return $this->db->query_one($sql, array($id));
    }

	public function get_persons($conditions = array()){
        $order_by = 'name_person ASC';
		extract($conditions);

		$where = $params = [];

		if(isset($id_office)){
			$where[] = 'p.id_office = ?';
			$params[] = $id_office;
		}

		$sql = "SELECT p.*, o.name_office, c.country
				FROM " . $this->our_team_table . " p
				INNER JOIN " . $this->team_office_table . " o ON p.id_office = o.id_office
				INNER JOIN " . $this->country_table . " c ON o.id_country = c.id";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		return $this->db->query_all($sql, $params);
    }

	public function count_persons($conditions = array()){
        extract($conditions);

		$where = $params = [];

		if(isset($id_office)){
			$where[] = 'p.id_office = ?';
			$params[] = $id_office;
		}

		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->our_team_table . " p
				INNER JOIN " . $this->team_office_table . " o ON p.id_office = o.id_office
				INNER JOIN " . $this->country_table . " c ON o.id_country = c.id";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
    }
}
