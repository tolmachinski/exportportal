<?php

namespace App\Common\Traits;

trait VinDecoderAwareTrait
{
    /**
     * Returns the VIN decoder.
     *
     * @return \TinyMVC_Library_Vindecoder
     */
    protected function getVinDecoder()
    {
        return library('vindecoder');
    }
}
