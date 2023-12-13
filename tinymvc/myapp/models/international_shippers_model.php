<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * International_Shippers model
 */
final class International_Shippers_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "international_shippers";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "INTERNATIONAL_SHIPPERS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_shipper";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_shipper' => Types::INTEGER,
    ];
}

/* End of file international_shippers_model.php */
/* Location: /tinymvc/myapp/models/international_shippers_model.php */
