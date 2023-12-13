<?php

/**
 * lib_importer_exporter_model.php
 *
 * library importer exporter model
 *
 * @author        Boinitchi Ion
 */

class Lib_Importer_Exporter_Model extends TinyMVC_Model{
    var $obj;
    private $library_importer_exporter = "library_importer_exporter";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function get_all_imp_exp($conditions = array()){
        $order_by = ' id_ie ASC ';

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

            array_push($params, ...array_fill(0, 5, '%' . $keywords . '%'));
        }

        if(isset($type_record)){
            $where[] = " type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT *
                FROM $this->library_importer_exporter";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $sql .= " ORDER BY ".$order_by;
        if (isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= ' LIMIT ' . $start . ',' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    function get_imp_exp_count($conditions = array()){
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

        if(isset($type_record)){
            $where[] = " type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->library_importer_exporter";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $temp = $this->db->query_one($sql, $params);
        return $temp['counter'];
    }

    function get_imp_exp($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($id_record)){
            $where[] = " id_ie = ? ";
            $params[] = $id_record;
        }

        $sql = "SELECT *
                FROM $this->library_importer_exporter";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params);
    }

    function set_imp_exp($data){
        $this->db->insert($this->library_importer_exporter, $data);
        return $this->db->last_insert_id();
    }

    function set_rows_imp_exp($data){
        $this->db->insert_batch($this->library_importer_exporter, $data);
        return $this->db->getAffectableRowsAmount();
    }

    function update_imp_exp($id_record, $data){
        $this->db->where('id_ie', $id_record);
        return $this->db->update($this->library_importer_exporter, $data);
    }

    function check_imp_exp($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if (isset($email)) {
            $where[] = " email = ?";
            $params[] = $email;
        }

        if(isset($id_record)){
            $where[] = " id_ie = ? ";
            $params[]= $id_record;
        }

        if(isset($not_id_record)){
            $where[] = " id_ie != ? ";
            $params[]= $not_id_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM " . $this->library_importer_exporter;

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function delete_imp_exp($conditions = array()){
        extract($conditions);

        if(isset($id_record))
            $this->db->where('id_ie', $id_record);

        if(isset($id_records))
            $this->db->in("id_ie", $id_records, false);

        if(isset($type_record))
            $this->db->where('type_record', $type_record);

        return $this->db->delete($this->library_importer_exporter);
    }

}
