<?php

declare(strict_types=1);

namespace App\Common\Workflow\Comments;

interface CommentTemplates
{
    /**
     * The blocked comment text template.
     */
    const BLOCKED_COMMENT_TEMPLATE = '[[BLOCKED]]';

    /**
     * The deleted comment text template.
     */
    const DELETED_COMMENT_TEMPLATE = '[[DELETED]]';
}
