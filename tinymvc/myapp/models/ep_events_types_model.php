<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * Ep_Events_Types model
 */
final class Ep_Events_Types_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "ep_events_types";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "EP_EVENTS_TYPES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope type by type slug
     *
     * @var string $typeSlug
     *
     * @return void
     */
    protected function scopeSlug(QueryBuilder $builder, string $typeSlug): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'slug',
                $builder->createNamedParameter($typeSlug, ParameterType::STRING, $this->nameScopeParameter('typeSlug'))
            )
        );
    }
}

/* End of file ep_events_types_model.php */
/* Location: /tinymvc/myapp/models/ep_events_types_model.php */
