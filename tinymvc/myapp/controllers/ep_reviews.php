<?php

declare(strict_types=1);

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Email\EpReviewThanks;
use App\Validators\EpReviewValidator;
use App\Common\Contracts\Company\CompanyType;
use App\Common\Contracts\User\UserStatus;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Ep_reviews_Controller extends TinyMVC_Controller
{
    private $userStatusesLabels = [
        'restricted'    => 'label-warning',
        'pending'       => 'label-default',
        'blocked'       => 'label-danger',
        'deleted'       => 'label-danger',
        'active'        => 'label-success',
    ];

    public function administration(): void
    {
        checkPermision('ep_reviews_administration');

        views(
            [
                'admin/header_view',
                'admin/ep_reviews/index_view',
                'admin/footer_view'
            ],
            [
                'title' => 'User reviews',
            ]
        );
    }

    public function ajax_dt_administration(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('ep_reviews_administration');

        $request = request()->request;

        /** @var Ep_Reviews_Model $epReviewsModel */
        $epReviewsModel = model(Ep_Reviews_Model::class);
        $epReviewsTable = $epReviewsModel->getTable();

        $joins = [];
        $conditions = dtConditions($request->all(), [
            ['as' => 'userId',              'key' => 'user',            'type' => 'int'],
            ['as' => 'userStatus',          'key' => 'user_status',     'type' => 'string'],
            ['as' => 'isModerated',         'key' => 'moderated',       'type' => 'int'],
            ['as' => 'isPublished',         'key' => 'published',       'type' => 'int'],
            ['as' => 'addedFromDate',       'key' => 'added_from',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'addedToDate',         'key' => 'added_to',        'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'publishedFromDate',   'key' => 'published_from',  'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'publishedToDate',     'key' => 'published_to',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        ]);

        if (!empty($conditions['userStatus'])) {
            $joins[] = 'users';
        }

        $order = array_column(dtOrdering($request->all(), [
            'writingDate'       => "`{$epReviewsTable}`.`added_date`",
            'publishingDate'    => "`{$epReviewsTable}`.`published_date`",
        ]), 'direction', 'column');

        $queryParams = [
            'conditions'    => $conditions,
            'with'          => ['user'],
            'joins'         => $joins,
            'order'         => $order,
            'limit'         => abs($request->getInt('iDisplayLength')),
            'skip'          => abs($request->getInt('iDisplayStart')),
        ];

        $epReviews = $epReviewsModel->findAllBy($queryParams);
        $epReviewsCount = $epReviewsModel->countBy(array_intersect_key($queryParams, ['conditions' => '', 'joins' => '']));

        $output = [
            'iTotalDisplayRecords'  => $epReviewsCount,
            'iTotalRecords'         => $epReviewsCount,
            'aaData'                => [],
            'sEcho'                 => request()->request->getInt('sEcho'),
        ];

        if (empty($epReviews)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($epReviews as $epReview) {
            $userName = $epReview['user']['fname'] . ' ' . $epReview['user']['lname'];

            if ($epReview['is_moderated']) {
                $moderateReviewBtn = <<<MODERATE
                    <a class="ep-icon ep-icon_ok txt-green call-systmess"
                        data-message="Once moderated review, can't be un-moderated"
                        data-type="info"
                        title="Moderated"
                    ></a>
                MODERATE;

                if ($epReview['is_published']) {
                    $publishReviewBtn = <<<PUBLISH
                        <a class="ep-icon ep-icon_ok txt-green confirm-dialog"
                            data-callback="toglePublishStatus"
                            data-message="Are you sure you want to un-publish this review?"
                            title="Unpublish review"
                            data-id="{$epReview['id']}"
                        ></a>
                    PUBLISH;
                } else {
                    $publishReviewBtn = <<<PUBLISH
                        <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                            data-callback="toglePublishStatus"
                            data-message="Are you sure you want to publish this review?"
                            title="Publish review"
                            data-id="{$epReview['id']}"
                        ></a>
                    PUBLISH;
                }
            } else {
                $moderateReviewBtn = <<<MODERATE
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="moderateReview"
                        data-message="Are you sure you want to moderate this review?"
                        title="Moderate review"
                        data-id="{$epReview['id']}"
                    ></a>
                MODERATE;

                $publishReviewBtn = <<<PUBLISH
                    <a class="ep-icon ep-icon_remove txt-red call-systmess"
                        data-message="You must first moderate this review."
                        data-type="warning"
                        title="Publish review"
                    ></a>
                PUBLISH;
            }

            $editReviewUrl = __SITE_URL . 'ep_reviews/popup_forms/edit_review/' . $epReview['id'];
            $userStatusWithLabel = "<span class=\"label {$this->userStatusesLabels[$epReview['user']['status']->value]}\">{$epReview['user']['status']->value}</span>";

            $output['aaData'][] = [
                'publishingDate'    => null === $epReview['published_date'] ? '&mdash;' : $epReview['published_date']->format('j M, Y H:i'),
                'isPublished'       => $publishReviewBtn,
                'isModerated'       => $moderateReviewBtn,
                'writingDate'       => null === $epReview['added_date'] ? '&mdash;' : $epReview['added_date']->format('j M, Y H:i'),
                'message'           => cleanOutput($epReview['message']),
                'user'              => '<a href="' . getUserLink($userName, $epReview['user']['idu'], strtolower($epReview['user']['gr_type'])) . '" target="_blank">' . $userName . '</a><br>' . $userStatusWithLabel,
                'id'                => $epReview['id'],
                'actions'           => <<<ACTIONS
                    <div class="dropdown">
                        <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                            <li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="{$editReviewUrl}" title="Edit" data-title="Edit review #{$epReview['id']}">
                                    <span class="ep-icon ep-icon_pencil"></span> Edit a review
                                </a>
                            </li>
                            <li>
                                <a class="confirm-dialog" data-callback="deleteReview" data-message="Are you sure you want to delete this review?" title="Delete a review" data-id="{$epReview['id']}">
                                    <span class="ep-icon ep-icon_remove txt-red"></span> Delete a review
                                </a>
                            </li>
                        </ul>
                    </div>
                ACTIONS,
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms(): void
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_review':
                checkPermisionAjaxModal('write_ep_reviews');

                /** @var Users_Model $usersModel */
                $usersModel = model(Users_Model::class);

                $userAccounts = $usersModel->findAllBy([
                    'with'          => [
                        'group'
                    ],
                    'conditions'    => [
                        'principal' => principal_id(),
                        'notStatus' => UserStatus::DELETED(),
                    ],
                    'columns'       => [
                        'idu',
                        'fname',
                        'lname',
                        'user_group',
                        'user_photo',
                    ],
                ]);

                $companies = $sellersIds = [];
                foreach ($userAccounts as $userAccount) {
                    if (strtolower($userAccount['group']['gr_type']->value) == 'seller') {
                        $sellersIds[] = $userAccount['idu'];
                    }
                }

                if (!empty($sellersIds)) {
                    /** @var Seller_Companies_Model $sellerCompaniesModel */
                    $sellerCompaniesModel = model(Seller_Companies_Model::class);

                    $companies = $sellerCompaniesModel->findAllBy([
                        'conditions' => [
                            'type'      => CompanyType::COMPANY(),
                            'usersIds'  => $sellersIds,
                        ],
                        'columns'   => [
                            'name_company',
                            'id_user',
                        ],
                    ]);
                }

                views(
                    'new/ep_reviews/popups/review_form_view',
                    [
                        'userAccounts' => $userAccounts,
                        'companies'    => array_column($companies, null, 'id_user'),
                    ]
                );

                break;
            case 'edit_review':
                checkPermisionAjaxModal('ep_reviews_administration');

                /** @var Ep_Reviews_Model $epReviewsModel */
                $epReviewsModel = model(Ep_Reviews_Model::class);

                if (
                    empty($reviewId = (int) uri()->segment(4))
                    || empty($review = $epReviewsModel->findOne($reviewId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                views(
                    'admin/ep_reviews/edit_form_view',
                    [
                        'review' => [
                            'message'   => $review['message'],
                            'id'        => $reviewId,
                        ],
                    ],
                );
                break;
        }
    }

    public function ajax_operations(): void
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_review':
                checkPermisionAjax('write_ep_reviews');
                $request = request()->request;

                $authorId = $request->getInt('user');
                if (empty($authorId)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $authorEmail = email_session();
                $authorName = user_name_session();

                if (id_session() !== $authorId) {
                    /** @var Users_Model $usersModel */
                    $usersModel = model(Users_Model::class);

                    $userAccounts = $usersModel->findAllBy([
                        'conditions'    => [
                            'principal' => principal_id(),
                            'notStatus' => UserStatus::DELETED(),
                        ],
                        'columns'       => [
                            'idu',
                            'fname',
                            'lname',
                            'email',
                        ],
                    ]);

                    $userAccountsById = array_column($userAccounts, null, 'idu');

                    if (!isset($userAccountsById[$authorId])) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    $authorEmail = $userAccountsById[$authorId]['email'];
                    $authorName = trim($userAccountsById[$authorId]['fname'] . ' ' . $userAccountsById[$authorId]['lname']);
                }

                $validator = new EpReviewValidator(new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)));
                if (!$validator->validate($request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                /** @var Ep_Reviews_Model $epReviewsModel */
                $epReviewsModel = model(Ep_Reviews_Model::class);

                $epReviewsModel->insertOne([
                    'id_user' => $authorId,
                    'message'   => $request->get('message'),
                ]);

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new EpReviewThanks($authorName))
                        ->to(new RefAddress((string) $authorId, new Address($authorEmail)))
                );

                jsonResponse(translate('systmess_success_ep_review_add_review'), 'success');

                break;
            case 'edit_review':
                checkPermisionAjax('ep_reviews_administration');
                $request = request()->request;

                $validator = new EpReviewValidator(new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)));
                if (!$validator->validate($request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                if (empty($reviewId = $request->getInt('id'))) {
                    jsonResponse(translate('systmess_error_ep_reviews_id_was_expected'));
                }

                /** @var Ep_Reviews_Model $epReviewsModel */
                $epReviewsModel = model(Ep_Reviews_Model::class);

                if (empty($review = $epReviewsModel->findOne($reviewId))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $epReviewsModel->updateOne(
                    $reviewId,
                    [
                        'message'       => $request->get('message'),
                        'updated_date'  => new \DateTimeImmutable(),
                    ]
                );

                jsonResponse(translate('systmess_success_ep_review_add_review'), 'success');

                break;
            case 'togle_published_status':
                checkPermisionAjax('ep_reviews_administration');

                if (empty($reviewId = request()->request->getInt('review'))) {
                    jsonResponse(translate('systmess_error_ep_reviews_id_was_expected'));
                }

                /** @var Ep_Reviews_Model $epReviewsModel */
                $epReviewsModel = model(Ep_Reviews_Model::class);

                if (empty($review = $epReviewsModel->findOne($reviewId, ['with' => ['user']]))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if ($review['is_published']) {
                    $reviewUpdates = [
                        'is_published' => 0
                    ];
                } else {
                    if (!$review['is_moderated']) {
                        jsonResponse(translate('systmess_error_ep_reviews_cannot_publish_not_moderated_review'), 'warning');
                    }

                    if ('deleted' === $review['user']['status']->value) {
                        jsonResponse(translate('systmess_error_ep_reviews_cannot_publish_deleted_account_review'), 'warning');
                    }

                    $reviewUpdates = [
                        'is_published'      => 1,
                        'published_date'    => new \DateTimeImmutable(),
                    ];
                }

                $epReviewsModel->updateOne($reviewId, $reviewUpdates);

                jsonResponse(
                    $review['is_published'] ? translate('systmess_success_ep_reviews_unpublish_review') : translate('systmess_success_ep_reviews_publish_review'),
                    'success'
                );
                break;

            case 'moderate_review':
                checkPermisionAjax('ep_reviews_administration');

                if (empty($reviewId = request()->request->getInt('review'))) {
                    jsonResponse(translate('systmess_error_ep_reviews_id_was_expected'));
                }

                /** @var Ep_Reviews_Model $epReviewsModel */
                $epReviewsModel = model(Ep_Reviews_Model::class);

                if (empty($review = $epReviewsModel->findOne($reviewId)) || $review['is_moderated']) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $epReviewsModel->updateOne($reviewId, ['is_moderated' => 1]);

                jsonResponse(translate('systmess_success_ep_reviews_moderate_review'), 'success');

                break;
            case 'delete_review':
                checkPermisionAjax('ep_reviews_administration');

                if (empty($reviewId = request()->request->getInt('review'))) {
                    jsonResponse(translate('systmess_error_ep_reviews_id_was_expected'));
                }

                /** @var Ep_Reviews_Model $epReviewsModel */
                $epReviewsModel = model(Ep_Reviews_Model::class);

                if (empty($review = $epReviewsModel->findOne($reviewId))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $epReviewsModel->deleteOne($reviewId);

                jsonResponse(translate('systmess_success_ep_reviews_delete_review'), 'success');

                break;
        }
    }
}

// End of file ep_reviews.php
// Location: /tinymvc/myapp/controllers/ep_reviews.php
