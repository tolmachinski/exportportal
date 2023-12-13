<?php

declare(strict_types=1);

namespace App\Common\Assets;

use JsonException;
use Psr\Cache\CacheItemPoolInterface;

final class EntrypointLookup implements EntrypointLookupInterface, IntegrityDataProviderInterface
{
    private const JS_TYPE = 'js';

    private const CSS_TYPE = 'css';

    /**
     * The path to the entrypoint json file.
     *
     * @var string
     */
    private $entrypointJsonPath;

    /**
     * The data from entrypointjson file.
     *
     * @var array
     */
    private $entriesData;

    /**
     * The list of returned files.
     *
     * @var array
     */
    private $returnedFiles = array();

    /**
     * The cache pool.
     *
     * @var null|CacheItemPoolInterface
     */
    private $cache;

    /**
     * The cache key.
     *
     * @var null|string
     */
    private $cacheKey;

    /**
     * The flag that indicates if strict mode is enabled.
     *
     * @var bool
     */
    private $strictMode;

    /**
     * Creates instance of entrypoints lookup service.
     */
    public function __construct(
        string $entrypointJsonPath,
        ?CacheItemPoolInterface $cache = null,
        ?string $cacheKey = null,
        ?bool $strictMode = true
    ) {
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
        $this->strictMode = $strictMode ?? false;
        $this->entrypointJsonPath = $entrypointJsonPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getJSFiles(string $entryName): iterable
    {
        yield from $this->getEntryFiles($entryName, static::JS_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCssFiles(string $entryName): iterable
    {
        yield from $this->getEntryFiles($entryName, static::CSS_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegrityData(): array
    {
        return $this->getEntriesData()['integrity'] ?? array();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllEntries(): array
    {
        return $this->getEntriesData()['entrypoints'] ?? array();
    }

    /**
     * {@inheritdoc}
     */
    public function resetFiles(): void
    {
        $this->returnedFiles = array();
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->resetFiles();
        $this->entriesData = null;
    }

    /**
     * Finds the files for entry.
     */
    private function getEntryFiles(string $entryName, string $key): array
    {
        $this->validateEntryName($entryName);
        $entryData = $this->getAllEntries()[$entryName] ?? array();
        $entryFiles = array_diff($entryData[$key] ?? array(), $this->returnedFiles ?? array());
        if (!empty($entryFiles)) {
            $this->saveNewFiles($entryFiles);
        }

        return $entryFiles;
    }

    /**
     * Saves new files records to prevent dublicates.
     */
    private function saveNewFiles(array $files): void
    {
        $this->returnedFiles = array_merge(
            $this->returnedFiles,
            array_values(
                array_diff($files, $this->returnedFiles ?? array())
            )
        );
    }

    /**
     * Validates entry name.
     *
     * @throws EntrypointNotFoundException if an entry name is passed that does not exist in entrypoints.json
     */
    private function validateEntryName(string $entryName): void
    {
        $allEntries = $this->getAllEntries();
        $entryData = $allEntries[$entryName] ?? null;
        if (null === $entryData && $this->strictMode) {
            $withoutExtension = substr($entryName, 0, strrpos($entryName, '.') ?: \mb_strlen($entryName, 'utf-8'));
            if (isset($allEntries[$withoutExtension])) {
                throw new EntrypointNotFoundException(
                    sprintf('Could not find the entry "%s". Try "%s" instead (without the extension).', $entryName, $withoutExtension)
                );
            }

            throw new EntrypointNotFoundException(
                sprintf('Could not find the entry "%s" in "%s". Found: %s.', $entryName, $this->entrypointJsonPath, implode(', ', array_keys($allEntries)))
            );
        }
    }

    /**
     * Get entry data from file or cache (if exists).
     *
     * @throws InvalidArgumentException if failed to read data from entrypoints.json
     */
    private function getEntriesData(): array
    {
        if (null !== $this->entriesData) {
            return $this->entriesData;
        }

        if ($this->cache && $this->entriesData = $this->getCachedEntriesData()) {
            return $this->entriesData;
        }

        if (!file_exists($this->entrypointJsonPath)) {
            if (!$this->strictMode) {
                return array();
            }

            throw new \InvalidArgumentException(
                sprintf('Could not find the entrypoints file from Webpack: the file "%s" does not exist.', $this->entrypointJsonPath)
            );
        }

        try {
            $this->entriesData = json_decode(file_get_contents($this->entrypointJsonPath), true, \JSON_THROW_ON_ERROR) ?? array();
        } catch (JsonException $exception) {
            throw new \InvalidArgumentException(
                sprintf('There was a problem JSON decoding the "%s" file: %s', $this->entrypointJsonPath, $exception->getMessage()),
                0,
                $exception
            );
        }

        if (!isset($this->entriesData['entrypoints'])) {
            throw new \InvalidArgumentException(
                sprintf('Could not find an "entrypoints" key in the "%s" file', $this->entrypointJsonPath)
            );
        }

        if ($this->cache) {
            $this->cacheEntriesData($this->entriesData);
        }

        return $this->entriesData;
    }

    /**
     * Get cached entry data.
     */
    private function getCachedEntriesData(): ?array
    {
        if (null === $this->cache) {
            return array();
        }

        $cachedItem = $this->cache->getItem($this->cacheKey);
        if ($cachedItem->isHit()) {
            return $cachedItem->get();
        }

        return null;
    }

    /**
     * Cache entry data.
     */
    private function cacheEntriesData(array $entryData): void
    {
        if (null === $this->cache) {
            return;
        }

        $this->cache->save(
            $this->cache->getItem($this->cacheKey)
                ->set($entryData)
        );
    }
}
