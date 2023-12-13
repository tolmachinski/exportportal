<?php
/**
 * user_photo_model.php
 *
 * users model
 *
 * @author
 * 
 * @deprecated 2.39.0 use User_Photos_Model
 */

class User_photo_Model extends TinyMVC_Model
{
	private $user_photo_table = "user_photo";
	public $path_to_logo_img = "public/img/users";

	function set_photo($data){
		return $this->db->insert($this->user_photo_table, $data);
	}

	function set_multi_photo($data){
		$this->db->insert_batch($this->user_photo_table, $data);
		return $this->db->getAffectableRowsAmount();
	}

	function get_photo($conditions){
        extract($conditions);

		if(isset($name_photo)){
            $this->db->where('name_photo', $name_photo);
        }

		if(isset($id_user)){
            $this->db->where('id_user', $id_user);
        }

		if(isset($id_photo)){
            $this->db->where('id_photo', $id_photo);
        }

        $this->db->limit(1);

        return $this->db->get_one($this->user_photo_table);
    }

    function get_users_photo($conditions){
        extract($conditions);

        $start = (int) ($start ?? 0);
        $per_p = (int) ($per_p ?? 50);

        $this->db->select('id_user');
        $this->db->where_raw("thumb_photo != ''");

        if(isset($actulized_photo)){
            $this->db->where('actulized_photo', $actulized_photo);
        }

        if(isset($id_user)){
            $this->db->where('id_user', $id_user);
        }

        $this->db->limit($per_p, $start);
        $this->db->groupby('id_user');

        return $this->db->get($this->user_photo_table);
    }

	function get_photos($conditions){
		extract($conditions);

        if(isset($actulized_photo)){
            $this->db->where('actulized_photo', $actulized_photo);
        }

        if (!empty($id_user)) {
            $this->db->where('id_user', $id_user);
        } elseif (!empty($users_list)) {
            $users_list = getArrayFromString($users_list);
            $this->db->in('id_user', $users_list);
        }

		if(!empty($photo_names)){
            $photo_names = getArrayFromString($photo_names);
            $this->db->in('name_photo', $photo_names);
        }

        return $this->db->get($this->user_photo_table);
    }

	function count_photos($conditions){
		extract($conditions);

        $this->db->select('COUNT(*) as counter');
        $this->db->where('id_user', $id_user);
        $this->db->limit(1);

        return $this->db->get_one($this->user_photo_table)['counter'];
    }

    function update_photo($id_photo, $data){
		$this->db->where('id_photo', $id_photo);
        return $this->db->update($this->user_photo_table, $data);
    }

    function update_photo_by_conditions(array $data, array $conditions){
        if (empty($conditions)) {
            return false;
        }

        if (isset($conditions['id_user'])) {
            $this->db->where('id_user', (int) $conditions['id_user']);
        }

        if (isset($conditions['name_photo'])) {
            $this->db->where('name_photo', $conditions['name_photo']);
        }

        return $this->db->update($this->user_photo_table, $data);
    }

	function delete_photo($id_photo){
		$this->db->where('id_photo = ?', array($id_photo));
        return $this->db->delete($this->user_photo_table);
	}
}
