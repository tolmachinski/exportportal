<?php

/**
 * invoice_model.php
 *
 * invoices templates model
 *
 * @author Litra Andrei
 */
class Invoices_Model extends TinyMVC_Model {

    // hold the current controller instance
    var $obj;
    private $item_order_invoices_table = "item_order_invoices";
    private $item_order_invoices_table_primary_key = "id_invoice";
    private $item_orders_table = "item_orders";
    private $users_table = "users";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

	/**
	 * Returns the name of the invoices table name.
	 *
	 * @return string
	 */
	public function get_invoices_table(): string
	{
		return $this->item_order_invoices_table;
	}

	/**
	 * Returns the name of the invoices table primary key.
	 *
	 * @return string
	 */
	public function get_invoices_table_primary_key(): string
	{
		return $this->item_order_invoices_table_primary_key;
	}

    public function set_invoice($data) {
        if (!count($data))
            return false;
        $this->db->insert($this->item_order_invoices_table, $data);
        return $this->db->last_insert_id();
    }

    public function set_more_invoice($data) {
        $this->db->insert_batch($this->item_order_invoices_table, $data);
        return $this->db->getAffectableRowsAmount();
    }

    public function get_invoices($conditions) {
        $where = array();
        $params = array();
        $order_by = "ii.date_create DESC";

        extract($conditions);

        if (isset($id_order)) {
            $where[] = " ii.id_order = ? ";
            $params[] = $id_order;
        }

        if (isset($id_seller)) {
            $where[] = " us.idu = ? ";
            $params[] = $id_seller;
        }

        if (isset($id_buyer)) {
            $where[] = " u.idu = ? ";
            $params[] = $id_buyer;
        }

        if (isset($invoice_status)) {
            $where[] = " ii.status = ? ";
            $params[] = $invoice_status;
        }

        $sql = "SELECT ii.*, io.status as id_status, io.ship_invoice, CONCAT(u.fname, ' ', u.lname) AS buyer_name, u.idu as id_buyer,
                    CONCAT(us.fname, ' ', us.lname) AS seller_name, us.idu as id_seller, cb.id_company, cb.name_company, cb.index_name, cb.logo_company
                    FROM " . $this->item_order_invoices_table . " ii
                    LEFT JOIN " . $this->item_orders_table . " io ON ii.id_order = io.id
                    LEFT JOIN " . $this->users_table . " u ON io.id_buyer = u.idu
                    LEFT JOIN " . $this->users_table . " us ON io.id_seller = us.idu
                    LEFT JOIN company_base cb ON io.id_seller = cb.id_user ";

        if (count($where))
            $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " GROUP BY ii.id_invoice ";
        $sql .= " ORDER BY " . $order_by;

        return $this->db->query_all($sql, $params);
    }

    public function get_invoice($id_invoice) {

        $sql = "SELECT ii.*, CONCAT(u.fname, ' ', u.lname) AS buyer_name, u.idu as id_buyer,
                            CONCAT(us.fname, ' ', us.lname) AS seller_name, us.idu as id_seller
                    FROM " . $this->item_order_invoices_table . " ii
                    LEFT JOIN " . $this->item_orders_table . " io ON ii.id_order = io.id
                    LEFT JOIN " . $this->users_table . " u ON io.id_buyer = u.idu
                    LEFT JOIN " . $this->users_table . " us ON io.id_seller = us.idu
                    WHERE ii.id_invoice = ?";

        return $this->db->query_one($sql, array($id_invoice));
	}

	public function get_base_invoice($invoice_id)
	{
		$this->db->select('*');
		$this->db->from($this->item_order_invoices_table);
		$this->db->where('id_invoice = ?', (int) $invoice_id);


		return $this->db->query_one();
	}

    public function get_order_invoice($id_order) {
        $sql = "SELECT *
                    FROM " . $this->item_order_invoices_table . "
                    WHERE id_order = ?";
        return $this->db->query_one($sql, array($id_order));
    }

    public function get_last_invoice($conditions) {
        $where = array();
        $params = array();
        $order_by = " date_create DESC";

        extract($conditions);

        if (isset($id_order)) {
            $where[] = " id_order = ? ";
            $params[] = $id_order;
        }

        $sql = "SELECT *
                FROM " . $this->item_order_invoices_table;

        if (count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $sql .= " ORDER BY " . $order_by;

        return $this->db->query_one($sql, $params);
    }

    public function count_order_invoices_by_status($id_order) {
        $sql = "SELECT
                        COUNT(*) as created,
                        SUM(if(status = 'accepted', 1, 0)) AS accepted,
                        SUM(if(status = 'declined', 1, 0)) AS declined,
                        SUM(if(status = 'sended', 1, 0)) AS sended,
                        SUM(if(status = 'saved', 1, 0)) AS saved
                    FROM " . $this->item_order_invoices_table . "
                    WHERE id_order = ? ";
        return $this->db->query_one($sql, array($id_order));
    }

    public function update_invoice($id, $data = array()) {
        if (empty($data))
            return false;

        $this->db->where('id_invoice', $id);
        return $this->db->update($this->item_order_invoices_table, $data);
    }

    public function get_invoice_by_status($id_order, $status = 'saved') {
        $sql = "SELECT *
                    FROM " . $this->item_order_invoices_table . "
                    WHERE id_order = ? AND status = ?";
        return $this->db->query_one($sql, array($id_order, $status));
    }

    public function get_invoice_count($conditions) {
        $where = array();
        $params = array();

        extract($conditions);

        if (isset($id_order)) {
            $where[] = " id_order = ? ";
            $params[] = $id_order;
        }

        if (isset($invoice_status)) {
            $where[] = " status = ? ";
            $params[] = $invoice_status;
        }
        $sql = "SELECT COUNT(*) as counter
                    FROM " . $this->item_order_invoices_table;

        if (count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }
}
