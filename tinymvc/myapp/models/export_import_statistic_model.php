<?php

class Export_Import_Statistic_Model extends TinyMVC_Model {

    private $export_import_statistic_table = 'export_import_statistic';

    function get_plot_data($country) {
        $sumExportImport = $this->db->query_one("
              SELECT
                  SUM(export_value) as sum_export_value,
                  SUM(import_value) as sum_import_value
              FROM {$this->export_import_statistic_table}
              WHERE source = ?
        ", array($country));


        $sumExport = $sumExportImport['sum_export_value'];
        $sumImport = $sumExportImport['sum_import_value'];


        $topExportProducts = $this->db->query_all("
            SELECT eip.name, SUM(eis.export_value) as amount
            FROM {$this->export_import_statistic_table} eis
            LEFT JOIN export_import_products eip ON eip.id = eis.sitc_id
            WHERE eis.source = ?
            GROUP BY eis.sitc_id
            ORDER BY amount DESC
            LIMIT 15
        ", array($country));


        $topImportProducts = $this->db->query_all("
            SELECT eip.name, SUM(eis.import_value) as amount
            FROM {$this->export_import_statistic_table} eis
            LEFT JOIN export_import_products eip ON eip.id = eis.sitc_id
            WHERE eis.source = ?
            GROUP BY eis.sitc_id
            ORDER BY amount DESC
            LIMIT 15
        ", array($country));


        $topExportCountries = $this->db->query_all("
            SELECT pc.country, SUM(eis.export_value) as amount
            FROM {$this->export_import_statistic_table} eis
            LEFT JOIN port_country pc ON pc.abr3 = eis.destination
            WHERE eis.source = ?
            GROUP BY eis.destination
            ORDER BY amount DESC
            LIMIT 15
        ", array($country));


        $topImportCountries = $this->db->query_all("
            SELECT pc.country, SUM(eis.import_value) as amount
            FROM {$this->export_import_statistic_table} eis
            LEFT JOIN port_country pc ON pc.abr3 = eis.destination
            WHERE eis.source = ?
            GROUP BY eis.destination
            ORDER BY amount DESC
            LIMIT 15
        ", array($country));


        return array(
            'topExportProducts' => $this->compose_percents($topExportProducts),
            'topImportProducts' => $this->compose_percents($topImportProducts),
            'topExportCountries' => $this->compose_percents($topExportCountries),
            'topImportCountries' => $this->compose_percents($topImportCountries)
        );
    }


    private function compose_percents($items) {
        $sum = 0;
        foreach ($items as $key => $item) {
            $sum += $items[$key]['amount'];
        }

        foreach ($items as $key => $item) {
            $items[$key]['percent'] = $items[$key]['amount'] * 100 / $sum;
        }

        return $items;
    }


    function get_template_data($country) {
        $countryResult = $this->db->query_one("
            SELECT * FROM port_country WHERE abr3 = ?
        ", array($country));

        if (empty($countryResult)) {
            return false;
        }


        $mostExportProduct = $this->db->query_one("
            SELECT eip.name, eis.year, eis.export_value FROM export_import_statistic eis
            LEFT JOIN export_import_products eip ON eip.id = eis.sitc_id
            WHERE eis.source = ?
            ORDER BY eis.export_value DESC
            LIMIT 1
        ", array($country));


        $mostImportProduct = $this->db->query_one("
            SELECT eip.name, eis.import_value FROM export_import_statistic eis
            LEFT JOIN export_import_products eip ON eip.id = eis.sitc_id
            WHERE eis.source = ?
            ORDER BY eis.import_value DESC
            LIMIT 1
        ", array($country));


        $topExportCountries = $this->db->query_all("
            SELECT pc.country as name, SUM(eis.export_value) as amount FROM export_import_statistic eis
            LEFT JOIN port_country pc ON pc.abr3 = eis.destination
            WHERE eis.source = ?
            GROUP BY eis.destination
            ORDER BY amount DESC
            LIMIT 5
        ", array($country));


        $topImportCountries = $this->db->query_all("
            SELECT pc.country as name, SUM(eis.import_value) as amount FROM export_import_statistic eis
            LEFT JOIN port_country pc ON pc.abr3 = eis.destination
            WHERE eis.source = ?
            GROUP BY eis.destination
            ORDER BY amount DESC
            LIMIT 5
        ", array($country));


        $topExportProducts = $this->db->query_all("
            SELECT eip.name, SUM(eis.export_value) as amount FROM export_import_statistic eis
            LEFT JOIN export_import_products eip ON eip.id = eis.sitc_id
            WHERE eis.source = ?
            GROUP BY eis.sitc_id
            ORDER BY amount DESC
            LIMIT 5
        ", array($country));


        $topImportProducts = $this->db->query_all("
            SELECT eip.name, SUM(eis.import_value) as amount FROM export_import_statistic eis
            LEFT JOIN export_import_products eip ON eip.id = eis.sitc_id
            WHERE eis.source = ?
            GROUP BY eis.sitc_id
            ORDER BY amount DESC
            LIMIT 5
        ", array($country));



        $data = array(
            'country_name' => $countryResult['country'],
            'year' => $mostExportProduct['year'],
            'most_export_product_name' => $mostExportProduct['name'],
            'most_export_product_amount' => $this->composeAmountValue($mostExportProduct['export_value']),
            'most_import_product_name' => $mostImportProduct['name'],
            'most_import_product_amount' => $this->composeAmountValue($mostImportProduct['import_value']),
            'top_export_countries' => $this->processTopItems($topExportCountries),
            'top_import_countries' => $this->processTopItems($topImportCountries),
            'top_export_products' => $this->processTopItems($topExportProducts),
            'top_import_products' => $this->processTopItems($topImportProducts),
        );

        return $data;
    }



    private function composeAmountValue($value) {
        if ($value >= 1000000000) {
            $value = number_format($value / 1000000000, 2) . 'B';
        } elseif ($value >= 1000000) {
            $value = number_format($value / 1000000, 2) . 'M';
        }

        return "\${$value}";
    }


    private function processTopItems($items) {
        $itemsProcessed = array();
        foreach ($items as $item) {
            $amount = $this->composeAmountValue($item['amount']);
            $itemsProcessed[] = "{$item['name']} - {$amount}";
        }

        return implode(', ', $itemsProcessed);
    }



}


