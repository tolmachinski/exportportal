<?php

declare(strict_types=1);

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Validators\PickOfTheMonthValidator;

/**
 * Controller PickOfTheMonth
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Pick_Of_The_Month_Controller extends TinyMVC_Controller
{
    private $types = [
        'item', 'company'
    ];

    /**
     * Administration page
     */
    public function company(): void
    {
        checkAdmin('manage_picks_of_the_month');

        views()->assign(array(
            'title'         => "Companies picks of the month",
            'url'           => 'pick_of_the_month/ajax_dt_list/company',
        ));

        views()->display('admin/header_view');
        views()->display('admin/pick_of_the_month/index_view');
        views()->display('admin/footer_view');
    }

    /**
     * Administration page
     */
    public function item(): void
    {
        checkAdmin('manage_picks_of_the_month');

        views()->assign(array(
            'title'         => "Items picks of the month",
            'url'           => 'pick_of_the_month/ajax_dt_list/item',
        ));

        views()->display('admin/header_view');
        views()->display('admin/pick_of_the_month/index_view');
        views()->display('admin/footer_view');
    }

    public function ajax_dt_list()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('manage_picks_of_the_month');

        $type = (string) cleanInput(uri()->segment(3));
        $request = request()->request;

        $dtFilters =  dtConditions($request->all(), [
            ['as' => 'start_date',   'key' => 'start_date',   'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'end_date',     'key' => 'end_date',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        ]);

        $perPage = $request->getInt('iDisplayLength', 10);
        $page = $request->getInt('iDisplayStart', 0) / $perPage + 1;

        try {

            switch($type)
            {
                case 'item':
                    /** @var Pick_Of_The_Month_Item_Model $pickModel */
                    $pickModel = model(Pick_Of_The_Month_Item_Model::class);

                    $columns = [
                        "{$pickModel->getTable()}.`id`",
                        "{$pickModel->getTable()}.`start_date`",
                        "{$pickModel->getTable()}.`end_date`",
                        "{$pickModel->getTable()}.`id_seller`",
                        "CONCAT_WS(' ', `phone_code`, `phone`) as full_phone",
                        "`email`",
                        "CONCAT_WS(' ', `fname`, `lname`) as user_name",
                        "{$pickModel->getTable()}.`id_item`",
                        "`title`",
                    ];

                    $joins = ['users', 'items'];

                    break;
                case 'company':
                    /** @var Pick_Of_The_Month_Company_Model $pickModel */
                    $pickModel = model(Pick_Of_The_Month_Company_Model::class);

                    $columns = [
                        "{$pickModel->getTable()}.`id`",
                        "{$pickModel->getTable()}.`start_date`",
                        "{$pickModel->getTable()}.`end_date`",
                        "{$pickModel->getTable()}.`id_seller`",
                        "`name_company`",
                        "`index_name`",
                        "`type_company`",
                        "CONCAT_WS(' ', `phone_code`, `phone`) as full_phone",
                        "`email`",
                        "CONCAT_WS(' ', `fname`, `lname`) as user_name",
                        "{$pickModel->getTable()}.`id_company`",
                    ];

                    $joins = ['companies', 'users'];

                    break;
            }

            $paginator = $pickModel->paginate(
                [
                    'columns'    => $columns,
                    'conditions' => $dtFilters,
                    'order'      => \array_column(
                        \dtOrdering(
                            request()->request->all(),
                            [
                                'dt_id'      => "`{$pickModel->getTable()}`.`{$pickModel->getPrimaryKey()}`",
                                'dt_start'   => "`{$pickModel->getTable()}`.`start_date`",
                                'dt_end'     => "`{$pickModel->getTable()}`.`end_date`",
                            ]
                        ),
                        'direction',
                        'column'
                    ),
                    'joins'     => $joins
                ],
                $perPage,
                $page
            );
            foreach($paginator['data'] as $row) {

                $delLink = __SITE_URL . 'pick_of_the_month/ajax_operations/delete/' . $type . '/' . $row['id'];
                $deleteButton = <<<DELETE_BUTTON
                <a href="#"
                    class="ep-icon ep-icon_remove txt-red confirm-dialog"
                    title="Delete the record"
                    data-delete-link="$delLink"
                    data-callback="delete_record"
                    data-message="Are you sure you want to delete this record">
                </a>
                DELETE_BUTTON;

                if($type == 'item'){
                    $resourceLink = "<a href='" . makeItemUrl($row['id_item'], $row['title']) . "' target='_blank'>" . $row['title'] . "</a>";
                }else{
                    $resourceLink = "<a href='" . getCompanyURL($row) . "' target='_blank'>" . $row['name_company'] . "</a>";
                }

                $aaData[] = [
                    'dt_id'         => $row['id'],
                    'dt_resource'   => $resourceLink,
                    'dt_start'      => getDateFormat($row['start_date'], 'Y-m-d'),
                    'dt_end'        => getDateFormat($row['end_date'], 'Y-m-d'),
                    'dt_email'      => cleanOutput($row['email']),
                    'dt_id_seller'  => (int) $row['id_seller'],
                    'dt_seller'     => "<a href='" . getUserLink($row['user_name'], $row['id_seller'], 'seller') . "' target='_blank'>" . $row['user_name'] . "</a>",
                    'dt_phone'      => cleanOutput($row['full_phone']),
                    'dt_actions'    => $deleteButton,
                ];

            }
        } catch (\Throwable $th) {
            $aaData = [];
        }

        jsonResponse('', 'success', [
            'sEcho'                => $request->getInt('sEcho', 0),
            'iTotalRecords'        => $paginator['total'] ?? 0,
            'iTotalDisplayRecords' => $paginator['total'] ?? 0,
            'aaData'               => $aaData ?? [],
        ]);

    }

    public function ajax_operations()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged_in'));
        }

        checkAdminAjax('manage_picks_of_the_month');

        $action = (string) cleanInput(uri()->segment(3));
        $type = (string) cleanInput(uri()->segment(4));
        $id = (int) cleanInput(uri()->segment(5));

        if(!in_array($type, $this->types)){
            show_404();
        }

        try {
            switch ($action) {
                case 'add':
                    return $this->addPickOfTheMonth($type);
                    break;
                case 'delete':
                    return $this->deletePickOfTheMonth($type, $id);
                    break;
                default:
                    show_404();
                break;
            }
        } catch (\Exception $exception) {
            $errorCode = $exception->getCode();
            if (400 === $errorCode) {
                jsonResponse($exception->getMessage(), 'warning');
            }
            if (404 === $errorCode || 500 === $errorCode) {
                jsonResponse($exception->getMessage());
            }

            jsonResponse('Failed to process request due to server error');
        }

    }

    private function addPickOfTheMonth($type)
    {
        $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new PickOfTheMonthValidator($adapter);

        if (!$validator->validate(request()->request->all())) {
			\jsonResponse(
                \array_map(
                    fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        switch($type)
        {
            case 'item':
                /** @var Pick_Of_The_Month_Item_Model $pickModel */
                $pickModel = model(Pick_Of_The_Month_Item_Model::class);

                /** @var Items_Model $resourceModel */
                $resourceModel = model(Items_Model::class);

                $idResource = request()->request->getInt('id_item');
                $resource = $resourceModel->get_item_simple($idResource);

                break;
            case 'company':
                /** @var Pick_Of_The_Month_Company_Model $pickModel */
                $pickModel = model(Pick_Of_The_Month_Company_Model::class);

                /** @var Company_Model $resourceModel */
                $resourceModel = model(Company_Model::class);

                $idResource = request()->request->getInt('id_company');
                $resource = $resourceModel->get_simple_company($idResource);

                break;
        }

        if(empty($resource)){
            jsonResponse('No such resource');
        }

        $startDate = request()->request->get('start_date');
        $endDate = request()->request->get('end_date');
        $found = $pickModel->findOneBy([
            'conditions' => [
                'dateBetween' => $startDate,
                'dateBetween' => $endDate,
            ]
        ]);

        if(!empty($found)){
            jsonResponse('There is already a pick of the month for this period');
        }

        $insert = [
            'id_seller'  => $type == 'item' ? $resource['id_seller'] : $resource['id_user'],
            'start_date' => getDateFormat($startDate, 'm/d/Y', 'Y-m-d'),
            'end_date'   => getDateFormat($endDate, 'm/d/Y', 'Y-m-d'),
            'comment'    => cleanInput(request()->request->get('comment')),
        ];

        if($type == 'item'){
            $insert['id_item'] = $idResource;
        }else{
            $insert['id_company'] = $idResource;
        }

        $pickModel->insertOne($insert);

        jsonResponse('Pick of the month inserted successfully!', 'success');
    }

    private function deletePickOfTheMonth($type, $id)
    {
        if(empty($id)){
            jsonResponse('No such resource');
        }

        switch($type)
        {
            case 'item':
                /** @var Pick_Of_The_Month_Item_Model $pickModel */
                $pickModel = model(Pick_Of_The_Month_Item_Model::class);
                break;
            case 'company':
                /** @var Pick_Of_The_Month_Company_Model $pickModel */
                $pickModel = model(Pick_Of_The_Month_Company_Model::class);
                break;
        }

        $resource = $pickModel->findOne($id);
        if(empty($resource)){
            jsonResponse('No such resource');
        }

        $pickModel->deleteOne($id);

        jsonResponse('Pick of the month deleted successfully!', 'success');
    }
}

// End of file pick_of_the_month.php
// Location: /tinymvc/myapp/controllers/pick_of_the_month.php
