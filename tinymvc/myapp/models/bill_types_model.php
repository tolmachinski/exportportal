<?php

declare(strict_types=1);

use App\Casts\Bill\BillTypesCast;
use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Bill_Types model
 */
final class Bill_Types_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "users_bills_types";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "USERS_BILLS_TYPES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_type";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_type'   => Types::INTEGER,
        'name_type' => BillTypesCast::class,
    ];
}

/* End of file bill_types_model.php */
/* Location: /tinymvc/myapp/models/bill_types_model.php */
