<?php

declare(strict_types=1);

use App\Common\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller Autocomplete.
 */
class Autocomplete_Controller extends TinyMVC_Controller
{
    public function index(): void
    {
        show_404();
    }

    /**
     * Remove the autocomplete record.
     */
    public function remove(): void
    {
        $request = request();
        if ('OPTIONS' === $request->getMethod()) {
            output('', 204);
        }

        try {
            /** @var TinyMVC_Library_Search_Autocomplete $autocomplete */
            $autocomplete = library(TinyMVC_Library_Search_Autocomplete::class);
            $autocomplete->setTextHostFromReferer($request);
            $autocomplete->removeAutocompleteRecord(
                $request->query->get('deltok') ?? null,
                $request->query->get('ref') ?? null,
                (int) id_session() ?: null
            );
        } catch (Exception $exception) {
            jsonResponse(
                translate('systmess_error_failed_to_delete_record'),
                'error',
                withDebugInformation(
                    [],
                    ['exception' => throwableToArray($exception)]
                )
            );
        }

        output('', 200);
    }

    public function ajax_get_item_suggestions(): Response
    {
        //region HTTP check
        checkIsAjax();
        $request = request();
        if ('OPTIONS' === $request->getMethod()) {
            return new Response('', 204);
        }
        //endregion HTTP check

        //region Text search
        //Returning empty succssful response if search keywords are empty
        $searchText = $request->request->get('keywords');
        if (empty($searchText)) {
            return new Response('', 200);
        }
        //Otherwise we use the ElasticSearch suggester to ge the list of results
        /** @var \Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(\Elasticsearch_Items_Model::class);
        $rezults = $elasticsearchItemsModel->getSuggestions($searchText);
        //remove whitespaces before applying bold
        $searchText = trim($searchText);
        $htmlSuggestions = views()->fetch('/new/template_views/autocomplete_suggestions_view', [
            'suggestions' => array_map(
                fn ($search) => preg_replace("/{$searchText}/i", '<b>$0</b>', $search['text']),
                $rezults['options']
            ),
        ]);
        //endregion Text search

        return new Response($htmlSuggestions, 200, [
            'Content-Type' => 'text/html',
        ]);
    }

    /**
     * Get categories suggestion from ElasticSearch and render with view
     *
     * @return Response
     */
    public function ajax_get_category_suggestions(): Response
    {
        checkIsAjax();

        $request = request();
        if (Request::METHOD_OPTIONS === $request->getMethod()) {
            return new Response('', 204);
        }

        $searchText = $request->request->get('keywords');
        if (empty($searchText)) {
            return new Response('', 200);
        }

        /** @var \Elasticsearch_Category_Model $elasticsearchCategoriesModel */
        $elasticsearchCategoriesModel = model(\Elasticsearch_Category_Model::class);
        $suggestions = $elasticsearchCategoriesModel->getSuggestions($searchText);

        $searchText = trim($searchText);

        $htmlSuggestions = views()->fetch('/new/template_views/autocomplete_suggestions_categories_view', [
            'suggestions' => array_map(
                fn ($item) => [
                    'text' => preg_replace("/{$searchText}/i", '<b>$0</b>', $item['text']),
                    'breadcrumbs' => $item['_source']['breadcrumbs_data']
                ],
                $suggestions['options']
            ),
        ]);

        return new Response($htmlSuggestions, 200, [
            'Content-Type' => 'text/html',
        ]);
    }

    /**
      * Get b2b suggestion from ElasticSearch and render with view
     */
    public function ajax_get_b2b_suggestions(): Response
    {
        checkIsAjax();

        $request = request();
        if (Request::METHOD_OPTIONS === $request->getMethod()) {
            return new Response('', 204);
        }

        $searchText = $request->request->get('keywords');
        if (empty($searchText)) {
            return new Response('', 200);
        }

        /** @var \Elasticsearch_B2b_Model $elasticsearchB2bModel */
        $elasticsearchB2bModel = model(\Elasticsearch_B2b_Model::class);
        $suggestions = $elasticsearchB2bModel->getSuggestions($searchText);

        $searchText = trim($searchText);
        $htmlSuggestions = views()->fetch('/new/template_views/autocomplete_suggestions_view', [
            'suggestions'    => array_map(
                fn ($search) => preg_replace("/{$searchText}/i", '<b>$0</b>', $search['text']),
                $suggestions['options']
            ),
        ]);

        return new Response($htmlSuggestions, 200, [
            'Content-Type' => 'text/html',
        ]);
    }

    public function ajax_get_blogs_suggestions(): Response
    {
        checkIsAjax();
        $request = request();
        if (Request::METHOD_OPTIONS === $request->getMethod()) {
            return new Response('', 204);
        }

        $searchText = $request->request->get('keywords');
        if (empty($searchText)) {
            return new Response('', 200);
        }

        /** @var \Elasticsearch_Blogs_Model $elasticsearchBlogsModel */
        $elasticsearchBlogsModel = model(\Elasticsearch_Blogs_Model::class);
        $suggestions = $elasticsearchBlogsModel->getSuggestions($searchText);

        $searchText = trim($searchText);
        $htmlSuggestions = views()->fetch('/new/template_views/autocomplete_suggestions_view', [
            'suggestions' => array_map(
                fn ($search) => preg_replace("/{$searchText}/i", '<b>$0</b>', $search['text']),
                $suggestions['options']
            ),
        ]);

        return new Response($htmlSuggestions, 200, [
            'Content-Type' => 'text/html',
        ]);
    }

    /**
     * Help suggestions
     */
    public function ajax_get_help_suggestions(): Response
    {
        checkIsAjax();
        $request = request();
        if (Request::METHOD_OPTIONS === $request->getMethod()) {
            return new Response('', 204);
        }

        $searchText = $request->request->get('keywords');
        if (empty($searchText)) {
            return new Response('', 200);
        }

        /** @var \ElasticSearch_Help_Model $elasticsearchHelpModel */
        $elasticsearchHelpModel = model(\ElasticSearch_Help_Model::class);
        $suggestions = $elasticsearchHelpModel->getSuggestions($searchText);

        $searchText = trim($searchText);
        $htmlSuggestions = views()->fetch('/new/template_views/autocomplete_suggestions_view', [
            'suggestions' => array_map(
                fn ($search) => preg_replace("/{$searchText}/i", '<b>$0</b>', $search['text']),
                $suggestions['options']
            )
        ]);

        return new Response($htmlSuggestions, 200, [
            'Content-Type' => 'text/html'
        ]);
    }

    /**
     * Events suggestions
     */
    public function ajax_get_events_suggestions(): Response
    {
        checkIsAjax();
        $request = request();
        if ('OPTIONS' === $request->getMethod()) {
            return new Response('', 204);
        }

        $searchText = $request->request->get('keywords');
        if (empty($searchText)) {
            return new Response('', 200);
        }

        /** @var \Elasticsearch_Ep_Events_Model $elasticsearchEventsModel */
        $elasticsearchEventsModel = model(\Elasticsearch_Ep_Events_Model::class);
        $suggestions = $elasticsearchEventsModel->getSuggestions($searchText);
        /* A function that prints the variable and dies. */

        $searchText = trim($searchText);
        $htmlSuggestions = views()->fetch('/new/template_views/autocomplete_suggestions_view', [
            'suggestions' => array_filter(
                array_unique(
                    array_map(
                        function ($search) use ($searchText) {
                            if ($search['_source']['end_date'] > date('Y-m-d h:i:s')) {
                                return preg_replace("/{$searchText}/i", '<b>$0</b>', $search['text']);
                            }
                        },
                        $suggestions['options']
                    )
                )
            ),
        ]);

        return new Response($htmlSuggestions, 200, [
            'Content-Type' => 'text/html',
        ]);
    }
}

// End of file autocomplete.php
// Location: /tinymvc/myapp/controllers/autocomplete.php
