<?php

declare(strict_types=1);

namespace App\DataProvider\User;

use Users_Model;
use DateTimeImmutable;
use App\Common\Database\Model;
use App\Common\Contracts\User\UserStatus;
use App\Common\Contracts\Group\GroupAlias;
use App\Common\Contracts\User\EmailStatus;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\HttpFoundation\InputBag;

final class UserDataListProvider
{
    private Users_Model $usersModel;

    private string $usersTable;

    public array $emailStatusesLabels = [
        EmailStatus::OK      => 'success',
        EmailStatus::UNKNOWN => 'warning',
        EmailStatus::BAD     => 'danger',
    ];

    public function __construct(Model $usersModel)
    {
        $this->usersModel = $usersModel;

        $this->usersTable = $this->usersModel->getTable();
    }

    /**
     * Get data list.
     */
    public function getDataList(InputBag $request, bool $byGroup = false): ?array
    {
        $scopes = $this->getScopesConditions($request);
        $with = $this->getWithConditions();

        $orderBy = array_column(
            dtOrdering(
                $request->all(),
                [
                    'dt_idu'                => "`{$this->usersTable}`.`idu`",
                    'dt_fullname'           => "CONCAT(`{$this->usersTable}`.`fname`, `{$this->usersTable}`.`lname`)",
                    'dt_email'              => "`{$this->usersTable}`.`email`",
                    'dt_registered'         => "`{$this->usersTable}`.`registration_date`",
                    'dt_resend_email_date'  => "`{$this->usersTable}`.`resend_email_date`",
                    'dt_activity'           => "`{$this->usersTable}`.`last_active`",
                    'dt_reset_pass_date'    => "`{$this->usersTable}`.`reset_password_date`",
                ]
            ),
            'direction',
            'column',
        );

        $params = [
            'with'      => $with,
            'scopes'    => $scopes,
            'order'     => $orderBy,
            'limit'     => $request->getInt('iDisplayLength'),
            'skip'      => $request->getInt('iDisplayStart'),
        ];

        if ($request->has('continent') || $request->has('focus_countries')) {
            $params['exists'] = [
                $this->usersModel->getRelationsRuleBuilder()->whereHas(
                    'country',
                    function (QueryBuilder $query, RelationInterface $relation) use ($request) {
                        $relation->getRelated()->getScope('continentId')($query, $request->getInt('continent'));
                    }
                ),
            ];
        }

        if ($byGroup) {
            if (isset($params['scopes']['groups'])) {
                unset($params['scopes']['groups']);
            }

            unset($params['order'], $params['limit'], $params['skip']);

            $params['columns'] = [
                "`{$this->usersTable}`.`user_group`",
                "COUNT(*) as counter",
            ];

            $params['group'] = [
                "`{$this->usersTable}`.`user_group`",
            ];
        }

        return $this->usersModel->findAllBy($params);
    }

    /**
     * Get data count.
     */
    public function getDataCount(InputBag $request): int
    {
        $scopes = $this->getScopesConditions($request);
        $with = $this->getWithConditions();

        if ($request->has('continent') || $request->has('focus_countries')) {
            $exists = [
                $this->usersModel->getRelationsRuleBuilder()->whereHas(
                    'country',
                    function (QueryBuilder $query, RelationInterface $relation) use ($request) {
                        $relation->getRelated()->getScope('continentId')($query, $request->getInt('continent'));
                    }
                ),
            ];
        }

        return $this->usersModel->countAllBy([
            'with'      => $with,
            'scopes'    => $scopes,
            'exists'    => $exists ?: []
        ]);
    }

    /**
     * Generate list of $with conditions.
     */
    private function getWithConditions(): array
    {
        return [
            'city',
            'state',
            'userPhotosList',
            'phoneCode',
            'userCampings',
            'userLocation',
            'shipperCompany',
            'buyerCompany',
            'sellerCompany',
            'userGroup',
            'country',
            'accountLimitationStatistics',
            'cancellationRequestsStatus',
        ];
    }

    /**
     * Generate list of $scopes conditions.
     */
    private function getScopesConditions(InputBag $request): array
    {
        $gmapScope = [];

        if (
            null !== $request->get('swlat')
            && null !== $request->get('swlng')
            && null !== $request->get('nelat')
            && null !== $request->get('nelng')
        ) {
            $gmapScope = [
                'as'   => 'gmapCoords',
                'key'  => 'swlat',
                'type' => fn () => [
                    'swlat' => $request->get('swlat'),
                    'swlng' => $request->get('swlng'),
                    'nelat' => $request->get('nelat'),
                    'nelng' => $request->get('nelng'),
                ],
            ];
        }

        return \dtConditions($request->all(), array_filter(
            [
                ['as' => 'id', 'key' => 'id_user', 'type' => 'int'],
                ['as' => 'status', 'key' => 'status', 'type' => fn ($userStatus) => UserStatus::tryFrom($userStatus)],
                ['as' => 'emailStatus', 'key' => 'email_status', 'type' => fn ($emailStatus) => EmailStatus::tryFrom($emailStatus)],
                ['as' => 'isLogged', 'key' => 'online', 'type' => 'bool'],
                ['as' => 'hasCompletedLocation', 'key' => 'location_completion', 'type' => 'bool'],
                ['as' => 'isFake', 'key' => 'fake_user', 'type' => 'bool'],
                ['as' => 'isModel', 'key' => 'is_model', 'type' => 'bool'],
                ['as' => 'isVerified', 'key' => 'is_verified', 'type' => 'bool'],
                ['as' => 'isFocusCountry', 'key' => 'focus_countries', 'type' => 'bool'],
                ['as' => 'countryId', 'key' => 'country', 'type' => 'int'],
                ['as' => 'stateId', 'key' => 'state', 'type' => 'int'],
                ['as' => 'cityId', 'key' => 'city', 'type' => 'int'],
                ['as' => 'userIp', 'key' => 'ip', 'type' => 'string'],
                [
                    'as'   => 'registrationDateGte',
                    'key'  => 'reg_date_from',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as'   => 'registrationDateLte',
                    'key'  => 'reg_date_to',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as'   => 'accreditationFilesUploadDateGte',
                    'key'  => 'document_upload_date_from',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as'   => 'accreditationFilesUploadDateLte',
                    'key'  => 'document_upload_date_to',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as'   => 'resendEmailDateGte',
                    'key'  => 'resend_date_from',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as'   => 'resendEmailDateLte',
                    'key'  => 'resend_date_to',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as'   => 'lastActivityDateGte',
                    'key'  => 'activity_date_from',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                [
                    'as'   => 'lastActivityDateLte',
                    'key'  => 'activity_date_to',
                    'type' => fn ($v) => validateDate($v, 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $v) : null,
                ],
                ['as' => 'restrictedDate', 'key' => 'restricted_from', 'nullable' => true,  'type' => function ($dateFrom) use ($request) {
                    return array_filter([
                        'gte'   => validateDate($dateFrom, 'm/d/Y') ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateFrom) : null,
                        'lte'   => validateDate($request->get('restricted_to'), 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $request->get('restricted_to')) : null,
                    ]) ?: null;
                }],
                ['as' => 'blockedDate', 'key' => 'blocked_from', 'nullable' => true,  'type' => function ($dateFrom) use ($request) {
                    return array_filter([
                        'gte'   => validateDate($dateFrom, 'm/d/Y') ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateFrom) : null,
                        'lte'   => validateDate($request->get('statistic_items_total_to'), 'm/d/Y') ? DateTimeImmutable::createFromFormat('m/d/Y', $request->get('restricted_to')) : null,
                    ]) ?: null;
                }],
                ['as' => 'onCrm', 'key' => 'in_crm', 'type' => 'bool'],
                ['as' => 'crmContactId', 'key' => 'crm_contact_id', 'type' => 'int'],
                ['as' => 'byItem', 'key' => 'search_by_item', 'type' => 'string'],
                ['as' => 'byCompany', 'key' => 'search_by_company', 'type' => 'string'],
                ['as' => 'keywords', 'key' => 'search', 'type' => 'string'],
                ['as' => 'itemsTotal', 'key' => 'statistic_items_total_from', 'nullable' => true,  'type' => function ($countFrom) use ($request) {
                    return array_filter([
                        'gte'   => (int) $countFrom,
                        'lte'   => (int) $request->get('statistic_items_total_to'),
                    ]) ?: null;
                }],
                ['as' => 'userFindType', 'key' => 'reg_info', 'type' => 'string|trim'],
                ['as' => 'userFindInfo', 'key' => 'campaign', 'type' => fn ($findInfo) => null !== $request->get('reg_info') && !empty($findInfo) ? (string) $findInfo : null],
                ['as' => 'userFindInfo', 'key' => 'brand_ambassador', 'type' => fn ($findInfo) => null !== $request->get('reg_info') && !empty($findInfo) ? (string) $findInfo : null],
                ['as' => 'industriesOfInterestIds', 'key' => 'industry', 'type' => fn ($listIndustries) => null === $listIndustries ? null : (array_filter(explode(',', $listIndustries)) ?: null)],
                ['as' => 'countriesIds', 'key' => 'country', 'type' => fn ($listCountries) => null === $listCountries ? null : (array_filter(explode(',', $listCountries)) ?: null)],
                ['as' => 'groups', 'nullable' => true, 'key' => 'group', 'type' => fn ($groupId) => isset($groupId) ? [(int) $groupId] : GroupAlias::getGroupsIdsByAliases(
                    GroupAlias::BUYER(),
                    GroupAlias::VERIFIED_SELLER(),
                    GroupAlias::CERTIFIED_SELLER(),
                    GroupAlias::VERIFIED_MANUFACTURER(),
                    GroupAlias::CERTIFIED_MANUFACTURER(),
                    GroupAlias::SHIPPER()
                )],
                $gmapScope,
            ]
        ));
    }
}
