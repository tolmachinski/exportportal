<?php

declare(strict_types=1);

namespace App\Renderer;

use App\Common\Contracts\EditRequest\EditRequestStatus;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * The service that renders the pages and/or popups for user profile edit requests.
 *
 * @author Anton Zencenco
 */
final class ProfileEditRequestDatatableRenderer
{
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

        jsonResponse(
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
        $userName = cleanOutput($user['full_name']);

        //region User
        $userInformation = sprintf(
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
            getUserLink($user['full_name'], $userId, (string) $user['group_type']),
            getUrlForGroup("contact/popup_forms/email_user/{$userId}"),
        );
        //endregion User

        //region Status
        $statusInformation = sprintf(
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
            cleanOutput("Filter \"{$status->label()}\" requests"),
            with($status, function (EditRequestStatus $status) {
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
        $buttons[] = sprintf(
            <<<'BUTTON'
            <li class="mnw-200">
                <a href="%s" class="fancybox.ajax fancyboxValidateModalDT" title="Show request details" data-title="#%s request details">
                    <i class="ep-icon ep-icon_magnifier"></i> Details
                </a>
            </li>
            BUTTON,
            getUrlForGroup("/profile_edit_requests/popup_forms/details/{$editRequestId}"),
            str_pad((string) $editRequestId, 11, '0', STR_PAD_LEFT)
        );
        //endregion Details button

        $actionsInformation = sprintf(
            <<<'ACTION_INFO'
            <div class="dropdown">
                <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                    %s
                </ul>
            </div>
            ACTION_INFO,
            implode('', $buttons)
        );
        //endregion Buttons

        return [
            'user'       => $userInformation,
            'status'     => $statusInformation,
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
