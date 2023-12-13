<?php

class Search_Log_Model extends TinyMVC_Model
{
    private $search_log_table = 'search_log';
    private $pages_table = 'pages';

    public function log($query)
    {
        $controller = app()->name;
        $action = tmvc::instance()->action;

        /** @var Pages_Model $pagesModel */
        $pagesModel = model(Pages_Model::class);

        $data = array(
            'query'       => $query,
            'uri'         => $_SERVER['REQUEST_URI'],
            'id_analytic' => $_COOKIE['ANALITICS_CT_SUID'] ?? session()->__get('ANALITICS_CT_SUID') ?? null,
            'referer'     => $_SERVER['HTTP_REFERER'] ?? null,
            'module'      => $controller . '/' . $action,
            'page'        => $pagesModel->find_page_by_hash(getPageHash($controller, $action))['id_page'],
            'id_user'     => session()->id ?? null,
            'lang'        => __SITE_LANG,
            'request'     => json_encode(array('get' => $_GET, 'post' => $_POST), JSON_INVALID_UTF8_SUBSTITUTE),
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'],
        );

        $this->db->insert($this->search_log_table, $data);
    }

    public function get_logs($conditions = array())
    {
        $limit = 10;
        $offset = 0;
        $order_by = 'id_search ASC';

        extract($conditions);

        $this->db->select('sl.*, p.page_name');
        $this->db->from("{$this->search_log_table} sl");
        $this->db->join('pages p', 'p.id_page = sl.page');

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (!empty($page)) {
            $this->db->where('id_page', $page);
        }

        if (!empty($date_from)) {
            $this->db->where('date > ', $date_from);
        }

        if (!empty($date_to)) {
            $this->db->where('date < ', $date_to);
        }

        $this->db->orderby($order_by);

        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->query_all();
    }

    public function count_logs($conditions)
    {
        extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->from("{$this->search_log_table} sl");
        $this->db->join('pages p', 'p.id_page = sl.page');

        if (!empty($page)) {
            $this->db->where('id_page', $page);
        }

        if (!empty($date_from)) {
            $this->db->where('date >', $date_from);
        }

        if (!empty($date_to)) {
            $this->db->where('date <', $date_to);
        }

        return $this->db->query_one()['counter'];
    }

    public function get_logs_group_by_query($conditions = array())
    {
        $limit = 10;
        $offset = 0;
        $order_by = 'count desc';

        extract($conditions);

        $this->db->select('sl.query, p.page_name, COUNT(sl.id_search) AS count');
        $this->db->from("{$this->search_log_table} sl");
        $this->db->join("{$this->pages_table} p", 'p.id_page = sl.page');

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (!empty($page)) {
            $this->db->where('id_page', $page);
        }

        $this->db->groupby('sl.query, p.id_page');
        $this->db->orderby($order_by);

        if (!empty($limit)) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->query_all();
    }

    public function count_logs_group_by_query($conditions = array())
    {
        extract($conditions);

        $where = $params = [];

        if (!empty($page)) {
            $where[] = "page = ?";
            $params[] = $page;
        }

        $where = empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);
        $sql = "
            SELECT COUNT(*) as count FROM (
                SELECT COUNT(id_search) AS 'count'
                FROM {$this->search_log_table} sl
                JOIN {$this->pages_table} p
                    ON p.id_page = sl.page
                {$where}
                GROUP BY sl.query, p.id_page
            ) AS search_log_group_by_query
        ";

        return $this->db->query_one($sql, $params)['count'];
    }
}
