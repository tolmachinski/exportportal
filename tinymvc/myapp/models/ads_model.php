<?php

/**
 * ads_model.php
 *
 * model for advertising
 *
 * @package		TinyMVC
 * @author		Litra Andrei
 */

class Ads_Model extends TinyMVC_Model{
    function selectPlaces(){
        $sql = "SELECT *
        		FROM ads_place
        		ORDER BY id";

        return $this->db->query_all($sql);
    }

    function insertPlace($place_info){
        $this->db->insert('ads_place', $place_info);
        return $this->db->last_insert_id();
    }

    function updatePlace($place_info){
        $this->db->where('id', $place_info['id']);
		return $this->db->update('ads_place',$place_info);
    }

    function deletePlace($id){
        $this->db->where('id', $id);
		return $this->db->delete('ads_place');
    }

    function insertAds($data){
        $ads_info = array(
            'title' => $data['title'],
            'file_name' => $data['file_name'],
            'type' => $data['type'],
            'alt' => $data['alt'],
            'url' => $data['url']
        );

        $this->db->insert('ads', $ads_info);
        return $this->db->last_insert_id();
    }

    function updateAds($ads_info){
        $this->db->where('id', $ads_info['id']);
		return $this->db->update('ads',$ads_info);
    }

    function insertCompany($data){
        $company_info = array(
            'user' =>$data['user'],
            'company_n' => $data['company_n'],
            'contact_n' => $data['contact_n'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'about_c' => $data['about_c']
        );

        $this->db->insert('ads_company', $company_info);
        return $this->db->last_insert_id();
    }

    function insertOrder($data){
        $order_info = array(
            'ads' => $data['ads'],
            'company' => $data['company'],
            'place' => $data['place'],
            'time' => $data['time'],
            'price' => $data['price'],
            'status' => $data['status'],
            'hash_info' => $data['hash_info']
        );

        $this->db->insert('ads_order', $order_info);
        return $this->db->last_insert_id();
    }

    function updateOrder($data){
        $this->db->where('id', $data['id']);
		return $this->db->update('ads_order',$data);
    }

    function selectOrders(){
        $sql = "SELECT o.id, o.time, o.price, o.begin_date, o.end_date, o.last_mod,
	        		b.id as banner_id, b.title,
	        		c.id AS company_id, c.company_n,
	        		p.place_name,
	        		s.id as status_id, s.status
                FROM ads_order o
                LEFT JOIN ads b ON o.ads = b.id
                LEFT JOIN ads_company c ON o.company = c.id
                LEFT JOIN ads_place p ON o.place = p.id
                LEFT JOIN ads_order_status s ON o.status = s.id
                ORDER BY id DESC ";
        return $this->db->query_all($sql);
    }

    function selectBanner($id){
        $sql = "SELECT * FROM ads WHERE id=?";
        return $this->db->query_one($sql, array($id));
    }

    function selectCompany($id){
        $sql = "SELECT * FROM ads_company WHERE id=?";
        return $this->db->query_one($sql, array($id));
    }

    function selectOrder($id){
        $sql = "SELECT * FROM ads_order WHERE id=?";
        return $this->db->query_one($sql, array($id));
    }

    function selectOrderByHash($hash){
        $sql = "SELECT * FROM ads_order WHERE hash_info=?";
        return $this->db->query_one($sql, array($hash));
    }

    function changeStatus($order, $status){
        $this->db->where('id', $order);
        return $this->db->update('ads_order', array('status' => $status));
    }

    function updateAdsDate($id, $end_date){
        $this->db->where('id', $id);
		return $this->db->update('ads',array('end_date'=>$end_date));
    }

    function updateDate($id, $begin_date, $end_date){
        $this->db->where('id', $id);
		return $this->db->update('ads_order',array('begin_date'=>$begin_date,'end_date'=>$end_date));
    }

    function selectMails(){
        $sql = "SELECT * FROM ads_email_template ORDER BY id";
        return $this->db->query_all($sql);
    }

    function selectMail($id){
        $sql = "SELECT * FROM ads_email_template WHERE id=?";
        return $this->db->query_one($sql, array($id));
    }

    function insertMail($ads_email){
        $this->db->insert('ads_email_template', $ads_email);
        return $this->db->last_insert_id();
    }

    function updateMail($ads_email){
        $this->db->where('id', $ads_email['id']);
		return $this->db->update('ads_email_template',$ads_email);
    }

    function deleteMail($id){
        $this->db->where('id', $id);
		return $this->db->delete('ads_email_template');
    }
}
