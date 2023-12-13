<?php
/**
 *country_articles.php.
 *
 *Country blogs model

 * @deprecated `v2.32.0` `2022/01/18` in favor of `\Modules_Model`
 *
 * @author
 */
class Ep_Modules_Model extends TinyMVC_Model
{
    private $ep_modules_table = 'ep_modules';

    public function get_all_modules()
    {
        $sql = 'SELECT id_module, name_module FROM ' . $this->ep_modules_table . ' ORDER BY name_module';

        return $this->db->query_all($sql);
    }

    public function get_calendar_modules()
    {
        $sql = 'SELECT *
			FROM ' . $this->ep_modules_table . '
			WHERE in_calendar = 1
			ORDER BY name_module';

        return $this->db->query_all($sql);
    }

    public function get_ep_modules($conditions)
    {
        $group_by_module = false;
        $select = 'm.*';

        extract($conditions);

        if (isset($sort_by)) {
            foreach ($sort_by as $sort_item) {
                $sort_item = explode('-', $sort_item);
                $multi_order_by[] = $sort_item[0] . ' ' . $sort_item[1];
            }

            $order_by = implode(',', $multi_order_by);
        }

        if (!empty($keywords)) {
            $this->db->where_raw('m.name_module LIKE ?', "%{$keywords}%");
        }

        if (!empty($email_notification)) {
            $this->db->where('m.email_notification', $email_notification);
        }

        $this->db->select("{$select}");
        $this->db->from("{$this->ep_modules_table} m");

        if (!empty($by_rights)) {
            $this->db->join('rights r', 'm.id_module = r.r_module', 'inner');
            $this->db->in('r.r_alias', $by_rights);
        }

        if (!empty($order_by)) {
            $this->db->orderby($order_by);
        }

        if ($group_by_module) {
            $this->db->groupby('m.id_module');
        }

        if (isset($start, $per_p)) {
            $this->db->limit($per_p, $start);
        }

        return $this->db->query_all();
    }

    public function get_ep_modules_counter($conditions)
    {
        $group_by_module = false;

        extract($conditions);

        if (!empty($keywords)) {
            $this->db->where_raw('name_module LIKE ?', "%{$keywords}%");
        }

        if (!empty($email_notification)) {
            $this->db->where('email_notification', $email_notification);
        }

        $this->db->select('COUNT(*) as counter');
        $this->db->from("{$this->ep_modules_table}");

        if ($group_by_module) {
            $this->db->groupby('id_module');
        }

        $temp = $this->db->query_one();

        return $temp['counter'];
    }

    public function get_sibling_up_module($position_module)
    {
        $sql = "SELECT *
				FROM ep_modules
				WHERE position_module < {$position_module}
				ORDER BY position_module DESC
				LIMIT 1";

        return $this->db->query_one($sql);
    }

    public function get_sibling_down_module($position_module)
    {
        $sql = "SELECT *
				FROM ep_modules
				WHERE position_module > {$position_module}
				ORDER BY position_module ASC
				LIMIT 1";

        return $this->db->query_one($sql);
    }

    public function get_last_module_position()
    {
        $sql = 'SELECT position_module
				FROM ep_modules
				ORDER BY position_module DESC
				LIMIT 1';
        $res = $this->db->query_one($sql);

        return (int) $res['position_module'];
    }

    public function update_module_position($id_module, $position_module, $id_sibling_module, $position_sibling_module)
    {
        $sql = "UPDATE ep_modules
				SET position_module = CASE
					WHEN id_module = {$id_module} THEN {$position_module}
					WHEN id_module = {$id_sibling_module} THEN {$position_sibling_module}
					END
				WHERE id_module IN (?, ?)";

        return $this->db->query($sql, [$id_module, $id_sibling_module]);
    }

    public function get_ep_module($id_module)
    {
        $sql = 'SELECT * FROM ep_modules WHERE id_module=?';

        return $this->db->query_one($sql, [$id_module]);
    }

    public function delete_ep_module($id_module)
    {
        $this->db->where('id_module', $id_module);

        return $this->db->delete($this->ep_modules_table);
    }

    public function update_ep_module($id_module, $update)
    {
        $this->db->where('id_module', $id_module);

        return $this->db->update($this->ep_modules_table, $update);
    }

    public function insert_ep_module($update)
    {
        return $this->db->insert($this->ep_modules_table, $update);
    }
}
