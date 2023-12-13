<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * Model Popups
 *
 * @deprecated 2.28.4 in favor if \Popup_Model
 *
 * @see \Popup_Model
 *
 */
class Popups_Model extends BaseModel
{
    private $popups_table = 'popups';

    public function getTable()
    {
        return $this->popups_table;
    }

    public function get_one($conditions = array())
    {
        $this->db->select('*');
        $this->db->from($this->popups_table);

        extract($conditions);

        if (null !== $popup_hash) {
            $this->db->where('popup_hash', $popup_hash);
        }

        if (null !== $is_active) {
            $this->db->where('is_active', $is_active);
        }

        return $this->db->query_one();
    }

    public function get_list($conditions = array())
    {
        $this->db->select('
            pp_r.id_popup,
            p.page_hash,
            pop.view_method, pop.repeat_on_cancel, pop.repeat_on_submit
        ');
        $this->db->from("{$this->popup_pages_relation_table} pp_r");
        $this->db->join("{$this->pages_table} p", 'pp_r.id_page = p.id_page', 'left');
        $this->db->join("{$this->popups_table} pop", 'pp_r.id_popup = pop.id_popup', 'left');

        extract($conditions);

        if (null !== $page_hash) {
            $this->db->where('p.page_hash', $page_hash);
        }

        return $this->db->query_all();
    }
}

/* End of file popups_model.php */
/* Location: /tinymvc/myapp/models/popups_model.php */
