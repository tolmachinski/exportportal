<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Elasticsearch_States model
 */
final class Elasticsearch_States_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "states";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "STATES";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * {@inheritdoc}
     */
    private $type = 'states';

    /**
     * {@inheritdoc}
     */
    public $records = null;

    /**
     * {@inheritdoc}
     */
    public $recordsCount = null;

    /**
     * {@inheritdoc}
     */
    public $aggregates = null;

    /**
     * @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary
     */
    protected $elasticsearchLibrary;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    /**
     * The method created for indexing states data in elasticsearch
     *
     * @param int|null $stateId
     *
     * @return void
     */
    public function index(?int $stateId = null): void
    {
        if (!empty($states = $this->getStatesFromMySql(['id' => $stateId]))) {
            $this->elasticsearchLibrary->indexBulk($this->type, $states);
        }
    }

    /**
     * @param int $countryId
     * @return bool
     */
    public function updateCountryByCountryId(int $countryId): bool
    {
        /** @var Countries_Model $portCountryModel */
        $portCountryModel = model(Countries_Model::class);

        $country = $portCountryModel->findOne(
            $countryId,
            [
                'columns'   => [
                    'id',
                    'country AS name',
                    'country_ascii_name AS ascii_name',
                    'country_alias AS alias',
                    'abr',
                    'abr3',
                ],
            ],
        );

        if (empty($country)) {
            return false;
        }

        $res = $this->elasticsearchLibrary->update_by_query(
            $this->elasticsearchLibrary->get_term('country.id', $countryId),
            'ctx._source.country = params.country',
            $this->type,
            [
                'country' => $country
            ]
        );

        return empty($res['failures']);
    }

    /**
     * Get states from MySQL
     *
     * @param array $params
     * @return array
     */
    private function getStatesFromMySql(array $params = []): array
    {
        $mappingStates = [];

        /** @var States_Model $statesModel */
        $statesModel = model(States_Model::class);

        $states = $statesModel->findAllBy([
            'conditions' => array_filter([
                'id' => $params['id'] ?? null,
            ]),
            'with' => [
                'country'
            ],
        ]);

        foreach ($states as $state) {
            $mappingStates[] = [
                'id'            => (int) $state['id'],
                'name'          => $state['state_name'],
                'ascii_name'    => $state['state_ascii_name'],
                'code'          => $state['state_code'],
                'country'       => [
                    'id'            => (int) $state['id_country'],
                    'name'          => $state['country']['country'] ?: '',
                    'ascii_name'    => $state['country']['country_ascii_name'] ?: '',
                    'alias'         => $state['country']['country_alias'] ?: '',
                    'abr'           => $state['country']['abr'] ?: '',
                    'abr3'          => $state['country']['abr3'] ?: '',
                ],
            ];
        }

        return $mappingStates;
    }
}

/* End of file elasticsearch_states_model.php */
/* Location: /tinymvc/myapp/models/elasticsearch_states_model.php */
