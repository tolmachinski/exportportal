<?php

use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/k.-Filesystem
 * @deprecated `[2022-04-30]` `v2.34` in favor of the `\ExportPortal\Contracts\Filesystem\FilesystemProviderInterface\FilesystemProviderInterface`
 *
 * Reason: replace with new filesystem integration
 *
 * @uses \ExportPortal\Contracts\Filesystem\FilesystemProviderInterface\FilesystemProviderInterface
 *
 * ```php
 * use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
 * //...
 * $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
 * ```
 */
class TinyMVC_Library_Filesystem
{
    /**
     * The filesystem provider.
     */
    private FilesystemProviderInterface $provider;

    public function __construct(ContainerInterface $container)
    {
        $this->provider = $container->get(FilesystemProviderInterface::class);
    }

    /**
     * Creates a filesystem instance.
     *
     * @param null|string $name
     *
     * @throws InvalidArgumentException if factory returns not instance of League\Flysystem\AdapterInterface
     *
     * @return FilesystemOperator
     */
    public function disk($name = null)
    {
        return $this->provider->storage($name ?? 'public.storage');
    }
}
