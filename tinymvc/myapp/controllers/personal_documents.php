<?php

use App\Common\Contracts\Cancel\CancellationRequestStatus;
use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Contracts\Upgrade\UpgradeRequestStatus;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Traits\DocumentsApiAwareTrait;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Common\Traits\ModalUriReferenceTrait;
use App\Common\Traits\VersionMetadataTrait;
use App\Common\Traits\VersionStatusesMetadataAwareTrait;
use App\Documents\File\File;
use App\Documents\File\FileAwareInterface;
use App\Documents\File\FileCopy;
use App\Documents\File\FileInterface;
use App\Documents\Serializer\VersionSerializerStatic;
use App\Documents\User\Manager;
use App\Documents\Versioning\AbstractVersion;
use App\Documents\Versioning\AcceptedVersion;
use App\Documents\Versioning\AcceptedVersionInterface;
use App\Documents\Versioning\ContentContext;
use App\Documents\Versioning\ContentContextEntries;
use App\Documents\Versioning\ExpiringVersionInterface;
use App\Documents\Versioning\PendingVersion;
use App\Documents\Versioning\PendingVersionInterface;
use App\Documents\Versioning\RejectedVersion;
use App\Documents\Versioning\RejectedVersionInterface;
use App\Documents\Versioning\VersionCollectionInterface;
use App\Documents\Versioning\VersionInterface;
use App\Documents\Versioning\VersionList;
use App\Plugins\EPDocs\Rest\Objects\File as FileObjects;
use App\Plugins\EPDocs\Rest\Resources\AccessToken as AccessTokenResource;
use App\Plugins\EPDocs\Rest\Resources\File as FileResource;
use App\Plugins\EPDocs\Rest\Resources\FilePermissions as FilePermissionsResource;
use App\Plugins\EPDocs\Rest\Resources\TemporaryFile as TemporaryFileResource;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Query\QueryBuilder;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use const App\Logger\Activity\OperationTypes\ADMIN_CONFIRM_DOCUMENT;
use const App\Logger\Activity\OperationTypes\ADMIN_UPLOAD_DOCUMENT;
use const App\Logger\Activity\OperationTypes\DECLINE_DOCUMENT;
use const App\Logger\Activity\OperationTypes\UPLOAD_DOCUMENT;
use const App\Logger\Activity\ResourceTypes\PERSONAL_DOCUMENT;

/**
 * Controller Personal_documents.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 */
class Personal_documents_Controller extends TinyMVC_Controller
{
    use VersionMetadataTrait;
    use ModalUriReferenceTrait;
    use DocumentsApiAwareTrait;
    use VersionStatusesMetadataAwareTrait;
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    /**
     * Show user dashboard for order documents.
     */
    public function index()
    {
        checkIsLogged();
        checkDomainForGroup();
        checkPermision('manage_personal_documents');

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->documentsEpl();
        } else {
            $this->documentsAll();
        }
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkDomainForGroup();
        checkPermisionAjaxModal('manage_personal_documents,manage_user_documents');

        $action = uri()->segment(3);
        switch ($action) {
            case 'upload':
                if (have_right('manage_user_documents')) {
                    $this->show_upload_popup((int) uri()->segment(5), (int) uri()->segment(4), true);
                } else {
                    $this->show_upload_popup((int) privileged_user_id(), (int) uri()->segment(4));
                }

                break;
            case 'replace':
                if (have_right('manage_user_documents')) {
                    $this->show_replace_popup((int) uri()->segment(5), (int) uri()->segment(4), true);
                } else {
                    $this->show_replace_popup((int) privileged_user_id(), (int) uri()->segment(4));
                }

                break;
            case 'versions':
                if (have_right('manage_user_documents')) {
                    $this->show_document_versions_popup((int) uri()->segment(5), (int) uri()->segment(4), true);
                } else {
                    $this->show_document_versions_popup((int) privileged_user_id(), (int) uri()->segment(4));
                }

                break;

            default:
                messageInModal(translate('sysmtess_provided_path_not_found'));

                break;
        }
    }

    /**
     * Executes actions on personal documents by AJAX.
     */
    public function ajax_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkDomainForGroup();
        checkPermisionAjax('manage_personal_documents,manage_user_documents');

        $action = uri()->segment(3);
        switch ($action) {
            case 'versions':
                if (have_right('manage_user_documents')) {
                    $this->show_document_versions((int) uri()->segment(5), (int) uri()->segment(4), true);
                } else {
                    $this->show_document_versions((int) privileged_user_id(), (int) uri()->segment(4));
                }

                break;
            case 'documents':
                checkIsLoggedAjaxDT();

                $this->show_documents_list();

                break;
            case 'upload_version':
                if (have_right('manage_user_documents')) {
                    $this->upload_document_version((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'), true);
                } else {
                    $this->upload_document_version((int) privileged_user_id(), (int) arrayGet($_POST, 'document'));
                }

                break;
            case 'replace_version':
                if (have_right('manage_user_documents')) {
                    $this->replace_document_version((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'), true);
                } else {
                    $this->replace_document_version((int) privileged_user_id(), (int) arrayGet($_POST, 'document'));
                }

                break;
            case 'accept_version':
                checkPermisionAjax('manage_user_documents');

                $this->accept_document_version((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'));

                break;
            case 'reject_version':
                checkPermisionAjax('manage_user_documents');

                $this->reject_document_version((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'), (int) arrayGet($_POST, 'reason'));

                break;
            case 'copy_version':
                checkPermisionAjax('manage_personal_documents');

                $this->copy_latest_document_version(
                    principal_id(),
                    (int) privileged_user_id(),
                    request()->request->getInt('source') ?: null,
                    request()->request->getInt('target') ?: null
                );

                break;
            case 'change_expiration_date':
                checkPermision('manage_user_documents');

                $this->change_version_expiration_date((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'));

                break;
            case 'remove_expiration_date':
                checkPermision('manage_user_documents');

                $this->remove_version_expiration_date((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'));

                break;
            case 'download_document':
                if (have_right('manage_user_documents')) {
                    $this->download_document((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'), arrayGet($_POST, 'version', null), true);
                } else {
                    $this->download_document((int) privileged_user_id(), (int) arrayGet($_POST, 'document'), arrayGet($_POST, 'version', null));
                }

                break;
            case 'remove_document':
                checkPermisionAjax('manage_user_documents');

                $this->remove_document((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'));

                break;
            case 'add_documents':
                checkPermisionAjax('manage_user_documents');

                $this->add_documents((int) arrayGet($_POST, 'user'), (array) arrayGet($_POST, 'documents', []));

                break;

            default:
                jsonResponse(translate('sysmtess_provided_path_not_found'));

                break;
        }
    }

    private function documentsEpl()
    {
        $data['templateViews'] = [
            'mainOutContent'    => 'personal_documents/my/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function documentsAll()
    {
        views(['new/header_view', 'new/personal_documents/my/index_view', 'new/footer_view']);
    }

    /**
     * Updates the documents context.
     *
     * @deprecated Remove it later
     *
     * @param mixed $user_id
     * @param mixed $document_id
     * @param mixed $is_administration
     */
    // public function update_documents_context(): void
    // {
    //     checkIsLogged();
    //     checkPermision('moderate_content');

    //     set_time_limit(0);
    //     ini_set('memory_limit', '-1');

    //     /** @var Accreditation_Model $documentTypes */
    //     $documentTypes = model(Accreditation_Model::class);
    //     /** @var User_Personal_Documents_Model $personalDocuments */
    //     $personalDocuments = model(User_Personal_Documents_Model::class);

    //     $type_id = (int) uri()->segment(3) ?? null ?: null;
    //     if (null === $type_id) {
    //         echo '<h2 style="color: red">The document type is required.</h2>';
    //         exit;
    //     }
    //     $type = with($documentTypes->get_document($type_id), function (array $type) {
    //         if (isset($type['document_base_context'])) {
    //             $type['document_base_context'] = json_decode($type['document_base_context'], true, 512, JSON_THROW_ON_ERROR);
    //         }

    //         return $type;
    //     });
    //     if (empty($type)) {
    //         echo '<h2 style="color: red">The document type with such ID is not found.</h2>';
    //         exit;
    //     }
    //     if (empty($type['document_base_context'])) {
    //         echo '<h2>Nothing to update - the document type content is empty.</h2>';
    //         exit;
    //     }

    //     $document_context = new ContentContext($type['document_base_context'] ?? []);
    //     $format_errors = function (Collection $collection, ?string $title, string $reason) {
    //         if ($collection->count() > 0) {
    //             echo null !== $title ? "<h2>{$title}</h2><br>" : '';
    //         }

    //         foreach ($collection as $document) {
    //             echo "Document: <strong>{$document['id_document']}</strong> - {$reason}";
    //         }
    //     };
    //     $write_failures = new ArrayCollection();
    //     $serialization_failures = new ArrayCollection();
    //     $deserialization_failures = new ArrayCollection();
    //     $documents = (new ArrayCollection($personalDocuments->get_documents(['conditions' => ['type' => 2]])))
    //         ->filter(function (array $document) { return !empty($document['versions']); })
    //         ->map(function (array $document) use ($deserialization_failures) {
    //             $document['versions'] = VersionSerializerStatic::deserialize($document['versions'], VersionList::class, 'json');
    //             if (null === $document['versions']) {
    //                 $deserialization_failures->add($document);
    //             }

    //             return $document;
    //         })
    //     ;

    //     list($documents_with_context, $documents_without_context) = $documents
    //         ->filter(function (array $document) use ($deserialization_failures) { return !$deserialization_failures->contains($document); })
    //         ->partition(function ($key, array $document) { return $document['versions']->last()->hasContext(); })
    //     ;

    //     if ($documents_without_context->isEmpty() && $documents_with_context->isEmpty()) {
    //         echo '<h2>Nothing to update - all documents have context.</h2>';
    //         $format_errors($deserialization_failures, 'Errors', 'Deserialization error');
    //         $format_errors($serialization_failures, null, 'Serialization error');
    //         exit;
    //     }

    //     $total = $documents_without_context->count() + $documents_with_context->count();
    //     $updated = 0;
    //     foreach ($documents_with_context as $document) {
    //         if ($deserialization_failures->contains($document)) {
    //             continue;
    //         }

    //         /** @var VersionList $versions */
    //         $versions = $document['versions'];
    //         /** @var AbstractVersion $latest_version */
    //         $latest_version = $versions->last();
    //         $stored_context = $latest_version->getContext();
    //         foreach ($document_context as $key => $value) {
    //             if (is_bool($value)) {
    //                 $stored_context->set($key, ($stored_context->get($key) ?? false) || $value);

    //                 continue;
    //             }

    //             if (is_array($value)) {
    //                 $is_assoc = array_reduce(array_values($value), function (bool $carry, $key) { return $carry && !is_numeric($key); }, false);
    //                 if ($is_assoc) {
    //                     $stored_context->set(
    //                         $key,
    //                         array_merge(
    //                             $document_context->get($key) ?? [],
    //                             $stored_context->get($key) ?? []
    //                         )
    //                     );
    //                 } else {
    //                     $stored_context->set(
    //                         $key,
    //                         array_unique(
    //                             array_merge(
    //                                 $document_context->get($key) ?? [],
    //                                 $stored_context->get($key) ?? []
    //                             )
    //                         )
    //                     );
    //                 }

    //                 continue;
    //             }

    //             if (!$stored_context->has($key) || null === $stored_context->get($key)) {
    //                 $stored_context->set($key, $document_context->get($key) ?? null);
    //             }
    //         }

    //         $serialized = VersionSerializerStatic::serialize($versions, 'json');
    //         if (null === $serialized) {
    //             $serialization_failures->add($document);
    //         }

    //         try {
    //             $personalDocuments->update_document($document['id_document'], ['versions' => $serialized]);
    //             ++$updated;
    //         } catch (Exception $exception) {
    //             $write_failures->add($document);
    //         }
    //     }

    //     foreach ($documents_without_context as $document) {
    //         if ($deserialization_failures->contains($document)) {
    //             continue;
    //         }

    //         /** @var VersionList $versions */
    //         $versions = $document['versions'];
    //         $versions->replace(
    //             $versions->last(),
    //             $versions->last()->withContext($document_context)
    //         );

    //         $serialized = VersionSerializerStatic::serialize($versions, 'json');
    //         if (null === $serialized) {
    //             $serialization_failures->add($document);
    //         }

    //         try {
    //             $personalDocuments->update_document($document['id_document'], ['versions' => $serialized]);
    //             ++$updated;
    //         } catch (Exception $exception) {
    //             $write_failures->add($document);
    //         }
    //     }

    //     echo "<h2>Updated {$updated} documents from {$total}</h2>";
    //     $format_errors($deserialization_failures, 'Errors', 'Deserialization error');
    //     $format_errors($serialization_failures, null, 'Serialization error');
    //     $format_errors($write_failures, null, 'Write error');
    // }

    /**
     * Shows popup where user can upload personla documents.
     *
     * @param int  $user_id
     * @param int  $document_id
     * @param bool $is_administration
     */
    private function show_upload_popup($user_id, $document_id, $is_administration = false)
    {
        //region User access
        if (
            empty($user_id)
            || (
                !$is_administration && logged_in() && (int) $user_id !== (int) privileged_user_id()
            )
        ) {
            messageInModal(translate('systmess_error_permission_not_granted'));
        }
        //endregion User access

        //region Document
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'with' => ['type'],
                'conditions' => [
                    'user' => (int) $user_id,
                ],
            ]))
        ) {
            messageInModal(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region latest version
        $latest_version = VersionSerializerStatic::deserialize(arrayGet($document, 'latest_version'), VersionInterface::class, 'json');
        $version_metadata = $this->getVersionMetadata($latest_version);

        if (!$version_metadata['is_uploadable'] && !$document['type']['document_is_multiple']) {
            messageInModal(
                $is_administration
                    ? translate('systmess_verification_file_download_error_message')
                    : translate('systmess_verification_file_upload_error_message')
            );
        }
        //endregion latest version

        //region View vars
        views()->assign([
            'action'       => getUrlForGroup('personal_documents/ajax_operation/upload_version'),
            'reference'    => ['from' => $this->getParsedUriReference()],
            'document'     => $document,
            'multiple'     => (int) $document['type']['document_is_multiple'],
            'another'      => true,
            'fileupload'   => $this->getFormattedFileuploadOptions(
                explode(',', config('fileupload_personal_document_formats', 'pdf,jpg,jpeg,png')),
                1,
                1,
                (int) config('fileupload_max_document_file_size', 2 * 1000 * 1000),
                config('fileupload_max_document_file_size_placeh', '2MB')
            ),
        ]);
        //endregion View vars

        if (!$is_administration) {
            views('new/personal_documents/my/upload_document_view');
        } else {
            views('admin/personal_documents/upload_document_view');
        }
    }

    /**
     * Shows popup where user can replace pending personal documents.
     *
     * @param int  $user_id
     * @param int  $document_id
     * @param bool $is_administration
     */
    private function show_replace_popup($user_id, $document_id, $is_administration = false)
    {
        //region User access
        if (
            empty($user_id)
            || (
                !$is_administration && logged_in() && (int) $user_id !== (int) privileged_user_id()
            )
        ) {
            messageInModal(translate('systmess_error_permission_not_granted'));
        }
        //endregion User access

        //region Document
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'with' => ['type'],
                'conditions' => [
                    'user' => (int) $user_id,
                ],
            ]))
        ) {
            messageInModal(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Versions
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionList::class, 'json');
        if (null === $versions) {
            messageInModal(
                $is_administration
                    ? translate('systmess_verification_replace_document_admin_error_message')
                    : translate('systmess_verification_replace_document_user_error_message')
            );
        }
        //endregion Versions

        //region latest version
        /** @var PendingVersion $latest_version */
        $latest_version = $versions->last();
        $version_metadata = $this->getVersionMetadata($latest_version);
        if (!$version_metadata['is_reuploadable']) {
            messageInModal(
                $is_administration
                    ? translate('systmess_verification_file_replacement_admin_error_message')
                    : translate('systmess_verification_replace_document_user_error_message')
            );
        }
        //endregion latest version

        //region View vars
        views()->assign([
            'action'       => getUrlForGroup('personal_documents/ajax_operation/replace_version'),
            'document'     => $document,
            'comment'      => $latest_version->getComment(),
            'multiple'     => (int) $document['type']['document_is_multiple'],
            'is_reupload'  => true,
            'reference'    => ['from' => $this->getParsedUriReference()],
            'fileupload'   => $this->getFormattedFileuploadOptions(
                explode(',', config('fileupload_personal_document_formats', 'pdf,jpg,jpeg,png')),
                1,
                1,
                (int) config('fileupload_max_document_file_size', 2 * 1000 * 1000),
                config('fileupload_max_document_file_size_placeh', '2MB')
            ),
        ]);
        //endregion View vars

        if (!$is_administration) {
            views('new/personal_documents/my/upload_document_view');
        } else {
            views('admin/personal_documents/upload_document_view');
        }
    }

    /**
     * Shows the popup with the document versions.
     *
     * @param int  $user_id
     * @param int  $document_id
     * @param bool $is_administration
     */
    private function show_document_versions_popup($user_id, $document_id, $is_administration = false)
    {
        //region User access
        if (
            empty($user_id)
            || (
                !$is_administration && logged_in() && (int) $user_id !== (int) privileged_user_id()
            )
        ) {
            messageInModal(translate('systmess_error_permission_not_granted'));
        }
        //endregion User access

        //region Document
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'conditions' => [
                    'user' => (int) $user_id,
                ],
            ]))
        ) {
            messageInModal(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        views('new/personal_documents/my/versions_view', [
            'document'  => $document,
            'reference' => [
                'from' => $this->getParsedUriReference(),
                'to'   => $this->makeUriReferenceQuery(
                    'versions_popup',
                    "/personal_documents/popup_forms/versions/{$document_id}",
                    translate('general_button_versions_text', null, true),
                    [],
                    ['w' => '99%', 'mw' => '900'],
                    true,
                    translate('personal_documents_dashboard_dt_button_versions_modal_title', null, true)
                ),
            ],
        ]);
    }

    /**
     * Shows the DT compatible document list.
     */
    private function show_documents_list()
    {
        $skip = isset($_POST['iDisplayStart']) ? (int) cleanInput($_POST['iDisplayStart']) : null;
        $limit = isset($_POST['iDisplayLength']) ? (int) cleanInput($_POST['iDisplayLength']) : null;
        $with = ['type'];
        $order = [];
        $conditions = array_merge(
            ['user' => (int) privileged_user_id()],
            dtConditions($_POST, [
                ['as' => 'search',            'key' => 'keywords',      'type' => 'cleanInput|cut_str:200|decodeCleanInput'],
                ['as' => 'created_from_date', 'key' => 'created_from',  'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'created_to_date',   'key' => 'created_to',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'updated_from_date', 'key' => 'updated_from',  'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'updated_to_date',   'key' => 'updated_to',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ])
        );
        $order = array_column(dt_ordering($_POST, [
            'created_at'  => 'date_created',
            'updated_at'  => 'date_updated',
            'expires_at'  => 'date_latest_version_expires',
        ]), 'direction', 'column');

        /** @var \User_Personal_Documents_Model $model */
        $model = model('user_personal_documents');
        $total = $model->count_documents(compact('conditions'));
        $documents = $model->get_documents(compact('with', 'conditions', 'order', 'limit', 'skip'));

        $output = [
            'sEcho'                => (int) arrayGet($_POST, 'sEcho', 0),
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => [],
        ];

        if (!empty($documents)) {
            $output['aaData'] = $this->get_documents_list_payload(model(User_Model::class), $documents);
        }

        jsonResponse('', 'success', $output);
    }

    /**
     * Shows the DT-compatible versions list.
     *
     * @param int  $user_id
     * @param int  $document_id
     * @param bool $is_administration
     */
    private function show_document_versions($user_id, $document_id, $is_administration = false)
    {
        //region Document
        /** @var User_Model $usersRepository */
        $usersRepository = model(User_Model::class);
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => array_filter([
                    'user' => !have_right('manage_user_documents') ? (int) $user_id : null,
                ]),
            ]))
        ) {
            jsonDTResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Versions
        if (!empty($raw_versions = arrayGet($document, 'versions'))) {
            $versions = VersionSerializerStatic::deserialize($raw_versions, VersionList::class, 'json');
            if (null === $versions) {
                jsonDTResponse(translate('systmess_error_version_document_loaded_with_error'));
            }
        } else {
            $versions = new VersionList();
        }

        $list = iterator_to_array($versions);
        krsort($list);
        //endregion Versions

        //region Make output
        $total = count($list);
        $skip = (int) arrayGet($_POST, 'iDisplayStart', 0);
        $limit = (int) arrayGet($_POST, 'iDisplayLength', $total);
        $slice = -1 !== $limit ? array_slice($list, $skip, $limit, true) : $list;
        $output = [
            'sEcho'                => (int) $_POST['sEcho'],
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $total,
            'aaData'               => [],
        ];

        if (!empty($slice)) {
            if ($is_administration) {
                $output['aaData'] = $this->get_admin_document_versions_payload($document, $slice, $versions->first(), $versions->last());
            } else {
                $output['aaData'] = $this->get_document_versions_payload($usersRepository, $document, $slice, $versions->first(), $versions->last());
            }
        }
        //endregion Make output

        jsonResponse('', 'success', $output);
    }

    /**
     * Uploads new document version.
     *
     * @param int  $user_id
     * @param int  $document_id
     * @param bool $is_administration
     */
    private function upload_document_version($user_id, $document_id, $is_administration = false)
    {
        //region Repositories
        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        /** @var Verification_Document_Types_Model $verificationDocumentTypesModel */
        $verificationDocumentTypesModel = model(Verification_Document_Types_Model::class);
        /** @var Activity_Log_Messages_Model $activityMessagesRepository */
        $activityMessagesRepository = model(Activity_Log_Messages_Model::class);
        //endregion Repositories

        //region User access
        if (
            empty($user_id)
            || (
                !$is_administration && logged_in() && (int) $user_id !== (int) privileged_user_id()
            )
            || empty($user = $usersRepository->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        //endregion User access

        //region Validation
        $validator_rules = [
            [
                'field' => 'uploaded_document',
                'label' => 'Document',
                'rules' => [
                    'required' => translate('systmess_document_required_validation_message'),
                    function ($attribute, $uuid, $fail) {
                        try {
                            Uuid::fromString((string) base64_decode($uuid));
                        } catch (InvalidUuidStringException $exception) {
                            $fail(translate('systmess_document_not_uploaded_error'));
                        }
                    },
                ],
            ],
            [
                'field' => 'comment',
                'label' => 'Comment',
                'rules' => ['max_len[500]' => ''],
            ],
            [
                'field' => 'subtitle',
                'label' => 'Subtitle',
                'rules' => ['max_len[100]' => ''],
            ],
        ];
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Document
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region latest version
        $latest_version = VersionSerializerStatic::deserialize(arrayGet($document, 'latest_version'), VersionInterface::class, 'json');
        $version_metadata = $this->getVersionMetadata($latest_version);
        if (!$version_metadata['is_uploadable'] && !$document['type']['document_is_multiple']) {
            jsonResponse(
                $is_administration
                    ? translate('systmess_verification_file_download_error_message')
                    : translate('systmess_verification_file_upload_error_message')
            );
        }
        //endregion latest version

        //region multiple create another document
        $multiple = request()->get('multiple');
        $uploadedAnother = false;

        if (1 == (int) $multiple && $document['type']['document_is_multiple'] && !$version_metadata['is_uploadable']) {
            $uploadedAnother = true;
            $document_id = $personalDocuments->create_document([
                'id_type'      => (int) $document['id_type'],
                'id_user'      => (int) $user_id,
                'id_principal' => (int) $user['id_principal'] ?: null,
                'subtitle'     => request()->get('subtitle') ?? null,
            ]);

            $document = $personalDocuments->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]);
        }
        //endregion multiple create another document

        //region Document base context
        try {
            $document_context = new ContentContext(
                json_decode($document['type']['document_base_context'] ?? null, true, 512, JSON_THROW_ON_ERROR)
            );
        } catch (JsonException $exception) {
            $document_context = new ContentContext();
        }
        //endregion Document type

        //region Versions
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionList::class, 'json');
        if (null === $versions) {
            $versions = new VersionList();
        }
        //endregion Versions

        //region Misc vars
        $file_id = base64_decode(arrayGet($_POST, 'uploaded_document'));
        $comment = cleanInput($_POST['comment']);
        $clean_file = function (FileResource $files, FileObjects $file = null) {
            if (null !== $file) {
                try {
                    $files->deleteFile($file->getId()); //  Delete file from EP Docs
                } catch (\Exception $exception) {
                    // @todo log the exception
                }
            }
        };
        //endregion Misc vars

        //region API resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var TemporaryFileResource $temporary_files */
        $temporary_files = $client->getResource(TemporaryFileResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        /** @var FilePermissionsResource $file_permissions */
        $file_permissions = $client->getResource(FilePermissionsResource::class);
        //endregion API resources

        //region Temporary file
        try {
            $temporary_file = $temporary_files->getFile($file_id); // Get temp file
        } catch (\Exception $exception) {
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_failed_to_upload_temp_admin_error_message')
                    : translate('systmess_upload_document_failed_message')
            );
        }
        //endregion Temporary file

        //region Users UUID resolve
        try {
            $owner = $users->findUserIfNotCreate($this->getUserApiContext($user_id)); // Create or get user
            $manager = $this->getGenericUser($users); // Create or get manager
        } catch (\Exception $exception) {
            // @todo better exceptions handling
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_failed_to_upload_admin_error_message')
                    : translate('systmess_upload_document_failed_message')
            );
        }
        //endregion Users UUID resolve

        //region Interactions
        try {
            $file = $files->createFile($owner->getId(), $temporary_file->getId()); // Create file
            $file_permissions->createPermissions($file->getId(), $manager->getId(), FilePermissionsResource::PERMISSION_READ | FilePermissionsResource::PERMISSION_WRITE); // Create permissions for manager
        } catch (\Exception $exception) {
            // @todo log the exception
            $clean_file($files, $file);
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_failed_upload_file_creation_error_admin')
                    : translate('systmess_upload_document_failed_message')
            );
        }
        //endregion Interactions

        //region new Version
        $versions->add(
            new PendingVersion(
                'v' . ($versions->count() + 1),
                $comment,
                new Manager(null, null, $manager->getId()),
                new File(
                    $file->getId(),
                    $file->getName(),
                    $file->getExtension(),
                    $file->getSize(),
                    $file->getType(),
                    $file->getOriginalName()
                ),
                $document_context
            )
        );
        $serialized_versions = VersionSerializerStatic::serialize($versions, 'json');
        if (null === $serialized_versions) {
            // @todo log the exception
            $clean_file($files, $file);
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_failed_serialization_admin_error')
                    : translate('systmess_upload_document_failed_message')
            );
        }
        //endregion new Version

        //region Update
        if (
            !$personalDocuments->update_document($document_id, [
                'versions' => $serialized_versions,
                'subtitle' => request()->get('subtitle') ?? null,
            ])
        ) {
            // @todo log the exception
            $clean_file($files, $file);
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_failed_upload_database_error_admin')
                    : translate('systmess_upload_document_failed_message')
            );
        }
        //endregion Update

        //region Update Activity Log
        $accreditation_document = $verificationDocumentTypesModel->runWithoutAllCasts(fn () => $verificationDocumentTypesModel->findOne((int) $document['id_type']));
        $fullname = "{$user['fname']} {$user['lname']}";
        $operation = $is_administration ? ADMIN_UPLOAD_DOCUMENT : UPLOAD_DOCUMENT;
        $context = array_merge(
            [
                'document' => [
                    'id'    => $accreditation_document['id_document'],
                    'title' => $accreditation_document['document_title'],
                ],
                'target_user' => [
                    'id'      => $user_id,
                    'name'    => $fullname,
                    'profile' => [
                        'url' => getUserLink($fullname, $user_id, $user['gr_type']),
                    ],
                ],
            ],
            get_user_activity_context()
        );

        $this->activity_logger->setResourceType(PERSONAL_DOCUMENT);
        $this->activity_logger->setOperationType($operation);
        $this->activity_logger->setResource($document_id);
        $this->activity_logger->info($activityMessagesRepository->get_message(PERSONAL_DOCUMENT, $operation), $context);
        //endregion Update Activity Log

        //region Notify
        $this->notify_document_managers(
            $user,
            $document,
            'personal_document_upload',
            $user['is_verified'] ? "/users/administration/user/{$user['idu']}" : "/verification/users?user={$user['idu']}"
        );
        //endregion Notify

        //region Actualize
        $this->actualizeVerificationInformation($personalDocuments, $usersRepository, $user['idu']);
        //endregion Actualize

        jsonResponse(translate('system_message_personal_documents_upload_version_success_text'), 'success', [
            'isReUplodable' => $this->getVersionMetadata($versions->last())['is_reuploadable'],
            'isMultiple'    => $uploadedAnother,
            'texts'         => [
                'notReuplodable' => translate('system_message_personal_documents_re_upload_not_eligible_text'),
            ],
        ]);
    }

    /**
     * Replaces the pending version of the document.
     *
     * @param int  $user_id
     * @param int  $document_id
     * @param bool $is_administration
     */
    private function replace_document_version($user_id, $document_id, $is_administration = false)
    {
        //region Repositories
        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        /** @var Verification_Document_Types_Model $verificationDocumentTypesModel */
        $verificationDocumentTypesModel = model(Verification_Document_Types_Model::class);
        /** @var Activity_Log_Messages_Model $activityMessagesRepository */
        $activityMessagesRepository = model(Activity_Log_Messages_Model::class);
        //endregion Repositories

        //region User access
        if (
            empty($user_id)
            || (
                !$is_administration && logged_in() && (int) $user_id !== (int) privileged_user_id()
            )
            || empty($user = $usersRepository->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        //endregion User access

        //region Validation
        $validator_rules = [
            [
                'field' => 'uploaded_document',
                'label' => 'Document',
                'rules' => [
                    'required' => translate('systmess_document_required_validation_message'),
                    function ($attribute, $uuid, $fail) {
                        try {
                            Uuid::fromString((string) base64_decode($uuid));
                        } catch (InvalidUuidStringException $exception) {
                            $fail(translate('systmess_document_not_uploaded_error'));
                        }
                    },
                ],
            ],
            [
                'field' => 'comment',
                'label' => 'Comment',
                'rules' => ['max_len[500]' => ''],
            ],
            [
                'field' => 'subtitle',
                'label' => 'Subtitle',
                'rules' => ['max_len[100]' => ''],
            ],
        ];
        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Document
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Versions
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionList::class, 'json');
        if (null === $versions) {
            jsonResponse(
                $is_administration
                    ? translate('systmess_verification_replace_document_admin_error_message')
                    : translate('systmess_verification_replace_document_user_error_message')
            );
        }
        //endregion Versions

        //region latest version
        /** @var PendingVersion $latest_version */
        $latest_version = $versions->last();
        $version_metadata = $this->getVersionMetadata($latest_version);
        if (!$version_metadata['is_reuploadable']) {
            jsonResponse(
                $is_administration
                ? translate('systmess_verification_replace_document_admin_error_message')
                : translate('systmess_verification_replace_document_user_error_message')
            );
        }
        //endregion latest version

        //region Misc vars
        $file_id = base64_decode(arrayGet($_POST, 'uploaded_document'));
        $comment = cleanInput($_POST['comment']);
        $clean_file = function (FileResource $files, FileObjects $file = null) {
            if (null !== $file) {
                try {
                    $files->deleteFile($file->getId()); //  Delete file from EP Docs
                } catch (\Exception $exception) {
                    // @todo log the exception
                }
            }
        };
        //endregion Misc vars

        //region API resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var TemporaryFileResource $temporary_files */
        $temporary_files = $client->getResource(TemporaryFileResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        /** @var FilePermissionsResource $file_permissions */
        $file_permissions = $client->getResource(FilePermissionsResource::class);
        //endregion API resources

        //region Temporary file
        try {
            $temporary_file = $temporary_files->getFile($file_id); // Get temp file
        } catch (\Exception $exception) {
            jsonResponse(
                $is_administration
                    ? translate('systmess_reupload_document_failed_admin_error')
                    : translate('systmess_reupload_document_failed_error')
            );
        }
        //endregion Temporary file

        //region Users UUID resolve
        try {
            $owner = $users->findUserIfNotCreate($this->getUserApiContext($user_id)); // Create or get user
            $manager = $this->getGenericUser($users); // Create or get manager
        } catch (\Exception $exception) {
            // @todo better exceptions handling
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_failed_reupload_general_admin_error')
                    : translate('systmess_reupload_document_failed_error')
            );
        }
        //endregion Users UUID resolve

        //region Remove old file
        if ($latest_version->hasFile()) {
            try {
                $files->deleteFile($latest_version->getFile()->getId());
            } catch (\Exception $exception) {
                jsonResponse(
                    $is_administration
                        ? translate('systmess_reupload_document_failed_creation_error_admin')
                        : translate('systmess_reupload_document_failed_error')
                );
            }
        }
        //endregion Remove old file

        //region Add new file
        try {
            $file = $files->createFile($owner->getId(), $temporary_file->getId()); // Create file
            $file_permissions->createPermissions($file->getId(), $manager->getId(), FilePermissionsResource::PERMISSION_READ | FilePermissionsResource::PERMISSION_WRITE); // Create permissions for manager
        } catch (\Exception $exception) {
            // @todo log the exception
            $clean_file($files, $file);
            jsonResponse(
                $is_administration
                ? translate('systmess_reupload_document_failed_creation_error_admin')
                : translate('systmess_reupload_document_failed_error')
            );
        }
        //endregion Add new file

        //region new Version
        $versions->replace(
            $versions->last(),
            (new PendingVersion(
                $latest_version->getName(),
                $comment,
                $latest_version->getManager(),
                new File(
                    $file->getId(),
                    $file->getName(),
                    $file->getExtension(),
                    $file->getSize(),
                    $file->getType(),
                    $file->getOriginalName()
                ),
                $latest_version->getContext()
            ))->replacesVersion($latest_version)
        );
        $serialized_versions = VersionSerializerStatic::serialize($versions, 'json');
        if (null === $serialized_versions) {
            // @todo log the exception
            $clean_file($files, $file);
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_failed_reupload_serialization_error')
                    : translate('systmess_reupload_document_failed_error')
            );
        }
        //endregion new Version

        //region Update
        if (
            !$personalDocuments->update_document($document_id, [
                'versions' => $serialized_versions,
                'subtitle' => request()->get('subtitle') ?? null,
            ])
        ) {
            // @todo log the exception
            $clean_file($files, $file);
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_failed_reupload_database_admin_error')
                    : translate('systmess_reupload_document_failed_error')
            );
        }
        //endregion Update

        //region Update Activity Log
        $accreditation_document = $verificationDocumentTypesModel->runWithoutAllCasts(fn () => $verificationDocumentTypesModel->findOne((int) $document['id_type']));
        $fullname = "{$user['fname']} {$user['lname']}";
        $operation = $is_administration ? ADMIN_UPLOAD_DOCUMENT : UPLOAD_DOCUMENT;
        $context = array_merge(
            [
                'document' => [
                    'id'    => $accreditation_document['id_document'],
                    'title' => $accreditation_document['document_title'],
                ],
                'target_user' => [
                    'id'      => $user_id,
                    'name'    => $fullname,
                    'profile' => [
                        'url' => getUserLink($fullname, $user_id, $user['gr_type']),
                    ],
                ],
            ],
            get_user_activity_context()
        );

        $this->activity_logger->setResourceType(PERSONAL_DOCUMENT);
        $this->activity_logger->setOperationType($operation);
        $this->activity_logger->setResource($document_id);
        $this->activity_logger->info($activityMessagesRepository->get_message(PERSONAL_DOCUMENT, $operation), $context);
        //endregion Update Activity Log

        //region Notify
        $this->notify_document_managers(
            $user,
            $document,
            'personal_document_re_upload',
            $user['is_verified'] ? "/users/administration/user/{$user['idu']}" : "/verification/users?user={$user['idu']}"
        );

        $metadata = $this->getVersionMetadata($versions->last());
        if (!$metadata['is_reuploadable']) {
            $this->notify_document_managers(
                $user,
                $document,
                'personal_document_re_upload_limit_reached',
                $user['is_verified'] ? "/users/administration/user/{$user['idu']}" : "/verification/users?user={$user['idu']}"
            );
        }
        //endregion Notify

        jsonResponse(translate('system_message_personal_documents_upload_version_success_text'), 'success', [
            'isReUplodable' => $this->getVersionMetadata($versions->last())['is_reuploadable'],
            'texts'         => [
                'notReuplodable' => translate('system_message_personal_documents_re_upload_not_eligible_text'),
            ],
        ]);
    }

    /**
     * Accepts the document version.
     *
     * @param int $user_id
     * @param int $document_id
     */
    private function accept_document_version($user_id, $document_id)
    {
        //region Repositories
        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        /** @var Verification_Document_Types_Model $verificationDocumentTypesModel */
        $verificationDocumentTypesModel = model(Verification_Document_Types_Model::class);
        /** @var Activity_Log_Messages_Model $activityMessagesRepository */
        $activityMessagesRepository = model(Activity_Log_Messages_Model::class);
        //endregion Repositories

        //region User access
        if (
            empty($user_id)
            || empty($user = $usersRepository->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User access

        //region Document
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Versions
        /** @var VersionCollectionInterface $versions */
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionCollectionInterface::class, 'json');
        if (null === $versions) {
            jsonResponse(translate('systmess_document_version_not_confirmed_error'));
        }
        //endregion Versions

        //region Check version
        $this->check_reupload_period($versions->last(), translate('systmess_confirmation_disabled_message'));
        //endregion Check version

        //region New version
        //region Latest version
        /** @var PendingVersion $latest_version */
        $latest_version = $versions->last();
        if (!$latest_version instanceof PendingVersionInterface) {
            jsonResponse(translate('systmess_invalid_version_type_message'));
        }

        if (
            $latest_version->hasContext()
            && $latest_version->getContext()->get(ContentContextEntries::REQUIRES_DYNAMIC_FIELDS) ?? false
        ) {
            $required_fields = $latest_version->getContext()->get(ContentContextEntries::DYNAMIC_FIELDS_NAMES_LIST) ?? [];
            $stored_fields = $latest_version->getContext()->get(ContentContextEntries::DYNAMIC_FIELDS_STORED_VALUES) ?? [];
            $filled_fields = array_filter(
                array_intersect_key($stored_fields, array_flip($required_fields)),
                function ($value) { return null !== $value; }
            );

            if (!empty($required_fields) && (empty($stored_fields) || empty($filled_fields))) {
                jsonResponse(translate('systmess_document_cannot_be_accepted_message'), 'warning');
            }
        }
        //endregion Latest version

        try {
            $versions->replace(
                $latest_version,
                AcceptedVersion::createFromVersion($latest_version)->withManager(new Manager(
                    $manager_id = (int) privileged_user_id(),
                    user_name_session(),
                    $this->getCachedUser(
                        $this->getApiClient()->getResource(UserResource::class),
                        $manager_id
                    )->getId()
                ))
            );
        } catch (\Exception $exception) {
            jsonResponse(translate('systmess_version_failed_manager_error'), 'error', [
                'debug' => [
                    'message' => $exception->getMessage(),
                ],
            ]);
        }
        //endregion New version

        //region Serialize version
        $serialized_versions = 0 !== $versions->count() ? VersionSerializerStatic::serialize($versions, 'json') : null;
        if (
            null === $serialized_versions && 0 !== $versions->count()
        ) {
            // We failed to serialize it so there is no meaning to go to the next step.
            jsonResponse(translate('systmess_failed_version_serialization_error'));
        }
        //endregion Serialize version

        //region Update
        if (!$personalDocuments->update_document($document_id, ['versions' => $serialized_versions])) {
            jsonResponse(translate('systmess_version_failed_saving_error'));
        }
        //endregion Update

        //region Update Activity Log
        $accreditation_document = $verificationDocumentTypesModel->runWithoutAllCasts(fn () => $verificationDocumentTypesModel->findOne((int) $document['id_type']));
        $fullname = "{$user['fname']} {$user['lname']}";
        $context = array_merge(
            [
                'document' => [
                    'id'    => $accreditation_document['id_document'],
                    'title' => $accreditation_document['document_title'],
                ],
                'target_user' => [
                    'id'      => $user_id,
                    'name'    => $fullname,
                    'profile' => [
                        'url' => getUserLink($fullname, $user_id, $user['gr_type']),
                    ],
                ],
            ],
            get_user_activity_context()
        );

        $this->activity_logger->setResourceType(PERSONAL_DOCUMENT);
        $this->activity_logger->setOperationType(ADMIN_CONFIRM_DOCUMENT);
        $this->activity_logger->setResource($document_id);
        $this->activity_logger->info($activityMessagesRepository->get_message(PERSONAL_DOCUMENT, ADMIN_CONFIRM_DOCUMENT), $context);
        //endregion Update Activity Log

        jsonResponse(translate('systmess_success_document_has_been_confirmed'), 'success', [
            'version' => [
                'metadata' => $this->getVersionMetadata($versions->last()),
            ],
        ]);
    }

    /**
     * Rejects the document version.
     *
     * @param int $user_id
     * @param int $document_id
     * @param int $reason_id
     */
    private function reject_document_version($user_id, $document_id, $reason_id)
    {
        //region Repositories
        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        /** @var Verification_Document_Types_Model $verificationDocumentTypesModel */
        $verificationDocumentTypesModel = model(Verification_Document_Types_Model::class);
        /** @var Activity_Log_Messages_Model $activityMessagesRepository */
        $activityMessagesRepository = model(Activity_Log_Messages_Model::class);
        //endregion Repositories

        //region User access
        if (
            empty($user_id) || empty($user = $usersRepository->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User access

        //region Document
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Reason
        if (
            empty($reason_id)
            || empty($reason = $usersRepository->get_notification_message($reason_id))
        ) {
            jsonResponse(translate('systmess_error_document_reason_does_not_exist'));
        }
        //endregion Reason

        //region Versions
        /** @var VersionCollectionInterface $versions */
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionCollectionInterface::class, 'json');
        if (null === $versions) {
            jsonResponse(translate('systmess_document_rejection_version_malformed'));
        }
        //endregion Versions

        //region Check version
        $this->check_reupload_period($versions->last(), translate('systmess_decline_disabled_for_document_message'));
        //endregion Check version

        //region New version
        /** @var PendingVersion $latest_version */
        $latest_version = $versions->last();
        if (!$latest_version instanceof PendingVersionInterface) {
            jsonResponse(translate('systmess_version_invalid_not_declined_message'));
        }

        try {
            $versions->replace(
                $latest_version,
                RejectedVersion::createFromVersion($latest_version)
                    ->withReasonCode($reason_id)
                    ->withReasonTitle(arrayGet($reason, 'message_title'))
                    ->withReason(arrayGet($reason, 'message_text'))
                    ->withManager(new Manager(
                        $manager_id = (int) privileged_user_id(),
                        user_name_session(),
                        $this->getCachedUser(
                            $this->getApiClient()->getResource(UserResource::class),
                            $manager_id
                        )->getId()
                    ))
            );
        } catch (\Exception $exception) {
            jsonResponse(translate('systmess_version_decline_failed_uuid_message'), 'error', [
                'debug' => [
                    'message' => $exception->getMessage(),
                ],
            ]);
        }
        //endregion New version

        //region Serialize version
        $serialized_versions = 0 !== $versions->count() ? VersionSerializerStatic::serialize($versions, 'json') : null;
        if (
            null === $serialized_versions && 0 !== $versions->count()
        ) {
            // We failed to serialize it so there is no meaning to go to the next step.
            jsonResponse(translate('systmess_version_decline_failed_serialization_message'));
        }
        //endregion Serialize version

        //region Update
        if (!$personalDocuments->update_document($document_id, ['versions' => $serialized_versions])) {
            jsonResponse(translate('systmess_decline_document_failur_save_message'));
        }
        //endregion Update

        //region Update Activity Log
        $accreditation_document = $verificationDocumentTypesModel->runWithoutAllCasts(fn () => $verificationDocumentTypesModel->findOne((int) $document['id_type']));
        $fullname = "{$user['fname']} {$user['lname']}";
        $context = array_merge(
            [
                'document' => [
                    'id'    => $accreditation_document['id_document'],
                    'title' => $accreditation_document['document_title'],
                ],
                'target_user' => [
                    'id'      => $user_id,
                    'name'    => $fullname,
                    'profile' => [
                        'url' => getUserLink($fullname, $user_id, $user['gr_type']),
                    ],
                ],
                'reason' => [
                    'id'    => $reason['id_message'],
                    'title' => $reason['message_title'],
                ],
            ],
            get_user_activity_context()
        );

        $this->activity_logger->setResourceType(PERSONAL_DOCUMENT);
        $this->activity_logger->setOperationType(DECLINE_DOCUMENT);
        $this->activity_logger->setResource($document_id);
        $this->activity_logger->info($activityMessagesRepository->get_message(PERSONAL_DOCUMENT, DECLINE_DOCUMENT), $context);
        //endregion Update Activity Log

        jsonResponse(translate('systmess_success_document_has_been_declined'), 'success', [
            'version' => [
                'metadata' => $this->getVersionMetadata($versions->last()),
            ],
        ]);
    }

    /**
     * Copies the latest document version into another document.
     */
    private function copy_latest_document_version(int $principal_id, int $user_id, ?int $source_document_id, ?int $target_document_id): void
    {
        //region Repositiories
        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        //endregion Repositiories

        //region User access
        if (
            empty($user_id) || empty($user = $usersRepository->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        if (
            empty($principal_id) || (int) $principal_id !== (int) $user['id_principal']
        ) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        //endregion User access

        //region Documents
        if (
            empty($source_document_id)
            || null === ($source_document = $personalDocuments->get_document($source_document_id, [
                'conditions' => [
                    'principal' => $principal_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        if (
            empty($target_document_id)
            || null === ($target_document = $personalDocuments->get_document($target_document_id, [
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Documents

        //region Versions
        /** @var VersionCollectionInterface $source_versions */
        $source_versions = VersionSerializerStatic::deserialize(arrayGet($source_document, 'versions'), VersionCollectionInterface::class, 'json');
        if (null === $source_versions) {
            jsonResponse(translate('system_message_personal_documents_copy_document_empty_source_text'));
        }
        if (0 === $source_versions->count()) {
            jsonResponse(translate('system_message_personal_documents_copy_document_empty_source_text'));
        }

        /** @var VersionCollectionInterface $target_versions */
        $target_versions = VersionSerializerStatic::deserialize(arrayGet($target_document, 'versions'), VersionCollectionInterface::class, 'json') ?? new VersionList();
        //endregion Versions

        //region Latest versions
        /** @var AbstractVersion|VersionInterface $latest_source_version */
        $latest_source_version = $source_versions->last() ?: null;
        /** @var AbstractVersion|VersionInterface $latest_target_version */
        $latest_target_version = $target_versions->last() ?: null;

        if (
            !($this->getVersionMetadata($latest_target_version)['is_uploadable'] ?? false)
        ) {
            jsonResponse(translate('system_message_personal_documents_copy_document_not_uploadable_text'));
        }

        if (
            $latest_source_version instanceof RejectedVersionInterface
            || (
                $latest_source_version instanceof ExpiringVersionInterface && $latest_source_version->isExpired()
            )
        ) {
            jsonResponse(translate('system_message_personal_documents_copy_document_deny_text'));
        }
        //endregion Latest versions

        //region Modify
        //region Decline old version
        if (
            $latest_target_version
            && !$latest_target_version instanceof RejectedVersionInterface
            && !$latest_target_version instanceof AcceptedVersionInterface
        ) {
            try {
                $target_versions->replace(
                    $latest_target_version,
                    RejectedVersion::createFromVersion($latest_target_version)
                        ->withReasonTitle(translate('personal_documents_copy_document_rejection_reason_title'))
                        ->withReason(translate('personal_documents_copy_document_rejection_reason_text'))
                );
            } catch (\Exception $exception) {
                jsonResponse(translate('system_message_personal_documents_copy_document_generic_failure_text'), 'error', withDebugInformation(
                    [],
                    ['exception' => throwableToArray($exception)]
                ));
            }
        }
        //endregion Decline old version

        //region Copy new
        //region API resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        /** @var FilePermissionsResource $file_permissions */
        $file_permissions = $client->getResource(FilePermissionsResource::class);
        //endregion API resources

        // Just in case
        $clean_file = function (FileResource $files, FileObjects $file = null) {
            if (null !== $file) {
                try {
                    $files->deleteFile($file->getId()); //  Delete file from EP Docs
                } catch (\Exception $exception) {
                    // @todo log the exception
                }
            }
        };

        try {
            $manager = $this->getGenericUser($users); // Create or get manager
            $new_owner = $this->getCachedUser($users, $user_id);
            $old_owner = $this->getCachedUser($users, (int) $source_document['id_user']);
            /** @var FileInterface $file */
            $source_file = $latest_source_version->getFile();
            if (!$files->hasFile($source_file->getId())) {
                jsonResponse(
                    translate('system_message_personal_documents_copy_file_not_found_text'),
                    'error'
                );
            }
            if (
                !$file_permissions->hasPermissions(
                    $source_file->getId(),
                    $old_owner->getId(),
                    FilePermissionsResource::PERMISSION_READ | FilePermissionsResource::PERMISSION_WRITE
                )
            ) {
                jsonResponse(translate('system_message_personal_documents_copy_file_access_denied_text'));
            }

            $new_file_resource = $files->copyFile($new_owner->getId(), $source_file->getId());
            $file_permissions->createPermissions(
                $new_file_resource->getId(),
                $manager->getId(),
                FilePermissionsResource::PERMISSION_READ | FilePermissionsResource::PERMISSION_WRITE
            ); // Create permissions for manager
        } catch (\Exception $exception) {
            // @todo log the exception
            $clean_file($files, $new_file_resource);

            jsonResponse(translate('system_message_personal_documents_copy_file_failure_text'), 'error', withDebugInformation(
                [],
                ['exception' => throwableToArray($exception)]
            ));
        }

        $target_versions->add(
            $latest_source_version
                ->withName('v' . ($target_versions->count() + 1))
                ->withFile(new FileCopy($new_file_resource->getId(), $source_file))
        );
        $serialized_versions = VersionSerializerStatic::serialize($target_versions, 'json');
        if (null === $serialized_versions) {
            // @todo log the exception
            $clean_file($files, $new_file_resource);
            // We failed to serialize it so there is no meaning to go to the next step.
            jsonResponse(translate('system_message_personal_documents_copy_file_serialization_failure_text'));
        }
        //endregion Copy new
        //endregion Modify

        if (!$personalDocuments->update_document($target_document_id, ['versions' => $serialized_versions])) {
            // @todo log the exception
            $clean_file($files, $new_file_resource);
            // This bloody DB always makes problems...
            jsonResponse(translate('system_message_personal_documents_copy_file_write_failure_text'));
        }

        jsonResponse(translate('system_message_personal_documents_copy_success_text'), 'success', [
            'isReUplodable' => $this->getVersionMetadata($target_versions->last())['is_reuploadable'] ?? false,
            'status'        => with($target_versions->last(), function (AbstractVersion $version) {
                $metadata = $this->getVersionMetadata($version);
                if ($metadata['is_expiring_soon']) {
                    return 'expires';
                }
                if ($metadata['is_expired']) {
                    return 'expired';
                }

                return $this->getVersionStatusesMetadata()[get_class($version)]['type'] ?? 'pending';
            }),
        ]);
    }

    /**
     * Removes the latest uploaded version.
     *
     * @param int $user_id
     * @param int $document_id
     *
     * @deprecated
     */
    private function remove_latest_document_version($user_id, $document_id)
    {
        //region User access
        if (
            empty($user_id)
            || (
                logged_in() && (int) $user_id !== (int) privileged_user_id()
            )
        ) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        //endregion User access

        //region Document
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region latest version
        if (null === arrayGet($document, 'latest_version')) {
            // Nothing to delete
            jsonResponse(translate('systmess_success_document_canceled'), 'success');
        }

        /** @var VersionInterface $latest_version */
        $latest_version = VersionSerializerStatic::deserialize(arrayGet($document, 'latest_version'), VersionInterface::class, 'json');
        if (!$latest_version instanceof PendingVersionInterface) {
            jsonResponse('You cannot delete the file right now. Please try again later or contact administration.');
        }
        //endregion latest version

        //region Versions
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionList::class, 'json');
        if (null === $versions) {
            $versions = new VersionList();
        }
        //endregion Versions

        //region API resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        /** @var FilePermissionsResource $file_permissions */
        $file_permissions = $client->getResource(FilePermissionsResource::class);
        //endregion API resources

        //region Users UUID resolve
        try {
            $owner = $users->findUserIfNotCreate($this->getUserApiContext($user_id)); // Create or get user
        } catch (\Exception $exception) {
            // @todo better exceptions handling
            jsonResponse('You cannot delete the document right now. Please try again later or contact administration.');
        }
        //endregion Users UUID resolve

        //region Remove from list
        $versions->removeElement($versions->last());
        $serialized_versions = 0 !== $versions->count() ? VersionSerializerStatic::serialize($versions, 'json') : null;
        if (
            null === $serialized_versions
            && 0 !== $versions->count()
        ) {
            // We failed to serialize it so there is no meaning to go to the next step.
            jsonResponse('You cannot delete the document right now. Please try again later or contact administration.');
        }
        //endregion Remove from list

        //region Remove file
        if (
            $latest_version instanceof FileAwareInterface
            && null !== ($file = $latest_version->getFile())
            && null !== $file->getId()
        ) {
            try {
                if (!$file_permissions->hasPermissions($file->getId(), $owner->getId(), FilePermissionsResource::PERMISSION_WRITE)) {
                    // For some reason owner has no access to the file.
                    // Such cases must be studied.
                    jsonResponse('You have no permission to delete this document. Please contact administration for more information.');
                }
                $files->deleteFile($file->getId()); // Delete file
            } catch (\Exception $exception) {
                // @todo better exceptions handling
                jsonResponse('You cannot delete the document right now. Please try again later or contact administration.');
            }
        }
        //endregion Remove file

        //region Update
        if (!$personalDocuments->update_document($document_id, ['versions' => $serialized_versions])) {
            jsonResponse('You cannot delete the document right now. Please try again later or contact administration.');
        }
        //endregion Update

        jsonResponse('The document version is deleted.', 'success', [
            'version' => [
                'metadata' => $this->getVersionMetadata($versions->count() > 0 ? $versions->last() : null),
            ],
        ]);
    }

    /**
     * Changes the latest version expiration date.
     *
     * @param int $user_id
     * @param int $document_id
     */
    private function change_version_expiration_date($user_id, $document_id)
    {
        //region Validation
        $this->validator->set_rules([
            [
                'field' => 'expires',
                'label' => 'Expire on',
                'rules' => [
                    'required'                 => 'The expiration date is required.',
                    'valid_date[m/d/Y]'        => 'The expiration date is invalid.',
                    'valid_date_future[m/d/Y]' => 'The expiration date must be past current date.',
                ],
            ],
        ]);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Document
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Versions
        /** @var VersionCollectionInterface $versions */
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionList::class, 'json');
        /** @var AbstractVersion $latest_version */
        $latest_version = null !== $versions ? $versions->last() : null;
        if (!$latest_version instanceof ExpiringVersionInterface) {
            jsonResponse(translate('systmess_expiration_date_fail_error'));
        }
        //endregion Versions

        //region Check version
        $this->check_reupload_period($latest_version, translate('systmess_expiration_temporary_disabled_message'));
        //endregion Check version

        //region Expiration
        $expires_at = with(arrayGet($_POST, 'expires'), function ($date) {
            return !empty($date) ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null;
        });
        if (null !== $expires_at) {
            $versions->replace($latest_version, $latest_version->withExpirationDate($expires_at));
        }
        //endregion Expiration

        //region Serialize
        $serialized_versions = 0 !== $versions->count() ? VersionSerializerStatic::serialize($versions, 'json') : null;
        if (
            null === $serialized_versions && 0 !== $versions->count()
        ) {
            // We failed to serialize it so there is no meaning to go to the next step.
            jsonResponse(translate('systmess_expiration_date_serialize_error'));
        }
        //endregion Serialize

        //region Update
        if (!$personalDocuments->update_document($document_id, ['versions' => $serialized_versions])) {
            jsonResponse(translate('systmess_expiration_date_failed_save_error'));
        }
        //endregion Update

        jsonResponse(translate('systmess_expiration_date_document_success_message'), 'success', [
            'version' => [
                'metadata' => $this->getVersionMetadata($versions->last()),
            ],
        ]);
    }

    /**
     * Removes the latest version expiration date.
     *
     * @param int $user_id
     * @param int $document_id
     */
    private function remove_version_expiration_date($user_id, $document_id)
    {
        //region Document
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Versions
        /** @var VersionCollectionInterface $versions */
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionList::class, 'json');
        /** @var AbstractVersion $latest_version */
        $latest_version = null !== $versions ? $versions->last() : null;
        if (!$latest_version instanceof ExpiringVersionInterface) {
            jsonResponse(translate('systmess_cannot_remove_expiration_date_not_supported'));
        }
        //endregion Versions

        //region Check version
        $this->check_reupload_period($latest_version, translate('systmess_expiration_date_disabled_message'));
        //endregion Check version

        //region Expiration
        $versions->replace($latest_version, $latest_version->withoutExpirationDate());
        //endregion Expiration

        //region Serialize
        $serialized_versions = 0 !== $versions->count() ? VersionSerializerStatic::serialize($versions, 'json') : null;
        if (
            null === $serialized_versions && 0 !== $versions->count()
        ) {
            // We failed to serialize it so there is no meaning to go to the next step.
            jsonResponse(translate('systmess_remove_expiration_serialize_error'));
        }
        //endregion Serialize

        //region Update
        if (!$personalDocuments->update_document($document_id, ['versions' => $serialized_versions])) {
            jsonResponse(translate('systmess_cannot_remove_expiration_date_failed_save'));
        }
        //endregion Update

        jsonResponse(translate('systmess_success_remove_expiration_date'), 'success', [
            'version' => [
                'metadata' => $this->getVersionMetadata($versions->last()),
            ],
        ]);
    }

    /**
     * Downloads the document.
     *
     * @param int      $user_id
     * @param int      $document_id
     * @param null|int $version_index
     * @param bool     $is_administration
     */
    private function download_document($user_id, $document_id, $version_index = null, $is_administration = false)
    {
        //region Document
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }

        $document['title'] = accreditation_i18n(
            arrayGet($document, 'type.document_i18n'),
            'title',
            __SITE_LANG,
            arrayGet(arrayGet($document, 'type'), 'document_title', 'Unknown document')
        );
        $document['slug'] = snakeCase(strForURL(str_replace(['/', '\\'], ' ', $document['title']), '_'));
        //endregion Document

        //region Version
        if ((0 === (int) $version_index || null === $version_index) && null === arrayGet($document, 'latest_version')) {
            // Nothing to download
            jsonResponse(translate('systmess_download_document_not_exist_error'));
        }

        /** @var VersionCollectionInterface $versions */
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionCollectionInterface::class, 'json');
        if (null === $versions) {
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_version_malformed_on_download_admin')
                    : translate('systmess_document_download_faile_error')
            );
        }

        /** @var AbstractVersion $version */
        $version = null === $version_index ? $versions->last() : $versions->get((int) $version_index);
        if (!$version instanceof FileAwareInterface || !$version->hasFile()) {
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_download_admin_version_error')
                    : translate('systmess_document_download_faile_error')
            );
        }
        //endregion Version

        //region Check version
        if ($is_administration) {
            $this->check_reupload_period($version, translate('systmess_download_disabled_error'));
        }
        //endregion Check version

        //region API resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        /** @var FilePermissionsResource $file_permissions */
        $file_permissions = $client->getResource(FilePermissionsResource::class);
        /** @var AccessTokenResource $access_tokens */
        $access_tokens = $client->getResource(AccessTokenResource::class);
        //endregion API resources

        //region Manager UUID resolve
        try {
            if ($is_administration) {
                $user = $this->getGenericUser($users);
            } else {
                $user = $this->getCachedUser($users, $user_id);
            }
        } catch (\Exception $exception) {
            // @todo better exceptions handling
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_download_admin_error')
                    : translate('systmess_document_download_faile_error')
            );
        }
        //endregion Manager UUID resolve

        //region Access token
        try {
            /** @var FileInterface $file */
            $file = $version->getFile();
            if (!$files->hasFile($file->getId())) {
                jsonResponse(
                    $is_administration
                        ? translate('systmess_document_download_not_epdocs_admin')
                        : translate('systmess_document_download_faile_error')
                );
            }
            if (!$file_permissions->hasPermissions($file->getId(), $user->getId(), FilePermissionsResource::PERMISSION_READ)) {
                jsonResponse(
                    $is_administration
                        ? translate('systmess_document_download_no_permission_admin')
                        : translate('systmess_document_download_faile_error')
                );
            }
            $access_token = $access_tokens->createToken($file->getId(), 90);
        } catch (\Exception $exception) {
            jsonResponse(
                $is_administration
                    ? translate('systmess_document_download_epdocs_admin_error')
                    : translate('systmess_document_download_faile_error')
            );
        }
        //endregion Access token

        jsonResponse(null, 'success', [
            'token' => [
                'url'      => config('env.EP_DOCS_HOST', 'http://localhost') . $access_token->getPath(),
                'name'     => "{$file->getName()}.{$file->getExtension()}",
                'filename' => $is_administration
                    ? sprintf('%s_document_%s_(%s).%s', orderNumber($user_id), $document['slug'], $version->getName(), $file->getExtension())
                    : sprintf('%s_%s_(%s).%s', orderNumber($document_id), $document['slug'], $version->getName(), $file->getExtension()),
            ],
        ]);
    }

    /**
     * Removes the document.
     *
     * @param int $user_id
     * @param int $document_id
     */
    private function remove_document($user_id, $document_id)
    {
        //region Document
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        if (
            empty($document_id)
            || null === ($document = $personalDocuments->get_document($document_id, [
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region User
        /** @var Users_Model $usersRepository */
        $usersRepository = \model(Users_Model::class);
        $user = $usersRepository->findOne((int) $user_id, [
            'with_count' => [
                'upgradeRequests as has_upgrade_requests' => function (RelationInterface $relation, QueryBuilder $builder) {
                    $relation->getRelated()->getScope('status')->call($relation->getRelated(), $builder, UpgradeRequestStatus::FRESH());
                },
                'cancellationRequests as has_cancellation_requests' => function (RelationInterface $relation, QueryBuilder $builder) {
                    $relation->getRelated()->getScope('status')->call($relation->getRelated(), $builder, CancellationRequestStatus::INIT());
                },
                'profileEditRequests as has_profile_edit_requests' => function (RelationInterface $relation, QueryBuilder $builder) {
                    $relation->getRelated()->getScope('status')->call($relation->getRelated(), $builder, EditRequestStatus::PENDING());
                },
            ],
        ]);
        // if ($user['has_upgrade_requests'] ?? false) {
        //     jsonResponse(translate('verification_documents_remove_document_error_has_upgrade_requests', null, true), 'warning');
        // }
        if ($user['has_cancellation_requests'] ?? false) {
            jsonResponse(translate('verification_documents_remove_document_error_has_cancellation_requests', null, true), 'warning');
        }
        if ($user['has_profile_edit_requests'] ?? false) {
            jsonResponse(translate('verification_documents_remove_document_error_has_profile_edit_requests', null, true), 'warning');
        }
        //endregion User

        //region Versions
        $versions = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionList::class, 'json');
        //endregion Versions

        //region Delete files
        //region API resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        /** @var FilePermissionsResource $file_permissions */
        $file_permissions = $client->getResource(FilePermissionsResource::class);
        //endregion API resources

        //region Manager UUID resolve
        try {
            $manager = $this->getGenericUser($users);
        } catch (\Exception $exception) {
            // @todo better exceptions handling
            jsonResponse(translate('systmess_remove_document_failure_message'));
        }
        //endregion Manager UUID resolve

        //region Remove file in chain
        foreach ($versions as $version) {
            /** @var VersionInterface/FileAwareInterface $version */
            if (!$version instanceof FileAwareInterface || !$version->hasFile()) {
                continue;
            }

            /** @var FileInterface $file */
            $file = $version->getFile();

            try {
                if ($files->hasFile($file->getId())) {
                    if (!$file_permissions->hasPermissions($file->getId(), $manager->getId(), FilePermissionsResource::PERMISSION_WRITE)) {
                        // For some reason owner has no access to the file.
                        // @todo Log
                        jsonResponse(translate('systmess_remove_document_permission_error'));
                    }
                    $files->deleteFile($file->getId());
                }
            } catch (\Exception $exception) {
                // File not deleted
                // @todo Log
                jsonResponse(translate('systmess_remove_document_failed_message'));
            }
        }
        //endregion Remove file in chain
        //endregion Delete files

        //region Delete document
        if (!$personalDocuments->delete_document($document_id)) {
            jsonResponse(translate('systmess_remove_document_failure_message'));
        }
        //endregion Delete document

        //region Find removed document type
        $type = [];
        $type_id = (int) arrayGet($document, 'id_type');
        /** @var Verification_Document_Types_Model $verificationDocumentTypes */
        $verificationDocumentTypes = model(Verification_Document_Types_Model::class);
        if (!empty($type_id)) {
            $type = array_filter((array) $verificationDocumentTypes->findOne($type_id));
        }
        //endregion Find removed document type

        //region Actualize
        $this->actualizeVerificationInformation($personalDocuments, model(User_Model::class), $user_id);
        //endregion Actualize

        jsonResponse(translate('systmess_document_removed_success_message'), 'success', [
            'type' => [
                'id'    => arrayGet($type, 'id_document'),
                'title' => accreditation_i18n(arrayGet($type, 'document_i18n'), 'title', 'en', arrayGet($type, 'document_title', 'Unknown document')),
            ],
        ]);
    }

    /**
     * Adds additional documents to the user.
     */
    private function add_documents(int $userId, array $documents = []): void
    {
        //region Repositories
        /** @var Users_Model $usersRepository */
        $usersRepository = \model(Users_Model::class);
        /** @var User_Personal_Documents_Model $personalDocuments */
        $personalDocuments = model(User_Personal_Documents_Model::class);
        /** @var Verification_Document_Types_Model $verificationDocumentTypes */
        $verificationDocumentTypes = model(Verification_Document_Types_Model::class);
        //endregion Repositories

        //region Validation
        $this->validator->set_rules([
            [
                'field' => 'user',
                'rules' => [
                    'required' => translate('systmess_user_id_required_validation_message'),
                    'integer'  => translate('validation_systmess_user_id_integer'),
                    function ($attribute, $value, $fail) use ($usersRepository) {
                        if (empty($value)) {
                            return;
                        }

                        if (!$usersRepository->has((int) $value)) {
                            $fail(translate('validation_systmess_id_not_found'));
                        }
                    },
                ],
            ],
            [
                'field' => 'documents',
                'rules' => [
                    'required' => translate('validation_systmess_document_required'),
                ],
            ],
        ]);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Documents check
        $documents = array_map(function ($document) { return (int) $document; }, $documents);
        $found = $verificationDocumentTypes->findAllBy([
            'scopes' => [
                'include' => array_filter($documents),
            ],
        ]);

        if (count($found) !== count($documents)) {
            jsonResponse(translate('systmess_document_not_found_add_additional_message'));
        }
        if (!empty($personalDocuments->get_documents([
            'conditions' => [
                'user'  => $userId,
                'types' => array_filter($documents),
            ],
        ]))) {
            jsonResponse(translate('systmess_more_documents_assigned_error'));
        }
        //endregion Documents check

        //region User
        $user = $usersRepository->findOne((int) $userId, [
            'with'       => ['group'],
            'with_count' => [
                'upgradeRequests as has_upgrade_requests' => function (RelationInterface $relation, QueryBuilder $builder) {
                    $relation->getRelated()->getScope('status')->call($relation->getRelated(), $builder, UpgradeRequestStatus::FRESH());
                },
                'cancellationRequests as has_cancellation_requests' => function (RelationInterface $relation, QueryBuilder $builder) {
                    $relation->getRelated()->getScope('status')->call($relation->getRelated(), $builder, CancellationRequestStatus::INIT());
                },
                'profileEditRequests as has_profile_edit_requests' => function (RelationInterface $relation, QueryBuilder $builder) {
                    $relation->getRelated()->getScope('status')->call($relation->getRelated(), $builder, EditRequestStatus::PENDING());
                },
            ],
        ]);
        // if ($user['has_upgrade_requests'] ?? false) {
        //     jsonResponse(translate('verification_documents_add_document_error_has_upgrade_requests', null, true), 'warning');
        // }
        if ($user['has_cancellation_requests'] ?? false) {
            jsonResponse(translate('verification_documents_add_document_error_has_cancellation_requests', null, true), 'warning');
        }
        if ($user['has_profile_edit_requests'] ?? false) {
            jsonResponse(translate('verification_documents_add_document_error_has_profile_edit_requests', null, true), 'warning');
        }
        //endregion User

        //region Additional documents
        $additional_documents = array_map(
            function ($document) use ($userId, $user) {
                return [
                    'id_type'      => (int) $document,
                    'id_user'      => (int) $userId,
                    'id_principal' => (int) $user['id_principal'] ?: null,
                ];
            },
            array_column($found, 'id_document')
        );
        //endregion Additional documents

        //region Create records
        if (!$personalDocuments->create_documents($additional_documents)) {
            jsonResponse(translate('systmess_failure_add_more_documents_error'));
        }
        //endregion Create records

        //region Actualize
        $this->actualizeVerificationInformation($personalDocuments, model(User_Model::class), $userId);
        //endregion Actualize

        jsonResponse(translate('systmess_success_document_has_been_assigned'), 'success', [
            'documents' => array_column($found, 'id_document'),
        ]);
    }

    /**
     * Sends notifcation to the document managers.
     *
     * @param array  $user
     * @param array  $document
     * @param string $notification
     * @param string $link
     */
    private function notify_document_managers($user, $document, $notification, $link = '/users/administration')
    {
        /** @var User_Model $usersRepository */
        $usersRepository = \model(User_Model::class);
        /** @var Notify_Model $notificationsRepository */
        $notificationsRepository = \model(Notify_Model::class);

        //region Vars
        $link = getUrlForGroup($link, 'admin');
        $targets = array_filter(array_column($usersRepository->get_users_by_additional_right('receive_document_notification'), 'idu'));
        $document_id = (int) $document['id_document'];
        $document_title = accreditation_i18n(arrayGet($document, 'type.document_i18n'), 'title', null, arrayGet($document, 'type.document_title'));
        if (empty($targets)) {
            return;
        }
        //endregion Vars

        //region Notifications
        $notificationsRepository->send_notify([
            'systmess'  => true,
            'mess_code' => $notification,
            'id_users'  => $targets,
            'replace'   => [
                '[LINK]'          => $link,
                '[USER]'          => cleanOutput(trim("{$user['fname']} {$user['lname']}")),
                '[DOCUMENT]'      => orderNumber($document_id),
                '[DOCUMENT_NAME]' => cleanOutput($document_title),
            ],
        ]);
        //endregion Notifications
    }

    /**
     * Returns the document list payload that is compatible with datatables.
     *
     * @return array
     */
    private function get_documents_list_payload(User_Model $usersRepository, array $documents = [])
    {
        //region Vars
        $uri_reference = $this->getUriReferenceQuery();
        $notifications = arrayByKey($usersRepository->get_notification_messages(['message_module' => 'accreditation']), 'id_message');
        $popup_placeholder = cleanOutput('
            <div class="elem-powered-by pl-10">
                <div class="elem-powered-by__txt">Secured by</div><div class="elem-powered-by__name">EP Docs</div>
            </div>
        ');
        $expiration_statuses = $this->getVersionExpirationMetadata();
        $default_status = $this->getVersionDeafultStatusesMetadata();
        $statuses = $this->getVersionStatusesMetadata();
        //endregion Vars

        foreach ($documents as $document) {
            //region Version
            /** @var null|AcceptedVersionInterface|ExpiringVersionInterface|PendingVersionInterface|RejectedVersionInterface|VersionInterface $version */
            $version = VersionSerializerStatic::deserialize($document['latest_version'], VersionInterface::class, 'json');
            //endregion Version

            //region Document vars
            $version_metadata = $this->getVersionMetadata($version);
            $document_id = (int) $document['id_document'];
            $document_title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title']);
            if (!empty($document['subtitle'])) {
                $document_title .= ' (' . cleanOutput($document['subtitle']) . ')';
            }
            $document_description = accreditation_i18n($document['type']['document_i18n'], 'description', null, $document['type']['document_description']);
            $is_expiring_soon = (bool) $version_metadata['is_expiring_soon'];
            $documentTypeMultiple = (bool) $document['type']['document_is_multiple'];
            $is_reuploadable = (bool) $version_metadata['is_reuploadable'];
            $is_uploadable = (bool) $version_metadata['is_uploadable'];
            $is_expirable = (bool) $version_metadata['is_version_expirable'];
            $is_rejected = (bool) $version_metadata['is_version_rejected'];
            $is_pending = (bool) $version_metadata['is_version_pending'];
            $is_expired = (bool) $version_metadata['is_expired'];
            //endregion Document vars

            //region Document
            //region Version status
            $document_version_status = null !== $version ? arrayGet($statuses, get_class($version), $default_status) : null;
            $document_version_color = $document_version_status['color'];
            $document_version_status_title = $document_version_status['title'];
            if ($is_rejected) {
                $document_version_status_info_title = cleanOutput(arrayGet($notifications, "{$version_metadata['rejection_code']}.message_title", $version->getReasonTitle()));
                $document_version_status_info_description = cleanOutput(arrayGet($notifications, "{$version_metadata['rejection_code']}.message_text", $version->getReason()));
                $document_version_status_label = "
                    {$document_version_status_title}
                    <a class=\"info-dialog\"
                        data-message=\"{$document_version_status_info_description}\"
                        data-title=\"{$document_version_status_info_title}\"
                        title=\"{$document_version_status_info_title}\">
                        <i class=\"ep-icon ep-icon_info fs-16\"></i>
                    </a>
                ";
            } else {
                $document_version_status_label = $document_version_status_title;
            }

            $document_status_label = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <div class=\"text-nowrap {$document_version_color}\" title=\"{$document_version_status['description']}\">
                        {$document_version_status_label}
                    </div>
                </div>
            ";
            //endregion Version status

            //region Expiration status
            $document_expiration_label = null;
            if ($is_expirable && $is_expiring_soon) {
                $document_expiration_status = null !== $version ? arrayGet($expiration_statuses, 'expires', $default_status) : null;
                $document_expiration_color = $document_expiration_status['color'];
                $document_expiration_status_label = is_callable($document_expiration_status['title'])
                    ? $document_expiration_status['title']($version->getExpirationDate())
                    : $document_expiration_status['title'];
                $document_expiration_label = "
                    <div class=\"main-data-table__item-ttl mw-300\">
                        <div class=\"text-nowrap {$document_expiration_color}\" title=\"{$document_expiration_status['description']}\">
                            {$document_expiration_status_label}
                        </div>
                    </div>
                ";
            } elseif ($is_expirable && $is_expired) {
                $document_expiration_status = null !== $version ? arrayGet($expiration_statuses, 'expired', $default_status) : null;
                $document_expiration_color = $document_expiration_status['color'];
                $document_expiration_status_label = $document_expiration_status['title'];
                $document_expiration_label = "
                    <div class=\"main-data-table__item-ttl mw-300\">
                        <div class=\"text-nowrap {$document_expiration_color}\" title=\"{$document_expiration_status['description']}\">
                            {$document_expiration_status_label}
                        </div>
                    </div>
                ";
            }
            //endregion Expiration status

            $document_preview = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <div class=\"txt-medium text-nowrap\" title=\"{$document_title}\">
                        {$document_title}
                    </div>
                </div>
                {$document_status_label}
                {$document_expiration_label}
            ";
            //endregion Document

            //region Description
            $description_preview = '&mdash;';
            if (!empty($document_description)) {
                $description_text = cleanOutput($document_description);
                $description_preview = "
                    <div class=\"grid-text\">
                        <div class=\"grid-text__item\">
                            <div>
                                {$description_text}
                            </div>
                        </div>
                    </div>
                ";
            }
            //endregion Description

            //region Expiration date
            $expiration_date = null;
            if (
                $version instanceof ExpiringVersionInterface && $version->hasExpirationDate()
            ) {
                $expiration_date = $version->getExpirationDate()->format(DATE_ATOM);
            }
            //endregion Expiration date

            //region Actions
            //region Versions button
            $verions_button_url = getUrlForGroup("personal_documents/popup_forms/versions/{$document_id}{$uri_reference}");
            $verions_button_text = translate('general_button_versions_text', null, true);
            $verions_button_modal_title = translate('personal_documents_dashboard_dt_button_versions_modal_title', null, true);
            $verions_button = "
                <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$verions_button_url}\"
                    data-title-type=\"html\"
                    data-title=\"{$verions_button_modal_title}{$popup_placeholder}\"
                    data-mw=\"900\"
                    data-w=\"99%\">
                    <i class=\"ep-icon ep-icon_folder\"></i>
                    <span>{$verions_button_text}</span>
                </a>
            ";
            //endregion Versions button

            //region Upload button
            $upload_button = null;
            if ($is_uploadable || $documentTypeMultiple) {
                $upload_button_url = getUrlForGroup("personal_documents/popup_forms/upload/{$document_id}{$uri_reference}");
                $upload_button_text = $documentTypeMultiple && !$is_uploadable ? translate('general_button_upload_another_text', null, true) : translate('general_button_upload_text', null, true);
                $upload_button_modal_title = $documentTypeMultiple && !$is_uploadable ? translate('general_button_upload_another_full_text', null, true) : translate('personal_documents_dashboard_dt_upload_document_modal_title', null, true);
                $upload_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$upload_button_url}\"
                        data-title-type=\"html\"
                        data-title=\"{$upload_button_modal_title}{$popup_placeholder}\">
                        <i class=\"ep-icon ep-icon_upload\"></i>
                        <span>{$upload_button_text}</span>
                    </a>
                ";
            }
            //endregion Upload button

            //region Re-upload button
            $re_upload_button = null;
            if ($is_reuploadable) {
                $re_upload_button_url = getUrlForGroup("personal_documents/popup_forms/replace/{$document_id}{$uri_reference}");
                $re_upload_button_text = translate('general_button_re_upload_text', null, true);
                $re_upload_button_modal_title = translate('personal_documents_dashboard_dt_re_upload_document_modal_title', null, true);
                $re_upload_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$re_upload_button_url}\"
                        data-title-type=\"html\"
                        data-title=\"{$re_upload_button_modal_title}{$popup_placeholder}\">
                        <i class=\"ep-icon ep-icon_upload\"></i>
                        <span>{$re_upload_button_text}</span>
                    </a>
                ";
            }
            //endregion Re-upload button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$upload_button}
                        {$re_upload_button}
                        {$verions_button}
                        {$all_button}
                    </div>
                </div>
            ";
            //endregion Actions

            $output[] = [
                'document'    => $document_preview,
                'description' => $description_preview,
                'created_at'  => getDateFormatIfNotEmpty(arrayGet($document, 'date_created')),
                'updated_at'  => getDateFormatIfNotEmpty(arrayGet($document, 'date_updated')),
                'expires_at'  => getDateFormatIfNotEmpty($expiration_date, DATE_ATOM, 'j M, Y'),
                'actions'     => $actions,
            ];
        }

        return $output;
    }

    /**
     * Returns the document versions payload that is compatible with datatables.
     *
     * @return array
     */
    private function get_document_versions_payload(
        User_Model $usersRepository,
        array $document,
        array $versions,
        VersionInterface $first_version,
        VersionInterface $latest_version
    ) {
        //region Vars
        $output = [];
        $document_id = (int) $document['id_document'];
        $notifications = arrayByKey($usersRepository->get_notification_messages(['message_module' => 'accreditation']), 'id_message');
        $document_title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title']);
        $expiration_statuses = $this->getVersionExpirationMetadata();
        $default_status = $this->getVersionDeafultStatusesMetadata();
        $statuses = $this->getVersionStatusesMetadata();
        $uri_reference = $this->getUriReferenceQuery();
        $popup_placeholder = cleanOutput('
            <div class="elem-powered-by pl-10">
                <div class="elem-powered-by__txt">Secured by</div><div class="elem-powered-by__name">EP Docs</div>
            </div>
        ');
        //endregion Vars

        /** @var null|AcceptedVersionInterface|ExpiringVersionInterface|PendingVersionInterface|RejectedVersionInterface|VersionInterface $version */
        foreach ($versions as $index => $version) {
            //region Document vars
            $version_name = null !== $version ? $version->getName() : null;
            $version_metadata = $this->getVersionMetadata($version);
            $is_latest_version = $latest_version === $version;
            $is_expiring_soon = (bool) $version_metadata['is_expiring_soon'];
            $is_reuploadable = (bool) $version_metadata['is_reuploadable'];
            $is_expirable = (bool) $version_metadata['is_version_expirable'];
            $is_uploaded = (bool) $version_metadata['is_uploaded'];
            $is_rejected = (bool) $version_metadata['is_version_rejected'];
            $is_pending = (bool) $version_metadata['is_version_pending'];
            $is_expired = (bool) $version_metadata['is_expired'];
            //endregion Document vars

            //region Version preview
            //region Badge
            $version_badge = null;
            if (null !== $version_name) {
                $version_badge = "
                    <span class=\"badge badge-pill badge-primary\">{$version_name}</span>
                ";
            }
            //endregion Badge

            //region Status
            $version_status = null !== $version ? arrayGet($statuses, get_class($version), $default_status) : null;
            $version_color = $version_status['color'];
            $version_status_title = $version_status['title'];

            if ($is_rejected) {
                $version_status_info_title = cleanOutput(arrayGet($notifications, "{$version_metadata['rejection_code']}.message_title", $version->getReasonTitle()));
                $version_status_info_description = cleanOutput(arrayGet($notifications, "{$version_metadata['rejection_code']}.message_text", $version->getReason()));
                $version_status_label = "
                    {$version_status_title}
                    <a class=\"info-dialog\"
                        data-message=\"{$version_status_info_description}\"
                        data-title=\"{$version_status_info_title}\"
                        title=\"{$version_status_info_title}\">
                        <i class=\"ep-icon ep-icon_info fs-16\"></i>
                    </a>
                ";
            } else {
                $version_status_label = $version_status_title;
            }

            $version_status = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <div class=\"text-nowrap {$version_color}\" title=\"{$version_status['description']}\">
                        {$version_status_label}
                    </div>
                </div>
            ";
            //endregion Status

            //region Expiration
            $expiration_label = null;
            if ($is_expirable && $is_expiring_soon) {
                $expiration_status = null !== $version ? arrayGet($expiration_statuses, 'expires', $default_status) : null;
                $expiration_color = $expiration_status['color'];
                $expiration_status_label = $expiration_status['title'];
                $expiration_status_label = is_callable($expiration_status['title'])
                    ? $expiration_status['title']($version->getExpirationDate())
                    : $expiration_status['title'];
                $expiration_label = "
                    <div class=\"main-data-table__item-ttl mw-300\">
                        <div class=\"text-nowrap {$expiration_color}\" title=\"{$expiration_status['description']}\">
                            {$expiration_status_label}
                        </div>
                    </div>
                ";
            } elseif ($is_expirable && $is_expired) {
                $expiration_status = null !== $version ? arrayGet($expiration_statuses, 'expired', $default_status) : null;
                $expiration_color = $expiration_status['color'];
                $expiration_status_label = $expiration_status['title'];
                $expiration_label = "
                    <div class=\"main-data-table__item-ttl mw-300\">
                        <div class=\"text-nowrap {$expiration_color}\" title=\"{$expiration_status['description']}\">
                            {$expiration_status_label}
                        </div>
                    </div>
                ";
            }
            //endregion Expiration

            //region Preview
            $version_preview = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <div class=\"txt-medium text-nowrap\"
                        title=\"{$document_title}\">
                        {$version_badge} {$document_title}
                    </div>
                </div>
                {$version_status}
                {$expiration_label}
            ";
            //endregion Preview
            //endregion Version preview

            //region Actions
            //region Download button
            $download_button = null;
            if ($is_uploaded) {
                $download_button_text = translate('general_button_download_text', null, true);
                $download_button = "
                    <a class=\"dropdown-item call-function\"
                        data-callback=\"downloadDocument\"
                        data-document=\"{$document_id}\"
                        data-version=\"{$index}\">
                        <i class=\"ep-icon ep-icon_download\"></i>
                        <span>{$download_button_text}</span>
                    </a>
                ";
            }
            //endregion Download button

            //region Re-upload button
            $re_upload_button = null;
            if ($is_latest_version && $is_reuploadable) {
                $re_upload_url = getUrlForGroup("personal_documents/popup_forms/replace/{$document_id}{$uri_reference}");
                $re_upload_text = translate('general_button_re_upload_text', null, true);
                $re_upload_modal_title = translate('personal_documents_dashboard_dt_re_upload_document_modal_title', null, true);
                $re_upload_button = "
                    <a class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$re_upload_url}\"
                        data-title-type=\"html\"
                        data-title=\"{$re_upload_modal_title}{$popup_placeholder}\">
                        <i class=\"ep-icon ep-icon_upload\"></i>
                        <span>{$re_upload_text}</span>
                    </a>
                ";
            }
            //endregion Re-upload button

            //region All button
            $all_button_text = translate('general_dt_info_all_text', null, true);
            $all_button = "
                <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                    data-callback=\"dataTableAllInfo\"
                    target=\"_blank\">
                    <i class=\"ep-icon ep-icon_info-stroke\"></i>
                    <span>{$all_button_text}</span>
                </a>
            ";
            //endregion All button

            $buttons = implode('', array_filter(array_map('trim', [
                $re_upload_button,
                $download_button,
                $all_button,
            ])));

            $actions_preview = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$buttons}
                    </div>
                </div>
            ";
            //endregion Actions

            $output[] = [
                'version'    => $version_preview,
                'created_at' => null !== $version ? getDateFormatIfNotEmpty($version->getCreationDate()->format(DATE_ATOM), DATE_ATOM) : '&mdash;',
                'actions'    => $actions_preview,
            ];
        }

        return array_values($output);
    }

    /**
     * Returns the document versions payload that is compatible with datatables.
     *
     * @return array
     */
    private function get_admin_document_versions_payload(
        array $document,
        array $versions,
        VersionInterface $first_version,
        VersionInterface $latest_version
    ) {
        //region Vars
        $output = [];
        $document_id = (int) $document['id_document'];
        $document_title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title']);
        $default_status = $this->getVersionDeafultStatusesMetadata();
        $statuses = $this->getVersionStatusesMetadata();
        //endregion Vars

        /** @var VersionInterface $version */
        foreach ($versions as $index => $version) {
            //region Document vars
            $version_name = null !== $version ? $version->getName() : null;
            $version_metadata = $this->getVersionMetadata($version);
            $is_uploaded = (bool) $version_metadata['is_uploaded'];
            $is_rejected = (bool) $version_metadata['is_version_rejected'];
            $is_expired = (bool) $version_metadata['is_expired'];
            //endregion Document vars

            //region Version preview
            //region Badge
            $version_badge = null;
            if (null !== $version_name) {
                $version_badge = "
                    <span class=\"badge badge-pill badge-primary\">{$version_name}</span>
                ";
            }
            //endregion Badge

            //region Status
            if ($is_expired) {
                $version_type = null !== $version ? arrayGet($statuses, ExpiringVersionInterface::class, $default_status) : null;
                $version_type_color = $version_type['color'];
            } else {
                $version_type = null !== $version ? arrayGet($statuses, get_class($version), $default_status) : null;
                $version_type_color = $version_type['color'];
            }
            $version_status = "
                <div class=\"main-data-table__item-ttl mw-300\">
                    <div class=\"text-nowrap {$version_type_color}\"
                        title=\"{$version_type['description']}\">
                        {$version_type['title']}
                    </div>
                </div>
            ";
            //endregion Status

            //region Preview
            $version_preview = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__float\">
                        <div class=\"main-data-table__item-ttl mw-300\">
                            <div class=\"txt-medium text-nowrap\"
                                title=\"{$document_title}\">
                                {$version_badge} {$document_title}
                            </div>
                        </div>
                        {$version_status}
                    </div>
                </div>
            ";
            //endregion Preview
            //endregion Version preview

            //region Actions
            //region Download button
            $download_button = null;
            if ($is_uploaded) {
                $download_button_text = translate('general_button_download_text', null, true);
                $download_button = "
                    <a class=\"dropdown-item call-function\"
                        data-callback=\"downloadDocument\"
                        data-document=\"{$document_id}\"
                        data-version=\"{$index}\">
                        <i class=\"ep-icon ep-icon_download\"></i>
                        <span>{$download_button_text}</span>
                    </a>
                ";
            }
            //endregion Download button

            $actions_preview = "
                {$download_button}
            ";
            //endregion Actions

            $output[] = [
                'version' => $version_preview,
                'actions' => $actions_preview,
            ];
        }

        return array_values($output);
    }

    private function check_reupload_period(VersionInterface $version, $appended_message = null)
    {
        $metadata = $this->getVersionMetadata($version);
        if ($metadata['is_reuploadable']) {
            jsonResponse(translate('systmess_document_buffer_period_not_yet_done_error') . $appended_message, 'warning');
        }
    }

    /**
     * Actualizes user's cerification information.
     */
    private function actualizeVerificationInformation(
        User_Personal_Documents_Model $personalDocuments,
        User_Model $usersRepository,
        int $userId
    ): void {
        //region Documents
        $documents = \array_filter((array) $personalDocuments->get_documents([
            'with'       => ['type'],
            'conditions' => [
                'user' => $userId,
            ],
        ]));
        //endregion Documents

        //region Uploads
        $totalUploads = \count($documents);
        $currentUploads = \array_reduce($documents, function ($carry, $document) {
            if (
                null !== $document
            ) {
                $latest_version = VersionSerializerStatic::deserialize($document['latest_version'], VersionInterface::class, 'json');
                if (
                    $latest_version instanceof PendingVersionInterface
                    || (
                        $latest_version instanceof AcceptedVersionInterface
                        && !(
                            $latest_version instanceof ExpiringVersionInterface
                            && $latest_version->hasExpirationDate()
                            && $latest_version->getExpirationDate() > new DateTimeImmutable()
                        )
                    )
                ) {
                    return $carry + 1;
                }
            }

            return $carry;
        }, 0);
        //endregion Uploads

        //region Progress calculation
        $uploadProgress = 'none';
        if ($currentUploads === $totalUploads) {
            $uploadProgress = 'full';
        } elseif ($currentUploads < $totalUploads) {
            $uploadProgress = 'partial';
        }
        //endregion Progress calculation

        //region Update
        $usersRepository->updateUserMain($userId, [
            'verfication_upload_progress'     => $uploadProgress,
            'accreditation_files_upload_date' => \date('Y-m-d H:i:s'),
        ]);
        //endregion Update
    }
}

// End of file personal_documents.php
// Location: /tinymvc/myapp/controllers/personal_documents.php
