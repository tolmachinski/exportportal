<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;

/**
 * Model Crm_leads
 *
 */
class Crm_Model extends BaseModel
{
    private $crm_sync_users_table = 'crm_sync_users';

    public function get_users_for_update($limit = 1)
    {
        $this->db->select('id_user');
        $this->db->from($this->crm_sync_users_table);
        $this->db->where('action', 'update');
        $this->db->where('is_resolved', '1');
        $this->db->limit($limit);

        return $this->db->query_all();
    }

    public function get_users_for_export($limit = 1)
    {
        $this->db->select('id_user');
        $this->db->from($this->crm_sync_users_table);
        $this->db->where('action', 'export');
        $this->db->where('is_resolved', '1');
        $this->db->limit($limit);

        return $this->db->query_all();
    }

    public function delete_records_by_users_ids($users_ids)
    {
        $this->db->in('id_user', $users_ids);
		return $this->db->delete($this->crm_sync_users_table);
    }

    public function create_or_update_record($userId)
    {
        if ('prod' !== config('env.APP_ENV')) {
            return false;
        }

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $user = $userModel->getSimpleUser($userId);
        if (empty($user) || !in_array($user['user_group'], array(1, 2, 3, 5, 6, 31)) || $user['fake_user'] || $user['is_model']) {
            return false;
        }

        $userModel->updateUserMain($userId, array('last_update_for_crm' => date('Y-m-d H:i:s')));

        $action = empty($user['zoho_id_record']) ? 'export' : 'update';

        $sql = "INSERT INTO $this->crm_sync_users_table (`id_user`, `action`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `action` = ?";

        return $this->db->query($sql, [$userId, $action, $action]);
    }

    public function update_record($id_user, $data)
    {
        if (empty($id_user) || empty($data)) {
            return false;
        }

        $this->db->where('id_user', $id_user);

        return $this->db->update($this->crm_sync_users_table, $data);
    }

    public function create_record(array $data){
        return $this->db->insert($this->crm_sync_users_table, $data);
    }
}

/* End of file crm_leads_model.php */
/* Location: /tinymvc/myapp/models/crm_leads_model.php */
