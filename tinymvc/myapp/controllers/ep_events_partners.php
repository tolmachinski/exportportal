<?php

use App\Filesystem\EpEventPartnersFilePathGenerator;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Filesystem\FilePathGenerator;
use App\Validators\PartnerValidator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Ep_events_partners_Controller extends TinyMVC_Controller
{
    private FilesystemOperator $storage;

    private FilesystemOperator $tempStorage;

    private PathPrefixer $tempPrefixer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
        $this->tempStorage = $storageProvider->storage('temp.storage');
        $this->tempPrefixer = $storageProvider->prefixer('temp.storage');
    }

    /**
     * Index page.
     */
    public function index(): void
    {
        headerRedirect(__SITE_URL . 'ep_events_partners/administration');
    }

    public function administration(): void
    {
        checkPermision('ep_events_administration');

        /** @var Ep_Events_Partners_Model $eventPartnersModel */
        $eventPartnersModel = model(Ep_Events_Partners_Model::class);

        views(
            [
                'admin/header_view',
                'admin/ep_events_partners/index_view',
                'admin/footer_view',
            ],
            [
                'title'         => 'EP events - partners',
                'eventPartners' => $eventPartnersModel->findAll(),
            ]
        );
    }

    public function ajax_dt_administration(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('ep_events_administration');

        /**
         * @var Ep_Events_Partners_Model $partnersModel
         */
        $partnersModel = model(Ep_Events_Partners_Model::class);

        $perPage = (int) $_POST['iDisplayLength'];
        $skip = (int) $_POST['iDisplayStart'];
        $page = $skip / $perPage + 1;

        $partnersTable = $partnersModel->getTable();
        $order = array_column(dt_ordering($_POST, [
            'dt_partner_id' => "`{$partnersTable}`.`id`",
        ]), 'direction', 'column');

        $conditions = dtConditions($_POST, [
            ['as' => 'partnersIds', 'key' => 'partners', 'type' => fn ($partnersIds) => empty($partnersIds) ? null : array_unique(array_map('intval', explode(',', $partnersIds)))],
        ]);

        $queryParams = compact('conditions', 'order');
        $partners = $partnersModel->paginate($queryParams, $perPage, $page);

        $output = [
            'iTotalDisplayRecords' => empty($partners['data']) ? 0 : $partnersModel->countBy($queryParams),
            'iTotalRecords'        => $partners['total'] ?: 0,
            'aaData'               => [],
            'sEcho'                => request()->request->getInt('sEcho', 0),
        ];

        if (empty($partners['data'])) {
            jsonResponse('', 'success', $output);
        }

        foreach ($partners['data'] as $partner) {
            $linkForEditpartner = __SITE_URL . 'ep_events_partners/popup_forms/edit_partner/' . $partner['id'];

            $imageLink = $this->storage->url(
                EpEventPartnersFilePathGenerator::mainImagePath((string) $partner['id'], $partner['image'])
            );

            $output['aaData'][] = [
                'dt_partner_id'      => $partner['id'],
                'dt_partner_image'   => '<img class="mw-50 mh-50" src="' . $imageLink . '" alt="">',
                'dt_partner_name'    => cleanOutput($partner['name']),
                'dt_partner_actions' => <<<ACTIONS
                                        <div class="dropdown">
                                            <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                                <li>
                                                    <a class="fancyboxValidateModalDT fancybox.ajax" href="{$linkForEditpartner}" title="Edit partner" data-title="Edit partner">
                                                        <span class="ep-icon ep-icon_pencil"></span> Edit partner
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        ACTIONS,
            ];
        }

        jsonResponse('', 'success', $output);
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms(): void
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_partner':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showAddPartnerPopup();

                break;
            case 'edit_partner':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showEditPartnerPopup((int) uri()->segment(4));

                break;

            default:
                messageInModal('The provided path is not found on the server');

                break;
        }
    }

    /**
     * Handles ajax operations.
     */
    public function ajax_operations(): void
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_partner':
                checkPermisionAjax('ep_events_administration');

                $this->addPartner();

                break;
            case 'edit_partner':
                checkPermisionAjax('ep_events_administration');

                $this->editpartner((int) uri()->segment(4));

                break;
            case 'upload_image':
                checkPermisionAjax('ep_events_administration');

                $this->uploadimage();

                break;
            case 'delete_temp_image':
                checkPermisionAjax('ep_events_administration');

                $this->removeTempimage();

                break;

            default:
                jsonResponse('The provided path is not found on the server');
        }
    }

    /**
     * Show the popup form that allows to add partner.
     */
    private function showAddPartnerPopup(): void
    {
        views(
            ['admin/ep_events_partners/partner_form_view'],
            [
                'imageRules'    => config('img.ep_events_partners.main.rules', []),
                'submitFormUrl' => __SITE_URL . 'ep_events_partners/ajax_operations/add_partner',
                'uploadFolder'  => encriptedFolderName(),
            ]
        );
    }

    /**
     * Show the popup form that allows to edit partner.
     */
    private function showEditPartnerPopup(int $partnerId): void
    {
        /** @var Ep_Events_partners_Model $partnersModel */
        $partnersModel = model(Ep_Events_Partners_Model::class);
        if (empty($partner = $partnersModel->findOneBy([
            'conditions' => ['id' => $partnerId],
        ]))) {
            messageInModal('Partner not found.');
        }

        $partner['image'] = $this->storage->url(
            EpEventPartnersFilePathGenerator::mainImagePath($partner['id'], $partner['image'])
        );

        views(
            ['admin/ep_events_partners/partner_form_view'],
            [
                'imageRules'    => config('img.ep_events_partners.main.rules', []),
                'submitFormUrl' => __SITE_URL . 'ep_events_partners/ajax_operations/edit_partner/' . $partnerId,
                'uploadFolder'  => encriptedFolderName(),
                'partner'       => $partner,
            ]
        );
    }

    /**
     * Action for processing form to adding the partner.
     */
    private function addPartner(): void
    {
        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));

        $validator = new PartnerValidator($adapter);
        if (!$validator->validate($request->all())) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        // endregion Validation

        $image = $request->get('partner_image');
        $newImageName = pathinfo($image, PATHINFO_BASENAME);
        $userName = $request->get('name');

        /** @var Ep_Events_Partners_Model $partnersModel */
        $partnersModel = model(Ep_Events_Partners_Model::class);

        if (empty($partnerId = $partnersModel->insertOne([
            'name'  => $userName,
            'image' => $newImageName,
        ]))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        $imageFolderPath = EpEventPartnersFilePathGenerator::mainFolderPath((string) $partnerId);
        if (!$this->storage->fileExists($imageFolderPath)) {
            $this->storage->createDirectory($imageFolderPath);
        }
        
        try {
            $file = $this->tempStorage->read($image);
        } catch (UnableToReadFile $error) {
            $this->rollBackPartner($partnerId);

            jsonResponse(translate('events_partner_pictures_error_upload_message'));
        }
        
        try {
            $imageFullPath = EpEventPartnersFilePathGenerator::mainImagePath((string) $partnerId, $newImageName);

            $this->storage->write($imageFullPath, $file);
        } catch (UnableToWriteFile $error) {
            $this->rollBackPartner($partnerId);
    
            jsonResponse(translate('events_partner_pictures_error_upload_message'));

        }

        jsonResponse(translate('events_partner_created_message'), 'success');
    }

    /**
     * Action for processing form to editing the partner.
     *
     * @var int
     */
    private function editpartner(int $partnerId): void
    {
        /** @var Ep_Events_Partners_Model $partnersModel */
        $partnersModel = model(Ep_Events_partners_Model::class);

        /** @var Elasticsearch_Ep_Events_Model $elasticsearchEpEventsModel */
        $elasticsearchEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);

        if (empty($partner = $partnersModel->findOneBy([
            'conditions'    => ['id' => $partnerId],
        ]))) {
            jsonResponse(translate('events_partner_not_found_message'));
        }

        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new PartnerValidator($adapter, false);
        if (!$validator->validate($request->all())) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        // endregion Validation

        $newImageToUpload = [];

        $image = $request->get('partner_image');
        if (!empty($image)) {
            $newImageName = pathinfo($image, PATHINFO_BASENAME);
            
            $imageFolderPath = EpEventPartnersFilePathGenerator::mainFolderPath((string) $partnerId);
            if (!$this->storage->fileExists($imageFolderPath)) {
                $this->storage->createDirectory($imageFolderPath);
            }
            
            try {
                $file = $this->tempStorage->read($image);
            } catch (UnableToReadFile $error) {
                $this->rollBackPartner(null, $newImageToUpload, 'tempStorage');

                jsonResponse(translate('events_partner_pictures_error_upload_message'));
            }
            
            try {
                $imageFullPath = EpEventPartnersFilePathGenerator::mainImagePath((string) $partnerId, $newImageName);

                $this->storage->write($imageFullPath, $file);
            } catch (UnableToWriteFile $error) {
                $this->rollBackPartner(null, $newImageToUpload, 'tempStorage');

                jsonResponse(translate('events_partner_pictures_error_upload_message'));
            }
        }

        $partnerUpdates = [
            'name'  => $request->get('name'),
            'image' => $newImageName ?: $partner['image'],
        ];

        if (
            !$partnersModel->updateOne(
                $partnerId,
                $partnerUpdates,
            )
        ) {
            $this->rollBackPartner(null, $newImageToUpload, 'tempStorage');

            jsonResponse(translate('events_partner_error_update_message'));
        }

        if (isset($newimageName)) {
            try {
                $this->storage->delete(
                    EpEventPartnersFilePathGenerator::mainImagePath((string) $partnerId, $partner['image'])
                );

                $this->tempStorage->delete($image);
            } catch (UnableToDeleteFile $error) {
                jsonResponse(translate('events_partners_pictures_error_delete_message'));
            }
        }

        $elasticsearchEpEventsModel->updateEventsPartner($partnerId, $partnerUpdates);

        jsonResponse(translate('events_partner_updated_message'), 'success');
    }

    /**
     * Action for uploading partner image.
     *
     * @var null|string
     */
    private function uploadimage(): void
    {
        /** @var null|UploadedFile */
        $uploadedFile = request()->files->get('image') ?: null;

        if (null === $uploadedFile) {
            jsonResponse(translate('events_partner_pictures_select_file_message'));
		}

        if (!$uploadedFile->isValid()) {
            jsonResponse(translate('events_partner_pictures_invalid_file_message'));
		}

        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $pathToFile = FilePathGenerator::uploadedFile($imageName);

        $pathToDirrectory = dirname($pathToFile);
        if (!$this->tempStorage->fileExists($pathToDirrectory)) {
            $this->tempStorage->createDirectory($pathToDirrectory);
        }

        $result = library(TinyMVC_Library_Image_intervention::class)->image_processing(
            [
                'tmp_name' => $uploadedFile->getRealPath(),
                'name' => pathinfo($imageName, PATHINFO_FILENAME)
            ],
            [
                'destination'           => $this->tempPrefixer->prefixDirectoryPath($pathToDirrectory),
                'rules'                 => config('img.ep_events_partners.main.rules'),
                'handlers'              => [
                    'resize' => config('img.ep_events_speakers.main.resize'),
                ],
                'use_original_name'     => true,
            ]
        );

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        $response = ['files' => []];
        foreach ($result as $resultByFile) {
            $response['files'][] = [
                'url'   => $this->tempStorage->url($pathToFile),
                'path'  => $pathToFile,
                'name'  => $resultByFile['new_name'],
            ];
        }

        jsonResponse('', 'success', $response);
    }

    /**
     * Action for removing partner temp image.
     *
     * @var null|string
     */
    private function removeTempimage(): void
    {
        $filePath = FilePathGenerator::uploadedFile($_POST['file']);
        if (empty($filePath)) {
            jsonResponse(translate('events_partner_pictures_not_found_message'));
        }

        if (!$this->tempStorage->fileExists($filePath)) {
            jsonResponse(translate('events_partner_pictures_not_found_message'));
        }

        try {
            $this->tempStorage->delete($filePath);
        }  catch (UnableToDeleteFile $error) {
            jsonResponse(translate('events_partners_pictures_error_delete_message'));
        }

        jsonResponse('', 'success');
    }

    private function rollBackPartner(?int $partnerId, ?array $files = null, ?string $storage = 'storage'): void
    {
        if (null !== $partnerId) {
            /** @var Ep_Events_Partners_Model $partnersModel */
            $partnersModel = model(Ep_Events_partners_Model::class);
            $partnersModel->deleteOne($partnerId);
        }

        if (null !== $files) {
            foreach ($files as $file) {
                try {
                    $this->{$storage}->delete($file);
                }  catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_partners_pictures_error_delete_message'));
                }
            }
        }
    }
}

// End of file ep_events_partners.php
// Location: /tinymvc/myapp/controllers/ep_events_partners.php
