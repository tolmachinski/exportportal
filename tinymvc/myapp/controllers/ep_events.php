<?php

use App\Common\Contracts\Calendar\EventType;
use App\Common\Contracts\CommentType;
use App\Common\Exceptions\NotFoundException;
use App\Common\Traits\PromotedEventProviderTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\ValidationException;
use App\Email\EmailFriendAboutEpEvent;
use App\Validators\EmailThisValidator;
use App\Filesystem\EpEventFilePathGenerator;
use App\Filesystem\EpEventGalleryImageThumb;
use App\Filesystem\EpEventMainImageThumb;
use App\Filesystem\EpEventPartnersFilePathGenerator;
use App\Filesystem\EpEventSpeakersFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use App\Services\CalendarEpEventsService;
use App\Validators\EpEventAddressValidator;
use App\Validators\EventAgendaValidator;
use App\Validators\EventPromotionValidator;
use App\Validators\OfflineEventValidator;
use App\Validators\OnlineEventValidator;
use App\Validators\ShareThisValidator;
use App\Validators\WebinarValidator;
use Doctrine\DBAL\Types\Types;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

use const App\Common\PUBLIC_DATETIME_FORMAT;

/**
 * Controller Ep_events
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */

class Ep_events_Controller extends TinyMVC_Controller
{
    use PromotedEventProviderTrait;

    public const OFFLINE_EVENT_TYPE_ID = 1;
    public const ONLINE_EVENT_TYPE_ID = 2;
    public const WEBINAR_TYPE_ID = 3;

    /**
     * The list of bread crumbs.
     */
    private array $breadcrumbs = [];

    private FilesystemOperator $storage;
    private FilesystemOperator $tempStorage;
    private PathPrefixer $prefixer;
    private PathPrefixer $tempPrefixer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
        $this->tempStorage = $storageProvider->storage('temp.storage');

        $this->prefixer = $storageProvider->prefixer('public.storage');
        $this->tempPrefixer = $storageProvider->prefixer('temp.storage');
    }

    public function index(): void
    {
        $eventsUriComponents = tmvc::instance()->site_urls['ep_events/index']['replace_uri_components'];
        $uri = uri()->uri_to_assoc();

        unset($uri['ep_events']);
        checkURI($uri, array_values((array) $eventsUriComponents));
        checkIsValidPage($uri['page']);

        $breadcrumbLink = __SITE_URL . 'ep_events';
        $this->breadcrumbs[] = [
            'link'  => $breadcrumbLink,
            'title' => translate('ep_events_header_title'),
        ];

        $linksMap = [
            $eventsUriComponents['country'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['country'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['category'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['category'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['type'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['type'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['time'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['time'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['label'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['label'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['page'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['page']],
            ],
            'sort' => [
                'type' => 'get',
                'deny' => ['sort', $eventsUriComponents['page']],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => ['keywords', $eventsUriComponents['page']],
            ],
        ];

        $linksTpl = uri()->make_templates($linksMap, $uri);
        $linksTplWithout = uri()->make_templates($linksMap, $uri, true);

        $perPage = (int) config('ep_events_per_page', 10);
        $page = (int) ($uri[$eventsUriComponents['page']] ?? 1);

        /** @var Elasticsearch_Ep_Events_Model $epEventsModel */
        $epEventsModel = model(Elasticsearch_Ep_Events_Model::class);

        $upcomingEventsParams = [
            'aggregateByCategories' => true,
            'perPage'               => $perPage,
            'page'                  => $page,
        ];

        $appliedFilters = [];

        /** @var Ep_Events_Categories_Model $epEventsCategoriesModel */
        $epEventsCategoriesModel = model(Ep_Events_Categories_Model::class);

        //region of processing filter by category
        if (!empty($categorySlug = $uri[$eventsUriComponents['category']] ?? null)) {
            if (empty($category = $epEventsCategoriesModel->findOneBy([
                'conditions' => [
                    'urlOrSpecialLink' => $categorySlug,
                ],
            ]))) {
                show_404();
            }

            // if the special link is not empty, then access should be done only by it
            if (($category['special_link'] ?? $categorySlug) !== $categorySlug) {
                headerRedirect(replace_dynamic_uri($category['special_link'], 'ep_events/' . $linksTpl[$eventsUriComponents['category']]));
            }

            $upcomingEventsParams['categorySlug'] = $categorySlug;
            $appliedFilters['category'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/' . $linksTplWithout['category'], '/'),
                'displayedValue'    => cleanOutput($category['name']),
                'value'             => $categorySlug,
                'name'              => translate('ep_events_detail_category_label'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['category'] . '/' . $categorySlug;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => cleanOutput($category['name']),
            ];

            $metaParams['[CATEGORY]'] = cleanOutput($category['name']);
        }
        //endregion of processing filter by a category

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);

        //region of processing filter by country
        if (!empty($countrySlug = $uri[$eventsUriComponents['country']] ?? null)) {
            if (
                empty($country = $countryModel->get_country(id_from_link($countrySlug)))
                || strForURL($country['country'] . ' ' . $country['id']) !== $countrySlug
            ) {
                show_404();
            }

            $upcomingEventsParams['countrySlug'] = $countrySlug;
            $appliedFilters['country'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/' . $linksTplWithout['country'], '/'),
                'displayedValue'    => cleanOutput($country['country']),
                'value'             => $countrySlug,
                'name'              => translate('ep_events_country_label'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['country'] . '/' . $countrySlug;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => cleanOutput($country['country']),
            ];

            $metaParams['[COUNTRY]'] = cleanOutput($country['country']);
        }
        //endregion of processing filter by a country

        /** @var Ep_Events_Types_Model $epEventTypesModel */
        $epEventTypesModel = model(Ep_Events_Types_Model::class);

        //region of processing filter by type
        $metaParams['[TYPE]'] = 'Events & Webinars';

        if (!empty($typeSlug = $uri[$eventsUriComponents['type']] ?? null)) {
            if (empty($type = $epEventTypesModel->findOneBy(['conditions' => ['slug' => $typeSlug]]))) {
                show_404();
            }

            $upcomingEventsParams['typeSlug'] = $typeSlug;
            $appliedFilters['type'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/' . $linksTplWithout['type'], '/'),
                'displayedValue'    => cleanOutput($type['title']),
                'value'             => $typeSlug,
                'name'              => translate('ep_events_detail_type_label'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['type'] . '/' . $typeSlug;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => cleanOutput($type['title']),
            ];

            switch ($type['alias']) {
                case 'offline':
                    $metaParams['[TYPE]'] = 'Offline Events';

                    break;
                case 'online':
                    $metaParams['[TYPE]'] = 'Virtual Events';

                    break;
                case 'webinar':
                    $metaParams['[TYPE]'] = 'Webinars';

                    break;
            }
        }
        //endregion of processing filter by type

        //region of processing filter by time
        $metaParams['[TIMING]'] = 'International Trade';

        $now = new DateTime();
        $upcomingEventsParams['endFromDateTime'] = $now->format('Y-m-d H:i');

        if (!empty($time = $uri[$eventsUriComponents['time']] ?? null)) {
            if (!in_array($time, ['upcoming', 'active'])) {
                show_404();
            }

            unset($upcomingEventsParams['endFromDateTime']);

            $breadcrumbTitle = '';
            switch ($time) {
                case 'upcoming':
                    $now->modify('+1 minutes');
                    $upcomingEventsParams['startFromDateTime'] = $now->format('Y-m-d H:i');
                    $breadcrumbTitle = translate('ep_events_upcoming_heading');

                    break;
                case 'active':
                    $upcomingEventsParams['startToDateTime'] = $now->format('Y-m-d H:i');
                    $upcomingEventsParams['endFromDateTime'] = $now->format('Y-m-d H:i');
                    $breadcrumbTitle = translate('ep_events_active');

                    break;
                case 'past': //on this page, it filter will not be used
                    $upcomingEventsParams['endToDateTime'] = $now->format('Y-m-d H:i');
                    $breadcrumbTitle = translate('ep_events_past_heading');

                    break;
            }

            $appliedFilters['time'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/' . $linksTplWithout['time'], '/'),
                'displayedValue'    => $breadcrumbTitle,
                'value'             => $time,
                'name'              => translate('ep_events_time_filter_title'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['time'] . '/' . $time;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => $breadcrumbTitle,
            ];

            $metaParams['[TIMING]'] = ucfirst($time);
        }
        //endregion of processing filter by time

        //region of processing filter by label
        if (!empty($label = $uri[$eventsUriComponents['label']] ?? null)) {
            if (!in_array($label, ['upcoming', 'recommended', 'attended'])) {
                show_404();
            }

            switch ($label) {
                case 'upcoming':
                    $upcomingEventsParams['upcomingByEp'] = 1;
                    $breadcrumbTitle = translate('ep_events_upcoming_by_ep');

                    break;
                case 'recommended':
                    $upcomingEventsParams['recommendedByEp'] = 1;
                    $breadcrumbTitle = translate('ep_events_recommended_by_ep');

                    break;
                case 'attended':
                    $upcomingEventsParams['attendedByEp'] = 1;
                    $breadcrumbTitle = translate('ep_events_attended_by_ep');

                    break;
            }

            $appliedFilters['label'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/' . $linksTplWithout['label'], '/'),
                'displayedValue'    => $breadcrumbTitle,
                'value'             => $label,
                'name'              => translate('ep_events_detail_label'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['label'] . '/' . $label;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => $breadcrumbTitle,
            ];
        }
        //endregion of processing filter by label

        //region of processing filter by keywords
        if (!empty($keywords = request()->query->get('keywords'))) {
            $upcomingEventsParams['search'] = trim(decodeUrlString($keywords));
            $appliedFilters['keywords'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/' . $linksTplWithout['keywords'], '/'),
                'displayedValue'    => cleanOutput(decodeUrlString($keywords)),
                'value'             => decodeUrlString($keywords),
                'name'              => translate('ep_events_keywords_title'),
            ];
        }
        //endregion of processing filter by keywords

        //region of sorting
        if (!empty($sort = request()->query->get('sort'))) {
            if (!in_array($sort, ['date', 'most_viewed'])) {
                show_404();
            }

            $sorting = [
                'displayedValue'    => 'date' === $sort ? translate('sort_by_date_txt') : translate('sort_by_most_viewed_txt'),
                'value'             => $sort,
            ];

            $upcomingEventsParams['sortBy'] = 'date' == $sort ? 'oldest' : 'most_viewed';
        }

        $sorting['default'] = isset($appliedFilters['keywords']) ? 'best_match' : 'date';

        if (empty($keywords) && empty($sort)) {
            $upcomingEventsParams['sortBy'] = 'oldest';
        }
        //endregion of sorting

        //region of getting upcoming events
        $epEventsModel->getEvents($upcomingEventsParams);

        $upcomingEvents = $epEventsModel->records;
        $upcomingEvents = array_map(function ($event) {
            $event['thumbs']['small'] = $this->storage->url(
                EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'] ?: '', EpEventMainImageThumb::SMALL())
            );

            $event['thumbs']['medium'] = $this->storage->url(
                EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'] ?: '', EpEventMainImageThumb::MEDIUM())
            );

            return $event;
        }, $upcomingEvents);

        $upcomingEventsCount = (int) $epEventsModel->recordsCount;
        //endregion of getting upcoming events

        //region of processing aggregate records by category
        // if a filter was applied by category, then we repeat the request without it for aggregate category counters
        if (isset($appliedFilters['category'], $upcomingEventsParams['aggregateByCategories'])) {
            unset($upcomingEventsParams['categorySlug']);

            $epEventsModel->getEvents($upcomingEventsParams);
        }

        if (!empty($epEventsModel->aggregates['categories'])) {
            $eventCategories = $epEventsCategoriesModel->findAllBy([
                'conditions' => [
                    'ids' => array_column($epEventsModel->aggregates['categories'], 'categoryId'),
                ],
            ]);

            $eventCategories = array_map(function ($category) use ($linksTpl, $eventsUriComponents) {
                $category['filterUrl'] = replace_dynamic_uri(
                    $category['special_link'] ?? $category['url'], 'ep_events/' . $linksTpl[$eventsUriComponents['category']]
                );

                return $category;
            }, $eventCategories);
        }
        //endregion of processing aggregate records by category

        //region of getting highlighted event
        $highlightedEvent = $epEventsModel->getHighlightedEvent();
        if (!empty($highlightedEvent)) {
            $image = $highlightedEvent['recommended_image'] ?? $highlightedEvent['main_image'];
            $highlightedEvent['image'] = $this->storage->url(
                EpEventFilePathGenerator::recomendedImagePath((string) $highlightedEvent['id'], $image ?? 'no-image.png')
            );
        }
        //endregion of getting highlighted event

        //region of getting additional events
        if (empty($appliedFilters) && 1 === $page) {
            $epEventsModel->getEvents([
                'endToDateTime' => (new DateTime())->format('Y-m-d H:i'),
                'perPage'       => config('past_events_per_page_on_upcoming_events_page', 3),
                'sortBy'        => 'newest',
            ]);

            $pastEvents = $epEventsModel->records;

            $pastEvents = array_map(function ($event) {
                $event['thumbs']['small'] = $this->storage->url(
                    EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'] ?: '', EpEventMainImageThumb::SMALL())
                );

                $event['thumbs']['medium'] = $this->storage->url(
                    EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'] ?: '', EpEventMainImageThumb::MEDIUM())
                );

                return $event;
            }, $pastEvents);

        } elseif ($upcomingEventsCount <= $perPage) {
            $alreadyDisplayedEventsIds = array_filter(array_unique(array_merge(array_column((array) $upcomingEvents, 'id'), [$highlightedEvent['id'] ?? null])));

            $epEventsModel->getEvents([
                'hasRecommendedLabel'   => 1,
                'endFromDateTime'       => (new DateTime())->format('Y-m-d H:i'),
                'notIdEvents'           => $alreadyDisplayedEventsIds,
                'perPage'               => config('recommended_events_per_page_on_upcoming_events_page', 5),
                'sortBy'                => 'random',
            ]);

            $recommendedEvents = (array) $epEventsModel->records;
            $recommendedEvents = array_map(function ($event) {
                $event['thumbs']['small'] = $this->storage->url(
                    EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'] ?: '', EpEventMainImageThumb::SMALL())
                );

                $event['thumbs']['medium'] = $this->storage->url(
                    EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'] ?: '', EpEventMainImageThumb::MEDIUM())
                );

                return $event;
            }, $recommendedEvents);

            $alreadyDisplayedEventsIds = array_unique(array_merge($alreadyDisplayedEventsIds, array_column($recommendedEvents, 'id')));
        }
        //endregion of getting additional events

        //region of getting countries
        $countries = $countryModel->fetch_port_country();
        $countries = array_map(function ($country) {
            $country['countrySlug'] = strForURL($country['country'] . ' ' . $country['id']);

            return $country;
        }, $countries);
        //endregion of getting countries

        //region of getting types
        $eventTypes = $epEventTypesModel->findAll();
        $eventTypes = array_column($eventTypes, null, 'alias');
        //endregion of getting types

        //region of pagination configs
        /** @var TinyMVC_Library_Pagination $paginationLibrary */
        $paginationLibrary = library(TinyMVC_Library_Pagination::class);

        $paginationLibrary->initialize([
            'replace_url'   => true,
            'total_rows'    => $upcomingEventsCount,
            'first_url'     => rtrim('ep_events/' . $linksTplWithout[$eventsUriComponents['page']], '/'),
            'base_url'      => 'ep_events/' . $linksTpl[$eventsUriComponents['page']],
            'per_page'      => $perPage,
            'cur_page'      => $page,
        ]);
        //endregion of pagination configs

        //region Promoted event
        $eventPromotion = $this->getPromotedEventDisplayInformation();
        //endregion Promoted event

        //region of getting calendar events
        if (logged_in() && have_right('have_calendar') && !empty($upcomingEvents)) {
            /** @var Calendar_Events_Model $calendarEventsModel */
            $calendarEventsModel = model(Calendar_Events_Model::class);

            //Save in the format ['id' => 'id'] in order to be able to use it in the future
            //isset($userCalendarEvents[$eventId]) instead of a more complex operation in_array($eventId, $userCalendarEvents)
            $eventsInCalendar = array_column(
                $calendarEventsModel->findAllBy([
                    'scopes' => [
                        'sourcesIds' => $alreadyDisplayedEventsIds,
                        'eventType'  => EventType::EP_EVENTS(),
                        'userId'     => id_session(),
                    ]
                ]),
                'source_id',
            );
        }
        //endregion of getting calendar events

        views()->displayWebpackTemplate([
            'linkToResetAllFilters' => __SITE_URL . 'ep_events',
            'secondBannerPosition'  => count($upcomingEvents) - 2 > 4 ? count($upcomingEvents) - 2 : count($upcomingEvents),
            'recommendedLabelUrl'   => replace_dynamic_uri('recommended', 'ep_events/' . $linksTpl[$eventsUriComponents['label']]),
            'firstBannerPosition'   => count($upcomingEvents) < 2 ? count($upcomingEvents) : 2,
            'categoriesCounters'    => $epEventsModel->aggregates['categories'] ?? null,
            'defaultSortingUrl'     => replace_dynamic_uri('', rtrim('ep_events/' . $linksTplWithout['sort'], '/')),
            'recommendedEvents'     => $recommendedEvents ?? null,
            'upcomingLabelUrl'      => replace_dynamic_uri('upcoming', 'ep_events/' . $linksTpl[$eventsUriComponents['label']]),
            'eventsInCalendar'      => $eventsInCalendar ?: [],
            'attendedLabelUrl'      => replace_dynamic_uri('attended', 'ep_events/' . $linksTpl[$eventsUriComponents['label']]),
            'highlightedEvent'      => $highlightedEvent,
            'linksTplWithout'       => $linksTplWithout,
            'appliedFilters'        => $appliedFilters,
            'upcomingEvents'        => $upcomingEvents,
            'eventPromotion'        => $eventPromotion,
            'breadcrumbs'           => $this->breadcrumbs,
            'currentPage'           => 'upcomingEvents',
            'eventsTypes'           => $eventTypes,
            'headerTitle'           => ($appliedFilters['type']['value'] ?? null) === $eventTypes['webinar']['slug'] ? translate('ep_events_webinars_header_ttl') : translate('ep_events_header_title'),
            'metaParams'            => $metaParams ?? null,
            'pagination'            => $paginationLibrary->create_links(),
            'categories'            => $eventCategories ?? null,
            'pastEvents'            => $pastEvents ?? null,
            'countries'             => $countries,
            'linksTpl'              => $linksTpl,
            'sorting'               => $sorting,
            'perPage'               => $perPage,
            'count'                 => $upcomingEventsCount,
            'page'                  => $page,
            'sidebar'               => true,
            'headerContent'         => empty($appliedFilters) && 1 == $page ? 'new/ep_events/header_view' : 'new/ep_events/events_header_view',
            'sidebarContent'        => 'new/ep_events/sidebar_view',
            'content'               => 'ep_events/index_view',
            'styleCritical'         => 'ep_events',
        ]);
    }

    public function detail(): void
    {
        $eventId = (int) uri()->segment(3);

        /** @var Elasticsearch_Ep_Events_Model $elasticsearchEpEventsModel */
        $elasticsearchEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);

        $elasticsearchEpEventsModel->getEvents(['id' => $eventId]);
        $event = $elasticsearchEpEventsModel->records[0];
        if (empty($event)) {
            show_404();
        }

        $isPastEvent = new DateTime() > (new DateTime())->createFromFormat('Y-m-d H:i:s', $event['end_date']);

        if (is_null(session()->__get("event_is_viewed_{$eventId}"))) {
            session()->__set("event_is_viewed_{$eventId}", 1);

            /** @var Ep_Events_Model $eventsModel */
            $eventsModel = model(Ep_Events_Model::class);

            $eventsModel->updateOne($eventId, ['views' => $event['views'] + 1]);
        }

        //region of determining of meta params
        $metaParams = [
            '[TITLE]'       => cleanOutput($event['title']),
            '[CATEGORY]'    => cleanOutput($event['category']['name']),
        ];

        if (!empty($event['country']['name'])) {
            $metaParams['[COUNTRY]'] = $event['country']['name'];
        }

        $eventImageUrl = EpEventFilePathGenerator::mainImagePath((int) $event['id'], $event['main_image']);
        if ($this->storage->fileExists($eventImageUrl)) {
            $metaParams['[IMAGE]'] = $this->storage->url($eventImageUrl);
        }

        switch ($event['type']['alias']) {
            case 'offline':
                $metaParams['[TYPE]'] = 'Offline Event';

                break;
            case 'online':
                $metaParams['[TYPE]'] = 'Virtual Event';

                break;
            case 'webinar':
                $metaParams['[TYPE]'] = 'Webinar';

                break;
        }

        $now = new DateTime();
        $eventStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $event['start_date']);
        $eventEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $event['end_date']);

        $metaParams['[TIMING]'] = 'Past';
        if ($eventStartDate <= $now && $eventEndDate >= $now) {
            $metaParams['[TIMING]'] = 'Active';
        } elseif ($eventStartDate > $now) {
            $metaParams['[TIMING]'] = 'Upcoming';
        }
        //endregion of determinating of meta params

        $highlightedEvent = $elasticsearchEpEventsModel->getHighlightedEvent([$eventId]);
        if (!empty($highlightedEvent)) {
            $image = $highlightedEvent['recommended_image'] ?? $highlightedEvent['main_image'];
            $highlightedEvent['image'] = $this->storage->url(
                EpEventFilePathGenerator::recomendedImagePath((string) $highlightedEvent['id'], $image ?? 'no-image.png')
            );
        }

        $relatedEvents = $elasticsearchEpEventsModel->getEvents([
            'notIdEvents' => array_filter([$eventId, $highlightedEvent['id'] ?? null]),
            'categoryId'  => $event['id_category'],
            'perPage'     => config('related_events_per_page_on_ep_events_detail_page', 3),
            'sortBy'      => 'oldest',
        ]);

        $relatedEvents = array_map(function ($event) {
                $event['thumbs']['small'] = $this->storage->url(
                    EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'] ?: '', EpEventMainImageThumb::SMALL())
                );

                $event['thumbs']['medium'] = $this->storage->url(
                    EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'] ?: '', EpEventMainImageThumb::MEDIUM())
                );

                return $event;
        }, $relatedEvents);

        $eventsUriComponents = tmvc::instance()->site_urls['ep_events/index']['replace_uri_components'];
        $categoryUrl = get_dynamic_url($eventsUriComponents['category'] . '/' . $event['category']['url'], __SITE_URL . 'ep_events/' . ($isPastEvent ? 'past' : ''));
        $labelUrl = __SITE_URL . 'ep_events/' . ($eventsUriComponents['label'] ?? 'label');
        $recommendedLabelUrl = $labelUrl . '/recommended';
        $upcomingLabelUrl = $labelUrl . '/upcoming';
        $attendedLabelUrl = $labelUrl . '/attended';

        // Start Work with images
        $event['thumbs'] = [
            'small' => $this->storage->url(EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'], EpEventMainImageThumb::SMALL())),
            'medium' => $this->storage->url(EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'], EpEventMainImageThumb::MEDIUM())),
        ];

        $event['main_image'] = $this->storage->url(
            EpEventFilePathGenerator::mainImagePath((string) $event['id'], $event['main_image'] ?? 'none.jpg')
        );

        if (!empty($event['speaker'])) {
            $event['speaker']['photo'] = $this->storage->url(
                EpEventSpeakersFilePathGenerator::mainImagePath((string) $event['speaker']['id'], $event['speaker']['photo'] ?? 'none.jpg')
            );
        }

        if (!empty($event['partners'])) {
            $event['partners'] = array_map(function ($partner) {
                return [
                    'id'    => $partner['id'],
                    'name'  => $partner['name'],
                    'image' => $this->storage->url(EpEventPartnersFilePathGenerator::mainImagePath((string) $partner['id'], $partner['image']))
                ];
            }, $event['partners']);
        }

        if (!empty($event['gallery'])) {
            $event['gallery'] = array_map(function ($item) use ($event) {
                return [
                    'id'    => $item['id'],
                    'name'  => $item['name'],
                    'image' => $this->storage->url(
                        EpEventFilePathGenerator::galleryThumbImagePath((string) $event['id'], $item['name'], EpEventGalleryImageThumb::SMALL())
                    )
                ];
            }, $event['gallery']);
        }
        // End Work with images

        if (logged_in()) {
            /** @var Calendar_Events_Model $calendarEventsModel */
            $calendarEventsModel = model(Calendar_Events_Model::class);

            $eventsInCalendar = array_column(
                $calendarEventsModel->findAllBy([
                    'scopes' => [
                        'sourcesIds' => array_merge([$eventId], array_column($relatedEvents, 'id')),
                        'userId'     => id_session(),
                    ],
                ]),
                'source_id'
            );
        }

        views()->displayWebpackTemplate([
            'headerContent'         => 'new/ep_events/event_detail_header_view',
            'content'               => 'ep_events/detail_page_view',
            'styleCritical'         => 'ep_events_detail',
            'pageConnect'           => 'ep_event_page',
            'customEncoreLinks'     => true,
            'breadcrumbs'           => array_filter([
                [
                    'link'  => __SITE_URL . 'ep_events',
                    'title' => translate('ep_events_header_title'),
                ],
                $isPastEvent ? [
                    'link'  => __SITE_URL . 'ep_events/past',
                    'title' => translate('ep_events_past_heading'),
                ] : null,
                [
                    'link'  => $categoryUrl,
                    'title' => cleanOutput($event['category']['name']),
                ],
                [
                    'link'  => getEpEventDetailUrl($event),
                    'title' => cleanOutput($event['title']),
                ],
            ]),
            'event'                 => $event,
            'relatedEvents'         => $relatedEvents,
            'highlightedEvent'      => $highlightedEvent,
            'recommendedLabelUrl'   => $recommendedLabelUrl,
            'upcomingLabelUrl'      => $upcomingLabelUrl,
            'attendedLabelUrl'      => $attendedLabelUrl,
            'metaParams'            => $metaParams,
            'isDetailPage'          => true,
            'comments'              => [
                'hash_components'   => eventsCommentsResourceHashComponents($event['id']),
                'type_id'           => CommentType::EP_EVENTS()->value,
            ],
            'eventsInCalendar'      => $eventsInCalendar ?? [],
        ]);
    }

    public function past(): void
    {
        $eventsUriComponents = tmvc::instance()->site_urls['ep_events/past']['replace_uri_components'];
        $uri = uri()->uri_to_assoc();

        unset($uri['ep_events']);
        checkURI($uri, array_values((array) $eventsUriComponents));
        checkIsValidPage($uri['page']);

        $breadcrumbLink = __SITE_URL . 'ep_events/past';
        $this->breadcrumbs = [
            [
                'link'  => __SITE_URL . 'ep_events',
                'title' => translate('ep_events_header_title'),
            ],
            [
                'link'  => __SITE_URL . 'ep_events/past',
                'title' => translate('ep_events_past_heading'),
            ],
        ];

        $linksMap = [
            $eventsUriComponents['country'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['country'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['category'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['category'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['type'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['type'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['time'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['time'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['label'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['label'], $eventsUriComponents['page']],
            ],
            $eventsUriComponents['page'] => [
                'type' => 'uri',
                'deny' => [$eventsUriComponents['page']],
            ],
            'sort' => [
                'type' => 'get',
                'deny' => ['sort', $eventsUriComponents['page']],
            ],
            'keywords' => [
                'type' => 'get',
                'deny' => ['keywords', $eventsUriComponents['page']],
            ],
        ];

        $linksTpl = uri()->make_templates($linksMap, $uri);
        $linksTplWithout = uri()->make_templates($linksMap, $uri, true);

        $perPage = (int) config('ep_events_per_page', 10);
        $page = (int) ($uri[$eventsUriComponents['page']] ?? 1);

        /** @var Elasticsearch_Ep_Events_Model $epEventsModel */
        $epEventsModel = model(Elasticsearch_Ep_Events_Model::class);

        $pastEventsParams = [
            'aggregateByCategories' => true,
            'perPage'               => $perPage,
            'page'                  => $page,
        ];

        $appliedFilters = [];

        /** @var Ep_Events_Categories_Model $epEventsCategoriesModel */
        $epEventsCategoriesModel = model(Ep_Events_Categories_Model::class);

        //region of processing filter by category
        if (!empty($categorySlug = $uri[$eventsUriComponents['category']] ?? null)) {
            if (empty($category = $epEventsCategoriesModel->findOneBy([
                'conditions' => [
                    'urlOrSpecialLink' => $categorySlug,
                ],
            ]))) {
                show_404();
            }

            // if the special link is not empty, then access should be done only by it
            if (($category['special_link'] ?? $categorySlug) !== $categorySlug) {
                headerRedirect(replace_dynamic_uri($category['special_link'], 'ep_events/past/' . $linksTpl[$eventsUriComponents['category']]));
            }

            $pastEventsParams['categorySlug'] = $categorySlug;
            $appliedFilters['category'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/past/' . $linksTplWithout['category'], '/'),
                'displayedValue'    => cleanOutput($category['name']),
                'value'             => $categorySlug,
                'name'              => translate('ep_events_detail_category_label'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['category'] . '/' . $categorySlug;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => cleanOutput($category['name']),
            ];

            $metaParams['[CATEGORY]'] = cleanOutput($category['name']);
        }
        //endregion of processing filter by a category

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);

        //region of processing filter by country
        if (!empty($countrySlug = $uri[$eventsUriComponents['country']] ?? null)) {
            if (
                empty($country = $countryModel->get_country(id_from_link($countrySlug)))
                || strForURL($country['country'] . ' ' . $country['id']) !== $countrySlug
            ) {
                show_404();
            }

            $pastEventsParams['countrySlug'] = $countrySlug;
            $appliedFilters['country'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/past/' . $linksTplWithout['country'], '/'),
                'displayedValue'    => cleanOutput($country['country']),
                'value'             => $countrySlug,
                'name'              => translate('ep_events_country_label'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['country'] . '/' . $countrySlug;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => cleanOutput($country['country']),
            ];

            $metaParams['[COUNTRY]'] = cleanOutput($country['country']);
        }
        //endregion of processing filter by a country

        /** @var Ep_Events_Types_Model $epEventTypesModel */
        $epEventTypesModel = model(Ep_Events_Types_Model::class);

        //region of processing filter by type
        $metaParams['[TYPE]'] = 'Events & Webinars';

        if (!empty($typeSlug = $uri[$eventsUriComponents['type']] ?? null)) {
            if (empty($type = $epEventTypesModel->findOneBy(['conditions' => ['slug' => $typeSlug]]))) {
                show_404();
            }

            $pastEventsParams['typeSlug'] = $typeSlug;
            $appliedFilters['type'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/past/' . $linksTplWithout['type'], '/'),
                'displayedValue'    => cleanOutput($type['title']),
                'value'             => $typeSlug,
                'name'              => translate('ep_events_detail_type_label'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['type'] . '/' . $typeSlug;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => cleanOutput($type['title']),
            ];

            switch ($type['alias']) {
                case 'offline':
                    $metaParams['[TYPE]'] = 'Offline Events';

                    break;
                case 'online':
                    $metaParams['[TYPE]'] = 'Virtual Events';

                    break;
                case 'webinar':
                    $metaParams['[TYPE]'] = 'Webinars';

                    break;
            }
        }
        //endregion of processing filter by type

        //region of processing filter by time
        $now = new DateTime();
        $pastEventsParams['endToDateTime'] = $now->format('Y-m-d H:i');

        if (!empty($time = $uri[$eventsUriComponents['time']] ?? null)) {
            if ('past' !== $time) {
                show_404();
            }

            $appliedFilters['time'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/past/' . $linksTplWithout['time'], '/'),
                'displayedValue'    => translate('ep_events_past_heading'),
                'value'             => $time,
                'name'              => translate('ep_events_time_filter_title'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['time'] . '/' . $time;
        }
        //endregion of processing filter by time

        //region of processing filter by label
        if (!empty($label = $uri[$eventsUriComponents['label']] ?? null)) {
            if (!in_array($label, ['upcoming', 'recommended', 'attended'])) {
                show_404();
            }

            switch ($label) {
                case 'upcoming':
                    $pastEventsParams['upcomingByEp'] = 1;
                    $breadcrumbTitle = translate('ep_events_upcoming_by_ep');

                    break;
                case 'recommended':
                    $pastEventsParams['recommendedByEp'] = 1;
                    $breadcrumbTitle = translate('ep_events_recommended_by_ep');

                    break;
                case 'attended':
                    $pastEventsParams['attendedByEp'] = 1;
                    $breadcrumbTitle = translate('ep_events_attended_by_ep');

                    break;
            }

            $appliedFilters['label'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/past/' . $linksTplWithout['label'], '/'),
                'displayedValue'    => $breadcrumbTitle,
                'value'             => $label,
                'name'              => translate('ep_events_detail_label'),
            ];

            $breadcrumbLink .= '/' . $eventsUriComponents['label'] . '/' . $label;
            $this->breadcrumbs[] = [
                'link'  => $breadcrumbLink,
                'title' => $breadcrumbTitle,
            ];
        }
        //endregion of processing filter by label

        //region of processing filter by keywords
        if (!empty($keywords = request()->query->get('keywords'))) {
            $pastEventsParams['search'] = trim($keywords);
            $appliedFilters['keywords'] = [
                'linkToResetFilter' => rtrim(__SITE_URL . 'ep_events/past/' . $linksTplWithout['keywords'], '/'),
                'displayedValue'    => cleanOutput($keywords),
                'value'             => $keywords,
                'name'              => translate('ep_events_keywords_title'),
            ];
        }
        //endregion of processing filter by keywords

        //region of sorting
        if (!empty($sort = request()->query->get('sort'))) {
            if (!in_array($sort, ['date', 'most_viewed'])) {
                show_404();
            }

            $sorting = [
                'displayedValue'    => 'date' === $sort ? translate('sort_by_date_txt') : translate('sort_by_most_viewed_txt'),
                'value'             => $sort,
            ];

            $pastEventsParams['sortBy'] = 'date' == $sort ? 'newest' : 'most_viewed';
        }

        $sorting['default'] = isset($appliedFilters['keywords']) ? 'best_match' : 'date';

        if (empty($keywords) && empty($sort)) {
            $pastEventsParams['sortBy'] = 'newest';
        }
        //endregion of sorting

        //region of getting upcoming events
        $epEventsModel->getEvents($pastEventsParams);

        $pastEvents = $epEventsModel->records;
        $pastEventsCount = (int) $epEventsModel->recordsCount;
        //endregion of getting upcoming events

        //region of processing aggregate records by category
        // if a filter was applied by category, then we repeat the request without it for aggregate category counters
        if (isset($appliedFilters['category'], $pastEventsParams['aggregateByCategories'])) {
            unset($pastEventsParams['categorySlug']);

            $epEventsModel->getEvents($pastEventsParams);
        }

        if (!empty($epEventsModel->aggregates['categories'])) {
            $eventCategories = $epEventsCategoriesModel->findAllBy([
                'conditions' => [
                    'ids' => array_column($epEventsModel->aggregates['categories'], 'categoryId'),
                ],
            ]);

            $eventCategories = array_map(function ($category) use ($linksTpl, $eventsUriComponents) {
                $category['filterUrl'] = replace_dynamic_uri(
                    $category['special_link'] ?? $category['url'], 'ep_events/past/' . $linksTpl[$eventsUriComponents['category']]
                );

                return $category;
            }, $eventCategories);
        }
        //endregion of processing aggregate records by category

        //region of getting countries
        $countries = $countryModel->fetch_port_country();
        $countries = array_map(function ($country) {
            $country['countrySlug'] = strForURL($country['country'] . ' ' . $country['id']);

            return $country;
        }, $countries);
        //endregion of getting countries

        //region of getting types
        $eventTypes = $epEventTypesModel->findAll();
        $eventTypes = array_column($eventTypes, null, 'alias');
        //endregion of getting types

        //region of pagination configs
        /** @var TinyMVC_Library_Pagination $paginationLibrary */
        $paginationLibrary = library(TinyMVC_Library_Pagination::class);

        $paginationLibrary->initialize([
            'cur_page'		    => $page,
        ]);
        //endregion of pagination configs
        views()->displayWebpackTemplate([
            'linkToResetAllFilters' => __SITE_URL . 'ep_events/past',
            'recommendedLabelUrl'   => replace_dynamic_uri('recommended', 'ep_events/past/' . $linksTpl[$eventsUriComponents['label']]),
            'categoriesCounters'    => $epEventsModel->aggregates['categories'] ?? null,
            'defaultSortingUrl'     => replace_dynamic_uri('', rtrim('ep_events/past/' . $linksTplWithout['sort'], '/')),
            'upcomingLabelUrl'      => replace_dynamic_uri('upcoming', 'ep_events/past/' . $linksTpl[$eventsUriComponents['label']]),
            'attendedLabelUrl'      => replace_dynamic_uri('attended', 'ep_events/past/' . $linksTpl[$eventsUriComponents['label']]),
            'linksTplWithout'       => $linksTplWithout,
            'appliedFilters'        => $appliedFilters,
            'breadcrumbs'           => $this->breadcrumbs,
            'currentPage'           => 'pastEvents',
            'eventsTypes'           => $eventTypes,
            'headerTitle'           => ($appliedFilters['type']['value'] ?? null) === $eventTypes['webinar']['slug'] ? translate('ep_events_past_webinars_heading') : translate('ep_events_past_heading'),
            'metaParams'            => $metaParams ?? null,
            'pagination'            => $paginationLibrary->create_links(),
            'categories'            => $eventCategories ?? null,
            'pastEvents'            => $pastEvents,
            'countries'             => $countries,
            'linksTpl'              => $linksTpl,
            'sorting'               => $sorting,
            'perPage'               => $perPage,
            'count'                 => $pastEventsCount,
            'page'                  => $page,
            'sidebar'               => true,
            'headerContent'         => 'new/ep_events/events_header_view',
            'sidebarContent'        => 'new/ep_events/sidebar_view',
            'content'               => 'ep_events/index_past_view',
            'styleCritical'         => 'ep_events_past',
        ]);
    }

    public function administration(): void
    {
        checkPermision('ep_events_administration');

        /** @var Ep_Events_Types_Model $eventTypesModel */
        $eventTypesModel = model(Ep_Events_Types_Model::class);

        /** @var Ep_Events_Categories_Model $eventCategoriesModel */
        $eventCategoriesModel = model(Ep_Events_Categories_Model::class);

        /** @var Ep_Events_Partners_Model $eventPartnersModel */
        $eventPartnersModel = model(Ep_Events_Partners_Model::class);

        /** @var Ep_Events_Speakers_Model $eventSpeakersModel */
        $eventSpeakersModel = model(Ep_Events_Speakers_Model::class);

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);

        views(
            [
                'admin/header_view',
                'admin/ep_events/index_view',
                'admin/footer_view',
            ],
            [
                'eventCategories'   => $eventCategoriesModel->findAll(),
                'eventPartners'     => $eventPartnersModel->findAll(),
                'eventSpeakers'     => $eventSpeakersModel->findAll(),
                'eventTypes'        => $eventTypesModel->findAll(),
                'countries'         => $countryModel->fetch_port_country(),
                'title'             => translate('ep_events_administration_page_title'),
            ]
        );
    }

    public function ajax_dt_administration(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('ep_events_administration');

        /**
         * @var Ep_events_Model $eventsModel
         */
        $eventsModel = model(Ep_events_Model::class);

        $perPage = (int) $_POST['iDisplayLength'];
        $skip = (int) $_POST['iDisplayStart'];
        $page = $skip / $perPage + 1;

        $eventsTable = $eventsModel->getTable();

        $order = array_column(
            dtOrdering($_POST, array_merge(
                [
                    'dt_event_id'           => "`{$eventsTable}`.`id`",
                    'dt_event_price'        => "`{$eventsTable}`.`ticket_price`",
                    'dt_event_start_date'   => "`{$eventsTable}`.`start_date`",
                    'dt_event_end_date'     => "`{$eventsTable}`.`end_date`",
                ],
                !have_right('view_event_statistic') ? [] : [
                    'dt_event_views' => "`{$eventsTable}`.`views`",
                ]
            )),
            'direction',
            'column'
        );

        $conditions = dtConditions($_POST, [
            ['as' => 'title',           'key' => 'title',           'type' => 'cut_str|trim'],
            ['as' => 'type',            'key' => 'type',            'type' => 'int'],
            ['as' => 'isRecommended',   'key' => 'is_recommended',  'type' => 'int'],
            ['as' => 'isUpcoming',      'key' => 'is_upcoming',     'type' => 'int'],
            ['as' => 'isAttended',      'key' => 'is_attended',     'type' => 'int'],
            ['as' => 'isPromoted',      'key' => 'promoted',        'type' => 'bool'],
            ['as' => 'categories',      'key' => 'categories',      'type' => fn ($categoriesIds) => empty($categoriesIds) ? null : array_unique(array_map('intval', explode(',', $categoriesIds)))],
            ['as' => 'partners',        'key' => 'partners',        'type' => fn ($partnersIds) => empty($partnersIds) ? null : array_unique(array_map('intval', explode(',', $partnersIds)))],
            ['as' => 'speakers',        'key' => 'speakers',        'type' => fn ($speakersIds) => empty($speakersIds) ? null : array_unique(array_map('intval', explode(',', $speakersIds)))],
            ['as' => 'countries',       'key' => 'countries',       'type' => fn ($countriesIds) => empty($countriesIds) ? null : array_unique(array_map('intval', explode(',', $countriesIds)))],
            ['as' => 'startDateFrom',   'key' => 'start_date_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 00:00:00'],
            ['as' => 'startDateTo',     'key' => 'start_date_to',   'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 23:59:59'],
            ['as' => 'endDateFrom',     'key' => 'end_date_from',   'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 00:00:00'],
            ['as' => 'endDateTo',       'key' => 'end_date_to',     'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 23:59:59'],
        ]);

        $columns = ["{$eventsModel->getTable()}.*"];
        $with = ['type', 'country', 'state', 'city', 'category', 'speaker'];
        $joins = array_filter([
            empty($conditions['partners']) ? null : 'eventPartnersRelations',
            empty($conditions['speakers']) ? null : 'speakers',
        ]);

        $queryParams = compact('columns', 'conditions', 'joins', 'with', 'order');
        $events = $eventsModel->runWithCasts(
            fn () => $eventsModel->paginate($queryParams, $perPage, $page),
            [
                'published_date' => Types::DATETIME_IMMUTABLE,
                'create_date'    => Types::DATETIME_IMMUTABLE,
                'update_date'    => Types::DATETIME_IMMUTABLE,
                'start_date'     => Types::DATETIME_IMMUTABLE,
                'end_date'       => Types::DATETIME_IMMUTABLE,
            ]
        );

        $output = [
            'iTotalDisplayRecords'  => empty($events['data']) ? 0 : $eventsModel->countBy($queryParams),
            'iTotalRecords'         => $events['total'] ?? 0,
            'aaData'                => [],
            'sEcho'                 => request()->request->getInt('sEcho'),
        ];

        if (empty($events['data'])) {
            jsonResponse('', 'success', $output);
        }

        $currentDate = new DateTimeImmutable();
        foreach ($events['data'] as $event) {
            $linkForOpenEventDetails = __SITE_URL . 'ep_events/popup_forms/event_details/' . $event['id'];
            $linkForUploadRecommendedImage = __SITE_URL . 'ep_events/popup_forms/recommended_image/' . $event['id'];
            $promoteEventFormLink = sprintf('%sep_events/popup_forms/promote_event/%s', __SITE_URL, $event['id']);
            $deletePromotionLink = sprintf('%sep_events/ajax_operations/delete_promotion/%s', __SITE_URL, $event['id']);
            $isPromotedEvent = null !== $event['promotion_start_date'];

            $agendaBtn = '';
            $highlightEventBtn = '';

            switch ($event['id_type']) {
                case self::OFFLINE_EVENT_TYPE_ID:
                    $linkForEditEvent = __SITE_URL . 'ep_events/popup_forms/edit_offline_event/' . $event['id'];
                    $linkForEditAgenda = __SITE_URL . 'ep_events/popup_forms/agenda/' . $event['id'];

                    $btnText = translate('ep_events_administration_agenda_btn', null, true);

                    $agendaBtn = sprintf(
                        <<<AGENDA
                        <li>
                            <a class="fancyboxValidateModalDT fancybox.ajax" href="{$linkForEditAgenda}" title="%s" data-title="%s">
                                <span class="ep-icon ep-icon_paper-stroke"></span> %s
                            </a>
                        </li>
                        AGENDA,
                        $btnText,
                        $btnText,
                        $btnText,
                    );

                    break;
                case self::ONLINE_EVENT_TYPE_ID:
                    $linkForEditEvent = __SITE_URL . 'ep_events/popup_forms/edit_online_event/' . $event['id'];

                    break;
                case self::WEBINAR_TYPE_ID:
                    $linkForEditEvent = __SITE_URL . 'ep_events/popup_forms/edit_webinar/' . $event['id'];

                    break;

                default:
                    $linkForEditEvent = null;
            }

            if ($event['is_recommended_by_ep']) {
                $btnRecommendedEvent = sprintf(
                    <<<RECOMMENDED
                    <a class="ep-icon ep-icon_ok txt-green confirm-dialog"
                        data-callback="togleRecommendedStatus"
                        data-message="%s"
                        title="%s"
                        data-id="{$event['id']}"
                    ></a>
                    RECOMMENDED,
                    translate('ep_events_administration_unmark_as_recommended_confirmation_msg', null, true),
                    translate('ep_events_administration_unmark_as_recommended_btn', null, true)
                );
            } else {
                $btnRecommendedEvent = sprintf(
                    <<<RECOMMENDED
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="togleRecommendedStatus"
                        data-message="%s"
                        title="%s"
                        data-id="{$event['id']}"
                    ></a>
                    RECOMMENDED,
                    translate('ep_events_administration_mark_as_recommended_confirmation_msg', null, true),
                    translate('ep_events_administration_mark_as_recommended_btn', null, true)
                );
            }

            if ($event['is_upcoming_by_ep']) {
                $btnUpcomingEvent = sprintf(
                    <<<UPCOMING
                    <a class="ep-icon ep-icon_ok txt-green confirm-dialog"
                        data-callback="togleUpcomingStatus"
                        data-message="%s"
                        title="%s"
                        data-id="{$event['id']}"
                    ></a>
                    UPCOMING,
                    translate('ep_events_administration_unmark_as_upcoming_confirmation_msg', null, true),
                    translate('ep_events_administration_unmark_as_upcoming_btn', null, true)
                );
            } else {
                $btnUpcomingEvent = sprintf(
                    <<<UPCOMING
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="togleUpcomingStatus"
                        data-message="%s"
                        title="%s"
                        data-id="{$event['id']}"
                    ></a>
                    UPCOMING,
                    translate('ep_events_administration_mark_as_upcoming_confirmation_msg', null, true),
                    translate('ep_events_administration_mark_as_upcoming_btn', null, true)
                );
            }

            if ($event['is_attended_by_ep']) {
                $btnAttendedEvent = sprintf(
                    <<<ATTENDED
                    <a class="ep-icon ep-icon_ok txt-green confirm-dialog"
                        data-callback="togleAttendedStatus"
                        data-message="%s"
                        title="%s"
                        data-id="{$event['id']}"
                    ></a>
                    ATTENDED,
                    translate('ep_events_administration_unmark_as_attended_confirmation_msg', null, true),
                    translate('ep_events_administration_unmark_as_attended_btn', null, true)
                );
            } else {
                $btnAttendedEvent = sprintf(
                    <<<ATTENDED
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="togleAttendedStatus"
                        data-message="%s"
                        title="%s"
                        data-id="{$event['id']}"
                    ></a>
                    ATTENDED,
                    translate('ep_events_administration_mark_as_attended_confirmation_msg', null, true),
                    translate('ep_events_administration_mark_as_attended_btn', null, true)
                );
            }

            if (!empty($event['highlighted_end_date'])) {
                $btnText = translate('ep_events_administration_unmark_as_highlighted_btn', null, true);

                $highlightEventBtn = sprintf(
                    <<<HIGHLIGHT
                    <li>
                        <a class="confirm-dialog"
                            data-callback="highlightEvent"
                            data-message="%s"
                            title="%s"
                            data-id="{$event['id']}"
                        ><span class="ep-icon ep-icon_star-received txt-green"></span> %s
                        </a>
                    </li>
                    HIGHLIGHT,
                    translate('ep_events_administration_unmark_as_highlighted_confirmation_msg', null, true),
                    $btnText,
                    $btnText
                );
            } elseif ($event['is_recommended_by_ep'] || $event['is_upcoming_by_ep'] || $event['is_attended_by_ep']) {
                $btnText = translate('ep_events_administration_mark_as_highlighted_btn', null, true);

                $highlightEventBtn = sprintf(
                    <<<HIGHLIGHT
                    <li>
                        <a class="confirm-dialog"
                            data-callback="highlightEvent"
                            data-message="%s"
                            title="%s"
                            data-id="{$event['id']}"
                        ><span class="ep-icon ep-icon_star txt-green"></span> %s
                        </a>
                    </li>
                    HIGHLIGHT,
                    translate('ep_events_administration_mark_as_highlighted_confirmation_msg', null, true),
                    $btnText,
                    $btnText
                );
            }

            $promotionMark = null;
            $hightlightMark = null;
            if ($isPromotedEvent) {
                $labelText = 'Promoted (pending)';
                $labelClassName = 'label-warning';
                if ($currentDate > $event['promotion_start_date'] && $currentDate >= $event['promotion_end_date']) {
                    $labelText = 'Promoted (expired)';
                    $labelClassName = 'label-default';
                } else if ($currentDate >= $event['promotion_start_date'] && $currentDate < $event['promotion_end_date']) {
                    $labelText = 'Promoted';
                    $labelClassName = 'label-success';
                }
                $promotionMark = sprintf(
                    '<span class="label %s" title="Promoted from %s to %s">%s</span>',
                    $labelClassName,
                    $event['promotion_start_date']->format(PUBLIC_DATETIME_FORMAT),
                    $event['promotion_end_date']->format(PUBLIC_DATETIME_FORMAT),
                    $labelText,
                );
            }
            if (!empty($event['highlighted_end_date'])) {
                $hightlightMark = sprintf(
                    '<span class="label label-success" title="Highlighted until %s">%s</span>',
                    getDateFormatIfNotEmpty($event['highlighted_end_date'], 'Y-m-d H:i:s', PUBLIC_DATETIME_FORMAT),
                    'Highlighted',
                );
            }
            $eventTitleBlock = sprintf(
                <<<TITLE
                <div class="grid-text">
                    <div class="grid-text__item">%s</div>
                    <div>%s %s</div>
                </div>
                TITLE,
                cleanOutput($event['title']),
                $hightlightMark,
                $promotionMark
            );

            $promoteButton = null;
            $deletePromotionButton = null;
            // Allow to delete promotion if it not started yet.
            if ($isPromotedEvent) {
                $deletePromotionButton = sprintf(
                    <<<'BUTTON'
                    <li>
                        <a class="confirm-dialog"
                            data-action-url="%s"
                            data-callback="deleteEventPromotion"
                            data-message="%s"
                            href="#"
                            title="%s">
                            <span class="txt-red ep-icon ep-icon_unfeatured"></span> %s
                        </a>
                    </li>
                    BUTTON,
                    $deletePromotionLink,
                    translate('ep_events_administration_delete_promotion_btn_confirm_text', null, true),
                    translate('ep_events_administration_delete_promotion_btn_title', null, true),
                    translate('ep_events_administration_delete_promotion_btn_text', null, true)
                );
            // Otherwise if event not started yet, then we can add promotion
            } elseif ($currentDate < $event['start_date']) {
                $promoteButton = sprintf(
                    <<<'BUTTON'
                    <li>
                        <a class="fancyboxValidateModalDT fancybox.ajax" data-fancybox-href="%s" title="%s" href="#" data-title="%s">
                            <span class="txt-green ep-icon ep-icon_featured"></span> %s
                        </a>
                    </li>
                    BUTTON,
                    $promoteEventFormLink,
                    translate('ep_events_administration_promote_btn_title', null, true),
                    translate('ep_events_administration_promote_popup_title', null, true),
                    translate('ep_events_administration_promote_btn_text', null, true)
                );
            // And finally, in any other cases we show that event is not eligible for promotion
            } else {
                $promoteButton = sprintf(
                    <<<'BUTTON'
                    <li class="call-systmess" data-message="%s" data-type="info">
                        <a class="call-systmess disabled" title="%s" href="#">
                            <span class="ep-icon ep-icon_featured"></span> %s
                        </a>
                    </li>
                    BUTTON,
                    translate('ep_events_administration_promote_btn_message_ineligible', null, true),
                    translate('ep_events_administration_promote_btn_title', null, true),
                    translate('ep_events_administration_promote_btn_text', null, true)
                );
            }

            $eventImage = $this->storage->url(
                EpEventFilePathGenerator::mainImagePath($event['id'], $event['main_image'])
            );

            $output['aaData'][] = [
                'dt_event_is_recommended'   => $btnRecommendedEvent,
                'dt_event_is_upcoming'      => $btnUpcomingEvent,
                'dt_event_is_attended'      => $btnAttendedEvent,
                'dt_event_start_date'       => null !== $event['start_date'] ? $event['start_date']->format('j M, Y H:i') : '—',
                'dt_event_end_date'         => null !== $event['end_date'] ? $event['end_date']->format('j M, Y H:i') : '—',
                'dt_event_location'         => cleanOutput(implode(', ', array_filter([
                    $event['country']['name'] ?? '',
                    $event['state']['name'] ?? '',
                    $event['city']['name'] ?? '',
                    $event['address'],
                ]))),
                'dt_event_image'            => '<img class="mw-50 mh-50 js-fs-image" src="' . $eventImage . '" alt="" data-fsw="50" data-fsh="50"/>',
                'dt_event_title'            => $eventTitleBlock,
                'dt_event_price'            => get_price($event['ticket_price']),
                'dt_event_views'            => have_right('view_event_statistic') ? $event['views'] : null,
                'dt_event_type'             => cleanOutput($event['type']['title']),
                'dt_event_id'               => $event['id'],
                'dt_event_actions'          => sprintf(
                    <<<ACTIONS
                    <div class="dropdown">
                        <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                            <li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="{$linkForOpenEventDetails}" title="%s" data-title="%s">
                                    <span class="ep-icon ep-icon_info"></span> %s
                                </a>
                            </li>
                            <li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="{$linkForEditEvent}" title="%s" data-title="%s">
                                    <span class="ep-icon ep-icon_pencil"></span> %s
                                </a>
                            </li>
                            <li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="{$linkForUploadRecommendedImage}" title="%s" data-title="%s">
                                    <span class="ep-icon ep-icon_photo-gallery"></span> %s
                                </a>
                            </li>
                            {$highlightEventBtn}
                            {$deletePromotionButton}
                            {$promoteButton}
                            {$agendaBtn}
                        </ul>
                    </div>
                    ACTIONS,
                    translate('ep_events_administration_view_event_details_btn_title', null, true),
                    translate('ep_events_administration_view_event_details_btn_data_title', null, true),
                    translate('ep_events_administration_view_event_details_btn', null, true),
                    translate('ep_events_administration_edit_event_btn_title', null, true),
                    translate('ep_events_administration_edit_event_btn_data_title', null, true),
                    translate('ep_events_administration_edit_event_btn', null, true),
                    translate('ep_events_administration_set_recommended_image_btn_title', null, true),
                    translate('ep_events_administration_set_recommended_image_btn_data_title', null, true),
                    translate('ep_events_administration_set_recommended_image_btn', null, true),
                ),
            ];
        }

        jsonResponse('', 'success', $output);
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms(): void
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'event_details':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showEventDetailsPopup((int) uri()->segment(4));

                break;
            case 'add_online_event':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showAddOnlineEventPopup();

                break;
            case 'edit_online_event':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showEditOnlineEventPopup((int) uri()->segment(4));

                break;
            case 'add_offline_event':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showAddOfflineEventPopup();

                break;
            case 'edit_offline_event':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showEditOfflineEventPopup((int) uri()->segment(4));

                break;
            case 'add_webinar':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showAddWebinarPopup();

                break;
            case 'edit_webinar':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showEditWebinarPopup((int) uri()->segment(4));

                break;
            case 'agenda':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showAgendaPopup((int) uri()->segment(4));

                break;
            case 'recommended_image':
                checkPermisionAjaxModal('ep_events_administration');

                $this->showRecommendedImagePopup((int) uri()->segment(4));

                break;
            case 'promote_event':
                checkPermisionAjaxModal('ep_events_administration');

                try {
                    /** @var Ep_Events_Model $eventsRepository */
                    $eventsRepository = model(Ep_Events_Model::class);
                    $this->showEventPromotionPopup($eventsRepository->getEvent(uri()->segment(4)));
                } catch (NotFoundException $e) {
                    messageInModal(translate('ep_events_administration_event_id_not_found'));
                }

                break;

            case 'email_this':
                checkPermisionAjaxModal('email_this');

                $this->showEmailThisEventPopup((int) uri()->segment(4));
                break;
            case 'share_event':
                checkPermisionAjaxModal('share_this');

                $this->showShareEventPopup((int) uri()->segment(4), id_session());
                break;

            default:
                messageInModal(translate('systmess_error_route_not_found'));

                break;
        }
    }

    /**
     * Handles ajax operations.
     *
     * @throws Exception
     */
    public function ajax_operations(): void
    {
        checkIsAjax();

        try {
            switch (uri()->segment(3)) {
                case 'add_online_event':
                    checkPermisionAjax('ep_events_administration');

                    $this->addOnlineEvent();

                    break;
                case 'edit_online_event':
                    checkPermisionAjax('ep_events_administration');

                    $this->editOnlineEvent((int) uri()->segment(4));

                    break;
                case 'add_offline_event':
                    checkPermisionAjax('ep_events_administration');

                    $this->addOfflineEvent();

                    break;
                case 'edit_offline_event':
                    checkPermisionAjax('ep_events_administration');

                    $this->editOfflineEvent((int) uri()->segment(4));

                    break;
                case 'add_webinar':
                    checkPermisionAjax('ep_events_administration');

                    $this->addWebinar();

                    break;
                case 'edit_webinar':
                    checkPermisionAjax('ep_events_administration');

                    $this->editWebinar((int) uri()->segment(4));

                    break;
                case 'upload_main_image':
                    checkPermisionAjax('ep_events_administration');

                    $this->uploadMainImage();

                    break;
                case 'upload_gallery':
                    checkPermisionAjax('ep_events_administration');

                    $this->uploadGallery();

                    break;
                case 'delete_temp_gallery_image':
                    checkPermisionAjax('ep_events_administration');

                    $this->removeTempGalleryImage();

                    break;
                case 'upload_recommended_image':
                    checkPermisionAjax('ep_events_administration');

                    $this->uploadRecommendedImage();

                    break;
                case 'delete_temp_recommended_image':
                    checkPermisionAjax('ep_events_administration');

                    $this->removeTempRecommendedImage();

                    break;
                case 'edit_recommended_image':
                    checkPermisionAjax('ep_events_administration');

                    $this->editRecommendedImage();

                    break;
                case 'agenda':
                    checkPermisionAjax('ep_events_administration');

                    $this->editAgenda(uri()->segment(4) ?? null);

                    break;
                case 'togle_recommended_status':
                    checkPermisionAjax('ep_events_administration');

                    $this->togleRecommendedStatus();

                    break;
                case 'togle_upcoming_status':
                    checkPermisionAjax('ep_events_administration');

                    $this->togleUpcomingStatus();

                    break;
                case 'togle_attended_status':
                    checkPermisionAjax('ep_events_administration');

                    $this->togleAttendedStatus();

                    break;
                case 'highlight_event':
                    checkPermisionAjax('ep_events_administration');

                    $this->togleEventHighlighting();

                    break;
                case 'promote_event':
                    checkPermisionAjax('ep_events_administration');

                    try {
                        /** @var Ep_Events_Model $eventsRepository */
                        $eventsRepository = model(Ep_Events_Model::class);
                        $this->promoteEvent(request(), $eventsRepository, $eventsRepository->getEvent(uri()->segment(4)));
                    } catch (NotFoundException $e) {
                        jsonResponse(translate('ep_events_administration_event_id_not_found'));
                    }

                    break;
                case 'delete_promotion':
                    checkPermisionAjax('ep_events_administration');

                    try {
                        /** @var Ep_Events_Model $eventsRepository */
                        $eventsRepository = model(Ep_Events_Model::class);
                        $this->deletePromotion($eventsRepository, $eventsRepository->getEvent(uri()->segment(4)));
                    } catch (NotFoundException $e) {
                        jsonResponse(translate('ep_events_administration_event_id_not_found'));
                    }

                    break;
                case 'email_this':
                    checkPermisionAjax('email_this');

                    $this->sendEmail(request()->request, id_session(), user_name_session(), 'email');
                    break;
                case 'share_this':
                    checkPermisionAjax('share_this');

                    $this->sendEmail(request()->request, id_session(), user_name_session(), 'share');
                    break;

                default:
                    jsonResponse(translate('systmess_error_route_not_found'));
            }
        } catch (ValidationException $e) {
            jsonResponse(
                array_merge(
                    array_map(
                        fn (ConstraintViolation $violation) => $violation->getMessage(),
                        iterator_to_array($e->getValidationErrors()->getIterator())
                    ),
                ),
                'error',
                !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
            );
        }
    }

    /**
     * Show popup with EP Event details.
     */
    private function showEventDetailsPopup(int $eventId): void
    {
        if (empty($eventId)) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        /** @var Ep_events_Model $eventsModel */
        $eventsModel = model(Ep_events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId],
            'with'          => ['type', 'country', 'state', 'city', 'category', 'speaker', 'partners'],
        ]))) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        $event['tags'] = empty($event['tags']) ? [] : array_filter(explode(';', $event['tags']), 'trim');
        $event['imageUrl'] = $this->storage->url(
            EpEventFilePathGenerator::mainImagePath($event['id'], $event['main_image'] ?: 'no-image.jpg')
        );
        if (!empty($event['speaker'])) {
            $event['speaker']['imageUrl'] = $this->storage->url(EpEventSpeakersFilePathGenerator::mainImagePath(
                (string) $event['speaker']['id'],
                $event['speaker']['photo'] ?: 'no-image.jpg'
            ));
        }
        if (!empty($event['partners'])) {
            $event['partners'] = $event['partners']->map(fn (array $partner) => array_merge($partner, [
                'imageUrl' => $this->storage->url(EpEventPartnersFilePathGenerator::mainImagePath(
                    (int) $partner['id'],
                    $partner['image'] ?: 'no-image.jpg'
                ))
            ]));
        }

        views(['admin/ep_events/details_view'], compact('event'));
    }

    /**
     * Show popup with Agenda.
     */
    private function showAgendaPopup(int $eventId): void
    {
        if (empty($eventId)) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        /** @var Ep_events_Model $eventsModel */
        $eventsModel = model(Ep_events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId, 'type' => self::OFFLINE_EVENT_TYPE_ID],
        ]))) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        views(
            ['admin/ep_events/agenda_view'],
            [
                'submitFormUrl' => __SITE_URL . 'ep_events/ajax_operations/agenda/' . $eventId,
                'event'         => $event,
            ]
        );
    }

    /**
     * Show popup with recommended image.
     */
    private function showRecommendedImagePopup(int $eventId): void
    {
        if (empty($eventId)) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        /** @var Ep_events_Model $eventsModel */
        $eventsModel = model(Ep_events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId],
        ]))) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        $moduleRecommended = 'ep_events.recommended';
        $mimeRecommendedProperties = getMimePropertiesFromFormats(config("img.{$moduleRecommended}.rules.format"));

        $fileupload_crop = [
            'title_text_popup'       => 'Recommended image',
            'btn_text_save_picture'  => 'Set new Recommended image',
            'preview_fanacy'         => false,
            'croppper_limit_by_min'  => true,
            'rules'                  => config("img.{$moduleRecommended}.rules"),
            'url'                    => [
                'upload' => __SITE_URL . 'ep_events/ajax_operations/upload_recommended_image',
            ],
            'accept'                 => arrayGet($mimeRecommendedProperties, 'accept'),
        ];

        $fileupload_crop['link_main_image'] = $fileupload_crop['link_thumb_main_image'] = $this->storage->url(
            EpEventFilePathGenerator::recomendedImagePath($event['id'], $event['recommended_image'] ?: 'no-image.jpeg')
        );

        views(
            ['admin/ep_events/recommended_image_form_view'],
            [
                'submitFormUrl'         => __SITE_URL . 'ep_events/ajax_operations/edit_recommended_image/' . $event['id'],
                'event'                 => $event,
                'fileupload_crop'       => $fileupload_crop,
            ]
        );
    }

    /**
     * Show popup for event promotion.
     */
    private function showEventPromotionPopup(array $event): void
    {
        $currentDate = new DateTimeImmutable();
        if ($event['start_date'] <= $currentDate) {
            messageInModal('This event is no longer eligible for promotion - it has already started.');
        }

        views(['admin/ep_events/promote_event_form'], [
            'actionUrl'      => getUrlForGroup("ep_events/ajax_operations/promote_event/{$event['id']}"),
            'minimumDate'    => $currentDate,
            'maximumDate'    => $event['start_date'],
        ]);
    }

    /**
     * Show the popup form that allows to add online event.
     */
    private function showAddOnlineEventPopup(): void
    {
        /** @var Ep_Events_Categories_Model $eventCategoriesModel */
        $eventCategoriesModel = model(Ep_Events_Categories_Model::class);

        views(
            ['admin/ep_events/online_event_form_view'],
            [
                'fileupload_crop_multiple'  => $this->uploadParamsMultipleImage(),
                'fileupload_crop'           => $this->uploadParamsMainImage(),
                'eventCategories'           => $eventCategoriesModel->findAll(),
                'submitFormUrl'             => __SITE_URL . 'ep_events/ajax_operations/add_online_event',
            ]
        );
    }

    /**
     * Show the popup form that allows to edit online event.
     */
    private function showEditOnlineEventPopup(int $eventId): void
    {
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => [
                'id'    => $eventId,
                'type'  => self::ONLINE_EVENT_TYPE_ID
            ],
            'with'          => ['gallery'],
        ]))) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        $event['tags'] = empty($event['tags']) ? [] : array_filter(explode(';', $event['tags']), 'trim');

        /** @var Ep_Events_Categories_Model $eventCategoriesModel */
        $eventCategoriesModel = model(Ep_Events_Categories_Model::class);

        views(
            ['admin/ep_events/online_event_form_view'],
            [
                'fileupload_crop_multiple'  => $this->uploadParamsMultipleImage($event),
                'fileupload_crop'           => $this->uploadParamsMainImage(['id' => $event['id'], 'main_image' => $event['main_image']]),
                'eventCategories'           => $eventCategoriesModel->findAll(),
                'submitFormUrl'             => __SITE_URL . 'ep_events/ajax_operations/edit_online_event/' . $eventId,
                'event'                     => $event,
            ]
        );
    }

    /**
     * Show the popup form that allows to add offline event.
     */
    private function showAddOfflineEventPopup(): void
    {
        /** @var Ep_Events_Categories_Model $eventCategoriesModel */
        $eventCategoriesModel = model(Ep_Events_Categories_Model::class);

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);

        /** @var Ep_Events_Partners_Model $eventPartnersModel */
        $eventPartnersModel = model(Ep_Events_Partners_Model::class);

        views(
            ['admin/ep_events/offline_event_form_view'],
            [
                'fileupload_crop_multiple'  => $this->uploadParamsMultipleImage(),
                'fileupload_crop'           => $this->uploadParamsMainImage(),
                'eventCategories'           => $eventCategoriesModel->findAll(),
                'eventPartners'             => $eventPartnersModel->findAll(),
                'submitFormUrl'             => __SITE_URL . 'ep_events/ajax_operations/add_offline_event',
                'location'                  => [
                    'countries' => $countryModel->fetch_port_country(),
                    'states'    => null,
                    'city'      => null,
                ],
            ]
        );
    }

    /**
     * Show the popup form that allows to edit offline event.
     */
    private function showEditOfflineEventPopup(int $eventId): void
    {
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId, self::OFFLINE_EVENT_TYPE_ID],
            'with'          => ['partners', 'gallery'],
        ]))) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        $event['tags'] = empty($event['tags']) ? [] : array_filter(explode(';', $event['tags']), 'trim');
        $event['partners'] = empty($event['partners']) ? [] : array_column($event['partners']->toArray(), null, 'id');

        /** @var Ep_Events_Categories_Model $eventCategoriesModel */
        $eventCategoriesModel = model(Ep_Events_Categories_Model::class);

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);

        /** @var Ep_Events_Partners_Model $eventPartnersModel */
        $eventPartnersModel = model(Ep_Events_Partners_Model::class);

        views(
            ['admin/ep_events/offline_event_form_view'],
            [
                'fileupload_crop_multiple'  => $this->uploadParamsMultipleImage($event),
                'fileupload_crop'           => $this->uploadParamsMainImage(['id' => $event['id'], 'main_image' => $event['main_image']]),
                'eventCategories'           => $eventCategoriesModel->findAll(),
                'eventPartners'             => $eventPartnersModel->findAll(),
                'submitFormUrl'             => __SITE_URL . 'ep_events/ajax_operations/edit_offline_event/' . $eventId,
                'event'                     => $event,
                'location'                  => [
                    'countries' => $countryModel->fetch_port_country(),
                    'states'    => empty($event['id_country']) ? null : $countryModel->get_states($event['id_country']),
                    'city'      => empty($event['id_city']) ? null : $countryModel->get_city($event['id_city']),
                ],
            ]
        );
    }

    /**
     * Show the popup form that allows to add webinar.
     */
    private function showAddWebinarPopup(): void
    {
        /** @var Ep_Events_Categories_Model $eventCategoriesModel */
        $eventCategoriesModel = model(Ep_Events_Categories_Model::class);
        /** @var Ep_Events_Speakers_Model $eventSpeakersModel */
        $eventSpeakersModel = model(Ep_Events_Speakers_Model::class);

        views(
            ['admin/ep_events/webinar_form_view'],
            [
                'fileupload_crop_multiple'  => $this->uploadParamsMultipleImage(),
                'fileupload_crop'           => $this->uploadParamsMainImage(),
                'eventCategories'           => $eventCategoriesModel->findAll(),
                'eventSpeakers'             => $eventSpeakersModel->findAll(),
                'submitFormUrl'             => __SITE_URL . 'ep_events/ajax_operations/add_webinar',
            ]
        );
    }

    /**
     * Show the popup form that allows to edit webinar.
     */
    private function showEditWebinarPopup(int $eventId): void
    {
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId, 'type' => self::WEBINAR_TYPE_ID],
            'with'          => ['gallery'],
        ]))) {
            messageInModal(translate('ep_events_administration_event_id_not_found'));
        }

        $event['tags'] = empty($event['tags']) ? [] : array_filter(explode(';', $event['tags']), 'trim');

        /** @var Ep_Events_Categories_Model $eventCategoriesModel */
        $eventCategoriesModel = model(Ep_Events_Categories_Model::class);

        /** @var Ep_Events_Speakers_Model $eventSpeakersModel */
        $eventSpeakersModel = model(Ep_Events_Speakers_Model::class);

        views(
            ['admin/ep_events/webinar_form_view'],
            [
                'fileupload_crop_multiple'  => $this->uploadParamsMultipleImage($event),
                'fileupload_crop'           => $this->uploadParamsMainImage(['id' => $event['id'], 'main_image' => $event['main_image']]),
                'eventCategories'           => $eventCategoriesModel->findAll(),
                'eventSpeakers'             => $eventSpeakersModel->findAll(),
                'submitFormUrl'             => __SITE_URL . 'ep_events/ajax_operations/edit_webinar/' . $eventId,
                'event'                     => $event,
            ]
        );
    }

    private function showEmailThisEventPopup(int $eventId): void
    {
        /** @var Elasticsearch_Ep_Events_Model $elasticsearchEventsModel */
        $elasticsearchEventsModel = model(Elasticsearch_Ep_Events_Model::class);

        $event = array_shift($elasticsearchEventsModel->getEvents(['id' => $eventId]));
        if (empty($event)) {
            messageInModal(translate('systmess_error_invalid_data'));
        }

        views(
            ['new/ep_events/email_share_view'],
            [
                'type'    => 'email',
                'eventId' => $eventId,
                'action'  => 'ep_events/ajax_operations/email_this',
            ]
        );
    }

    private function showShareEventPopup(int $eventId, int $userId): void
    {
        /** @var Elasticsearch_Ep_Events_Model $elasticsearchEventsModel */
        $elasticsearchEventsModel = model(Elasticsearch_Ep_Events_Model::class);

        $event = array_shift($elasticsearchEventsModel->getEvents(['id' => $eventId]));
        if (empty($event)) {
            messageInModal(translate('systmess_error_invalid_data'));
        }

        /** @var User_Followers_Model $userFollowersModel */
        $userFollowersModel = model(User_Followers_Model::class);

        if (0 === $userFollowersModel->countAllBy([
            'scopes' => [
                'followedUser' => $userId,
            ],
        ])) {
            messageInModal(translate('systmess_error_share_event_no_followers'));
        }

        views(
            ['new/ep_events/email_share_view'],
            [
                'type'    => 'share',
                'eventId' => $eventId,
                'action'  => 'ep_events/ajax_operations/share_this',
            ]
        );
    }

    /**
     * Action for processing form to adding the online event.
     *
     * @throws Exception
     */
    private function addOnlineEvent(): void
    {
        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new OnlineEventValidator($adapter);
        if (!$validator->validate($request->all())) {
            jsonResponse(
                array_map(
                    fn (ConstraintViolation $violation) => $violation->getMessage(),
                    iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        $mainImage = $request->get('images_main');
        $newMainImageName = pathinfo($mainImage, PATHINFO_BASENAME);

        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);

        /** @var TinyMVC_Library_Cleanhtml $sanitizerLibrary */
        $sanitizerLibrary = library(TinyMVC_Library_Cleanhtml::class);
        $sanitizerLibrary->defaultTextarea(['style' => 'text-align']);
        $sanitizerLibrary->addAdditionalTags('<h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        $isPublishedEvent = (int) filter_var($request->get('published'), FILTER_VALIDATE_BOOLEAN);

        if (empty($eventId = $eventsModel->insertOne([
            'short_description' => $request->get('short_description'),
            'published_date'    => $isPublishedEvent ? (new DateTime())->format('Y/m/d H:i:s') : null,
            'is_published'      => $isPublishedEvent,
            'id_category'       => $request->getInt('category'),
            'description'       => $sanitizerLibrary->sanitize($request->get('description')),
            'why_attend'        => $sanitizerLibrary->sanitize($request->get('why_attend')),
            'main_image'        => $newMainImageName,
            'start_date'        => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_start')))->format('Y/m/d H:i:s'),
            'end_date'          => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_end')))->format('Y/m/d H:i:s'),
            'id_type'           => self::ONLINE_EVENT_TYPE_ID,
            'title'             => $request->get('title'),
            'tags'              => implode(';', $request->get('tags')),
        ]))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        $uploadedImages = [];
        $moduleMain = 'ep_events.main';

        $mainImageFolderPath = EpEventFilePathGenerator::mainFolderPath((int) $eventId);
        if (!$this->storage->fileExists($mainImageFolderPath)) {
            $this->storage->createDirectory($mainImageFolderPath);
        }

        try {
            $file = $this->tempStorage->read($mainImage);
        } catch (UnableToReadFile $error) {
            $this->rollBackEvent($eventId);

            jsonResponse(translate('ep_events_administration_error_upload_main_image'));
        }

        try {
            $mainImageFullPath = EpEventFilePathGenerator::mainImagePath((int) $eventId, $newMainImageName);

            $this->storage->write($mainImageFullPath, $file);
        } catch (UnableToWriteFile $error) {
            $this->rollBackEvent($eventId);

            jsonResponse(translate('ep_events_administration_error_upload_main_image'));
        }

        $uploadedImages[] = $mainImageFullPath;

        $thumbs = config("img.{$moduleMain}.thumbs");
        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumbTempName = str_replace('{THUMB_NAME}', $newMainImageName, $thumb['name']);
                $thumbTempPath = str_replace($newMainImageName, $thumbTempName, $mainImage);

                try {
                    $file = $this->tempStorage->read($thumbTempPath);
                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                }

                try {
                    $thumbNewPath = EpEventFilePathGenerator::mainImagePath((int) $eventId, $thumbTempName);

                    $this->storage->write($thumbNewPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                }

                $uploadedImages[] = $thumbNewPath;
            }
        }

        $imagesToOptimization[] = [
            'file_path'	=> $mainImageFullPath,
            'context'   => ['eventId' => $eventId],
            'type'      => 'ep_event_main_image',
        ];

        $imagesGallery = (array) $request->get('images_multiple');
        if (!empty($imagesGallery)) {
            $moduleGallery = 'ep_events.gallery';

            $galleryLimit = config("img.{$moduleGallery}.limit", 1);
            if (count($imagesGallery) > $galleryLimit) {
                jsonResponse(translate('ep_events_administration_error_gallery_limit_exceeded', ['{{LIMIT_IMAGES}}' => $galleryLimit]));
            }

            $galleryFolderPath = EpEventFilePathGenerator::galleryFolderPath((string) $eventId);
            if (!$this->storage->fileExists($galleryFolderPath)) {
                $this->storage->createDirectory($galleryFolderPath);
            }

            $eventGallery = [];

            foreach ($imagesGallery as $image) {
                $newImageName = pathinfo($image, PATHINFO_BASENAME);

                try {
                    $file = $this->tempStorage->read($image);
                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                try {
                    $imageFullPath = EpEventFilePathGenerator::galleryImagePath((int) $eventId, $newImageName);

                    $this->storage->write($imageFullPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                $uploadedImages[] = $imageFullPath;
                $eventGallery[] = [
                    'id_event'  => $eventId,
                    'name'      => $newImageName,
                ];

                $thumbs = config("img.{$moduleGallery}.thumbs");
                if (!empty($thumbs)) {
                    $galleryImageTempPath = pathinfo($image, PATHINFO_DIRNAME);

                    foreach ($thumbs as $thumb) {
                        $thumbTempName = str_replace('{THUMB_NAME}', $newImageName, $thumb['name']);
                        $thumbTempPath = $galleryImageTempPath . '/' . $thumbTempName;

                        try {
                            $file = $this->tempStorage->read($thumbTempPath);
                        } catch (UnableToReadFile $error) {
                            $this->rollBackEvent($eventId, $uploadedImages);
                            jsonResponse($image);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        try {
                            $thumbNewPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $thumbTempName);

                            $this->storage->write($thumbNewPath, $file);
                        } catch (UnableToWriteFile $error) {
                            $this->rollBackEvent($eventId, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        $uploadedImages[] = $thumbNewPath;
                    }
                }

                $imagesToOptimization[] = [
                    'file_path'	=> $thumbNewPath,
                    'context'   => ['eventId' => $eventId],
                    'type'      => 'ep_event_gallery_image',
                ];
            }

            /** @var Ep_Events_Images_Model $eventImagesModel */
            $eventImagesModel = model(Ep_Events_Images_Model::class);
            if (count($eventGallery) != $eventImagesModel->insertMany($eventGallery)) {
                $this->rollBackEvent($eventId, $uploadedImages);

                jsonResponse(translate('systmess_internal_server_error'));
            }
        }

        if (!empty($imagesToOptimization)) {
            /** @var Image_optimization_Model $optimizationImagesModel */
            $optimizationImagesModel = model(Image_optimization_Model::class);
            $optimizationImagesModel->insertMany($imagesToOptimization);
        }

        jsonResponse(translate('ep_events_administration_succes_added_online_event'), 'success');
    }

    /**
     * Action for processing form to editing the online event.
     *
     * @throws Exception
     */
    private function editOnlineEvent(int $eventId): void
    {
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId, 'type' => self::ONLINE_EVENT_TYPE_ID],
            'with'          => ['gallery'],
        ]))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        $request = request()->request;
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new OnlineEventValidator($adapter, false);
        if (!$validator->validate($request->all())) {
            jsonResponse(
                array_map(
                    fn (ConstraintViolation $violation) => $violation->getMessage(),
                    iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        $removedGallery = (array) $request->get('images_multiple_removed');
        $countRemovedImages = empty($removedGallery) ? 0 : count($removedGallery);
        if (!empty($countRemovedImages)) {
            $alreadyUploadedImages = empty($event['gallery']) ? [] : array_column($event['gallery']->toArray(), 'name');

            $eventGalleryToDelete = [];
            foreach ($removedGallery as $removedImage) {
                if (!in_array($removedImage, $alreadyUploadedImages)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $eventGalleryToDelete[] = $removedImage;
            }
        }

        $uploadedImages = [];
        $imagesGallery = (array) $request->get('images_multiple');
        $moduleGallery = 'ep_events.gallery';

        if (!empty($imagesGallery)) {
            $galleryLimit = config("img.{$moduleGallery}.limit", 1);
            $countAlreadyUploadedImages = empty($event['gallery']) ? 0 : $event['gallery']->count();
            $countNewImages = count($imagesGallery);

            if (($countAlreadyUploadedImages - $countRemovedImages) + $countNewImages > $galleryLimit) {
                jsonResponse(translate('ep_events_administration_error_gallery_limit_exceeded', ['{{LIMIT_IMAGES}}' => $galleryLimit]));
            }

            $eventGalleryToInsert = [];

            $galleryFolderPath = EpEventFilePathGenerator::galleryFolderPath((string) $eventId);
            if (!$this->storage->fileExists($galleryFolderPath)) {
                $this->storage->createDirectory($galleryFolderPath);
            }

            foreach ($imagesGallery as $image) {
                $newImageName = uniqid() . '.' . pathinfo($image, PATHINFO_EXTENSION);
                $newImageFullPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $newImageName);

                try {
                    $file = $this->tempStorage->read($image);
                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent(null, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                try {
                    $this->storage->write($newImageFullPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackEvent(null, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                $uploadedImages[] = $newImageFullPath;

                $eventGalleryToInsert[] = [
                    'id_event'  => $eventId,
                    'name'      => $newImageName,
                ];

                $thumbs = config("img.{$moduleGallery}.thumbs");
                if (!empty($thumbs)) {
                    foreach ($thumbs as $thumb) {
                        $thumbTempName = str_replace('{THUMB_NAME}', $newImageName, $thumb['name']);
                        $thumbTempPath = str_replace($newImageName, $thumbTempName, $image);

                        try {
                            $file = $this->tempStorage->read($thumbTempPath);
                        } catch (UnableToReadFile $error) {
                            $this->rollBackEvent(null, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        try {
                            $thumbNewPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $thumbTempName);

                            $this->storage->write($thumbNewPath, $file);
                        } catch (UnableToWriteFile $error) {
                            $this->rollBackEvent(null, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        $uploadedImages[] = $thumbNewPath;
                    }
                }

                $imagesToOptimization[] = [
                    'file_path'	=> $thumbNewPath,
                    'context'   => ['eventId' => $eventId],
                    'type'      => 'ep_event_gallery_image',
                ];
            }
        }

        $imagesMain = $request->get('images_main');
        if (!empty($imagesMain)) {
            $newMainImageName = uniqid() . '.' . pathinfo($imagesMain, PATHINFO_EXTENSION);

            $moduleMain = 'ep_events.main';
            $mainImageFolderPath = EpEventFilePathGenerator::mainFolderPath((string) $eventId);
            if (!$this->storage->fileExists($mainImageFolderPath)) {
                $this->storage->createDirectory($mainImageFolderPath);
            }

            try {
                $file = $this->tempStorage->read($imagesMain);
            } catch (UnableToReadFile $error) {
                $this->rollBackEvent(null, $uploadedImages);

                jsonResponse(translate('ep_events_administration_error_upload_main_image'));
            }

            try {
                $mainImageFullPath = EpEventFilePathGenerator::mainImagePath((string) $eventId, $newMainImageName);

                $this->storage->write($mainImageFullPath, $file);
            } catch (UnableToWriteFile $error) {
                $this->rollBackEvent(null, $uploadedImages);

                jsonResponse(translate('ep_events_administration_error_upload_main_image'));
            }

            $uploadedImages[] = $mainImageFullPath;

            $thumbs = config("img.{$moduleMain}.thumbs");
            if (!empty($thumbs)) {
                foreach ($thumbs as $thumb) {
                    $thumbTempName = str_replace('{THUMB_NAME}', $newMainImageName, $thumb['name']);
                    $thumbTempPath = str_replace($newMainImageName, $thumbTempName, $imagesMain);

                    try {
                        $file = $this->tempStorage->read($thumbTempPath);
                    } catch (UnableToReadFile $error) {
                        $this->rollBackEvent($eventId, $uploadedImages);

                        jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                    }

                    try {
                        $thumbNewPath = EpEventFilePathGenerator::mainImagePath((int) $eventId, $thumbTempName);

                        $this->storage->write($thumbNewPath, $file);
                    } catch (UnableToWriteFile $error) {
                        $this->rollBackEvent($eventId, $uploadedImages);

                        jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                    }

                    $uploadedImages[] = $thumbNewPath;
                }
            }

            $imagesToOptimization[] = [
                'file_path'	=> $thumbNewPath,
                'context'   => ['eventId' => $eventId],
                'type'      => 'ep_event_main_image',
            ];
        }
        //endregion processing main image

        /** @var Ep_Events_Images_Model $eventImagesModel */
        $eventImagesModel = model(Ep_Events_Images_Model::class);
        if (!empty($eventGalleryToDelete)) {
            $eventImagesModel->deleteAllBy([
                'conditions' => [
                    'eventId'   => $eventId,
                    'images'    => $eventGalleryToDelete,
                ],
            ]);

            foreach ($eventGalleryToDelete as $imageToDelete) {
                try {
                    $this->storage->delete(
                        EpEventFilePathGenerator::galleryImagePath((string) $eventId, $imageToDelete)
                    );
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_pictures_error_delete_message'));
                }

                array_map(function ($image) use ($imageToDelete, $eventId) {
                    try {
                        $this->storage->delete(
                            EpEventFilePathGenerator::galleryImagePath((string) $eventId, str_replace('{THUMB_NAME}', $imageToDelete, $image['name']))
                        );
                    } catch (UnableToDeleteFile $error) {
                        jsonResponse(translate('events_pictures_error_delete_message'));
                    }
                }, config("img.ep_events.main.thumbs") ?? []);
            }
        }

        if (!empty($eventGalleryToInsert) && count($eventGalleryToInsert) != $eventImagesModel->insertMany($eventGalleryToInsert)) {
            $this->rollBackEvent(null, $uploadedImages);

            jsonResponse(translate('systmess_internal_server_error'));
        }

        /** @var TinyMVC_Library_Cleanhtml $sanitizerLibrary */
        $sanitizerLibrary = library(TinyMVC_Library_Cleanhtml::class);
        $sanitizerLibrary->defaultTextarea(['style' => 'text-align']);
        $sanitizerLibrary->addAdditionalTags('<h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        $publishedDate = null;
        $isPublishedEvent = (int) filter_var($request->get('published'), FILTER_VALIDATE_BOOLEAN);
        if ($isPublishedEvent) {
            $publishedDate = $event['is_published'] ? $event['published_date'] : (new DateTime())->format('Y-m-d H:i:s');
        }

        $updatedEvent = [
            'short_description' => $request->get('short_description'),
            'published_date'    => $publishedDate,
            'is_published'      => $isPublishedEvent,
            'id_category'       => $request->getInt('category'),
            'description'       => $sanitizerLibrary->sanitize($request->get('description')),
            'why_attend'        => $sanitizerLibrary->sanitize($request->get('why_attend')),
            'main_image'        => $newMainImageName ?? $event['main_image'],
            'start_date'        => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_start')))->format('Y-m-d H:i:s'),
            'end_date'          => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_end')))->format('Y-m-d H:i:s'),
            'title'             => $request->get('title'),
            'tags'              => implode(';', $request->get('tags')),
        ];

        if (!$eventsModel->updateOne($eventId, $updatedEvent)) {
            $this->rollBackEvent(null, $uploadedImages);

            jsonResponse(translate('systmess_internal_server_error'));
        }

        if (isset($newMainImageName)) {
            try {
                $this->storage->delete(
                    EpEventFilePathGenerator::mainImagePath($eventId, $event['main_image'])
                );
            } catch (UnableToDeleteFile $error) {
                jsonResponse(translate('events_pictures_error_delete_message'));
            }

            array_map(function ($image) use ($event, $eventId) {
                try {
                    $this->storage->delete(
                        EpEventFilePathGenerator::mainImagePath($eventId, str_replace('{THUMB_NAME}', $event['main_image'], $image['name']))
                    );
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_pictures_error_delete_message'));
                }
            }, config("img.ep_events.main.thumbs") ?? []);
        }

        if (!empty($imagesToOptimization)) {
            /** @var Image_optimization_Model $optimizationImagesModel */
            $optimizationImagesModel = model(Image_optimization_Model::class);
            $optimizationImagesModel->insertMany($imagesToOptimization);
        }

        //Update calendar events
        $this->container->get(CalendarEpEventsService::class)->actualizeEvent($eventId, $event, $updatedEvent);

        jsonResponse(translate('ep_events_administration_succes_edited_online_event'), 'success');
    }

    /**
     * Action for processing form to adding the offline event.
     *
     * @throws Exception
     */
    private function addOfflineEvent(): void
    {
        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validators = [
            new OfflineEventValidator($adapter),
            new EpEventAddressValidator($adapter, null, null, [
                'country'       => 'country',
                'state'         => 'state',
                'city'          => 'city',
                'address'       => 'address',
            ]),
        ];

        $validator = new AggregateValidator($validators);
        if (!$validator->validate($request->all())) {
            jsonResponse(
                array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        $mainImage = $request->get('images_main');
        $newMainImageName = pathinfo($mainImage, PATHINFO_BASENAME);

        //region insert event in DB
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);

        /** @var TinyMVC_Library_Cleanhtml $sanitizerLibrary */
        $sanitizerLibrary = library(TinyMVC_Library_Cleanhtml::class);
        $sanitizerLibrary->defaultTextarea(['style' => 'text-align']);
        $sanitizerLibrary->addAdditionalTags('<h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        $isPublishedEvent = (int) filter_var($request->get('published'), FILTER_VALIDATE_BOOLEAN);

        if (empty($eventId = $eventsModel->insertOne([
            'nr_of_participants'    => $request->getInt('nr_of_participants'),
            'short_description'     => $request->get('short_description'),
            'published_date'        => $isPublishedEvent ? (new DateTime())->format('Y-m-d H:i:s') : null,
            'is_published'          => $isPublishedEvent,
            'ticket_price'          => (float) $request->get('ticket_price'),
            'id_category'           => $request->getInt('category'),
            'description'           => $sanitizerLibrary->sanitize($request->get('description')),
            'id_country'            => $request->getInt('country'),
            'why_attend'            => $sanitizerLibrary->sanitize($request->get('why_attend')),
            'main_image'            => $newMainImageName,
            'start_date'            => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_start')))->format('Y/m/d H:i:s'),
            'end_date'              => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_end')))->format('Y/m/d H:i:s'),
            'id_state'              => $request->getInt('state'),
            'id_city'               => $request->getInt('city'),
            'address'               => $request->get('address'),
            'id_type'               => self::OFFLINE_EVENT_TYPE_ID,
            'title'                 => $request->get('title'),
            'tags'                  => implode(';', $request->get('tags')),
        ]))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        $uploadedImages = [];
        $moduleMain = 'ep_events.main';

        $newMainImageFullPath = EpEventFilePathGenerator::mainImagePath((string) $eventId, $newMainImageName);

        $mainImageFolderPath = EpEventFilePathGenerator::mainFolderPath((string) $eventId);
        if (!$this->storage->fileExists($mainImageFolderPath)) {
            $this->storage->createDirectory($mainImageFolderPath);
        }

        try {
            $file = $this->tempStorage->read($mainImage);
        } catch (UnableToReadFile $error) {
            $this->rollBackEvent($eventId);

            jsonResponse(translate('ep_events_administration_error_upload_main_image'));
        }

        try {
            $this->storage->write($newMainImageFullPath, $file);
        } catch (UnableToReadFile $error) {
            $this->rollBackEvent($eventId);

            jsonResponse(translate('ep_events_administration_error_upload_main_image'));
        }

        $thumbs = config("img.{$moduleMain}.thumbs");
        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumbTempName = str_replace('{THUMB_NAME}', $newMainImageName, $thumb['name']);
                $thumbTempPath = str_replace($newMainImageName, $thumbTempName, $mainImage);

                try {
                    $file = $this->tempStorage->read($thumbTempPath);

                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                }

                try {
                    $thumbNewPath = EpEventFilePathGenerator::mainImagePath((int) $eventId, $thumbTempName);

                    $this->storage->write($thumbNewPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                }

                $uploadedImages[] = $thumbNewPath;
            }
        }

        $imagesToOptimization[] = [
            'file_path'	=> $newMainImageFullPath,
            'context'   => ['eventId' => $eventId],
            'type'      => 'ep_event_main_image',
        ];

        $uploadedImages[] = $newMainImageFullPath;

        $moduleGallery = 'ep_events.gallery';

        $imagesGallery = (array) $request->get('images_multiple');
        if (!empty($imagesGallery)) {
            $galleryFolderPath = EpEventFilePathGenerator::galleryFolderPath((string) $eventId);
            if(!$this->storage->fileExists($galleryFolderPath)) {
                $this->storage->createDirectory($galleryFolderPath);
            }

            $eventGallery = [];

            foreach ($imagesGallery as $image) {
                $newImageName = pathinfo($image, PATHINFO_BASENAME);

                try {
                    $file = $this->tempStorage->read($image);
                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                try {
                    $imageFullPath = EpEventFilePathGenerator::galleryImagePath((int) $eventId, $newImageName);

                    $this->storage->write($imageFullPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                $uploadedImages[] = $imageFullPath;
                $eventGallery[] = [
                    'id_event'  => $eventId,
                    'name'      => $newImageName,
                ];

                $thumbs = config("img.{$moduleGallery}.thumbs");
                if (!empty($thumbs)) {
                    $galleryImageTempPath = pathinfo($image, PATHINFO_DIRNAME);

                    foreach ($thumbs as $thumb) {
                        $thumbTempName = str_replace('{THUMB_NAME}', $newImageName, $thumb['name']);
                        $thumbTempPath = $galleryImageTempPath . '/' . $thumbTempName;

                        try {
                            $file = $this->tempStorage->read($thumbTempPath);

                        } catch (UnableToReadFile $error) {
                            $this->rollBackEvent($eventId, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        try {
                            $thumbNewPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $thumbTempName);

                            $this->storage->write($thumbNewPath, $file);
                        } catch (UnableToWriteFile $error) {
                            $this->rollBackEvent($eventId, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        $uploadedImages[] = $thumbNewPath;
                    }
                }

                $imagesToOptimization[] = [
                    'file_path'	=> $thumbNewPath,
                    'context'   => ['eventId' => $eventId],
                    'type'      => 'ep_event_gallery_image',
                ];
            }

            /** @var Ep_Events_Images_Model $eventImagesModel */
            $eventImagesModel = model(Ep_Events_Images_Model::class);
            if (count($eventGallery) != $eventImagesModel->insertMany($eventGallery)) {
                $this->rollBackEvent($eventId, $uploadedImages);

                jsonResponse(translate('systmess_internal_server_error'));
            }
        }

        if (!empty($eventPartnersRecords = array_map(
            fn ($partnerId) => ['id_event' => $eventId, 'id_partner' => (int) $partnerId],
            (array) $request->get('partners')
        ))) {
            /** @var Ep_Events_Partners_Relations_Model $epEventsPartnersRelationsModel */
            $epEventsPartnersRelationsModel = model(Ep_Events_Partners_Relations_Model::class);
            if (count($eventPartnersRecords) != $epEventsPartnersRelationsModel->insertMany($eventPartnersRecords)) {
                $this->rollBackEvent($eventId, $uploadedImages);

                jsonResponse(translate('systmess_internal_server_error'));
            }
        }

        if (!empty($imagesToOptimization)) {
            /** @var Image_optimization_Model $optimizationImagesModel */
            $optimizationImagesModel = model(Image_optimization_Model::class);
            $optimizationImagesModel->insertMany($imagesToOptimization);
        }

        jsonResponse(translate('ep_events_administration_succes_added_offline_event'), 'success');
    }

    /**
     * Action for processing form to editing the offline event.
     *
     * @throws Exception
     */
    private function editOfflineEvent(int $eventId): void
    {
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId, 'type' => self::OFFLINE_EVENT_TYPE_ID],
            'with'          => ['partners', 'gallery'],
        ]))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validators = [
            new OfflineEventValidator($adapter, false),
            new EpEventAddressValidator($adapter, null, null, [
                'country'       => 'country',
                'state'         => 'state',
                'city'          => 'city',
                'address'       => 'address',
            ]),
        ];

        $validator = new AggregateValidator($validators);
        if (!$validator->validate($request->all())) {
            jsonResponse(
                array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        $removedGallery = (array) $request->get('images_multiple_removed');
        $countRemovedImages = empty($removedGallery) ? 0 : count($removedGallery);
        if (!empty($countRemovedImages)) {
            $alreadyUploadedImages = empty($event['gallery']) ? [] : array_column($event['gallery']->toArray(), 'name');

            $eventGalleryToDelete = [];
            foreach ($removedGallery as $removedImage) {
                if (!in_array($removedImage, $alreadyUploadedImages)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $eventGalleryToDelete[] = $removedImage;
            }
        }

        $uploadedImages = [];

        $moduleGallery = 'ep_events.gallery';
        $imagesGallery = (array) $request->get('images_multiple');
        if (!empty($imagesGallery)) {
            $galleryLimit = config("img.{$moduleGallery}.limit", 1);
            $countAlreadyUploadedImages = empty($event['gallery']) ? 0 : $event['gallery']->count();
            $countNewImages = count($imagesGallery);

            if (($countAlreadyUploadedImages - $countRemovedImages) + $countNewImages > $galleryLimit) {
                jsonResponse(translate('ep_events_administration_error_gallery_limit_exceeded', ['{{LIMIT_IMAGES}}' => $galleryLimit]));
            }

            $eventGalleryToInsert = [];

            $galleryFolderPath = EpEventFilePathGenerator::galleryFolderPath((string) $eventId);
            if (!$this->storage->fileExists($galleryFolderPath)) {
                $this->storage->createDirectory($galleryFolderPath);
            }

            foreach ($imagesGallery as $image) {
                $newImageName = uniqid() . '.' . pathinfo($image, PATHINFO_EXTENSION);

                try {
                    $file = $this->tempStorage->read($image);
                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent(null, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                try {
                    $newImageFullPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $newImageName);

                    $this->storage->write($newImageFullPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackEvent(null, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                $uploadedImages[] = $newImageFullPath;

                $eventGalleryToInsert[] = [
                    'id_event'  => $eventId,
                    'name'      => $newImageName,
                ];

                $thumbs = config("img.{$moduleGallery}.thumbs");
                if (!empty($thumbs)) {
                    foreach ($thumbs as $thumb) {
                        $thumbTempName = str_replace('{THUMB_NAME}', $newImageName, $thumb['name']);
                        $thumbTempPath = str_replace($newImageName, $thumbTempName, $image);

                        try {
                            $file = $this->tempStorage->read($thumbTempPath);

                        } catch (UnableToReadFile $error) {
                            $this->rollBackEvent(null, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        try {
                            $thumbNewPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $thumbTempName);

                            $this->storage->write($thumbNewPath, $file);
                        } catch (UnableToWriteFile $error) {
                            $this->rollBackEvent(null, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        $uploadedImages[] = $thumbNewPath;
                    }
                }

                $imagesToOptimization[] = [
                    'file_path'	=> $newImageFullPath,
                    'context'   => ['eventId' => $eventId],
                    'type'      => 'ep_event_gallery_image',
                ];
            }
        }

        $imagesMain = $request->get('images_main');
        if (!empty($imagesMain)) {
            $newMainImageName = uniqid() . '.' . pathinfo($imagesMain, PATHINFO_EXTENSION);

            $moduleMain = 'ep_events.main';

            $mainImageFolderPath = EpEventFilePathGenerator::mainFolderPath((string) $eventId);
            if (!$this->storage->fileExists($mainImageFolderPath)) {
                $this->storage->createDirectory($mainImageFolderPath);
            }

            try {
                $file = $this->tempStorage->read($imagesMain);
            } catch (UnableToReadFile $error) {
                $this->rollBackEvent(null, $uploadedImages);

                jsonResponse(translate('ep_events_administration_error_upload_main_image'));
            }

            try {
                $mainImageFullPath = EpEventFilePathGenerator::mainImagePath((string) $eventId, $newMainImageName);

                $this->storage->write($mainImageFullPath, $file);
            } catch (UnableToWriteFile $error) {
                $this->rollBackEvent(null, $uploadedImages);

                jsonResponse(translate('ep_events_administration_error_upload_main_image'));
            }

            $thumbs = config("img.{$moduleMain}.thumbs");
            if (!empty($thumbs)) {
                foreach ($thumbs as $thumb) {
                    $thumbTempName = str_replace('{THUMB_NAME}', $newMainImageName, $thumb['name']);
                    $thumbTempPath = str_replace($newMainImageName, $thumbTempName, $imagesMain);

                    try {
                        $file = $this->tempStorage->read($thumbTempPath);

                    } catch (UnableToReadFile $error) {
                        $this->rollBackEvent(null, $uploadedImages);

                        jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                    }

                    try {
                        $thumbNewPath = EpEventFilePathGenerator::mainImagePath((int) $eventId, $thumbTempName);

                        $this->storage->write($thumbNewPath, $file);
                    } catch (UnableToWriteFile $error) {
                        $this->rollBackEvent(null, $uploadedImages);

                        jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                    }

                    $uploadedImages[] = $thumbNewPath;
                }
            }

            $uploadedImages[] = $mainImageFullPath;

            $imagesToOptimization[] = [
                'file_path'	=> $thumbNewPath,
                'context'   => ['eventId' => $eventId],
                'type'      => 'ep_event_main_image',
            ];
        }

        /** @var Ep_Events_Images_Model $eventImagesModel */
        $eventImagesModel = model(Ep_Events_Images_Model::class);
        if (!empty($eventGalleryToDelete)) {
            $eventImagesModel->deleteAllBy([
                'conditions' => [
                    'eventId'   => $eventId,
                    'images'    => $eventGalleryToDelete,
                ],
            ]);

            foreach ($eventGalleryToDelete as $imageToDelete) {
                try {
                    $this->storage->delete(
                        EpEventFilePathGenerator::mainImagePath($eventId, $imageToDelete)
                    );
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_pictures_error_delete_message'));
                }

                array_map(function ($item) use ($eventId, $imageToDelete) {
                    try {
                        $this->storage->delete(
                            EpEventFilePathGenerator::mainImagePath($eventId, str_replace('{THUMB_NAME}', $imageToDelete, $item['name']))
                        );
                    } catch (UnableToDeleteFile $error) {
                        jsonResponse(translate('events_pictures_error_delete_message'));
                    }
                }, config("img.{$moduleGallery}.thumbs") ?? []);
            }
        }

        if (!empty($eventGalleryToInsert) && count($eventGalleryToInsert) != $eventImagesModel->insertMany($eventGalleryToInsert)) {
            $this->rollBackEvent(null, $uploadedImages);

            jsonResponse(translate('systmess_internal_server_error'));
        }

        /** @var TinyMVC_Library_Cleanhtml $sanitizerLibrary */
        $sanitizerLibrary = library(TinyMVC_Library_Cleanhtml::class);
        $sanitizerLibrary->defaultTextarea(['style' => 'text-align']);
        $sanitizerLibrary->addAdditionalTags('<h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        $publishedDate = null;
        $isPublishedEvent = (int) filter_var($request->get('published'), FILTER_VALIDATE_BOOLEAN);
        if ($isPublishedEvent) {
            $publishedDate = $event['is_published'] ? $event['published_date'] : (new DateTime())->format('Y-m-d H:i:s');
        }

        $updatedEvent = [
            'nr_of_participants'    => $request->getInt('nr_of_participants'),
            'short_description'     => $request->get('short_description'),
            'published_date'        => $publishedDate,
            'is_published'          => $isPublishedEvent,
            'ticket_price'          => (float) $request->get('ticket_price'),
            'id_category'           => $request->getInt('category'),
            'description'           => $sanitizerLibrary->sanitize($request->get('description')),
            'id_country'            => $request->getInt('country'),
            'why_attend'            => $sanitizerLibrary->sanitize($request->get('why_attend')),
            'main_image'            => $newMainImageName ?? $event['main_image'],
            'start_date'            => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_start')))->format('Y-m-d H:i:s'),
            'end_date'              => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_end')))->format('Y-m-d H:i:s'),
            'id_state'              => $request->getInt('state'),
            'id_city'               => $request->getInt('city'),
            'address'               => $request->get('address'),
            'title'                 => $request->get('title'),
            'tags'                  => implode(';', $request->get('tags')),
        ];

        if (!$eventsModel->updateOne($eventId, $updatedEvent)) {
            $this->rollBackEvent(null, $uploadedImages);

            jsonResponse(translate('systmess_internal_server_error'));
        }

        if (isset($newMainImageName)) {
            try {
                $this->storage->delete(
                    EpEventFilePathGenerator::mainImagePath($eventId, $event['main_image'])
                );
            } catch (UnableToDeleteFile $error) {
                jsonResponse(translate('events_pictures_error_delete_message'));
            }

            array_map(function ($item) use ($eventId, $event) {
                try {
                    $this->storage->delete(
                        EpEventFilePathGenerator::mainImagePath((string) $eventId, str_replace('{THUMB_NAME}', $event['main_image'], $item['name']))
                    );
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_pictures_error_delete_message'));
                }
            }, config("img.{$moduleMain}.thumbs") ?? []);
        }

        //region update partners
        $currentEventPartnersIds = null === $event['partners'] ? [] : array_column($event['partners']->toArray(), 'id');
        $incommingEventPartnersIds = array_map(fn ($partnerId) => (int) $partnerId, (array) $request->get('partners'));
        $oldPartnersIds = array_diff($currentEventPartnersIds, $incommingEventPartnersIds);
        $newPartnersIds = array_diff($incommingEventPartnersIds, $currentEventPartnersIds);

        if (!empty($oldPartnersIds) || !empty($newPartnersIds)) {
            /** @var Ep_Events_Partners_Relations_Model $epEventsPartnersRelationsModel */
            $epEventsPartnersRelationsModel = model(Ep_Events_Partners_Relations_Model::class);
            if (!empty($oldPartnersIds)) {
                $epEventsPartnersRelationsModel->deleteAllBy([
                    'conditions' => [
                        'eventId'       => $eventId,
                        'partnersIds'   => $oldPartnersIds,
                    ],
                ]);
            }

            if (!empty($newPartnersIds)) {
                $eventPartnersRecords = array_map(
                    fn ($partnerId) => ['id_event' => $eventId, 'id_partner' => $partnerId],
                    $newPartnersIds
                );

                if (count($eventPartnersRecords) != $epEventsPartnersRelationsModel->insertMany($eventPartnersRecords)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }
            }
        }

        if (!empty($imagesToOptimization)) {
            /** @var Image_optimization_Model $optimizationImagesModel */
            $optimizationImagesModel = model(Image_optimization_Model::class);
            $optimizationImagesModel->insertMany($imagesToOptimization);
        }

        //Update calendar events
        $this->container->get(CalendarEpEventsService::class)->actualizeEvent($eventId, $event, $updatedEvent);

        jsonResponse(translate('ep_events_administration_succes_edited_offline_event'), 'success');
    }

    /**
     * Action for processing form to adding the webinar.
     *
     * @throws Exception
     */
    private function addWebinar(): void
    {
        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new WebinarValidator($adapter);
        if (!$validator->validate($request->all())) {
            jsonResponse(
                array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }


        $mainImage = $request->get('images_main');
        $newMainImageName = pathinfo($mainImage, PATHINFO_BASENAME);

        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);

        /** @var TinyMVC_Library_Cleanhtml $sanitizerLibrary */
        $sanitizerLibrary = library(TinyMVC_Library_Cleanhtml::class);
        $sanitizerLibrary->defaultTextarea(['style' => 'text-align']);
        $sanitizerLibrary->addAdditionalTags('<h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        $isPublishedEvent = (int) filter_var($request->get('published'), FILTER_VALIDATE_BOOLEAN);

        if (empty($eventId = $eventsModel->insertOne([
            'short_description' => $request->get('short_description'),
            'published_date'    => $isPublishedEvent ? (new DateTime())->format('Y-m-d H:i:s') : null,
            'is_published'      => $isPublishedEvent,
            'id_category'       => $request->getInt('category'),
            'description'       => $sanitizerLibrary->sanitize($request->get('description')),
            'why_attend'        => $sanitizerLibrary->sanitize($request->get('why_attend')),
            'main_image'        => $newMainImageName,
            'id_speaker'        => $request->getInt('speaker'),
            'start_date'        => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_start')))->format('Y/m/d H:i:s'),
            'end_date'          => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_end')))->format('Y/m/d H:i:s'),
            'id_type'           => self::WEBINAR_TYPE_ID,
            'title'             => $request->get('title'),
            'tags'              => implode(';', $request->get('tags')),
            'url'               => $request->get('link'),
        ]))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        $uploadedImages = [];

        $moduleMain = 'ep_events.main';

        $mainImageFolderPath = EpEventFilePathGenerator::mainFolderPath((string) $eventId);
        if (!$this->storage->fileExists($mainImageFolderPath)) {
            $this->storage->createDirectory($mainImageFolderPath);
        }

        try {
            $file = $this->tempStorage->read($mainImage);
        } catch (UnableToReadFile $error) {
            $this->rollBackEvent($eventId);

            jsonResponse(translate('ep_events_administration_error_upload_main_image'));
        }

        try {
            $mainImageFullPath = EpEventFilePathGenerator::mainImagePath((string) $eventId, $newMainImageName);

            $this->storage->write($mainImageFullPath, $file);
        } catch (UnableToWriteFile $error) {
            $this->rollBackEvent($eventId);

            jsonResponse(translate('ep_events_administration_error_upload_main_image'));
        }

        $thumbs = config("img.{$moduleMain}.thumbs");
        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $thumbTempName = str_replace('{THUMB_NAME}', $newMainImageName, $thumb['name']);
                $thumbTempPath = str_replace($newMainImageName, $thumbTempName, $mainImage);

                try {
                    $file = $this->tempStorage->read($thumbTempPath);

                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                }

                try {
                    $thumbNewPath = EpEventFilePathGenerator::mainImagePath((int) $eventId, $thumbTempName);

                    $this->storage->write($thumbNewPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                }

                $uploadedImages[] = $thumbNewPath;
            }
        }

        $imagesToOptimization[] = [
            'file_path'	=> $thumbNewPath,
            'context'   => ['eventId' => $eventId],
            'type'      => 'ep_event_main_image',
        ];

        $uploadedImages[] = $mainImageFullPath;

        $moduleGallery = 'ep_events.gallery';
        $imagesGallery = (array) $request->get('images_multiple');
        if (!empty($imagesGallery)) {

            $galleryFolderPath = EpEventFilePathGenerator::galleryFolderPath((string) $eventId);
            if (!$this->storage->fileExists($galleryFolderPath)) {
                $this->storage->createDirectory($galleryFolderPath);
            }

            $eventGallery = [];

            foreach ($imagesGallery as $image) {
                $newImageName = pathinfo($image, PATHINFO_BASENAME);

                try {
                    $file = $this->tempStorage->read($image);
                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                try {
                    $imageFullPath = EpEventFilePathGenerator::galleryImagePath((int) $eventId, $newImageName);

                    $this->storage->write($imageFullPath, $file);
                } catch (UnableToWriteFile $error) {
                    $this->rollBackEvent($eventId, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                $uploadedImages[] = $imageFullPath;
                $eventGallery[] = [
                    'id_event'  => $eventId,
                    'name'      => $newImageName,
                ];

                $thumbs = config("img.{$moduleGallery}.thumbs");
                if (!empty($thumbs)) {
                    $galleryImageTempPath = pathinfo($image, PATHINFO_DIRNAME);

                    foreach ($thumbs as $thumb) {
                        $thumbTempName = str_replace('{THUMB_NAME}', $newImageName, $thumb['name']);
                        $thumbTempPath = $galleryImageTempPath . '/' . $thumbTempName;

                        try {
                            $file = $this->tempStorage->read($thumbTempPath);
                        } catch (UnableToReadFile $error) {
                            $this->rollBackEvent($eventId, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        try {
                            $thumbNewPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $thumbTempName);

                            $file = $this->storage->write($thumbNewPath, $file);
                        } catch (UnableToReadFile $error) {
                            $this->rollBackEvent($eventId, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        $uploadedImages[] = $thumbNewPath;
                    }
                }

                $imagesToOptimization[] = [
                    'file_path'	=> $thumbNewPath,
                    'context'   => ['eventId' => $eventId],
                    'type'      => 'ep_event_gallery_image',
                ];
            }

            /** @var Ep_Events_Images_Model $eventImagesModel */
            $eventImagesModel = model(Ep_Events_Images_Model::class);
            if (count($eventGallery) != $eventImagesModel->insertMany($eventGallery)) {
                $this->rollBackEvent($eventId, $uploadedImages);

                jsonResponse(translate('systmess_internal_server_error'));
            }
        }

        if (!empty($imagesToOptimization)) {
            /** @var Image_optimization_Model $optimizationImagesModel */
            $optimizationImagesModel = model(Image_optimization_Model::class);
            $optimizationImagesModel->insertMany($imagesToOptimization);
        }

        jsonResponse(translate('ep_events_administration_succes_added_webinar'), 'success');
    }

    /**
     * Action for processing form to editing the webinar.
     *
     * @throws Exception
     */
    private function editWebinar(int $eventId): void
    {
        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId, 'type' => self::WEBINAR_TYPE_ID],
            'with'          => ['gallery'],
        ]))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new WebinarValidator($adapter, false);
        if (!$validator->validate($request->all())) {
            jsonResponse(
                array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        $removedGallery = (array) $request->get('images_multiple_removed');
        $countRemovedImages = empty($removedGallery) ? 0 : count($removedGallery);
        if (!empty($countRemovedImages)) {
            $alreadyUploadedImages = empty($event['gallery']) ? [] : array_column($event['gallery']->toArray(), 'name');

            $eventGalleryToDelete = [];
            foreach ($removedGallery as $removedImage) {
                if (!in_array($removedImage, $alreadyUploadedImages)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $eventGalleryToDelete[] = $removedImage;
            }
        }

        $uploadedImages = [];
        $moduleGallery = 'ep_events.gallery';

        $imagesGallery = (array) $request->get('images_multiple');
        if (!empty($imagesGallery)) {
            $galleryLimit = config("img.{$moduleGallery}.limit", 1);
            $countAlreadyUploadedImages = empty($event['gallery']) ? 0 : $event['gallery']->count();
            $countNewImages = count($imagesGallery);

            if ($countAlreadyUploadedImages - $countRemovedImages + $countNewImages > $galleryLimit) {
                jsonResponse(translate('ep_events_administration_error_gallery_limit_exceeded', ['{{LIMIT_IMAGES}}' => $galleryLimit]));
            }

            $eventGalleryToInsert = [];

            $galleryFolderPath = EpEventFilePathGenerator::mainFolderPath((string) $eventId);
            if (!$this->storage->fileExists($galleryFolderPath)) {
                $this->storage->createDirectory($galleryFolderPath);
            }

            foreach ($imagesGallery as $image) {
                $newImageName = uniqid() . '.' . pathinfo($image, PATHINFO_EXTENSION);

                try {
                    $file = $this->tempStorage->read($image);
                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent(null, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                try {
                    $imageFullPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $newImageName);

                    $this->storage->write($imageFullPath, $file);
                } catch (UnableToReadFile $error) {
                    $this->rollBackEvent(null, $uploadedImages);

                    jsonResponse(translate('ep_events_administration_error_upload_gallery_image'));
                }

                $uploadedImages[] = $imageFullPath;

                $eventGalleryToInsert[] = [
                    'id_event'  => $eventId,
                    'name'      => $newImageName,
                ];

                $thumbs = config("img.{$moduleGallery}.thumbs");
                if (!empty($thumbs)) {
                    foreach ($thumbs as $thumb) {
                        $thumbTempName = str_replace('{THUMB_NAME}', $newImageName, $thumb['name']);
                        $thumbTempPath = str_replace($newImageName, $thumbTempName, $image);

                        try {
                            $file = $this->tempStorage->read($thumbTempPath);
                        } catch (UnableToReadFile $error) {
                            $this->rollBackEvent(null, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        try {
                            $thumbNewPath = EpEventFilePathGenerator::galleryImagePath((string) $eventId, $thumbTempName);

                            $this->storage->write($thumbNewPath, $file);
                        } catch (UnableToReadFile $error) {
                            $this->rollBackEvent(null, $uploadedImages);

                            jsonResponse(translate('ep_events_administration_error_upload_gallery_image_thumbs'));
                        }

                        $uploadedImages[] = $thumbNewPath;
                    }
                }

                $imagesToOptimization[] = [
                    'file_path'	=> $thumbNewPath,
                    'context'   => ['eventId' => $eventId],
                    'type'      => 'ep_event_gallery_image',
                ];
            }
        }

        $imagesMain = $request->get('images_main');
        if (!empty($imagesMain)) {
            $newMainImageName = uniqid() . '.' . pathinfo($imagesMain, PATHINFO_EXTENSION);
            $moduleMain = 'ep_events.main';

            $mainImageFolderPath = EpEventFilePathGenerator::mainFolderPath((string) $eventId);
            if (!$this->storage->fileExists($mainImageFolderPath)) {
                $this->storage->createDirectory($mainImageFolderPath);
            }

            try {
                $file = $this->tempStorage->read($imagesMain);
            } catch (UnableToReadFile $error) {
                $this->rollBackEvent(null, $uploadedImages);

                jsonResponse(translate('ep_events_administration_error_upload_main_image'));
            }

            try {
                $mainImageFullPath = EpEventFilePathGenerator::mainImagePath((string) $eventId, $newMainImageName);

                $this->storage->write($mainImageFullPath, $file);
            } catch (UnableToReadFile $error) {
                $this->rollBackEvent(null, $uploadedImages);

                jsonResponse(translate('ep_events_administration_error_upload_main_image'));
            }

            $uploadedImages[] = $mainImageFullPath;

            $thumbs = config("img.{$moduleMain}.thumbs");
            if (!empty($thumbs)) {
                foreach ($thumbs as $thumb) {
                    $thumbTempName = str_replace('{THUMB_NAME}', $newMainImageName, $thumb['name']);
                    $thumbTempPath = str_replace($newMainImageName, $thumbTempName, $imagesMain);

                    try {
                        $file = $this->tempStorage->read($thumbTempPath);

                    } catch (UnableToReadFile $error) {
                        $this->rollBackEvent(null, $uploadedImages);

                        jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                    }

                    try {
                        $thumbNewPath = EpEventFilePathGenerator::mainImagePath((int) $eventId, $thumbTempName);

                        $this->storage->write($thumbNewPath, $file);
                    } catch (UnableToWriteFile $error) {
                        $this->rollBackEvent(null, $uploadedImages);

                        jsonResponse(translate('ep_events_administration_error_upload_main_image_thumbs'));
                    }

                    $uploadedImages[] = $thumbNewPath;
                }
            }

            $imagesToOptimization[] = [
                'file_path'	=> $thumbNewPath,
                'context'   => ['eventId' => $eventId],
                'type'      => 'ep_event_main_image',
            ];
        }

        /** @var Ep_Events_Images_Model $eventImagesModel */
        $eventImagesModel = model(Ep_Events_Images_Model::class);
        if (!empty($eventGalleryToDelete)) {
            $eventImagesModel->deleteAllBy([
                'conditions' => [
                    'eventId'   => $eventId,
                    'images'    => $eventGalleryToDelete,
                ],
            ]);

            foreach ($eventGalleryToDelete as $imageToDelete) {
                try {
                    $this->storage->delete(
                        EpEventFilePathGenerator::galleryImagePath($eventId, $imageToDelete)
                    );
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_pictures_error_delete_message'));
                }

                array_map(function ($item) use ($eventId, $imageToDelete) {
                    try {
                        $this->storage->delete(
                            EpEventFilePathGenerator::galleryImagePath($eventId, str_replace('{THUMB_NAME}', $imageToDelete, $item['name']))
                        );
                    } catch (UnableToDeleteFile $error) {
                        jsonResponse(translate('events_pictures_error_delete_message'));
                    }
                }, config("img.{$moduleGallery}.thumbs") ?? []);
            }
        }

        if (!empty($eventGalleryToInsert) && count($eventGalleryToInsert) != $eventImagesModel->insertMany($eventGalleryToInsert)) {
            $this->rollBackEvent(null, $uploadedImages);

            jsonResponse(translate('systmess_internal_server_error'));
        }

        /** @var TinyMVC_Library_Cleanhtml $sanitizerLibrary */
        $sanitizerLibrary = library(TinyMVC_Library_Cleanhtml::class);
        $sanitizerLibrary->defaultTextarea(['style' => 'text-align']);
        $sanitizerLibrary->addAdditionalTags('<h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

        $isPublishedEvent = (int) filter_var($request->get('published'), FILTER_VALIDATE_BOOLEAN);

        $publishedDate = null;
        if ($isPublishedEvent) {
            $publishedDate = $event['is_published'] ? $event['published_date'] : (new DateTime())->format('Y-m-d H:i:s');
        }

        $updatedEvent = [
            'short_description' => $request->get('short_description'),
            'published_date'    => $publishedDate,
            'is_published'      => $isPublishedEvent,
            'id_category'       => $request->getInt('category'),
            'description'       => $sanitizerLibrary->sanitize($request->get('description')),
            'why_attend'        => $sanitizerLibrary->sanitize($request->get('why_attend')),
            'main_image'        => $newMainImageName ?? $event['main_image'],
            'id_speaker'        => $request->getInt('speaker'),
            'start_date'        => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_start')))->format('Y-m-d H:i:s'),
            'end_date'          => (DateTimeImmutable::createFromFormat('m/d/Y H:i', $request->get('date_end')))->format('Y-m-d H:i:s'),
            'title'             => $request->get('title'),
            'tags'              => implode(';', $request->get('tags')),
            'url'               => $request->get('link'),
        ];

        if (!$eventsModel->updateOne($eventId, $updatedEvent)) {
            $this->rollBackEvent(null, $uploadedImages);

            jsonResponse(translate('systmess_internal_server_error'));
        }

        if (isset($newMainImageName)) {
            try {
                $this->storage->delete(
                    EpEventFilePathGenerator::mainImagePath($eventId, $event['main_image'])
                );
            } catch (UnableToDeleteFile $error) {
                jsonResponse(translate('events_pictures_error_delete_message'));
            }

            array_map(function ($item) use ($eventId, $event) {
                try {
                    $this->storage->delete(
                        EpEventFilePathGenerator::mainImagePath($eventId, str_replace('{THUMB_NAME}', $event['main_image'], $item['name']))
                    );
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_pictures_error_delete_message'));
                }
            }, config("img.{$moduleMain}.thumbs") ?? []);
        }

        if (!empty($imagesToOptimization)) {
            /** @var Image_optimization_Model $optimizationImagesModel */
            $optimizationImagesModel = model(Image_optimization_Model::class);
            $optimizationImagesModel->insertMany($imagesToOptimization);
        }

        //Update calendar events
        $this->container->get(CalendarEpEventsService::class)->actualizeEvent($eventId, $event, $updatedEvent);

        jsonResponse(translate('ep_events_administration_succes_edited_webinar'), 'success');
    }

    /**
     * Action for editing the recommended image.
     */
    private function editRecommendedImage(): void
    {
        $request = request()->request;

        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        $eventId = $request->getInt('id_event');
        if (empty($event = $eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId],
        ]))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        $newImage = $request->get('images_main');
        if (empty($newImage)) {
            jsonResponse('Nothing changed', 'info');
        }

        $recommendedImagePath = EpEventFilePathGenerator::recomendedFolderPath((string) $eventId);
        if (!$this->storage->fileExists($recommendedImagePath)) {
            $this->storage->createDirectory($recommendedImagePath);
        }

        $newRecommendedImageName = uniqid() . '.' . pathinfo($newImage, PATHINFO_EXTENSION);
        $newImageFullPath = EpEventFilePathGenerator::recomendedImagePath((string) $eventId, $newRecommendedImageName);

        try {
            $file = $this->tempStorage->read($newImage);
        } catch (UnableToReadFile $error) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        try {
            $this->storage->write($newImageFullPath, $file);
        } catch (UnableToWriteFile $error) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        if (
            !$eventsModel->updateOne(
                $eventId,
                ['recommended_image' => $newRecommendedImageName ?: null]
            )
        ) {
            $this->rollBackEvent(null, [$newImageFullPath]);

            jsonResponse(translate('systmess_internal_server_error'));
        }

        /** @var Image_optimization_Model $optimizationImagesModel */
        $optimizationImagesModel = model(Image_optimization_Model::class);
        $imageToOptimization = [
            'file_path' => EpEventFilePathGenerator::recomendedImagePath((string) $eventId, $newRecommendedImageName),
            'context'   => ['eventId' => $eventId],
            'type'      => 'ep_event_recommended_image',
        ];

        $optimizationImagesModel->insertOne($imageToOptimization);

        jsonResponse(translate('ep_events_administration_succes_recommended_image'), 'success');
    }

    /**
     * Action for uploading event main image.
     *
     * @var null|string
     */
    private function uploadMainImage(): void
    {
        /** @var null|UploadedFile */
        $uploadedFile = request()->files->get('files') ?? null;
        if (null === $uploadedFile) {
            jsonResponse(translate('events_speaker_pictures_select_file_message'));
		}

        if (!$uploadedFile->isValid()) {
            jsonResponse(translate('events_speaker_pictures_invalid_file_message'));
		}

        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $pathToFile = FilePathGenerator::uploadedFile($imageName);
        $pathToDirrectory = dirname($pathToFile);
        if (!$this->tempStorage->fileExists($pathToDirrectory)) {
            $this->tempStorage->createDirectory($pathToDirrectory);
        }

        $module = 'ep_events.main';

        $result = library(TinyMVC_Library_Image_intervention::class)->image_processing(
            [
                'tmp_name' => $uploadedFile->getRealPath(),
                'name' => pathinfo($imageName, PATHINFO_FILENAME)
            ],
            [
                'destination'       => $this->tempPrefixer->prefixDirectoryPath($pathToDirrectory),
                'rules'             => config("img.{$module}.rules"),
                'handlers'          => [
                    'create_thumbs' => config("img.{$module}.thumbs"),
                    'resize'        => config("img.{$module}.resize"),
                ],
                'use_original_name' => true,
        ]);

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        $files = [
            'thumb'     => $this->tempStorage->url($pathToFile),
            'path'      => $pathToFile,
            'tmp_url'   => $pathToFile,
        ];

        jsonResponse('Photo was successfully uploaded.', 'success', $files);
    }

    /**
     * Action for uploading event gallery images.
     *
     * @var null|string
     */
    private function uploadGallery(): void
    {
        /** @var null|UploadedFile */
        $uploadedFile = request()->files->get('files') ?? null;
        if (null === $uploadedFile) {
            jsonResponse(translate('events_speaker_pictures_select_file_message'));
		}

        if (!$uploadedFile->isValid()) {
            jsonResponse(translate('events_speaker_pictures_invalid_file_message'));
		}

        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $pathToFile = FilePathGenerator::uploadedFile($imageName);

        $pathToDirrectory = dirname($pathToFile);
        if (!$this->tempStorage->fileExists($pathToDirrectory)) {
            $this->tempStorage->createDirectory($pathToDirrectory);
        }

        $module = 'ep_events.gallery';
        $eventId = (int) uri()->segment(5);
        $countAlreadyUploadedImages = 0;

        if (!empty($eventId)) {
            /** @var Ep_Events_Images_Model $eventImagesModel */
            $eventImagesModel = model(Ep_Events_Images_Model::class);
            $countAlreadyUploadedImages = $eventImagesModel->countBy(['conditions' => ['eventId' => $eventId]]);
        }

        $maxCountGalleryImages = (int) config('limit_ep_events_gallery_images', 10);
        $countAvailableImages = $maxCountGalleryImages - $countAlreadyUploadedImages;
        if (0 === $countAvailableImages) {
            jsonResponse(translate('ep_events_administration_error_gallery_limit_exceeded', ['{{LIMIT_IMAGES}}' => $maxCountGalleryImages]));
        }

        $result = library(TinyMVC_Library_Image_intervention::class)->image_processing(
            [
                'tmp_name' => $uploadedFile->getRealPath(),
                'name' => pathinfo($imageName, PATHINFO_FILENAME)
            ],
            [
                'destination'           => $this->tempPrefixer->prefixDirectoryPath($pathToDirrectory),
                'rules'                 => config("img.{$module}.rules"),
                'handlers'              => [
                    'create_thumbs' => config("img.{$module}.thumbs") ?? [],
                    'resize'        => config("img.{$module}.resize") ?? [],
                ],
                'use_original_name'     => true,
        ]);

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        $files = [
            'thumb'     => $this->tempStorage->url($pathToFile),
            'path'      => $pathToFile,
            'tmp_url'   => $pathToFile,
            'name'      => $result[0]['new_name'],
        ];

        jsonResponse('', 'success', $files);
    }

    /**
     * Action for removing event gallery image.
     *
     * @var null|string
     */
    private function removeTempGalleryImage(): void
    {
        $image = request()->request->get('file');
        if (empty($image)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $filePath = FilePathGenerator::uploadedFile($image);
        if (!$this->tempStorage->fileExists($filePath)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        try {
            $this->tempStorage->delete($filePath);
        } catch (UnableToDeleteFile $error) {
            jsonResponse(translate('events_pictures_error_delete_message'));
        }

        array_map(function ($item) use ($image, $filePath) {
            $thumbName = str_replace('{THUMB_NAME}', $image, $item['name']);
            $thumbPath = pathinfo($filePath, PATHINFO_DIRNAME);

            try {
                $this->tempStorage->delete("$thumbPath/$thumbName");
            } catch (UnableToDeleteFile $error) {
                jsonResponse(translate('events_pictures_error_delete_message'));
            }
        }, config("img.ep_events.gallery.thumbs") ?? []);

        jsonResponse('', 'success');
    }

    /**
     * Action for uploading event recommended image.
     *
     * @var null|string
     */
    private function uploadRecommendedImage(): void
    {

        /** @var null|UploadedFile */
        $uploadedFile = request()->files->get('files') ?? null;
        if (null === $uploadedFile) {
            jsonResponse(translate('events_speaker_pictures_select_file_message'));
        }

        if (!$uploadedFile->isValid()) {
            jsonResponse(translate('events_speaker_pictures_invalid_file_message'));
        }

        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $pathToFile = FilePathGenerator::uploadedFile($imageName);

        $pathToDirrectory = dirname($pathToFile);
        if (!$this->tempStorage->fileExists($pathToDirrectory)) {
            $this->tempStorage->createDirectory($pathToDirrectory);
        }

        $module = 'ep_events.recommended';

        $result = library(TinyMVC_Library_Image_intervention::class)->image_processing(
            [
                'tmp_name'  => $uploadedFile->getRealPath(),
                'name'      => pathinfo($imageName, PATHINFO_FILENAME)
            ],
            [
                'destination'           => $this->tempPrefixer->prefixDirectoryPath($pathToDirrectory),
                'rules'                 => config("img.{$module}.rules"),
                'handlers'              => [
                    'resize' => config("img.{$module}.resize"),
                ],
                'use_original_name'     => true,
            ]
        );

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        $files = [
            'path'    => $pathToFile,
            'thumb'   => $this->tempStorage->url($pathToFile),
            'tmp_url' => $pathToFile,
        ];

        jsonResponse('Photo was successfully uploaded.', 'success', $files);
    }

    /**
     * Action for removing event temp recommended image.
     *
     * @var null|string
     */
    private function removeTempRecommendedImage(): void
    {
        $image = request()->request->get('file');
        if (empty($image)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $filePath = FilePathGenerator::uploadedFile($image);
        if (!$this->tempStorage->fileExists($filePath)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        try {
            $this->tempStorage->delete($filePath);
        } catch (UnableToDeleteFile $error) {
            jsonResponse($error);
        }

        jsonResponse('', 'success');
    }

    /**
     * Action for editing event agenda.
     *
     * @throws Exception
     */
    private function editAgenda(?int $eventId): void
    {
        if (empty($eventId)) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);

        if (empty($eventsModel->findOneBy([
            'conditions'    => ['id' => $eventId, 'type' => self::OFFLINE_EVENT_TYPE_ID],
        ]))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        $request = request()->request;

        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new EventAgendaValidator($adapter);

        if (!$validator->validate($request->all())) {
            jsonResponse(
                array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        $agenda = [];
        $descriptionFields = (array) $request->get('description');
        $startDateFields = (array) $request->get('date_start');

        if (!empty($descriptionFields)) {
            /** @var TinyMVC_Library_Cleanhtml $sanitizerLibrary */
            $sanitizerLibrary = library(TinyMVC_Library_Cleanhtml::class);
            $sanitizerLibrary->addAdditionalTags('<p><span><strong><em><b><i><u><br>');

            foreach ($descriptionFields as $descriptionField) {
                $agenda[] = [
                    'startDate'     => array_shift($startDateFields),
                    'description'   => $sanitizerLibrary->sanitize($descriptionField),
                ];
            }
        }

        usort($agenda, function ($a, $b) {
            return DateTime::createFromFormat('m/d/Y H:i', $a['startDate']) > DateTime::createFromFormat('m/d/Y H:i', $b['startDate']) ? 1 : -1;
        });

        $eventUpdates = ['agenda' => empty($agenda) ? null : $agenda];
        if (!$eventsModel->updateOne((int) $eventId, $eventUpdates)) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        jsonResponse(translate('systmess_success_edited_ep_events_agenda'), 'success');
    }

    /**
     * Action for togle event recommended status.
     */
    private function togleRecommendedStatus()
    {
        $eventId = request()->request->getInt('event');

        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($eventId) || empty($event = $eventsModel->findOne($eventId))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        if (empty($event['is_recommended_by_ep'])) {
            if ($event['is_attended_by_ep']) {
                jsonResponse(translate('systmess_error_ep_event_already_marked_as_atended'));
            }

            if ($event['is_upcoming_by_ep']) {
                jsonResponse(translate('systmess_error_ep_event_already_marked_as_upcoming'));
            }

            $eventUpdates = ['is_recommended_by_ep' => 1];
            $successResponseMessage = translate('systmess_succes_marked_ep_events_as_recommended');
        } else {
            if (!empty($event['highlighted_end_date'])) {
                jsonResponse(translate('systmess_error_ep_event_is_highlighted'), 'warning');
            }

            $eventUpdates = ['is_recommended_by_ep' => 0];
            $successResponseMessage = translate('systmess_succes_unmarked_ep_events_as_recommended');
        }

        if (!$eventsModel->updateOne($eventId, $eventUpdates)) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        jsonResponse($successResponseMessage, 'success');
    }

    /**
     * Action for togle event upcoming status.
     */
    private function togleUpcomingStatus()
    {
        $eventId = request()->request->getInt('event');

        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($eventId) || empty($event = $eventsModel->findOne($eventId))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        if (empty($event['is_upcoming_by_ep'])) {
            if ($event['is_attended_by_ep']) {
                jsonResponse(translate('systmess_error_ep_event_already_marked_as_atended'));
            }

            if ($event['is_recommended_by_ep']) {
                jsonResponse(translate('systmess_error_ep_event_already_marked_as_recommended'));
            }

            $eventUpdates = ['is_upcoming_by_ep' => 1];
            $successResponseMessage = translate('systmess_succes_marked_ep_events_as_upcoming');
        } else {
            if (!empty($event['highlighted_end_date'])) {
                jsonResponse(translate('systmess_error_ep_event_is_highlighted'), 'warning');
            }

            $eventUpdates = ['is_upcoming_by_ep' => 0];
            $successResponseMessage = translate('systmess_succes_unmarked_ep_events_as_upcoming');
        }

        if (!$eventsModel->updateOne($eventId, $eventUpdates)) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        jsonResponse($successResponseMessage, 'success');
    }

    /**
     * Action for togle event attended status.
     */
    private function togleAttendedStatus()
    {
        $eventId = request()->request->getInt('event');

        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);
        if (empty($eventId) || empty($event = $eventsModel->findOne($eventId))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        if (empty($event['is_attended_by_ep'])) {
            if ($event['is_upcoming_by_ep']) {
                jsonResponse(translate('systmess_error_ep_event_already_marked_as_upcoming'));
            }

            if ($event['is_recommended_by_ep']) {
                jsonResponse(translate('systmess_error_ep_event_already_marked_as_recommended'));
            }

            $eventUpdates = ['is_attended_by_ep' => 1];
            $successResponseMessage = translate('systmess_succes_marked_ep_events_as_attended');
        } else {
            if (!empty($event['highlighted_end_date'])) {
                jsonResponse(translate('systmess_error_ep_event_is_highlighted'), 'warning');
            }

            $eventUpdates = ['is_attended_by_ep' => 0];
            $successResponseMessage = translate('systmess_succes_unmarked_ep_events_as_attended');
        }

        if (!$eventsModel->updateOne($eventId, $eventUpdates)) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        jsonResponse($successResponseMessage, 'success');
    }

    /**
     * Action for togle event highlighted status.
     */
    private function togleEventHighlighting()
    {
        $eventId = request()->request->getInt('event');

        /** @var Ep_Events_Model $eventsModel */
        $eventsModel = model(Ep_Events_Model::class);

        if (empty($eventId) || empty($event = $eventsModel->findOne($eventId))) {
            jsonResponse(translate('ep_events_administration_event_id_not_found'));
        }

        if (empty($event['highlighted_end_date'])) {
            if (!($event['is_recommended_by_ep'] || $event['is_upcoming_by_ep'] || $event['is_attended_by_ep'])) {
                jsonResponse(translate('systmess_error_ep_events_highlight'));
            }

            $eventsModel->updateMany(['highlighted_end_date' => null], ['isHighlighted']);
            $eventsModel->updateOne($eventId, ['highlighted_end_date' => (new DateTime())->modify('+1 month')->format('Y-m-d H:i:s')]);

            jsonResponse(translate('systmess_success_marked_ep_events_as_highlighted'), 'success');
        } else {
            $eventsModel->updateOne($eventId, ['highlighted_end_date' => null]);

            jsonResponse(translate('systmess_success_unmarked_ep_events_as_highlighted'), 'success');
        }
    }

    /**
     * Promotes the event.
     */
    private function promoteEvent(Request $request, Ep_Events_Model $eventsRepository, array $event): void
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        // Here we are going to validate provided promotion date interval (start date and end date)
        // to see if we can fit it in the list of all promotions.
        $validator = new EventPromotionValidator($adapter, $currentDate = new DateTimeImmutable(), $event['start_date'], array_map(
            // To properly validate the interval of dates provided by request, we need
            // to have an ordered list of promotion date intervals that are going to
            // happen from now up until the start of the event. If we have this list,
            // then we can easily find if provided interval fits into list without
            // overlapping with other intravals (only one promoted event at the time
            // is allowed).
            fn (array $event) => [$event['promotion_start_date'], $event['promotion_end_date']],
            $eventsRepository->findAllBy([
                'scopes' => ['promotedEventsForInterval' => fn () => [$currentDate, $event['start_date']]],
                'order'  => ['promotion_start_date' => 'ASC'],
            ]),
        ));
        if (!$validator->validate(request()->request->all())) {
            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Add promotion period
        if (
            !$eventsRepository->updateOne($event['id'], [
                'promotion_start_date' => DateTimeImmutable::createFromFormat('m/d/Y H:s', $request->request->get('start_date')),
                'promotion_end_date'   => DateTimeImmutable::createFromFormat('m/d/Y H:s', $request->request->get('end_date')),
            ])
        ) {
            jsonResponse('Failed to start promotion for event.');
        }
        //endregion Add promotion period

        jsonResponse('The promotion period for event has been successfully set.', 'success');
    }

    /**
     * Promotes the event.
     */
    private function deletePromotion(Ep_Events_Model $eventsRepository, array $event): void
    {
        //region Remove promotion period
        if (
            null !== $event['promotion_start_date']
            && !$eventsRepository->updateOne($event['id'], [
                'promotion_start_date' => null,
                'promotion_end_date'   => null,
            ])
        ) {
            jsonResponse('Failed to delete promotion from event.');
        }
        //endregion Remove promotion period

        jsonResponse('The promotion period has been succesfully remove from event.', 'success');
    }

    private function rollBackEvent(?int $eventId, ?array $files = null): void
    {
        if (null !== $eventId) {
            /** @var Ep_events_Model $eventsModel */
            $eventsModel = model(Ep_events_Model::class);

            $eventsModel->deleteOne($eventId);
        }

        if (null !== $files) {
            foreach ($files as $file) {
                try {
                    $this->storage->delete($file);
                } catch (UnableToDeleteFile $error) {
                    jsonResponse(translate('events_pictures_error_delete_message'));
                }
            }
        }
    }

    private function uploadParamsMainImage($event = ['id' => 'none', 'main_image' => 'none.jpg']): array
    {
        $moduleMain = 'ep_events.main';
        $mimeRecommendedProperties = getMimePropertiesFromFormats(config("img.{$moduleMain}.rules.format"));
        $imageLink = $this->storage->url(
            EpEventFilePathGenerator::mainImagePath((int) $event['id'], $event['main_image'])
        );

        return [
            'link_main_image'        => $imageLink,
            'link_thumb_main_image'  => $imageLink,
            'title_text_popup'       => 'Main image',
            'btn_text_save_picture'  => 'Set new Main image',
            'preview_fanacy'         => false,
            'croppper_limit_by_min'  => true,
            'rules'                  => config("img.{$moduleMain}.rules"),
            'url'                    => [
                'upload' => __SITE_URL . 'ep_events/ajax_operations/upload_main_image',
            ],
            'accept'                 => arrayGet($mimeRecommendedProperties, 'accept'),
        ];
    }

    private function uploadParamsMultipleImage($event = null): array
    {
        $moduleGallery = 'ep_events.gallery';
        $mimeRecommendedProperties = getMimePropertiesFromFormats(config("img.{$moduleGallery}.rules.format"));

        $images = [];

        if (!empty($event['gallery'])) {
            foreach ($event['gallery'] as $galleryItem) {
                $images[] = [
                    'link' => $this->storage->url(
                        EpEventFilePathGenerator::galleryImagePath((string) $event['id'], $galleryItem['name'])
                    ),
                    'name' => $galleryItem['name'],
                ];
            }
        }

        return [
            'images_links'           => $images,
            'title_text_popup'       => 'Event gallery',
            'btn_text_save_picture'  => 'Set new image',
            'preview_fanacy'         => false,
            'croppper_limit_by_min'  => true,
            'limit'                  => config("img.{$moduleGallery}.limit"),
            'rules'                  => config("img.{$moduleGallery}.rules"),
            'url'                    => [
                'upload'    => __SITE_URL . 'ep_events/ajax_operations/upload_gallery',
                'removeTmp' => __SITE_URL . 'ep_events/ajax_operations/delete_temp_gallery_image',
            ],
            'accept'                 => arrayGet($mimeRecommendedProperties, 'accept'),
        ];
    }

    private function sendEmail(InputBag $request, int $userId, string $userName, string $operationType): void
    {
        is_allowed("freq_allowed_send_email_to_user");

        switch($operationType){
            case 'email':
                checkPermisionAjax('email_this');

                //region Validation
                $validator = new EmailThisValidator(
                    new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)),
                    (int) config('email_this_max_email_count', 10)
                );

                if (!$validator->validate($request->all())) {
                    jsonResponse(
                        array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }
                //endregion Validation

                if (empty($eventId = $request->getInt('event_id'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Elasticsearch_Ep_Events_Model $elasticsearchEpEventsModel */
                $elasticsearchEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);

                $event = $elasticsearchEpEventsModel->getEvents([
                    'id' => $eventId,
                ]);

                if (empty($event = array_shift($event))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $filteredEmails = filter_email($request->get('emails'));

				if (empty($filteredEmails)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface */
                    $storageProvider = container()->get(FilesystemProviderInterface::class);
                    $storage = $storageProvider->storage('public.storage');

                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutEpEvent(
                            $userName,
                            $request->get('message'),
                            $event,
                            $storage->url(EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'], EpEventMainImageThumb::SMALL()))
                        ))
                        ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                        ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                if (config('env.APP_ENV') === 'prod') {
                    /** @var TinyMVC_Library_Zoho_crm $crmLibrary */
                    $crmLibrary = library(TinyMVC_Library_Zoho_crm::class);

                    foreach ($filteredEmails as $filteredEmail) {
                        if (empty($crmLibrary->getLeadsByUserEmail($filteredEmail))) {
                            $crmLibrary->createLead([
                                'first_name'  => 'No First Name',
                                'last_name'   => 'No Last Name',
                                'email'       => $filteredEmail,
                                'lead_source' => 'ExportPortal API',
                            ]);
                        }
                    }
                }

                /** @var Share_Statistic_Model $shareStatisticRepository */
                $shareStatisticRepository = model(Share_Statistic_Model::class);
                $shareStatisticRepository->add([
                    'type'          => 'ep_event',
                    'type_sharing'  => 'email this',
                    'id_item'       => $eventId,
                    'id_user'       => id_session(),
                ]);

				jsonResponse(translate('systmess_success_email_has_been_sent'), 'success');
			break;
            case 'share':
                checkPermisionAjax('share_this');

                //region Validation
                $validator = new ShareThisValidator(new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)));

                if (!$validator->validate($request->all())) {
                    jsonResponse(
                        array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }
                //endregion Validation

                if (empty($eventId = $request->getInt('event_id'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var Elasticsearch_Ep_Events_Model $elasticsearchEpEventsModel */
                $elasticsearchEpEventsModel = model(Elasticsearch_Ep_Events_Model::class);

                $event = $elasticsearchEpEventsModel->getEvents([
                    'id' => $eventId,
                ]);

                if (empty($event = array_shift($event))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var User_Followers_Model $userFollowersModel */
                $userFollowersModel = model(User_Followers_Model::class);

                $followers = $userFollowersModel->findAllBy([
                    'scopes' => [
                        'followedUser' => $userId,
                    ],
                    'with'   => ['user'],
                ]);

                if (empty($followers)) {
                    jsonResponse(translate('systmess_error_share_event_no_followers'));
                }

                $recipientAddresses = [];
                foreach ($followers as $follower) {
                    $recipientAddresses[] = new RefAddress((string) $follower['user']['idu'], new Address($follower['user']['email']));
                }

                try {
                    /** @var FilesystemProviderInterface */
                    $storageProvider = container()->get(FilesystemProviderInterface::class);
                    $storage = $storageProvider->storage('public.storage');

                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutEpEvent(
                            $userName,
                            $request->get('message'),
                            $event,
                            $storage->url(EpEventFilePathGenerator::mainImageThumbPath((string) $event['id'], $event['main_image'], EpEventMainImageThumb::SMALL()))
                        ))
                        ->to(...$recipientAddresses)
                        ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                /** @var Share_Statistic_Model $shareStatisticRepository */
                $shareStatisticRepository = model(Share_Statistic_Model::class);
                $shareStatisticRepository->add([
                    'type'          => 'ep_event',
                    'type_sharing'  => 'share this',
                    'id_item'       => $eventId,
                    'id_user'       => $userId,
                ]);

                jsonResponse(translate('systmess_successfully_shared_event_information'), 'success');
			break;
		}
    }
}
