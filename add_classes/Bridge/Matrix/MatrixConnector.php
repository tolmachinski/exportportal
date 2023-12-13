<?php

declare(strict_types=1);

namespace App\Bridge\Matrix;

use App\Bridge\Matrix\User\UserReferenceProviderInterface;
use App\Common\Exceptions\ContextAwareException;
use ExportPortal\Matrix\Client\Api\SessionManagementApi;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\LoginRequest;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use ExportPortal\Matrix\Client\Model\UserMatrixIdentifier;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

final class MatrixConnector implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The matrix connector configuration.
     */
    private Configuration $configuration;

    /**
     * The matrix client.
     */
    private MatrixClient $matrixClient;

    /**
     * The Matrix service user.
     */
    private AuthenticatedUser $serviceUser;

    /**
     * The exception handler.
     */
    private ExceptionHandlerInterface $exceptionHandler;

    /**
     * The users provider.
     */
    private UserReferenceProviderInterface $userReferenceProvider;

    /**
     * @param Configuration                  $configuration         the matrix connector configuration
     * @param MatrixClient                   $matrixClient          the matrix client
     * @param UserReferenceProviderInterface $userReferenceProvider the users provider
     * @param null|ExceptionHandlerInterface $exceptionHandler      the exception handler
     */
    public function __construct(
        Configuration $configuration,
        MatrixClient $matrixClient,
        UserReferenceProviderInterface $userReferenceProvider,
        ?ExceptionHandlerInterface $exceptionHandler = null
    ) {
        $this->logger = $configuration->getLogger();
        $this->matrixClient = $matrixClient;
        $this->configuration = $configuration;
        $this->exceptionHandler = $exceptionHandler ?? new ExceptionHandler($this->logger);
        $this->userReferenceProvider = $userReferenceProvider;
    }

    /**
     * Get the matrix connector configuration.
     */
    public function getConfig(): Configuration
    {
        return $this->configuration;
    }

    /**
     * Get the logger.
     *
     * @deprecated
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get the matrix client.
     */
    public function getMatrixClient(): MatrixClient
    {
        return $this->matrixClient;
    }

    /**
     * Get the exception handler.
     */
    public function getExceptionHandler(): ExceptionHandlerInterface
    {
        return $this->exceptionHandler;
    }

    /**
     * Logis user on matrix server using password.
     */
    public function getServiceUserAccount(): AuthenticatedUser
    {
        if (!isset($this->serviceUser)) {
            $configs = $this->getConfig();
            $this->serviceUser = (new AuthenticatedUser())
                ->setAccessToken($configs->getAccessToken())
                ->setHomeServer($configs->getHomeserverHost())
                ->setDeviceId($configs->getDeviceId())
                ->setUserId($configs->getUserId())
            ;
        }

        return $this->serviceUser;
    }

    /**
     * Get the users provider.
     */
    public function getUserReferenceProvider(): UserReferenceProviderInterface
    {
        return $this->userReferenceProvider;
    }

    /**
     * Logis under the user's account on the matrix server.
     *
     * @param int $ttl the the time-to-live of the token milliseconds
     *
     * @return AuthenticatedUser the authenticated user data
     */
    public function loginAsMatrixUser(AuthenticatedUser $admin, string $userToImpersonateId, ?int $ttl = 3600000): AuthenticatedUser
    {
        // Get the host from configs.
        $host = $this->configuration->getHomeserverHost();
        // Prepare request
        $request = new Request('POST', "{$host}/_synapse/admin/v1/users/{$userToImpersonateId}/login", ['Authorization' => "Bearer {$admin->getAccessToken()}"]);

        try {
            $response = $this->matrixClient->getHttpClient()->send($request, ['json' => ['valid_until_ms' => \time() * 1000 + $ttl]]);
            $responseBody = \json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new ContextAwareException(
                sprintf('Failed to login as the user to the matrix server with user "%s".', $userToImpersonateId),
                ['userId' => $userToImpersonateId],
                $e->getCode(),
                $e
            );
        }

        return (new AuthenticatedUser())
            ->setAccessToken($responseBody['access_token'])
            ->setHomeServer($admin->getHomeServer())
            ->setWellKnown($admin->getWellKnown())
            ->setDeviceId($admin->getDeviceId())
            ->setUserId($userToImpersonateId)
        ;
    }

    /**
     * Logis user on matrix server using password.
     */
    public function loginUserWithPassword(string $userId, string $password, ?string $deviceId = null): AuthenticatedUser
    {
        try {
            return $this->matrixClient->getSessionManagementApi()->login(
                (new LoginRequest())
                    ->setType(LoginRequest::TYPE_PASSWORD)
                    ->setPassword($password)
                    ->setDeviceId($deviceId = $deviceId ?? $this->configuration->getDeviceId())
                    ->setIdentifier((new UserMatrixIdentifier())->setUser($userId))
            );
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to login to the matrix server with user "%s".', $userId),
                ['userId' => $userId, 'deviceId' => $deviceId],
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Logis user on matrix server using password.
     */
    public function logoutUser(AuthenticatedUser $user): void
    {
        /** @var SessionManagementApi $sessionManagementApi */
        $sessionManagementApi = \tap($this->matrixClient->getSessionManagementApi(), function (SessionManagementApi $api) use ($user) {
            $api->getConfig()->setAccessToken($user->getAccessToken());
        });

        try {
            $sessionManagementApi->logout();
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to logout from the matrix server with user "%s".', $user->getUserId()),
                ['userId' => $user->getUserId(), 'deviceId' => $user->getDeviceId()],
                $e->getCode(),
                $e
            );
        }
    }
}
