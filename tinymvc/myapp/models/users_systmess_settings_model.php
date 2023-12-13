<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * Model Users_systmess_settings
 *
 * @deprecated `v2.32.0` `2022/01/18` in favor of `\User_System_Message_Settings_Model`
 *
 */
class Users_systmess_settings_Model extends BaseModel
{
    private $users_systmess_settings_table = 'users_systmess_settings';
    private $ep_modules_table = 'ep_modules';
    private $users_table = 'users';

    public function get_settings($id_user)
    {
        $this->db->select('uss.*, m.*');
        $this->db->from("{$this->users_systmess_settings_table} uss");
        $this->db->join("{$this->ep_modules_table} m", 'uss.module = m.id_module');
        $this->db->where('m.email_notification', 1);
        if (is_array($id_user)) {
            $this->db->in('uss.id_user', $id_user);
        } else {
            $this->db->where('uss.id_user', $id_user);
        }

        return $this->db->query_all();
    }

    public function add_default_settings($id_user)
    {
        $this->db->query(
            "
                INSERT INTO {$this->users_systmess_settings_table} (id_user, module)
                SELECT idu, ep_modules.id_module
                FROM {$this->users_table} u
                JOIN ep_modules
                WHERE u.idu = ?
            ",
            [$id_user]
        );
    }

    public function add($id_user, $id_module)
    {
        return $this->db->insert($this->users_systmess_settings_table, array(
            'id_user' => $id_user,
            'module'  => $id_module
        ));
    }

    public function remove($id_user, $id_module)
    {
        $this->db->where('id_user', $id_user);
        $this->db->where('module', $id_module);
        return $this->db->delete($this->users_systmess_settings_table);
    }

    /**
     * @param $userId
     */
    public function deleteAllUserSystmessSettings(int $userId)
    {
        $this->db->where('id_user', $userId);
        return $this->db->delete($this->users_systmess_settings_table);
    }
}

/* End of file users_systmess_settings_model.php */
/* Location: /tinymvc/myapp/models/users_systmess_settings_model.php */
