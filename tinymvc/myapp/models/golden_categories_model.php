<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Golden_Categories model
 */
final class Golden_Categories_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_category_groups";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_CATEGORY_GROUPS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_group";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_group'  => Types::INTEGER,
        'images'    => Types::JSON,
    ];

        /**
     * Scope a query to filter by group ID.
     */
    protected function scopeId(QueryBuilder $builder, int $groupId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($groupId, ParameterType::INTEGER, $this->nameScopeParameter('groupId'))
            )
        );
    }


    protected function industries(): RelationInterface
    {
        return $this->hasManyThrough(
            Item_Category_Model::class,
            Golden_Categories_Industries_Pivot_Model::class,
            'id_group',
            'category_id',
            $this->getPrimaryKey(),
            'id_category'
        );
    }
}

/* End of file golden_categories_model.php */
/* Location: /tinymvc/myapp/models/golden_categories_model.php */
