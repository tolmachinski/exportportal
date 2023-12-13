<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class AnonymousCommentValidator extends Validator
{
    const DEFAULT_MAX_COMMENT_SIZE = 1000;
    const DEFAULT_MAX_EMAIL_SIZE = 254;
    const DEFAULT_MAX_NAME_SIZE = 150;
    const DEFAULT_MIN_NAME_SIZE = 2;

    /**
     * The max size of comment text.
     *
     * @var int
     */
    private $maxCommentSize;

    /**
     * The max size of the author email.
     *
     * @var int
     */
    private $maxEmailSize;

    /**
     * The max size of author name.
     *
     * @var int
     */
    private $maxNameSize;

    /**
     * The min size of author name.
     *
     * @var int
     */
    private $minNameSize;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $maxCommentSize = self::DEFAULT_MAX_COMMENT_SIZE,
        int $maxEmailSize = self::DEFAULT_MAX_EMAIL_SIZE,
        int $minNameSize = self::DEFAULT_MIN_NAME_SIZE,
        int $maxNameSize = self::DEFAULT_MAX_NAME_SIZE,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxCommentSize = $maxCommentSize;
        $this->maxEmailSize = $maxEmailSize;
        $this->maxNameSize = $maxNameSize;
        $this->minNameSize = $minNameSize;

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
            'authorEmail' => array(
                'field' => $fields->get('authorEmail'),
                'label' => $labels->get('authorEmail'),
                'rules' => $this->getAuthorEmailRules($this->maxEmailSize, $messages),
            ),
            'authorName' => array(
                'field' => $fields->get('authorName'),
                'label' => $labels->get('authorName'),
                'rules' => $this->getAuthorNameRules($this->minNameSize, $this->maxNameSize, $messages),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array(
            'comment'     => 'comment',
            'authorName'  => 'name',
            'authorEmail' => 'email',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'comment'     => 'Comment',
            'authorName'  => 'Name',
            'authorEmail' => 'Email',
        );
    }

    /**
     * Get the comment validation rules.
     */
    protected function getCommentRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            'required'               => $messages->get('comment.required') ?? '',
            "max_len[{$maxLength}]"  => $messages->get('comment.maxLength') ?? '',
        );
    }

    /**
     * Get the author email rules.
     */
    protected function getAuthorEmailRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            'required'               => $messages->get('authorEmail.required') ?? '',
            'no_whitespaces'         => $messages->get('authorEmail.noWhitespaces') ?? '',
            'valid_email'            => $messages->get('authorEmail.validEmail') ?? '',
            "max_len[{$maxLength}]"  => $messages->get('authorEmail.maxLength') ?? '',
        );
    }

    /**
     * Get the author name validation rules.
     */
    protected function getAuthorNameRules(int $minLength, int $maxLength, ParameterBag $messages): array
    {
        return array(
            'required'               => $messages->get('authorName.required') ?? '',
            'valid_user_name'        => $messages->get('authorName.validName') ?? '',
            "min_len[{$minLength}]"  => $messages->get('authorName.minLength') ?? '',
            "max_len[{$maxLength}]"  => $messages->get('authorName.maxLength') ?? '',
        );
    }
}
