<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Seller_Company_Edit_Request_Documents model.
 */
final class Seller_Company_Edit_Request_Documents_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'uploaded_at_date';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'updated_at_date';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'seller_company_edit_request_documents';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'SELLER_COMPANY_EDIT_REQUEST_DOCUMENTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        'uploaded_at_date',
        'updated_at_date',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'id_document',
        'remote_uuid',
        'updated_at_date',
        'uploaded_at_date',
        'deleted_at_date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'               => Types::INTEGER,
        'uuid'             => CustomTypes::UUID,
        'id_type'          => Types::INTEGER,
        'id_user'          => Types::INTEGER,
        'id_request'       => Types::INTEGER,
        'id_document'      => Types::INTEGER,
        'remote_uuid'      => CustomTypes::UUID,
        'uploaded_at_date' => Types::DATETIME_IMMUTABLE,
        'updated_at_date'  => Types::DATETIME_IMMUTABLE,
        'deleted_at_date'  => Types::DATETIME_IMMUTABLE,
        'metadata'         => Types::JSON,
        'is_processed'     => Types::BOOLEAN,
    ];

    /**
     * Scope query for request.
     */
    protected function scopeRequest(QueryBuilder $builder, int $requestId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_request',
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('request_id'))
            )
        );
    }

    /**
     * Scope query for processed/not processed documents.
     */
    protected function scopeIsPorcessed(QueryBuilder $builder, bool $isPorcessed): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('is_processed'),
                $builder->createNamedParameter($isPorcessed, ParameterType::BOOLEAN, $this->nameScopeParameter('is_processed'))
            )
        );
    }

    /**
     * Relation with verification document type.
     */
    protected function type(): RelationInterface
    {
        return $this->belongsTo(Verification_Document_Types_Model::class, 'id_type')->enableNativeCast();
    }

    /**
     * Relation with profile edit request.
     */
    protected function request(): RelationInterface
    {
        return $this->belongsTo(Seller_Company_Edit_Requests_Model::class, 'id_request')->enableNativeCast();
    }

    /**
     * Relation with original document.
     */
    protected function originalDocument(): RelationInterface
    {
        return $this->belongsTo(Verification_Documents_Model::class, 'id_document');
    }
}

// End of file seller_company_edit_request_documents_model.php
// Location: /tinymvc/myapp/models/seller_company_edit_request_documents_model.php
