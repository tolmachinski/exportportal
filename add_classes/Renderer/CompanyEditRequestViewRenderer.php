<?php

declare(strict_types=1);

namespace App\Renderer;

use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Exceptions\AccessDeniedException;
use App\DataProvider\CompanyEditRequestProvider;
use App\DataProvider\CompanyProvider;
use App\Services\EditRequest\CompanyEditRequestProcessingService;
use Doctrine\Common\Collections\ArrayCollection;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use TinyMVC_View as Renderer;
use const App\Common\PUBLIC_DATETIME_FORMAT;

/**
 * The service that renders the pages and/or popups for user company edit requests.
 *
 * @author Anton Zencenco
 */
final class CompanyEditRequestViewRenderer extends AbstractViewRenderer
{
    use PhoneFormatterTrait;

    /**
     * The company edit requesty processing service instance.
     */
    private CompanyEditRequestProcessingService $processingService;

    /**
     * The data provider for company edit requsts.
     */
    private CompanyEditRequestProvider $editRequestProvider;

    /**
     * The data provider for company.
     */
    private CompanyProvider $companyProvider;

    /**
     * @param CompanyEditRequestProcessingService $processingService   the company edit requesty processing service instance
     * @param CompanyEditRequestProvider          $editRequestProvider the data provider for company edit requsts
     * @param CompanyProvider                     $companyProvider     the data provider for company
     */
    public function __construct(
        Renderer $renderer,
        CompanyEditRequestProcessingService $processingService,
        CompanyEditRequestProvider $editRequestProvider,
        CompanyProvider $companyProvider
    ) {
        parent::__construct($renderer);

        $this->editRequestProvider = $editRequestProvider;
        $this->processingService = $processingService;
        $this->companyProvider = $companyProvider;
    }

    /**
     * Render details popup.
     */
    public function detailsPopup(?int $editRequestId): void
    {
        $editRequest = $this->editRequestProvider->getDetailedRequest($editRequestId);
        $documents = ($editRequest['documents'] ?? new ArrayCollection())->getValues();
        $userProfile = $editRequest['user'] ?? null;
        if (null === $userProfile) {
            throw new \InvalidArgumentException('The edit request instance must contain the user subresource.');
        }
        $company = $this->companyProvider->getDetailedCompany($editRequest['id_company']);

        $this->render('admin/company_edit_requests/details_view', array_merge(
            $this->makeUserPreviewData($userProfile, $editRequest),
            $this->makeCompanyPreviewData($company, $editRequest),
            [
                'userId'           => (int) $editRequest['id_user'],
                'request'          => str_pad((string) $editRequestId, 11, '0', STR_PAD_LEFT),
                'acceptUrl'        => getUrlForGroup("/company_edit_requests/ajax_operations/accept/{$editRequestId}"),
                'declineUrl'       => getUrlForGroup("/company_edit_requests/popup_forms/decline/{$editRequestId}"),
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

        $this->render('admin/company_edit_requests/decline_form_view', [
            'request'    => str_pad($editRequestId = (string) $editRequest['id'], 11, '0', STR_PAD_LEFT),
            'backUrl'    => getUrlForGroup("/company_edit_requests/popup_forms/details/{$editRequestId}"),
            'declineUrl' => getUrlForGroup("/company_edit_requests/ajax_operations/decline/{$editRequestId}"),
        ]);
    }

    /**
     * Makes the data diff display information for edit request.
     */
    private function makeEditRequestDataDiff(array $editRequest, array $company): array
    {
        $rawDiffData = [
            'Type'         => [$editRequest['type']['name_type'] ?? null, $company['type']['name_type'] ?? null],
            'Legal name'   => [$editRequest['legal_name'], $company['legal_name_company']],
            'Display name' => [$editRequest['display_name'], $company['name_company']],
            'Country'      => [$editRequest['country']['country'] ?? null, $company['country']['country'] ?? null],
            'State'        => [$editRequest['state']['state'] ?? null, $company['state']['state'] ?? null],
            'City'         => [$editRequest['city']['city'] ?? null, $company['city']['city'] ?? null],
            'Phone'        => [
                $this->parseRawPhoneNumber($editRequest['phone_code']['ccode'] ?? $editRequest['phone_code_inline'] ?? null, $editRequest['phone']),
                $this->parseRawPhoneNumber($company['stored_phone_code']['ccode'] ?? $company['phone_code_company'] ?? null, $company['phone_company']),
            ],
            'Fax'          => [
                $this->parseRawPhoneNumber($editRequest['fax_code']['ccode'] ?? $editRequest['fax_code_inline'] ?? null, $editRequest['fax']),
                $this->parseRawPhoneNumber($company['stored_fax_code']['ccode'] ?? $company['fax_code_company'] ?? null, $company['fax_company']),
            ],
            'Address'     => [$editRequest['address'], $company['address_company']],
            'Postal code' => [$editRequest['postal_code'], $company['zip_company']],
            'Latitude'    => [$editRequest['latitude'], $company['latitude']],
            'Longitude'   => [$editRequest['longitude'], $company['longitude']],
        ];

        $diffData = [];
        foreach ($rawDiffData as $key => list($new, $current)) {
            $new = $new instanceof PhoneNumber ? PhoneNumberUtil::getInstance()->format($new, PhoneNumberFormat::INTERNATIONAL) : $new;
            $current = $current instanceof PhoneNumber ? PhoneNumberUtil::getInstance()->format($current, PhoneNumberFormat::INTERNATIONAL) : $current;
            if ($current === $new) {
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
                'downloadUrl'         => getUrlForGroup("/company_edit_requests/ajax_operations/download/{$document['id']}"),
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
        ];
    }

    /**
     * Makes the preview information for profile edit request users.
     */
    private function makeCompanyPreviewData(array $company, array $editRequest): array
    {
        return [
            'legalName'   => $company['legal_name'],
            'displayName' => $company['display_name'],
            'companyDiff' => $this->makeEditRequestDataDiff($editRequest, $company),
            'companyUrl'  => \getCompanyURL([
                'index_name'   => $company['index_name'] ?: null,
                'type_company' => (string) $company['type_company'],
                'name_company' => $company['display_name'],
                'id_company'   => $company['id_company'],
            ]),
        ];
    }
}
