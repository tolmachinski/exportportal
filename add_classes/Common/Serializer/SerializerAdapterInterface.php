<?php

namespace App\Common\Serializer;

use Symfony\Component\Serializer\SerializerInterface;

interface SerializerAdapterInterface extends SerializerInterface
{
    /**
     * Returns the serialzier.
     *
     * @return \Symfony\Component\Serializer\SerializerInterface
     */
    public function getSerializer();

    /**
     * Returns the serialzier.
     *
     * @return \App\Common\Serializer\Serializer\Context\ContextInterface
     */
    public function getContext();
}
