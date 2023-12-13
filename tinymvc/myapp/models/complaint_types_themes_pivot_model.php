<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;

/**
 * Complaint_Types_Themes_Pivot model.
 */
class Complaint_Types_Themes_Pivot_Model extends Model
{
    /**
     * The table name.
     */
    protected string $table = 'complains_types_themes_tie';

    /**
     * The table primary key.
     */
    protected $primaryKey = 'id_tie';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_tie'   => Types::INTEGER,
        'id_type'  => Types::INTEGER,
        'id_theme' => Types::INTEGER,
    ];
}

// End of file complaint_types_themes_pivot_model.php
// Location: /tinymvc/myapp/models/complaint_types_themes_pivot_model.php
