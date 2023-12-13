<?php

/**
 * lib_icc_country_model.php
 *
 * library ICC Country model
 *
 * @author        Boinitchi Ion
 */

class Lib_Icc_Country_Model extends TinyMVC_Model{
    var $obj;
    private $library_icc_country = "library_icc_country";
    private $port_country = "port_country";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function get_all_icc_country($conditions = array()){
        $order_by = ' lic.id_icc ASC ';

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
            $where[] = " (lic.country  LIKE ? OR
                         lic.agencies LIKE ? OR
                         lic.phone LIKE ? OR
                         lic.email LIKE ? OR
                         lic.url_site LIKE ?) ";
            array_push($params, ...array_fill(0, 5, '%' . $keywords . '%'));
        }

        if(isset($country_id)){
            $where[] = " lic.id_country = ? ";
            $params[]= $country_id;
        }

        if(isset($exist_country)){
            $where[] = " lic.id_country != 0 ";
        }

        if(isset($type_record)){
            $where[] = " lic.type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " lic.is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT lic.*, pc.country as country_port
                FROM $this->library_icc_country lic
                LEFT JOIN $this->port_country pc ON pc.id = lic.id_country";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        if (!empty($group_by)){
            $sql .= " GROUP BY {$group_by} ";
        }

        $sql .= " ORDER BY ".$order_by;

        if (isset($start) && isset($per_p)) {
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= ' LIMIT ' . $start . ',' . $per_p;
        }


        return $this->db->query_all($sql, $params);
    }

    function get_icc_country_count($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($keywords)){
            $where[] = " (lic.country  LIKE ? OR
                         lic.agencies LIKE ? OR
                         lic.phone LIKE ? OR
                         lic.email LIKE ? OR
                         lic.url_site LIKE ?) ";

            array_push($params, ...array_fill(0, 5, '%' . $keywords . '%'));
        }

        if(isset($country_id)){
            $where[] = " lic.id_country = ? ";
            $params[]= $country_id;
        }

        if(isset($exist_country)){
            $where[] = " lic.id_country != 0 ";
        }

        if(isset($type_record)){
            $where[] = " lic.type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " lic.is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->library_icc_country lic
                LEFT JOIN $this->port_country pc ON pc.id = lic.id_country ";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $temp = $this->db->query_one($sql, $params);
        return $temp['counter'];
    }

    function get_icc_country($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($id_record)){
            $where[] = " id_icc = ? ";
            $params[] = $id_record;
        }

        $sql = "SELECT *
                FROM $this->library_icc_country";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params);
    }

    function set_icc_country($data){
        $this->db->insert($this->library_icc_country, $data);
        return $this->db->last_insert_id();
    }

    function set_rows_icc_country($data){
        $this->db->insert_batch($this->library_icc_country, $data);
        return $this->db->getAffectableRowsAmount();
    }

    function update_icc_country($id_record, $data){
        $this->db->where('id_icc', $id_record);
        return $this->db->update($this->library_icc_country, $data);
    }

    function check_icc_country($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($email)) {
            $where[] = " email = ?";
            $params[] = $email;
        }

        if(isset($id_record)){
            $where[] = " id_icc = ? ";
            $params[]= $id_record;
        }

        if(isset($not_id_record)){
            $where[] = " id_icc != ? ";
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
                FROM " . $this->library_icc_country;

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function delete_icc_country($conditions = array()){
        extract($conditions);

        if(isset($id_record))
            $this->db->where('id_icc', $id_record);

        if(isset($id_records))
            $this->db->in("id_icc", $id_records, false);

        if(isset($type_record))
            $this->db->where('type_record', $type_record);

        return $this->db->delete($this->library_icc_country);
    }

    public function update_country_icc($params = array()){
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

        $sql = "UPDATE `{$this->library_icc_country}` SET ";

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
