<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Exceptions\ContextAwareException;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use App\Messenger\Message\Event\UserWasActiveEvent;
use DateTimeImmutable;
use ExportPortal\Matrix\Client\Api\RoomParticipationApi;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Updates the user's activity date on the matrix server when user was active.
 *
 * @author Anton Zencenco
 *
 * @deprecated
 */
final class UpdateActivityInMatrixWhenUserIsActive implements MessageSubscriberInterface
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
                $this->logger->error(sprintf('The sync reference "%d" is not present in the sync table.', $syncId), [
                    'syncId'  => $syncId,
                    'message' => $message,
                ]);
            }

            return;
        }
        if (null === $userReference['profile_room_id']) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The sync reference "%d" does not contain the profile room ID.', $syncId), [
                    'syncId'  => $syncId,
                    'message' => $message,
                ]);
            }

            return;
        }

        try {
            $this->updateAcitivityDate(
                $this->matrixConnector->getMatrixClient(),
                $loggedUser = $this->matrixConnector->loginAsMatrixUser($this->matrixConnector->getServiceUserAccount(), $userReference['mxid']),
                $userReference['profile_room_id'],
                $userReference['user'],
                $this->matrixConnector->getConfig()->getEventNamespace()
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
     * Updates the user's private profile room.
     */
    protected function updateAcitivityDate(
        MatrixClient $matrixClient,
        AuthenticatedUser $user,
        string $roomId,
        array $userData,
        string $eventNamspace
    ): void {
        /** @var RoomParticipationApi $roomApi */
        $roomApi = \tap($matrixClient->getRoomParticipationApi(), function (RoomParticipationApi $api) use ($user) {
            $api->getConfig()->setAccessToken($user->getAccessToken());
        });

        /** @var DateTimeImmutable $date */
        $date = $userData['last_active'] instanceof DateTimeImmutable ? $userData['last_active'] : new DateTimeImmutable();
        $timeZone = $date->getTimezone();

        try {
            $roomApi->setRoomStateWithKey($roomId, "{$eventNamspace}.last_active", '', (object) [
                'date'     => $date->format(\DateTime::RFC2822),
                'timezone' => [
                    'name'   => $timeZone->getName(),
                    'offset' => $timeZone->getOffset($date),
                ],
            ]);
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to update user activity date in room with name "%s" on this matrix server.', $roomId),
                ['userId' => $user->getUserId(), 'roomId' => $roomId],
                $e->getCode(),
                $e
            );
        }
    }
}
