<?php

namespace App\Common\Transformers\Analytics;

use App\Common\Contracts\TransformerInterface;

final class TargetsTransformer implements TransformerInterface
{
    private $targets;

    public function __construct(array $target)
    {
        $this->targets = $target;
    }

    public function __toString()
    {
        return sprintf('[%s]', implode(', ', $this->transform()));
    }

    public function transform()
    {
        $collector = array();
        foreach ($this->targets as $target) {
            $collector[] = sprintf('{type: %s, value: "%s"}', $target['type'], $target['value']);
        }

        return $collector;
    }
}
