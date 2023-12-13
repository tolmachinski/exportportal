<?php

namespace App\Payments\Serializer\Context;

use App\Common\Serializer\Context\AbstractContext;

final class CurrencyContext extends AbstractContext
{
    /**
     * {@inheritdoc}
     */
    protected function getCachebleContext()
    {
        return array();
    }
}
