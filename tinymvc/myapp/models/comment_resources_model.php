<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\QueryException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Comment_Resources model.
 */
class Comment_Resources_Model extends Model
{
    /**
     * The table name.
     */
    protected string $table = 'comment_resources';

    /**
     * The table primary key.
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'      => Types::INTEGER,
        'id_type' => Types::INTEGER,
        'context' => Types::JSON,
    ];

    /**
     * Get the resource.
     *
     * @throws NotFoundException if resource with such ID is not found
     * @throws QueryException    if Db query failed
     */
    public function get_resource(?int $resourceId): ?array
    {
        try {
            if (null === ($resource = $resourceId ? $this->find($resourceId) : null)) {
                throw new NotFoundException("The resource with ID '{$resourceId}' is not found.");
            }

            return $resource;
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    /**
     * Get the resource.
     *
     * @throws NotFoundException if resource is not found
     * @throws QueryException    if DB query failed
     */
    public function get_resource_by_conditions(array $params): ?array
    {
        try {
            $resource = $this->findRecord(
                null,
                $this->getTable(),
                null,
                null,
                null,
                $params
            );

            if (empty($resource)) {
                throw new NotFoundException('The resource is not found.');
            }

            return $resource;
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }
    }

    /**
     * Add one recource.
     */
    public function add(array $resource): int
    {
        return (int) $this->insertOne($resource, true);
    }

    /**
     * Scope a query to filter by resource ID.
     */
    protected function scopeId(QueryBuilder $builder, int $resourceId): void
    {
        $this->scopePrimaryKey($builder, $this->getTable(), $this->getPrimaryKey(), $resourceId);
    }

    /**
     * Scope comment query by type.
     */
    protected function scopeType(QueryBuilder $builder, int $type): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_type',
                $builder->createNamedParameter($type, ParameterType::INTEGER, $this->nameScopeParameter('typeId'))
            )
        );
    }

    /**
     * Scope comment query by token.
     */
    protected function scopeToken(QueryBuilder $builder, string $hash): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'token',
                $builder->createNamedParameter($hash, ParameterType::STRING, $this->nameScopeParameter('tokenHash'))
            )
        );
    }

    /**
     * Resolves static relationships with type.
     */
    protected function type(): RelationInterface
    {
        return $this->belongsTo(Comment_Types_Model::class, 'id_type')->disableNativeCast();
    }
}

// End of file comment_resources_model.php
// Location: /tinymvc/myapp/models/comment_resources_model.php
