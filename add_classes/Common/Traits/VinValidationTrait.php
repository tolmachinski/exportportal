<?php

namespace App\Common\Traits;

/**
 * @deprecated
 */
trait VinValidationTrait
{
    use VinDecoderAwareTrait;

    /**
     * Validates the VIN code.
     *
     * @param string $vinCode
     * @param array  $errors
     *
     * @return bool
     */
    protected function validateVin($vinCode, &$errors = array())
    {
        if (empty($vinCode)) {
            return true;
        }

        $decoder = $this->getVinDecoder();
        if ($decoder->is_used($vinCode)) {
            $errors['vin_used'] = 'The VIN is already used by other vehicle.';
        }

        if (empty($decoder->decode($vinCode, 'both'))) {
            $errors['vin_empty'] = 'The VIN number is not correct.';
        }

        return empty($errors);
    }
}
