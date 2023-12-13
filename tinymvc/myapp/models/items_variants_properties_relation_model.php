<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Items_Variants_Properties_Relation model
 */
final class Items_Variants_Properties_Relation_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "items_variants_properties_relation";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEMS_VARIANTS_PROPERTIES_RELATION";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_property_option'    => Types::INTEGER,
        'id_variant'            => Types::INTEGER,
        'id'                    => Types::INTEGER,
    ];
}

/* End of file items_variants_properties_relation_model.php */
/* Location: /tinymvc/myapp/models/items_variants_properties_relation_model.php */
