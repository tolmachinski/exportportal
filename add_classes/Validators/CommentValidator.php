<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class CommentValidator extends Validator
{
    const DEFAULT_MAX_COMMENT_SIZE = 1000;

    /**
     * The max size of comment text.
     *
     * @var int
     */
    private $maxCommentSize;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $maxCommentSize = self::DEFAULT_MAX_COMMENT_SIZE,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxCommentSize = $maxCommentSize;

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

        return array(
            'comment' => array(
                'field' => $fields->get('comment'),
                'label' => $labels->get('comment'),
                'rules' => $this->getCommentRules($this->maxCommentSize, $messages),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array(
            'comment' => 'comment',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'comment' => 'Comment',
        );
    }

    /**
     * Get the comment validation rule.
     */
    protected function getCommentRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            'required'               => $messages->get('comment.required') ?? '',
            "max_len[{$maxLength}]"  => $messages->get('comment.maxLength') ?? '',
        );
    }
}
