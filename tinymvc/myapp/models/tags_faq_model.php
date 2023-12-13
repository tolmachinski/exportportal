<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;

/**
 * Tags_Faq model
 */
final class Tags_Faq_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "faq_tags";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "FAQ_TAGS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_tag";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_tag'        => Types::INTEGER,
        'date_created'  => Types::DATETIME_IMMUTABLE,
        'date_updated'  => Types::DATETIME_IMMUTABLE,
        'top_priority'  => Types::INTEGER,
    ];

    /**
     * Scope query by tags ids
     *
     * @param QueryBuilder $builder
     * @param array $tagsIds
     *
     * @return void
     */
    protected function scopeTagsIds(QueryBuilder $builder, array $tagsIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`{$this->getPrimaryKey()}`",
                array_map(
                    fn ($index, $tagId) => $builder->createNamedParameter(
                        (int) $tagId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("tagID{$index}")
                    ),
                    array_keys($tagsIds),
                    $tagsIds
                )
            )
        );
    }
}

/* End of file tags_faq_model.php */
/* Location: /tinymvc/myapp/models/tags_faq_model.php */
