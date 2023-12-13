<?php

namespace App\Common\Serializer\Context;

use InvalidArgumentException;

final class AggregatedContext extends AbstractContext
{
    /**
     * List of aggregated contexts.
     *
     * @var array
     */
    private $contexts = array();

    public function __construct(array $contexts = array())
    {
        foreach ($contexts as $index => $context) {
            if (!$context instanceof ContextInterface) {
                throw new InvalidArgumentException(sprintf('Invalid type found at "%s" in contexts list - only instances of %s are accepted', $index, ContextInterface::class));
            }
        }

        $this->contexts = $contexts;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCachebleContext()
    {
        $extract = array();
        foreach ($this->contexts as $context) {
            $extract = array_merge_recursive($extract, $context->getContext());
        }

        return $extract;
    }
}
