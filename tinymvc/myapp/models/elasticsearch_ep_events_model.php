<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Traits\Elasticsearch\AutocompleteAnalyzerTrait;

/**
 * Elasticsearch_Ep_Events model
 */
final class Elasticsearch_Ep_Events_Model extends Model
{
    use AutocompleteAnalyzerTrait;
    /**
     * {@inheritdoc}
     */
    private $type = 'ep_events';

    /**
     * {@inheritdoc}
     */
    protected string $table = "ep_events";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "EP_EVENTS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * {@inheritdoc}
     */
    public $records = null;

    /**
     * {@inheritdoc}
     */
    public $recordsCount = null;

    /**
     * {@inheritdoc}
     */
    public $aggregates = null;

    /**
     * @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary
     */
    protected $elasticsearchLibrary;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    /**
     * The method created for indexing ep_events data in elasticsearch
     *
     * @param int|null $eventId
     *
     * @return void
     */
    public function index(?int $eventId = null)
    {
        $events = $this->getEventsFromMySql($eventId);
        $chunckedEvents = array_chunk($events, 1000);

        foreach ($chunckedEvents as $chunk) {
            $bulkQueries = [];

            foreach ($chunk as $event) {
                $tokens = $this->analyzeAutocompleteText($event['title']);
                foreach ($tokens as $token) {
                    $event['suggest_autocomplete'][] = [
                        'input'  => $token['token'],
                        'weight' => 30,
                    ];
                }

                $event['suggest_autocomplete'][] = [
                    'input'  => strtolower($event['title']),
                    'weight' => 35,
                ];

                if (!empty($event['category'])) {
                    $event['suggest_autocomplete'][] = [
                        'input'  => strtolower($event['category']['name']),
                        'weight' => 20,
                    ];
                }

                array_push(
                    $bulkQueries,
                    ...$this->elasticsearchLibrary->bulk_index_query(
                        $this->type,
                        $event['id'],
                        $event
                    )
                );
            }

            $this->elasticsearchLibrary->bulk($bulkQueries);
        }
    }

    /**
     * The method created for getting events
     *
     * @param array $conditions
     *
     * @return array - Events
     */
    public function getEvents(array $conditions = []): array
    {
        $must = $should = $filterMust = $filterMustNot = [];

        $page = $conditions['page'] ?? 1;
        $perPage = $conditions['perPage'] ?? 20;

        if (isset($conditions['id'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('id', $conditions['id']);
        }

        if (isset($conditions['notIdEvent'])) {
            $filterMustNot[] = $this->elasticsearchLibrary->get_term('id', $conditions['notIdEvent']);
        }

        if (isset($conditions['notIdEvents'])) {
            $filterMustNot[] = $this->elasticsearchLibrary->get_terms('id', $conditions['notIdEvents']);
        }

        if (isset($conditions['speakerId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('id_speaker', $conditions['speakerId']);
        }

        if (isset($conditions['typeId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('id_type', $conditions['typeId']);
        }

        if (isset($conditions['typeSlug'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_nested(
                'type',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('type.slug', $conditions['typeSlug'])
                    ]
                ]
            );
        }

        if (isset($conditions['categoryId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('id_category', $conditions['categoryId']);
        }

        if (isset($conditions['categorySlug'])) {
            $filterMust[] = [
                "nested" => [
                    "path" => 'category',
                    "query" => [
                        'bool' => [
                            'should' => [
                                $this->elasticsearchLibrary->get_term('category.url', $conditions['categorySlug']),
                                $this->elasticsearchLibrary->get_term('category.special_link', $conditions['categorySlug'])
                            ]
                        ]
                    ]
                ]
            ];
        }

        if (isset($conditions['countryId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('id_country', $conditions['countryId']);
        }

        if (isset($conditions['countrySlug'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_nested(
                'country',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('country.slug', $conditions['countrySlug'])
                    ]
                ]
            );
        }

        if (isset($conditions['stateId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('id_state', $conditions['stateId']);
        }

        if (isset($conditions['cityId'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('id_city', $conditions['cityId']);
        }

        if (isset($conditions['recommendedByEp'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('is_recommended_by_ep', $conditions['recommendedByEp']);
        }

        if (isset($conditions['attendedByEp'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('is_attended_by_ep', $conditions['attendedByEp']);
        }

        if (isset($conditions['upcomingByEp'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_match('is_upcoming_by_ep', $conditions['upcomingByEp']);
        }

        if (isset($conditions['hasRecommendedLabel'])) {
            $filterMust[] = [
                'bool' => [
                    'should' => [
                        $this->elasticsearchLibrary->get_term('is_recommended_by_ep', 1),
                        $this->elasticsearchLibrary->get_term('is_upcoming_by_ep', 1),
                        $this->elasticsearchLibrary->get_term('is_attended_by_ep', 1),
                    ]
                ]
            ];
        }

        if (isset($conditions['startFromDateTime'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_range('start_date', ['from' => $conditions['startFromDateTime'], 'dateFormat' => 'yyyy-MM-dd HH:mm']);
        }

        if (isset($conditions['startFromDate'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_range('start_date', ['from' => $conditions['startFromDate'], 'dateFormat' => 'yyyy-MM-dd']);
        }

        if (isset($conditions['startToDateTime'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_range('start_date', ['to' => $conditions['startToDateTime'], 'dateFormat' => 'yyyy-MM-dd HH:mm']);
        }

        if (isset($conditions['startToDate'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_range('start_date', ['to' => $conditions['startToDate'], 'dateFormat' => 'yyyy-MM-dd']);
        }

        if (isset($conditions['endFromDateTime'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_range('end_date', ['from' => $conditions['endFromDateTime'], 'dateFormat' => 'yyyy-MM-dd HH:mm']);
        }

        if (isset($conditions['endFromDate'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_range('end_date', ['from' => $conditions['endFromDate'], 'dateFormat' => 'yyyy-MM-dd']);
        }

        if (isset($conditions['endToDateTime'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_range('end_date', ['to' => $conditions['endToDateTime'], 'dateFormat' => 'yyyy-MM-dd HH:mm']);
        }

        if (isset($conditions['endToDate'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_range('end_date', ['to' => $conditions['endToDate'], 'dateFormat' => 'yyyy-MM-dd']);
        }

        if (isset($conditions['hasPartner'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_nested(
                'partners',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('partners.id', $conditions['hasPartner'])
                    ]
                ]
            );
        }

        switch ($conditions['sortBy'] ?? null) {
            case 'oldest':
                $sortBy =['start_date' => 'asc'];

                break;
            case 'newest':
                $sortBy = ['start_date' => 'desc'];

                break;
            case 'less_viewed':
                $sortBy = ['views' => 'asc'];

                break;
            case 'most_viewed':
                $sortBy = ['views' => 'desc'];

                break;
            case 'random':
                $sortBy = [
                    '_script' => [
                        'type'      => 'number',
                        'script'    => 'Math.random()',
                        'order'     => 'asc'
                    ]
                ];

                break;
        }

        if (isset($conditions['search'])) {
            $filterMust[] = [
                'bool' => [
                    'should' => [
                        $this->elasticsearchLibrary->get_match('title', $conditions['search']),
                        $this->elasticsearchLibrary->get_nested(
                            'category',
                            [
                                'bool'  => [
                                    'should'  => $this->elasticsearchLibrary->get_match('category.name', $conditions['search'])
                                ]
                            ]
                        ),
                        $this->elasticsearchLibrary->get_match('tags', $conditions['search']),
                        $this->elasticsearchLibrary->get_match('short_description', $conditions['search']),
                    ],
                ],
            ];
        }

        if (isset($conditions['aggregateByCategories'])) {
            $aggregates['categories'] = [
                'terms' => ['field' => 'id_category']
            ];
        }

        $elasticQuery =  [
            'query' => [
                'bool' => [
                    'must' => $must,
                    'should' => $should,
                    'filter' => [
                        'bool' => [
                            'must' => $filterMust,
                            'must_not' => $filterMustNot
                        ]
                    ]
                ]
            ],
            'sort'  => $sortBy ?? ['_score'],
            'size'  => $perPage,
            'from'  => $perPage * ($page - 1),
        ];

        if (isset($aggregates)) {
            $elasticQuery['aggs'] = $aggregates;
        }

        $melasticResults = $this->elasticsearchLibrary->mget($this->type, [$elasticQuery]);
        $elasticResults = $melasticResults['responses'][0];

        if (isset($elasticResults['hits']['hits'])) {
            $this->records = array_map(fn ($ar) => $ar['_source'], $elasticResults['hits']['hits']);
            $this->recordsCount = $elasticResults['hits']['total']['value'];
        }

        if (isset($conditions['aggregateByCategories']) && !empty($elasticResults['aggregations']['categories']['buckets'])) {
            foreach ($elasticResults['aggregations']['categories']['buckets'] as $aggregationCategory) {
                $this->aggregates['categories'][$aggregationCategory['key']] = [
                    'categoryId'    => $aggregationCategory['key'],
                    'eventsCount'   => $aggregationCategory['doc_count'],
                ];
            }
        }

        return (array) $this->records;
    }

    public function getHighlightedEvent(array $excludedEventsIds = []) {
        $elasticQuery =  [
            'query' => [
                'bool' => [
                    'filter'    => [
                        'bool' => array_filter([
                            'must' => [
                                [
                                    'range' => [
                                        'start_date' => [
                                            'gte'   => (new \DateTime())->format('Y-m-d H:i:s')
                                        ]
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            $this->elasticsearchLibrary->get_term('is_recommended_by_ep', 1),
                                            $this->elasticsearchLibrary->get_term('is_upcoming_by_ep', 1),
                                            $this->elasticsearchLibrary->get_term('is_attended_by_ep', 1),
                                        ]
                                    ]
                                ],
                            ],
                            'must_not' => empty($excludedEventsIds) ? null : $this->elasticsearchLibrary->get_terms('id', $excludedEventsIds),
                        ]),
                    ]
                ]
            ],
            'sort'  => [
                ['highlighted_end_date' => 'desc'],
                [
                    '_script' => [
                        'type'      => 'number',
                        'script'    => 'Math.random()',
                        'order'     => 'asc'
                    ]
                ]
            ],
            'size'  => 1,
            'from'  => 0
        ];

        $melasticResults = $this->elasticsearchLibrary->mget($this->type, [$elasticQuery]);
        $elasticResults = $melasticResults['responses'][0];

        if (!empty($elasticResults['hits']['hits'])) {
            $response = array_shift($elasticResults['hits']['hits']);

            return $response['_source'];
        }

        return [];
    }

    /**
     * The method created for deleting event from elasticsearch
     *
     * @param int $eventId
     *
     * @return void
     */
    public function deleteEvent(int $eventId)
    {
        $this->elasticsearchLibrary->delete($this->type, $eventId);
    }

    /**
     * The method created for update event in elasticsearch
     *
     * @param int $eventId
     *
     * @return void
     */
    public function updateEvent(int $eventId)
    {
        $events = $this->getEventsFromMySql($eventId);
        $event = array_shift($events);

        if (empty($event)) {
            return $this->deleteEvent($eventId);
        }

        $tokens = $this->analyzeAutocompleteText($event['title']);
        foreach ($tokens as $token) {
            $event['suggest_autocomplete'][] = [
                'input'  => $token['token'],
                'weight' => 30,
            ];
        }

        $event['suggest_autocomplete'][] = [
            'input'  => strtolower($event['title']),
            'weight' => 35,
        ];

        if (!empty($event['category'])) {
            $event['suggest_autocomplete'][] = [
                'input'  => strtolower($event['category']['name']),
                'weight' => 20,
            ];
        }

        $elasticResult = $this->elasticsearchLibrary->get_by_id($this->type, $eventId);
        if (empty($elasticResult['found'])) {
            return $this->elasticsearchLibrary->index($this->type, $eventId, $event);
        }

        $this->elasticsearchLibrary->update($this->type, $eventId, $event);
    }

    /**
     * Method created to update events speaker
     *
     * @param int $speakerId
     * @param array $speaker
     *
     * @return bool
     */
    public function updateEventsSpeaker(int $speakerId, array $speaker): bool
    {
        $result = $this->elasticsearchLibrary->update_by_query(
            [
                'bool'  => [
                    'must'  => $this->elasticsearchLibrary->get_term('id_speaker', $speakerId)
                ]
            ],
            <<<SCRIPT
                ctx._source.speaker.name = params.speakerName;
                ctx._source.speaker.photo = params.speakerPhoto;
                ctx._source.speaker.position = params.speakerPosition;
            SCRIPT,
            $this->type,
            [
                'speakerPosition'   => $speaker['position'],
                'speakerPhoto'      => $speaker['photo'],
                'speakerName'       => $speaker['name'],
            ]
        );

        return empty($result['failures']);
    }

    /**
     * Method created to update events partner
     *
     * @param int $partnerId
     * @param array $partner
     *
     * @return bool
     */
    public function updateEventsPartner(int $partnerId, array $partner): bool
    {
        $result = $this->elasticsearchLibrary->update_by_query(
            $this->elasticsearchLibrary->get_nested(
                'partners',
                [
                    'bool'  => [
                        'must'  => $this->elasticsearchLibrary->get_term('partners.id', $partnerId)
                    ]
                ]
            ),
            <<<SCRIPT
                for(int i = 0; i <= ctx._source['partners'].size() - 1; i++){
                    if (ctx._source.partners[i].id == params.partnerId) {
                        ctx._source.partners[i].name = params.partnerName;
                        ctx._source.partners[i].image = params.partnerImage;
                    }
                }
            SCRIPT,
            $this->type,
            [
                'partnerImage'  => $partner['image'],
                'partnerName'   => $partner['name'],
                'partnerId'     => $partnerId,
            ],
        );

        return empty($result['failures']);
    }

    /**
     * Method created for increment event views
     *
     * @param int $eventId
     *
     * @return void
     */
    public function incrementViews(int $eventId)
    {
        $this->elasticsearchLibrary->update_by_query(
            [
                'term' => [
                    'id' => $eventId,
                ],
            ],
            'ctx._source.views += params.plusViews',
            $this->type,
            [
                'plusViews' => 1
            ]
        );
    }

    private function getEventsFromMySql(?int $eventId = null): array
    {
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);

        $conditions = array_filter([
            'id' => $eventId ?: null,
            'published' => 1,
        ]);

        $with = ['type', 'country', 'state', 'city', 'category', 'speaker', 'partners', 'gallery'];

        $events = $eventsModel->findAllBy(compact('with', 'conditions'));

        if (!empty($events)) {
            foreach ($events as &$event) {
                $event['tags'] = empty($event['tags'])  ? null : array_filter(explode(';', $event['tags']), 'trim');
                $event['gallery'] = empty($event['gallery']) ? null : $event['gallery']->toArray();
                $event['partners'] = array_map(
                    fn ($partner) => [
                        'image' => $partner['image'],
                        'name'  => $partner['name'],
                        'id'    => (int) $partner['id'],
                    ],
                    empty($event['partners']) ? [] : $event['partners']->toArray()
                );

                if (!empty($event['country'])) {
                    $event['country']['slug'] = strForURL($event['country']['name'] . ' ' . $event['country']['id']);
                }
            }
        }

        return $events ?: [];
    }

    /**
     * Completion suggester
     */
    public function getSuggestions(string $text): array
    {
        /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
        $elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
        $melasticResults = $elasticsearchLibrary->mget('ep_events', [
            [
                '_source' => [
                    'end_date'
                ],
                'suggest' => [
                    'events-suggest' => [
                        'text'       => $text,
                        'completion' => [
                            'field'           => 'suggest_autocomplete',
                            'size'            => 20,
                            // 'skip_duplicates' => true,
                        ],
                    ],
                ],
            ],
        ]);

        $result = $melasticResults['responses'][0]['suggest']['events-suggest'][0];
        $result['options'] = array_slice(
            $melasticResults['responses'][0]['suggest']['events-suggest'][0]['options'],
            0,
            config('events_suggestion_search_size', 10)
        );

        return $result;
    }
}

/* End of file elasticsearch_ep_events_model.php */
/* Location: /tinymvc/myapp/models/elasticsearch_ep_events_model.php */
