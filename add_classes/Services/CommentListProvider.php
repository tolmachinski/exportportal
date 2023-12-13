<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Comments\Author;
use App\Entities\Comments\Comment;
use App\Entities\Comments\Resource as CommentResource;
use App\Entities\Comments\Type as CommentType;
use App\Users\Contracts\PersonInterface;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Comments_Model;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @deprecated
 */
final class CommentListProvider
{
    const BLOCKED_COMMENT_TEMPLATE = '[[BLOCKED]]';

    const DELETED_COMMENT_TEMPLATE = '[[DELETED]]';

    /**
     * The current user.
     *
     * @var PersonInterface
     */
    private $currentUser;

    /**
     * The comments repository.
     *
     * @var Comments_Model
     */
    private $repository;

    /**
     * The comment resource.
     *
     * @var CommentResource
     */
    private $resource;

    /**
     * The comment type.
     *
     * @var CommentType
     */
    private $type;

    /**
     * The locale ISO code.
     *
     * @var string
     */
    private $locale;

    /**
     * Creates instance of the comment list service.
     */
    public function __construct(
        PersonInterface $currentUser,
        Comments_Model $repository,
        CommentType $type,
        CommentResource $resource,
        string $locale
    ) {
        $this->currentUser = $currentUser;
        $this->repository = $repository;
        $this->resource = $resource;
        $this->type = $type;
        $this->locale = $locale;
    }

    public function getList(int $page = 1, ?int $level = 0): array
    {
        /**
         * @var Collection   $comments
         * @var ParameterBag $paginator
         */
        list('comments' => $comments, 'paginator' => $paginator) = $this->repository->get_list($this->type, $this->resource, $level, $page);

        return array(
            'content' => $this->transformComments($comments),
            'context' => array(
                'paginator' => $paginator->all(),
            ),
        );
    }

    private function transformComments(Collection $comments): array
    {
        return $comments
            ->map(function (Comment $comment) {
                return $this->transformComment(
                    $this->normalizeComment($comment),
                    $this->currentUser
                );
            })
            ->getValues()
        ;
    }

    private function transformComment(Comment $comment, PersonInterface $currentUser): array
    {
        $author = $comment->getAuthor();
        $capabilities = $this->collectAuthorCapabilities($comment, $currentUser);
        $commentRendererPayload = array(
            'commentId'       => $comment->getId(),
            'contentText'     => $this->extractCommentTexts($comment),
        );
        $comment = array(
            'commentRenderer' => \array_merge(
                $commentRendererPayload,
                $this->extractAuthorInformation($author, $currentUser),
                array(
                    'createdDateTime'   => $this->transformDate($comment->getCreatedAt()),
                    'updatedDateTime'   => $this->transformDate($comment->getUpdatedAt()),
                    'modifiedDateTime'  => $this->transformDate($comment->getModifiedAt()),
                    'publishedDateTime' => $this->transformDate($comment->getPublishedAt()),
                    'deletedDateTime'   => $this->transformDate($comment->getDeletedAt()),
                    'blockedDateTime'   => $this->transformDate($comment->getBlockedAt()),
                )
            ),
        );

        return array(
            'threadRenderer' => array(
                'comment' => $comment,
                // 'replies' => $this->transformComments($comment->getChildren())
            ),
        );
    }

    private function collectAuthorCapabilities(Comment $comment, PersonInterface $currentUser): array
    {
        $author = $comment->getAuthor();
        $isAuthor = null !== $author && $author->getId() === $currentUser->getId();
        $canEdit = $isAuthor && !$comment->isBlocked() && !$comment->isDeleted();
        $canFlag = !$isAuthor && !$comment->isBlocked() && !$comment->isDeleted();
        $canReply = !$comment->isBlocked() && !$comment->isDeleted();
        $canDelete = false;
        $canBlock = false;

        return \compact(
            'canEdit',
            'canFlag',
            'canReply',
            'canBlock',
            'canDelete'
        );
    }

    private function transformDate(?DateTimeImmutable $date): array
    {
        $date = null === $date ? null : CarbonImmutable::instance($date)->locale($this->locale);
        $isDate = null !== $date;

        return array(
            'dateValue' => array(
                'iso' => $isDate ? $date->format(DateTime::ATOM) : null,
            ),
            'dateText' => array(
                'simpleText' => $isDate ? $date->ago(Carbon::DIFF_RELATIVE_TO_NOW) : null,
            ),
        );
    }

    private function extractAuthorInformation(?Author $author, PersonInterface $currentUser): array
    {
        if (null === $author) {
            return array();
        }

        return array(
            'authorText'      => array('simpleText' => $author->getName()),
            'authorEndpoint'  => array(
                'browseEndpoint' => array(
                    'canonicalUrl' => null,
                ),
            ),
            'authorThumbnail' => array(
                'thumbnails' => array(),
            ),
            'authorIsResourceOwner' => $author->isRegistered() && $author->getId() === $currentUser->getId(),
        );
    }

    /**
     * Extract texts from comment.
     */
    private function extractCommentTexts(Comment $comment): array
    {
        return array(
            'simpleText' => $comment->isBlocked() ? static::BLOCKED_COMMENT_TEMPLATE : $comment->getText(),
        );
    }

    /**
     * Normalzie the comment.
     */
    private function normalizeComment(Comment $comment): Comment
    {
        $comment->setType($this->type);
        $comment->setResource($this->resource);

        return $comment;
    }
}
