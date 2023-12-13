<?php

declare(strict_types=1);

use App\Common\Database\Model;

/**
 * Elasticsearch_Countries model
 */
final class Elasticsearch_Countries_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "port_country";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "PORT_COUNTRY";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * {@inheritdoc}
     */
    private $type = 'countries';

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
     * The method created for indexing countries data in elasticsearch
     *
     * @param int|null $countryId
     *
     * @return void
     */
    public function index(?int $countryId = null): void
    {
        $countries = $this->getCountriesFromMySql(['id' => $countryId]);

        if (!empty($countries)) {
            $this->elasticsearchLibrary->indexBulk($this->type, $countries);
        }
    }

    /**
     * The method created to update a country in ElasticSearch by the country id
     *
     * @param int $countryId
     * @param array $country
     *
     * @return bool
     */
    public function updateCountryById(int $countryId, array $country = []): bool
    {
        $country = $country ?: array_shift($this->getCountriesFromMySql(['id' => $countryId]));

        if (empty($country)) {
            return false;
        }

        $curlResult = $this->elasticsearchLibrary->update($this->type, $countryId, $country);

        return 'updated' === $curlResult['result'];
    }

    /**
     * Get countries from MySQL
     *
     * @param null|int $countryId
     * @return array
     */
    private function getCountriesFromMySql(array $params = []): array
    {
        $mappingCountries = [];

        /** @var Countries_Model $portCountryModel */
        $portCountryModel = model(Countries_Model::class);
        $countryTable = $portCountryModel->getTable();

        $countries = $portCountryModel->findAllBy([
            'conditions' => array_filter([
                'id' => $params['id'] ?: null
            ]),
            'columns' => [
                "`{$countryTable}`.`id`",
                "`{$countryTable}`.`country`",
                "`{$countryTable}`.`country_ascii_name`",
                "`{$countryTable}`.`country_alias`",
                "`{$countryTable}`.`abr`",
                "`{$countryTable}`.`abr3`",
                "`{$countryTable}`.`country_latitude`",
                "`{$countryTable}`.`country_longitude`",
                "`{$countryTable}`.`id_continent`",
                "`{$countryTable}`.`is_focus_country`",
                "`{$countryTable}`.`position_on_select`",
            ],
            'with' => [
                'continent',
                'countryCode',
            ],
        ]);

        foreach ($countries as $country) {
            $portCountryCodes = [];
            if (null !== $country['country_code']) {
                foreach ($country['country_code']->toArray() as $countryCode) {
                    $portCountryCodes[] = [
                        'id'                                => (int) $countryCode['id_code'],
                        'ccode'                             => $countryCode['ccode'],
                        'phone_pattern_general'             => $countryCode['phone_pattern_general'],
                        'phone_pattern_international_mask'  => $countryCode['phone_pattern_international_mask'],
                    ];
                }
            }

            $mappingCountries[] = [
                'id'                    => (int) $country['id'],
                'name'                  => $country['country'],
                'alias'                 => $country['country_alias'],
                'ascii_name'            => $country['country_ascii_name'],
                'abr'                   => $country['abr'],
                'abr3'                  => $country['abr3'],
                'phone_code'            => $portCountryCodes,
                'location'              => [
                    'lat'   => (float) $country['country_latitude'],
                    'lon'   => (float) $country['country_longitude'],
                ],
                'continent'             => [
                    'id'    => (int) $country['continent']['id_continent'],
                    'name'  => $country['continent']['name_continent'],
                ],
                'is_focus_country'      => (int) $country['is_focus_country'],
                'position_on_select'    => (int) $country['position_on_select'],
            ];
        }

        return $mappingCountries;
    }
}

/* End of file elasticsearch_countries_model.php */
/* Location: /tinymvc/myapp/models/elasticsearch_countries_model.php */
