<?php

use App\Filesystem\EpEventSpeakersFilePathGenerator;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Filesystem\FilePathGenerator;
use App\Validators\SpeakerValidator;
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
 *
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Ep_events_speakers_Controller extends TinyMVC_Controller
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
        headerRedirect(__SITE_URL . 'ep_events_speakers/administration');
    }

    public function administration(): void
    {
        checkPermision('ep_events_administration');

        /** @var Ep_Events_Speakers_Model $eventSpeakersModel */
        $eventSpeakersModel = model(Ep_Events_Speakers_Model::class);

        views(
            [
                'admin/header_view',
                'admin/ep_events_speakers/index_view',
                'admin/footer_view',
            ],
            [
                'title'         => 'EP events - speakers',
                'eventSpeakers' => $eventSpeakersModel->findAll(),
            ],
        );
    }

    public function ajax_dt_administration(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('ep_events_administration');

        /**
         * @var Ep_Events_Speakers_Model $speakersModel
         */
        $speakersModel = model(Ep_Events_Speakers_Model::class);

        $perPage = (int) $_POST['iDisplayLength'];
        $skip = (int) $_POST['iDisplayStart'];
        $page = $skip / $perPage + 1;

        $speakersTable = $speakersModel->getTable();
        $order = array_column(dt_ordering($_POST, [
            'dt_speaker_id' => "`{$speakersTable}`.`id`",
        ]), 'direction', 'column');

        $conditions = dtConditions($_POST, [
            ['as' => 'speakersIds', 'key' => 'speakers', 'type' => fn ($speakersIds) => empty($speakersIds) ? null : array_unique(array_map('intval', explode(',', $speakersIds)))],
        ]);

        $queryParams = compact('conditions', 'order');
        $speakers = $speakersModel->paginate($queryParams, $perPage, $page);

        $output = [
            'iTotalDisplayRecords' => empty($speakers['data']) ? 0 : $speakersModel->countBy($queryParams),
            'iTotalRecords'        => $speakers['total'] ?? 0,
            'aaData'               => [],
            'sEcho'                => request()->request->getInt('sEcho', 0),
        ];

        if (empty($speakers['data'])) {
            jsonResponse('', 'success', $output);
        }

        foreach ($speakers['data'] as $speaker) {
            $linkForEditSpeaker = __SITE_URL . 'ep_events_speakers/popup_forms/edit_speaker/' . $speaker['id'];

            $imageLink = $this->storage->url(
                EpEventSpeakersFilePathGenerator::mainImagePath((string) $speaker['id'], $speaker['photo'] ?? 'null.jpeg')
            );

            $output['aaData'][] = [
                'dt_speaker_id'      => $speaker['id'],
                'dt_speaker_photo'   => '<img class="mw-50 mh-50" src="' . $imageLink . '" alt="" />',
                'dt_speaker_name'    => cleanOutput($speaker['name']),
                'dt_speaker_actions' => <<<ACTIONS
                                        <div class="dropdown">
                                            <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                                <li>
                                                    <a class="fancyboxValidateModalDT fancybox.ajax" href="{$linkForEditSpeaker}" title="Edit speaker" data-title="Edit speaker">
                                                        <span class="ep-icon ep-icon_pencil"></span> Edit speaker
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
            case 'add_speaker':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showAddSpeakerPopup();

                break;
            case 'edit_speaker':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showEditSpeakerPopup((int) uri()->segment(4));

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
            case 'add_speaker':
                checkPermisionAjax('ep_events_administration');

                $this->addSpeaker();

                break;
            case 'edit_speaker':
                checkPermisionAjax('ep_events_administration');

                $this->editSpeaker((int) uri()->segment(4));

                break;
            case 'upload_photo':
                checkPermisionAjax('ep_events_administration');

                $this->uploadPhoto();

                break;
            case 'delete_temp_photo':
                checkPermisionAjax('ep_events_administration');

                $this->removeTempPhoto();

                break;

            default:
                jsonResponse('The provided path is not found on the server');
        }
    }

    /**
     * Show the popup form that allows to add speaker.
     */
    private function showAddSpeakerPopup(): void
    {
        views(
            ['admin/ep_events_speakers/speaker_form_view'],
            [
                'photoRules'    => config('img.ep_events_speakers.main.rules', []),
                'submitFormUrl' => __SITE_URL . 'ep_events_speakers/ajax_operations/add_speaker',
                'uploadFolder'  => encriptedFolderName(),
            ]
        );
    }

    private function showEditSpeakerPopup(int $speakerId): void
    {
        /** @var Ep_Events_Speakers_Model $eventSpeakersModel */
        $eventSpeakersModel = model(Ep_Events_Speakers_Model::class);
        if (empty($speaker = $eventSpeakersModel->findOneBy([
            'conditions' => ['id' => $speakerId],
        ]))) {
            messageInModal('Speaker not found.');
        }

        $speaker['photo'] = $this->storage->url(
            EpEventSpeakersFilePathGenerator::mainImagePath($speaker['id'], $speaker['photo'] ?? 'null.jpeg')
        );

        views(
            ['admin/ep_events_speakers/speaker_form_view'],
            [
                'photoRules'    => config('img.ep_events_speakers.main.rules', []),
                'submitFormUrl' => __SITE_URL . 'ep_events_speakers/ajax_operations/edit_speaker/' . $speakerId,
                'uploadFolder'  => encriptedFolderName(),
                'speaker'       => $speaker,
            ]
        );
    }

    /**
     * Action for processing form to adding the speaker.
     */
    private function addSpeaker(): void
    {
        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new SpeakerValidator($adapter);
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

        $image = $request->get('speaker_photo');
        $newPhotoName = pathinfo($image, PATHINFO_BASENAME);

        // region insert speaker in DB
        /** @var Ep_Events_Speakers_Model $speakersModel */
        $speakersModel = model(Ep_Events_Speakers_Model::class);
        if (empty($speakerId = $speakersModel->insertOne([
            'name'     => $request->get('name'),
            'photo'    => $newPhotoName,
            'position' => $request->get('position'),
        ]))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }
        // endregion insert speaker in DB

        // region upload photo
        $newImageToUpload = [];
        $photoFolderPath = EpEventSpeakersFilePathGenerator::mainFolderPath((string) $speakerId);
        if (!$this->storage->fileExists($photoFolderPath)) {
            $this->storage->createDirectory($photoFolderPath);
        }

        try {
            $file = $this->tempStorage->read($image);

        } catch (UnableToReadFile $error) {
            $this->rollBackSpeaker($speakerId);

            jsonResponse(translate('events_speakers_pictures_error_upload_message'));
        }

        try {
            $photoPath = EpEventSpeakersFilePathGenerator::mainImagePath((string) $speakerId, $newPhotoName);

            $this->storage->write($photoPath, $file);
        } catch (UnableToWriteFile $error) {
            $this->rollBackSpeaker($speakerId);

            jsonResponse(translate('events_speakers_pictures_error_upload_message'));
        }

        $newImageToUpload[] = $photoPath;

        $thumbs = config('img.ep_events_speakers.main.thumbs');
        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                try {
                    $file = $this->tempStorage->read($image);

                } catch (UnableToReadFile $error) {
                    $this->rollBackSpeaker($speakerId, $newImageToUpload, 'tempStorage');

                    jsonResponse(translate('events_speaker_thumbs_upload_error_message'));
                }

                try {
                    $thumbNewName = str_replace('{THUMB_NAME}', $newPhotoName, $thumb['name']);
                    $thumbNewPath = EpEventSpeakersFilePathGenerator::mainImagePath((string) $speakerId, $thumbNewName);

                    $this->storage->write($thumbNewPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackSpeaker($speakerId, $newImageToUpload, 'tempStorage');

                    jsonResponse(translate('events_speaker_thumbs_upload_error_message'));
                }

                $newImageToUpload[] = $thumbNewPath;
            }
        }

        jsonResponse(translate('events_speaker_created_message'), 'success');
    }

    /**
     * Action for processing form to editing the speaker.
     *
     * @var int
     */
    private function editSpeaker(int $speakerId): void
    {
        /** @var Ep_Events_Speakers_Model $speakersModel */
        $speakersModel = model(Ep_Events_Speakers_Model::class);
        /** @var Elasticsearch_Ep_Events_Model $elasticsearchEpEventsModel */
        $elasticsearchEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);
        if (empty($speaker = $speakersModel->findOneBy([
            'conditions'    => ['id' => $speakerId],
        ]))) {
            jsonResponse(translate('events_speaker_not_found_message'));
        }

        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new SpeakerValidator($adapter, false);
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
        $image = $request->get('speaker_photo');
        if (!empty($image)) {
            $newPhotoName = pathinfo($image, PATHINFO_BASENAME);

            $photoFolderPath = EpEventSpeakersFilePathGenerator::mainFolderPath((string) $speakerId);
            if (!$this->storage->fileExists($photoFolderPath)) {
                $this->storage->createDirectory($photoFolderPath);
            }

            try {
                $file = $this->tempStorage->read($image);

            } catch (UnableToReadFile $error) {
                $this->rollBackSpeaker(null, $newImageToUpload, 'tempStorage');

                jsonResponse(translate('events_speakers_pictures_error_upload_message'));
            }

            try {
                $photoFullPath = EpEventSpeakersFilePathGenerator::mainImagePath((string) $speakerId, $newPhotoName);

                $this->storage->write($photoFullPath, $file);
            } catch (UnableToWriteFile $error) {
                $this->rollBackSpeaker(null, $newImageToUpload, 'tempStorage');

                jsonResponse(translate('events_speakers_pictures_error_upload_message'));
            }

            $thumbs = config('img.ep_events_speakers.main.thumbs');
            if (!empty($thumbs)) {
                foreach ($thumbs as $thumb) {
                    $thumbTempName = str_replace('{THUMB_NAME}', $newPhotoName, $thumb['name']);
                    $thumbTempPath = str_replace($newPhotoName, $thumbTempName, $image);

                    try {
                        $file = $this->tempStorage->read($thumbTempPath);

                    } catch (UnableToReadFile $error) {
                        $this->rollBackSpeaker(null, $newImageToUpload, 'tempStorage');

                        jsonResponse(translate('events_speaker_thumbs_upload_error_message'));
                    }

                    try {
                        $thumbNewPath = EpEventSpeakersFilePathGenerator::mainImagePath((string) $speakerId, $thumbTempName);

                        $this->storage->write($thumbNewPath, $file);
                    } catch (UnableToWriteFile $error) {
                        $this->rollBackSpeaker(null, $newImageToUpload, 'tempStorage');

                        jsonResponse(translate('events_speaker_thumbs_upload_error_message'));
                    }

                    $newImageToUpload[] = $thumbNewPath;
                }
            }
        }

        $speakerUpdates = [
            'name'     => $request->get('name'),
            'photo'    => $newPhotoName ?? $speaker['photo'],
            'position' => $request->get('position'),
        ];

        if (
            !$speakersModel->updateOne(
                $speakerId,
                $speakerUpdates,
            )
        ) {
            $this->rollBackSpeaker(null, $newImageToUpload, 'storage');

            jsonResponse(translate('events_speaker_error_update_message'));
        }

        if (isset($newPhotoName)) {
            try {
                $this->storage->delete(
                    EpEventSpeakersFilePathGenerator::mainImagePath((string) $speakerId, $speaker['photo'])
                );
            } catch (UnableToDeleteFile $error) {
                jsonResponse(translate('events_speakers_pictures_error_delete_message'));
            }

            $thumbs = config("img.ep_events_speakers.main.thumbs") ?? [];
            array_map(function ($item) use ($speakerId, $speaker) {
                try {
                    $this->storage->delete(
                        EpEventSpeakersFilePathGenerator::mainImagePath((string) $speakerId, str_replace('{THUMB_NAME}', $speaker['photo'], $item['name']))
                    );
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_speakers_pictures_error_delete_message'));
                }
            }, $thumbs);
        }

        $elasticsearchEpEventsModel->updateEventsSpeaker($speakerId, $speakerUpdates);

        jsonResponse(translate('events_speaker_updated_message'), 'success');
    }

    /**
     * Action for uploading speaker photo.
     *
     * @var null|string
     */
    private function uploadPhoto(): void
    {
        /** @var null|UploadedFile */
        $uploadedFile = request()->files->get('photo') ?? null;

        if (null === $uploadedFile) {
            jsonResponse(translate('events_speaker_pictures_select_file_message'));
		}

        if (!$uploadedFile->isValid()) {
            jsonResponse(translate('events_speaker_pictures_invalid_file_message'));
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
                'rules'                 => config('img.ep_events_speakers.main.rules'),
                'handlers'              => [
                    'create_thumbs' => config('img.ep_events_speakers.main.thumbs'),
                    'resize'        => config('img.ep_events_speakers.main.resize'),
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
     * Action for removing speaker temp photo.
     *
     * @var null|string
     */
    private function removeTempPhoto(): void
    {
        $imageName = request()->request->get('file');
        if (empty($imageName)) {
            jsonResponse(translate('events_speaker_pictures_not_found_message'));
        }

        $filePath = FilePathGenerator::uploadedFile($imageName);
        if (!$this->tempStorage->fileExists($filePath)) {
            jsonResponse(translate('events_speaker_pictures_not_found_message'));
        }

        try {
            $this->tempStorage->delete($filePath);
        } catch (UnableToDeleteFile $error) {
            jsonResponse(translate('events_speakers_pictures_error_delete_message'));
        }

        $thumbs = config("img.ep_events_speakers.main.thumbs") ?? [];
        array_map(function ($item) use ($imageName) {
           try {
                $this->tempStorage->delete(
                    FilePathGenerator::uploadedFile(str_replace('{THUMB_NAME}', $imageName, $item['name']))
                );
            } catch (UnableToDeleteFile $error) {
                jsonResponse(translate('events_speakers_pictures_error_delete_message'));
            }
        }, $thumbs);

        jsonResponse('', 'success');
    }

    private function rollBackSpeaker(?int $speakerId, ?array $files = null, ?string $storage = 'storage'): void
    {
        if (null !== $speakerId) {
            /** @var Ep_Events_Speakers_Model $speakersModel */
            $speakersModel = model(Ep_Events_Speakers_Model::class);
            $speakersModel->deleteOne($speakerId);
        }

        if (null !== $files) {
            foreach ($files as $file) {
                try {
                    $this->{$storage}->delete($file);
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_speakers_pictures_error_delete_message'));
                }
            }
        }
    }
}