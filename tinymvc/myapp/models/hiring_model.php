<?php
/**
 *
 *
 * model
 *
 * @author
 */

class Hiring_Model extends TinyMVC_Model {

    var $obj;
    private $hiring_table = "epteam_vacancy";
    private $offices_table = "epteam_offices";
    private $country_table = "port_country";
    public $files_path = "public/img/vacancies";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    public function set_vacancy($data){
        if(!count($data))
        	return false;
        $this->db->insert($this->hiring_table, $data);
        return $this->db->last_insert_id();
    }

    public function get_vacancy($id_hiring){
        $sql = "SELECT h.*, o.name_office, pc.country, o.phone_office, o.email_office, o.address_office
                FROM " . $this->hiring_table . " h
                LEFT JOIN ".$this->offices_table." o ON h.id_office = o.id_office
				LEFT JOIN ".$this->country_table." pc ON h.id_country = pc.id
                WHERE id_vacancy = ? ";
        return $this->db->query_one($sql, array($id_hiring));
    }

    public function exist_vacancy($value){
		$sql = "SELECT COUNT(*) as exist
				FROM " . $this->hiring_table . "
				WHERE id_vacancy = ?";
		$rez = $this->db->query_one($sql, array($value));
		return $rez['exist'];
	}

    private function _get_vacancies_params($conditions = array()){
        $this->db->from("{$this->hiring_table} h");
        $this->db->join("{$this->offices_table} o", 'h.id_office = o.id_office', 'left');
        $this->db->join("{$this->country_table} pc", 'pc.id = h.id_country', 'left');

        extract($conditions);

        if (isset($visible)) {
            $this->db->where('h.visible_vacancy = ?', (int) $visible);
        }

        if (isset($id_office)) {
            $this->db->where('h.id_office = ?', (int) $id_office);
        }

        if (isset($id_country)) {
            $this->db->in('h.id_country', array((int) $id_country, 0));
        }

        if (isset($not_id_vacancy)) {
            $this->db->where('h.id_vacancy != ?', (int) $not_id_vacancy);
        }
    }

    public function get_vacancies($conditions = array()){
        $order_by = 'h.date_vacancy ASC';

        $this->db->select("h.*, o.name_office, pc.country");

        $this->_get_vacancies_params($conditions);

        if (isset($conditions['sort_by'])) {
            $multi_order_by = array();
            foreach ($conditions['sort_by'] as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            if (!empty($multi_order_by)) {
                $order_by = implode(',', $multi_order_by);
            }
        }

        $this->db->orderby($order_by);

		if(isset($limit)) {
            $this->db->limit($limit);
        } elseif(isset($conditions['per_p'], $conditions['start'])){
            $this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);
        }

        return $this->db->query_all();
    }

    public function count_vacancies($conditions = array()){
        $this->db->select('COUNT(h.id_vacancy) as total_rows');

        $this->_get_vacancies_params($conditions);

        $records = $this->db->query_one();

        return $records['total_rows'];
    }

    public function update_vacancy($id_vacancy, $data){
        $this->db->where('id_vacancy', $id_vacancy);
        return $this->db->update($this->hiring_table, $data);
    }

    public function delete_vacancy($id_vacancy){
        $this->db->where('id_vacancy', $id_vacancy);
        return $this->db->delete($this->hiring_table);
    }


}
