<?php
class Cr_Expense_Reports_Model extends TinyMVC_Model {

    public $default_temp_folder = "temp/expense_reports";
    private $cr_expense_reports = "cr_expense_reports";
    private $users = "users";
    private $port_country = "port_country";
    private $zips = "zips";
    private $states = "states";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    public function insert_report($insert = array()) {
        if(empty($insert)) {
            return false;
        }

        return $this->db->insert($this->cr_expense_reports, $insert);
    }

    public function update_report($id_ereport = 0, $data = array()) {
        if (empty($data)){
            return false;
        }

        $this->db->where("id_ereport = ?", $id_ereport);
        return $this->db->update($this->cr_expense_reports, $data);
    }

    public function delete_report($id_ereport = 0) {
        $this->db->where("id_ereport = ?", (int) $id_ereport);
        return $this->db->delete($this->cr_expense_reports);
    }

    public function get_report($id_ereport = 0) {
        $this->db->select("*");
        $this->db->from($this->cr_expense_reports);
        $this->db->where("id_ereport = ?", $id_ereport);

        $result = $this->db->query_one();
        return !empty($result)?$result:array();
    }

    private function _ereports_params($conditions = array()){
        $this->db->from("{$this->cr_expense_reports} cer");
        $this->db->join("{$this->users} u", "cer.id_user = u.idu", "inner");
        $this->db->join("{$this->port_country} pc", "u.country = pc.id", "left");
        $this->db->join("{$this->states} st", "u.state = st.id", "left");
        $this->db->join("{$this->zips} z", "u.city = z.id", "left");

        extract($conditions);

        if(isset($refund_amount_from)) {
            $this->db->where("cer.ereport_refund_amount >= ?", $refund_amount_from);
        }

        if(isset($refund_amount_to)) {
            $this->db->where("cer.ereport_refund_amount <= ?", $refund_amount_to);
        }

        if(isset($created_from)) {
            $this->db->where("cer.ereport_date >= ?", $created_from);
        }

        if(isset($created_to)) {
            $this->db->where("cer.ereport_date <= ?", $created_to);
        }

        if(isset($updated_from)) {
            $this->db->where("cer.ereport_updated >= ?", $updated_from);
        }

        if(isset($updated_to)) {
            $this->db->where("cer.ereport_updated <= ?", $updated_to);
        }

        if(isset($status_filter)) {
            $this->db->where("cer.ereport_status = ?", $status_filter);
        }

        if(isset($id_user)) {
            $this->db->where("cer.id_user = ?", $id_user);
        }

        if (isset($removed)) {
            $this->db->where("cer.ereport_removed = ?", $removed);
        }

		if(isset($logged)){
            $this->db->where("u.logged = ?", $logged);
		}

        if (isset($id)) {
            $this->db->where("cer.id_ereport = ?", $id);
        } elseif (isset($reports_list)) {
            $this->db->in("cer.id_ereport", $reports_list);
        }

        if(!empty($keywords)){
            $words = explode(" ", $keywords);
            foreach($words as $word){
                if (mb_strlen($word) > 3) {
                    $this->db->where_raw(" (cer.ereport_title LIKE ? OR cer.ereport_description LIKE ?) ", array_fill(0, 2, '%' . $keywords . '%'));
                }
            }
        }
    }

    function get_reports($conditions) {
        $this->db->select("
            cer.*,
            u.logged, u.fname, u.lname, u.email, u.phone, u.phone_code, u.user_photo, u.user_group, u.user_type, u.country, u.address,
            IF(z.city != st.state AND u.state > 0, CONCAT_WS(', ', z.city, st.state), z.city) as user_city,
            pc.country as user_country, pc.zip
        ");

        $this->_ereports_params($conditions);

        $order_by = "cer.ereport_status ASC, cer.ereport_date DESC";
        if(isset($conditions['sort_by'])){
			foreach($conditions['sort_by'] as $sort_item){
				$sort_item = explode("-", $sort_item);
				$multi_order_by[] = $sort_item[0]." ".$sort_item[1];
			}

            if(!empty($multi_order_by)){
                $order_by = implode(",", $multi_order_by);
            }
        }

        $this->db->orderby($order_by);
		$this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);

        $records = $this->db->query_all();

        return empty($records) ? [] : $records;
    }

    function count_reports($conditions) {
        $this->db->select("COUNT(cer.id_ereport) as total_rows");
        $this->_ereports_params($conditions);
        $result = $this->db->query_one();

        return (int) $result["total_rows"];
    }
}
