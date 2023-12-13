<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Database;

use App\Common\Database\Model;

/**
 * @author Anton Zencenco
 */
interface RepositoryAwareInterface
{
    /**
     * Get the database repository.
     */
    public function getRepository(): Model;
}
