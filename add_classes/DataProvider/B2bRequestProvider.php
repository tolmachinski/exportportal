<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Contracts\B2B\B2bRequestStatus;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\B2bRequestNotFoundException;

/**
 * The b2b data provider service.
 *
 * @author Bendiucov Tatiana
 */
final class B2bRequestProvider
{
    /**
     * The requests repository.
     */
    private Model $b2bRepository;

    /**
     * The partners repository.
     */
    private Model $b2bPartnersRepository;

    /**
     * The users repository.
     */
    private Model $usersRepository;

    /**
     * Create the b2b data provider service class.
     *
     * @param Model $b2bRepository the b2b model
     */
    public function __construct(Model $b2bRepository, Model $b2bPartnersRepository, Model $usersRepository)
    {
        $this->b2bRepository = $b2bRepository;
        $this->b2bPartnersRepository = $b2bPartnersRepository;
        $this->usersRepository = $usersRepository;
    }

    /**
     * Get the b2b repository.
     */
    public function getRepository(): Model
    {
        return $this->b2bRepository;
    }

    /**
     * Get b2b request.
     *
     * @throws B2bRequestNotFoundException
     */
    public function getRequest(?int $b2bRequestId): array
    {
        if (
            empty($b2bRequestId)
            || null === ($request = $this->b2bRepository->findOne($b2bRequestId))
        ) {
            throw new B2bRequestNotFoundException();
        }

        return $request;
    }

    /**
     * Get b2b request with relation tables data.
     *
     * @throws B2bRequestNotFoundException
     */
    public function getRequestWithRelationData(?int $b2bRequestId): array
    {
        if (
            empty($b2bRequestId)
            || null === ($request = $this->b2bRepository->findOne($b2bRequestId, [
                'with' => ['industries', 'categories', 'countries', 'photos'],
            ]))
        ) {
            throw new B2bRequestNotFoundException();
        }

        return $request;
    }

    /**
     * Get b2b request with full information.
     *
     * @throws B2bRequestNotFoundException
     */
    public function getRequestDetailsFullData(?int $b2bRequestId): array
    {
        $partnersTypesTable = $this->b2bPartnersRepository->getTable();
        $usersTable = $this->usersRepository->getTable();

        if (
            empty($b2bRequestId)
            || null === ($request = $this->b2bRepository->findOne($b2bRequestId, [
                'columns' => [
                    "{$this->b2bRepository->getTable()}.*",
                    "{$partnersTypesTable}.`name` as p_type",
                    "{$usersTable}.`lname`",
                    "{$usersTable}.`fname`",
                ],
                'joins' => ['partnersTypes', 'users'],
                'with' => [
                    'industries',
                    'categories',
                    'countries',
                    'photos',
                    'company',
                    'advice' => function (RelationInterface $relation) {
                        $relation
                            ->getQuery()
                            ->setMaxResults((int) config('b2b_detail_page_advice_per_page', 3))
                            ->orderBy('date_advice', 'desc')
                        ;
                    },
                    'followers' => function (RelationInterface $relation) {
                        $relation
                            ->getQuery()
                            ->setMaxResults((int) config('b2b_detail_page_followers_per_page', 8))
                            ->orderBy('date_follow', 'desc')
                        ;
                    }, ],
            ]))
        ) {
            throw new B2bRequestNotFoundException();
        }
        $request['industries'] = null === $request['industries'] ? [] : array_column($request['industries']->toArray(), null, 'category_id');
        $request['categories'] = null === $request['categories'] ? [] : array_column($request['categories']->toArray(), null, 'category_id');
        $request['countries'] = null === $request['countries'] ? [] : array_column($request['countries']->toArray(), null, 'id');
        $request['followers'] = null === $request['followers'] ? [] : array_column($request['followers']->toArray(), null, 'id_follower');
        $request['photos'] = null === $request['photos'] ? [] : array_column($request['photos']->toArray(), null, 'id');
        $request['advice'] = null === $request['advice'] ? [] : array_column($request['advice']->toArray(), null, 'id_advice');

        return $request;
    }

    public function getOtherRequestsThan(int $b2bRequestId, int $sellerId, ?int $limit = null, ?int $skip = null): array
    {
        if (
            empty($b2bRequestId)
            || empty($sellerId)
            || null === ($requests = $this->b2bRepository->findAllBy([
                'scopes' => [
                    'notRequest' => $b2bRequestId,
                    'userId'     => $sellerId,
                    'status'     => B2bRequestStatus::ENABLED,
                ],
                'with'   => [
                    'company',
                    'countries',
                    'photos' => function (RelationInterface $relation) {
                        $relation
                            ->getQuery()
                            ->where('is_main', 1)
                        ;
                    },
                ],
                'order'  => ['b2b_date_register' => 'desc'],
                'limit'  => $limit,
                'skip'   => $skip
            ]))
        ) {
            return [];
        }

        return array_map(
            function ($request) {
                $request['countries'] = null === $request['countries'] ? [] : array_column($request['countries']->toArray(), null, 'id');
                $request['mainImage'] = null === $request['photos'] ? [] : array_pop($request['photos']->toArray());

                return $request;
            },
            $requests
        );
    }

    public function getCountOtherRequestsThan(int $b2bRequestId, int $sellerId): int
    {
        if (empty($b2bRequestId) || empty($sellerId)) {
            return 0;
        }

        return $this->b2bRepository->countAllBy([
            'scopes' => [
                'notRequest' => $b2bRequestId,
                'userId'     => $sellerId,
                'status'     => B2bRequestStatus::ENABLED,
            ]
        ]);
    }
}
