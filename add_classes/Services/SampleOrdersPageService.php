<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Buttons\ChatButton;
use App\Common\Exceptions\NotFoundException;
use App\Common\Http\Filters\FiltersFactoryInterface;
use App\Common\Http\Filters\QueryFilterFactory;
use App\Common\Http\Filters\RequestFilterFactory;
use App\Common\Http\Filters\UriPathPatternFiltersFactory;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ExportPortal\Contracts\Chat\Recource\ResourceOptions;
use ExportPortal\Contracts\Chat\Recource\ResourceType;
use OutOfBoundsException;
use Sample_Orders_Model;
use Sample_Orders_Statuses_Model;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use TinyMVC_Library_Make_Pdf;

final class SampleOrdersPageService implements SampleServiceInterface
{
    use SampleOrdersEntitiesAwareTrait;

    private const PATH_REGEX_TEMPLATE = '/^{{prefix}}(\/{{order}}\/(?P<order>[^\/]+))?(\/{{status}}\/(?P<status>[^\/]+))?(\/{{assigned}}\/(?P<assigned>[^\/]+))?(\/{{page}}\/(?P<page>[\d]+))?\/?$/i';
    private const DEFAULT_LOCALE_NAME = 'en';

    /**
     * The page locale.
     *
     * @var string
     */
    private $locale;

    /**
     * The default locale.
     *
     * @var string
     */
    private $defaultLocale;

    /**
     * The amount of samples per page.
     *
     * @var null|int
     */
    private $samplesPerPage;

    /**
     * The base path of the page.
     *
     * @var string
     */
    private $pagePath;

    /**
     * The i18n Vocabulary.
     *
     * @var ParameterBag
     */
    private $i18nVocabulary;

    /**
     * The i18n Vocabulary for current locale.
     *
     * @var ParameterBag
     */
    private $localeVocabulary;

    /**
     * The samples repository.
     *
     * @var Sample_Orders_Model
     */
    private $sampleOrdersRepository;

    /**
     * The statuses repository.
     *
     * @var Sample_Orders_Statuses_Model
     */
    private $statusesRepository;

    /**
     * The URI filters factory.
     *
     * @var FiltersFactoryInterface
     */
    private $uriFiltersFactory;

    /**
     * The URI query filters factory.
     *
     * @var FiltersFactoryInterface
     */
    private $queryFilterFactory;

    /**
     * The request filters factory.
     *
     * @var FiltersFactoryInterface
     */
    private $requestFilterFactory;

    /**
     * The request filters prototypes.
     *
     * @var Collection
     */
    private $requestFiltersPrototypes;

    /**
     * The path filters prototypes.
     *
     * @var Collection
     */
    private $pathFiltersPrototypes;

    /**
     * The query filters prototypes.
     *
     * @var Collection
     */
    private $queryFiltersPrototypes;

    /**
     * the fitlers pattern for current path.
     *
     * @var string
     */
    private $currentPathFilterPattern;

    /**
     * Creates the instance of the service.
     *
     * @param Request $request
     */
    public function __construct(
        string $locale,
        string $pagePath,
        Sample_Orders_Model $samples,
        Sample_Orders_Statuses_Model $statuses,
        ?int $samplesPerPage = null,
        ?ParameterBag $i18nVocabulary = null,
        string $defaultLocale = self::DEFAULT_LOCALE_NAME
    ) {
        $this->locale = $locale;
        $this->pagePath = $pagePath;
        $this->defaultLocale = $defaultLocale;
        $this->samplesPerPage = $samplesPerPage;
        $this->i18nVocabulary = $i18nVocabulary ?? $this->getBaseVocabulary();
        $this->statusesRepository = $statuses;
        $this->sampleOrdersRepository = $samples;
        $this->currentPathFilterPattern = str_replace('{{prefix}}', preg_quote($this->pagePath, '/'), static::PATH_REGEX_TEMPLATE);
        $this->requestFiltersPrototypes = $this->getFiltersPrototypes();
        $this->queryFiltersPrototypes = $this->requestFiltersPrototypes->filter(function (ParameterBag $filter) { return in_array('query', $filter->get('allow', array())); });
        $this->pathFiltersPrototypes = $this->requestFiltersPrototypes->filter(function (ParameterBag $filter) { return in_array('path', $filter->get('allow', array())); });
        $this->localeVocabulary = ($i18nVocabulary ?? $this->getBaseVocabulary())->get(
            $this->locale,
            $this->i18nVocabulary->get($this->defaultLocale, new ParameterBag())
        );

        $this->requestFilterFactory = new RequestFilterFactory($this->requestFiltersPrototypes);
        $this->queryFilterFactory = new QueryFilterFactory($this->queryFiltersPrototypes);
        $this->uriFiltersFactory = new UriPathPatternFiltersFactory(
            $this->currentPathFilterPattern,
            $this->pathFiltersPrototypes,
            $this->localeVocabulary
        );
    }

    /**
     * Finds samples for given request.
     */
    public function findSamples(Request $request, int $userId, bool $isSeller): array
    {
        //region Filters
        $requestFilters = $this->withOwnerFilter($this->requestFilterFactory->createFilters($request), $userId, $isSeller);
        $filters = arrayCollapse($this->prepareFilters($requestFilters)->getValues());
        $assignedBuyer = $filters['assigned'] ?? null;
        $statusToken = $filters['status'] ?? null;
        $page = $filters['page'] ?? 1;
        $page = $page < 1 ? 1 : $page;
        unset($filters['page']);
        //endregion Filters

        //region Ensure entities
        //region Status
        if (null !== $statusToken) {
            $status = $this->resolveStatusByToken($statusToken);
            $filters = array_replace($filters, array('status' => ((int) $status['id'] ?? null) ?: null));
        }
        //endregion Status

        //region Assingned status
        if (null !== $assignedBuyer) {
            $filters['assigned_buyer'] = (bool) $assignedBuyer;
            unset($filters['assigned']);
        }
        //endregion Assingned status

        //region Samples
        $paginator = $this->sampleOrdersRepository->paginate_samples($page, $this->samplesPerPage, $filters, array('creation_date' => 'DESC'));
        if (1 !== $page) {
            $this->ensurePageExists($page, $paginator['last_page'] ?? 1);
        }
        $this->prepareSamples($paginator['data'], $isSeller);
        //endregion Samples
        //endregion Ensure entities

        return $paginator;
    }

    /**
     * Returns the information used on page.
     */
    public function getPageInformation(Request $request, int $userId, bool $isSeller): array
    {
        //region Filters
        $pathFilters = $this->uriFiltersFactory->createFilters($request);
        $queryFilters = $this->queryFilterFactory->createFilters($request);
        $collectedFitlers = $this->withOwnerFilter(
            new ArrayCollection(array_merge($pathFilters->getValues(), $queryFilters->getValues())),
            $userId,
            $isSeller
        );

        $filters = arrayCollapse($this->prepareFilters($collectedFitlers)->getValues());
        $withAssignedBuyer = $filters['assigned'] ?? null;
        $statusToken = $filters['status'] ?? null;
        $orderId = $filters['order'] ?? null;
        $page = $filters['page'] ?? 1;
        $status = null;
        $statusId = null;
        //endregion Filters

        //region Ensure entities
        //region Status
        if (null !== $statusToken) {
            $status = $this->resolveStatusByToken($statusToken);
            $statusId = (int) $status['id'] ?? null;
            // $filters = array_replace($filters, array('status' => ((int) $status['id'] ?? null) ?: null));
        }
        //endregion Status

        //region Sample
        if (null !== $orderId) {
            $this->ensureSampleOrderExists($orderId);
            $this->ensureSampleOrderOwnership($orderId, $userId);
        }
        //endregion Sample

        //region Samples
        $paginator = $this->sampleOrdersRepository->getPaginator(
            array('conditions' => array_filter(
                array('assigned_buyer' => $withAssignedBuyer, 'status' => $statusId, 'order' => $orderId),
                function ($value) { return null !== $value; }
            )),
            $this->samplesPerPage,
            $page
        );
        if (1 !== $page) {
            $this->ensurePageExists($page, $paginator['last_page'] ?? 1);
        }
        //endregion Samples
        //endregion Ensure entities

        //region Filters metadata
        // Here we will collect metadata for URL builders on the page
        list($pathMetadata, $queryMetadata) = $this->getUrlBuilderMetadata($this->localeVocabulary);
        //endregion Filters metadata

        return array(
            'page'      => $page,
            'status'    => $status,
            'filters'   => array_filter($filters, function ($key) { return !in_array($key, array('buyer', 'seller', 'owned_by')); }, ARRAY_FILTER_USE_KEY),
            'paginator' => $paginator,
            'is_seller' => $isSeller,
            'statuses'  => $this->statusesRepository->find_with_sample_count_for_user($userId, $isSeller),
            'metadata'  => array(
                'url' => array(
                    'path'  => $pathMetadata,
                    'query' => $queryMetadata,
                ),
            ),
        );
    }

    /**
     * Returns the detailed information about the sample that will be used in view.
     */
    public function getSampleInformation(Request $request, ?int $sampleOrderId, int $userId, bool $isSeller): ?array
    {
        //region Filters
        $requestFilters = $this->withOwnerFilter($this->requestFilterFactory->createFilters($request), $userId, $isSeller);
        $filters = arrayCollapse($this->prepareFilters($requestFilters)->getValues());
        $withAssignedBuyer = $filters['assigned'] ?? null;
        $statusToken = $filters['status'] ?? null;
        //endregion Filters

        //region Ensure entities
        //region Status
        if (null !== $statusToken) {
            $status = $this->resolveStatusByToken($statusToken);
            $filters = array_replace($filters, array('status' => ((int) $status['id'] ?? null) ?: null));
        }
        //endregion Status

        //region Assingned status
        if (null !== $withAssignedBuyer) {
            $filters['assigned_buyer'] = (bool) $withAssignedBuyer;
            unset($filters['assigned']);
        }
        //endregion Assingned status

        //region Sample
        //region Security & access
        $this->ensureSampleOrderExists($sampleOrderId);
        $this->ensureSampleOrderOwnership($sampleOrderId, $userId);
        //endregion Security & access
        //endregion Sample

        // Now, to ensure consistency of the content on the page we must fetch sample order with additional filters
        // Sometimes it can result in NULL value
        // In that case we must return null to ensure that placeholder will be shown on the page
        // but not the NotFoundException message
        $rawSample = $this->sampleOrdersRepository->get_detailed_sample($sampleOrderId, $filters);
        if (null === $rawSample) {
            return null;
        }

        $sample = with($this->processSample($rawSample), function (array $sample) use ($isSeller) {
            return $this->processSampleStatus($sample ?? null, $isSeller ? 'seller' : 'buyer');
        });
        //endregion Sample

        return array(
            'resource_options' => (new ResourceOptions())->type(ResourceType::from(ResourceType::SAMPLE_ORDER))->id((string) $sample['id'] ?: null),
            'purchase_order'   => $sample['purchase_order'] ?? array(),
            'products'         => $sample['purchased_products'] ?? array(),
            'shipper'          => $sample['shipper'] ?? array(),
            'status'           => $sample['status'] ?? array(),
            'seller'           => $sample['seller'] ?? array(),
            'buyer'            => $sample['buyer'] ?? array(),
            'sample'           => $sample,
            'is_seller'        => $isSeller,
        );
    }

    /**
     * Returns the detailed information about the sample that will be used in view.
     */
    public function getSampleInformationForAdmin(int $sampleId): array
    {
        //region Security & access
        $this->ensureSampleOrderExists($sampleId);
        //endregion Security & access

        $sample = with($this->processSample($this->sampleOrdersRepository->get_detailed_sample($sampleId)), function (array $sample) {
            return $this->processSampleStatus($sample ?? null, 'ep_manager');
        });

        return array(
            'products'  => $sample['purchased_products'] ?? array(),
            'shipper'   => $sample['shipper'] ?? array(),
            'status'    => $sample['status'] ?? array(),
            'seller'    => $sample['seller'] ?? array(),
            'buyer'     => $sample['buyer'] ?? array(),
            'bill'      => $sample['bill'] ?? array(),
            'sample'    => $sample,
            'is_seller' => false,
        );
    }

    /**
     * Returns the sample statuses information for provided user.
     */
    public function getSampleStatusesForUser(int $userId, bool $isSeller): array
    {
        return array(
            'statuses' => array_column(
                $this->statusesRepository->find_with_sample_count_for_user($userId, $isSeller),
                'samples_count',
                'alias'
            ),
        );
    }

    /**
     * Prepares filters to be used in database request.
     */
    private function prepareFilters(Collection $filters): Collection
    {
        return $filters->map(function (ParameterBag $filter) {
            if (null !== ($formatter = $filter->get('formatter')) && $formatter instanceof Closure) {
                $formatter($filter, $filter->get('value') ?? null);
            }

            return array($filter->get('name') => $filter->get('value'));
        });
    }

    /**
     * Prepares the samples to be used in views.
     */
    private function prepareSamples(array &$samples, bool $isSeller): void
    {
        foreach ($samples as &$sampleEntry) {
            $sampleEntry = with(
                $this->processSample($sampleEntry),
                function (array $sample) use ($isSeller) {
                    return $this->processSampleStatus($sample ?? null, $isSeller ? 'seller' : 'buyer');
                }
            );
        }
    }

    /**
     * Processes the raw sample.
     *
     * @todo Must be changed to high level astraction in the future
     */
    private function processSample(array $sample): array
    {
        $sample['price'] = priceToUsdMoney($sample['price'] ?? 0);
        $sample['total'] = $sample['final_price'] = priceToUsdMoney($sample['final_price'] ?? $sample['price'] ?? 0);
        $sample['is_disputed'] = (bool) (int) ($sample['dispute_opened'] ?? 0);
        $sample['is_cancelling'] = (bool) (int) ($sample['cancel_request'] ?? 0);
        $sample['is_order_completed'] = !empty($sample['status']) && in_array($sample['status']['alias'] ?? null, array('order-completed'));

        if (null !== $sample['seller']) {
            $seller = &$sample['seller'];
            $btnChatSeller = new ChatButton(['recipient' => $seller['idu'], 'recipientStatus' => 'active', 'module' => 35, 'item' => $sample['id']], ['text' => 'Chat with seller']);
            $seller['btnChat'] = $btnChatSeller->button();
            $seller['logo'] = getDisplayImageLink(
                array('{ID}' => $seller['id_company'], '{FILE_NAME}' => $seller['logo']),
                'companies.main',
                array('thumb_size' => 1, 'no_image_group' => (int) $seller['group'])
            );
            $seller['url'] = getCompanyURL(array(
                'company_name' => $seller['name_company'],
                'id_company'   => $seller['id_company'],
                'index_name'   => $seller['slug'],
                'type'         => $seller['type'],
            ));
        }

        if (null !== $sample['buyer']) {
            $buyer = &$sample['buyer'];
            $btnChatBuyer = new ChatButton(['recipient' => $buyer['idu'], 'recipientStatus' => 'active', 'module' => 35, 'item' => $sample['id']], ['text' => 'Chat with buyer']);
            $buyer['btnChat'] = $btnChatBuyer->button();
            $buyer['url'] = getUserLink($buyer['fullname'], $buyer['idu'], 'buyer');
            $buyer['photo'] = getDisplayImageLink(
                array('{ID}' => $buyer['idu'], '{FILE_NAME}' => $buyer['photo']),
                'users.main',
                array('thumb_size' => 1, 'no_image_group' => (int) $buyer['group'])
            );
        }

        if (null !== $sample['shipper']) {
            $shipper = &$sample['shipper'];
            $shipper['logo'] = getDisplayImageLink(array('{FILE_NAME}' => $shipper['image']), 'international_shippers.main');
        }

        $decodableIndexes = array('purchased_products', 'purchase_order', 'purchase_order_timeline', 'timeline_countdowns', 'package_detail');
        foreach ($decodableIndexes as $indexName) {
            if (!isset($sample[$indexName])) {
                $sample[$indexName] = array();

                continue;
            }

            try {
                if (!\is_array($sample[$indexName] ?? null)) {
                    $sample[$indexName] = json_decode($sample[$indexName], true, JSON_THROW_ON_ERROR);
                }
            } catch (\Exception $exception) {
                $sample[$indexName] = array();
            }
        }

        foreach ($sample['purchased_products'] as &$orderedItem) {
            $orderedItem['url'] = makeItemUrl((int) $orderedItem['item_id'], $orderedItem['name']);
            $orderedItem['unit_price'] = priceToUsdMoney($orderedItem['unit_price'] ?? 0);
            $orderedItem['price'] = $orderedItem['unit_price']->multiply((int) ($orderedItem['quantity'] ?? 0));
            $orderedItem['photo'] = getDisplayImageLink(
                array('{ID}' => $orderedItem['snapshot_id'], '{FILE_NAME}' => $orderedItem['image']),
                'items.snapshot',
                array('thumb_size' => 1)
            );
        }

        return $sample;
    }

    /**
     * Processes the sample status.
     */
    private function processSampleStatus(?array $simpleOrder, ?string $userType = null): array
    {
        if (null === $simpleOrder['status'] || null === $userType) {
            return $simpleOrder;
        }

        $emptyDescription = array('mandatory' => '', 'optional' => '');

        try {
            $statusDescription = json_decode($simpleOrder['status']['description'], true, JSON_THROW_ON_ERROR);

            $simpleOrder['status']['description'] = $statusDescription[$userType]['text'] ?? $emptyDescription;
        } catch (\Exception $exception) {
            $simpleOrder['status']['description'] = $emptyDescription;
        }

        return $simpleOrder;
    }

    /**
     * Returns the list of filters with apeended owner filter.
     */
    private function withOwnerFilter(Collection $filters, int $userId, bool $isSeller): Collection
    {
        return new ArrayCollection(array_merge(
            array(new ParameterBag(array('type' => 'entity', 'name' => $isSeller ? 'seller' : 'buyer', 'value' => $userId))),
            $filters->slice(0)
        ));
    }

    /**
     * Returns status if it exists.
     *
     * @param int|string $statusToken
     */
    private function resolveStatusByToken(&$statusToken): ?array
    {
        if (null === $statusToken) {
            return null;
        }

        if (is_numeric($statusToken)) {
            $status = $this->statusesRepository->find((int) $statusToken);
        } elseif (is_string($statusToken)) {
            $status = $this->statusesRepository->find_by_alias($statusToken);
        }

        if (null === $status) {
            throw new NotFoundException("The status '{$statusToken}' is not found.", static::STATUS_NOT_FOUND_ERROR);
        }

        return $status;
    }

    /**
     * Ensures that the page exists.
     */
    private function ensurePageExists(int $currentPage, int $lastPage): void
    {
        if ($currentPage > $lastPage) {
            throw new OutOfBoundsException("The page '{$currentPage}' is not found", static::PAGE_NOT_FOUND_ERROR);
        }
    }

    /**
     * Returns the base I18N vocabulary.
     */
    private function getBaseVocabulary(): ParameterBag
    {
        return new ParameterBag(array(
            $this->defaultLocale => new ParameterBag(array(
                'page'     => 'page',
                'order'    => 'order',
                'status'   => 'status',
                'entity'   => 'entity',
                'keywords' => 'keywords',
                'assigned' => 'assigned-to-buyer',
            )),
        ));
    }

    /**
     * Returns the page filters.
     */
    private function getFiltersPrototypes(): Collection
    {
        return new ArrayCollection(array(
            new ParameterBag(array(
                'type'      => 'scalar:any',
                'name'      => 'status',
                'value'     => null,
                'allow'     => array('path', 'request'),
                'formatter' => function (ParameterBag $filter, $value) {
                    $filter->set('value', is_numeric($value) || is_string($value) ? $value : null);
                },
            )),
            new ParameterBag(array(
                'type'      => 'scalar:number:page',
                'name'      => 'page',
                'value'     => null,
                'allow'     => array('path', 'request'),
                'formatter' => function (ParameterBag $filter, $value) {
                    $filter->set('value', null !== $value ? (int) $value : null);
                },
            )),
            new ParameterBag(array(
                'type'      => 'option:boolean',
                'name'      => 'assigned',
                'value'     => null,
                'allow'     => array('path', 'request'),
                'formatter' => function (ParameterBag $filter, $value) {
                    $filter->set('value', null !== $value ? (int) filter_var($value, FILTER_VALIDATE_BOOLEAN) : null);
                },
            )),
            new ParameterBag(array(
                'type'      => 'entity',
                'name'      => 'order',
                'value'     => null,
                'allow'     => array('path', 'request'),
                'formatter' => function (ParameterBag $filter, $value) {
                    $filter->set('value', null !== $value ? (int) $value : null);
                },
            )),
            new ParameterBag(array(
                'type'      => 'raw',
                'name'      => 'keywords',
                'value'     => null,
                'allow'     => array('query', 'request'),
                'formatter' => function (ParameterBag $filter, $value) {
                    $filter->set('value', is_string($value) ? $value : null);
                },
            )),
        ));
    }

    /**
     * Returns metadata for URL builders collected from known page filters.
     *
     * @return mixed[]
     */
    private function getUrlBuilderMetadata(ParameterBag $i18nVocabulary): array
    {
        $pathMetadata = array();
        if (preg_match_all('/\\{\\{([\\p{L}]*)\\}\\}/', $this->currentPathFilterPattern, $matching)) {
            $knownPathFilters = $this->pathFiltersPrototypes->map(function (ParameterBag $filter) { return $filter->get('name'); })->getValues();
            $allowedPathFilters = array_filter($matching[1] ?? array(), function ($name) use ($knownPathFilters) { return in_array($name, $knownPathFilters); });
            $pathMetadata = $this->pathFiltersPrototypes
                ->map(function (ParameterBag $prototype) use ($allowedPathFilters, $i18nVocabulary) {
                    if (!in_array($key = $prototype->get('name'), $allowedPathFilters)) {
                        return null;
                    }

                    return array('key' => $key, 'name' => $i18nVocabulary->get($key), 'type' => $prototype->get('type'), 'position' => array_search($key, $allowedPathFilters));
                })
                ->filter(function (?array $entry) { return null !== $entry; })
                ->getValues()
            ;
        }

        $queryMetadata = $this->queryFiltersPrototypes
            ->map(function (ParameterBag $prototype) use ($i18nVocabulary) {
                return array(
                    'key'      => $key = $prototype->get('name'),
                    'name'     => $i18nVocabulary->get($key),
                    'type'     => $prototype->get('type'),
                    'position' => null,
                );
            })
            ->filter(function (?array $entry) { return null !== $entry; })
            ->getValues()
        ;

        return array($pathMetadata, $queryMetadata);
    }

    /**
     * Generate Invoice in format PDF.
     *
     * @param int $idOrder
     *
     * @deprecated
     */
    private function generateInvoicePdf($idOrder, TinyMVC_Library_Make_Pdf $pdf_library)
    {
        try {
            $pdf_library->sample_order_invoice($idOrder)->Output('Invoice_for_sample_order_' . orderNumberOnly($idOrder) . '.pdf', 'I');
        } catch (NotFoundException $exception) {
            //throw $th;
        }
    }

    /**
     * Generate Contract in format PDF.
     *
     * @param int $idOrder
     *
     * @deprecated
     */
    private function generateContractPdf($idOrder, TinyMVC_Library_Make_Pdf $pdf_library)
    {
        try {
            $pdf_library->sample_order_contract($idOrder)->Output('Contract_for_sample_order_' . orderNumberOnly($idOrder) . '.pdf', 'I');
        } catch (NotFoundException $exception) {
            //throw $th;
        }
    }
}
