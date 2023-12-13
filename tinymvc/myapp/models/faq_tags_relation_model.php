<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * Model Faq_tags
 *
 *  @deprecated use instead Tags_Faq_Relation_Model
 *
 */
class Faq_tags_relation_Model extends BaseModel
{
    private $faq_tags_relation_table = 'faq_tags_relation';
    private $faq_table = 'faq';
    private $tags_table = 'faq_tags';
    private $primary_key_field = 'id_rel';

    public function add($data = array())
    {
        if (empty($data)) {
            return false;
        }

        $this->db->insert($this->faq_tags_relation_table, $data);
        return $this->db->last_insert_id();
    }

    public function add_batch($data = array())
    {
        if (empty($data)) {
            return false;
        }

        return $this->db->insert_batch($this->faq_tags_relation_table, $data);
    }

    public function delete($params)
    {
        if (!is_array($params)) {
            $primary_key = (int) $params;
            $this->db->where($this->primary_key_field, $primary_key);
        } else {
            extract($params);

            if (isset($id_faq)) {
                $this->db->where('id_faq', $id_faq);
            }
        }

        return !!$this->db->delete($this->faq_tags_relation_table);
    }

    public function change($primary_key, $data = array())
    {
        if (empty($data)) {
            return false;
        }
        $this->db->where($this->primary_key_field, $primary_key);

        return $this->db->update($this->faq_tags_relation_table, $data);
    }

    public function get_one($primary_key = 0)
    {
        $this->db->select('*');
        $this->db->from($this->faq_tags_relation_table);
        $this->db->where($this->primary_key_field, $primary_key);

        return $this->db->query_one();
    }

    public function get_list($conditions = array())
    {
        $offset = 0;
		$order_by = "f_t.{$this->primary_key_field} ASC";

        $this->db->select('f_t.*,
            f.question as faq_question,
            t.name as tag_name'
        );
        $this->db->from("{$this->faq_tags_relation_table} f_t");
        $this->db->join("{$this->faq_table} f", 'f_t.id_faq = f.id_faq', 'left');
        $this->db->join("{$this->tags_table} t", 'f_t.id_tag = t.id_tag', 'left');

		extract($conditions);

		if(isset($sort_by)){
			foreach($sort_by as $sort_item){
				$sort_item = explode('-', $sort_item);
				$multi_order_by[] = $sort_item[0].' '.$sort_item[1];
			}

			$order_by = implode(',', $multi_order_by);
		}

        if (isset($id_faq)) {
            $this->db->where('f_t.id_faq', $id_faq);
        }

		$this->db->orderby($order_by);

		if(isset($limit)) {
			$this->db->limit($limit, $offset);
        }

		return $this->db->query_all();
    }

    public function get_count($conditions)
    {
		$where = array();
        $params = array();

        $this->db->select("COUNT(*) as counter");
        $this->db->from($this->faq_tags_relation_table);

        extract($conditions);


		$rez = $this->db->query_one();
		return $rez['counter'];
    }
}

/* End of file faq_tags_model.php */
/* Location: /tinymvc/myapp/models/faq_tags_model.php */
