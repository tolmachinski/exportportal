<?php

/**
 * @deprecated 2.39.0 use Auth_Context_Model
 */

class Auth_Model extends TinyMVC_Model {

    private $auth_table = 'auth_context_form';

    /**
     * Check if hash exists by token email and token password
     */
    public function exists_hash($token_email, $token_password = '')
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->auth_table);

        //region Conditions
        $this->db->where('token_email', $token_email);
        if(!empty($token_password)){
            $this->db->where('token_password', $token_password);
        }
        //endregion Conditions

        $counter = $this->db->query_one();
        if (empty($counter)) {
            return false;
        }

        return (bool) (int) arrayGet($counter, 'AGGREGATE');
    }

    /**
     * Get hashes by token email and token password
     */
    function get_hash($token_email, $token_password = '')
    {
        $this->db->select('*');
        $this->db->from($this->auth_table);
        $this->db->where('token_email', $token_email);
        if(!empty($token_password)){
            $this->db->where('token_password', $token_password);
        }

        return $this->db->query_one();
    }

    /**
     * Get one by id_principal
     */
    function get_hash_by_id_principal($id_principal)
    {
        $this->db->select('*');
        $this->db->from($this->auth_table);
        $this->db->where('id_principal', $id_principal);

        return $this->db->query_one();
    }

    /**
     * Add hash to table
     *
     * @array $data - insert data
     */
    function add_hash($id_principal, $data)
    {
        $this->db->insert(
            $this->auth_table,
            array_merge(
                $data,
                array('id_principal' => $id_principal)
            )
        );

    }

    /**
     * Change hash when email or password is changed
     *
     * @int $id_user
     * @array $data
     */
    function change_hash($id_principal, $data)
    {
        $this->db->where('id_principal', $id_principal);
        return $this->db->update($this->auth_table, $data);
    }

    /**
     * Delete one record by id principal
     */
    function delete_one($id_principal)
    {
        $this->db->where('id_principal', $id_principal);
		$this->db->delete($this->auth_table);
    }

}
