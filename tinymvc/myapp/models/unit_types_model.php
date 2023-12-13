<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Unit_Types model
 */
final class Unit_Types_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "unit_type";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "UNIT_TYPE";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'    => Types::INTEGER,
    ];
}

/* End of file unit_types_model.php */
/* Location: /tinymvc/myapp/models/unit_types_model.php */
