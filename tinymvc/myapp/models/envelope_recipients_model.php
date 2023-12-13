<?php

declare(strict_types=1);

use App\Casts\Envelopes\DocumentsCast;
use App\Common\Database\Model;
use App\Common\Database\Platforms\MySQL\Types\Types as MySQLTypes;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use App\Envelope\EnvelopeStatuses;
use App\Envelope\RecipientStatuses;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Envelope_Recipeints model.
 */
final class Envelope_Recipients_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    protected const CREATED_AT = 'cretead_at_date';

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
    protected string $table = 'envelope_recipients';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPE_RECIPIENTS';

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
        'confirmed_at_date',
        'completed_at_date',
        'delivery_method',
        'delivery_at_date',
        'decline_reason',
        'declined_at_date',
        'routing_order',
        'sent_at_date',
        'signed_at_date',
        'assigned_at_date',
        'due_date',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                 => Types::INTEGER,
        'id_user'            => Types::INTEGER,
        'id_envelope'        => Types::INTEGER,
        'uuid'               => CustomTypes::UUID,
        'type'               => MySQLTypes::ENUM,
        'confirmed_at_date'  => Types::DATETIME_IMMUTABLE,
        'completed_at_date'  => Types::DATETIME_IMMUTABLE,
        'delivery_method'    => MySQLTypes::ENUM,
        'delivery_at_date'   => Types::DATETIME_IMMUTABLE,
        'declined_at_date'   => Types::DATETIME_IMMUTABLE,
        'declined_by_sender' => Types::BOOLEAN,
        'routing_order'      => Types::INTEGER,
        'status'             => MySQLTypes::ENUM,
        'sent_at_date'       => Types::DATETIME_IMMUTABLE,
        'signed_at_date'     => Types::DATETIME_IMMUTABLE,
        'assigned_at_date'   => Types::DATETIME_IMMUTABLE,
        'cretead_at_date'    => Types::DATETIME_IMMUTABLE,
        'updated_at_date'    => Types::DATETIME_IMMUTABLE,
        'due_date'           => Types::DATE_IMMUTABLE,
    ];

    /**
     * Finds recipients that are currently in the active routing.
     */
    public function findRouted(?int $envelopeId): array
    {
        return $this->runWithCasts(
            fn () => $this->findAllBy([
                'columns'    => ['`id`', $this->getTable() . '.`due_date`'],
                'with'       => ['envelope', 'user'],
                'conditions' => [
                    'envelope'        => $envelopeId,
                    'have_routing'    => true,
                    'current_routing' => true,
                ],
            ]),
            ['documents' => DocumentsCast::class]
        );
    }

    public function findSoonToExpire()
    {
        return $this->findAllBy([
            'columns'    => [
                $this->qualifyColumn('`id`'),
                $this->qualifyColumn('`id_user`'),
                $this->qualifyColumn('`id_envelope`'),
                $this->qualifyColumn('`due_date`')
            ],
            'with'       => ['user as signer', 'order_sender'],
            'conditions' => [
                'status'              => [RecipientStatuses::SENT, RecipientStatuses::DELIVERED],
                'envelope_status_not' => [EnvelopeStatuses::FINISHED],
                'current_routing'     => true,
                'due_date'            => new DateTime('tomorrow'),
            ],
        ]);

    }

    public function findExpired()
    {
        return $this->findAllBy([
            'with'       => ['user as signer', 'order_sender'],
            'conditions' => [
                'status'              => [RecipientStatuses::SENT, RecipientStatuses::DELIVERED],
                'envelope_status_not' => [EnvelopeStatuses::FINISHED],
                'current_routing'     => true,
                'due_date'            => new DateTime(),
            ],
        ]);
    }

    /**
     * Scope query for specific sender.
     */
    protected function scopeEnvelope(QueryBuilder $builder, int $envelopeId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_envelope',
                $builder->createNamedParameter($envelopeId, ParameterType::INTEGER, $this->nameScopeParameter('envelopeId'))
            )
        );
    }

    /**
     * Scope query for specific sender.
     *
     * @param \DateTimeInterface|int|string $dueDate
     */
    protected function scopeDueDate(QueryBuilder $builder, DateTime $dueDate): void
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($dueDate, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->getTable()}.due_date)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('dueDate'))
            )
        );
    }

    /**
     * Scope query by the list of IDs.
     */
    protected function scopeIds(QueryBuilder $builder, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->getPrimaryKey(),
                array_map(
                    fn (int $index, $hashe) => $builder->createNamedParameter(
                        (int) $hashe,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("idFromList{$index}")
                    ),
                    array_keys($ids),
                    $ids
                )
            )
        );
    }

    /**
     * Scope query by the list of statuses.
     */
    protected function scopeStatus(QueryBuilder $builder, array $status): void
    {
        if (empty($status)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'status',
                array_map(
                    fn (int $index, $status) => $builder->createNamedParameter(
                        (string) $status,
                        ParameterType::STRING,
                        $this->nameScopeParameter("{$status}")
                    ),
                    array_keys($status),
                    $status
                )
            )
        );
    }

    /**
     * Scope query not to include the list of statuses.
     */
    protected function scopeEnvelopeStatusNot(QueryBuilder $builder, array $status): void
    {
        $relation = $this->envelope();
        $subqueryBuilder = $relation->getExistenceQuery($relation->getQuery(), $builder);
        $table = $relation->getRelated()->getTable();
        $subqueryBuilder->andWhere(
            $builder->expr()->notIn(
                "`{$table}`.`status`",
                array_map(
                    fn (int $index, $status) => $builder->createNamedParameter(
                        (string) $status,
                        ParameterType::STRING,
                        $this->nameScopeParameter("{$status}")
                    ),
                    array_keys(EnvelopeStatuses::FINISHED),
                    EnvelopeStatuses::FINISHED
                )
            )
        );

        $builder->andWhere("EXISTS ({$subqueryBuilder->getSQL()})");
    }

    /**
     * Scope query by the list of IDs.
     */
    protected function scopeUuids(QueryBuilder $builder, array $uuids): void
    {
        if (empty($uuids)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'uuid',
                array_map(
                    fn (int $index, $uuid) => $builder->createNamedParameter(
                        (string) $uuid,
                        ParameterType::STRING,
                        $this->nameScopeParameter("uuidFromList{$index}")
                    ),
                    array_keys($uuids),
                    $uuids
                )
            )
        );
    }

    /**
     * Scope query by the list of envelope IDs.
     */
    protected function scopeEnvelopes(QueryBuilder $builder, array $envelopesIds): void
    {
        if (empty($envelopesIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'id_envelope',
                array_map(
                    fn (int $index, $envelopesId) => $builder->createNamedParameter(
                        (int) $envelopesId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("envelopesFromList{$index}")
                    ),
                    array_keys($envelopesIds),
                    $envelopesIds
                )
            )
        );
    }

    /**
     * Scope query by recipients that have routing.
     */
    protected function scopeHaveRouting(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->isNotNull(
                $this->qualifyColumn('routing_order')
            )
        );
    }

    /**
     * Scope query by current envelope recipients.
     */
    protected function scopeCurrentRouting(QueryBuilder $builder, bool $findCurrent): void
    {
        $relation = $this->envelope();
        $subqueryBuilder = $relation->getExistenceQuery($relation->getQuery(), $builder);
        $subqueryBuilder->andWhere(
            $findCurrent
                ? $builder->expr()->eq(
                    $relation->getRelated()->qualifyColumn('current_routing_order'),
                    $this->qualifyColumn('routing_order'),
                )
                : $builder->expr()->neq(
                    $relation->getRelated()->qualifyColumn('current_routing_order'),
                    $this->qualifyColumn('routing_order'),
                )
        );

        $builder->andWhere("EXISTS ({$subqueryBuilder->getSQL()})");
    }

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(new PortableModel($this->getHandler(), 'users', 'idu'), 'id_user')->disableNativeCast();
    }

    /**
     * Relation with the order and sender for cron.
     */
    protected function orderSender(): RelationInterface
    {
        /** @var Envelopes_Orders_Pivot_Model $orderPivotModel */
        $orderPivotModel = model(Envelopes_Orders_Pivot_Model::class);
        $orderRefTable = $orderPivotModel->getTable();

        /** @var User_Model $userTableModel */
        $userTableModel = model(User_Model::class);
        $userTable = $userTableModel->get_users_table();

        $relation = $this->belongsTo(Envelopes_Model::class, 'id_envelope');
        $relation->disableNativeCast();
        $table = $relation->getRelated()->getTable();
        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$table}`.`id`",
                "`{$table}`.`id_sender`",
                "`{$table}`.`display_title` as document_title",
                "`u`.`email` as `email`",
                "`u`.`fname`",
                "`u`.`lname`",
                "`{$orderRefTable}`.`id_order`",
            )
            ->leftJoin($table, $orderRefTable, null, "{$orderRefTable}.`id_envelope` = {$table}.`id`")
            ->leftJoin($table, $userTable, 'u', "u.`idu` = {$table}.`id_sender`");


        return $relation;

    }

    /**
     * Relation with the envelope.
     */
    protected function envelope(): RelationInterface
    {
        return $this->belongsTo(Envelopes_Model::class, 'id_envelope')->disableNativeCast();
    }

    /**
     * Relation with the workflow.
     */
    protected function workflowSteps(): RelationInterface
    {
        $workflowSteps = $this->resolveRelatedModel(Envelope_Workflow_Steps_Model::class);
        $relation = $this->hasMany(Envelope_Recipients_Workflow_Steps_Pivot_Model::class, 'id_recipient');
        $relation->enableNativeCast();
        $builder = $relation->getQuery();
        $related = $relation->getRelated();
        $related->mergeCasts($workflowSteps->getCasts());

        $builder
            ->select(
                $relation->getExistenceCompareKey(),
                sprintf('%s.*', $workflowStepsTable = $workflowSteps->getTable())
            )
            ->innerJoin(
                $table = $related->getTable(),
                $workflowStepsTable,
                null,
                "{$workflowStepsTable}.{$workflowSteps->getPrimaryKey()} = {$table}.id_workflow_step"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the documents.
     */
    protected function documents(): RelationInterface
    {
        $documents = $this->resolveRelatedModel(Envelope_Documents_Model::class);
        $relation = $this->hasMany(Envelope_Recipients_Documents_Pivot_Model::class, 'id_recipient');
        $relation->enableNativeCast();
        $builder = $relation->getQuery();
        $related = $relation->getRelated();
        $related->mergeCasts($documents->getCasts());

        $builder
            ->select(
                $relation->getExistenceCompareKey(),
                sprintf('%s.*', $documentsTable = $documents->getTable())
            )
            ->innerJoin(
                $table = $related->getTable(),
                $documentsTable,
                null,
                "{$documentsTable}.{$documents->getPrimaryKey()} = {$table}.id_document"
            )
        ;

        return $relation;
    }
}

// End of file envelope_recipeints_model.php
// Location: /tinymvc/myapp/models/envelope_recipeints_model.php
