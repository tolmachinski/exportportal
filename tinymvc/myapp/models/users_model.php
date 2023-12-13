<?php

declare(strict_types=1);

use App\Common\Database\Model;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\ParameterType;
use App\Common\Database\Concerns;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Common\Contracts\User\UserType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Contracts\User\EmailStatus;
use App\Common\Contracts\Company\CompanyType;
use App\Common\Contracts\Shipper\ShipperType;
use App\Common\Contracts\User\UserSourceType;
use App\Common\Contracts\User\RestrictionType;
use App\Common\Database\Types\Types as CustomTypes;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Contracts\User\VerificationUploadProgress;
use App\Common\Contracts\Cancel\CancellationRequestStatus;

/**
 * Users model.
 */
final class Users_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'registration_date';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = null;

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

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
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'id_principal',
        'email_status',
        'status_temp',
        'menu',
        'state',
        'city',
        'phone_code',
        'phone',
        'showed_status',
        'fax_code',
        'fax',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'complete_profile_fields',
        'date_profile_updated',
        'documents_info',
        'activation_account_date',
        'verification_documents_date',
        'sync_with_related_accounts',
        'trade_passport_expiration_date',
        'matchmaking_email_date',
        'zoho_id_record'
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'idu'                             => Types::INTEGER,
        'id_principal'                    => Types::INTEGER,
        'email_status'                    => EmailStatus::class,
        'status'                          => UserStatus::class,
        'status_temp'                     => UserStatus::class,
        'user_type'                       => UserType::class,
        'user_group'                      => Types::INTEGER,
        'user_upgrade_from'               => Types::INTEGER,
        'upgrade_package'                 => Types::INTEGER,
        'logged'                          => Types::BOOLEAN,
        'last_active'                     => Types::DATETIME_IMMUTABLE,
        'sent_systmess_date'              => Types::DATETIME_IMMUTABLE,
        'fake_user'                       => Types::BOOLEAN,
        'is_model'                        => Types::BOOLEAN,
        'registration_date'               => Types::DATETIME_IMMUTABLE,
        'notice'                          => CustomTypes::SIMPLE_JSON_ARRAY,
        'menu'                            => Types::JSON,
        'id_company'                      => Types::INTEGER,
        'is_muted'                        => Types::BOOLEAN,
        'invited_to_chat'                 => Types::BOOLEAN,
        'invited_to_chat_date'            => Types::DATETIME_IMMUTABLE,
        'clean_session_time'              => Types::DATETIME_IMMUTABLE,
        'notify_email'                    => Types::BOOLEAN,
        'subscription_email'              => Types::BOOLEAN,
        'resend_accreditation_email'      => Types::INTEGER,
        'resend_email_date'               => Types::DATETIME_IMMUTABLE,
        'accreditation_transfer'          => Types::JSON,
        'accreditation_docs'              => CustomTypes::SIMPLE_JSON_ARRAY,
        'accreditation'                   => Types::BOOLEAN,
        'accreditation_files'             => Types::BOOLEAN,
        'accreditation_files_upload'      => Types::INTEGER,
        'accreditation_files_upload_date' => Types::DATETIME_IMMUTABLE,
        'calling_status'                  => Types::INTEGER,
        'calling_date_last'               => Types::DATETIME_IMMUTABLE,
        'paid'                            => Types::BOOLEAN,
        'paid_price'                      => CustomTypes::SIMPLE_MONEY,
        'paid_until'                      => Types::DATETIME_IMMUTABLE,
        'free_featured_items'             => Types::INTEGER,
        'user_photo_actulized'            => Types::BOOLEAN,
        'user_photo_with_badge'           => Types::BOOLEAN,
        'user_city_lat'                   => Types::DECIMAL,
        'user_city_lng'                   => Types::DECIMAL,
        'country'                         => Types::INTEGER,
        'state'                           => Types::INTEGER,
        'city'                            => Types::INTEGER,
        'phone_code_id'                   => Types::INTEGER,
        'showed_status_date'              => Types::DATETIME_IMMUTABLE,
        'fax_code_id'                     => Types::INTEGER,
        'user_page_blocked'               => Types::INTEGER,
        'user_find_type'                  => UserSourceType::class,
        'complete_profile_fields'         => Types::JSON,
        'profile_completed'               => Types::BOOLEAN,
        'date_profile_updated'            => Types::DATETIME_IMMUTABLE,
        'email_confirmed'                 => Types::BOOLEAN,
        'accreditation_docs_exported'     => Types::BOOLEAN,
        'is_verified'                     => Types::BOOLEAN,
        'verfication_upload_progress'     => VerificationUploadProgress::class,
        'last_update_for_crm'             => Types::DATETIME_IMMUTABLE,
        'documents_info'                  => Types::JSON,
        'not_auto_logout_date'            => Types::DATE_IMMUTABLE,
        'activation_account_date'         => Types::DATETIME_IMMUTABLE,
        'verification_documents_date'     => Types::DATETIME_IMMUTABLE,
        'sync_with_related_accounts'      => Types::JSON,
        'trade_passport_expiration_date'  => Types::DATETIME_IMMUTABLE,
        'matchmaking_email_date'          => Types::DATETIME_IMMUTABLE,
        'accept_matchmaking_email'        => Types::BOOLEAN,
        'check_items_views_email_date'    => Types::DATETIME_IMMUTABLE,
    ];

    private $userColumnsExportedToCrm = [
        'registration_date' => '',
        'zoho_id_record'    => '',
        'description'       => '',
        'is_verified'       => '',
        'user_group'        => '',
        'phone_code'        => '',
        'user_photo'        => '',
        'instagram'         => '',
        'linkedin'          => '',
        'facebook'          => '',
        'fax_code'          => '',
        'twitter'           => '',
        'address'           => '',
        'youtube'           => '',
        'website'           => '',
        'status'            => '',
        'fname'             => '',
        'lname'             => '',
        'email'             => '',
        'skype'             => '',
        'phone'             => '',
        'idu'               => '',
        'fax'               => '',
    ];

    /**
     * Prepare data for cron: get_certification_documents_notification.
     *
     * @return array $users
     */
    public function getUsersToNotifyAboutCertificationDocuments(): array
    {
        /** @var Notify_Model $notifyModel */
        $notifyModel = model(Notify_Model::class);

        /** @var SystMess_Model $systemMessagesModel */
        $systemMessagesModel = model(SystMess_Model::class);

        $userSystmessagesTable = $notifyModel->user_systmessages_table;
        $systemMessagesTable = $systemMessagesModel->systmessages_table;

        $queryBuilder = $this->createQueryBuilder();

        $subQueryBuilder = $this->createQueryBuilder();
        $subQueryBuilder
            ->select(
                "{$userSystmessagesTable}.`idu`",
                "{$userSystmessagesTable}.`id_um`",
                "{$userSystmessagesTable}.`init_date`",
                "row_number() over (partition by {$userSystmessagesTable}.`idmess`, {$userSystmessagesTable}.`idu` order by {$userSystmessagesTable}.`id_um` desc) as systmessRank"
            )
            ->from($userSystmessagesTable)
            ->leftJoin($userSystmessagesTable, $systemMessagesTable, $systemMessagesTable, "{$systemMessagesTable}.`idmess` = {$userSystmessagesTable}.`idmess`")
            ->where(
                $queryBuilder->expr()->eq(
                    "{$systemMessagesTable}.`mess_code`",
                    $queryBuilder->createNamedParameter('get_certification_documents', ParameterType::STRING, $this->nameScopeParameter('mess_code'))
                )
            )
        ;

        $joinQueryBuilder = $this->createQueryBuilder();
        $joinQueryBuilder
            ->select('*')
            ->from('(' . $subQueryBuilder->getSQL() . ') ranks')
            ->where(
                $queryBuilder->expr()->eq(
                    'systmessRank',
                    $queryBuilder->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('systmessRank'))
                )
            )
        ;

        $queryBuilder
            ->select(
                "{$this->table}.`idu`",
                'systmessagesQuery.`init_date`'
            )
            ->from($this->table)
            ->leftJoin($this->table, '(' . $joinQueryBuilder->getSQL() . ')', 'systmessagesQuery', "{$this->table}.`idu` = systmessagesQuery.`idu`")
            ->andWhere(
                $queryBuilder->expr()->in(
                    "{$this->table}.`user_group`",
                    [
                        $queryBuilder->createNamedParameter(3, ParameterType::INTEGER, $this->nameScopeParameter('userGroup1')),
                        $queryBuilder->createNamedParameter(6, ParameterType::INTEGER, $this->nameScopeParameter('userGroup2')),
                    ]
                ),
                $queryBuilder->expr()->eq(
                    "{$this->table}.`status`",
                    $queryBuilder->createNamedParameter('active', ParameterType::STRING, $this->nameScopeParameter('userStatus')),
                ),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->isNull('systmessagesQuery.`init_date`'),
                    $queryBuilder->expr()->lte(
                        'DATE(systmessagesQuery.init_date)',
                        'DATE_ADD(CURDATE(), INTERVAL -2 WEEK)'
                    )
                )
            )
        ;

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    /**
     * Gets the list of users that have all provided rights
     * // TODO: Refactor method and delete findUsersWithAllRights from old user model.
     *
     * @author Bendiucov Tatiana
     *
     * @todo Refactoring [09.11.2021]
     * This is the method findUsersWithAllRights copied from user_model and it should be in a new model with rights that isn't created yet
     */
    public function findUsersWithRights(array $rights = []): array
    {
        if (empty($rights)) {
            return [];
        }

        // Make the general query
        $query = $this->createQueryBuilder();
        // Make rights subquery
        $rightsSubQuery = $this->createQueryBuilder();
        $rightsSubQuery
            ->select('idgroup')
            ->from('rights')
            ->leftJoin('rights', 'usergroup_rights', null, 'rights.idright = usergroup_rights.idright')
            ->andWhere(
                $rightsSubQuery->expr()->and(
                    ...array_map(
                        fn (string $right) => $rightsSubQuery->expr()->eq('r_alias', $query->createPositionalParameter($right)),
                        $rights
                    )
                )
            )
        ;

        // Build general query
        $query
            ->select('*')
            ->from($this->table)
            ->where(
                $query->expr()->or(
                    $query->expr()->in('user_group', $rightsSubQuery->getSQL())
                )
            )
        ;

        return $query->execute()->fetchAllAssociative();
    }

    /**
     * Get users and additional data from other tables.
     *
     * @param array usersIds
     * @param bool  $itemsInfo
     * @param mixed $usersIds
     */
    public function getSellersForList($usersIds, $itemsInfo = false): array
    {
        /** @var Seller_Companies_Model $sellerCompaniesModel */
        $sellerCompaniesModel = model(Seller_Companies_Model::class);
        $companiesTable = $sellerCompaniesModel->getTable();

        /** @var User_Groups_Model $userGroupsModel */
        $userGroupsModel = model(User_Groups_Model::class);
        $userGroupsTable = $userGroupsModel->getTable();

        /** @var Products_Model $productsModel */
        $productsModel = model(Products_Model::class);
        $productsTable = $productsModel->getTable();

        /** @var Countries_Model $portCountryModel */
        $portCountryModel = model(Countries_Model::class);
        $portCountryTable = $portCountryModel->getTable();

        $usersTable = $this->table;
        $queryBuilder = $this->createQueryBuilder();

        $columns = [
            "{$usersTable}.`idu`",
            "CONCAT({$usersTable}.`fname`, ' ', {$usersTable}.`lname`) user_name",
            "{$usersTable}.`logged`",
            "{$usersTable}.`paid`",
            "{$usersTable}.`user_photo`",
            'userCountries.`country` user_country',
            'companyCountries.`country` company_country',
            "{$userGroupsTable}.`gr_name`",
            "{$usersTable}.`user_group`",
            "{$companiesTable}.`name_company`",
            "{$companiesTable}.`type_company`",
            "{$companiesTable}.`index_name`",
            "{$companiesTable}.`id_company`",
            "{$companiesTable}.`logo_company`",
            "{$companiesTable}.`rating_count_company`",
            "{$companiesTable}.`rating_company`",
        ];

        if ($itemsInfo) {
            $columns[] = "COUNT({$productsTable}.`id`) active_listing";

            $queryBuilder->innerJoin(
                $usersTable,
                $productsTable,
                $productsTable,
                "{$usersTable}.`idu` = {$productsTable}.`id_seller`",
            );
        }

        $queryBuilder
            ->select(...$columns)
            ->from($usersTable)
            ->leftJoin(
                $usersTable,
                $userGroupsTable,
                $userGroupsTable,
                "{$usersTable}.`user_group` = {$userGroupsTable}.`idgroup`",
            )
            ->leftJoin(
                $usersTable,
                $companiesTable,
                $companiesTable,
                "{$usersTable}.`idu` = {$companiesTable}.`id_user` AND {$companiesTable}.`type_company` = 'company'",
            )
            ->leftJoin(
                $usersTable,
                $portCountryTable,
                'userCountries',
                "{$usersTable}.`country` = userCountries.`id`",
            )
            ->leftJoin(
                $companiesTable,
                $portCountryTable,
                'companyCountries',
                "{$companiesTable}.`id_country` = companyCountries.`id`",
            )
            ->andWhere(
                $queryBuilder->expr()->in("{$usersTable}.`idu`", array_map(
                    fn ($i, $userId) => $queryBuilder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter("userId{$i}")),
                    array_keys($usersIds),
                    $usersIds
                ))
            )
            ->groupBy("{$usersTable}.`idu`")
        ;

        return $queryBuilder->execute()->fetchAllAssociative();
    }

    /**
     * Prepare data for cron block_users().
     *
     * @return array $users
     */
    public function getUsersForBlockingAccount(): array
    {
        /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
        $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);

        $usersBlockingStatisticsTable = $usersBlockingStatisticsModel->getTable();
        $usersTable = $this->table;

        $queryBuilder = $this->createQueryBuilder();
        $subQueryBuilder = $this->createQueryBuilder();
        $blockingBuilder = $this->createQueryBuilder();

        $subQueryBuilder
            ->select(
                "{$usersBlockingStatisticsTable}.`id_user`",
                "{$usersBlockingStatisticsTable}.`blocking_date`",
                "{$usersBlockingStatisticsTable}.`cancel_date`",
                "ROW_NUMBER() over (PARTITION BY {$usersBlockingStatisticsTable}.`id_user` ORDER BY {$usersBlockingStatisticsTable}.`id` DESC) as blockingRank"
            )
            ->from($usersBlockingStatisticsTable)
            ->andWhere(
                $queryBuilder->expr()->eq(
                    "{$usersBlockingStatisticsTable}.`type`",
                    $queryBuilder->createNamedParameter((string) RestrictionType::RESTRICTION(), ParameterType::STRING, $this->nameScopeParameter('restrictionType_1'))
                )
            )
        ;

        $blockingBuilder
            ->select("{$usersBlockingStatisticsTable}.`id_user`")
            ->from($usersBlockingStatisticsTable)
            ->andWhere(
                $queryBuilder->expr()->eq(
                    "{$usersBlockingStatisticsTable}.`type`",
                    $queryBuilder->createNamedParameter((string) RestrictionType::BLOCKING(), ParameterType::STRING, $this->nameScopeParameter('restrictionType_2'))
                ),
                $queryBuilder->expr()->isNotNull("{$usersBlockingStatisticsTable}.`cancel_date`"),
            )
        ;

        $queryBuilder
            ->select(
                "{$usersTable}.`idu`",
                "{$usersTable}.`fname`",
                "{$usersTable}.`lname`",
                "{$usersTable}.`email`",
                "{$usersTable}.`status`",
                "{$usersTable}.`notice`",
                "{$usersTable}.`status_temp`"
            )
            ->from($usersTable)
            ->leftJoin($usersTable, "({$subQueryBuilder->getSQL()})", 'restrictionsLog', "{$usersTable}.`idu` = restrictionsLog.`id_user`")
            ->leftJoin($usersTable, "({$blockingBuilder->getSQL()})", 'blockingLog', "{$usersTable}.`idu` = blockingLog.`id_user`")
            ->andWhere(
                $queryBuilder->expr()->in(
                    "{$usersTable}.`status`",
                    [
                        $queryBuilder->createNamedParameter((string) UserStatus::FRESH(), ParameterType::STRING, $this->nameScopeParameter('userStatus1')),
                        $queryBuilder->createNamedParameter((string) UserStatus::PENDING(), ParameterType::STRING, $this->nameScopeParameter('userStatus2')),
                        $queryBuilder->createNamedParameter((string) UserStatus::RESTRICTED(), ParameterType::STRING, $this->nameScopeParameter('userStatus3')),
                    ]
                ),
                $queryBuilder->expr()->eq(
                    'restrictionsLog.`blockingRank`',
                    $queryBuilder->createNamedParameter(1, ParameterType::INTEGER, $this->nameScopeParameter('blockingRank')),
                ),
                $queryBuilder->expr()->isNull('blockingLog.`id_user`'),
                'DATE(DATE_ADD(IF(restrictionsLog.`cancel_date` IS NULL, restrictionsLog.`blocking_date`, restrictionsLog.`cancel_date`), INTERVAL 30 DAY)) <= CURRENT_DATE()'
            )
        ;

        return $this->restoreAttributesList($queryBuilder->execute()->fetchAllAssociative());
    }

    /**
     * Update one user by user id.
     *
     * @param int $userId
     */
    public function updateOne($userId, array $user): bool
    {
        $updatingResult = parent::updateOne($userId, $user);

        if ($updatingResult) {
            if (isset($user['fname'], $user['lname'])) {
                /** @var Elasticsearch_User_Model $elasticsearchUserModel */
                $elasticsearchUserModel = model(Elasticsearch_User_Model::class);

                $elasticsearchUserModel->update_other_models((int) $userId, (string) $user['fname'], (string) $user['lname']);
            }

            if (!empty(array_intersect_key($this->userColumnsExportedToCrm, $user))) {
                /** @var Crm_Model $crmModel */
                $crmModel = model(Crm_Model::class);

                $crmModel->create_or_update_record($userId);
            }
        }

        return $updatingResult;
    }

    /**
     * Update notice for one user.
     */
    public function setNotice(int $idUser, array $notice)
    {
        // Make the general query
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->update($this->table)
            ->set('notice', "CONCAT_WS(',', :noticeValue, notice)")
            ->setParameter('noticeValue', json_encode($notice))
            ->where('idu = :userId')
            ->setParameter('userId', $idUser)
            ->execute()
        ;
    }

    /**
     * Scope query for ID.
     */
    protected function scopeId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId', true))
            )
        );
    }

    /**
     * Scope query for Company id.
     */
    protected function scopeCompanyId(QueryBuilder $builder, int $companyId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`id_company`",
                $builder->createNamedParameter($companyId, ParameterType::INTEGER, $this->nameScopeParameter('companyId'))
            )
        );
    }

    /**
     * Scope query for activation Code.
     */
    protected function scopeActivationCode(QueryBuilder $builder, string $activationCode): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'activation_code',
                $builder->createNamedParameter($activationCode, ParameterType::STRING, $this->nameScopeParameter('activationCode'))
            )
        );
    }

    /**
     * Scope query for AccreditationToken.
     */
    protected function scopeAccreditationToken(QueryBuilder $builder, string $accreditationToken): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`accreditation_token`",
                $builder->createNamedParameter($accreditationToken, ParameterType::STRING, $this->nameScopeParameter('accreditationToken'))
            )
        );
    }

    /**
     * Scope query for AccreditationFiles.
     */
    protected function scopeAccreditationFiles(QueryBuilder $builder, string $accreditationFiles): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`accreditation_files`",
                $builder->createNamedParameter($accreditationFiles, ParameterType::STRING, $this->nameScopeParameter('accreditationFiles'))
            )
        );
    }

    /**
     * Scope query for Not ID.
     */
    protected function scopeNotId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId', true))
            )
        );
    }

    /**
     * Scope query for user IDs.
     */
    protected function scopeIds(QueryBuilder $builder, array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('idu', array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("userId{$i}", true)),
                array_keys($userIds),
                $userIds
            ))
        );
    }

    /**
     * Scope query for group except those ones.
     */
    protected function scopeNotIds(QueryBuilder $builder, array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn('idu', array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("userId{$i}", true)),
                array_keys($userIds),
                $userIds
            ))
        );
    }

    /**
     * Scope query for principal.
     */
    protected function scopePrincipal(QueryBuilder $builder, int $principalId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_principal',
                $builder->createNamedParameter($principalId, ParameterType::INTEGER, $this->nameScopeParameter('principalId', true))
            )
        );
    }

    /**
     * Scope query for principal except.
     */
    protected function scopeNotPrincipal(QueryBuilder $builder, int $principalId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                'id_principal',
                $builder->createNamedParameter($principalId, ParameterType::INTEGER, $this->nameScopeParameter('principalId', true))
            )
        );
    }

    /**
     * Scope query for status.
     */
    protected function scopeStatus(QueryBuilder $builder, UserStatus $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`status`",
                $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter('status', true))
            )
        );
    }

    /**
     * Scope query for excluding the status.
     */
    protected function scopeNotStatus(QueryBuilder $builder, UserStatus $status): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                "`{$this->table}`.`status`",
                $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter('status', true))
            )
        );
    }

    /**
     * Scope query for statuses.
     *
     * @param UserStatus[] $statuses
     */
    protected function scopeStatuses(QueryBuilder $builder, array $statuses): void
    {
        if (empty($statuses)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('status', array_map(
                fn (int $i, $status) => $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter("status{$i}", true)),
                array_keys($statuses),
                $statuses
            ))
        );
    }

    /**
     * Scope query for statuses.
     *
     * @param UserStatus[] $statuses
     */
    protected function scopeNotStatuses(QueryBuilder $builder, array $statuses): void
    {
        if (empty($statuses)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                "`{$this->table}`.`status`",
                array_map(
                    fn (int $i, $status) => $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter("notStatus_{$i}")),
                    array_keys($statuses),
                    $statuses
                )
            )
        );
    }

    /**
     * Scope query for group.
     */
    protected function scopeGroup(QueryBuilder $builder, int $groupId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'user_group',
                $builder->createNamedParameter($groupId, ParameterType::INTEGER, $this->nameScopeParameter('groupId'))
            )
        );
    }

    /**
     * Scope query for group except this one.
     */
    protected function scopeNotGroup(QueryBuilder $builder, int $groupId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                'user_group',
                $builder->createNamedParameter($groupId, ParameterType::INTEGER, $this->nameScopeParameter('groupId'))
            )
        );
    }

    /**
     * Scope query for groups.
     */
    protected function scopeGroups(QueryBuilder $builder, array $groups): void
    {
        if (empty($groups)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('user_group', array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("userGroupId{$i}", true)),
                array_keys($groups),
                $groups
            ))
        );
    }

    /**
     * Scope query for group except this one.
     */
    protected function scopeNotInGroups(QueryBuilder $builder, array $groups): void
    {
        if (empty($groups)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->notIn(
                "`{$this->table}`.`user_group`",
                array_map(
                    fn (int $i, $group) => $builder->createNamedParameter((int) $group, ParameterType::INTEGER, $this->nameScopeParameter("groupNotIn_{$i}")),
                    array_keys($groups),
                    $groups
                )
            )
        );
    }

    /**
     * Scope query for group except those ones.
     *
     * @deprecated
     *
     * @todo remove because not used 2022.10.11
     */
    protected function scopeNotGroups(QueryBuilder $builder, array $groups): void
    {
        $this->scopeNotInGroups($builder, $groups);
    }

    /**
     * Scope query for group types.
     */
    protected function scopeGroupTypes(QueryBuilder $builder, array $groupTypes): void
    {
        if (empty($groupTypes)) {
            return;
        }

        /** @var User_Groups_Model $userGroupsModel */
        $userGroupsModel = model(User_Groups_Model::class);

        $builder->andWhere(
            $builder->expr()->in(
                "`{$userGroupsModel->getTable()}`.`gr_type`",
                array_map(
                    fn (int $i, $groupType) => $builder->createNamedParameter((string) $groupType, ParameterType::STRING, $this->nameScopeParameter("groupTypes{$i}")),
                    array_keys($groupTypes),
                    $groupTypes
                )
            )
        );
    }

    /**
     * Scope query for email.
     */
    protected function scopeEmail(QueryBuilder $builder, string $email): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'email',
                $builder->createNamedParameter((string) $email, ParameterType::STRING, $this->nameScopeParameter('email', true))
            )
        );
    }

    /**
     * Scope query for not demo user status.
     */
    protected function scopeIsFake(QueryBuilder $builder, bool $fake): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'fake_user',
                $builder->createNamedParameter($fake, ParameterType::BOOLEAN, $this->nameScopeParameter('isFake', true))
            )
        );
    }

    /**
     * Scope for Is model.
     */
    protected function scopeIsModel(QueryBuilder $builder, bool $model): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`is_model`",
                $builder->createNamedParameter($model, ParameterType::BOOLEAN, $this->nameScopeParameter('isModel'))
            )
        );
    }

    /**
     * Scope query for real users.
     */
    protected function scopeIsRealUser(QueryBuilder $builder, bool $isRealUser): void
    {
        if ($isRealUser) {
            $builder->andWhere(
                $builder->expr()->and(
                    $builder->expr()->eq(
                        "`{$this->table}`.`fake_user`",
                        $builder->createNamedParameter(false, ParameterType::BOOLEAN, $this->nameScopeParameter('isFakeUser'))
                    ),
                    $builder->expr()->eq(
                        "`{$this->table}`.`is_model`",
                        $builder->createNamedParameter(false, ParameterType::BOOLEAN, $this->nameScopeParameter('isModelUser'))
                    )
                )
            );
        } else {
            $builder->andWhere(
                $builder->expr()->or(
                    $builder->expr()->eq(
                        "`{$this->table}`.`fake_user`",
                        $builder->createNamedParameter(true, ParameterType::BOOLEAN, $this->nameScopeParameter('isFakeUser'))
                    ),
                    $builder->expr()->eq(
                        "`{$this->table}`.`is_model`",
                        $builder->createNamedParameter(true, ParameterType::BOOLEAN, $this->nameScopeParameter('isModelUser'))
                    )
                )
            );
        }
    }

    /**
     * Scope query by verified situation.
     */
    protected function scopeIsVerified(QueryBuilder $builder, bool $isVerified): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`is_verified`",
                $builder->createNamedParameter($isVerified, ParameterType::BOOLEAN, $this->nameScopeParameter('isVerified'))
            )
        );
    }

    /**
     * Scope query by registration date.
     */
    protected function scopeRegistrationDate(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "DATE(`{$this->table}`.`registration_date`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('registrationDate'))
            )
        );
    }

    /**
     * Scope query by registration date lte.
     */
    protected function scopeRegistrationDateLte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`registration_date`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('registrationDateLte'))
            )
        );
    }

    /**
     * Scope query by registration date gte.
     */
    protected function scopeRegistrationDateGte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`registration_date`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('registrationDateGte'))
            )
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $this->appendSearchConditionsToQuery(
            $builder,
            $text,
            ['fname', 'lname', 'email'],
            ['fname', 'lname', 'email'],
        );
    }

    /**
     * Scope a query to filter by user name or company name.
     */
    protected function scopeNameOrCompanyName(QueryBuilder $builder, string $searchText): void
    {
        if (null === $searchText || '' === $searchText) {
            return;
        }

        $this->appendSearchConditionsToQuery($userSearchQuery = $this->createQueryBuilder(), $searchText, ['fname', 'lname'], ['fname', 'lname']);
        $this->scopeIds(
            $userIdsQuery = $this->createQueryBuilder(),
            \array_unique(
                \array_merge(
                    \array_column($this->getRelation('sellerCompany')->getRelated()->findAllBy(['scopes' => ['search' => $searchText, 'type' => CompanyType::COMPANY()]]), 'id_user'),
                    \array_column($this->getRelation('shipperCompany')->getRelated()->findAllBy(['scopes' => ['search' => $searchText]]), 'id_user')
                )
            )
        );

        /** @var mixed[] $parameters */
        $parameters = \array_merge($builder->getParameters(), $userSearchQuery->getParameters(), $userIdsQuery->getParameters());
        /** @var mixed[] $parameterTypes */
        $parameterTypes = \array_merge($builder->getParameterTypes(), $userSearchQuery->getParameterTypes(), $userIdsQuery->getParameterTypes());
        $builder
            ->setParameters($parameters, $parameterTypes)
            ->andwhere(
                $builder->expr()->or(
                    ...\array_filter([
                        $userSearchQuery->getQueryPart('where'),
                        $userIdsQuery->getQueryPart('where'),
                    ])
                )
            )
        ;
    }

    /**
     * Scope query by restriction date.
     */
    protected function scopeWasNotRestrictedAfterDate(QueryBuilder $builder, DateTimeInterface $date): void
    {
        /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
        $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);
        $usersBlockingStatisticsTable = $usersBlockingStatisticsModel->getTable();

        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->lt(
                    "DATE(`{$usersBlockingStatisticsTable}`.`blocking_date`)",
                    $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('restrictionDate'))
                ),
                $builder->expr()->isNull("{$usersBlockingStatisticsTable}.`blocking_date`")
            )
        );
    }

    /**
     * Scope query by accreditation files upload.
     */
    protected function scopeAccreditationFilesUploadDateGte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`accreditation_files_upload_date`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('accreditationFilesUploadDateGte'))
            )
        );
    }

    /**
     * Scope query by accreditation files upload.
     */
    protected function scopeAccreditationFilesUploadDateLte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`accreditation_files_upload_date`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('accreditationFilesUploadDateLte'))
            )
        );
    }

    /**
     * Scope query to filter users without active close requests.
     */
    protected function scopeWithoutActiveCancellationRequests(QueryBuilder $builder): void
    {
        /** @var User_Cancellation_Requests_Model $userCancellationModel */
        $userCancellationModel = model(User_Cancellation_Requests_Model::class);
        $userCancellationTable = $userCancellationModel->getTable();
        $usersTable = $this->table;

        $builder
            ->andWhere($builder->expr()->isNull("`{$userCancellationTable}`.`idreq`"))
            ->leftJoin(
                $usersTable,
                $userCancellationTable,
                $userCancellationTable,
                "`{$usersTable}`.`idu` = `{$userCancellationTable}`.`user` AND `{$userCancellationTable}`.`status` IN ('init', 'confirmed')"
            )
        ;
    }

    /**
     * Scope query by resend email date gte.
     */
    protected function scopeResendEmailDateGte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`resend_email_date`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('resendEmailDateGte'))
            )
        );
    }

    /**
     * Scope query by resend email date lte.
     */
    protected function scopeResendEmailDateLte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`resend_email_date`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('resendEmailDateLte'))
            )
        );
    }

    /**
     * Scope query by resend email date gte.
     */
    protected function scopeLastActivityDateGte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`last_active`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('lastActivityDateGte'))
            )
        );
    }

    /**
     * Scope query by resend email date lte.
     */
    protected function scopeLastActivityDateTimeLte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`last_active`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('lastActivityDateLte'))
            )
        );
    }

    /**
     * Scope query for verification upload progress.
     */
    protected function scopeVerificationProgress(QueryBuilder $builder, VerificationUploadProgress $status): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`verfication_upload_progress`",
                $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter('verificationProgress'))
            )
        );
    }

    /**
     * Scope query for verification upload progress.
     */
    protected function scopeHasCompletedLocation(QueryBuilder $builder, bool $hasCompletedLocation): void
    {
        if ($hasCompletedLocation) {
            $builder->andWhere(
                $builder->expr()->and(
                    $builder->expr()->isNotNull("`{$this->table}`.`country`"),
                    $builder->expr()->isNotNull("`{$this->table}`.`state`"),
                    $builder->expr()->isNotNull("`{$this->table}`.`city`"),
                    $builder->expr()->neq("`{$this->table}`.`country`", 0),
                    $builder->expr()->neq("`{$this->table}`.`state`", 0),
                    $builder->expr()->neq("`{$this->table}`.`city`", 0),
                )
            );
        } else {
            $builder->andWhere(
                $builder->expr()->or(
                    $builder->expr()->isNull("`{$this->table}`.`country`"),
                    $builder->expr()->isNull("`{$this->table}`.`state`"),
                    $builder->expr()->isNull("`{$this->table}`.`city`"),
                    $builder->expr()->eq("`{$this->table}`.`country`", 0),
                    $builder->expr()->eq("`{$this->table}`.`state`", 0),
                    $builder->expr()->eq("`{$this->table}`.`city`", 0),
                )
            );
        }
    }

    /**
     * Scope query by calling date gte.
     */
    protected function scopeCallingDateGte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`calling_date_last`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('callingDateGte'))
            )
        );
    }

    /**
     * Scope query by calling date lte.
     */
    protected function scopeCallingDateLte(QueryBuilder $builder, DateTimeInterface $date): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`calling_date_last`)",
                $builder->createNamedParameter($date->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('callingDateLte'))
            )
        );
    }

    /**
     * Scope query for not demo user status.
     */
    protected function scopeFromFocusCountry(QueryBuilder $builder, bool $fromFocusCountry): void
    {
        /** @var Countries_Model $countryModel */
        $countryModel = model(Countries_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "`{$countryModel->getTable()}`.`is_focus_country`",
                $builder->createNamedParameter($fromFocusCountry, ParameterType::BOOLEAN, $this->nameScopeParameter('usersFromFocusCountry'))
            )
        );
    }

    /**
     * Scope query for verification upload progress.
     */
    protected function scopeCallingStatus(QueryBuilder $builder, int $callingStatus): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`calling_status`",
                $builder->createNamedParameter($callingStatus, ParameterType::INTEGER, $this->nameScopeParameter('callingStatus'))
            )
        );
    }

    /**
     * Scope query for crm contact id.
     */
    protected function scopeCrmContactId(QueryBuilder $builder, int $crmContactId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`zoho_id_record`",
                $builder->createNamedParameter($crmContactId, ParameterType::INTEGER, $this->nameScopeParameter('crmContactId'))
            )
        );
    }

    /**
     * Scope query for email status.
     */
    protected function scopeEmailStatus(QueryBuilder $builder, EmailStatus $emailStatus): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`email_status`",
                $builder->createNamedParameter((string) $emailStatus, ParameterType::STRING, $this->nameScopeParameter('emailStatus'))
            )
        );
    }

    /**
     * Scope query for email status.
     */
    protected function scopeEmailStatusIn(QueryBuilder $builder, array $emailStatuses): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->table}`.`email_status`",
                array_map(
                    fn ($i, $status) => $builder->createNamedParameter((string) $status, ParameterType::STRING, $this->nameScopeParameter("emailStatus_{$i}")),
                    array_keys($emailStatuses),
                    $emailStatuses
                )
            )
        );
    }

    /**
     * Scope query for is logged.
     */
    protected function scopeIsLogged(QueryBuilder $builder, bool $isLogged): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`logged`",
                $builder->createNamedParameter((int) $isLogged, ParameterType::STRING, $this->nameScopeParameter('isLogged'))
            )
        );
    }

    /**
     * Scope query for country id.
     */
    protected function scopeCountryId(QueryBuilder $builder, int $countryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`country`",
                $builder->createNamedParameter($countryId, ParameterType::INTEGER, $this->nameScopeParameter('countryId'))
            )
        );
    }

    protected function scopeCountriesIds(QueryBuilder $builder, array $countries): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->table}`.`country`",
                array_map(
                    fn ($id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("userCountry_{$id}")),
                    $countries
                )
            )
        );
    }

    /**
     * Scope query for country id.
     */
    protected function scopeStateId(QueryBuilder $builder, int $state): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`state`",
                $builder->createNamedParameter($state, ParameterType::INTEGER, $this->nameScopeParameter('stateId'))
            )
        );
    }

    /**
     * Scope query for country id.
     */
    protected function scopeCityId(QueryBuilder $builder, int $city): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`city`",
                $builder->createNamedParameter($city, ParameterType::INTEGER, $this->nameScopeParameter('cityId'))
            )
        );
    }

    /**
     * Scope query for keywords.
     */
    protected function scopeKeywords(QueryBuilder $builder, string $keywords): void
    {
        $words = explode(' ', $keywords);
        foreach ($words as $index => $word) {
            if (strlen($word) > 3) {
                $builder->andWhere(
                    $builder->expr()->or(
                        $builder->expr()->like(
                            "`{$this->table}`.`fname`",
                            $builder->createNamedParameter("%{$word}%", ParameterType::STRING, $this->nameScopeParameter("keywordForFname{$index}"))
                        ),
                        $builder->expr()->like(
                            "`{$this->table}`.`lname`",
                            $builder->createNamedParameter("%{$word}%", ParameterType::STRING, $this->nameScopeParameter("keywordForLname{$index}"))
                        ),
                        $builder->expr()->like(
                            "`{$this->table}`.`email`",
                            $builder->createNamedParameter("%{$word}%", ParameterType::STRING, $this->nameScopeParameter("keywordForEmail{$index}"))
                        ),
                        $builder->expr()->like(
                            "CONCAT(`{$this->table}`.`phone_code`, `{$this->table}`.`phone`)",
                            $builder->createNamedParameter("%{$word}%", ParameterType::STRING, $this->nameScopeParameter("keywordForEmail{$index}"))
                        ),
                    )
                );
            }
        }
    }

    /**
     * Scope query by check_items_views_email_date.
     */
    protected function scopeItemsViewsEmailDateTimeLte(QueryBuilder $builder, DateTimeInterface $dateTime): void
    {
        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->isNull("`{$this->table}`.`check_items_views_email_date`"),
                $builder->expr()->lte(
                    "`{$this->table}`.`check_items_views_email_date`",
                    $builder->createNamedParameter($dateTime->format('Y-m-d H:i:s'), ParameterType::STRING, $this->nameScopeParameter('itemsViewsEmailDateTimeLte'))
                ),
            )
        );
    }

    /**
     * Scope query by activation_account_date.
     */
    protected function scopeActivationAccountDateTimeLte(QueryBuilder $builder, DateTimeInterface $dateTime): void
    {
        $builder->andWhere(
            $builder->expr()->isNotNull("`{$this->table}`.`activation_account_date`"),
            $builder->expr()->lte(
                "`{$this->table}`.`activation_account_date`",
                $builder->createNamedParameter($dateTime->format('Y-m-d H:i:s'), ParameterType::STRING, $this->nameScopeParameter('activationAccountDateTimeLte'))
            ),
        );
    }

    /**
     * Relation with the principal.
     */
    protected function principal(): RelationInterface
    {
        return $this->belongsTo(Principals_Model::class, 'id_principal')->enableNativeCast();
    }

    /**
     * Relation with the group.
     */
    protected function group(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'user_group')->enableNativeCast();
    }

    /**
     * Relation with the contacts.
     */
    protected function contacts(): RelationInterface
    {
        $relation = $this->hasMany(User_Contacts_Model::class, 'id_user');
        $relation->enableNativeCast();
        $realtedRepository = $relation->getRelated();
        $contactRelation = $realtedRepository->getRelation('contact');
        $contactRepository = $contactRelation->getRelated();
        $contactsTableAlias = sprintf('%s__contacts', $contactRepository->getTable());
        $realtedRepository->mergeCasts($contactRepository->getCasts());
        $queryBuilder = $relation->getQuery();
        $queryBuilder
            ->select($relation->getExistenceCompareKey(), "{$contactsTableAlias}.*")
            ->leftJoin(
                $relation->getRelated()->getTable(),
                $contactRepository->getTable(),
                $contactsTableAlias,
                "{$contactRelation->getQualifiedParentKey()} = {$contactsTableAlias}.{$contactRelation->getParentKey()}"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the followers.
     */
    protected function followers(): RelationInterface
    {
        $relation = $this->hasMany(User_Followers_Model::class, 'id_user');
        $relation->enableNativeCast();
        $realtedRepository = $relation->getRelated();
        $followedUserRelation = $realtedRepository->getRelation('user');
        $followedUserRepository = $followedUserRelation->getRelated();
        $followedUserTableAlias = sprintf('%s__followers', $followedUserRepository->getTable());
        $realtedRepository->mergeCasts($followedUserRepository->getCasts());
        $queryBuilder = $relation->getQuery();
        $queryBuilder
            ->select($relation->getExistenceCompareKey(), "{$followedUserTableAlias}.*")
            ->leftJoin(
                $relation->getRelated()->getTable(),
                $followedUserRepository->getTable(),
                $followedUserTableAlias,
                "{$followedUserRelation->getQualifiedParentKey()} = {$followedUserTableAlias}.{$followedUserRelation->getParentKey()}"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the followers.
     */
    protected function followings(): RelationInterface
    {
        $relation = $this->hasMany(User_Followers_Model::class, 'id_user_follower');
        $relation->enableNativeCast();
        $realtedrepository = $relation->getRelated();
        $followedUserRelation = $realtedrepository->getRelation('followedUser');
        $followedUserRepository = $followedUserRelation->getRelated();
        $followedUserTableAlias = sprintf('%s__followings', $followedUserRepository->getTable());
        $realtedrepository->mergeCasts($followedUserRepository->getCasts());
        $queryBuilder = $relation->getQuery();
        $queryBuilder
            ->select($relation->getExistenceCompareKey(), "{$followedUserTableAlias}.*")
            ->leftJoin(
                $relation->getRelated()->getTable(),
                $followedUserRepository->getTable(),
                $followedUserTableAlias,
                "{$followedUserRelation->getQualifiedParentKey()} = {$followedUserTableAlias}.{$followedUserRelation->getParentKey()}"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the buyer company.
     */
    protected function buyerCompany(): RelationInterface
    {
        return $this->hasOne(Buyer_Companies_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with the seller company.
     */
    protected function sellerCompany(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->hasOne(Seller_Companies_Model::class, 'id_user')->enableNativeCast();
        $query = $relation->getQuery();
        $query->andWhere(
            $query->expr()->eq(
                'type_company',
                $query->createNamedParameter((string) CompanyType::COMPANY(), Types::STRING, ':typeCompany')
            )
        );

        return $relation;
    }

    /**
     * Relation with the seller company branches.
     */
    protected function sellerCompanyBranches(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->hasMany(Seller_Companies_Model::class, 'id_user')->enableNativeCast();
        $query = $relation->getQuery();
        $query->andWhere(
            $query->expr()->eq(
                'type_company',
                $query->createNamedParameter((string) CompanyType::BRANCH(), Types::STRING, ':typeBranch')
            )
        );

        return $relation;
    }

    /**
     * Relation with the any seller company.
     */
    protected function anySellerCompany(): RelationInterface
    {
        return $this->hasMany(Seller_Companies_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with the shipper company.
     */
    protected function shipperCompany(): RelationInterface
    {
        return $this->hasOne(Shipper_Companies_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with the saved sellers.
     */
    protected function savedSellers(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->hasMany(User_Saved_Companies_Model::class, 'user_id')->enableNativeCast();
        $relatedRepository = $relation->getRelated();
        $sellerRelation = $relatedRepository->getRelation('company');
        $sellerRepository = $sellerRelation->getRelated();
        $sellerTableAlias = sprintf('%s__saved_sellers', $sellerRepository->getTable());
        $relatedRepository->mergeCasts($sellerRepository->getCasts());
        $queryBuilder = $relation->getQuery();
        $queryBuilder
            ->select($relation->getExistenceCompareKey(), "{$sellerTableAlias}.*")
            ->leftJoin(
                $relation->getRelated()->getTable(),
                $sellerRepository->getTable(),
                $sellerTableAlias,
                "{$sellerRelation->getQualifiedParentKey()} = {$sellerTableAlias}.{$sellerRelation->getParentKey()}"
            )
        ;

        return $relation;
    }

    /**
     * Relation with the sellers's products.
     */
    protected function products(): RelationInterface
    {
        return $this->hasMany(Products_Model::class, 'id_seller')->enableNativeCast();
    }

    /**
     * Relation with the buyer's product orders.
     */
    protected function buyerProductOrders(): RelationInterface
    {
        return $this->hasMany(Product_Orders_Model::class, 'id_buyer')->enableNativeCast();
    }

    /**
     * Relation with the sellers's product orders.
     */
    protected function sellerProductOrders(): RelationInterface
    {
        return $this->hasMany(Product_Orders_Model::class, 'id_seller')->enableNativeCast();
    }

    /**
     * Relation with the shipper's product orders.
     */
    protected function shipperProductOrders(): RelationInterface
    {
        $relation = $this->hasMany(Product_Orders_Model::class, 'id_shipper');
        $relation->enableNativeCast();
        $query = $relation->getQuery();
        $query->andWhere(
            $query->expr()->eq(
                'shipper_type',
                $query->createNamedParameter((string) ShipperType::SHIPPER(), ParameterType::STRING, ':shipperType')
            )
        );

        return $relation;
    }

    /**
     * Relation with the account limitation records.
     */
    protected function accountLimitationRecords(): RelationInterface
    {
        return $this->hasMany(Users_Blocking_Statistics_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with Users_Blocking_Statistics_Model with group.
     */
    protected function accountLimitationStatistics(): RelationInterface
    {
        $relation = $this->hasMany(Users_Blocking_Statistics_Model::class, 'id_user');
        $relation->enableNativeCast();
        $relatedRepository = $relation->getRelated();
        $relatedRepositoryTable = $relatedRepository->getTable();

        $query = $relation->getQuery();

        $query->select(
            "`{$relatedRepositoryTable}`.`id_user`",
            "`{$relatedRepositoryTable}`.`type`",
            'COUNT(*) AS `counter`',
        );

        $query->groupBy(["`{$relatedRepositoryTable}`.`id_user`", "`{$relatedRepositoryTable}`.`type`"]);

        return $relation;
    }

    /**
     * Relation with the matrix reference.
     */
    protected function matrixReference(): RelationInterface
    {
        return $this->hasOne(Matrix_Users_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with country.
     */
    protected function country(): RelationInterface
    {
        return $this->belongsTo(Countries_Model::class, 'country')->setName('locationCountry')->enableNativeCast();
    }

    /**
     * Relation with state/region.
     */
    protected function state(): RelationInterface
    {
        return $this->belongsTo(States_Model::class, 'state')->setName('locationState')->enableNativeCast();
    }

    /**
     * Relation with city.
     */
    protected function city(): RelationInterface
    {
        return $this->belongsTo(Cities_Model::class, 'city')->setName('locationCity')->enableNativeCast();
    }

    /**
     * Relation with phone code.
     */
    protected function phoneCode(): RelationInterface
    {
        return $this->hasOne(Phone_Codes_Model::class, 'id_code', 'phone_code_id')->setName('personalPhoneCode')->enableNativeCast();
    }

    /**
     * Relation with campings.
     */
    protected function userCampings(): RelationInterface
    {
        return $this->hasOne(Campaign_Model::class, 'campaign_alias', 'user_find_info')->setName('userCampings')->enableNativeCast();
    }

    /**
     * Relation with fax phone code.
     */
    protected function faxCode(): RelationInterface
    {
        return $this->hasOne(Phone_Codes_Model::class, 'id_code', 'fax_code_id')->setName('personalFaxCode')->enableNativeCast();
    }

    /**
     * Relation with verification documents.
     */
    protected function verificationDocuments(): RelationInterface
    {
        return $this->hasMany(Verification_Documents_Model::class, 'id_user');
    }

    /**
     * Relation with upgrade requests.
     */
    protected function upgradeRequests(): RelationInterface
    {
        return $this->hasMany(Upgrade_Requests_Model::class, 'id_user');
    }

    /**
     * Relation with upgrade requests.
     */
    protected function cancellationRequests(): RelationInterface
    {
        return $this->hasMany(User_Cancellation_Requests_Model::class, 'user');
    }

    /**
     * Relation with upgrade requests.
     */
    protected function cancellationRequestsStatus(): RelationInterface
    {
        $relation = $this->hasMany(User_Cancellation_Requests_Model::class, 'user');
        $relation->enableNativeCast();
        $relatedRepository = $relation->getRelated();
        $relatedRepositoryTable = $relatedRepository->getTable();

        $query = $relation->getQuery();

        $query->select(
            "`{$relatedRepositoryTable}`.`idreq`",
            "`{$relatedRepositoryTable}`.`user`",
            "`{$relatedRepositoryTable}`.`status`",
        );

        $query->andWhere(
            $query->expr()->in(
                "`{$relatedRepositoryTable}`.`status`",
                [
                    $query->createNamedParameter((string) CancellationRequestStatus::INIT(), ParameterType::STRING, $this->nameScopeParameter('cancellationRequestStatus')),
                ]
            )
        );

        return $relation;
    }

    /**
     * Relation with profile edit request.
     */
    protected function profileEditRequests(): RelationInterface
    {
        return $this->hasMany(Profile_Edit_Requests_Model::class, 'id_user');
    }

    /**
     * Relation with complete profile options.
     */
    protected function completeProfileOptions(): RelationInterface
    {
        return $this->hasMany(Users_Complete_Profile_Options_Model::class, 'id_user');
    }

    /**
     * Relation with the any seller company.
     */
    protected function itemsViewsNotifications(): RelationInterface
    {
        return $this->hasMany(Items_Views_Notifications_Model::class, 'user_id')->enableNativeCast();
    }

    /**
     * Relation with industries.
     */
    protected function industries(): RelationInterface
    {
        return $this->hasManyThrough(
            Categories_Model::class,
            User_Industries_Pivot_Model::class,
            'id_user',
            'category_id',
            $this->getPrimaryKey(),
            'id_industry'
        );
    }

    /**
     * Relation with additional rights.
     */
    protected function additionalRights(): RelationInterface
    {
        return $this->hasManyThrough(
            Rights_Model::class,
            User_Rights_Pivot_Model::class,
            'id_user',
            'idright',
            $this->getPrimaryKey(),
            'id_right'
        );
    }

    /**
     * Scope for join user groups table.
     */
    protected function bindUserGroups(QueryBuilder $builder): void
    {
        /** @var User_Groups_Model $userGroupsModel */
        $userGroupsModel = model(User_Groups_Model::class);

        $userGroupsTable = $userGroupsModel->getTable();
        $usersTable = $this->table;

        $builder
            ->leftJoin(
                $usersTable,
                $userGroupsTable,
                $userGroupsTable,
                "`{$usersTable}`.`user_group` = `{$userGroupsTable}`.`idgroup`"
            )
        ;
    }

    /**
     * Scope for join users calling statuses table.
     */
    protected function bindUserCallingStatuses(QueryBuilder $builder): void
    {
        /** @var Users_Calling_Statuses_Model $usersCallingStatusesModel */
        $usersCallingStatusesModel = model(Users_Calling_Statuses_Model::class);

        $usersCallingStatusesTable = $usersCallingStatusesModel->getTable();
        $usersTable = $this->table;

        $builder
            ->leftJoin(
                $usersTable,
                $usersCallingStatusesTable,
                $usersCallingStatusesTable,
                "`{$usersTable}`.`calling_status` = `{$usersCallingStatusesTable}`.`id_status`"
            )
        ;
    }

    /**
     * Left Join with Countries table.
     */
    protected function bindCountries(QueryBuilder $builder): void
    {
        /** @var Countries_Model $countryModel */
        $countryModel = model(Countries_Model::class);

        $usersTable = $this->table;
        $countriesTable = $countryModel->getTable();
        $builder->leftJoin(
            $usersTable,
            $countriesTable,
            $countriesTable,
            "`{$countriesTable}`.`id` = `{$usersTable}`.`country`"
        );
    }

    /**
     * Scope for join cities table.
     */
    protected function bindCities(QueryBuilder $builder): void
    {
        /** @var Cities_Model $citiesModel */
        $citiesModel = model(Cities_Model::class);

        $citiesTable = $citiesModel->getTable();
        $usersTable = $this->table;

        $builder
            ->leftJoin(
                $usersTable,
                $citiesTable,
                $citiesTable,
                "`{$usersTable}`.`city` = `{$citiesTable}`.`id`"
            )
        ;
    }

    /**
     * Scope for join with usersStatitcs.
     */
    protected function bindRestrictionUsersStatistics(QueryBuilder $builder): void
    {
        /** @var Users_Blocking_Statistics_Model $usersBlockingStatisticsModel */
        $usersBlockingStatisticsModel = model(Users_Blocking_Statistics_Model::class);

        $usersBlockingStatisticsTable = $usersBlockingStatisticsModel->getTable();
        $usersTable = $this->table;

        $restrictionType = RestrictionType::RESTRICTION();

        $builder
            ->leftJoin(
                $usersTable,
                $usersBlockingStatisticsTable,
                $usersBlockingStatisticsTable,
                "`{$usersBlockingStatisticsTable}`.`id_user` = `{$usersTable}`.`idu` AND `{$usersBlockingStatisticsTable}`.`type` = '{$restrictionType}'"
            )
        ;
    }

    /**
     * Relation with auth table.
     */
    protected function authContext(): RelationInterface
    {
        return $this->hasOne(Auth_Context_Model::class, 'id_principal', 'id_principal')
            ->enableNativeCast()
        ;
    }

    /**
     * Relation with users photo table.
     */
    protected function userPhotosList(): RelationInterface
    {
        return $this->hasMany(User_Photos_Model::class, 'id_user')
            ->enableNativeCast()
        ;
    }

    protected function scopeItemsTotal(QueryBuilder $builder, array $params = []): void
    {
        /** @var User_Statistics_Model $userStatisticsModel */
        $userStatisticsModel = $this->userStatistics()->getRelated();
        $userStatisticsTable = $userStatisticsModel->getTable();

        $conditions = [
            "`{$this->table}`.`idu` = `{$userStatisticsTable}`.`id_user`",
        ];

        if (!empty($params)) {
            if (!empty($params['gte'])) {
                $params['gte'] = (int) $params['gte'];
                $conditions[] = "`{$userStatisticsTable}`.`items_total` >= {$params['gte']}";
            }

            if (!empty($params['lte'])) {
                $params['lte'] = (int) $params['lte'];
                $conditions[] = "`{$userStatisticsTable}`.`items_total` <= {$params['lte']}";
            }
        }

        $builder
            ->innerJoin(
                $this->table,
                $userStatisticsTable,
                $userStatisticsTable,
                implode(' AND ', $conditions)
            )
        ;
    }

    /**
     * Scope by map coordinats.
     */
    protected function scopeGmapCoords(QueryBuilder $builder, array $coords = []): void
    {
        if (empty($coords)) {
            return;
        }

        $lngOperand = $coords['nelng'] < $coords['swlng'] ? 'or' : 'and';

        $builder->andWhere(
            $builder->expr()->{$lngOperand}(
                $builder->expr()->gte(
                    'user_city_lng',
                    $builder->createNamedParameter($coords['swlng'], ParameterType::STRING, $this->nameScopeParameter('swlng'))
                ),
                $builder->expr()->lte(
                    'user_city_lng',
                    $builder->createNamedParameter($coords['nelng'], ParameterType::STRING, $this->nameScopeParameter('nelng'))
                ),
            ),
            $builder->expr()->gte(
                'user_city_lat',
                $builder->createNamedParameter($coords['swlat'], ParameterType::STRING, $this->nameScopeParameter('swlat'))
            ),
            $builder->expr()->lte(
                'user_city_lat',
                $builder->createNamedParameter($coords['nelat'], ParameterType::STRING, $this->nameScopeParameter('nelat'))
            )
        );
    }

    /**
     * Scope for Is model.
     */
    protected function scopeUserIp(QueryBuilder $builder, string $ip): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`user_ip`",
                $builder->createNamedParameter($ip, ParameterType::STRING, $this->nameScopeParameter('userIp'))
            )
        );
    }

    /**
     * Scope for Is model.
     */
    protected function scopeUserFindType(QueryBuilder $builder, string $findType): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`user_find_type`",
                $builder->createNamedParameter($findType, ParameterType::STRING, $this->nameScopeParameter('userFindType'))
            )
        );
    }

    /**
     * Scope for user find info.
     */
    protected function scopeUserFindInfo(QueryBuilder $builder, string $findInfo): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`user_find_info`",
                $builder->createNamedParameter($findInfo, ParameterType::STRING, $this->nameScopeParameter('userFindInfo'))
            )
        );
    }

    /**
     * Scope for in crm.
     */
    protected function scopeOnCrm(QueryBuilder $builder, bool $onCrm): void
    {
        $method = 'isNull';
        if ($onCrm) {
            $method = 'isNotNull';
        }

        $builder->andWhere(
            $builder->expr()->{$method}(
                'zoho_id_record'
            )
        );
    }

    /**
     * Get relation with Custom locations.
     */
    protected function userLocation(): RelationInterface
    {
        return $this->hasMany(Custom_Locations_Model::class, 'id_principal', 'id_principal')
            ->enableNativeCast()
        ;
    }

    /**
     * Get relation with groups.
     */
    protected function userGroup(): RelationInterface
    {
        return $this->belongsTo(User_Groups_Model::class, 'user_group', 'idgroup')
            ->setName('userGroupData')
            ->enableNativeCast()
        ;
    }

    /**
     * Scope by restiction relation.
     */
    protected function scopeRestrictedDate(QueryBuilder $builder, array $dateTime = []): void
    {
        /** @var Users_Blocking_Statistics_Model $userBlockingModel */
        $userBlockingModel = $this->accountLimitationRecords()->getRelated();
        $userBlockingTable = $userBlockingModel->getTable();

        $blockingType = RestrictionType::RESTRICTION();

        $conditions = [
            "`{$this->table}`.`idu` = `{$userBlockingTable}`.`id_user`",
            "`{$userBlockingTable}`.`type` = {$blockingType} ",
        ];

        if (!empty($dateTime)) {
            if (!empty($dateTime['gte'])) {
                $dateTime['gte'] = $dateTime['gte']->format('Y-m-d H:i:s');
                $conditions[] = "`{$userBlockingTable}`.`blocking_date` >= {$dateTime['gte']}";
            }

            if (!empty($dateTime['lte'])) {
                $dateTime['lte'] = (int) $dateTime['lte'];
                $conditions[] = "`{$userBlockingTable}`.`blocking_date` <= {$dateTime['lte']}";
            }
        }

        $builder
            ->innerJoin(
                $this->table,
                $userBlockingTable,
                $userBlockingTable,
                implode(' AND ', $conditions)
            )
            ->groupBy("`{$userBlockingTable}`.`id_user`")
        ;
    }

    /**
     * Scope bby blocked relation.
     */
    protected function scopeBlockedDate(QueryBuilder $builder, array $dateTime = []): void
    {
        /** @var Users_Blocking_Statistics_Model $userBlockingModel */
        $userBlockingModel = $this->accountLimitationRecords()->getRelated();
        $userBlockingTable = $userBlockingModel->getTable();

        $blockingType = RestrictionType::BLOCKING();

        $conditions = [
            "`{$this->table}`.`idu` = `{$userBlockingTable}`.`id_user`",
            "`{$userBlockingTable}`.`type` = {$blockingType} ",
        ];

        if (!empty($dateTime)) {
            if (!empty($dateTime['gte'])) {
                $dateTime['gte'] = $dateTime['gte']->format('Y-m-d H:i:s');
                $conditions[] = "`{$userBlockingTable}`.`blocking_date` >= {$dateTime['gte']}";
            }

            if (!empty($dateTime['lte'])) {
                $dateTime['lte'] = (int) $dateTime['lte'];
                $conditions[] = "`{$userBlockingTable}`.`blocking_date` <= {$dateTime['lte']}";
            }
        }

        $builder
            ->innerJoin(
                $this->table,
                $userBlockingTable,
                $userBlockingTable,
                implode(' AND ', $conditions)
            )
        ;
    }

    /**
     * Scope search by item title.
     */
    protected function scopeByItem(QueryBuilder $builder, string $title): void
    {
        /** @var Products_Model $productsModel */
        $productsModel = $this->products()->getRelated();
        $productsTable = $productsModel->getTable();

        $subQueryBuilder = $this->createQueryBuilder();
        $subQueryBuilder
            ->select("DISTINCT(`{$productsTable}`.`id_seller`)")
            ->from("{$productsTable}")
            ->andWhere(
                $subQueryBuilder->expr()->like(
                    "`{$productsTable}`.`title`",
                    $subQueryBuilder->createNamedParameter(
                        $title,
                        ParameterType::STRING,
                        $this->nameScopeParameter('itemTile')
                    )
                )
            )
        ;

        $builder->andWhere(
            $builder->expr()->in(
                "{$this->table}.`idu`",
                "({$subQueryBuilder->getSQL()})"
            )
        );
    }

    /**
     * Scope by company.
     */
    protected function scopeByCompany(QueryBuilder $builder, string $name): void
    {
        /** @var Seller_Companies_Model $sellersCompaniesModel */
        $sellersCompaniesModel = $this->sellerCompany()->getRelated();
        $sellersCompaniesTable = $sellersCompaniesModel->getTable();

        $sellersSubQueryBuilder = $this->createQueryBuilder();
        $sellersSubQueryBuilder
            ->select("DISTINCT(`{$sellersCompaniesTable}`.`id_user`)")
            ->from("{$sellersCompaniesTable}")
            ->orWhere(
                $sellersSubQueryBuilder->expr()->like(
                    "`{$sellersCompaniesTable}`.`name_company`",
                    $builder->createNamedParameter(
                        "%{$name}%",
                        ParameterType::STRING,
                        $this->nameScopeParameter('sellerCompanyName')
                    )
                ),
                $sellersSubQueryBuilder->expr()->like(
                    "`{$sellersCompaniesTable}`.`legal_name_company`",
                    $builder->createNamedParameter(
                        "%{$name}%",
                        ParameterType::STRING,
                        $this->nameScopeParameter('sellerLegalCompanyName')
                    )
                )
            )
        ;

        /** @var Buyer_Companies_Model $buyersCompaniesModel */
        $buyersCompaniesModel = $this->buyerCompany()->getRelated();
        $buyersCompaniesTable = $buyersCompaniesModel->getTable();

        $buyersSubQueryBuilder = $this->createQueryBuilder();
        $buyersSubQueryBuilder
            ->select("DISTINCT(`{$buyersCompaniesTable}`.`id_user`)")
            ->from("{$buyersCompaniesTable}")
            ->orWhere(
                $buyersSubQueryBuilder->expr()->like(
                    "`{$buyersCompaniesTable}`.`company_name`",
                    $builder->createNamedParameter(
                        "%{$name}%",
                        ParameterType::STRING,
                        $this->nameScopeParameter('buyerCompanyName')
                    )
                )
            )
        ;

        /** @var Shipper_Companies_Model $shipersCompaniesModel */
        $shipersCompaniesModel = $this->shipperCompany()->getRelated();
        $shipersCompaniesTable = $shipersCompaniesModel->getTable();

        $shipersSubQueryBuilder = $this->createQueryBuilder();
        $shipersSubQueryBuilder
            ->select("DISTINCT(`{$shipersCompaniesTable}`.`id_user`)")
            ->from("{$shipersCompaniesTable}")
            ->orWhere(
                $shipersSubQueryBuilder->expr()->like(
                    "`{$shipersCompaniesTable}`.`co_name`",
                    $builder->createNamedParameter(
                        "%{$name}%",
                        ParameterType::STRING,
                        $this->nameScopeParameter('shiperCompanyName')
                    )
                )
            )
        ;

        $builder->andWhere(
            $builder->expr()->or(
                $builder->expr()->in(
                    "{$this->table}.`idu`",
                    "({$sellersSubQueryBuilder->getSQL()})"
                ),
                $builder->expr()->in(
                    "{$this->table}.`idu`",
                    "({$buyersSubQueryBuilder->getSQL()})"
                ),
                $builder->expr()->in(
                    "{$this->table}.`idu`",
                    "({$shipersSubQueryBuilder->getSQL()})"
                ),
            )
        );
    }

    // Scope a query to filter users by industries on interest
    protected function scopeIndustriesOfInterestIds(QueryBuilder $builder, array $industriesIds): void
    {
        $industriesRelationTable = $this->industriesOfInterest()->getRelated()->getTable();

        $subqueryBuilder = $this->createQueryBuilder();
        $subqueryBuilder
            ->select('*')
            ->from($industriesRelationTable)
            ->andWhere(
                $subqueryBuilder->expr()->eq(
                    "`{$this->table}`.`idu`",
                    "`{$industriesRelationTable}`.`idu`"
                )
            )
            ->andWhere(
                $builder->expr()->in(
                    "`{$industriesRelationTable}`.`id_category`",
                    array_map(
                        fn (int $i, $industryId) => $builder->createNamedParameter((int) $industryId, ParameterType::INTEGER, $this->nameScopeParameter("industryId{$i}")),
                        array_keys($industriesIds),
                        $industriesIds
                    )
                )
            )
        ;

        $builder->andWhere("EXISTS ({$subqueryBuilder->getSQL()})");
    }

    /**
     * Get relation with industries of interest.
     */
    protected function industriesOfInterest(): RelationInterface
    {
        return $this->hasMany(Buyer_Item_Categories_Stats_Model::class, 'idu', 'idu')->enableNativeCast();
    }

    /**
     * Relation with the principal.
     */
    protected function userStatistics(): RelationInterface
    {
        return $this->belongsTo(User_Statistics_Model::class, 'idu')->enableNativeCast();
    }

    /**
     * Dcope.
     */
    protected function scopeCancellationRequests(QueryBuilder $builder): void
    {
        /** @var User_Cancellation_Requests_Model $cancelationRequestsModel */
        $userCancelationRequestsModel = $this->cancellationRequests()->getRelated();
        $userCancelationRequestsTable = $userCancelationRequestsModel->getTable();

        $status = (string) CancellationRequestStatus::INIT();

        $builder
            ->innerJoin(
                $this->table,
                $userCancelationRequestsTable,
                $userCancelationRequestsTable,
                "`{$userCancelationRequestsTable}`.`user` = `{$this->table}`.`idu` AND `{$userCancelationRequestsTable}`.`status` = \"{$status}\""
            )
        ;
    }

    /**
     * Scope by continent id.
     */
    protected function scopeContinentId(QueryBuilder $builder, int $continentId): void
    {
        $countryRelationTable = $this->country()->getRelated()->getTable();
        $builder->andWhere(
            $builder->expr()->eq(
                "{$countryRelationTable}.`id_continent`",
                $builder->createNamedParameter($continentId, ParameterType::INTEGER, $this->nameScopeParameter('continentId'))
            )
        );
    }

    /**
     * Scope by is focus country.
     */
    protected function scopeIsFocusCountry(QueryBuilder $builder, bool $isFocusCountry): void
    {
        $countryRelation = $this->country()->getRelated();
        $countryRelationTable = $countryRelation->getTable();

        $builder->andWhere(
            $builder->expr()->eq(
                "{$countryRelationTable}.`is_focus_country`",
                $builder->createNamedParameter($isFocusCountry, ParameterType::BOOLEAN, $this->nameScopeParameter('isFocusCountry'))
            )
        );
    }
}
