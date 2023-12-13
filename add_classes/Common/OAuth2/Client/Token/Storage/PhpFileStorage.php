<?php

declare(strict_types=1);

namespace App\Common\OAuth2\Client\Token\Storage;

use InvalidArgumentException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToWriteFile;
use League\OAuth2\Client\Token\AccessToken;
use RuntimeException;

class PhpFileStorage implements StorageInterface
{
    /**
     * The path to the file with the access token.
     */
    private string $targetDirectory;

    /**
     * The storage options.
     */
    private array $options;

    /**
     * The filesystem instance.
     */
    private FilesystemOperator $filesystem;

    public function __construct()
    {
        $this->targetDirectory = '/';
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter(), ['visibility' => 'private']);
        $this->options = [];
    }

    /**
     * Sets the options for storage.
     */
    public function updateOptions(array $options = []): void
    {
        $targetDirectory = $options['target'] ?? null;
        if (null === $targetDirectory) {
            throw new InvalidArgumentException('The set must have the the key "target".');
        }
        if (!\is_string($targetDirectory)) {
            throw new InvalidArgumentException('The "target" must be a string.');
        }

        $this->targetDirectory = $targetDirectory;
        $this->filesystem = new Filesystem(new LocalFilesystemAdapter($targetDirectory), ['visibility' => $options['visibility'] ?? 'private']);
        $this->options = $options;
    }

    /**
     * Get the storage options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Reads from the access token from storage.
     */
    public function readAccessToken(): ?AccessToken
    {
        $token = $this->readTokenFromFile();
        if (null === $token) {
            return null;
        }

        return new AccessToken($token);
    }

    /**
     * Writes access token into the storage.
     */
    public function writeAccessToken(AccessToken $accessToken): void
    {
        try {
            $this->removeAccessToken();
        } catch (\Throwable $e) {
            // Nothing to do here
        }

        try {
            $this->filesystem->write('/app_oauth2_token.php', $this->generateFileContent($accessToken));
        } catch (UnableToWriteFile $e) {
            throw new RuntimeException('Failed to write access token to the file', 0, $e);
        }
    }

    /**
     * Removes access token from storage.
     */
    public function removeAccessToken(): void
    {
        if (!$this->filesystem->fileExists('/app_oauth2_token.php')) {
            return;
        }

        if (!$this->filesystem->delete('/app_oauth2_token.php')) {
            throw new RuntimeException('Failed to delete file with access token.');
        }
    }

    /**
     * Reads token from the file.
     */
    private function readTokenFromFile(): ?array
    {
        if (!$this->filesystem->fileExists('/app_oauth2_token.php')) {
            return null;
        }

        return (function (): array {
            return include "{$this->targetDirectory}/app_oauth2_token.php";
        })();
    }

    /**
     * Generates the php file contents.
     */
    private function generateFileContent(AccessToken $accessToken): string
    {
        $parameters = [
            'expires'           => $accessToken->getExpires(),
            'access_token'      => $accessToken->getToken(),
            'refresh_token'     => $accessToken->getRefreshToken(),
            'resource_owner_id' => $accessToken->getResourceOwnerId(),
        ] + $accessToken->getValues();

        return \sprintf(
            "<?php return [\n%s\n];",
            \implode("\n", \array_map(
                fn (string $key, $value) => \sprintf("\t\"%s\" => %s,", $key, null === $value ? 'null' : (\is_string($value) ? "\"{$value}\"" : $value)),
                array_keys($parameters),
                $parameters
            ))
        );
    }
}
