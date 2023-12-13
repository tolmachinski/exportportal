<?php

use App\Common\Contracts\User\EmailStatus;
use App\Common\Contracts\User\UserStatus;
use App\Common\Contracts\User\VerificationUploadProgress;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Encryption\SerializeException;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\DocumentNotFoundException;
use App\Common\Exceptions\UserNotFoundException;
use App\Common\Traits\DocumentsApiAwareTrait;
use App\Common\Traits\ModalUriReferenceTrait;
use App\Common\Traits\VersionMetadataTrait;
use App\Common\Traits\VersionStatusesMetadataAwareTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Documents\Serializer\VersionSerializerStatic;
use App\Documents\Versioning\AbstractVersion;
use App\Documents\Versioning\ContentContextEntries;
use App\Documents\Versioning\VersionCollectionInterface;
use App\Documents\Versioning\VersionInterface;
use App\Documents\Versioning\VersionList;
use App\Email\GroupEmailTemplates;
use App\Messenger\Message\Event\Lifecycle\UserWasVerifiedEvent;
use App\Plugins\EPDocs\Rest\Objects\File as FileObject;
use App\Plugins\EPDocs\Rest\Resources\AccessToken as AccessTokenResource;
use App\Plugins\EPDocs\Rest\Resources\File as FileResource;
use App\Plugins\EPDocs\Rest\Resources\FilePermissions as FilePermissionsResource;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;
use App\Validators\BusinessNumberValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Query\QueryBuilder;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Chat\Recource\ResourceOptions;
use ExportPortal\Contracts\Chat\Recource\ResourceType;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use const App\Logger\Activity\OperationTypes\ADMIN_VERIFICATION_USER;
use const App\Logger\Activity\ResourceTypes\USER;

/**
 * Controller Verification.
 *
 * @author Bendiucov Tatiana
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
class Verification_Controller extends TinyMVC_Controller
{
    use VersionMetadataTrait;
    use DocumentsApiAwareTrait;
    use ModalUriReferenceTrait;
    use VersionStatusesMetadataAwareTrait;

    private const CUSTOM_FIELDS_BUSINESS_NUMBER_FIELD_KEY = 'businessNumber';
    private const CUSTOM_FIELDS_BUSINESS_NUMBER_REQUEST_KEY = 'business_number';

    /**
     * Controller Verification index page.
     */
    public function index()
    {
        checkIsLogged();
        checkDomainForGroup();
        checkPermision('manage_personal_documents');

        $this->show_verification_page(model('user')->getSimpleUser((int) privileged_user_id()));
    }

    /**
     * Shows the page with users that must pass verification.
     */
    public function users()
    {
        checkIsLogged();
        checkDomainForGroup();
        checkPermision('manage_user_documents');

        views(['admin/header_view', 'admin/verification/index_view', 'admin/footer_view'], [
            'calling_statuses' => model('user')->get_calling_statuses(),
            'title'            => "User's verification",
            'filters'          => with($_GET, function ($params) {
                $user = arrayGet($params, 'user');
                $filters = [];
                if (null !== $user) {
                    $filters['user'] = ['value' => (int) $user, 'placeholder' => orderNumber($user)];
                }

                return $filters;
            }),
        ]);
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkDomainForGroup();
        checkPermisionAjaxModal('manage_personal_documents,manage_user_documents');

        $action = uri()->segment(3);
        switch ($action) {
            case 'emails':
                checkPermisionAjaxModal('manage_user_documents');

                $this->show_emails_popup((int) uri()->segment(4));

                break;
            case 'edit_custom_fields':
                checkPermisionAjaxModal('manage_user_documents');

                $this->show_custom_fields_edit_popup(request(), (int) uri()->segment(5) ?: null, (int) uri()->segment(7) ?: null);

                break;
            case 'user_verification_documents':
                checkPermisionAjaxModal('manage_user_documents');

                $this->show_user_verification_documents_popup((int) uri()->segment(4));

                break;

            default:
                messageInModal(translate('sysmtess_provided_path_not_found'));

            break;
        }
    }

    public function ajax_operations()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkDomainForGroup();
        checkPermisionAjax('manage_personal_documents,manage_user_documents');

        $action = uri()->segment(3);
        switch ($action) {
            case 'complete':
                checkPermisionAjax('manage_user_documents');

                $this->complete_verification((int) arrayGet($_POST, 'user'));

                break;
            case 'fetch_files':
                checkPermisionAjax('manage_user_documents');

                $this->fetch_user_files((int) arrayGet($_POST, 'user'));

                break;
            case 'remove_uploaded_file':
                checkPermisionAjax('manage_user_documents');

                $this->remove_user_file((int) request()->request->get('user'), (string) request()->request->get('file'));

                break;
            case 'access_uploaded_file':
                checkPermisionAjax('manage_user_documents');

                $this->accesss_user_file((int) request()->request->get('user'), (string) request()->request->get('file'));

                break;
            case 'edit_custom_fields':
                checkPermisionAjax('manage_user_documents');

                $this->edit_custom_fields(request(), request()->request->getInt('user') ?: null, request()->request->getInt('document') ?: null);

                break;
            case 'upload_notification':
                checkPermisionAjax('manage_user_documents');

                $this->send_upload_notification((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'));

                break;
            case 'assignment_notification':
                checkPermisionAjax('manage_user_documents');

                $this->send_assignment_notification((int) arrayGet($_POST, 'user'), (array) arrayGet($_POST, 'documents'));

                break;
            case 'confirmation_notification':
                checkPermisionAjax('manage_user_documents');

                $this->send_confirmation_notification((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'));

                break;
            case 'completion_notification':
                    checkPermisionAjax('manage_user_documents');

                    $this->send_completion_notification((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'));

                    break;
            case 'rejection_notification':
                    checkPermisionAjax('manage_user_documents');

                    $this->send_rejection_notification((int) arrayGet($_POST, 'user'), (int) arrayGet($_POST, 'document'));

                    break;
            // case 'completion_email':
            //     checkPermisionAjax('manage_user_documents');

            //     $this->send_completion_email((int) arrayGet($_POST, 'user'));

            //     break;
            // case 'rejection_email':
            //     checkPermisionAjax('manage_user_documents');

            //     $this->send_rejection_email((int) arrayGet($_POST, 'user'));

            //     break;
            case 'send_emails':
                checkPermisionAjax('manage_user_documents');

                $this->send_emails();

                break;

            default:
                jsonResponse(translate('sysmtess_provided_path_not_found'));

                break;
        }
    }

    public function ajax_users_list_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('manage_user_documents');

        //region User information
        /** @var Countries_Model $countriesModel */
        $countriesModel = model(Countries_Model::class);
        $countriesTable = $countriesModel->getTable();

        /** @var Cities_Model $citiesModel */
        $citiesModel = model(Cities_Model::class);
        $citiesTable = $citiesModel->getTable();

        /** @var User_Groups_Model $userGroupsModel */
        $userGroupsModel = model(User_Groups_Model::class);
        $userGroupsTable = $userGroupsModel->getTable();

        /** @var Users_Calling_Statuses_Model $usersCallingStatusesModel */
        $usersCallingStatusesModel = model(Users_Calling_Statuses_Model::class);
        $usersCallingStatusesTable = $usersCallingStatusesModel->getTable();

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $request = request()->request;

        $usersQueryBuilderParams = [
            'conditions'    => array_merge(
                [
                    'withoutActiveCancellationRequests' => true,
                    'isVerified'                        => false,
                    'groupTypes'                        => [
                        'Buyer',
                        'Seller',
                        'Shipper',
                    ],
                ],
                dtConditions(
                    $request->all(),
                    [
                        ['as' => 'accreditationFilesUploadDateGte',         'key' => 'upload_date_from',        'type' => fn ($date) => validateDate($date, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null],
                        ['as' => 'accreditationFilesUploadDateLte',         'key' => 'upload_date_to',          'type' => fn ($date) => validateDate($date, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null],
                        ['as' => 'registrationDateGte',                     'key' => 'reg_date_from',           'type' => fn ($date) => validateDate($date, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null],
                        ['as' => 'registrationDateLte',                     'key' => 'reg_date_to',             'type' => fn ($date) => validateDate($date, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null],
                        ['as' => 'resendEmailDateGte',                      'key' => 'resend_date_from',        'type' => fn ($date) => validateDate($date, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null],
                        ['as' => 'resendEmailDateLte',                      'key' => 'resend_date_to',          'type' => fn ($date) => validateDate($date, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null],
                        ['as' => 'verificationProgress',                    'key' => 'upload_status',           'type' => fn ($status) => VerificationUploadProgress::tryFrom((string) $status)],
                        ['as' => 'hasCompletedLocation',                    'key' => 'location_completion',     'type' => 'bool'],
                        ['as' => 'callingDateGte',                          'key' => 'calling_from_date',       'type' => fn ($date) => validateDate($date, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null],
                        ['as' => 'callingDateLte',                          'key' => 'calling_to_date',         'type' => fn ($date) => validateDate($date, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $date) : null],
                        ['as' => 'callingStatus',                           'key' => 'calling_status',          'type' => 'int'],
                        ['as' => 'crmContactId',                            'key' => 'crm_contact_id',          'type' => 'int'],
                        ['as' => 'emailStatus',                             'key' => 'email_status',            'type' => fn ($emailStatus) => EmailStatus::tryFrom((string) $emailStatus)],
                        ['as' => 'group',                                   'key' => 'group',                   'type' => 'int'],
                        ['as' => 'isLogged',                                'key' => 'online',                  'type' => 'bool'],
                        ['as' => 'countryId',                               'key' => 'country',                 'type' => 'int'],
                        ['as' => 'id',                                      'key' => 'id_user',                 'type' => 'int'],
                        ['as' => 'status',                                  'key' => 'status',                  'type' => fn ($userStatus) => UserStatus::tryFrom((string) $userStatus)],
                        ['as' => 'keywords',                                'key' => 'search',                  'type' => 'cut_str:200'],
                    ]
                )
            ),
            'columns'       => [
                "`{$usersTable}`.*",
                "`{$countriesTable}`.`country` as country_name",
                "`{$citiesTable}`.`city` as city_name",
                "`{$userGroupsTable}`.`gr_name`",
                "`{$userGroupsTable}`.`gr_type`",
                "`{$usersCallingStatusesTable}`.`status_title`",
                "`{$usersCallingStatusesTable}`.`status_description`",
                "`{$usersCallingStatusesTable}`.`status_color`",
            ],
            'order'         => \array_column(
                \dtOrdering(
                    $request->all(),
                    [
                        'dt_resent_verification'    => "`{$usersTable}`.`resend_accreditation_email`",
                        'dt_resend_email_date'      => "`{$usersTable}`.`resend_email_date`",
                        'dt_last_upload_date'       => "`{$usersTable}`.`accreditation_files_upload_date`",
                        'dt_reg_date'               => "`{$usersTable}`.`registration_date`",
                        'dt_country'                => "`{$countriesTable}`.`country`",
                        'dt_status'                 => "`{$usersTable}`.`status`",
                        'dt_email'                  => "`{$usersTable}`.`email`",
                        'dt_group'                  => "`{$usersTable}`.`user_group`",
                        'dt_user'                   => "`{$usersTable}`.`fname`",
                        'dt_id'                     => "`{$usersTable}`.`idu`",
                    ],
                ),
                'direction',
                'column',
            ) ?: ["`{$usersTable}`.`idu`" => 'DESC'],
            'joins'         => [
                'userGroups',
                'userCallingStatuses',
                'countries',
                'cities',
            ],
            'limit'         => abs($request->getInt('iDisplayLength')),
            'skip'          => abs($request->getInt('iDisplayStart')),
        ];

        $users = $usersModel->runWithoutAllCasts(fn () => $usersModel->findAllBy($usersQueryBuilderParams));
        $users_count = $usersModel->countBy(array_intersect_key($usersQueryBuilderParams, ['conditions' => '', 'joins' => '']));
        //endregion User information

        //region Auxiliary information
        $shippers = [];
        $companies = [];
        $sellersIds = array_column(array_filter($users, function ($user) { return 'Seller' === $user['gr_type']; }), 'idu');
        $shippersIds = array_column(array_filter($users, function ($user) { return 'Shipper' === $user['gr_type']; }), 'idu');
        $bills_counts = [];
        $country_codes = array_map(
            function ($codes) { return array_column((array) $codes, 'ccode'); },
            arrayByKey(array_filter((array) model('country')->get_ccodes()), 'id_country', true)
        );

        if (!empty($sellersIds)) {
            /** @var Seller_Companies_Model $sellerCompaniesModel */
            $sellerCompaniesModel = model(Seller_Companies_Model::class);

            $companies = array_column(
                (array) $sellerCompaniesModel->runWithoutAllCasts(
                    fn () => $sellerCompaniesModel->findAllBy([
                        'conditions' => [
                            'usersIds' => $sellersIds,
                        ],
                    ])
                ),
                null,
                'id_user'
            );
        }

        if (!empty($shippersIds)) {
            /** @var Shipper_Companies_Model $shipperCompaniesModel */
            $shipperCompaniesModel = model(Shipper_Companies_Model::class);

            $shippers = array_column(
                (array) $shipperCompaniesModel->runWithoutAllCasts(
                    fn () => $shipperCompaniesModel->findAllBy([
                        'conditions' => [
                            'usersIds' => $shippersIds,
                        ],
                    ])
                ),
                null,
                'id_user'
            );
        }

        if (!empty($users)) {
            $bills_counts = arrayByKey(
                array_filter((array) model('user_bills')->get_bills_counts_by_user([
                    'id_users' => implode(',', array_column($users, 'idu')),
                ])),
                'id_user'
            );
        }
        $custom_locations = arrayByKey(
            model(Custom_Locations_Model::class)->find_by_principals(array_map('intval', array_column($users, 'id_principal'))),
            'id_principal'
        );
        $email_status_labels = model('user')->get_emails_status_labels();
        //endregion Auxiliary information

        jsonResponse(null, 'success', [
            'sEcho'                => $request->getInt('sEcho'),
            'aaData'               => $this->get_users_list($users, $companies, $shippers, $bills_counts, $country_codes, $email_status_labels, $custom_locations),
            'iTotalRecords'        => $users_count,
            'iTotalDisplayRecords' => $users_count,
        ]);
    }

    private function show_verification_page(array $user)
    {
        //region Vars
        //region Document
        $user_id = (int) $user['idu'];
        $principal_id = (int) $user['id_principal'];
        $prepare_document = function (array $document): array {
            if (null !== $document['latest_version']) {
                $document['latest_version'] = VersionSerializerStatic::deserialize($document['latest_version'], VersionInterface::class, 'json');
            }

            $document['metadata'] = $this->getVersionMetadata($document['latest_version']);
            $document['title'] = translate('personal_documents_unknown_document_title');
            $document['description'] = '&mdash;';
            if (!empty($document['type'])) {
                if (null !== $title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title'])) {
                    $document['title'] = $title;
                }
                if (null !== $description = accreditation_i18n($document['type']['document_i18n'], 'description', null, $document['type']['document_description'])) {
                    $document['description'] = $description;
                }
                $document_titles = json_decode($document['type']['document_titles'], true);
                if (null !== $country_title = $document_titles[session()->country]) {
                    $document['country_title'] = $country_title;
                }
            }

            return $document;
        };

        list($documents, $other_documents) = (
            new ArrayCollection((array) model('user_personal_documents')->get_documents([
                'with'       => ['type', 'owner'],
                'order'      => ['id_type' => 'ASC'],
                'conditions' => [
                    'principal' => (int) $user['id_principal'],
                ],
            ]))
        )
            ->filter(function ($document) { return $document; })
            ->map($prepare_document)
            ->partition(function ($i, $document) use ($user_id) { return ((int) $document['id_user']) === $user_id; })
        ;

        $types = array_map('intval', array_column($documents->getValues(), 'id_type'));
        $other_documents = (
            new ArrayCollection(arrayByKey(
                $other_documents
                    ->filter(function (array $document) use ($types) { return in_array((int) $document['id_type'], $types); })
                    ->filter(function (array $document) {
                        return null !== $document['versions']
                            && !$document['metadata']['is_version_rejected']
                            && !$document['metadata']['is_expired'];
                    })
                    ->getValues(),
                'id_type',
                true
            ))
        )->map(function (array $documents) { return new ArrayCollection($documents); });
        //endregion Document

        //region Upgrade
        $id_group = (int) $user['user_group'];
        $upgrade_bill = null;
        $upgrade_package = null;
        if (
            !empty($upgrade_request = model('upgrade')->get_latest_request([
                'conditions' => [
                    'user'   => (int) $user['idu'],
                    'status' => ['new'],
                ],
            ]))
            && 'upgrade' == $upgrade_request['type']
        ) {
            $upgrade_package = model('packages')->getGrPackage((int) $upgrade_request['id_package']);
            $upgrade_bill = model('user_bills')->get_simple_bill([
                'id_bill' => $upgrade_request['id_bill'],
            ]);

            $id_group = (int) $upgrade_package['gr_to'];
        }
        //endregion Upgrade

        //region Group
        $groups = array_column(model(UserGroup_Model::class)->getGroups(), 'gr_name', 'idgroup');
        $group = tap(model('usergroup')->getGroup($id_group), function (&$group) {
            $group['name'] = $group['gr_name'];
            $group['user_guide'] = model('user_personal_documents')->get_userguide_by_group($group['gr_alias']);
            $group['thumbnail'] = __IMG_URL . getImage("public/img/groups/{$group['stamp_pic']}", thumbNoPhoto($group['idgroup']));
        });
        //endregion Group

        //region Industries
        $industries = [];
        if ('Seller' == $user['gr_type']) {
            $industries = with(
                model('company')->get_seller_industries((int) $user['idu']),
                function ($industries) {
                    return null !== $industries ? (is_string($industries) ? array_filter(explode(',', $industries)) : $industries) : [];
                }
            );
        }
        //endregion Industries

        //region Verification
        $is_verified = filter_var($user['is_verified'], FILTER_VALIDATE_BOOLEAN);
        $is_verifying = in_array($user['verfication_upload_progress'], ['partial', 'none']);
        $show_upload_placeholders = $documents->filter(function ($document) {
            return null === $document['versions']
                || $document['metadata']['is_version_pending']
                || $document['metadata']['is_version_rejected']
                || $document['metadata']['is_expiring_soon']
                || $document['metadata']['is_expired'];
        })->count() > 0;
        //endregion Verification

        $current_package = $upgrade_benefits = $upgrade_packages = null;
        if (
            !$is_verified
            && null === $upgrade_package
            && 'Seller' == $user['gr_type']
        ) {
            //region Upgrade packages
            $upgradePackagesParams = [
                'gr_from'     => $id_group,
                'is_disabled' => 0,
            ];

            if (filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN)) {
                $upgradePackagesParams['period'] = 5;
                $upgradePackagesParams['is_disabled'] = 1;
            }
            $upgrade_packages = model('packages')->getGrPackages($upgradePackagesParams);
            //endregion Upgrade packages

            //region Upgrade benefits
            $prepare_benefit_groups = function ($str) {
                $str['benefit_groups'] = explode(',', $str['benefit_groups']);

                return $str;
            };

            $upgrade_groups = array_column($upgrade_packages, 'gr_to');
            $upgrade_groups[] = $id_group;

            $upgrade_benefits = array_map($prepare_benefit_groups, model('upgrade')->get_upgrade_benefits([
                'id_group' => $upgrade_groups,
                'order_by' => 'benefit_weight ASC, benefit_groups ASC',
            ]));
            //endregion Upgrade benefits

            $current_package = model('packages')->getGrPackageByCondition(['gr_from' => 0, 'gr_to' => $id_group]);
        }

        //region Packages
        // $packages = null;
        // if (!$is_verified && null === $upgrade_package) {
        //     $packages = array_filter(
        //         (array) model('packages')->getGrPackages(
        //                     [
        //                         'gr_from'     => $group['idgroup'],
        //                         'is_disabled' => 0, 'group_by' => ['p.gr_to']
        //                     ]
        //                 )
        //     );

        //     foreach ($packages as &$package) {
        //         $package['image'] = __IMG_URL . getImage("public/img/groups/new/{$package['gt_stamp_pic']}", thumbNoPhoto($group['gr_to']));
        //     }
        // }
        //endregion Packages

        //region Misc vars
        $notifications = arrayByKey(model('user')->get_notification_messages(['message_module' => 'accreditation']), 'id_message');
        $statuses = array_merge($this->getVersionStatusesMetadata(), $this->getVersionExpirationMetadata());
        //endregion Misc vars
        //endregion Vars

        $data = compact(
            'group',
            'groups',
            'statuses',
            'packages',
            'documents',
            'is_verified',
            'is_verifying',
            'upgrade_bill',
            'notifications',
            'upgrade_package',
            'upgrade_request',
            'other_documents',
            'show_upload_placeholders',
            'upgrade_benefits',
            'current_package',
            'upgrade_packages',
            'id_group',
        );

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->verificationEpl($data);
        } else {
            $this->verificationAll($data);
        }
    }

    private function verificationEpl($data)
    {
        $data['templateViews'] = [
            'mainOutContent'    => 'verification/my/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function verificationAll($data)
    {
        views(['new/header_view', 'new/verification/my/index_view', 'new/footer_view'], $data);
    }

    private function show_user_verification_documents_popup($user_id)
    {
        //region User access
        if (
            empty($user_id) || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            messageInModal(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User access

        //region Vars
        //region User
        $user['full_name'] = trim("{$user['fname']} {$user['lname']}");
        //endregion User

        //region Document
        $documents = array_map(
            function ($document) {
                $document['title'] = translate('personal_documents_unknown_document_title');
                $document['versions'] = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionCollectionInterface::class, 'json');
                $document['latest_version'] = VersionSerializerStatic::deserialize(arrayGet($document, 'latest_version'), VersionInterface::class, 'json');
                $document = array_merge($document, $this->getVersionMetadata($document['latest_version']));
                if (!empty($document['type'])) {
                    if (null !== $title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title'])) {
                        $document['title'] = $title . (!empty($document['subtitle']) ? ' (' . cleanOutput($document['subtitle']) . ')' : '');
                    }
                }

                return $document;
            },
            array_filter(
                (array) model('user_personal_documents')->get_documents([
                    'with'       => ['type'],
                    'order'      => ['id_document' => 'ASC'],
                    'conditions' => [
                        'user' => (int) $user['idu'],
                    ],
                ])
            )
        );
        //endregion Document

        //region Verification
        $is_verified = filter_var($user['is_verified'], FILTER_VALIDATE_BOOLEAN);
        $is_verifying = in_array($user['verfication_upload_progress'], ['partial', 'none']);
        //endregion Verification

        //region Country
        $country_id = (int) $user['country'];
        //endregion Country

        //region Group
        $group_id = (int) $user['user_group'];
        $group = tap(model('usergroup')->getGroup($group_id), function (&$group) {
            $group['name'] = $group['gr_name'];
            $group['thumbnail'] = __IMG_URL . getImage("public/img/groups/new/{$group['stamp_pic']}", thumbNoPhoto($group['idgroup']));
        });
        //endregion Group

        //region Industries
        $industries = [];
        if ('Seller' == $user['gr_type']) {
            $industries = with(
                model('company')->get_seller_industries((int) $user['idu']),
                function ($industries) {
                    return null !== $industries ? (is_string($industries) ? array_filter(explode(',', $industries)) : $industries) : [];
                }
            );
        }
        //endregion Industries

        //region Upgrade packages
        $is_upgrading = false;
        if (!empty($upgrade_request = model('upgrade')->get_latest_request([
            'with'       => ['package'],
            'conditions' => [
                'user'           => (int) $user_id,
                'status'         => ['new'],
                'is_not_expired' => true,
            ],
        ]))) {
            $is_upgrading = true;
            if (!empty($package = arrayGet($upgrade_request, 'package'))) {
                $group_id = (int) arrayGet($package, 'gr_to', $group_id);
            }
        }
        //endregion Upgrade packages

        //region Additional documents
        /** @var Verification_Document_Types_Model */
        $verificationTypes = model(Verification_Document_Types_Model::class);
        $ruleBuilder = $verificationTypes->getRelationsRuleBuilder();
        $additional_documents = $verificationTypes->findAllBy([
            'scopes' => array_filter(['exclude' => array_column($documents, 'id_type')]),
            'exists' => array_filter([
                !empty($group_id) ? $ruleBuilder->whereHas(
                    'groupsReference',
                    function (QueryBuilder $builder, RelationInterface $relation) use ($group_id) {
                        $relation->getRelated()->getScope('userGroup')($builder, $group_id);
                    }
                ) : null,
                !empty($country_id) ? $ruleBuilder->whereHas(
                    'countriesReference',
                    function (QueryBuilder $builder, RelationInterface $relation) use ($country_id) {
                        $relation->getRelated()->getScope('country')($builder, $country_id);
                    }
                ) : null,
                !empty($industries) ? $ruleBuilder->whereHas(
                    'industriesReference',
                    function (QueryBuilder $builder, RelationInterface $relation) use ($industries) {
                        $relation->getRelated()->getScope('industries')($builder, $industries);
                    }
                ) : null,
            ]),
        ]);
        //endregion Additional documents

        //region temporary functionality
        $recovered_info = empty($user['documents_info']) ? null : json_decode($user['documents_info'], true);
        //endregion temporary functionality

        //region Misc vars
        $action = getUrlForGroup('personal_documents/ajax_operation/add_documents');
        $modal_refernce = $this->makeUriReferenceQuery(
            'verification_popup',
            "/verification/popup_forms/user_verification_documents/{$user_id}",
            'View verification documents',
            [],
            [],
            true,
            trim("Verification documents for {$user['fname']} {$user['lname']}")
        );
        $notifications = array_map(
            function ($notification) {
                return [
                    'text'  => arrayGet($notification, 'message_text'),
                    'title' => arrayGet($notification, 'message_title'),
                ];
            },
            arrayByKey(model('user')->get_notification_messages(['message_module' => 'accreditation']), 'id_message')
        );
        $statuses = array_merge($this->getVersionStatusesMetadata(), $this->getVersionExpirationMetadata());
        $resourceOptions = ResourceOptions::fromRaw(ResourceType::from(ResourceType::ACCREDITATION), '1');
        //endregion Misc vars
        //endregion Vars

        views('admin/verification/user_documents_view', compact(
            'btnChat',
            'user',
            'group',
            'action',
            'statuses',
            'documents',
            'is_verified',
            'is_upgrading',
            'is_verifying',
            'notifications',
            'recovered_info',
            'modal_refernce',
            'resourceOptions',
            'additional_documents',
        ));
    }

    /**
     * Shows the popup where manager can edit custom fields.
     */
    private function show_custom_fields_edit_popup(Request $request, ?int $user_id, ?int $document_id): void
    {
        //region Information
        try {
            /** @var AbstractVersion $latest */
            list('latest' => $latest) = $this->resolve_document_information($user_id, $document_id);
        } catch (UserNotFoundException $exception) {
            messageInModal(translate('systmess_error_user_does_not_exist'));
        } catch (DocumentNotFoundException $exception) {
            messageInModal(translate('systmess_error_document_does_not_exist'));
        } catch (SerializeException $exception) {
            messageInModal(translate('systmess_deserialization_error_for_verification_documents'));
        }
        //endregion Information

        //region Check fields
        if (
            !$latest->getContext()->has(ContentContextEntries::REQUIRES_DYNAMIC_FIELDS)
            || !$latest->getContext()->has(ContentContextEntries::REQUIRES_DYNAMIC_FIELDS)
            || empty($latest->getContext()->get(ContentContextEntries::DYNAMIC_FIELDS_NAMES_LIST))
        ) {
            messageInModal(translate('systmess_document_version_additional_fields_error'));
        }
        //endregion Check fields

        views()->display('admin/verification/edit_custom_fields_form', [
            'is_dialog' => $request->query->getInt('dialog') ?: false,
            'document'  => $document_id,
            'version'   => $latest,
            'action'    => getUrlForGroup('/verification/ajax_operations/edit_custom_fields'),
            'user'      => $user_id,
        ]);
    }

    /**
     * Shows the popup where email can be sent to the user.
     *
     * @param int $user_id
     */
    private function show_emails_popup($user_id)
    {
        //region User
        if (
            empty($user_id) || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            messageInModal(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Templates
        $templateCall = new GroupEmailTemplates();
        $templates = $templateCall->getVerificationTemplates([
            'group_type'   => $user['gr_type'],
            'list_of_keys' => [
                'confirm_email',
                'epl_confirm_email',
                'verification_documents_remind',
                'complete_profile_remind',
                'add_products_remind',
            ],
        ]);
        //endregion Templates

        views('admin/verification/send_emails_view', compact(
            'user',
            'templates'
        ));
    }

    /**
     * Complete user verification.
     *
     * @param int $user_id
     */
    private function complete_verification($user_id)
    {
        //region User access
        if (
            empty($user_id)
            || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }

        if (filter_var(arrayGet($user, 'is_verified', false), FILTER_VALIDATE_BOOLEAN)) {
            jsonResponse(translate('systmess_verify_again_error'));
        }
        //endregion User access

        //region Misc vars
        $lang_code = arrayGet($user, 'user_initial_lang_code', 'en');
        //endregion Misc vars

        //region Documents
        $found_documents = array_map(
            function ($document) use ($lang_code) {
                $document['title'] = translate('personal_documents_unknown_document_title');
                $document['latest_version'] = VersionSerializerStatic::deserialize(arrayGet($document, 'latest_version'), VersionInterface::class, 'json');
                if (!empty($document['type'])) {
                    if (null !== $title = accreditation_i18n($document['type']['document_i18n'], 'title', $lang_code, $document['type']['document_title'])) {
                        $document['title'] = $title;
                    }
                }

                return $document;
            },
            array_filter((array) model('user_personal_documents')->get_documents([
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        );

        //region Accepted documents
        $accepted_documents = array_filter($found_documents, function ($document) {
            $metadata = $this->getVersionMetadata($document['latest_version']);

            return $metadata['is_version_accepted'] && !$metadata['is_expired'];
        });
        if (empty($accepted_documents) || count($accepted_documents) !== count($found_documents)) {
            jsonResponse(translate('systmess_error_user_not_pass_verification_process'));
        }
        //endregion Accepted documents
        //endregion Documents

        //region Update
        if (!model('user')->updateUserMain($user_id, ['is_verified' => 1, 'verification_documents_date' => date('Y-m-d H:i:s')])) {
            jsonResponse(translate('systmess_failed_to_update_info'));
        }

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->sync((int) $user_id);
        //endregion Update

        //region Update auxiliary records
        model('user')->set_notice($user_id, [
            'add_by'   => 'System',
            'notice'   => 'The verification has been completed.',
            'add_date' => date('Y/m/d H:i:s'),
        ]);
        //endregion Update auxiliary records

        //region Update profile completion
        model('complete_profile')->update_user_profile_option($user_id, 'account_verification');

        /** @var TinyMVC_Library_Auth $authenticationLibrary */
        $authenticationLibrary = library(TinyMVC_Library_Auth::class);
        $authenticationLibrary->setUserCompleteProfile((int) $user_id);
        //endregion Update profile completion

        // Wake up, Neo
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserWasVerifiedEvent((int) $user_id));

        //region Update Activity Log
        $fullname = "{$user['fname']} {$user['lname']}";
        $context = array_merge(
            [
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

        $this->activity_logger->setResourceType(USER);
        $this->activity_logger->setOperationType(ADMIN_VERIFICATION_USER);
        $this->activity_logger->setResource($user_id);
        $this->activity_logger->info(model('activity_log_messages')->get_message(USER, ADMIN_VERIFICATION_USER), $context);
        //endregion Update Activity Log

        jsonResponse(translate('systmess_success_accreditation_confirmed'), 'success');
    }

    /**
     * Retches all uplaoded to the EP Docs files for user.
     */
    private function fetch_user_files(int $user_id): void
    {
        //region User
        if (null === $user_id || empty($user = model(User_Model::class)->getSimpleUser($user_id))) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        //endregion Resources

        //region API interactions
        try {
            $user = $users->findUser($this->getUserApiContext($user_id));
            if (null === $user) {
                throw new UserNotFoundException(translate('systmess_user_id_not_found_epdocs_error'));
            }

            $user_files = $files->getUserFiles(
                $user->getId()
            );
        } catch (\Exception $exception) {
            jsonResponse(translate('systmess_failed_fetch_documents_error'), 'error', withDebugInformation(
                [],
                ['exception' => throwableToArray($exception)]
            ));
        }
        //endregion API interactions

        jsonResponse(
            null,
            'success',
            [
                'preview' => views()->fetch('admin/verification/file_list_view', [
                    'files' => $user_files,
                    'user'  => [
                        'id' => $user_id,
                    ],
                ]),
            ],
        );
    }

    /**
     * Removes from the EP Docs user file.
     */
    private function remove_user_file(int $user_id, string $file_id): void
    {
        //region User
        if (null === $user_id || empty(model(User_Model::class)->getSimpleUser($user_id))) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        /** @var FilePermissionsResource $file_permissions */
        $file_permissions = $client->getResource(FilePermissionsResource::class);
        //endregion Resources

        //region API interactions
        try {
            $user = $users->findUser($this->getUserApiContext($user_id));
            if (null === $user) {
                throw new UserNotFoundException(translate('systmess_user_id_not_found_epdocs_error'));
            }

            $file = (new FileObject())->setId(Uuid::fromString($file_id));
            if ($files->hasFile($file->getId())) {
                if (!$file_permissions->hasPermissions($file->getId(), $user->getId(), FilePermissionsResource::PERMISSION_WRITE)) {
                    throw new AccessDeniedException("The user with ID '{$user->getId()}' has no access to the file '{$file->getId()}'.");
                }
                $files->deleteFile($file->getId());
            }
        } catch (InvalidUuidStringException $exception) {
            jsonResponse(translate('systmess_file_id_invalid_error'), 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (UserNotFoundException $exception) {
            jsonResponse(translate('systmess_user_id_not_found_epdocs_error'), 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (AccessDeniedException $exception) {
            jsonResponse(translate('systmess_file_not_accesible_to_user_error'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($exception),
            ]));
        } catch (\Exception $exception) {
            jsonResponse(translate('systmess_failed_delete_file_epdocs_error'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($exception),
            ]));
        }
        //endregion API interactions

        jsonResponse(translate('systmess_succes_document_deleted'), 'success');
    }

    /**
     * Returns the access token from the EP Docs user file.
     */
    private function accesss_user_file(int $user_id, string $file_id): void
    {
        //region User
        if (null === $user_id || empty(model(User_Model::class)->getSimpleUser($user_id))) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Resources
        $client = $this->getApiClient();
        /** @var UserResource $users */
        $users = $client->getResource(UserResource::class);
        /** @var FileResource $files */
        $files = $client->getResource(FileResource::class);
        /** @var FilePermissionsResource $file_permissions */
        $file_permissions = $client->getResource(FilePermissionsResource::class);
        /** @var AccessTokenResource $access_tokens */
        $access_tokens = $client->getResource(AccessTokenResource::class);
        //endregion Resources

        //region API interactions
        try {
            $user = $users->findUser($this->getUserApiContext($user_id));
            if (null === $user) {
                throw new UserNotFoundException('The user with such ID is not found in EP Docs.');
            }

            $file = $files->getFile(Uuid::fromString($file_id));
            if (!$file_permissions->hasPermissions($file->getId(), $user->getId(), FilePermissionsResource::PERMISSION_READ)) {
                throw new AccessDeniedException("The user with ID '{$user->getId()}' has no access to the file '{$file->getId()}'.");
            }

            $access_token = $access_tokens->createToken($file->getId(), 90);
        } catch (InvalidUuidStringException $exception) {
            jsonResponse('The file ID is invalid', 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (UserNotFoundException $exception) {
            jsonResponse('The user with such ID IS not found.', 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (\App\Plugins\EPDocs\NotFoundException $exception) {
            jsonResponse('The file with such ID is not found.', 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (AccessDeniedException $exception) {
            jsonResponse("The file is not accessible for user and probably doesn't belong to him.", 'error', withDebugInformation([], [
                'exception' => throwableToArray($exception),
            ]));
        } catch (\Exception $exception) {
            jsonResponse('Failed to download the file from EPDocs.', 'error', withDebugInformation([], [
                'exception' => throwableToArray($exception),
            ]));
        }
        //endregion API interactions

        jsonResponse(null, 'success', [
            'token' => [
                'url'      => config('env.EP_DOCS_HOST', 'http://localhost') . $access_token->getPath(),
                'name'     => "{$file->getName()}.{$file->getExtension()}",
                'filename' => sprintf('%s_%s.%s', orderNumber($user_id), strForURL($file->getName()), $file->getExtension()),
            ],
        ]);
    }

    /**
     * Edit custom fields.
     */
    private function edit_custom_fields(Request $request, ?int $user_id, ?int $document_id): void
    {
        //region Information
        try {
            /** @var VersionCollectionInterface $versions */
            list('versions' => $versions, 'latest' => $latest, 'user' => $user) = $this->resolve_document_information($user_id, $document_id);
        } catch (UserNotFoundException $exception) {
            jsonResponse(translate('systmess_error_user_does_not_exist'), 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (DocumentNotFoundException $exception) {
            jsonResponse(translate('systmess_error_document_does_not_exist'), 'error', withDebugInformation([], ['exception' => throwableToArray($exception)]));
        } catch (SerializeException $exception) {
            jsonResponse(
                'Operation stopped due to deserialization error. Please try again later or contact the developers to resolve this problem.',
                'error',
                withDebugInformation([], ['exception' => throwableToArray($exception)])
            );
        }
        //endregion Information

        //region Check fields
        /** @var AbstractVersion $latest */
        if (
            !$latest->getContext()->has(ContentContextEntries::REQUIRES_DYNAMIC_FIELDS)
            || !$latest->getContext()->has(ContentContextEntries::REQUIRES_DYNAMIC_FIELDS)
            || empty($fields = $latest->getContext()->get(ContentContextEntries::DYNAMIC_FIELDS_NAMES_LIST))
        ) {
            jsonResponse("This document version doesn't support additional fields.");
        }
        //endregion Check fields

        //region Validation
        $legacy_validator = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new AggregateValidator(
            array_map(
                function (callable $builder) use ($legacy_validator) { return $builder($legacy_validator); },
                array_intersect_key(
                    [
                        static::CUSTOM_FIELDS_BUSINESS_NUMBER_FIELD_KEY => function (ValidatorAdapter $adapter) {
                            return new BusinessNumberValidator(
                                $adapter,
                                null,
                                null,
                                [
                                    static::CUSTOM_FIELDS_BUSINESS_NUMBER_FIELD_KEY => static::CUSTOM_FIELDS_BUSINESS_NUMBER_REQUEST_KEY,
                                ]
                            );
                        },
                    ],
                    array_flip($fields)
                )
            )
        );

        if (!$validator->validate($request->request->all())) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        //region Saving
        // Prepare field savers
        $savers = [
            static::CUSTOM_FIELDS_BUSINESS_NUMBER_FIELD_KEY => function (ParameterBag $data) use ($user, $user_id) {
                switch (mb_strtolower($user['gr_type'])) {
                    case 'seller':
                        if ($company_id = model(Company_Model::class)->get_seller_base_company($user_id)['id_company'] ?? null) {
                            model(Company_Model::class)->update_company_business_number(
                                $company_id,
                                $data->get(static::CUSTOM_FIELDS_BUSINESS_NUMBER_REQUEST_KEY)
                            );
                        }

                        break;
                    case 'buyer':
                        if ($company_id = model(Company_Buyer_Model::class)->get_company_by_user($user_id)['id'] ?? null) {
                            model(Company_Buyer_Model::class)->update_company_business_number(
                                $company_id,
                                $data->get(static::CUSTOM_FIELDS_BUSINESS_NUMBER_REQUEST_KEY)
                            );
                        }

                        break;
                }
            },
        ];

        // Store fields data into context
        $latest->getContext()->set(ContentContextEntries::DYNAMIC_FIELDS_STORED_VALUES, array_map(
            function (string $key) use ($request) { return $request->request->get($key) ?? null; },
            array_intersect_key(
                [static::CUSTOM_FIELDS_BUSINESS_NUMBER_FIELD_KEY => static::CUSTOM_FIELDS_BUSINESS_NUMBER_REQUEST_KEY],
                array_flip($fields)
            )
        ));

        try {
            if (null === ($versions = VersionSerializerStatic::serialize($versions, 'json'))) {
                throw new SerializeException('Failed to serialize the versions');
            }

            array_walk(array_intersect_key($savers, array_flip($fields)), function (callable $saver) use ($request) {
                $saver($request->request);
            });
            model(User_Personal_Documents_Model::class)->update_document($document_id, ['versions' => $versions]);
        } catch (SerializeException $exception) {
            jsonResponse(
                'Operation stopped due to deserialization error. Please try again later or contact the developers to resolve this problem.',
                'error',
                withDebugInformation([], ['exception' => throwableToArray($exception)])
            );
        } catch (Exception $exception) {
            jsonResponse('Failed to write custom fields', 'error', withDebugInformation(
                [],
                [
                    'exception' => throwableToArray($exception), ]
            ));
        }
        //endregion Saving

        jsonResponse('The custom fields have been successfully saved', 'success');
    }

    /**
     * Sends notifcation to the user about upload of the documents.
     *
     * @param int   $user_id
     * @param mixed $document_id
     */
    private function send_upload_notification($user_id, $document_id)
    {
        //region Suppress notifications if needed
        if (filter_var(config('env.SUPPRESS_DOCUMENT_NOTIFICATIONS', false), FILTER_VALIDATE_BOOLEAN)) {
            jsonResponse('The notification about document upload is suppressed.', 'success');
        }
        //endregion Suppress notifications if needed

        //region User
        if (
            empty($user_id)
            || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Document
        if (
            empty($document_id)
            || null === ($document = model('user_personal_documents')->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Vars
        $is_verified = filter_var($user['is_verified'], FILTER_VALIDATE_BOOLEAN);
        $user_group = snakeCase($user['gr_type']);
        $link = $is_verified
            ? getUrlForGroup('/personal_documents', $user_group)
            : getUrlForGroup('/verification', $user_group);
        $document_title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title']);
        //endregion Vars

        //region Notifications
        model('notify')->send_notify([
            'systmess'  => true,
            'mess_code' => 'verification_documents_upload',
            'id_users'  => [$user_id],
            'replace'   => [
                '[LINK]'          => $link,
                '[DOCUMENT]'      => orderNumber($document_id),
                '[DOCUMENT_NAME]' => cleanOutput($document_title),
            ],
        ]);
        //endregion Notifications

        jsonResponse('The notification about document upload is sent to user', 'success');
    }

    /**
     * Sends notifcation to the user about assignment of the documents.
     *
     * @param int $user_id
     */
    private function send_assignment_notification($user_id, array $documents = [])
    {
        //region Suppress notifications if needed
        if (filter_var(config('env.SUPPRESS_DOCUMENT_NOTIFICATIONS', false), FILTER_VALIDATE_BOOLEAN)) {
            jsonResponse('The notification about document assignment is suppressed.', 'success');
        }
        //endregion Suppress notifications if needed

        //region User
        if (
            empty($user_id)
            || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Documents
        $list = array_map(function ($document) { return (int) $document; }, $documents);
        /** @var Verification_Document_Types_Model $verificationDocumentTypes */
        $verificationDocumentTypes = model(Verification_Document_Types_Model::class);
        $assigned_documents = $verificationDocumentTypes->findAllBy([
            'scopes' => [
                'include' => $list,
            ],
        ]);

        if (count($assigned_documents) !== count($documents)) {
            jsonResponse('At least one of the documents is not found.');
        }
        if (empty(model('user_personal_documents')->get_documents([
            'conditions' => [
                'user'  => $user_id,
                'types' => $list,
            ],
        ]))) {
            jsonResponse('One or more documents are not assigned to the user.');
        }
        //endregion Documents

        //region Vars
        $is_verified = filter_var($user['is_verified'], FILTER_VALIDATE_BOOLEAN);
        $user_group = snakeCase($user['gr_type']);
        $link = $is_verified
            ? getUrlForGroup('/personal_documents', $user_group)
            : getUrlForGroup('/verification', $user_group);
        //endregion Vars

        //region Notifications
        model('notify')->send_notify([
            'systmess'  => true,
            'mess_code' => 'verification_documents_assignment',
            'id_users'  => [$user_id],
            'replace'   => [
                '[LINK]' => $link,
            ],
        ]);
        //endregion Notifications

        jsonResponse('The notification about document assignment is sent to user', 'success');
    }

    /**
     * Sends notifcation to the user about assignment of the documents.
     *
     * @param int $user_id
     * @param int $document_id
     */
    private function send_confirmation_notification($user_id, $document_id)
    {
        //region Suppress notifications if needed
        if (filter_var(config('env.SUPPRESS_DOCUMENT_NOTIFICATIONS', false), FILTER_VALIDATE_BOOLEAN)) {
            jsonResponse('The notification about document confirmation is suppressed.', 'success');
        }
        //endregion Suppress notifications if needed

        //region User
        if (
            empty($user_id)
            || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Document
        if (
            empty($document_id)
            || null === ($document = model('user_personal_documents')->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Vars
        $is_verified = filter_var($user['is_verified'], FILTER_VALIDATE_BOOLEAN);
        $user_group = snakeCase($user['gr_type']);
        $link = $is_verified
            ? getUrlForGroup('/personal_documents', $user_group)
            : getUrlForGroup('/verification', $user_group);
        $document_title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title']);
        //endregion Vars

        //region Notifications
        model('notify')->send_notify([
            'systmess'  => true,
            'mess_code' => 'verification_documents_confirmation',
            'id_users'  => [$user_id],
            'replace'   => [
                '[LINK]'          => $link,
                '[DOCUMENT]'      => orderNumber($document_id),
                '[DOCUMENT_NAME]' => cleanOutput($document_title),
            ],
        ]);
        //endregion Notifications

        jsonResponse('The notification about document confirmation is sent to user', 'success');
    }

    /**
     * Sends notifcation to the user about rejection of the document.
     *
     * @param int $user_id
     * @param int $document_id
     */
    private function send_rejection_notification($user_id, $document_id)
    {
        //region Suppress notifications if needed
        if (filter_var(config('env.SUPPRESS_DOCUMENT_NOTIFICATIONS', false), FILTER_VALIDATE_BOOLEAN)) {
            jsonResponse('The notification about document rejection is suppressed.', 'success');
        }
        //endregion Suppress notifications if needed

        //region User
        if (
            empty($user_id)
            || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Document
        if (
            empty($document_id)
            || null === ($document = model('user_personal_documents')->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            jsonResponse(translate('systmess_error_document_does_not_exist'));
        }
        //endregion Document

        //region Vars
        $is_verified = filter_var($user['is_verified'], FILTER_VALIDATE_BOOLEAN);
        $user_group = snakeCase($user['gr_type']);
        $link = $is_verified
            ? getUrlForGroup('/personal_documents', $user_group)
            : getUrlForGroup('/verification', $user_group);
        $document_title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title']);
        //endregion Vars

        //region Notifications
        model('notify')->send_notify([
            'systmess'  => true,
            'mess_code' => 'verification_documents_rejection',
            'id_users'  => [$user_id],
            'replace'   => [
                '[LINK]'          => $link,
                '[DOCUMENT]'      => orderNumber($document_id),
                '[DOCUMENT_NAME]' => cleanOutput($document_title),
            ],
        ]);
        //endregion Notifications

        jsonResponse('The notification about document rejection is sent to user', 'success');
    }

    /**
     * Sends notifcation to the user about completion of the verification.
     *
     * @param int $user_id
     */
    private function send_completion_notification($user_id)
    {
        //region Suppress notifications if needed
        if (filter_var(config('env.SUPPRESS_DOCUMENT_NOTIFICATIONS', false), FILTER_VALIDATE_BOOLEAN)) {
            jsonResponse('The notification about verification completion is suppressed.', 'success');
        }
        //endregion Suppress notifications if needed

        //region User
        if (
            empty($user_id)
            || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion User

        //region Vars
        $is_verified = filter_var($user['is_verified'], FILTER_VALIDATE_BOOLEAN);
        $user_group = snakeCase($user['gr_type']);
        $link = $is_verified
            ? getUrlForGroup('/personal_documents', $user_group)
            : getUrlForGroup('/verification', $user_group);
        //endregion Vars

        //region Notifications
        model('notify')->send_notify([
            'systmess'  => true,
            'mess_code' => 'verification_completion',
            'id_users'  => [$user_id],
            'replace'   => [
                '[LINK]' => $link,
            ],
        ]);
        //endregion Notifications

        jsonResponse('The notification about verification completion is sent to user', 'success');
    }

    /**
     * Sends the email notifications using specified template.
     */
    private function send_emails()
    {
        //region Validation
        $validator_rules = [
            [
                'field' => 'email_template',
                'label' => 'Email template',
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'users',
                'label' => 'User(s) info',
                'rules' => ['required' => ''],
            ],
        ];

        $this->validator->set_rules($validator_rules);
        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }
        //endregion Validation

        //region Users
        $list = array_filter(
            array_map(function ($user) { return (int) $user; }, arrayGet($_POST, 'users', []))
        );
        if (empty($list) || empty($users = model('user')->getSimpleUsers(implode(',', $list)))) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
        //endregion Users

        //region Template
        $templateKey = cleanInput($_POST['email_template']);
        $templateCall = new GroupEmailTemplates();
        $templateData = $templateCall->getVerificationTemplate($templateKey);

        if (empty($templateKey) || empty($templateData)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        //endregion Template

        //region Send mails
        foreach ($users as $user) {
            $userId = (int) $user['idu'];
            $userGroup = arrayGet($user, 'gr_type', null);
            $allowedAccess = arrayGet($templateData, 'restrict_gr_access', []);

            if (null === $userGroup || !in_array($userGroup, $allowedAccess)) {
                continue;
            }

            $email = arrayGet($user, 'email');

            //region Send email
            $templateCall->sentEmailTemplate($templateData['template_name'], [
                'userId'        => $userId,
                'email'         => $email,
                'userName'      => "{$user['fname']} {$user['lname']}",
                'token'         => $user['confirmation_token'],
            ]);
            //endregion Send email

            //region Update history
            model(User_Model::class)->set_notice($userId, [
                'add_date' => date('Y/m/d H:i:s'),
                'add_by'   => user_name_session(),
                'notice'   => "\"{$templateData['title']}\" email has been sent.",
            ]);
            //endregion Update history

            //region Update user
            model(User_Model::class)->updateUserMain($userId, [
                'resend_accreditation_email' => arrayGet($user, 'resend_accreditation_email', 0) + 1,
                'resend_email_date'          => date('Y-m-d H:i:s'),
            ]);
            //endregion Update user
        }
        //endregion Send mails

        jsonResponse(translate('systmess_success_email_has_been_sent'), 'success');
    }

    /**
     * Returns the list of the users formated for DT.
     */
    private function get_users_list(
        array $users,
        array $companies = [],
        array $shippers = [],
        array $bills_counts = [],
        array $country_phone_codes = [],
        array $email_status_labels = [],
        array $custom_locations = []
    ) {
        $output = [];
        $libPhoneUtils = PhoneNumberUtil::getInstance();
        foreach ($users as $user) {
            //region Vars
            $status = $user['status'];
            $user_id = (int) $user['idu'];
            $group_id = (int) $user['user_group'];
            $group_name = cleanOutput($user['gr_name']);
            $principal_id = (int) $user['id_principal'] ?: null;
            $user_raw_name = trim("{$user['fname']} {$user['lname']}");
            $user_group_type = strtolower($user['gr_type']);
            $email_status = $user['email_status'] ?? null;
            $user_name = cleanOutput($user_raw_name);
            $is_shipper = 'shipper' === $user['user_type'];
            $is_logged = isset($user['logged']) && filter_var($user['logged'], FILTER_VALIDATE_BOOLEAN);
            $is_active = 'active' === $user['status'];
            //endregion Vars

            //region User
            //region Online/offline
            $online_status = $is_logged ? 'online' : 'offline';
            $online_status_text = ucfirst($online_status);
            $online_status_color = $is_logged ? 'txt-green' : 'txt-red';
            $user_online_filter = "
                <a class=\"ep-icon ep-icon_onoff {$online_status_color} dt_filter\"
                    title=\"Filter just {$online_status}\"
                    data-value=\"{$user['logged']}\"
                    data-value-text=\"{$online_status_text}\"
                    data-title=\"OnLine/OffLine\"
                    data-name=\"online\">
                </a>
            ";
            //endregion Online/offline

            //region Personal page
            if ($is_shipper) {
                $user_personal_page_url = getShipperURL([
                    'id'      => arrayGet($shippers, "{$user_id}.id"),
                    'co_name' => arrayGet($shippers, "{$user_id}.co_name"),
                ]);
            } else {
                $user_personal_page_url = getUserLink($user_raw_name, $user_id, $user_group_type);
            }
            $user_personal_page = "
                <a class=\"ep-icon ep-icon_user\"
                    href=\"{$user_personal_page_url}\"
                    title=\"View personal page of {$user_name}\"
                    target=\"_blank\">
                </a>
            ";
            //endregion Personal page

            //region Contact
            $contact_url = getUrlForGroup("contact/popup_forms/email_user/{$user_id}");
            $user_contact_button = "
                <a class=\"ep-icon ep-icon_envelope fancyboxValidateModal fancybox.ajax\"
                    href=\"{$contact_url}\"
                    title=\"Email this user\" data-title=\"Email user {$user_name}\">
                </a>
            ";
            //endregion Contact

            $user_information = "
                <div class=\"tal\">
                    {$user_online_filter}
                    {$user_personal_page}
                    {$user_contact_button}
                </div>
                <div>{$user_name}</div>
            ";
            //endregion User

            //region Group
            $user_group = "
                <div class=\"tal\">
                    <a class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                        title=\"Group {$group_name}\"
                        data-value=\"{$group_id}\"
                        data-name=\"group\"
                        data-title=\"Group\"
                        data-value-text=\"{$group_name}\">
                    </a>
                </div>
                {$group_name}
            ";
            //endregion Group

            //region Photo
            if ($is_shipper) {
                $photo_name = arrayGet($shippers, "{$user_id}.logo");
                if (!empty($photo_name)) {
                    $photo_url = getShipperLogo($user_id, $photo_name, 0);
                } else {
                    $photo_url = thumbNoPhoto($group_id);
                }
            } else {
                $photo_url = getUserAvatar($user_id, arrayGet($user, 'user_photo'), arrayGet($user, 'user_group'), 0);
            }
            $user_photo = "
                <img class=\"mw-75 mh-75\" src=\"{$photo_url}\" alt=\"{$user_name}\"/>
            ";
            //endregion Photo

            //region Company
            $user_company = '&mdash;';
            if (in_array($user['gr_type'], ['Seller', 'Shipper'])) {
                if ($is_shipper) {
                    $shipper_url = $user_personal_page_url;
                    $shipper_name = cleanOutput(arrayGet($shippers, "{$user_id}.co_name"));
                    $user_company = "
                        {$user_photo}
                        <a href=\"{$shipper_url}\" target=\"_blank\">
                            {$shipper_name}
                        </a>
                    ";
                } else {
                    $company_url = getCompanyURL(arrayGet($companies, "{$user_id}"));
                    $company_name = cleanOutput(arrayGet($companies, "{$user_id}.name_company"));
                    $company_rating = cleanOutput(arrayGet($companies, "{$user_id}.rating_company"));
                    $company_logo_url = getCompanyLogo(arrayGet($companies, "{$user_id}.id_company"), arrayGet($companies, "{$user_id}.logo_company"), 0);
                    $user_company = "
                        <img class=\"pull-left mw-75 mh-75\" src=\"{$company_logo_url}\" alt=\"{$company_name}\"/>
                        <a target=\"{$company_url}\">{$company_name}</a> | Rating: {$company_rating}
                    ";
                }
            }
            //endregion Company

            //region Status
            $status_title = capitalWord($status);
            $user_status = "
                <div class=\"tal\">
                    <a class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                        title=\"Filter just {$status_title}\"
                        data-value-text=\"{$status_title}\"
                        data-value=\"{$status}\"
                        data-name=\"status\"
                        data-title=\"Status\">
                    </a>
                </div>
                <div>{$status_title}</div>
            ";
            //endregion Status

            //region Details
            $user_details = "
                <br>{$user_id}<br/>
                <a rel=\"user_details\" title=\"View details\" class=\"mt-10 ep-icon ep-icon_plus\"></a>
            ";
            //endregion Details

            //region Call status
            $user_call_status = "
                <div class=\"tal\">
                    <a class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                        title=\"Filter just {$user['status_title']}\"
                        data-value-text=\"{$user['status_title']}\"
                        data-value=\"{$user['calling_status']}\"
                        data-name=\"calling_status\"
                        data-title=\"Calling status\">
                    </a>
                </div>
                <div>
                    <i class=\"ep-icon ep-icon_support fs-30\"
                        title=\"{$user['status_title']}\"
                        style=\"color: {$user['status_color']};\"></i>
                </div>
            ";
            //endregion Call status

            //region Country
            $user_country = '&mdash;<br><span class="label label-danger">Incompleted location</span>';
            if (!empty($user['country'])) {
                $country_id = arrayGet($user, 'country');
                $country_name = cleanOutput(arrayGet($user, 'country_name'));
                $country_flag = null;
                if (null !== $country_name) {
                    $country_flag_url = getCountryFlag(arrayGet($user, 'country_name'));
                    $country_codes = isset($country_phone_codes[$country_id]) ? implode(', ', (array) $country_phone_codes[$country_id]) : '';
                    $country_title = trim("{$country_name} {$country_codes}");
                    $country_flag = "
                        <img width=\"24\" height=\"24\" src=\"{$country_flag_url}\" title=\"{$country_title}\" alt=\"{$country_name}\">
                    ";
                }

                $location_warning = null;
                if (empty($user['state']) || empty($user['city'])) {
                    $location_warning = <<<'WARNING'
                    <br><span class="label label-danger">Incompleted location</span>
                    WARNING;
                }

                $user_country = "
                    <div class=\"tal\">
                        <a class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                            title=\"Filter by {$country_name}\"
                            data-value-text=\"{$country_name}\"
                            data-value=\"{$country_id}\"
                            data-title=\"Country\"
                            data-name=\"country\">
                        </a>
                        {$country_flag}
                        {$location_warning}
                    </div>
                ";
            }
            //endregion Country

            //region Full address
            $address_parts = array_filter([
                arrayget($user, 'address'),
                arrayget($user, 'zip'),
                arrayget($user, 'city_name'),
                arrayget($user, 'country_name'),
            ]);
            $user_full_address = '&mdash;';
            if (!empty($address_parts)) {
                $user_full_address = implode(', ', $address_parts);
            }
            //endregion Full address

            //region Phone & fax
            $user_phone_and_fax = '&mdash;';
            if (!empty($user['phone']) || !empty($user['fax'])) {
                $user_fax = null;
                $user_phone = null;
                if (!empty($user['fax'])) {
                    $user_phone = 'Fax: ' . trim("{$user['fax_code']} {$user['fax']}");
                }
                if (!empty($user['phone'])) {
                    try {
                        //try to convert phone number to the international format
                        $phoneNumber = $libPhoneUtils->parse("{$user['phone_code']} {$user['phone']}");
                        $userPhone = $libPhoneUtils->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
                    } catch (NumberParseException $e) {
                        $userPhone = "{$user['phone_code']} {$user['phone']}";
                    }

                    $user_phone = "Phone: {$userPhone}";
                }

                $user_phone_and_fax = implode('|', array_filter([$user_phone, $user_fax]));
            }
            //endregion Phone & fax

            //region Bills
            $user_bills = '&mdash;';
            if (isset($bills_counts[$user_id])) {
                $bills_url = getUrlForGroup("billing/popup_forms/user_bills/{$user_id}");
                $all_bills_count = (int) arrayGet($bills_counts, "{$user_id}.counter_all");
                $new_bills_count = (int) arrayGet($bills_counts, "{$user_id}.counter_init");
                $paid_bills_count = (int) arrayGet($bills_counts, "{$user_id}.counter_paid");
                $confirmed_bills_count = (int) arrayGet($bills_counts, "{$user_id}.counter_confirmed");
                $unvalidated_bills_count = (int) arrayGet($bills_counts, "{$user_id}.counter_unvalidated");
                $user_bills = "
                    <div class=\"tal\">
                        <div>
                            <a href=\"{$bills_url}\" class=\"fancybox fancybox.ajax\" data-title=\"All bills\">
                                All: <strong class=\"txt-red\">{$all_bills_count}</strong>
                            </a>
                        </div>
                        <div>
                            <a href=\"{$bills_url}/init\" class=\"fancybox fancybox.ajax\" data-title=\"New bills\">
                                New: <strong class=\"txt-red\">{$new_bills_count}</strong>
                            </a>
                        </div>
                        <div>
                            <a href=\"{$bills_url}/paid\" class=\"fancybox fancybox.ajax\" data-title=\"Paid bills\">
                                Paid: <strong class=\"txt-red\">{$paid_bills_count}</strong>
                            </a>
                        </div>
                        <div>
                            <a href=\"{$bills_url}/confirmed\" class=\"fancybox fancybox.ajax\" data-title=\"Confirmed bills\">
                                Confirmed: <strong class=\"txt-red\">{$confirmed_bills_count}</strong>
                            </a>
                        </div>
                        <div>
                            <a href=\"{$bills_url}/unvalidated\" class=\"fancybox fancybox.ajax\" data-title=\"Cancelled bills\">
                                Cancelled: <strong class=\"txt-red\">{$unvalidated_bills_count}</strong>
                            </a>
                        </div>
                    </div>
                ";
            }
            //endregion Bills

            //region Actions
            //region Resend button
            $resend_button_url = getUrlForGroup("verification/popup_forms/emails/{$user_id}");
            $resend_button = '<li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" ' . addQaUniqueIdentifier('admin-users__datatable__verification-dropdown__send-button') . ' data-fancybox-href="' . $resend_button_url . '" data-title="Send emails to ' . $user_name . '" title="Send emails to ' . $user_name . '">
                                    <span class="ep-icon ep-icon_envelope-send"></span> Send email
                                </a>
                            </li>';
            //endregion Resend button

            //region Documents button
            $documents_button_url = getUrlForGroup("verification/popup_forms/user_verification_documents/{$user_id}");
            $documents_button = '<li>
                                    <a class="fancyboxValidateModalDT fancybox.ajax" href="' . $documents_button_url . '" data-title="Verification documents for ' . $user_name . '" title="View verification documents" ' . addQaUniqueIdentifier('admin-users__datatable__verification-dropdown__verification-button') . '>
                                        <span class="ep-icon ep-icon_items"></span> Verification documents
                                    </a>
                                </li>';
            //endregion Documents button

            //region Notices button
            $notices_button_url = getUrlForGroup("users/popup_show_notice/{$user_id}");
            $notices_button = '<li>
                                    <a class="fancyboxValidateModalDT fancybox.ajax" ' . addQaUniqueIdentifier('admin-users__datatable__verification-dropdown__notice-button') . ' href="' . $notices_button_url . '" data-title="Notices for ' . $user_name . '" title="Notices">
                                        <span class="ep-icon ep-icon_notice"></span> Notices
                                    </a>
                                </li>';
            //endregion Notices button

            //region Call notices button
            $call_notices_bitton_url = getUrlForGroup("users/popup_forms/calling_notices/{$user_id}");
            $call_notices_bitton = '<li>
                                        <a class="fancyboxValidateModalDT fancybox.ajax" ' . addQaUniqueIdentifier('admin-users__datatable__verification-dropdown__calling-button') . ' href="' . $call_notices_bitton_url . '" data-title="Calling status and notice ' . $user_name . '" title="Calling notices">
                                            <span class="ep-icon ep-icon_support"></span> Calling notices
                                        </a>
                                    </li>';
            //endregion Call notices button

            //region Delete user button
            $delete_user_button = '';
            if ('new' == $user['status']) {
                $delete_user_button = '<li>
                                            <a class="confirm-dialog" ' . addQaUniqueIdentifier('admin-users__datatable__verification-dropdown__delete-button') . ' href="' . $call_notices_bitton_url . '" data-callback="delete_user" data-user="' . $user_id . '" title="Delete user" data-message="Are you sure you want to Delete user: ' . $user_name . '">
                                                <span class="ep-icon ep-icon_remove txt-red"></span> Delete user
                                            </a>
                                        </li>';
            }
            //endregion Delete user button

            $user_actions = '<div class="dropdown">
                                <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown" ' . addQaUniqueIdentifier('admin-users__datatable__verification-dropdown-toggle-button') . '></a>
                                <ul class="dropdown-menu dropdown-menu-right">'
                                    . $resend_button
                                    . $documents_button
                                    . $notices_button
                                    . $call_notices_bitton
                                    . $delete_user_button
                                . '</ul>
                            </div>';
            //endregion Actions

            //region Email status label
            $email_status_label = null;
            if (null !== $email_status) {
                $email_status_name = cleanOutput($email_status);
                $email_status_lebel_class = $email_status_labels[$email_status] ?? 'error';
                $email_status_label = <<<STATUS_LABEL
                    <br>
                    <span class="label label-{$email_status_lebel_class}" title="Email status: {$email_status_name}">
                        {$email_status_name}
                    </span>
                STATUS_LABEL;
            }
            //endregion Email status label

            //region Custom location
            $user_custom_location = '&mdash;';
            if (!empty($custom_location = $custom_locations[$principal_id]['location'] ?? null)) {
                $user_custom_location = cleanOutput($custom_location);
            }
            //endregion Custom location

            $output[] = [
                'dt_id'                  => $user_details,
                'dt_user'                => $user_information,
                'dt_email'               => cleanOutput($user['email'] ?? null) ?: '&mdash;' . $email_status_label,
                'dt_group'               => $user_group,
                'dt_photo'               => $user_photo,
                'dt_status'              => $user_status,
                'dt_company'             => $user_company,
                'dt_country'             => $user_country,
                'dt_phone_fax'           => $user_phone_and_fax,
                'dt_full_address'        => $user_full_address,
                'dt_calling_status'      => $user_call_status,
                'dt_custom_location'     => $user_custom_location,
                'dt_bills'               => $user_bills,
                'dt_reg_date'            => getDateFormatIfNotEmpty($user['registration_date']),
                'dt_calling_date'        => getDateFormatIfNotEmpty($user['calling_date_last']),
                'dt_resend_email_date'   => getDateFormatIfNotEmpty($user['resend_email_date']),
                'dt_last_upload_date'    => getDateFormatIfNotEmpty($user['accreditation_files_upload_date']),
                'dt_resent_verification' => (int) arrayGet($user, 'resend_accreditation_email', 0),
                'dt_actions'             => $user_actions,
            ];
        }

        return $output;
    }

    /**
     * Resoves information about document.
     */
    private function resolve_document_information(?int $user_id, ?int $document_id): array
    {
        //region User
        if (null === $user_id || empty($user = model(User_Model::class)->getSimpleUser($user_id))) {
            throw new UserNotFoundException($user_id);
        }
        //endregion User

        //region Document
        if (
            empty($document_id)
            || null === ($document = model(User_Personal_Documents_Model::class)->get_document($document_id, [
                'with'       => ['type'],
                'conditions' => [
                    'user' => $user_id,
                ],
            ]))
        ) {
            throw new DocumentNotFoundException($document_id);
        }
        //endregion Document

        //region Versions
        /** @var VersionList $versions */
        $versions = VersionSerializerStatic::deserialize($document['versions'] ?? null, VersionList::class, 'json');
        if (null === $versions) {
            throw new SerializeException('The deserialization of the document version failed.');
        }

        /** @var AbstractVersion $latest */
        $latest = $versions->last();
        //endregion Versions

        return compact('user', 'document', 'versions', 'latest');
    }
}

// End of file verification.php
// Location: /tinymvc/myapp/controllers/verification.php
