<?php

class Ec_B2b_Requests_Model extends TinyMVC_Model {
    const TYPE_SHORT_DECK = 'short deck';
    const TYPE_BUSINESS_PLAN = 'business plan';

    const DOC_SHORT_DECK = 'public/css/style.css';
    const DOC_BUSINESS_PLAN = 'public/css/style.css';

    private $ecb2b_requests_table = "ecb2b_requests";

    function get_count(){
        $temp = $this->db->query_one("SELECT COUNT(*) as counter FROM {$this->ecb2b_requests_table}");
        return $temp['counter'];
    }

    function update($id, $data){
        $this->db->where('id', $id);
        return $this->db->update($this->ecb2b_requests_table, $data);
    }

    function insert($data){
        $this->db->insert($this->ecb2b_requests_table, $data);
        return $this->db->last_insert_id();
    }

    function get_requests($conditions = array()) {
        $where = array();
        $params = array();

        extract($conditions);

        if (isset($search)) {
            $where[] = " full_name LIKE ? OR email LIKE ? OR phone LIKE ?";
            array_push($params, ...array_fill(0, 3, '%' . $search . '%'));
        }

        if (isset($request_from)) {
            $where[] = " DATE(date_created) >= ? ";
            $params[] = $request_from;
        }

        if (isset($request_to)) {
            $where[] = " DATE(date_created) <= ? ";
            $params[] = $request_to;
        }

        if (isset($processed_from)) {
            $where[] = " DATE(date_processed) >= ? ";
            $params[] = $processed_from;
        }

        if (isset($processed_to)) {
            $where[] = " DATE(date_processed) <= ? ";
            $params[] = $processed_to;
        }

        if (isset($type)) {
            $where[] = " type = ? ";
            $params[] = $type;
        }

        if (isset($id)) {
            $where[] = " id = ? ";
            $params[] = $id;
        }


        $sql = "SELECT * FROM {$this->ecb2b_requests_table}";

        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ' , $where);
        }


        if (isset($sort_by) && isset($sort_by_type)) {
            $sql .= ' ORDER BY ' . $sort_by . ' ' . $sort_by_type;
        }

        if (isset($start_from) && isset($limit)) {
            $start_from = (int) $start_from;
            $limit = (int) $limit;
            $sql .= ' LIMIT ' . $start_from . ', ' . $limit;
        }

        return isset($id) ? $this->db->query_one($sql, $params) : $this->db->query_all($sql, $params);
    }

    function count_requests($conditions = array()) {
        $where = array();
        $params = array();

        extract($conditions);

        if (isset($search)) {
            $where[] = " full_name LIKE ? OR email LIKE ? OR phone LIKE ?";
            array_push($params, ...array_fill(0, 3, '%' . $search . '%'));
        }

        if (isset($request_from)) {
            $where[] = " DATE(date_created) >= ? ";
            $params[] = $request_from;
        }

        if (isset($request_to)) {
            $where[] = " DATE(date_created) <= ? ";
            $params[] = $request_to;
        }

        if (isset($processed_from)) {
            $where[] = " DATE(date_processed) >= ? ";
            $params[] = $processed_from;
        }

        if (isset($processed_to)) {
            $where[] = " DATE(date_processed) <= ? ";
            $params[] = $processed_to;
        }

        if (isset($type)) {
            $where[] = " type = ? ";
            $params[] = $type;
        }

        if (isset($id)) {
            $where[] = " id = ? ";
            $params[] = $id;
        }

        $sql = "SELECT COUNT(*) as `AGGREGATE` FROM {$this->ecb2b_requests_table}";
        if (count($where)) {
            $sql = $sql . ' WHERE ' . implode(' AND ' , $where);
        }
        $counter = $this->db->query_one($sql, $params);

        return !empty($counter['AGGREGATE']) ? (int) $counter['AGGREGATE'] : 0;
    }
}
