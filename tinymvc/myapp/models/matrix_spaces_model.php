<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Matrix_Spaces model.
 */
final class Matrix_Spaces_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    protected const CREATED_AT = 'created_at_date';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    protected const UPDATED_AT = 'updated_at_date';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'matrix_spaces';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'MATRIX_SPACES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $nullable = [
        self::UPDATED_AT,
        self::CREATED_AT,
    ];

    /**
     * {@inheritdoc}
     */
    protected array $casts = [
        // Generic types
        'id'             => Types::INTEGER,
        'name'           => Types::STRING,
        'alias'          => Types::STRING,
        'room_id'        => Types::STRING,
        self::UPDATED_AT => Types::DATETIME_IMMUTABLE,
        self::CREATED_AT => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Find space by its name.
     */
    public function findByName(string $name): ?array
    {
        return $this->findOneBy([
            'conditions' => [
                'name' => $name,
            ],
        ]);
    }

    /**
     * Find space by its alias.
     */
    public function findByAlias(string $alias): ?array
    {
        return $this->findOneBy([
            'conditions' => [
                'alias' => $alias,
            ],
        ]);
    }

    /**
     * Scope room by IDs or names.
     */
    protected function scopeIdOrName(QueryBuilder $builder, array $references): void
    {
        if (empty($references)) {
            return;
        }
        $spaceIds = [];
        $spaceNames = [];
        foreach ($references as $key) {
            if (\is_numeric($key)) {
                $spaceIds[] = (int) $key;
            } else {
                $spaceNames[] = $key;
            }
        }

        $spaceIds = array_unique($spaceIds);
        if (!empty($spaceIds)) {
            $builder->andWhere($builder->expr()->in('id', \array_map(
                fn (int $index, $id) => $builder->createNamedParameter(
                    $id,
                    ParameterType::INTEGER,
                    $this->nameScopeParameter(\sprintf('referenceId%s%s', $index, \bin2hex(\random_bytes(12))))
                ),
                \array_keys($spaceIds),
                $spaceIds
            )));
        }
        $spaceNames = array_unique($spaceNames);
        if (!empty($spaceNames)) {
            $builder->andWhere($builder->expr()->in('name', \array_map(
                fn (int $index, $name) => $builder->createNamedParameter(
                    $name,
                    ParameterType::STRING,
                    $this->nameScopeParameter(\sprintf('referenceName%s%s', $index, \bin2hex(\random_bytes(12))))
                ),
                \array_keys($spaceNames),
                $spaceNames
            )));
        }
    }

    /**
     * Scope room by space name.
     */
    protected function scopeName(QueryBuilder $builder, string $name): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'name',
                $builder->createNamedParameter($name, ParameterType::STRING, $this->nameScopeParameter('roomName'))
            )
        );
    }

    /**
     * Scope room by space alias.
     */
    protected function scopeAlias(QueryBuilder $builder, string $alias): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'alias',
                $builder->createNamedParameter($alias, ParameterType::STRING, $this->nameScopeParameter('roomAlias'))
            )
        );
    }

    /**
     * Scope room by matrix room ID.
     */
    protected function scopeRoomId(QueryBuilder $builder, string $roomId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'room_id',
                $builder->createNamedParameter($roomId, ParameterType::STRING, $this->nameScopeParameter('roomId'))
            )
        );
    }
}

// End of file matrix_spaces_model.php
// Location: /tinymvc/myapp/models/matrix_spaces_model.php
