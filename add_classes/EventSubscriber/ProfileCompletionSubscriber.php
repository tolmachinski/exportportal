<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ProfileUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TinyMVC_Library_Auth as LegacyAuthService;

/**
 * @author Anton Zencenco
 */
class ProfileCompletionSubscriber implements EventSubscriberInterface
{
    /**
     * The CRM repository.
     */
    private \Crm_Model $crmRepository;

    /**
     * The user profile option repository.
     */
    private \Users_Complete_Profile_Options_Model $userProfileOptionsRepository;

    /**
     * The auth service.
     */
    private LegacyAuthService $authService;

    /**
     * @param LegacyAuthService                     $authService                  the legacy auth service
     * @param \Users_Complete_Profile_Options_Model $userProfileOptionsRepository the user profile option repository
     * @param \Crm_Model                            $crmRepository                the CRM repository
     */
    public function __construct(
        LegacyAuthService $authService,
        \Users_Complete_Profile_Options_Model $userProfileOptionsRepository,
        \Crm_Model $crmRepository
    ) {
        $this->authService = $authService;
        $this->crmRepository = $crmRepository;
        $this->userProfileOptionsRepository = $userProfileOptionsRepository;
    }

    /**
     * Handles the user profile update event.
     */
    public function onProfileUpdate(ProfileUpdateEvent $event): void
    {
        $userId = $event->getUserId();
        // If there is no updated option - just leave.
        $option = $event->getOption();
        if (null === $option) {
            return;
        }

        if ($this->userProfileOptionsRepository->hasProfileOption($event->getUserId(), $option)) {
            return;
        }

        $this->userProfileOptionsRepository->insertOne(['id_user' => $event->getUserId(), 'profile_key' => $option]);
        $this->crmRepository->create_or_update_record($event->getUserId());
        $this->authService->setUserCompleteProfile($userId);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProfileUpdateEvent::class => ['onProfileUpdate', 0],
        ];
    }
}
