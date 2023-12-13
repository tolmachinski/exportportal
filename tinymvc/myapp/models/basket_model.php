<?php
/**
 * basket_model.php
 *
 * user's masket system model
 *
 * @author Litra Andrei
 * @deprecated in favor of \User_Basket_Model
 */
class Basket_Model extends TinyMVC_Model {

	private $obj;
	private $items_table = "items";
	private $users_table = "users";
	private $basket_table = "user_basket";
	private $variants_group_table = "item_cat_variants_group";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	public function add_to_basket($data){
		$this->db->insert($this->basket_table, $data);
		return $this->db->last_insert_id();
	}

	public function update_basket_item($id_basket_item, $data){
		$this->db->where('id_basket_item', $id_basket_item);
		return $this->db->update($this->basket_table, $data);
	}

	public function update_basket_items_quantity($items){
        $params = [];

		$sql = "UPDATE " . $this->basket_table . "
				SET quantity =
					CASE id_basket_item ";

		foreach ($items as $key => $quantity) {
			$case[] = "WHEN ? THEN ?";
            $params[] = $key;
            $params[] = $quantity;
        }

		if (empty($case)) {
			return false;
        }

		$sql .= implode(' ', $case);

		$sql .= " END WHERE id_basket_item IN (" . implode(',', array_fill(0, count($items), '?')) . ")";

        array_push($params, ...array_keys($items));

		return $this->db->query($sql, $params);
	}

	public function delete_basket_item($id_basket_item){
		$this->db->where('id_basket_item', $id_basket_item);
		return $this->db->delete($this->basket_table);
	}

    public function get_basket_item_simple($user, $id_basket_item)
    {
        $this->db->from($this->basket_table);
        $this->db->where("id_basket_item = ?", $id_basket_item);
        $this->db->where("id_user = ?", $user);

        return $this->db->get_one();
	}

	public function is_user_basket_item($user, $id_basket_item){
		$sql = "SELECT COUNT(*) as counter
			FROM " . $this->basket_table ."
			WHERE id_basket_item = ? AND id_user = ?";
		$rez = $this->db->query_one($sql, array($id_basket_item, $user));
		return $rez['counter'];
	}

	public function get_basket($conditions){
		$this->db->select("b.*, it.title, it.id_seller, it.min_sale_q, it.quantity as disp_q, it.samples, it.is_out_of_stock, p.photo_name, pc.country, it.id_cat");
		$this->db->from("{$this->basket_table} b");
		$this->db->join("item_photo p", "b.id_item = p.sale_id AND p.main_photo = 1", 'left');
		$this->db->join("items it", "b.id_item = it.id");
		$this->db->join("port_country pc", "it.p_country = pc.id");

		$by_seller = false;
		extract($conditions);

		if(isset($user)){
			$this->db->where("b.id_user = ?", (int) $user);
		}

		$this->db->groupby("b.id_basket_item");

		$records = $this->db->get();

		if($by_seller){
			$records = arrayByKey($records, 'id_seller', true);
		}

		return !empty($records) ? $records : array();
	}

	public function get_user_basket_item_key($id_user, $id_item, $basket_item_key){
		$sql = "SELECT *
				FROM $this->basket_table
				WHERE id_user = ? AND id_item = ? AND basket_item_key = ?";
		return $this->db->query_one($sql, array($id_user, $id_item, $basket_item_key));
	}

	public function get_basket_item($id_basket_item){
		$sql = "SELECT b.*, i.id_seller
			FROM " . $this->basket_table . " b
			LEFT JOIN items i ON b.id_item = i.id
			WHERE id_basket_item = ?
			LIMIT 1";
		return $this->db->query_one($sql, array($id_basket_item));
	}

	public function count_basket_items($id_user){
		$sql = "SELECT COUNT(*) as counter
			FROM " . $this->basket_table . "
			WHERE id_user = ? ";
		$rez = $this->db->query_one($sql, array($id_user));

		return $rez['counter'];
	}

	public function is_last_from_seller($id_user, $id_seller){
		$sql = "SELECT COUNT(*) as counter
				FROM " . $this->basket_table . " b
				LEFT JOIN items i ON i.id = b.id_item
				WHERE id_user = ? AND id_seller = ?";

		$temp = $this->db->query_one($sql, array($id_user, $id_seller));
		return !$temp['counter'];
	}

	public function get_basket_by_user($id_user = 0, $conditions = array()){
		$this->db->select("b.*, i.id_seller, i.weight, i.quantity as quantity_item, i.min_sale_q, i.max_sale_q, i.title, i.is_out_of_stock");
		$this->db->from("{$this->basket_table} b");
		$this->db->join("{$this->items_table} i", "b.id_item = i.id", "inner");
		$this->db->where("b.id_user = ?", (int) $id_user);

		extract($conditions);

		if(isset($id_seller)){
			$this->db->where("i.id_seller = ?", (int) $id_seller);
		}

		if(isset($id_basket_item)){
			$this->db->where("b.id_basket_item = ?", (int) $id_basket_item);
		}

		$records = $this->db->get();

		return !empty($records) ? $records : array();
	}
}
