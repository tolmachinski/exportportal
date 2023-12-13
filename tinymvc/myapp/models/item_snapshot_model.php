<?php
/**
 * item_snapshot_model.php
 *
 * Items snapshot system model
 *
 * @author Andrew Cravciuc
 */


class Item_Snapshot_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;

    private $snapshots_table = "item_snapshots";
    private $items_table = "items";
    private $item_ordered_table = "item_ordered";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	/**
	* Items snapshot functions
	*/
    public function insert_item_snapshot($data = array()){
    	if(empty($data))
    		return false;

        $this->db->insert($this->snapshots_table, $data);
        return $this->db->last_insert_id();
    }

    public function update_item_snapshots($id_item, $type, $data = array()){
    	if(empty($data))
    		return false;

		$this->db->where('id_item', $id_item);
		$this->db->where('type', $type);
        return $this->db->update($this->snapshots_table, $data);
	}

    /**
	* Get the last snapshot for one item
	*/
    public function get_last_item_snapshot($id_item){
        $sql = "SELECT *
				FROM $this->snapshots_table
				WHERE id_item = ? AND type = 'item'
				ORDER BY id_snapshot DESC";

		return $this->db->query_one($sql, array($id_item));
	}

	public function get_latest_snapshots_for_items(array $items): array
	{
		if (empty($items)) {
            return array();
        }

        $this->db->select("*");
        $this->db->from("{$this->snapshots_table} AS `SNAPSHOTS`");
		$this->db->where_raw(sprintf("`SNAPSHOTS`.`id_item` IN (%s)", implode(', ', array_fill(0, count($items), '?'))), $items);
		$this->db->where('type = ?', 'item');
		$this->db->where('is_last_snapshot = ?', 1);

		return array_filter((array) $this->db->query_all());
	}

    /**
	* Get unused snapshots for one item
	*/
    public function get_unused_item_snapshot($id_item){
        $sql = "SELECT GROUP_CONCAT(its.id_snapshot) as snapshots
				FROM $this->snapshots_table its
				LEFT OUTER JOIN $this->item_ordered_table ito
				ON its.id_snapshot = ito.id_snapshot
				WHERE  ito.id_snapshot IS null AND type = 'item' AND its.id_item = ?";
		$rez = $this->db->query_one($sql, array($id_item));
		return $rez['snapshots'];
    }
    /**
	* DELETE unused snapshots for one item
	*/
    public function delete_unused_item_snapshots($id_item, $snapshots){
        $this->db->in('id_snapshot', $snapshots, true);
        $this->db->where('id_item', $id_item);
    	return $this->db->delete($this->snapshots_table);
    }
}

