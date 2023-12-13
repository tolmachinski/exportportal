<?php

use App\Common\Contracts\Email\EmailTemplate;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Mime\BodyRendererInterface;

class Emails_Template_Controller extends TinyMVC_Controller
{
    private $emailElements = [
        'paragraphSimple'   => [
            'displayName'   => 'Paragraph',
            'name'          => 'paragraphSimple',
            'used'          => 'many',
            'params'        => [
                'paragraphSimpleText' => [
                    'name' => 'paragraphSimpleText',
                    'type' => 'textarea',
                ],
            ],
        ],
        'paragraphLinks'   => [
            'displayName'   => 'Paragraph with Links',
            'name'          => 'paragraphLinks',
            'used'          => 'many',
            'params'        => [
                'paragraphLinksText' => [
                    'name' => 'paragraphLinksText',
                    'type' => 'textarea',
                ],
                'paragraphLinksTextLinks' => [
                    'name' => 'paragraphLinksTextLinks',
                    'type' => 'textarea',
                ],
                'paragraphLinksTextLinksValues' => [
                    'name' => 'paragraphLinksTextLinksValues',
                    'type' => 'textarea',
                ],
            ],
        ],
        'titleSimple'   => [
            'displayName'   => 'Title',
            'name'          => 'titleSimple',
            'used'          => 'many',
            'params'        => [
                'titleSimpleText' => [
                    'name' => 'titleSimpleText',
                    'type' => 'text',
                ],
            ],
        ],
        'mainBtn'           => [
            'displayName'   => 'Main Button',
            'name'          => 'mainBtn',
            'used'          => 'once',
            'params'        => [
                'mainBtnText' => [
                    'name' => 'mainBtnText',
                    'type' => 'text',
                ],
            ],
        ],
        'secondaryBtn'           => [
            'displayName'   => 'Secondary Button',
            'name'          => 'secondaryBtn',
            'used'          => 'many',
            'params'        => [
                'secondaryBtnLink' => [
                    'name' => 'secondaryBtnLink',
                    'type' => 'text',
                ],
                'secondaryBtnText' => [
                    'name' => 'secondaryBtnText',
                    'type' => 'text',
                ],
            ],
        ],
        'listBlock'   => [
            'displayName'   => 'List',
            'name'          => 'listBlock',
            'used'          => 'many',
            'params'        => [
                'listBlockText' => [
                    'name' => 'listBlockText',
                    'type' => 'textarea',
                ],
            ],
        ],
        'messageSimple'   => [
            'displayName'   => 'User message',
            'name'          => 'messageSimple',
            'used'          => 'once',
            'params'        => [],
        ],
        'tableInfo'      => [
            'displayName'   => 'Table with Information',
            'name'          => 'tableInfo',
            'used'          => 'once',
            'params'        => [
                'tableInfoImage' => [
                    'name' => 'tableInfoImage',
                    'type' => 'radio',
                ],
                'tableInfoTitle' => [
                    'name' => 'tableInfoTitle',
                    'type' => 'radio',
                ],
                'tableInfoDescription' => [
                    'name' => 'tableInfoDescription',
                    'type' => 'radio',
                ],
            ],
        ],
        'customBlock'    => [
            'displayName'   => 'Custom Block',
            'name'          => 'customBlock',
            'used'          => 'many',
            'params'        => [
                'customBlockText' => [
                    'name' => 'customBlockText',
                    'type' => 'textarea',
                ]
            ],
        ],
        'helloBlock'    => [
            'displayName'   => 'Hello',
            'name'          => 'helloBlock',
            'used'          => 'once',
            'params'        => [
                'userName' => [
                    'name' => 'userName',
                    'type' => 'radio',
                ]
            ],
        ],
        'supportBlock'  => [
            'displayName'   => 'Support',
            'name'          => 'supportBlock',
            'used'          => 'once',
            'params'        => [],
        ],
    ];

    public function index()
    {
        headerRedirect(__SITE_URL . 'emails_template/administration');
    }

    public function administration()
    {
        checkAdmin('manage_emails_template');

        /** @var Emails_Template_Structure_Model $emailTemplatesStructure */
        $emailTemplatesStructure = model(Emails_Template_Structure_Model::class);

        views(['admin/header_view', 'admin/emails_template/index_view', 'admin/footer_view'], [
            'emailsTemplateStructure' => $emailTemplatesStructure->getEmailsTemplatesStructure(),
            'title'                   => 'The email templates',
        ]);
    }

    public function ajax_dt_emails_template()
    {
        checkAdminAjaxDT('manage_emails_template');

        /** @var Emails_Template_Model $emailTemplates */
        $emailTemplates = model(Emails_Template_Model::class);

        $request = request();
        $parameters = $request->request;

        $conditions = array_merge(
            [
                'perP'    => $parameters->getInt('iDisplayLength'),
                'start'   => $parameters->getInt('iDisplayStart'),
                'orderBy' => flat_dt_ordering($parameters->all(), [
                    'dt_id_template'        => 'e.id_emails_template',
                    'dt_template_name'      => 'e.name',
                    'dt_alias'              => 'e.alias_template',
                    'dt_subject'            => 'e.subject',
                    'dt_header'             => 'e.header',
                    'dt_proofread'          => 'e.proofread',
                    'dt_template_structure' => 'structure_name',
                    'dt_created'            => 'e.emails_template_date',
                    'dt_updated'            => 'e.emails_template_update',
                ]),
            ],
            dtConditions($parameters->all(), [
                ['as' => 'keywords',        'key' => 'keywords',        'type' => 'string'],
                ['as' => 'email_structure', 'key' => 'email_structure', 'type' => 'int'],
                ['as' => 'proofread',       'key' => 'proofread',       'type' => 'int'],
            ])
        );

        $emailsTemplate = $emailTemplates->getEmailsTemplates($conditions);
        $emailsTemplateCount = (int) $emailTemplates->getCountEmailsTemplates($conditions);

        $output = [
            'sEcho'                 => $parameters->getInt('sEcho'),
            'iTotalRecords'         => $emailsTemplateCount,
            'iTotalDisplayRecords'  => $emailsTemplateCount,
            'aaData'                => [],
        ];

        if (empty($emailsTemplate)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($emailsTemplate as $emailsTemplateItem) {
            $editEmailsTemplate = '';
            if (have_right('edit_emails_template')) {
                $editEmailsTemplateUrl = __SITE_URL . 'emails_template/popup_forms/edit_template/' . $emailsTemplateItem['id_emails_template'];
                $editEmailsTemplate = <<<BUTTON
                <a
                    class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil"
                    href="{$editEmailsTemplateUrl}"
                    data-table="dtEmailsTemplate"
                    data-title="Edit {$emailsTemplateItem['name']}"
                    title="Edit {$emailsTemplateItem['name']}"
                ></a>
                BUTTON;
            }

            $logEmailsTemplateUrl = __SITE_URL . 'emails_template/popup_forms/view_log/' . $emailsTemplateItem['id_emails_template'];
            $logEmailsTemplate = <<<BUTTON
                <a
                    class="fancybox fancybox.ajax ep-icon ep-icon_text"
                    href="{$logEmailsTemplateUrl}"
                    data-title="View log {$emailsTemplateItem['name']}"
                    title="View log {$emailsTemplateItem['name']}"
                ></a>
                BUTTON;

            $viewEmailsTemplateUrl = __SITE_URL . 'emails_template/popup_forms/view_html/' . $emailsTemplateItem['id_emails_template'];
            $viewEmailsTemplate = <<<BUTTON
            <a
                class="fancybox-ttl-inside fancybox.iframe ep-icon ep-icon_visible"
                href="{$viewEmailsTemplateUrl}"
                data-title="{$emailsTemplateItem['name']}"
                title="View {$emailsTemplateItem['name']}"
            ></a>
            BUTTON;

            $output['aaData'][] = [
                'dt_id_template'            => $emailsTemplateItem['id_emails_template'],
                'dt_template_name'          => $emailsTemplateItem['name'],
                'dt_template_structure'     => $emailsTemplateItem['structure_name'],
                'dt_alias'                  => $emailsTemplateItem['alias_template'],
                'dt_subject'                => !empty($emailsTemplateItem['subject']) ? $emailsTemplateItem['subject'] : "---",
                'dt_header'                 => !empty($emailsTemplateItem['header']) ?  $emailsTemplateItem['header'] : "---",
                'dt_triggered_information'  => (empty($emailsTemplateItem['triggered_information'])) ? '-' : "<div class=\"ep-tinymce-text\">" . $emailsTemplateItem['triggered_information'] . "</div>",
                'dt_proofread'              => ($emailsTemplateItem['proofread']) ? 'Yes' : 'No',
                'dt_created'                => getDateFormat($emailsTemplateItem['emails_template_date']),
                'dt_updated'                => getDateFormat($emailsTemplateItem['emails_template_update']),
                'dt_actions'                => $viewEmailsTemplate
                    . $editEmailsTemplate
                    . $logEmailsTemplate,
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        checkAdminAjaxModal('manage_emails_template');

        $formAction = cleanInput($this->uri->segment(3));
        $idRecord = (int) $this->uri->segment(4);

        switch ($formAction) {
            case 'view_html':
                /** @var Emails_Template_Model $emailTemplates */
                $emailTemplates = model(Emails_Template_Model::class);

                $record = $emailTemplates->getEmailTemplate($idRecord);
                $html = 'Please check email template to more information';

                /** @var BodyRendererInterface $bodyRenderer */
                $bodyRenderer = $this->getContainer()->get(BodyRendererInterface::class);
                $templateCase = EmailTemplate::from($record['alias_template']);
                $className = $templateCase->className();
                $email = new $className(...$templateCase->templateData());
                $email->templateReplacements([]);
                $bodyRenderer->render($email);
                $html = $email->getHtmlBody();

                views()->display('admin/emails_template/preview_email_template_view', ['content' => $html]);

                break;

            case 'add_template':
                if (!isAjaxRequest()) {
                    headerRedirect();
                }

                checkAdminAjaxModal('edit_emails_template');

                /** @var Emails_Template_Structure_Model $emailTemplatesStructure */
                $emailTemplatesStructure = model(Emails_Template_Structure_Model::class);

                views()->assign([
                    'emailElements'             => $this->emailElements,
                    'emailsTemplateStructure'   => $emailTemplatesStructure->getEmailsTemplatesStructure(),
                ]);
                views()->display('admin/emails_template/form_view');

                break;

            case 'edit_template':
                if (!isAjaxRequest()) {
                    headerRedirect();
                }

                checkAdminAjaxModal('edit_emails_template');

                /** @var Emails_Template_Model $emailTemplates */
                $emailTemplates = model(Emails_Template_Model::class);

                /** @var Emails_Template_Structure_Model $emailTemplatesStructure */
                $emailTemplatesStructure = model(Emails_Template_Structure_Model::class);

                views()->assign([
                    'emailElements'             => $this->emailElements,
                    'template'                  => $emailTemplates->getEmailTemplate($idRecord),
                    'emailsTemplateStructure'   => $emailTemplatesStructure->getEmailsTemplatesStructure(),
                ]);
                views()->display('admin/emails_template/form_view');

                break;

            case 'view_log':
                if (!isAjaxRequest()) {
                    headerRedirect();
                }

                /** @var Emails_Template_Model $emailTemplates */
                $emailTemplates = model(Emails_Template_Model::class);

                $template = $emailTemplates->getEmailTemplate($idRecord);
                $data['logs'] = [];

                if (!empty($template['log'])) {
                    /** @var User_Model $userModel */
                    $userModel = model(User_Model::class);

                    $logs = json_decode($template['log'], true);
                    $usersId = array_column($logs, 'user_id');

                    $data['usersInfo'] = arrayByKey($userModel->getSimpleUsers(implode(',', $usersId), 'users.idu, CONCAT(users.fname, " ", users.lname) AS user_name'), 'idu');
                    $data['logs'] = $logs;
                }

                views()->assign($data);
                views()->display('admin/emails_template/show_log_view');

                break;
        }
    }

    public function ajax_operation()
    {
        checkIsAjax();

        $op = cleanInput($this->uri->segment(3));
        if (empty($op)) {
            jsonResponse('Error: you cannot perform this action. Please try again later.');
        }

        $request = request();
        $parameters = $request->request;

        switch ($op) {
            case 'update_template':
                checkAdminAjax('edit_emails_template');

                $idEmailsTemplate = $parameters->getInt('id_emails_template');
                $isInsert = $idEmailsTemplate === 0;
                $isJsonStructure = false;

                $validatorRules = [
                    [
                        'field' => 'id_emails_template',
                        'label' => 'Email template',
                        'rules' => ['natural' => ''],
                    ],
                    [
                        'field' => 'template_structure',
                        'label' => 'Template structure',
                        'rules' => ['required' => '', 'min[1]' => '', 'natural' => ''],
                    ],
                    [
                        'field' => 'name',
                        'label' => 'Name',
                        'rules' => ['required' => '', 'min_len[2]' => '', 'max_len[150]' => ''],
                    ],
                ];

                if (!empty($parameters->get('subject'))) {
                    $validatorRules[] = [
                        'field' => 'subject',
                        'label' => 'Subject',
                        'rules' => ['min_len[2]' => '', 'max_len[150]' => ''],
                    ];
                }

                if (!empty($parameters->get('header'))) {
                    $validatorRules[] = [
                        'field' => 'header',
                        'label' => 'Header',
                        'rules' => ['min_len[2]' => '', 'max_len[150]' => ''],
                    ];
                }

                if ($isInsert) {
                    $validatorRules[] = [
                        'field' => 'alias_template',
                        'label' => 'Alias',
                        'rules' => ['required' => '', 'min_len[2]' => '', 'max_len[150]' => ''],
                    ];
                }

                /** @var Emails_Template_Structure_Model $emailTemplatesStructure */
                $emailTemplatesStructure = model(Emails_Template_Structure_Model::class);

                $idEmailsTemplateStructure = $parameters->getInt('template_structure');
                if (!$emailTemplatesStructure->issetEmailTemplate($idEmailsTemplateStructure)) {
                    jsonResponse('Error: The structure email template does not exist.');
                }

                $structureDetail = $emailTemplatesStructure->getEmailTemplate($idEmailsTemplateStructure);
                $isJsonStructure = (int)$structureDetail['json_structure'] === 1;

                if (!$isJsonStructure) {
                    $validatorRules[] = [
                        'field' => 'content',
                        'label' => 'Content',
                        'rules' => ['required' => ''],
                    ];
                }

                $this->validator->set_rules($validatorRules);

                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Emails_Template_Model $emailTemplates */
                $emailTemplates = model(Emails_Template_Model::class);

                if (!$isInsert && !$emailTemplates->issetEmailTemplate($idEmailsTemplate)) {
                    jsonResponse('Error: The email template does not exist.');
                }

                $dataMain = [
                    'id_emails_template_structure'      => $parameters->getInt('template_structure'),
                    'name'                              => cleanInput($parameters->get('name')),
                    'subject'                           => cleanInput($parameters->get('subject')),
                    'header'                            => cleanInput($parameters->get('header')),
                    'triggered_information'             => $parameters->get('triggered_information'),
                    'preview_template_data'             => null,
                ];

                if ($isInsert) {
                    $dataMain['alias_template'] = $parameters->get('alias_template');
                }

                if ($isJsonStructure) {
                    $contentJson = $parameters->get('content_template_data');
                    if (!empty($contentJson)) {
                        $contentJsonDB = [];

                        foreach ($contentJson as $contentJsonItem) {
                            $contentJsonDB[] = $contentJsonItem;
                        }

                        $dataMain['content_json'] = json_encode($contentJsonDB);
                    }
                } else {
                    $dataMain['content'] = $parameters->get('content');
                }

                if (have_right('manage_proofread')) {
                    $dataMain['proofread'] = $parameters->getInt('proofread');
                }

                $defaultDataInsert = [];
                if (!empty($parameters->get('preview_template_data'))) {
                    foreach ($parameters->get('preview_template_data') as $defaultDataItem) {
                        $defaultDataInsert[cleanInput($defaultDataItem['name'])] = cleanInput($defaultDataItem['value']);
                    }

                    $dataMain['preview_template_data'] = json_encode($defaultDataInsert, JSON_FORCE_OBJECT);
                }

                if ($isInsert) {
                    if ($emailTemplates->insertEmailTemplate($dataMain)) {
                        jsonResponse('Email template inserted successfully.', 'success');
                    }
                } else {
                    if (!empty($log = $this->createLogData($idEmailsTemplate, $parameters, $dataMain, $defaultDataInsert))) {
                        $dataMain['log'] = $log;
                    }

                    if ($emailTemplates->updateEmailTemplate($idEmailsTemplate, $dataMain)) {
                        jsonResponse('Email template updated successfully.', 'success');
                    }
                }

                jsonResponse('Error: you cannot save the email template now. Please try again later');

                break;
        }
    }

    public function export_emails()
    {
        /** @var Emails_Template_Model $emailTemplates */
        $emailTemplates = model(Emails_Template_Model::class);

        $data = $emailTemplates->getEmailsTemplates();
        $now = date('Y-m-d-H_i');
        $this->returnReport($data, "emails_{$now}.xlsx");
    }

    /**
     * Get report
     *
     * @param array $data - log data
     * @param string $fileName - name of the file with extension
     *
     */
    private function returnReport($data, $fileName = 'emails.xlsx')
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('Emails');

        $headerColumns = [
            'A' => ['name' => 'Alias',      'width' =>  40],
            'B' => ['name' => 'Name',       'width' =>  30],
            'C' => ['name' => 'Content',    'width' => 130],
            'D' => ['name' => 'Triggered',  'width' =>  40],
            'E' => ['name' => 'Updated on', 'width' =>  30],
            'F' => ['name' => 'Proofread',  'width' =>  20]
        ];

		//region generate headings
		$rowIndex = 1;

        foreach($headerColumns as $letter => $heading)
        {
            $activeSheet->getColumnDimension($letter)->setWidth($heading['width']);
            $activeSheet->getStyle($letter . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle($letter . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $activeSheet->setCellValue($letter . $rowIndex, $heading['name'])
                        ->getStyle($letter . $rowIndex)
                            ->getFont()
                                ->setSize(14)
                                    ->setBold(true);
        }

        //endregion generate headings

        //region introduce data
        $rowIndex = 2;
        $excel->getDefaultStyle()->getAlignment()->setWrapText(true);
        foreach($data as $one)
        {
            $activeSheet
                ->setCellValue("A$rowIndex", $one['alias_template'])
                ->setCellValue("B$rowIndex", $one['name'])
                ->setCellValue("C$rowIndex", preg_replace("/\s{2,}/", "\n", strip_tags($one['content'])))
                ->setCellValue("D$rowIndex", $one['triggered_information'])
                ->setCellValue("E$rowIndex", getDateFormat($one['emails_template_update']))
                ->setCellValue("F$rowIndex", $one['proofread'] ? 'Yes' : 'No');

            $rowIndex++;
        }
        //endregion introduce data

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

		$objWriter = IOFactory::createWriter($excel, 'Xlsx');
        $objWriter->save('php://output');
    }

    private function createLogData($idEmail, $parameters, $update, $previewInsert)
    {
        $logUpdate = [
            'user_id'   => id_session(),
            'log_date'  => date('Y-m-d H:i:s'),
            'updated'   => [],
        ];

        /** @var Emails_Template_Model $emailTemplates */
        $emailTemplates = model(Emails_Template_Model::class);

        $emailInfo = $emailTemplates->getEmailTemplate($idEmail);

        //region preview_template_data
        if (!empty($parameters->get('preview_template_data'))) {
            $previewInfo = [];
            if (!empty($emailInfo['preview_template_data'])) {
                $previewInfo = json_decode($emailInfo['preview_template_data'], true);
            }

            $previewDiff = array_diff_assoc($previewInsert, $previewInfo);

            if (!empty($previewInfo)) {
                foreach ($previewInfo as $previewInfoKey => $previewInfoValue) {
                    if (isset($previewDiff[$previewInfoKey])) {
                        $logUpdate['updated']['preview_template_data'][] = '- ' . $previewInfoKey . ' => ' . $previewInfoValue . PHP_EOL . '+ ' . $previewInfoKey . ' => ' . $previewDiff[$previewInfoKey];
                        unset($previewDiff[$previewInfoKey]);
                    } elseif (!isset($previewInsert[$previewInfoKey])) {
                        $logUpdate['updated']['preview_template_data'][] = '- ' . $previewInfoKey . ' => ' . $previewInfoValue . PHP_EOL . '+';
                    }
                }
            }

            if (count($previewDiff) > 0) {
                foreach ($previewDiff as $previewDiffKey => $previewDiffValue) {
                    $logUpdate['updated']['preview_template_data'][] = '- ' . PHP_EOL . '+' . $previewDiffKey . ' => ' . $previewDiffValue;
                }
            }
        } elseif (!empty($emailInfo['preview_template_data'])) {
            $previewInfoTemplate = json_decode($emailInfo['preview_template_data'], true);
            foreach ($previewInfoTemplate as $previewInfoTemplateKey => $previewInfoTemplateValue) {
                $logUpdate['updated']['preview_template_data'][] = '- ' . $previewInfoTemplateKey . ' => ' . $previewInfoTemplateValue . PHP_EOL . '+';
            }
        }
        //endregion preview_template_data

        //region content
        $contentForm = $parameters->get('content');
        if(!empty($contentForm)) {
            $builderDiff = new DiffOnlyOutputBuilder('');
            $differInit = new Differ($builderDiff);
            $differContent = $differInit->diff($emailInfo['content'], $contentForm);

            if (!empty($differContent)) {
                $logUpdate['updated']['content'] = $differContent;
            }
        }
        //endregion content

        //region updated data
        $emailInfo['content_json'] = str_replace([': ',', '], [':',','], (string)$emailInfo['content_json']);
        $updateDiff = array_diff_assoc(array_intersect_key($update, $emailInfo), $emailInfo);

        if (!empty($updateDiff)) {
            unset($updateDiff['preview_template_data'], $updateDiff['content']);

            if (!empty($updateDiff)) {
                foreach ($updateDiff as $updateDiffKey => $updateDiffValue) {
                    $logUpdate['updated'][$updateDiffKey] = '- ' . $emailInfo[$updateDiffKey] . PHP_EOL . '+ ' . $updateDiffValue;
                }
            }
        }
        //endregion updated data

        //region prepared log
        $existingLog = [];

        if (!empty($logUpdate['updated'])) {
            if (!empty($emailInfo['log'])) {
                $existingLog = json_decode($emailInfo['log'], true);
            }

            array_unshift($existingLog, $logUpdate);

            return json_encode($existingLog, JSON_FORCE_OBJECT);
        }
        //endregion prepared log

        return [];
    }
}
