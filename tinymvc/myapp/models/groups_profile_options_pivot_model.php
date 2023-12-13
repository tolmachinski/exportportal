<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Groups_Profile_Options_Pivot model.
 */
final class Groups_Profile_Options_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'group_profile_options_relation';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'GROUP_PROFILE_OPTIONS_RELATION';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'group_option_rel_id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'group_option_rel_id',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'id_group',
        'id_option',
        'option_percent',
        'option_weight',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'group_option_rel_id' => Types::INTEGER,
        'id_group'            => Types::INTEGER,
        'id_option'           => Types::INTEGER,
        'option_percent'      => Types::INTEGER,
        'option_weight'       => Types::INTEGER,
        'is_required'         => Types::BOOLEAN,
    ];

    /**
     * Scope a query to filter by entry ID.
     */
    protected function scopeId(QueryBuilder $builder, int $entryId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($entryId, ParameterType::INTEGER, $this->nameScopeParameter('entry'))
            )
        );
    }

    /**
     * Scope a query to filter by group ID.
     */
    protected function scopeGroup(QueryBuilder $builder, int $groupId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_group'),
                $builder->createNamedParameter($groupId, ParameterType::INTEGER, $this->nameScopeParameter('group'))
            )
        );
    }

    /**
     * Scope a query to filter by option ID.
     */
    protected function scopeCompletionOption(QueryBuilder $builder, int $optionId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_option'),
                $builder->createNamedParameter($optionId, ParameterType::INTEGER, $this->nameScopeParameter('option'))
            )
        );
    }

    /**
     * Relation with user group.
     */
    protected function group(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'id_group');
    }

    /**
     * Relation with profile completion options.
     */
    protected function completionOption(): RelationInterface
    {
        return $this->belongsTo(Complete_Profile_Options_Model::class, 'id_option');
    }
}

// End of file groups_profile_options_pivot_model.php
// Location: /tinymvc/myapp/models/groups_profile_options_pivot_model.php
