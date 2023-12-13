<?php
/**
 * @deprecated in favor of \International_Shippers_Model
 */
class Ishippers_Model extends TinyMVC_Model{
	private $international_shippers_table = 'international_shippers';
	private $seller_shipper_ipartners_table = 'seller_shipper_ipartners';
	private $international_shippers_table_primary_key = 'id_shipper';

	/**
	 * Returns the international shippers table name.
	 *
	 * @return string
	 */
	public function get_shippers_table(): string
	{
		return $this->international_shippers_table;
	}

	/**
	 * Returns the international shippers table primary key.
	 *
	 * @return string
	 */
	public function get_shippers_table_primary_key(): string
	{
		return $this->international_shippers_table_primary_key;
	}

	function insert_shipper($data){
		$this->db->insert($this->international_shippers_table, $data);
		return $this->db->last_insert_id();
	}

	function update_shipper($data, $id_shipper){
		$this->db->where('id_shipper', $id_shipper);
		return $this->db->update($this->international_shippers_table, $data);
	}

	function delete_shipper($id_shipper){
		$this->db->where('id_shipper', $id_shipper);
		return $this->db->delete($this->international_shippers_table);
	}

	function get_shipper($id_shipper){
		$sql = "SELECT *
				FROM  $this->international_shippers_table
				WHERE id_shipper = ?";

		return $this->db->query_one($sql, array($id_shipper));
	}

	/**
	 * Checks if shippers with provided ID exists in the table.
	 */
	public function has_shipper(int $shipper_id): bool
	{
		if (empty($shipper_id)) {
            return false;
        }

        $this->db->select('COUNT(*) AS `AGGREGATE`');
        $this->db->from("`{$this->international_shippers_table}` as `SHIPPERS`");
		$this->db->where('`SHIPPERS`.`id_shipper` = ?', $shipper_id);
		if (!($counter = $this->db->query_one())) {
			return false;
		}

		return (bool) (int) $counter['AGGREGATE'] ?? 0;
	}

	function get_shipper_by_name($shipper_name){
		$sql = "SELECT *
				FROM  $this->international_shippers_table
				WHERE shipper_name = ?";

		return $this->db->query_one($sql, array($shipper_name));
	}

	function get_shippers($conditions = array()){
		$where = array();
		$params = array();
        $order_by = " id_shipper ASC ";

		extract($conditions);

		if(isset($shippers_list)){
            $shippers_list = getArrayFromString($shippers_list);
			$where[] = " id_shipper IN (" . implode(',', array_fill(0, count($shippers_list), '?')) . ") ";
            array_push($params, ...$shippers_list);
		}

		$sql = "SELECT *
				FROM $this->international_shippers_table";

		if(count($where))
			$sql .= " WHERE " . implode(" AND " , $where);


		if(isset($order_by))
			$sql .= " ORDER BY " . $order_by;

		if(isset($start, $per_p)){
            $start = (int) $start;
            $per_p = (int) $per_p;
			$sql .= " LIMIT " . $start . "," . $per_p;
		}
		return $this->db->query_all($sql, $params);
	}

	function get_count_shippers($conditions = array()){
		$where = array();
		$params = array();

		extract($conditions);

		if(isset($shippers_list)){
            $shippers_list = getArrayFromString($shippers_list);
			$where[] = " id_shipper IN (" . implode(',', array_fill(0, count($shippers_list), '?')) . ") ";
            array_push($params, ...$shippers_list);
		}

		$sql = "SELECT COUNT(*) as counter
				FROM $this->international_shippers_table ";

		if(count($where))
			$sql .= " WHERE " . implode(" AND " , $where);

		$rez = $this->db->query_one($sql, $params);
		return $rez['counter'];
	}

	function get_seller_shipper_ipartners($id_seller){
		$sql = "SELECT ssp.*, ish.*
				FROM $this->seller_shipper_ipartners_table ssp
				LEFT JOIN $this->international_shippers_table ish ON ssp.id_shipper = ish.id_shipper
				WHERE id_seller = ?";

		return $this->db->query_all($sql, array($id_seller));
	}

	function get_partnership($id_shipper, $id_seller){
		$sql = "SELECT *
				FROM $this->seller_shipper_ipartners_table
				WHERE id_shipper = ? AND id_seller = ?";

		return $this->db->query_one($sql, array($id_shipper, $id_seller));
	}

	function delete_partnership($id_partner){
		$this->db->where('id_partner', $id_partner);
		return $this->db->delete($this->seller_shipper_ipartners_table);
	}

	function insert_partnership($data){
		$this->db->insert($this->seller_shipper_ipartners_table, $data);
		return $this->db->last_insert_id();
	}
}

