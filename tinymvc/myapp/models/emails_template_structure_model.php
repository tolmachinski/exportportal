<?php

use App\Common\Database\BaseModel;

class Emails_Template_Structure_Model extends BaseModel
{
    public $emailsTemplateStructureTable = 'emails_template_structure';

    public function getEmailsTemplatesStructure($conditions = [])
    {
        $orderBy = 'id_emails_template_structure DESC';

        $this->db->select('*');
        $this->db->from($this->emailsTemplateStructureTable);
        $this->db->orderby($orderBy);

        if (isset($conditions['per_p'], $conditions['start'])) {
            $this->db->limit((int) $conditions['per_p'], (int) $conditions['start']);
        }

        return $this->db->query_all();
    }

    public function getCountEmailsTemplates()
    {
        $this->db->select('COUNT(*) over() as counter');
        $this->db->from($this->emailsTemplateStructureTable);
        $temp = $this->db->query_one();

        return (int) $temp['counter'];
    }

    public function getEmailTemplate($idStructure)
    {
        $this->db->from($this->emailsTemplateStructureTable);
        $this->db->where('id_emails_template_structure', $idStructure);

        return $this->db->query_one();
    }

    public function issetEmailTemplate($idStructure)
    {
        $this->db->select('COUNT(*) over() as counter');
        $this->db->from($this->emailsTemplateStructureTable);
        $this->db->where('id_emails_template_structure', $idStructure);

        $temp = $this->db->query_one();

        return (int) $temp['counter'];
    }
}
