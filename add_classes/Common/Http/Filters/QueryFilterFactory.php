<?php

declare(strict_types=1);

namespace App\Common\Http\Filters;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class QueryFilterFactory implements FiltersFactoryInterface
{
    /**
     * The list of filter prototypes.
     *
     * @var Collection
     */
    private $filterPrototypes;

    /**
     * Creates instance of the filter factory.
     *
     * @param Collection $filterPrototypes
     */
    public function __construct(Collection $filterPrototypes)
    {
        $this->filterPrototypes = $filterPrototypes;
    }

    /**
     * {@inheritdoc}
     */
    public function createFilters(Request $request): Collection
    {
        return $this->filterPrototypes->map(function (ParameterBag $base) use ($request) {
            $key = $base->get('name');
            $value = $request->query->get($key);
            $filter = clone $base;
            if (null !== $value) {
                $filter->set('value', $value);
            }

            return $filter;
        })->filter(function (ParameterBag $filter) { return null !== $filter->get('value'); });
    }
}
