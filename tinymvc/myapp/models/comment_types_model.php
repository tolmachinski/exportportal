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
 * Comment_Types model.
 */
class Comment_Types_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    protected const UPDATED_AT = 'date_updated';

    /**
     * The table name.
     */
    protected string $table = 'comment_types';

    /**
     * The table primary key.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'             => Types::INTEGER,
        self::CREATED_AT => Types::DATETIME_IMMUTABLE,
        self::UPDATED_AT => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Get all comments types.
     */
    public function get_all_types(): array
    {
        $types = $this->findAll();

        return $types ?? [];
    }

    /**
     * Get the comment type by ID.
     *
     * @throws NotFoundException if records with such ID is not found
     * @throws QueryException    on query exception
     */
    public function get_type(int $id): array
    {
        $type = null;

        try {
            if (null !== $id) {
                $type = $this->findOneBy([
                    'columns'    => ['`id`', '`alias`', '`name`', '`date_created` AS `createdAt`', '`date_updated` AS `updatedAt`'],
                    'conditions' => ['id' => $id],
                ]);
            }

            if (null === $type) {
                throw new NotFoundException("The type with id {$id} is not found.");
            }
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }

        return $type;
    }

    /**
     * Get the comment type by alias.
     *
     * @throws NotFoundException if records with such alias is not found
     * @throws QueryException    on query exception
     */
    public function get_type_by_alias(?string $alias): array
    {
        $type = null;

        try {
            if (null !== $alias) {
                $type = $this->findOneBy([
                    'columns'    => ['`id`', '`alias`', '`name`', '`date_created` AS `createdAt`', '`date_updated` AS `updatedAt`'],
                    'conditions' => ['alias' => $alias],
                ]);
            }

            if (null === $type) {
                throw new NotFoundException("The type with alias '{$alias}' is not found.");
            }
        } catch (NotFoundException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw QueryException::executionFailed($this->getHandler(), $exception);
        }

        return $type;
    }

    /**
     * Add one comment type.
     */
    public function add(string $name, string $alias): int
    {
        return (int) $this->insertOne(['name'  => $name, 'alias' => $alias]);
    }

    /**
     * Scope query by id.
     */
    protected function scopeId(QueryBuilder $builder, int $id): void
    {
        $this->scopePrimaryKey($builder, $this->getTable(), $this->getPrimaryKey(), $id);
    }

    /**
     * Scope query by alias.
     */
    protected function scopeAlias(QueryBuilder $builder, string $alias): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'alias',
                $builder->createNamedParameter($alias, ParameterType::STRING, $this->nameScopeParameter('alias'))
            )
        );
    }

    /**
     * Resolves static relationships with comments.
     *
     * @deprecated
     */
    protected function comments(): RelationInterface
    {
        return $this->hasMany(Comments_Model::class, 'id_type')->disableNativeCast();
    }

    /**
     * Resolves static relationships with resources.
     */
    protected function resources(): RelationInterface
    {
        return $this->hasMany(Comment_Resources_Model::class, 'id_type')->disableNativeCast();
    }
}

// End of file comment_types_model.php
// Location: /tinymvc/myapp/models/comment_types_model.php
