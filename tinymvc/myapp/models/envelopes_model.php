<?php

declare(strict_types=1);

use App\Casts\Envelopes\DocumentsCast;
use App\Casts\Envelopes\RecipientsRoutingCast;
use App\Casts\Envelopes\SenderCast;
use App\Casts\Envelopes\WorkflowCast;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Platforms\MySQL\Types\Types as MySQLTypes;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use App\Common\Exceptions\NotFoundException;
use App\Envelope\RecipientStatuses;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Envelopes model.
 */
final class Envelopes_Model extends Model
{
    use Concerns\CanSearch;

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
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * The table name.
     */
    protected string $table = 'envelopes';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPES';

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
        'id_original_envelope',
        'display_title',
        'display_type',
        'display_description',
        'dispaly_info_updated_at_date',
        'external_envelope',
        'status_changed_at_date',
        'sent_at_date',
        'sent_original_at_date',
        'completed_at_date',
        'deleted_at_date',
        'declined_at_date',
        'expires_at_date',
        'purge_state',
        'purge_started_at_date',
        'purge_completed_at_date',
        'void_reason',
        'voided_at_date',
        'signing_location',
        'signing_mechanism',
        'remove_envelope',
        'current_routing_order',
        'workflow_completed_at',
        'processed_at_date',
        self::UPDATED_AT,
        self::CREATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        // Generic types
        'id'                           => Types::INTEGER,
        'id_sender'                    => Types::INTEGER,
        'id_original_envelope'         => Types::INTEGER,
        'uuid'                         => CustomTypes::UUID,
        'type'                         => MySQLTypes::ENUM,
        'dispaly_info_updated_at_date' => Types::DATETIME_IMMUTABLE,
        'status'                       => MySQLTypes::ENUM,
        'status_changed_at_date'       => Types::DATETIME_IMMUTABLE,
        'sent_at_date'                 => Types::DATETIME_IMMUTABLE,
        'sent_original_at_date'        => Types::DATETIME_IMMUTABLE,
        'created_at_date'              => Types::DATETIME_IMMUTABLE,
        'completed_at_date'            => Types::DATETIME_IMMUTABLE,
        'deleted_at_date'              => Types::DATETIME_IMMUTABLE,
        'declined_at_date'             => Types::DATETIME_IMMUTABLE,
        'updated_at_date'              => Types::DATETIME_IMMUTABLE,
        'expiration_enabled'           => Types::BOOLEAN,
        'expires_at_date'              => Types::DATE_MUTABLE,
        'purge_started_at_date'        => Types::DATETIME_IMMUTABLE,
        'purge_completed_at_date'      => Types::DATETIME_IMMUTABLE,
        'voided_at_date'               => Types::DATETIME_IMMUTABLE,
        'signing_enabled'              => Types::BOOLEAN,
        'signing_location'             => MySQLTypes::ENUM,
        'signing_mechanism'            => MySQLTypes::ENUM,
        'current_routing_order'        => Types::INTEGER,
        'current_workflow_step'        => CustomTypes::UUID,
        'workflow_status'              => MySQLTypes::ENUM,
        'workflow_completed_at'        => Types::DATETIME_IMMUTABLE,
        'processed_at_date'            => Types::DATETIME_IMMUTABLE,
        'remote_envelope'              => CustomTypes::UUID,
        'metadata'                     => Types::JSON,

        // Complex types
        'recipients_routing'           => RecipientsRoutingCast::class,
        'workflow'                     => WorkflowCast::class,
    ];

    /**
     * Get envelope by ID for preview.
     *
     * @param mixed $envelopeId
     *
     * @throws NotFoundException if envelope is not found
     */
    public function findForDetails($envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->runWithCasts(
                fn () => $this->findOne($envelopeId, [
                    'with' => [
                        'order_reference',
                        'documents',
                        'recipients',
                    ],
                ]),
                ['documents' => DocumentsCast::class]
            )
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }

    /**
     * Get envelope by ID for preview.
     *
     * @param mixed $envelopeId
     *
     * @throws NotFoundException if envelope is not found
     */
    public function findForPreview($envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->findOne($envelopeId, [
                'with' => ['recipients as recipients_routing'],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }

    /**
     * Get envelope by ID for edit.
     *
     * @throws NotFoundException if envelope is not found
     */
    public function findForEdit(?int $envelopeId, bool $extended = false): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->findOne($envelopeId, [
                'with'       => array_filter([
                    $extended ? 'documents' : false,
                    $extended ? 'recipients' : false,
                    'order_reference',
                ]),
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }

    /**
     * Get envelope by ID for copy.
     *
     * @throws NotFoundException if envelope is not found
     */
    public function findForCopy(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->findOne($envelopeId, [
                'with'       => array_filter([
                    'documents',
                    'recipients as recipients_routing',
                    'order_reference',
                ]),
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }

    /**
     * Get envelope by ID for edit.
     *
     * @throws NotFoundException if envelope is not found
     */
    public function findForSigning(?int $envelopeId): array
    {
        if (
            null === $envelopeId
            || null === $envelope = $this->findOneBy([
                'with'       => ['recipients as recipients_routing'],
                'conditions' => [
                    'id' => $envelopeId,
                ],
            ])
        ) {
            throw new NotFoundException(sprintf('The envelope with ID %s is not found', varToString($envelopeId)));
        }

        return $envelope;
    }

    /**
     * Returns the paginator prepared for datagrid.
     */
    public function paginateForGrid(?array $commonFilters = [], ?array $filters = [], ?array $ordering = [], ?int $perPage = null, ?int $page = 1): array
    {
        $paginator = $this->getPaginator(['conditions' => $allFilters = array_merge($commonFilters ?? [], $filters ?? [])], $perPage, $page);
        $paginator['all'] = $this->countBy(['conditions' => $commonFilters ?? []]);
        $paginator['data'] = $this->runWithCasts(
            fn () => $this->findAllBy([
                'with'       => [
                    'order_reference',
                    'recipients as recipients_routing',
                    'workflow_steps as workflow',
                    'documents',
                ],
                'conditions' => $allFilters,
                'order'      => $ordering ?? [],
                'limit'      => $perPage,
                'skip'       => (($page ?? 1) - 1) * $perPage,
            ]),
            ['documents' => DocumentsCast::class]
        );

        return $paginator;
    }

    /**
     * Returns the paginator prepared for datagrid.
     */
    public function paginateForAdminGrid(?array $commonFilters = [], ?array $filters = [], ?array $ordering = [], ?int $perPage = null, ?int $page = 1): array
    {
        $paginator = $this->getPaginator(['conditions' => $allFilters = array_merge($commonFilters ?? [], $filters ?? [])], $perPage, $page);
        $paginator['all'] = $this->countBy(['conditions' => $allFilters]);
        $paginator['data'] = $this->runWithCasts(
            fn () => $this->findAllBy([
                'with'       => [
                    'sender',
                    'order_reference',
                    'recipients as recipients_routing',
                    'documents',
                ],
                'conditions' => $allFilters,
                'order'      => $ordering ?? [],
                'limit'      => $perPage,
                'skip'       => (($page ?? 1) - 1) * $perPage,
            ]),
            [
                'sender'    => SenderCast::class,
                'documents' => DocumentsCast::class,
            ]
        );

        return $paginator;
    }

    /**
     * Scope query for specific envelope by ID.
     */
    protected function scopeId(QueryBuilder $builder, int $envelopeId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($envelopeId, ParameterType::INTEGER, $this->nameScopeParameter('envelopeId'))
            )
        );
    }

    /**
     * Scope query for UUID.
     */
    protected function scopeUuid(QueryBuilder $builder, string $envelopeUuid): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'uuid',
                $builder->createNamedParameter($envelopeUuid, ParameterType::STRING, $this->nameScopeParameter('envelopeUuid'))
            )
        );
    }

    /**
     * Scope query for remote UUID.
     */
    protected function scopeRemoteEnvelope(QueryBuilder $builder, string $removeEnvelopeId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'remote_envelope',
                $builder->createNamedParameter($removeEnvelopeId, ParameterType::STRING, $this->nameScopeParameter('remoteEnvelopeId'))
            )
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $this->appendSearchConditionsToQuery(
            $builder,
            $text,
            ['display_title', 'display_type', 'display_description'],
            ['display_title', 'display_type', 'display_description'],
        );
    }

    /**
     * Scope query for specific sender.
     */
    protected function scopeSender(QueryBuilder $builder, int $senderId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_sender',
                $builder->createNamedParameter($senderId, ParameterType::INTEGER, $this->nameScopeParameter('senderId'))
            )
        );
    }

    /**
     * Scope query for specific status.
     */
    protected function scopeType(QueryBuilder $builder, string $type): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'type',
                $builder->createNamedParameter($type, ParameterType::STRING, $this->nameScopeParameter('type'))
            )
        );
    }

    /**
     * Scope query for specific status.
     */
    protected function scopeStatus(QueryBuilder $builder, string $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'status',
                $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('status'))
            )
        );
    }

    /**
     * Scope query for ommit specific status.
     */
    protected function scopeNotStatus(QueryBuilder $builder, string $status): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                'status',
                $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('status'))
            )
        );
    }

    /**
     * Scope query for specific order.
     */
    protected function scopeOrder(QueryBuilder $builder, int $orderId): void
    {
        $relation = $this->hasOne(Envelopes_Orders_Pivot_Model::class, 'id_envelope');
        $subqueryBuilder = $relation->getExistenceQuery($relation->getQuery(), $builder);
        $subqueryBuilder
            ->andWhere(
                $subqueryBuilder->expr()->eq(
                    'id_order',
                    $builder->createNamedParameter($orderId, ParameterType::INTEGER, ':orderId')
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subqueryBuilder->getSQL()})");
    }

    /**
     * Scope query for envelopes for orders.
     */
    protected function scopeForOrders(QueryBuilder $builder): void
    {
        $relation = $this->hasOne(Envelopes_Orders_Pivot_Model::class, 'id_envelope');
        $builder->andWhere(
            "EXISTS ({$relation->getExistenceQuery($relation->getQuery(), $builder)->getSQL()})"
        );
    }

    /**
     * Scope query for combined recipient search.
     */
    protected function scopeCombinedRecipient(QueryBuilder $builder, ?int $userId, ?string $userName, ?string $type, ?string $status): void
    {
        if (empty(array_filter([$userId, $userName, $type, $status]))) {
            return;
        }

        $relation = $this->recipients();
        $subqueryBuilder = $relation->getExistenceQuery($relation->getQuery(), $builder);

        if (!empty($userId)) {
            $subqueryBuilder->andWhere(
                $builder->expr()->eq(
                    'id_user',
                    $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('combinedRecipientUserId'))
                )
            );
        }

        if (!empty($userName)) {
            $recipients = $relation->getRelated();
            $userRelation = $recipients->getRelation('user');
            $userSubqueryBuilder = $userRelation->getExistenceQuery($userRelation->getQuery(), $builder);
            $userSubqueryBuilder->andWhere(
                $subqueryBuilder->expr()->or(
                    $subqueryBuilder->expr()->like(
                        'fname',
                        $builder->createNamedParameter("%{$userName}%", ParameterType::STRING, $this->nameScopeParameter('combinedRecipientUserFirstName'))
                    ),
                    $subqueryBuilder->expr()->like(
                        'lname',
                        $builder->createNamedParameter("%{$userName}%", ParameterType::STRING, $this->nameScopeParameter('combinedRecipientUserLastName'))
                    ),
                    $subqueryBuilder->expr()->like(
                        'TRIM(CONCAT(fname, " ", lname))',
                        $builder->createNamedParameter("%{$userName}%", ParameterType::STRING, $this->nameScopeParameter('combinedRecipientUserName'))
                    )
                )
            );

            $subqueryBuilder->andWhere("EXISTS ({$userSubqueryBuilder->getSQL()})");
        }

        if (!empty($type)) {
            $subqueryBuilder->andWhere(
                $builder->expr()->eq(
                    'type',
                    $builder->createNamedParameter($type, ParameterType::STRING, $this->nameScopeParameter('combinedRecipientUserType'))
                )
            );
        }

        if (!empty($status)) {
            $subqueryBuilder->andWhere(
                $builder->expr()->eq(
                    'status',
                    $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('combinedRecipientUserStatus'))
                )
            );
        }

        $builder->andWhere("EXISTS ({$subqueryBuilder->getSQL()})");
    }

    /**
     * Scope query for user's combined sender search.
     */
    protected function scopeCombinedSender(QueryBuilder $builder, ?int $userId, ?string $userName): void
    {
        if (empty(array_filter([$userId, $userName]))) {
            return;
        }

        if (!empty($userId)) {
            $builder->andWhere(
                $builder->expr()->eq(
                    'id_sender',
                    $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('combinedSenderUserId'))
                )
            );
        }

        if (!empty($userName)) {
            $userRelation = $this->sender();
            $subqueryBuilder = $userRelation->getExistenceQuery($userRelation->getQuery(), $builder);
            $subqueryBuilder->andWhere(
                $builder->expr()->or(
                    $builder->expr()->like(
                        'fname',
                        $builder->createNamedParameter("%{$userName}%", ParameterType::STRING, $this->nameScopeParameter('combinedSenderUserFirstName'))
                    ),
                    $builder->expr()->like(
                        'lname',
                        $builder->createNamedParameter("%{$userName}%", ParameterType::STRING, $this->nameScopeParameter('combinedSenderUserLastName'))
                    ),
                    $builder->expr()->like(
                        'TRIM(CONCAT(fname, " ", lname))',
                        $builder->createNamedParameter("%{$userName}%", ParameterType::STRING, $this->nameScopeParameter('combinedSenderUserName'))
                    )
                )
            );

            $builder->andWhere("EXISTS ({$subqueryBuilder->getSQL()})");
        }
    }

    /**
     * Scope query for user's envelopes.
     */
    protected function scopeForUser(QueryBuilder $builder, int $usersId): void
    {
        $relation = $this->recipients();
        $subqueryBuilder = $relation
            ->getExistenceQuery($relation->getQuery(), $builder)
            ->andWhere(
                $builder->expr()->eq(
                    'id_user',
                    $builder->createNamedParameter($usersId, ParameterType::INTEGER, $this->nameScopeParameter('recipientUserTargetId'))
                ),
                $builder->expr()->neq(
                    'status',
                    $builder->createNamedParameter(RecipientStatuses::CREATED, ParameterType::STRING, $this->nameScopeParameter('recipientUserTargetStatus'))
                )
            )
        ;

        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->eq(
                    'id_sender',
                    $builder->createNamedParameter($usersId, ParameterType::INTEGER, $this->nameScopeParameter('userSenderId'))
                ),
                "EXISTS ({$subqueryBuilder->getSQL()})"
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $createdAt
     */
    protected function scopeCreatedFromDate(QueryBuilder $builder, $createdAt)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($createdAt, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.created_at_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('createdFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $createdAt
     */
    protected function scopeCreatedToDate(QueryBuilder $builder, $createdAt)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($createdAt, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.created_at_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('createdTo'))
            )
        );
    }

    /**
     * Scope a query to filter by update date from.
     *
     * @param \DateTimeInterface|int|string $updatedAt
     */
    protected function scopeUpdatedFromDate(QueryBuilder $builder, $updatedAt)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updatedAt, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.updated_at_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('updatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by update date to.
     *
     * @param \DateTimeInterface|int|string $updatedAt
     */
    protected function scopeUpdatedToDate(QueryBuilder $builder, $updatedAt)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updatedAt, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.updated_at_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('updatedTo'))
            )
        );
    }

    /**
     * Relation with the order (reference).
     */
    protected function orderReference(): RelationInterface
    {
        return $this->hasOne(Envelopes_Orders_Pivot_Model::class, 'id_envelope')->enableNativeCast();
    }

    /**
     * Relation with the order.
     */
    protected function order(): RelationInterface
    {
        $orders = new PortableModel($this->getHandler(), 'item_orders', 'id');
        $relation = $this->hasOne(Envelopes_Orders_Pivot_Model::class, 'id_envelope');
        $relation->disableNativeCast();
        $builder = $relation->getQuery();
        $related = $relation->getRelated();
        $builder
            ->select(
                $relation->getExistenceCompareKey(),
                sprintf('%s.*', $ordersTable = $orders->getTable())
            )
            ->innerJoin(
                $table = $related->getTable(),
                $ordersTable,
                null,
                "{$ordersTable}.{$orders->getPrimaryKey()} = {$table}.id_order"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the sender.
     */
    protected function sender(): RelationInterface
    {
        return \tap(
            $this->belongsTo(
                (new PortableModel($this->getHandler(), 'users', 'idu'))->mergeCasts([
                    'idu'          => Types::INTEGER,
                    'id_principal' => Types::INTEGER,
                    'user_group'   => Types::INTEGER,
                ]),
                'id_sender'
            ),
            function (RelationInterface $relation) {
                $related = $relation->getRelated();
                $relation->enableNativeCast();
                $relation
                    ->getQuery()
                    ->select(
                        '*',
                        "TRIM(CONCAT({$related->qualifyColumn('`fname`')}, ' ', {$related->qualifyColumn('`lname`')})) AS `full_name`",
                    )
                ;
            }
        );
    }

    /**
     * Relation with the sender.
     */
    protected function extendedSender(): RelationInterface
    {
        return \tap($this->sender(), function (RelationInterface $relation) {
            /** @var User_Groups_Model $userGroups */
            $userGroups = $this->resolveRelatedModel(User_Groups_Model::class);
            $related = $relation->getRelated();
            $related->mergeCasts(array_merge($userGroups->getCasts(), ['id' => Types::INTEGER]));
            $relation->enableNativeCast();
            $relation
                ->getQuery()
                ->select(
                    "{$related->qualifyColumn('*')}",
                    "{$related->qualifyColumn($related->getPrimaryKey())} as `id`",
                    "{$related->qualifyColumn('email')}",
                    "{$userGroups->qualifyColumn('`gr_type`')} as `group_type`",
                    "TRIM(CONCAT({$related->qualifyColumn('`fname`')}, ' ', {$related->qualifyColumn('`lname`')})) AS `full_name`",
                    "{$related->qualifyColumn('`fname`')} as `first_name`",
                    "{$related->qualifyColumn('`lname`')} as `last_name`",
                    "{$related->qualifyColumn('`legal_name`')}",
                )
                ->innerJoin(
                    $related->getTable(),
                    $userGroups->getTable(),
                    null,
                    "{$related->qualifyColumn('user_group')} = {$userGroups->qualifyColumn($userGroups->getPrimaryKey())}"
                )
            ;
        });
    }

    /**
     * Relation with the history.
     */
    protected function history(): RelationInterface
    {
        return $this->hasMany(Envelope_History_Model::class, 'id_envelope')->enableNativeCast();
    }

    /**
     * Relation with the documents.
     */
    protected function documents(): RelationInterface
    {
        return $this->hasMany(Envelope_Documents_Model::class, 'id_envelope')->enableNativeCast();
    }

    /**
     * Relation with the attachments.
     */
    protected function attachments(): RelationInterface
    {
        return $this->hasMany(Envelope_Attachments_Model::class, 'id_envelope')->enableNativeCast();
    }

    /**
     * Relation with the recipients.
     */
    protected function recipients(): RelationInterface
    {
        return $this->hasMany(Envelope_Recipients_Model::class, 'id_envelope')->enableNativeCast();
    }

    /**
     * Relation with the recipients.
     */
    protected function extendedRecipients(): RelationInterface
    {
        /** @var User_Model $users */
        $users = model(User_Model::class);
        /** @var User_Groups_Model $userGroups */
        $userGroups = $this->resolveRelatedModel(User_Groups_Model::class);
        $usersTable = $users->get_users_table();
        $usersPrimaryKey = $users->get_users_table_primary_key();
        $relation = $this->hasMany(Envelope_Recipients_Model::class, 'id_envelope');
        $relation->enableNativeCast();
        $table = $relation->getRelated()->getTable();
        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$table}`.*",
                "`{$usersTable}`.`email`",
                "TRIM(CONCAT(`{$usersTable}`.`fname`, ' ', `{$usersTable}`.`lname`)) AS `full_name`",
                "`{$usersTable}`.`fname` as `first_name`",
                "`{$usersTable}`.`lname` as `last_name`",
                "`{$usersTable}`.`legal_name`",
                "{$userGroups->qualifyColumn('`gr_type`')} as `group_type`",
            )
            ->leftJoin($table, $usersTable, $usersTable, "{$usersTable}.{$usersPrimaryKey} = {$table}.id_user")
            ->innerJoin(
                $table,
                $userGroups->getTable(),
                $userGroups->getTable(),
                "{$usersTable}.user_group = {$userGroups->qualifyColumn($userGroups->getPrimaryKey())}"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the original envelope.
     */
    protected function originalEnvelope(): RelationInterface
    {
        return $this->belongsTo(Envelopes_Model::class, 'id_original_envelope')->enableNativeCast();
    }

    /**
     * Relation with the workflow steps.
     */
    protected function workflowSteps(): RelationInterface
    {
        return $this->hasMany(Envelope_Workflow_Steps_Model::class, 'id_envelope')->enableNativeCast();
    }

    /**
     * Relation with the last workflow step.
     */
    protected function currentWorkflowStep(): RelationInterface
    {
        return $this->hasOne(Envelope_Workflow_Steps_Model::class, 'uuid', 'current_workflow_step')->enableNativeCast();
    }
}

// End of file envelopes_model.php
// Location: /tinymvc/myapp/models/envelopes_model.php
