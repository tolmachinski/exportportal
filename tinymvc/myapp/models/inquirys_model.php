<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Inquirys model
 */
final class Inquirys_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "item_inquiry";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "ITEM_INQUIRY";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_inquiry";
}

/* End of file inquirys_model.php */
/* Location: /tinymvc/myapp/models/inquirys_model.php */
