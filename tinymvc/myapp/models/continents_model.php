<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Continents model
 */
final class Continents_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "continents";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "CONTINENTS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id_continent";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_continent'          => Types::INTEGER,
    ];
}

/* End of file continents_model.php */
/* Location: /tinymvc/myapp/models/continents_model.php */
