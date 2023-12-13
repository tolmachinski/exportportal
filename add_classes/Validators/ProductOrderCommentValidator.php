<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;

use Product_Orders_Model;
use Product_Order_Comments_Model;

use Symfony\Component\HttpFoundation\ParameterBag;

class ProductOrderCommentValidator extends Validator
{
    protected const MAX_MESSAGE_LENGTH = 1000;
    protected $commentAction;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        string $commentAction = 'addComment',
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->commentAction = $commentAction;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            'message' => [
                'field' => $fields->get('message'),
                'label' => $labels->get('message'),
                'rules' => $this->getMessageRules(static::MAX_MESSAGE_LENGTH, $messages),
            ],
            'orderId' => [
                'field' => $fields->get('orderId'),
                'label' => $labels->get('orderId'),
                'rules' => $this->getOrderIdRules($messages),
            ],
            'commentId' => [
                'field' => $fields->get('commentId'),
                'label' => $labels->get('commentId'),
                'rules' => $this->getCommentIdRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'message'   => 'message',
            'orderId'   => 'order',
            'commentId' => 'comment',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'message'   => 'Comment message',
            'orderId'   => 'Order ID',
            'commentId' => 'Comment ID',
        ];
    }

    /**
     * Get the short description validation rules.
     */
    protected function getMessageRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'              => '',
            "max_len[{$maxLength}]" => '',
        ];
    }

    /**
     * Get order id validation rules
     */
    protected function getOrderIdRules(ParameterBag $messages): array
    {
        return [
            function (string $attr, $orderId, callable $fail) use ($messages) {
                /** @var Product_Orders_Model $productOrderModel */
                $productOrderModel = model(Product_Orders_Model::class);

                if (
                    empty($orderId)
                    || !$productOrderModel->has((int) $orderId)
                ) {
                    $fail(translate('systmess_error_invalid_data'));
                }
            }
        ];
    }

    /**
     * Get order id validation rules
     */
    protected function getCommentIdRules(ParameterBag $messages): array
    {
        return [
            function (string $attr, $commentId, callable $fail) use ($messages) {
                if ('addComment' === $this->commentAction) {
                    return;
                }

                /** @var Product_Order_Comments_Model $productOrderCommentsModel */
                $productOrderCommentsModel = model(Product_Order_Comments_Model::class);

                if (
                    empty($commentId)
                    || !$productOrderCommentsModel->has((int) $commentId)
                ) {
                    $fail(translate('systmess_error_invalid_data'));
                }
            }
        ];
    }


}
