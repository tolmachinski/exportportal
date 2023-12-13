<?php

/**
 * lib_inspection_agency_model.php
 *
 * library inspection agency model
 *
 * @author        Boinitchi Ion
 */

class Lib_Inspection_Agency_Model extends TinyMVC_Model{
    var $obj;
    private $library_inspection_agency = "library_inspection_agency";
    private $port_country = "port_country";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function get_all_inspection($conditions = array()){
        $order_by = ' lia.id_ia ASC ';

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
            $where[] = " (lia.country LIKE ? OR
                         lia.company LIKE ? OR
                         lia.address LIKE ? OR
                         lia.phone LIKE ? OR
                         lia.email LIKE ?) ";
            array_push($params, ...array_fill(0, 5, '%' . $keywords . '%'));
        }

        if(isset($country_id)){
            $where[] = " lia.id_country = ? ";
            $params[]= $country_id;
        }

        if(isset($exist_country)){
            $where[] = " lia.id_country != 0 ";
        }

        if(isset($type_record)){
            $where[] = " lia.type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " lia.is_visible = ? ";
            $params[] = $visible_record;
        }

        if (isset($is_country)){
            $operation = " = 0";
            if ($is_country){
                $operation = " != 0";
            }
            $where[] = " id_country {$operation}";
        }

        $sql = "SELECT lia.*, pc.country as country_port
                FROM $this->library_inspection_agency lia
                LEFT JOIN $this->port_country pc ON pc.id = lia.id_country";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        if (!empty($group_by)){
            $sql .= " GROUP BY {$group_by} ";
        }

        $sql .= " ORDER BY ".$order_by;

        if(isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= ' LIMIT ' . $start . ',' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    function get_inspection_count($conditions = array()){
        extract($conditions);

        $where = $params = [];
        if(isset($keywords)){
            $where[] = " (lia.country LIKE ? OR
                         lia.company LIKE ? OR
                         lia.address LIKE ? OR
                         lia.phone LIKE ? OR
                         lia.email LIKE ?) ";

            array_push($params, ...array_fill(0, 5, '%' . $keywords . '%'));
        }

        if(isset($country_id)){
            $where[] = " lia.id_country = ? ";
            $params[]= $country_id;
        }

        if(isset($exist_country)){
            $where[] = " lia.id_country != 0 ";
        }

        if(isset($type_record)){
            $where[] = " lia.type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " lia.is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->library_inspection_agency lia
                LEFT JOIN $this->port_country pc ON pc.id = lia.id_country";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $temp = $this->db->query_one($sql, $params);
        return $temp['counter'];
    }

    function get_inspection($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($id_record)){
            $where[] = " id_ia = ? ";
            $params[] = $id_record;
        }

        $sql = "SELECT *
                FROM $this->library_inspection_agency";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params);
    }

    function set_inspection($data){
        $this->db->insert($this->library_inspection_agency, $data);
        return $this->db->last_insert_id();
    }

    function set_rows_inspection($data){
        $this->db->insert_batch($this->library_inspection_agency, $data);
        return $this->db->getAffectableRowsAmount();
    }

    function update_inspection($id_record, $data){
        $this->db->where('id_ia', $id_record);
        return $this->db->update($this->library_inspection_agency, $data);
    }

    function check_inspection($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if (isset($email)) {
            $where[] = " email = ?";
            $params[] = $email;
        }

        if(isset($id_record)){
            $where[] = " id_ia = ? ";
            $params[]= $id_record;
        }

        if(isset($not_id_record)){
            $where[] = " id_ia != ? ";
            $params[]= $not_id_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        if (!empty($country_name)){
            $where[] = " country = ? ";
            $params[] = $country_name;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM " . $this->library_inspection_agency;

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function delete_inspection($conditions = array()){
        extract($conditions);

        if(isset($id_record))
            $this->db->where('id_ia', $id_record);

        if(isset($id_records))
            $this->db->in("id_ia", $id_records, false);

        if(isset($type_record))
            $this->db->where('type_record', $type_record);

        return $this->db->delete($this->library_inspection_agency);
    }

    public function update_country_inspection_agency($params = array()){
        if (empty($params)){
            return false;
        }

        extract($params);

        $replace_columns_from = explode(',', $replace_columns['from']);
        $replace_columns_from = array_map('trim', $replace_columns_from);

        $replace_columns_to = explode(',', $replace_columns['to']);
        $replace_columns_to = array_map('trim', $replace_columns_to);

        if (empty($replace_columns)){
            return false;
        }

        if (empty($replace_columns_from) || empty($replace_columns_to)){
            return false;
        }

        $sql = "UPDATE `{$this->library_inspection_agency}` SET ";

        $count_to = count($replace_columns_to) - 1;
        $count_from = count($replace_columns_from);
        $comma = ",";
        $count_cf = 0;
        $temp_value = "";
        foreach($replace_columns_to as $column){
            if ($count_cf == $count_from){
                $count_cf = 0;
            }

            $temp_value = $source_data[$replace_columns_from[$count_cf]];

            if ($count_to == 0){
                $comma = "";
            }

            $sql .= "`{$column}` = CASE
            WHEN `{$column}` = '{$condition['value']}' THEN '{$temp_value}'
            ELSE `{$column}` END{$comma}
            ";
            $count_cf++;
            $count_to--;
        }

        $where = "WHERE ";

        $colums_where = explode(',', $condition['column']);
        $colums_where = array_map('trim', $colums_where);

        $or_where = array();
        foreach($colums_where as $column_condition){
            $or_where[] = "`{$column_condition}` IN ('{$condition['value']}')";
        }

        $sql .= "WHERE " . implode(" OR ", $or_where);

        return $this->db->query_raw($sql, null, 0, null);
    }
}
