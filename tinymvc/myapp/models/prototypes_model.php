<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Prototypes model
 */
final class Prototypes_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_prototype";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_PROTOTYPE";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_prototype";
}

/* End of file prototypes_model.php */
/* Location: /tinymvc/myapp/models/prototypes_model.php */
