<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\NotFoundException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Ep_Events model.
 */
final class Ep_Events_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'ep_events';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'EP_EVENTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id',
    ];

    /**
     * {@inheritdoc}
     */
    protected array $nullable = [
        'id_speaker',
        'id_category',
        'id_country',
        'id_state',
        'id_city',
        'tags',
        'url',
        'agenda',
        'address',
        'end_date',
        'start_date',
        'why_attend',
        'ticket_price',
        'published_date',
        'recommended_image',
        'highlighted_end_date',
        'promotion_start_date',
        'nr_of_participants',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'promotion_end_date'    => Types::DATETIME_IMMUTABLE,
        'promotion_start_date'  => Types::DATETIME_IMMUTABLE,
        'is_recommended_by_ep'  => Types::INTEGER,
        'nr_of_participants'    => Types::INTEGER,
        'is_attended_by_ep'     => Types::INTEGER,
        'is_upcoming_by_ep'     => Types::INTEGER,
        'ticket_price'          => Types::FLOAT,
        'is_published'          => Types::INTEGER,
        'id_category'           => Types::INTEGER,
        'id_country'            => Types::INTEGER,
        'id_speaker'            => Types::INTEGER,
        'id_state'              => Types::INTEGER,
        'id_type'               => Types::INTEGER,
        'id_city'               => Types::INTEGER,
        'agenda'                => Types::JSON,
        'views'                 => Types::INTEGER,
        'id'                    => Types::INTEGER,
    ];

    /**
     * Retruns event by its ID or throws exception on failure.
     *
     * @param null|int|string $eventId
     *
     * @throws NotFoundException if event is not found
     */
    public function getEvent($eventId): array
    {
        if (
            null === $eventId
            || null === (
                $event = $this->runWithCasts(
                    fn () => $this->findOne($eventId),
                    [
                        'published_date' => Types::DATETIME_IMMUTABLE,
                        'create_date'    => Types::DATETIME_IMMUTABLE,
                        'update_date'    => Types::DATETIME_IMMUTABLE,
                        'start_date'     => Types::DATETIME_IMMUTABLE,
                        'end_date'       => Types::DATETIME_IMMUTABLE,
                    ]
                )
            )
        ) {
            throw new NotFoundException(sprintf('The event with ID "%s" is not found.', $eventId ?? 'null'));
        }

        return $event;
    }

    /**
     * Finds currently promoted event if any.
     */
    public function findCurrentPromotedEvent(): ?array
    {
        return $this->runWithCasts(
            fn () => $this->findOneBy([
                'scopes' => [
                    'promotedEventAtDate' => new DateTimeImmutable(),
                    'published'           => 1,
                ],
            ]),
            [
                'published_date' => Types::DATETIME_IMMUTABLE,
                'create_date'    => Types::DATETIME_IMMUTABLE,
                'update_date'    => Types::DATETIME_IMMUTABLE,
                'start_date'     => Types::DATETIME_IMMUTABLE,
                'end_date'       => Types::DATETIME_IMMUTABLE,
            ]
        );
    }

    /**
     * Updates one event.
     *
     * @param mixed $id
     */
    public function updateOne($id, array $record): bool
    {
        $isUpdated = parent::updateOne($id, $record);

        if ($isUpdated) {
            /** @var Elasticsearch_Ep_Events_Model $elasticEpEventsModel */
            $elasticEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);

            $elasticEpEventsModel->updateEvent((int) $id);
        }

        return $isUpdated;
    }

    /**
     * Inserts one event.
     */
    public function insertOne(array $record): string
    {
        $lastInsertedId = parent::insertOne($record);

        if (!empty($lastInsertedId)) {
            /** @var Elasticsearch_Ep_Events_Model $elasticEpEventsModel */
            $elasticEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);

            $elasticEpEventsModel->index((int) $lastInsertedId);
        }

        return $lastInsertedId;
    }

    /**
     * Scope event by event ID.
     */
    protected function scopeId(QueryBuilder $builder, int $eventId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($eventId, ParameterType::INTEGER, $this->nameScopeParameter('eventId'))
            )
        );
    }

    /**
     * Scope event by event IDs.
     */
    protected function scopeIds(QueryBuilder $builder, array $eventsIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`{$this->getPrimaryKey()}`",
                array_map(
                    fn ($index, $eventId) => $builder->createNamedParameter(
                        (int) $eventId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("eventID{$index}")
                    ),
                    array_keys($eventsIds),
                    $eventsIds
                )
            )
        );
    }

    /**
     * Scope event by event type.
     */
    protected function scopeType(QueryBuilder $builder, int $eventType): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_type',
                $builder->createNamedParameter($eventType, ParameterType::INTEGER, $this->nameScopeParameter('eventType'))
            )
        );
    }

    /**
     * Scope event by event title.
     */
    protected function scopeTitle(QueryBuilder $builder, string $eventTitle): void
    {
        $builder->andWhere(
            $builder->expr()->like(
                'title',
                $builder->createNamedParameter("%{$eventTitle}%", ParameterType::STRING, $this->nameScopeParameter('eventTitle'))
            )
        );
    }

    /**
     * Scope event by event recommended status.
     */
    protected function scopeIsRecommended(QueryBuilder $builder, int $isRecommended): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'is_recommended_by_ep',
                $builder->createNamedParameter($isRecommended, ParameterType::INTEGER, $this->nameScopeParameter('eventIsRecommended'))
            )
        );
    }

    /**
     * Scope event by event upcoming status.
     */
    protected function scopeIsUpcoming(QueryBuilder $builder, int $isUpcoming): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'is_upcoming_by_ep',
                $builder->createNamedParameter($isUpcoming, ParameterType::INTEGER, $this->nameScopeParameter('eventIsUpcoming'))
            )
        );
    }

    /**
     * Scope event by event attended status.
     */
    protected function scopeIsAttended(QueryBuilder $builder, int $isAttended): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'is_attended_by_ep',
                $builder->createNamedParameter($isAttended, ParameterType::INTEGER, $this->nameScopeParameter('eventIsAttended'))
            )
        );
    }

    /**
     * Scope event by event category.
     */
    protected function scopeCategory(QueryBuilder $builder, int $eventCategory): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_category',
                $builder->createNamedParameter($eventCategory, ParameterType::INTEGER, $this->nameScopeParameter('eventCategory'))
            )
        );
    }

    /**
     * Scope event by event partners.
     */
    protected function scopePartners(QueryBuilder $builder, array $eventPartners): void
    {
        /** @var Ep_Events_Partners_Relations_Model $eventPartnersRelationsModel */
        $eventPartnersRelationsModel = model(Ep_Events_Partners_Relations_Model::class);

        $builder->andWhere(
            $builder->expr()->in(
                "`{$eventPartnersRelationsModel->getTable()}`.`id_partner`",
                array_map(
                    fn ($index, $partnerId) => $builder->createNamedParameter(
                        (int) $partnerId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("eventPartner{$index}")
                    ),
                    array_keys($eventPartners),
                    $eventPartners
                )
            )
        );
    }

    /**
     * Scope event by event categories.
     */
    protected function scopeCategories(QueryBuilder $builder, array $eventCategories): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_category`",
                array_map(
                    fn ($index, $categoryId) => $builder->createNamedParameter(
                        (int) $categoryId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("eventCategory{$index}")
                    ),
                    array_keys($eventCategories),
                    $eventCategories
                )
            )
        );
    }

    /**
     * Scope event by event speakers.
     */
    protected function scopeSpeakers(QueryBuilder $builder, array $eventSpeakers): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_speaker`",
                array_map(
                    fn ($index, $speakerId) => $builder->createNamedParameter(
                        (int) $speakerId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("eventSpeaker{$index}")
                    ),
                    array_keys($eventSpeakers),
                    $eventSpeakers
                )
            )
        );
    }

    /**
     * Scope event by event countries.
     */
    protected function scopeCountries(QueryBuilder $builder, array $eventCountries): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_country`",
                array_map(
                    fn ($index, $countryId) => $builder->createNamedParameter(
                        (int) $countryId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("eventCountry{$index}")
                    ),
                    array_keys($eventCountries),
                    $eventCountries
                )
            )
        );
    }

    /**
     * Scope event by event start date.
     */
    protected function scopeStartDateTo(QueryBuilder $builder, string $startDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                'start_date',
                $builder->createNamedParameter($startDate, ParameterType::STRING, $this->nameScopeParameter('eventStartDateTo'))
            )
        );
    }

    /**
     * Scope event by event start date.
     */
    protected function scopeStartDateFrom(QueryBuilder $builder, string $startDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                'start_date',
                $builder->createNamedParameter($startDate, ParameterType::STRING, $this->nameScopeParameter('eventStartDateFrom'))
            )
        );
    }

    /**
     * Scope event by event end date.
     */
    protected function scopeEndDateTo(QueryBuilder $builder, string $endDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                'end_date',
                $builder->createNamedParameter($endDate, ParameterType::STRING, $this->nameScopeParameter('eventEndDateTo'))
            )
        );
    }

    /**
     * Scope event by event end date.
     */
    protected function scopeEndDateFrom(QueryBuilder $builder, string $endDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                'end_date',
                $builder->createNamedParameter($endDate, ParameterType::STRING, $this->nameScopeParameter('eventEndDateFrom'))
            )
        );
    }

    /**
     * Scope a query for promoted events.
     */
    protected function scopeIsPromoted(QueryBuilder $builder, bool $isPromoted = true)
    {
        $builder->andWhere(
            $isPromoted ? $builder->expr()->isNotNull('promotion_start_date') : $builder->expr()->isNull('promotion_start_date'),
        );
    }

    /**
     * Scope a query for promoted events that ends after specific date.
     */
    protected function scopePromotedEventsForInterval(QueryBuilder $builder, DateTimeInterface $intervalStartDate, ?DateTimeInterface $intervalEndDate = null)
    {
        // Create parameter for beginning of interval
        $intervalStartDateParameter = $builder->createNamedParameter(
            // Transform datetime object for beginning of interval into format accepted by query
            $this->castAttributeToDatabaseValue('promotion_start_date', $intervalStartDate),
            ParameterType::STRING,
            $this->nameScopeParameter('intervalStart', true)
        );
        $builder->andWhere(
            // Event starts after current date
            $builder->expr()->gte('start_date', $intervalStartDateParameter),
            // Event is promoted
            $builder->expr()->isNotNull('promotion_start_date'),
        );

        // If interval has ending, that we need to add a specific conditions for that
        if (null !== $intervalEndDate) {
            // Create parameter for the end of interval
            $intervalEndDateParameter = $builder->createNamedParameter(
                // Transform datetime object for end of interval into format accepted by query
                $this->castAttributeToDatabaseValue('promotion_end_date', $intervalEndDate),
                ParameterType::STRING,
                $this->nameScopeParameter('intervalEnd', true)
            );
            $builder->andWhere(
                $builder->expr()->or(
                    // The promotion is inside interval
                    $builder->expr()->lte('promotion_end_date', $intervalEndDateParameter),
                    // Or it is already ongoing
                    $builder->expr()->and(
                        $builder->expr()->gte($intervalEndDateParameter, 'promotion_start_date'),
                        $builder->expr()->lte($intervalEndDateParameter, 'promotion_end_date'),
                    )
                )
            );
        }
    }

    /**
     * Scope a query for promoted event at the provided date.
     */
    protected function scopePromotedEventAtDate(QueryBuilder $builder, DateTimeInterface $targetDate)
    {
        // Create parameter for date
        $targetDateParameter = $builder->createNamedParameter(
            // Transform datetime object for beginning of interval into format accepted by query
            $this->castAttributeToDatabaseValue('promotion_start_date', $targetDate),
            ParameterType::STRING,
            $this->nameScopeParameter('targetDate', true)
        );
        $builder->andWhere(
            // Event is promoted
            $builder->expr()->isNotNull('promotion_start_date'),
            $builder->expr()->and(
                $builder->expr()->gte($targetDateParameter, 'promotion_start_date'),
                $builder->expr()->lte($targetDateParameter, 'promotion_end_date'),
            )
        );
    }

    /**
     * Scope event by published status.
     */
    protected function scopePublished(QueryBuilder $builder, int $isPublished): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'is_published',
                $builder->createNamedParameter($isPublished, ParameterType::INTEGER, $this->nameScopeParameter('eventPublished'))
            )
        );
    }

    /**
     * Scope event highlighted status.
     */
    protected function scopeIsHighlighted(QueryBuilder $builder): void
    {
        $builder->andWhere($builder->expr()->isNotNull('highlighted_end_date'));
    }

    /**
     * Scope event by event highlighted date.
     */
    protected function scopeHighlightedDateTo(QueryBuilder $builder, string $date): void
    {
        $builder->andWhere(
            $builder->expr()->lt(
                'highlighted_end_date',
                $builder->createNamedParameter((new \DateTime())->format('Y-m-d H:i:s'), ParameterType::STRING, $this->nameScopeParameter('eventHighlightedDateTo'))
            )
        );
    }

    /**
     * Scope for join with event partners relations.
     */
    protected function bindEventPartnersRelations(QueryBuilder $builder): void
    {
        /** @var Ep_Events_Partners_Relations_Model $eventPartnersRelationsModel */
        $eventPartnersRelationsModel = model(Ep_Events_Partners_Relations_Model::class);
        $builder
            ->leftJoin(
                $this->getTable(),
                $eventPartnersRelationsModel->getTable(),
                $eventPartnersRelationsModel->getTable(),
                "`{$eventPartnersRelationsModel->getTable()}`.`id_event` = `{$this->getTable()}`.`{$this->getPrimaryKey()}`"
            )
        ;
    }

    /**
     * Scope for join with speakers.
     */
    protected function bindSpeakers(QueryBuilder $builder): void
    {
        /** @var Ep_Events_Speakers_Model $eventSpeakersModel */
        $eventSpeakersModel = model(Ep_Events_Speakers_Model::class);
        $builder
            ->leftJoin(
                $this->getTable(),
                $eventSpeakersModel->getTable(),
                $eventSpeakersModel->getTable(),
                "`{$eventSpeakersModel->getTable()}`.`{$eventSpeakersModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_speaker`"
            )
        ;
    }

    /**
     * Scope for join with types.
     */
    protected function bindTypes(QueryBuilder $builder): void
    {
        /** @var Ep_Events_Types_Model $eventTypesModel */
        $eventTypesModel = model(Ep_Events_Types_Model::class);

        $builder
            ->leftJoin(
                $this->getTable(),
                $eventTypesModel->getTable(),
                $eventTypesModel->getTable(),
                "`{$eventTypesModel->getTable()}`.`{$eventTypesModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_type`"
            )
        ;
    }

    /**
     * Scope for join with categories.
     */
    protected function bindCategories(QueryBuilder $builder): void
    {
        /** @var Ep_Events_Categories_Model $eventCategoriesModel */
        $eventCategoriesModel = model(Ep_Events_Categories_Model::class);

        $builder
            ->leftJoin(
                $this->getTable(),
                $eventCategoriesModel->getTable(),
                $eventCategoriesModel->getTable(),
                "`{$eventCategoriesModel->getTable()}`.`{$eventCategoriesModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_category`"
            )
        ;
    }

    /**
     * Resolves static relationships with event type.
     */
    protected function type(): RelationInterface
    {
        /** @var Ep_Events_Types_Model $eventTypesModel */
        $eventTypesModel = model(Ep_Events_Types_Model::class);

        $typesTable = $eventTypesModel->getTable();
        $relation = $this->belongsTo(Ep_Events_Types_Model::class, 'id_type');
        $relation->disableNativeCast();
        $builder = $relation->getQuery();

        $builder->select("`{$typesTable}`.*");

        return $relation;
    }

    /**
     * Resolves static relationships with event speaker.
     */
    protected function speaker(): RelationInterface
    {
        /** @var Ep_Events_Speakers_Model $eventSpeakersModel */
        $eventSpeakersModel = model(Ep_Events_Speakers_Model::class);

        $speakersTable = $eventSpeakersModel->getTable();
        $relation = $this->belongsTo(Ep_Events_Speakers_Model::class, 'id_speaker');
        $relation->disableNativeCast();
        $builder = $relation->getQuery();

        $builder->select("`{$speakersTable}`.*");

        return $relation;
    }

    /**
     * Resolves static relationships with event partners.
     */
    protected function partners(): RelationInterface
    {
        $relation = $this->hasMany(Ep_Events_Partners_Relations_Model::class, 'id_event', 'id');
        $relation->disableNativeCast();
        $eventsPartnersRelationsTable = $relation->getRelated()->getTable();

        $builder = $relation->getQuery();

        /** @var Ep_Events_Partners_Model $eventsPartnersModel */
        $eventsPartnersModel = model(Ep_Events_Partners_Model::class);
        $eventsPartnersTable = $eventsPartnersModel->getTable();

        $builder
            ->select(
                "`{$eventsPartnersRelationsTable}`.`id_event`",
                "`{$eventsPartnersTable}`.*",
            )
            ->leftJoin($eventsPartnersRelationsTable, $eventsPartnersTable, null, "{$eventsPartnersTable}.{$eventsPartnersModel->getPrimaryKey()} = {$eventsPartnersRelationsTable}.id_partner")
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with event gallery.
     */
    protected function gallery(): RelationInterface
    {
        return $this->hasMany(Ep_Events_Images_Model::class, 'id_event')->disableNativeCast();
    }

    /**
     * Resolves static relationships with event category.
     */
    protected function category(): RelationInterface
    {
        $relation = $this->belongsTo(Ep_Events_Categories_Model::class, 'id_category');
        $relation->disableNativeCast();
        $builder = $relation->getQuery();

        $categoriesTable = $relation->getRelated()->getTable();

        $builder
            ->select(
                "`{$categoriesTable}`.`id`",
                "`{$categoriesTable}`.`name`",
                "`{$categoriesTable}`.`url`",
                "`{$categoriesTable}`.`special_link`",
            )
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with event country.
     */
    protected function country(): RelationInterface
    {
        /** @var Country_Model $countriesModel */
        $countriesModel = model(Country_Model::class);

        $countriesTable = $countriesModel->get_countries_table();

        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $countriesTable, 'id'),
            'id_country'
        );
        $relation->disableNativeCast();
        $builder = $relation->getQuery();

        $builder
            ->select(
                "`{$countriesTable}`.`id`",
                "`{$countriesTable}`.`country` AS 'name'",
            )
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with event state.
     */
    protected function state(): RelationInterface
    {
        /** @var Country_Model $countriesModel */
        $countriesModel = model(Country_Model::class);

        $satesTable = $countriesModel->get_regions_table();

        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $satesTable, 'id'),
            'id_state'
        );
        $relation->disableNativeCast();
        $builder = $relation->getQuery();

        $builder
            ->select(
                "`{$satesTable}`.`id`",
                "`{$satesTable}`.`state` AS 'name'",
            )
        ;

        return $relation;
    }

    /**
     * Resolves static relationships with event city.
     */
    protected function city(): RelationInterface
    {
        /** @var Country_Model $countriesModel */
        $countriesModel = model(Country_Model::class);

        $citiesTable = $countriesModel->get_cities_table();

        $relation = $this->belongsTo(
            new PortableModel($this->getHandler(), $citiesTable, 'id'),
            'id_city'
        );
        $relation->disableNativeCast();
        $builder = $relation->getQuery();

        $builder
            ->select(
                "`{$citiesTable}`.`id`",
                "`{$citiesTable}`.`city` AS 'name'",
            )
        ;

        return $relation;
    }
}

// End of file ep_events_model.php
// Location: /tinymvc/myapp/models/ep_events_model.php
