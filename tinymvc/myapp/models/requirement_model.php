<?php
/**
 * requirement_model.php
 * @author
 */

class Requirement_Model extends TinyMVC_Model
{
    private $requirement_table = 'customs_requirements';
    private $port_country_table = 'port_country';

    public function set_requirement($data) {
        return $this->db->insert($this->requirement_table, $data);
    }

    public function get_requirement($conditions = array()) {
        extract($conditions);

        $where = $params = [];

        if (isset($country)) {
            $where[] = " id_country = ? ";
            $params[] = $country;
        }

        if (isset($id_record)) {
            $where[] = " id_customs_req = ? ";
            $params[] = $id_record;
        }

        if (isset($visible)) {
            $where[] = " visible = ? ";
            $params[] = $visible;
        }

        $sql = "SELECT * FROM $this->requirement_table";

        if (count($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return $this->db->query_one($sql, $params);
    }

    public function exist_requirement_by_condition($conditions = array()) {
        extract($conditions);

        $where = $params = [];

        if (isset($country)) {
            $where[] = " id_country = ? ";
            $params[] = $country;
        }

        if (isset($not_id_record)) {
            $where[] = " id_customs_req != ? ";
            $params[] = $not_id_record;
        }

        $sql = "SELECT COUNT(*) as counter FROM " . $this->requirement_table;

        if (count($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

    public function get_requirements($conditions = array()) {
        $page = 0;
        $per_p = 20;
        $order_by = "date DESC";
        $rel = "";

        extract($conditions);

        $where = $params = [];

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (isset($country)) {
            $where[] = " cat.id_country = ? ";
            $params[] = $country;
        }

        if (isset($visible)) {
            $where[] = " cat.visible = ? ";
            $params[] = $visible;
        }

        if (isset($keywords)) {
            $order_by = $order_by . ", REL DESC";
            $where[] = " MATCH (cat.customs_text) AGAINST (?)";
            $params[] = $keywords;
            $rel = " , MATCH (cat.customs_text) AGAINST (?) as REL";
            array_unshift($params, $keywords);
        }

        $sql = "SELECT cat.*, pc.country " . $rel . "
                FROM " . $this->requirement_table . " cat
                INNER JOIN " . $this->port_country_table . " pc ON cat.id_country = pc.id";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY " . $order_by;

        if (!isset($count)) {
            $count = $this->counter_by_conditions($conditions);
        }

        $pages = ceil($count / $per_p);

        if (!isset($start)) {
            if ($page > $pages) $page = $pages;
            $start = ($page - 1) * $per_p;

            if ($start < 0) $start = 0;
        }

        $sql .= " LIMIT " . $start;

        if ($per_p > 0) {
            $sql .= "," . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    public function counter_by_conditions($conditions = array()) {
        extract($conditions);

        $where = $params = [];

        if (isset($country)) {
            $where[] = " id_country = ? ";
            $params[] = $country;
        }

        if (isset($visible)) {
            $where[] = " visible = ? ";
            $params[] = $visible;
        }

        if (isset($keywords)) {
            $where[] = " MATCH (customs_text) AGAINST (?)";
            $params[] = $keywords;
        }

        $sql = "SELECT COUNT(*) as counter FROM " . $this->requirement_table;

        if (count($where)) {
            $sql .= " WHERE " . implode(" AND", $where);
        }

        return $this->db->query_one($sql, $params)['counter'];
    }

    public function update_requirement($id_record, $data) {
        $this->db->where('id_customs_req', $id_record);
        return $this->db->update($this->requirement_table, $data);
    }

    public function delete_requirement($id_record) {
        $this->db->where('id_customs_req', $id_record);
        return $this->db->delete($this->requirement_table);
    }

    public function get_countries_requirements(){
        $sql = "SELECT pc.*
        FROM {$this->port_country_table} pc
        INNER JOIN {$this->requirement_table} cr ON pc.id = cr.id_country";
        return $this->db->query_all($sql);
    }
}
