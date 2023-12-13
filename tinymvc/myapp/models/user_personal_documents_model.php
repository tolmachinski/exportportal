<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\BaseModel;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Model UserPersonalDocuments.
 *
 * @deprecated v2.30.3 in favor of \Verification_Documents_Model
 */
class User_Personal_Documents_Model extends BaseModel
{
    use Concerns\CanTransformValues;
    use Concerns\ConvertsAttributes;
    use Concerns\CanSearch;

    /**
     * List of columns the table which is later used on create or update.
     * Each record must be an array that have filled keys:
     *  - name: is a name of the column
     *  - type: indicates the type of the column
     *  - fillable: indicates that column is fillable in NORMAL mode (columns with this flag set to `false` are fillable in FORCE mode).
     *
     * @var array
     */
    protected $documents_columns_metadata = [
        ['name' => 'id_document',       'fillable' => false, 'type' => 'int'],
        ['name' => 'id_type',           'fillable' => true,  'type' => 'int'],
        ['name' => 'id_user',           'fillable' => true,  'type' => 'int'],
        ['name' => 'id_principal',      'fillable' => true,  'type' => 'int'],
        ['name' => 'versions',          'fillable' => true,  'type' => 'array', 'nullable' => true],
        ['name' => 'date_created',      'fillable' => false, 'type' => 'datetime'],
        ['name' => 'date_updated',      'fillable' => false, 'type' => 'datetime'],
        ['name' => 'subtitle',          'fillable' => true,  'type' => 'string'],
    ];

    private $userguide_by_group = [
        'buyer'                  => 'EPUserGuide_Buyer.pdf',
        'verified-seller'        => 'EPUserGuide_Seller.pdf',
        'certified-seller'       => 'EPUserGuide_Seller.pdf',
        'verified-manufacturer'  => 'EPUserGuide_Manufacturer.pdf',
        'certified-manufacturer' => 'EPUserGuide_Manufacturer.pdf',
        'shipper'                => 'EPUserGuide_FreightForwarder.pdf',
    ];
    /**
     * Name of the user personal documents table.
     *
     * @var string
     */
    private $documents_table = 'users_personal_documents';

    /**
     * Alias of the user personal documents table.
     *
     * @var string
     */
    private $documents_table_alias = 'PERSONAL_DOCUMENTS';

    /**
     * Name of the user personal documents table.
     *
     * @var string
     */
    private $users_table = 'users';

    /**
     * Alias of the user personal documents table.
     *
     * @var string
     */
    private $users_table_alias = 'USERS';

    /**
     * Name of the user personal document types table.
     *
     * @var string
     */
    private $document_types_table = 'accreditation_docs';

    /**
     * Alias of the user personal document types table.
     *
     * @var string
     */
    private $document_types_table_alias = 'DOCUMENT_TYPES';

    public function get_document($document_id, array $params = [])
    {
        return $this->findRecord(
            'document',
            $this->documents_table,
            $this->documents_table_alias,
            'id_document',
            $document_id,
            $params
        );
    }

    public function find_document(array $params = [])
    {
        return $this->findRecord(
            'document',
            $this->documents_table,
            $this->documents_table_alias,
            null,
            null,
            $params
        );
    }

    public function get_documents(array $params = [])
    {
        return $this->findRecords(
            'document',
            $this->documents_table,
            $this->documents_table_alias,
            $params
        );
    }

    public function get_userguide_by_group(string $group_alias)
    {
        return $this->userguide_by_group[$group_alias];
    }

    public function count_documents(array $params = [])
    {
        unset($params['order'], $params['with'], $params['limit'], $params['skip']);
        if (!isset($params['columns'])) {
            $params['columns'] = ['COUNT(*) AS AGGREGATE'];
        }

        $is_multiple = isset($params['multiple']) ? (bool) $params['multiple'] : false;
        $counters = $this->get_documents($params);
        if ($is_multiple) {
            return $counters;
        }

        return (int) arrayGet($counters, '0.AGGREGATE', 0);
    }

    public function create_document(array $document, $force = false)
    {
        return $this->db->insert(
            $this->documents_table,
            $this->recordAttributesToDatabaseValues(
                $document,
                $this->documents_columns_metadata,
                $force
            )
        );
    }

    public function create_documents(array $documents, $force = false)
    {
        return $this->db->insert_batch(
            $this->documents_table,
            $this->recordsListToDatabaseValues(
                $documents,
                $this->documents_columns_metadata,
                $force
            )
        );
    }

    public function update_document($document_id, array $document, $force = false)
    {
        $this->db->where("`{$this->documents_table}`.`id_document` = ?", (int) $document_id);

        return $this->db->update(
            $this->documents_table,
            $this->recordAttributesToDatabaseValues(
                $document,
                $this->documents_columns_metadata,
                $force
            )
        );
    }

    public function delete_document($document_id)
    {
        $this->db->where("`{$this->documents_table}`.`id_document` = ?", (int) $document_id);

        return $this->db->delete($this->documents_table);
    }

    public function delete_user_documents($user_id, $type_id = null)
    {
        $this->db->where("`{$this->documents_table}`.`id_user` = ?", (int) $user_id);

        if (null !== $type_id) {
            $list = getArrayFromString($type_id);

            $this->db->where_raw("`{$this->documents_table}`.`id_type` IN (" . implode(',', array_fill(0, count($list), '?')) . ")", $list);
        }

        return $this->db->delete($this->documents_table);
    }

    /**
     * Scope a query to filter by keywords.
     *
     * @param string $keywords
     */
    protected function scopeDocumentSearch(QueryBuilder $builder, $keywords)
    {
        if (empty($keywords)) {
            return;
        }

        $expressions = $builder->expr();
        $text_parameter = $builder->createNamedParameter(
            $keywords,
            ParameterType::STRING,
            $this->nameScopeParameter('documentSearchText')
        );
        $text_token_parameter = $builder->createNamedParameter(
            "%{$keywords}%",
            ParameterType::STRING,
            $this->nameScopeParameter('documentSearchTextToken')
        );
        $intl_search_expression = $expressions->or(
            $expressions->eq("{$this->document_types_table}.document_translated_titles", $text_parameter),
            $expressions->eq("{$this->document_types_table}.document_translated_descriptions", $text_parameter),
            $expressions->like("{$this->document_types_table}.document_translated_titles", $text_token_parameter),
            $expressions->like("{$this->document_types_table}.document_translated_descriptions", $text_token_parameter),
            $expressions->like("{$this->documents_table_alias}.subtitle", $text_token_parameter),
        );

        $search_tokens = $this->tokenizeSearchText($keywords, true);
        $use_boolean_search = !empty($search_tokens);
        if ($use_boolean_search) {
            $search_expression = $builder->expr()->and(
                sprintf(
                    'MATCH (%s.document_title, %s.document_description) AGAINST (%s IN BOOLEAN MODE)',
                    $this->document_types_table,
                    $this->document_types_table,
                    $builder->createNamedParameter(
                        $builder->expr()->literal(implode(' ', $search_tokens)),
                        ParameterType::STRING,
                        $this->nameScopeParameter('documentSearchMatchedText')
                    )
                )
            );
        } else {
            $search_expression = $expressions->or(
                $expressions->eq("{$this->document_types_table}.document_title", $text_parameter),
                $expressions->eq("{$this->document_types_table}.document_description", $text_parameter),
                $expressions->like("{$this->document_types_table}.document_title", $text_token_parameter),
                $expressions->like("{$this->document_types_table}.document_description", $text_token_parameter),
                $expressions->like("{$this->documents_table_alias}.subtitle", $text_token_parameter),
            );
        }

        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('id_document')
            ->from($this->document_types_table)
            ->where(
                $subquery_builder->expr()->or(
                    $search_expression,
                    $intl_search_expression
                )
            )
        ;

        $builder->andWhere(
            $expressions->in("{$this->documents_table_alias}.id_type", "({$subquery_builder->getSQL()})")
        );
    }

    /**
     * Scope a query to filter by document principal ID.
     */
    protected function scopeDocumentPrincipal(QueryBuilder $builder, int $principal_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->documents_table_alias}.id_principal",
                $builder->createNamedParameter((int) $principal_id, ParameterType::INTEGER, $this->nameScopeParameter('documentPrincipalId'))
            )
        );
    }

    /**
     * Scope a query to filter by document user ID.
     *
     * @param int $user_id
     */
    protected function scopeDocumentUser(QueryBuilder $builder, $user_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->documents_table_alias}.id_user",
                $builder->createNamedParameter((int) $user_id, ParameterType::INTEGER, $this->nameScopeParameter('documentUserId'))
            )
        );
    }

    /**
     * Scope a query to filter by document expluding user ID.
     */
    protected function scopeDocumentNotUser(QueryBuilder $builder, int $user_id)
    {
        $builder->andWhere(
            $builder->expr()->neq(
                "{$this->documents_table_alias}.id_user",
                $builder->createNamedParameter((int) $user_id, ParameterType::INTEGER, $this->nameScopeParameter('documentNotUserId'))
            )
        );
    }

    /**
     * Scope a query to filter by document type ID.
     *
     * @param int $type_id
     */
    protected function scopeDocumentType(QueryBuilder $builder, $type_id)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->documents_table_alias}.id_type",
                $builder->createNamedParameter((int) $type_id, ParameterType::INTEGER, $this->nameScopeParameter('documentTypeId'))
            )
        );
    }

    /**
     * Scope a query to filter by document type ID.
     */
    protected function scopeDocumentNotAcceptedOrExpired(QueryBuilder $builder)
    {
        $expressions = $builder->expr();
        $builder->andWhere(
            $expressions->or(
                $expressions->isNull("{$this->documents_table_alias}.latest_version"),
                $expressions->neq(
                    "{$this->documents_table_alias}.latest_version->>'$.type'",
                    $builder->createNamedParameter('accepted', ParameterType::STRING, $this->nameScopeParameter('documentAcceptedStatus'))
                ),
                $expressions->and(
                    $expressions->isNotNull("{$this->documents_table_alias}.date_latest_version_expires"),
                    $expressions->lte("{$this->documents_table_alias}.date_latest_version_expires", 'NOW()'),
                )
            )
        );
    }

    /**
     * Scope a query to filter by document types list.
     *
     * @param mixed $types
     */
    protected function scopeDocumentTypes(QueryBuilder $builder, $types)
    {
        if (empty($types)) {
            return;
        }

        $types = getArrayFromString($types);

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->documents_table_alias}.id_type",
                array_map(
                    fn (int $index, $type) => $builder->createNamedParameter(
                        (int) $type,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("documentTypesIds{$index}")
                    ),
                    array_keys($types),
                    $types
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeDocumentCreatedAt(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->documents_table_alias}.date_created",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentCreatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeDocumentCreatedFrom(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->documents_table_alias}.date_created",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentCreatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeDocumentCreatedTo(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->documents_table_alias}.date_created",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentCreatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeDocumentCreatedAtDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->documents_table_alias}.date_created)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentCreatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeDocumentCreatedFromDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->documents_table_alias}.date_created)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentCreatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeDocumentCreatedToDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->documents_table_alias}.date_created)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentCreatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeDocumentUpdatedAt(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->documents_table_alias}.date_updated",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentUpdatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeDocumentUpdatedFrom(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->documents_table_alias}.date_updated",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentUpdatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by update datetime to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeDocumentUpdatedTo(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->documents_table_alias}.date_updated",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentUpdatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by update date.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeDocumentUpdatedAtDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->documents_table_alias}.date_updated)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentUpdatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update date from.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeDocumentUpdatedFromDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->documents_table_alias}.date_updated)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentUpdatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by update date to.
     *
     * @param \DateTimeInterface|int|string $updated_at
     */
    protected function scopeDocumentUpdatedToDate(QueryBuilder $builder, $updated_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($updated_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->documents_table_alias}.date_updated)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentUpdatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime.
     *
     * @param \DateTimeInterface|int|string $expired_at
     */
    protected function scopeDocumentExpiredAt(QueryBuilder $builder, $expired_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expired_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->documents_table_alias}.date_latest_version_expires",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentExpiredAt'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime from.
     *
     * @param \DateTimeInterface|int|string $expired_at
     */
    protected function scopeDocumentExpiredFrom(QueryBuilder $builder, $expired_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expired_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->documents_table_alias}.date_latest_version_expires",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentExpiredFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration datetime to.
     *
     * @param \DateTimeInterface|int|string $expired_at
     */
    protected function scopeDocumentExpiredTo(QueryBuilder $builder, $expired_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expired_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->documents_table_alias}.date_latest_version_expires",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentExpiredTo'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration date.
     *
     * @param \DateTimeInterface|int|string $expired_at
     */
    protected function scopeDocumentExpiredAtDate(QueryBuilder $builder, $expired_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expired_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->documents_table_alias}.date_latest_version_expires)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentExpiredAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration date from.
     *
     * @param \DateTimeInterface|int|string $expired_at
     */
    protected function scopeDocumentExpiredFromDate(QueryBuilder $builder, $expired_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expired_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->documents_table_alias}.date_latest_version_expires)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentExpiredFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration date to.
     *
     * @param \DateTimeInterface|int|string $expired_at
     */
    protected function scopeDocumentExpiredToDate(QueryBuilder $builder, $expired_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($expired_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->documents_table_alias}.date_latest_version_expires)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('documentExpiredToDate'))
            )
        );
    }

    /**
     * Scope a query to filter expiration date interval.
     *
     * @param mixed $from
     * @param mixed $to
     */
    protected function scopeDocumentExpiredInInterval(QueryBuilder $builder, $from, $to)
    {
        if (
            null === ($from_date = $this->convertDatetimeAttributeToDatabaseValue($from, 'Y-m-d H:i:s'))
            || null === ($to_date = $this->convertDatetimeAttributeToDatabaseValue($to, 'Y-m-d H:i:s'))
        ) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->isNotNull("{$this->documents_table_alias}.date_latest_version_expires"),
                $builder->expr()->gt(
                    "{$this->documents_table_alias}.date_latest_version_expires",
                    $builder->createNamedParameter($from_date, ParameterType::STRING, $this->nameScopeParameter('documentExpiredIntervalFrom'))
                ),
                $builder->expr()->lte(
                    "{$this->documents_table_alias}.date_latest_version_expires",
                    $builder->createNamedParameter($to_date, ParameterType::STRING, $this->nameScopeParameter('documentExpiredIntervalTo'))
                )
            )
        );
    }

    protected function scopeDocumentExpiringAfter(QueryBuilder $builder, DateInterval $interval)
    {
        $current_date = new \DateTimeImmutable();

        $this->scopeDocumentExpiredInInterval($builder, $current_date, $current_date->add($interval));
    }

    /**
     * Scope a query to filter expiration by list of date intervals.
     *
     * @param \DateTimeImmutable $current_date
     * @param \DateInterval[]    $intervals
     */
    protected function scopeDocumentExpiringInDates(QueryBuilder $builder, DateTimeImmutable $current_date, array $intervals = [])
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
                $current_date->add($interval)->format('Y-m-d'),
                ParameterType::STRING,
                $this->nameScopeParameter("documentExpireIntervals{$index}")
            );
        }

        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->isNotNull("{$this->documents_table_alias}.date_latest_version_expires"),
                $builder->expr()->in(
                    "DATE({$this->documents_table_alias}.date_latest_version_expires)",
                    $parameters
                )
            )
        );
    }

    /**
     * Scope a query to filter creation of the original between two dates.
     *
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     */
    protected function scopeDocumentOriginalCreatedInInterval(QueryBuilder $builder, DateTimeImmutable $from, DateTimeImmutable $to)
    {
        if (
            null === ($from_date = $this->convertDatetimeAttributeToDatabaseValue($from, 'Y-m-d H:i:s'))
            || null === ($to_date = $this->convertDatetimeAttributeToDatabaseValue($to, 'Y-m-d H:i:s'))
        ) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->and(
                $builder->expr()->isNotNull("{$this->documents_table_alias}.date_original_version_created"),
                $builder->expr()->gte(
                    "{$this->documents_table_alias}.date_original_version_created",
                    $builder->createNamedParameter($from_date, ParameterType::STRING, $this->nameScopeParameter('documentCreatedIntervalFrom'))
                ),
                $builder->expr()->lte(
                    "{$this->documents_table_alias}.date_original_version_created",
                    $builder->createNamedParameter($to_date, ParameterType::STRING, $this->nameScopeParameter('documentCreatedIntervalTo'))
                )
            )
        );
    }

    /**
     * Scope a query to bind users to the query.
     */
    protected function bindDocumentType(QueryBuilder $builder)
    {
        $builder->leftJoin(
            $this->documents_table_alias,
            $this->document_types_table,
            $this->document_types_table_alias,
            "`{$this->documents_table_alias}`.`id_type` = `{$this->document_types_table_alias}`.`id_document`"
        );
    }

    /**
     * Resolves static relationships with document types.
     */
    protected function documentType(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->document_types_table, 'id_document'),
            'id_type'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with document owner.
     */
    protected function documentOwner(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->users_table, 'idu'),
            'id_user'
        )->disableNativeCast();
    }

    /**
     * Creates new related instance of the model for relation.
     *
     * @param BaseModel|Model|string $source
     */
    protected function resolveRelatedModel($source): Model
    {
        if ($source === $this) {
            return new PortableModel($this->getHandler(), $this->documents_table_alias, 'id_document');
        }

        return parent::resolveRelatedModel($source);
    }
}

// End of file user_personal_documents_model.php
// Location: /tinymvc/myapp/models/user_personal_documents_model.php
