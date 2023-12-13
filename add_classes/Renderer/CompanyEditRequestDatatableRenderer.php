<?php

declare(strict_types=1);

namespace App\Renderer;

use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Contracts\Media\CompanyLogoThumb;
use App\Filesystem\CompanyLogoFilePathGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * The service that renders the pages and/or popups for user company edit requests.
 *
 * @author Anton Zencenco
 */
final class CompanyEditRequestDatatableRenderer
{
    /**
     * The filesystem porvider.
     */
    private FilesystemProviderInterface $filesystemProvider;

    /**
     * @param FilesystemProviderInterface $filesystemProvider the filesystem
     */
    public function __construct(
        FilesystemProviderInterface $filesystemProvider
    ) {
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * Renders the response for admin grid.
     */
    public function adminGrid(array $paginator, int $drawNumber, bool $isLegacyMode = false): void
    {
        list(
            'all'   => $totalCount,
            'total' => $filteredCount,
            'data'  => $editRequestsList
        ) = $paginator;
        $editRequestsList = (new ArrayCollection($editRequestsList ?? []))->map(fn (array $entry) => $this->formatEditRequestListEntry($entry));

        \jsonResponse(
            null,
            'success',
            $isLegacyMode
                ? [
                    'sEcho'                => $drawNumber,
                    'aaData'               => $editRequestsList->toArray(),
                    'iTotalRecords'        => $totalCount ?? 0,
                    'iTotalDisplayRecords' => $filteredCount ?? 0,
                ]
                : [
                    'draw'            => $drawNumber,
                    'data'            => $editRequestsList->toArray(),
                    'recordsTotal'    => $totalCount ?? 0,
                    'recordsFiltered' => $filteredCount ?? 0,
                ]
        );
    }

    /**
     * Formats the entry for the type list.
     */
    private function formatEditRequestListEntry(array $editRequest): array
    {
        $editRequestId = $editRequest['id'];
        /** @var EditRequestStatus $status */
        $status = $editRequest['status'];
        $user = $editRequest['user'];
        $userId = $user['idu'];
        $company = $editRequest['company'];
        $companyId = $editRequest['id_company'];
        $userName = \cleanOutput($user['full_name']);

        //region User
        $userInformation = \sprintf(
            <<<USER_INFO
            <div class="tal">
                <a
                    class="ep-icon ep-icon_filter txt-green dt_filter"
                    data-value-text="{$userName}"
                    data-value="{$userId}"
                    data-title="User"
                    data-name="user"
                    title="Filter user's {$userName} requests"
                ></a>
                <a class="ep-icon ep-icon_user" href="%s" title="View personal page of {$userName}" target="_blank"></a>
                <a class="ep-icon ep-icon_envelope fancyboxValidateModal fancybox.ajax" href="%s" title="Email this user" data-title="Email user {$userName}"></a>
            </div>
            <div>{$userName}</div>
            USER_INFO,
            \getUserLink($user['full_name'], $userId, (string) $user['group_type']),
            \getUrlForGroup("contact/popup_forms/email_user/{$userId}"),
        );
        //endregion User

        //region Company
        $publicDisk = $this->filesystemProvider->storage('public.storage');
        $thumbImage = $publicDisk->url(CompanyLogoFilePathGenerator::thumbImage($companyId, $company['logo_company'], CompanyLogoThumb::SMALL()));
        $companyDisplayName = \cleanOutput($company['display_name']);
        $companyInformation = \sprintf(
            <<<COMPANY_INFO
                <div style="background:url('%s') no-repeat 0 center;background-size:70px auto;padding-left:80px;">
                    <div>
                        <a
                            class="ep-icon ep-icon_filter txt-green dt_filter"
                            data-value-text="{$companyDisplayName}"
                            data-value="{$companyId}"
                            data-title="Company"
                            data-name="company"
                            title="Filter company's &quot;{$companyDisplayName}&quot; requests"
                        ></a>
                        <a class="ep-icon ep-icon_building" title="View company page" href="%s" target="_blank"></a>
                        <a
                            href="%s"
                            title="View company details"
                            class="ep-icon ep-icon_visible fancybox.ajax fancybox"
                            data-title="Company &quot;{$companyDisplayName}&quot; details"
                        ></a>
                    </div>
                    <div class="clearfix"><strong class="pull-left lh-16 pr-5">Display Name: </strong>{$companyDisplayName}</div>
                    <div class="clearfix"><strong class="pull-left lh-16 pr-5">Legal Name: </strong>%s</div>
                    <div class="clearfix"><strong class="pull-left lh-16 pr-5">Company type: </strong>%s</div>
                </div
            COMPANY_INFO,
            $thumbImage,
            \getCompanyURL($company),
            \getUrlForGroup("/directory/popup_forms/company_details/{$userId}"),
            \cleanOutput($company['legal_name']),
            \cleanOutput($company['type_name']),
        );
        //endregion Company

        //region Status
        $statusInformation = \sprintf(
            <<<STATUS_INFO
            <div class="tal">
                <a
                    class="ep-icon ep-icon_filter txt-green dt_filter"
                    data-value-text="{$status->label()}"
                    data-value="{$status->value}"
                    data-title="Status"
                    data-name="status"
                    title="%s"
                ></a>
            </div>
            <div><span class="label %s">{$status->label()}</span></div>
            STATUS_INFO,
            \cleanOutput("Filter \"{$status->label()}\" requests"),
            \with($status, function (EditRequestStatus $status) {
                switch ($status) {
                    case EditRequestStatus::PENDING(): return 'label-warning';
                    case EditRequestStatus::ACCEPTED(): return 'label-success';
                    case EditRequestStatus::DECLINED(): return 'label-danger';
                }
            })
        );
        //endregion Status

        //region Buttons
        $buttons = [];

        //region Details button
        $buttons[] = \sprintf(
            <<<'BUTTON'
            <li class="mnw-200">
                <a href="%s" class="fancybox.ajax fancyboxValidateModalDT" title="Show request details" data-title="#%s request details">
                    <i class="ep-icon ep-icon_magnifier"></i> Details
                </a>
            </li>
            BUTTON,
            \getUrlForGroup("/company_edit_requests/popup_forms/details/{$editRequestId}"),
            \str_pad((string) $editRequestId, 11, '0', STR_PAD_LEFT)
        );
        //endregion Details button

        $actionsInformation = \sprintf(
            <<<'ACTION_INFO'
            <div class="dropdown">
                <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                    %s
                </ul>
            </div>
            ACTION_INFO,
            \implode('', $buttons)
        );
        //endregion Buttons

        return [
            'user'       => $userInformation,
            'status'     => $statusInformation,
            'company'    => $companyInformation,
            'reason'     => \cleanOutput($editRequest['reason']) ?: '%mdash',
            'request'    => \cleanOutput($editRequestId),
            'createdAt'  => \getDateFormatIfNotEmpty($editRequest['created_at_date']),
            'updatedAt'  => \getDateFormatIfNotEmpty($editRequest['updated_at_date']),
            'acceptedAt' => \getDateFormatIfNotEmpty($editRequest['accepted_at_date']),
            'declinedAt' => \getDateFormatIfNotEmpty($editRequest['declined_at_date']),
            'actions'    => $actionsInformation,
        ];
    }
}
