<?php

declare(strict_types=1);

use App\Common\Contracts\Document\DocumentTypeCategory;
use App\Common\Contracts\Group\GroupType;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\AlreadyExistsException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\ValidationException;
use App\Validators\VerificationDocumentTypeLocaleValidator;
use App\Validators\VerificationDocumentTypeValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller Verification_Document_Types.
 */
class Verification_Document_Types_Controller extends TinyMVC_Controller
{
    /**
     * Index page.
     */
    public function index(): Response
    {
        return new RedirectResponse(getUrlForGroup('/verification_document_types/administration'));
    }

    /**
     * Shows administration page for document types.
     */
    public function administration(): void
    {
        checkAdmin('moderate_content,manage_user_documents');

        /** @var Locales_Model $localesRepository */
        $localesRepository = model(Locales_Model::class);
        views(['admin/header_view', 'admin/verification_document_types/index_view', 'admin/footer_view'], [
            'title'      => 'Document types',
            'addUrl'     => getUrlForGroup('/verification_document_types/popup_forms/add'),
            'categories' => $this->getCategoriesDisplayData(),
            'languages'  => $localesRepository->findAllBy([
                'scopes' => [
                    'exclude' => ['en'],
                ],
            ]),
        ]);
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkDomainForGroup();
        checkPermisionAjaxModal('manage_content,manage_user_documents');

        try {
            switch (uri()->segment(3)) {
                case 'add':
                    checkPermisionAjaxModal('moderate_content');

                    $this->showAddDocumentTypePopup(
                        model(Seller_Company_Types_Model::class),
                        model(User_Groups_Model::class),
                        model(Countries_Model::class),
                        model(Categories_Model::class),
                        model(Locales_Model::class)
                    );

                    break;
                case 'edit':
                    checkPermisionAjaxModal('moderate_content');

                    $this->showEditDocumentTypePopup(
                        model(Verification_Document_Types_Model::class),
                        model(Seller_Company_Types_Model::class),
                        model(User_Groups_Model::class),
                        model(Countries_Model::class),
                        model(Categories_Model::class),
                        model(Locales_Model::class),
                        (int) uri()->segment(4) ?: null
                    );

                    break;
                case 'add_localization':
                    checkPermisionAjaxModal('moderate_content,manage_translations');

                    $this->showAddDocumentTypeLocalePopup(
                        model(Verification_Document_Types_Model::class),
                        model(Locales_Model::class),
                        (int) uri()->segment(4) ?: null
                    );

                    break;
                case 'edit_localization':
                    checkPermisionAjaxModal('moderate_content,manage_translations');

                    $this->showEditDocumentTypeLocalePopup(
                        model(Verification_Document_Types_Model::class),
                        model(Locales_Model::class),
                        (int) uri()->segment(4) ?: null,
                        (int) uri()->segment(5) ?: null
                    );

                    break;

                default:
                    messageInModal(translate('systmess_error_route_not_found', null, true));

                    break;
            }
        } catch (NotFoundException $e) {
            messageInModal(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        case 0: return translate('systmess_error_document_does_not_exist');
                        case 1: return translate('systmess_error_accreditation_document_id_not_found');
                        case 2: return translate('systmess_error_accreditation_document_i18n_not_found');
                        case 3: return translate('systmess_error_lang_id_not_found');

                        default: return translate('systmess_error_invalid_data', null, true);
                    }
                }
            )));
        } catch (AccessDeniedException | OwnershipException $e) {
            messageInModal(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_permission_not_granted', null, true);
                    }
                }
            )));
        }
    }

    /**
     * Executes actions on verification documents by AJAX.
     */
    public function ajax_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkDomainForGroup();
        checkPermisionAjax('manage_content,manage_user_documents');

        $request = request();
        $action = uri()->segment(3);

        try {
            switch ($action) {
                case 'list':
                    $this->showDocumentTypesList(
                        model(Verification_Document_Types_Model::class),
                        model(Locales_Model::class),
                        $request,
                        $request->request->getInt('start') ?: $request->request->getInt('iDisplayStart') ?: 0,
                        $request->request->getInt('length') ?: $request->request->getInt('iDisplayLength') ?: 10,
                        'legacy' === $request->query->get('mode')
                    );

                    break;
                case 'add':
                    $this->addDocumentType(
                        $request,
                        model(Verification_Document_Types_Model::class),
                        model(Categories_Model::class),
                        model(Countries_Model::class)
                    );

                    break;
                case 'edit':
                    $this->editDocumentType(
                        $request,
                        model(Verification_Document_Types_Model::class),
                        model(Countries_Model::class),
                        model(Categories_Model::class),
                        (int) uri()->segment(4) ?: null
                    );

                    break;
                case 'delete':
                    $this->deleteDocumentType(model(Verification_Document_Types_Model::class), (int) uri()->segment(4) ?: null);

                    break;
                case 'add_localization':
                    checkPermisionAjaxModal('moderate_content,manage_translations');

                    $this->addDocumentTypeLocale(
                        $request,
                        model(Verification_Document_Types_Model::class),
                        model(Locales_Model::class),
                        (int) uri()->segment(4) ?: null
                    );

                    break;
                case 'edit_localization':
                    checkPermisionAjaxModal('moderate_content,manage_translations');

                    $this->editDocumentTypeLocale(
                        $request,
                        model(Verification_Document_Types_Model::class),
                        model(Locales_Model::class),
                        (int) uri()->segment(4) ?: null,
                        (int) uri()->segment(5) ?: null
                    );

                    break;

                default:
                    jsonResponse(translate('sysmtess_provided_path_not_found'));

                    break;
            }
        } catch (NotFoundException $e) {
            jsonResponse(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        case 0: return translate('systmess_error_document_does_not_exist');
                        case 1: return translate('systmess_error_accreditation_document_id_not_found');
                        case 2: return translate('systmess_error_accreditation_document_i18n_not_found');
                        case 3: return translate('systmess_error_lang_id_not_found');

                        default: return translate('systmess_error_invalid_data', null, true);
                    }
                }
            )));
        } catch (AlreadyExistsException $e) {
            jsonResponse(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_accreditation_document_i18n_exists', null, true);
                    }
                }
            )));
        } catch (AccessDeniedException | OwnershipException $e) {
            jsonResponse(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_permission_not_granted', null, true);
                    }
                }
            )));
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (\Throwable $exception) {
            jsonResponse($exception->getMessage(), 'error', withDebugInformation(
                [],
                ['exception' => throwableToArray($exception)]
            ));
        }
    }

    /**
     * Shows the popup where the document type can be added.
     */
    private function showAddDocumentTypePopup(
        Seller_Company_Types_Model $companyTypesRepository,
        User_Groups_Model $userGroupsRepository,
        Countries_Model $countriesRepository,
        Categories_Model $categoriesRepository,
        Locales_Model $localesRepository
    ): void {
        views()->display('admin/verification_document_types/add_document_type_view', [
            'action'        => getUrlForGroup('/verification_document_types/ajax_operation/add'),
            'groups'        => $userGroupsRepository->findAllBy(['scopes' => [
                'types' => [GroupType::BUYER(), GroupType::SELLER(), GroupType::SHIPPER()],
            ]]),
            'countries'     => $countriesRepository->findAll(),
            'languages'     => $localesRepository->findAll(),
            'industries'    => $categoriesRepository->findAllBy(['scopes' => ['isIndustry' => true], 'order' => ['name' => 'ASC']]),
            'company_types' => $companyTypesRepository->findAll(),
            'categories'    => $this->getCategoriesDisplayData(),
        ]);
    }

    /**
     * Shows the popup where the document type can be edited.
     *
     * @throws NotFoundException if document type is not found
     */
    private function showEditDocumentTypePopup(
        Verification_Document_Types_Model $documentTypesRepository,
        Seller_Company_Types_Model $companyTypesRepository,
        User_Groups_Model $userGroupsRepository,
        Countries_Model $countriesRepository,
        Categories_Model $categoriesRepository,
        Locales_Model $localesRepository,
        ?int $typeId
    ): void {
        if (
            null === $typeId
            || null === ($documentType = $documentTypesRepository->findOne($typeId))
        ) {
            throw new NotFoundException(sprintf('The document type with ID "%s" is not found', $typeId), 1);
        }

        views()->display('admin/verification_document_types/edit_document_type_view', [
            'company_types'            => $companyTypesRepository->findAll(),
            'countries'                => $countries = $countriesRepository->findAll(),
            'action'                   => getUrlForGroup("/verification_document_types/ajax_operation/edit/{$typeId}"),
            'groups'                   => $groups = $userGroupsRepository->findAllBy(['scopes' => [
                'types' => [GroupType::BUYER(), GroupType::SELLER(), GroupType::SHIPPER()],
            ]]),
            'document'                 => $documentType,
            'languages'                => $localesRepository->findAll(),
            'industries'               => $industries = $categoriesRepository->findAllBy(['scopes' => ['isIndustry' => true], 'order' => ['name' => 'ASC']]),
            'groups_all'               => count($documentType['document_groups'] ?? []) === count($groups),
            'countries_all'            => count($documentType['document_countries'] ?? []) === count($countries),
            'industries_all'           => count($documentType['document_industries'] ?? []) === count($industries),
            'groups_selected'          => $documentType['document_groups'],
            'countries_selected'       => $documentType['document_countries'],
            'industries_selected'      => $documentType['document_industries'],
            'groups_required_selected' => $documentType['document_groups_required'],
            'selected_company_types'   => $documentType['document_additional_options']['company_types'] ?? [],
            'categories'               => $this->getCategoriesDisplayData(),
        ]);
    }

    /**
     * Shows the popup where the document type locale can be added.
     */
    private function showAddDocumentTypeLocalePopup(
        Verification_Document_Types_Model $documentTypesRepository,
        Locales_Model $localesRepository,
        ?int $typeId
    ): void {
        if (
            null === $typeId
            || null === ($documentType = $documentTypesRepository->findOne($typeId))
        ) {
            throw new NotFoundException(sprintf('The document type with ID "%s" is not found', $typeId), 1);
        }

        $languages = arrayByKey(
            $localesRepository->findAllBy([
                'scopes' => [
                    'ids'     => session()->group_lang_restriction ? session()->group_lang_restriction_list ?? [] : null,
                    'exclude' => ['en'],
                ],
            ]),
            'lang_iso2'
        );
        $translations = array_keys(
            array_filter($documentType['document_i18n'] ?? [], fn (string $code) => isset($languages[$code]), ARRAY_FILTER_USE_KEY)
        );

        views()->display('admin/verification_document_types/add_document_type_localization_form_view', [
            'action'       => getUrlForGroup("/verification_document_types/ajax_operation/add_localization/{$typeId}"),
            'document'     => $documentType,
            'languages'    => arrayByKey($languages, 'id_lang'),
            'translations' => $translations,
        ]);
    }

    /**
     * Shows the popup where the document type locale can be edited.
     */
    private function showEditDocumentTypeLocalePopup(
        Verification_Document_Types_Model $documentTypesRepository,
        Locales_Model $localesRepository,
        ?int $typeId,
        ?int $localeId
    ): void {
        // Find document type.
        if (
            null === $typeId
            || null === ($documentType = $documentTypesRepository->findOne($typeId))
        ) {
            throw new NotFoundException(sprintf('The document type with ID "%s" is not found', $typeId), 1);
        }
        // Find the locale
        if (
            null === $localeId
            || null === ($locale = $localesRepository->findOne($localeId))
        ) {
            throw new NotFoundException(sprintf('The locale with ID "%s" is not found', $localeId), 3);
        }
        // Check locales in session
        if (
            session()->group_lang_restriction
            && !in_array($localeId, session()->group_lang_restriction_list ?? [])
        ) {
            throw new AccessDeniedException(sprintf('The locale with ID "%s" cannot be edited.', $localeId));
        }

        $localeCode = $locale['lang_iso2'] ?? 'en';
        $documentTypeLocales = $documentType['document_i18n'] ?? [];
        $translation = !empty($documentTypeLocales[$localeCode]['title']['value'] ?? null) ? $documentTypeLocales[$localeCode]['title'] : null;
        if (null === $translation) {
            throw new NotFoundException(sprintf('The locale with ID "%s" for document type "%s" is not found', $localeId, $typeId), 2);
        }

        views()->display('admin/verification_document_types/edit_document_type_localization_form_view', [
            'action'       => getUrlForGroup("/verification_document_types/ajax_operation/edit_localization/{$typeId}/{$localeId}"),
            'document'     => [
                'id'             => $typeId,
                'title'          => $translation['value'],
                'title_original' => $documentType['document_title'],
            ],
            'language'     => [
                'id'   => $typeId,
                'name' => $locale['lang_name'],
            ],
        ]);
    }

    /**
     * Shows the list of document types in Datatables format.
     */
    private function showDocumentTypesList(
        Verification_Document_Types_Model $documentTypesRepository,
        Locales_Model $localesRepository,
        Request $request,
        int $offset = 0,
        int $perPage = 10,
        bool $isLegacyMode = false
    ): void {
        list(
            'total' => $totalTypesCount,
            'data'  => $typeList
        ) = $documentTypesRepository->paginateForGrid(
            \dtConditions(
                $isLegacyMode ? $request->request->all() : $request->request->get('filters') ?? [],
                [
                    ['as' => 'category',    'key' => 'category', 'type' => fn (string $v) => DocumentTypeCategory::from($v)],
                    ['as' => 'hasLocale',   'key' => 'lang',     'type' => 'cleanInput'],
                    ['as' => 'hasNoLocale', 'key' => 'not_lang', 'type' => 'cleanInput'],
                    [
                        'as'   => 'originalUpdatedFromDate',
                        'key'  => 'base_update_from',
                        'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)->setTime(0, 0, 0), ],
                    [
                        'as'   => 'originalUpdatedToDate',
                        'key'  => 'base_update_to',
                        'type' => fn (string $v) => DateTimeImmutable::createFromFormat('m/d/Y', $v)->setTime(23, 59, 59), ],
                ]
            ),
            \array_column(
                \dtOrdering(
                    $request->request->all(),
                    [
                        'dt_id'     => 'id_document',
                        'dt_title'  => 'document_title',
                        'dt_update' => 'document_base_text_updated_at',
                    ],
                    null,
                    $isLegacyMode
                ),
                'direction',
                'column'
            ),
            $perPage,
            $offset / $perPage + 1,
        );
        $locales = arrayByKey($localesRepository->findAllBy(['scopes' => ['exclude' => ['en']]]), 'lang_iso2');
        $typeList = (new ArrayCollection($typeList ?? []))->map(
            fn (array $documentType) => $this->formatDocumentTypeListEntry($documentType, $locales)
        );

        jsonResponse(
            null,
            'success',
            $isLegacyMode
                ? [
                    'sEcho'                => $request->request->getInt('draw'),
                    'aaData'               => $typeList ? $typeList->toArray() : [],
                    'iTotalRecords'        => $totalTypesCount ?? 0,
                    'iTotalDisplayRecords' => $totalTypesCount ?? 0,
                ]
                : [
                    'draw'            => $request->request->getInt('draw'),
                    'data'            => $typeList ? $typeList->toArray() : [],
                    'recordsTotal'    => $totalTypesCount ?? 0,
                    'recordsFiltered' => $totalTypesCount ?? 0,
                ]
        );
    }

    /**
     * Formats the entry for the type list.
     */
    private function formatDocumentTypeListEntry(array $documentType, array $locales): array
    {
        $typeId = $documentType['id_document'];
        /** @var null|DateTimeImmutable $textUpdateDate */
        $textUpdateDate = $documentType['document_base_text_updated_at'];
        $addLocalizationUrl = getUrlForGroup("/verification_document_types/popup_forms/add_localization/{$typeId}");
        $editUrl = getUrlForGroup("/verification_document_types/popup_forms/edit/{$typeId}");

        $localizations = [];
        $usedLocalizations = [];
        foreach ($documentType['document_i18n'] ?? [] as $localeCode => $i18n) {
            if (!isset($locales[$localeCode])) {
                continue;
            }
            if (empty($i18n['title']['value'])) {
                continue;
            }

            $localeId = $locales[$localeCode]['id_lang'];
            $localeName = $locales[$localeCode]['lang_name'];
            $updateDate = null;
            $labelColor = 'btn-primary';
            $labelTitle = "Translated in language: '{$localeName}'";

            if (null !== $i18n['title']['updated_at']) {
                $updateDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $i18n['title']['updated_at'] ?? null) ?: null;
            }
            if (null !== $updateDate) {
                $labelTitle = "{$labelTitle}. Last update: {$updateDate->format('Y-m-d H:i:s')}";
            }
            if (
                null !== $textUpdateDate
                && (
                    null === $updateDate || $updateDate < $textUpdateDate
                )
            ) {
                $labelColor = 'btn-danger';
                $labelTitle = "{$labelTitle}. Update required";
            }

            $usedLocalizations[] = $localeCode;
            $localizations[] = sprintf(
                '<a href="%s"
                    class="btn btn-xs %s mnw-30 w-30 mb-5 fancyboxValidateModalDT fancybox.ajax"
                    data-title="Edit translation"
                    title="%s">
                    %s
                </a>',
                getUrlForGroup("/verification_document_types/popup_forms/edit_localization/{$typeId}/{$localeId}"),
                cleanOutput($labelColor),
                cleanOutput($labelTitle),
                cleanOutput(mb_strtoupper($localeCode))
            );
        }

        $editButton = '';
        $deleteButton = '';
        $translateButton = '';
        if (have_right_or('moderate_content')) {
            $editButton = "
                <a href=\"{$editUrl}\"
                    class=\"ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax\"
                    title=\"Edit document\"
                    data-title=\"Edit document\">
                </a>
            ";

            $deleteButton = sprintf(
                <<<'BUTTON'
                <a href="#"
                    class="ep-icon ep-icon_remove txt-red confirm-dialog"
                    title="Delete the document"
                    data-callback="delete_doc"
                    data-doc="%s"
                    data-message="%s">
                </a>
                BUTTON,
                $typeId,
                cleanOutput(translate('systmess_confirm_delete_this_document'))
            );
        }

        if (have_right('manage_translations') && !empty(array_diff_key($locales, array_flip($usedLocalizations)))) {
            $translateButton = "
                <a href=\"{$addLocalizationUrl}\"
                    class=\"ep-icon ep-icon_globe-circle fancyboxValidateModalDT fancybox.ajax fs-24\"
                    title=\"Translate document\"
                    data-title=\"Add translation\">
                </a>
            ";
        }

        return [
            'dt_id'           => $typeId,
            'dt_title'        => cleanOutput($documentType['document_title']),
            'dt_category'     => DocumentTypeCategory::getLabel($documentType['document_category']),
            'dt_translations' => implode('', $localizations),
            'dt_update'       => null !== $textUpdateDate ? $textUpdateDate->format('j M, Y H:i') : '',
            'dt_actions'      => "
                {$translateButton}
                {$editButton}
                {$deleteButton}
            ",
        ];
    }

    /**
     * Adds the new document type.
     */
    private function addDocumentType(
        Request $request,
        Verification_Document_Types_Model $documentTypesRepository,
        Categories_Model $categoriesRepository,
        Countries_Model $countriesRepository
    ): void {
        //region Validate
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new VerificationDocumentTypeValidator(
            $adapter,
            array_column($allCountries = $countriesRepository->findAll(), 'country', $countriesRepository->getPrimaryKey())
        );
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to add new document type.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        //region Add document
        //region Collect data
        $groups = (array) $request->request->get('groups', []);
        $requiredGroups = (array) $request->request->get('groups_required', []);
        $targetCountries = (array) $request->request->get('countries', []);
        $targetIndustries = (array) $request->request->get('industries', []);
        $documentTypeTitle = cleanInput($request->request->get('document_title'));
        $existingCountries = array_column($allCountries, $countriesRepository->getPrimaryKey());
        $existingIndustries = array_column($categoriesRepository->findAllBy(['scopes' => ['isIndustry' => true]]), $categoriesRepository->getPrimaryKey());
        $documentTypeTitles = array_map(
            fn (string $v) => cleanInput($v),
            array_filter((array) ($request->request->get('document_titles') ?? []), fn (?string $title) => !empty($title))
        );
        $documentType = [
            'document_title'                => $documentTypeTitle,
            'document_category'             => DocumentTypeCategory::tryFrom($request->request->get('category')),
            'document_groups'               => $groups,
            'document_countries'            => $targetCountries,
            'document_titles'               => $documentTypeTitles,
            'document_industries'           => $targetIndustries,
            'document_groups_required'      => $requiredGroups,
            'document_general_countries'    => empty(array_diff($existingCountries, $targetCountries)),
            'document_general_industries'   => empty(array_diff($existingIndustries, $targetIndustries)),
            'document_additional_options'   => !$request->request->has('company_types') ? null : [
                'company_types' => array_map(fn ($v) => (int) $v, (array) $request->request->get('company_types')),
            ],
            'document_i18n'                 => [
                'en' => [
                    'title' => [
                        'value'      => $documentTypeTitle,
                        'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                        'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                    ],
                ],
            ],
        ];
        //endregion Collect data

        //region Add record
        if (!($typeId = $documentTypesRepository->insertOne($documentType))) {
            jsonResponse(translate('systmess_error_document_type_added'));
        }

        // Create relations with the groups
        $documentTypesRepository->getRelation('groupsReference')->getRelated()->insertMany(array_map(
            fn ($groupId) => ['id_group' => (int) $groupId, 'id_document' => (int) $typeId, 'is_required' => in_array($groupId, $requiredGroups)],
            $groups ?? []
        ));
        // Create relations with the countries
        $documentTypesRepository->getRelation('countriesReference')->getRelated()->insertMany(array_map(
            fn ($countryId) => ['id_country' => (int) $countryId, 'id_document' => (int) $typeId],
            $targetCountries ?? []
        ));
        // Create relations with the industries
        $documentTypesRepository->getRelation('industriesReference')->getRelated()->insertMany(array_map(
            fn ($industryId) => ['id_industry' => (int) $industryId, 'id_document' => (int) $typeId],
            $targetIndustries ?? []
        ));
        //endregion Add record
        //endregion Add document

        jsonResponse(translate('systmess_success_document_added'), 'success');
    }

    /**
     * Edits the document type.
     */
    private function editDocumentType(
        Request $request,
        Verification_Document_Types_Model $documentTypesRepository,
        Countries_Model $countriesRepository,
        Categories_Model $categoriesRepository,
        ?int $typeId
    ): void {
        if (
            null === $typeId
            || null === ($documentType = $documentTypesRepository->findOne($typeId))
        ) {
            throw new NotFoundException(sprintf('The document type with ID "%s" is not found', $typeId), 1);
        }

        //region Validate
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new VerificationDocumentTypeValidator(
            $adapter,
            array_column($allCountries = $countriesRepository->findAll(), 'country', $countriesRepository->getPrimaryKey())
        );
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to edit document type.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        //region Edit document
        //region Collect data
        $groups = (array) $request->request->get('groups', []);
        $requiredGroups = (array) $request->request->get('groups_required', []);
        $targetCountries = (array) $request->request->get('countries', []);
        $targetIndustries = (array) $request->request->get('industries', []);
        $documentTypeTitle = cleanInput($request->request->get('document_title'));
        $existingCountries = array_column($allCountries, $countriesRepository->getPrimaryKey());
        $existingIndustries = array_column($categoriesRepository->findAllBy(['scopes' => ['isIndustry' => true]]), $categoriesRepository->getPrimaryKey());
        $documentTypeTitles = array_map(
            fn (string $v) => cleanInput($v),
            array_filter((array) ($request->request->get('document_titles') ?? []), fn (?string $title) => !empty($title))
        );
        $documentTypeUpdate = [
            'document_title'                => $documentTypeTitle,
            'document_category'             => DocumentTypeCategory::tryFrom($request->request->get('category')),
            'document_groups'               => $groups,
            'document_countries'            => $targetCountries,
            'document_titles'               => $documentTypeTitles,
            'document_i18n'                 => $documentType['document_i18n'],
            'document_industries'           => $targetIndustries,
            'document_groups_required'      => $requiredGroups,
            'document_general_countries'    => empty(array_diff($existingCountries, $targetCountries)),
            'document_general_industries'   => empty(array_diff($existingIndustries, $targetIndustries)),
            'document_additional_options'   => !$request->request->has('company_types') ? null : [
                'company_types' => array_map(fn ($v) => (int) $v, (array) $request->request->get('company_types')),
            ],
        ];
        $updateDate = new DateTimeImmutable();
        if ($documentTypeUpdate['document_title'] !== $documentType['document_title'] || empty($documentType['document_i18n']['en']['title'] ?? null)) {
            $documentTypeUpdate['document_i18n']['en']['title']['value'] = $documentTypeUpdate['document_title'];
            $documentTypeUpdate['document_i18n']['en']['title']['updated_at'] = $updateDate->format('Y-m-d H:i:s');
            if (empty($document['document_i18n']['en']['title']['created_at'])) {
                $documentTypeUpdate['document_i18n']['en']['title']['created_at'] = $updateDate->format('Y-m-d H:i:s');
            }
        }
        if ($documentTypeUpdate['document_title'] !== $documentType['document_title']) {
            $documentTypeUpdate['document_base_text_updated_at'] = $updateDate;
        }
        //endregion Collect data

        //region Add record
        if (!$documentTypesRepository->updateOne($typeId, $documentTypeUpdate)) {
            jsonResponse(translate('systmess_error_document_type_edited'));
        }

        // Create relations with the groups
        $documentTypesRepository->getRelation('groupsReference')->getRelated()->deleteAllBy(['scopes' => ['documentType' => $typeId]]);
        $documentTypesRepository->getRelation('groupsReference')->getRelated()->insertMany(array_map(
            fn ($groupId) => ['id_group' => (int) $groupId, 'id_document' => $typeId, 'is_required' => in_array($groupId, $requiredGroups)],
            $groups ?? []
        ));
        // Create relations with the countries
        $documentTypesRepository->getRelation('countriesReference')->getRelated()->deleteAllBy(['scopes' => ['documentType' => $typeId]]);
        $documentTypesRepository->getRelation('countriesReference')->getRelated()->insertMany(array_map(
            fn ($countryId) => ['id_country' => (int) $countryId, 'id_document' => $typeId],
            $targetCountries ?? []
        ));
        // Create relations with the industries
        $documentTypesRepository->getRelation('industriesReference')->getRelated()->deleteAllBy(['scopes' => ['documentType' => $typeId]]);
        $documentTypesRepository->getRelation('industriesReference')->getRelated()->insertMany(array_map(
            fn ($industryId) => ['id_industry' => (int) $industryId, 'id_document' => $typeId],
            $targetIndustries ?? []
        ));
        //endregion Add record
        //endregion Edit document

        jsonResponse(translate('systmess_success_document_updated'), 'success');
    }

    /**
     * Adds the locale for the verification document type.
     */
    private function addDocumentTypeLocale(
        Request $request,
        Verification_Document_Types_Model $documentTypesRepository,
        Locales_Model $localesRepository,
        ?int $typeId
    ): void {
        //region Validation
        // Find document type.
        if (
            null === $typeId
            || null === ($documentType = $documentTypesRepository->findOne($typeId))
        ) {
            throw new NotFoundException(sprintf('The document type with ID "%s" is not found', $typeId), 1);
        }

        // Validate the request
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new VerificationDocumentTypeLocaleValidator($adapter, $localesRepository);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to add locale for document type.', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Update
        //region New locale
        // Check locales in session
        $localeId = $request->request->get('language');
        if (
            session()->group_lang_restriction
            && !in_array($localeId, session()->group_lang_restriction_list ?? [])
        ) {
            throw new AccessDeniedException(sprintf('The locale with ID "%s" cannot be edited.', $localeId));
        }

        // Get Locale
        $locale = $localesRepository->findOne($localeId);
        // Add locale to the list
        $localeCode = $locale['lang_iso2'] ?? 'en';
        $documentTypeLocales = $documentType['document_i18n'] ?? [];
        $translation = !empty($documentTypeLocales[$localeCode]['title']['value'] ?? null) ? $documentTypeLocales[$localeCode]['title'] : null;
        if (null !== $translation) {
            throw new AlreadyExistsException(sprintf('The locale with ID "%s" for document type "%s" is already exists.', $localeId, $typeId), 2);
        }
        $documentTypeLocales[$localeCode] = [
            'title' => [
                'value'      => trim(cleanInput($request->request->get('title'))),
                'created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
            'lang' => [
                'id'        => $localeId,
                'abbr_iso2' => $localeCode,
                'lang_name' => $locale['lang_name'],
            ],
        ];
        //endregion New locale

        if (
            !$documentTypesRepository->updateOne(
                $typeId,
                [
                    'document_i18n'                   => $documentTypeLocales,
                    'document_translation_updated_at' => new DateTimeImmutable(),
                ]
            )
        ) {
            jsonResponse(translate('systmess_error_accreditation_document_i18n_insert'));
        }
        //endregion Update

        jsonResponse(translate('systmess_success_accreditation_document_i18n_insert'), 'success');
    }

    /**
     * Edits the locale for the verification document type.
     */
    private function editDocumentTypeLocale(
        Request $request,
        Verification_Document_Types_Model $documentTypesRepository,
        Locales_Model $localesRepository,
        ?int $typeId,
        ?int $localeId
    ): void {
        //region Validation
        // Find document type.
        if (
            null === $typeId
            || null === ($documentType = $documentTypesRepository->findOne($typeId))
        ) {
            throw new NotFoundException(sprintf('The document type with ID "%s" is not found', $typeId), 1);
        }
        // Find the locale
        if (
            null === $localeId
            || null === ($locale = $localesRepository->findOne($localeId))
        ) {
            throw new NotFoundException(sprintf('The locale with ID "%s" is not found', $localeId), 3);
        }

        // Validate the request
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new VerificationDocumentTypeLocaleValidator($adapter);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to edit locale for document type.', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Update
        //region Update locale
        // Check locales in session
        if (
            session()->group_lang_restriction
            && !in_array($localeId, session()->group_lang_restriction_list ?? [])
        ) {
            throw new AccessDeniedException(sprintf('The locale with ID "%s" cannot be edited.', $localeId));
        }

        // Check locale
        $localeCode = $locale['lang_iso2'] ?? 'en';
        $documentTypeLocales = $documentType['document_i18n'] ?? [];
        $translation = !empty($documentTypeLocales[$localeCode]['title']['value'] ?? null) ? $documentTypeLocales[$localeCode]['title'] : null;
        if (null === $translation) {
            throw new NotFoundException(sprintf('The locale with ID "%s" for document type "%s" is not found', $localeId, $typeId), 2);
        }

        $documentTypeLocales[$localeCode]['title']['value'] = trim(cleanInput($request->request->get('title')));
        $documentTypeLocales[$localeCode]['title']['updated_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $documentTypeLocales[$localeCode]['lang'] = [
            'id'        => $localeId,
            'abbr_iso2' => $localeCode,
            'lang_name' => $locale['lang_name'],
        ];
        //endregion Update locale

        if (
            !$documentTypesRepository->updateOne(
                $typeId,
                [
                    'document_i18n'                   => $documentTypeLocales,
                    'document_translation_updated_at' => new DateTimeImmutable(),
                ]
            )
        ) {
            jsonResponse(translate('systmess_error_accreditation_document_i18n_update'));
        }
        //endregion Update

        jsonResponse(translate('systmess_success_accreditation_document_i18n_update'), 'success');
    }

    /**
     * Deletes the document type.
     */
    private function deleteDocumentType(Verification_Document_Types_Model $documentTypesRepository, ?int $typeId): void
    {
        // The delete request are idempotent, so we don't need to fail when
        // document type is not found.
        // Only when delete process failed we need to show error.
        if (
            $documentTypesRepository->has($typeId)
            && !$documentTypesRepository->deleteOne($typeId)
        ) {
            jsonResponse('systmess_error_document_type_not_deleted');
        }

        jsonResponse(translate('systmess_succes_document_deleted'), 'success');
    }

    /**
     * Gets the categiries display data.
     */
    private function getCategoriesDisplayData(): array
    {
        return [
            [DocumentTypeCategory::PERSONAL(), DocumentTypeCategory::getLabel(DocumentTypeCategory::PERSONAL())],
            [DocumentTypeCategory::BUSINESS(), DocumentTypeCategory::getLabel(DocumentTypeCategory::BUSINESS())],
            [DocumentTypeCategory::OTHER(), DocumentTypeCategory::getLabel(DocumentTypeCategory::OTHER())],
        ];
    }
}

// End of file verification_document_types.php
// Location: /tinymvc/myapp/controllers/verification_document_types.php
