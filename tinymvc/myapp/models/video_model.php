<?php

/**
 *
 *
 * model for video
 *
 * @author
 */

class Video_Model extends TinyMVC_Model
{
    private $videos_table = "video";

	public function setVideo($data){
        return empty($data) ? false : $this->db->insert($this->videos_table, $data);
	}

	public function existVideo($id_video){
        $this->db->select('COUNT(*) as exist');
        $this->db->where('id_video', $id_video);
        $this->db->limit(1);

        return $this->db->get_one($this->videos_table)['exist'];
	}

	public function updateVideo($id, $data){
        $this->db->where('id_video', $id);
        return $this->db->update($this->videos_table, $data);
    }

	public function deleteVideo($id){
		$this->db->where('id_video', $id);
		return $this->db->delete($this->videos_table);
	}

	public function getVideo($id){
        $this->db->where('id_video', $id);
        $this->db->limit(1);

        return $this->db->get_one($this->videos_table);
    }

    /**
     * @param null|array $conditions['short_names']
     */
	public function getVideos($conditions = []) {
        if (!empty($conditions['short_names'])) {
            $this->db->in('short_name', $conditions['short_names']);
        }

        return $this->db->get($this->videos_table);
    }
}
