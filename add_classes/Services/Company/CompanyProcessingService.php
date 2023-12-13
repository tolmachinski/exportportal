<?php

declare(strict_types=1);

namespace App\Services\Company;

use App\Common\Contracts\Group\GroupAlias;
use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Database\Exceptions\WriteException;
use App\Common\Database\Model;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\CompanyNotFoundException;
use App\Common\Exceptions\MismatchStatusException;
use App\Common\Exceptions\ProfileCompletionException;
use App\Common\Exceptions\UserNotFoundException;
use App\DataProvider\CompanyProvider;
use App\Event\ProfileUpdateEvent;
use App\Event\SellerCompanyUpdateEvent;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Messenger\Message\Command\Company as CompanyCommands;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use App\Services\PhoneCodesService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TinyMVC_Library_Cleanhtml as LegacyHtmlSanitizer;

/**
 * @author Anton Zencenco
 */
final class CompanyProcessingService
{
    /**
     * The phone codes service.
     */
    private PhoneCodesService $phoneCodesService;

    /**
     * The company data provider.
     */
    private CompanyProvider $companyProvider;

    /**
     * The media processor service.
     */
    private CompanyMediaProcessorService $mediaProcessor;

    /**
     * The repository for company.
     */
    private Model $companyRepository;

    /**
     * The repository for users.
     */
    private Model $usersRepository;

    /**
     * The event dispatcher.
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * The event bus instance.
     */
    private MessageBusInterface $eventBus;

    /**
     * The command bus instance.
     */
    private MessageBusInterface $commandBus;

    /**
     * Create the service.
     *
     * @param PhoneCodesService   $phoneCodesService the phone codes service
     * @param MessageBusInterface $eventBus          the event bus
     */
    public function __construct(
        PhoneCodesService $phoneCodesService,
        CompanyProvider $companyProvider,
        CompanyMediaProcessorService $mediaProcessor,
        MessageBusInterface $eventBus,
        MessageBusInterface $commandBus,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
        $this->mediaProcessor = $mediaProcessor;
        $this->companyProvider = $companyProvider;
        $this->companyRepository = $companyProvider->getRepository();
        $this->phoneCodesService = $phoneCodesService;
        $this->eventDispatcher = $eventDispatcher;
        $this->usersRepository = $this->companyRepository->getRelation('user')->getRelated();
    }

    /**
     * Import company information from existing account's company.
     *
     * @throws AccessDeniedException      if the actor is not the owner of the source company
     * @throws MismatchStatusException    if source account is deleted
     * @throws ProfileCompletionException if source company is not completed
     * @throws CompanyNotFoundException   if one of the companies is not found
     * @throws UserNotFoundException      if owner of the source company doesn't exists
     *
     * @return array the updated target company
     */
    public function importCompanyInformation(int $targetCompanyId, ?int $sourceAccountId, ?int $actorId): array
    {
        $targetCompany = $this->companyProvider->getCompany($targetCompanyId);
        $sourceCompany = $this->companyProvider->getUserSourceCompany($sourceAccountId);

        //region Check access
        // Determine if owner of the source company is exists
        if (null === $sourceCompany['user']) {
            throw new UserNotFoundException('The owner of the source company is not found.');
        }

        // If user is deleted then we cannot import their data
        if (null === ($sourceCompany['user']['status'] ?? null) || UserStatus::DELETED() === $sourceCompany['user']['status']) {
            throw new MismatchStatusException(
                \sprintf('Cannot import company information from user in the status "%s".', (string) UserStatus::DELETED())
            );
        }

        // If actor is defined, check if their principal is the same as the owner of the source company.
        if (null !== $actorId && null !== $sourceCompany['user']) {
            if (
                false === (bool) $this->usersRepository->countAllBy([
                    'scopes' => ['id' => $actorId, 'principal' => $sourceCompany['user']['id_principal']],
                ])
            ) {
                throw new AccessDeniedException('The actor is not the owner of the source company.');
            }
        }

        // Determine if source company is complete
        if (
            !$this->usersRepository
                ->getRelation('completeProfileOptions')
                ->getRelated()
                ->has([$sourceCompany['user']['idu'], 'company_main'])
        ) {
            throw new ProfileCompletionException('Cannot import information from profiles that are not completed.');
        }
        //endregion Check access

        //region Collect information
        $update = array_intersect_key(
            $sourceCompany,
            array_fill_keys(
                [
                    'id_city',
                    'id_state',
                    'id_country',
                    'email_company',
                    'fax_company',
                    'fax_code_company',
                    'id_fax_code_company',
                    'phone_code_company',
                    'phone_company',
                    'id_phone_code_company',
                    'employees_company',
                    'revenue_company',
                    'description_company',
                    'address_company',
                    'longitude',
                    'latitude',
                    'zip_company',
                ],
                ''
            )
        );
        //endregion Collect information

        //region Update company
        $connection = $this->companyRepository->getConnection();
        $connection->beginTransaction();
        try {
            // Write new user information
            if (!$this->companyRepository->updateOne($targetCompanyId, $update)) {
                throw new WriteException(\sprintf('Failed to update company with ID "%s".', $targetCompanyId));
            }

            // Update industriess
            $this->updateCompanyIndustries(
                $targetCompanyId,
                ($sourceCompany['industries'] ?? new ArrayCollection())->map(fn (array $industry) => $industry['industry_id'])->toArray()
            );
            // Update categories
            $this->updateCompanyCategories(
                $targetCompanyId,
                ($sourceCompany['categories'] ?? new ArrayCollection())->map(fn (array $category) => $category['category_id'])->toArray()
            );

            $connection->commit();
        } catch (\Throwable $e) {
            // Roll back changes (if any)
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Roll up the exception
            throw $e;
        }

        // Update company media
        $this->commandBus->dispatch(new CompanyCommands\UpdateSellerLogo(
            $targetCompanyId,
            null === $sourceCompany['logo_company'] ? null : CompanyLogoFilePathGenerator::logoPath(
                (int) $sourceCompany['id_company'],
                $sourceCompany['logo_company']
            )
        ));
        $this->commandBus->dispatch(new CompanyCommands\UpdateCompanyVideo(
            $targetCompanyId,
            $sourceCompany['user']['group']['gr_type'],
            $sourceCompany['video_company'] ?? null
        ));

        // Send app events
        $this->eventDispatcher->dispatch(new SellerCompanyUpdateEvent($targetCompanyId, $targetCompany, $update, false));
        /** @var GroupAlias $groupAlias */
        $groupAlias = $targetCompany['user']['group']['gr_alias'] ?? null;
        if (null === $groupAlias || !$groupAlias->isCertified()) {
            $this->eventDispatcher->dispatch(new ProfileUpdateEvent((int) $targetCompany['user']['idu'], 'company_main'));
        }
        // Send event about profile update to the async transport
        $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedSellerCompanyEvent(
            $actorId ?? $targetCompany['id_user'],
            $targetCompanyId
        ));
        //endregion Update company

        return \array_merge(
            $targetCompany,
            []
        );
    }

    /**
     * Save general profile information.
     *
     * @throws CompanyNotFoundException if company is not found
     * @throws WriteException           when failed to write changes
     *
     * @return array the updated company information
     */
    public function saveGeneralCompanyInformation(Request $request, ?int $companyId): array
    {
        $company = $this->companyProvider->getCompany($companyId);
        //region Collect data
        /** @var CountryCodeInterface $phoneCode */
        $phoneCode = $this->phoneCodesService->findAllMatchingCountryCodes($request->request->getInt('phone_code'))->first() ?: null;
        /** @var CountryCodeInterface $faxCode */
        $faxCode = $this->phoneCodesService->findAllMatchingCountryCodes($request->request->getInt('fax_code'))->first() ?: null;
        // Base update block.
        $update = [
            'id_type'               => $typeId = $request->request->getInt('type'),
            'id_city'               => $request->request->getInt('city'),
            'id_state'              => $request->request->getInt('region'),
            'id_country'            => $request->request->getInt('country'),
            'name_company'          => \cleanInput($request->request->get('display_name')),
            'legal_name_company'    => \cleanInput($request->request->get('legal_name')),
            'latitude'              => \cleanInput($request->request->get('latitude')),
            'longitude'             => \cleanInput($request->request->get('longitude')),
            'zip_company'           => \cleanInput($request->request->get('postal_code')),
            'address_company'       => \cleanInput($request->request->get('address')),
            'id_phone_code_company' => $phoneCode ? $phoneCode->getId() : null,
            'phone_code_company'    => $phoneCode ? $phoneCode->getName() : null,
            'phone_company'         => \cleanInput($request->request->get('phone')),
            'id_fax_code_company'   => $faxCode ? $faxCode->getId() : null,
            'fax_code_company'      => $faxCode ? $faxCode->getName() : null,
            'fax_company'           => \cleanInput($request->request->get('fax')),
            'visible_company'       => UserStatus::ACTIVE() === ($company['user']['status'] ?? null),
            'updated_company'       => new \DateTimeImmutable(),
        ];
        //endregion Collect data

        //region Update
        if (!$this->companyRepository->updateOne($companyId, $update)) {
            throw new WriteException(\sprintf('Failed to update profile for user with ID "%s".', $companyId));
        }
        //endregion Update

        // Send app events
        $this->eventDispatcher->dispatch(new SellerCompanyUpdateEvent($companyId, $company, $update, false));
        // Send event about profile update to the async transport
        $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedSellerCompanyEvent($company['id_user'], $companyId));

        // Return merged updated company information
        return \array_merge($company, $update, [
            'type' => $typeId === $company['id_type']
                ? $company['type']
                : $this->companyRepository->getRelation('type')->getRelated()->findOne($typeId),
        ]);
    }

    /**
     * Undocumented function.
     */
    public function saveAdditionalCompanyInformation(
        Request $request,
        LegacyHtmlSanitizer $sanitizer,
        ?int $companyId,
        array $relatedAccounts = []
    ): array {
        //region Collect data
        $company = $this->companyProvider->getCompany($companyId);
        $update = [
            'email_company'       => \cleanInput($request->request->get('email', true)),
            'revenue_company'     => \priceToUsdMoney($request->request->get('revenue')),
            'employees_company'   => (int) \cleanInput($request->request->get('employees')),
            'description_company' => $sanitizer->sanitizeUserInput($request->request->get('description')),
            'visible_company'     => UserStatus::ACTIVE() === ($company['user']['status'] ?? null),
            'updated_company'     => new \DateTimeImmutable(),
        ];
        // Add the video to the list of saved information to preserve it's value
        // The thumb will be updated later
        if ($request->request->get('video') !== $company['video_company']) {
            $update['video_company'] = $request->request->get('video');
        }
        if ($request->request->has('index_name')) {
            $update['index_name'] = \cleanInput($request->request->get('index_name'), true);
        }
        $industries = $this->getCategoriesForUpdate((array) $request->request->get('industries'), true);
        $categories = $this->getCategoriesForUpdate((array) $request->request->get('categories'));
        //endregion Collect data

        //region Update
        $isLogoUpdated = $request->request->has('logo');
        $connection = $this->companyRepository->getConnection();
        $connection->beginTransaction();
        try {
            // Write new user information
            if (!$this->companyRepository->updateOne($companyId, $update)) {
                throw new WriteException(\sprintf('Failed to update company with ID "%s".', $companyId));
            }

            // Update industriess
            $this->updateCompanyIndustries($companyId, $industries);
            // Update categories
            $this->updateCompanyCategories($companyId, $categories);
            // Update logo
            if ($isLogoUpdated) {
                $this->mediaProcessor->updateCompanyLogo($companyId, $request->request->get('logo') ?: null, true);
            }

            // Update related accounts but only in the cases when user explicitly selected the account for synchronization
            // Here we have the user's related accounts and synchronized accounts in order to
            // have the valid list of related comapnies we must filter out the ID values that do not belong
            // to said list of related accounts using intersection of arrays.
            // This way we can be sure that we will got the companies that are really belong to the user.
            $synchronizedAccounts = \array_intersect(
                $relatedAccounts,
                \array_values(
                    \array_map(fn ($v) => (int) $v, (array) $request->request->get('sync_with_accounts'))
                )
            );
            // If list of synchronizations is empty that means that the user choose to remove all previous settings
            // and we need to rewrite the existing ones. That is why we still call this method even if the list
            // of related companies can be empty.
            $this->updateRelatedCompanies(
                $company['id_user'],
                $updatedCompany = \array_merge($company, $update),
                \array_column($relatedCompanies = $this->companyProvider->getRelatedCompanies($synchronizedAccounts), 'id_company', 'id_user'),
                $synchronizedAccounts,
                $industries,
                $categories,
                \array_keys($update),
            );
            // Commit changes
            $connection->commit();
        } catch (\Throwable $e) {
            // Roll back changes (if any)
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Roll up the exception
            throw $e;
        }
        //endregion Update

        // Send events about company addendum information update
        $updatedCompanies = \array_merge($relatedCompanies, [$company]);
        foreach ($updatedCompanies as $updatedCompany) {
            $updatedCompanyId = $updatedCompany['id_company'];
            /** @var GroupType $groupType */
            $groupType = $updatedCompany['user']['group']['gr_type'] ?? $updatedCompany['owner_group']['gr_type'] ?? null;
            /** @var GroupAlias $groupAlias */
            $groupAlias = $updatedCompany['user']['group']['gr_alias'] ?? $updatedCompany['owner_group']['gr_alias'] ?? null;
            $userId = $updatedCompany['id_user'];

            // If we have related company and have logo field, then we send command to update logo
            if ($companyId !== $updatedCompanyId && $isLogoUpdated) {
                $this->commandBus->dispatch(new CompanyCommands\UpdateSellerLogo($updatedCompanyId, $request->request->get('logo') ?: null, true));
            }
            // If we have video field, then we send command to update video
            if ($request->request->has('video')) {
                $this->commandBus->dispatch(new CompanyCommands\UpdateCompanyVideo(
                    $updatedCompanyId,
                    $groupType,
                    $request->request->get('video') ?: null
                ));
            }
            // Send app events
            $this->eventDispatcher->dispatch(new SellerCompanyUpdateEvent($updatedCompanyId, $company, $update, true));
            if (
                $companyId === $updatedCompanyId
                || null === $groupAlias
                || !$groupAlias->isCertified()
            ) {
                $this->eventDispatcher->dispatch(new ProfileUpdateEvent($userId, 'company_main'));
            }
            // Finally, send event to the bus about company update
            if ($companyId === $updatedCompanyId) {
                $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedCompanyAddendumEvent($userId, $updatedCompanyId));
            } else {
                $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedRelatedCompanyEvent($userId, $updatedCompanyId));
            }
        }

        return $updatedCompany;
    }

    /**
     * Get categories and industries for update.
     */
    private function getCategoriesForUpdate(array $savedCategories, bool $isIndustry = false): array
    {
        if (empty($savedCategories)) {
            return [];
        }
        $categoriesRepository = $this->companyRepository
            ->getRelation($isIndustry ? 'industries' : 'categories')
            ->getRelated()
        ;

        return array_column(
            $categoriesRepository->findAllBy(['columns' => ['category_id'], 'scopes' => ['isIndustry' => $isIndustry, 'ids' => $savedCategories]]),
            'category_id'
        );
    }

    /**
     * Updates the company industries.
     *
     * @throws \RuntimeException if companies pivot is invalid
     * @throws WriteException    when failed to write the companies
     */
    private function updateCompanyIndustries(int $companyId, array $industrise): void
    {
        // If we have no industries - we must leave.
        if (empty($industrise)) {
            return;
        }

        // We cannot allow here any errors, so if by ANY slight chance that the pivot model will be
        // the company repository, we will throw exception to stop operation.
        $pivot = $this->companyRepository->getRelation('industries')->getParent();
        if (\get_class($this->companyRepository) === \get_class($pivot)) {
            throw new \RuntimeException(\sprintf('The pivot cannot be instance of "%s" class', \get_class($this->companyRepository)));
        }

        // Otherwise we happily delete everything and insert new industries.
        if (
            !$pivot->deleteAllBy(['scopes' => ['company' => $companyId]])
            || !$pivot->insertMany(
                \array_map(
                    fn (int $id) => ['id_company' => $companyId, 'id_industry' => $id],
                    $industrise
                )
            )
        ) {
            throw new WriteException(\sprintf('Failed to update industries for company with ID "%s".', $companyId));
        }
    }

    /**
     * Updates the company industries.
     *
     * @throws \RuntimeException if categories pivot is invalid
     * @throws WriteException    when failed to write the categories
     */
    private function updateCompanyCategories(int $companyId, array $categories): void
    {
        // If categories are not empty, then we need override the existing ones
        if (empty($categories)) {
            return;
        }

        // We cannot allow here any errors, so if by ANY slight chance that the pivot model will be
        // the user repository, we will throw exception to stop operation.
        $pivot = $this->companyRepository->getRelation('categories')->getParent();
        if (\get_class($this->companyRepository) === \get_class($pivot)) {
            throw new \RuntimeException(\sprintf('The pivot cannot be instance of "%s" class', \get_class($this->companyRepository)));
        }

        // Otherwise we happily delete everything and insert new categories.
        if (
            !$pivot->deleteAllBy(['scopes' => ['company' => $companyId]])
            || !$pivot->insertMany(
                \array_map(
                    fn (int $id) => ['id_company' => $companyId, 'id_category' => $id],
                    $categories
                )
            )
        ) {
            throw new WriteException(\sprintf('Failed to update industries for company with ID "%s".', $companyId));
        }
    }

    /**
     * Update user related accounts.
     */
    private function updateRelatedCompanies(
        int $userId,
        array $baseCompany,
        array $relatedCompanies,
        array $savedSyncSettings,
        array $industries = [],
        array $categories = [],
        ?array $syncColumns = null
    ): void {
        $syncSettings = $baseCompany['user']['sync_with_related_accounts'] ?? [];
        $updateInformation = array_intersect_key(
            $baseCompany,
            array_fill_keys(
                $syncColumns ?? [
                    'id_country',
                    'id_state',
                    'id_city',
                    'zip_company',
                    'address_company',
                    'latitude',
                    'longitude',
                    'logo_company',
                    'video_company',
                    'id_phone_code_company',
                    'phone_code_company',
                    'phone_company',
                    'id_fax_code_company',
                    'fax_code_company',
                    'fax_company',
                    'email_company',
                    'employees_company',
                    'revenue_company',
                    'description_company',
                ],
                ''
            )
        );
        // The company index name must be ommited.
        unset($updateInformation['index_name']);

        foreach ($savedSyncSettings as $accountId) {
            if (!isset($relatedCompanies[$accountId])) {
                continue;
            }

            // Update related company information
            $companyId = $relatedCompanies[$accountId];
            if (!$this->companyRepository->updateOne($companyId, $updateInformation)) {
                throw new WriteException(\sprintf('Failed to update related company with ID "%s" for user with ID "%s".', $companyId, $userId));
            }
            // Update industriess
            $this->updateCompanyIndustries($companyId, $industries);
            // Update categories
            $this->updateCompanyCategories($companyId, $categories);
        }

        // Update syncronization settings
        $syncSettings['company_info'] = \array_combine(
            \array_values($savedSyncSettings),
            \array_fill(0, count($savedSyncSettings), [])
        );
        if (!$this->usersRepository->updateOne($userId, ['sync_with_related_accounts' => $syncSettings])) {
            throw new WriteException(\sprintf('Failed to update related accounts information for user with ID "%s".', $userId));
        }
    }
}
