<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\String\UnicodeString;

/**
 * Complaints model.
 */
class Complaints_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'date_time';

    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    protected const UPDATED_AT = 'date_change';

    /**
     * The table name.
     */
    protected string $table = 'complains';

    /**
     * The table primary key.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

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
        'id_to',
        'notice',
        'search_info',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'             => Types::INTEGER,
        'id_type'        => Types::INTEGER,
        'id_theme'       => Types::INTEGER,
        'id_item'        => Types::INTEGER,
        'id_from'        => Types::INTEGER,
        'id_to'          => Types::INTEGER,
        self::CREATED_AT => Types::DATETIME_IMMUTABLE,
        self::UPDATED_AT => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope query by type.
     */
    public function scopeType(QueryBuilder $builder, int $type): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_type',
                $builder->createNamedParameter($type, ParameterType::INTEGER, $this->nameScopeParameter('typeId'))
            )
        );
    }

    /**
     * Scope query by item.
     */
    public function scopeItem(QueryBuilder $builder, int $item): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_item',
                $builder->createNamedParameter($item, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope query by complainer.
     */
    public function scopeComplainer(QueryBuilder $builder, int $complainer): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_from',
                $builder->createNamedParameter($complainer, ParameterType::INTEGER, $this->nameScopeParameter('complainerId'))
            )
        );
    }

    /**
     * Scope query by complainee.
     */
    public function scopeComplainee(QueryBuilder $builder, int $complainee): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_to',
                $builder->createNamedParameter($complainee, ParameterType::INTEGER, $this->nameScopeParameter('complaineeId'))
            )
        );
    }

    /**
     * Scope query by complainee.
     */
    public function scopeStatus(QueryBuilder $builder, string $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'status',
                $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('status'))
            )
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $search_text = (new UnicodeString($text))->trim();
        $search_tokens = $this->tokenizeSearchText($search_text, true);
        $use_match_search = !empty($search_tokens);
        if ($use_match_search) {
            $builder
                ->andWhere(
                    sprintf(
                        "(MATCH (`{$this->getTable()}`.`search_info`) AGAINST (%s IN BOOLEAN MODE))",
                        $builder->createNamedParameter(
                            $this->db->pdo->quote(implode(' ', $search_tokens)),
                            ParameterType::STRING,
                            $this->nameScopeParameter('searchText')
                        )
                    )
                )
            ;
        } else {
            $builder
                ->andWhere(
                    sprintf(
                        "(`{$this->getTable()}`.`search_info` = %s OR `{$this->getTable()}`.`search_info` LIKE %s)",
                        $builder->createNamedParameter(
                            (string) $search_text,
                            ParameterType::STRING,
                            $this->nameScopeParameter('originalSearchText')
                        ),
                        $builder->createNamedParameter(
                            (string) $search_text->prepend('%')->append('%'),
                            ParameterType::STRING,
                            $this->nameScopeParameter('wrappedSearchText')
                        )
                    )
                )
            ;
        }
    }

    /**
     * Resolves static relationships with complaint type.
     */
    protected function type(): RelationInterface
    {
        return $this->belongsTo(Complaint_Types_Model::class, 'id_type')->disableNativeCast();
    }

    /**
     * Resolves static relationships with complaint theme.
     */
    protected function theme(): RelationInterface
    {
        return $this->belongsTo(Complaint_Themes_Model::class, 'id_theme')->disableNativeCast();
    }
}

// End of file comment_reports_model.php
// Location: /tinymvc/myapp/models/comment_reports_model.php
