<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Http\Request;

final class BlogCategoryRouteResolverService
{
    /**
     * The list of category names.
     *
     * @var string[]
     */
    private array $blogsCategories;

    /**
     * The list of category i18n names.
     *
     * @var string[]
     */
    private array $blogsCategoriesI18n;

    /** @var Model */
    private $blogsCategoriesModel;

    /** @var Model */
    private $blogsCategoriesI18nModel;

    /**
     * Creates the instance of the service.
     */
    public function __construct(Model $blogsCategoriesModel, Model $blogsCategoriesI18nModel)
    {
        $this->blogsCategoriesModel = $blogsCategoriesModel;
        $this->blogsCategoriesI18nModel = $blogsCategoriesI18nModel;
    }

    private function getCategories(): array
    {
        if (!isset($this->blogsCategories)) {
            $this->blogsCategories = array_column($this->blogsCategoriesModel->findAll(), 'url');
        }

        return $this->blogsCategories;
    }

    private function getLocalizedCategories(): array
    {
        if (!isset($this->blogsCategoriesI18n)) {
            $this->blogsCategoriesI18n = array_column($this->blogsCategoriesI18nModel->findAll(), 'url');
        }

        return $this->blogsCategoriesI18n;
    }

    /**
     * Used to rewrite exist pathInfo for blog/all
     */
    public function resolvePathInfo($pathInfo, Request $request)
    {
        $pathInfoParts = array_filter(explode('/', $pathInfo));

        if (isset($pathInfoParts[1]) && !in_array($pathInfoParts[1], ['detail', 'preview_blog']) && !$request->isAjaxRequest()) {
            if (
                !in_array($pathInfoParts[1], $this->getCategories())
                && !in_array($pathInfoParts[1], $this->getLocalizedCategories())
            ) {
                return "/blog/all{$pathInfo}";
            }
        }

        return $pathInfo;
    }

    /**
     * Used to define action on the routing to blog detail page
     */
    public function isValidBlogDetailUrl($urlSegments): bool
    {
        if ('detail' === $urlSegments[1]) {
            return true;
        }

        return in_array($urlSegments[1], $this->getCategories()) || in_array($urlSegments[1], $this->getLocalizedCategories());
    }
}
