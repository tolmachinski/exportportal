<?php

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [01.12.2021]
 * library refactoring code style
 *
 * @see https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/h.-ElasticSearch
 */
class TinyMVC_Library_Elasticsearch
{
    public $type = '';
    private $index = 'ep';
    private $elasticIndexUrl;

    /**
     * @param ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->ip_port = $container->getParameter('kernel.env.ELASTIC_SEARCH_API_HOST', 'http://localhost:9200');
        $this->index = $container->getParameter('kernel.env.ELASTIC_SEARCH_INDEX', 'ep');
    }

    public function elasticCommand($HTTP_METHOD, $url, $body = [], $plaintext = false)
    {
        $this->elasticIndexUrl = "{$this->ip_port}/{$this->index}_{$this->type}/";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->elasticIndexUrl . $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $HTTP_METHOD);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($body)) {
            if ($plaintext) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($body), ]
                );
            } else {
                $bodyJson = json_encode($body);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($bodyJson), ]
                );
            }
        }

        $curl_result = curl_exec($ch);
        curl_close($ch);

        return json_decode($curl_result, true);
    }

    public function get($type, $query)
    {
        $this->type = $type;

        return $this->elasticCommand('POST', '_search', $query);
    }

    public function count($type, $query)
    {
        $this->type = $type;
        if (!empty($query)) {
            return $this->elasticCommand('GET', '_count', json_encode(array_shift($query), JSON_FORCE_OBJECT), true);
        }

        return $this->elasticCommand('POST', '_count');
    }

    public function get_by_id($type, $id)
    {
        $this->type = $type;

        return $this->elasticCommand('GET', "_doc/{$id}");
    }

    public function mget($type, $queries)
    {
        $this->type = $type;

        if (empty($queries)) {
            return 'bad request';
        }

        $_mqueries = '';
        foreach ($queries as $query) {
            $_mqueries .= '{}' . PHP_EOL . json_encode($query) . PHP_EOL;
        }

        return $this->elasticCommand('POST', '_msearch', $_mqueries, true);
    }

    public function index($type, $id, $body)
    {
        $this->type = $type;

        return $this->elasticCommand('PUT', "_doc/{$id}", $body);
    }

    public function indexBulk(string $type, array $records, string $primaryKeyName = 'id')
    {
        $this->type = $type;
        $bulkQueries = [];

        foreach ($records as $record) {
            $bulkQueries[] = json_encode(
                [
                    'index' => [
                        '_id'    => $record[$primaryKeyName],
                        '_index' => "{$this->index}_{$this->type}",
                    ],
                ]
            );

            $bulkQueries[] = json_encode($record);
        }

        return $this->elasticCommand('POST', '_bulk', implode(PHP_EOL, $bulkQueries) . PHP_EOL, true);
    }

    public function update($type, $id, $body, array $requestOptions = []) {
        $this->type = $type;

        return $this->elasticCommand("POST", "_update/{$id}", array_merge(['doc' => $body], $requestOptions));
    }

    public function update_by_query($query, $script, $type = null, $params = [])
    {
        $this->type = $type;

        $body = [
            'query'  => $query,
            'script' => [
                'source'    => $script,
                'lang'      => 'painless',
            ],
        ];

        if (!empty($params)) {
            $body['script']['params'] = $params;
        }

        return $this->elasticCommand('POST', '_update_by_query', $body);
    }

    public function update_by_script($type, $id, $script)
    {
        $this->type = $type;

        $this->elasticCommand('POST', "_update/{$id}", ['script' => $script]);
    }

    public function get_match($field, $value, $operator = 'and', $analyzer = 'default')
    {
        return [
            'match' => [
                $field => [
                    'query'    => $value,
                    'operator' => $operator,
                    'analyzer' => $analyzer,
                ],
            ],
        ];
    }

    public function get_range(string $field, array $params)
    {
        return [
            'range' => [
                $field => array_filter([
                    'time_zone' => $params['timeZone'] ?? null,
                    'relation'  => $params['relation'] ?? null,
                    'format'    => $params['dateFormat'] ?? null,
                    'boost'     => $params['boost'] ?? null,
                    'gte'       => $params['from'] ?? null,
                    'lte'       => $params['to'] ?? null,
                    'gt'        => $params['strictFrom'] ?? null,
                    'lt'        => $params['strictTo'] ?? null,
                ]),
            ],
        ];
    }

    public function get_term($field, $value)
    {
        return [
            'term' => [
                $field => $value,
            ],
        ];
    }

    public function get_terms($field, $values)
    {
        return [
            'terms' => [
                $field => $values,
            ],
        ];
    }

    public function get_nested($path, $query)
    {
        return [
            'nested' => [
                'path'  => $path,
                'query' => $query,
            ],
        ];
    }

    public function get_multi_term($terms = [])
    {
        $filter_terms = [];
        foreach ($terms as $field => $value) {
            $filter_terms[] = [
                'term' => [
                    $field => $value,
                ],
            ];
        }

        return [
            'bool' => [
                'must' => $filter_terms,
            ],
        ];
    }

    public function get_multi_match($fields, $query, $type = 'best_fields', $operator = 'or')
    {
        return [
            'multi_match' => [
                'fields'                => $fields,
                'query'                 => $query,
                'type'                  => $type,
                'operator'              => $operator,
            ],
        ];
    }

    //$ids = 1,2,3
    public function delete($type, $ids)
    {
        $this->type = $type;

        if (empty($ids)) {
            return;
        }

        $ids = is_array($ids) ? $ids : explode(',', $ids);

        $queries = [];
        foreach ($ids as $id) {
            $queries[] = ['delete' => ['_index' => $this->index . '_' . $type, '_id' => $id]];
        }

        return $this->bulk($queries);
    }

    public function deleteById(string $type, int $id): ?array
    {
        $this->type = $type;

        return $this->elasticCommand('DELETE', "_doc/{$id}");
    }

    public function delete_by_query($type, $query)
    {
        $this->type = $type;

        $body = [
            'query' => $query,
        ];

        return $this->elasticCommand('POST', '_delete_by_query', $body);
    }

    public function bulk($queries)
    {
        $_bulk = '';
        foreach ($queries as $query) {
            $_bulk .= json_encode($query) . PHP_EOL;
        }

        return $this->elasticCommand('POST', '_bulk', $_bulk, true);
    }

    public function bulk_index_query($type, $id, $data): array
    {
        $this->type = $type;

        return [
            [
                'index' => [
                    '_id'    => $id,
                    '_index' => "{$this->index}_{$this->type}",
                ],
            ],
            $data,
        ];
    }

    /**
     * Method for calling analyze API endpoint in ElasticSearch
     *
     * @param string $type - index name
     * @param array $body - the body of the query
     *
     * @return array
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.15/indices-analyze.html
     */
    public function analyze(string $type, array $body):array
    {
        $this->type = $type;

        return $this->elasticCommand('POST', "_analyze", $body);
    }
}
