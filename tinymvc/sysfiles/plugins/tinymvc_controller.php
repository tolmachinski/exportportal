<?php

/*
 * Name:       TinyMVC
 * About:      An MVC application framework for PHP
 * Copyright:  (C) 2007-2008 Monte Ohrt, All rights reserved.
 * Author:     Monte Ohrt, monte [at] ohrt [dot] com
 * License:    LGPL, see included license file
 */

// ------------------------------------------------------------------------

use App\Common\Http\Exceptions\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;

/**
 * TinyMVC_Controller.
 *
 * @author		Monte Ohrt
 */
class TinyMVC_Controller implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The name of the controller.
     *
     * @var string
     */
    public $name;

    /**
     * The loader.
     *
     * @var TinyMVC_Load
     */
    public $load;

    /**
     * The view handler.
     *
     * @var TinyMVC_View
     */
    public $view;

    /**
     * Controller constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        // save controller instance
        tmvc::instance($this, 'controller');

        $this->load = $container->get(TinyMVC_Load::class); // instantiate load library
        $this->view = $container->get(TinyMVC_View::class); // instantiate view library
        $this->container = $container;
    }

    /**
     * Magic call method. Gets called when an unspecified method is used.
     *
     * @param mixed $function
     * @param mixed $args
     */
    public function __call($function, $args)
    {
        if (DEBUG_MODE) {
            // header('HTTP/1.0 404 Not Found');
            throw new NotFoundHttpException("Unknown controller method '{$function}'");
        }

        show_404();
    }

    /**
     * Return result on calling controller as a function.
     */
    public function __invoke(string $action, array ...$args)
    {
        ob_start();
        $response = $this->{$action}(...$args);
        $output = ob_get_contents();
        ob_end_clean();

        if ($response && !$response instanceof Response) {
            throw new LogicException(
                sprintf('The controller must return a "%s" object but it returned %s.', Response::class, varToString($response))
            );
        }

        return $response ?? $output;
    }

    /**
     * The default controller method.
     */
    public function index()
    {
        // Your princess is in another castle.
    }

    /**
     * Gets a container parameter by its name.
     *
     * @return null|array|bool|float|int|string
     */
    protected function getParameter(string $name)
    {
        if (!$this->container->has('parameter_bag')) {
            throw new ServiceNotFoundException('parameter_bag.', null, null, [], sprintf(
                'The "%s::getParameter()" method is missing a parameter bag to work properly. ' .
                'Did you forget to register your controller as a service subscriber? ' .
                'This can be fixed either by using autoconfiguration or by manually wiring a "parameter_bag" in the service locator passed to the controller.',
                static::class
            ));
        }

        return $this->container->get('parameter_bag')->get($name);
    }

    /**
     * Get the container.
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Returns true if the service id is defined.
     */
    protected function has(string $id): bool
    {
        return $this->container->has($id);
    }

    /**
     * Gets a container service by its id.
     *
     * @return object The service
     */
    protected function get(string $id): object
    {
        return $this->container->get($id);
    }
}
