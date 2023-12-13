<?php

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Remove [02.12.2021]
 * Library not used
 */
class TinyMVC_Library_Models
{
    /**
     * Construct an instance of this class.
     *
     * @param ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        /** @var TinyMVC_Load */
        $loader = $container->get(TinyMVC_Load::class);
        $loader->model(User_Model::class, 'user');
    }
}
