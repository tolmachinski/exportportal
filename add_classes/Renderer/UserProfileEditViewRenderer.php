<?php

declare(strict_types=1);

namespace App\Renderer;

use App\Common\Contracts\Entities\CountryCodeInterface;
use App\Common\Contracts\Entities\Phone\PatternsAwareInterface;
use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\UserSourceType;
use App\Common\Exceptions\AccessDeniedException;
use App\DataProvider\AccountProvider;
use App\DataProvider\UserProfileProvider;
use App\Services\EditRequest\ProfileEditRequestDocumentsService;
use App\Services\EditRequest\ProfileEditRequestProcessingService;
use App\Services\PhoneCodesService;
use Doctrine\Common\Collections\ArrayCollection;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use TinyMVC_View as Renderer;

/**
 * The service that renders the pages and/or popups for user profile.
 *
 * @author Anton Zencenco
 */
final class UserProfileEditViewRenderer extends AbstractViewRenderer
{
    use PhoneFormatterTrait;

    /**
     * The phone utils instance.
     */
    private PhoneNumberUtil $phoneUtils;

    /**
     * The user provider service.
     */
    private UserProfileProvider $userProfileProvider;

    /**
     * The edit request processor service instance.
     */
    private ProfileEditRequestProcessingService $requestProcessor;

    /**
     * {@inheritDoc}
     *
     * @param UserProfileProvider                 $userProfileProvider the user provider service
     * @param ProfileEditRequestProcessingService $requestProcessor    the edit request processor service instance
     * @param null|PhoneNumberUtil                $phoneUtils          the phone utils instance
     */
    public function __construct(
        Renderer $renderer,
        UserProfileProvider $userProfileProvider,
        ProfileEditRequestProcessingService $requestProcessor,
        PhoneNumberUtil $phoneUtils = null
    ) {
        parent::__construct($renderer);

        $this->phoneUtils = $phoneUtils ?? PhoneNumberUtil::getInstance();
        $this->requestProcessor = $requestProcessor;
        $this->userProfileProvider = $userProfileProvider;
    }

    /**
     * Renders profile edit page for buyer and seller.
     */
    public function renderForBuyerOrSeller(AccountProvider $accountProvider, int $userId, bool $isBuyer = false, array $relatedAccounts = []): void
    {
        $user = $this->userProfileProvider->getProfileForEditPage($userId, $isBuyer);
        $sourceAccounts = $accountProvider->getSourceAccounts($userId, 'account_preferences', $relatedAccounts = \array_filter(
            $relatedAccounts,
            fn (array $account) => in_array($account['gr_type'], [GroupType::SELLER(), GroupType::BUYER()])
        ));

        $this->renderPage('user/profile/index_view', [
            // Page information
            'title'              => 'Personal info',

            // Profile information
            'profile'            => [
                'city'        => $user['location_city']['city'] ?? null,
                'state'       => $user['location_state']['state'] ?? null,
                'country'     => $user['location_country']['country'] ?? null,
                'address'     => $user['address'] ?: null,
                'postalCode'  => $user['zip'] ?: null,
                'description' => $user['description'] ?: null,
                'legalName'   => $user['legal_name'] ?: null,
                'firstName'   => $user['fname'],
                'lastName'    => $user['lname'],
                'faxNumber'   => null !== (
                    $fax = $this->parseRawPhoneNumber(
                        $user['stored_fax_code']['ccode'] ?? $user['fax_code'] ?? null,
                        $user['fax']
                    )
                ) ? $this->phoneUtils->format($fax, PhoneNumberFormat::INTERNATIONAL) : null,
                'phoneNumber' => null !== (
                    $phone = $this->parseRawPhoneNumber(
                        $user['stored_phone_code']['ccode'] ?? $user['phone_code'] ?? null,
                        $user['phone']
                    )
                ) ? $this->phoneUtils->format($phone, PhoneNumberFormat::INTERNATIONAL) : null,
            ],

            // Edit flags
            'isBuyer'            => $isBuyer,
            'hasEditRequest'     => $this->requestProcessor->hasPendingRequest($userId),
            'canRequestEdit'     => $this->requestProcessor->canCreateRequest($userId),
            'showAboutFields'    => (
                empty($user['user_find_type'])
                || UserSourceType::NONE() === $user['user_find_type']
                || (UserSourceType::SEARCH_ENGINES() === $user['user_find_type'] && empty($user['user_find_info']))
            ),

            // URLs for edit
            'editPopupUrl'       => getUrlForGroup('/profile/popup_forms/edit'),
            'requestPopupUrl'    => getUrlForGroup('/profile/popup_forms/edit?isRequest=1'),

            // Information about other accounts
            'relatedAccounts'    => array_column($relatedAccounts, 'accountLabel', 'idu'),
            'syncPersonalInfo'   => $user['sync_with_related_accounts']['personal_info'] ?? [],
            'sourceAccounts'     => $sourceAccounts ?? [],

            // Assets data
            'webpackData'        => ['dashboardOldPage' => true, 'pageConnect' => 'preferences_page'],
            'currentPage'        => 'preferences',
        ]);
    }

    /**
     * Renders profile edit page for shippers.
     */
    public function renderBasePage(
        PhoneCodesService $phoneCodesService,
        \User_Model $userModel,
        \Category_Model $categoryModel,
        \Country_Model $countryModel,
        \Complete_Profile_Model $completeProfileModel,
        int $userId
    ): void {
        //region User
        $userId = id_session();
        $user = $userModel->getUser($userId);
        $is_user = !have_right('manage_content');
        //endregion User

        //region Location
        $city = null;
        $regions = [];
        $countries = [];
        $user_country = isset($user['country']) ? (int) $user['country'] : null;
        $user_city = isset($user['city']) ? (int) $user['city'] : null;
        if ($is_user) {
            $countries = $countryModel->fetch_port_country();
            $regions = null !== $user_country ? $countryModel->get_states($user_country) : [];
            $city = null !== $user_city ? $countryModel->get_city($user_city) : null;
        }
        //endregion Location

        //region Phone & Fax
        $phone_codes = $fax_codes = $is_user ? $phoneCodesService->getCountryCodes() : new ArrayCollection();

        //region Phone codes
        $phone_code = !$is_user ? null : $phoneCodesService->findAllMatchingCountryCodes(
            !empty($user['phone_code_id']) ? (int) $user['phone_code_id'] : null,
            !empty($user['phone_code']) ? (string) $user['phone_code'] : null, // Fallback to old phone code system
            $user_country, // Or falling back to user country
            PhoneCodesService::SORT_BY_PRIORITY
        )->first();
        //endregion Phone codes

        //region Fax codes
        $fax_code = !$is_user ? null : $phoneCodesService->findAllMatchingCountryCodes(
            !empty($user['fax_code_id']) ? (int) $user['fax_code_id'] : null,
            !empty($user['fax_code']) ? (string) $user['fax_code'] : null, // Fallback to old fax code system
            $user_country, // Or falling back to user country
            PhoneCodesService::SORT_BY_PRIORITY
        )->first();
        //endregion Fax codes
        //endregion Phone & Fax

        $userAccounts = session()->__get('accounts') ?: $userModel->get_related_users_by_id_principal(principal_id());
        $userAccounts = array_column($userAccounts, null, 'idu');
        $otherRelatedAccounts = array_diff_key($userAccounts, [$userId => $userId]);

        foreach ($otherRelatedAccounts as &$relatedAccount) {
            $groupLabel = 'Buyer';

            if (is_verified_seller($relatedAccount['user_group']) || is_certified_seller($relatedAccount['user_group'])) {
                $groupLabel = 'Seller';
            } elseif (is_verified_manufacturer($relatedAccount['user_group']) || is_certified_manufacturer($relatedAccount['user_group'])) {
                $groupLabel = 'Manufacturer';
            }

            $relatedAccount['accountLabel'] = $groupLabel;
        }

        //region of check can use existing information
        if (!$completeProfileModel->is_profile_option_completed($userId, 'account_preferences')) {
            if (!empty($otherRelatedAccounts)) {
                $profileCompletion = $completeProfileModel->get_users_profile_options(array_keys($otherRelatedAccounts));

                foreach ($profileCompletion as $relatedAccountId => $completeProfileOptions) {
                    $completeProfileOptions = array_column($completeProfileOptions, null, 'option_alias');

                    if ((int) $completeProfileOptions['account_preferences']['option_completed']) {
                        $personalInformationSourceAccounts[$relatedAccountId] = $otherRelatedAccounts[$relatedAccountId]['accountLabel'];
                    }
                }
            }
        }

        $syncWithRelatedAccounts = null === $user['sync_with_related_accounts'] ? [] : json_decode($user['sync_with_related_accounts'], true);
        //endregion of check can use existing information

        $viewVars = [
            'user'                              => $user,
            'title'                             => 'Personal info',
            'states'                            => $regions,
            'fax_codes'                         => $fax_codes,
            'phone_codes'                       => $phone_codes,
            'port_country'                      => $countries,
            'city_selected'                     => $city,
            'relatedAccounts'                   => array_column($otherRelatedAccounts, 'accountLabel', 'idu'),
            'syncPersonalInfo'                  => $syncWithRelatedAccounts['personal_info'] ?? [],
            'selected_fax_code'                 => $fax_code,
            'selected_phone_code'               => $phone_code,
            'find_about_us_block'               => empty($user['user_find_type']) || ('search_engines' === $user['user_find_type'] && empty($user['user_find_info'])),
            'personalInformationSourceAccounts' => $personalInformationSourceAccounts ?? [],
        ];
        if (\is_shipper((int) $user['user_group'] ?? null)) {
            $this->renderEplPage('user/profile/base_view', \array_merge($viewVars, [
                'webpackData' => ['dashboardOldPage' => true],
            ]));

            return;
        }

        $this->renderLegacyPage('new/user/profile/base_view', $viewVars);
    }

    /**
     * Renders edit profile/create request form.
     *
     * @throws AccessDeniedException if user has profile edit request
     */
    public function renderEditForm(
        PhoneCodesService $phoneCodesService,
        ProfileEditRequestDocumentsService $documentsService,
        int $userId
    ): void {
        //region User
        if ($this->requestProcessor->hasPendingRequest($userId)) {
            throw new AccessDeniedException('User already have the profile edit request.');
        }
        // Get the user profile
        $user = $this->userProfileProvider->getProfileForEditForm($userId);
        //endregion User

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
        //region Location
        $countriesRepository = $this->userProfileProvider->getRepository()->getRelation('country')->getRelated();
        $statesRepository = $this->userProfileProvider->getRepository()->getRelation('state')->getRelated();
        $userCountry = $user['country'] ?? null;
        $states = [];
        $countries = array_map(
            fn (array $country) => array_merge($country, ['selected' => $country['id'] === $userCountry]),
            $countriesRepository->findAll([
                'columns' => ["{$countriesRepository->getPrimaryKey()} as `id`", 'country', 'position_on_select'],
            ])
        );
        if (null !== $userCountry) {
            $states = array_map(
                fn (array $state) => ['id' => $state['id'], 'name' => $state['state'], 'selected' => $state['id'] === ($user['state'] ?? null)],
                $statesRepository->findAllBy(['scopes' => ['country' => $userCountry]]) ?? []
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
            ->map(fn (array $code) => array_merge($code, ['selected' => $code['id'] === $user['phone_code_id']]))
            ->getValues()
        ;
        $selectedPhoneCode = $rawPhoneCodes->filter(fn (array $code) => $code['id'] === $user['phone_code_id'])->first() ?: null;
        $faxCodes = $rawPhoneCodes
            ->map(fn (array $code) => array_merge($code, ['selected' => $code['id'] === $user['fax_code_id']]))
            ->getValues()
        ;
        $selectedFaxCode = $rawPhoneCodes->filter(fn (array $code) => $code['id'] === $user['fax_code_id'])->first() ?: null;
        //endregion Phones

        $this->render('new/user/profile/edit_form_view', [
            // URLs
            'editUrl' => !$canRequestEdit
                ? getUrlForGroup('/profile/ajax_operations/save')
                : getUrlForGroup('/profile_edit_requests/ajax_operations/create'),

            // Profile information
            'profile'        => [
                'firstName' => $user['fname'],
                'lastName'  => $user['lname'],
                'legalName' => $user['legal_name'],
                'location'  => [
                    'countries'       => $countries ?? [],
                    'states'          => $states,
                    'city'            => $user['location_city'] ?? null,
                    'address'         => $user['address'] ?: null,
                    'postalCode'      => $user['zip'] ?: null,
                    'selectedCountry' => $userCountry,
                ],
                'phone' => [
                    'code'     => $user['stored_phone_code']['ccode'] ?? $user['phone_code'] ?? null,
                    'number'   => $user['phone'] ?: null,
                    'codeList' => $phoneCodes,
                    'selected' => $selectedPhoneCode ?? $rawPhoneCodes->first() ?: null,
                ],
                'fax' => [
                    'code'     => $user['stored_fax_code']['ccode'] ?? $user['fax_code'] ?? null,
                    'number'   => $user['fax'] ?: null,
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
