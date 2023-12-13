<?php

declare(strict_types=1);

namespace App\Common\Assets;

use Doctrine\Common\Collections\Collection;

final class EntrypointLookupCollection implements EntrypointLookupCollectionInterface
{
    /**
     * The collection of entrypoint builds.
     *
     * @var Collection
     */
    private $builds;

    /**
     * The default build name.
     *
     * @var string
     */
    private $defaultBuildName;

    public function __construct(Collection $builds, string $defaultBuildName = null)
    {
        $this->builds = $builds;
        $this->defaultBuildName = $defaultBuildName;
    }

    /**
     * {@inheritdoc}
     */
    public function hasEntrypointLookup(?string $buildName = null): bool
    {
        if (null === $buildName) {
            if (null === $this->defaultBuildName) {
                return false;
            }

            $buildName = $this->defaultBuildName;
        }

        return $this->builds->containsKey($buildName);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntrypointLookup(?string $buildName = null): EntrypointLookupInterface
    {
        if (null === $buildName) {
            if (null === $this->defaultBuildName) {
                throw new UndefinedBuildException('There is no default build configured: please pass an argument to getEntrypointLookup().');
            }

            $buildName = $this->defaultBuildName;
        }

        if (!$this->builds->containsKey($buildName)) {
            throw new UndefinedBuildException(
                sprintf('The build "%s" is not configured', $buildName)
            );
        }

        return $this->builds->get($buildName);
    }
}
