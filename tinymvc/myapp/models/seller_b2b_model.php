<?php

class Seller_b2b_Model extends TinyMVC_Model
{
	private $seller_b2b_table = "seller_b2b";

	function get_seller_b2b($id_seller = 0){
        $this->db->select("*");
        $this->db->from($this->seller_b2b_table);

        if (isset($id_seller)) {
            $this->db->where("id_seller = ?", (int) $id_seller);
        }

        return $this->db->query_one();
	}

	public function update_seller_b2b($id_seller, $data){
		$sql = "INSERT INTO $this->seller_b2b_table (id_seller, {$data['block_name']}) VALUES (?,?)
				  ON DUPLICATE KEY UPDATE {$data['block_name']}=?";
		return $this->db->query($sql, [$id_seller, $data['value'], $data['value']]);
	}
}
