<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Complaint_Types model.
 */
class Complaint_Types_Model extends Model
{
    /**
     * The table name.
     */
    protected string $table = 'complains_types';

    /**
     * The table primary key.
     */
    protected $primaryKey = 'id_type';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_type',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id' => Types::INTEGER,
    ];

    /**
     * Resolves static relationships with complaint themes.
     */
    protected function themes(): RelationInterface
    {
        /** @var Complaint_Themes_Model $themes */
        $themes = model(Complaint_Themes_Model::class);
        /** @var Complaint_Types_Themes_Pivot_Model $pivot */
        $pivot = model(Complaint_Types_Themes_Pivot_Model::class);

        $realtion = $this->hasMany($pivot, 'id_theme');
        $realtion->disableNativeCast();
        $builder = $realtion->getQuery();
        $builder
            ->select("{$pivot->getTable()}.id_type", "{$themes->getTable()}.*")
            ->innerJoin($pivot->getTable(), $themes->getTable(), null, "{$themes->getTable()}.{$themes->getPrimaryKey()} = {$pivot->getTable()}.id_theme")
        ;

        return $realtion;
    }
}

// End of file complaint_types_model.php
// Location: /tinymvc/myapp/models/complaint_types_model.php
