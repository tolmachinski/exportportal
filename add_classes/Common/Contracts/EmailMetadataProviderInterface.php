<?php

namespace App\Common\Contracts;

use App\Common\Exceptions\BadEmailException;

interface EmailMetadataProviderInterface
{
    /**
     * Returns metadata for provided email.
     *
     * @return array
     *
     * @throws BadEmailException if email is invalid
     */
    public function getMetadata(string $email): array;
}
