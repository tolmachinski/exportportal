<?php

class Shippers_photos_Model extends TinyMVC_Model {

    private $orders_shippers_pictures_table = 'orders_shippers_pictures';
    public $pictures_path = 'public/img/shippers';

	function insert_pictures_batch($data){
		if (empty($data)) {
			return false;
        }

		$this->db->insert_batch($this->orders_shippers_pictures_table, $data);
		return $this->db->getAffectableRowsAmount();
    }

	function get_pictures($conditions = array()){
		$order_by = ' id_picture ASC ';

        extract($conditions);

        $where = $params = [];

        if(isset($id_shipper)){
            $where[] = " id_shipper = ? ";
            $params[] = $id_shipper;
        }

        $sql = "SELECT * FROM {$this->orders_shippers_pictures_table}";

        if (!empty($pictures)) {
            $pictures = getArrayFromString($pictures);
            $where[] = " picture IN (" . implode(',', array_fill(0, count($pictures), '?')) . ") ";
            array_push($params, ...$pictures);
        }

        if (!empty($where)) {
            $sql = $sql . ' WHERE ' . implode(' AND ' , $where);
        }

		$sql .= " ORDER BY {$order_by}";

		if (isset($start)) {
			$sql .= ' LIMIT ' . $start . ', ' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

	function get_pictures_count($conditions = array())
    {
        extract($conditions);

        if (!empty($id_shipper)) {
            $this->db->where('id_shipper', $id_shipper);
        }

        $this->db->select('count(*) as counter');
        $this->db->limit(1);

        return $this->db->get_one($this->orders_shippers_pictures_table)['counter'];
    }

	function get_picture($id_shipper, $id_picture){
        return $this->db->query_one("SELECT * FROM $this->orders_shippers_pictures_table WHERE id_shipper = ? AND id_picture = ? ", array($id_shipper, $id_picture));
    }

	function delete_picture($id_picture){
        $this->db->where('id_picture', $id_picture);
        return $this->db->delete($this->orders_shippers_pictures_table);
    }

    function update_picture_name(int $id_shipper, string $old_picture_name, string $new_picture_name){
        $this->db->where('id_shipper', $id_shipper);
        $this->db->where('picture', $old_picture_name);

        return $this->db->update($this->orders_shippers_pictures_table, array('picture' => $new_picture_name));
    }
}

