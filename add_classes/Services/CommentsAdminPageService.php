<?php

declare(strict_types=1);

namespace App\Services;

use Comments_Model;
use App\Common\Workflow\Comments\CommentStates;

final class CommentsAdminPageService
{
    /**
     * The samples repository.
     *
     * @var Comments_Model
     */
    private $commentsRepository;

    /**
     * Creates the instance of the service.
     */
    public function __construct()
    {
        $this->commentsRepository = model(Comments_Model::class);
    }

    public function getTableContent(): array
    {
        $request = request();
        $limit = $request->request->getInt('iDisplayLength', 10);
        $skip = $request->request->getInt('iDisplayStart', 0);
        $page = $skip / $limit + 1;
        $with = array('author', 'resource');
        $joins = array();

        $conditions = dtConditions($_POST, array(
            array('as' => 'state',              'key' => 'state',               'type' => 'cleaninput|trim'),
            array('as' => 'id_type',            'key' => 'type',                'type' => 'intval'),
            array('as' => 'id_author',          'key' => 'id_author',           'type' => 'intval'),
            array('as' => 'created_to',         'key' => 'created_to',          'type' => 'getDateFormat:m/d/Y,Y-m-d'),
            array('as' => 'published_to',       'key' => 'published_to',        'type' => 'getDateFormat:m/d/Y,Y-m-d'),
            array('as' => 'created_from',       'key' => 'created_from',        'type' => 'getDateFormat:m/d/Y,Y-m-d'),
            array('as' => 'published_from',     'key' => 'published_from',      'type' => 'getDateFormat:m/d/Y,Y-m-d'),
        ));

        $filters_by_entity = $_POST['entities'] ?? null;
        if (!empty($filters_by_entity)) {
            foreach ($filters_by_entity as $entity_key => $entity_value) {
                switch ($entity_key) {
                    case 'id_blog':
                        $conditions['resource_tokens'][] = getCommentResourceToken(blogCommentsResourceHashComponents((int) $entity_value));
                    break;
                    case 'id_news':
                        $conditions['resource_tokens'][] = getCommentResourceToken(newsCommentsResourceHashComponents((int) $entity_value));
                    break;
                    case 'id_updates':
                        $conditions['resource_tokens'][] = getCommentResourceToken(updatesCommentsResourceHashComponents((int) $entity_value));
                    break;
                    case 'id_trade_news':
                        $conditions['resource_tokens'][] = getCommentResourceToken(tradeNewsCommentsResourceHashComponents((int) $entity_value));
                    break;
                    case 'id_event':
                        $conditions['resource_tokens'][] = getCommentResourceToken(eventsCommentsResourceHashComponents((int) $entity_value));
                    break;
                    default: jsonDTResponse(translate("systmess_error_invalid_entity_input_name"));
                }
            }

            $joins = ['resources'];
        }

        if (isset($conditions['id_type'])) {
            $joins[] = 'resources';
            $joins[] = 'types';
        }

        $comments_table_alias = $this->commentsRepository->getTable();

        $order = array_column(dt_ordering($request->request->all(), array(
            'dt_comment_published_date' => "`{$comments_table_alias}`.`date_published`",
            'dt_comment_created_date'   => "`{$comments_table_alias}`.`date_created`",
            'dt_comment_id'             => "`{$comments_table_alias}`.`id`",
        )), 'direction', 'column');

        $comments = $this->commentsRepository->get_comments(compact('conditions', 'with', 'joins', 'limit', 'skip', 'order'));
        $count_comments = $this->commentsRepository->get_count_comments(compact('conditions', 'joins'));

        $response = array(
            'total' => $count_comments,
            'data' => array()
        );

        if (null === $comments || $comments->isEmpty()) {
            return $response;
        }

        $comment_states = CommentStates::ALLSTATES;

        $table_content = array();
        $can_delete_comment = have_right('delete_comment');
        $can_block_comment = have_right('block_comment');
        $can_publish_comment = have_right('publish_comment');
        $can_unpublish_comment = have_right('unpublish_comment');

        foreach ($comments as $comment) {
            $buffer = array();
            $actions = $delete_btn = $block_btn = $publish_btn = $unpublish_btn = '';
            $is_empty_dropdown_with_actions = true;

            $author = $comment['author'];
            $author_name = $author['name'];

            if (!empty($author['id_user'])) {
                if ('Shipper' === $author['group_type']) {
                    $author_name = '<a href="#" class="call-systmess" data-message="' . translate("systmess_info_forwarder_profile_have_not_public_page") . '" data-type="info"><i class="ep-icon ep-icon_user-page "></i>' . $author_name . '</a>';
                } else {
                    $author_name = '<a href="' . getUserLink($author['name'], $author['id_user'], $author['group_type'])  . '" target="blank"><i class="ep-icon ep-icon_user-page "></i>' . $author_name . '</a>';
                }
            }

            $author_name = '<a class="dt_filter ep-icon ep-icon_filter txt-green m-0 mr-5" data-name="id_author" data-title="Author" data-value-text="' . cleanOutput($author['name']) . '" data-value="' . $comment['id_author'] . '" title="Filter by Author"></a>' . $author_name;

            $comment_state = '<span class="txt-red">INVALID STATUS</span>';
            if (isset($comment_states[$comment['state']])) {
                $comment_state = '<span><i class="fs-30 ' . $comment_states[$comment['state']]['icon'] . '"></i>' . $comment['state'] . '</span>';
            }

            if ($can_delete_comment && CommentStates::DELETED !== $comment['state']) {
                $delete_btn = '<li>
                                <a class="confirm-dialog" data-callback="deleteComment" data-message="' . translate("systmess_really_want_to_delete_comment") . '" data-comment="' . $comment['id'] . '" title="Delete the comment" data-href="' . __SITE_URL . 'comments/ajax_operations/delete' . '">
                                    <span class="ep-icon ep-icon_remove-circle2 txt-red"></span> Delete
                                </a>
                            </li>';
                $is_empty_dropdown_with_actions = false;
            }

            if ($can_block_comment && !in_array($comment['state'], array(CommentStates::DELETED, CommentStates::BLOCKED))) {
                $block_btn = '<li>
                                <a class="confirm-dialog" data-callback="blockComment" data-message="' . translate("systmess_confirm_block_comment") . '" data-comment="' . $comment['id'] . '" title="Block the comment" data-href="' . __SITE_URL . 'comments/ajax_operations/block' . '">
                                    <span class="ep-icon ep-icon_minus-circle txt-red"></span> Block
                                </a>
                            </li>';
                $is_empty_dropdown_with_actions = false;
            }

            if ($can_publish_comment && !in_array($comment['state'], array(CommentStates::DELETED, CommentStates::PUBLISHED))) {
                $publish_btn = '<li>
                                    <a class="confirm-dialog" data-callback="publishComment" data-message="' . translate("systmess_confirm_publish_comment") . '" data-comment="' . $comment['id'] . '" title="Publish the comment" data-href="' . __SITE_URL . 'comments/ajax_operations/publish' . '">
                                        <span class="ep-icon ep-icon_comments-stroke txt-green"></span> Publish
                                    </a>
                                </li>';
                $is_empty_dropdown_with_actions = false;
            }

            if ($can_unpublish_comment && !in_array($comment['state'], array(CommentStates::DELETED, CommentStates::UNPUBLISHED))) {
                $unpublish_btn = '<li>
                                    <a class="confirm-dialog" data-callback="unpublishComment" data-message="' . translate("systmess_confirm_unpublish_comment") . '" data-comment="' . $comment['id'] . '" title="Unpublish the comment" data-href="' . __SITE_URL . 'comments/ajax_operations/unpublish' . '">
                                        <span class="ep-icon ep-icon_comment-stroke txt-green"></span> Unpublish
                                    </a>
                                </li>';
                $is_empty_dropdown_with_actions = false;
            }

            if (!$is_empty_dropdown_with_actions) {
                $actions = '<div class="dropdown">
                                <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                    ' . $publish_btn . '
                                    ' . $unpublish_btn . '
                                    ' . $block_btn . '
                                    ' . $delete_btn . '
                                </ul>
                            </div>';
            }

            $comment_type = '<a class="call-function" href="#" data-callback="openCommentResource" title="Go to page" data-resource="' . $comment['resource']['id'] . '">'
                            . '<i class="ep-icon ep-icon_link txt-green mr-5"></i>'
                            . cleanOutput($comment['resource']['type_name'])
                            . '</a>';

            $buffer = array(
                'dt_comment_published_date' => $comment['date_published'],
                'dt_comment_created_date'   => $comment['date_created'],
                'dt_comment_actions'        => $actions,
                'dt_comment_author'         => $author_name,
                'dt_author_email'           => $author['email'],
                'dt_comment_state'          => $comment_state,
                'dt_comment_text'           => cleanOutput($comment['text']),
                'dt_comment_type'           => $comment_type,
                'dt_comment_id'             => $comment['id'],
            );

            $table_content[] = $buffer;
        }

        $response['data'] = $table_content;

        return $response;
    }
}
