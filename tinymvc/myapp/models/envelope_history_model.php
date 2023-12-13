<?php

declare(strict_types=1);

use App\Casts\Envelopes\HistoryEventCast;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Types\Types;

/**
 * Envelope_History model.
 */
final class Envelope_History_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    protected const CREATED_AT = 'created_at_date';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    protected const UPDATED_AT = 'updated_at_date';

    /**
     * {@inheritdoc}
     */
    protected string $table = 'envelope_history';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'ENVELOPE_HISTORY';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

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
        'context',
        self::UPDATED_AT,
        self::CREATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        // Generic types
        'id'          => Types::INTEGER,
        'id_user'     => Types::INTEGER,
        'id_envelope' => Types::INTEGER,
        'context'     => Types::JSON,

        // Complex types
        'event'       => HistoryEventCast::class,
    ];

    /**
     * Relation with the envelope.
     */
    protected function envelope(): RelationInterface
    {
        return $this->belongsTo(Envelopes_Model::class, 'id_envelope')->enableNativeCast();
    }

    /**
     * Relation with the sender.
     */
    protected function user(): RelationInterface
    {
        return \tap(
            $this->belongsTo(
                (new PortableModel($this->getHandler(), 'users', 'idu'))->mergeCasts([
                    'idu'          => Types::INTEGER,
                    'id_principal' => Types::INTEGER,
                    'user_group'   => Types::INTEGER,
                ]),
                'id_sender'
            ),
            function (RelationInterface $relation) {
                $relation->enableNativeCast();
                /** @var User_Groups_Model $userGroups */
                $userGroups = $this->resolveRelatedModel(User_Groups_Model::class);
                $related = $relation->getRelated();
                $related->mergeCasts(array_merge($userGroups->getCasts(), ['id' => Types::INTEGER]));
                $relation
                    ->getQuery()
                    ->select(
                        '*',
                        "{$related->qualifyColumn($related->getPrimaryKey())} as `id`",
                        "{$userGroups->qualifyColumn('`gr_type`')} as `group_type`",
                        "TRIM(CONCAT({$related->qualifyColumn('`fname`')}, ' ', {$related->qualifyColumn('`lname`')})) AS `full_name`",
                        "{$related->qualifyColumn('`legal_name`')}",
                    )
                    ->innerJoin(
                        $related->getTable(),
                        $userGroups->getTable(),
                        null,
                        "{$related->qualifyColumn('user_group')} = {$userGroups->qualifyColumn($userGroups->getPrimaryKey())}"
                    )
                ;
            }
        );
    }
}

// End of file envelope_history_model.php
// Location: /tinymvc/myapp/models/envelope_history_model.php
