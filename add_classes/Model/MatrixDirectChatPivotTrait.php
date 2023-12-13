<?php

declare(strict_types=1);

namespace App\Model;

use App\Casts\Group\GroupAliasCast;
use App\Casts\Group\GroupTypeCast;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

trait MatrixDirectChatPivotTrait
{
    /**
     * Binds the room to the pivot.
     */
    protected function bindRooms(QueryBuilder $builder): void
    {
        $roomColumn = \constant('static::ROOM_COLUMN');
        /** @var \Matrix_Rooms_Model $resources */
        $resources = model(\Matrix_Rooms_Model::class);
        $builder
            ->leftJoin(
                $this->getTable(),
                $resources->getTable(),
                $resources->getTable(),
                "`{$this->getTable()}`.`{$roomColumn}` = `{$resources->getTable()}`.`{$resources->getPrimaryKey()}`"
            )
        ;
    }

    /**
     * Scope room by room ID.
     */
    protected function scopeRoom(QueryBuilder $builder, int $roomId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                \constant('static::ROOM_COLUMN'),
                $builder->createNamedParameter($roomId, ParameterType::INTEGER, $this->nameScopeParameter('roomId', true))
            )
        );
    }

    /**
     * Scope room by sender ID.
     */
    protected function scopeSender(QueryBuilder $builder, int $senderId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                \constant('static::SENDER_COLUMN'),
                $builder->createNamedParameter($senderId, ParameterType::INTEGER, $this->nameScopeParameter('senderId', true))
            )
        );
    }

    /**
     * Scope room by recipient ID.
     */
    protected function scopeRecipient(QueryBuilder $builder, int $recipientId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                \constant('static::RECIPIENT_COLUMN'),
                $builder->createNamedParameter($recipientId, ParameterType::INTEGER, $this->nameScopeParameter('recipientId', true))
            )
        );
    }

    /**
     * Scope room by sample order ID.
     */
    protected function scopeResource(QueryBuilder $builder, int $resourceId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                \constant('static::RESOURCE_COLUMN'),
                $builder->createNamedParameter($resourceId, ParameterType::INTEGER, $this->nameScopeParameter('resourceId', true))
            )
        );
    }

    /**
     * Scope room by senders IDs.
     */
    protected function scopeSenders(QueryBuilder $builder, array $senderIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                \constant('static::SENDER_COLUMN'),
                array_map(
                    fn (int $index, $senderId) => $builder->createNamedParameter(
                        (int) $senderId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('senderId_' . $index, true)
                    ),
                    array_keys($senderIds),
                    $senderIds
                )
            )
        );
    }

    /**
     * Scope room by recipients IDs.
     */
    protected function scopeRecipients(QueryBuilder $builder, array $recipientIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                \constant('static::RECIPIENT_COLUMN'),
                array_map(
                    fn (int $index, $recipientId) => $builder->createNamedParameter(
                        (int) $recipientId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('recipientId_' . $index, true)
                    ),
                    array_keys($recipientIds),
                    $recipientIds
                )
            )
        );
    }

    /**
     * Relation with the room reference.
     */
    protected function room(): RelationInterface
    {
        return $this->belongsTo(\Matrix_Rooms_Model::class, \constant('static::ROOM_COLUMN'))->enableNativeCast();
    }

    /**
     * Relation with the recipient.
     */
    protected function sender(): RelationInterface
    {
        return $this->makeUserRelation(\constant('static::SENDER_COLUMN'))->setName('sender');
    }

    /**
     * Relation with the recipient.
     */
    protected function recipient(): RelationInterface
    {
        return $this->makeUserRelation(\constant('static::RECIPIENT_COLUMN'))->setName('recipient');
    }

    /**
     * Relation with the sample order.
     *
     * @param mixed $resourceModel
     */
    private function makeResourceRelation($resourceModel): RelationInterface
    {
        return $this->belongsTo($resourceModel, \constant('static::RESOURCE_COLUMN'))->enableNativeCast();
    }

    /**
     * Makes relation with the user.
     */
    private function makeUserRelation(string $column): RelationInterface
    {
        /** @var \User_Groups_Model $userGroupsRepository */
        $userGroupsRepository = $this->resolveRelatedModel(\User_Groups_Model::class);
        /** @var Model $usersRepository */
        $usersRepository = \with(model(\User_Model::class), fn (\User_Model $repository) => new PortableModel(
            $repository->getHandler(),
            $repository->get_users_table(),
            $repository->get_users_table_primary_key(),
            [
                // Simple casts
                'idu'          => Types::INTEGER,
                'id'           => Types::INTEGER,
                'id_principal' => Types::INTEGER,
                'group_id'     => Types::INTEGER,
                'is_muted'     => Types::BOOLEAN,
                'is_model'     => Types::BOOLEAN,
                'is_verified'  => Types::BOOLEAN,
                'fake_user'    => Types::BOOLEAN,
                'last_active'  => Types::DATETIME_IMMUTABLE,

                // Complex casts
                'group_type'   => GroupTypeCast::class,
                'group_alias'  => GroupAliasCast::class,
            ],
            ['idu', 'id', 'id_principal']
        ));
        $usersRepository->mergeCasts($userGroupsRepository->getCasts());
        $usersRelation = $this->belongsTo($usersRepository, $column);
        $usersRelation->enableNativeCast();
        $usersRelation
            ->getQuery()
            ->select(
                $usersRepository->qualifyColumn('*'),
                $usersRepository->qualifyColumn($usersRepository->getPrimaryKey()) . ' AS `id`',
                $usersRepository->qualifyColumn('`email`'),
                "TRIM(CONCAT({$usersRepository->qualifyColumn('`fname`')}, ' ', {$usersRepository->qualifyColumn('`lname`')})) AS `full_name`",
                $usersRepository->qualifyColumn('`fname`') . ' AS `first_name`',
                $usersRepository->qualifyColumn('`lname`') . ' AS `last_name`',
                $usersRepository->qualifyColumn('`legal_name`'),
                $userGroupsRepository->qualifyColumn('`idgroup`') . ' AS `group_id`',
                $userGroupsRepository->qualifyColumn('`gr_alias`') . ' AS `group_alias`',
                $userGroupsRepository->qualifyColumn('`gr_type`') . ' AS `group_type`',
                $userGroupsRepository->qualifyColumn('`gr_name`') . ' AS `group_name`',
            )
            ->innerJoin(
                $usersRepository->getTable(),
                $userGroupsRepository->getTable(),
                null,
                "{$usersRepository->qualifyColumn('`user_group`')} = {$userGroupsRepository->qualifyColumn("`{$userGroupsRepository->getPrimaryKey()}`")}"
            )
        ;

        return $usersRelation;
    }
}
