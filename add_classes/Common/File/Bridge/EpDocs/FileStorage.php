<?php

declare(strict_types=1);

namespace App\Common\File\Bridge\EpDocs;

use App\Common\Database\Model;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\NotFoundException;
use App\Common\File\Bridge\File;
use App\Common\File\Bridge\FileInterface;
use App\Common\File\Bridge\Reference;
use App\Common\File\Bridge\ReferenceInterface;
use App\Common\File\Bridge\StorageInterface as FileStorageInterface;
use App\Plugins\EPDocs\Rest\Objects\AccessToken as AccessTokenObject;
use App\Plugins\EPDocs\Rest\Objects\File as FileObject;
use App\Plugins\EPDocs\Rest\Objects\TemporaryFile as TemporaryFileObject;
use App\Plugins\EPDocs\Rest\Objects\User as UserObject;
use App\Plugins\EPDocs\Rest\Resources\AccessToken as AccessTokenResource;
use App\Plugins\EPDocs\Rest\Resources\File as FileResource;
use App\Plugins\EPDocs\Rest\Resources\FilePermissions as FilePermissionsResource;
use App\Plugins\EPDocs\Rest\Resources\TemporaryFile as TemporaryFileResource;
use App\Plugins\EPDocs\Rest\Resources\User as UserResource;
use App\Plugins\EPDocs\Rest\RestClient;
use App\Plugins\EPDocs\UserAwareTrait;
use Exception;
use Psr\SimpleCache\CacheInterface;

final class FileStorage implements FileStorageInterface
{
    use UserAwareTrait;

    private const CACHE_KEY = 'generic-user';

    private const CACHE_TTL = 60 * 60;

    /**
     * The documents repository.
     */
    private Model $documentsRepository;

    /**
     * The token used to get the reference to document manager user in the API.
     */
    private ?string $managerToken;

    /**
     * The resource instance for files.
     */
    private FileResource $files;

    /**
     * The resource instance file access tokens.
     */
    private AccessTokenResource $accessTokens;

    /**
     * The resource instance for temporary files.
     */
    private TemporaryFileResource $temporaryFiles;

    /**
     * The resource instance for file permissions.
     */
    private FilePermissionsResource $filePermissions;

    /**
     * Create instance of the command.
     */
    public function __construct(
        RestClient $apiClient,
        string $requestOrigin,
        ?string $managerToken = null,
        ?CacheInterface $cachePool = null,
        ?string $cacheKey = self::CACHE_KEY,
        ?int $cacheTtl = self::CACHE_TTL
    ) {
        $this->cacheTtl = $cacheTtl;
        $this->cacheKey = $cacheKey;
        $this->apiClient = $apiClient;
        $this->cachePool = $cachePool;
        $this->managerToken = $managerToken;
        $this->apiRequestOrigin = $requestOrigin;
        $this->usersResource = $this->apiClient->getResource(UserResource::class);
        $this->temporaryFiles = $this->apiClient->getResource(TemporaryFileResource::class);
        $this->filePermissions = $this->apiClient->getResource(FilePermissionsResource::class);
        $this->accessTokens = $this->apiClient->getResource(AccessTokenResource::class);
        $this->files = $this->apiClient->getResource(FileResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePathPrefix(): string
    {
        return (string) $this->apiClient->getHttpClient()->getConfig('base_uri') ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getFiles(iterable $filesIds): iterable
    {
        foreach ($filesIds as $fileId) {
            yield $this->transformRemoteFileToLocalFile(
                $this->files->getFile($fileId)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTemporaryFiles(iterable $temporaryFilesIds): iterable
    {
        $temporaryFiles = $this->temporaryFiles->findFiles(\iterator_to_array($temporaryFilesIds));
        if (count($temporaryFiles) !== \iterator_count($temporaryFilesIds)) {
            throw new NotFoundException('At least one of the temporary files is not found.');
        }

        foreach ($temporaryFiles as $temporaryFile) {
            yield $this->transformTemporaryRemoteFileToLocalFile(
                $temporaryFile
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesAccessTokens(iterable $fileIds, $userId, int $timeout = 90): iterable
    {
        $user = $this->getUserFromApi($userId);
        foreach ($fileIds as $fileId) {
            if (!$this->files->hasFile($fileId)) {
                throw new NotFoundException(
                    \sprintf('The file with the UUID "%s" is not found.', (string) $fileId)
                );
            }

            // Get the file from EPDocs
            $file = $this->files->getFile($fileId);
            // Check access to the file
            if (!$this->filePermissions->hasPermissions($fileId, $user->getId(), FilePermissionsResource::PERMISSION_READ)) {
                new AccessDeniedException(
                    \sprintf(
                        'The user "%s" has no access to the file "%s".',
                        (string) $userId,
                        (string) $fileId
                    )
                );
            }
            $token = $this->accessTokens->createToken($fileId, $timeout);

            yield $this->transformRemoteFileToLocalFile($file) => $this->transformAccessTokenToReference($token);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeTemporaryFiles(iterable $temporaryFiles, int $senderId, array $recipients): iterable
    {
        $owner = $this->getUserFromApi($senderId); // Create or get file owner
        /** @var array<UserObject> */
        $knownUsers = [];
        foreach ($temporaryFiles as $temporaryFile) {
            $file = $this->files->createFile($owner->getId(), $temporaryFile->getUuid()); // Create file
            $otherUsers = !empty($knownUsers) ? $knownUsers : $this->getUsersFromApi(\array_merge($recipients, [$this->managerToken]));
            foreach ($otherUsers as $userId => $user) {
                $permission = FilePermissionsResource::PERMISSION_READ;
                $knownUsers[$userId] = $user; // Store user in memory to reuse them later
                if ($userId === $this->managerToken) {
                    $permission = $permission | FilePermissionsResource::PERMISSION_WRITE | FilePermissionsResource::PERMISSION_EXECUTE;
                }

                $this->filePermissions->createPermissions(
                    $file->getId(),
                    $user->getId(),
                    $permission
                );
            }

            yield $temporaryFile->getUuid() => $this->transformRemoteFileToLocalFile($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function copyFiles(iterable $sourceFiles, int $senderId, array $recipients): iterable
    {
        $owner = $this->getUserFromApi($senderId); // Create or get file owner
        /** @var array<UserObject> */
        $knownUsers = [];
        foreach ($sourceFiles as $sourceFile) {
            $file = $this->files->copyFile($owner->getId(), $sourceFile->getUuid()); // Create file
            $otherUsers = !empty($knownUsers) ? $knownUsers : $this->getUsersFromApi(\array_merge($recipients, [$this->managerToken]));
            foreach ($otherUsers as $userId => $user) {
                $permission = FilePermissionsResource::PERMISSION_READ;
                $knownUsers[$userId] = $user; // Store user in memory to reuse them later
                if ($userId === $this->managerToken) {
                    $permission = $permission | FilePermissionsResource::PERMISSION_WRITE | FilePermissionsResource::PERMISSION_EXECUTE;
                }

                $this->filePermissions->createPermissions(
                    $file->getId(),
                    $user->getId(),
                    $permission
                );
            }

            yield $sourceFile->getUuid() => $this->transformRemoteFileToLocalFile($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createFile($userId, $fileStream, string $name, ?string $type, array $recipients): FileInterface
    {
        $owner = $this->getUserFromApi($userId); // Create or get file owner
        $file = $this->files->createFileFromResource($owner->getId(), $fileStream, $name, $type);
        $knownUsers = [];
        $otherUsers = !empty($knownUsers) ? $knownUsers : $this->getUsersFromApi(\array_merge($recipients, [$this->managerToken]));
        foreach ($otherUsers as $userId => $user) {
            $permission = FilePermissionsResource::PERMISSION_READ;
            $knownUsers[$userId] = $user; // Store user in memory to reuse them later
            if ($userId === $this->managerToken) {
                $permission = $permission | FilePermissionsResource::PERMISSION_WRITE | FilePermissionsResource::PERMISSION_EXECUTE;
            }

            $this->filePermissions->createPermissions(
                $file->getId(),
                $user->getId(),
                $permission
            );
        }

        return $this->transformRemoteFileToLocalFile($file);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFiles(iterable $files): iterable
    {
        foreach ($files as $file) {
            try {
                yield $file => [$this->files->deleteFile($file->getUuid()), null]; // Remove file
            } catch (Exception $e) {
                yield $file => [false, $e];
            }
        }
    }

    /**
     * Transforms the remote file object to the compatible format.
     */
    private function transformRemoteFileToLocalFile(FileObject $file): FileInterface
    {
        return new File(
            null,
            $file->getId(),
            $file->getName(),
            $file->getOriginalName(),
            $file->getExtension(),
            null,
            null,
            $file->getType(),
            $file->getSize(),
            $file->getCreatedAt()
        );
    }

    /**
     * Transforms the remote temporary file object to the compatible format.
     */
    private function transformTemporaryRemoteFileToLocalFile(TemporaryFileObject $temporaryFile): FileInterface
    {
        return new File(
            null,
            $temporaryFile->getId(),
            $temporaryFile->getName(),
            $temporaryFile->getOriginalName(),
            $temporaryFile->getExtension(),
            null,
            null,
            $temporaryFile->getType(),
            $temporaryFile->getSize()
        );
    }

    /**
     * Transforms the remote file access token object to the compatible format.
     */
    private function transformAccessTokenToReference(AccessTokenObject $token): ReferenceInterface
    {
        return new Reference(
            $token->getId(),
            $token->getPath(),
            $token->getPreviewPath(),
            $token->getTtl()
        );
    }
}
