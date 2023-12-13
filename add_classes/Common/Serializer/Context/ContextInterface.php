<?php

namespace App\Common\Serializer\Context;

interface ContextInterface
{
    /**
     * Returns the serialization context.
     *
     * @return array
     */
    public function getContext();
}
