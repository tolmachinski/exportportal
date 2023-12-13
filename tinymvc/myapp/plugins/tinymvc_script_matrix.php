<?php

declare(strict_types=1);

use App\Bridge\Matrix\MatrixConnector;
use App\DataProvider\MatrixUserProvider;
use ExportPortal\Contracts\Chat\Recource\ResourceOptions;
use ExportPortal\Contracts\Chat\Recource\ResourceType;

if (!function_exists('contactUserButton')) {
    /**
     * Show contact user button.
     * NOTE: this function is not finished and must be used on the later stages of the matrix chat development!!!
     * DO NOT USE IT!
     *
     * @todo Finish it
     */
    function contactUserButton(
        ?int $recipientId,
        ?ResourceOptions $option = null,
        ?string $text = null,
        ?string $icon = null,
        ?array $attributes = [],
        ?string $tag = 'button',
        bool $disabled = false
    ): ?string {
        $userId = (int) privileged_user_id();
        if (
            null === $recipientId
            || $recipientId === $userId
            || !logged_in()
            || !have_right('manage_messages')
            || \userGroupType()->isAdministration()
        ) {
            return null;
        }

        $tag = $tag ?? 'button';
        $option = $option ?? (new ResourceOptions())->type(ResourceType::from(ResourceType::USER));
        $classNames = ['call-action', $attributes['class'] ?? 'dropdown-item'];
        $baseAttributes = [
            'class'              => '',
            'title'              => $attributes['title'] ?? \translate('chat_button_generic_text', null, true),
            'type'               => 'button' === $tag ? 'button' : null,
            'data-title'         => $attributes['data-title'] ?? $attributes['title'] ?? \translate('chat_button_generic_title', null, true),
            'data-js-action'     => 'chat:open-contact-popup',
            'data-recipient'     => $recipientId,
            'data-resource-type' => null !== $option->getType() ? (string) $option->getType() : null,
            'data-resource-id'   => $option->getId(),
        ];
        if ($disabled || 'active' !== user_status()) {
            if ('button' === $tag) {
                $baseAttributes['disabled'] = null;
            } else {
                $classNames[] = 'disabled';
            }
        }
        $buttonAttributes = array_filter(array_merge($attributes ?? [], $baseAttributes, ['class' => implode(' ', $classNames)]));

        return sprintf(
            "<{$tag} %s>%s%s</{$tag}>",
            implode(' ', array_map(
                fn ($key, $value) => null === $value ? $key : sprintf('%s="%s"', $key, cleanOutput((string) $value)),
                array_keys($buttonAttributes),
                $buttonAttributes
            )),
            $icon ?? '<i class="ep-icon ep-icon_chat-stroke"></i>',
            null !== $text ? sprintf('<span class="txt">%s</span>', $text) : ''
        );
    }
}

if (!function_exists('matrixChatEnabled')) {
    /**
     * Determine if matrix chat is enabled.
     */
    function matrixChatEnabled(): bool
    {
        // Check if matrix chat is enabled/disabled
        if (!\filter_var(config('enable_matrix_chat'), \FILTER_VALIDATE_BOOLEAN)) {
            if (
                // If chat lock bypass for matrix is on
                !\filter_var(config('matrix_lock_bypass'), \FILTER_VALIDATE_BOOLEAN)
                // Or there is no trsuted IPs
                || empty($trustedIps = explode(',', config('matrix_lock_bypass_trusted_ips') ?: ''))
                // Or we don't know at least one client IP
                || empty($clientIps = request()->getClientIps())
            ) {
                // Then we can say that chat is diasbled
                return false;
            }

            // After that we check if at least one of the trsuted IPs icorresponds to the one of the client IPs
            return \count(\array_intersect($clientIps, $trustedIps)) > 0;
        }

        return true;
    }
}

if (!function_exists('matrixChatAccessibleForCurrentUser')) {
    /**
     * Determine if matrix chat is accessible for current user.
     */
    function matrixChatAccessibleForCurrentUser(): bool
    {
        return matrixChatEnabled() && logged_in() && \have_right('manage_messages') && !userStatus()->isLimited() && null !== currentUserMatrixCredentials();
    }
}

if (!function_exists('matrixChatHiddenForCurrentUser')) {
    /**
     * Determine if matrix chat is hidden for current user.
     */
    function matrixChatHiddenForCurrentUser(): bool
    {
        return !matrixChatAccessibleForCurrentUser() ? true : userStatus() !== \App\Common\Contracts\User\UserStatus::ACTIVE();
    }
}

if (!function_exists('currentUserMatrixCredentials')) {
    /**
     * Returns matrix credentials for current user.
     */
    function currentUserMatrixCredentials(): ?array
    {
        return container()->get(MatrixUserProvider::class)->userCredentials((int) id_session());
    }
}

if (!function_exists('userMatrixCredentials')) {
    /**
     * Returns matrix credentials for specified user.
     *
     * @deprecated `[2022-11-02]` `v2.40.4.2` in favor of the `\App\DataProvider\MatrixUserProvider`
     *
     * Instead of using this function, you need to use `\App\DataProvider\MatrixUserProvider` instance like this:
     *
     * ```
     * <?php
     *
     * declare(strict_types=1);
     *
     * use \App\DataProvider\MatrixUserProvider;
     *
     * $provider = $this->getContainer()->get(MatrixUserProvider::class);
     * $credentials = $provider->userCredentials($userId);
     * ```
     */
    function userMatrixCredentials(int $userId): ?array
    {
        $session = session();
        $hasKeys = $session->matrixKeys ?? false;
        $creadentials = $session->matrix ?? null;
        if (null === $creadentials || !$hasKeys) {
            /** @var MatrixConnector $matrixConnector */
            $matrixConnector = container()->get(MatrixConnector::class);
            $reference = $matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId);
            if (null === $reference) {
                return null;
            }

            $session->matrixKeys = $reference['has_initialized_keys'];
            $session->matrix = $creadentials = [
                'profileId' => $reference['profile_room_id'],
                'matrixId'  => $reference['mxid'],
                'username'  => $reference['username'],
                'password'  => $reference['password'],
                'hasKeys'   => $reference['has_initialized_keys'],
            ];
        }

        return $creadentials;
    }
}

if (!function_exists('getMatrixDialogData')) {
    /**
     * Return message and type of dialog if chat is unnavailable for user.
     */
    function getMatrixDialogData()
    {
        $title = '';
        $translate = translate('systmess_info_user_is_innactive_for_chat', null, true);
        $type = 'info';

        if(!logged_in()) {
            return [
                $type,
                translate('systmess_error_should_be_logged_in', null, true),
                null,
            ];
        }

        if (\userStatus() === \App\Common\Contracts\User\UserStatus::RESTRICTED()) {
            return [
                'warning',
                translate('systmess_info_chat_access_denied_popup_desc', null, true),
                translate('systmess_info_chat_access_denied_popup_ttl', null, true),
            ];
        }

        if (!matrixChatEnabled()) {
            return [
                $type,
                translate('systmess_error_chat_is_unavailable', null, true),
                null,
            ];
        }

        return [$type, $translate, $title];
    }
}

// End of file tinymvc_script_assets.php
// Location: /tinymvc/myapp/plugins/tinymvc_script_assets.php
