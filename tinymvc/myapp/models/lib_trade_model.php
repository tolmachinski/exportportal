<?php

/**
 * lib_trade_model.php
 *
 * library trade model
 *
 * @author        Boinitchi Ion
 */

class Lib_Trade_Model extends TinyMVC_Model{
    var $obj;
    private $library_trade_performance = "library_trade_performance";
    private $port_country = "port_country";

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->obj = tmvc::instance()->controller;
    }

    function set_trade($data){
        $this->db->insert($this->library_trade_performance, $data);
        return $this->db->last_insert_id();
    }

    function set_rows_trade($data){
        $this->db->insert_batch($this->library_trade_performance, $data);
        return $this->db->getAffectableRowsAmount();
    }

    function update_trade($id_record, $data){
        $this->db->where('id_trade', $id_record);
        return $this->db->update($this->library_trade_performance, $data);
    }

    function get_all_trade($conditions = array()){
        $order_by = ' trade.id_trade ASC ';

        extract($conditions);
        $where = $params = [];

        if(isset($sort_by)){
            $number_columns = array('export', 'import', 'trade', 'total_export', 'total_import', 'world_export', 'world_import', 'growth_import', 'growth_export', 'net_trade');

            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);

                $create_number = $sort_item[0];
                if(in_array($create_number, $number_columns)){
                    $create_number = " CONVERT(REPLACE($create_number,' ',''), DECIMAL(15,4))";
                }

                $multi_order_by[] = $create_number.' '.$sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (isset($keywords)){
            $where[] = " (trade.industry LIKE ? OR trade.country LIKE ?) ";
            array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
        }

        if(isset($country_id)){
            $where[] = " trade.id_country = ? ";
            $params[]= $country_id;
        }

        if(isset($exist_country)){
            $where[] = " trade.id_country != 0 ";
        }

        if(isset($type_record)){
            $where[] = " trade.type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " trade.is_visible = ? ";
            $params[] = $visible_record;
        }

        if(isset($country_name)){
            $where[] = " trade.country = ? ";
            $params[]= $country_name;
        }

        $sql = "SELECT trade.*, pc.country as country_port
                FROM $this->library_trade_performance trade
                LEFT JOIN $this->port_country pc ON pc.id = trade.id_country";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        if(!empty($group_by)){
            $sql .= ' GROUP BY '.$group_by;
        }

        $sql .= " ORDER BY ".$order_by;

        if(isset($start) && isset($per_p)){
            $start = (int) $start;
            $per_p = (int) $per_p;
            $sql .= ' LIMIT '. $start . ',' . $per_p;
        }

        return $this->db->query_all($sql, $params);
    }

    function get_trade($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($id_record)){
            $where[] = " id_trade = ? ";
            $params[] = $id_record;
        }

        if(isset($visible_record)){
            $where[] = " is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT *
                FROM $this->library_trade_performance";

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        return $this->db->query_one($sql, $params);
    }

    function check_trade($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($id_record)){
            $where[] = " id_trade = ? ";
            $params[]= $id_record;
        }

        if(isset($not_id_record)){
            $where[] = " id_trade != ? ";
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
                FROM " . $this->library_trade_performance;

        if(count($where))
            $sql .= " WHERE " . implode(" AND ", $where);

        $rez = $this->db->query_one($sql, $params);
        return $rez['counter'];
    }

    function get_trade_count($conditions = array()){
        $where = $params = [];
        $field_join = 'id_country';
        extract($conditions);

        if (isset($keywords)){
            $where[] = " (trade.industry LIKE ? OR trade.country LIKE ?) ";
            array_push($params, ...array_fill(0, 2, '%' . $keywords . '%'));
        }

        if(isset($country_id)){
            $where[] = " trade.id_country = ? ";
            $params[]= $country_id;
        }

        if(isset($exist_country)){
            $where[] = " trade.id_country != 0 ";
        }

        if(isset($type_record)){
            $where[] = " trade.type_record = ? ";
            $params[] = $type_record;
        }

        if(isset($visible_record)){
            $where[] = " trade.is_visible = ? ";
            $params[] = $visible_record;
        }

        $sql = "SELECT COUNT(*) as counter
                FROM $this->library_trade_performance trade
                LEFT JOIN $this->port_country pc ON pc.id = trade.{$field_join}";

        if(count($where))
            $sql .= " WHERE " . implode(" AND", $where);

        $temp = $this->db->query_one($sql, $params);
        return $temp['counter'];
    }

    function delete_trade($conditions = array()){
        extract($conditions);

        if(isset($id_record))
            $this->db->where('id_trade', $id_record);

        if(isset($id_records))
            $this->db->in("id_trade", $id_records, false);

        if(isset($type_record))
            $this->db->where('type_record', $type_record);

        return $this->db->delete($this->library_trade_performance);
    }

    function get_countries_trade($conditions = array()){
        extract($conditions);
        $where = $params = [];

        if(isset($keywords)){
            $where[] = " ( industry LIKE ? ) ";
            $params[] = '%' . $keywords . '%';
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
                FROM $this->library_trade_performance ";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY country ";

        return $this->db->query_all($sql, $params);
    }

    public function update_country_trade($params = array()){
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

        $count_columns = count($replace_columns);

        if (empty($replace_columns_from) || empty($replace_columns_to) || $count_columns != count($replace_columns_to)){
            return false;
        }

        $sql = "UPDATE `{$this->library_trade_performance}` SET ";

        $comma = ",";
        $i = 0;
        foreach($replace_columns_to as $column){
            $temp_value = "";
            if (!isset($source_data[$replace_columns_from[$i]])){
                return false;
            }

            $temp_value = $source_data[$replace_columns_from[$i]];
            $i++;
            if ($count_columns == $i){
                $comma = "";
            }

            $sql .= "`{$column}` = CASE
            WHEN `{$column}` = '{$condition['value']}' THEN '{$temp_value}'
            ELSE `{$column}` END{$comma}
            ";
        }
        $sql .= "WHERE `{$condition['column']}` IN ('{$condition['value']}')";

        return $this->db->query_raw($sql, null, 0, null);
    }
}
