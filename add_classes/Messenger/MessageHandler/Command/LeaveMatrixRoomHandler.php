<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Exceptions\ContextAwareException;
use App\Common\Exceptions\NotFoundException;
use App\Messenger\Message\Command\LeaveMatrixRoomById;
use App\Messenger\Message\Command\LeaveMatrixRoomByMxId;
use ExportPortal\Matrix\Client\Api\RoomMembershipApi;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\Leave;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Makes user to leave the matrix room.
 *
 * @author Anton Zencenco
 */
final class LeaveMatrixRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
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
     * Leave room with user ID.
     */
    public function onLeaveRoomById(LeaveMatrixRoomById $message): void
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

        $this->leaveRoom($message->getRoomId(), $userReference['mxid'], $message->getReason());
    }

    /**
     * Leave room with matrix user ID.
     */
    public function onLeaveRoomByMxId(LeaveMatrixRoomByMxId $message): void
    {
        $this->leaveRoom($message->getRoomId(), $message->getMxId(), $message->getReason());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield LeaveMatrixRoomById::class => ['bus' => 'command.bus', 'method' => 'onLeaveRoomById'];
        yield LeaveMatrixRoomByMxId::class => ['bus' => 'command.bus', 'method' => 'onLeaveRoomByMxId'];
    }

    /**
     * Leave the matrix room.
     */
    protected function leaveRoom(string $roomId, string $mxId, ?string $reason = null): void
    {
        // Extract parameters from configs.
        $matrixClient = $this->matrixConnector->getMatrixClient();

        try {
            $user = $serviceUser = $this->matrixConnector->getServiceUserAccount();
            if ($mxId !== $serviceUser->getUserId()) {
                $user = $this->matrixConnector->loginAsMatrixUser($serviceUser, $mxId);
            }

            // Leave to the room
            $this->doLeaveRoom($matrixClient, $user, $roomId, $reason);
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
     * Send request to leave room to the matrix server.
     */
    protected function doLeaveRoom(MatrixClient $matrixClient, AuthenticatedUser $admin, string $roomId, ?string $reason = null): array
    {
        $roomApi = \tap($matrixClient->getRoomMembershipApi(), function (RoomMembershipApi $api) use ($admin) {
            $api->getConfig()->setAccessToken($admin->getAccessToken());
        });

        try {
            return $roomApi->leaveRoom((new Leave())->setReason($reason), $roomId);
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to leave the room "%s" on this matrix server.', $roomId),
                ['userId' => $admin->getUserId(), 'roomId' => $roomId],
                $e->getCode(),
                $e
            );
        }
    }
}
