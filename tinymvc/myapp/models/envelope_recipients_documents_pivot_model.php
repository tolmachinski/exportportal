<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Envelope_Recipients_Documents_Pivot model.
 */
final class Envelope_Recipients_Documents_Pivot_Model extends Model
{
    /**
     * The table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The table name.
     */
    protected string $table = 'envelope_recipients_documents_pivot';

    /**
     * The table alias.
     */
    protected string $alias = 'ENVELOPE_RECIPIENTS_DOCUMENTS_PIVOT';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'           => Types::INTEGER,
        'id_envelope'  => Types::INTEGER,
        'id_recipient' => Types::INTEGER,
        'id_document'  => Types::INTEGER,
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
     * Scope query by the list of IDs.
     */
    protected function scopeDocuments(QueryBuilder $builder, array $documentsIds): void
    {
        if (empty($ids)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'id_document',
                array_map(
                    fn (int $index, $documentsId) => $builder->createNamedParameter(
                        (int) $documentsId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("documentId{$index}")
                    ),
                    array_keys($documentsIds),
                    $documentsIds
                )
            )
        );
    }

    /**
     * Scope query by the list of IDs.
     */
    protected function scopeNotDocuments(QueryBuilder $builder, array $documentsIds): void
    {
        if (empty($ids)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                'id_document',
                array_map(
                    fn (int $index, $documentsId) => $builder->createNamedParameter(
                        (int) $documentsId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("documentId{$index}")
                    ),
                    array_keys($documentsIds),
                    $documentsIds
                )
            )
        );
    }

    /**
     * Scope query by the list of IDs.
     */
    protected function scopeRecipients(QueryBuilder $builder, array $recipientsIds): void
    {
        if (empty($ids)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                'id_recipient',
                array_map(
                    fn (int $index, $recipientsId) => $builder->createNamedParameter(
                        (int) $recipientsId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("recipientId{$index}")
                    ),
                    array_keys($recipientsIds),
                    $recipientsIds
                )
            )
        );
    }

    /**
     * Scope query by the list of IDs.
     */
    protected function scopeNotRecipients(QueryBuilder $builder, array $recipientsIds): void
    {
        if (empty($ids)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                'id_recipient',
                array_map(
                    fn (int $index, $recipientsId) => $builder->createNamedParameter(
                        (int) $recipientsId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("recipientId{$index}")
                    ),
                    array_keys($recipientsIds),
                    $recipientsIds
                )
            )
        );
    }

    /**
     * Scope query by recipient ID.
     */
    protected function scopeAuthoriative(QueryBuilder $builder, bool $authoriative): void
    {
        $relation = $this->document();
        $subqueryBuilder = $relation->getExistenceQuery($relation->getQuery(), $builder);
        $subqueryBuilder
            ->andWhere(
                $subqueryBuilder->expr()->eq(
                    'is_authoriative_copy',
                    $builder->createNamedParameter($authoriative, ParameterType::BOOLEAN, $this->nameScopeParameter('isAuthoriative'))
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
        return $this->belongsTo(Envelopes_Model::class, 'id_envelope')->disableNativeCast();
    }

    /**
     * Relation with the recipient.
     */
    protected function recipient(): RelationInterface
    {
        return $this->belongsTo(Envelope_Recipients_Model::class, 'id_recipient')->disableNativeCast();
    }

    /**
     * Relation with the document.
     */
    protected function document(): RelationInterface
    {
        return $this->belongsTo(Envelope_Documents_Model::class, 'id_document')->disableNativeCast();
    }
}

// End of file envelope_recipients_documents_pivot_model.php
// Location: /tinymvc/myapp/models/envelope_recipients_documents_pivot_model.php
