<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * Ep_Events_Images model
 */
final class Ep_Events_Images_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "ep_events_images";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "EP_EVENTS_IMAGES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope event gallery query by event ID.
     * @var int $eventId
     *
     * @return void
     */
    protected function scopeEventId(QueryBuilder $builder, int $eventId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->getTable()}`.`id_event`",
                $builder->createNamedParameter($eventId, ParameterType::INTEGER, $this->nameScopeParameter('eventId'))
            )
        );
    }

    /**
     * Scope event gallery query by images name.
     * @var array $images
     *
     * @return void
     */
    protected function scopeImages(QueryBuilder $builder, array $images): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`name`",
                array_map(
                    fn ($index, $image) => $builder->createNamedParameter(
                        (string) $image,
                        ParameterType::STRING,
                        $this->nameScopeParameter("resourceTokens{$index}")
                    ),
                    array_keys($images),
                    $images
                )
            )
        );
    }
}

/* End of file ep_events_images_model.php */
/* Location: /tinymvc/myapp/models/ep_events_images_model.php */
