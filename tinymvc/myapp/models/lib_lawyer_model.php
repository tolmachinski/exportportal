<?php

/**
 * lib_lawyer_model.php
 *
 * library lawyer model
 *
 * @author        Boinitchi Ion
 */

class Lib_Lawyer_Model extends TinyMVC_Model{
    var $obj;
    private $library_lawyers = "library_lawyers";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function get_all_lawyers($conditions = array()){
        $order_by= ' id_law ASC ';

        extract($conditions);
        $where = $params = [];

        if(isset($sort_by)){
            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if(isset($keywords)){
            $where[] = " (company LIKE ? OR
                         address LIKE ? OR
                         phone LIKE ? OR
                         email LIKE ? OR
                         url_site LIKE ?) ";

            array_push($params , ...array_fill(0, 5, '%' . $keywords . '%'));
        }

        if(isset($type_record)){
            $where[] = " type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        if (isset($is_country)){
            $operation = " = 0";
            if ($is_country){
                $operation = " != 0";
            }
            $where[] = " id_country {$operation}";
        }

        $sql = "SELECT *
                FROM $this->library_lawyers";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $sql .= " ORDER BY ".$order_by;

        if(isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= ' LIMIT ' . $start . ',' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    function get_lawyer_count($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($keywords)){
            $where[] = " (company LIKE ? OR
                         address LIKE ? OR
                         phone LIKE ? OR
                         email LIKE ? OR
                         url_site LIKE ?) ";

            array_push($params, ...array_fill(0, 5, '%' . $keywords . '%'));
        }

        if(isset($country_id)){
            $where[] = " id_country = ? ";
            $params[]= $country_id;
        }

        if(isset($exist_country)){
            $where[] = " id_country != 0 ";
        }

        if(isset($type_record)){
            $where[] = " type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->library_lawyers";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $temp = $this->db->query_one($sql, $params);
        return $temp['counter'];
    }

    function get_lawyer($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($id_record)){
            $where[] = " id_law = ? ";
            $params[] = $id_record;
        }

        $sql = "SELECT *
                FROM $this->library_lawyers";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params);
    }

    function set_lawyer($data){
        $this->db->insert($this->library_lawyers, $data);
        return $this->db->last_insert_id();
    }

    function set_rows_lawyer($data){
        $this->db->insert_batch($this->library_lawyers, $data);
        return $this->db->getAffectableRowsAmount();
    }

    function update_lawyer($id_record, $data){
        $this->db->where('id_law', $id_record);
        return $this->db->update($this->library_lawyers, $data);
    }

    function check_lawyer($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if (isset($email)) {
            $where[] = " email = ?";
            $params[] = $email;
        }

        if(isset($id_record)){
            $where[] = " id_law = ? ";
            $params[]= $id_record;
        }

        if(isset($not_id_record)){
            $where[] = " id_law != ? ";
            $params[]= $not_id_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM " . $this->library_lawyers;

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function delete_lawyer($conditions = array()){
        extract($conditions);

        if(isset($id_record))
            $this->db->where('id_law', $id_record);

        if(isset($id_records))
            $this->db->in("id_law", $id_records, false);

        if(isset($type_record))
            $this->db->where('type_record', $type_record);

        return $this->db->delete($this->library_lawyers);
    }

}
