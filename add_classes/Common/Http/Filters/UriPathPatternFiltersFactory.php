<?php

declare(strict_types=1);

namespace App\Common\Http\Filters;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class UriPathPatternFiltersFactory implements FiltersFactoryInterface
{
    /**
     * The URI path pattern.
     *
     * @var string
     */
    private $pathPattern;

    /**
     * The vocabularu that contains the path slugs.
     *
     * @var ParameterBag
     */
    private $slugVocabulary;

    /**
     * The list of filter prototypes.
     *
     * @var Collection
     */
    private $filterPrototypes;

    /**
     * Creates instance of the filter factory.
     *
     * @param string       $pattern
     * @param Collection   $filterPrototypes
     * @param ParameterBag $slugVocabulary
     */
    public function __construct(
        string $pattern,
        Collection $filterPrototypes,
        ParameterBag $slugVocabulary
    ) {
        $this->pathPattern = $pattern;
        $this->filterPrototypes = $filterPrototypes;
        $this->slugVocabulary = $slugVocabulary ?? new ParameterBag();
    }

    /**
     * {@inheritdoc}
     */
    public function createFilters(Request $request): Collection
    {
        $rawFilters = $this->getFilterValuesFrommPath($request->getPathInfo());

        return $this->filterPrototypes->map(function (ParameterBag $base) use ($rawFilters) {
            $key = $base->get('name');
            $filter = clone $base;
            if (isset($rawFilters[$key])) {
                $filter->set('value', $rawFilters[$key]);
            }

            return $filter;
        })->filter(function (ParameterBag $filter) { return null !== $filter->get('value'); });
    }

    /**
     * Returns the values of the filters fethed from uri path.
     *
     * @param string $uriPath
     *
     * @return array
     */
    private function getFilterValuesFrommPath(string $uriPath): array
    {
        $pattern = $this->createUriMatchingPattern($this->pathPattern, "/\{\{([\p{L}_]*)\}\}/", $this->slugVocabulary);
        if (
            !preg_match_all($pattern, $uriPath, $matching, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL)
            || empty($matching[0])
        ) {
            return array();
        }

        return array_filter(
            array_filter($matching[0], function ($key) { return !is_numeric($key); }, ARRAY_FILTER_USE_KEY)
        );
    }

    /**
     * Creates the URI paraemeters pattern.
     *
     * @param string       $prefix
     * @param string       $template
     * @param ParameterBag $localeVocabulary
     *
     * @return string
     */
    private function createUriMatchingPattern(string $template, string $pattern, ParameterBag $localeVocabulary): string
    {
        if (
            !preg_match_all($pattern, $template, $matching)
            || empty($matching[0])
            || empty($matching[1])
        ) {
            return $template;
        }

        $replacement = array();
        foreach (array_combine($matching[0], $matching[1]) as $key => $part) {
            $replacement[$key] = preg_quote($localeVocabulary->get($part, ''), '/');
        }

        return strtr($template, $replacement);
    }
}
