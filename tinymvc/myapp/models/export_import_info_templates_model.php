<?php

class Export_Import_Info_Templates_Model extends TinyMVC_Model {

    private $export_import_info_templates_table = 'export_import_info_templates';

    function get_templates() {
        $sql = "
            SELECT * FROM {$this->export_import_info_templates_table}
        ";

        return $this->db->query_all($sql);
    }


    function updateTemplates($templates) {
        $cases = $params = [];
        foreach ($templates as $key => $text) {
            $cases[] = "WHEN `key` = ? THEN ?";
            $params[] = $key;
            $params[] = $text;
        }

        if (empty($cases)) {
            return false;
        }

        $cases = implode(' ', $cases);

        $sql = "
            UPDATE {$this->export_import_info_templates_table}
            SET `text` = CASE
              $cases
            ELSE `text` END
        ";

        return $this->db->query($sql, $params);
    }

}


