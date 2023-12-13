<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\ParameterType;

/**
 * Ep_Events_Speakers model
 */
final class Ep_Events_Speakers_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "ep_events_speakers";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "EP_EVENTS_SPEAKERS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * Scope speaker by speaker ID.
     *
     * @var int $speakerId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $speakerId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($speakerId, ParameterType::INTEGER, $this->nameScopeParameter('speakerId'))
            )
        );
    }

    /**
     * Scope speaker by speakers ids
     */
    protected function scopeSpeakersIds(QueryBuilder $builder, array $speakersIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`{$this->getPrimaryKey()}`",
                array_map(
                    fn ($index, $speakerId) => $builder->createNamedParameter(
                        (int) $speakerId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("speakerId{$index}")
                    ),
                    array_keys($speakersIds),
                    $speakersIds
                )
            )
        );
    }
}

/* End of file ep_events_speakers_model.php */
/* Location: /tinymvc/myapp/models/ep_events_speakers_model.php */
