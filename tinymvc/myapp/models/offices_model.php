<?php

/**
 *
 *
 * model for text block
 *
 * @author
 */

class Offices_Model extends TinyMVC_Model {
    private $team_office_table = "epteam_offices";
    private $country_table = "port_country";

	public function set_office($data){
        return empty($data) ? false : $this->db->insert($this->team_office_table, $data);
	}

	public function exist_office($id_office){
		$sql = "SELECT COUNT(*) as exist
				FROM " . $this->team_office_table . "
				WHERE `id_office` = ?";
		return $this->db->query_one($sql, array($id_office))['exist'];
	}

	public function update_office($id_office, $data){
        $this->db->where('id_office', $id_office);
        return $this->db->update($this->team_office_table, $data);
    }

	public function delete_office($id_office){
		$this->db->where('id_office', $id_office);
		return $this->db->delete($this->team_office_table);
	}

	public function get_office($id_office){
		$sql = "SELECT o.*, c.country
				FROM " . $this->team_office_table . " o
				INNER JOIN " . $this->country_table . " c ON o.id_country = c.id
				WHERE id_office = ?";
		return $this->db->query_one($sql, array($id_office));
    }

	public function get_offices($conditions = array()){
		$order_by = 'name_office ASC';

		extract($conditions);

        $where = $params = [];

		if(isset($visible)){
			$where[] = 'visible_vacancy = ?';
			$params[] = $visible;
		}

		$sql = "SELECT o.*, c.country, c.country as country_name, o.address_office as address_company
				FROM " . $this->team_office_table . " o
				INNER JOIN " . $this->country_table . " c ON o.id_country = c.id";

		if(!empty($where)) {
			$sql .= " WHERE " . implode(" AND", $where);
        }

		$sql .= " ORDER BY " . $order_by;
		return $this->db->query_all($sql, $params);
    }

	public function get_office_location($conditions){
		$order_by = 'name_office ASC';

		extract($conditions);

        $where = $params = [];

		if(isset($id_continent)){
			$where[] = 'id_continent = ?';
			$params[] = $id_continent;
		}

		if(isset($id_country)){
			$where[] = 'id_country = ?';
			$params[] = $id_country;
		}

		$sql = "SELECT DISTINCT c.country, c.id
				FROM " . $this->team_office_table . " o
				INNER JOIN " . $this->country_table . " c ON o.id_country = c.id";

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		if (isset($limit)) {
			$sql .= " LIMIT ". $limit;
        }

		return $this->db->query_all($sql, $params);
	}
}
