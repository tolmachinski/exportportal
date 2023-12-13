<?php

namespace App\Common\Kernel\Controller;

use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;

final class ControllerResolver extends BaseControllerResolver
{
    /**
     * The container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * The path to the controllers directory.
     *
     * @var string
     */
    private $controllersDir;

    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->controllersDir = $container->getParameter('kernel.controllers_dir');

        parent::__construct($logger);
    }

    /**
     * {@inheritdoc}
     *
     * This method looks for a '_controller' request attribute that represents
     * the controller name (a string like ClassName::MethodName).
     */
    public function getController(Request $request)
    {
        if (!$request->attributes->has('_controller')) {
            $request->attributes->set('_controller', $this->resolveLegacyController($request));
        }

        if ('error_controller' === $request->attributes->get('_controller')) {
            return $this->container->get('error_controller');
        }

        return parent::getController($request);
    }

    /**
     * Returns an instantiated controller.
     *
     * @param string $class A class name
     *
     * @return object
     */
    protected function instantiateController($class)
    {
        return new $class($this->container);
    }

    /**
     * Resolves the controller name.
     *
     * @return string
     */
    private function resolveControllerName(array $urlSegments)
    {
        $rootController = $this->container->getParameter('kernel.root_controller');
        $defaultController = $this->container->getParameter('kernel.default_controller');
        if (null !== $rootController) {
            $controllerName = $rootController;
        } else {
            $controllerName = !empty($urlSegments[0]) ? preg_replace('!\W!', '', $urlSegments[0]) : $defaultController;
        }

        $controllerFile = "{$this->controllersDir}/{$controllerName}.php";
        if (!file_exists($controllerFile)) {
            $controllerName = $defaultController;
        }

        return $this->normalizeName($controllerName) . '_Controller';
    }

    /**
     * Resolve the action name.
     *
     * @return string
     */
    private function resolveActionName(array $urlSegments)
    {
        $rootAction = $this->container->getParameter('kernel.root_controller.action');
        $rootController = $this->container->getParameter('kernel.root_controller');
        $defaultAction = $this->container->getParameter('kernel.default_controller.action');
        if (null !== $rootController) {
            // user override if set
            $actionName = $rootAction;
        } else {
            // get from url if present, else use default
            $actionName = !empty($urlSegments[1]) ? $urlSegments[1] : $defaultAction;
        }

        return $actionName;
    }

    /**
     * Returns the path info.
     *
     * @return string
     */
    private function getPathInfoForControllerMatch(Request $request)
    {
        if ($request->attributes->has('_request.resolved_path')) {
            return $request->attributes->get('_request.resolved_path');
        }

        return $request->getPathInfo();
    }

    /**
     * Camelize name.
     *
     * @param string $str
     *
     * @return string
     */
    private function normalizeName($str)
    {
        return str_replace(' ', '', ucwords($str));
    }

    /**
     * Resolves the legacy controller and returns the controller-method pair.
     *
     * @throws LogicException if method begins with '_'
     */
    private function resolveLegacyController(Request $request): array
    {
        $pathInfo = $this->getPathInfoForControllerMatch($request);
        $urlSegments = \array_filter('/' === $pathInfo ? [] : \explode('/', \ltrim($pathInfo, '/')));
        $controllerName = $this->resolveControllerName($urlSegments);
        $actionName = $this->resolveActionName($urlSegments);

        if (!method_exists($controllerName, $actionName) && !DEBUG_MODE) {
            unset($this->controller);
            $errorController = $this->container->getParameter('kernel.error_controller');
            $errorAction = $this->container->getParameter('kernel.error_controller.action');

            $controllerName = $this->normalizeName($errorController) . '_Controller';
            $actionName = $errorAction;
        }

        // Cannot call method names starting with underscore
        if ('_' === substr($actionName, 0, 1)) {
            throw new LogicException("Action name not allowed '{$actionName}'");
        }

        return [$this->instantiateController($controllerName), $actionName];
    }
}
