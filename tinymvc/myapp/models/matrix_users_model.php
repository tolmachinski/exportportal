<?php

declare(strict_types=1);

use App\Casts\Group\GroupAliasCast;
use App\Casts\Group\GroupTypeCast;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Matrix_Users model.
 */
final class Matrix_Users_Model extends Model
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
    protected string $table = 'matrix_users';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'MATRIX_USERS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'last_sync_at',
        self::UPDATED_AT,
        self::CREATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        // Generic types
        'id'                   => Types::INTEGER,
        'id_user'              => Types::INTEGER,
        'last_sync_at'         => Types::DATETIME_IMMUTABLE,
        self::UPDATED_AT       => Types::DATETIME_IMMUTABLE,
        self::CREATED_AT       => Types::DATETIME_IMMUTABLE,
        'create_room_at_date'  => Types::DATETIME_IMMUTABLE,
        'create_cargo_room_at' => Types::DATETIME_IMMUTABLE,
        'is_deactivated'       => Types::BOOLEAN,
        'has_pending_removal'  => Types::BOOLEAN,
        'has_initialized_keys' => Types::BOOLEAN,
    ];

    /**
     * Scope query for specific sender.
     */
    protected function scopeUser(QueryBuilder $builder, int $senderId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($senderId, ParameterType::INTEGER, $this->nameScopeParameter('senderId'))
            )
        );
    }

    /**
     * Scope query for specific sender.
     */
    protected function scopeMxid(QueryBuilder $builder, string $mxid): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'mxid',
                $builder->createNamedParameter($mxid, ParameterType::STRING, $this->nameScopeParameter('mxid'))
            )
        );
    }

    /**
     * Scope query for list of users MXIDs.
     *
     * @param string[] $mxids
     */
    protected function scopeMxids(QueryBuilder $builder, array $mxids): void
    {
        if (empty($mxids)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('mxid', \array_map(
                fn (int $index, $mxid) => $builder->createNamedParameter(
                    (string) $mxid,
                    ParameterType::STRING,
                    $this->nameScopeParameter(\sprintf('mxidId%s%s', $index, \bin2hex(\random_bytes(12))))
                ),
                \array_keys($mxids),
                $mxids
            ))
        );
    }

    /**
     * Scope query for specific sender.
     */
    protected function scopeVersion(QueryBuilder $builder, string $syncVersion): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'version',
                $builder->createNamedParameter($syncVersion, ParameterType::STRING, $this->nameScopeParameter('syncVersion'))
            )
        );
    }

    /**
     * Scope query for cargo room presence.
     */
    protected function scopeHasCargoRoom(QueryBuilder $builder, bool $hasRoom): void
    {
        if ($hasRoom) {
            $builder->andWhere(
                $builder->expr()->isNotNull(
                    $this->qualifyColumn('cargo_room_id')
                )
            );
        } else {
            $builder->andWhere(
                $builder->expr()->isNull(
                    $this->qualifyColumn('cargo_room_id')
                )
            );
        }
    }

    /**
     * Scope query for server notices room presence.
     */
    protected function scopeHasServerNoticesRoom(QueryBuilder $builder, bool $hasRoom): void
    {
        if ($hasRoom) {
            $builder->andWhere(
                $builder->expr()->isNotNull(
                    $this->qualifyColumn('server_notices_room_id')
                )
            );
        } else {
            $builder->andWhere(
                $builder->expr()->isNull(
                    $this->qualifyColumn('server_notices_room_id')
                )
            );
        }
    }

    /**
     * Relation with the sender.
     */
    protected function user(): RelationInterface
    {
        /** @var User_Groups_Model $userGroupsRepository */
        $userGroupsRepository = $this->resolveRelatedModel(User_Groups_Model::class);
        /** @var RelationInterface $relation */
        $relation = $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
        $relation->getRelated()->mergeCasts([
            // Simple casts
            'id'          => Types::INTEGER,
            'group_id'    => Types::INTEGER,
            // Complex casts
            'group_type'  => GroupTypeCast::class,
            'group_alias' => GroupAliasCast::class,
        ]);
        $repository = $relation->getRelated();
        $relation
            ->getQuery()
            ->select(
                $repository->qualifyColumn('*'),
                $repository->qualifyColumn($repository->getPrimaryKey()) . ' AS `id`',
                $repository->qualifyColumn('`email`'),
                "TRIM(CONCAT({$repository->qualifyColumn('`fname`')}, ' ', {$repository->qualifyColumn('`lname`')})) AS `full_name`",
                $repository->qualifyColumn('`fname`') . ' AS `first_name`',
                $repository->qualifyColumn('`lname`') . ' AS `last_name`',
                $repository->qualifyColumn('`legal_name`'),
                $userGroupsRepository->qualifyColumn('`idgroup`') . ' AS `group_id`',
                $userGroupsRepository->qualifyColumn('`gr_alias`') . ' AS `group_alias`',
                $userGroupsRepository->qualifyColumn('`gr_type`') . ' AS `group_type`',
                $userGroupsRepository->qualifyColumn('`gr_name`') . ' AS `group_name`',
            )
            ->innerJoin(
                $repository->getTable(),
                $userGroupsRepository->getTable(),
                null,
                "{$repository->qualifyColumn('`user_group`')} = {$userGroupsRepository->qualifyColumn("`{$userGroupsRepository->getPrimaryKey()}`")}"
            )
        ;

        return $relation;
    }
}

// End of file matrix_users_model.php
// Location: /tinymvc/myapp/models/matrix_users_model.php
