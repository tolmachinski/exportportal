<?php

namespace App\Plugins\EPDocs;

class ApiClient implements Client
{
    /**
     * Returns the new API resource.
     *
     * @param string $resourceName
     *
     * @return \App\Plugins\EPDocs\Resource
     */
    public function getResource($resourceName)
    {
        if (!is_string($resourceName)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid argument 1 at % - expected % got %',
                __METHOD__,
                'string',
                gettype($resourceName)
            ));
        }

        if (!class_exists($resourceName)) {
            throw new NotFoundException(sprintf('The resource with name "%s" is not found', $resourceName));
        }

        if (!$this->isAcceptedResourceName($resourceName)) {
            throw new UnknownResourceException(sprintf(
                'The resource with name "%s" must be instance of "%s" interface',
                $resourceName,
                Resource::class
            ));
        }

        return $this->createResource($resourceName);
    }

    /**
     * Checks if resource name is accepted.
     *
     * @param string $resourceName
     *
     * @return bool
     */
    protected function isAcceptedResourceName($resourceName)
    {
        return !is_a($resourceName, Resource::class, true);
    }

    /**
     * Creates the new resource from its name.
     *
     * @param string $resourceName
     *
     * @return \App\Plugins\EPDocs\Resource
     */
    protected function createResource($resourceName)
    {
        return new $resourceName($this);
    }
}
