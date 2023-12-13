<?php

declare(strict_types=1);

namespace App\Envelope\Reader;

use App\Common\Database\Model;
use App\Common\Exceptions\NotFoundException;
use Psr\SimpleCache\CacheInterface;

final class DatabaseEntityReader implements EntityReaderInterface
{
    /**
     * The entity repository.
     */
    private Model $entityRepository;

    /**
     * The additional filters for enitity query.
     */
    private array $filters;

    /**
     * The list of included enitity relations.
     */
    private array $relations;

    /**
     * The cache pull for enitity.
     */
    private CacheInterface $cachePool;

    /**
     * Creates the instance of the enitity reader.
     */
    public function __construct(Model $entityRepository, array $filters = [], array $relations = [], ?CacheInterface $cachePool = null)
    {
        $this->entityRepository = $entityRepository;
        $this->filters = $filters;
        $this->relations = $relations;
        $this->cachePool = $cachePool;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity($enitityId): array
    {
        $entity = null;
        if (null !== $enitityId) {
            if (null !== $this->cachePool && $this->cachePool->has($cacheKey = (string) $enitityId)) {
                $entity = $this->cachePool->get($cacheKey);
            } else {
                $entity = $this->getEntityStorage()->findOne((int) $enitityId, [
                    'with'       => $this->relations,
                    'conditions' => $this->filters,
                ]);
            }
        }

        if (null === $entity) {
            throw new NotFoundException(sprintf('The entity with ID %s is not found', varToString($enitityId)));
        }

        return $entity;
    }

    /**
     * Get the entity repository.
     */
    private function getEntityStorage(): Model
    {
        return $this->entityRepository;
    }
}
