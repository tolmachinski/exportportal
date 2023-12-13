<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Database\Model;
use App\Common\Exceptions\ContextAwareException;
use App\Messenger\Message\Command\DeactivateKnownMatrixUser;
use App\Messenger\Message\Command\DeactivateUnknownMatrixUser;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Matrix_Users_Model as MatrixUsersRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Deactivates the user's account on the matrix server.
 *
 * @author Anton Zencenco
 */
final class DeactivateMatrixUserHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The matrix connector.
     */
    private MatrixConnector $matrixConnector;

    /**
     * The matrix users repository.
     */
    private MatrixUsersRepository $matrisUsersRepository;

    /**
     * @param MatrixConnector $matrixConnector the matrix connector
     */
    public function __construct(MatrixConnector $matrixConnector, MatrixUsersRepository $matrisUsersRepository)
    {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->matrixConnector = $matrixConnector;
        $this->matrisUsersRepository = $matrisUsersRepository;
    }

    /**
     * Deactivate matrix user by user ID.
     */
    public function deactivateUserById(DeactivateKnownMatrixUser $message)
    {
        if (null === $userReference = $this->matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId = $message->getUserId())) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The sync reference for user ID "%d" is not present in the sync table.', $userId), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            return;
        }
        if ($userReference['is_deactivated']) {
            // If user is already deactivated, then we have nothing to do here.
            return;
        }

        $this->deactivateUser($userReference['mxid'], $userReference);
    }

    /**
     * Deactivate matrix user by matrix ID.
     */
    public function deactivateUserByMatrixId(DeactivateUnknownMatrixUser $message)
    {
        $this->deactivateUser($message->getMatrixUserId());
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield DeactivateKnownMatrixUser::class   => ['bus' => 'command.bus', 'method' => 'deactivateUserById'];
        yield DeactivateUnknownMatrixUser::class => ['bus' => 'command.bus', 'method' => 'deactivateUserByMatrixId'];
    }

    /**
     * Deactivate user.
     */
    protected function deactivateUser(string $userMatrixId, ?array $userReference = null): void
    {
        // Extract parameters from configs
        $matrixClient = $this->matrixConnector->getMatrixClient();
        $matrixConfigs = $this->matrixConnector->getConfig();

        try {
            // Deactivate user
            $isDeactivated = $this->deactivateAuthenticatedUser($matrixClient, $this->matrixConnector->getServiceUserAccount(), $userMatrixId);
        } catch (ContextAwareException | RequestException $e) {
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');
            if ($e instanceof RequestException) {
                // Roll exception forward - maybe we can recover in the next try.
                throw $e;
            }

            return;
        }

        //region Write records in storage
        if (!$isDeactivated) {
            // Log this issue.
            if ($this->logger) {
                $this->logger->error(sprintf('Failed to deactivate user "%s".', $userMatrixId), $errorContext = [
                    'mxid'       => $userMatrixId,
                    'userId'     => $userReference['id_user'] ?? null,
                    'homeserver' => $homeserver = $matrixConfigs->getHomeserverHost(),
                ]);
            }

            throw new ContextAwareException(sprintf('Failed to dactivate user "%s" on homeserver "%s".', $userMatrixId, $homeserver), $errorContext);
        }

        if (null === $userReference) {
            // We will reach this place only when failed to store matrix user in the first place.
            return;
        }

        $this->updateRecord($this->matrisUsersRepository, $userReference['id']);
        //endregion Write records in storage
    }

    /**
     * Update record in the database.
     */
    protected function updateRecord(Model $matrixUsersRepositry, int $syncId): void
    {
        $connection = $matrixUsersRepositry->getConnection();
        $connection->beginTransaction();

        try {
            $matrixUsersRepositry->updateOne($syncId, ['is_deactivated' => true]);
            $connection->commit();
        } catch (\Throwable $e) {
            try {
                // If failed to delete, then we will set the special flag that will indicate that this record ust be removed later.
                $matrixUsersRepositry->updateOne($syncId, ['has_pending_removal' => true]);
            } catch (\Throwable $e) {
                // If failed, then just let it be.
            }
            $connection->rollBack();

            throw $e;
        }
    }

    /**
     * Deactivates the matrix user's account.
     *
     * @return bool if account deactivated
     */
    protected function deactivateAuthenticatedUser(MatrixClient $matrixClient, AuthenticatedUser $admin, string $userToDeactivate): bool
    {
        // Get the host from connector configs.
        $matrixHost = $this->matrixConnector->getConfig()->getHomeserverHost();
        // Create request
        $request = new Request('POST', "{$matrixHost}/_synapse/admin/v1/deactivate/{$userToDeactivate}", ['Authorization' => "Bearer {$admin->getAccessToken()}"]);

        try {
            $response = $matrixClient->getHttpClient()->send($request, ['json' => ['erase' => true]]);
            $responseBody = \json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new ContextAwareException(
                sprintf('Failed to deactivatethe user "%s" at the matrix server.', $userToDeactivate),
                ['userId' => $userToDeactivate],
                $e->getCode(),
                $e
            );
        }

        return 'success' === $responseBody['id_server_unbind_result'];
    }
}
