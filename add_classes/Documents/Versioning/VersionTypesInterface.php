<?php

namespace App\Documents\Versioning;

interface VersionTypesInterface
{
    const NONE = null;

    const PENDING = 'pending';

    const ACCEPTED = 'accepted';

    const REJECTED = 'rejected';
}
