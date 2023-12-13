<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Exceptions\MatchmakingException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\Style\Fill;
use \PhpOffice\PhpSpreadsheet\Style\Border;

final class MatchmakingService
{
    /**
     * @param int $userId
     * @param array $conditions
     * @param int|null $page
     * @param int|null $perPage
     *
     * @throws MatchmakingException
     *
     * @return array [$buyers, $countBuyers]
     */
    public function getBuyers(int $userId, array $conditions = [], ?int $page = null, ?int $perPage = null): array
    {
        /** @var \User_Model $userModel */
        $userModel = model(\User_Model::class);
        /** @var \Items_Model $itemsModel */
        $itemsModel = model(\Items_Model::class);
        /** @var \Category_Model $categoryModel */
        $categoryModel = model(\Category_Model::class);
        /** @var \Company_Model $companyModel */
        $companyModel = model(\Company_Model::class);

        $itemsIndustries = $itemsModel->getItems([
            'columns' => [
                "DISTINCT(CAST(JSON_EXTRACT(JSON_KEYS(JSON_EXTRACT(CONCAT('[', {$categoryModel->get_categories_table()}.breadcrumbs, ']'), '$[0]')), '$[0]') AS UNSIGNED)) industryId",
            ],
            'conditions' => [
                'isVisible' => 1,
                'sellerId'  => $userId,
                'isDraft'   => 0,
            ],
            'joins' => [
                'categories'
            ],
        ]);

        $companyIndustries = $companyModel->getCompanies([
            'columns' => [
                "`{$companyModel->getRelationIndustryTable()}`.`id_industry` industryId",
            ],
            'conditions' => [
                'companyType'   => 'company',
                'userId'        => $userId,
            ],
            'joins' => [
                'relationIndustry',
            ],
        ]);

        if (empty($industries = array_unique(array_column(array_merge($itemsIndustries, $companyIndustries), 'industryId')))) {
            throw new MatchmakingException(translate('systmess_matchmaking_buyers_list_no_seller_industries'), MatchmakingException::SELLER_WITHOUT_INDUSTRIES_CODE);
        }

        if (isset($page, $perPage)) {
            $countBuyers = $userModel->getCountMatchmakingBuyers($industries, $conditions);

            if (empty($countBuyers)) {
                throw new MatchmakingException(translate('systmess_matchmaking_empty_buyers_list'), MatchmakingException::EMPTY_BUYERS_LIST_CODE);
            }

            if ($page >= $countBuyers) {
                throw new MatchmakingException(
                    translate('systmess_error_invalid_data'),
                    MatchmakingException::PAGE_GREATER_THAN_TOTAL_BUYERS_CODE,
                    ['totalRecords' => $countBuyers],
                );
            }
        }

        $buyers = $userModel->getMatchmakingBuyers($industries, $conditions, $page, $perPage);

        if (empty($buyers)) {
            throw new MatchmakingException(translate('systmess_matchmaking_empty_buyers_list'), MatchmakingException::EMPTY_BUYERS_LIST_CODE);
        }

        return [$buyers, $countBuyers ?? count($buyers)];
    }

    /**
     * @param int $userId
     * @param array $conditions
     *
     * @return int $countBuyers
     */
    public function countBuyers(int $userId, array $conditions = []): int
    {
        /** @var \User_Model $userModel */
        $userModel = model(\User_Model::class);
        /** @var \Items_Model $itemsModel */
        $itemsModel = model(\Items_Model::class);
        /** @var \Category_Model $categoryModel */
        $categoryModel = model(\Category_Model::class);
        /** @var \Company_Model $companyModel */
        $companyModel = model(\Company_Model::class);

        $itemsIndustries = $itemsModel->getItems([
            'columns' => [
                "DISTINCT(CAST(JSON_EXTRACT(JSON_KEYS(JSON_EXTRACT(CONCAT('[', {$categoryModel->get_categories_table()}.breadcrumbs, ']'), '$[0]')), '$[0]') AS UNSIGNED)) industryId",
            ],
            'conditions' => [
                'isVisible' => 1,
                'sellerId'  => $userId,
                'isDraft'   => 0,
            ],
            'joins' => [
                'categories'
            ],
        ]);

        $companyIndustries = $companyModel->getCompanies([
            'columns' => [
                "`{$companyModel->getRelationIndustryTable()}`.`id_industry` industryId",
            ],
            'conditions' => [
                'companyType'   => 'company',
                'userId'        => $userId,
            ],
            'joins' => [
                'relationIndustry',
            ],
        ]);

        $industries = array_unique(array_column(array_merge($itemsIndustries, $companyIndustries), 'industryId'));

        return empty($industries) ? 0 : $userModel->getCountMatchmakingBuyers($industries, $conditions);
    }

    /**
     * @param int $userId
     * @param array $conditions
     * @param int|null $page
     * @param int|null $perPage
     *
     * @throws MatchmakingException
     *
     * @return array [$sellers, $countSellers]
     */
    public function getSellers(int $userId, array $conditions = [], ?int $page = null, ?int $perPage = null): array
    {
        /** @var \Buyer_Item_Categories_Stats_Model $buyerStats */
        $buyerStats = model(\Buyer_Item_Categories_Stats_Model::class);

        if (empty($industriesOfInterest = $buyerStats->findAllBy(['conditions' => ['idUser' => $userId]]))) {
            throw new MatchmakingException(translate('systmess_matchmaking_buyer_without_industries'), MatchmakingException::BUYER_WITHOUT_INDUSTRIES_CODE);
        }

        $industriesIds = array_column($industriesOfInterest, 'id_category');

        /** @var \Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(\Elasticsearch_Items_Model::class);
        $countItemsBySeller = $elasticsearchItemsModel->getCountItemsBySeller(['industries' => $industriesIds]);

        if (empty($countSellers = count($countItemsBySeller))) {
            throw new MatchmakingException(translate('systmess_matchmaking_empty_sellers_list'), MatchmakingException::EMPTY_SELLERS_LIST_CODE);
        }

        if ($page >= $countSellers) {
            throw new MatchmakingException(
                translate('systmess_error_invalid_data'),
                MatchmakingException::PAGE_GREATER_THAN_TOTAL_SELLERS_CODE,
                ['totalRecords' => $countSellers],
            );
        }

        /** @var \Company_Model $companyModel */
        $companyModel = model(\Company_Model::class);
        $companiesTable = $companyModel->get_company_table();

        /** @var \Country_Model $countryModel */
        $countryModel = model(\Country_Model::class);
        $countryTable = $countryModel->get_countries_table();

        $queryParams = array_filter(
            [
                'matchmakingCertifiedSellers'   => $conditions['matchmakingCertifiedSellers'] ?? null,
                'matchmakingJoinB2bRequests'    => $industriesIds,
                'hasB2bRequests'                => $conditions['hasB2bRequests'] ?? null,
                'companyType'                   => 'company',
                'sellersIds'                    => array_keys($countItemsBySeller),
            ],
            fn ($value) => null !== $value
        );

        if (!empty($conditions)) {
            $countSellers = $companyModel->countCompanies([
                'conditions' => $queryParams,
                'joins' => [
                    'users',
                    'usersCountry',
                ],
            ]);
        }

        /** @var \User_Model $userModel */
        $userModel = model(\User_Model::class);
        $usersTable = $userModel->get_users_table();

        $sellers = $companyModel->getCompanies([
            'columns' => [
                "`{$usersTable}`.`idu` userId",
                "`{$usersTable}`.`fname` userFname",
                "`{$usersTable}`.`lname` userLname",
                "`{$usersTable}`.`email` userEmail",
                "`{$usersTable}`.`phone_code` userPhoneCode",
                "`{$usersTable}`.`phone` userPhone",
                "IF(`{$usersTable}`.`user_group` IN (3, 6), 1, 0) isCertified",
                "`{$countryTable}`.`country` userCountry",
                "`{$companiesTable}`.`id_company`",
                "`{$companiesTable}`.`index_name`",
                "`{$companiesTable}`.`type_company`",
                "`{$companiesTable}`.`name_company`",
                "`b2bRequests`.`hasB2bRequests`",
            ],
            'conditions' => $queryParams,
            'joins' => [
                'users',
                'usersCountry',
            ],
            'order' => [
                'isCertified' => 'desc',
                'hasB2bRequests' => 'desc',
            ],
            'limit' => $perPage,
            'skip'  => $page,
        ]);

        usort($sellers, function ($seller1, $seller2) use ($countItemsBySeller) {
            if ($seller1['isCertified'] == $seller2['isCertified']) {
                if ($seller1['hasB2bRequests'] == $seller2['hasB2bRequests']) {
                    return $countItemsBySeller[$seller1['userId']] <= $countItemsBySeller[$seller2['userId']] ? 1 : -1;
                }

                return $seller1['hasB2bRequests'] <= $seller2['hasB2bRequests'] ? 1 : -1;
            }

            return $seller1['isCertified'] <= $seller2['isCertified'] ? 1 : -1;
        });

        foreach ($sellers as &$seller) {
            $seller['countItems'] = $countItemsBySeller[$seller['userId']];
            $seller['fullName'] = $seller['userFname'] . ' ' . $seller['userLname'];
        }

        return [$sellers, $countSellers];
    }

    /**
     * @param int $userId
     * @param array $conditions
     *
     * @return array [$countSellers, $countItems]
     */
    public function counSellersItems(int $userId, array $conditions = []): array
    {
        /** @var \Buyer_Item_Categories_Stats_Model $buyerStats */
        $buyerStats = model(\Buyer_Item_Categories_Stats_Model::class);

        if (empty($industriesOfInterest = $buyerStats->findAllBy(['conditions' => ['idUser' => $userId]]))) {
            return [0, 0];
        }

        $industriesIds = array_column($industriesOfInterest, 'id_category');

        /** @var \Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(\Elasticsearch_Items_Model::class);
        $countItemsBySeller = $elasticsearchItemsModel->getCountItemsBySeller(['industries' => $industriesIds]);

        if (empty($conditions)) {
            return [count($countItemsBySeller), array_sum(array_values($countItemsBySeller))];
        }

        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);

        $queryParams = array_filter(
            [
                'matchmakingCertifiedSellers'   => $conditions['matchmakingCertifiedSellers'] ?? null,
                'matchmakingJoinB2bRequests'    => $industriesIds,
                'hasB2bRequests'                => $conditions['hasB2bRequests'] ?? null,
                'companyType'                   => 'company',
                'sellersIds'                    => array_keys($countItemsBySeller),
            ],
            fn ($value) => null !== $value
        );

        /** @var \User_Model $userModel */
        $userModel = model(\User_Model::class);

        $usersTable = $userModel->get_users_table();
        $sellers = $companyModel->getCompanies([
            'columns' => [
                "`{$usersTable}`.`idu` userId"
            ],
            'conditions' => $queryParams,
            'joins' => [
                'users',
                'usersCountry',
            ]
        ]);

        $countItems = 0;
        foreach ($sellers as $seller) {
            $countItems += $countItemsBySeller[$seller['userId']];
        }

        return [count($sellers), $countItems];
    }

    /**
     * @param int $userId
     * @param array $conditions
     *
     * @return Spreadsheet
     */
    public function getExcelWithBuyers(int $userId, array $conditions = []): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('List of buyers');

        //region set columns dimensions
        $activeSheet->getColumnDimension('B')->setWidth(50);
        $activeSheet->getColumnDimension('C')->setWidth(50);
        $activeSheet->getColumnDimension('D')->setWidth(20);
        $activeSheet->getColumnDimension('E')->setWidth(30);

        $activeSheet->getDefaultRowDimension()->setRowHeight(20);
        $activeSheet->getRowDimension(1)->setRowHeight(30);
        //endregion set columns dimensions

        //region first row
        $activeSheet->getStyle('B1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('C0504D');
        $activeSheet->getStyle('B1:E1')->getFont()->getColor(Fill::FILL_SOLID)->setARGB('FFFFFF');

        $activeSheet->setCellValue('B1', 'Full Name');
        $activeSheet->setCellValue('C1', 'Email');
        $activeSheet->setCellValue('D1', 'Phone number');
        $activeSheet->setCellValue('E1', 'Country');

        $activeSheet->getStyle('B1:E1')->getFont()->setSize(12)->setBold(true);
        $activeSheet->getStyle('B1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        //endregion first row

        try {
            list($buyers, $countBuyers) = $this->getBuyers($userId, $conditions);
        } catch (MatchmakingException $e) {
            $buyers = [];
        }

        $rowIndex = 1;
        foreach ($buyers as $buyer) {
            $rowIndex++;
            $activeSheet->setCellValue('B' . $rowIndex, decodeCleanInput($buyer['fname'] . ' ' . $buyer['lname']));
            $activeSheet->getCell('B' . $rowIndex)->getHyperlink()->setUrl(getUserLink($buyer['fname'] . ' ' . $buyer['lname'], $buyer['idu'], 'buyer'));
            $activeSheet->setCellValue('C' . $rowIndex, $buyer['email']);
            $activeSheet->setCellValue('D' . $rowIndex, $buyer['phone_code'] . ' ' . $buyer['phone']);
            $activeSheet->setCellValue('E' . $rowIndex, $buyer['country']);

            if ($rowIndex % 2 <> 0) {
                $activeSheet->getStyle("B{$rowIndex}:E{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC0CB');
            }
        }

        //freeze first row
        $activeSheet->freezePane('A2');
        //add border
        $activeSheet->getStyle('B1:E' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $activeSheet->getStyle('B1:E' . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //set font size
        $activeSheet->getStyle('B2:E' . $rowIndex)->getFont()->setSize(10);
        //set cell autoheight (work only in Microsoft Office)
        $activeSheet->getStyle('B1:E' . $rowIndex)->getAlignment()->setWrapText(true);

        return $excel;
    }

    /**
     * @param int $userId
     * @param array $conditions
     *
     * @return Spreadsheet
     */
    public function getExcelWithSellers(int $userId, array $conditions = []): Spreadsheet
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('List of sellers');

        //region set columns dimensions
        $activeSheet->getColumnDimension('B')->setWidth(50);
        $activeSheet->getColumnDimension('C')->setWidth(50);
        $activeSheet->getColumnDimension('D')->setWidth(20);
        $activeSheet->getColumnDimension('E')->setWidth(30);

        $activeSheet->getDefaultRowDimension()->setRowHeight(20);
        $activeSheet->getRowDimension(1)->setRowHeight(30);
        //endregion set columns dimensions

        //region first row
        $activeSheet->getStyle('B1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('4682B4');
        $activeSheet->getStyle('B1:E1')->getFont()->getColor(Fill::FILL_SOLID)->setARGB('FFFFFF');

        $activeSheet->setCellValue('B1', 'Full Name');
        $activeSheet->setCellValue('C1', 'Email');
        $activeSheet->setCellValue('D1', 'Phone number');
        $activeSheet->setCellValue('E1', 'Country');

        $activeSheet->getStyle('B1:E1')->getFont()->setSize(12)->setBold(true);
        $activeSheet->getStyle('B1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        //endregion first row

        try {
            list($sellers, $countSellers) = $this->getSellers($userId, $conditions);
        } catch (MatchmakingException $e) {
            $sellers = [];
        }

        $rowIndex = 1;
        foreach ($sellers as $seller) {
            $rowIndex++;
            $activeSheet->setCellValue('B' . $rowIndex, decodeCleanInput($seller['fullName']));
            $activeSheet->getCell('B' . $rowIndex)->getHyperlink()->setUrl(getUserLink($seller['fullName'], $seller['userId'], 'seller'));
            $activeSheet->setCellValue('C' . $rowIndex, $seller['userEmail']);
            $activeSheet->setCellValue('D' . $rowIndex, $seller['userPhoneCode'] . ' ' . $seller['userPhone']);
            $activeSheet->setCellValue('E' . $rowIndex, $seller['userCountry']);

            if ($rowIndex % 2 <> 0) {
                $activeSheet->getStyle("B{$rowIndex}:E{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F0F8FF');
            }
        }

        //freeze first row
        $activeSheet->freezePane('A2');
        //add border
        $activeSheet->getStyle('B1:E' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        //set vertical alignment
        $activeSheet->getStyle('B1:E' . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        //set font size
        $activeSheet->getStyle('B2:E' . $rowIndex)->getFont()->setSize(10);
        //set cell autoheight (work only in Microsoft Office)
        $activeSheet->getStyle('B1:E' . $rowIndex)->getAlignment()->setWrapText(true);

        return $excel;
    }
}
