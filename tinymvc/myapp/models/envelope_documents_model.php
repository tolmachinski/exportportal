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
 * Envelope_Documents model.
 */
final class Envelope_Documents_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    protected const CREATED_AT = 'uploaded_at_date';

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
    protected string $table = 'envelope_documents';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPE_DOCUMENTS';

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
        'id_parent_document',
        'label',
        'file_name',
        'file_extension',
        'file_original_name',
        'local_path',
        'mime_type',
        'remote_uuid',
        'updated_at_date',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                     => Types::INTEGER,
        'id_envelope'            => Types::INTEGER,
        'id_parent_document'     => Types::INTEGER,
        'uuid'                   => CustomTypes::UUID,
        'file_size'              => Types::INTEGER,
        'remote_uuid'            => CustomTypes::UUID,
        'is_authoriative_copy'   => Types::BOOLEAN,
        'is_final_external_copy' => Types::BOOLEAN,
        'uploaded_at_date'       => Types::DATETIME_IMMUTABLE,
        'updated_at_date'        => Types::DATETIME_IMMUTABLE,
        'deleted_at_date'        => Types::DATETIME_IMMUTABLE,
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
     * Scope query for specific sender.
     */
    protected function scopeEnvelopes(QueryBuilder $builder, array $envelopeIds): void
    {
        if (empty($envelopeIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'id_envelope',
                array_map(
                    fn (int $index, $envelopeId) => $builder->createNamedParameter(
                        (int) $envelopeId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("envelopeFromList{$index}")
                    ),
                    array_keys($envelopeIds),
                    $envelopeIds
                )
            )
        );
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
     * Scope query by recipient ID.
     */
    protected function scopeAuthoriative(QueryBuilder $builder, bool $isAuthoriative): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'is_authoriative_copy',
                $builder->createNamedParameter((int) $isAuthoriative, ParameterType::INTEGER, $this->nameScopeParameter('authoriativeCopy'))
            )
        );
    }

    /**
     * Scope query by recipient ID.
     */
    protected function scopeRecipient(QueryBuilder $builder, int $recipientId): void
    {
        $relation = $this->recipients();
        $subqueryBuilder = $relation->getExistenceQuery($relation->getQuery(), $builder);
        $subqueryBuilder
            ->andWhere(
                $subqueryBuilder->expr()->eq(
                    'id_recipient',
                    $builder->createNamedParameter($recipientId, ParameterType::INTEGER, $this->nameScopeParameter('recipientId'))
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subqueryBuilder->getSQL()})");
    }

    /**
     * Scope query by recipient ID.
     */
    protected function scopeRoutingOrder(QueryBuilder $builder, int $routingOrder): void
    {
        $relation = $this->recipients();
        $subqueryBuilder = $relation->getExistenceQuery($relation->getQuery(), $builder);
        $subqueryBuilder
            ->andWhere(
                $subqueryBuilder->expr()->eq(
                    'routing_order',
                    $builder->createNamedParameter($routingOrder, ParameterType::INTEGER, $this->nameScopeParameter('routingOrder'))
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subqueryBuilder->getSQL()})");
    }

    /**
     * Relation with the envelope.
     */
    protected function envelope(): RelationInterface
    {
        return $this->belongsTo(Envelopes_Model::class, 'id_envelope')->enableNativeCast();
    }

    /**
     * Relation with the parent document.
     */
    protected function parent(): RelationInterface
    {
        return $this->belongsTo(Envelope_Documents_Model::class, 'id_parent_document')->enableNativeCast();
    }

    /**
     * Relation with the recipients.
     */
    protected function recipients(): RelationInterface
    {
        $recipients = $this->resolveRelatedModel(Envelope_Recipients_Model::class);
        $relation = $this->hasMany(Envelope_Recipients_Documents_Pivot_Model::class, 'id_document');
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

// End of file envelope_documents_model.php
// Location: /tinymvc/myapp/models/envelope_documents_model.php
