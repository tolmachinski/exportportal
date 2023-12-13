<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Complete_Profile_Options model.
 */
final class Complete_Profile_Options_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'profile_options';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'PROFILE_OPTIONS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_option';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_option',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_option'  => Types::INTEGER,
        'option_img' => Types::JSON,
    ];

    /**
     * Scope a query to filter by option alias.
     */
    protected function scopeGroup(QueryBuilder $builder, string $alias)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('option_alias'),
                $builder->createNamedParameter($alias, ParameterType::STRING, $this->nameScopeParameter('alias'))
            )
        );
    }
}

// End of file complete_profile_options_model.php
// Location: /tinymvc/myapp/models/complete_profile_options_model.php
