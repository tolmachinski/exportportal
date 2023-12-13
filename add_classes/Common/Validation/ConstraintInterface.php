<?php

namespace App\Common\Validation;

interface ConstraintInterface
{
    /**
     * @param ValidationDataInterface $data
     *
     * @throws ValidationException
     */
    public function assert(ValidationDataInterface $data);
}
