<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Golden_Categories_Industries_Pivot model
 */
final class Golden_Categories_Industries_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_categories_group_relation";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_CATEGORIES_GROUP_RELATION";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";
}

/* End of file golden_categories_industries_pivot_model.php */
/* Location: /tinymvc/myapp/models/golden_categories_industries_pivot_model.php */
