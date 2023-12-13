<?php

/**
 * config_lib_model.php
 *
 *
 * @package       TinyMVC
 * @author        Boinitchi Ion
 */

class Config_Lib_Model extends TinyMVC_Model{

    private $library_table = 'library';

    function get_lib_configs($params = array())
    {
        extract($params);

        $this->db->select('*');
        $this->db->from($this->library_table);

        //region Conditions
        $this->db->where('is_visible', 1);

        if(isset($keywords)){
            $this->db->where_raw("lib_description LIKE ?", "%{$keywords}%");
        }
        //endregion Conditions

        //region OrderBy
        $order_by= ' id_lib ASC ';
        if(isset($sort_by)){
            $multi_order_by = array();
            foreach($sort_by as $sort_item){
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            if(!empty($multi_order_by)){
                $order_by = implode(',', $multi_order_by);
            }
        }
        $this->db->orderby($order_by);
        //endregion OrderBy

        //region Limits
        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }
        //endregion Limits

        return $this->db->query_all();
    }

    function get_lib_config($id_lib)
    {
        $this->db->select('*');
        $this->db->from($this->library_table);
        $this->db->where('id_lib', (int) $id_lib);

        return $this->db->query_one();
    }

    function count_lib_configs($conditions = array())
    {
        extract($params);

        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->library_table);

        //region Conditions
        $this->db->where('is_visible', 1);

        if(isset($conditions['keywords'])){
            $this->db->where_raw("lib_description LIKE ?", "%{$keywords}%");
        }
        //endregion Conditions

        $data = $this->db->query_one();
        if (!$data || empty($data)) {
            return 0;
        }

        return isset($data['AGGREGATE']) ? (int) $data['AGGREGATE'] : 0;
    }

    public function set_library_setting($data)
    {
        $this->db->insert($this->library_table, $data);
        return $this->db->last_insert_id();
    }

    public function update_library_setting($id_record, $data)
    {
        $this->db->where('id_lib', $id_record);
        return $this->db->update($this->library_table, $data);
    }

    public function check_lib_config($params = array())
    {
        extract($params);

        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->library_table);

        //region Conditions
        if(isset($title)) {
            $this->db->where('lib_title', $title);
        }

        if(isset($file)) {
            $this->db->where('file_name', $file);
        }

        if(isset($not_id_record)){
            $this->db->where('id_lib !=', $not_id_record);
        }
        //endregion Conditions

        $counter = $this->db->query_one();
        if (empty($counter)) {
            return false;
        }

        return (bool) (int) arrayGet($counter, 'AGGREGATE');
    }

    public function delete_library_setting($id_record)
    {
        $this->db->where('id_lib', $id_record);
        return $this->db->delete($this->library_table);
    }

    public function list_record_by_relation($conditions = array())
    {
        extract($conditions);

        $this->db->select($db_select);
        $this->db->from($db_table);

        return $this->db->query_all();
    }

    public function select_by_relation($conditions = array())
    {
        extract($conditions);

        $this->db->select($db_select);
        $this->db->from($db_table);

        $this->db->in($db_colum, $where, true);
        $records= $this->db->query_all();

        $output = array();
        if(!empty($records)){
            foreach($records as $item){
                $output[$item[$db_colum]] = $item;
            }
        }
        return $output;
    }

}
