<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * Model Faq_tags
 *
 * @deprecated use instead Tags_Faq_Model
 */
class Faq_tags_Model extends BaseModel
{
    private $faq_tags_table = 'faq_tags';
    private $primary_key_field = 'id_tag';

    public function add($data = array())
    {
        if (empty($data)) {
            return false;
        }

        $this->db->insert($this->faq_tags_table, $data);
        return $this->db->last_insert_id();
    }

    public function delete($params)
    {
        if (!is_array($params)) {
            $primary_key = (int) $params;
            $this->db->where($this->primary_key_field, $primary_key);
        } else {
            return false;
        }

        if (!$this->db->delete($this->faq_tags_table)) {
            return false;
        }
        return true;
    }

    public function change($primary_key, $data = array())
    {
        if (empty($data)) {
            return false;
        }
        $this->db->where($this->primary_key_field, $primary_key);

        return $this->db->update($this->faq_tags_table, $data);
    }

    public function get_one($primary_key = 0)
    {
        $this->db->select('*');
        $this->db->from($this->faq_tags_table);
        $this->db->where($this->primary_key_field, $primary_key);

        return $this->db->query_one();
    }

    public function get_list($conditions = array())
    {
        $limit = 10;
        $offset = 0;
		$order_by = "name ASC";

        $this->db->select('*');
        $this->db->from("{$this->faq_tags_table} ");

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        if (isset($ids_tags)) {
            $this->db->in('id_tag', $ids_tags);
        }

		$this->db->orderby($order_by);

		if(!empty($limit)) {
			$this->db->limit($limit, $offset);
        }

		return $this->db->query_all();
    }

    public function get_count($conditions)
    {
		$where = array();
        $params = array();

        $this->db->select("COUNT(*) as counter");
        $this->db->from($this->faq_tags_table);

        extract($conditions);


		$rez = $this->db->query_one();
		return $rez['counter'];
    }
}

/* End of file faq_tags_model.php */
/* Location: /tinymvc/myapp/models/faq_tags_model.php */
