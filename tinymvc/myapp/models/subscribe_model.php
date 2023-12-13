<?php

class Subscribe_Model extends TinyMVC_Model {

	protected $subscribe_table = 'subscribers_base';
	protected $unsubscribe_table = 'unsubscribers_base';
    protected $unsubscribersFeedback = 'unsubscribers_feedback';

	function add_subscriber($name, $email){
		return $this->db->insert($this->subscribe_table, array('subscriber_name' => $name, 'subscriber_email' => $email, 'subscriber_date' => time()));
	}

    public function insertSubscriber($data = [])
    {
        return $this->db->insert($this->subscribe_table, $data);
    }

    public function existSubscriber($email)
    {
        $this->db->select('COUNT(*) as counter');
        $this->db->from($this->subscribe_table);
        $this->db->where('subscriber_email', $email);

        return $this->db->query_one()['counter'];
    }

    public function checkIsConfirmed($email)
    {
        $this->db->select('confirmed');
        $this->db->from($this->subscribe_table);
        $this->db->where('subscriber_email', $email);

        return $this->db->query_one()['confirmed'] == 1 ? true : false;
    }

    public function updateConfirmedStatus($email, $status = '0')
    {
        $this->db->where('subscriber_email', $email);

        return $this->db->update(
            $this->subscribe_table,
            [
                'confirmed' => $status,
            ]
        );
    }

    public function getSubscriberByToken($token)
    {
        $this->db->select('*');
        $this->db->from($this->subscribe_table);
        $this->db->where('token_email', $token);
        return $this->db->query_one();
    }

    public function getSubscriberByEmail($email)
    {
        $this->db->select('*');
        $this->db->from($this->subscribe_table);
        $this->db->where('subscriber_email', $email);
        return $this->db->query_one();
    }

	function insert_whatsapp_subscriber($data = array()){
        return empty($data) ? false : $this->db->insert('contacts_whatsapp', $data);
	}

	function exist_whatsapp_subscriber($whatsapp_number_clean = 0) {
        $this->db->select('COUNT(*) as counter');
        $this->db->where('whatsapp_number_clean', $whatsapp_number_clean);
        $this->db->limit(1);

        return $this->db->get_one('contacts_whatsapp')['counter'];
    }

    function get_all_subscribers(){
        return $this->db->get($this->subscribe_table);
    }

	function check_unsubscriber_exists($email)
	{
		$this->db->select('COUNT(*) AS AGGREGATE');
		$this->db->from($this->unsubscribe_table);
		$this->db->where('email', $email);

		return (bool) (int) ($this->db->query_one()['AGGREGATE'] ?? false);
	}

	function unsubscribe_email($email)
	{
		return $this->db->insert($this->unsubscribe_table, array('email' => $email));
	}

    public function getUnsubscribeReasons()
    {
        return [
            '1'     => translate('unsubscribe_no_longer_in_the_international_trade inustry'),
            '2'     => translate('unsubscribe_no_longer_interested_in_the_exports_industry'),
            '3'     => translate('unsubscribe_not_sure'),
            '4'     => translate('unsubscribe_not_find_the_content_interesting'),
            '5'     => translate('unsubscribe_have_received_too_many_emails'),
            'other' => translate('unsubscribe_other_reason_txt'),
        ];
    }

    public function insertSubscriberFeedback($data)
    {
        return empty($data) ? false : $this->db->insert($this->unsubscribersFeedback, $data);
    }

    public function deleteSubscriber($id) {
        $this->db->where('subscriber_id', $id);
        return $this->db->delete($this->subscribe_table);
    }

    public function getUnsubscriberByEmail($email) {
        $this->db->select('*');
        $this->db->from($this->unsubscribe_table);
        $this->db->where('email', $email);
        return $this->db->query_one();
    }

    public function deleteUnsubscriber($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->unsubscribe_table);
    }
}
