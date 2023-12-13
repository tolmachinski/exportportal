<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * Ep_Events_Partners_Relations model
 */
final class Ep_Events_Partners_Relations_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "ep_events_partners_relations";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "EP_EVENTS_PARTNERS_RELATIONS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = array("id_event", "id_partner");

    /**
     * Scope relation by partners ids
     *
     * @var array $partnerIds
     *
     */
    protected function scopePartnersIds(QueryBuilder $builder, array $partnersIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`id_partner`",
                array_map(
                    fn ($index, $partnerId) => $builder->createNamedParameter(
                        (int) $partnerId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("partnerId{$index}")
                    ),
                    array_keys($partnersIds),
                    $partnersIds
                )
            )
        );
    }

    /**
     * Scope comment query by event ID.
     *
     * @var int $eventId
     *
     * @return void
     */
    protected function scopeEventId(QueryBuilder $builder, int $eventId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                '`id_event`',
                $builder->createNamedParameter($eventId, ParameterType::INTEGER, $this->nameScopeParameter('eventId'))
            )
        );
    }
}

/* End of file ep_events_partners_relations_model.php */
/* Location: /tinymvc/myapp/models/ep_events_partners_relations_model.php */
