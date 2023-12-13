<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Database\Concerns;

/**
 * country_model.php
 *
 * country model
 *
 * @author Litra Andrei
 *
 * @see \Continents_Model
 * @see \Countries_Model
 * @see \Phone_Codes_Model
 * @see \States_Model
 * @see \Cities_Model
 *
 * @deprecated in favor of \Countries_Model
 */

class Country_Model extends BaseModel
{
    use Concerns\CanSearch;

    // hold the current controller instance
	private $port_countries = 'port_country';
	private $port_countries_primary_key = 'id';
	private $port_country_codes = 'port_country_codes';
	private $zips_table = 'zips';
	private $zips_table_primary_key = 'id';
	private $states_table = 'states';
    private $states_table_primary_key = 'id';
    private $continents_table = 'continents';
    private $continents_table_primary_key = 'id_continent';

    /**
     * Returns the countries table name.
     *
     * @return string
     */
    public function get_countries_table(): string
    {
        return $this->port_countries;
    }

    /**
     * Returns the countries table primary key.
     *
     * @return string
     */
    public function get_countries_table_primary_key(): string
    {
        return $this->port_countries_primary_key;
    }

    /**
     * Returns the regions table name.
     *
     * @return string
     */
    public function get_regions_table(): string
    {
        return $this->states_table;
    }

    /**
     * Returns the regions table primary key.
     *
     * @return string
     */
    public function get_regions_table_primary_key(): string
    {
        return $this->states_table_primary_key;
    }

    /**
     * Returns the cities table name.
     *
     * @return string
     */
    public function get_cities_table(): string
    {
        return $this->zips_table;
    }

    /**
     * Returns the cities table primary key.
     *
     * @return string
     */
    public function get_cities_table_primary_key(): string
    {
        return $this->zips_table_primary_key;
    }

    public function get_precise_location(int $country_id, int $region_id, int $city_id):? array
    {
        $this->db->select(
			<<<COLUMNS
			`COUNTRIES`.`id` as `id_country`, `STATES`.`id` as `id_state`, `CITIES`.`id` as `id_city`,
			`COUNTRIES`.`country`, `STATES`.`state` as `state`, `CITIES`.`city`
			COLUMNS
		);
		$this->db->from('`zips` AS `CITIES`');
		$this->db->join('`states` AS `STATES`', '`CITIES`.`state` = `STATES`.`id`', 'left');
		$this->db->join('`port_country` AS `COUNTRIES`', '`CITIES`.`id_country` = `COUNTRIES`.`id`', 'left');
		$this->db->where('`COUNTRIES`.`id` = ?', $country_id);
		$this->db->where('`STATES`.`id` = ?', $region_id);
        $this->db->where('`CITIES`.`id` = ?', $city_id);

        $record = $this->db->query_one();
        if (empty($record) || !is_array($record)) {
            return null;
        }

        return $record;
    }

    function get_location($id) {
        $sql = "SELECT zips.*, states.state as state_name
	            FROM zips, states
	            WHERE zips.state = states.id
	            AND zips.id = ?";

        return $this->db->query_one($sql, array($id));
    }

    /**
     * @param array|null $conditions
     * @param array|null $conditions['columns']
     *
     * @return array|null
     */
    function get_country_state_city($city, ?array $conditions = null) {
        $columns = $conditions['columns'] ?? [
            "`{$this->get_countries_table()}`.`country`",
            "`{$this->get_regions_table()}`.`state_name` state",
            "`{$this->get_cities_table()}`.`city`",
        ];

        return $this->findRecord(
            null,
            $this->zips_table,
            null,
            $this->zips_table_primary_key,
            (int) $city,
            [
                'columns' => $columns,
                'joins' => [
                    'state',
                    'country',
                ],
            ]
        );
    }

    function get_countries_states_cities($conditions = array()) {
        $where = array();
        $params = array();

        extract($conditions);

        if(isset($lat_lng_need_complet)){
            $where[] = " z.lat_lng_need_complet = ? ";
            $params[] = $lat_lng_need_complet;
        }

        $sql = "SELECT z.id, z.city, pc.country_name, s.state_name
                FROM {$this->zips_table} z
                INNER JOIN {$this->port_countries} pc on z.id_country = pc.id
                INNER JOIN {$this->states_table} s on z.state = s.id";

        if(!empty($where)){
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        if(isset($limit)){
            $sql .= " LIMIT {$limit} ";
        }

        return $this->db->query_all($sql, $params);
    }

    function get_country_city($country, $city, $state = false) {
        $sql = "SELECT * FROM port_country WHERE id = ?";

        $ctr_data = $this->db->query_one($sql, array($country));

		$ret = array(
			'country' 	=> '',
			'city' 	=> '',
			'state' => ''
		);

		if (empty($ctr_data)) {
            return $ret;
        }

		$ret['country'] = $ctr_data['country'];
        $ret['city'] = $this->get_city($city);
        $ret['city'] = $ret['city']['city'];

		if ($state) {
            $state_info = $this->get_state($state,'state_name');
            if($state_info['state_name'] != $ret['city']){
                $ret['state'] = $state_info['state_name'];
            }
		}

        return $ret;
    }

    function get_state_city($city) {
        $sql = "SELECT zips.*,states.state as state
				FROM zips, states
				WHERE zips.state = states.id AND zips.id = ?";

        $city = $this->db->query_one($sql, array($city));

        return empty($city) ? '' : $city['state'] . ", " . $city['city'];
    }

    public function find_regions(string $search_query, ?int $country_id, ?int $limit, ?int $skip): array
    {
        $this->db->select('*');
        $this->db->from($this->get_regions_table());
        if (null !== $country_id) {
            $this->db->where('id_country = ?', $country_id);
        }

        $match_search = null;
        $search_tokens = $this->tokenizeSearchText($search_query);
        if ($uses_matching = !empty($search_tokens)) {
            $match_search = <<<MATCH
            OR MATCH (`state`, `state_name`, `state_code`) AGAINST (? IN BOOLEAN MODE)
            MATCH;
        }

        $this->db->where_raw(
            <<<CONDITION
            (
                state = ?
                OR state_code = ?
                OR state LIKE ?
                OR state_code LIKE ?
                {$match_search}
            )
            CONDITION,
            array_merge(
                array_fill(0, 2, $search_query),
                array_fill(0, 2, "{$search_query}%"),
                !$uses_matching ? array() : array($this->getConnection()->quote(implode(' ', $search_tokens)))
            )
        );

        if (null !== $limit) {
            $this->db->limit($limit, $skip);
        }

        $records = $this->db->query_all();
        if (empty($records)) {
            return array();
        }

        return $records;
    }

    public function cound_found_regions(string $search_query, ?int $country_id): int
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->get_regions_table());
        if (null !== $country_id) {
            $this->db->where('id_country = ?', $country_id);
        }

        $match_search = null;
        $search_tokens = $this->tokenizeSearchText($search_query);
        if ($uses_matching = !empty($search_tokens)) {
            $match_search = <<<MATCH
            OR MATCH (`state`, `state_name`, `state_code`) AGAINST (? IN BOOLEAN MODE)
            MATCH;
        }

        $this->db->where_raw(
            <<<CONDITION
            (
                state = ?
                OR state_code = ?
                OR state LIKE ?
                OR state_code LIKE ?
                {$match_search}
            )
            CONDITION,
            array_merge(
                array_fill(0, 2, $search_query),
                array_fill(0, 2, "{$search_query}%"),
                !$uses_matching ? array() : array($this->getHandler()->getConnection()->quote(implode(' ', $search_tokens)))
            )
        );

        $counter = $this->db->query_one();
        if (empty($counter)) {
            return 0;
        }

        return (int) ($counter['AGGREGATE'] ?? 0);
    }

    function get_continents() {
        return $this->db->query_all("SELECT * FROM continents ORDER BY name_continent ASC");
    }

    public function get_continent($continent_id){
        $this->db->from($this->continents_table);
        $this->db->where('id_continent', $continent_id);

        return $this->db->get_one();
    }

	function get_state_cities($cities) {
        $cities = getArrayFromString($cities);

        $sql = "SELECT z.id, CONCAT( z.city,', ', s.state) as location
				FROM zips z, states s
				WHERE z.state = s.id AND z.id IN (" . implode(',', array_fill(0, count($cities), '?')) . ")";

        $cities = $this->db->query_all($sql, $cities);

        return empty($cities) ? [] : array_column($cities, 'location', 'id');
    }

    function get_simple_city($city) {
        $city = $this->db->query_one("SELECT * FROM zips WHERE id = ?", [$city]);

        return empty($city) ? '' : $city['city'];
    }

    function get_simple_countries($list_countries) {
        if (empty($list_countries)) {
            return [];
        }

        $list_countries = getArrayFromString($list_countries);

        $sql = "SELECT * FROM port_country WHERE id IN (" . implode(',', array_fill(0, count($list_countries), '?')) . ") ORDER BY country_name ASC";
        $countries = $this->db->query_all($sql, $list_countries);

        return empty($countries) ? [] : array_column($countries, null, 'id');
    }

    function get_simple_states($list_states) {
        $list_states = getArrayFromString($list_states);

        $sql = "SELECT *
                FROM states
                WHERE id IN (" . implode(',', array_fill(0, count($list_states), '?')) . ")";
        $states = $this->db->query_all($sql, $list_states);

        return empty($states) ? [] : array_column($states, null, 'id');
    }

    function get_simple_cities($cities) {
        $cities = getArrayFromString($cities);

        $sql = "SELECT *
                FROM zips
                WHERE id IN (" . implode(',', array_fill(0, count($cities), '?')) . ")";

        $cities = $this->db->query_all($sql, $cities);

        return empty($cities) ? [] : array_column($cities, null, 'id');
    }

    function get_simple_cities_by_state($list_cities) {
        $list_cities = getArrayFromString($list_cities);

        $sql = "SELECT * FROM zips
        		WHERE id IN (". implode(',', array_fill(0, count($list_cities), '?')) . ")";

        $cities =  $this->db->query_all($sql, $list_cities);

        return empty($cities) ? null : array_column($cities, 'city', 'id');
    }
    function get_cities_state($list_cities) {
        $list_cities = getArrayFromString($list_cities);

        $sql = "SELECT z.*, s.state as state_name
				FROM zips z
				LEFT JOIN states s ON z.state = s.id
                WHERE z.id IN (" . implode(',', array_fill(0, count($list_cities), '?')) . ")";

        $cities = $this->db->query_all($sql, $list_cities);

        if (empty($cities)) {
            return null;
        }

        $processedCities = [];
		foreach ($cities as $city) {
			$processedCities[$city['id']] = $city['city'] . ', ' . $city['state_name'];
		}

        return $processedCities;
    }

	function get_country($id){
		$sql = "SELECT * FROM {$this->port_countries} c WHERE id = ?";
        return $this->db->query_one($sql,  array($id));
    }

    function getAllCountries():array
    {
        return array_column($this->findRecords(null, $this->get_countries_table(), null, ['order' => ['country' => 'asc']]), null, 'id');
    }

    public function has_country($country_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->port_countries);
        $this->db->where('id = ?', (int) $country_id);
        $counters = $this->db->query_one();
        if(empty($counters)) {
            return false;
        }

        return (int) arrayGet($counters, 'AGGREGATE', 0);
    }

	function get_country_by_alias($alias = ''){
		$sql = "SELECT * FROM {$this->port_countries} WHERE country_alias = ?";
        return $this->db->query_one($sql,  array($alias));
	}

	function get_countries($params = array()) {
        $columns = null;
        $joins = null;
        $skip = null;
        $limit = null;
        $order = array('country' => 'ASC');
        $conditions = array();

        extract($params);

        if (null !== $columns) {
            $this->db->select($columns);
        }

        $this->db->from($this->port_countries);
        if (null !== $joins) {
            foreach ($joins as $join) {
                if ('continent' === $join) {
                    $this->db->join($this->continents_table, "{$this->port_countries}.`id_continent` = {$this->continents_table}.`id_continent`", 'left');
                }
            }
        }

        if (isset($conditions['position'])) {
            $this->db->where("{$this->port_countries}.`position_on_select`", $conditions['position']);
        }

        if (isset($conditions['special_position'])) {
            $this->db->where_raw("{$this->port_countries}.`position_on_select` IS" . (empty($conditions['special_position']) ? ' ' : ' NOT ') . "NULL");
        }

        if (isset($conditions['is_focus_country'])) {
            $this->db->where("{$this->port_countries}.`is_focus_country`", $conditions['is_focus_country']);
        }

        if (isset($conditions['id_continent'])) {
            $this->db->where("{$this->port_countries}.`id_continent`", $conditions['id_continent']);
        }

        if (isset($conditions['ccode'])) {
            $this->db->where("{$this->port_countries}.`ccode`", $conditions['ccode']);
        }

        if (isset($conditions['country'])) {
            $this->db->where_raw("{$this->port_countries}.`country` LIKE ?", $conditions['country'] . '%');
        }

        //region OrderBy
        $ordering = array();
        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $direction = mb_strtoupper($direction);
                $ordering[] = "{$column} {$direction}";
            } else {
                $ordering[] = $column;
            }
        }

        if (!empty($ordering)) {
            $this->db->orderby(implode(', ', $ordering));
        }
        //endregion OrderBy

        //region Limits
        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }
        //endregion Limits

        return $this->db->get();
    }

    public function get_count_countries(array $params = array()){
        $conditions = array();

        extract($params);

        if (isset($conditions['position'])) {
            $this->db->where("{$this->port_countries}.`position_on_select`", $conditions['position']);
        }

        if (isset($conditions['special_position'])) {
            $this->db->where_raw("{$this->port_countries}.`position_on_select` IS" . (empty($conditions['special_position']) ? ' ' : ' NOT ') . "NULL");
        }

        if (isset($conditions['is_focus_country'])) {
            $this->db->where('is_focus_country', $conditions['is_focus_country']);
        }

        if (isset($conditions['id_continent'])) {
            $this->db->where('id_continent', $conditions['id_continent']);
        }

        if (isset($conditions['ccode'])) {
            $this->db->where("{$this->port_countries}.`ccode`", $conditions['ccode']);
        }

        if (isset($conditions['country'])) {
            $this->db->where_raw("{$this->port_countries}.`country` LIKE ?", $conditions['country'] . '%');
        }

        $this->db->select('COUNT(*) as count_countries');
        $this->db->from($this->port_countries);
        $result = $this->db->get_one();

        return empty($result['count_countries']) ? 0 : (int) $result['count_countries'];
    }

	function get_ccodes(){
		$sql = "SELECT pcc.*, pc.country, pc.abr
				FROM $this->port_country_codes pcc
                LEFT JOIN $this->port_countries pc ON pcc.id_country = pc.id
                ORDER BY pc.country ASC";

        return $this->db->query_all($sql);
    }

    function update_ccode($id_code, $data){
        $this->db->where('id_code', $id_code);
        $this->db->update($this->port_country_codes, $data);
    }

    function update_ccode_by_country_id($country_id, $data){
        $this->db->where('id_country', $country_id);
        return $this->db->update($this->port_country_codes, $data);
    }

    public function get_extended_country_codes_list()
    {
        $this->db->select(
            "`CODES`.*, " .
            "`COUNTRIES`.`country` as `country_name`, `COUNTRIES`.`abr` AS `country_iso3166_alpha2`, `COUNTRIES`.`abr3` AS `country_iso3166_alpha3`, " .
            "`COUNTRIES`.`country_latitude`, `COUNTRIES`.`country_longitude`"
        );
        $this->db->from("`{$this->port_country_codes}` AS `CODES`");
        $this->db->join("`{$this->port_countries}` AS `COUNTRIES`", '`CODES`.`id_country` = `COUNTRIES`.`id`', 'left');
        $this->db->orderby("COUNTRIES.country");

        return array_filter((array) $this->db->query_all());
    }

    /**
     * Returns the list of phone country codes by the list od IDs.
     *
     * @param array $codeIds
     * @return Array<int,mixed[]> the list of id - code pair.
     */
    public function getExtendedCountryCodes(array $codeIds): array
    {
        if (empty($codeIds)) {
            return [];
        }

        $this->db->select(
            "`CODES`.*, " .
            "`COUNTRIES`.`country` as `country_name`, `COUNTRIES`.`abr` AS `country_iso3166_alpha2`, `COUNTRIES`.`abr3` AS `country_iso3166_alpha3`, " .
            "`COUNTRIES`.`country_latitude`, `COUNTRIES`.`country_longitude`"
        );
        $this->db->from("`{$this->port_country_codes}` AS `CODES`");
        $this->db->join("`{$this->port_countries}` AS `COUNTRIES`", '`CODES`.`id_country` = `COUNTRIES`.`id`', 'left');
        $this->db->where_raw(
            sprintf('id_code IN (%s)', implode(', ', array_fill(0, count($codeIds), '?'))),
            array_map(fn ($id) => (int) $id, $codeIds)
        );

        return arrayByKey(array_filter((array) $this->db->query_all()), 'id_code');
    }

    public function has_country_code($code)
    {
        $this->db->select("COUNT(*) AS AGGREGATE");
        $this->db->from("{$this->port_country_codes} AS CODES");
        $this->db->where('CODES.id_code = ?', (int) $code);
        $this->db->or_where('CODES.ccode = ?', $code); // Legacy condition

        $counter = $this->db->query_one();
        if(empty($code)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE']: false;
    }

    public function get_country_code($code_id)
    {
        $this->db->select("CODES.*, COUNTRIES.country_name as country, COUNTRIES.abr as iso");
        $this->db->from("{$this->port_country_codes} AS CODES");
        $this->db->join("{$this->port_countries} AS COUNTRIES", "CODES.id_country = COUNTRIES.id");
        $this->db->where('id_code = ?', (int) $code_id);

        $code = $this->db->query_one();

        return $code ?: null;
    }

    function update_country($id_country, $data){
        $this->db->where('id', $id_country);
        return $this->db->update($this->port_countries, $data);
    }

    function update_city($id_city = 0, $data = array()){
        if(empty($data)){
            return false;
        }

        $this->db->where('id', $id_city);
        $this->db->update($this->zips_table, $data);
    }

    function update_country_translation($id_country, $lang, $translation) {
        $query =  "UPDATE {$this->port_countries} SET translations_data = JSON_SET(COALESCE(translations_data, '{}'), ?, ?) WHERE id = ? LIMIT 1";

        return $this->db->query($query, array('$.' . $lang, $translation, $id_country));
    }

    function update_country_code($id_code, $data){
        $this->db->where('id_code', $id_code);
        $this->db->update($this->port_country_codes, $data);
    }

	//new
	function fetch_port_country($id = 0, $ids = array(), $order_by_ids = false)
    {
        $this->db->select("*");
        $this->db->from($this->port_countries);

        if (!empty($id)) {
            $this->db->where("id = ?", (int) $id);
        }
        if (!empty($ids)) {
            $this->db->in("id", $ids);
        }
        if ($order_by_ids) {
            $this->db->orderby('FIELD(id, ' . implode(',', array_fill(0, count($ids), '?')) . ')');
        } else {
            $this->db->orderby("country ASC");
        }

        $records = $this->db->query_all(null, $order_by_ids ? $ids : null);

        return empty($records) ? [] : $records;
    }

	function get_city($id, $fields = '*'){
		$sql = "SELECT $fields
				FROM $this->zips_table
				WHERE id = ?";
        return $this->db->query_one($sql,  array($id));
    }

    public function has_city($cityId, ?int $stateId = null, ?int $countryId = null)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->zips_table);
        $this->db->where('id = ?', (int) $cityId);

        if (null !== $stateId) {
            $this->db->where('state = ?', $stateId);
        }

        if (null !== $countryId) {
            $this->db->where('id_country = ?', $countryId);
        }

        $counters = $this->db->query_one();
        if(empty($counters)) {
            return false;
        }

        return (int) arrayGet($counters, 'AGGREGATE', 0);
    }

    function get_states($country){
        $sql = "SELECT * FROM states WHERE id_country = ? ORDER BY state";
        return $this->db->query_all($sql, $country);
    }

	function get_state($id,$fields = '*'){
        $sql = "SELECT $fields
                FROM states
                WHERE id = ?";
        return $this->db->query_one($sql, array($id));
    }

    public function has_state($stateId, ?int $countryId = null)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->states_table);
        $this->db->where('id = ?', (int) $stateId);

        if (null !== $countryId) {
            $this->db->where('id_country = ?', $countryId);
        }

        $counters = $this->db->query_one();
        if(empty($counters)) {
            return false;
        }

        return (int) arrayGet($counters, 'AGGREGATE', 0);
    }

	function get_cities_by_list($cities_list){
        $cities_list = getArrayFromString($cities_list);

        $this->db->select('id, city');
        $this->db->in('id', $cities_list);

		$cities = $this->db->get($this->zips_table);

        return empty($cities) ? null : array_column($cities, 'city', 'id');
	}

    /**
     * Scope for join with states
     */
    protected function bindState(QueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                $this->get_cities_table(),
                $this->get_regions_table(),
                $this->get_regions_table(),
                "`{$this->get_regions_table()}`.`{$this->get_regions_table_primary_key()}` = `{$this->get_cities_table()}`.`state`"
            );
    }

    /**
     * Scope for join with countries
     */
    protected function bindCountry(QueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                $this->get_cities_table(),
                $this->get_countries_table(),
                $this->get_countries_table(),
                "`{$this->get_countries_table()}`.`{$this->get_countries_table_primary_key()}` = `{$this->get_cities_table()}`.`id_country`"
            );
    }
}
