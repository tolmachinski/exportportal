<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * Model Popups_data
 *
 */
class Popups_data_Model extends BaseModel
{
    private $popups_data_table = 'popups_data';
    private $users_table = 'users';
    private $groups_table = 'user_groups';

    public function set($data)
    {
        return $this->db->insert($this->popups_data_table, $data);
    }

    public function get_list($conditions = array())
    {
        $skip = null;
        $limit = null;
        $order = array();
        $sort_by = array('id_data_asc');
        $this->db->select("p.*,
                            CONCAT(u.fname, ' ', u.lname) as username, u.status, u.email,
                            g.gr_name
                        ");
        $this->db->from("{$this->popups_data_table} as p");
        $this->db->join("{$this->users_table} AS u", 'p.id_user = u.idu', 'LEFT');
        $this->db->join("{$this->groups_table} AS g", 'u.user_group = g.idgroup', 'LEFT');

        extract($conditions);

        foreach($sort_by as $sort_by_item){
            switch ($sort_by_item) {
                case 'id_data_asc':
                    $order[] = 'p.id_data ASC';
                break;
                case 'id_data_desc':
                    $order[] = 'p.id_data DESC';
                break;
                case 'username_asc':
                    $order[] = 'username ASC';
                break;
                case 'username_desc':
                    $order[] = 'username DESC';
                break;
                case 'status_asc':
                    $order[] = 'u.status ASC';
                break;
                case 'status_desc':
                    $order[] = 'u.status DESC';
                break;
                case 'gr_name_asc':
                    $order[] = 'g.gr_name ASC';
                break;
                case 'gr_name_desc':
                    $order[] = 'g.gr_name DESC';
                break;
                case 'date_created_asc':
                    $order[] = 'p.date_created ASC';
                break;
                case 'date_created_desc':
                    $order[] = 'p.date_created DESC';
                break;
                case 'rand':
                    $order[] = 'RAND()';
                break;
            }
        }

        if (null !== $id_popup) {
            $this->db->where('p.id_popup', $id_popup);
        }

        if (null !== $user_status) {
            $this->db->where('u.status', $user_status);
        }

        if (null !== $user_type) {
            $this->db->where('g.idgroup', $user_type);
        }

        if (null !== $start_date) {
            $this->db->where("p.date_created >= ?", $start_date);
        }

        if (null !== $finish_date) {
            $this->db->where("p.date_created <= ?", $finish_date);
        }

        if (isset($condition_search)) {
            if (str_word_count_utf8($condition_search) > 1) {
                $escaped_search_string = $this->getConnection()->quote(trim($condition_search));
                $search_parts = preg_split("/\b/", trim($escaped_search_string, "'"));
                $search_parts = array_map('trim', $search_parts);
                $search_parts = array_filter($search_parts);
                if (!empty($search_parts)) {
                    // Drop array keys
                    $search_parts = array_values($search_parts);
                    // Unite words - each consecutive word have lesser contribution
                    $search_condition = implode('* <', $search_parts);
                    $search_condition = "{$search_condition}*";
                    $this->db->where_raw("MATCH u.fname, u.lname, u.email) AGAINST (? IN BOOLEAN MODE)", $search_condition);
                }
            } else {
                $this->db->where_raw("(u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ?)", array_fill(0, 3, '%' . $condition_search . '%'));
            }
        }

        //region OrderBy
        if (!empty($order)) {
            $this->db->orderby(implode(', ', $order));
        }
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

    public function count_list($conditions = array())
    {
        $this->db->select('COUNT(*) AS counter');
        $this->db->from("{$this->popups_data_table} as p");
        $this->db->join("{$this->users_table} AS u", 'p.id_user = u.idu', 'LEFT');
        $this->db->join("{$this->groups_table} AS g", 'u.user_group = g.idgroup', 'LEFT');

        extract($conditions);

        if (null !== $id_popup) {
            $this->db->where('p.id_popup', $id_popup);
        }

        if (null !== $user_status) {
            $this->db->where('u.status', $user_status);
        }

        if (null !== $user_type) {
            $this->db->where('g.idgroup', $user_type);
        }

        if (null !== $start_date) {
            $this->db->where("p.date_created >= ?", $start_date);
        }

        if (null !== $finish_date) {
            $this->db->where("p.date_created <= ?", $finish_date);
        }

        return $this->db->query_one()['counter'];
    }
}

/* End of file popups_data_model.php */
/* Location: /tinymvc/myapp/models/popups_data_model.php */
