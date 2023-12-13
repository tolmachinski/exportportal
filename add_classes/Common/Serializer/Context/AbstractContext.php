<?php

namespace App\Common\Serializer\Context;

abstract class AbstractContext implements ContextInterface
{
    /**
     * Cached context.
     *
     * @var array
     */
    private $cachedContext;

    /**
     * Checks if context has cache support.
     *
     * @return bool
     */
    public function hasCacheSupport()
    {
        return true;
    }

    public function getContext()
    {
        if ($this->hasCacheSupport()) {
            if (empty($this->cachedContext)) {
                $this->cachedContext = $this->getCachebleContext();
            }

            return $this->cachedContext;
        }

        return $this->getCachebleContext();
    }

    /**
     * Returns the context cached by instance later.
     *
     * @return array
     */
    abstract protected function getCachebleContext();
}
