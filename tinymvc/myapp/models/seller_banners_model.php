
<?php
/**
 * @deprecated in favor of \Sellers_Banners_Model
*/
class Seller_Banners_Model extends TinyMVC_Model
{
    public $path_to_images = 'public/img/seller_banners';
    public $path_to_temp = 'temp/seller_banners';

    public function get_seller_banner($id, $id_user) {
        $this->db->where('id', $id);
        $this->db->where('id_user', $id_user);
        $this->db->limit(1);
        return $this->db->get_one('seller_banners');
    }

    public function get_banner($id) {
        $this->db->where('id', $id);
        $this->db->limit(1);
        return $this->db->get_one('seller_banners');
    }


    private function get_banners_condition($id_user, $params) {
        $condition = array(
            'where' => array('id_user = ?'),
            'params' => array($id_user)
        );


        if(isset($params['keywords'])){
            $condition['where'][] = ' link LIKE ? ';
            $condition['params'][] = "%{$params['keywords']}%";
        }

        return $condition;
    }


    public function insert($data) {
        $this->db->insert('seller_banners', $data);
        return $this->db->last_insert_id();
    }


    public function update($data, $id_user, $id) {
        $this->db->where('id', $id);
        $this->db->where('id_user', $id_user);
        return $this->db->update('seller_banners', $data);
    }


    public function remove($id_user, $id) {
        $this->db->where('id', $id);
        $this->db->where('id_user', $id_user);
        return $this->db->delete('seller_banners');
    }


    public function get_banners($id_user, $params){
        $order_by = implode(',', $params['order_by']);

        $condition = $this->get_banners_condition($id_user, $params);

        $sql = "SELECT id, id_user, link, image, date_added, page FROM seller_banners";

        if (!empty($condition['where'])) {
            $sql .= ' WHERE ' . implode(" AND ", $condition['where']);
        }

        $sql .= " ORDER BY $order_by";

        $count_widgets = $this->count_banners($id_user, $params);
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

    public function count_banners($id_user, $params){
        $condition = $this->get_banners_condition($id_user, $params);

        $sql = "SELECT COUNT(*) as counter FROM seller_banners";

        if (!empty($condition['where'])) {
            $sql .= ' WHERE ' . implode(" AND", $condition['where']);
        }

        return $this->db->query_one($sql, $condition['params'])['counter'];
    }

}
