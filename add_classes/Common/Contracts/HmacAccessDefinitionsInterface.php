<?php

namespace App\Common\Contracts;

interface HmacAccessDefinitionsInterface
{
    const ALGORITHM = 'sha256';

    const CONTEXT = 'hmac_hash_context';

    const SECRET = 'hmac_hash_secret';
}
