<?php

class Session_Logs_Model extends TinyMVC_Model
{
    protected $session_logs_table   = 'users_session_logs';
    protected $users_table          = 'users';

    public function handler_insert($data = array())
    {
        return empty($data) ? false : $this->db->insert($this->session_logs_table, $data);
    }

    public function handler_insert_batch($data = array())
    {
        return empty($data) ? false : $this->db->insert_batch($this->session_logs_table, $data);
    }

    private function _get_params($conditions = array())
    {
        $this->db->from($this->session_logs_table);

        extract($conditions);

        if (isset($id_user)) {
            $this->db->where('id_user = ?', (int) $id_user);
        }

        if (isset($start_date)) {
            $this->db->where('log_date >= ?', $start_date);
        }

        if (isset($finish_date)) {
            $this->db->where('log_date <= ?', $finish_date);
        }

        if (isset($log_type)) {
            $this->db->where('log_type = ?', $log_type);
        }
    }

    public function handler_get_all($conditions = array())
    {
        $order_by = 'log_date DESC';

        $this->db->select('*');

        $this->_get_params($conditions);

        if (isset($conditions['sort_by'])) {
            $multi_order_by = array();
            foreach ($conditions['sort_by'] as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            if (!empty($multi_order_by)) {
                $order_by = implode(',', $multi_order_by);
            }
        }

        $this->db->orderby($order_by);

        $this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);

        return $this->db->query_all() ?: [];
    }

    public function handler_get_count($conditions)
    {
        $this->db->select('COUNT(*) as total_rows');

        $this->_get_params($conditions);

        return $this->db->query_one()['total_rows'];
    }
}
