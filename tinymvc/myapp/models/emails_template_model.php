<?php

use App\Common\Database\BaseModel;

class Emails_Template_Model extends BaseModel
{
    public $emailsTemplateTable = 'emails_template';
    public $emailsTemplateStructureTable = 'emails_template_structure';

    public function getEmailsTemplates($conditions = [])
    {
        $orderBy = 'e.id_emails_template DESC';

        if (isset($conditions['orderBy'])) {
            $multiOrderBy = [];
            foreach ($conditions['orderBy'] as $sortItem) {
                $sortItem = explode('-', $sortItem);
                $multiOrderBy[] = $sortItem[0] . ' ' . $sortItem[1];
            }

            $orderBy = implode(',', $multiOrderBy);
        }

        $this->db->select('e.*, et.name as structure_name');
        $this->db->from("{$this->emailsTemplateTable} AS e");
        $this->db->join("{$this->emailsTemplateStructureTable} AS et", 'e.id_emails_template_structure = et.id_emails_template_structure', 'left');

        if (isset($conditions['email_structure'])) {
            $this->db->where('e.id_emails_template_structure', $conditions['email_structure']);
        }

        if (isset($conditions['proofread'])) {
            $this->db->where('e.proofread', $conditions['proofread']);
        }

        if (isset($conditions['keywords'])) {
            $this->db->where_raw('MATCH (e.name, e.content) AGAINST (?)', $conditions['keywords']);
        }

        $this->db->orderby($orderBy);

        if (isset($conditions['perP']) && isset($conditions['start'])) {
            $this->db->limit((int) $conditions['perP'], (int) $conditions['start']);
        }

        return $this->db->query_all();
    }

    public function getCountEmailsTemplates($conditions = [])
    {
        $this->db->select('COUNT(*) over() as counter');
        $this->db->from("{$this->emailsTemplateTable} AS e");
        $this->db->join("{$this->emailsTemplateStructureTable} AS et", 'e.id_emails_template_structure = et.id_emails_template_structure', 'left');

        if (isset($conditions['email_structure'])) {
            $this->db->where('e.id_emails_template_structure', $conditions['email_structure']);
        }

        if (isset($conditions['proofread'])) {
            $this->db->where('e.proofread', $conditions['proofread']);
        }

        if (isset($conditions['keywords'])) {
            $this->db->where_raw('MATCH (e.name, e.content) AGAINST (?)', $conditions['keywords']);
        }

        $temp = $this->db->query_one();

        return (int) $temp['counter'];
    }

    public function getEmailTemplate($idTemplate)
    {
        $this->db->select('e.*, et.name as structure_name, et.template, et.json_structure');
        $this->db->from("{$this->emailsTemplateTable} AS e");
        $this->db->where('e.id_emails_template', $idTemplate);
        $this->db->join("{$this->emailsTemplateStructureTable} AS et", 'e.id_emails_template_structure = et.id_emails_template_structure', 'left');

        return $this->db->query_one();
    }

    public function issetEmailTemplate($idTemplate)
    {
        $this->db->select('COUNT(*) over() as counter');
        $this->db->from("{$this->emailsTemplateTable} AS e");
        $this->db->where('e.id_emails_template', $idTemplate);

        $temp = $this->db->query_one();

        return (int) $temp['counter'];
    }

    public function issetEmailTemplateByAlias($alias)
    {
        $this->db->select('COUNT(*) over() as counter');
        $this->db->from("{$this->emailsTemplateTable} AS e");
        $this->db->where('e.alias_template', $alias);

        $temp = $this->db->query_one();

        return (int) $temp['counter'];
    }

    public function getEmailTemplateByAlias($alias)
    {
        $this->db->select('e.*, et.name as structure_name, et.template');
        $this->db->from("{$this->emailsTemplateTable} AS e");
        $this->db->where('e.alias_template', $alias);
        $this->db->join("{$this->emailsTemplateStructureTable} AS et", 'e.id_emails_template_structure = et.id_emails_template_structure', 'left');

        return $this->db->query_one();
    }

    public function updateEmailTemplate($id, $update)
    {
        $this->db->where('id_emails_template', $id);

        return $this->db->update($this->emailsTemplateTable, $update);
    }

    public function insertEmailTemplate($data)
    {
        return $this->db->insert($this->emailsTemplateTable, $data);
    }
}
