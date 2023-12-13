<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Event;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Exceptions\ContextAwareException;
use App\Messenger\Message\Event\MatrixUserProfileRoomAddedEvent;
use ExportPortal\Matrix\Client\Api\UserDataApi;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Updates the user's profile room's ID into the account data in the user's matrix account.
 *
 * @author Anton Zencenco
 */
final class UpdateUserMatrixAccountDataAfterProfileRoomCreated implements MessageSubscriberInterface
{
    /**
     * The matrix connector.
     */
    private MatrixConnector $matrixConnector;

    /**
     * @param MessageBusInterface $matrixConnector the matrix connector
     */
    public function __construct(MatrixConnector $matrixConnector)
    {
        $this->matrixConnector = $matrixConnector;
    }

    /**
     * ACtivate sync when room created.
     */
    public function __invoke(MatrixUserProfileRoomAddedEvent $message)
    {
        // Extract parameters from configs.
        $serviceUser = $this->matrixConnector->getServiceUserAccount();
        $matrixClient = $this->matrixConnector->getMatrixClient();
        $matrixConfigs = $this->matrixConnector->getConfig();
        $namingStrategy = $matrixConfigs->getNamingStrategy();
        $userMxid = $namingStrategy->matrixId((string) $message->getUserId());

        try {
            $this->updateUserAccountData(
                $matrixClient,
                $loggedUser = $this->matrixConnector->loginAsMatrixUser($serviceUser, $userMxid),
                $message->getRoomId(),
                $matrixConfigs->getEventNamespace()
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
        yield MatrixUserProfileRoomAddedEvent::class => ['bus' => 'event.bus'];
    }

    /**
     * Update user account data.
     */
    private function updateUserAccountData(MatrixClient $matrixClient, AuthenticatedUser $user, string $roomId, string $eventNamspace): void
    {
        /** @var UserDataApi $userDataApi */
        $userDataApi = \tap($matrixClient->getUserDataApi(), function (UserDataApi $api) use ($user) {
            $api->getConfig()->setAccessToken($user->getAccessToken());
        });

        try {
            $userDataApi->setAccountData((object) ['room_id' => $roomId], $user->getUserId(), "{$eventNamspace}.profile");
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to update user "%s" account data on this matrix server.', $user->getUserId()),
                ['userId' => $user->getUserId(), 'roomId' => $roomId],
                $e->getCode(),
                $e
            );
        }
    }
}
