<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Bridge\Matrix\MatrixConnector;
use App\Messenger\Message\Command\DeactivateUnknownMatrixUser;
use App\Messenger\Message\Command\SyncMatrixUser;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use App\Messenger\Message\Event\UserGroupChangedEvent;
use App\Messenger\Message\Event\UserUpdatedCompanyEvent;
use App\Messenger\Message\Event\UserUpdatedProfileEvent;
use App\Messenger\Message\Event\UserWasBlockedEvent;
use App\Messenger\Message\Event\UserWasDeletedEvent;
use App\Messenger\Message\Event\UserWasMarkedFakeEvent;
use App\Messenger\Message\Event\UserWasMarkedRealEvent;
use App\Messenger\Message\Event\UserWasMutedEvent;
use App\Messenger\Message\Event\UserWasRestrictedEvent;
use App\Messenger\Message\Event\UserWasUnblockedEvent;
use App\Messenger\Message\Event\UserWasUnmutedEvent;
use App\Messenger\Message\Event\UserWasUnrestrictedEvent;
use App\Messenger\Message\Event\UserWasVerifiedEvent;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Process user's data on matrix server after changes in the user's account.
 *
 * @author Anton Zencenco
 */
final class ProcessUserDataInMatrixAfterAccountChanges implements MessageSubscriberInterface
{
    /**
     * The command bus.
     */
    private MessageBusInterface $commandBus;

    /**
     * The matrix connector instance.
     */
    private MatrixConnector $matrixConnector;

    /**
     * @param MessageBusInterface $commandBus the command bus
     */
    public function __construct(MessageBusInterface $commandBus, MatrixConnector $matrixConnector)
    {
        $this->commandBus = $commandBus;
        $this->matrixConnector = $matrixConnector;
    }

    /**
     * Process data when user was verified.
     */
    public function onUserVerfied(LifecycleEvents\UserWasVerifiedEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user updated profile.
     */
    public function onUserProfileUpdated(LifecycleEvents\UserUpdatedProfileEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user updated buyer company.
     */
    public function onUserBuyerCompanyUpdated(LifecycleEvents\UserUpdatedBuyerCompanyEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user updated seller company.
     */
    public function onUserSellerCompanyUpdated(LifecycleEvents\UserUpdatedSellerCompanyEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user updated shipper company.
     */
    public function onUserShipperCompanyUpdated(LifecycleEvents\UserUpdatedShipperCompanyEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user updated company addendum.
     */
    public function onUserCompanyAddendumUpdated(LifecycleEvents\UserUpdatedCompanyAddendumEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user updated related company.
     */
    public function onUserRelatedCompanyUpdated(LifecycleEvents\UserUpdatedRelatedCompanyEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user group changed.
     */
    public function onUserGroupChanged(LifecycleEvents\UserGroupChangedEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was restricted.
     */
    public function onUserRestricted(LifecycleEvents\UserWasRestrictedEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was unrestricted.
     */
    public function onUserUnrestricted(LifecycleEvents\UserWasUnrestrictedEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was blocked.
     */
    public function onUserBlocked(LifecycleEvents\UserWasBlockedEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was unblocked.
     */
    public function onUserUnblocked(LifecycleEvents\UserWasUnblockedEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was muted.
     */
    public function onUserMuted(LifecycleEvents\UserWasMutedEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was unmuted.
     */
    public function onUserUnmuted(LifecycleEvents\UserWasUnmutedEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was marked as fake one.
     */
    public function onUserWasMarkedFake(LifecycleEvents\UserWasMarkedFakeEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was marked as real one.
     */
    public function onUserWasMarkedReal(LifecycleEvents\UserWasMarkedRealEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user changed email.
     */
    public function onUserEmailChanged(LifecycleEvents\UserUpdatedEmailEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user changed logo.
     */
    public function onLogoChanged(LifecycleEvents\UserUpdatedLogoEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user changed seller logo.
     */
    public function onSellerLogoChanged(LifecycleEvents\UserUpdatedSellerCompanyLogoEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user changed company logo.
     */
    public function onShipperLogoChanged(LifecycleEvents\UserUpdatedShipperCompanyLogoEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user changed photo.
     */
    public function onPhotoChanged(LifecycleEvents\UserUpdatedPhotoEvent $message)
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was removed.
     */
    public function onRemove(LifecycleEvents\UserWasRemovedEvent $message): void
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was restored after removal.
     */
    public function onRestore(LifecycleEvents\UserWasRestoredEvent $message): void
    {
        $this->syncUserProfile($message->getUserId());
    }

    /**
     * Process data when user was deleted.
     */
    public function onUserDeleted(LifecycleEvents\UserWasDeletedEvent $message)
    {
        $this->commandBus->dispatch(new DeactivateUnknownMatrixUser(
            $this->matrixConnector->getConfig()->getUserNamingStrategy()->matrixId((string) $message->getUserId())
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LifecycleEvents\UserWasVerifiedEvent::class               => ['bus' => 'event.bus', 'method' => 'onUserVerfied'];
        yield LifecycleEvents\UserUpdatedProfileEvent::class            => ['bus' => 'event.bus', 'method' => 'onUserProfileUpdated'];
        yield LifecycleEvents\UserUpdatedBuyerCompanyEvent::class       => ['bus' => 'event.bus', 'method' => 'onUserBuyerCompanyUpdated'];
        yield LifecycleEvents\UserUpdatedSellerCompanyEvent::class      => ['bus' => 'event.bus', 'method' => 'onUserSellerCompanyUpdated'];
        yield LifecycleEvents\UserUpdatedShipperCompanyEvent::class     => ['bus' => 'event.bus', 'method' => 'onUserShipperCompanyUpdated'];
        yield LifecycleEvents\UserUpdatedCompanyAddendumEvent::class    => ['bus' => 'event.bus', 'method' => 'onUserCompanyAddendumUpdated'];
        yield LifecycleEvents\UserUpdatedRelatedCompanyEvent::class     => ['bus' => 'event.bus', 'method' => 'onUserRelatedCompanyUpdated'];
        yield LifecycleEvents\UserGroupChangedEvent::class              => ['bus' => 'event.bus', 'method' => 'onUserGroupChanged'];
        yield LifecycleEvents\UserWasUnrestrictedEvent::class           => ['bus' => 'event.bus', 'method' => 'onUserUnrestricted'];
        yield LifecycleEvents\UserWasRestrictedEvent::class             => ['bus' => 'event.bus', 'method' => 'onUserRestricted'];
        yield LifecycleEvents\UserWasUnblockedEvent::class              => ['bus' => 'event.bus', 'method' => 'onUserUnblocked'];
        yield LifecycleEvents\UserWasBlockedEvent::class                => ['bus' => 'event.bus', 'method' => 'onUserBlocked'];
        yield LifecycleEvents\UserWasDeletedEvent::class                => ['bus' => 'event.bus', 'method' => 'onUserDeleted'];
        yield LifecycleEvents\UserWasMutedEvent::class                  => ['bus' => 'event.bus', 'method' => 'onUserMuted'];
        yield LifecycleEvents\UserWasUnmutedEvent::class                => ['bus' => 'event.bus', 'method' => 'onUserUnmuted'];
        yield LifecycleEvents\UserWasMarkedFakeEvent::class             => ['bus' => 'event.bus', 'method' => 'onUserWasMarkedFake'];
        yield LifecycleEvents\UserWasMarkedRealEvent::class             => ['bus' => 'event.bus', 'method' => 'onUserWasMarkedReal'];
        yield LifecycleEvents\UserUpdatedEmailEvent::class              => ['bus' => 'event.bus', 'method' => 'onUserEmailChanged'];
        yield LifecycleEvents\UserUpdatedLogoEvent::class               => ['bus' => 'event.bus', 'method' => 'onLogoChanged'];
        yield LifecycleEvents\UserUpdatedSellerCompanyLogoEvent::class  => ['bus' => 'event.bus', 'method' => 'onSellerLogoChanged'];
        yield LifecycleEvents\UserUpdatedShipperCompanyLogoEvent::class => ['bus' => 'event.bus', 'method' => 'onShipperLogoChanged'];
        yield LifecycleEvents\UserUpdatedPhotoEvent::class              => ['bus' => 'event.bus', 'method' => 'onPhotoChanged'];
        yield LifecycleEvents\UserWasRemovedEvent::class                => ['bus' => 'event.bus', 'method' => 'onRemove'];
        yield LifecycleEvents\UserWasRestoredEvent::class               => ['bus' => 'event.bus', 'method' => 'onRestore'];
        // Deprecated events
        yield LifecycleEvents\UserUpdatedCompanyLogoEvent::class => ['bus' => 'event.bus', 'method' => 'onSellerLogoChanged'];
        yield LifecycleEvents\UserUpdatedCompanyEvent::class     => ['bus' => 'event.bus', 'method' => 'onUserSellerCompanyUpdated'];
        yield UserWasVerifiedEvent::class                        => ['bus' => 'event.bus', 'method' => 'onUserVerfied'];
        yield UserUpdatedProfileEvent::class                     => ['bus' => 'event.bus', 'method' => 'onUserProfileUpdated'];
        yield UserUpdatedCompanyEvent::class                     => ['bus' => 'event.bus', 'method' => 'onUserSellerCompanyUpdated'];
        yield UserGroupChangedEvent::class                       => ['bus' => 'event.bus', 'method' => 'onUserGroupChanged'];
        yield UserWasUnrestrictedEvent::class                    => ['bus' => 'event.bus', 'method' => 'onUserUnrestricted'];
        yield UserWasRestrictedEvent::class                      => ['bus' => 'event.bus', 'method' => 'onUserRestricted'];
        yield UserWasUnblockedEvent::class                       => ['bus' => 'event.bus', 'method' => 'onUserUnblocked'];
        yield UserWasBlockedEvent::class                         => ['bus' => 'event.bus', 'method' => 'onUserBlocked'];
        yield UserWasDeletedEvent::class                         => ['bus' => 'event.bus', 'method' => 'onUserDeleted'];
        yield UserWasMutedEvent::class                           => ['bus' => 'event.bus', 'method' => 'onUserMuted'];
        yield UserWasUnmutedEvent::class                         => ['bus' => 'event.bus', 'method' => 'onUserUnmuted'];
        yield UserWasMarkedFakeEvent::class                      => ['bus' => 'event.bus', 'method' => 'onUserWasMarkedFake'];
        yield UserWasMarkedRealEvent::class                      => ['bus' => 'event.bus', 'method' => 'onUserWasMarkedReal'];
    }

    /**
     * Sends a message that sync user profile.
     */
    private function syncUserProfile(int $userId, bool $firstSync = false): void
    {
        $this->commandBus->dispatch(new SyncMatrixUser($userId, $firstSync));
    }
}
