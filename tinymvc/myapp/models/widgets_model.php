<?php

class Widgets_Model extends TinyMVC_Model
{
    public function get_widget($id, $id_seller) {
        $this->db->where('id', $id);
        $this->db->where('id_user', $id_seller);
        $this->db->limit(1);

        return $this->db->get_one('widgets');
    }


    private function get_widgets_condition($id_user, $params) {
        $condition = array(
            'where' => array('id_user = ?'),
            'params' => array($id_user)
        );


        if(isset($params['keywords'])){
            $condition['where'][] = ' site LIKE ? ';
            $condition['params'][] = "%{$params['keywords']}%";
        }

        return $condition;
    }


    public function insert($data) {
        return $this->db->insert('widgets', $data);
    }


    public function update($data, $id_user, $id) {
        $this->db->where('id', $id);
        $this->db->where('id_user', $id_user);
        return $this->db->update('widgets', $data);
    }


    public function remove($id_user, $id) {
        $this->db->where('id', $id);
        $this->db->where('id_user', $id_user);
        return $this->db->delete('widgets');
    }


    public function get_widgets($id_user, $params){
        $order_by = implode(',', $params['order_by']);

        $condition = $this->get_widgets_condition($id_user, $params);

        $sql = "SELECT id, site, width, height FROM widgets";
        if(count($condition['where'])) {
            $sql .= ' WHERE ' . implode(" AND ", $condition['where']);
        }
        $sql .= " ORDER BY $order_by";


        $count_widgets = $this->count_widgets($id_user, $params);
        $max_pages = ceil($count_widgets / $params['per_p']);

        if(!isset($params['start'])) {
            if ($params['page'] > $max_pages) {
                $params['page'] = $max_pages;
            }

            $params['start'] = ($params['page'] - 1) * $params['per_p'];

            if($params['start'] < 0) {
                $params['start'] = 0;
            }
        }

        $sql .= " LIMIT {$params['start']}, {$params['per_p']}";

        return $this->db->query_all($sql, $condition['params']);
    }

    public function count_widgets($id_user, $params){
        $condition = $this->get_widgets_condition($id_user, $params);

        $sql = "SELECT COUNT(*) as counter FROM widgets";

        if(count($condition['where'])) {
            $sql .= ' WHERE ' . implode(" AND", $condition['where']);
        }

        return $this->db->query_one($sql, $condition['params'])['counter'];
    }
}

