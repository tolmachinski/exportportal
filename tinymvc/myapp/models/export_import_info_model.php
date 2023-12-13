<?php

class Export_Import_Info_Model extends TinyMVC_Model {

    private $export_import_info_table = 'export_import_info';

    public function get_info($conditions) {
        $where = array();
        $params = array();
        $select = '*';
        $order_by = 'pc.country ASC';
        extract($conditions);

        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 10);

        if (isset($sort_by)) {
            $order_by = implode(',', $sort_by);
        }

        if (isset($id)) {
            $where[] = 'ei.id = ?';
            $params[] = $id;
        }

        if (isset($keywords)) {
            $where[] = " pc.country LIKE ? ";
            $params[] = '%' . $keywords . '%';
        }

        if (isset($abr)) {
            $where[] = " pc.abr3 = ? ";
            $params[] = strtoupper($abr);
        }

        if (isset($id_country)) {
            $where[] = " ei.id_country = ? ";
            $params[] = strtoupper($id_country);
        }

        $sql = "
            SELECT $select
            FROM {$this->export_import_info_table} ei
            LEFT JOIN port_country pc ON pc.id = ei.id_country
        ";

        if (count($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY $order_by LIMIT $start, $per_p";

        $returnOne = isset($id) || isset($abr) || isset($id_country);

        return $returnOne ? $this->db->query_one($sql, $params) : $this->db->query_all($sql, $params);
    }

    public function get_info_count($conditions) {
        extract($conditions);
        $where = $params = [];

        if (isset($keywords)) {
            $where[] = " pc.country LIKE ? ";
            $params[] = '%' . $keywords . '%';
        }

        $sql = "
            SELECT COUNT(ei.id) as counter
            FROM {$this->export_import_info_table} ei
            LEFT JOIN port_country pc ON pc.id = ei.id_country
        ";

        if (count($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $temp = $this->db->query_one($sql, $params);
        return $temp['counter'];
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->export_import_info_table, $data);
    }
}
