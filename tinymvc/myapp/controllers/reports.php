<?php

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\Style\Fill;
use \PhpOffice\PhpSpreadsheet\Style\Border;
use \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Reports_Controller extends TinyMVC_Controller
{
    public const REPORT_GROUPED_USERS_BY_COUNTRY_ID = 1;
    public const REPORT_USERS_YOY_ID = 2;
    public const REPORT_USERS_QOQ_ID = 3;
    public const REPORT_USERS_MOM_ID = 4;
    public const REPORT_USERS_MOM_BY_USER_TYPES_ID = 5;
    public const REPORT_USERS_YOY_BY_USER_TYPES_ID = 6;
    public const REPORT_USERS_SELLERS_PRODUCTS_ID = 7;
    public const REPORT_USERS_SELLERS_MANUFACTURERS_ID = 8;
    public const REPORT_PRODUCTS_ACTIVE_ID = 9;
    public const REPORT_PRODUCTS_DRAFT_ID = 10;
    public const REPORT_SELLERS_PER_INDUSTRY_ID = 11;
    public const REPORT_BUYERS_PER_INDUSTRY_ID = 12;
    public const REPORT_ITEMS_PER_INDUSTRY_ID = 13;

    public const BUYERS_GROUP_ID = 1;
    public const VERIFIED_SELLER_GROUP_ID = 2;
    public const CERTIFIED_SELLER_GROUP_ID = 3;
    public const VERIFIED_MANUFACTURER_GROUP_ID = 5;
    public const CERTIFIED_MANUFACTURER_GROUP_ID = 6;
    public const SHIPPERS_GROUP_ID = 31;

    public function index(): void
    {
        checkAdmin('export_db_reports');

        /** @var Reports_Model $reportsModel */
        $reportsModel = model(Reports_Model::class);

        views(
            [
                'admin/header_view',
                'admin/reports/index_view',
                'admin/footer_view'
            ],
            [
                'reports'   => $reportsModel->get_reports(),
                'title'     => 'Make reports'
            ],
        );
    }

    public function download_report()
    {
        if (!have_right('export_db_reports')) {
            die(translate('systmess_error_permission_not_granted'));
        }

        /** @var Reports_Model $reportsModel */
        $reportsModel = model(Reports_Model::class);

        if (empty($id_report = (int) $_GET['report']) || empty($report = $reportsModel->get_report($id_report))) {
            die('Invalid report id.');
        }

        switch ($id_report) {
            case static::REPORT_GROUPED_USERS_BY_COUNTRY_ID:
                $file_name = empty($_GET['focus_countries']) ? 'Grouped_users_by_country_' : 'Grouped_users_by_Focus_countries_';
                $file_name .= date('m-d-Y H:i') . '.xlsx';
                $excel_file = $this->grouped_users_by_country();

                break;
            case static::REPORT_USERS_YOY_ID:
                $file_name = empty($_GET['focus_countries']) ? 'User report - YOY - ' : 'User report - YOY by Focus countries - ';
                $file_name .= date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->users_yoy();

                break;
            case static::REPORT_USERS_QOQ_ID:
                $file_name = empty($_GET['focus_countries']) ? 'User report - QOQ - ' : 'User report - QOQ Focus Countries - ';
                $file_name .= date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->users_qoq();

                break;
            case static::REPORT_USERS_MOM_ID:
                $file_name = empty($_GET['focus_countries']) ? 'User report - MOM - ' : 'User report - MOM Focus Countries - ';
                $file_name .= date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->users_mom();

                break;
            case static::REPORT_USERS_MOM_BY_USER_TYPES_ID:
                $file_name = empty($_GET['focus_countries']) ? 'User report - MOM by user types - ' : 'User report - MOM by user types Focus Countries - ';
                $file_name .= date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->users_mom_by_user_types();

                break;
            case static::REPORT_USERS_YOY_BY_USER_TYPES_ID:
                $file_name = empty($_GET['focus_countries']) ? 'User report - YOY by user types - ' : 'User report - YOY by user types Focus Countries - ';
                $file_name .= date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->users_yoy_by_user_types();

                break;
            case static::REPORT_USERS_SELLERS_PRODUCTS_ID:
                $file_name = 'User report - Sellers and Products - ';
                $file_name .= date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->sellers_and_products();

                break;
            case static::REPORT_USERS_SELLERS_MANUFACTURERS_ID:
                $file_name = 'Report on Products - Sellers and Manufacturers - ' . date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->sellersAndManufacturers();

                break;
            case static::REPORT_PRODUCTS_ACTIVE_ID:
                $file_name = 'Report on Products - Products Active - ' . date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->productsActive();

                break;
            case static::REPORT_PRODUCTS_DRAFT_ID:
                $file_name = 'Report on Products - Products Draft - ' . date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->productsDraft();

                break;
            case static::REPORT_SELLERS_PER_INDUSTRY_ID:
                $file_name = 'Report on Products - Sellers per Industry - ' . date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->sellersPerIndustry();

                break;
            case static::REPORT_BUYERS_PER_INDUSTRY_ID:
                $file_name = 'Report on Products - Buyers per Industry - ' . date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->buyersPerIndustry();

                break;
            case static::REPORT_ITEMS_PER_INDUSTRY_ID:
                $file_name = 'Report on Products - Products per Industry - ' . date('m-d-Y H:i') . '.xlsx';

                $excel_file = $this->productsPerIndustry();

                break;
            default:
                # code...
                break;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '"');
		header('Cache-Control: max-age=0');
		$objWriter = IOFactory::createWriter($excel_file, 'Xlsx');
        $objWriter->save('php://output');
    }

    public function export_subscribers()
    {
        if (!have_right('export_db_reports')) {
            die(translate('systmess_error_permission_not_granted'));
        }

        /**
         * @var Subscribe_Model $subscribers_model
         */
        $subscribers_model = model(Subscribe_Model::class);
        $subscribers = $subscribers_model->get_all_subscribers();

        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $active_sheet->setTitle('EP Subscribers');

        $row_index = 1;

        $active_sheet->getColumnDimension('A')->setWidth(30);
        $active_sheet->getStyle('A' . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->setCellValue('A' . $row_index, 'Subscribe date')
                    ->getStyle('A' . $row_index)
                        ->getFont()
                        ->setSize(12)
                        ->setBold(true);

        $active_sheet->getColumnDimension('B')->setWidth(50);
        $active_sheet->getStyle('B' . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->setCellValue('B' . $row_index, 'Email')
                    ->getStyle('B' . $row_index)
                        ->getFont()
                        ->setSize(12)
                        ->setBold(true);

        $row_index++;

        foreach ($subscribers as $subscriber) {
            $active_sheet->setCellValueExplicit('A' . $row_index, getDateFormat($subscriber['subscriber_date'], null, 'j M, Y'), DataType::TYPE_STRING);
            $active_sheet->setCellValueExplicit('B' . $row_index, $subscriber['subscriber_email'], DataType::TYPE_STRING)->getStyle('B' . $row_index);

            $row_index++;
        }

        $file_name = 'List of EP subscribers on ' . date('m-d-Y H:i') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '"');
		header('Cache-Control: max-age=0');
		$objWriter = IOFactory::createWriter($excel, 'Xlsx');
        $objWriter->save('php://output');
    }

    private function grouped_users_by_country(): Spreadsheet
    {
        /** @var Countries_Model $countryModel */
        $countryModel = model(Countries_Model::class);
        $countriesTable = $countryModel->getTable();

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $b_g_columns = array('B', 'C', 'D', 'E', 'F', 'G');
        $b_h_columns = array('B', 'C', 'D', 'E', 'F', 'G', 'H');

        // The order of the columns and their meaning cannot be changed
        $first_row_columns = array(
            'A' => array(
                'title' => 'Country',
                'width' => 45,
            ),
            'B' => array(
                'title' => 'Buyer',
                'width' => 20,
            ),
            'C' => array(
                'title' => 'Certified Manufacturer',
                'width' => 25,
            ),
            'D' => array(
                'title' => 'Certified Seller',
                'width' => 20,
            ),
            'E' => array(
                'title' => 'Freight Forwarder',
                'width' => 25,
            ),
            'F' => array(
                'title' => 'Verified Manufacturer',
                'width' => 25,
            ),
            'G' => array(
                'title' => 'Verified Seller',
                'width' => 20,
            ),
            'H' => array(
                'title' => 'Grand Total',
                'width' => 15,
            ),
        );

        $usersConditions = [
            'isFake'    => false,
            'groups'    => [1, 2, 3, 5, 6, 31],
        ];

        $applied_filters = $usersCounters = [];

        if (!empty($_GET['reg_date_from'])) {
            $usersConditions['registrationDateGte'] = \DateTimeImmutable::createFromFormat('m/d/Y', $_GET['reg_date_from']);
            $applied_filters['Registered from'] = $usersConditions['registrationDateGte']->format('Y-m-d');
        }

        if (!empty($_GET['reg_date_to'])) {
            $usersConditions['registrationDateLte'] = \DateTimeImmutable::createFromFormat('m/d/Y', $_GET['reg_date_to']);
            $applied_filters['Registered to'] = $usersConditions['registrationDateLte']->format('Y-m-d');
        }

        if (!empty($_GET['focus_countries'])) {
            $usersConditions['fromFocusCountry'] = true;
            $applied_filters['Only focus country'] = 'YES';
        }

        $usersCounters = $usersModel->findAllBy([
            'columns'       => [
                "`{$usersTable}`.`user_group`",
                "`{$countriesTable}`.`country`",
                "`{$countriesTable}`.`country` as countryName",
                "`{$countriesTable}`.`is_focus_country`",
                "COUNT(*) as counter"
            ],
            'conditions'    => $usersConditions,
            'joins'         => ['countries'],
            'group'         => [
                "`{$usersTable}`.`user_group`",
                "`{$usersTable}`.`country`",
            ],
            'order'         => [
                "`{$countriesTable}`.`country`" => 'ASC'
            ],
        ]);

        foreach ($usersCounters as $row) {
            $usersCountersByCountry[$row['countryName'] ?: 'No name']['isFocusCountry']  = (bool) (int) $row['is_focus_country'];
            $usersCountersByCountry[$row['countryName'] ?: 'No name'][$row['user_group']] = (int) $row['counter'];
        }

        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $active_sheet->setTitle('UT');

        $row_index = 1;

        if (!empty($applied_filters)) {
            $active_sheet->mergeCells("A{$row_index}:H{$row_index}");

            foreach ($applied_filters as $filter_name => $filter_value) {
                $applied_filters_raw[] = "[{$filter_name} => {$filter_value}]";
            }

            $applied_filters_title = 'Applied filters: ' . implode(' AND ',  $applied_filters_raw);

            $active_sheet->setCellValue('A' . $row_index, $applied_filters_title);

            $row_index++;
        }

        foreach ($first_row_columns as $column_index => $column_settings) {
            $active_sheet->getColumnDimension($column_index)->setWidth($column_settings['width']);
            $active_sheet->getStyle($column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->setCellValue($column_index . $row_index, $column_settings['title'])
                        ->getStyle($column_index . $row_index)
                            ->getFont()
                            ->setSize(12)
                            ->setBold(true);
        }

        $row_index++;

        // freeze first row and first column
        $active_sheet->freezePane('B' . $row_index);
        //align all table content
        $active_sheet->getStyle('A:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $focusCountriesRows = [];

        //region data by country
        foreach ($usersCountersByCountry as $countryName => $country) {
            $active_sheet->setCellValueExplicit('A' . $row_index, $countryName, DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);

            if ($country['isFocusCountry']) {
                $focusCountriesRows[] = "COLUMN_INDEX{$row_index}";
                $active_sheet->getStyle('A' . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF9FC5E8');
            }

            if (!empty($country[static::BUYERS_GROUP_ID])) {
                $active_sheet->setCellValueExplicit('B' . $row_index, $country[static::BUYERS_GROUP_ID], DataType::TYPE_NUMERIC);
            }

            if (!empty($country[static::CERTIFIED_MANUFACTURER_GROUP_ID])) {
                $active_sheet->setCellValueExplicit('C' . $row_index, $country[static::CERTIFIED_MANUFACTURER_GROUP_ID], DataType::TYPE_NUMERIC);
            }

            if (!empty($country[static::CERTIFIED_SELLER_GROUP_ID])) {
                $active_sheet->setCellValueExplicit('D' . $row_index, $country[static::CERTIFIED_SELLER_GROUP_ID], DataType::TYPE_NUMERIC);
            }

            if (!empty($country[static::SHIPPERS_GROUP_ID])) {
                $active_sheet->setCellValueExplicit('E' . $row_index, $country[static::SHIPPERS_GROUP_ID], DataType::TYPE_NUMERIC);
            }

            if (!empty($country[static::VERIFIED_MANUFACTURER_GROUP_ID])) {
                $active_sheet->setCellValueExplicit('F' . $row_index, $country[static::VERIFIED_MANUFACTURER_GROUP_ID], DataType::TYPE_NUMERIC);
            }

            if (!empty($country[static::VERIFIED_SELLER_GROUP_ID])) {
                $active_sheet->setCellValueExplicit('G' . $row_index, $country[static::VERIFIED_SELLER_GROUP_ID], DataType::TYPE_NUMERIC);
            }

            $active_sheet->setCellValueExplicit('H' . $row_index, "=SUM(B{$row_index}:G{$row_index})", DataType::TYPE_FORMULA)->getStyle('H' . $row_index)->getFont()->setBold(true);

            $row_index++;
        }
        //endregion data by country

        $last_row_index = $row_index-1;

        //region grand total
        $active_sheet->setCellValueExplicit('A' . $row_index, 'Grand Total', DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);
        foreach ($b_h_columns as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $row_index, "=SUM({$column_index}2:{$column_index}{$last_row_index})", DataType::TYPE_FORMULA)->getStyle($column_index . $row_index)->getFont()->setBold(true);
        }
        //endregion grand total

        $grand_total_index_row = $row_index++;

        //region value of each user type
        $active_sheet->setCellValueExplicit('A' . $row_index, 'Value of each user type', DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);
        foreach ($b_h_columns as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $row_index, "=CONCATENATE(IF(H{$grand_total_index_row} > 0, ROUND({$column_index}{$grand_total_index_row} / H{$grand_total_index_row} * 100, 2), 0), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $row_index)->getFont()->setBold(true);
        }
        //endregion value of each user type

        $row_index++;

        //region focus countries
        $active_sheet->setCellValueExplicit('A' . $row_index, 'Focus Countries', DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);
        if (!empty($focusCountriesRows)) {
            $active_sheet->setCellValueExplicit('B' . $row_index, "=SUM(" . str_replace('COLUMN_INDEX', 'B', implode(',', $focusCountriesRows)) . ")", DataType::TYPE_FORMULA)->getStyle('B' . $row_index)->getFont()->setBold(true);
            $active_sheet->setCellValueExplicit('C' . $row_index, "=SUM(" . str_replace('COLUMN_INDEX', 'C', implode(',', $focusCountriesRows)) . ")", DataType::TYPE_FORMULA)->getStyle('C' . $row_index)->getFont()->setBold(true);
            $active_sheet->setCellValueExplicit('D' . $row_index, "=SUM(" . str_replace('COLUMN_INDEX', 'D', implode(',', $focusCountriesRows)) . ")", DataType::TYPE_FORMULA)->getStyle('D' . $row_index)->getFont()->setBold(true);
            $active_sheet->setCellValueExplicit('E' . $row_index, "=SUM(" . str_replace('COLUMN_INDEX', 'E', implode(',', $focusCountriesRows)) . ")", DataType::TYPE_FORMULA)->getStyle('E' . $row_index)->getFont()->setBold(true);
            $active_sheet->setCellValueExplicit('F' . $row_index, "=SUM(" . str_replace('COLUMN_INDEX', 'F', implode(',', $focusCountriesRows)) . ")", DataType::TYPE_FORMULA)->getStyle('F' . $row_index)->getFont()->setBold(true);
            $active_sheet->setCellValueExplicit('G' . $row_index, "=SUM(" . str_replace('COLUMN_INDEX', 'G', implode(',', $focusCountriesRows)) . ")", DataType::TYPE_FORMULA, DataType::TYPE_NUMERIC)->getStyle('G' . $row_index)->getFont()->setBold(true);
        }
        $active_sheet->setCellValueExplicit('H' . $row_index, "=SUM(B{$row_index}:G{$row_index})", DataType::TYPE_FORMULA)->getStyle('H' . $row_index)->getFont()->setBold(true);
        //endregion focus countries

        $focus_countries_row_index = $row_index++;

        //region all other countries
        $active_sheet->setCellValueExplicit('A' . $row_index, 'All other countries', DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);
        foreach ($b_g_columns as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $row_index, "={$column_index}{$grand_total_index_row} - {$column_index}{$focus_countries_row_index}", DataType::TYPE_FORMULA)->getStyle($column_index . $row_index)->getFont()->setBold(true);
        }

        $active_sheet->setCellValueExplicit('H' . $row_index, "=SUM(B{$row_index}:G{$row_index})", DataType::TYPE_FORMULA)->getStyle('H' . $row_index)->getFont()->setBold(true);
        //endregion all other countries

        $all_other_countries_row_index = $row_index++;

        //region FC VS AC
        $active_sheet->setCellValueExplicit('A' . $row_index, 'Value of FC VS AC', DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);
        foreach ($b_h_columns as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $row_index, "=CONCATENATE(IF({$column_index}{$all_other_countries_row_index} > 0, ROUND({$column_index}{$focus_countries_row_index} / {$column_index}{$all_other_countries_row_index} * 100, 2), 0), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $row_index)->getFont()->setBold(true);
        }

        $active_sheet->getStyle("A{$row_index}:H{$row_index}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCD5C5C');
        //endregion FC VS AC

        $row_index++;

        //region Value of AC to grand total
        $active_sheet->setCellValueExplicit('A' . $row_index, 'Value of AC to grand total', DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);
        foreach ($b_h_columns as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $row_index, "=CONCATENATE(IF({$column_index}{$grand_total_index_row} > 0, ROUND({$column_index}{$all_other_countries_row_index} / {$column_index}{$grand_total_index_row} * 100, 2), 0), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $row_index)->getFont()->setBold(true);
        }

        $active_sheet->getStyle("A{$row_index}:H{$row_index}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF36A64B');
        //endregion Value of AC to grand total

        $row_index++;

        //region Value of FC to grand total
        $active_sheet->setCellValueExplicit('A' . $row_index, 'Value of FC to grand total', DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);
        foreach ($b_h_columns as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $row_index, "=CONCATENATE(IF({$column_index}{$grand_total_index_row} > 0, ROUND({$column_index}{$focus_countries_row_index} / {$column_index}{$grand_total_index_row} * 100, 2), 0), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $row_index)->getFont()->setBold(true);
        }
        //endregion Value of FC to grand total

        return $excel;
    }

    private function users_yoy(): Spreadsheet
    {
        // Set relationship between year and column
        $first_year = 2014;
        $first_year_column_index = $incremented_column_index = 'B';
        $last_year = (int) date('Y');

        $column_year_relation = array($first_year => $first_year_column_index);
        for ($year = ++$first_year; $year <= $last_year; $year++) {
            $column_year_relation[$year] = ++$incremented_column_index;
        }

        $last_year_column_index = $incremented_column_index;
        $grand_total_column_index = ++$incremented_column_index;

        $first_row_columns = array(
            'A' => array(
                'title' => 'User country',
                'width' => 50,
            ),
        );

        foreach ($column_year_relation as $year => $column_index) {
            $first_row_columns[$column_index] = array(
                'title' => $year == $last_year ? 'YTD ' . $year : $year,
                'width' => 15,
            );
        }

        $first_row_columns[$grand_total_column_index] = array(
            'title' => 'Grand Total',
            'width' => 25,
        );

        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $active_sheet->setTitle('Year');

        //region get countries
        /** @var Country_Model countryModel */
        $countryModel = model(Country_Model::class);

        $countries = $countryModel->get_countries();
        $country_no_name = array(
            'is_focus_country'  => 0,
            'country'           => 'No name',
            'id'                => 0,
        );

        array_unshift($countries, $country_no_name);
        //endregion get countries

        //region get users statistics
        $is_applied_filter_by_focus_country = false;

        if (!empty($_GET['focus_countries'])) {
            $is_applied_filter_by_focus_country = true;

            $users_conditions = array(
                'only_focus_country'    => true,
                'join_with_country'     => true,
                'group_by'              => 'users.country',
            );

            $applied_filters['Only focus country'] = 'YES';
        }

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $all_users = $userModel->get_report_users_yoy($users_conditions ?? array());
        $all_users_by_country = arrayByKey($all_users, 'country', TRUE);
        //endregion get users statistics

        $row_index = 1;

        if (!empty($applied_filters)) {
            $active_sheet->mergeCells("A{$row_index}:{$grand_total_column_index}{$row_index}");

            foreach ($applied_filters as $filter_name => $filter_value) {
                $applied_filters_raw[] = "[{$filter_name} => {$filter_value}]";
            }

            $applied_filters_title = 'Applied filters: ' . implode(' AND ',  $applied_filters_raw);

            $active_sheet->setCellValue('A' . $row_index, $applied_filters_title);

            $row_index++;
        }

        //region generate first row
        foreach ($first_row_columns as $column_index => $column_settings) {
            $active_sheet->getColumnDimension($column_index)->setWidth($column_settings['width']);
            $active_sheet->getStyle($column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->setCellValue($column_index . $row_index, $column_settings['title'])
                        ->getStyle($column_index . $row_index)
                            ->getFont()
                            ->setSize(12)
                            ->setBold(true);
        }
        //endregion generate first row

        $row_index++;

        // freeze first row and first column
        $active_sheet->freezePane($first_year_column_index . $row_index);
        //align all table content
        $active_sheet->getStyle('A:' . $grand_total_column_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        //region data by country
        $focus_country_rows = array();
        $first_country_row_index = $row_index;

        foreach ($countries as $country) {
            if (empty($all_users_by_country[$country['id']])) {
                continue;
            }

            $active_sheet->setCellValueExplicit('A' . $row_index, $country['country'], DataType::TYPE_STRING)->getStyle('A' . $row_index)->getFont()->setBold(true);

            foreach ($all_users_by_country[$country['id']] as $statistics) {
                $active_sheet->setCellValueExplicit($column_year_relation[$statistics['year_of_registration']] . $row_index, $statistics['count_registered_users'], DataType::TYPE_NUMERIC);
            }

            $active_sheet->setCellValueExplicit($grand_total_column_index . $row_index, "=SUM({$first_year_column_index}{$row_index}:{$last_year_column_index}{$row_index})", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $row_index)->getFont()->setBold(true);

            if ($country['is_focus_country']) {
                $focus_country_rows[] = $row_index;

                if (!$is_applied_filter_by_focus_country) {
                    $active_sheet->getStyle('A' . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF9FC5E8');
                }
            }

            $row_index++;
        }

        $last_country_row_index = $row_index - 1;
        //endregion data by country

        //region grand total row
        $grand_total_row_index = $row_index;

        $active_sheet->setCellValueExplicit('A' . $grand_total_row_index, 'Grand Total', DataType::TYPE_STRING)->getStyle('A' . $grand_total_row_index)->getFont()->setBold(true);
        foreach ($column_year_relation as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $grand_total_row_index, "=SUM({$column_index}{$first_country_row_index}:{$column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($column_index . $grand_total_row_index)->getFont()->setBold(true);
        }

        $active_sheet->setCellValueExplicit($grand_total_column_index . $grand_total_row_index, "=SUM({$grand_total_column_index}{$first_country_row_index}:{$grand_total_column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $grand_total_row_index)->getFont()->setBold(true);
        //endregion grand total row

        $row_index++;

        //region value
        $value_row_index = $row_index;

        $active_sheet->setCellValueExplicit('A' . $value_row_index, 'Value', DataType::TYPE_STRING)->getStyle('A' . $value_row_index)->getFont()->setBold(true);

        foreach ($column_year_relation as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $value_row_index, "=CONCAT(ROUND({$column_index}{$grand_total_row_index}/{$grand_total_column_index}{$grand_total_row_index} *100, 2), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $value_row_index)->getFont()->setBold(true);
        }
        //endregion value

        $row_index++;

        //region yoy
        $yoy_row_index = $row_index;

        $active_sheet->setCellValueExplicit('A' . $yoy_row_index, 'YOY', DataType::TYPE_STRING)->getStyle('A' . $yoy_row_index)->getFont()->setBold(true);

        for ($iterated_column_index = $first_year_column_index; $iterated_column_index < $last_year_column_index; $iterated_column_index++) {
            $next_column_index = chr(ord($iterated_column_index) + 1);

            $active_sheet->setCellValueExplicit($iterated_column_index . $yoy_row_index, "=CONCAT(IF({$next_column_index}{$grand_total_row_index} = 0, 100, ROUND({$iterated_column_index}{$grand_total_row_index} / {$next_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($iterated_column_index . $yoy_row_index)->getFont()->setBold(true);
        }
        //endregion yoy

        $row_index++;

        //region alternative yoy
        $alternative_yoy_row_index = $row_index;

        $active_sheet->setCellValueExplicit('A' . $alternative_yoy_row_index, 'Alternative YOY', DataType::TYPE_STRING)->getStyle('A' . $alternative_yoy_row_index)->getFont()->setBold(true);

        $previous_cell_value = 0;
        $previous_cell_column_index = '';
        foreach ($column_year_relation as $column_index) {
            if (0 == $previous_cell_value) {
                $active_sheet->setCellValueExplicit($column_index . $alternative_yoy_row_index, "=CONCAT(IF({$column_index}{$grand_total_row_index} = 0, 0, 100), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $alternative_yoy_row_index)->getFont()->setBold(true);
            } else {
                $active_sheet->setCellValueExplicit($column_index . $alternative_yoy_row_index, "=CONCAT(ROUND(({$column_index}{$grand_total_row_index} - {$previous_cell_column_index}{$grand_total_row_index}) * 100 / {$previous_cell_column_index}{$grand_total_row_index}, 2), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $alternative_yoy_row_index)->getFont()->setBold(true);
            }

            $previous_cell_value = (int) $active_sheet->getCell($column_index . $grand_total_row_index)->getCalculatedValue();
            $previous_cell_column_index = $column_index;
        }
        //endregion alternative yoy

        $row_index++;

        //region focus country
        $focus_country_row_index = $row_index;

        $active_sheet->setCellValueExplicit('A' . $focus_country_row_index, 'FC', DataType::TYPE_STRING)->getStyle('A' . $focus_country_row_index)->getFont()->setBold(true);

        foreach ($column_year_relation as $column_index) {
            $involved_cells = array();
            foreach ($focus_country_rows as $index) {
                $involved_cells[] = $column_index . $index;
            }

            $active_sheet->setCellValueExplicit($column_index . $focus_country_row_index, "=SUM(" . implode(',', $involved_cells) . ")", DataType::TYPE_FORMULA)->getStyle($column_index . $focus_country_row_index)->getFont()->setBold(true);
        }

        $involved_cells = array();
        foreach ($focus_country_rows as $index) {
            $involved_cells[] = $grand_total_column_index . $index;
        }

        $active_sheet->setCellValueExplicit($grand_total_column_index . $focus_country_row_index, "=SUM(" . implode(',', $involved_cells) . ")", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $focus_country_row_index)->getFont()->setBold(true);
        //endregion focus country

        $row_index++;

        //region focus country value
        $focus_country_value_row_index = $row_index;

        $active_sheet->setCellValueExplicit('A' . $focus_country_value_row_index, 'FC Value', DataType::TYPE_STRING)->getStyle('A' . $focus_country_value_row_index)->getFont()->setBold(true);
        foreach ($column_year_relation as $column_index) {
            $active_sheet->setCellValueExplicit($column_index . $focus_country_value_row_index, "=CONCAT(IF({$column_index}{$grand_total_row_index} = 0, 0, ROUND({$column_index}{$focus_country_row_index}/{$column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $focus_country_value_row_index)->getFont()->setBold(true);
        }

        $active_sheet->setCellValueExplicit($grand_total_column_index . $focus_country_value_row_index, "=CONCAT(IF({$grand_total_column_index}{$grand_total_row_index} = 0, 0, ROUND({$grand_total_column_index}{$focus_country_row_index}/{$grand_total_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $focus_country_value_row_index)->getFont()->setBold(true);
        //endregion focus country value

        return $excel;
    }

    private function users_qoq(): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $active_sheet->setTitle('Quarters');

        $first_year = 2014;
        $current_year = (int) date('Y');
        $current_quarter = intdiv(date('n') + 2, 3);

        $year_subcolumns = array(
            0 => 'Total',
            1 => 'Q1',
            2 => 'Q2',
            3 => 'Q3',
            4 => 'Q4',
        );

        $country_column_index = 'A';
        $grand_total_column_index = 'B';
        $columns_data = array();

        $row_index = 2;

        //region generate second row
        $active_sheet->getColumnDimension($country_column_index)->setWidth(60);
        $active_sheet->getStyle($country_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle($country_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $active_sheet->setCellValue($country_column_index . $row_index, 'Country')
                    ->getStyle($country_column_index . $row_index)
                        ->getFont()
                        ->setSize(14)
                        ->setBold(true);

        $active_sheet->getColumnDimension($grand_total_column_index)->setWidth(30);
        $active_sheet->getStyle($grand_total_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle($grand_total_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $active_sheet->setCellValue($grand_total_column_index . $row_index, 'Total')
                    ->getStyle($grand_total_column_index . $row_index)
                        ->getFont()
                        ->setSize(14)
                        ->setBold(true);

        $total_by_year_columns = $year_columns = array();
        $iterated_column_index = chr(ord($grand_total_column_index) + 1);

        for ($year = $first_year; $year <= $current_year; $year++) {
            $year_columns[$year]['first_column'] = $iterated_column_index;

            foreach ($year_subcolumns as $quarter_index => $year_subcolumn) {
                $column_title = $year_subcolumn;

                if ($year == $current_year) {
                    if ($quarter_index > $current_quarter) {
                        break;
                    }

                    $column_title = (0 == $quarter_index ? "Total YTD " . date('F j') : $year_subcolumn);
                    if ($quarter_index == $current_quarter) {
                        $column_title = 'YTD ' . date('F j') . " " . $column_title;
                    }
                }

                $active_sheet->getColumnDimension($iterated_column_index)->setWidth(20);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setWrapText(true);
                $active_sheet->setCellValue($iterated_column_index . $row_index, $column_title)
                            ->getStyle($iterated_column_index . $row_index)
                                ->getFont()
                                ->setSize(14)
                                ->setBold(true);


                $columns_data[$iterated_column_index] = array(
                    'quarter'   => $quarter_index,
                    'year'      => $year,
                );

                if (0 == $quarter_index) {
                    $total_by_year_columns[] = $iterated_column_index . "{ROW_INDEX}";
                }

                $last_column = $iterated_column_index++;
            }

            $year_columns[$year]['last_column'] = $last_column;
        }

        $active_sheet->getRowDimension('1')->setRowHeight(18);
        $active_sheet->getRowDimension('2')->setRowHeight(30);
        //endregion generate second row

        $row_index = 1;

        //region generate first row
        $active_sheet->getStyle($grand_total_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->setCellValue($grand_total_column_index . $row_index, 'Grand Total')
                    ->getStyle($grand_total_column_index . $row_index)
                        ->getFont()
                        ->setSize(14)
                        ->setBold(true);

        foreach ($year_columns as $year => $year_column) {
            $active_sheet->getStyle($year_column['first_column'] . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->mergeCells($year_column['first_column'] . $row_index . ':' . $year_column['last_column'] . $row_index);
            $active_sheet->setCellValue($year_column['first_column'] . $row_index, $year)
                        ->getStyle($year_column['first_column'] . $row_index)
                            ->getFont()
                            ->setSize(14)
                            ->setBold(true);
        }
        //endregion generate first row

        $last_column_index = $year_column['last_column'];

        //fill first row
        $active_sheet->getStyle("A1:{$last_column_index}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFBDBDBD');

        //freeze first row and first column
        $active_sheet->freezePane('B3');
        //align all table content
        $active_sheet->getStyle("A:{$last_column_index}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle("A:{$last_column_index}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        //region get countries
        /** @var Country_Model countryModel */
        $countryModel = model(Country_Model::class);

        $countries = $countryModel->get_countries();
        $country_no_name = array(
            'is_focus_country'  => 0,
            'country'           => 'No name',
            'id'                => 0,
        );

        array_unshift($countries, $country_no_name);
        //endregion get countries

        //region get users statistics
        $is_applied_filter_by_fc = false;
        if (!empty($_GET['focus_countries'])) {
            $is_applied_filter_by_fc = true;

            $users_conditions = array(
                'only_focus_country'    => true,
                'join_with_country'     => true,
            );
        }

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $all_users = $userModel->get_report_users_qoq($users_conditions ?? array());
        $all_users_by_country = arrayByKey($all_users, 'country', TRUE);
        //endregion get users statistics

        $row_index = 3;

        //region of data by country
        $focus_country_rows = array();
        $first_country_row_index = $row_index;

        foreach ($countries as $country) {
            if (empty($all_users_by_country[$country['id']])) {
                continue;
            }

            $active_sheet->setCellValueExplicit($grand_total_column_index . $row_index, "=SUM(" . str_replace("{ROW_INDEX}", $row_index, implode(',', $total_by_year_columns)) . ")", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $row_index)->getFont()->setSize(14)->setBold(true);

            if ($country['is_focus_country']) {
                $focus_country_rows[] = "{COLUMN_INDEX}" . $row_index;
            }

            $statistics_by_country = arrayByKey($all_users_by_country[$country['id']], 'registration_year', TRUE);

            foreach ($columns_data as $column_index => $column_params) {
                if (!isset($statistics_by_country[$column_params['year']])) {
                    continue;
                }

                $active_sheet->setCellValueExplicit($country_column_index . $row_index, $country['country'], DataType::TYPE_STRING)->getStyle($country_column_index . $row_index)->getFont()->setSize(14)->setBold(true);

                $statistics_by_year = array_column($statistics_by_country[$column_params['year']], null, 'quarter');

                if (0 == $column_params['quarter']) {
                    $active_sheet->setCellValueExplicit($column_index . $row_index, array_sum(array_column($statistics_by_year, 'count_registered_users')), DataType::TYPE_NUMERIC)->getStyle($column_index . $row_index)->getFont()->setSize(14);
                }

                if (!isset($statistics_by_year[$column_params['quarter']])) {
                    continue;
                }

                $active_sheet->setCellValueExplicit($column_index . $row_index, (int) $statistics_by_year[$column_params['quarter']]['count_registered_users'], DataType::TYPE_NUMERIC)->getStyle($column_index . $row_index)->getFont()->setSize(14);
            }

            $active_sheet->getRowDimension($row_index)->setRowHeight(18);

            $last_country_row_index = $row_index++;
        }
        //endregion of data by country

        $grand_total_row_index = $row_index;

        //region of grand total row
        $active_sheet->setCellValueExplicit($country_column_index . $grand_total_row_index, "Grand Total", DataType::TYPE_STRING)->getStyle($country_column_index . $grand_total_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $grand_total_row_index, "=SUM({$grand_total_column_index}{$first_country_row_index}:{$grand_total_column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $grand_total_row_index)->getFont()->setSize(14)->setBold(true);

        foreach ($columns_data as $column_index => $column_params) {
            $active_sheet->setCellValueExplicit($column_index . $grand_total_row_index, "=SUM({$column_index}{$first_country_row_index}:{$column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($column_index . $grand_total_row_index)->getFont()->setSize(14)->setBold(true);
        }
        //endregion of grand total row

        $qoq_row_index = ++$row_index;

        //region of QOQ
        $active_sheet->setCellValueExplicit($country_column_index . $qoq_row_index, "QOQ", DataType::TYPE_STRING)->getStyle($country_column_index . $qoq_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $qoq_row_index, "-", DataType::TYPE_STRING)->getStyle($grand_total_column_index . $qoq_row_index)->getFont()->setSize(14)->setBold(true);

        $previous_quarter_column_index = '';

        foreach ($columns_data as $column_index => $column_params) {
            if (0 == $column_params['quarter']) {
                continue;
            }

            if (!empty($previous_quarter_column_index)) {
                $active_sheet->setCellValueExplicit($column_index . $qoq_row_index, "=CONCAT(IF({$previous_quarter_column_index}{$grand_total_row_index} = 0, {$column_index}{$grand_total_row_index} * 100, ROUND(({$column_index}{$grand_total_row_index} - {$previous_quarter_column_index}{$grand_total_row_index}) * 100 / {$previous_quarter_column_index}{$grand_total_row_index}, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $qoq_row_index)->getFont()->setSize(14);
            }

            $previous_quarter_column_index = $column_index;
        }
        //endregion of QOQ

        $focus_country_row_index = ++$row_index;

        //region of focus countries
        $active_sheet->setCellValueExplicit($country_column_index . $focus_country_row_index, "FC", DataType::TYPE_STRING)->getStyle($country_column_index . $focus_country_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $focus_country_row_index, "=SUM(" . str_replace("{COLUMN_INDEX}", $grand_total_column_index, implode(',', $focus_country_rows)) . ")", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $focus_country_row_index)->getFont()->setSize(14)->setBold(true);

        foreach ($columns_data as $column_index => $column_params) {
            $active_sheet->setCellValueExplicit($column_index . $focus_country_row_index, "=SUM(" . str_replace("{COLUMN_INDEX}", $column_index, implode(',', $focus_country_rows)) . ")", DataType::TYPE_FORMULA)->getStyle($column_index . $focus_country_row_index)->getFont()->setSize(14)->setBold(true);
        }
        //endregion of focus countries

        $focus_country_value_row_index = ++$row_index;

        //region of focus country value
        $active_sheet->setCellValueExplicit($country_column_index . $focus_country_value_row_index, "Value of FC", DataType::TYPE_STRING)->getStyle($country_column_index . $focus_country_value_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $focus_country_value_row_index, "=CONCAT(IF({$grand_total_column_index}{$grand_total_row_index} = 0, 0.00, ROUND({$grand_total_column_index}{$focus_country_row_index} / {$grand_total_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $focus_country_value_row_index)->getFont()->setSize(14)->setBold(true);

        foreach ($columns_data as $column_index => $column_params) {
            $active_sheet->setCellValueExplicit($column_index . $focus_country_value_row_index, "=CONCAT(IF({$column_index}{$grand_total_row_index} = 0, 0, ROUND({$column_index}{$focus_country_row_index} / {$column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $focus_country_value_row_index)->getFont()->setSize(14)->setBold(true);
        }
        //endregion of focus country value

        $q_to_total_row_index = ++$row_index;

        //region of q to total
        $active_sheet->setCellValueExplicit($country_column_index . $q_to_total_row_index, "Value of Q to total", DataType::TYPE_STRING)->getStyle($country_column_index . $q_to_total_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $q_to_total_row_index, "-", DataType::TYPE_STRING)->getStyle($grand_total_column_index . $q_to_total_row_index)->getFont()->setSize(14)->setBold(true);

        $total_by_year_column_index = '';
        foreach ($columns_data as $column_index => $column_params) {
            if (0 == $column_params['quarter']) {
                $total_by_year_column_index = $column_index;
                $active_sheet->setCellValueExplicit($column_index . $q_to_total_row_index, "=CONCAT(IF({$grand_total_column_index}{$grand_total_row_index} = 0, 0, ROUND({$column_index}{$grand_total_row_index} / {$grand_total_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $q_to_total_row_index)->getFont()->setSize(14)->setBold(true);
            } else {
                $active_sheet->setCellValueExplicit($column_index . $q_to_total_row_index, "=CONCAT(IF({$total_by_year_column_index}{$grand_total_row_index} = 0, 0, ROUND({$column_index}{$grand_total_row_index} / {$total_by_year_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $q_to_total_row_index)->getFont()->setSize(14)->setBold(true);
            }
        }
        //endregion of q to total

        //region of fill not even rows
        for ($iterated_row_index = 3; $iterated_row_index <= $row_index; $iterated_row_index++) {
            if (1 == $iterated_row_index % 2) {
                $active_sheet->getStyle("A{$iterated_row_index}:{$last_column_index}{$iterated_row_index}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F3F3');
            }
        }
        //endregion of fill not even rows

        //region fill focus country cells
        if (!$is_applied_filter_by_fc) {
            foreach ($focus_country_rows as $country_cell) {
                $active_sheet->getStyle(str_replace("{COLUMN_INDEX}", $country_column_index, $country_cell))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF9FC5E8');
            }
        }
        //endregion fill focus country cells

        $active_sheet->getStyle("A1:{$last_column_index}{$row_index}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $excel;
    }

    private function users_mom(): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $active_sheet->setTitle('Months');

        $first_year = 2014;
        $current_year = (int) date('Y');
        $current_month_order_number = (int) date('n');

        $list_of_months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October',  11 => 'November', 12 => 'December');

        $country_column_index = 'A';
        $grand_total_column_index = 'B';
        $columns_data = array();

        $row_index = 2;

        //region generate second row
        $active_sheet->getColumnDimension($country_column_index)->setWidth(60);
        $active_sheet->getStyle($country_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle($country_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $active_sheet->setCellValue($country_column_index . $row_index, 'Country')
                    ->getStyle($country_column_index . $row_index)
                        ->getFont()
                        ->setSize(14)
                        ->setBold(true);

        $active_sheet->getColumnDimension($grand_total_column_index)->setWidth(15);
        $active_sheet->getStyle($grand_total_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle($grand_total_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $active_sheet->setCellValue($grand_total_column_index . $row_index, 'Total')
                    ->getStyle($grand_total_column_index . $row_index)
                        ->getFont()
                        ->setSize(14)
                        ->setBold(true);

        $total_by_year_columns = $year_columns = array();
        $iterated_column_index = chr(ord($grand_total_column_index) + 1);

        for ($year = $first_year; $year <= $current_year; $year++) {
            $year_columns[$year]['first_column'] = $iterated_column_index;

            foreach ($list_of_months as $month_order_number => $month) {
                $column_title = $month;

                if ($year == $current_year) {
                    if ($month_order_number > $current_month_order_number) {
                        break;
                    }

                    if ($month_order_number == $current_month_order_number) {
                        $column_title = 'YTD ' . date('F j');
                    }
                }

                $active_sheet->getColumnDimension($iterated_column_index)->setWidth(15);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setWrapText(true);
                $active_sheet->setCellValue($iterated_column_index . $row_index, $column_title)
                            ->getStyle($iterated_column_index . $row_index)
                                ->getFont()
                                ->setSize(14)
                                ->setBold(true);

                $columns_data[$iterated_column_index] = array(
                    'month' => $month_order_number,
                    'year'  => $year,
                );

                $iterated_column_index++;
            }

            $active_sheet->getColumnDimension($iterated_column_index)->setWidth(20);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setWrapText(true);
                $active_sheet->setCellValue($iterated_column_index . $row_index, 'Total')
                            ->getStyle($iterated_column_index . $row_index)
                                ->getFont()
                                ->setSize(14)
                                ->setBold(true);

            $total_by_year_columns[$year] = $iterated_column_index . "{ROW_INDEX}";

            $columns_data[$iterated_column_index] = array(
                'month' => 0,
                'year'  => $year,
            );

            $year_columns[$year]['last_column'] = $iterated_column_index++;
        }

        $active_sheet->getRowDimension('1')->setRowHeight(20);
        $active_sheet->getRowDimension('2')->setRowHeight(45);
        //endregion generate second row

        $row_index = 1;

        //region generate first row
        $active_sheet->getStyle($grand_total_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->setCellValue($grand_total_column_index . $row_index, 'Grand Total')
                    ->getStyle($grand_total_column_index . $row_index)
                        ->getFont()
                        ->setSize(14)
                        ->setBold(true);

        foreach ($year_columns as $year => $year_column) {
            $active_sheet->getStyle($year_column['first_column'] . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->mergeCells($year_column['first_column'] . $row_index . ':' . $year_column['last_column'] . $row_index);
            $active_sheet->setCellValue($year_column['first_column'] . $row_index, $year)
                        ->getStyle($year_column['first_column'] . $row_index)
                            ->getFont()
                            ->setSize(14)
                            ->setBold(true);
        }
        //endregion generate first row

        $last_column_index = $year_column['last_column'];

        //freeze first row and first column
        $active_sheet->freezePane('C3');
        //align all table content
        $active_sheet->getStyle("A:{$last_column_index}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle("A:{$last_column_index}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        //region get countries
        /** @var Country_Model countryModel */
        $countryModel = model(Country_Model::class);

        $countries = $countryModel->get_countries();
        $country_no_name = array(
            'is_focus_country'  => 0,
            'country'           => 'No name',
            'id'                => 0,
        );

        array_unshift($countries, $country_no_name);
        //endregion get countries

        //region get users statistics
        $is_applied_filter_by_fc = false;
        if (!empty($_GET['focus_countries'])) {
            $is_applied_filter_by_fc = true;

            $users_conditions = array(
                'only_focus_country'    => true,
                'join_with_country'     => true,
            );
        }

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $all_users = $userModel->get_report_users_mom($users_conditions ?? array());
        $all_users_by_country = arrayByKey($all_users, 'country', TRUE);
        //endregion get users statistics

        $row_index = 3;

        //region of data by country
        $focus_country_rows = array();
        $first_country_row_index = $row_index;

        foreach ($countries as $country) {
            if (empty($all_users_by_country[$country['id']])) {
                continue;
            }

            $active_sheet->setCellValueExplicit($grand_total_column_index . $row_index, "=SUM(" . str_replace("{ROW_INDEX}", $row_index, implode(',', $total_by_year_columns)) . ")", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $row_index)->getFont()->setSize(14)->setBold(true);

            if ($country['is_focus_country']) {
                $focus_country_rows[] = "{COLUMN_INDEX}" . $row_index;

                if (!$is_applied_filter_by_fc) {
                    $active_sheet->getStyle($country_column_index . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF9FC5E8');
                }
            }

            $statistics_by_country = arrayByKey($all_users_by_country[$country['id']], 'registration_year', TRUE);

            $active_sheet->setCellValueExplicit($country_column_index . $row_index, $country['country'], DataType::TYPE_STRING)->getStyle($country_column_index . $row_index)->getFont()->setSize(14)->setBold(true);

            foreach ($columns_data as $column_index => $column_params) {
                if (!isset($statistics_by_country[$column_params['year']])) {
                    continue;
                }

                $statistics_by_year = array_column($statistics_by_country[$column_params['year']], null, 'month');

                if (0 == $column_params['month']) {
                    $active_sheet->setCellValueExplicit($column_index . $row_index, array_sum(array_column($statistics_by_year, 'count_registered_users')), DataType::TYPE_NUMERIC)->getStyle($column_index . $row_index)->getFont()->setSize(14);
                }

                if (!isset($statistics_by_year[$column_params['month']])) {
                    continue;
                }

                $active_sheet->setCellValueExplicit($column_index . $row_index, (int) $statistics_by_year[$column_params['month']]['count_registered_users'], DataType::TYPE_NUMERIC)->getStyle($column_index . $row_index)->getFont()->setSize(14);
            }

            $active_sheet->getRowDimension($row_index)->setRowHeight(18);

            $last_country_row_index = $row_index++;
        }
        //endregion of data by country

        $grand_total_row_index = $row_index;

        //region of grand total row
        $active_sheet->setCellValueExplicit($country_column_index . $grand_total_row_index, "Grand Total", DataType::TYPE_STRING)->getStyle($country_column_index . $grand_total_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $grand_total_row_index, "=SUM({$grand_total_column_index}{$first_country_row_index}:{$grand_total_column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $grand_total_row_index)->getFont()->setSize(14)->setBold(true);

        foreach ($columns_data as $column_index => $column_params) {
            $active_sheet->setCellValueExplicit($column_index . $grand_total_row_index, "=SUM({$column_index}{$first_country_row_index}:{$column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($column_index . $grand_total_row_index)->getFont()->setSize(14)->setBold(true);
        }
        //endregion of grand total row

        $focus_country_row_index = ++$row_index;

        //region of focus countries
        $active_sheet->setCellValueExplicit($country_column_index . $focus_country_row_index, "FC", DataType::TYPE_STRING)->getStyle($country_column_index . $focus_country_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $focus_country_row_index, "=SUM(" . str_replace("{COLUMN_INDEX}", $grand_total_column_index, implode(',', $focus_country_rows)) . ")", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $focus_country_row_index)->getFont()->setSize(14)->setBold(true);

        foreach ($columns_data as $column_index => $column_params) {
            $active_sheet->setCellValueExplicit($column_index . $focus_country_row_index, "=SUM(" . str_replace("{COLUMN_INDEX}", $column_index, implode(',', $focus_country_rows)) . ")", DataType::TYPE_FORMULA)->getStyle($column_index . $focus_country_row_index)->getFont()->setSize(14)->setBold(true);
        }
        //endregion of focus countries

        $focus_country_value_row_index = ++$row_index;

        //region of focus country value
        $active_sheet->setCellValueExplicit($country_column_index . $focus_country_value_row_index, "Value of FC to total per month", DataType::TYPE_STRING)->getStyle($country_column_index . $focus_country_value_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $focus_country_value_row_index, "=CONCAT(IF({$grand_total_column_index}{$grand_total_row_index} = 0, 0, ROUND({$grand_total_column_index}{$focus_country_row_index} / {$grand_total_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $focus_country_value_row_index)->getFont()->setSize(14)->setBold(true);

        foreach ($columns_data as $column_index => $column_params) {
            $active_sheet->setCellValueExplicit($column_index . $focus_country_value_row_index, "=CONCAT(IF({$column_index}{$grand_total_row_index} = 0, 0, ROUND({$column_index}{$focus_country_row_index} / {$column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $focus_country_value_row_index)->getFont()->setSize(14)->setBold(true);
        }
        //endregion of focus country value

        $value_of_each_month_row_index = ++$row_index;

        //region of value of each month
        $active_sheet->setCellValueExplicit($country_column_index . $value_of_each_month_row_index, "Value of each month", DataType::TYPE_STRING)->getStyle($country_column_index . $value_of_each_month_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $value_of_each_month_row_index, "-", DataType::TYPE_STRING)->getStyle($grand_total_column_index . $value_of_each_month_row_index)->getFont()->setSize(14)->setBold(true);

        foreach ($columns_data as $column_index => $column_params) {
            if (0 == $column_params['month']) {
                continue;
            }

            $total_by_year_column_index = str_replace('{ROW_INDEX}', '', $total_by_year_columns[$column_params['year']]);
            $active_sheet->setCellValueExplicit($column_index . $value_of_each_month_row_index, "=CONCAT(IF({$total_by_year_column_index}{$grand_total_row_index} = 0, 0, ROUND({$column_index}{$grand_total_row_index} / {$total_by_year_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $value_of_each_month_row_index)->getFont()->setSize(14)->setBold(true);
        }
        //endregion of value of each month

        $mom_row_index = ++$row_index;

        //region of MOM
        $active_sheet->setCellValueExplicit($country_column_index . $mom_row_index, "MOM", DataType::TYPE_STRING)->getStyle($country_column_index . $mom_row_index)->getFont()->setSize(14)->setBold(true);
        $active_sheet->setCellValueExplicit($grand_total_column_index . $mom_row_index, "-", DataType::TYPE_STRING)->getStyle($grand_total_column_index . $mom_row_index)->getFont()->setSize(14)->setBold(true);

        $prev_column_index = $grand_total_column_index;
        foreach ($columns_data as $column_index => $column_params) {
            if (0 == $column_params['month']) {
                continue;
            }

            $next_column_index = $column_index;
            $next_column_index++;

            if (!isset($columns_data[$next_column_index])) {
                break;
            }

            if (0 == $columns_data[$next_column_index]['month']) {
                $next_column_index++;

                if (!isset($columns_data[$next_column_index])) {
                    break;
                }
            }

            if ($grand_total_column_index == $prev_column_index) {
                $active_sheet->setCellValueExplicit($column_index . $mom_row_index, "-", DataType::TYPE_STRING)->getStyle($column_index . $mom_row_index)->getFont()->setSize(14)->setBold(true);
                $prev_column_index = $column_index;

                continue;
            }

            $active_sheet->setCellValueExplicit(
                $column_index . $mom_row_index,
                "=CONCAT(IF({$prev_column_index}{$grand_total_row_index} = 0, {$column_index}{$grand_total_row_index} * 100, ROUND(({$column_index}{$grand_total_row_index} - {$prev_column_index}{$grand_total_row_index}) * 100 / {$prev_column_index}{$grand_total_row_index}, 2)), \"%\")",
                DataType::TYPE_FORMULA
            )->getStyle($column_index . $mom_row_index)->getFont()->setSize(14)->setBold(true);

            $prev_column_index = $column_index;
        }
        //endregion of MOM

        $active_sheet->getStyle("A1:{$last_column_index}{$row_index}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $excel;
    }

    private function users_mom_by_user_types(): Spreadsheet
    {
        $first_year = 2014;
        $current_year = (int) date('Y');
        $current_quarter = intdiv(date('n') + 2, 3);
        $current_month = (int) date('n');

        $list_of_quarters = array(1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4');
        $list_of_months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October',  11 => 'November', 12 => 'December');
        $list_of_user_groups = array(
            static::BUYERS_GROUP_ID => 'Buyer',
            static::CERTIFIED_MANUFACTURER_GROUP_ID => 'Certified Manufacturer',
            static::CERTIFIED_SELLER_GROUP_ID => 'Certified Seller',
            static::SHIPPERS_GROUP_ID => 'Freight Forwarder',
            static::VERIFIED_MANUFACTURER_GROUP_ID => 'Verified Manufacturer',
            static::VERIFIED_SELLER_GROUP_ID => 'Verified Seller',
        );

        $count_user_groups = count($list_of_user_groups);

        $country_column_index = 'A';

        $sheet_index = 0;
        $excel = new Spreadsheet();

        for ($year = $first_year; $year <= $current_year; $year++) {
            $excel->setActiveSheetIndex($sheet_index);
            $active_sheet = $excel->getActiveSheet();
            $active_sheet->setTitle('MOM per user types ' . $year);

            $active_sheet->getColumnDimension($country_column_index)->setWidth(50);
            $active_sheet->mergeCells("{$country_column_index}1:{$country_column_index}4");
            $active_sheet->getStyle("{$country_column_index}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->getStyle("{$country_column_index}1")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $active_sheet->setCellValue("{$country_column_index}1", 'Country')
                            ->getStyle("{$country_column_index}1")
                                ->getFont()
                                    ->setSize(12)
                                    ->setBold(true);

            $row_index = 2;

            $total_by_year_columns = $year_columns = $quarter_columns = $user_groups_column_indexes = $columns_data = array();

            //region generate second row
            $first_iterated_column_index = $iterated_column_index = chr(ord($country_column_index) + 1);

            $year_columns[$year]['first_column_index'] = $iterated_column_index;

            foreach ($list_of_months as $month_order => $month) {
                if ($year == $current_year) {
                    if ($month_order > $current_month) {
                        break;
                    }

                    if ($month_order == $current_month) {
                        $month = 'YTD ' . date('F j');
                    }
                }

                $month_first_column_index = $iterated_column_index;

                for ($i = 1; $i < $count_user_groups; $i++) {
                    $iterated_column_index++;
                }

                $active_sheet->mergeCells("{$month_first_column_index}{$row_index}:{$iterated_column_index}{$row_index}");
                $active_sheet->getStyle($month_first_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $active_sheet->getStyle($month_first_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $active_sheet->setCellValue($month_first_column_index . $row_index, $month)
                                ->getStyle($month_first_column_index . $row_index)
                                    ->getFont()
                                        ->setSize(12)
                                        ->setBold(true);

                $month_quarter = intdiv($month_order + 2, 3);
                if (!isset($quarter_columns[$year][$month_quarter]['first_column_index'])) {
                    $quarter_columns[$year][$month_quarter]['first_column_index'] = $month_first_column_index;
                }

                $quarter_columns[$year][$month_quarter]['last_column_index'] = $iterated_column_index;

                $iterated_column_index++;
            }

            $active_sheet->mergeCells("{$iterated_column_index}2:{$iterated_column_index}4");
            $active_sheet->getStyle($iterated_column_index . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->getStyle($iterated_column_index . '2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $active_sheet->setCellValue($iterated_column_index . '2', 'Total')
                            ->getStyle($iterated_column_index . '2')
                                ->getFont()
                                    ->setSize(12)
                                    ->setBold(true);

            $year_columns[$year]['last_column_index'] = $iterated_column_index;

            $total_by_year_columns[$year] = $iterated_column_index . "{ROW_INDEX}";

            $last_column_index = $iterated_column_index++;
            //endregion generate second row

            $row_index = 1;

            //region generate first row
            foreach ($list_of_quarters as $quarter_index => $quarter) {
                if (!isset($quarter_columns[$year][$quarter_index])) {
                    break;
                }

                if ($year == $current_year && $quarter_index == $current_quarter) {
                    $quarter = 'YTD ' . $quarter;
                }

                $quarter_first_column_index = $quarter_columns[$year][$quarter_index]['first_column_index'];
                $quarter_last_column_index = $quarter_columns[$year][$quarter_index]['last_column_index'];

                $active_sheet->mergeCells("{$quarter_first_column_index}{$row_index}:{$quarter_last_column_index}{$row_index}");
                $active_sheet->getStyle($quarter_first_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $active_sheet->getStyle($quarter_first_column_index . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $active_sheet->setCellValue($quarter_first_column_index . $row_index, $quarter)
                                ->getStyle($quarter_first_column_index . $row_index)
                                    ->getFont()
                                        ->setSize(12)
                                        ->setBold(true);
            }
            //endregion generate first row

            $row_index = 3;

            //region generate third row
            $iterated_column_index = $first_iterated_column_index;
            $previous_groups_column_indexes = array();

            foreach ($list_of_months as $month_order => $month) {
                if ($year == $current_year) {
                    if ($month_order > $current_month) {
                        break;
                    }
                }

                foreach ($list_of_user_groups as $user_group_id => $user_group) {
                    $active_sheet->getColumnDimension($iterated_column_index)->setWidth(25);
                    $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $active_sheet->setCellValue($iterated_column_index . $row_index, $user_group)
                                    ->getStyle($iterated_column_index . $row_index)
                                        ->getFont()
                                            ->setSize(12)
                                            ->setBold(true);

                    $columns_data[$iterated_column_index] = array(
                        'user_group'    => $user_group_id,
                        'month'         => $month_order,
                        'year'          => $year,
                    );

                    $user_groups_column_indexes[$user_group_id][$iterated_column_index] = '';

                    if (isset($previous_groups_column_indexes[$user_group_id])) {
                        $prev_column_index = $previous_groups_column_indexes[$user_group_id];
                        $user_groups_column_indexes[$user_group_id][$prev_column_index] = $iterated_column_index;
                    }

                    $previous_groups_column_indexes[$user_group_id] = $iterated_column_index;

                    $iterated_column_index++;
                }
            }

            $columns_data[$iterated_column_index] = array(
                'is_total'  => true,
                'year'      => $year,
            );

            //skip column with Total
            $iterated_column_index++;

            //endregion generate third row

            //freeze first row and first column
            $active_sheet->freezePane('B5');
            //align all table content
            $active_sheet->getStyle("{$country_column_index}:{$last_column_index}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->getStyle("{$country_column_index}:{$last_column_index}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            //region get countries
            /** @var Country_Model countryModel */
            $countryModel = model(Country_Model::class);

            $countries = $countryModel->get_countries();
            $country_no_name = array(
                'is_focus_country'  => 0,
                'country'           => 'No name',
                'id'                => 0,
            );

            array_unshift($countries, $country_no_name);
            //endregion get countries

            //region get users statistics
            $is_applied_filter_by_fc = false;
            if (!empty($_GET['focus_countries'])) {
                $is_applied_filter_by_fc = true;

                $users_conditions = array(
                    'only_focus_country'    => true,
                    'join_with_country'     => true,
                );
            }

            /** @var User_Model $userModel */
            $userModel = model(User_Model::class);

            $all_users = $userModel->get_report_users_mom_by_user_types($users_conditions ?? array());
            $all_users_by_country = arrayByKey($all_users, 'country', TRUE);
            //endregion get users statistics

            $row_index = 5;

            //region of data by country
            $focus_country_rows = array();
            $first_country_row_index = $last_country_row_index = $row_index;

            foreach ($countries as $country) {
                if (empty($all_users_by_country[$country['id']])) {
                    continue;
                }

                $statistics_by_country = arrayByKey($all_users_by_country[$country['id']], 'registration_year', TRUE);

                if (!isset($statistics_by_country[$year])) {
                    continue;
                }

                if ($country['is_focus_country']) {
                    $focus_country_rows[] = "{COLUMN_INDEX}" . $row_index;

                    if (!$is_applied_filter_by_fc) {
                        $active_sheet->getStyle($country_column_index . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF9FC5E8');
                    }
                }

                $active_sheet->setCellValueExplicit($country_column_index . $row_index, $country['country'], DataType::TYPE_STRING)->getStyle($country_column_index . $row_index)->getFont()->setSize(12)->setBold(true);

                $first_column_index_of_year = $last_column_index_of_year = null;
                foreach ($columns_data as $column_index => $column_params) {
                    if (!isset($statistics_by_country[$column_params['year']])) {
                        continue;
                    }

                    if (null == $first_column_index_of_year) {
                        $first_column_index_of_year = $column_index;
                    }

                    if (isset($column_params['is_total'])) {
                        $active_sheet->setCellValueExplicit($column_index . $row_index, "=SUM({$first_column_index_of_year}{$row_index}:{$last_column_index_of_year}{$row_index})", DataType::TYPE_FORMULA)->getStyle($column_index . $row_index)->getFont()->setSize(12)->setBold(true);
                        $first_column_index_of_year = null;

                        continue;
                    } else {
                        $last_column_index_of_year = $column_index;
                    }

                    $statistics_by_month = arrayByKey($statistics_by_country[$column_params['year']], 'month', true);

                    if (!isset($statistics_by_month[$column_params['month']])) {
                        continue;
                    }

                    $statistics_by_user_groups = array_column($statistics_by_month[$column_params['month']], null, 'user_group');

                    if (!isset($statistics_by_user_groups[$column_params['user_group']])) {
                        continue;
                    }

                    $active_sheet->setCellValueExplicit($column_index . $row_index, (int) $statistics_by_user_groups[$column_params['user_group']], DataType::TYPE_NUMERIC)->getStyle($column_index . $row_index)->getFont()->setSize(12);
                }

                $last_country_row_index = $row_index++;
            }
            //endregion of data by country

            $grand_total_row_index = $row_index;

            //region of grand total row
            $active_sheet->setCellValueExplicit($country_column_index . $grand_total_row_index, "Grand Total", DataType::TYPE_STRING)->getStyle($country_column_index . $grand_total_row_index)->getFont()->setSize(12)->setBold(true);

            if ($first_country_row_index != $last_country_row_index) {
                foreach ($columns_data as $column_index => $column_params) {
                    $active_sheet->setCellValueExplicit($column_index . $grand_total_row_index, "=SUM({$column_index}{$first_country_row_index}:{$column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($column_index . $grand_total_row_index)->getFont()->setSize(12)->setBold(true);
                }
            }
            //endregion of grand total row

            $focus_country_row_index = ++$row_index;

            //region of focus country
            $active_sheet->setCellValueExplicit($country_column_index . $focus_country_row_index, "FC", DataType::TYPE_STRING)->getStyle($country_column_index . $focus_country_row_index)->getFont()->setSize(12)->setBold(true);

            if ($first_country_row_index != $last_country_row_index) {
                foreach ($columns_data as $column_index => $column_params) {
                    $active_sheet->setCellValueExplicit($column_index . $focus_country_row_index, "=SUM(" . str_replace("{COLUMN_INDEX}", $column_index, implode(',', $focus_country_rows)) . ")", DataType::TYPE_FORMULA)->getStyle($column_index . $focus_country_row_index)->getFont()->setSize(12)->setBold(true);
                }
            }
            //endregion of focus country

            $focus_country_value_row_index = ++$row_index;

            //region of focus country value
            $active_sheet->setCellValueExplicit($country_column_index . $focus_country_value_row_index, "Value FC to all per month", DataType::TYPE_STRING)->getStyle($country_column_index . $focus_country_value_row_index)->getFont()->setSize(12)->setBold(true);

            if ($first_country_row_index != $last_country_row_index) {
                foreach ($columns_data as $column_index => $column_params) {
                    $active_sheet->setCellValueExplicit($column_index . $focus_country_value_row_index, "=CONCAT(IF({$column_index}{$grand_total_row_index} = 0, 0, ROUND({$column_index}{$focus_country_row_index} / {$column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($column_index . $focus_country_value_row_index)->getFont()->setSize(12)->setBold(true);
                }
            }
            //endregion of focus country value

            $active_sheet->getStyle("A1:{$last_column_index}{$row_index}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $sheet_index++;

            if ($year != $current_year) {
                $newSheet = new Worksheet($excel);
                $excel->addSheet($newSheet, $sheet_index);
            }
        }

        return $excel;
    }

    private function users_yoy_by_user_types(): Spreadsheet
    {
        $first_year = 2014;
        $current_year = (int) date('Y');
        $current_month = (int) date('n');

        $list_of_user_groups = array(
            static::BUYERS_GROUP_ID => 'Buyer',
            static::CERTIFIED_MANUFACTURER_GROUP_ID => 'Certified Manufacturer',
            static::CERTIFIED_SELLER_GROUP_ID => 'Certified Seller',
            static::SHIPPERS_GROUP_ID => 'Freight Forwarder',
            static::VERIFIED_MANUFACTURER_GROUP_ID => 'Verified Manufacturer',
            static::VERIFIED_SELLER_GROUP_ID => 'Verified Seller',
        );

        $country_column_index = 'A';
        $first_group_column_index = 'B';
        $last_group_column_index = 'G';
        $grand_total_column_index = 'H';

        $sheet_index = 0;
        $excel = new Spreadsheet();

        for ($year = $first_year; $year <= $current_year; $year++) {
            $iterated_column_index = $first_group_column_index;

            $excel->setActiveSheetIndex($sheet_index);
            $active_sheet = $excel->getActiveSheet();
            $active_sheet->setTitle('YOY per user types ' . $year);

            $row_index = 1;

            //region generate first row
            $active_sheet->getColumnDimension($country_column_index)->setWidth(50);
            $active_sheet->mergeCells("{$country_column_index}1:{$country_column_index}2");
            $active_sheet->getStyle("{$country_column_index}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->getStyle("{$country_column_index}1")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $active_sheet->setCellValue("{$country_column_index}1", 'Country')->getStyle("{$country_column_index}1")->getFont()->setSize(12)->setBold(true);

            $active_sheet->mergeCells("{$first_group_column_index}1:{$grand_total_column_index}1");
            $active_sheet->getStyle("{$first_group_column_index}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->getStyle("{$first_group_column_index}1")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $active_sheet->setCellValue("{$first_group_column_index}1", $year == $current_year ? 'YTD ' . date(' Y F j') : $year)->getStyle("{$first_group_column_index}1")->getFont()->setSize(12)->setBold(true);
            //region generate first row

            $row_index++;

            //region generate second row
            foreach ($list_of_user_groups as $user_group) {
                $active_sheet->getColumnDimension($iterated_column_index)->setWidth(25);
                $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $active_sheet->setCellValue($iterated_column_index . $row_index, $user_group)->getStyle($iterated_column_index . $row_index)->getFont()->setSize(12)->setBold(true);

                $iterated_column_index++;
            }

            $active_sheet->getColumnDimension($iterated_column_index)->setWidth(15);
            $active_sheet->getStyle($iterated_column_index . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $active_sheet->setCellValue($iterated_column_index . $row_index, 'Grand Total')->getStyle($iterated_column_index . $row_index)->getFont()->setSize(12)->setBold(true);
            //endregion generate second row

            //freeze first row and first column
            $active_sheet->freezePane('B3');
            //align all table content
            $active_sheet->getStyle("{$country_column_index}:{$grand_total_column_index}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            //region get countries
            /** @var Country_Model countryModel */
            $countryModel = model(Country_Model::class);

            $countries = $countryModel->get_countries();
            $country_no_name = array(
                'is_focus_country'  => 0,
                'country'           => 'No name',
                'id'                => 0,
            );

            array_unshift($countries, $country_no_name);
            //endregion get countries

            //region get users statistics
            $is_applied_filter_by_fc = false;
            if (!empty($_GET['focus_countries'])) {
                $is_applied_filter_by_fc = true;

                $users_conditions = array(
                    'only_focus_country'    => true,
                    'join_with_country'     => true,
                );
            }

            /** @var User_Model $userModel */
            $userModel = model(User_Model::class);

            $all_users = $userModel->get_report_users_yoy_by_user_types($users_conditions ?? array());
            $all_users_by_country = arrayByKey($all_users, 'country', TRUE);
            //endregion get users statistics

            $first_country_row_index = $last_country_row_index = ++$row_index;

            //region of data by country
            $focus_country_rows = array();

            foreach ($countries as $country) {
                if (empty($all_users_by_country[$country['id']])) {
                    continue;
                }

                $statistics_by_country = arrayByKey($all_users_by_country[$country['id']], 'registration_year', TRUE);

                if (!isset($statistics_by_country[$year])) {
                    continue;
                }

                if ($country['is_focus_country']) {
                    $focus_country_rows[] = "{COLUMN_INDEX}" . $row_index;

                    if (!$is_applied_filter_by_fc) {
                        $active_sheet->getStyle($country_column_index . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF9FC5E8');
                    }
                }

                $active_sheet->setCellValueExplicit($country_column_index . $row_index, $country['country'], DataType::TYPE_STRING)->getStyle($country_column_index . $row_index)->getFont()->setSize(12)->setBold(true);

                $statistics_by_groups = array_column($statistics_by_country[$year], null, 'user_group');

                $iterated_column_index = $first_group_column_index;
                foreach ($list_of_user_groups as $group_id => $user_group) {
                    if (!isset($statistics_by_groups[$group_id])) {
                        continue;
                    }

                    $active_sheet->setCellValue($iterated_column_index . $row_index, (int) $statistics_by_groups[$group_id], DataType::TYPE_NUMERIC)->getStyle($iterated_column_index . $row_index)->getFont()->setSize(12);

                    $iterated_column_index++;
                }

                $active_sheet->setCellValue($grand_total_column_index . $row_index, "=SUM({$first_group_column_index}{$row_index}:{$last_group_column_index}{$row_index})", DataType::TYPE_FORMULA)->getStyle($grand_total_column_index . $row_index)->getFont()->setSize(12)->setBold(true);

                $last_country_row_index = $row_index++;
            }
            //endregion of data by country

            $grand_total_row_index = $row_index;

            //region of grand total row
            $active_sheet->setCellValueExplicit($country_column_index . $grand_total_row_index, "Grand Total", DataType::TYPE_STRING)->getStyle($country_column_index . $grand_total_row_index)->getFont()->setSize(12)->setBold(true);

            if ($first_country_row_index != $last_country_row_index) {
                $iterated_column_index = $first_group_column_index;

                foreach ($list_of_user_groups as $group_id => $user_group) {
                    $active_sheet->setCellValue($iterated_column_index . $row_index, "=SUM({$iterated_column_index}{$first_country_row_index}:{$iterated_column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($iterated_column_index . $row_index)->getFont()->setSize(12)->setBold(true);

                    $iterated_column_index++;
                }

                $active_sheet->setCellValue($iterated_column_index . $row_index, "=SUM({$iterated_column_index}{$first_country_row_index}:{$iterated_column_index}{$last_country_row_index})", DataType::TYPE_FORMULA)->getStyle($iterated_column_index . $row_index)->getFont()->setSize(12)->setBold(true);
            }
            //endregion of grand total row

            $focus_country_row_index = ++$row_index;

            //region of focus country
            $active_sheet->setCellValueExplicit($country_column_index . $focus_country_row_index, "FC", DataType::TYPE_STRING)->getStyle($country_column_index . $focus_country_row_index)->getFont()->setSize(12)->setBold(true);

            if ($first_country_row_index != $last_country_row_index) {
                $iterated_column_index = $first_group_column_index;

                foreach ($list_of_user_groups as $group_id => $user_group) {
                    $active_sheet->setCellValueExplicit($iterated_column_index . $focus_country_row_index, "=SUM(" . str_replace("{COLUMN_INDEX}", $iterated_column_index, implode(',', $focus_country_rows)) . ")", DataType::TYPE_FORMULA)->getStyle($iterated_column_index . $focus_country_row_index)->getFont()->setSize(12)->setBold(true);

                    $iterated_column_index++;
                }

                $active_sheet->setCellValueExplicit($iterated_column_index . $focus_country_row_index, "=SUM(" . str_replace("{COLUMN_INDEX}", $iterated_column_index, implode(',', $focus_country_rows)) . ")", DataType::TYPE_FORMULA)->getStyle($iterated_column_index . $focus_country_row_index)->getFont()->setSize(12)->setBold(true);
            }
            //endregion of focus country

            $focus_country_value_row_index = ++$row_index;

            //region of focus country value
            $active_sheet->setCellValueExplicit($country_column_index . $focus_country_value_row_index, "Value FC to all per month", DataType::TYPE_STRING)->getStyle($country_column_index . $focus_country_value_row_index)->getFont()->setSize(12)->setBold(true);

            if ($first_country_row_index != $last_country_row_index) {
                $iterated_column_index = $first_group_column_index;

                foreach ($list_of_user_groups as $group_id => $user_group) {
                    $active_sheet->setCellValueExplicit($iterated_column_index . $focus_country_value_row_index, "=CONCAT(IF({$iterated_column_index}{$grand_total_row_index} = 0, 0, ROUND({$iterated_column_index}{$focus_country_row_index} / {$iterated_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($iterated_column_index . $focus_country_value_row_index)->getFont()->setSize(12)->setBold(true);

                    $iterated_column_index++;
                }

                $active_sheet->setCellValueExplicit($iterated_column_index . $focus_country_value_row_index, "=CONCAT(IF({$iterated_column_index}{$grand_total_row_index} = 0, 0, ROUND({$iterated_column_index}{$focus_country_row_index} / {$iterated_column_index}{$grand_total_row_index} * 100, 2)), \"%\")", DataType::TYPE_FORMULA)->getStyle($iterated_column_index . $focus_country_value_row_index)->getFont()->setSize(12)->setBold(true);
            }
            //endregion of focus country value

            $active_sheet->getStyle("{$country_column_index}1:{$grand_total_column_index}{$row_index}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $sheet_index++;

            if ($year != $current_year) {
                $newSheet = new Worksheet($excel);
                $excel->addSheet($newSheet, $sheet_index);
            }
        }

        return $excel;
    }

    private function sellers_and_products(): Spreadsheet
    {
        /**
         * @var Company_Model $companyModel
         */
        $companyModel = model(Company_Model::class);

        /**
         * @var Items_Model $itemsModel
         */
        $itemsModel = model(Items_Model::class);

        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $active_sheet = $excel->getActiveSheet();
        $active_sheet->setTitle('Sellers & Products');

        $row_index = 1;

        $active_sheet->getColumnDimension('A')->setWidth(50);
        $active_sheet->getStyle('A' . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle('A' . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle('A' . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF23A9F2');
        $active_sheet->setCellValue('A' . $row_index, 'INDUSTRY')
                    ->getStyle('A' . $row_index)
                        ->getFont()
                        ->setSize(12)
                        ->setBold(true);

        $active_sheet->getColumnDimension('B')->setWidth(25);
        $active_sheet->getStyle('B' . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle('B' . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle('B' . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF23A9F2');
        $active_sheet->setCellValue('B' . $row_index, 'NUMBER OF SELLERS')
                    ->getStyle('B' . $row_index)
                        ->getFont()
                        ->setSize(12)
                        ->setBold(true);

        $active_sheet->getColumnDimension('C')->setWidth(20);
        $active_sheet->getStyle('C' . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle('C' . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle('C' . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF23A9F2');
        $active_sheet->setCellValue('C' . $row_index, 'NUMBER OF ITEMS')
                    ->getStyle('C' . $row_index)
                        ->getFont()
                        ->setSize(12)
                        ->setBold(true);

        $active_sheet->getColumnDimension('D')->setWidth(50);
        $active_sheet->getStyle('D' . $row_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle('D' . $row_index)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle('D' . $row_index)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF23A9F2');
        $active_sheet->setCellValue('D' . $row_index, 'COUNTRIES')
                    ->getStyle('D' . $row_index)
                        ->getFont()
                        ->setSize(12)
                        ->setBold(true);

        //freeze first row and first column
        $active_sheet->freezePane('A2');
        //align all table content
        $active_sheet->getStyle('A:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        //set font size
        $active_sheet->getStyle('A:D')->getFont()->setSize(12);
        //set height for first row
        $active_sheet->getRowDimension('1')->setRowHeight(20);

        $industriesWithSellers = $companyModel->get_count_sellers_by_industries();
        $itemsByIndustriesRaw = $itemsModel->getGroupedItemsByIndustries();
        $itemsByIndustries = array_column($itemsByIndustriesRaw, null, 'category_root');

        foreach ($industriesWithSellers as $industry) {
            $row_index++;

            $active_sheet->setCellValueExplicit('A' . $row_index, $industry['name'], DataType::TYPE_STRING);
            $active_sheet->setCellValueExplicit('B' . $row_index, (int) $industry['count_sellers'], DataType::TYPE_NUMERIC);
            $active_sheet->setCellValueExplicit('C' . $row_index, (int) ($itemsByIndustries[$industry['category_id']]['count_items'] ?? 0), DataType::TYPE_NUMERIC);
            $active_sheet->setCellValueExplicit('D' . $row_index, empty($itemsByIndustries[$industry['category_id']]['countries']) ? '' : implode("\n", explode(',', $itemsByIndustries[$industry['category_id']]['countries'])), DataType::TYPE_STRING);

        }

        //add border
        $active_sheet->getStyle("A1:D{$row_index}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $active_sheet->getStyle("A1:D{$row_index}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //set cell autoheight (work only in Microsoft Office)
        $active_sheet->getStyle("A1:D{$row_index}")->getAlignment()->setWrapText(true);

        return $excel;
    }

    private function sellersAndManufacturers(): Spreadsheet
    {
        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('Sellers & Manufacturers');

        //region set columns dimensions
        $activeSheet->getColumnDimension('A')->setWidth(25);
        $activeSheet->getColumnDimension('B')->setWidth(20);
        $activeSheet->getColumnDimension('C')->setWidth(20);
        $activeSheet->getColumnDimension('D')->setWidth(15);
        $activeSheet->getColumnDimension('E')->setWidth(15);
        $activeSheet->getColumnDimension('F')->setWidth(15);
        $activeSheet->getColumnDimension('G')->setWidth(15);

        $activeSheet->getDefaultRowDimension()->setRowHeight(20);
        //endregion set columns dimensions

        //region first row
        $activeSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $activeSheet->setCellValue('A1', 'YTD ' . date('M j'))
                    ->getStyle('A1')
                        ->getFont()
                        ->setSize(12)
                        ->setBold(true);
        //endregion first row

        //region second row
        $activeSheet->mergeCells('A2:A3');
        $activeSheet->setCellValue('A2', 'UT')->getStyle('A2')->getFont()->setSize(12)->setBold(true);

        $activeSheet->mergeCells('B2:B3');
        $activeSheet->setCellValue('B2', 'Registered')->getStyle('B2')->getFont()->setSize(12)->setBold(true);

        $activeSheet->mergeCells('C2:C3');
        $activeSheet->setCellValue('C2', 'Active')->getStyle('C2')->getFont()->setSize(12)->setBold(true);

        $activeSheet->mergeCells('D2:G2');
        $activeSheet->setCellValue('D2', 'Inactive')->getStyle('D2')->getFont()->setSize(12)->setBold(true);

        $activeSheet->setCellValue('D3', 'New')->getStyle('D3')->getFont()->setSize(12)->setBold(true);
        $activeSheet->setCellValue('E3', 'Pending')->getStyle('E3')->getFont()->setSize(12)->setBold(true);
        $activeSheet->setCellValue('F3', 'Blocked')->getStyle('F3')->getFont()->setSize(12)->setBold(true);
        $activeSheet->setCellValue('G3', 'Deleted')->getStyle('G3')->getFont()->setSize(12)->setBold(true);
        //endregion second row

        //region first column
        $activeSheet->setCellValue('A4', 'Verified Sellers');
        $activeSheet->setCellValue('A5', 'Certified Sellers');
        $activeSheet->setCellValue('A6', 'Verified Manufacturers');
        $activeSheet->setCellValue('A7', 'Certified Manufacturers');
        $activeSheet->setCellValue('A8', 'Grand Total');
        //endregion first column

        //region get statistics from DB
        $countRegisteredUsersByUserGroup = $userModel->countUsersGroupedByUserGroup([
            'userGroups'    => [2, 3, 5, 6],
            'modelUser'     => 0,
            'fakeUser'      => 0,
        ]);

        $countUsersByUserGroupAndStatus = $userModel->countUsersGroupedByUserGroupAndStatus([
            'userGroups'    => [2, 3, 5, 6],
            'userStatuses'  => ['active', 'new', 'pending', 'blocked', 'deleted'],
            'modelUser'     => 0,
            'fakeUser'      => 0,
        ]);
        //endregion get statistics from DB

        //region verified sellers row
        $verifiedSellersCounters = $countUsersByUserGroupAndStatus[static::VERIFIED_SELLER_GROUP_ID] ?? [];
        $verifiedSellersCounters = array_column($verifiedSellersCounters, null, 'status');

        $activeSheet->setCellValueExplicit('B4', (int) ($countRegisteredUsersByUserGroup[static::VERIFIED_SELLER_GROUP_ID]['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('C4', (int) ($verifiedSellersCounters['active']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('D4', (int) ($verifiedSellersCounters['new']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('E4', (int) ($verifiedSellersCounters['pending']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('F4', (int) ($verifiedSellersCounters['blocked']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('G4', (int) ($verifiedSellersCounters['deleted']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        //endregion verified sellers row

        //region certified sellers row
        $certifiedSellersCounters = $countUsersByUserGroupAndStatus[static::CERTIFIED_SELLER_GROUP_ID] ?? [];
        $certifiedSellersCounters = array_column($certifiedSellersCounters, null, 'status');

        $activeSheet->setCellValueExplicit('B5', (int) ($countRegisteredUsersByUserGroup[static::CERTIFIED_SELLER_GROUP_ID]['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('C5', (int) ($certifiedSellersCounters['active']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('D5', (int) ($certifiedSellersCounters['new']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('E5', (int) ($certifiedSellersCounters['pending']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('F5', (int) ($certifiedSellersCounters['blocked']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('G5', (int) ($certifiedSellersCounters['deleted']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        //endregion certified sellers row

        //region verified manufacturers row
        $verifiedManufacturersCounters = $countUsersByUserGroupAndStatus[static::VERIFIED_MANUFACTURER_GROUP_ID] ?? [];
        $verifiedManufacturersCounters = array_column($verifiedManufacturersCounters, null, 'status');

        $activeSheet->setCellValueExplicit('B6', (int) ($countRegisteredUsersByUserGroup[static::VERIFIED_MANUFACTURER_GROUP_ID]['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('C6', (int) ($verifiedManufacturersCounters['active']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('D6', (int) ($verifiedManufacturersCounters['new']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('E6', (int) ($verifiedManufacturersCounters['pending']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('F6', (int) ($verifiedManufacturersCounters['blocked']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('G6', (int) ($verifiedManufacturersCounters['deleted']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        //endregion verified manufacturers row

        //region certified manufacturers row
        $certifiedManufacturersCounters = $countUsersByUserGroupAndStatus[static::CERTIFIED_MANUFACTURER_GROUP_ID] ?? [];
        $certifiedManufacturersCounters = array_column($certifiedManufacturersCounters, null, 'status');

        $activeSheet->setCellValueExplicit('B7', (int) ($countRegisteredUsersByUserGroup[static::CERTIFIED_MANUFACTURER_GROUP_ID]['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('C7', (int) ($certifiedManufacturersCounters['active']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('D7', (int) ($certifiedManufacturersCounters['new']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('E7', (int) ($certifiedManufacturersCounters['pending']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('F7', (int) ($certifiedManufacturersCounters['blocked']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        $activeSheet->setCellValueExplicit('G7', (int) ($certifiedManufacturersCounters['deleted']['countUsers'] ?? 0), DataType::TYPE_NUMERIC);
        //endregion certified manufacturers row

        //region grand total row
        for ($columnIndex = 'B'; $columnIndex <= 'G'; $columnIndex++) {
            $activeSheet->setCellValueExplicit($columnIndex . '8', "=SUM({$columnIndex}4:{$columnIndex}7)", DataType::TYPE_FORMULA);
        }
        //endregion grand total row

        //add border
        $activeSheet->getStyle('A2:G8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $activeSheet->getStyle('A2:G8')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //align all table content
        $activeSheet->getStyle('A:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        //set font size
        $activeSheet->getStyle('A:G')->getFont()->setSize(12);
        //set cell autoheight (work only in Microsoft Office)
        $activeSheet->getStyle('A2:G8')->getAlignment()->setWrapText(true);

        return $excel;
    }

    private function productsActive(): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('Products Active');

        //region set columns dimensions
        $activeSheet->getColumnDimension('A')->setWidth(60); //Item title
        $activeSheet->getColumnDimension('B')->setWidth(80); //Item URL
        $activeSheet->getColumnDimension('C')->setWidth(20); //Item status
        $activeSheet->getColumnDimension('D')->setWidth(40); //Item industry
        $activeSheet->getColumnDimension('E')->setWidth(40); //Item category
        $activeSheet->getColumnDimension('F')->setWidth(70); //Item category [alternative]
        $activeSheet->getColumnDimension('G')->setWidth(35); //Item country
        $activeSheet->getColumnDimension('H')->setWidth(20); //Item created on

        $activeSheet->getDefaultRowDimension()->setRowHeight(20);
        //endregion set columns dimensions

        //region first row
        $activeSheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7E6E6');

        $activeSheet->setCellValue('A1', 'Item');
        $activeSheet->setCellValue('B1', 'Link');
        $activeSheet->setCellValue('C1', 'Status');
        $activeSheet->setCellValue('D1', 'Industry');
        $activeSheet->setCellValue('E1', 'Category');
        $activeSheet->setCellValue('F1', 'Category [suggested version]');
        $activeSheet->setCellValue('G1', 'Item Country');
        $activeSheet->setCellValue('H1', 'Created on');

        $activeSheet->getStyle('A1:H1')->getFont()->setSize(12)->setBold(true);
        //endregion first row

        //region get statistics from DB
        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        $countriesById = $countryModel->getAllCountries();

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $items = $itemsModel->getItemsAvailableOnPublicPage([
            'select' => [
                'i.`id`',
                'i.`title`',
                'i.`id_cat`',
                'i.`p_country`',
                'i.`create_date`',
                'cat.`name`',
                'cat.`breadcrumbs`',
            ],
            'modelUser' => 0,
        ]);
        //endregion get statistics from DB

        //region second row
        $activeSheet->getStyle('A2:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF2CC');

        $activeSheet->setCellValue('C2', 'TOTAL ACTIVE: ' . count($items));
        $activeSheet->setCellValue('D2', 'REPORT DATE: ' . date('F d, Y'));
        $activeSheet->getStyle('A2:H2')->getFont()->setSize(10)->setBold(true);
        //endregion second row

        $rowIndex = 2;
        $categoriesCache = [];

        foreach ($items as $item) {
            $rowIndex++;

            $activeSheet->setCellValue('A' . $rowIndex, $item['title']);
            $activeSheet->setCellValue('B' . $rowIndex, makeItemUrl($item['id'], $item['title']));
            $activeSheet->setCellValue('C' . $rowIndex, 'ACTIVE');

            if (!isset($categoriesCache[$item['id_cat']])) {
                $categoryCrumbs = json_decode('[' . $item['breadcrumbs'] . ']', true);
                $industry = array_shift(array_shift($categoryCrumbs));
                $categoryTree = [];

                foreach ($categoryCrumbs as $category) {
                    $categoryTree[] = array_shift($category);
                }

                $categoriesCache[$item['id_cat']] = [
                    'industry'  => $industry,
                    'tree'      => implode(' => ', $categoryTree),
                ];
            }

            $activeSheet->setCellValue('D' . $rowIndex, $categoriesCache[$item['id_cat']]['industry']);
            $activeSheet->setCellValue('E' . $rowIndex, $item['name']);
            $activeSheet->setCellValue('F' . $rowIndex, $categoriesCache[$item['id_cat']]['tree']);
            $activeSheet->setCellValue('G' . $rowIndex, $countriesById[$item['p_country']]['country_name'] ?? '!----------!');
            $activeSheet->setCellValue('H' . $rowIndex, $item['create_date']);
        }

        //freeze first row
        $activeSheet->freezePane('A3');
        //add border
        $activeSheet->getStyle('A1:H' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $activeSheet->getStyle('A1:H' . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //align all table content
        $activeSheet->getStyle('A:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        //set font size
        $activeSheet->getStyle('A3:H' . $rowIndex)->getFont()->setSize(10);
        //set cell autoheight (work only in Microsoft Office)
        $activeSheet->getStyle('A1:H' . $rowIndex)->getAlignment()->setWrapText(true);

        return $excel;
    }

    private function productsDraft(): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('Products Draft');

        //region set columns dimensions
        $activeSheet->getColumnDimension('A')->setWidth(60); //Item title
        $activeSheet->getColumnDimension('B')->setWidth(80); //Item URL
        $activeSheet->getColumnDimension('C')->setWidth(20); //Item status
        $activeSheet->getColumnDimension('D')->setWidth(40); //Item industry
        $activeSheet->getColumnDimension('E')->setWidth(40); //Item category
        $activeSheet->getColumnDimension('F')->setWidth(60); //Item category [alternative]
        $activeSheet->getColumnDimension('G')->setWidth(35); //Item country
        $activeSheet->getColumnDimension('H')->setWidth(20); //Item created on

        $activeSheet->getDefaultRowDimension()->setRowHeight(20);
        //endregion set columns dimensions

        //region first row
        $activeSheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7E6E6');

        $activeSheet->setCellValue('A1', 'Item');
        $activeSheet->setCellValue('B1', 'Link');
        $activeSheet->setCellValue('C1', 'Status');
        $activeSheet->setCellValue('D1', 'Industry');
        $activeSheet->setCellValue('E1', 'Category');
        $activeSheet->setCellValue('F1', 'Category [suggested version]');
        $activeSheet->setCellValue('G1', 'Item Country');
        $activeSheet->setCellValue('H1', 'Created on');

        $activeSheet->getStyle('A1:H1')->getFont()->setSize(12)->setBold(true);
        //endregion first row

        //region get statistics from DB
        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        $countriesById = $countryModel->getAllCountries();

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $items = $itemsModel->getItemsNotAvailableOnPublicPage([
            'select' => [
                'i.`id`',
                'i.`title`',
                'i.`id_cat`',
                'i.`p_country`',
                'i.`create_date`',
                'cat.`name` as category_name',
                'cat.`breadcrumbs`',
                'i.`draft`',
                'i.`visible`',
                'i.`moderation_is_approved`',
                'i.`blocked`',
                'i.`status`',
                'u.`status` as user_status',
            ]
        ]);
        //endregion get statistics from DB

        //region second row
        $activeSheet->getStyle('A2:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF2CC');

        $activeSheet->setCellValue('C2', 'TOTAL ACTIVE: ' . count($items));
        $activeSheet->setCellValue('D2', 'REPORT DATE: ' . date('F d, Y'));
        $activeSheet->getStyle('A2:H2')->getFont()->setSize(10)->setBold(true);
        //endregion second row

        $rowIndex = 2;
        $categoriesCache = [];

        foreach ($items as $item) {
            $rowIndex++;

            $activeSheet->setCellValue('A' . $rowIndex, $item['title']);
            $activeSheet->setCellValue('B' . $rowIndex, makeItemUrl($item['id'], $item['title']));

            if ($item['draft']) {
                $status = 'DRAFT ITEM';
            } elseif ('active' != $item['user_status']) {
                $status = 'USER STATUS: ' . $item['user_status'];
            } elseif ($item['blocked']) {
                $status = 'BLOCKED ITEM';
            } elseif (!$item['visible']) {
                $status = 'NOT VISIBLE ITEM';
            } elseif (!$item['moderation_is_approved']) {
                $status = 'NOT MODERATED ITEM';
            } else {
                switch ($item['status']) {
                    case 4:
                        $status = 'ITEM STATUS: EXPIRED';

                        break;
                    case 5:
                        $status = 'ITEM STATUS: ORDERED';

                        break;
                    case 6:
                        $status = 'ITEM STATUS: SOLD';

                        break;
                }
            }

            $activeSheet->setCellValue('C' . $rowIndex, $status);

            if (!isset($categoriesCache[$item['id_cat']])) {
                $categoryCrumbs = json_decode('[' . $item['breadcrumbs'] . ']', true);
                $industry = array_shift(array_shift($categoryCrumbs));
                $categoryTree = [];

                foreach ($categoryCrumbs as $category) {
                    $categoryTree[] = array_shift($category);
                }

                $categoriesCache[$item['id_cat']] = [
                    'industry'  => $industry,
                    'tree'      => implode(' => ', $categoryTree),
                ];
            }

            $activeSheet->setCellValue('D' . $rowIndex, $categoriesCache[$item['id_cat']]['industry']);
            $activeSheet->setCellValue('E' . $rowIndex, $item['category_name']);
            $activeSheet->setCellValue('F' . $rowIndex, $categoriesCache[$item['id_cat']]['tree']);
            $activeSheet->setCellValue('G' . $rowIndex, $countriesById[$item['p_country']]['country_name'] ?? '');
            $activeSheet->setCellValue('H' . $rowIndex, $item['create_date']);
        }

        //freeze first row
        $activeSheet->freezePane('A3');
        //add border
        $activeSheet->getStyle('A1:H' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $activeSheet->getStyle('A1:H' . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //align all table content
        $activeSheet->getStyle('A:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        //set font size
        $activeSheet->getStyle('A3:H' . $rowIndex)->getFont()->setSize(10);
        //set cell autoheight (work only in Microsoft Office)
        $activeSheet->getStyle('A1:H' . $rowIndex)->getAlignment()->setWrapText(true);

        return $excel;
    }

    private function sellersPerIndustry(): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('Sellers per Industry');

        //region set columns dimensions
        $activeSheet->getColumnDimension('A')->setWidth(50);
        $activeSheet->getColumnDimension('B')->setWidth(20);
        $activeSheet->getColumnDimension('C')->setWidth(20);

        $activeSheet->getDefaultRowDimension()->setRowHeight(20);
        //endregion set columns dimensions

        //align all table content
        $activeSheet->getStyle('A:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        //region first row
        $activeSheet->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7E6E6');

        $activeSheet->setCellValue('A1', 'Industry');
        $activeSheet->setCellValue('B1', 'Total Sellers');
        $activeSheet->setCellValue('C1', 'Active Sellers');

        $activeSheet->getStyle('A1:C1')->getFont()->setSize(12)->setBold(true);
        //endregion first row

        //region get statistics from DB
        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);

        $industries = $categoryModel->get_industries();

        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);

        $countCompaniesByIndustry = $companyModel->get_report_sellers_per_industry();
        $countActiveCompaniesByIndustry = $companyModel->get_report_sellers_per_industry([
            'companyBlocked'    => 0,
            'userStatus'        => 'active',
        ]);

        $countActiveSellers = $companyModel->countCompanies([
            'conditions' => [
                'companyBlocked'    => 0,
                'userStatus'        => 'active',
                'modelUser'         => 0,
                'fakeUser'          => 0,
            ],
            'joins'     => [
                'users'
            ],
        ]);

        $countSellers = $companyModel->countCompanies([
            'conditions' => [
                'modelUser'         => 0,
                'fakeUser'          => 0,
            ],
            'joins'     => [
                'users'
            ],
        ]);
        //endregion get statistics from DB

        //region second row
        $activeSheet->setCellValue('A2', 'REPORT CREATED ON: ' . date('F d, Y'));
        $activeSheet->setCellValue('B2', 'TOTAL SELLERS: ' . $countSellers);
        $activeSheet->setCellValue('C2', 'Active Sellers: ' . $countActiveSellers);

        $activeSheet->getStyle('A2:C2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF2CC');
        $activeSheet->getStyle('A2:C2')->getFont()->setSize(10)->setBold(true);
        $activeSheet->getRowDimension('2')->setRowHeight(30);
        //endregion second row

        $rowIndex = 2;
        foreach ($industries as $industry) {
            $rowIndex++;

            $activeSheet->setCellValue('A' . $rowIndex, $industry['name']);
            $activeSheet->setCellValue('B' . $rowIndex, $countCompaniesByIndustry[$industry['category_id']]['countSellers'] ?? 0);
            $activeSheet->setCellValue('C' . $rowIndex, $countActiveCompaniesByIndustry[$industry['category_id']]['countSellers'] ?? 0);
        }

        //freeze first row
        $activeSheet->freezePane('A3');
        //add border
        $activeSheet->getStyle('A1:C' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $activeSheet->getStyle('A1:C' . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //set font size
        $activeSheet->getStyle('A3:C' . $rowIndex)->getFont()->setSize(10);
        //set cell autoheight (work only in Microsoft Office)
        $activeSheet->getStyle('A1:C' . $rowIndex)->getAlignment()->setWrapText(true);

        return $excel;
    }

    private function buyersPerIndustry(): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('Buyers per Industry');

        //region set columns dimensions
        $activeSheet->getColumnDimension('A')->setWidth(50);
        $activeSheet->getColumnDimension('B')->setWidth(20);
        $activeSheet->getColumnDimension('C')->setWidth(20);

        $activeSheet->getDefaultRowDimension()->setRowHeight(20);
        //endregion set columns dimensions

        //align all table content
        $activeSheet->getStyle('B2:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        //region first row
        $activeSheet->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7E6E6');

        $activeSheet->setCellValue('A1', 'Industry');
        $activeSheet->setCellValue('B1', 'Total Buyers');
        $activeSheet->setCellValue('C1', 'Active Buyers');

        $activeSheet->getStyle('A1:C1')->getFont()->setSize(12)->setBold(true);
        $activeSheet->getStyle('A1:C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        //endregion first row

        //region get statistics from DB
        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);

        $industries = $categoryModel->get_industries();

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $countActiveBuyers = $userModel->count_users([
            'fake_user' => 0,
            'is_model'  => 0,
            'status'    => 'active',
            'group'     => 1,
        ]);

        $countBuyers = $userModel->count_users([
            'fake_user' => 0,
            'is_model'  => 0,
            'group'     => 1,
        ]);

        /** @var \Buyer_Item_Categories_Stats_Model $buyerStats */
        $buyerStats = model(\Buyer_Item_Categories_Stats_Model::class);

        $countBuyersByIndustry = $buyerStats->getReportBuyersPerIndustry();
        $countActiveBuyersByIndustry = $buyerStats->getReportBuyersPerIndustry([
            'userStatus'        => 'active',
        ]);

        $mostPopularIndustriesForBuyers = array_column(array_slice($countBuyersByIndustry, 0, 10), 'id_category');
        $mostPopularIndustriesForActiveBuyers = array_column(array_slice($countActiveBuyersByIndustry, 0, 10), 'id_category');
        //endregion get statistics from DB

        //region second row
        $activeSheet->setCellValue('A2', 'REPORT CREATED ON: ' . date('F d, Y'));
        $activeSheet->setCellValue('B2', 'TOTAL Buyers: ' . $countBuyers);
        $activeSheet->setCellValue('C2', 'Active Buyers: ' . $countActiveBuyers);

        $activeSheet->getStyle('A2:C2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF2CC');
        $activeSheet->getStyle('A2:C2')->getFont()->setSize(10)->setBold(true);
        $activeSheet->getRowDimension('2')->setRowHeight(30);
        //endregion second row

        $rowIndex = 2;
        foreach ($industries as $industry) {
            $rowIndex++;

            $activeSheet->setCellValue('A' . $rowIndex, $industry['name']);
            $activeSheet->setCellValue('B' . $rowIndex, $countBuyersByIndustry[$industry['category_id']]['countBuyers'] ?? 0);
            $activeSheet->setCellValue('C' . $rowIndex, $countActiveBuyersByIndustry[$industry['category_id']]['countBuyers'] ?? 0);

            if (in_array($industry['category_id'], $mostPopularIndustriesForBuyers)) {
                $activeSheet->getStyle('A' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
                $activeSheet->getStyle('B' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
            }

            if (in_array($industry['category_id'], $mostPopularIndustriesForActiveBuyers)) {
                $activeSheet->getStyle('A' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
                $activeSheet->getStyle('C' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
            }
        }

        $activeSheet->getColumnDimension('D')->setWidth(20);
        $activeSheet->getColumnDimension('E')->setWidth(20);
        $activeSheet->getColumnDimension('F')->setWidth(20);
        $activeSheet->getStyle('E5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
        $activeSheet->setCellValue('F5', 'TOP 10');
        $activeSheet->getStyle('F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $activeSheet->getStyle('F5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        //freeze first row
        $activeSheet->freezePane('A3');
        //add border
        $activeSheet->getStyle('A1:C' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $activeSheet->getStyle('A1:C' . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //set font size
        $activeSheet->getStyle('A3:C' . $rowIndex)->getFont()->setSize(10);
        //set cell autoheight (work only in Microsoft Office)
        $activeSheet->getStyle('A1:C' . $rowIndex)->getAlignment()->setWrapText(true);

        return $excel;
    }

    private function productsPerIndustry(): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('Products per Industry');

        //region set columns dimensions
        $activeSheet->getColumnDimension('A')->setWidth(50);
        $activeSheet->getColumnDimension('B')->setWidth(20);
        $activeSheet->getColumnDimension('C')->setWidth(20);
        $activeSheet->getColumnDimension('D')->setWidth(20);

        $activeSheet->getDefaultRowDimension()->setRowHeight(20);
        //endregion set columns dimensions

        //align all table content
        $activeSheet->getStyle('B2:D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        //region first row
        $activeSheet->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7E6E6');

        $activeSheet->setCellValue('A1', 'Industry');
        $activeSheet->setCellValue('B1', 'Total Items');
        $activeSheet->setCellValue('C1', 'Total Active Items');
        $activeSheet->setCellValue('D1', 'Total Draft Items ');

        $activeSheet->getStyle('A1:D1')->getFont()->setSize(12)->setBold(true);
        $activeSheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        //endregion first row

        //region get statistics from DB
        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);

        $industries = $categoryModel->get_industries();

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $countAllItems = $itemsModel->count_items([
            'fake_item'     => 0,
            'model_item'    => 0,
        ]);

        $countDraftItems = $itemsModel->count_items([
            'fake_item'     => 0,
            'model_item'    => 0,
            'draft'         => 1,
        ]);

        $countActiveItems = $itemsModel->getCountItemsAvailableOnPublicPage([
            'modelUser' => 0
        ]);

        $countItemsByIndustry = $itemsModel->getReportItemsPerIndustry();
        $countDraftItemsByIndustry = $itemsModel->getReportItemsPerIndustry([
            'draft' => 1
        ]);

        $countActiveItemsByIndustry = $itemsModel->getReportItemsPerIndustry([
            'onlyActiveItems' => true
        ]);

        $mostPopularItemsIndustries = array_column(array_slice($countItemsByIndustry, 0, 10), 'industryId');
        $mostPopularActiveItemsIndustries = array_column(array_slice($countActiveItemsByIndustry, 0, 10), 'industryId');
        $mostPopularDraftItemsIndustries = array_column(array_slice($countDraftItemsByIndustry, 0, 10), 'industryId');
        //endregion get statistics from DB

        //region second row
        $activeSheet->setCellValue('A2', 'REPORT CREATED ON: ' . date('F d, Y'));
        $activeSheet->setCellValue('B2', 'TOTAL ITEMS: ' . $countAllItems);
        $activeSheet->setCellValue('C2', 'TOTAL ACTIVE: ' . $countActiveItems);
        $activeSheet->setCellValue('D2', 'TOTAL DRAFT: ' . $countDraftItems);

        $activeSheet->getStyle('A2:D2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF2CC');
        $activeSheet->getStyle('A2:D2')->getFont()->setSize(10)->setBold(true);
        $activeSheet->getRowDimension('2')->setRowHeight(30);
        //endregion second row

        $rowIndex = 2;
        foreach ($industries as $industry) {
            $rowIndex++;

            $activeSheet->setCellValue('A' . $rowIndex, $industry['name']);
            $activeSheet->setCellValue('B' . $rowIndex, $countItemsByIndustry[$industry['category_id']]['countItems'] ?? 0);
            $activeSheet->setCellValue('C' . $rowIndex, $countActiveItemsByIndustry[$industry['category_id']]['countItems'] ?? 0);
            $activeSheet->setCellValue('D' . $rowIndex, $countDraftItemsByIndustry[$industry['category_id']]['countItems'] ?? 0);

            if (in_array($industry['category_id'], $mostPopularItemsIndustries)) {
                $activeSheet->getStyle('A' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
                $activeSheet->getStyle('B' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
            }

            if (in_array($industry['category_id'], $mostPopularActiveItemsIndustries)) {
                $activeSheet->getStyle('A' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
                $activeSheet->getStyle('C' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
            }

            if (in_array($industry['category_id'], $mostPopularDraftItemsIndustries)) {
                $activeSheet->getStyle('A' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
                $activeSheet->getStyle('D' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
            }
        }

        $activeSheet->getColumnDimension('E')->setWidth(20);
        $activeSheet->getColumnDimension('F')->setWidth(20);
        $activeSheet->getColumnDimension('G')->setWidth(20);
        $activeSheet->getStyle('F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5A6BD');
        $activeSheet->setCellValue('G5', 'TOP 10');
        $activeSheet->getStyle('G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $activeSheet->getStyle('G5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        //freeze first row
        $activeSheet->freezePane('A3');
        //add border
        $activeSheet->getStyle('A1:D' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $activeSheet->getStyle('A1:D' . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //set font size
        $activeSheet->getStyle('A3:D' . $rowIndex)->getFont()->setSize(10);
        //set cell autoheight (work only in Microsoft Office)
        $activeSheet->getStyle('A1:D' . $rowIndex)->getAlignment()->setWrapText(true);

        if (isset($countDraftItemsByIndustry[''])) {
            $rowIndex += 2;

            $activeSheet->setCellValue('A' . $rowIndex, 'Items without industry');
            $activeSheet->setCellValue('D' . $rowIndex, $countDraftItemsByIndustry['']['countItems'] ?? 0);
        }

        return $excel;
    }
}

/* End of file reports.php */
/* Location: /tinymvc/myapp/controllers/reports.php */
