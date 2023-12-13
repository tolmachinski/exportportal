<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * User_Rights_Pivot model.
 */
final class User_Group_Rights_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'usergroup_rights';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_RIGHTS_PIVOT';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = ['idgroup', 'idright'];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'idgroup' => Types::INTEGER,
        'idright' => Types::INTEGER,
    ];

    /**
     * Relation with group.
     */
    protected function group(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'idgroup');
    }

    /**
     * Relation with right.
     */
    protected function right(): RelationInterface
    {
        return $this->belongsTo(Rights_Model::class, 'idright');
    }
}

// End of file user_rights_pivot_model.php
// Location: /tinymvc/myapp/models/user_rights_pivot_model.php
