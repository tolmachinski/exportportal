<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Pos model
 */
final class Pos_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_po";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_PO";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_po";
}

/* End of file pos_model.php */
/* Location: /tinymvc/myapp/models/pos_model.php */
