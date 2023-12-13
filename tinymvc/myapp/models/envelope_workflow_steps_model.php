<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Platforms\MySQL\Types\Types as MySQLTypes;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Envelope_Workflow_Steps model.
 */
final class Envelope_Workflow_Steps_Model extends Model
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
    protected string $table = 'envelope_workflow_steps';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPE_WORKFLOW_STEPS';

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
        'id_item',
        'completed_at_date',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'id_item'           => Types::INTEGER,
        'id_envelope'       => Types::INTEGER,
        'uuid'              => CustomTypes::UUID,
        'status'            => MySQLTypes::ENUM,
        'step_order'        => Types::INTEGER,
        'metadata'          => Types::JSON,
        'completed_at_date' => Types::DATETIME_IMMUTABLE,
        'cretead_at_date'   => Types::DATETIME_IMMUTABLE,
        'updated_at_date'   => Types::DATETIME_IMMUTABLE,
    ];

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
     * Scope for document signing action.
     *
     * @deprecated
     */
    protected function scopeDocumentSigning(QueryBuilder $builder): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'action',
                $builder->createNamedParameter('recipient:sign', ParameterType::STRING, $this->nameScopeParameter('actionSignDocument'))
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
     * Relation with the envelope.
     */
    protected function envelope(): RelationInterface
    {
        return $this->belongsTo(Envelopes_Model::class, 'id_envelope')->disableNativeCast();
    }

    /**
     * Relation with the recipients.
     */
    protected function recipients(): RelationInterface
    {
        $recipients = $this->resolveRelatedModel(Envelope_Recipients_Model::class);
        $relation = $this->hasMany(Envelope_Recipients_Workflow_Steps_Pivot_Model::class, 'id_workflow_step');
        $relation->enableNativeCast();
        $builder = $relation->getQuery();
        $related = $relation->getRelated();
        $related->mergeCasts($recipients->getCasts());

        $builder
            ->select(
                $relation->getExistenceCompareKey(),
                sprintf('%s.*', $recipientsTable = $recipients->getTable())
            )
            ->innerJoin(
                $table = $related->getTable(),
                $recipientsTable,
                null,
                "{$recipientsTable}.{$recipients->getPrimaryKey()} = {$table}.id_recipient"
            )
        ;

        return $relation;
    }
}

// End of file envelope_workflow_steps_model.php
// Location: /tinymvc/myapp/models/envelope_workflow_steps_model.php
