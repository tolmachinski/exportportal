<?php

declare(strict_types=1);

namespace App\Common\Http\Filters;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

interface FiltersFactoryInterface
{
    /**
     * Creates thi list of the filters.
     *
     * @param Request $request
     *
     * @return Collection|ParameterBag[]
     */
    public function createFilters(Request $request): Collection;
}
