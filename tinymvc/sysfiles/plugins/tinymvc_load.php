<?php

/**
 * Name:       TinyMVC
 * About:      An MVC application framework for PHP
 * Copyright:  (C) 2007-2008 Monte Ohrt, All rights reserved.
 * Author:     Monte Ohrt, monte [at] ohrt [dot] com
 * License:    LGPL, see included license file.
 */

use App\Common\Database\Connection\PdoRegistry;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

// ------------------------------------------------------------------------

/**
 * TinyMVC_Load.
 *
 * @author		Monte Ohrt
 */
class TinyMVC_Load implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The connection registry.
     */
    private ConnectionRegistry $connectionRegistry;

    /**
     * The model locator.
     */
    private ModelLocator $modelLocator;

    /**
     * The library locator.
     */
    private LibraryLocator $libraryLocator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->connectionRegistry = $container->get(PdoRegistry::class);
        $this->libraryLocator = $container->get(LibraryLocator::class);
        $this->modelLocator = $container->get(ModelLocator::class);
    }

    /**
     * load a model object.
     *
     * @param string $modelName  the name of the model class
     * @param string $modelAlias the property name alias
     * @param string $filename   the filename
     * @param string $poolName   the database pool name to use
     *
     * @return bool
     */
    public function model($modelName, $modelAlias = null, $filename = null, $poolName = null)
    {
        // if no alias, use the model name
        if (!isset($modelAlias)) {
            $modelAlias = $modelName;
        }

        // TODO: remove this variable
        // if no filename, use the lower-case model name
        if (!isset($filename)) {
            $filename = strtolower($modelName) . '.php';
        }

        if (empty($modelAlias)) {
            throw new Exception('Model name cannot be empty');
        }
        if (!preg_match('!^[a-zA-Z][a-zA-Z0-9_]+$!', $modelAlias)) {
            throw new Exception("Model name '{$modelAlias}' is an invalid syntax");
        }
        if (method_exists($this, $modelAlias)) {
            throw new Exception("Model name '{$modelAlias}' is an invalid (reserved) name");
        }

        $className = !str_ends_with(strtolower($modelName), '_model') ? "{$modelName}_Model" : $modelName;
        $serviceName = implode(':', array_filter([$className, $modelAlias]));
        $serviceId = implode('@', array_filter([$serviceName, $poolName ?? $this->connectionRegistry->getDefaultConnectionName()]));
        if ($this->modelLocator->has($serviceId)) {
            return true;
        }

        $model = $this->modelLocator->get($serviceId);
        if (null !== $controller = $this->container->get(TinyMVC_Controller::class, ContainerInterface::NULL_ON_INVALID_REFERENCE)) {
            $controller->{$modelAlias} = $model;
        }

        return true;
    }

    /**
     * Load a library plugin.
     *
     * @param string $libName  the name of the library
     * @param string $alias    the property name alias
     * @param string $filename the filename
     *
     * @return bool
     */
    public function library($libName, $alias = null, $filename = null)
    {
        // if no alias, use the class name
        if (!isset($alias)) {
            $alias = $libName;
        }

        if (empty($alias)) {
            throw new Exception('Library name cannot be empty');
        }
        if (!preg_match('!^[0-9a-zA-Z][0-9a-zA-Z_]+$!', $alias)) {
            throw new Exception("Library name '{$alias}' is an invalid syntax");
        }
        if (method_exists($this, $alias)) {
            throw new Exception("Library name '{$alias}' is an invalid (reserved) name");
        }

        $className = !str_starts_with(strtolower($libName), 'tinymvc_library_') ? "TinyMVC_Library_{$libName}" : $libName;
        $serviceId = implode(':', array_filter([$className, $alias]));
        if ($this->libraryLocator->has($serviceId)) {
            return true;
        }

        $library = $this->libraryLocator->get($serviceId);
        if (null !== $controller = $this->container->get(TinyMVC_Controller::class, ContainerInterface::NULL_ON_INVALID_REFERENCE)) {
            $controller->{$alias} = $library;
        }

        return true;
    }

    /**
     * Load a script plugin.
     *
     * @param string $scriptName the script plugin name
     *
     * @return bool
     */
    public function script($scriptName)
    {
        if (!preg_match('!^[a-zA-Z][a-zA-Z_]+$!', $scriptName)) {
            throw new Exception("Invalid script name '{$scriptName}'");
        }
        $filename = strtolower("TinyMVC_Script_{$scriptName}.php");

        // look in myapps/myfiles/sysfiles plugins dirs
        $filepath = TMVC_MYAPPDIR . 'plugins' . DS . $filename;
        if (!file_exists($filepath)) {
            $filepath = TMVC_BASEDIR . 'myfiles' . DS . 'plugins' . DS . $filename;
        }
        if (!file_exists($filepath)) {
            $filepath = TMVC_BASEDIR . 'sysfiles' . DS . 'plugins' . DS . $filename;
        }

        if (!file_exists($filepath)) {
            throw new Exception("Unknown script file '{$filename}'");
        }

        return require_once $filepath;
    }

    /**
     * Returns a database plugin object.
     *
     * @param string $connection the name of the database connection (if NULL default pool is used)
     *
     * @return TinyMVC_PDO
     */
    public function database($connection = null)
    {
        return $this->connectionRegistry->getConnection($connection);
    }
}
