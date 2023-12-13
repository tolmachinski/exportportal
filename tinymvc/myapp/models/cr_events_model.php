<?php

declare(strict_types=1);

use App\Common\Database\BaseModel;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\PortableModel;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * CR events model.
 */
class Cr_events_Model extends BaseModel
{
    use Concerns\ConvertsAttributes;

    /**
     * Base path to CR events assets.
     *
     * @var string
     */
    public $event_images_path = 'public/img/cr_event_images';

    /**
     * Name of the CR events table.
     *
     * @var string
     */
    private $events_table = 'cr_events';

    /**
     * Alias of the CR events table.
     *
     * @var string
     */
    private $events_table_alias = 'EVENTS';

    /**
     * Name of the CR users-events pivot table.
     *
     * @var string
     */
    private $cr_events_cr_users_table = 'cr_events_cr_users_assign';

    /**
     * Alias of the CR users-events pivot table.
     *
     * @var string
     */
    private $cr_events_cr_users_table_alias = 'EVENT_USER_PIVOT';

    /**
     * Name of the CR assigned users table.
     *
     * @var string
     */
    private $events_users_attend_table = 'cr_events_users_attend';

    /**
     * Alias of the CR assigned users table.
     *
     * @var string
     */
    private $events_users_attend_table_alias = 'EVENT_ATTENDANCES';

    /**
     * Name of the CR events types table.
     *
     * @var string
     */
    private $events_types_table = 'cr_events_types';

    /**
     * Alias of the CR events types table.
     *
     * @var string
     */
    private $events_types_table_alias = 'EVENT_TYPES';

    /**
     * Name of the countries table.
     *
     * @var string
     */
    private $port_country_table = 'port_country';

    /**
     * Alias of the countries table.
     *
     * @var string
     */
    private $port_country_table_alias = 'COUNTRIES';

    /**
     * Name of the cities table.
     *
     * @var string
     */
    private $cities_table = 'zips';

    /**
     * Alias of the cities table.
     *
     * @var string
     */
    private $cities_table_alias = 'CITIES';

    /**
     * Name of the states table table.
     *
     * @var string
     */
    private $states_table = 'states';

    /**
     * Alias of the states table table.
     *
     * @var string
     */
    private $states_table_alias = 'STATES';

    public function is_event_exists($event_id)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from("`{$this->events_table}` AS `{$this->events_table_alias}`");
        $this->db->where("`{$this->events_table_alias}`.`id_event` = ?", (int) $event_id);
        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    public function add_event(array $data)
    {
        return $this->db->insert($this->events_table, $data);
    }

    public function update_event($event_id, array $data)
    {
        $this->db->where('id_event', $event_id);

        return $this->db->update($this->events_table, $data);
    }

    public function remove_event($event_id)
    {
        $this->db->where('id_event = ?', (int) $event_id);

        return $this->db->delete($this->events_table);
    }

    public function get_types()
    {
        $this->db->select('*');
        $this->db->from($this->events_types_table);
        $this->db->orderby('`event_type_name` ASC');

        return $this->db->query_all();
    }

    public function get_event($event_id)
    {
        return $this->findRecord(
            'event',
            $this->events_table,
            $this->events_table_alias,
            'id_event',
            (int) $event_id,
            array(
                'joins'   => array('types', 'countries'),
                'columns' => array(
                    "`{$this->events_table_alias}`.*",
                    "`{$this->port_country_table_alias}`.`country`",
                    "`{$this->port_country_table_alias}`.`country_alias`",
                    "`{$this->events_types_table_alias}`.`event_type_name`",
                ),
            )
        );
    }

    /**
     * Find Event.
     *
     * @param int      $event
     * @param null|int $ambassador
     *
     * @throws \App\Common\Exceptions\NotFoundException  if event doesn't exists
     * @throws \App\Common\Exceptions\OwnershipException if ambassador doesn't own the event
     *
     * @return array
     */
    public function find_event($event, $ambassador = null)
    {
        if (
            empty($event) ||
            empty($eventData = $this->get_event($event))
        ) {
            throw new NotFoundException('The event with such ID is not found on this server');
        }
        if (null !== $ambassador) {
            if ((int) $ambassador !== (int) $eventData['event_id_user']) {
                throw new OwnershipException('The event does not belong to this ambassador');
            }
        }

        return $eventData;
    }

    public function get_events(array $params = array())
    {
        return $this->findRecords(
            'event',
            $this->events_table,
            $this->events_table_alias,
            $params
        );
    }

    public function count_events(array $params = array())
    {
        unset($params['order'], $params['with'], $params['limit'], $params['skip']);
        if (!isset($params['columns'])) {
            $params['columns'] = array('COUNT(*) AS AGGREGATE');
        }

        $is_multiple = isset($params['multiple']) ? (bool) $params['multiple'] : false;
        $counters = $this->get_events($params);
        if ($is_multiple) {
            return $counters;
        }

        return (int) arrayGet($counters, '0.AGGREGATE', 0);
    }

    public function get_user_events(array $params = array())
    {
        $order = array('event_date_start' => 'ASC', 'event_name' => 'ASC');
        $joins = array('types', 'countries', 'states', 'cities');
        $columns = array(
            "{$this->events_table_alias}.*",
            "{$this->port_country_table_alias}.country",
            "{$this->port_country_table_alias}.country_alias",
            "{$this->events_types_table_alias}.event_type_name",
            "{$this->cities_table_alias}.city",
            "IF(
                {$this->cities_table_alias}.city = {$this->states_table_alias}.state,
                CONCAT_WS(', ', {$this->port_country_table_alias}.country, {$this->cities_table_alias}.city),
                CONCAT_WS(', ', {$this->port_country_table_alias}.country, {$this->states_table_alias}.state, {$this->cities_table_alias}.city)
            ) as user_location",
        );

        return $this->get_events(array_merge($params, compact('joins', 'columns', 'order')));
    }

    public function get_events_dates(array $params = array())
    {
        unset($params['limit'], $params['skip'], $params['joins'], $params['with'], $params['order']);

        if (isset($params['conditions'])) {
            $params['conditions'] = array_intersect_key($params['conditions'], array_flip(array(
                'status',
                'country',
                'visible',
                'active',
                'expired',
                'active_today',
                'expired_today',
            )));
        }

        return $this->get_events(array_replace($params, array(
            'group'   => array('date'),
            'columns' => array(
                "DATE(`{$this->events_table_alias}`.`event_date_start`) AS `date`",
                "ANY_VALUE(`{$this->events_table_alias}`.`event_name`) AS `title`",
            ),
        )));
    }

    public function get_events_month_counters(array $params = array())
    {
        unset($params['limit'], $params['skip'], $params['joins'], $params['with'], $params['order']);

        if (isset($params['conditions'])) {
            $params['conditions'] = array_intersect_key($params['conditions'], array_flip(array(
                'type',
                'search',
                'country',
                'status',
                'visible',
                'active',
                'expired',
                'active_today',
                'expired_today',
            )));
        }

        return $this->get_events(array_replace($params, array(
            'group'   => array('date_value'),
            'columns' => array(
                'COUNT(*) AS counter',
                "DATE_FORMAT(`{$this->events_table_alias}`.`event_date_start`, '%m-%Y') as `date_value`",
            ),
        )));
    }

    public function get_events_type_counters(array $params = array())
    {
        unset($params['limit'], $params['skip'], $params['joins'], $params['with'], $params['order']);

        if (isset($params['conditions'])) {
            $params['conditions'] = array_intersect_key($params['conditions'], array_flip(array(
                'search',
                'search',
                'country',
                'status',
                'visible',
                'active',
                'expired',
                'started_at',
                'active_today',
                'expired_today',
                'started_at_date',
                'started_at_month',
            )));
        }

        return $this->get_events(array_replace($params, array(
            'group'   => array("`{$this->events_table_alias}`.`event_id_type`"),
            'columns' => array(
                'COUNT(*) AS counter',
                "`{$this->events_table_alias}`.`event_id_type`",
            ),
        )));
    }

    public function get_events_country_counters(array $params = array())
    {
        unset($params['limit'], $params['skip'], $params['joins'], $params['with'], $params['order']);

        if (isset($params['conditions'])) {
            $params['conditions'] = array_intersect_key($params['conditions'], array_flip(array(
                'status',
                'visible',
                'active',
                'expired',
                'active_today',
                'expired_today',
            )));
        }

        return $this->get_events(array_replace($params, array(
            'group'   => array("`{$this->events_table_alias}`.`event_id_country`"),
            'columns' => array(
                'COUNT(*) AS counter',
                "`{$this->events_table_alias}`.`event_id_country`",
            ),
        )));
    }

    public function get_assigned_users($event_id, array $conditions = array())
    {
        $this->db->select('*');
        $this->db->from($this->cr_events_cr_users_table);
        $this->db->where('id_event = ?', (int) $event_id);

        extract($conditions);

        if (isset($assigned_by_admin) && true === $assigned_by_admin) {
            $this->db->where('assigned_by_admin = ?', 1);
        }
        if (isset($assigned_by_them_self) && true === $assigned_by_them_self) {
            $this->db->where('assigned_by_admin = ?', 0);
        }

        return $this->db->query_all();
    }

    public function count_assigned_users($event_id, array $conditions = array())
    {
        $this->db->select('COUNT(*) as AGGREGATE');
        $this->db->from($this->cr_events_cr_users_table);
        $this->db->where('id_event = ?', (int) $event_id);

        extract($conditions);

        if (isset($assigned_by_admin) && true === $assigned_by_admin) {
            $this->db->where('assigned_by_admin = ?', 1);
        }
        if (isset($assigned_by_them_self) && true === $assigned_by_them_self) {
            $this->db->where('assigned_by_admin = ?', 0);
        }

        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (int) $counter['AGGREGATE'] : false;
    }

    public function is_user_assigned($user_id, $event_id)
    {
        $this->db->select('COUNT(*) as AGGREGATE');
        $this->db->from($this->cr_events_cr_users_table);
        $this->db->where('id_user = ?', (int) $user_id);
        $this->db->where('id_event = ?', (int) $event_id);
        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    public function assign_users(array $users = array(), $event_id = null, $by_admin = false)
    {
        if (empty($users)) {
            return false;
        }

        $insert = array();
        foreach ($users as $user_id) {
            $insert[] = array(
                'id_event'          => (int) $event_id,
                'id_user'           => (int) $user_id,
                'assigned_by_admin' => (int) $by_admin,
            );
        }

        return $this->db->insert_batch($this->cr_events_cr_users_table, $insert);
    }

    public function un_assign_users($users, $event_id)
    {
        if (empty($users)) {
            return false;
        }

        $builder = $this->createQueryBuilder();
        $builder
            ->delete($this->cr_events_cr_users_table)
            ->where(
                $builder->expr()->and(
                    $builder->expr()->eq('id_event', $builder->createNamedParameter((int) $event_id, ParameterType::INTEGER, ':event')),
                    $builder->expr()->eq('assigned_by_admin', $builder->createNamedParameter(1, ParameterType::INTEGER, ':assigned'))
                )
            )
        ;
        $this->scopeEventOfUsers($builder, $users);
        $builder->execute();

        return true;
    }

    public function has_attendance_for_email($event_id, $email)
    {
        $this->db->select('COUNT(*) AS AGGREGATE');
        $this->db->from($this->events_users_attend_table);
        $this->db->where('id_event = ?', (int) $event_id);
        $this->db->where('attend_email = ?', $email);
        $counter = $this->db->query_one();
        if (!$counter || empty($counter)) {
            return false;
        }

        return isset($counter['AGGREGATE']) ? (bool) (int) $counter['AGGREGATE'] : false;
    }

    public function attend_event(array $data)
    {
        return $this->db->insert($this->events_users_attend_table, $data);
    }

    public function get_attend_record($attendance_id)
    {
        $this->db->select('*');
        $this->db->from($this->events_users_attend_table);
        $this->db->where('id_attend = ?', (int) $attendance_id);
        $attendance = $this->db->query_one();
        if (empty($attendance)) {
            return null;
        }

        return $attendance;
    }

    public function get_attend_record_by_user($user_id, $event_id)
    {
        $this->db->select('*');
        $this->db->from($this->events_users_attend_table);
        $this->db->where('id_user = ?', (int) $user_id);
        $this->db->where('id_event = ?', (int) $event_id);
        $attendance = $this->db->query_one();
        if (empty($attendance)) {
            return null;
        }

        return $attendance;
    }

    public function get_attend_records_by_event($event_id)
    {
        $this->db->select('*');
        $this->db->from($this->events_users_attend_table);
        $this->db->where('id_event = ?', (int) $event_id);

        return $this->db->query_all();
    }

    public function update_attend_record($attendance_id, array $data)
    {
        $this->db->where('id_attend = ?', (int) $attendance_id);

        return $this->db->update($this->events_users_attend_table, $data);
    }

    /**
     * Scope a query to include users from list.
     *
     * @param null|int|int[]|string|string[] $users
     */
    protected function scopeEventOfUsers(QueryBuilder $builder, $users = null)
    {
        if (null === $users) {
            return;
        }

        if (is_array($users)) {
            $list = array_map('intval', $users);
        } elseif (is_string($users) && false !== strpos($users, ',')) {
            $list = array_map('intval', explode(',', $users));
        } else {
            $list = array((int) $users);
        }

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->events_table_alias}.event_id_user",
                array_map(
                    fn (int $index, $user) => $builder->createNamedParameter(
                        (int) $user,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("eventUserIds{$index}")
                    ),
                    array_keys($list),
                    $list
                )
            )
        );
    }

    /**
     * Scope a query to exclude specified events.
     *
     * @param int|int[]|string|string[] $events
     */
    protected function scopeEventExcludedEvents(QueryBuilder $builder, $events)
    {
        if (empty($events)) {
            return;
        }

        if (is_array($events)) {
            $list = array_map('intval', $events);
        } elseif (is_string($events) && false !== strpos($events, ',')) {
            $list = array_map('intval', explode(',', $events));
        } else {
            $list = array((int) $events);
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                "{$this->events_table_alias}.id_event",
                array_map(
                    fn (int $index, $event) => $builder->createNamedParameter(
                        (int) $event,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("eventExcludedIds{$index}")
                    ),
                    array_keys($list),
                    $list
                )
            )
        );
    }

    /**
     * Scope a query to filter by keywords.
     *
     * @param string $keywords
     */
    protected function scopeEventSearch(QueryBuilder $builder, $keywords)
    {
        if (str_word_count_utf8($keywords) > 1) {
            $escaped_search_string = $this->db->getConnection()->quote(trim($keywords));
            $search_parts = preg_split('/\\b/', trim($escaped_search_string, "'"));
            $search_parts = array_map('trim', $search_parts);
            $search_parts = array_filter($search_parts);
            if (!empty($search_parts)) {
                // Drop array keys
                $search_parts = array_values($search_parts);
                $parameter = $builder->createNamedParameter(
                    $builder->expr()->literal(implode('* <', $search_parts) . '*'),
                    ParameterType::STRING,
                    $this->nameScopeParameter('eventSearchMatchedText')
                );

                $builder->andWhere(
                    <<<CONDITION
                    MATCH (
                        {$this->events_table_alias}.event_name,
                        {$this->events_table_alias}.event_short_description,
                        {$this->events_table_alias}.event_address
                    ) AGAINST ({$parameter} IN BOOLEAN MODE)
                    CONDITION
                );
            }
        } else {
            $text_parameter = $builder->createNamedParameter(
                $keywords,
                ParameterType::STRING,
                $this->nameScopeParameter('eventSearchText')
            );
            $text_token_parameter = $builder->createNamedParameter(
                "%{$keywords}%",
                ParameterType::STRING,
                $this->nameScopeParameter('eventSearchTextToken')
            );

            $expressions = $builder->expr();
            $builder->andWhere(
                $expressions->or(
                    $expressions->eq("{$this->events_table_alias}.event_name", $text_parameter),
                    $expressions->eq("{$this->events_table_alias}.event_short_description", $text_parameter),
                    $expressions->eq("{$this->events_table_alias}.event_address", $text_parameter),
                    $expressions->like("{$this->events_table_alias}.event_name", $text_token_parameter),
                    $expressions->like("{$this->events_table_alias}.event_short_description", $text_token_parameter),
                    $expressions->like("{$this->events_table_alias}.event_address", $text_token_parameter)
                )
            );
        }
    }

    /**
     * Scope a query to filter by country.
     *
     * @param int $country
     */
    protected function scopeEventCountry(QueryBuilder $builder, $country)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_id_country",
                $builder->createNamedParameter((int) $country, ParameterType::INTEGER, $this->nameScopeParameter('eventCountryId'))
            )
        );
    }

    /**
     * Scope a query to filter by state.
     *
     * @param int $state
     */
    protected function scopeEventState(QueryBuilder $builder, $state)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_id_state",
                $builder->createNamedParameter((int) $state, ParameterType::INTEGER, $this->nameScopeParameter('eventStateId'))
            )
        );
    }

    /**
     * Scope a query to filter by city.
     *
     * @param int $city
     */
    protected function scopeEventCity(QueryBuilder $builder, $city)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_id_city",
                $builder->createNamedParameter((int) $city, ParameterType::INTEGER, $this->nameScopeParameter('eventCityId'))
            )
        );
    }

    /**
     * Scope a query to filter by type.
     *
     * @param int $type
     */
    protected function scopeEventType(QueryBuilder $builder, $type)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_id_type",
                $builder->createNamedParameter((int) $type, ParameterType::INTEGER, $this->nameScopeParameter('eventTypeId'))
            )
        );
    }

    /**
     * Scope a query to filter by status.
     *
     * @param string $status
     */
    protected function scopeEventStatus(QueryBuilder $builder, $status)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_status",
                $builder->createNamedParameter($status, ParameterType::STRING, $this->nameScopeParameter('eventStatus'))
            )
        );
    }

    /**
     * Scope a query to filter by visilibility.
     *
     * @param bool  $status
     * @param mixed $visibility
     */
    protected function scopeEventVisible(QueryBuilder $builder, $visibility)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_is_visible",
                $builder->createNamedParameter((int) (bool) $visibility, ParameterType::BOOLEAN, $this->nameScopeParameter('eventVisibility'))
            )
        );
    }

    /**
     * Scope a query to filter by assigned users list.
     *
     * @param mixed $users
     */
    protected function scopeEventAssignedUsers(QueryBuilder $builder, $users)
    {
        if (empty($users)) {
            return;
        }

        if (is_array($users)) {
            $list = array_map('intval', $users);
        } elseif (is_string($users) && false !== strpos($users, ',')) {
            $list = array_map('intval', explode(',', $users));
        } else {
            $list = array((int) $users);
        }

        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->cr_events_cr_users_table, $this->cr_events_cr_users_table_alias)
            ->where(
                $subquery_builder->expr()->eq("{$this->cr_events_cr_users_table_alias}.id_event", "{$this->events_table_alias}.id_event")
            )
            ->andWhere(
                $subquery_builder->expr()->in(
                    "{$this->cr_events_cr_users_table_alias}.id_user",
                    array_map(
                        fn (int $index, $user) => $builder->createNamedParameter(
                            (int) $user,
                            ParameterType::INTEGER,
                            $this->nameScopeParameter("eventAssignedUsersIds{$index}")
                        ),
                        array_keys($list),
                        $list
                    )
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subquery_builder->getSQL()})");
    }

    /**
     * Scope a query to filter by assigned user.
     *
     * @param int|string $user
     */
    protected function scopeEventAssignedUser(QueryBuilder $builder, $user)
    {
        if (empty($user)) {
            return;
        }

        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->cr_events_cr_users_table, $this->cr_events_cr_users_table_alias)
            ->where(
                $subquery_builder->expr()->eq("{$this->cr_events_cr_users_table_alias}.id_event", "{$this->events_table_alias}.id_event")
            )
            ->andWhere(
                $subquery_builder->expr()->eq(
                    "{$this->cr_events_cr_users_table_alias}.id_user",
                    $builder->createNamedParameter(
                        (int) $user,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('eventAssignedUserId')
                    )
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subquery_builder->getSQL()})");
    }

    /**
     * Scope a query to filter by attendee.
     *
     * @param int|string $attendee
     */
    protected function scopeEventAttendee(QueryBuilder $builder, $attendee)
    {
        if (empty($attendee)) {
            return;
        }

        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->events_users_attend_table, $this->events_users_attend_table_alias)
            ->where(
                $subquery_builder->expr()->eq("{$this->events_users_attend_table_alias}.id_event", "{$this->events_table_alias}.id_event")
            )
            ->andWhere(
                $subquery_builder->expr()->eq(
                    "{$this->events_users_attend_table_alias}.id_user",
                    $builder->createNamedParameter(
                        (int) $attendee,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('eventAttendeeId')
                    )
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subquery_builder->getSQL()})");
    }

    /**
     * Scope a query to filter by confirmed attendee.
     *
     * @param int|string $attendee
     */
    protected function scopeEventConfirmedAttendee(QueryBuilder $builder, $attendee)
    {
        if (empty($attendee)) {
            return;
        }

        $subquery_builder = $this->createQueryBuilder();
        $subquery_builder
            ->select('*')
            ->from($this->events_users_attend_table, $this->events_users_attend_table_alias)
            ->where(
                $subquery_builder->expr()->eq("{$this->events_users_attend_table_alias}.id_event", "{$this->events_table_alias}.id_event")
            )
            ->andWhere(
                $subquery_builder->expr()->eq(
                    "{$this->events_users_attend_table_alias}.id_user",
                    $builder->createNamedParameter(
                        (int) $attendee,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter('eventConfirmedAttendeeId')
                    )
                )
            )
            ->andWhere(
                $subquery_builder->expr()->eq(
                    "{$this->events_users_attend_table_alias}.id_user",
                    $builder->createNamedParameter(
                        'confirmed',
                        ParameterType::STRING,
                        $this->nameScopeParameter('eventAttendeeStatus')
                    )
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subquery_builder->getSQL()})");
    }

    /**
     * Scope a query to filter by EP manager.
     *
     * @param int|string $user
     * @param mixed      $manager
     */
    protected function scopeEventManager(QueryBuilder $builder, $manager)
    {
        if (empty($manager)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_ep_manager",
                $builder->createNamedParameter((int) $manager, ParameterType::INTEGER, $this->nameScopeParameter('eventManager'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEventCreatedAt(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_date_create",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventCreatedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEventCreatedFrom(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->events_table_alias}.event_date_create",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventCreatedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEventCreatedTo(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->events_table_alias}.event_date_create",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventCreatedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEventCreatedAtDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->events_table_alias}.event_date_create)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventCreatedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date from.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEventCreatedFromDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->events_table_alias}.event_date_create)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventCreatedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation date to.
     *
     * @param \DateTimeInterface|int|string $created_at
     */
    protected function scopeEventCreatedToDate(QueryBuilder $builder, $created_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($created_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->events_table_alias}.event_date_create)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventCreatedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by creation month.
     *
     * @param \DateTimeInterface|int|string $month
     */
    protected function scopeEventStartedAtMonth(QueryBuilder $builder, $month)
    {
        if (is_numeric($month)) {
            try {
                $now = new \DateTime();
                $now->setDate($now->format('Y'), $month, $now->format('d'));
            } catch (\Exception $exception) {
                return;
            }
            if (null === $now) {
                return;
            }

            $date = $now->format('Y-m');
        } else {
            if (is_string($month)) {
                try {
                    $pos = mb_strpos($month, '-');
                    if (4 === $pos) {
                        $month = \DateTime::createFromFormat('Y-m', $month);
                    } elseif (2 === $pos) {
                        $month = \DateTime::createFromFormat('m-Y', $month);
                    }
                } catch (\Exception $exception) {
                    return;
                }
            }
            if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($month, 'm-Y'))) {
                return;
            }
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE_FORMAT({$this->events_table_alias}.event_date_start, '%m-%Y')",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventStartedAtMonth'))
            )
        );
    }

    /**
     * Scope a query to filter by start datetime.
     *
     * @param \DateTimeInterface|int|string $started_at
     */
    protected function scopeEventStartedAt(QueryBuilder $builder, $started_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($started_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_date_start",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventStartedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by start datetime from.
     *
     * @param \DateTimeInterface|int|string $started_at
     */
    protected function scopeEventStartedFrom(QueryBuilder $builder, $started_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($started_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->events_table_alias}.event_date_start",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventStartedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     *
     * @param \DateTimeInterface|int|string $started_at
     */
    protected function scopeEventStartedTo(QueryBuilder $builder, $started_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($started_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->events_table_alias}.event_date_start",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventStartedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by start date.
     *
     * @param \DateTimeInterface|int|string $started_at
     */
    protected function scopeEventStartedAtDate(QueryBuilder $builder, $started_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($started_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->events_table_alias}.event_date_start)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventStartedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by start date from.
     *
     * @param \DateTimeInterface|int|string $started_at
     */
    protected function scopeEventStartedFromDate(QueryBuilder $builder, $started_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($started_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->events_table_alias}.event_date_start)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventStartedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by start date to.
     *
     * @param \DateTimeInterface|int|string $started_at
     */
    protected function scopeEventStartedToDate(QueryBuilder $builder, $started_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($started_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->events_table_alias}.event_date_start)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventStartedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by end datetime.
     *
     * @param \DateTimeInterface|int|string $ended_at
     */
    protected function scopeEventEndedAt(QueryBuilder $builder, $ended_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($ended_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->events_table_alias}.event_date_end",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventEndedAt'))
            )
        );
    }

    /**
     * Scope a query to filter by end datetime from.
     *
     * @param \DateTimeInterface|int|string $ended_at
     */
    protected function scopeEventEndedFrom(QueryBuilder $builder, $ended_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($ended_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->events_table_alias}.event_date_end",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventEndedFrom'))
            )
        );
    }

    /**
     * Scope a query to filter by end datetime to.
     *
     * @param \DateTimeInterface|int|string $ended_at
     */
    protected function scopeEventEndedTo(QueryBuilder $builder, $ended_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($ended_at))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "{$this->events_table_alias}.event_date_end",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventEndedTo'))
            )
        );
    }

    /**
     * Scope a query to filter by end date.
     *
     * @param \DateTimeInterface|int|string $ended_at
     */
    protected function scopeEventEndedAtDate(QueryBuilder $builder, $ended_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($ended_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->eq(
                "DATE({$this->events_table_alias}.event_date_end)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventEndedAtDate'))
            )
        );
    }

    /**
     * Scope a query to filter by end date from.
     *
     * @param \DateTimeInterface|int|string $ended_at
     */
    protected function scopeEventEndedFromDate(QueryBuilder $builder, $ended_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($ended_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->events_table_alias}.event_date_end)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventEndedFromDate'))
            )
        );
    }

    /**
     * Scope a query to filter by end date to.
     *
     * @param \DateTimeInterface|int|string $ended_at
     */
    protected function scopeEventEndedToDate(QueryBuilder $builder, $ended_at)
    {
        if (null === ($date = $this->convertDatetimeAttributeToDatabaseValue($ended_at, 'Y-m-d'))) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->events_table_alias}.event_date_end)",
                $builder->createNamedParameter($date, ParameterType::STRING, $this->nameScopeParameter('eventEndedToDate'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration.
     */
    protected function scopeEventExpired(QueryBuilder $builder)
    {
        $builder->andWhere(
            $builder->expr()->lt(
                "{$this->events_table_alias}.event_date_end",
                'NOW()'
            )
        );
    }

    /**
     * Scope a query to filter by expiration today.
     */
    protected function scopeEventExpiredToday(QueryBuilder $builder)
    {
        $builder->andWhere(
            $builder->expr()->lt(
                "DATE({$this->events_table_alias}.event_date_end)",
                'DATE(NOW())'
            )
        );
    }

    /**
     * Scope a query to filter by active state.
     */
    protected function scopeEventActive(QueryBuilder $builder)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "{$this->events_table_alias}.event_date_end",
                'NOW()'
            )
        );
    }

    /**
     * Scope a query to filter by active state today.
     */
    protected function scopeEventActiveToday(QueryBuilder $builder)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->events_table_alias}.event_date_end)",
                'DATE(NOW())'
            )
        );
    }

    /**
     * Scope a query to bind event types to the query.
     */
    protected function bindEventTypes(QueryBuilder $builder)
    {
        $builder->leftJoin(
            $this->events_table_alias,
            $this->events_types_table,
            $this->events_types_table_alias,
            "`{$this->events_table_alias}`.event_id_type = `{$this->events_types_table_alias}`.id"
        );
    }

    /**
     * Scope a query to bind countries to the query.
     */
    protected function bindEventCountries(QueryBuilder $builder)
    {
        $builder->leftJoin(
            $this->events_table_alias,
            $this->port_country_table,
            $this->port_country_table_alias,
            "`{$this->events_table_alias}`.event_id_country = `{$this->port_country_table_alias}`.id"
        );
    }

    /**
     * Scope a query to bind states to the query.
     */
    protected function bindEventStates(QueryBuilder $builder)
    {
        $builder->leftJoin(
            $this->events_table_alias,
            $this->states_table,
            $this->states_table_alias,
            "`{$this->events_table_alias}`.event_id_state = `{$this->states_table_alias}`.id"
        );
    }

    /**
     * Scope a query to bind cities to the query.
     */
    protected function bindEventCities(QueryBuilder $builder)
    {
        $builder->leftJoin(
            $this->events_table_alias,
            $this->cities_table,
            $this->cities_table_alias,
            "`{$this->events_table_alias}`.event_id_city = `{$this->cities_table_alias}`.id"
        );
    }

    /**
     * Resolves static relationships with country.
     */
    protected function eventCountry(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->port_country_table, 'id'),
            'event_id_country'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with state.
     */
    protected function eventState(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->states_table, 'id'),
            'event_id_state'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with city.
     */
    protected function eventCity(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->cities_table, 'id'),
            'event_id_city'
        )->disableNativeCast();
    }

    /**
     * Resolves static relationships with type.
     */
    protected function eventType(): RelationInterface
    {
        return $this->belongsTo(
            new PortableModel($this->getHandler(), $this->events_types_table, 'id'),
            'event_id_type'
        )->disableNativeCast();
    }

    /**
     * Creates new related instance of the model for relation.
     *
     * @param BaseModel|Model|string $source
     */
    protected function resolveRelatedModel($source): Model
    {
        if ($source === $this) {
            return new PortableModel($this->getHandler(), $this->events_table_alias, 'id_event');
        }

        return parent::resolveRelatedModel($source);
    }
}
