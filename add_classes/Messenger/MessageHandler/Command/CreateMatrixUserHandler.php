<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\ClientException;
use App\Bridge\Matrix\MatrixConnector;
use App\Common\Exceptions\ContextAwareException;
use App\Messenger\Message\Command\CreateMatrixUser;
use App\Messenger\Message\Command\DeactivateUnknownMatrixUser;
use App\Messenger\Message\Event\MatrixUserAddedEvent;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\DummyAuthenticationData;
use ExportPortal\Matrix\Client\Model\RateLimitError;
use ExportPortal\Matrix\Client\Model\RegisterUserRequest;
use ExportPortal\Matrix\Client\Model\RegisterUserResponse as RegisteredUser;
use Matrix_Users_Model as MatrixUsersRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use User_Model as UsersRepository;

/**
 * Creates the user's account on the matrix server.
 *
 * @author Anton Zencenco
 */
final class CreateMatrixUserHandler implements MessageSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The command bus.
     */
    private MessageBusInterface $commandBus;

    /**
     * The events bus.
     */
    private MessageBusInterface $eventBus;

    /**
     * The matrix connector.
     */
    private MatrixConnector $matrixConnector;

    /**
     * The matrix users repository.
     */
    private MatrixUsersRepository $matrisUsersRepository;

    /**
     * The users repository.
     */
    private UsersRepository $usersRepository;

    /**
     * @param MessageBusInterface $eventBus the events bus
     */
    public function __construct(
        MessageBusInterface $commandBus,
        MessageBusInterface $eventBus,
        MatrixConnector $matrixConnector,
        UsersRepository $usersRepository,
        MatrixUsersRepository $matrisUsersRepository
    ) {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
        $this->matrixConnector = $matrixConnector;
        $this->usersRepository = $usersRepository;
        $this->matrisUsersRepository = $matrisUsersRepository;
    }

    /**
     * Handle message.
     */
    public function __invoke(CreateMatrixUser $message)
    {
        if (null === $this->usersRepository->getSimpleUser($userId = $message->getUserId())) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The user %d from the message "%s" does not exist.', $userId, CreateUser::class), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            return;
        }

        // Extract parameters from configs
        $matrixClient = $this->matrixConnector->getMatrixClient();
        $matrixConfigs = $this->matrixConnector->getConfig();

        //region Create matrix user
        try {
            // Create user name.
            $userName = $matrixConfigs->getUserNamingStrategy()->userName((string) $userId);
            // Check if user name is avalable.
            // if (!$this->isUsernameAvailable($matrixClient, $userName)) {
            //     // If it is already taken, then silently fail. And, od course, log it if logger is set.
            //     if ($this->logger) {
            //         $this->logger->warning(sprintf('The username "%s" is already exists on the matrix server.', $userName), [
            //             'userName' => $userName,
            //             'message'  => $message,
            //         ]);
            //     }

            //     return;
            // }

            // Login user
            $createdUser = $this->registerUser($matrixClient, $userName, $userPassword = \bin2hex(\random_bytes(128)), $deviceId = $matrixConfigs->getDeviceId());
            // Write records in storage
            $this->writeReference($createdUser, (int) $userId, $userName, $userPassword, \bin2hex(\random_bytes(128)));
        } catch (\Throwable $e) {
            // Handle the exception
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server');
            // If user was already crated we need to deactivate him
            // if (isset($createdUser)) {
            //     $this->commandBus->dispatch(new DeactivateUnknownMatrixUser($createdUser->getUserId()), [
            //         new DispatchAfterCurrentBusStamp(),
            //         new DelayStamp(5000),
            //     ]);
            // }

            // If the exception was caused by rate limits, then we will throw exception to trigger retry
            if ($e instanceof ClientException && $e->getResponse() instanceof RateLimitError) {
                throw new ContextAwareException(
                    sprintf('Failed to register new user with name "%s" on this matrix server.', $userName),
                    ['userName' => $userName, 'deviceId' => $deviceId],
                    $e->getCode(),
                    $e
                );
            }

            // Else we will roll out the new exception that prevents the command from being retried.
            throw new UnrecoverableMessageHandlingException(
                \sprintf('Failed to created the matrix account for user with ID %s due to error: %s', $userId, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
        //endregion Create matrix user

        // And finally, send an event to create profile room.
        $this->eventBus->dispatch(new Envelope(
            new MatrixUserAddedEvent($userId),
            [new DispatchAfterCurrentBusStamp(), new DelayStamp(5000)]
        ));
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield CreateMatrixUser::class => ['bus' => 'command.bus'];
    }

    /**
     * Write reference into the database.
     */
    protected function writeReference(RegisteredUser $user, int $userId, string $userName, string $userPassword, string $securityPhrase): void
    {
        $matrixConfigs = $this->matrixConnector->getConfig();
        $connection = $this->matrisUsersRepository->getConnection();
        $connection->beginTransaction();

        try {
            $this->matrisUsersRepository->insertOne([
                'mxid'            => $user->getUserId(),
                'version'         => $matrixConfigs->getSyncVersion(),
                'id_user'         => $userId,
                'username'        => $userName,
                'password'        => $userPassword,
                'security_phrase' => $securityPhrase,
            ]);

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }
    }

    /**
     * Determine if username is available on the matrix server.
     *
     * @deprecated v2.27.14
     */
    protected function isUsernameAvailable(MatrixClient $matrixClient, string $userName): bool
    {
        try {
            $availability = $matrixClient->getUserDataApi()->checkUsernameAvailability($userName);
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to make request that check if username "%s" exists on the matrix server.', $userName),
                ['userName' => $userName],
                $e->getCode(),
                $e
            );
        }

        return $availability->getAvailable();
    }

    /**
     * Register user in matrix.
     *
     * @throws ClientException when request failed
     */
    protected function registerUser(MatrixClient $matrixClient, string $userName, string $password, ?string $deviceId): RegisteredUser
    {
        try {
            return $matrixClient->getUserDataApi()->register(
                (new RegisterUserRequest())
                    ->setUsername($userName)
                    ->setPassword($password)
                    ->setDeviceId($deviceId)
                    ->setAuth(new DummyAuthenticationData())
            );
        } catch (ApiException $e) {
            throw ClientException::fromApiException($e);
        }
    }
}
