<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

class Seller_Wall_Model extends BaseModel
{
    /**
     * The name of the seller wall table
     *
     * @var string
     */
    private $wall_table = 'seller_wall';

    public function get_items($conditions)
    {
        $offset = 0;
        $limit = 10;
        $order_by = "date DESC";

        extract($conditions);

        if (empty($limit)) {
            $limit = 10;
        }
        if (empty($offset)) {
            $offset = 0;
        }

        $this->db->select('*');
        $this->db->from($this->wall_table);
        if (isset($id_seller)) {
            $id_seller = getArrayFromString($id_seller);
            $this->db->in('id_seller', array_map('intval', $id_seller));
        }

        if (isset($id_sellers)) {
            $this->db->in('id_seller', $id_sellers);
        }

        $this->db->orderby($order_by);
        $this->db->limit($limit, $offset);

        return $this->db->query_all();
    }

    public function insert($data)
    {
        return $this->db->insert($this->wall_table, $data);
    }

    public function update($id_item, $type, $id_seller, array $data, $list_items = false)
    {
        $this->db->in('id_item', $id_item, $list_items);
        $this->db->where('type = ?', $type);
        $this->db->where('id_seller = ?', $id_seller);
        return $this->db->update($this->wall_table, $data);
    }
}
