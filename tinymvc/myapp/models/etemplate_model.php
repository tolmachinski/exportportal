<?php
class Etemplate_Model extends TinyMVC_Model
{
    function create_template($email_template) {
        $email_template = cleanInput($email_template);

        $this->db->insert('email_template', array('template_subject' => $email_template));

        return $this->db->last_insert_id();
    }

	function get_template($template_key){
		$sql = "SELECT *
				FROM email_template
				WHERE template_id = ?";
		return $this->db->query_one($sql, array($template_key));
	}

    function fetch_template_summary() {
        $this->db->query("SELECT * FROM email_template");

        if($this->db->numRows() > 0) {
            while($row = $this->db->next()) {
                $results[] = array('id' => $row['template_id'],
                                   'subject' => $row['template_subject']);
            }

            return $results;
        }

        return null;
    }

    function fetch_template_content($template_id) {
        $template_id = (int) $template_id;

        $this->db->query("SELECT * FROM email_template WHERE template_id = ?", $template_id);

        if($this->db->numRows() > 0) {
            while($row = $this->db->next()) {
                $results[] = array('id' => $row['template_id'],
                                   'subject' => $row['template_subject'],
                                   'message' => $row['template_message'],
                                   'fromname' => $row['template_fromname'],
                                   'fromemail' => $row['template_fromemail']);
            }

            return $results;
        }

        return null;
    }

    function update_template($template_id, $content) {
        $this->db->where('template_id', $template_id);
        $this->db->update('email_template', array('template_subject' => $content['subject'],
                                                  'template_message' => $content['message'],
                                                  'template_fromname' => $content['fromname'],
                                                  'template_fromemail' => $content['fromemail']
                                                  ));
    }

    function delete_template($template_id) {
        $template_id = (int)$template_id;

        $this->db->where('template_id', $template_id);
        $this->db->delete('email_template');
    }

    function validate_template_id($template_id) {
        $this->db->query("SELECT * FROM email_template WHERE template_id = ?", (int) $template_id);

        return $this->db->numRows() > 0;
    }

    function validate_order_id($order_id) {
    	return $this->db->query("SELECT * FROM orders WHERE id = ?", (int) $order_id);
    }

    function select_order_users($order_id) {
    	return $this->db->query_all("SELECT `id_buyer`,`id_seller` FROM orders WHERE id = ?", (int) $order_id);
    }

    function send_mass_email($user_level, $template_id) {
        $template = $this->db->query_one("SELECT * FROM email_template WHERE template_id = ?", $template_id);

        $this->db->where('active', 1);

        if (0 != $user_level) {
            $this->db->where('user_level', $user_level);
        }

        $data = $this->db->get('user');

        foreach($data as $row) {
            $userSubject = $template['template_subject'];
            $userMessage = $template['template_message'];
            foreach($row as $key => $value) {
                $userSubject = str_replace("[$key]", $value, $userSubject);
                $userMessage = str_replace("[$key]", $value, $userMessage);
            }
            mail($row['email'], $userSubject, $userMessage, "From: " . $template['template_fromname'] . "<" . $template['template_fromemail'] . ">\r\nReply-To: " . $template['template_fromemail']);
        }
    }
}
