<?php

declare(strict_types=1);

use App\Casts\Verification\LatestVersionCast;
use App\Casts\Verification\VersionsCast;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Verification_Documents_Model model.
 */
final class Verification_Documents_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'date_updated';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'users_personal_documents';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'VERIFICATION_DOCUMENTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_document';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_document',
        self::CREATED_AT,
        self::UPDATED_AT,
        'latest_version',
        'latest_version_index',
        'date_latest_version_expires',
        'date_latest_version_created',
        'date_original_version_created',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'versions',
        'subtitle',
        'id_manager',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        // simple casts
        'id_document'                     => Types::INTEGER,
        'id_type'                         => Types::INTEGER,
        'id_user'                         => Types::INTEGER,
        'id_principal'                    => Types::INTEGER,
        'id_manager'                      => Types::INTEGER,
        'versions'                        => Types::JSON,
        'latest_version'                  => Types::JSON,
        'latest_version_index'            => Types::INTEGER,
        'date_latest_version_expires'     => Types::DATETIME_IMMUTABLE,
        'date_latest_version_created'     => Types::DATETIME_IMMUTABLE,
        'date_original_version_created'   => Types::DATETIME_IMMUTABLE,
        'date_created'                    => Types::DATETIME_IMMUTABLE,
        'date_updated'                    => Types::DATETIME_IMMUTABLE,
        'is_manually_added'               => Types::BOOLEAN,

        // complex casts
        'versions'                        => VersionsCast::class,
        'latest_version'                  => LatestVersionCast::class,
    ];

    /**
     * Returns the paginator prepared for datagrid.
     */
    public function paginateForGrid(?array $filters = [], ?array $ordering = [], ?int $perPage = null, ?int $page = 1): array
    {
        $paginator = $this->getPaginator(['scopes' => $filters], $perPage, $page);
        $paginator['all'] = $this->countBy(['scopes' => $commonFilters ?? []]);
        $paginator['data'] = $this->findAllBy([
            'conditions' => $filters,
            'order'      => $ordering ?? [],
            'limit'      => $perPage,
            'skip'       => (($page ?? 1) - 1) * $perPage,
        ]);

        return $paginator;
    }

    /**
     * Scope a query to filter by keywords.
     */
    protected function scopeSearch(QueryBuilder $builder, string $keywords)
    {
        if (empty($keywords)) {
            return;
        }

        $types = $this->type()->getRelated();
        $expressions = $builder->expr();
        $textParameter = $builder->createNamedParameter(
            $keywords,
            ParameterType::STRING,
            $this->nameScopeParameter('document_search_text', true)
        );
        $textTokenParameter = $builder->createNamedParameter(
            "%{$keywords}%",
            ParameterType::STRING,
            $this->nameScopeParameter('document_search_text_token', true)
        );
        $intlSearchExpression = $expressions->or(
            $expressions->eq($types->qualifyColumn('document_translated_titles'), $textParameter),
            $expressions->eq($types->qualifyColumn('document_translated_descriptions'), $textParameter),
            $expressions->like($types->qualifyColumn('document_translated_titles'), $textTokenParameter),
            $expressions->like($types->qualifyColumn('document_translated_descriptions'), $textTokenParameter),
            $expressions->like($this->qualifyColumn('subtitle'), $textTokenParameter),
        );

        $searchTokens = $this->tokenizeSearchText($keywords, true);
        if (!empty($searchTokens)) {
            $searchExpression = $builder->expr()->and(
                sprintf(
                    'MATCH (%s.document_title, %s.document_description) AGAINST (%s IN BOOLEAN MODE)',
                    $types->getTable(),
                    $types->getTable(),
                    $builder->createNamedParameter(
                        $builder->expr()->literal(implode(' ', $searchTokens)),
                        ParameterType::STRING,
                        $this->nameScopeParameter('document_search_matched_text', true)
                    )
                )
            );
        } else {
            $searchExpression = $expressions->or(
                $expressions->eq($types->qualifyColumn('document_title'), $textParameter),
                $expressions->eq($types->qualifyColumn('document_description'), $textParameter),
                $expressions->like($types->qualifyColumn('document_title'), $textTokenParameter),
                $expressions->like($types->qualifyColumn('document_description'), $textTokenParameter),
                $expressions->like($this->qualifyColumn('subtitle'), $textTokenParameter),
            );
        }

        $subqueryBuilder = $this->createQueryBuilder();
        $subqueryBuilder
            ->select($types->getPrimaryKey())
            ->from($types->getTable())
            ->where(
                $subqueryBuilder->expr()->or(
                    $searchExpression,
                    $intlSearchExpression
                )
            )
        ;

        $builder->andWhere(
            $expressions->in($this->qualifyColumn('id_type'), "({$subqueryBuilder->getSQL()})")
        );
    }

    /**
     * Scope a query to filter by document principal ID.
     */
    protected function scopePrincipal(QueryBuilder $builder, int $principalId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_principal'),
                $builder->createNamedParameter((int) $principalId, ParameterType::INTEGER, $this->nameScopeParameter('document_principal_id', true))
            )
        );
    }

    /**
     * Scope a query to filter by document user ID.
     *
     * @param mixed $userId
     */
    protected function scopeUser(QueryBuilder $builder, $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter('document_user_id', true))
            )
        );
    }

    /**
     * Scope a query to filter by document expluding user ID.
     */
    protected function scopeNotUser(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter('document_not_user_id', true))
            )
        );
    }

    /**
     * Scope a query to filter by document type ID.
     */
    protected function scopeType(QueryBuilder $builder, int $typeId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_type'),
                $builder->createNamedParameter((int) $typeId, ParameterType::INTEGER, $this->nameScopeParameter('document_type_id', true))
            )
        );
    }

    /**
     * Scope a query to filter by document type ID.
     */
    protected function scopeNotAcceptedOrExpired(QueryBuilder $builder)
    {
        $expressions = $builder->expr();
        $builder->andWhere(
            $expressions->or(
                $expressions->isNull($this->qualifyColumn('latest_version')),
                $expressions->neq(
                    "{$this->qualifyColumn('latest_version')}->>'$.type'",
                    $builder->createNamedParameter('accepted', ParameterType::STRING, $this->nameScopeParameter('document_accepted_status', true))
                ),
                $expressions->and(
                    $expressions->isNotNull($this->qualifyColumn('date_latest_version_expires')),
                    $expressions->lte($this->qualifyColumn('date_latest_version_expires'), 'NOW()'),
                )
            )
        );
    }

    /**
     * Scope a query to filter by document types list.
     */
    protected function scopeTypes(QueryBuilder $builder, array $typeIds)
    {
        if (empty($typeIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('id_type'),
                array_map(
                    fn (int $index, $type) => $builder->createNamedParameter(
                        (int) $type,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("document_types_id_{$index}", true)
                    ),
                    array_keys($typeIds),
                    $typeIds
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime.
     */
    protected function scopeCreatedAt(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('date_created'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_created_at', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime from.
     */
    protected function scopeCreatedFrom(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('date_created'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_created_from', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     */
    protected function scopeCreatedTo(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('date_created'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_created_to', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation date.
     *
     * @param \DateTimeInterface|int|string $createdAt
     */
    protected function scopeCreatedAtDate(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->qualifyColumn('date_created')})",
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_created_at_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     */
    protected function scopeCreatedFromDate(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->qualifyColumn('date_created')})",
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_created_from_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation date to.
     */
    protected function scopeCreatedToDate(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->qualifyColumn('date_created')})",
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_created_to_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by update datetime.
     */
    protected function scopeUpdatedAt(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('date_updated'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_updated', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_updated_at', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by update datetime from.
     */
    protected function scopeUpdatedFrom(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('date_updated'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_updated', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_updated_from', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by update datetime to.
     */
    protected function scopeUpdatedTo(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('date_updated'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_updated', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_updated_to', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by update date.
     */
    protected function scopeUpdatedAtDate(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->qualifyColumn('date_updated')})",
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_updated', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_updated_at_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by update date from.
     */
    protected function scopeUpdatedFromDate(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->qualifyColumn('date_updated')})",
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_updated', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_updated_from_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by update date to.
     */
    protected function scopeUpdatedToDate(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->qualifyColumn('date_updated')})",
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_updated', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_updated_to_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime.
     */
    protected function scopeExpiredAt(QueryBuilder $builder, DateTimeImmutable $expiredAt)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('date_latest_version_expires'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_latest_version_expires', $expiredAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_expired_at', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime from.
     */
    protected function scopeExpiredFrom(QueryBuilder $builder, DateTimeImmutable $expiredAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('date_latest_version_expires'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_latest_version_expires', $expiredAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_expired_from', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime to.
     */
    protected function scopeExpiredTo(QueryBuilder $builder, DateTimeImmutable $expiredAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('date_latest_version_expires'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_latest_version_expires', $expiredAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_expired_to', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by expiration date.
     */
    protected function scopeExpiredAtDate(QueryBuilder $builder, DateTimeImmutable $expiredAt)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('date_latest_version_expires'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_latest_version_expires', $expiredAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_expired_at_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by expiration date from.
     */
    protected function scopeExpiredFromDate(QueryBuilder $builder, DateTimeImmutable $expiredAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('date_latest_version_expires'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_latest_version_expires', $expiredAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_expired_from_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter by expiration date to.
     */
    protected function scopeExpiredToDate(QueryBuilder $builder, DateTimeImmutable $expiredAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('date_latest_version_expires'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_latest_version_expires', $expiredAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('document_expired_to_date', true)
                )
            )
        );
    }

    /**
     * Scope a query to filter expiration date interval.
     */
    protected function scopeExpiredInInterval(QueryBuilder $builder, DateTimeImmutable $from, DateTimeImmutable $to)
    {
        $builder->andWhere(
            $builder->expr()->and(
                $this->qualifyColumn('date_latest_version_expires'),
                $builder->expr()->gt(
                    $this->qualifyColumn('date_latest_version_expires'),
                    $builder->createNamedParameter(
                        $this->castAttributeToDatabaseValue('date_latest_version_expires', $from),
                        ParameterType::STRING,
                        $this->nameScopeParameter('document_expired_interval_from', true)
                    )
                ),
                $builder->expr()->lte(
                    $this->qualifyColumn('date_latest_version_expires'),
                    $builder->createNamedParameter(
                        $this->castAttributeToDatabaseValue('date_latest_version_expires', $to),
                        ParameterType::STRING,
                        $this->nameScopeParameter('document_expired_interval_to', true)
                    )
                )
            )
        );
    }

    protected function scopeExpiringAfter(QueryBuilder $builder, DateInterval $interval)
    {
        $this->scopeExpiredInInterval($builder, new \DateTimeImmutable(), (new \DateTimeImmutable())->add($interval));
    }

    /**
     * Scope a query to filter expiration by list of date intervals.
     *
     * @param \DateInterval[] $intervals
     */
    protected function scopeExpiringInDates(QueryBuilder $builder, DateTimeImmutable $currentDate, array $intervals = [])
    {
        if (empty($intervals)) {
            return;
        }

        $parameters = [];
        foreach ($intervals as $index => $interval) {
            if (!$interval instanceof \DateInterval) {
                throw new \InvalidArgumentException(sprintf('The interval nr. %s must be instance of "\\%s"', $index + 1, \DateInterval::class));
            }

            $parameters[] = $builder->createNamedParameter(
                $currentDate->add($interval)->format('Y-m-d'),
                ParameterType::STRING,
                $this->nameScopeParameter("document_expire_interval_{$index}", true)
            );
        }

        $builder->andWhere(
            $builder->expr()->and(
                $this->qualifyColumn('date_latest_version_expires'),
                $builder->expr()->in(
                    $this->qualifyColumn('date_latest_version_expires'),
                    $parameters
                )
            )
        );
    }

    /**
     * Scope a query to filter creation of the original between two dates.
     */
    protected function scopeOriginalCreatedInInterval(QueryBuilder $builder, DateTimeImmutable $from, DateTimeImmutable $to)
    {
        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->isNotNull($this->qualifyColumn('date_original_version_created')),
                $builder->expr()->gte(
                    $this->qualifyColumn('date_original_version_created'),
                    $builder->createNamedParameter(
                        $this->castAttributeToDatabaseValue('date_original_version_created', $from),
                        ParameterType::STRING,
                        $this->nameScopeParameter('document_created_interval_from', true)
                    )
                ),
                $builder->expr()->lte(
                    $this->qualifyColumn('date_original_version_created'),
                    $builder->createNamedParameter(
                        $this->castAttributeToDatabaseValue('date_original_version_created', $to),
                        ParameterType::STRING,
                        $this->nameScopeParameter('document_created_interval_to', true)
                    )
                )
            )
        );
    }

    /**
     * Scope a query to bind users to the query.
     */
    protected function bindDocumentType(QueryBuilder $builder)
    {
        $types = $this->type()->getRelated();
        $builder->leftJoin(
            $this->getTable(),
            $types->getTable(),
            $types->getTable(),
            "{$this->qualifyColumn('id_type')} = {$types->qualifyColumn('id_document')}"
        );
    }

    /**
     * Relation with the type.
     */
    protected function type(): RelationInterface
    {
        return $this->belongsTo(Verification_Document_Types_Model::class, 'id_type')->enableNativeCast();
    }

    /**
     * Relation with the user.
     */
    protected function owner(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with the principal.
     */
    protected function principal(): RelationInterface
    {
        return $this->belongsTo(Principals_Model::class, 'id_principal')->enableNativeCast();
    }

    /**
     * Relation with the manager.
     */
    protected function manager(): RelationInterface
    {
        return $this->hasOne(Users_Model::class, 'id_manager')->enableNativeCast();
    }
}

// End of file verification_documents_model.php
// Location: /tinymvc/myapp/models/verification_documents_model.php
