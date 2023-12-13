<?php

declare(strict_types=1);

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Validators\WebinarsValidator;

/**
 * Controller Webinars
 */
class Webinars_Controller extends TinyMVC_Controller
{
    public function index()
    {
        show_404();
    }
    /**
     * Index page
     */
    public function administration(): void
    {
        checkIsLogged();
        checkPermision('webinars_administration');

        /** @var Webinar_Model $webinarsModel*/
        $webinarsModel = model(Webinar_Model::class);

        $id = id_from_link(uri()->segment(3));

        views([
            'admin/header_view',
            'admin/webinars/index_view',
            'admin/footer_view'],
        [
            'title'   => 'Webinars',
            'webinar' => $webinarsModel->findOne($id)
        ]);
    }

    public function ajaxDtAdministration()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('webinars_administration');

        $request = request()->request;

        $dtFilters =  dtConditions($request->all(), [
            ['as' => 'id',           'key' => 'id',   'type' => 'int'],
            ['as' => 'start_from',   'key' => 'start_from',   'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'start_to',     'key' => 'start_to',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        ]);

        $perPage = $request->getInt('iDisplayLength', 10);
        $page = $request->getInt('iDisplayStart', 0) / $perPage + 1;

        try {

            /** @var Webinar_Model $webinarsModel*/
            $webinarsModel = model(Webinar_Model::class);

            $paginator = $webinarsModel->paginate(
                [
                    'conditions' => array_merge($dtFilters),
                    'joins'      => ['requestsCount', 'leadsCount'],
                    'order'      => \array_column(
                        \dtOrdering(
                            request()->request->all(),
                            [
                                'dt_id'         => "`{$webinarsModel->getTable()}`.`{$webinarsModel->getPrimaryKey()}`",
                                'dt_start_date' => "`{$webinarsModel->getTable()}`.`start_date`",
                                'dt_created'    => "`{$webinarsModel->getTable()}`.`created_date`",
                                'dt_requested'  => "`requested`",
                                'dt_attended'   => "`attended`",
                                'dt_leads'      => "`leads`",
                            ]
                        ),
                        'direction',
                        'column'
                    ),
                ],
                $perPage,
                $page
            );

            foreach($paginator['data'] as $row) {

                #region link
                $webinar = '';
                if(!empty($row['link'])){
                    $webinar = <<<WEBINAR_INFO
                        <a href="{$row['link']}" target="_blank">{$row['link']}</a>
                    WEBINAR_INFO;
                }
                #endregion link

                $editBtn = <<<BUTTON
                    <a
                        data-title="Edit webinar"
                        href="/webinars/popup_forms/edit_webinar/{$row['id']}"
                        class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT fs-16"
                        title="Edit Webinar"></a>
                    BUTTON;

                $aaData[] = [
                    'dt_id'         => $row['id'],
                    'dt_title'      => $row['title'],
                    'dt_start_date' => getDateFormat($row['start_date']),
                    'dt_link'       => $webinar,
                    'dt_requested'  => $row['requested'],
                    'dt_attended'   => $row['attended'],
                    'dt_leads'      => $row['leads'],
                    'dt_created'    => getDateFormat($row['created_date']),
                    'dt_actions'    => $editBtn,
                ];

            }
        } catch (\Throwable $th) {
            dump($th);
            $aaData = [];
        }

        jsonResponse('', 'success', [
            'sEcho'                => $request->getInt('sEcho', 0),
            'iTotalRecords'        => $paginator['total'] ?? 0,
            'iTotalDisplayRecords' => $paginator['total'] ?? 0,
            'aaData'               => $aaData ?? [],
        ]);
    }

    public function popup_forms()
    {
        checkIsLoggedAjax();
        checkPermisionAjaxModal('webinars_administration');

        $op = uri()->segment(3);

        switch ($op) {
            case 'add_webinar':
                views()->display('admin/webinars/form_view', [
                    'url' => 'webinars/ajax_operations/add'
                ]);
            break;
            case 'edit_webinar':
                $id = (int) uri()->segment(4);

                if(empty($id)){
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Webinar_Model $webinarModel */
                $webinarModel = model(Webinar_Model::class);

                $webinar = $webinarModel->findOne($id);
                if(empty($webinar)){
                    messageInModal('No such webinar found!');
                }

                views()->display('admin/webinars/form_view', [
                    'url' => 'webinars/ajax_operations/edit',
                    'one' => $webinar
                ]);
            break;
        }
    }

    public function ajax_operations()
    {
        checkIsLoggedAjax();
        checkPermisionAjaxModal('webinars_administration');

        $op = uri()->segment(3);

        switch ($op) {
            case 'add':
                $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
                $validator = new WebinarsValidator($adapter);

                if (!$validator->validate(request()->request->all())) {
                    \jsonResponse(
                        \array_map(
                            fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                /** @var Webinar_Model $webinarModel */
                $webinarModel = model(Webinar_Model::class);

                $insert = [
                    'title'      => cleanInput(request()->request->get('title')),
                    'start_date' => DateTimeImmutable::createFromFormat('m/d/Y H:i', request()->request->get('start_date')),
                    'link'       => cleanInput(request()->request->get('link')),
                ];

                $webinarModel->insertOne($insert);

                jsonResponse('Webinar inserted successfully!', 'success');
            break;
            case 'edit':
                $id = request()->request->getInt('id');

                if(empty($id)){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
                $validator = new WebinarsValidator($adapter);

                if (!$validator->validate(request()->request->all())) {
                    \jsonResponse(
                        \array_map(
                            fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                /** @var Webinar_Model $webinarModel */
                $webinarModel = model(Webinar_Model::class);

                $webinar = $webinarModel->findOne($id);
                if(empty($webinar)){
                    jsonResponse('No such webinar found!');
                }

                $update = [
                    'title'      => cleanInput(request()->request->get('title')),
                    'start_date' => DateTimeImmutable::createFromFormat('m/d/Y H:i', request()->request->get('start_date')),
                    'link'       => cleanInput(request()->request->get('link')),
                ];

                $webinarModel->updateOne($id, $update);

                jsonResponse('Webinar updated successfully!', 'success');
            break;
        }
    }
}

// End of file webinars.php
// Location: /tinymvc/myapp/controllers/webinars.php
