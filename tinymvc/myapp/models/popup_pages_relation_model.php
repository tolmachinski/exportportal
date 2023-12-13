<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * Model Popup_pages_relation
 *
 */
class Popup_pages_relation_Model extends BaseModel
{
    private $popup_pages_relation_table = 'popup_pages_relation';
    private $pages_table = 'pages';
    private $popups_table = 'popups';

    public function get_one($conditions = array())
    {
        $this->db->select('
            pp_r.id_popup,
            p.page_hash,
            pop.view_method, pp.repeat_on_cancel, pp.repeat_on_submit
        ');
        $this->db->from("{$this->popup_pages_relation_table} pp_r");
        $this->db->join("{$this->pages_table} p", 'pp_r.id_page = p.id_page', 'left');
        $this->db->join("{$this->popups_table} pop", 'pp_r.id_popup = pop.id_popup', 'left');

        extract($conditions);

        if (null !== $page_hash) {
            $this->db->where('p.page_hash', $page_hash);
        }

        if (null !== $is_active) {
            $this->db->where('p.is_active', $is_active);
        }

        return $this->db->query_one();
    }

    public function get_list($conditions = array())
    {
        $this->db->select('
            pp_r.id_popup,
            p.page_hash,
            pop.view_method, pop.repeat_on_cancel, pop.repeat_on_submit, pop.is_active, pop.popup_hash
        ');
        $this->db->from("{$this->popup_pages_relation_table} pp_r");
        $this->db->join("{$this->pages_table} p", 'pp_r.id_page = p.id_page', 'left');
        $this->db->join("{$this->popups_table} pop", 'pp_r.id_popup = pop.id_popup', 'left');

        extract($conditions);

        if (null !== $page_hash) {
            $this->db->where('p.page_hash', $page_hash);
        }

        if (null !== $is_active) {
            $this->db->where('pop.is_active', $is_active);
        }

        if (null !== $call_on_start) {
            $this->db->where('pop.call_on_start', $call_on_start);
        }

        return $this->db->query_all();
    }
}

/* End of file popup_pages_relation_model.php */
/* Location: /tinymvc/myapp/models/popup_pages_relation_model.php */
