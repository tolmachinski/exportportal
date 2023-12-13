<?php

declare(strict_types=1);

use App\Common\Contracts\Company\CompanyType;
use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Elasticsearch_Users model.
 */
final class Elasticsearch_Users_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    private $type = 'users';

    /**
     * {@inheritdoc}
     */
    public $users = [];

    /**
     * {@inheritdoc}
     */
    public $usersCount = 0;
    /**
     * {@inheritdoc}
     */
    protected string $table = 'users';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USERS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'idu';

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'idu'           => Types::INTEGER,
        'user_group'    => Types::INTEGER,
        'logged'        => Types::INTEGER,
        'is_verified'   => Types::INTEGER,
        'status'        => UserStatus::class,
    ];

    /**
     * @var TinyMVC_Library_Elasticsearch
     */
    protected $elasticsearchLibrary;

    public function __construct(TinyMVC_PDO $connectionHandler)
    {
        parent::__construct($connectionHandler);

        $this->elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);
    }

    /**
     * The method was created to get users from elasticsearch.
     *
     * @param array $params - query filters
     * @return array
     */
    public function getUsers(array $params): array
    {
        $must = $should = $filterMust = $filterMustNot = [];

        $page = $params['page'] ?? 1;
        $perPage = $params['perPage'] ?? 20;

        if (!empty($params['id'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_term('id', $params['id']);
        }

        if (!empty($params['notId'])) {
            $filterMustNot[] = $this->elasticsearchLibrary->get_term('id', $params['notId']);
        }

        if (isset($params['is_verified'])) {
            $filterMust[] = $this->elasticsearchLibrary->get_term('is_verified', $params['is_verified']);
        }

        if (!empty($params['nameOrCompanyName'])) {
            $filterMust[] = [
                'bool' => [
                    'should' => [
                        $this->elasticsearchLibrary->get_match('fullName', $params['nameOrCompanyName']),
                        $this->elasticsearchLibrary->get_nested(
                            'company',
                            [
                                'bool'  => [
                                    'should'  => $this->elasticsearchLibrary->get_match('company.name', $params['nameOrCompanyName'])
                                ]
                            ]
                        ),
                    ]
                ]
            ];
        }

        $elasticQuery = [
            'query' => [
                'bool' => [
                    'must'      => $must,
                    'should'    => $should,
                    'filter'    => [
                        'bool'  => [
                            'must'      => $filterMust,
                            'must_not'  => $filterMustNot
                        ]
                    ]
                ]
            ],
            'sort'  => $params['sortBy'] ?? ['_score'],
            'size'  => $perPage,
            'from'  => $perPage * ($page - 1),
        ];

        $elasticResult = $this->elasticsearchLibrary->get($this->type, $elasticQuery);

        if (isset($elasticResult['hits']['hits'])) {
            $this->users = array_map(fn ($ar) => $ar['_source'], $elasticResult['hits']['hits']);
            $this->usersCount = $elasticResult['hits']['total']['value'];
        }

        return $this->users;
    }

    /**
     * The method created for indexing users data in elasticsearch.
     */
    public function index(?int $userId = null)
    {
        if (null !== $userId) {
            $users = $this->getUsersFromMySQL(['id' => $userId], 1);

            if (empty($user = array_shift($users))) {
                return false;
            }

            return $this->elasticsearchLibrary->index($this->type, $user['id'], $user);
        }

        ini_set('max_execution_time', '0');
        ini_set('request_terminate_timeout', '0');

        if (PHP_SAPI === 'cli') {
            $countIndexedUsers = 0;
            $countUsers = $this->countAllBy([
                'scopes'    => [
                    'status'        => UserStatus::ACTIVE(),
                    'groupTypes'    => [GroupType::BUYER(), GroupType::SELLER(), GroupType::SHIPPER()],
                    'isFake'        => false,
                ],
                'joins'     => ['userGroups'],
            ]);

            $this->showIndexingStatus(0, $countUsers);
        }

        $limit = 1000;
        $skip = 0;

        while (!empty($users = $this->getUsersFromMySQL([], $limit, $skip))) {
            $this->elasticsearchLibrary->indexBulk($this->type, $users);
            $skip += $limit;

            if (PHP_SAPI === 'cli') {
                $countIndexedUsers += count($users);
                $this->showIndexingStatus($countIndexedUsers, $countUsers);
            }

            unset($users);
        }
    }

    /**
     * Index|update or delete user in elasticsearch
     *
     * @param int $userId
     * @return bool
     */
    public function sync(int $userId): bool
    {
        // delete user
        if (empty($user = $this->getUserFromMySQL($userId))) {
            $result = $this->elasticsearchLibrary->deleteById($this->type, $userId);

            return in_array($result['result'] ?? 'not_found', ['deleted', 'not_found']);
        }

        // update user
        if ($this->elasticsearchLibrary->get_by_id($this->type, $userId)['found']) {
            $result = $this->elasticsearchLibrary->update($this->type, $userId, $user, ['detect_noop' => false]);

            return 'updated' === $result['result'];
        }

        // index user
        $result = $this->elasticsearchLibrary->index($this->type, $userId, $user);

        return 'created' === $result['result'];
    }

    /**
     * Delete user from elasticsearch
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId):bool
    {
        $result = $this->elasticsearchLibrary->deleteById($this->type, $userId);

        return in_array($result['result'], ['deleted', 'not_found']);
    }
    /**
     * This method is intended for getting data from Mysql.
     *
     * @param array $params
     * @param int $page
     * @param int $perPage
     *
     * @return array
     */
    private function getUsersFromMySQL(array $params = [], int $limit = 1000, int $skip = 0): array
    {
        $conditions = array_filter(
            [
                'id'            => $params['id'] ?? null,
                'status'        => UserStatus::ACTIVE(),
                'groupTypes'    => [GroupType::BUYER(), GroupType::SELLER(), GroupType::SHIPPER()],
                'isFake'        => false,
            ],
            fn ($value) => null !== $value
        );

        $columns = [
            "`{$this->table}`.`idu`",
            "`{$this->table}`.`fname`",
            "`{$this->table}`.`lname`",
            "`{$this->table}`.`status`",
            "`{$this->table}`.`user_group`",
            "`{$this->table}`.`logged`",
            "`{$this->table}`.`user_photo`",
            "`{$this->table}`.`is_verified`",
        ];

        $joins = ['userGroups'];
        $with = ['group'];

        $users = $this->findAllBy(compact('with', 'conditions', 'joins', 'columns', 'limit', 'skip'));

        $sellersIds = $shippersIds = [];
        foreach ($users as $user) {
            switch ($user['group']['gr_type']) {
                case GroupType::SELLER():
                    $sellersIds[] = $user['idu'];

                    break;
                case GroupType::SHIPPER():
                    $shippersIds[] = $user['idu'];

                    break;
            }
        }

        $sellers = [];
        if (!empty($sellersIds)) {
            /** @var Seller_Companies_Model $sellerCompaniesModel */
            $sellerCompaniesModel = model(Seller_Companies_Model::class);
            $sellerCompaniesTable = $sellerCompaniesModel->getTable();

            $sellers = array_column(
                $sellerCompaniesModel->findAllBy([
                    'columns'   => [
                        "`{$sellerCompaniesTable}`.`id_company`",
                        "`{$sellerCompaniesTable}`.`name_company`",
                        "`{$sellerCompaniesTable}`.`legal_name_company`",
                        "`{$sellerCompaniesTable}`.`id_user`",
                    ],
                    'scopes'    => [
                        'usersIds'  => $sellersIds,
                        'type'      => CompanyType::COMPANY(),
                    ],
                ]),
                null,
                'id_user'
            );
        }

        $shippers = [];
        if (!empty($shippersIds)) {
            /** @var Shipper_Companies_Model $shipperCompaniesModel */
            $shipperCompaniesModel = model(Shipper_Companies_Model::class);
            $shipperCompaniesTable = $shipperCompaniesModel->getTable();

            $shippers = array_column(
                $shipperCompaniesModel->findAllBy([
                    'columns'   => [
                        "`{$shipperCompaniesTable}`.`id`",
                        "`{$shipperCompaniesTable}`.`co_name`",
                        "`{$shipperCompaniesTable}`.`legal_co_name`",
                        "`{$shipperCompaniesTable}`.`id_user`",
                    ],
                    'scopes'    => [
                        'usersIds' => $shippersIds,
                    ],
                ]),
                null,
                'id_user'
            );
        }

        $dataForElasticsearch = [];
        foreach ($users as $user) {
            $row = [
                'id'            => $user['idu'],
                'fname'         => $user['fname'],
                'lname'         => $user['lname'],
                'fullName'      => "{$user['fname']} {$user['lname']}",
                'logged'        => $user['logged'],
                'photo'         => $user['user_photo'],
                'is_verified'   => $user['is_verified'],
                'group'         => [
                    'id'    => $user['group']['idgroup'],
                    'name'  => $user['group']['gr_name'],
                    'type'  => (string) $user['group']['gr_type'],
                    'alias' => (string) $user['group']['gr_alias'],
                ],
            ];

            switch ($user['group']['gr_type']) {
                case GroupType::SELLER():
                    $row['company'] = [
                        'id'        => $sellers[$user['idu']]['id_company'],
                        'name'      => decodeCleanInput($sellers[$user['idu']]['name_company']),
                        'legalName' => decodeCleanInput($sellers[$user['idu']]['legal_name_company']),
                    ];

                    break;
                case GroupType::SHIPPER():
                    $row['company'] = [
                        'id'        => $shippers[$user['idu']]['id'],
                        'name'      => decodeCleanInput($shippers[$user['idu']]['co_name']),
                        'legalName' => decodeCleanInput($shippers[$user['idu']]['legal_co_name']),
                    ];

                    break;
            }

            $dataForElasticsearch[] = $row;
        }

        return $dataForElasticsearch;
    }

    /**
     * This method return the user from MySQL
     *
     * @param int $userId
     * @return array|null
     */
    private function getUserFromMySQL(int $userId): ?array
    {
        $users = $this->getUsersFromMySQL(['id' => $userId], 1);

        return array_shift($users);
    }

    //region scopes

    /**
     * Scope by user id
     *
     * @param QueryBuilder $builder
     * @param int $userId
     *
     * @return void
     */
    protected function scopeId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`idu`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope by user status
     *
     * @param QueryBuilder $builder
     * @param UserStatus $status
     *
     * @return void
     */
    protected function scopeStatus(QueryBuilder $builder, UserStatus $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`status`",
                $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter('userStatus'))
            )
        );
    }

    /**
     * Scope query for group types - require join with userGroups
     *
     * @param QueryBuilder $builder
     * @param GroupType[] $groupTypes
     *
     * @return void
     */
    protected function scopeGroupTypes(QueryBuilder $builder, array $groupTypes): void
    {
        if (empty($groupTypes)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->group()->getRelated()->getTable()}`.`gr_type`",
                array_map(
                    fn (int $i, $groupType) => $builder->createNamedParameter((string) $groupType, ParameterType::STRING, $this->nameScopeParameter("groupTypes_{$i}")),
                    array_keys($groupTypes),
                    $groupTypes
                )
            )
        );
    }

    /**
     * Scope query for not fake user status.
     *
     * @param QueryBuilder $builder
     * @param bool $fake
     *
     * @return void
     */
    protected function scopeIsFake(QueryBuilder $builder, bool $fake): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`fake_user`",
                $builder->createNamedParameter((int) $fake, ParameterType::INTEGER, $this->nameScopeParameter('isFake'))
            )
        );
    }

    /**
     * Scope for join user groups table.
     *
     * @param QueryBuilder $builder
     * @return void
     */
    protected function bindUserGroups(QueryBuilder $builder): void
    {
        $userGroupsTable = $this->group()->getRelated()->getTable();

        $builder
            ->leftJoin(
                $this->table,
                $userGroupsTable,
                $userGroupsTable,
                "`{$this->table}`.`user_group` = `{$userGroupsTable}`.`idgroup`"
            )
        ;
    }

    /**
     * Relation with the group.
     */
    protected function group(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'user_group')->enableNativeCast();
    }

    private function showIndexingStatus($done, $total)
    {
        static $startTime;
        $startTime = $startTime ?: microtime(true);

        $percent = (float) ($done / $total);
        $disp = number_format($percent * 100, 0);

        echo "\r {$done}/{$total} {$disp}% " . number_format(microtime(true) - $startTime, 2) . ' sec';

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo "\n";
        }
    }
}

// End of file elasticsearch_users_model.php
// Location: /tinymvc/myapp/models/elasticsearch_users_model.php
