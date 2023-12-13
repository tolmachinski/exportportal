<?php

class Video_Tour_Model extends TinyMVC_Model {
    private $video_tour_table = "video_tour";

	public function get_video_tour($conditions){
		extract($conditions);

		if (!empty($page)){
            $this->db->where('page', $page);
		}

		if (!empty($user_group)){
            $this->db->where('user_group', $user_group);
		}

        $this->db->limit(1);

        return $this->db->get_one($this->video_tour_table);
    }
}


