<?php

use App\Common\Database\Model;
use App\Common\Exceptions\MethodNotFoundException;
use App\Common\Traits\Elasticsearch\AutocompleteAnalyzerTrait;

final class ElasticSearch_Help_Model extends Model
{
    use AutocompleteAnalyzerTrait;

    private $type = 'help';

    /** @var TinyMVC_Library_Elasticsearch */
    private $elasticsearchLibrary;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    /**
     * Index method.
     *
     * Collect and  index data for Help suggestions
     */
    public function index(): void
    {
        $data = array_merge(
            $this->getFaqItems(),
            // $this->getUserGuidesItems(),
            $this->getTopicsItems(),
            $this->getCommunityHelpItems()
        );

        $this->elasticsearchLibrary->indexBulk($this->type, $data);
    }

    /**
     * Synq item request. Create / Update / Delete item from index.
     *
     * @param int    $item   item id
     * @param string $type   method name
     * @param bool   $remove only remove item
     */
    public function syncItem(int $item, string $type = null, bool $remove = false)
    {
        if (empty($item) || empty($type)) {
            return false;
        }

        try {
            $result = $this->{$type}($item);
            $data = array_shift($result);
        } catch (MethodNotFoundException $e) {
            return false;
        }

        if (empty($data)) {
            return false;
        }

        if ($remove || empty($data)) {
            return $this->elasticsearchLibrary->delete($this->type, $data['id']);
        }

        $elasticResult = $this->elasticsearchLibrary->get_by_id($this->type, $item);
        if (!$elasticResult['found']) {
            return $this->elasticsearchLibrary->index($this->type, $data['id'], $data);
        }

        return $this->elasticsearchLibrary->update($this->type, $data['id'], $data);
    }

    /**
     * Get suggestions.
     */
    public function getSuggestions(string $text): array
    {
        $melasticResults = $this->elasticsearchLibrary->mget('help', [
            [
                '_source' => 'suggest',
                'suggest' => [
                    'help-suggest' => [
                        'text'       => $text,
                        'completion' => [
                            'field'           => 'suggest_autocomplete',
                            'size'            => 20,
                            'skip_duplicates' => true,
                        ],
                    ],
                ],
            ],
        ]);

        $suggestions = $melasticResults['responses'][0]['suggest']['help-suggest'][0];
        $suggestions['options'] = array_slice(
            $melasticResults['responses'][0]['suggest']['help-suggest'][0]['options'],
            0,
            config('help_suggestion_search_size', 5)
        );

        return (array) $suggestions;
    }

    /**
     * Get FAQ data.
     *
     * @param null|mixed $id
     */
    private function getFaqItems($id = null): array
    {
        $params = [
            'columns' => [
                'id_faq', 'question',
            ],
            'with'    => ['tags'],
        ];

        /** @var Faqs_Model $faqModel */
        $faqModel = model(\Faqs_Model::class);

        if (null !== $id) {
            $faqItems[] = $faqModel->findOne($id);
        } else {
            $faqItems = $faqModel->findAllBy($params);
        }

        return array_map(function ($item) use ($id) {
            $tokens = $this->analyzeAutocompleteText($item['question']);
            foreach ($tokens as $token) {
                $item['suggest_autocomplete'][] = [
                    'input'  => $token['token'],
                    'weight' => 1,
                ];
            }

            $item['suggest_autocomplete'][] = [
                'input'  => $item['question'],
                'weight' => 2,
            ];

            if (!empty($item['tags'])) {
                foreach ($item['tags']->toArray() as $tag) {
                    $tokensTags = $this->analyzeAutocompleteText($tag['name']);
                    foreach ($tokensTags as $tokenTag) {
                        $item['suggest_autocomplete'][] = [
                            'input'  => $tokenTag['token'],
                            'weight' => 1,
                        ];
                    }
                }
            }

            return [
                'id'                   => elasticsearchModelNaming($id ?: $item['id_faq'], 'faq'),
                'suggest_autocomplete' => $item['suggest_autocomplete'],
            ];
        }, $faqItems);
    }

    /**
     * Get User guides data.
     *
     * @param null|mixed $id
     */
    private function getUserGuidesItems($id = null): array
    {
        $params = [
            'columns' => [
                'id_menu', 'menu_title',
            ],
        ];

        /** @var User_Guides_Model $userGuidesModel */
        $userGuidesModel = model(\User_Guides_Model::class);

        if (null !== $id) {
            $userGuidesItems[] = $userGuidesModel->findOne($id);
        } else {
            $userGuidesItems = $userGuidesModel->findAll($params);
        }

        return array_map(function ($item) use ($id) {
            $tokens = $this->analyzeAutocompleteText($item['menu_title']);
            foreach ($tokens as $token) {
                $item['suggest_autocomplete'][] = [
                    'input'  => $token['token'],
                    'weight' => 1,
                ];
            }

            $item['suggest_autocomplete'][] = [
                'input'  => $item['menu_title'],
                'weight' => 2,
            ];

            return [
                'id'                   => elasticsearchModelNaming($id ?: $item['id_menu'], 'user-guides'),
                'suggest_autocomplete' => $item['suggest_autocomplete'],
            ];
        }, $userGuidesItems);
    }

    /**
     * Get Topics data.
     *
     * @param null|mixed $id
     */
    private function getTopicsItems($id = null)
    {
        $params = [
            'columns' => [
                'id_topic', 'title_topic',
            ],
            'conditions' => [
                'visible_topic' => 1,
            ],
        ];

        /** @var Popular_Topics_Model $topicsModel */
        $topicsModel = model(\Popular_Topics_Model::class);

        if (null !== $id) {
            $topicsItems[] = $topicsModel->findOne($id);
        } else {
            $topicsItems = $topicsModel->findAll($params);
        }

        return array_map(function ($item) use ($id) {
            $tokens = $this->analyzeAutocompleteText($item['title_topic']);
            foreach ($tokens as $token) {
                $item['suggest_autocomplete'][] = [
                    'input'  => $token['token'],
                    'weight' => 2,
                ];
            }

            $item['suggest_autocomplete'][] = [
                'input'  => $item['title_topic'],
                'weight' => 3,
            ];

            return [
                'id'                   => elasticsearchModelNaming($id ?: $item['id_topic'], 'topics'),
                'suggest_autocomplete' => $item['suggest_autocomplete'],
            ];
        }, $topicsItems);
    }

    /**
     * Get Community help data.
     *
     * @param null|mixed $id
     */
    private function getCommunityHelpItems($id = null): array
    {
        $params = [
            'columns' => [
                'id_question', 'title_question',
            ],
            'with' => [
                'category',
            ],
        ];

        /** @var Community_Questions_Model $questionsModel */
        $questionsModel = model(\Community_Questions_Model::class);

        if (null !== $id) {
            $questionsItems[] = $questionsModel->findOne($id);
        } else {
            $questionsItems = $questionsModel->findAll($params);
        }

        return array_map(function ($item) use ($id) {
            $tokens = $this->analyzeAutocompleteText($item['title_question']);
            foreach ($tokens as $token) {
                $item['suggest_autocomplete'][] = [
                    'input'  => $token['token'],
                    'weight' => 2,
                ];
            }

            $item['suggest_autocomplete'][] = [
                'input'  => $item['title_question'],
                'weight' => 3,
            ];

            if (null !== ($item['category'] ?? null)) {
                $tokensCat = $this->analyzeAutocompleteText($item['category']['title_cat']);

                foreach ($tokensCat as $token) {
                    $item['suggest_autocomplete'][] = [
                        'input'  => $token['token'],
                        'weight' => 2,
                    ];
                }

                $item['suggest_autocomplete'][] = [
                    'input'  => $item['category']['title_cat'],
                    'weight' => 3,
                ];
            }

            return [
                'id'                   => elasticsearchModelNaming($id ?: $item['id_question'], 'community-help'),
                'suggest_autocomplete' => $item['suggest_autocomplete'],
            ];
        }, $questionsItems);
    }
}
