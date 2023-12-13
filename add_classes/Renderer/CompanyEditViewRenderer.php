<?php

declare(strict_types=1);

namespace App\Renderer;

use App\Common\Contracts\Entities\CountryCodeInterface;
use App\Common\Contracts\Entities\Phone\PatternsAwareInterface;
use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\Media\CompanyLogoThumb;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\AlreadyExistsException;
use App\DataProvider\AccountProvider;
use App\DataProvider\CompanyProvider;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Services\EditRequest\CompanyEditRequestDocumentsService;
use App\Services\EditRequest\CompanyEditRequestProcessingService;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use App\Services\PhoneCodesService;
use Doctrine\Common\Collections\ArrayCollection;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use TinyMVC_View as Renderer;

/**
 * The service that renders the pages and/or popups for company edit.
 *
 * @author Anton Zencenco
 */
final class CompanyEditViewRenderer extends AbstractViewRenderer
{
    use PhoneFormatterTrait;

    /**
     * The company porvider.
     */
    private CompanyProvider $companyProvider;

    /**
     * The filesystem porvider.
     */
    private FilesystemProviderInterface $filesystemProvider;

    /**
     * The edit request processor service instance.
     */
    private CompanyEditRequestProcessingService $requestProcessor;

    /**
     * The phone utils instance.
     */
    private PhoneNumberUtil $phoneUtils;

    /**
     * @param Renderer        $renderer   the page renderer
     * @param PhoneNumberUtil $phoneUtils the phone utils instance
     * @param FilesystemProviderInterface $filesystemProvider the filesystem
     */
    public function __construct(
        Renderer $renderer,
        CompanyProvider $companyProvider,
        CompanyEditRequestProcessingService $requestProcessor,
        PhoneNumberUtil $phoneUtils = null,
        FilesystemProviderInterface $filesystemProvider
    ) {
        parent::__construct($renderer);

        $this->phoneUtils = $phoneUtils ?? PhoneNumberUtil::getInstance();
        $this->companyProvider = $companyProvider;
        $this->requestProcessor = $requestProcessor;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * Renders company edit page.
     */
    public function renderEditPage(AccountProvider $accountProvider, int $companyId, array $relatedAccounts = []): void
    {
        $company = $this->companyProvider->getCompanyForEditPage($companyId);
        $userId = $company['id_user'];

        //region Categories & industries
        $categories = $this->companyProvider->getRepository()->getRelation('industries')->getRelated();
        $selectedIndustries = array_column(($company['industries'] ?? new ArrayCollection())->getValues(), null, 'category_id');
        $selectedCategories = array_column(($company['categories'] ?? new ArrayCollection())->getValues(), null, 'category_id');
        $baseIndustries = $categories->findAllBy(['scopes' => ['hasChildren' => true, 'isIndustry' => true], 'order' => ['name' => 'ASC']]);
        $baseCategories = arrayByKey(
            $categories->findAllBy(['scopes' => ['parents' => array_keys($selectedIndustries)], 'order' => ['name' => 'ASC']]),
            'parent',
            true
        );
        //endregion Categories & industries

        //region Location
        $latitude = $company['latitude'] ?? null;
        $longitude = $company['longitude'] ?? null;
        if (null === $latitude || null === $longitude) {
            // @todo Run geocode here
        }
        //endregion Location

        //region Account
        $syncWithRelatedAccounts = $company['user']['sync_with_related_accounts']['company_info'] ?? [];
        $sourceAccounts = $accountProvider->getSourceAccounts($userId, 'company_main', $sellerRelatedAccounts = \array_filter(
            $relatedAccounts,
            fn (array $account) => $account['gr_type'] === GroupType::SELLER()
        ));
        //endregion Account
        $publicDisk = $this->filesystemProvider->storage('public.storage');
        $thumbImage = $publicDisk->url(CompanyLogoFilePathGenerator::thumbImage($companyId, $company['logo_company'] ?: 'no-image.jpeg', CompanyLogoThumb::MEDIUM()));
        $logoImage = $publicDisk->url(CompanyLogoFilePathGenerator::logoPath($companyId, $company['logo_company'] ?: 'no-image.jpeg'));
        $this->renderPage('user/seller/company/edit_view', [
            // Edit flags
            'hasIndexName'     => $hasIndexName = !empty($company['index_name']) && empty($company['index_name_temp']),
            'hasEditRequest'   => $this->requestProcessor->hasPendingRequest($userId),
            'canRequestEdit'   => $this->requestProcessor->canCreateRequest($userId),
            'canHaveIndexName' => have_right('id_generated'),

            // URLs for edit
            'baseUrl'         => \getUrlForGroup(),
            'editPopupUrl'    => \getUrlForGroup('/company/popup_forms/edit'),
            'requestPopupUrl' => \getUrlForGroup('/company/popup_forms/edit?isRequest=1'),

            // Company information
            'company'         => [
                'url'         => $hasIndexName ? \getUrlForGroup($company['index_name'] ?: $company['index_name_temp'] ?: '') : null,
                'email'       => $company['email_company'] ?? null,
                'video'       => $company['video_company'] ?? null,
                'city'        => $company['city']['city'] ?? null,
                'state'       => $company['state']['state'] ?? null,
                'country'     => $company['country']['country'] ?? null,
                'address'     => $company['address_company'] ?: null,
                'postalCode'  => $company['zip_company'] ?: null,
                'description' => $company['description_company'] ?: null,
                'displayName' => $company['name_company'],
                'typeName'    => $company['type']['name_type'] ?: null,
                'legalName'   => $company['legal_name_company'],
                'indexName'   => $hasIndexName ? ($company['index_name'] ?: $company['index_name_temp'] ?: null) : null,
                'employees'   => $company['employees_company'] ?? null,
                'revenue'     => \moneyToDecimal($company['revenue_company'] ?? null),
                'faxNumber'   => null !== (
                    $fax = $this->parseRawPhoneNumber(
                        $company['stored_fax_code']['ccode'] ?? $company['fax_code_company'] ?? null,
                        $company['fax_company']
                    )
                ) ? $this->phoneUtils->format($fax, PhoneNumberFormat::INTERNATIONAL) : null,
                'phoneNumber' => null !== (
                    $phone = $this->parseRawPhoneNumber(
                        $company['stored_phone_code']['ccode'] ?? $company['phone_code_company'] ?? null,
                        $company['phone_company']
                    )
                ) ? $this->phoneUtils->format($phone, PhoneNumberFormat::INTERNATIONAL) : null,
            ],

            // Industries information
            'industries'      => [
                'all'        => $baseIndustries,
                'maximum'    => (int) \config('multipleselect_max_industries', 3),
                'selected'   => $selectedIndustries,
            ],

            // Categories information
            'categories'      => [
                'all'        => $baseCategories,
                'selected'   => $selectedCategories,
            ],

            // Account information
            'sourceAccounts'  => $sourceAccounts,
            'syncCompanyInfo' => $syncWithRelatedAccounts,
            'relatedAccounts' => \array_column($sellerRelatedAccounts, 'accountLabel', 'idu'),

            // Cropper options
            'cropperOptions'  => [
                'url'                    => ['upload' => \getUrlForGroup('company/ajax_company_upload_photo')],
                'rules'                  => \config('img.companies.main.rules'),
                'accept'                 => \getMimePropertiesFromFormats(\config('img.companies.main.rules.format'))['accept'] ?? [],
                'title_text_popup'       => 'Logo',
                'croppper_limit_by_min'  => true,
                'btn_text_save_picture'  => 'Set new logo',
                'link_main_image'        => $logoImage,
                'link_thumb_main_image'  => $thumbImage,
            ],

            // Assets data
            'webpackData'     => ['dashboardOldPage' => true, 'pageConnect' => 'company_edit_page'],
            'currentPage'     => 'edit',
        ]);
    }

    /**
     * Renders edit company or create request form.
     *
     * @throws AccessDeniedException if user has profile edit request
     */
    public function renderEditForm(
        CompanyEditRequestDocumentsService $documentsService,
        PhoneCodesService $phoneCodesService,
        int $companyId
    ): void {
        $company = $this->companyProvider->getCompanyForEditForm($companyId);
        $userId = $company['id_user'];
        if ($this->requestProcessor->hasPendingRequest($userId)) {
            throw new AlreadyExistsException('User already have the pending company edit request.');
        }

        //region Documents
        $documents = [];
        if ($canRequestEdit = $this->requestProcessor->canCreateRequest($userId)) {
            $documents = array_map(
                fn (array $document) => [
                    'id'          => $document['id_document'],
                    'url'         => getUrlForGroup("/profile/popup_forms/upload-document/{$document['id_document']}"),
                    'type'        => $document['type']['id_document'],
                    'title'       => $document['type']['document_title'] ?? null,
                    'subtitle'    => $document['subtitle'] ?? null,
                    'description' => $document['type']['document_description'] ?? null,
                ],
                $documentsService->getRequiredDocuments($userId)
            );
        }
        //endregion Documents

        //region Location
        $countriesRepository = $this->companyProvider->getRepository()->getRelation('country')->getRelated();
        $statesRepository = $this->companyProvider->getRepository()->getRelation('state')->getRelated();
        $companyCountry = $company['id_country'] ?? null;
        $states = [];
        $countries = array_map(
            fn (array $country) => array_merge($country, ['selected' => $country['id'] === $companyCountry]),
            $countriesRepository->findAll([
                'columns' => ["{$countriesRepository->getPrimaryKey()} as `id`", 'country', 'position_on_select'],
            ])
        );
        if (null !== $companyCountry) {
            $states = array_map(
                fn (array $state) => ['id' => $state['id'], 'name' => $state['state'], 'selected' => $state['id'] === ($company['id_state'] ?? null)],
                $statesRepository->findAllBy(['scopes' => ['country' => $companyCountry]]) ?? []
            );
        }
        //endregion Location

        //region Phones
        /** @var Collection<CountryCodeInterface|PatternsAwareInterface> */
        $rawPhoneCodes = ($phoneCodesService->getCountryCodes() ?? new ArrayCollection())->map(
            fn (CountryCodeInterface $code) => [
                'id'          => $code->getId(),
                'name'        => $code->getName(),
                'mask'        => $code instanceof PatternsAwareInterface ? $code->getPattern(PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK) : null,
                'countryId'   => $code->getCountry()->getId(),
                'countryName' => $code->getCountry()->getName(),
                'countryFlag' => getCountryFlag($code->getCountry()->getName()),
            ]
        );
        $phoneCodes = $rawPhoneCodes
            ->map(fn (array $code) => array_merge($code, ['selected' => $code['id'] === $company['id_phone_code_company']]))
            ->getValues()
        ;
        $selectedPhoneCode = $rawPhoneCodes->filter(fn (array $code) => $code['id'] === $company['id_phone_code_company'])->first() ?: null;
        $faxCodes = $rawPhoneCodes
            ->map(fn (array $code) => array_merge($code, ['selected' => $code['id'] === $company['id_fax_code_company']]))
            ->getValues()
        ;
        $selectedFaxCode = $rawPhoneCodes->filter(fn (array $code) => $code['id'] === $company['id_fax_code_company'])->first() ?: null;
        //endregion Phones

        $this->render('new/user/seller/company/edit_form_view', [
            // URLs
            'editUrl'        => !$canRequestEdit
                ? getUrlForGroup('/company/ajax_company_operation/edit')
                : getUrlForGroup('/company_edit_requests/ajax_operations/create'),

            // Company information
            'company'        => [
                'displayName' => $company['name_company'],
                'legalName'   => $company['legal_name_company'],

                // Location information
                'location'    => [
                    'countries'       => $countries ?? [],
                    'states'          => $states,
                    'city'            => $company['city'] ?? null,
                    'address'         => $company['address_company'] ?: null,
                    'postalCode'      => $company['zip_company'] ?: null,
                    'selectedCountry' => $companyCountry,
                    // Geodata information
                    'marker'         => [
                        'title'     => $company['name_company'],
                        'latitude'  => $company['latitude'] ?? null,
                        'longitude' => $company['longitude'] ?? null,
                    ],
                ],

                // Type information
                'types'       => \array_map(
                    fn (array $type) => [
                        'id'       => $type['id_type'],
                        'name'     => $type['name_type'],
                        'selected' => $type['id_type'] === $company['id_type'],
                    ],
                    $this->companyProvider->getRepository()->getRelation('type')->getRelated()->findAll()
                ),

                // Phone information
                'phone' => [
                    'code'     => $company['stored_phone_code']['ccode'] ?? $company['phone_code_company'] ?? null,
                    'number'   => $company['phone_company'] ?: null,
                    'codeList' => $phoneCodes,
                    'selected' => $selectedPhoneCode ?? $rawPhoneCodes->first() ?: null,
                ],

                // Fax information
                'fax' => [
                    'code'     => $company['stored_fax_code']['ccode'] ?? $company['fax_code_company'] ?? null,
                    'number'   => $company['fax_company'] ?: null,
                    'codeList' => $faxCodes,
                    'selected' => $selectedFaxCode ?? $rawPhoneCodes->first() ?: null,
                ],
            ],

            // Documents information
            'documents'      => $documents,

            // Request information
            'canRequestEdit' => $canRequestEdit,
        ]);
    }
}
