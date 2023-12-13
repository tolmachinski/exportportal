<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;

/**
 * Tags_Faq_Relation model
 */
final class Tags_Faq_Relation_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "faq_tags_relation";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "FAQ_TAGS_RELATION";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_rel";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_rel'    => Types::INTEGER,
        'id_faq'    => Types::INTEGER,
        'id_tag'    => Types::INTEGER,
    ];

    /**
     * Scope relations by faq id
     *
     * @param QueryBuilder $builder
     * @param int $faqId
     *
     * @return void
    */
    protected function scopeFaqId(QueryBuilder $builder, int $faqId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_faq`",
                $builder->createNamedParameter($faqId, ParameterType::INTEGER, $this->nameScopeParameter('faqId'))
            )
        );
    }
}

/* End of file tags_faq_relation_model.php */
/* Location: /tinymvc/myapp/models/tags_faq_relation_model.php */
