<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Exceptions\ContextAwareException;
use App\Common\Exceptions\NotFoundException;
use App\Messenger\Message\Command\JoinMatrixRoomWithId;
use App\Messenger\Message\Command\JoinMatrixRoomWithMxId;
use ExportPortal\Matrix\Client\Api\RoomMembershipApi;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\Join;
use ExportPortal\Matrix\Client\Model\JoinedRoomReference;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Makes user to join into the matrix room.
 *
 * @author Anton Zencenco
 */
final class JoinMatrixRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The matrix connector.
     */
    protected MatrixConnector $matrixConnector;

    public function __construct(MatrixConnector $matrixConnector)
    {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->matrixConnector = $matrixConnector;
    }

    /**
     * Join room with user ID.
     */
    public function onJoinRoomWithId(JoinMatrixRoomWithId $message): void
    {
        if (null === $userReference = $this->matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId = $message->getUserId())) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The sync reference for user with ID "%d" is not present in the sync table.', $userId), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            throw new NotFoundException(
                \sprintf('The matrix reference for user "%s" is not found', $userId)
            );
        }

        $this->joinRoom($message->getRoomId(), $userReference['mxid'], $message->getReason());
    }

    /**
     * Join room with matrix user ID.
     */
    public function onJoinRoomWithMxId(JoinMatrixRoomWithMxId $message): void
    {
        $this->joinRoom($message->getRoomId(), $message->getMxId(), $message->getReason());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield JoinMatrixRoomWithId::class => ['bus' => 'command.bus', 'method' => 'onJoinRoomWithId'];
        yield JoinMatrixRoomWithMxId::class => ['bus' => 'command.bus', 'method' => 'onJoinRoomWithMxId'];
    }

    /**
     * Join the room.
     */
    protected function joinRoom(string $roomId, string $mxId, ?string $reason = null): void
    {
        // Extract parameters from configs.
        $matrixClient = $this->matrixConnector->getMatrixClient();

        try {
            $user = $serviceUser = $this->matrixConnector->getServiceUserAccount();
            if ($mxId !== $serviceUser->getUserId()) {
                $user = $this->matrixConnector->loginAsMatrixUser($serviceUser, $mxId);
            }

            // Join to the room
            $this->doJoinRoom($matrixClient, $user, $roomId, $reason);
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
                if ($user->getUserId() !== $serviceUser->getUserId()) {
                    $this->matrixConnector->logoutUser($user);
                }
            } catch (\Throwable $e) {
                // Just roll with it - we don't need to bother with logout.
            }
        }
    }

    /**
     * send request to join room to the matrix server.
     */
    protected function doJoinRoom(MatrixClient $matrixClient, AuthenticatedUser $admin, string $roomId, ?string $reason = null): JoinedRoomReference
    {
        $roomApi = \tap($matrixClient->getRoomMembershipApi(), function (RoomMembershipApi $api) use ($admin) {
            $api->getConfig()->setAccessToken($admin->getAccessToken());
        });

        try {
            return $roomApi->joinRoom((new Join())->setReason($reason), $roomId);
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to join the room "%s" on this matrix server.', $roomId),
                ['userId' => $admin->getUserId(), 'roomId' => $roomId],
                $e->getCode(),
                $e
            );
        }
    }
}
