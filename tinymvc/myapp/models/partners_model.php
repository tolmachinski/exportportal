<?php
/**
 * partners_model.php
 *
 * partners model
 *
 * @author
 */

class Partners_Model extends TinyMVC_Model
{
    private $partners_table = "partners";
    private $country_table = "port_country";
    private $continent_table = "continents";

	public function get_partner($id){
        $sql = "SELECT p.*, c.country
				FROM ".$this->partners_table." p
				LEFT JOIN ".$this->country_table." c ON p.id_country = c.id
				WHERE id_partner = ? ";
        return $this->db->query_one($sql, array($id));
    }

	public function get_partners($conditions = []){
		$where = array();
		$params = array();
		$order_by = 'id_partner DESC';

		extract($conditions);

		$sql = "SELECT p.*, c.country, co.name_continent
			FROM ".$this->partners_table." p
			LEFT JOIN ".$this->country_table." c ON p.id_country = c.id
			LEFT JOIN ".$this->continent_table." co ON co.id_continent = c.id_continent ";

		if(isset($on_home)){
			$where[] = 'on_home = ?';
			$params[] = $on_home;
		}

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		$sql .= " ORDER BY " . $order_by;

		if (isset($limit)) {
			$sql .= " LIMIT ". $limit;
        }

		return $this->db->query_all($sql, $params);
    }

	public function count_partners($conditions = []){
        extract($conditions);

		$where = $params = [];

		$sql = "SELECT COUNT(*) as counter
			FROM ".$this->partners_table." p
			LEFT JOIN ".$this->country_table." c ON p.id_country = c.id
			LEFT JOIN ".$this->continent_table." co ON co.id_continent = c.id_continent ";

		if (isset($on_home)) {
			$where[] = 'on_home = ?';
			$params[] = $on_home;
		}

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
        }

		return $this->db->query_one($sql, $params)['counter'];
    }

	public function set_partner($data){
        return empty($data) ? false : $this->db->insert($this->partners_table, $data);
    }

	public function exist_partner($value){
		$sql = "SELECT COUNT(*) as exist
				FROM " . $this->partners_table . "
				WHERE `id_partner` = ?";
		return $this->db->query_one($sql, array($value))['exist'];
	}

	public function update_partner($id, $data){
		$this->db->where('id_partner', $id);
		return $this->db->update($this->partners_table, $data);
	}

	public function delete_partner($id_partner){
		$this->db->where('id_partner', $id_partner);
        return $this->db->delete($this->partners_table);
	}
}

