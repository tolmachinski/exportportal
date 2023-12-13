<?php

use App\Common\Contracts\EmailMetadataProviderInterface;
use App\Common\Exceptions\BadEmailException;
use App\Common\Exceptions\QueryException;
use App\Common\Http\Request;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ValidationException;
use App\Services\ProductRequestsService;

/**
 * Product Requests controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 */
class Product_Requests_Controller extends TinyMVC_Controller
{
    public const EMAIL_UNACCEPTED_STATUS = 'Bad';

    /**
     * Shows page with product request administration dasboard.
     */
    public function administration(): void
    {
        checkIsLogged();
        checkPermision('view_product_requests');
        checkPermision('monitor_product_requests');

        views(array('admin/header_view', 'admin/product_requests/index_view', 'admin/footer_view'), array(
            'title'      => 'Product Requests',
            'countries'  => model(Country_Model::class)->get_countries(),
            'categories' => model(Category_Model::class)->get_industries(),
        ));
    }

    /**
     * Handles requests to the forms.
     */
    public function popup_forms(): void
    {
        switch (uri()->segment(3)) {
            case 'send':
                if (logged_in()) {
                    checkPermisionAjaxModal('create_product_request');
                }

                $this->show_product_request_form();

                break;
            default:
                messageInModal(translate('systmess_error_route_not_found'));

                break;
        }
    }

    /**
     * Handles ajax operations.
     */
    public function ajax_operations(): void
    {
        checkIsAjax();

        $request = request();

        switch (uri()->segment(3)) {
            case 'list':
                checkIsLoggedAjax();
                checkPermisionAjax('view_product_requests');
                checkPermisionAjax('monitor_product_requests');

                $this->get_product_requests_list($request, $request->request->getInt('iDisplayStart', 0), $request->request->getInt('iDisplayLength', 10));

                break;
            case 'send':
                if (logged_in()) {
                    checkPermisionAjax('create_product_request');
                }

                $this->create_product_request($request, (int) privileged_user_id() ?: null);

                break;
            default:
                json(array('message' => translate('systmess_error_route_not_found'), 'mess_type' => 'error'), 404);

                break;
        }
    }

    /**
     * Shows the modal popup which can be used to send product request.
     */
    private function show_product_request_form(): void
    {
        views()->display('new/product_requests/popups/send_request_view', array(
            'countries'  => model(Country_Model::class)->get_countries(),
            'industries' => model(Category_Model::class)->get_industries(),
        ));
    }

    /**
     * Returns the product requests detailed list.
     */
    private function get_product_requests_list(Request $request, int $offset, int $per_page): void
    {
        list('data' => $products, 'total' => $total) = (new ProductRequestsService(model(Product_Requests_Model::class)))->getGridRequests(
            $request,
            $per_page,
            $offset / $per_page + 1,
        );

        jsonResponse(null, 'success', array(
            'sEcho'                => $request->request->getInt('sEcho', 0),
            'aaData'               => $products ?? array(),
            'iTotalRecords'        => $total ?? 0,
            'iTotalDisplayRecords' => $total ?? 0,
        ));
    }

    /**
     * Creates a product request.
     */
    private function create_product_request(Request $request, ?int $user_id): void
    {
        //region Email checker
        $email_metadata_provider = null;
        if (null === $user_id && 'dev' !== config('env.APP_ENV')) {
            $email_metadata_provider = new class() implements EmailMetadataProviderInterface {
                /** {@inheritdoc} */
                public function getMetadata(string $email): array
                {
                    $response = \arrayCamelizeAssocKeys(\checkEmailDeliverability($email, true));
                    if (Product_Requests_Controller::EMAIL_UNACCEPTED_STATUS === $response['status'] ?? null) {
                        throw new BadEmailException('The provided email cannot be used - it is invalid.', $response, ProductRequestsService::EMAIL_BAD_ERROR);
                    }

                    return $response;
                }
            };
        }
        //endregion Email checker

        /** @var Product_Requests_Model $productRequestModel */
        $productRequestModel = model(Product_Requests_Model::class);

        //region Add request
        try {
            list($productRequestId, $productRequest) = (new ProductRequestsService($productRequestModel))->addRequest(
                $request,
                $user_id,
                $email_metadata_provider
            );
        } catch (ValidationException $exception) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) { return $violation->getMessage(); },
                    \iterator_to_array($exception->getValidationErrors()->getIterator())
                )
            );
        } catch (QueryException | BadEmailException | RuntimeException | Exception $exception) {
            $system_message = $exception instanceof BadEmailException ? translate('systmess_error_undeliverable_email', array('[USER_EMAIL]' => $request->request->get('email') ?? null)) : translate('systmess_internal_server_error');
            jsonResponse(
                throwableToMessage(
                    $exception,
                    $system_message
                ),
                'error',
                !DEBUG_MODE ? array() : array('errors' => array(throwableToArray($exception)))
            );
        }
        //endregion Add request

        //region Create ticket in Zoho Desk
        if (config('env.APP_ENV') === 'prod') {
            $email = email_session() ?? $productRequest->email;

            /** @var Category_Model $categoryModel */
            $categoryModel = model(Category_Model::class);
            $industry = $categoryModel->get_category($productRequest->idCategory);

            if (!empty($productRequest->idDepartureCountry) || !empty($productRequest->idDestinationCountry)) {
                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);
                $countries = $countryModel->get_simple_countries(array_filter([$productRequest->idDepartureCountry, $productRequest->idDestinationCountry]));
            }

            $productRequestTicketData = [
                'departmentId'          => (int) config('env.ZOHO_EP_DEPARTMENT_ID'),
                'classification'        => 'Request',
                'category'              => 'Product request', // don't change it
                'prepareDescription'    => true,
                'subject'               => cut_str('PR: ' . $productRequest->title, 255), //limited by zoho desk ticket
                'email'                 => $email,
                'contact'               => [
                    'lastName'  => app()->session->lname ?? cut_str($productRequest->name, 80), //limited by zoho crm contact
                    'firstName' => app()->session->fname ?? (string) substr($productRequest->name, 80, 40), //limited by zoho desk contact (100), de facto 40, 41 throw Internal Server Error
                    'email'     => $email,
                ],
                'description'           => [
                    'User id'               => id_session() ?: '&mdash;',
                    'User name'             => user_name_session() ?: $productRequest->name,
                    'Product name'          => $productRequest->title,
                    'Category'              => $industry['name'],
                    'Amount'                => $productRequest->quantity ?: '&mdash;',
                    'Start price'           => $productRequest->startPrice->isZero() ? '&mdash;' : moneyToDecimal($productRequest->startPrice) . ' ' . $productRequest->startPrice->getCurrency()->getCode(),
                    'Final price'           => $productRequest->finalPrice->isZero() ? '&mdash;' : moneyToDecimal($productRequest->finalPrice) . ' ' . $productRequest->finalPrice->getCurrency()->getCode(),
                    'Destination country'   => $countries[$productRequest->idDestinationCountry]['country'] ?? '&mdash;',
                    'Departure country'     => $countries[$productRequest->idDepartureCountry]['country'] ?? '&mdash;',
                    'Details'               => $productRequest->details ?: '&mdash;',
                ]
            ];

            /** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
            $zohoDeskLibrary = library(TinyMVC_Library_Zoho_Desk::class);

            try {
                $zohoDeskLibrary->createTicket($productRequestTicketData);
            } catch (Exception $e) {

            }
        }
        //endregion Create ticket in Zoho Desk

        jsonResponse(translate('product_requests_create_request_success_text'), 'success', array(
            'data' => array(
                'product' => array('id' => $productRequestId, 'email' => $productRequest->email ?? null),
            ),
        ));
    }
}

// End of file product_requests.php
// Location: /tinymvc/myapp/controllers/product_requests.php
