<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Exceptions\ContextAwareException;
use App\Messenger\Message\Command\DeleteMatrixRoom;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Deletes the room from matrix server.
 *
 * @author Anton Zencenco
 */
final class DeleteMatrixRoomHandler implements MessageSubscriberInterface, LoggerAwareInterface
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

    /**
     * Handle delete room message.
     */
    public function __invoke(DeleteMatrixRoom $message)
    {
        $admin = $this->matrixConnector->getServiceUserAccount();
        $legacyMode = false;
        $deletionData = [
            'new_room_user_id' => $message->getNewRoomUserId(),
            'room_name'        => $message->getNewRoomName(),
            'message'          => $message->getNewRoomMessage(),
            'block'            => $message->getBlock(),
            'purge'            => $message->getPurge(),
        ];

        try {
            try {
                $result = $this->deleteRoom($admin, $message->getRoomId(), $deletionData, $legacyMode);
            } catch (ClientException $e) {
                $response = $e->getResponse();
                if (null === $response) {
                    throw $e;
                }

                $responseBody = \json_decode($e->getResponse()->getBody()->getContents(), false, 512, \JSON_THROW_ON_ERROR);
                if ('M_UNRECOGNIZED' === ($responseBody->errcode ?? null)) {
                    $legacyMode = true;
                    $result = $this->deleteRoom($admin, $message->getRoomId(), $deletionData, $legacyMode);
                } else {
                    throw $e;
                }
            }
        } catch (BadResponseException $e) {
            if (!$message->getPurge()) {
                throw $e;
            }
            // If we failed to make a request with the `purge` flag set, then we will try again in force mode.
            $result = $this->deleteRoom($admin, $message->getRoomId(), $deletionData, $legacyMode, true);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield DeleteMatrixRoom::class => ['bus' => 'command.bus'];
    }

    /**
     * Deactivates the matrix user's account.
     */
    private function deleteRoom(AuthenticatedUser $admin, string $roomId, array $deletionData, bool $legacy = false, bool $force = false)
    {
        // Get the host from configs.
        $host = $this->matrixConnector->getConfig()->getHomeserverHost();
        $requestUrl = "{$host}/_synapse/admin/v1/rooms/{$roomId}";
        $requestMethod = 'DELETE';
        // Given that Synapse recently changed the Rooms API endpoint for deletion and we don't know
        // the capabilities of our local service, the support for legacy request was added.
        if ($legacy) {
            $requestUrl = "{$host}/_synapse/admin/v1/rooms/{$roomId}/delete";
            $requestMethod = 'POST';
        }
        // Create request
        $request = new Request($requestMethod, $requestUrl, ['Authorization' => "Bearer {$admin->getAccessToken()}"]);
        $requestBody = \array_merge($deletionData, ['force_purge' => $force]);

        try {
            $response = $this->matrixConnector->getMatrixClient()->getHttpClient()->send($request, ['json' => $requestBody]);
            $responseBody = \json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (BadResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ContextAwareException(
                sprintf('Failed to delete the room "%s" from the matrix server.', $roomId),
                ['room' => $roomId],
                $e->getCode(),
                $e
            );
        }

        return (object) \arrayCamelizeAssocKeys($responseBody);
    }
}
