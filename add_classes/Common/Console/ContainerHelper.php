<?php

declare(strict_types=1);

namespace App\Common\Console;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerHelper extends Helper
{
    /**
     * The CLI container.
     */
    protected ContainerInterface $container;

    /**
     * @param ContainerInterface $container The CLI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retrieves CLI container.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'connection';
    }
}
