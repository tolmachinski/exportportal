<?php

declare(strict_types=1);

use App\Common\Contracts\Group\GroupType;
use App\Common\Database\Exceptions\WriteException;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\MismatchStatusException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Exceptions\ProfileCompletionException;
use App\Common\Exceptions\UserNotFoundException;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\ValidationException;
use App\DataProvider\UserProfileProvider;
use App\DataProvider\VerificationDocumentProvider;
use App\Renderer\UserProfileEditViewRenderer;
use App\Renderer\VerificationDocumentsViewRenderer;
use App\Services\EditRequest\ProfileEditRequestDocumentsService;
use App\Services\PhoneCodesService;
use App\Services\Profile\UserProfileProcessingService;
use App\Validators\AddressValidator;
use App\Validators\LegalNameValidator;
use App\Validators\PhoneValidator;
use App\Validators\ProfileAdditionalInformationValidation;
use App\Validators\RegistrationSourceValidator;
use App\Validators\UserNameValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use TinyMVC_Library_Session as LegacySessionHandler;
use TinyMVC_Library_validator as LegacyValidator;

/**
 * Controller Profile.
 *
 * @author Anton Zencenco
 */
class Profile_Controller extends TinyMVC_Controller
{
    /**
     * The user profile data provider.
     */
    private UserProfileProvider $userProvider;

    /**
     * The page renderer for profile edit pages.
     */
    private UserProfileEditViewRenderer $pageRenderer;

    /**
     * The user profile processing service.
     */
    private UserProfileProcessingService $processingService;

    /**
     * Controller constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->userProvider = $container->get(UserProfileProvider::class);
        $this->pageRenderer = $container->get(UserProfileEditViewRenderer::class);
        $this->processingService = $container->get(UserProfileProcessingService::class);
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkDomainForGroup();

        try {
            switch (uri()->segment(3)) {
                case 'edit':
                    $this->showEditPopup(
                        $this->getContainer()->get(PhoneCodesService::class),
                        $this->getContainer()->get(ProfileEditRequestDocumentsService::class),
                        (int) id_session()
                    );

                    break;
                case 'upload-document':
                    $this->showUploadDocumentPopup(
                        $this->getContainer()->get(VerificationDocumentProvider::class),
                        $this->getContainer()->get(VerificationDocumentsViewRenderer::class),
                        (int) id_session(),
                        (int) uri()->segment(4) ?: null
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
     * Executes actions on profile by AJAX.
     */
    public function ajax_operations()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkDomainForGroup();

        $request = request();
        $action = uri()->segment(3);
        /** @var LibraryLocator */
        $libraryLocator = $this->getContainer()->get(LibraryLocator::class);

        try {
            switch ($action) {
                case 'save':
                    $this->saveProfile(
                        $request,
                        $libraryLocator->get(LegacySessionHandler::class),
                        $libraryLocator->get(LegacyValidator::class)
                    );

                    break;
                case 'save-legacy':
                    $this->saveLegacyProfile(
                        $request,
                        $libraryLocator->get(LegacySessionHandler::class),
                        $libraryLocator->get(LegacyValidator::class)
                    );

                    break;
                case 'save-additional':
                    $this->saveProfileAddendum($request, $libraryLocator->get(LegacyValidator::class));

                    break;
                case 'use-existing':
                    $this->useExistingProfileInformation(
                        $libraryLocator->get(LegacySessionHandler::class),
                        $request->request->getInt('account') ?: null
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
                        default: return translate('systmess_error_invalid_data', null, true);
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
     * Shows profile edit popups.
     */
    private function showEditPopup(
        PhoneCodesService $phoneCodesService,
        ProfileEditRequestDocumentsService $documentsService,
        int $userId
    ): void {
        try {
            // Render form.
            $this->pageRenderer->renderEditForm($phoneCodesService, $documentsService, $userId);
        } catch (AccessDeniedException $e) {
            messageInModal(translate('user_preferences_profile_edit_form_request_already_exists', null, true));
        }
    }

    /**
     * Shows the upload document form popup.
     */
    private function showUploadDocumentPopup(
        VerificationDocumentProvider $documentProvider,
        VerificationDocumentsViewRenderer $documentsService,
        int $userId,
        ?int $documentId
    ): void {
        try {
            $documentsService->inlineUploadPopup($userId, $documentProvider->getDocumentForDownload($documentId));
        } catch (NotFoundException $e) {
            messageInModal(translate('systmess_error_document_does_not_exist'));
        }
    }

    /**
     * Saves the general profile information from edit form.
     */
    private function saveProfile(Request $request, LegacySessionHandler $sessionHandler, LegacyValidator $legacyValidator): void
    {
        $userId = (int) id_session();

        //region Validate
        $adapter = new LegacyValidatorAdapter($legacyValidator);
        $validator = new AggregateValidator(
            array_filter([
                new UserNameValidator($adapter),
                !$request->request->has('has_legal_name') ? null : new LegalNameValidator($adapter),
                new PhoneValidator(
                    $adapter,
                    [
                        'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                        'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                        'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                    ],
                    ['phone' => translate('register_label_phone'), 'code' => translate('register_label_country_code')],
                    ['phone' => 'phone', 'code' => 'phone_code']
                ),
                new PhoneValidator(
                    $adapter,
                    [
                        'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                        'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                        'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                    ],
                    ['phone' => translate('register_label_fax'), 'code' => translate('register_label_country_code')],
                    ['phone' => 'fax', 'code'  => 'fax_code'],
                    false
                ),
                new AddressValidator($adapter, [], [], ['city' => 'city', 'state' => 'region', 'postalCode' => 'postal_code']),
            ])
        );
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to create edit request due to validation errors.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        //region Save
        try {
            // Update profile
            $updatedProfile = $this->processingService->saveGeneralProfileInformation(
                $request,
                $this->userProvider->getProfile($userId),
            );

            /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
            $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
            $elasticsearchUsersModel->sync((int) $userId);

            // Alter some information in the current session after we got the updated user profile.
            $sessionHandler->set('fname', $updatedProfile['fname']);
            $sessionHandler->set('lname', $updatedProfile['lname']);
            $sessionHandler->set('country', $updatedProfile['city']);
            $sessionHandler->set('legal_name', $updatedProfile['legal_name'] ?: null);
        } catch (WriteException $e) {
            jsonResponse(translate('user_preferences_profile_edit_form_save_error'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }
        //endregion Save

        jsonResponse(translate('systmess_information_successfully_changed'), 'success', [
            'url'    => check_group_type('Seller,Buyer,CR Affiliate,Shipper') ? getMyProfileLink() : null,
            'isEdit' => true,
        ]);
    }

    /**
     * Saves the general profile information from edit form.
     */
    private function saveLegacyProfile(Request $request, LegacySessionHandler $sessionHandler, LegacyValidator $legacyValidator): void
    {
        $userId = (int) id_session();

        //region Validate
        $adapter = new LegacyValidatorAdapter($legacyValidator);
        $validator = new AggregateValidator(
            array_filter([
                new UserNameValidator(
                    $adapter,
                    UserNameValidator::MIN_NAME_LENGTH,
                    UserNameValidator::MAX_NAME_LENGTH,
                    [],
                    ['firstName' => 'First Name', 'lastName' => 'Last Name'],
                    ['firstName' => 'fname', 'lastName' => 'lname']
                ),
                !$request->request->has('checkbox_legal_name') ? null : new LegalNameValidator($adapter),
                new ProfileAdditionalInformationValidation(
                    new LegacyValidatorAdapter($legacyValidator),
                    $this->userProvider->getRepository(),
                    (int) principal_id(),
                    $userId
                ),
                new PhoneValidator(
                    $adapter,
                    [
                        'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                        'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                        'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                    ],
                    ['phone' => translate('register_label_phone'), 'code' => translate('register_label_country_code')],
                    ['phone' => 'phone', 'code' => 'phone_code']
                ),
                new PhoneValidator(
                    $adapter,
                    [
                        'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                        'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                        'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                    ],
                    ['phone' => translate('register_label_fax'), 'code' => translate('register_label_country_code')],
                    ['phone' => 'fax', 'code'  => 'fax_code'],
                    false
                ),
                new AddressValidator($adapter, [], [], ['city' => 'port_city', 'state' => 'states', 'postalCode' => 'zip']),
                $request->request->has('find_type') ? new RegistrationSourceValidator($adapter, 500) : null,
            ])
        );
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to create edit request due to validation errors.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        //region Save
        try {
            // Update profile
            $updatedProfile = $this->processingService->saveLegacyProfileInformation(
                $request,
                $this->userProvider->getProfile((int) id_session()),
                !have_right('manage_content')
            );

            // Alter some information in the current session after we got the updated user profile.
            $sessionHandler->set('fname', $updatedProfile['fname']);
            $sessionHandler->set('lname', $updatedProfile['lname']);
            $sessionHandler->set('country', $updatedProfile['city']);
            $sessionHandler->set('legal_name', $updatedProfile['legal_name'] ?: null);
        } catch (WriteException $e) {
            jsonResponse(translate('user_preferences_profile_edit_form_save_error'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }
        //endregion Save

        jsonResponse(translate('systmess_information_successfully_changed'), 'success', [
            'url'    => check_group_type('Seller,Buyer,CR Affiliate,Shipper') ? getMyProfileLink() : null,
            'isEdit' => true,
        ]);
    }

    /**
     * Saves the additional profile information from edit form.
     */
    private function saveProfileAddendum(Request $request, LegacyValidator $legacyValidator): void
    {
        $userId = (int) id_session();

        //region Validate
        $adapter = new LegacyValidatorAdapter($legacyValidator);
        $validator = new AggregateValidator(array_filter(
            [
                new ProfileAdditionalInformationValidation($adapter, $this->userProvider->getRepository(), (int) principal_id(), $userId),
                $request->request->has('find_type') ? new RegistrationSourceValidator($adapter, 500) : null,
            ]
        ));
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to create edit request due to validation errors.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        //region Save
        try {
            $this->processingService->saveAdditionalProfileInformation(
                $request,
                $this->userProvider->getAddedndumProfile($userId)
            );
        } catch (WriteException $e) {
            jsonResponse(translate('user_preferences_profile_edit_form_save_error'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }
        //endregion Save

        jsonResponse(translate('systmess_information_successfully_changed'), 'success', [
            'url'    => check_group_type('Seller,Buyer,CR Affiliate,Shipper') ? getMyProfileLink() : null,
            'isEdit' => true,
        ]);
    }

    /**
     * Use the existinf profile information to fill up the current one.
     */
    private function useExistingProfileInformation(LegacySessionHandler $sessionHandler, ?int $sourceAccountId): void
    {
        //region Save
        try {
            // Use existing profile to update current one
            $updatedProfile = $this->processingService->importProfileInformation(
                $this->userProvider->getSourceProfile($sourceAccountId, (int) principal_id(), [GroupType::BUYER(), GroupType::SELLER()]),
                $this->userProvider->getProfile((int) id_session())
            );

            // Alter some information in the current session after we got the updated user profile.
            $sessionHandler->set('fname', $updatedProfile['fname']);
            $sessionHandler->set('lname', $updatedProfile['lname']);
            $sessionHandler->set('country', $updatedProfile['city']);
            $sessionHandler->set('legal_name', $updatedProfile['legal_name'] ?: null);
        } catch (UserNotFoundException | MismatchStatusException | ProfileCompletionException $e) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Save

        session()->setMessages('The personal information was successfully copied from your another account');
        jsonResponse('', 'success');
    }
}

// End of file profile.php
// Location: /tinymvc/myapp/controllers/profile.php
