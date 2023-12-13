<?php

declare(strict_types=1);

namespace App\Common\Buttons;

use App\Common\Contracts\User\UserStatus;
use ExportPortal\Contracts\Chat\Recource\ResourceOptions;
use ExportPortal\Contracts\Chat\Recource\ResourceType;

/**
 * @deprecated
 */
class ChatButton
{
    private $dataParams = [
        'hide'              => false,
        'recipient'         => 0,
        'recipientStatus'   => 'inactive',
        'module'            => 0,
        'item'              => 0,
    ];

    private $buttonParams = [
        'tag'       => 'button',
        'classes'   => 'dropdown-item',
        'icon'      => '<i class="ep-icon ep-icon_chat-stroke"></i>',
        'disabled'  => false,
    ];

    public function __construct(array $dataParams = [], array $buttonParams = [])
    {
        $this->dataParams = array_merge($this->dataParams, $dataParams);
        $this->buttonParams = array_merge(
            $this->buttonParams,
            [
                'text'          => \translate('chat_button_generic_text', null, true),
                'title'         => \translate('chat_button_generic_text', null, true),
                'data-title'    => \translate('chat_button_generic_title', null, true),
                'disabled'      => \logged_in() && UserStatus::ACTIVE() !== \userStatus(),
            ],
            $buttonParams
        );
    }

    /**
     * Renders the button.
     *
     * @deprecated
     *
     * @uses static::render
     */
    public function button(): ?string
    {
        return $this->render();
    }

    /**
     * Renders the button.
     */
    public function render(): ?string
    {
        $userId = (int) privileged_user_id();

        if (
            (bool) $this->dataParams['hide']
            || ('active' !== $this->dataParams['recipientStatus'])
            || 0 === (int) $this->dataParams['recipient']
            || (int) $this->dataParams['recipient'] === $userId
            || (\logged_in() && \userGroupType()->isAdministration())
        ) {
            return null;
        }

        $params = $this->buttonParams;
        $tag = $params['tag'] ?? 'button';
        $isDisabled = $params['disabled'] ?? false;
        $buttonAttributes = array_filter([
            'type'  => 'button' === $tag ? 'button' : null,
            'class' => \implode(' ', \array_filter(['call-action', $params['classes'] ?? null, $isDisabled ? 'disabled' : null])),
            'title' => $params['title'],
        ]);
        if (!matrixChatEnabled() || matrixChatHiddenForCurrentUser()) {
            list($dataDialogType, $dataMessageKey) = getMatrixDialogData();

            return \sprintf(
                "<{$tag} class=\"js-open-dialog %s\" data-message=\"%s\" data-type=\"%s\"%s>%s%s</{$tag}>",
                $buttonAttributes['class'],
                $dataMessageKey,
                cleanOutput($dataDialogType),
                isset($params['atas']) ? addQaUniqueIdentifier($params['atas']) : '',
                $params['icon'],
                !empty($params['text']) ? "<span class=\"txt\">{$params['text']}</span>" : ''
            );
        }

        // $resourceType = $this->transformModuleToResourceType((int) $this->dataParams['module'] ?: null);
        // $resourceId = (string) $this->dataParams['item'] ?: null;
        // $title = \cleanOutput($this->buttonParams['title'] ?? '') ?: null;

        // return \contactUserButton(
        //     (int) $this->dataParams['recipient'] ?: null,
        //     ResourceOptions::fromRaw($resourceType, $resourceId),
        //     $this->buttonParams['text'] ?: null,
        //     $this->buttonParams['icon'] ?: null,
        //     [
        //         'class'      => $this->buttonParams['classes'] ?: null,
        //         'title'      => $title,
        //         'data-title' => $title,
        //     ]
        // );

        $data = [
            'js-action' => 'chat:open-contact-popup',
            'title'     => cleanOutput($params['data-title']),
            'user'      => (int) $this->dataParams['recipient'],
            'module'    => (int) $this->dataParams['module'],
            'item'      => (int) $this->dataParams['item'],
        ];
        foreach ($data as $dataKey => $dataItem) {
            if (0 === $dataItem) {
                continue;
            }

            $buttonAttributes["data-{$dataKey}"] = $dataItem;
        }
        if ($isDisabled && 'button' === $tag) {
            $buttonAttributes[] = 'disabled';
        }

        return sprintf(
            "<{$tag} %s%s>%s%s</{$tag}>",
            implode(' ', array_map(
                fn ($key, $value) => is_string($key) ? sprintf('%s="%s"', $key, cleanOutput((string) $value)) : cleanOutput((string) $value),
                array_keys($buttonAttributes),
                $buttonAttributes
            )),
            isset($params['atas']) ? addQaUniqueIdentifier($params['atas']) : '',
            $params['icon'],
            !empty($params['text']) ? "<span class=\"txt\">{$params['text']}</span>" : ''
        );
    }

    /**
     * Thansform the module ID to the valid resource type.
     */
    private function transformModuleToResourceType(?int $module): ResourceType
    {
        switch ($module) {
            case 1: return ResourceType::from(ResourceType::BILL);
            case 2: return ResourceType::from(ResourceType::B2B);
            case 3: return ResourceType::from(ResourceType::B2B_RESPONSE);
            case 4: return ResourceType::from(ResourceType::DISPUTE);
            case 5: return ResourceType::from(ResourceType::ESTIMATE);
            case 6: return ResourceType::from(ResourceType::EVENT);
            case 7: return ResourceType::from(ResourceType::INQUIRY);
            case 8: return ResourceType::from(ResourceType::INVOICE);
            case 9: return ResourceType::from(ResourceType::ORDER);
            case 10: return ResourceType::from(ResourceType::USER);
            case 11: return ResourceType::from(ResourceType::PO);
            case 12: return ResourceType::from(ResourceType::GROUPS);
            case 13: return ResourceType::from(ResourceType::REPORT);
            case 14: return ResourceType::from(ResourceType::COMPANY);
            case 16: return ResourceType::from(ResourceType::OFFER);
            case 17: return ResourceType::from(ResourceType::BLOG);
            case 18: return ResourceType::from(ResourceType::STAFF);
            case 19: return ResourceType::from(ResourceType::BRANCH);
            case 20: return ResourceType::from(ResourceType::SIMPLE_MESSAGE);
            case 21: return ResourceType::from(ResourceType::QUESTION);
            case 22: return ResourceType::from(ResourceType::OTHER);
            case 23: return ResourceType::from(ResourceType::PROTOTYPE);
            case 24: return ResourceType::from(ResourceType::ORDER_CANCELLING);
            case 25: return ResourceType::from(ResourceType::REVIEW);
            case 26: return ResourceType::from(ResourceType::FEEDBACK);
            case 27: return ResourceType::from(ResourceType::ORDER_DOCUMENT);
            case 28: return ResourceType::from(ResourceType::ACCREDITATION);
            case 31: return ResourceType::from(ResourceType::EXPENSE_REPORTS);
            case 32: return ResourceType::from(ResourceType::ORDER_BIDDING);
            case 32: return ResourceType::from(ResourceType::UPCOMING_ORDER);
            case 33: return ResourceType::from(ResourceType::ORDER_BID);
            case 35: return ResourceType::from(ResourceType::SAMPLE_ORDER);
        }

        return ResourceType::from(ResourceType::USER);
    }
}
