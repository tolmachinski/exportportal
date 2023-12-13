<?php

/**
 * lib_consulates_model.php
 *
 * library consulates model
 *
 * @author        Boinitchi Ion
 */

class Lib_Consulates_Model extends TinyMVC_Model{
    var $obj;
    private $library_consulates = "library_consulates";
    private $port_country = "port_country";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function set_consulate($data){
        $this->db->insert($this->library_consulates, $data);
        return $this->db->last_insert_id();
    }

    function set_rows_consulates($data){
        $this->db->insert_batch($this->library_consulates, $data);
        return $this->db->getAffectableRowsAmount();
    }

    function update_consulate($id_record, $data){
        $this->db->where('id_consulate', $id_record);
        return $this->db->update($this->library_consulates, $data);
    }

    function get_countries_consulates($conditions = array()){
        $group_by = "country_main";
        extract($conditions);

        $where = $params = [];

        if(isset($keywords)){
            $where[] = " ( mission_name LIKE ? ) ";
            $params[] = '%' . $keywords . '%';
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        if (isset($country_status)){
            unset($is_country, $is_consulates);

            $operation = " = 0";
            if ($is_consulates){
                $operation = " != 0";
            }
            $where[] = " (id_country {$operation} OR id_country_cons {$operation}) ";
        }

        if (isset($is_country)){
            $operation = " = 0";
            if ($is_country){
                $operation = " != 0";
            }
            $where[] = " id_country {$operation}";
        }

        if (isset($is_consulates)){
            $operation = " = 0";
            if ($is_consulates){
                $operation = " != 0";
            }
            $where[] = " id_country_cons {$operation}";
            $group_by = "country_consulate";
        }

        $sql = "SELECT *
                FROM $this->library_consulates ";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY {$group_by} ";

        return $this->db->query_all($sql, $params);
    }

    function get_all_consulates($conditions = array()){
        $order_by = ' con.id_consulate ASC ';

        extract($conditions);
        $where   = $params  = [];

        if(isset($sort_by)){
            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0].' '.$sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if(isset($keywords)){
            $where[] = " (con.mission_name LIKE ? OR
                         con.head LIKE ? OR
                         con.address LIKE ? OR
                         con.phone LIKE ? OR
                         con.email LIKE ? OR
                         con.url_site LIKE ?) ";
            array_push($params, ...array_fill(0, 6, '%' . $keywords . '%'));
        }

        if(isset($not_country)){
            $where[] = " (con.id_country = 0 OR id_country_cons = 0) ";
        }

        if(isset($main_country)){
            $where[] = " con.id_country = ? ";
            $params[]= $main_country;
        }

        if(isset($main_country_exist)){
            $where[] = " con.id_country != 0 ";
        }

        if(isset($consulate_country)){
            $where[] = " con.id_country_cons = ? ";
            $params[]= $consulate_country;
        }

        if(isset($consulate_countr_exist)){
            $where[] = " con.id_country_cons != 0 ";
        }

        if(isset($type_record)){
            $where[] = " con.type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " con.is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT con.*, pc.country as country_port, pc_con.country as country_port_consulate
                FROM $this->library_consulates con
                LEFT JOIN $this->port_country pc ON pc.id = con.id_country
                LEFT JOIN $this->port_country pc_con ON pc_con.id = con.id_country_cons";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        if(!empty($group_by)){
            $sql .= " GROUP BY $group_by ";
        }

        $sql .= " ORDER BY $order_by ";

        if(isset($start, $per_p)){
            $start = (int) $start;
            $per_p = (int) $per_p;

            $sql .= " LIMIT $start, $per_p ";
        }

        return $this->db->query_all($sql, $params);
    }

    function get_consulate($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($id_record)){
            $where[] = " id_consulate = ? ";
            $params[] = $id_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT *
                FROM $this->library_consulates";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params);
    }

    function check_consulates($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($email)){
            $where[] = " email = ?";
            $params[] = $email;
        }

        if(isset($id_record)){
            $where[] = " id_consulate = ? ";
            $params[]= $id_record;
        }

        if(isset($not_id_record)){
            $where[] = " id_consulate != ? ";
            $params[]= $not_id_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        if (!empty($country_present)){
            $where[] = " (country_main = ? OR country_consulate = ?) ";
            array_push($params, $country_present, $country_present);
        }

        $sql = "SELECT COUNT(*) as counter
                FROM " . $this->library_consulates;

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function get_consulates_count($conditions = array()){
        $field_join = 'id_country';
        extract($conditions);

        $where  = $params = [];

        if(isset($keywords)){
            $where[] = " (con.mission_name LIKE ? OR
                         con.head LIKE ? OR
                         con.address LIKE ? OR
                         con.phone LIKE ? OR
                         con.email LIKE ? OR
                         con.url_site LIKE ?) ";

            array_push($params, ...array_fill(0, 6, '%' . $keywords . '%'));
        }

        if(isset($not_country)){
            $where[] = " (con.id_country = 0 OR id_country_cons = 0) ";
        }

        if(isset($main_country)){
            $where[] = " con.id_country = ? ";
            $params[]= $main_country;
        }

        if(isset($main_country_exist)){
            $where[] = " con.id_country != 0 ";
        }

        if(isset($consulate_country)){
            $where[] = " con.id_country_cons = ? ";
            $params[]= $consulate_country;
            $field_join="id_country_cons";
        }

        if(isset($consulate_countr_exist)){
            $where[] = " con.id_country_cons != 0 ";
            $field_join="id_country_cons";
        }

        if(isset($type_record)){
            $where[] = " con.type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " con.is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->library_consulates con
                LEFT JOIN $this->port_country pc ON pc.id = con.{$field_join}";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $temp = $this->db->query_one($sql, $params);
        return $temp['counter'];
    }

    function delete_consulates($conditions = array()){
        extract($conditions);

        if(isset($id_record))
            $this->db->where('id_consulate', $id_record);

        if(isset($id_records))
            $this->db->in("id_consulate", $id_records, false);

        if(isset($type_record))
            $this->db->where('type_record', $type_record);

        return $this->db->delete($this->library_consulates);
    }

    public function update_country_consulates($params = array()){
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

        $sql = "UPDATE `{$this->library_consulates}` SET ";
        $queryParams = [];

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

            $sql .= "`?` = CASE
            WHEN `?` = ? THEN ?
            ELSE ? END{$comma}
            ";

            array_push($queryParams, $column, $column, $condition['value'], $temp_value, $column);

            $count_cf++;
            $count_to--;
        }

        $colums_where = explode(',', $condition['column']);
        $colums_where = array_map('trim', $colums_where);

        $or_where = array();
        foreach($colums_where as $column_condition){
            $condition['value'] = getArrayFromString($condition['value']);
            $or_where[] = "`?` IN (" . implode('?', array_fill(0, count($condition['value']), '?')) . ")";
            array_push($queryParams, $column_condition, ...$condition['value']);
        }

        $sql .= "WHERE " . implode(" OR ", $or_where);

        return $this->db->query_raw($sql, $queryParams, 0, null);
    }
}
