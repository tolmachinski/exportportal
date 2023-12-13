<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Items_Variants_Properties model
 */
final class Items_Variants_Properties_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_variants_properties";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_VARIANTS_PROPERTIES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'priority'  => Types::INTEGER,
        'id_item'   => Types::INTEGER,
        'price'     => Types::DECIMAL,
        'id'        => Types::INTEGER,
    ];

    /**
     * Scope query by item id
     *
     * @param QueryBuilder $builder
     * @param int $itemId
     *
     * @return void
     */
    protected function scopeItemId(QueryBuilder $builder, int $itemId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_item`",
                $builder->createNamedParameter($itemId, ParameterType::INTEGER, $this->nameScopeParameter('itemId'))
            )
        );
    }

    /**
     * Scope query by property ids
     *
     * @param QueryBuilder $builder
     * @param array $propertyIds
     *
     * @return void
     */
    protected function scopeIds(QueryBuilder $builder, array $propertyIds): void
    {
        if (empty($propertyIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id`",
                array_map(
                    fn (int $i, $propertyId) => $builder->createNamedParameter((int) $propertyId, ParameterType::INTEGER, $this->nameScopeParameter("propertyId_{$i}")),
                    array_keys($propertyIds),
                    $propertyIds
                )
            )
        );
    }

    /**
     * Resolves static relationships with property Options
     */
    protected function propertyOptions(): RelationInterface
    {
        return $this->hasMany(Items_Variants_Properties_Options_Model::class, 'id_property')->enableNativeCast();
    }
}

/* End of file items_variants_properties_model.php */
/* Location: /tinymvc/myapp/models/items_variants_properties_model.php */
