<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * Ep_Events_Partners model
 */
final class Ep_Events_Partners_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "ep_events_partners";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "EP_EVENTS_PARTNERS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

        /**
     * Scope partner by parner ID.
     *
     * @var int $partnerId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $partnerId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($partnerId, ParameterType::INTEGER, $this->nameScopeParameter('partnerId'))
            )
        );
    }

    /**
     * Scope partner by partners ids
     */
    protected function scopePartnersIds(QueryBuilder $builder, array $partnersIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`{$this->getPrimaryKey()}`",
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
}

/* End of file ep_events_partners_model.php */
/* Location: /tinymvc/myapp/models/ep_events_partners_model.php */
