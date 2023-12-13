<?php

declare(strict_types=1);

namespace App\Common\Workflow\Comments;

interface CommentStates
{
    const UNPUBLISHED = 'UNPUBLISHED';

    const PUBLISHED = 'PUBLISHED';

    const BLOCKED = 'BLOCKED';

    const DELETED = 'DELETED';

    const SUBMITED = 'SUBMITED';

    const ALLSTATES = array(
        self::UNPUBLISHED => array(
            'icon' => 'ep-icon ep-icon_comment-stroke txt-green'
        ),
        self::PUBLISHED => array(
            'icon' => 'ep-icon ep-icon_comments-stroke txt-green'
        ),
        self::BLOCKED => array(
            'icon' => 'ep-icon ep-icon_minus-circle txt-red'
        ),
        self::DELETED => array(
            'icon' => 'ep-icon ep-icon_remove-circle2 txt-red'
        ),
        self::SUBMITED => array(
            'icon' => 'ep-icon ep-icon_new txt-orange'
        ),
    );
}
