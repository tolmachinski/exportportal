<?php

namespace App\Common\DependencyInjection;

final class StaticContainer extends \Symfony\Component\DependencyInjection\Container
{
    protected $containerDir;

    protected $parameters = [];

    protected $getServiceProxy;

    private $dynamicParameters = [];

    private $loadedDynamicParameters = [
        'kernel.runtime_environment'      => false,
        'kernel.secret'                   => false,
        'backstop.enabled'                => false,
        'mailer.envelope.support'         => false,
        'mailer.envelope.no_reply'        => false,
        'mailer.envelope.base_url'        => false,
        'mailer.envelope.img_url'         => false,
        'mailer.envelope.preview_url'     => false,
        'mailer.envelope.unsubscribe_url' => false,
    ];

    private $debugMode = false;

    private $buildParameters;

    public function __construct(array $buildParameters = [], $containerDir = __DIR__)
    {
        $this->parameters = $this->getDefaultParameters();
        $this->containerDir = $containerDir;
        $this->buildParameters = $buildParameters;
        $this->getServiceProxy = function (...$args) { return $this->getService(...$args); };
        $this->services = [];
        $this->privates = [];
        $this->privates['service_container'] = function () {};
        $this->syntheticIds = [
            'kernel'     => true,
            'controller' => true,
        ];
        $this->aliases = [
            'App\\Kernel'                                                                     => 'kernel',
            \TinyMVC_Controller::class                                                        => 'controller',
            \TinyMVC_Library_Fastcache::class                                                 => 'fastcache',
            \TinyMVC_Load::class                                                              => 'loader',
            \TinyMVC_View::class                                                              => 'renderer',
            \App\Bridge\Matrix\MatrixConnector::class                                         => 'matrix',
            \App\Bridge\Matrix\Room\RoomFactory::class                                        => 'matrix.room.factory',
            \App\Bridge\Matrix\Room\SpaceFactory::class                                       => 'matrix.space.factory',
            \App\Common\Database\Connection\PdoRegistry::class                                => 'pdo',
            \App\Common\Database\Connection\Registry::class                                   => 'doctrine',
            \App\Common\DependencyInjection\ServiceLocator\LibraryLocator::class              => 'library_locator',
            \App\Common\DependencyInjection\ServiceLocator\ModelLocator::class                => 'model_locator',
            \App\Common\Encryption\Storage\FileKeyStorage::class                              => 'app.encryption.file_key_storage',
            \App\Common\Media\Thumbnail\ThumbnailReaderInterface::class                       => 'media.thumnail_reader',
            \App\Common\Messenger\HubInterface::class                                         => 'messenger',
            \App\Common\Transformers\CompanyPickOfTheMonthForBaiduTransformer::class          => 'app.transformer.pick_of_month.company',
            \App\Common\Transformers\ItemPickOfTheMonthForBaiduTransformer::class             => 'app.transformer.pick_of_month.item',
            \App\Common\Transformers\ItemsListForBaiduTransformer::class                      => 'app.transformer.items.baidu',
            \App\Common\Validation\Legacy\ValidatorAdapter::class                             => 'validation.adapter',
            \App\DataProvider\AccountProvider::class                                          => 'app.data_provider.account',
            \App\DataProvider\B2bIndexedRequestProvider::class                                => 'app.data_provider.b2b_indexed_request',
            \App\DataProvider\B2bRequestProvider::class                                       => 'app.data_provider.b2b_request',
            \App\DataProvider\CompanyEditRequestProvider::class                               => 'app.data_provider.company_edit_request',
            \App\DataProvider\CompanyProvider::class                                          => 'app.data_provider.company',
            \App\DataProvider\DroplistItemsDataProvider::class                                => 'app.data_provider.droplist',
            \App\DataProvider\IndexedBlogDataProvider::class                                  => 'app.data_provider.indexed_blog',
            \App\DataProvider\IndexedProductDataProvider::class                               => 'app.data_provider.product',
            \App\DataProvider\MatrixUserProvider::class                                       => 'app.data_provider.maxtrix_user',
            \App\DataProvider\NavigationBarStateProvider::class                               => 'app.data_provider.navbar_state',
            \App\DataProvider\NotificationMetadataProvider::class                             => 'app.data_provider.notification_metadata',
            \App\DataProvider\ProfileEditRequestProvider::class                               => 'app.data_provider.profile_edit_request',
            \App\DataProvider\User\UserDataListProvider::class                                => 'app.data_provider.user_data_list',
            \App\DataProvider\UserProfileProvider::class                                      => 'app.data_provider.user_profile',
            \App\DataProvider\UserRoomsProvider::class                                        => 'app.data_provider.user_room',
            \App\DataProvider\VerificationDocumentProvider::class                             => 'app.data_provider.verification_documents',
            \App\Plugins\EPDocs\Rest\RestClient::class                                        => 'epdocs_client',
            \App\Renderer\CompanyEditRequestDatatableRenderer::class                          => 'app.renderer.datatable.company_edit_request',
            \App\Renderer\CompanyEditRequestViewRenderer::class                               => 'app.renderer.view.company_edit_request',
            \App\Renderer\CompanyEditViewRenderer::class                                      => 'app.renderer.view.company_edit',
            \App\Renderer\ProfileEditRequestDatatableRenderer::class                          => 'app.renderer.datatable.profile_edit_request',
            \App\Renderer\ProfileEditRequestViewRenderer::class                               => 'app.renderer.view.profile_edit_request',
            \App\Renderer\UserProfileEditViewRenderer::class                                  => 'app.renderer.view.profile_edit',
            \App\Renderer\VerificationDocumentsViewRenderer::class                            => 'app.renderer.view.verification_documents',
            \App\Seo\SeoPageService::class                                                    => 'app.seo.seo_page_service',
            \App\Services\B2b\B2bRequestProcessingService::class                              => 'app.processing.b2b.request',
            \App\Services\BlogCategoryRouteResolverService::class                             => 'app.blog_category_route_resolver',
            \App\Services\BlogCategoryRouteResolverService::class                             => 'app.blog_category_route_resolver',
            \App\Services\BuyerIndustryOfInterestService::class                               => 'app.buyer_industry_of_interest',
            \App\Services\CalendarEpEventsService::class                                      => 'app.calendar_ep_events_service',
            \App\Services\ChatBindingService::class                                           => 'app.chat.binding',
            \App\Services\Company\CompanyGuardService::class                                  => 'app.processing.company.access',
            \App\Services\Company\CompanyMediaProcessorService::class                         => 'app.processing.company.media',
            \App\Services\Company\CompanyProcessingService::class                             => 'app.processing.company',
            \App\Services\EditRequest\CompanyEditRequestDocumentsService::class               => 'app.edit_request.company.documents',
            \App\Services\EditRequest\CompanyEditRequestProcessingService::class              => 'app.edit_request.company.processing',
            \App\Services\EditRequest\ProfileEditRequestDocumentsService::class               => 'app.edit_request.profile.documents',
            \App\Services\EditRequest\ProfileEditRequestProcessingService::class              => 'app.edit_request.profile.processing',
            \App\Services\PhoneCodesService::class                                            => 'app.phone_codes',
            \App\Services\Profile\UserProfileProcessingService::class                         => 'app.processing.profile',
            \App\Services\SampleOrdersService::class                                          => 'app.sample_orders',
            \Doctrine\Persistence\ConnectionRegistry::class                                   => 'doctrine',
            \ExportPortal\Bridge\Symfony\Component\Messenger\HubInterface::class              => 'messenger',
            \ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface::class        => 'messenger',
            \ExportPortal\Contracts\Filesystem\FilesystemProviderInterface::class             => 'filesystem.provider',
            \ExportPortal\Matrix\Client\Client::class                                         => 'matrix.client',
            \GuzzleHttp\ClientInterface::class                                                => 'guzzle.http_client',
            \Intervention\Image\ImageManager::class                                           => 'intervention_image.manager',
            \Money\MoneyFormatter::class                                                      => 'money.formatter',
            \Psr\Log\LoggerInterface::class                                                   => 'logger',
            \Symfony\Component\Console\CommandLoader\CommandLoaderInterface::class            => 'console.command_loader',
            \Symfony\Component\Mime\MimeTypesInterface::class                                 => 'mime_types',
            \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag::class           => 'parameter_bag',
            \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class  => 'parameter_bag',
            \Symfony\Component\EventDispatcher\EventDispatcher::class                         => 'event_dispatcher',
            \Symfony\Component\EventDispatcher\EventDispatcherInterface::class                => 'event_dispatcher',
            \Symfony\Component\HttpFoundation\RequestStack::class                             => 'request_stack',
            \Symfony\Component\HttpFoundation\Session\Session::class                          => 'session',
            \Symfony\Component\HttpFoundation\Session\SessionInterface::class                 => 'session',
            \Symfony\Component\Notifier\Chatter::class                                        => 'chatter',
            \Symfony\Component\Notifier\ChatterInterface::class                               => 'chatter',
            \Symfony\Component\Notifier\Notifier::class                                       => 'notifier',
            \Symfony\Component\Notifier\NotifierInterface::class                              => 'notifier',
            \Symfony\Component\Notifier\Texter::class                                         => 'texter',
            \Symfony\Component\Notifier\TexterInterface::class                                => 'texter',
            \Symfony\Component\Mailer\Mailer::class                                           => 'mailer.mailer',
            \Symfony\Component\Mailer\MailerInterface::class                                  => 'mailer.mailer',
            \Symfony\Component\Mime\BodyRendererInterface::class                              => 'mailer.bridge.mime_body_renderer',
            'database_connection'                                                             => 'doctrine.dbal.default_connection',
        ];
        $this->methodMap = [
            'doctrine.dbal.default_connection'            => 'getDoctrineDbalDefaultConnectionService',
            'console.command_loader'                      => 'getConsoleCommandLoaderService',
            'guzzle.http_client'                          => 'getGuzzleHttpClientService',
            'event_dispatcher'                            => 'getEventDispatcherService',
            'library_locator'                             => 'getLibraryLocatorService',
            'model_locator'                               => 'getModelLocatorService',
            'request_stack'                               => 'getRequestStackService',
            'fastcache'                                   => 'getFastCacheService',
            'renderer'                                    => 'getRendererService',
            'session'                                     => 'getSessionService',
            'loader'                                      => 'getLoaderService',
            'pdo'                                         => 'getPdoService',
            'logger'                                      => 'getLoggerService',
            'logger.filesystem'                           => 'getLoggerFilesystemService',
            'matrix'                                      => 'getMatrixService',
            'chatter'                                     => 'getChatterService',
            'texter'                                      => 'getTexterService',
            'notifier'                                    => 'getNotifierService',
            'doctrine'                                    => 'getDoctrineService',
            'messenger'                                   => 'getMessengerService',
            'cache.app'                                   => 'getCacheAppService',
            'epdocs_client'                               => 'getEpDocsRestService',
            'parameter_bag'                               => 'getParameterBag',
            'matrix.client'                               => 'getMatrixClientApiLocatorService',
            'validation.adapter'                          => 'getValidationAdapterService',
            'matrix.room.factory'                         => 'getMatrixRoomFactoryService',
            'matrix.space.factory'                        => 'getMatrixSpaceFactoryService',
            'chatter.transports'                          => 'getNotifierChatterTransportsService',
            'texter.transports'                           => 'getNotifierTexterTransportsService',
            'mailer.transports'                           => 'getMailerTransportsService',
            'mailer.mailer'                               => 'getMailerService',
            'mailer.bridge.mime_body_renderer'            => 'getMailerBridgeMimeBodyRendererService',
            'app.buyer_industry_of_interest'              => 'getBuyerIndustryOfInterestService',
            'app.calendar_ep_events_service'              => 'getCalendarEpEventsService',
            'app.chat.binding'                            => 'getChatBindingService',
            'app.data_provider.account'                   => 'getDataProviderAccountProviderService',
            'app.data_provider.b2b_indexed_request'       => 'getDataProviderB2bIndexedRequestProviderService',
            'app.data_provider.b2b_request'               => 'getDataProviderB2bRequestProviderService',
            'app.data_provider.company_edit_request'      => 'getDataProviderCompanyEditRequestProviderService',
            'app.data_provider.company'                   => 'getDataProviderCompanyProviderService',
            'app.data_provider.indexed_blog'              => 'getDataProviderIndexedBlogProviderService',
            'app.data_provider.maxtrix_user'              => 'getMatrixUserProviderService',
            'app.data_provider.navbar_state'              => 'getNavigationBarStateProviderService',
            'app.data_provider.notification_metadata'     => 'getDataProviderNotificationMetadataProviderService',
            'app.data_provider.product'                   => 'getIndexedDataProviderProductService',
            'app.data_provider.profile_edit_request'      => 'getDataProviderProfileEditRequestProviderService',
            'app.data_provider.user_data_list'            => 'getUserDataListProviderService',
            'app.data_provider.user_profile'              => 'getDataProviderUserProfileProviderService',
            'app.data_provider.user_room'                 => 'getDataProviderUserRoomsProviderService',
            'app.data_provider.verification_documents'    => 'getDataProviderVerificationDocumentProviderService',
            'app.edit_request.company.documents'          => 'getEditRequestCompanyEditRequestDocumentsServiceService',
            'app.edit_request.company.processing'         => 'getCompanyEditRequestProcessingServiceService',
            'app.edit_request.profile.documents'          => 'getEditRequestProfileEditRequestDocumentsServiceService',
            'app.edit_request.profile.processing'         => 'getProfileEditRequestProcessingServiceService',
            'app.phone_codes'                             => 'getPhoneCodesService',
            'app.processing.company.media'                => 'getCompanyMediaProcessorServiceService',
            'app.processing.company'                      => 'getCompanyProcessingServiceService',
            'app.processing.company.access'               => 'getCompanyGuardServiceService',
            'app.processing.b2b.request'                  => 'getB2bRequestProcessingServiceService',
            'app.processing.profile'                      => 'getUserProfileProcessingServiceService',
            'app.renderer.datatable.company_edit_request' => 'getRendererDatatableCompanyEditRequestDatatableRendererService',
            'app.renderer.datatable.profile_edit_request' => 'getRendererDatatableProfileEditRequestDatatableRendererService',
            'app.renderer.view.company_edit_request'      => 'getRendererViewCompanyEditRequestViewRendererService',
            'app.renderer.view.company_edit'              => 'getRendererViewCompanyEditViewRendererService',
            'app.renderer.view.profile_edit_request'      => 'getRendererViewProfileEditRequestViewRendererService',
            'app.renderer.view.profile_edit'              => 'getRendererViewUserProfileEditViewRendererService',
            'app.renderer.view.verification_documents'    => 'getRendererViewVerificationDocumentsViewRendererService',
            'app.transformer.items.baidu'                 => 'getItemsListForBaiduTransformerService',
            'app.transformer.pick_of_month.company'       => 'getCompanyPickOfTheMonthForBaiduTransformerService',
            'app.transformer.pick_of_month.item'          => 'getItemPickOfTheMonthForBaiduTransformerService',
            'app.encryption.file_key_storage'             => 'getEncryptionFileKeyStorageService',
            'app.sample_orders'                           => 'getSampleOrdersService',
            'app.seo.seo_page_service'                    => 'getSeoPageServiceService',
            'intervention_image.manager'                  => 'getInterventionImageMangerService',
            'app.data_provider.product'                   => 'getIndexedDataProviderProductService',
            'app.blog_category_route_resolver'            => 'getBlogCategoryRouteResolverService',
            'filesystem.provider'                         => 'getFilesystemFilesystemproviderService',
            'media.thumnail_reader'                       => 'getMediaThumbnailReaderService',
            'mime_types'                                  => 'getMimeTypeService',
            'app.data_provider.droplist'                  => 'getDroplistItemsDataProviderService',
            'money.formatter'                             => 'getMoneyMoneyFormatterService',
        ];
        $this->debugMode = $this->getParameter('kernel.debug');
        foreach ($buildParameters['kernel.build_config']->get('env') ?? [] as $key => $value) {
            $this->loadedDynamicParameters["kernel.env.{$key}"] = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function compile()
    {
        throw new \LogicException('You cannot compile a static container.');
    }

    /**
     * {@inheritDoc}
     */
    public function isCompiled(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getRemovedIds(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     *
     * @return null|array|bool|float|int|string
     */
    public function getParameter($name)
    {
        if (isset($this->buildParameters[$name])) {
            return $this->buildParameters[$name];
        }

        if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters))) {
            throw new \InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function hasParameter($name): bool
    {
        if (isset($this->buildParameters[$name])) {
            return true;
        }

        return isset($this->parameters[$name]) || \array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($name, $value)
    {
        throw new \LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterBag(): \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
    {
        if (null === $this->parameterBag) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            foreach ($this->buildParameters as $name => $value) {
                $parameters[$name] = $value;
            }
            $this->parameterBag = new \Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag($parameters);
        }

        return $this->privates['paramter_bag'] = $this->parameterBag;
    }

    /**
     * Return the default ontainer parameters.
     *
     * @return array
     */
    protected function getDefaultParameters()
    {
        return [
            'kernel.app_config'                      => [],
            'kernel.project_dir'                     => \dirname(\dirname(__DIR__)),
            'kernel.environment'                     => 'dev',
            'kernel.debug'                           => true,
            'kernel.logs_dir'                        => (\dirname(\dirname(__DIR__)) . '' . \DIRECTORY_SEPARATOR . 'var/log'),
            'kernel.build_dir'                       => (\dirname(\dirname(__DIR__)) . '' . \DIRECTORY_SEPARATOR . 'var/cache'),
            'kernel.cache_dir'                       => (\dirname(\dirname(__DIR__)) . '' . \DIRECTORY_SEPARATOR . 'var/cache'),
            'kernel.controllers_dir'                 => (\dirname(\dirname(__DIR__)) . '' . \DIRECTORY_SEPARATOR . 'tinymvc/myapp/controllers'),
            'kernel.charset'                         => 'UTF-8',
            'kernel.container_class'                 => \get_class($this),
            'kernel.http_method_override'            => true,
            'kernel.trusted_hosts'                   => [],
            'kernel.default_locale'                  => 'en',
            'kernel.root_controller'                 => null,
            'kernel.root_controller.action'          => null,
            'kernel.error_controller'                => 'error_controller',
            'kernel.error_controller.action'         => 'index',
            'kernel.not_found_controller'            => null,
            'kernel.not_found_controller.action'     => null,
            'kernel.default_controller'              => 'default',
            'kernel.default_controller.action'       => 'index',
            'kernel.bundles_metadata'                => [],
            'kernel.routing_replacement_map'         => [],
            'kernel.runtime_config'                  => new \Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag(),
            'kernel.build_config'                    => new \Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag(),
            'database.connections'                   => [],
            'databases.default_connection'           => 'default',
            'debug.error_handler.throw_at'           => -1,
            'request_listener.http_port'             => 80,
            'request_listener.https_port'            => 443,
            'messenger.config_file'                  => \ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface::DEFAULT_CONFIG_FILE,
            'session.metadata.storage_key'           => '_sf2_meta',
            'session.metadata.update_threshold'      => 0,
            'session.storage.options'                => [
                'cache_limiter'   => '0',
                'cookie_secure'   => 'auto',
                'cookie_httponly' => true,
                'cookie_samesite' => 'lax',
                'gc_probability'  => 1,
            ],
            'mailer.templates.templates_table'       => 'emails_template',
            'mailer.templates.structure_table'       => 'emails_template_structure',
            'mailer.templates.view_path'             => 'admin/emails_template/email_elements/general/%s_view',
            'mailer.log.log_table'                   => 'monolog_logs',
            'app.base_uri'                           => 'http://localhost',
            'app.current_url'                        => 'http://localhost',
        ];
    }

    /**
     * Gets the private 'debug.stopwatch' shared service.
     *
     * @return \Symfony\Component\Stopwatch\Stopwatch
     */
    protected function getStopwatchService()
    {
        return $this->privates['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch(true);
    }

    /**
     * Gets the public 'request_stack' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestStack
     */
    protected function getRequestStackService()
    {
        return $this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack();
    }

    /**
     * Gets the private 'controller_resolver' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
     */
    protected function getControllerResolverService()
    {
        $controllerResolver = new \App\Common\Kernel\Controller\ControllerResolver($this, ($this->privates['monolog.logger.request'] ?? $this->getMonologLoggerRequestService()));
        if ($this->debugMode) {
            $controllerResolver = new \Symfony\Component\HttpKernel\Controller\TraceableControllerResolver(
                $controllerResolver,
                ($this->privates['debug.stopwatch'] ?? $this->getStopwatchService()),
                ($this->privates['arguments_resolver'] ?? $this->getArgumentResolverService())
            );
        }

        return $this->privates['controller_resolver'] = $controllerResolver;
    }

    /**
     * Gets the private 'arguments_resolver' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ArgumentResolver
     */
    protected function getArgumentResolverService()
    {
        $argumentsResolver = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver(
            ($this->privates['argument_metadata_factory'] ?? $this->privates['argument_metadata_factory'] = new \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory())
        );
        if ($this->debugMode) {
            $argumentsResolver = new \Symfony\Component\HttpKernel\Controller\TraceableArgumentResolver(
                $argumentsResolver,
                ($this->privates['debug.stopwatch'] ?? $this->getStopwatchService())
            );
        }

        return $this->privates['arguments_resolver'] = $argumentsResolver;
    }

    /**
     * Gets the public 'http_kernel' shared service.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernel
     */
    protected function getHttpKernelService()
    {
        return $this->services['http_kernel'] = new \Symfony\Component\HttpKernel\HttpKernel(
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['controller_resolver'] ?? $this->getControllerResolverService()),
            ($this->services['request_stack'] ?? $this->getRequestStackService()),
            ($this->privates['arguments_resolver'] ?? $this->getArgumentResolverService())
        );
    }

    /**
     * Gets the public 'guzzle.http_client' shared service.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getGuzzleHttpClientService()
    {
        return $this->services['guzzle.http_client'] = new \GuzzleHttp\Client();
    }

    /**
     * Gets the private 'guzzle.epdocs.http_client' shared service.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getGuzzleHttpEpDocsClientService()
    {
        return $this->privates['guzzle.epdocs.http_client'] = new \GuzzleHttp\Client([
            'base_uri' => $this->getEnv('EP_DOCS_HOST'),
            'handler'  => ($this->privates['guzzle.epdocs.http_client.handler_stack'] ?? $this->getGuzzleHttpEpDocsClientHandlerStackService())
        ]);
    }

    /**
     * Gets the private 'guzzle.epdocs.http_client.handler_stack' shared service.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getGuzzleHttpEpDocsClientHandlerStackService()
    {
        $instance = \GuzzleHttp\HandlerStack::create();
        $instance->push($this->privates['guzzle.epdocs.http_client.cache_middleware'] ?? $this->getGuzzleHttpEpDocsClientCaheMiddlewareService(), 'http_cache');

        return $this->privates['guzzle.epdocs.http_client.handler_stack'] = $instance;
    }

    /**
     * Gets the private 'guzzle.epdocs.http_client.cache_middleware' shared service.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getGuzzleHttpEpDocsClientCaheMiddlewareService()
    {
        return $this->privates['guzzle.epdocs.http_client.cache_middleware'] = new \Kevinrob\GuzzleCache\CacheMiddleware(
            ($this->privates['guzzle.epdocs.http_client.cache_middleware.strategy'] ?? $this->privates['guzzle.epdocs.http_client.cache_middleware.strategy'] = new \Kevinrob\GuzzleCache\Strategy\PublicCacheStrategy(
                ($this->privates['guzzle.epdocs.http_client.cache_middleware.storage'] ?? $this->privates['guzzle.epdocs.http_client.cache_middleware.storage'] = new \Kevinrob\GuzzleCache\Storage\Psr6CacheStorage(
                    ($this->privates['cache.ep_docs'] ?? $this->getCacheEpDocsService())
                ))
            ))
        );
    }

    /**
     * Gets the private 'cache.ep_docs' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCacheEpDocsService()
    {
        $adapter = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('ePDo45623tY', 0, ($this->getParameter('kernel.cache_dir') . '/pools'), ($this->privates['cache.default_marshaller'] ?? ($this->privates['cache.default_marshaller'] = new \Symfony\Component\Cache\Marshaller\DefaultMarshaller(null))));
        $adapter->setLogger(($this->privates['monolog.logger.cache'] ?? $this->getMonologLoggerCacheService()));
        if ($this->debugMode) {
            $adapter = new \Symfony\Component\Cache\Adapter\TraceableAdapter($adapter);
        }

        return $this->privates['cache.ep_docs'] = $adapter;
    }

    /**
     * Gets the private 'exception_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ErrorListener
     */
    protected function getExceptionListenerService()
    {
        return $this->privates['exception_listener'] = new \Symfony\Component\HttpKernel\EventListener\ErrorListener(
            ($this->privates['error_controller'] ?? $this->getErrorControllerService()),
            ($this->privates['monolog.logger.request'] ?? $this->getMonologLoggerRequestService()),
            $this->debugMode
        );
    }

    /**
     * Gets the private 'error_controller' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ErrorController
     */
    protected function getErrorControllerService()
    {
        throw new \RuntimeException('This part is nor implemented yet!');
    }

    /**
     * Gets the private 'error_handler' shared service.
     *
     * @return \Symfony\Component\ErrorHandler\ErrorHandler
     */
    protected function getErrorHandlerService()
    {
        return $this->privates['error_handler'] = new \Symfony\Component\ErrorHandler\ErrorHandler(
            ($this->privates['monolog.logger.request'] ?? $this->getMonologLoggerRequestService()),
            $this->debugMode
        );
    }

    /**
     * Gets the private 'session_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\SessionListener
     */
    protected function getSessionListenerService()
    {
        return $this->privates['session_listener'] = new \Symfony\Component\HttpKernel\EventListener\SessionListener(
            new \Symfony\Component\DependencyInjection\Argument\ServiceLocator(
                $this->getServiceProxy,
                [
                    'initialized_session' => ['services', 'session', null, false],
                    'request_stack'       => ['services', 'request_stack', 'getRequestStackService', false],
                    'logger'              => ['privates', 'monolog.logger', 'getMonologLoggerService', false],
                    'session'             => ['services', 'session', 'getSessionService', false],
                    'session_storage'     => ['privates', 'session.storage.native', 'getSessionStorageNativeService', false],
                    'session_collector'   => ['privates', 'data_collector.request.session_collector', 'getDataCollectorSessionCollectorService', false],
                ],
                [
                    'initialized_session' => '?',
                    'request_stack'       => '?',
                    'logger'              => '?',
                    'session'             => '?',
                    'session_storage'     => '?',
                    'session_collector'   => '?',
                ]
            )
        );
    }

    /**
     * Gets the private 'data_collector.request' shared service.
     *
     * @return \Symfony\Component\HttpKernel\DataCollector\RequestDataCollector
     */
    protected function getDataCollectorRequestService()
    {
        return $this->privates['data_collector.request'] = new \Symfony\Component\HttpKernel\DataCollector\RequestDataCollector(
            ($this->services['request_stack'] ?? $this->getRequestStackService())
        );
    }

    /**
     * Gets the private 'data_collector.request.session_collector' shared service.
     *
     * @return \Closure
     */
    protected function getDataCollectorSessionCollectorService()
    {
        return $this->privates['data_collector.request.session_collector'] = \Closure::fromCallable([
            ($this->privates['data_collector.request'] ?? $this->getDataCollectorRequestService()),
            'collectSessionUsage',
        ]);
    }

    /**
     * Gets the public 'event_dispatcher' shared service.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected function getEventDispatcherService()
    {
        $instance = new \Symfony\Component\EventDispatcher\EventDispatcher();
        if ($this->debugMode) {
            $instance = new \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher(
                $instance,
                ($this->privates['debug.stopwatch'] ?? $this->getStopwatchService()),
                ($this->privates['monolog.logger.event'] ?? $this->getMonologLoggerEventService()),
                ($this->services['request_stack'] ?? $this->getRequestStackService())
            );
        }

        $listenersToFactoryMap = [
            \Symfony\Component\HttpKernel\EventListener\SessionListener::class          => function () { return $this->privates['session_listener'] ?? $this->getSessionListenerService(); },
            \Symfony\Component\HttpKernel\EventListener\ResponseListener:: class        => function () { return $this->privates['response_listener'] ?? ($this->privates['response_listener'] = new \Symfony\Component\HttpKernel\EventListener\ResponseListener($this->getParameter('kernel.charset'))); },
            \Symfony\Component\HttpKernel\EventListener\ValidateRequestListener::class  => function () { return $this->privates['validate_request_listener'] ?? ($this->privates['validate_request_listener'] = new \Symfony\Component\HttpKernel\EventListener\ValidateRequestListener()); },
            \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener::class => function () { return $this->privates['streamed_response_listener'] ?? ($this->privates['streamed_response_listener'] = new \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener()); },
            \Symfony\Component\Notifier\EventListener\NotificationLoggerListener::class => function () { return $this->privates['notifier.logger_notification_listener'] ?? ($this->privates['notifier.logger_notification_listener'] = new \Symfony\Component\Notifier\EventListener\NotificationLoggerListener()); },
            \Symfony\Component\Mailer\EventListener\MessageLoggerListener::class        => function () { return $this->privates['mailer.message_logger_listener'] ?? ($this->privates['mailer.message_logger_listener'] = new \Symfony\Component\Mailer\EventListener\MessageLoggerListener()); },
            \Symfony\Component\Mailer\EventListener\EnvelopeListener::class             => function () { return $this->privates['mailer.envelope_listener'] ?? ($this->privates['mailer.envelope_listener'] = new \Symfony\Component\Mailer\EventListener\EnvelopeListener($this->getParameter('mailer.envelope.no_reply'))); },
            \Symfony\Component\Mailer\EventListener\MessageListener::class              => function () { return $this->privates['mailer.message_listener'] ?? ($this->privates['mailer.message_listener'] ?? new \Symfony\Component\Mailer\EventListener\MessageListener(null, null)); },
            \ExportPortal\Bridge\Mailer\EventListener\MessageRenderListener::class      => function () { return $this->privates['mailer.bridge.message_listener'] ?? $this->getMailerBridgeMessageListenerService(); },
            \ExportPortal\Bridge\Mailer\EventListener\EnvelopeListener::class           => function () { return $this->privates['mailer.bridge.envelope_listener'] ?? ($this->privates['mailer.bridge.envelope_listener'] = new \ExportPortal\Bridge\Mailer\EventListener\EnvelopeListener($this->getParameter('mailer.envelope.support'))); },
            \ExportPortal\Bridge\Mailer\EventListener\WriteEmailListener::class         => function () { return $this->privates['mailer.bridge.write_email_listener'] ?? ($this->privates['mailer.bridge.write_email_listener'] = new \ExportPortal\Bridge\Mailer\EventListener\WriteEmailListener(
                ($this->privates['monolog.logger.communication.db'] ?? $this->getMonologLoggerCommunicationDbService()),
                $this->getParameter('mailer.envelope.preview_url')
            )); },
            \ExportPortal\Bridge\Notifier\EventListener\MessageListener::class          => function () { return $this->privates['notifier.bridge.message_listener'] ?? $this->getNotifierBridgeMessageListenerService(); },
            \ExportPortal\Bridge\Notifier\EventListener\RecipientsListener::class       => function () { return $this->privates['notifier.bridge.recipient_listener'] ?? $this->getNotifierBridgeRecipientListenerService(); },
            \ExportPortal\Bridge\Notifier\EventListener\LogMessageListener::class       => function () { return $this->privates['notifier.bridge.write_message_listener'] ?? $this->getNotifierBridgeLogMessageListenerService(); },
            \ExportPortal\Bridge\Matrix\EventListener\RecipientsListener::class         => function () { return $this->privates['matrix.bridge.recipient_listener'] ?? $this->getMatrixBridgeRecipientListenerService(); },
            \App\EventListener\EmailAfterNotificationListener::class                    => function () { return $this->privates['notifier.bridge.send_mail_listener'] ?? $this->getNotifierBridgeSentMailListenerService(); },
            \App\EventListener\LogSellerCompanyUpdateListener::class                    => function () { return $this->privates['lifecycle.seller_update_log_listener'] ?? ($this->privates['lifecycle.seller_update_log_listener'] = new \App\EventListener\LogSellerCompanyUpdateListener(
                ($this->privates['library.legacy.activity'] ?? $this->privates['library.legacy.activity'] = ($this->services['library_locator'] ?? $this->getLibraryLocatorService())->get(\TinyMVC_Library_Activity_Logger::class)),
                ($this->privates['models.activity_log_messages'] ?? $this->privates['models.activity_log_messages'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Activity_Log_Messages_Model::class)),
            )); },
            \App\EventSubscriber\CacheUpdatedCompanySubscriber::class                   => function () { return $this->privates['lifecycle.seller_update_cache_listener'] ?? ($this->privates['lifecycle.seller_update_cache_listener'] = new \App\EventSubscriber\CacheUpdatedCompanySubscriber(
                ($this->services['library_locator'] ?? $this->getLibraryLocatorService()),
                ($this->services['model_locator'] ?? $this->getModelLocatorService())
            )); },
            \App\EventSubscriber\ModerationSubscriber::class                            => function () { return $this->privates['lifecycle.seller_update_moderation_listener'] ?? ($this->privates['lifecycle.seller_update_moderation_listener'] = new \App\EventSubscriber\ModerationSubscriber(
                ($this->privates['models.moderation'] ?? $this->privates['models.moderation'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Moderation_Model::class)),
            )); },
            \App\EventSubscriber\ProfileCompletionSubscriber::class                     => function () { return $this->privates['lifecycle.profile.completion_subscriber'] ?? ($this->privates['lifecycle.profile.completion_subscriber'] = new \App\EventSubscriber\ProfileCompletionSubscriber(
                ($this->privates['library.legacy.auth'] ?? $this->privates['library.legacy.auth'] = ($this->services['library_locator'] ?? $this->getLibraryLocatorService())->get(\TinyMVC_Library_Auth::class)),
                ($this->privates['models.profile_completion'] ?? $this->privates['models.profile_completion'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Complete_Profile_Options_Model::class)),
                ($this->privates['models.crm'] ?? $this->privates['models.crm'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Crm_Model::class)),
            )); },
        ];

        $listenersMap = [];
        foreach ($listenersToFactoryMap as $listenerClass => $factory) {
            $events = \forward_static_call("{$listenerClass}::getSubscribedEvents");
            foreach ($events as $event => $descriptor) {
                if (!\is_array($descriptor)) {
                    $descriptor = [$descriptor];
                } elseif (\is_array($descriptor[0])) {
                    foreach ($descriptor as $nestedDescriptor) {
                        if (!\is_array($nestedDescriptor)) {
                            $nestedDescriptor = [$nestedDescriptor];
                        }

                        $listenersMap[] = [$event, $factory, $nestedDescriptor[0], isset($nestedDescriptor[1]) ? $nestedDescriptor[1] : 0];
                    }

                    continue;
                }

                $listenersMap[] = [$event, $factory, $descriptor[0], isset($descriptor[1]) ? $descriptor[1] : 0];
            }
        }

        foreach ($listenersMap as list($event, $factory, $method, $priority)) {
            $instance->addListener(
                $event,
                function () use ($factory, $method) {
                    return call_user_func_array([$factory(), $method], func_get_args());
                },
                $priority
            );
        }

        return $this->services['event_dispatcher'] = $instance;
    }

    /**
     * Gets the private 'mailer.bridge.message_listener' shared service.
     *
     * @return \ExportPortal\Bridge\Mailer\EventListener\MessageRenderListener
     */
    protected function getMailerBridgeMessageListenerService()
    {
        return $this->privates['mailer.bridge.message_listener'] = new \ExportPortal\Bridge\Mailer\EventListener\MessageRenderListener(
            ($this->privates['mailer.bridge.mime_body_renderer'] ?? $this->getMailerBridgeMimeBodyRendererService())
        );
    }

    /**
     * Gets the private 'notifier.bridge.message_listener' shared service.
     *
     * @return \ExportPortal\Bridge\Notifier\EventListener\MessageListener
     */
    protected function getNotifierBridgeMessageListenerService()
    {
        return $this->privates['notifier.bridge.message_listener'] = new \ExportPortal\Bridge\Notifier\EventListener\MessageListener(
            ($this->services['app.data_provider.notification_metadata'] ?? $this->getDataProviderNotificationMetadataProviderService())
        );
    }

    /**
     * Gets the private 'notifier.bridge.recipient_listener' shared service.
     *
     * @return \ExportPortal\Bridge\Notifier\EventListener\RecipientsListener
     */
    protected function getNotifierBridgeRecipientListenerService()
    {
        return $this->privates['notifier.bridge.recipient_listener'] = new \ExportPortal\Bridge\Notifier\EventListener\RecipientsListener(
            ($this->services['app.data_provider.user_profile'] ?? $this->getDataProviderUserProfileProviderService())
        );
    }

    /**
     * Gets the private 'matrix.bridge.recipient_listener' shared service.
     *
     * @return \ExportPortal\Bridge\Notifier\EventListener\RecipientsListener
     */
    protected function getMatrixBridgeRecipientListenerService()
    {
        return $this->privates['matrix.bridge.recipient_listener'] = new \ExportPortal\Bridge\Matrix\EventListener\RecipientsListener(
            ($this->services['app.data_provider.user_room'] ?? $this->getDataProviderUserRoomsProviderService())
        );
    }

    /**
     * Gets the private 'notifier.bridge.write_message_listener' shared service.
     *
     * @return \ExportPortal\Bridge\Notifier\EventListener\RecipientsListener
     */
    protected function getNotifierBridgeLogMessageListenerService()
    {
        return $this->privates['notifier.bridge.write_message_listener'] = new \ExportPortal\Bridge\Notifier\EventListener\LogMessageListener(
            ($this->privates['monolog.logger.communication.db'] ?? $this->getMonologLoggerCommunicationDbService())
        );
    }

    /**
     * Gets the private 'notifier.bridge.send_mail_listener' shared service.
     *
     * @return \App\EventListener\EmailAfterNotificationListener
     */
    protected function getNotifierBridgeSentMailListenerService()
    {
        return $this->privates['notifier.bridge.send_mail_listener'] = new \App\EventListener\EmailAfterNotificationListener(
            ($this->services['mailer.mailer'] ?? $this->getMailerService()),
            ($this->services['model_locator'] ?? $this->getModelLocatorService())
        );
    }

    /**
     * Gets the public 'mailer.bridge.mime_body_renderer' shared service.
     *
     * @return \ExportPortal\Bridge\Mailer\Mime\LegacyBodyRenderer
     */
    protected function getMailerBridgeMimeBodyRendererService()
    {
        return $this->services['mailer.bridge.mime_body_renderer'] = new \ExportPortal\Bridge\Mailer\Mime\LegacyBodyRenderer(
            ($this->services['doctrine.dbal.default_connection'] ?? $this->getDoctrineDbalDefaultConnectionService()),
            ($this->services['renderer'] ?? $this->getRendererService()),
            $this->getParameter('mailer.templates.templates_table'),
            $this->getParameter('mailer.templates.structure_table'),
            $this->getParameter('mailer.templates.view_path'),
            [
                'imgUrl'          => $this->getParameter('mailer.envelope.img_url'),
                'siteUrl'         => $this->getParameter('mailer.envelope.base_url'),
                'previewUrl'      => $this->getParameter('mailer.envelope.preview_url'),
                'unsubscribeLink' => $this->getParameter('mailer.envelope.unsubscribe_url'),
                'supportEmail'    => $this->getParameter('mailer.envelope.support'),
            ],
            $this->getParameter('kernel.default_locale')
        );
    }

    /**
     * Gets the public 'session' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    protected function getSessionService()
    {
        return $this->services['session'] = new \Symfony\Component\HttpFoundation\Session\Session(
            ($this->privates['session.storage.native'] ?? $this->getSessionStorageNativeService()),
            null,
            null,
            \Closure::fromCallable([
                ($this->privates['session_listener'] ?? $this->getSessionListenerService()),
                'onSessionUsage',
            ])
        );
    }

    /**
     * Gets the private 'session.storage.native' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage
     */
    protected function getSessionStorageNativeService()
    {
        return $this->privates['session.storage.native'] = new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(
            $this->getParameter('session.storage.options'),
            null,
            new \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag(
                $this->getParameter('session.metadata.storage_key'),
                $this->getParameter('session.metadata.update_threshold')
            )
        );
    }

    /**
     * Gets the public 'renderer' shared service.
     *
     * @return \TinyMVC_View
     */
    protected function getRendererService()
    {
        return $this->services['renderer'] = new \TinyMVC_View($this);
    }

    /**
     * Gets the public 'fastcache' shared service.
     *
     * @return \TinyMVC_Library_Fastcache
     */
    protected function getFastCacheService()
    {
        return $this->services['fastcache'] = new \TinyMVC_Library_Fastcache($this);
    }

    /**
     * Gets the public 'loader' shared service.
     *
     * @return \TinyMVC_Load
     */
    protected function getLoaderService()
    {
        return $this->services['loader'] = new \TinyMVC_Load($this);
    }

    /**
     * Gets the public 'model_locator' shared service.
     *
     * @return \App\Common\DependencyInjection\ServiceLocator\ModelLocator
     */
    protected function getModelLocatorService()
    {
        return $this->services['model_locator'] = new \App\Common\DependencyInjection\ServiceLocator\ModelLocator(
            ($this->services['pdo'] ?? $this->getPdoService())
        );
    }

    /**
     * Gets the public 'library_locator' shared service.
     *
     * @return \App\Common\DependencyInjection\ServiceLocator\ModelLocator
     */
    protected function getLibraryLocatorService()
    {
        return $this->services['library_locator'] = new \App\Common\DependencyInjection\ServiceLocator\LibraryLocator($this);
    }

    /**
     * Gets the public 'doctrine' shared service.
     *
     * @return \App\Common\Database\Connection\Registry
     */
    protected function getDoctrineService()
    {
        $connections = $this->getParameter('database.connections');
        $connectionNames = \array_keys($connections);

        return $this->services['doctrine'] = new \App\Common\Database\Connection\Registry(
            ($this->privates['doctrine.dbal.connection_locator'] ?? $this->getDoctrineDbalConnectionLocatorService()),
            \array_combine($connectionNames, \array_map(fn (string $connection) =>"doctrine.dbal.{$connection}_connection", $connectionNames)),
            $this->getParameter('database.connections.default_connection') ?? 'default'
        );
    }

    /**
     * Gets the public 'pdo' shared service.
     *
     * @return \App\Common\Database\Connection\PdoRegistry
     */
    protected function getPdoService()
    {
        $connections = $this->getParameter('database.connections');
        $connectionNames = \array_keys($connections);

        return $this->services['pdo'] = new \App\Common\Database\Connection\PdoRegistry(
            ($this->privates['doctrine.dbal.wrapped_connection_locator'] ?? $this->getDoctrineDbalWrappedConnectionLocatorService()),
            \array_combine($connectionNames, \array_map(fn (string $connection) =>"doctrine.dbal.{$connection}_connection", $connectionNames)),
            $this->getParameter('database.connections.default_connection') ?? 'default'
        );
    }

    /**
     * Gets the private 'doctrine.dbal.connection_locator' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Argument\ServiceLocator
     */
    protected function getDoctrineDbalConnectionLocatorService()
    {
        $connections = $this->getParameter('database.connections');
        $connectionNames = \array_keys($connections);
        $connectionsServicesMap = [];
        $connectionsServicesTypes = [];
        foreach ($connectionNames as $name) {
            $serviceId = "doctrine.dbal.{$name}_connection";
            $connectionsServicesMap[$serviceId] = [$serviceId, $name];
            $connectionsServicesTypes[$serviceId] = '?';
        }

        return $this->privates['doctrine.dbal.connection_locator'] = new \Symfony\Component\DependencyInjection\Argument\ServiceLocator(
            function (string $serviceId, string $connectionId) use ($connections) {
                if (!isset($this->privates[$serviceId])) {
                    $configuration = new \Doctrine\DBAL\Configuration();
                    if ($this->debugMode) {
                        $configuration->setSQLLogger(new \Doctrine\DBAL\Logging\LoggerChain([
                            ($this->privates['doctrine.dbal.logger'] ?? $this->getDoctrineDbalLoggerService()),
                            ($this->privates['doctrine.dbal.logger.profiling.default'] ?? ($this->privates['doctrine.dbal.logger.profiling.default'] = new \Doctrine\DBAL\Logging\DebugStack())),
                        ]));
                    }

                    $this->privates[$serviceId] = ($this->privates['doctrine.dbal.connection_factory'] ?? $this->getDoctrineDbalConnectionFactoryService())->createConnection(
                        $connections[$connectionId] ?? [],
                        $configuration,
                        ($this->privates['doctrine.event_manager'] ?? $this->getDbalDoctrineEventManagerService())
                    );
                }

                return $this->privates[$serviceId];
            },
            $connectionsServicesMap,
            $connectionsServicesTypes
        );
    }

    /**
     * Gets the private 'doctrine.dbal.wrapped_connection_locator' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Argument\ServiceLocator
     */
    protected function getDoctrineDbalWrappedConnectionLocatorService()
    {
        /** @var \Symfony\Component\DependencyInjection\Argument\ServiceLocator */
        $realLocator = ($this->privates['doctrine.dbal.connection_locator'] ?? $this->getDoctrineDbalConnectionLocatorService());
        $connectionsServicesMap = [];
        $connectionsServicesTypes = [];
        foreach ($realLocator->getProvidedServices() as $serviceId => $type) {
            $wrappedServiceId = $serviceId . '.wrapped';
            $connectionsServicesMap[$serviceId] = [$wrappedServiceId, $serviceId];
            $connectionsServicesTypes[$serviceId] = '?';
        }

        return $this->privates['doctrine.dbal.wrapped_connection_locator'] = new \Symfony\Component\DependencyInjection\Argument\ServiceLocator(
            function (string $wrapperId, string $serviceId) use ($realLocator) {
                if (!isset($this->privates[$wrapperId])) {
                    $this->privates[$wrapperId] = new \TinyMVC_PDO($realLocator->get($serviceId), $this->debugMode);
                }

                return $this->privates[$wrapperId];
            },
            $connectionsServicesMap,
            $connectionsServicesTypes
        );
    }

    /**
     * Gets the private 'doctrine.dbal.connection_factory' shared service.
     *
     * @return App\Common\Database\ConnectionFactory
     */
    protected function getDoctrineDbalConnectionFactoryService()
    {
        return $this->privates['doctrine.dbal.connection_factory'] = new \App\Common\Database\ConnectionFactory(\App\Common\Database\CustomTypesProvider::getCommonTypes());
    }

    /**
     * Gets the public 'doctrine.dbal.default_connection' shared service.
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDoctrineDbalDefaultConnectionService()
    {
        return $this->services['doctrine.dbal.default_connection'] = ($this->services['doctrine'] ?? $this->getDoctrineService())->getConnection(
            ($this->services['doctrine'] ?? $this->getDoctrineService())->getDefaultConnectionName()
        );
    }

    /**
     * Gets the public 'doctrine.dbal.default_connection' shared service.
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDoctrineDbalPdoDefaultConnectionService()
    {
        return $this->privates['doctrine.dbal.pdo.default_connection'] = ($this->services['pdo'] ?? $this->getPdoService())->getConnection(
            ($this->services['pdo'] ?? $this->getPdoService())->getDefaultConnectionName()
        );
    }

    /**
     * Gets the private 'doctrine.event_manager' shared service.
     *
     * @return \Doctrine\Common\EventManager
     */
    protected function getDbalDoctrineEventManagerService()
    {
        return $this->privates['doctrine.event_manager'] = new \Doctrine\Common\EventManager();
    }

    /**
     * Gets the private 'debug.log_processor' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Processor\DebugProcessor
     */
    protected function getDebugLogProcessorService()
    {
        return $this->privates['debug.log_processor'] = new \Symfony\Bridge\Monolog\Processor\DebugProcessor(($this->services['request_stack'] ?? $this->getRequestStackService()));
    }

    /**
     * Gets the private 'monolog.handler.console' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Handler\ConsoleHandler
     */
    protected function getMonologHandlerConsoleService()
    {
        return $this->privates['monolog.handler.console'] = new \Symfony\Bridge\Monolog\Handler\ConsoleHandler(null, true, [], []);
    }

    /**
     * Gets the private 'monolog.handler.main' shared service.
     *
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getMonologHandlerMainService()
    {
        $handler = new \Monolog\Handler\StreamHandler(\sprintf('%s/%s.log', $this->getParameter('kernel.logs_dir'), $this->getParameter('kernel.environment')), \Monolog\Logger::DEBUG, true, 0664, false);
        $handler->pushProcessor(($this->privates['monolog.processor.psr_log_message'] ?? ($this->privates['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())));

        return $this->privates['monolog.handler.main'] = $handler;
    }

    /**
     * Gets the private 'monolog.handler.doctrine' shared service.
     *
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getMonologHandlerDoctrineService()
    {
        $handler = new \Monolog\Handler\StreamHandler(\sprintf('%s/%s.doctrine.log', $this->getParameter('kernel.logs_dir'), $this->getParameter('kernel.environment')), \Monolog\Logger::DEBUG, true, 0664, false);
        $handler->pushProcessor(($this->privates['monolog.processor.psr_log_message'] ?? ($this->privates['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())));

        return $this->privates['monolog.handler.doctrine'] = $handler;
    }

    /**
     * Gets the private 'monolog.handler.messenger' shared service.
     *
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getMonologHandlerMessengerService()
    {
        $handler = new \Monolog\Handler\StreamHandler(\sprintf('%s/%s.messenger.log', $this->getParameter('kernel.logs_dir'), $this->getParameter('kernel.environment')), \Monolog\Logger::DEBUG, true, 0664, false);
        $handler->pushProcessor(($this->privates['monolog.processor.psr_log_message'] ?? ($this->privates['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())));

        return $this->privates['monolog.handler.messenger'] = $handler;
    }

    /**
     * Gets the private 'monolog.handler.epdocs' shared service.
     *
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getMonologHandlerEpDocsService()
    {
        $handler = new \Monolog\Handler\StreamHandler(\sprintf('%s/%s.epdocs.log', $this->getParameter('kernel.logs_dir'), $this->getParameter('kernel.environment')), \Monolog\Logger::DEBUG, true, 0664, false);
        $handler->pushProcessor(($this->privates['monolog.processor.psr_log_message'] ?? ($this->privates['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())));

        return $this->privates['monolog.handler.epdocs'] = $handler;
    }

    /**
     * Gets the public 'logger' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getLoggerService()
    {
        return $this->services['logger'] = ($this->privates['monolog.logger'] ?? $this->getMonologLoggerService());
    }

    /**
     * Gets the public 'logger.filesystem' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getLoggerFilesystemService()
    {
        return $this->services['logger.filesystem'] = ($this->privates['monolog.logger.filesystem'] ?? $this->getMonologLoggerFilesystemService());
    }

    /**
     * Gets the private 'monolog.logger' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('app');
        $logger->useMicrosecondTimestamps(true);
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.console'] ?? $this->getMonologHandlerConsoleService()));
        $logger->pushHandler(($this->privates['monolog.handler.main'] ?? $this->getMonologHandlerMainService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.cache' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerCacheService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('cache');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.console'] ?? $this->getMonologHandlerConsoleService()));
        $logger->pushHandler(($this->privates['monolog.handler.main'] ?? $this->getMonologHandlerMainService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.cache'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.communication.db' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerCommunicationDbService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('CommunicationLogger');
        $logger->pushHandler(($this->privates['monolog.mailer.db.handler'] ?? ($this->privates['monolog.mailer.db.handler'] = new \App\Logger\CommunicationLogger(
            $this->getParameter('mailer.log.log_table'),
            ($this->privates['doctrine.dbal.pdo.default_connection'] ?? $this->getDoctrineDbalPdoDefaultConnectionService())
        ))));

        return $this->privates['monolog.logger.communication.db'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.request' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerRequestService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('request');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.console'] ?? $this->getMonologHandlerConsoleService()));
        $logger->pushHandler(($this->privates['monolog.handler.main'] ?? $this->getMonologHandlerMainService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.request'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.event' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerEventService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('event');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(new \Monolog\Handler\NullHandler());
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.event'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.mailer' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerMailerService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('mailer');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.console'] ?? $this->getMonologHandlerConsoleService()));
        $logger->pushHandler(($this->privates['monolog.handler.main'] ?? $this->getMonologHandlerMainService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.mailer'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.notifier' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerNotifierService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('notifier');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.console'] ?? $this->getMonologHandlerConsoleService()));
        $logger->pushHandler(($this->privates['monolog.handler.main'] ?? $this->getMonologHandlerMainService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.notifier'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.epdocs_api' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerEpDocsApiService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('epdocs-api');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.console'] ?? $this->getMonologHandlerConsoleService()));
        $logger->pushHandler(($this->privates['monolog.handler.epdocs'] ?? $this->getMonologHandlerEpDocsService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.epdocs_api'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.messenger' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerMessengerService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('messenger');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.messenger'] ?? $this->getMonologHandlerMessengerService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.messenger'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.matrix' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerMatrixService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('matrix');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.matrix_client'] ?? $this->getMonologHandlerMatrxiClientService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.matrix'] = $logger;
    }

    /**
     * Gets the private 'monolog.logger.filesystem' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonologLoggerFilesystemService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('filesystem');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.filesystem_client'] ?? $this->getMonologHandlerFilesystemClientService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.filesystem'] = $logger;
    }

    /**
     * Gets the private 'doctrine.dbal.logger' shared service.
     *
     * @return \App\Common\Database\Logger\DbalLogger
     */
    protected function getDoctrineDbalLoggerService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('doctrine');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.doctrine'] ?? $this->getMonologHandlerDoctrineService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['doctrine.dbal.logger'] = new \App\Common\Database\Logger\DbalLogger($logger, ($this->privates['debug.stopwatch'] ?? $this->getStopwatchService()));
    }

    /**
     * Gets the private 'doctrine.dbal.logger' shared service.
     *
     * @return \App\Common\Database\Logger\DbalLogger
     */
    protected function getMonologLoggerHttpClientService()
    {
        $logger = new \Symfony\Bridge\Monolog\Logger('http_client');
        if ($this->debugMode) {
            $logger->pushProcessor(($this->privates['debug.log_processor'] ?? $this->getDebugLogProcessorService()));
        }
        $logger->pushHandler(($this->privates['monolog.handler.console'] ?? $this->getMonologHandlerConsoleService()));
        $logger->pushHandler(($this->privates['monolog.handler.main'] ?? $this->getMonologHandlerMainService()));
        if (\is_object($logger) && method_exists($logger, 'removeDebugLogger') && \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $logger->removeDebugLogger();
        }

        return $this->privates['monolog.logger.http_client'] = $logger;
    }

    /**
     * Gets the public 'filesystem.provider' shared service.
     *
     * @return \ExportPortal\Contracts\Filesystem\FilesystemProviderInterface
     */
    protected function getFilesystemFilesystemproviderService()
    {
        return $this->services['filesystem.provider'] = new \ExportPortal\Bridge\Filesystem\StaticFilesystemProvider(
            $this,
            $this->getParameter('kernel.config_dir'),
            ['filesystem.php', "{$this->getParameter('kernel.environment')}/filesystem.php"]
        );
    }

    /**
     * Gets the private 'messenger.transport_factory' shared service.
     *
     * @return \Symfony\Component\Messenger\Transport\TransportFactoryInterface
     */
    protected function getMessengerTransportFactoryService()
    {
        return $this->privates['messenger.transport_factory'] = new \Symfony\Component\Messenger\Transport\TransportFactory(
            new \Symfony\Component\DependencyInjection\Argument\RewindableGenerator(
                function () {
                    yield 0 => ($this->privates['messenger.transport.amqp.factory'] ?? $this->privates['messenger.transport.amqp.factory'] = new \Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransportFactory());
                    yield 1 => ($this->privates['messenger.transport.redis.factory'] ?? $this->privates['messenger.transport.redis.factory'] = new \Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransportFactory());
                    yield 2 => ($this->privates['messenger.transport.doctrine.factory'] ?? $this->privates['messenger.transport.doctrine.factory'] = new \Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransportFactory(($this->services['doctrine'] ?? $this->getDoctrineService())));
                    yield 3 => ($this->privates['messenger.transport.in-memory.factory'] ?? $this->privates['messenger.transport.in-memory.factory'] = new \Symfony\Component\Messenger\Transport\InMemoryTransportFactory());
                    yield 4 => ($this->privates['messenger.transport.sync.factory'] ?? $this->privates['messenger.transport.sync.factory'] = new \Symfony\Component\Messenger\Transport\Sync\SyncTransportFactory(
                        ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus())
                    ));
                },
                5
            )
        );
    }

    /**
     * Gets the private 'messenger.middleware_factory' shared service.
     *
     * @return \ExportPortal\Bridge\Symfony\Component\Messenger\Middleware\MiddlewareFactoryInterface
     */
    protected function getMessengerMiddlewareFactoryService()
    {
        return $this->privates['messenger.middleware_factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Middleware\MiddlewareFactory(
            new \Symfony\Component\DependencyInjection\Argument\RewindableGenerator(
                function () {
                    yield 0 => ($this->privates['messenger.middleware.generic.factory'] ?? $this->privates['messenger.middleware.generic.factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Middleware\GenericMiddlewareFactory(
                        ($this->privates['messenger.senders_locator'] ?? $this->privates['messenger.senders_locator'] = ($this->services['messenger'] ?? $this->getMessengerService())->getSenders()),
                        ($this->privates['messenger.receivers_locator'] ?? $this->privates['messenger.receivers_locator'] = ($this->services['messenger'] ?? $this->getMessengerService())->getReceivers()),
                    ));
                    yield 1 => ($this->privates['messenger.middleware.handlers.factory'] ?? $this->privates['messenger.middleware.handlers.factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Middleware\HandleMessageMiddlewareFactory(
                        ($this->privates['messenger.handler_regisrty'] ?? $this->privates['messenger.handler_regisrty'] = ($this->services['messenger'] ?? $this->getMessengerService())->getHandlersRegistry()),
                        ($this->privates['monolog.logger.messenger'] ?? $this->getMonologLoggerMessengerService())
                    ));
                    yield 2 => ($this->privates['messenger.middleware.send.factory'] ?? $this->privates['messenger.middleware.send.factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Middleware\SendMessageMiddlewareFactory(
                        ($this->privates['messenger.senders_locator'] ?? $this->privates['messenger.senders_locator'] = ($this->services['messenger'] ?? $this->getMessengerService())->getSenders()),
                        ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
                    ));
                    if ($this->debugMode) {
                        yield 3 => ($this->privates['messenger.traceable.factory'] ?? $this->privates['messenger.traceable.factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Middleware\TraceableMiddlewareFactory(
                            ($this->privates['debug.stopwatch'] ?? $this->getStopwatchService()),
                        ));
                    }

                    yield 4 => ($this->privates['messenger.fallback.factory'] ?? $this->privates['messenger.fallback.factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Middleware\FallbackMiddlewareFactory());
                },
                $this->debugMode ? 4 : 3
            )
        );
    }

    /**
     * Gets the private 'messenger.bus_factory' shared service.
     *
     * @return \ExportPortal\Bridge\Symfony\Component\Messenger\MessageBusFactoryInterface
     */
    protected function getMessengerBusFactoryService()
    {
        return $this->privates['messenger.bus_factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\MessageBusFactory(
            new \Symfony\Component\DependencyInjection\Argument\RewindableGenerator(
                function () {
                    yield 0 => ($this->privates['messenger.bus.generic.factory'] ?? $this->privates['messenger.bus.generic.factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\GenericBusFactory(
                        ($this->privates['messenger.container'] ?? $this->privates['messenger.container'] = new \Symfony\Component\DependencyInjection\Container()),
                        ($this->privates['messenger.middleware_factory'] ?? $this->getMessengerMiddlewareFactoryService()),
                        'messenger',
                        $this->debugMode
                    ));
                },
                1
            )
        );
    }

    /**
     * Gets the private 'messenger.serializer_factory' shared service.
     *
     * @return \ExportPortal\Bridge\Symfony\Component\Messenger\Serialization\SerializerFactoryInterface
     */
    protected function getMessengerSerializerFactoryService()
    {
        return $this->privates['messenger.serializer_factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Serialization\SerializerFactory(
            new \Symfony\Component\DependencyInjection\Argument\RewindableGenerator(
                function () {
                    yield 0 => ($this->privates['messenger.serializer.factory.built_in'] ?? $this->privates['messenger.serializer.factory.built_in'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Serialization\BuiltIntSerializerFactory($this->debugMode));
                },
                1
            )
        );
    }

    /**
     * Gets the private 'messenger.retry_strategy_factory' shared service.
     *
     * @return \ExportPortal\Bridge\Symfony\Component\Messenger\Retry\RetryStrategyFactoryInterface
     */
    protected function getMessengerRetryStrategyFactoryService()
    {
        return $this->privates['messenger.retry_strategy_factory'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Retry\RetryStrategyFactory(
            new \Symfony\Component\DependencyInjection\Argument\RewindableGenerator(
                function () {
                    yield 0 => ($this->privates['messenger.retry_strategy.factory.built_in'] ?? $this->privates['messenger.retry_strategy.factory.built_in'] = new \ExportPortal\Bridge\Symfony\Component\Messenger\Retry\BuiltInRetryStrategyFactory());
                },
                1
            )
        );
    }

    /**
     * Gets the public 'messenger' shared service.
     *
     * @return \ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface
     */
    protected function getMessengerService()
    {
        return $this->services['messenger'] = (
            (new \ExportPortal\Bridge\Symfony\Component\Messenger\Builder\MessengerBuilder())
                ->setDebug($this->getParameter('kernel.debug'))
                ->setContainer(($this->privates['messenger.container'] ?? $this->privates['messenger.container'] = new \Symfony\Component\DependencyInjection\Container()))
                ->setServiceContainer($this)
                ->setEventDispatcher(($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()))
                ->setTransportFactory(($this->privates['messenger.transport_factory'] ?? $this->getMessengerTransportFactoryService()))
                ->setMessageBusFactory(($this->privates['messenger.bus_factory'] ?? $this->getMessengerBusFactoryService()))
                ->setMiddlewareFactory(($this->privates['messenger.middleware_factory'] ?? $this->getMessengerMiddlewareFactoryService()))
                ->setSerializerFactory(($this->privates['messenger.serializer_factory'] ?? $this->getMessengerSerializerFactoryService()))
                ->setRetryStrategyFactory(($this->privates['messenger.retry_strategy_factory'] ?? $this->getMessengerRetryStrategyFactoryService()))
                ->setRootDirectory($this->getParameter('kernel.root_dir'))
                ->setConfigurationDirectories([$this->getParameter('kernel.config_dir')])
                ->addConfigurationFile($this->getParameter('messenger.config_file'))
                ->addConfigurationFile($this->getParameter('kernel.environment') . '/' . $this->getParameter('messenger.config_file'))
                ->setContainerPrefix('messenger')
                ->setCachePool(($this->privates['cache.messenger.restart_workers_signal'] ?? $this->getCacheMessengerRestartWorkersSignalService()))
        )->buildMessenger();
    }

    /**
     * Gets the public 'messenger.default_bus' shared service.
     *
     * @return \Symfony\Component\Messenger\TraceableMessageBus
     */
    protected function getMessengerDefaultBusService()
    {
        return $this->services['messenger.default_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getDefaultBus();
    }

    /**
     * Gets the public 'console.command_loader' shared service.
     *
     * @return \Symfony\Component\Console\CommandLoader\FactoryCommandLoader
     */
    protected function getConsoleCommandLoaderService()
    {
        return $this->services['console.command_loader'] = new \Symfony\Component\Console\CommandLoader\FactoryCommandLoader([
            // Base commands
            \App\Console\Commands\Release::getDefaultName()                                    => fn () => new \App\Console\Commands\Release(),
            \App\Console\Commands\PreRelease::getDefaultName()                                 => fn () => new \App\Console\Commands\PreRelease(),
            \App\Console\Commands\CreateKey::getDefaultName()                                  => fn () => new \App\Console\Commands\CreateKey(($this->privates['app.encryption.file_key_storage'] ?? $this->getEncryptionFileKeyStorageService())),
            \App\Console\Commands\MakeController::getDefaultName()                             => fn () => new \App\Console\Commands\MakeController(),
            \App\Console\Commands\MakeLibrary::getDefaultName()                                => fn () => new \App\Console\Commands\MakeLibrary(),
            \App\Console\Commands\MakeModel::getDefaultName()                                  => fn () => new \App\Console\Commands\MakeModel(),
            \App\Console\Commands\MakeTestHelper::getDefaultName()                             => fn () => new \App\Console\Commands\MakeTestHelper(),
            \App\Console\Commands\MaintenanceCommand::getDefaultName()                         => fn () => new \App\Console\Commands\MaintenanceCommand(),
            \App\Console\Commands\CacheZohoToken::getDefaultName()                             => fn () => new \App\Console\Commands\CacheZohoToken(),
            \App\Console\Commands\CacheConfiguration::getDefaultName()                         => fn () => new \App\Console\Commands\CacheConfiguration(
                ($this->privates['models.configs'] ?? $this->privates['models.configs'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Configs_Model::class)),
                $this->getParameter('kernel.build_dir'),
                $this->debugMode
            ),

            // Filesystem commands
            \ExportPortal\Bridge\Filesystem\Console\GenerateLinksCommand::getDefaultName()     => fn () => new \ExportPortal\Bridge\Filesystem\Console\GenerateLinksCommand(
                ($this->services['filesystem.provider'] ?? $this->getFilesystemFilesystemproviderService())->links(),
                new \Symfony\Component\Filesystem\Filesystem()
            ),

            // Matrix commands
            \App\Console\Commands\ExportUsersToMatrix::getDefaultName()                        => fn () => new \App\Console\Commands\ExportUsersToMatrix(
                ($this->services['matrix'] ?? $this->getMatrixService()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
                ($this->privates['models.groups'] ?? $this->privates['models.groups'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_Groups_Model::class)),
            ),
            \App\Console\Commands\Matrix\CreateCargoRoom::getDefaultName()                     => fn () => new \App\Console\Commands\Matrix\CreateCargoRoom(
                ($this->services['matrix'] ?? $this->getMatrixService()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
                ($this->privates['models.groups'] ?? $this->privates['models.groups'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_Groups_Model::class)),
            ),
            \App\Console\Commands\Matrix\CreateServerNoticesRoom::getDefaultName()             => fn () => new \App\Console\Commands\Matrix\CreateServerNoticesRoom(
                ($this->services['matrix'] ?? $this->getMatrixService()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
                ($this->privates['models.groups'] ?? $this->privates['models.groups'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_Groups_Model::class)),
            ),
            \App\Console\Commands\Matrix\UpdateCargoRoom::getDefaultName()                     => fn () => new \App\Console\Commands\Matrix\UpdateCargoRoom(
                ($this->services['matrix'] ?? $this->getMatrixService()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
                ($this->privates['models.groups'] ?? $this->privates['models.groups'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_Groups_Model::class)),
            ),
            \App\Console\Commands\Matrix\UpdateServerNoticesRoom::getDefaultName()             => fn () => new \App\Console\Commands\Matrix\UpdateServerNoticesRoom(
                ($this->services['matrix'] ?? $this->getMatrixService()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
                ($this->privates['models.groups'] ?? $this->privates['models.groups'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_Groups_Model::class)),
            ),
            \App\Console\Commands\SyncUsersOnMatrix::getDefaultName()                          => fn () => new \App\Console\Commands\SyncUsersOnMatrix(
                ($this->services['matrix'] ?? $this->getMatrixService()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
                ($this->privates['models.groups'] ?? $this->privates['models.groups'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_Groups_Model::class)),
            ),
            \App\Console\Commands\DeactivateUsersOnMatrix::getDefaultName()                    => fn () => new \App\Console\Commands\DeactivateUsersOnMatrix(
                ($this->services['matrix'] ?? $this->getMatrixService()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
                ($this->privates['models.groups'] ?? $this->privates['models.groups'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_Groups_Model::class)),
            ),
            \App\Console\Commands\GenerateMatrixAccessToken::getDefaultName()                  => fn () => new \App\Console\Commands\GenerateMatrixAccessToken(($this->services['matrix'] ?? $this->getMatrixService())),
            \App\Console\Commands\GenerateMatrixKeygenUrlList::getDefaultName()                => fn () => new \App\Console\Commands\GenerateMatrixKeygenUrlList(
                ($this->services['matrix'] ?? $this->getMatrixService()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
                ($this->privates['models.groups'] ?? $this->privates['models.groups'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_Groups_Model::class)),
            ),

            // Messenger commands
            \Symfony\Component\Messenger\Command\ConsumeMessagesCommand::getDefaultName()      => fn () => new \Symfony\Component\Messenger\Command\ConsumeMessagesCommand(
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->privates['messenger.receivers_locator'] ?? $this->privates['messenger.receivers_locator'] = ($this->services['messenger'] ?? $this->getMessengerService())->getReceivers()),
                ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
                ($this->privates['monolog.logger.messenger'] ?? $this->getMonologLoggerMessengerService()),
                \array_values(($this->privates['messenger.receivers_locator'] ?? $this->privates['messenger.receivers_locator'] = ($this->services['messenger'] ?? $this->getMessengerService())->getReceivers())->getProvidedServices()),
            ),
            \Symfony\Component\Messenger\Command\FailedMessagesRemoveCommand::getDefaultName() => fn () => new \Symfony\Component\Messenger\Command\FailedMessagesRemoveCommand(
                ($this->services['messenger'] ?? $this->getMessengerService())->getFailureTransportName(),
                ($this->privates['messenger.failure_transports'] ?? $this->privates['messenger.failure_transports'] = ($this->services['messenger'] ?? $this->getMessengerService())->getFailureTransports())
            ),
            \Symfony\Component\Messenger\Command\FailedMessagesRetryCommand::getDefaultName()  => fn () => new \Symfony\Component\Messenger\Command\FailedMessagesRetryCommand(
                ($this->services['messenger'] ?? $this->getMessengerService())->getFailureTransportName(),
                ($this->privates['messenger.failure_transports'] ?? $this->privates['messenger.failure_transports'] = ($this->services['messenger'] ?? $this->getMessengerService())->getFailureTransports()),
                ($this->privates['messenger.routable_message_bus'] ?? $this->privates['messenger.routable_message_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->getRoutableBus()),
                ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
                ($this->privates['monolog.logger.messenger'] ?? $this->getMonologLoggerMessengerService())
            ),
            \Symfony\Component\Messenger\Command\FailedMessagesShowCommand::getDefaultName()   => fn () => new \Symfony\Component\Messenger\Command\FailedMessagesShowCommand(
                ($this->services['messenger'] ?? $this->getMessengerService())->getFailureTransportName(),
                ($this->privates['messenger.failure_transports'] ?? $this->privates['messenger.failure_transports'] = ($this->services['messenger'] ?? $this->getMessengerService())->getFailureTransports())
            ),
            \Symfony\Component\Messenger\Command\SetupTransportsCommand::getDefaultName()      => fn () => new \Symfony\Component\Messenger\Command\SetupTransportsCommand(
                ($this->privates['messenger.receivers_locator'] ?? $this->privates['messenger.receivers_locator'] = ($this->services['messenger'] ?? $this->getMessengerService())->getReceivers()),
                \array_values(($this->privates['messenger.receivers_locator'] ?? $this->privates['messenger.receivers_locator'] = ($this->services['messenger'] ?? $this->getMessengerService())->getReceivers())->getProvidedServices())
            ),
            \Symfony\Component\Messenger\Command\StopWorkersCommand::getDefaultName()          => fn () => new \Symfony\Component\Messenger\Command\StopWorkersCommand(
                ($this->privates['cache.messenger.restart_workers_signal'] ?? $this->getCacheMessengerRestartWorkersSignalService()),
            ),
            \Symfony\Component\Messenger\Command\DebugCommand::getDefaultName()                => fn () => new \Symfony\Component\Messenger\Command\DebugCommand(
                ($this->services['messenger'] ?? $this->getMessengerService())->getCommandsMapping()
            ),
        ]);
    }

    /**
     * Gets the public 'cache.app' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCacheAppService()
    {
        $adapter = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('XeVhVO6g9c', 0, ($this->getParameter('kernel.cache_dir') . '/pools'), ($this->privates['cache.default_marshaller'] ?? ($this->privates['cache.default_marshaller'] = new \Symfony\Component\Cache\Marshaller\DefaultMarshaller(null))));
        $adapter->setLogger(($this->privates['monolog.logger.cache'] ?? $this->getMonologLoggerCacheService()));
        if ($this->debugMode) {
            $adapter = new \Symfony\Component\Cache\Adapter\TraceableAdapter($adapter);
        }

        return $this->services['cache.app'] = $adapter;
    }

    /**
     * Gets the private 'cache.messenger.restart_workers_signal' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCacheMessengerRestartWorkersSignalService()
    {
        $adapter = new \Symfony\Component\Cache\Adapter\FilesystemAdapter('KucKcS82si', 0, ($this->getParameter('kernel.cache_dir') . '/pools'), ($this->privates['cache.default_marshaller'] ?? ($this->privates['cache.default_marshaller'] = new \Symfony\Component\Cache\Marshaller\DefaultMarshaller(null))));
        $adapter->setLogger(($this->privates['monolog.logger.cache'] ?? $this->getMonologLoggerCacheService()));
        if ($this->debugMode) {
            $adapter = new \Symfony\Component\Cache\Adapter\TraceableAdapter($adapter);
        }

        return $this->privates['cache.messenger.restart_workers_signal'] = $adapter;
    }

    /**
     * Gets the private 'cache.maxtrix.user_provider' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCacheMatrixUserProviderCacheService()
    {
        $adapter = new \Symfony\Component\Cache\Adapter\ArrayAdapter();
        if ($this->debugMode) {
            $adapter = new \Symfony\Component\Cache\Adapter\TraceableAdapter($adapter);
        }

        return $this->privates['cache.maxtrix.user_provider'] = $adapter;
    }

    /**
     * Gets the private 'cache.templates.navbar_state_provider' shared service.
     *
     * @return \Symfony\Component\Cache\Adapter\TraceableAdapter
     */
    protected function getCacheNavigationBarStateProviderService()
    {
        $adapter = new \Symfony\Component\Cache\Adapter\ArrayAdapter();
        if ($this->debugMode) {
            $adapter = new \Symfony\Component\Cache\Adapter\TraceableAdapter($adapter);
        }

        return $this->privates['cache.templates.navbar_state_provider'] = $adapter;
    }

    /**
     * Gets the private 'monolog.handler.matrix_client' shared service.
     *
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getMonologHandlerMatrxiClientService()
    {
        $handler = new \Monolog\Handler\StreamHandler(\sprintf('%s/%s.matrix.log', $this->getParameter('kernel.logs_dir'), $this->getParameter('kernel.environment')), \Monolog\Logger::DEBUG, true, 0664, false);
        $handler->pushProcessor(($this->privates['monolog.processor.psr_log_message'] ?? ($this->privates['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())));

        return $this->privates['monolog.handler.matrix_client'] = $handler;
    }

    /**
     * Gets the private 'monolog.handler.filesystem_client' shared service.
     *
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getMonologHandlerFilesystemClientService()
    {
        $handler = new \Monolog\Handler\StreamHandler(\sprintf('%s/%s.filesystem.log', $this->getParameter('kernel.logs_dir'), $this->getParameter('kernel.environment')), \Monolog\Logger::DEBUG, true, null, false);
        $handler->pushProcessor(($this->privates['monolog.processor.psr_log_message'] ?? ($this->privates['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())));

        return $this->privates['monolog.handler.filesystem_client'] = $handler;
    }

    /**
     * Gets the private 'matrix.client.configurations' shared service.
     *
     * @return \ExportPortal\Matrix\Client\Configuration
     */
    protected function getMatrixClientSessionConfigurationService()
    {
        return $this->privates['matrix.client.configurations'] = (new \ExportPortal\Matrix\Client\Configuration())
            ->setHost($this->getParameter('kernel.env.MATRIX_HOMESERVER_HOST'))
            ->setUserAgent($this->getParameter('kernel.env.MATRIX_USER_AGENT') ?? 'ExportPortal/App')
            ->setTempFolderPath($this->getParameter('kernel.cache_dir') . '/matrix')
            ->setApiKeyPrefix('acess_token', 'Bearer')
        ;
    }

    /**
     * Gets the public 'matrix.client' shared service.
     *
     * @return \ExportPortal\Matrix\Client\Client
     */
    protected function getMatrixClientApiLocatorService()
    {
        return $this->services['matrix.client'] = new \ExportPortal\Matrix\Client\Client(
            ($this->privates['guzzle.http_client'] ?? $this->getGuzzleHttpClientService()),
            ($this->privates['matrix.client.configurations'] ?? $this->getMatrixClientSessionConfigurationService()),
            ($this->privates['matrix.client.header_selector'] ?? $this->privates['matrix.client.header_selector'] = new \ExportPortal\Matrix\Client\HeaderSelector())
        );
    }

    /**
     * Gets the public 'matrix' shared service.
     *
     * @return \App\Bridge\Matrix\MatrixConnector
     */
    protected function getMatrixService()
    {
        return $this->services['matrix'] = new \App\Bridge\Matrix\MatrixConnector(
            (
                $this->privates['matrix.configuration'] ?? $this->privates['matrix.configuration'] = (new \App\Bridge\Matrix\Configuration())
                    ->setLogger(($this->privates['monolog.logger.matrix'] ?? $this->getMonologLoggerMatrixService()))
                    ->setDeviceId($this->getParameter('kernel.env.MATRIX_LOCAL_DEVICE_ID'))
                    ->setSyncVersion($this->getParameter('kernel.env.MATRIX_SYNC_VERSION'))
                    ->setEventNamespace($this->getParameter('kernel.env.MATRIX_EVENT_NAMESPACE'))
                    ->setUser($this->getParameter('kernel.env.MATRIX_ADMIN_USER'))
                    ->setUserId($this->getParameter('kernel.env.MATRIX_ADMIN_USER_ID'))
                    ->setPassword($this->getParameter('kernel.env.MATRIX_ADMIN_PASSWORD'))
                    ->setAccessToken($this->getParameter('kernel.env.MATRIX_ADMIN_ACCESS_TOKEN'))
                    ->setHomeserverName($this->getParameter('kernel.env.MATRIX_HOMESERVER_NAME'))
                    ->setHomeserverHost($this->getParameter('kernel.env.MATRIX_HOMESERVER_HOST'))
                    ->setEncryptionEnabled(\filter_var($this->getParameter('kernel.env.MATRIX_ENCRYPTION_ENABLED'), \FILTER_VALIDATE_BOOL))
                    ->setNamingStrategy(
                        ($this->privates['matrix.naming_strategy.postfixed'] ?? $this->privates['matrix.naming_strategy.postfixed'] = new \App\Bridge\Matrix\Mapping\PostfixedNamingStrategy(
                            ($this->privates['matrix.naming_strategy.uuid'] ?? $this->privates['matrix.naming_strategy.uuid'] = new \App\Bridge\Matrix\Mapping\UuidNamingStrategy(
                                $this->getParameter('kernel.env.APP_UUID_NAMESPACE'),
                                $this->getParameter('kernel.env.MATRIX_HOMESERVER_NAME')
                            )),
                            $this->getParameter('kernel.env.MATRIX_SYNC_VERSION')
                        ))
                    )
                    ->setUserNamingStrategy(
                        ($this->privates['matrix.naming_strategy.user.postfixed'] ?? $this->privates['matrix.naming_strategy.user.postfixed'] = new \App\Bridge\Matrix\Mapping\UserPostfixedNamingStrategy(
                            ($this->privates['matrix.naming_strategy.user.uuid'] ?? $this->privates['matrix.naming_strategy.user.uuid'] = new \App\Bridge\Matrix\Mapping\UserUuidNamingStrategy(
                                $this->getParameter('kernel.env.APP_UUID_NAMESPACE'),
                                $this->getParameter('kernel.env.MATRIX_HOMESERVER_NAME')
                            )),
                            $this->getParameter('kernel.env.MATRIX_SYNC_VERSION')
                        ))
                    )
                    ->setSpacesNamingStrategy(
                        ($this->privates['matrix.naming_strategy.spaces.postfixed'] ?? $this->privates['matrix.naming_strategy.spaces.postfixed'] = new \App\Bridge\Matrix\Mapping\DefaultSpacesNamingStrategy(
                            $this->getParameter('kernel.env.MATRIX_HOMESERVER_NAME')
                        ))
                    )
            ),
            ($this->services['matrix.client'] ?? $this->getMatrixClientApiLocatorService()),
            ($this->privates['matrix.data_providers.user_reference'] = new \App\Bridge\Matrix\User\DatabaseUserReferenceProvider(
                ($this->privates['matrix.models.matrix_users'] ?? $this->privates['matrix.models.matrix_users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Matrix_Users_Model::class)),
                $this->getParameter('kernel.env.MATRIX_SYNC_VERSION')
            )),
            ($this->privates['matrix.error_handler'] ?? $this->privates['matrix.error_handler'] = new \App\Bridge\Matrix\ExceptionHandler(($this->privates['monolog.logger.matrix'] ?? $this->privates['monolog.logger.matrix'] = $this->getMonologLoggerMatrixService()))),
        );
    }

    /**
     * Gets the public 'matrix.room.factory' shared service.
     *
     * @return \App\Bridge\Matrix\Room\RoomFactory
     */
    protected function getMatrixRoomFactoryService()
    {
        return $this->services['matrix.room.factory'] = new \App\Bridge\Matrix\Room\RoomFactory(
            ($this->services['matrix'] ?? $this->getMatrixService()),
            ($this->privates['matrix.models.matrix_rooms'] ?? $this->privates['matrix.models.matrix_rooms'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Matrix_Rooms_Model::class)),
            ($this->privates['matrix.models.matrix_spaces'] ?? $this->privates['matrix.models.matrix_spaces'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Matrix_Spaces_Model::class)),
        );
    }

    /**
     * Gets the public 'matrix.space.factory' shared service.
     *
     * @return \App\Bridge\Matrix\Room\SpaceFactory
     */
    protected function getMatrixSpaceFactoryService()
    {
        return $this->services['matrix.space.factory'] = new \App\Bridge\Matrix\Room\SpaceFactory(
            ($this->services['matrix'] ?? $this->getMatrixService()),
            ($this->privates['matrix.models.matrix_rooms'] ?? $this->privates['matrix.models.matrix_rooms'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Matrix_Rooms_Model::class)),
            ($this->privates['matrix.models.matrix_spaces'] ?? $this->privates['matrix.models.matrix_spaces'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Matrix_Spaces_Model::class)),
        );
    }

    /**
     * Gets the public 'mailer.mailer' shared service.
     *
     * @return \Symfony\Component\Mailer\Mailer
     */
    protected function getMailerService()
    {
        return $this->services['mailer.mailer'] = new \Symfony\Component\Mailer\Mailer(
            ($this->privates['mailer.transports'] ?? $this->getMailerTransportsService()),
            ($this->services['messenger.default_bus'] ?? $this->getMessengerDefaultBusService()),
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService())
        );
    }

    /**
     * Gets the public 'mailer.transports' shared service.
     *
     * @return \Symfony\Component\Mailer\Transport
     */
    protected function getMailerTransportsService()
    {
        return $this->services['mailer.transports'] = (new \Symfony\Component\Mailer\Transport(new \Symfony\Component\DependencyInjection\Argument\RewindableGenerator(function () {
            yield 0 => ($this->privates['mailer.transports.factory.null'] ?? $this->getMailerTransportFactoryNullService());
            yield 1 => ($this->privates['mailer.transports.factory.sendmail'] ?? $this->getMailerTransportFactorySendmailService());
            yield 2 => ($this->privates['mailer.transports.factory.native'] ?? $this->getMailerTransportFactoryNativeService());
            yield 3 => ($this->privates['mailer.transports.factory.smtp'] ?? $this->getMailerTransportFactorySmtpService());
            yield 4 => ($this->privates['mailer.transports.factory.legacy'] ?? $this->getMailerTransportFactoryLegacyService());
        }, 5)))->fromStrings(['main' => $this->getEnv('MAILER_DSN')]);
    }

    /**
     * Gets the private 'mailer.transports.factory.null' shared service.
     *
     * @return \Symfony\Component\Mailer\Transport\TransportFactoryInterface
     */
    protected function getMailerTransportFactoryNullService()
    {
        return $this->privates['mailer.transports.factory.null'] = new \Symfony\Component\Mailer\Transport\NullTransportFactory(
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['http_client'] ?? $this->getHttpClientService()),
            ($this->privates['monolog.logger.mailer'] ?? $this->getMonologLoggerMailerService())
        );
    }

    /**
     * Gets the private 'mailer.transports.factory.sendmail' shared service.
     *
     * @return \Symfony\Component\Mailer\Transport\TransportFactoryInterface
     */
    protected function getMailerTransportFactorySendmailService()
    {
        return $this->privates['mailer.transports.factory.sendmail'] = new \Symfony\Component\Mailer\Transport\SendmailTransportFactory(
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['http_client'] ?? $this->getHttpClientService()),
            ($this->privates['monolog.logger.mailer'] ?? $this->getMonologLoggerMailerService())
        );
    }

    /**
     * Gets the private 'mailer.transports.factory.native' shared service.
     *
     * @return \Symfony\Component\Mailer\Transport\TransportFactoryInterface
     */
    protected function getMailerTransportFactoryNativeService()
    {
        return $this->privates['mailer.transports.factory.native'] = new \Symfony\Component\Mailer\Transport\NativeTransportFactory(
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['http_client'] ?? $this->getHttpClientService()),
            ($this->privates['monolog.logger.mailer'] ?? $this->getMonologLoggerMailerService())
        );
    }

    /**
     * Gets the private 'mailer.transports.factory.smtp' shared service.
     *
     * @return \Symfony\Component\Mailer\Transport\TransportFactoryInterface
     */
    protected function getMailerTransportFactorySmtpService()
    {
        return $this->privates['mailer.transports.factory.smtp'] = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory(
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['http_client'] ?? $this->getHttpClientService()),
            ($this->privates['monolog.logger.mailer'] ?? $this->getMonologLoggerMailerService())
        );
    }

    /**
     * Gets the private 'mailer.transports.factory.legacy' shared service.
     *
     * @return \Symfony\Component\Mailer\Transport\TransportFactoryInterface
     */
    protected function getMailerTransportFactoryLegacyService()
    {
        return $this->privates['mailer.transports.factory.legacy'] = new \ExportPortal\Mailer\Bridge\Legacy\Transport\LegacyTransportFactory(
            ($this->services['doctrine'] ?? $this->getDoctrineService()),
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['http_client'] ?? $this->getHttpClientService()),
            ($this->privates['monolog.logger.mailer'] ?? $this->getMonologLoggerMailerService())
        );
    }

    /**
     * Gets the public 'notifier' shared service.
     *
     * @return \Symfony\Component\Notifier\Notifier
     */
    protected function getNotifierService()
    {
        $this->services['notifier'] = $instance = new \Symfony\Component\Notifier\Notifier(
            new \Symfony\Component\DependencyInjection\Argument\ServiceLocator(
                $this->getServiceProxy,
                [
                    'sms'     => ['privates', 'notifier.channel.sms', 'getNotifierChannelSmsService', false],
                    'chat'    => ['privates', 'notifier.channel.chat', 'getNotifierChannelChatService', false],
                    'push'    => ['privates', 'notifier.channel.push', 'getNotifierChannelPushService', false],
                    'email'   => ['privates', 'notifier.channel.email', 'getNotifierChannelEmailService', false],
                    'browser' => ['privates', 'notifier.channel.browser', 'getNotifierChannelBrowserService', false],
                    'storage' => ['privates', 'notifier.channel.storage', 'getNotifierChannelStorageService', false],
                ],
                [
                    'sms'     => 'Symfony\\Component\\Notifier\\Channel\\SmsChannel',
                    'chat'    => 'Symfony\\Component\\Notifier\\Channel\\ChatChannel',
                    'push'    => 'Symfony\\Component\\Notifier\\Channel\\PushChannel',
                    'email'   => 'Symfony\\Component\\Notifier\\Channel\\EmailChannel',
                    'browser' => 'Symfony\\Component\\Notifier\\Channel\\BrowserChannel',
                    'storage' => \ExportPortal\Bridge\Notifier\Channel\StorageChannel::class,
                ]
            ),
            new \Symfony\Component\Notifier\Channel\ChannelPolicy(
                ['urgent' => [0 => 'email'], 'high' => [0 => 'email'], 'medium' => [0 => 'email'], 'low' => [0 => 'email']]
            )
        );
        $instance->addAdminRecipient(new \Symfony\Component\Notifier\Recipient\Recipient('admin@example.com', ''));

        return $instance;
    }

    /**
     * Gets the private 'notifier.channel.storage' shared service.
     *
     * @return \ExportPortal\Bridge\Notifier\Channel\StorageChannel
     */
    protected function getNotifierChannelStorageService()
    {
        return $this->privates['notifier.channel.storage'] = new \ExportPortal\Bridge\Notifier\Channel\StorageChannel(
            null, // We have a bus, so we don't need the transport here
            ($this->services['messenger.default_bus'] ?? $this->getMessengerDefaultBusService())
        );
    }

    /**
     * Gets the private 'notifier.channel.push' shared service.
     *
     * @return \Symfony\Component\Notifier\Channel\PushChannel
     */
    protected function getNotifierChannelPushService()
    {
        return $this->privates['notifier.channel.push'] = new \Symfony\Component\Notifier\Channel\PushChannel(
            null, // We have a bus, so we don't need the transport here
            ($this->services['messenger.default_bus'] ?? $this->getMessengerDefaultBusService())
        );
    }

    /**
     * Gets the private 'notifier.channel.browser' shared service.
     *
     * @return \Symfony\Component\Notifier\Channel\BrowserChannel
     */
    protected function getNotifierChannelBrowserService()
    {
        return $this->privates['notifier.channel.browser'] = new \Symfony\Component\Notifier\Channel\BrowserChannel(
            ($this->services['request_stack'] ?? $this->getRequestStackService())
        );
    }

    /**
     * Gets the private 'notifier.channel.chat' shared service.
     *
     * @return \Symfony\Component\Notifier\Channel\ChatChannel
     */
    protected function getNotifierChannelChatService()
    {
        return $this->privates['notifier.channel.chat'] = new \Symfony\Component\Notifier\Channel\ChatChannel(
            null, // We have a bus, so we don't need the transport here
            ($this->services['messenger.default_bus'] ?? $this->getMessengerDefaultBusService())
        );
    }

    /**
     * Gets the private 'notifier.channel.email' shared service.
     *
     * @return \Symfony\Component\Notifier\Channel\EmailChannel
     */
    protected function getNotifierChannelEmailService()
    {
        return $this->privates['notifier.channel.email'] = new \Symfony\Component\Notifier\Channel\EmailChannel(
            null, // We have a bus, so we don't need the transport here
            ($this->services['messenger.default_bus'] ?? $this->getMessengerDefaultBusService()),
            null
        );
    }

    /**
     * Gets the private 'notifier.channel.sms' shared service.
     *
     * @return \Symfony\Component\Notifier\Channel\SmsChannel
     */
    protected function getNotifierChannelSmsService()
    {
        return $this->privates['notifier.channel.sms'] = new \Symfony\Component\Notifier\Channel\SmsChannel(
            null, // We have a bus, so we don't need the transport here
            ($this->services['messenger.default_bus'] ?? $this->getMessengerDefaultBusService())
        );
    }

    /**
     * Gets the private 'chatter' shared service.
     *
     * @return \Symfony\Component\Notifier\Chatter
     */
    protected function getChatterService()
    {
        return $this->privates['chatter'] = new \Symfony\Component\Notifier\Chatter(
            ($this->privates['chatter.transports'] ?? $this->getNotifierChatterTransportsService()),
            ($this->services['messenger.default_bus'] ?? $this->getMessengerDefaultBusService()),
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService())
        );
    }

    /**
     * Gets the public 'chatter.transports' shared service.
     *
     * @return \Symfony\Component\Notifier\Transport\Transports
     */
    protected function getNotifierChatterTransportsService()
    {
        return $this->services['chatter.transports'] = (new \Symfony\Component\Notifier\Transport(
            new \Symfony\Component\DependencyInjection\Argument\RewindableGenerator(
                function () {
                    yield 0 => ($this->privates['notifier.transport_factory.matrix'] ?? $this->getNotifierTransportFactoryMatrixService());
                    yield 1 => ($this->privates['notifier.transport_factory.null'] ?? $this->getNotifierTransportFactoryNullService());
                },
                2
            )
        ))->fromStrings(['matrix' => $this->getEnv('NOTIFIER_MATRIX_DSN')]);
    }

    /**
     * Gets the public 'texter' shared service.
     *
     * @return \Symfony\Component\Notifier\Texter
     */
    protected function getTexterService()
    {
        return $this->privates['texter'] = new \Symfony\Component\Notifier\Texter(
            ($this->services['texter.transports'] ?? $this->getNotifierTexterTransportsService()),
            ($this->services['messenger.default_bus'] ?? $this->getMessengerDefaultBusService()),
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService())
        );
    }

    /**
     * Gets the public 'texter.transports' shared service.
     *
     * @return \Symfony\Component\Notifier\Transport\Transports
     */
    protected function getNotifierTexterTransportsService()
    {
        return $this->services['texter.transports'] = (new \Symfony\Component\Notifier\Transport(
            new \Symfony\Component\DependencyInjection\Argument\RewindableGenerator(
                function () {
                    yield 0 => ($this->privates['notifier.transport_factory.legacy'] ?? $this->getNotifierTransportFactoryLegacyService());
                    yield 1 => ($this->privates['notifier.transport_factory.null'] ?? $this->getNotifierTransportFactoryNullService());
                },
                2
            )
        ))->fromStrings(['legacy' => $this->getEnv('NOTIFIER_LEGACY_DSN')]);
    }

    /**
     * Gets the private 'notifier.transport_factory.matrix' shared service.
     *
     * @return \ExportPortal\Component\Notifier\Bridge\Matrix\MatrixTransportFactory
     */
    protected function getNotifierTransportFactoryMatrixService()
    {
        return $this->privates['notifier.transport_factory.matrix'] = new \ExportPortal\Component\Notifier\Bridge\Matrix\MatrixTransportFactory(
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['http_client'] ?? $this->getHttpClientService())
        );
    }

    /**
     * Gets the private 'notifier.transport_factory.null' shared service.
     *
     * @return \Symfony\Component\Notifier\Transport\NullTransportFactory
     */
    protected function getNotifierTransportFactoryNullService()
    {
        return $this->privates['notifier.transport_factory.null'] = new \Symfony\Component\Notifier\Transport\NullTransportFactory(
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['http_client'] ?? $this->getHttpClientService())
        );
    }

    /**
     * Gets the private 'notifier.transport_factory.legacy' shared service.
     *
     * @return \Symfony\Component\Notifier\Transport\NullTransportFactory
     */
    protected function getNotifierTransportFactoryLegacyService()
    {
        return $this->privates['notifier.transport_factory.legacy'] = new \ExportPortal\Component\Notifier\Bridge\Legacy\LegacyTransportFactory(
            ($this->services['doctrine'] ?? $this->getDoctrineService()),
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->privates['http_client'] ?? $this->getHttpClientService()),
            ($this->privates['monolog.logger.notifier'] ?? $this->getMonologLoggerNotifierService())
        );
    }

    /**
     * Gets the private 'http_client' shared service.
     *
     * @return \Symfony\Component\HttpClient\TraceableHttpClient
     */
    protected function getHttpClientService()
    {
        $instance = \Symfony\Component\HttpClient\HttpClient::create([], 6);
        if ($this->debugMode) {
            $instance = new \Symfony\Component\HttpClient\TraceableHttpClient(
                $instance,
                ($this->privates['debug.stopwatch'] ?? $this->getStopwatchService())
            );
        }
        /** @var \Psr\Log\LoggerAwareInterface $instance */
        $instance->setLogger(($this->privates['monolog.logger.http_client'] ?? $this->getMonologLoggerHttpClientService()));

        return $this->privates['http_client'] = $instance;
    }

    /**
     * Gets the public 'app.chat.binding' shared service.
     *
     * @return \App\Services\ChatBindingService
     */
    protected function getChatBindingService()
    {
        return $this->services['app.chat.binding'] = new \App\Services\ChatBindingService(
            ($this->services['matrix'] ?? $this->getMatrixService()),
            ($this->services['model_locator'] ?? $this->getModelLocatorService()),
        );
    }

    /**
     * Gets the public 'app.sample_orders' shared service.
     *
     * @return \App\Services\SampleOrdersService
     */
    protected function getSampleOrdersService()
    {
        return $this->services['app.sample_orders'] = new \App\Services\SampleOrdersService(
            ($this->services['model_locator'] ?? $this->getModelLocatorService()),
            ($this->services['messenger'] ?? $this->getMessengerService()),
            ($this->services['app.chat.binding'] ?? $this->getChatBindingService()),
            ($this->services['validation.adapter'] ?? $this->getValidationAdapterService())
        );
    }

    /**
     * Gets the public 'validation.adapter' shared service.
     *
     * @return \App\Common\Validation\Legacy\ValidatorAdapter
     */
    protected function getValidationAdapterService()
    {
        return $this->services['validation.adapter'] = new \App\Common\Validation\Legacy\ValidatorAdapter(
            ($this->privates['validator.legacy'] ?? $this->privates['validator.legacy'] = ($this->services['library_locator'] ?? $this->getLibraryLocatorService())->get(\Validator::class))
        );
    }

    /**
     * Gets the private 'epdocs_client.auth' shared service.
     *
     * @return \App\Plugins\EPDocs\Http\Auth
     */
    protected function getEpDocsAuthService()
    {
        return $this->privates['epdocs_client.auth'] = new \App\Plugins\EPDocs\Http\Auth(
            ($this->privates['guzzle.epdocs.http_client'] ?? $this->getGuzzleHttpEpDocsClientService()),
            ($this->privates['epdocs_client.auth.strategy'] ?? $this->privates['epdocs_client.auth.strategy'] = new \App\Plugins\EPDocs\Http\Authentication\Bearer()),
            ($this->privates['epdocs_client.auth.storage'] ?? $this->privates['epdocs_client.auth.storage'] = new \App\Plugins\EPDocs\Storage\JwtTokenStorage(
                ($this->privates['guzzle.epdocs.http_client'] ?? $this->getGuzzleHttpEpDocsClientService()),
                ($this->privates['epdocs_client.auth.credentials'] ?? $this->privates['epdocs_client.auth.credentials'] = new \App\Plugins\EPDocs\Credentials\JwtCredentials(
                    $this->getEnv('EP_DOCS_API_USERNAME'),
                    $this->getEnv('EP_DOCS_API_SECRET')
                ))
            ))
        );
    }

    /**
     * Gets the public 'epdocs_client' shared service.
     *
     * @return \App\Plugins\EPDocs\Rest\RestClient
     */
    protected function getEpDocsRestService()
    {
        return $this->services['epdocs_client'] = new \App\Plugins\EPDocs\Rest\RestClient(
            ($this->privates['guzzle.epdocs.http_client'] ?? $this->getGuzzleHttpEpDocsClientService()),
            ($this->privates['epdocs_client.auth'] ?? $this->getEpDocsAuthService()),
            ($this->privates['epdocs_client.configuration'] ?? $this->privates['epdocs_client.configuration'] = (new \App\Plugins\EPDocs\Configuration())
                ->setHttpOrigin($this->getEnv('EP_DOCS_REFERRER'))
                ->setDefaultUserId($this->getEnv('EP_DOCS_ADMIN_SALT'))
            ),
            ($this->privates['monolog.logger.epdocs_api'] ?? $this->getMonologLoggerEpDocsApiService())
        );
    }

    /**
     * Gets the public 'app.buyer_industry_of_interest' shared service.
     *
     * @return \App\Services\BuyerIndustryOfInterestService
     */
    protected function getBuyerIndustryOfInterestService()
    {
        return $this->services['app.buyer_industry_of_interest'] = new \App\Services\BuyerIndustryOfInterestService(
            ($this->services['model_locator'] ?? $this->getModelLocatorService())
        );
    }

    /**
     * Gets the public 'app.buyer_industry_of_interest' shared service.
     *
     * @return \App\Services\CalendarEpEventsService
     */
    protected function getCalendarEpEventsService()
    {
        return $this->services['app.calendar_ep_events_service'] = new \App\Services\CalendarEpEventsService(
            ($this->privates['models.calendar_events'] ?? $this->privates['models.calendar_events'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Calendar_Events_Model::class)),
            ($this->privates['models.calendar_notifications'] ?? $this->privates['models.calendar_notifications'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Calendar_Notifications_Model::class)),
            ($this->services['notifier'] ?? $this->getNotifierService())
        );
    }

    /**
     * Gets the public 'app.edit_request.profile.processing' shared service.
     *
     * @return \App\Services\EditRequest\ProfileEditRequestProcessingService
     */
    protected function getProfileEditRequestProcessingServiceService()
    {
        return $this->services['app.edit_request.profile.processing'] = new \App\Services\EditRequest\ProfileEditRequestProcessingService(
            ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
            ($this->privates['models.profile_edit_requests'] ?? $this->privates['models.profile_edit_requests'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Profile_Edit_Requests_Model::class)),
            ($this->privates['models.elasticsearch_users'] ?? $this->privates['models.elasticsearch_users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Elasticsearch_Users_Model::class)),
            ($this->services['app.edit_request.profile.documents'] ?? $this->getEditRequestProfileEditRequestDocumentsServiceService()),
            ($this->services['app.phone_codes'] ?? $this->getPhoneCodesService()),
            ($this->privates['messenger.command_bus'] ?? $this->privates['messenger.command_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('command.bus')),
            ($this->privates['messenger.event_bus'] ?? $this->privates['messenger.event_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('event.bus')),
        );
    }

    /**
     * Gets the public 'app.edit_request.company.processing' shared service.
     *
     * @return \App\Services\Profile\CompanyEditRequestProcessingService
     */
    protected function getCompanyEditRequestProcessingServiceService()
    {
        return $this->services['app.edit_request.company.processing'] = new \App\Services\EditRequest\CompanyEditRequestProcessingService(
            ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
            ($this->privates['models.company_edit_requests'] ?? $this->privates['models.company_edit_requests'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Seller_Company_Edit_Requests_Model::class)),
            ($this->privates['models.elasticsearch_users'] ?? $this->privates['models.elasticsearch_users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Elasticsearch_Users_Model::class)),
            ($this->services['app.data_provider.company'] ?? $this->getDataProviderCompanyProviderService()),
            ($this->services['app.edit_request.company.documents'] ?? $this->getEditRequestCompanyEditRequestDocumentsServiceService()),
            ($this->services['app.phone_codes'] ?? $this->getPhoneCodesService()),
            ($this->privates['messenger.command_bus'] ?? $this->privates['messenger.command_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('command.bus')),
            ($this->privates['messenger.event_bus'] ?? $this->privates['messenger.event_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('event.bus')),
        );
    }

    /**
     * Gets the public 'app.edit_request.profile.documents' shared service.
     *
     * @return \App\Services\EditRequest\ProfileEditRequestDocumentsService
     */
    protected function getEditRequestProfileEditRequestDocumentsServiceService()
    {
        return $this->services['app.edit_request.profile.documents'] = new \App\Services\EditRequest\ProfileEditRequestDocumentsService(
            ($this->privates['models.profile_edit_requests'] ?? $this->privates['models.profile_edit_requests'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Profile_Edit_Requests_Model::class)),
            ($this->privates['models.profile_edit_request_documents'] ?? $this->privates['models.profile_edit_request_documents'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Profile_Edit_Request_Documents_Model::class)),
            ($this->privates['models.verification_documents'] ?? $this->privates['models.verification_documents'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Verification_Documents_Model::class)),
            ($this->services['epdocs_client'] ?? $this->getEpDocsRestService()),
            ($this->privates['messenger.command_bus'] ?? $this->privates['messenger.command_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('command.bus')),
            ($this->privates['monolog.logger'] ?? $this->getMonologLoggerService()),
        );
    }

    /**
     * Gets the public 'app.edit_request.company.documents' shared service.
     *
     * @return \App\Services\EditRequest\CompanyEditRequestDocumentsService
     */
    protected function getEditRequestCompanyEditRequestDocumentsServiceService()
    {
        return $this->services['app.edit_request.company.documents'] = new \App\Services\EditRequest\CompanyEditRequestDocumentsService(
            ($this->privates['models.company_edit_requests'] ?? $this->privates['models.company_edit_requests'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Seller_Company_Edit_Requests_Model::class)),
            ($this->privates['models.company_edit_request_documents'] ?? $this->privates['models.profile_edit_request_documents'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Seller_Company_Edit_Request_Documents_Model::class)),
            ($this->privates['models.verification_documents'] ?? $this->privates['models.verification_documents'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Verification_Documents_Model::class)),
            ($this->services['epdocs_client'] ?? $this->getEpDocsRestService()),
            ($this->privates['messenger.command_bus'] ?? $this->privates['messenger.command_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('command.bus')),
            ($this->privates['monolog.logger'] ?? $this->getMonologLoggerService()),
        );
    }

    /**
     * Gets the public 'app.phone_codes' shared service.
     *
     * @return \App\Services\PhoneCodesService
     */
    protected function getPhoneCodesService()
    {
        return $this->services['app.phone_codes'] = new \App\Services\PhoneCodesService(
            ($this->privates['models.phone_codes'] ?? $this->privates['models.phone_codes'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Phone_Codes_Model::class)),
            ($this->privates['league.iso_provider'] ?? ($this->services['league.iso_provider'] = new \League\ISO3166\ISO3166())),
        );
    }

    /**
     * Gets the public 'app.processing.profile' shared service.
     *
     * @return \App\Services\Profile\UserProfileProcessingService
     */
    protected function getUserProfileProcessingServiceService()
    {
        return $this->services['app.processing.profile'] = new \App\Services\Profile\UserProfileProcessingService(
            ($this->services['app.phone_codes'] ?? $this->getPhoneCodesService()),
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->services['model_locator'] ?? $this->getModelLocatorService()),
            ($this->privates['messenger.event_bus'] ?? $this->privates['messenger.event_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('event.bus')),
        );
    }

    /**
     * Gets the public 'app.processing.company' shared service.
     *
     * @return \App\Services\Company\CompanyProcessingService
     */
    protected function getCompanyProcessingServiceService()
    {
        return $this->services['app.processing.company'] = new \App\Services\Company\CompanyProcessingService(
            ($this->services['app.phone_codes'] ?? $this->getPhoneCodesService()),
            ($this->services['app.data_provider.company'] ?? $this->getDataProviderCompanyProviderService()),
            ($this->services['app.processing.company.media'] ?? $this->getCompanyMediaProcessorServiceService()),
            ($this->privates['messenger.event_bus'] ?? $this->privates['messenger.event_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('event.bus')),
            ($this->privates['messenger.command_bus'] ?? $this->privates['messenger.command_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('command.bus')),
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService())
        );
    }

    /**
     * Gets the public 'app.processing.b2b.request' shared service.
     *
     * @return \App\Services\B2b\B2bRequestProcessingService
     */
    protected function getB2bRequestProcessingServiceService()
    {
        return $this->services['app.processing.b2b.request'] = new \App\Services\B2b\B2bRequestProcessingService(
            ($this->services['app.data_provider.b2b_request'] ?? $this->getDataProviderB2bRequestProviderService()),
            $this->getModelLocatorService()->get(\User_Statistic_Model::class),
        );
    }

    /**
     * Gets the public 'app.processing.company.access' shared service.
     *
     * @return \App\Services\Company\CompanyGuardService
     */
    protected function getCompanyGuardServiceService()
    {
        return $this->services['app.processing.company.access'] = new \App\Services\Company\CompanyGuardService(
            ($this->services['app.data_provider.company'] ?? $this->getDataProviderCompanyProviderService())
        );
    }

    /**
     * Gets the public 'app.processing.company.media' shared service.
     *
     * @return \App\Services\Company\CompanyMediaProcessorService
     */
    protected function getCompanyMediaProcessorServiceService()
    {
        /** @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface */
        $parameters = $this->getParameter('kernel.build_config');

        return $this->services['app.processing.company.media'] = new \App\Services\Company\CompanyMediaProcessorService(
            ($this->services['app.data_provider.company'] ?? $this->getDataProviderCompanyProviderService()),
            ($this->privates['messenger.event_bus'] ?? $this->privates['messenger.event_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('event.bus')),
            ($this->privates['messenger.command_bus'] ?? $this->privates['messenger.command_bus'] = ($this->services['messenger'] ?? $this->getMessengerService())->bus('command.bus')),
            ($this->services['event_dispatcher'] ?? $this->getEventDispatcherService()),
            ($this->services['media.thumnail_reader'] ?? $this->getMediaThumbnailReaderService()),
            ($this->privates['library.legacy.image_handler'] ?? $this->privates['library.legacy.image_handler'] = ($this->services['library_locator'] ?? $this->getLibraryLocatorService())->get(\TinyMVC_Library_Image_intervention::class)),
            ($this->services['intervention_image.manager'] ?? $this->getInterventionImageMangerService()),
            ($this->services['mime_types'] ?? $this->getMimeTypeService()),
            ($this->services['filesystem.provider'] ?? $this->getFilesystemFilesystemproviderService()),
            $parameters->get('img')
        );
    }

    /**
     * Gets the public 'app.renderer.datatable.profile_edit_request' shared service.
     *
     * @return \App\Renderer\ProfileEditRequestDatatableRenderer
     */
    protected function getRendererDatatableProfileEditRequestDatatableRendererService()
    {
        return $this->services['app.renderer.datatable.profile_edit_request'] = new \App\Renderer\ProfileEditRequestDatatableRenderer();
    }

    /**
     * Gets the public 'app.renderer.datatable.company_edit_request' shared service.
     *
     * @return \App\Renderer\CompanyEditRequestDatatableRenderer
     */
    protected function getRendererDatatableCompanyEditRequestDatatableRendererService()
    {
        return $this->services['app.renderer.datatable.company_edit_request'] = new \App\Renderer\CompanyEditRequestDatatableRenderer(
            ($this->services['filesystem.provider'] ?? $this->getFilesystemFilesystemproviderService())
        );
    }

    /**
     * Gets the public 'app.renderer.view.profile_edit_request' shared service.
     *
     * @return \App\Renderer\ProfileEditRequestViewRenderer
     */
    protected function getRendererViewProfileEditRequestViewRendererService()
    {
        return $this->services['app.renderer.view.profile_edit_request'] = new \App\Renderer\ProfileEditRequestViewRenderer(
            ($this->services['renderer'] ?? $this->getRendererService()),
            ($this->services['app.edit_request.profile.processing'] ?? $this->getProfileEditRequestProcessingServiceService()),
            ($this->services['app.data_provider.profile_edit_request'] ?? $this->getDataProviderProfileEditRequestProviderService()),
            ($this->services['app.data_provider.user_profile'] ?? $this->getDataProviderUserProfileProviderService()),
        );
    }

    /**
     * Gets the public 'app.renderer.view.company_edit_request' shared service.
     *
     * @return \App\Renderer\CompanyEditRequestViewRenderer
     */
    protected function getRendererViewCompanyEditRequestViewRendererService()
    {
        return $this->services['app.renderer.view.company_edit_request'] = new \App\Renderer\CompanyEditRequestViewRenderer(
            ($this->services['renderer'] ?? $this->getRendererService()),
            ($this->services['app.edit_request.company.processing'] ?? $this->getCompanyEditRequestProcessingServiceService()),
            ($this->services['app.data_provider.company_edit_request'] ?? $this->getDataProviderCompanyEditRequestProviderService()),
            ($this->services['app.data_provider.company'] ?? $this->getDataProviderCompanyProviderService())
        );
    }

    /**
     * Gets the public 'app.renderer.view.profile_edit' shared service.
     *
     * @return \App\Renderer\UserProfileEditViewRenderer
     */
    protected function getRendererViewUserProfileEditViewRendererService()
    {
        return $this->services['app.renderer.view.profile_edit'] = new \App\Renderer\UserProfileEditViewRenderer(
            ($this->services['renderer'] ?? $this->getRendererService()),
            ($this->services['app.data_provider.user_profile'] ?? $this->getDataProviderUserProfileProviderService()),
            ($this->services['app.edit_request.profile.processing'] ?? $this->getProfileEditRequestProcessingServiceService()),
            ($this->privates['phone_utils'] ?? \libphonenumber\PhoneNumberUtil::getInstance())
        );
    }

    /**
     * Gets the public 'app.renderer.view.company_edit' shared service.
     *
     * @return \App\Renderer\CompanyEditViewRenderer
     */
    protected function getRendererViewCompanyEditViewRendererService()
    {
        return $this->services['app.renderer.view.company_edit'] = new \App\Renderer\CompanyEditViewRenderer(
            ($this->services['renderer'] ?? $this->getRendererService()),
            ($this->services['app.data_provider.company'] ?? $this->getDataProviderCompanyProviderService()),
            ($this->services['app.edit_request.company.processing'] ?? $this->getCompanyEditRequestProcessingServiceService()),
            ($this->privates['phone_utils'] ?? \libphonenumber\PhoneNumberUtil::getInstance()),
            ($this->services['filesystem.provider'] ?? $this->getFilesystemFilesystemproviderService()),
        );
    }

    /**
     * Gets the public 'app.renderer.view.verification_documents' shared service.
     *
     * @return \App\Renderer\VerificationDocumentsViewRenderer
     */
    protected function getRendererViewVerificationDocumentsViewRendererService()
    {
        return $this->services['app.renderer.view.verification_documents'] = new \App\Renderer\VerificationDocumentsViewRenderer(
            ($this->services['renderer'] ?? $this->getRendererService()),
            ($this->services['model_locator'] ?? $this->getModelLocatorService()),
        );
    }

    /**
     * Gets the public 'app.data_provider.profile_edit_request' shared service.
     *
     * @return \App\DataProvider\ProfileEditRequestProvider
     */
    protected function getDataProviderProfileEditRequestProviderService()
    {
        return $this->services['app.data_provider.profile_edit_request'] = new \App\DataProvider\ProfileEditRequestProvider(
            ($this->services['model_locator'] ?? $this->getModelLocatorService())
        );
    }

    /**
     * Gets the public 'app.data_provider.company_edit_request' shared service.
     *
     * @return \App\DataProvider\CompanyEditRequestProvider
     */
    protected function getDataProviderCompanyEditRequestProviderService()
    {
        return $this->services['app.data_provider.company_edit_request'] = new \App\DataProvider\CompanyEditRequestProvider(
            ($this->services['model_locator'] ?? $this->getModelLocatorService())
        );
    }

    /**
     * Gets the public 'app.data_provider.account' shared service.
     *
     * @return \App\DataProvider\AccountProvider
     */
    protected function getDataProviderAccountProviderService()
    {
        return $this->services['app.data_provider.account'] = new \App\DataProvider\AccountProvider(
            ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
            ($this->privates['models.profile_complete_options'] ?? $this->privates['models.profile_complete_options'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Complete_Profile_Options_Model::class)),
            ($this->privates['library.legacy.session'] ?? $this->privates['library.legacy.session'] = ($this->services['library_locator'] ?? $this->getLibraryLocatorService())->get(\TinyMVC_Library_Session::class)),
        );
    }

    /**
     * Gets the public 'app.data_provider.user_profile' shared service.
     *
     * @return \App\DataProvider\UserProfileProvider
     */
    protected function getDataProviderUserProfileProviderService()
    {
        return $this->services['app.data_provider.user_profile'] = new \App\DataProvider\UserProfileProvider(
            ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class))
        );
    }

    /**
     * Gets the public 'app.data_provider.user_profile' shared service.
     *
     * @return \App\DataProvider\IndexedBlogDataProvider
     */
    protected function getDataProviderIndexedBlogProviderService()
    {
        return $this->services['app.data_provider.indexed_blog'] = new \App\DataProvider\IndexedBlogDataProvider(
            ($this->services['model_locator'] ?? $this->getModelLocatorService())
        );
    }

    /**
     * Gets the public 'app.data_provider.b2b_request' shared service.
     *
     * @return \App\DataProvider\B2bRequestProvider
     */
    protected function getDataProviderB2bRequestProviderService()
    {
        return $this->services['app.data_provider.b2b_request'] = new \App\DataProvider\B2bRequestProvider(
            ($this->privates['models.b2b'] ?? $this->privates['models.b2b'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\B2b_Requests_Model::class)),
            ($this->privates['models.b2b_partners'] ?? $this->privates['models.b2b_partners'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Partners_Types_Model::class)),
            ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class)),
        );
    }

    /**
     * Gets the public 'app.data_provider.b2b_indexed_request' shared service.
     *
     * @return \App\DataProvider\B2bIndexedRequestProvider
     */
    protected function getDataProviderB2bIndexedRequestProviderService()
    {
        return $this->services['app.data_provider.b2b_indexed_request'] = new \App\DataProvider\B2bIndexedRequestProvider(
            ($this->privates['models.elastic_b2b'] ?? $this->privates['models.elastic_b2b'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Elasticsearch_B2b_Model::class))
        );
    }

    /**
     * Gets the public 'app.data_provider.user_room' shared service.
     *
     * @return \App\DataProvider\UserRoomsProvider
     */
    protected function getDataProviderUserRoomsProviderService()
    {
        return $this->services['app.data_provider.user_room'] = new \App\DataProvider\UserRoomsProvider(
            ($this->privates['matrix.models.matrix_users'] ?? $this->privates['matrix.models.matrix_users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Matrix_Users_Model::class)),
            (new \Symfony\Component\Cache\Adapter\ArrayAdapter())
        );
    }

    /**
     * Gets the public 'app.data_provider.company' shared service.
     *
     * @return \App\DataProvider\CompanyProvider
     */
    protected function getDataProviderCompanyProviderService()
    {
        return $this->services['app.data_provider.company'] = new \App\DataProvider\CompanyProvider(
            ($this->services['model_locator'] ?? $this->getModelLocatorService())
        );
    }

    /**
     * Gets the public 'app.data_provider.notification_metadata' shared service.
     *
     * @return \App\DataProvider\NotificationMetadataProvider
     */
    protected function getDataProviderNotificationMetadataProviderService()
    {
        return $this->services['app.data_provider.notification_metadata'] = new \App\DataProvider\NotificationMetadataProvider(
            ($this->services['model_locator'] ?? $this->getModelLocatorService())
        );
    }

    /**
     * Gets the public 'app.data_provider.verification_documents' shared service.
     *
     * @return \App\DataProvider\VerificationDocumentProvider
     */
    protected function getDataProviderVerificationDocumentProviderService()
    {
        return $this->services['app.data_provider.user_profile'] = new \App\DataProvider\VerificationDocumentProvider(
            ($this->services['model_locator'] ?? $this->getModelLocatorService())
        );
    }

    /**
     * Gets the public 'app.encryption.file_key_storage' shared service.
     *
     * @return \App\Common\Encryption\Storage\FileKeyStorage
     */
    protected function getEncryptionFileKeyStorageService()
    {
        return $this->privates['app.encryption.file_key_storage'] = new \App\Common\Encryption\Storage\FileKeyStorage(
            ($this->privates['filesystem.legacy.server_key.storage'] ?? $this->privates['filesystem.legacy.server_key.storage'] = ($this->services['filesystem.provider'] ?? $this->getFilesystemFilesystemproviderService())->storage('root.storage')),
            $this->getParameter('kernel.env.APP_ENCRYPTION_KEY_PATH')
        );
    }

    /**
     * Gets the public 'intervention_image.manager' shared service.
     *
     * @return \Intervention\Image\ImageManager
     */
    protected function getInterventionImageMangerService()
    {
        return $this->services['intervention_image.manager'] = new \Intervention\Image\ImageManager(['driver' => 'imagick']);
    }

    /**
     * Gets the public 'media.thumnail_reader' shared service.
     *
     * @return \App\Common\Media\Thumbnail\ThumbnailReader
     */
    protected function getMediaThumbnailReaderService()
    {
        return $this->services['media.thumnail_reader'] = new \App\Common\Media\Thumbnail\ThumbnailReader(
            [
                new \App\Common\Media\Thumbnail\Provider\YoutubeThumbnailProvider(($this->privates['guzzle.http_client'] ?? $this->getGuzzleHttpClientService())),
                new \App\Common\Media\Thumbnail\Provider\VimeoThumbnailProvider(($this->privates['guzzle.http_client'] ?? $this->getGuzzleHttpClientService())),
            ]
        );
    }

    /**
     * Gets the public 'mime_types' shared service.
     *
     * @return \Symfony\Component\Mime\MimeTypes
     */
    protected function getMimeTypeService()
    {
        return $this->services['mime_types'] = new \Symfony\Component\Mime\MimeTypes();
    }

    /**
     * Returns the dynamic parameter.
     */
    private function getDynamicParameter(string $name)
    {
        switch ($name) {
            case 'kernel.runtime_environment': $value = $this->getEnv('default:kernel.environment:APP_RUNTIME_ENV'); break;
            case 'kernel.secret': $value = $this->getEnv('APP_SECRET'); break;

            case 'mailer.envelope.no_reply': $value = $this->getEnv('EMAIL_NO_REPLY'); break;
            case 'mailer.envelope.support': $value = $this->getEnv('EMAIL_SUPPORT'); break;
            case 'mailer.envelope.img_url': $value = $this->getEnv('MAILER_BASE_URL'); break;
            case 'mailer.envelope.base_url': $value = $this->getEnv('MAILER_BASE_URL'); break;
            case 'mailer.envelope.preview_url': $value = $this->getEnv('MAILER_PREVIEW_URL'); break;
            case 'mailer.envelope.unsubscribe_url': $value = $this->getEnv('MAILER_UNSUBSCRIBE_URL'); break;

            case 'backstop.enabled': $value = \filter_var($this->getEnv('BACKSTOP_TEST_MODE'), \FILTER_VALIDATE_BOOL); break;

            default:
                if (\str_starts_with($name, 'kernel.env.')) {
                    $value = $this->getEnv(\strtoupper(\substr($name, \strlen('kernel.env.'))));

                    break;
                }

                throw new \InvalidArgumentException(sprintf('The dynamic parameter "%s" must be defined.', $name));
        }
        $this->loadedDynamicParameters[$name] = true;

        return $this->dynamicParameters[$name] = $value;
    }

    /**
     * Gets the item data provider shared service.
     *
     * @return \App\DataProvider\IndexedProductDataProvider
     */
    protected function getIndexedDataProviderProductService ()
    {
        return $this->services['app.data_provider.product'] = new \App\DataProvider\IndexedProductDataProvider(
            ($this->privates['models.products'] ?? $this->privates['models.products'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Products_Model::class)),
            ($this->privates['models.products.elastic'] ?? $this->privates['models.products.elastic'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Elasticsearch_Items_Model::class)),
            ($this->privates['app.transformer.items.baidu'] ?? $this->getItemsListForBaiduTransformerService()),
        );
    }

    /**
     * Gets ерш droplist items data provider shared service.
     */
    protected function getDroplistItemsDataProviderService()
    {
        return $this->services['app.data_provider.droplist'] = new \App\DataProvider\DroplistItemsDataProvider(
            ($this->privates['models.droplist'] ?? $this->privates['models.droplist'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Items_Droplist_Model::class))
        );
    }

    /**
     * Gets the public 'app.transformer.items.baidu' shared service.
     *
     * @return \App\Common\Transformers\ItemsListForBaiduTransformer
     */
    protected function getItemsListForBaiduTransformerService()
    {
        return $this->services['app.transformer.items.baidu'] = new \App\Common\Transformers\ItemsListForBaiduTransformer($this->getParameter('app.base_uri'));
    }

    /**
     * Gets the public 'app.transformer.pick_of_month.company' shared service.
     *
     * @return \App\Common\Transformers\CompanyPickOfTheMonthForBaiduTransformer
     */
    protected function getCompanyPickOfTheMonthForBaiduTransformerService()
    {
        return $this->services['app.transformer.pick_of_month.company'] = new \App\Common\Transformers\CompanyPickOfTheMonthForBaiduTransformer(
            ($this->services['filesystem.provider'] ?? $this->getFilesystemFilesystemproviderService()),
            $this->getParameter('app.base_uri')
        );
    }

    /**
     * Gets the public 'app.transformer.pick_of_month.item' shared service.
     *
     * @return \App\Common\Transformers\ItemPickOfTheMonthForBaiduTransformer
     */
    protected function getItemPickOfTheMonthForBaiduTransformerService()
    {
        return $this->services['app.transformer.pick_of_month.item'] = new \App\Common\Transformers\ItemPickOfTheMonthForBaiduTransformer(
            ($this->services['filesystem.provider'] ?? $this->getFilesystemFilesystemproviderService()),
            $this->getParameter('app.base_uri')
        );
    }

    /**
     * Gets the public 'money.formatter' shared service.
     *
     * @return \Money\MoneyFormatter
     */
    protected function getMoneyMoneyFormatterService()
    {
        $currencies = $this->privates['money.currencies'] ?? ($this->privates['money.currencies'] = new \Money\Currencies\ISOCurrencies());

        return $this->services['money.formatter'] = new \Money\Formatter\AggregateMoneyFormatter([
            'USD' => new \Money\Formatter\IntlMoneyFormatter(
                $this->privates['money.formatter.usd_formatter'] ?? ($this->privates['money.formatter.usd_formatter'] = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY)),
                $currencies
            ),
            'GBP' => new \Money\Formatter\IntlMoneyFormatter(
                $this->privates['money.formatter.gbp_formatter'] ?? ($this->privates['money.formatter.gbp_formatter'] = new \NumberFormatter('en_GB', \NumberFormatter::CURRENCY)),
                $currencies
            ),
            'EUR' => new \Money\Formatter\IntlMoneyFormatter(
                $this->privates['money.formatter.eur_formatter'] ?? ($this->privates['money.formatter.eur_formatter'] = new \NumberFormatter('nl_NL', \NumberFormatter::CURRENCY)),
                $currencies
            ),
        ]);
    }

    /**
     * Get canonical URL.
     *
     * @return \App\Seo\SeoPageService
     */
    protected function getSeoPageServiceService()
    {
        $instance = new \App\Seo\SeoPageService();
        $instance->setCanonicalUrl($this->getParameter('app.current_url'));

        return $this->services['app.seo.seo_page_service'] = $instance;
    }

    /**
     * Gets the item data provider shared service.
     *
     * @return \App\DataProvider\User\UserDataListProvider
     */
    protected function getUserDataListProviderService()
    {
        return $this->services['app.data_provider.user_data_list'] = new \App\DataProvider\User\UserDataListProvider(
            ($this->privates['models.users'] ?? $this->privates['models.users'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Users_Model::class))
        );
    }

    /**
     * Gets 'app.blog_category_route_resolver' shared service.
     *
     * @return \App\Services\BlogCategoryRouteResolverService
     */
    protected function getBlogCategoryRouteResolverService()
    {
        return $this->services['app.blog_category_route_resolver'] = new \App\Services\BlogCategoryRouteResolverService(
            ($this->privates['models.blogsCategories'] ?? $this->privates['models.blogsCategories'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Blogs_Categories_Model::class)),
            ($this->privates['models.blogsCategoriesI18n'] ?? $this->privates['models.blogsCategoriesI18n'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\Blogs_Categories_I18n_Model::class)),
        );
    }

    /**
     * Gets 'app.data_provider.maxtrix_user' shared service.
     *
     * @return \App\DataProvider\MatrixUserProvider
     */
    protected function getMatrixUserProviderService()
    {
        return $this->services['app.data_provider.maxtrix_user'] = new \App\DataProvider\MatrixUserProvider(
            ($this->services['matrix'] ?? $this->getMatrixService()),
            ($this->privates['library.legacy.session'] ?? $this->privates['library.legacy.session'] = ($this->services['library_locator'] ?? $this->getLibraryLocatorService())->get(\TinyMVC_Library_Session::class)),
            ($this->privates['cache.maxtrix.user_provide'] ?? $this->getCacheMatrixUserProviderCacheService()),
        );
    }

    /**
     * Gets 'app.data_provider.navbar_state' shared service.
     *
     * @return \App\DataProvider\NavigationBarStateProvider
     */
    protected function getNavigationBarStateProviderService()
    {
        return $this->services['app.data_provider.navbar_state'] = new \App\DataProvider\NavigationBarStateProvider(
            ($this->privates['models.user_system_messages'] ?? $this->privates['models.user_system_messages'] = ($this->services['model_locator'] ?? $this->getModelLocatorService())->get(\User_System_Messages_Model::class)),
            ($this->privates['library.legacy.session'] ?? $this->privates['library.legacy.session'] = ($this->services['library_locator'] ?? $this->getLibraryLocatorService())->get(\TinyMVC_Library_Session::class)),
            ($this->privates['cache.templates.navbar_state_provider'] ?? $this->getCacheNavigationBarStateProviderService()),
        );
    }
}
