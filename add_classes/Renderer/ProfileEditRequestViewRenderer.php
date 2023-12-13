<?php

declare(strict_types=1);

namespace App\Renderer;

use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Exceptions\AccessDeniedException;
use App\DataProvider\ProfileEditRequestProvider;
use App\DataProvider\UserProfileProvider;
use App\Services\EditRequest\ProfileEditRequestProcessingService;
use Doctrine\Common\Collections\ArrayCollection;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use TinyMVC_View as Renderer;
use const App\Common\PUBLIC_DATETIME_FORMAT;

/**
 * The service that renders the pages and/or popups for user profile edit requests.
 *
 * @author Anton Zencenco
 */
final class ProfileEditRequestViewRenderer extends AbstractViewRenderer
{
    use PhoneFormatterTrait;

    /**
     * The company edit requesty processing service instance.
     */
    private ProfileEditRequestProcessingService $processingService;

    /**
     * The data provider for company edit requsts.
     */
    private ProfileEditRequestProvider $editRequestProvider;

    /**
     * The data provider for company.
     */
    private UserProfileProvider $profileProvider;

    /**
     * @param ProfileEditRequestProcessingService $processingService   the processing service for profile edit requests
     * @param ProfileEditRequestProvider          $editRequestProvider the data provider for profile edit requsts
     * @param UserProfileProvider                 $profileProvider     the data provider for profile
     */
    public function __construct(
        Renderer $renderer,
        ProfileEditRequestProcessingService $processingService,
        ProfileEditRequestProvider $editRequestProvider,
        UserProfileProvider $profileProvider
    ) {
        parent::__construct($renderer);

        $this->editRequestProvider = $editRequestProvider;
        $this->processingService = $processingService;
        $this->profileProvider = $profileProvider;
    }

    /**
     * Render details popup.
     */
    public function detailsPopup(?int $editRequestId): void
    {
        $editRequest = $this->editRequestProvider->getDetailedRequest($editRequestId);
        $documents = ($editRequest['documents'] ?? new ArrayCollection())->getValues();
        $userProfile = $this->profileProvider->getDetailedProfile($editRequest['id_user']);

        $this->render('admin/profile_edit_requests/details_view', array_merge(
            $this->makeUserPreviewData($userProfile, $editRequest),
            [
                'userId'           => (int) $editRequest['id_user'],
                'request'          => str_pad((string) $editRequestId, 11, '0', STR_PAD_LEFT),
                'acceptUrl'        => getUrlForGroup("/profile_edit_requests/ajax_operations/accept/{$editRequestId}"),
                'declineUrl'       => getUrlForGroup("/profile_edit_requests/popup_forms/decline/{$editRequestId}"),
                'requestDate'      => null === $editRequest['created_at_date'] ? null : $editRequest['created_at_date']->format(PUBLIC_DATETIME_FORMAT),
                'declineDate'      => null === $editRequest['declined_at_date'] ? null : $editRequest['declined_at_date']->format(PUBLIC_DATETIME_FORMAT),
                'declineReason'    => $editRequest['decline_reason'],
                'hasOtherRequests' => !$this->processingService->canAcceptRequest($userProfile['idu']),
                'isCompleted'      => EditRequestStatus::ACCEPTED() === $editRequest['status'],
                'isDeclined'       => EditRequestStatus::DECLINED() === $editRequest['status'],
                'isPending'        => EditRequestStatus::PENDING() === $editRequest['status'] || null === $editRequest['status'],
                'documents'        => $this->makeEditRequestDocumentsList($documents),
                'status'           => [
                    'label' => EditRequestStatus::getLabel($editRequest['status']),
                    'color' => with($editRequest['status'], function (EditRequestStatus $status) {
                        switch ($status) {
                            case EditRequestStatus::PENDING(): return 'label-warning';
                            case EditRequestStatus::ACCEPTED(): return 'label-success';
                            case EditRequestStatus::DECLINED(): return 'label-danger';
                        }
                    }),
                ],
            ]
        ));
    }

    /**
     * Render decline popup.
     */
    public function declinePopup(?int $editRequestId): void
    {
        $editRequest = $this->editRequestProvider->getDecliningRequest($editRequestId);
        // We need to check if profile edit request is in satus PENDING
        // If not, then we cannot decline request.
        if ($editRequest['status'] !== EditRequestStatus::PENDING()) {
            throw new AccessDeniedException(
                sprintf('Only requests in status "%s" can be declined', (string) EditRequestStatus::PENDING()),
                11
            );
        }

        $this->render('admin/profile_edit_requests/decline_form_view', [
            'request'    => str_pad((string) $editRequestId, 11, '0', STR_PAD_LEFT),
            'backUrl'    => getUrlForGroup("/profile_edit_requests/popup_forms/details/{$editRequestId}"),
            'declineUrl' => getUrlForGroup("/profile_edit_requests/ajax_operations/decline/{$editRequestId}"),
        ]);
    }

    /**
     * Makes the data diff display information for edit request.
     */
    private function makeEditRequestDataDiff(array $editRequest, array $user): array
    {
        $rawDiffData = [
            'Country'     => [$editRequest['country']['country'] ?? null, $user['location_country']['country'] ?? null],
            'State'       => [$editRequest['state']['state'] ?? null, $user['location_state']['state'] ?? null],
            'City'        => [$editRequest['city']['city'] ?? null, $user['location_city']['city'] ?? null],
            'First name'  => [$editRequest['first_name'], $user['fname']],
            'Last name'   => [$editRequest['last_name'], $user['lname']],
            'Legal name'  => [$editRequest['legal_name'], $user['legal_name']],
            'Phone'       => [
                $this->parseRawPhoneNumber($editRequest['phone_code']['ccode'] ?? $editRequest['phone_code_inline'] ?? null, $editRequest['phone']),
                $this->parseRawPhoneNumber($user['stored_phone_code']['ccode'] ?? $user['phone_code'] ?? null, $user['phone']),
            ],
            'Fax'         => [
                $this->parseRawPhoneNumber($editRequest['fax_code']['ccode'] ?? $editRequest['fax_code_inline'] ?? null, $editRequest['fax']),
                $this->parseRawPhoneNumber($user['stored_fax_code']['ccode'] ?? $user['fax_code'] ?? null, $user['fax']),
            ],
            'Address'     => [$editRequest['address'], $user['address']],
            'Postal code' => [$editRequest['postal_code'], $user['zip']],
        ];

        $diffData = [];
        foreach ($rawDiffData as $key => list($new, $current)) {
            $new = $new instanceof PhoneNumber ? PhoneNumberUtil::getInstance()->format($new, PhoneNumberFormat::INTERNATIONAL) : $new;
            $current = $current instanceof PhoneNumber ? PhoneNumberUtil::getInstance()->format($current, PhoneNumberFormat::INTERNATIONAL) : $current;
            if (($current ?: null) === ($new ?: null)) {
                continue;
            }

            $diffData[$key] = [$current, $new];
        }

        return $diffData;
    }

    /**
     * Makes the display information for the list of edit request documents.
     */
    private function makeEditRequestDocumentsList(array $documents): array
    {
        $displayDocuments = [];
        foreach ($documents as $document) {
            $displayDocuments[] = [
                'title'               => $document['type']['document_title'] ?? $document['internal_name'],
                'enabled'             => $document['is_processed'],
                'downloadUrl'         => getUrlForGroup("/profile_edit_requests/ajax_operations/download/{$document['id']}"),
                'downloadOriginalUrl' => getUrlForGroup('/personal_documents/ajax_operation/download_document'),
                'originalDocument'    => $document['id_document'],
            ];
        }

        return $displayDocuments;
    }

    /**
     * Makes the preview information for profile edit request users.
     */
    private function makeUserPreviewData(array $user, array $editRequest): array
    {
        return [
            'userName'   => $userName = trim("{$user['fname']} {$user['lname']}"),
            'profileUrl' => getUserLink($userName, $user['idu'], (string) $user['group']['gr_type']),
            'userDiff'   => $this->makeEditRequestDataDiff($editRequest, $user),
        ];
    }
}
