<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Complaint_Themes model.
 */
class Complaint_Themes_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    protected const UPDATED_AT = 'date_updated';

    /**
     * The table name.
     */
    protected string $table = 'complains_types_themes';

    /**
     * The table primary key.
     */
    protected $primaryKey = 'id_theme';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'theme',
        'message',
        'i18n',
        'text_updated_at',
        'translated_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'              => Types::INTEGER,
        'text_updated_at' => Types::DATETIME_IMMUTABLE,
        'translated_at'   => Types::DATETIME_IMMUTABLE,
        self::CREATED_AT  => Types::DATETIME_IMMUTABLE,
        self::UPDATED_AT  => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Resolves static relationships with complaint types.
     */
    protected function types(): RelationInterface
    {
        /** @var Complaint_Types_Model $types */
        $types = model(Complaint_Types_Model::class);
        /** @var Complaint_Types_Themes_Pivot_Model $pivot */
        $pivot = model(Complaint_Types_Themes_Pivot_Model::class);

        $realtion = $this->hasMany($pivot, 'id_theme');
        $realtion->disableNativeCast();
        $builder = $realtion->getQuery();
        $builder
            ->select("{$pivot->getTable()}.id_theme", "{$types->getTable()}.*")
            ->innerJoin($pivot->getTable(), $types->getTable(), null, "{$types->getTable()}.{$types->getPrimaryKey()} = {$pivot->getTable()}.id_type")
        ;

        return $realtion;
    }
}

// End of file complaint_themes_model.php
// Location: /tinymvc/myapp/models/complaint_themes_model.php
