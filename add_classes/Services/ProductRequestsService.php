<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Contracts\EmailMetadataProviderInterface;
use App\Common\Exceptions\QueryException;
use App\Common\Http\Request;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\ValidationException;
use App\Validators\NameAndEmailValidator;
use App\Validators\ProductRequestValidator;
use DateTimeImmutable;
use Product_Requests_Model;
use RuntimeException;
use TinyMVC_Library_validator;

use function Symfony\Component\String\u;

final class ProductRequestsService
{
    public const EMAIL_BAD_ERROR = 0x000036502;
    public const STORAGE_WRITE_ERROR = 0x000036501;

    /**
     * The product requests repository.
     *
     * @var Product_Requests_Model
     */
    private $productRequests;

    /**
     * Creates instance of the product requests service.
     */
    public function __construct(Product_Requests_Model $productRequests)
    {
        $this->productRequests = $productRequests;
    }

    /**
     * Adds one product request.
     *
     * @throws ValidationException if failed validation
     * @throws QueryException      if failed to write record to DB
     */
    public function addRequest(Request $request, ?int $userId, ?EmailMetadataProviderInterface $emailMetadataProvider): array
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(\library(TinyMVC_Library_validator::class));
        $validator = new ProductRequestValidator($adapter);
        if (null === $userId) {
            $validator = new AggregateValidator(array($validator, new NameAndEmailValidator($adapter)));
        }

        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to add product request due to validation errors.', 0, null, $validator->getViolations());
        }
        //endregion Validation

        //region Email check
        $email = $request->request->get('email') ?? null;
        $emailMetadata = null;
        if (null !== $email && null !== $emailMetadataProvider) {
            $response = (object) $emailMetadataProvider->getMetadata($email);
            $emailMetadata = array(
                'status'  => $response->status,
                'details' => \json_decode($response->fullResponseJson) ?? null,
            );
        }
        //endregion Email check

        //region Save
        $now = new DateTimeImmutable();
        $productRequest = array(
            'id_user'                => $userId,
            'id_category'            => $request->request->getInt('category') ?: null,
            'id_departure_country'   => $request->request->getInt('departure_country') ?: null,
            'id_destination_country' => $request->request->getInt('destination_country') ?: null,
            'email'                  => $request->request->get('email') ?? null,
            'name'                   => $request->request->get('name') ?? null,
            'title'                  => $request->request->get('title') ?? null,
            'details'                => $request->request->get('details') ?? null,
            'quantity'               => $request->request->getInt('quantity') ?? 0,
            'email_meta'             => $emailMetadata,
            'start_price'            => \priceToUsdMoney($request->request->get('start_price')),
            'final_price'            => \priceToUsdMoney($request->request->get('final_price')),
        );

        try {
            if (!($productRequestId = $this->productRequests->insertOne($productRequest))) {
                throw new RuntimeException('Failed to write product request into the database.');
            }

            $productRequest = \array_merge(
                array('id' => (int) $productRequestId),
                $productRequest,
                array('creation_date' => clone $now, 'update_date' => clone $now)
            );
        } catch (\Exception $exception) {
            throw QueryException::executionFailed($this->productRequests->getHandler(), $exception, static::STORAGE_WRITE_ERROR);
        }
        //endregion Save

        return array((int) $productRequestId, (object) \arrayCamelizeAssocKeys($productRequest));
    }

    /**
     * Returns the paginator that contains the list of product requests compatible with datagrid.
     */
    public function getGridRequests(Request $request, ?int $perPage, ?int $page = 1): array
    {
        $paginator = $this->productRequests->paginate_for_grid(
            \dtConditions($request->request->all(), array(
                array('as' => 'category',            'key' => 'category',      'type' => 'int'),
                array('as' => 'departure_country',   'key' => 'country_from',  'type' => 'int'),
                array('as' => 'destination_country', 'key' => 'country_to',    'type' => 'int'),
                array('as' => 'quantity_from',       'key' => 'quantity_from', 'type' => 'int'),
                array('as' => 'quantity_to',         'key' => 'quantity_to',   'type' => 'int'),
                array('as' => 'price_from',          'key' => 'price_from',    'type' => 'priceToUsdMoney'),
                array('as' => 'price_to',            'key' => 'price_to',      'type' => 'priceToUsdMoney'),
                array('as' => 'created_from_date',   'key' => 'created_from',  'type' => 'getDateFormat:m/d/Y,Y-m-d'),
                array('as' => 'created_to_date',     'key' => 'created_to',    'type' => 'getDateFormat:m/d/Y,Y-m-d'),
                array('as' => 'updated_from_date',   'key' => 'updated_from',  'type' => 'getDateFormat:m/d/Y,Y-m-d'),
                array('as' => 'updated_to_date',     'key' => 'updated_to',    'type' => 'getDateFormat:m/d/Y,Y-m-d'),
                array('as' => 'search',              'key' => 'search',        'type' => 'cut_str:200'),
            )),
            \array_column(
                \dt_ordering($request->request->all(), array(
                    'quantity'    => 'quantity',
                    'updated_at'  => 'update_date',
                    'created_at'  => 'creation_date',
                    'start_price' => 'start_price',
                    'final_price' => 'final_price',
                )),
                'direction',
                'column'
            ),
            $perPage,
            $page
        );
        $paginator['data'] = array_map(
            function (array $entry) { return $this->processProductRequestForGrid($entry); },
            $paginator['data']
        );

        return $paginator;
    }

    /**
     * Transforms the product request into format accepted by datagrid.
     */
    private function processProductRequestForGrid(array $productRequest): array
    {
        $requestId = $productRequest['id'];
        $user = $productRequest['user'] ?? null;
        $category = $productRequest['category'] ?? null;
        $isRegistered = null !== $productRequest['user'];
        $emailMetadata = $productRequest['email_meta'] ?? null;
        $departureCountry = $productRequest['departure_country'] ?? null;
        $destinationCountry = $productRequest['destination_country'] ?? null;
        $emailStatusColors = array(
            'Ok'        => 'success',
            'Catch-All' => 'warning',
            'Unknown'   => 'warning',
            'Bad'       => 'danger',
        );

        //region User
        if (!$isRegistered) {
            $user = array(
                'name'         => $productRequest['name'] ?? null,
                'email'        => $productRequest['email'] ?? null,
                'email_status' => $emailMetadata['status'] ?? 'Unknown',
            );
        }

        $userLink = null;
        $userName = null !== $user['name'] ? \cleanOutput($user['name']) : null;
        $userLabel = '<span class="label label-default" title="This user is not registered">Not registered</span>';
        $userEmail = '&mdash;';
        $emailLabel = '';
        if (null !== $userEmail) {
            $userEmail = "<a href=\"mailto: {$user['email']}\">{$user['email']}</a>";
            $statusText = \cleanOutput($user['email_status']);
            $statusColor = $emailStatusColors[$user['email_status']] ?? 'default';
            $emailLabel = "<span class=\"label label-{$statusColor}\" title=\"Email status: {$statusText}\">{$statusText}</span>";
        }
        if ($isRegistered) {
            $userUrl = \getUserLink($user['name'], $user['id'], 'buyer');
            $userLabel = '<span class="label label-success" title="This user is registered">Registered</span>';
            $userLink = <<<USER_LINK
            <a href="{$userUrl}"
                class="ep-icon ep-icon_user"
                title="View personal page of {$userName}"
                target="_blank">
            </a>
            </br>
            USER_LINK;
        }
        $userInfo = <<<USER_INFO
        <div class="tal">
            <div class="w-100pr" style="min-height: 40px">
                {$userLink}
                <span>{$userName}</span>
                <br>
                {$userEmail}
                <br>
                {$userLabel} {$emailLabel}
            </div>
        </div>
        USER_INFO;
        //endregion User

        //region Product
        $productTitle = \cleanOutput($productRequest['title']);
        $productDetails = null !== $productRequest['details'] ? \cleanOutput($productRequest['details']) : '&mdash;';
        $productCategory = null !== $category ? \cleanOutput($category['name']) : '&mdash;';
        $productInfo = <<<PRODUCT_INFO
        <div class="tal">
            <div class="w-100pr" style="min-height: 40px">
                <strong>{$productTitle}</strong>
                <br>
                <span><strong>Category:</strong> {$productCategory}</span>
                <br>
                <span><strong>Details:</strong> {$productDetails}</span>
            </div>
        </div>
        PRODUCT_INFO;
        //endregion Product

        //region Shipping
        $shipTo = '<div class="h-40 vam"><span class="lh-40">&mdash;</span></div>';
        $shipFrom = '<div class="h-40 vam"><span class="lh-40">&mdash;</span></div>';
        if (null !== $departureCountry) {
            $countryFlag = \getCountryFlag($departureCountry['name']);
            $countryName = \cleanOutput($departureCountry['name']);
            $countryId = \cleanOutput($departureCountry['id']);
            $shipFrom = <<<FROM
            <a class="dt_filter" data-value-text="{$countryName}" data-value="{$countryId}" data-title="Country (from)" data-name="country_from">
                <img width="24" height="24" src="{$countryFlag}" title="Filter by: {$countryName}" alt="{$countryName}"/>
            </a>
            <br>
            <span>{$countryName}</span>
            FROM;
        }
        if (null !== $destinationCountry) {
            $countryFlag = \getCountryFlag($destinationCountry['name']);
            $countryName = \cleanOutput($destinationCountry['name']);
            $countryId = \cleanOutput($destinationCountry['id']);
            $shipTo = <<<TO
            <a class="dt_filter" data-value-text="{$countryName}" data-value="{$countryId}" data-title="Country (to)" data-name="country_to">
                <img width="24" height="24" src="{$countryFlag}" title="Filter by: {$countryName}" alt="{$countryName}"/>
            </a>
            <br>
            <span>{$countryName}</span>
            TO;
        }
        //endregion Shipping

        return array(
            'request'             => $requestId,
            'user'                => $userInfo,
            'product'             => $productInfo,
            'quantity'            => $productRequest['quantity'] ?? 0,
            'start_price'         => u('$')->append(\get_price($productRequest['start_price'] ?? 0, false)),
            'final_price'         => u('$')->append(\get_price($productRequest['final_price'] ?? 0, false)),
            'created_at'          => \getDateFormatIfNotEmpty($productRequest['creation_date']),
            'updated_at'          => \getDateFormatIfNotEmpty($productRequest['update_date']),
            'departure_country'   => $shipFrom,
            'destination_country' => $shipTo,
        );
    }
}
