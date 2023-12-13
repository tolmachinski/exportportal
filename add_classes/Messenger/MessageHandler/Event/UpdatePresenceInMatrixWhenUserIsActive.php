<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Exceptions\ContextAwareException;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use App\Messenger\Message\Event\UserWasActiveEvent;
use ExportPortal\Matrix\Client\Api\PresenceApi;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use ExportPortal\Matrix\Client\Model\UserPresence;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Updates the user's presence on the matrix server when user was active.
 *
 * @author Anton Zencenco
 */
final class UpdatePresenceInMatrixWhenUserIsActive implements MessageSubscriberInterface
{
    use LoggerAwareTrait;

    /**
     * The matrix connector.
     */
    private MatrixConnector $matrixConnector;

    /**
     * @param MatrixConnector $matrixConnector the matrix connector
     */
    public function __construct(MatrixConnector $matrixConnector)
    {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->matrixConnector = $matrixConnector;
    }

    public function __invoke(LifecycleEvents\UserWasActiveEvent $message)
    {
        // If user ID is empty then leave immediately
        if (empty($syncId = $message->getUserId())) {
            return;
        }

        // Retrieve the sync reference.
        if (null === $userReference = $this->matrixConnector->getUserReferenceProvider()->getReferenceByUserId($syncId)) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The sync reference "%d" is not present in the sync table.', $syncId), [
                    'syncId'  => $syncId,
                    'message' => $message,
                ]);
            }

            return;
        }

        try {
            $this->updateUserPresence(
                $this->matrixConnector->getMatrixClient(),
                $loggedUser = $this->matrixConnector->loginAsMatrixUser($this->matrixConnector->getServiceUserAccount(), $userReference['mxid'])
            );
        } catch (ContextAwareException | RequestException $e) {
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');
            if ($e instanceof RequestException) {
                // Roll exception forward - maybe we can recover in the next try.
                throw $e;
            }

            return;
        } finally {
            // After that we need to logout current users
            try {
                $this->matrixConnector->logoutUser($loggedUser);
            } catch (\Throwable $e) {
                // Just roll with it - we don't need to bother with logout.
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LifecycleEvents\UserWasActiveEvent::class => ['bus' => 'event.bus'];
        yield UserWasActiveEvent::class                 => ['bus' => 'event.bus'];
    }

    /**
     * Updates the user's presence.
     */
    protected function updateUserPresence(MatrixClient $matrixClient, AuthenticatedUser $user): void
    {
        /** @var PresenceApi $presenceApi */
        $presenceApi = \tap($matrixClient->getPresenceApi(), function (PresenceApi $api) use ($user) {
            $api->getConfig()->setAccessToken($user->getAccessToken());
        });

        try {
            $presenceApi->setPresence((new UserPresence())->setPresence(UserPresence::PRESENCE_ONLINE), $user->getUserId());
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to update user presence on this matrix server.'),
                ['userId' => $user->getUserId()],
                $e->getCode(),
                $e
            );
        }
    }
}
