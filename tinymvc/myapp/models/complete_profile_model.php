<?php

/**
 * @deprecated in favor of \Complete_Profile_Options_Model
 */
class Complete_Profile_Model extends TinyMVC_Model
{
    /** @deprecated in favor of \Users_Model::getTable() */
    private $users = 'users';
    /** @deprecated in favor of \Complete_Profile_Options_Model::getTable() */
    private $profile_options = 'profile_options';
    /** @deprecated in favor of \Users_Complete_Profile_Options_Model::getTable() */
    private $users_complete_profile = 'users_complete_profile';
    /** @deprecated in favor of \Groups_Profile_Options_Pivot_Model::getTable() */
    private $group_profile_options_relation = 'group_profile_options_relation';

    public function get_user_profile_options($id_user = null)
    {
        if (null === $id_user) {
            return [];
        }

        $this->db->select(' po.option_alias, po.option_name, po.option_description, po.option_update_name, po.option_update_description, po.id_option, po.option_url,po.option_img,
                            gpor.id_group, gpor.option_percent, gpor.option_percent, gpor.option_weight, gpor.is_required,
                            IF(ucp.id_user IS NULL, 0, 1) AS option_completed ');
        $this->db->from("{$this->profile_options} po");
        $this->db->join("{$this->users_complete_profile} ucp", "po.option_alias = ucp.profile_key AND ucp.id_user = {$id_user}", 'LEFT');
        $this->db->join("{$this->group_profile_options_relation} gpor", 'po.id_option = gpor.id_option', 'inner');
        $this->db->join("{$this->users} u", 'gpor.id_group = u.user_group', 'inner');
        $this->db->where('u.idu = ?', (int) $id_user);
        $this->db->orderby('gpor.option_weight ASC');

        return $this->db->get();
    }

    /**
     *
     * @param array $usersIds List of users ID(s)
     *
     * @return array Porfile completion options grouped by user ID
     */
    public function get_users_profile_options(array $usersIds): array
    {
        if (empty($usersIds)) {
            return [];
        }

        $this->db->select('u.idu, po.*, gpor.*, IF(ucp.id_user IS NULL, 0, 1) AS option_completed');
        $this->db->from("{$this->group_profile_options_relation} gpor");
        $this->db->join("{$this->users} u", 'gpor.id_group = u.user_group');
        $this->db->join("{$this->profile_options} po", 'gpor.id_option = po.id_option', 'left');
        $this->db->join("{$this->users_complete_profile} ucp", 'u.idu = ucp.id_user AND po.option_alias = ucp.profile_key', 'left');
        $this->db->in('u.idu', $usersIds);

        $queryResult = $this->db->get();

        return empty($queryResult) ? [] : arrayByKey($queryResult, 'idu', true);
    }

    public function is_profile_option_completed($id_user = null, $profile_key = null): bool
    {
        if (null === $id_user || null === $profile_key) {
            return false;
        }

        $this->db->select('COUNT(*) as option_completed');
        $this->db->from($this->users_complete_profile);
        $this->db->where('id_user = ?', (int) $id_user);
        $this->db->where('profile_key =?', $profile_key);

        $record = $this->db->get_one();

        return $record['option_completed'] > 0;
    }

    public function update_user_profile_option($id_user = null, $profile_key = null)
    {
        if (!$this->is_profile_option_completed($id_user, $profile_key)) {
            $this->db->insert($this->users_complete_profile, array(
                'id_user'     => $id_user,
                'profile_key' => $profile_key,
            ));

            /** @var Crm_Model $crmModel */
            $crmModel = model(Crm_Model::class);

            $crmModel->create_or_update_record($id_user);
        }

        return true;
    }

    public function delete_user_profile_option($user_id, $option)
    {
        if (empty($user_id) || empty($option)) {
            return false;
        }

        $this->db->where('id_user = ?', $user_id);
        $this->db->where('profile_key = ?', $option);

        $response = $this->db->delete($this->users_complete_profile);

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

        $crmModel->create_or_update_record($user_id);

        return $response;
    }

    public function delete_profile_option_for_users(array $users, $option)
    {
        if (empty($users) || empty($option)) {
            return false;
        }

        $this->db->in('id_user', $users);
        $this->db->where('profile_key = ?', $option);

        $response = $this->db->delete($this->users_complete_profile);

        /** @var Crm_Model $crmModel */
        $crmModel = model(Crm_Model::class);

        foreach ($users as $id_user) {
            $crmModel->create_or_update_record($id_user);
        }

        return $response;
    }

    /**
     * @param int $userId
     */
    public function delete_all_user_profile_options(int $userId)
    {
        $this->db->where('id_user', $userId);
        return $this->db->delete($this->users_complete_profile);
    }
}
