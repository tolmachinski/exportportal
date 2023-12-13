<?php

use App\Common\Exceptions\NotFoundException;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Workflow\Comments\CommentStates;
use App\Services\CommentsAdminPageService;
use App\Validators\AnonymousCommentValidator;
use App\Validators\CommentValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;

use const App\Common\DB_DATE_FORMAT;

/**
 * Controller Comments.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Comments_Controller extends TinyMVC_Controller
{
    /**
     * Comments administration
     */
    public function administration(): void
    {
        checkPermision('comments_administration');

        /**
         * @var Comment_Types_Model $comment_types_model
         */
        $comment_types_model = model(Comment_Types_Model::class);

        $data = array(
            'comment_states'    => array_keys(CommentStates::ALLSTATES),
            'comment_types'     => $comment_types_model->get_all_types(),
            'title'             => 'Comments',
        );

        views(array('admin/header_view', 'admin/comments/index_view', 'admin/footer_view'), $data);
    }

    public function ajax_dt_administration()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('comments_administration');

        try {
            $paginator = (new CommentsAdminPageService())->getTableContent();

            jsonResponse('', 'success', array(
                'sEcho'                => request()->request->getInt('sEcho', 0),
                'iTotalRecords'        => $paginator['total'] ?? 0,
                'iTotalDisplayRecords' => $paginator['total'] ?? 0,
                'aaData'               => $paginator['data'] ?? array(),
            ));
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Shows popup forms.
     */
    public function popup_forms(): void
    {
        switch (uri()->segment(3)) {
            case 'add':
                $this->show_add_comment_popup((int) uri()->segment(5) ?: null);

                break;
            case 'edit':
                $this->show_edit_comment_popup((int) uri()->segment(5));

                break;
            default:
                messageInModal(translate('systmess_error_route_not_found'));

                break;
        }
    }

    /**
     * Handles ajax operations.
     */
    public function ajax_operations(): void
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add':
                if(!logged_in() && !ajax_validate_google_recaptcha()){
                    jsonResponse(translate('systmess_error_you_didnt_pass_bot_check'));
                }

                $this->add_comment(
                    request(),
                    (int) privileged_user_id() ?: null,
                    request()->request->getInt('resource') ?: null
                );

                break;
            case 'edit':
                if (!logged_in()) {
                    jsonResponse(translate('systmess_error_permission_not_granted'));
                }

                $this->edit_comment();

                break;
            case 'delete':
                if (!logged_in()) {
                    jsonResponse(translate('systmess_error_permission_not_granted'));
                }

                if (have_right('delete_comment')) {
                    $this->delete_comment_by_administrator();
                } else {
                    $this->delete_comment();
                }

                break;
            case 'block':
                checkPermisionAjax('block_comment');

                $this->block_comment();

                break;
            case 'publish':
                checkPermisionAjax('publish_comment');

                $this->publish_comment();

                break;
            case 'unpublish':
                checkPermisionAjax('unpublish_comment');

                $this->unpublish_comment();

                break;
            case 'list':
                $page = request()->request->getInt('page');

                $this->get_comments_list(
                    request()->request->getInt('resource') ?: null,
                    request()->request->getInt('level') ?: 0,
                    $page < 1 ? 1 : $page,
                    (int) config('comments_per_page', 10)
                );

                break;
            case 'link_to_resource':
                checkPermisionAjax('comments_administration');

                $this->link_to_resource();
                break;
            case 'recent':
                $this->recent_comments(
                    (int) request()->request->getInt('maxCommentId'),
                    (int) request()->request->get('resourceId') ?: null
                );

                break;
            default:
                json(array('message' => translate('systmess_error_route_not_found'), 'mess_type' => 'error'), 404);

                break;
        }
    }

    /**
     * Show the popup form that allows to add comment.
     */
    private function show_add_comment_popup(?int $resource_id): void
    {
        //region Entities
        //region Resource
        try {
            /** @var Comment_Resources_Model $resources */
            $resources = model(Comment_Resources_Model::class);
            $resource = $resources->get_resource($resource_id);
        } catch (Exception $e) {
            messageInModal(translate('systmess_error_invalid_data'));
        }
        //endregion Resource
        //endregion Entities

        views()->display('new/comments/add_comment_form', array(
            'resource' => $resource,
        ));
    }

    /**
     * Show the popup form that allows to edit comment.
     */
    private function show_edit_comment_popup(int $comment_id): void
    {
        try {
            /** @var Comments_Model $comments_model */
            $comments_model = model(Comments_Model::class);
            $comment = $comments_model->get_comment($comment_id);
        } catch (Exception $e) {
            messageInModal(translate('systmess_error_invalid_data'));
        }

        // Only published comments user can edit
        if (!is_my($comment['author']['id_user']) || CommentStates::PUBLISHED !== $comment['state']) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }

        views()->display('new/comments/edit_comment_form', array(
            'comment' => $comment,
        ));
    }

    /**
     * Add a comment.
     */
    private function add_comment(Request $request, ?int $user_id, ?int $resource_id): void
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        if ($is_registered = null !== $user_id) {
            $validator = new CommentValidator($adapter);
        } else {
            $validator = new AnonymousCommentValidator($adapter);
        }

        if (!$validator->validate($request->request->all())) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        //region Entities
        //region User
        if ($is_registered) {
            if (null === $user_id || empty(model(User_Model::class)->getSimpleUser($user_id))) {
                jsonResponse(translate('systmess_error_user_does_not_exist'));
            }
        }
        //endregion User

        //region Resource
        try {
            $resource = model(Comment_Resources_Model::class)->get_resource($resource_id);
        } catch (Exception $e) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error', withDebugInformation(
                array(),
                array('exception' => throwableToArray($e))
            ));
        }
        //endregion Resource

        //region Author
        try {
            $author = model(Comment_Authors_Model::class)->get_or_create_author(
                $user_id,
                $request->request->get('email'),
                $request->request->get('name')
            );
        } catch (Exception $e) {
            jsonResponse(translate('systmess_error_invalid_data'), 'error', withDebugInformation(
                array(),
                array('exception' => throwableToArray($e))
            ));
        }
        //endregion Author

        //region Comment
        $comment = array(
            'id_lang'        => (int) tmvc::instance()->current_lang_detail['id_lang'] ?: null,
            'id_author'      => $author['id'],
            'id_resource'    => $resource['id'],
            'state'          => filter_var($author['is_registered'] ?? 0, FILTER_VALIDATE_BOOL) ? CommentStates::PUBLISHED : CommentStates::SUBMITED,
            'text'           => $request->request->get('comment'),
            'date_published' => new DateTimeImmutable(),
        );
        //endregion Comment
        //endregion Entities

        //region Write
        if (!($comment_id = model(Comments_Model::class)->add($comment))) {
            jsonResponse(translate('systmess_error_failed_add_comment'));
        }

        $conditions = [
            'resource' => $resource['id'],
            'state' => CommentStates::PUBLISHED,
        ];
        //endregion Write

        $comment_counter = model(Comments_Model::class)->get_count_comments(compact('conditions')) ?? 0;

        jsonResponse(!logged_in() ? translate('systmess_success_unmodered_comment_added') : translate('systmess_success_modered_comment_added'), 'success', [
            'resourceId'        => $resource['id'],
            'comment'           => $comment_id,
            'commentCounter'    => $comment_counter,
        ]);
    }

    /**
     * Get recent comments after last id.
     */
    private function recent_comments(int $max_comment_id, ?int $resource_id): void
    {
        try {
            /** @var Comments_Model $last_comments */
            $last_comments = model(Comments_Model::class)->get_comments_after_id($max_comment_id, $resource_id);
        } catch (Exception $e) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $identifiers = [];

        $comments_list = views()->fetch('new/comments/comments_tree_view', array(
            'paginator' => 1000,
            'comments'  => $last_comments->map(function (array $comment) use (&$identifiers) {
                $identifiers[] = $comment['id'];
                $comment['published_at'] = $comment['date_published'];
                $comment['author']['photo'] = null !== $comment['author']['photo']
                    ? getDisplayImageLink(
                        array('{ID}' => $comment['author']['user'] ?? null, '{FILE_NAME}' => $comment['author']['photo'] ?? null),
                        'users.main',
                        array('thumb_size' => 0, 'no_image_group' => $comment['author']['group'] ?? 0)
                    )
                    : __SITE_URL . getNoPhoto(0);

                return $comment;
            })
        ));

        $response = ['maxCommentId' => max($identifiers) ?: null];

        if (logged_in()) {
            $response['commentsList'] = $comments_list;
        }

        jsonResponse('', 'success', $response);
    }

    /**
     * Edit a comment.
     */
    private function edit_comment(): void
    {
        /**
         * @var Request $request
         */
        $request = request();

        //region Validation
        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new CommentValidator($adapter);

        if (!$validator->validate($request->request->all())) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }

        $comment_id = (int) $request->request->get('comment_id');
        if (empty($comment_id)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Validation

        //region Comment
        try {
            /** @var Comments_Model $comments_model */
            $comments_model = model(Comments_Model::class);
            $comment = $comments_model->get_comment($comment_id);
        } catch (NotFoundException $e) {
            jsonResponse(translate('systmess_error_invalid_data'));
        } catch (Exception $e) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        // Only published comments user can edit
        if (!is_my($comment['author']['id_user']) || CommentStates::PUBLISHED !== $comment['state']) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }

        $comment_updates = array(
            'text' => $request->request->get('comment'),
        );
        //endregion Comment

        //region Write
        if (!$comments_model->edit($comment_id, $comment_updates)) {
            jsonResponse(translate('systmess_error_failed_edit_comment'));
        }
        //endregion Write

        jsonResponse(translate('systmess_success_comment_edited'), 'success', [
            'commentId' => $comment_id,
            'text'      => $comment_updates['text'],
        ]);
    }

    /**
     * Delete a comment.
     */
    private function delete_comment(): void
    {
        //region Validation
        $comment_id = (int) request()->request->get('comment');
        if (empty($comment_id)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Validation

        //region Comment
        try {
            /** @var Comments_Model $comments_model */
            $comments_model = model(Comments_Model::class);
            $comment = $comments_model->get_comment($comment_id);
        } catch (NotFoundException $e) {
            jsonResponse(translate('systmess_error_invalid_data'));
        } catch (Exception $e) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        // Only published comments user can delete
        if (!is_my($comment['author']['id_user']) || CommentStates::PUBLISHED !== $comment['state']) {
            jsonResponse(translate('systmess_error_permission_not_granted'));
        }
        //endregion Comment

        //region Write
        if (!$comments_model->delete($comment_id)) {
            jsonResponse(translate('systmess_error_comment_delete'));
        }
        //endregion Write

        jsonResponse(translate('community_comment_deleted'), 'success');
    }

    /**
     * Delete a comment by administrator
     */
    private function delete_comment_by_administrator(): void
    {
        //region Validation
        $comment_id = (int) request()->request->get('comment');
        if (empty($comment_id)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Validation

        /** @var Comments_Model $comments_model */
        $comments_model = model(Comments_Model::class);

        if (!$comments_model->delete($comment_id)) {
            jsonResponse(translate('systmess_error_admin_comment_delete'));
        }

        jsonResponse(translate('community_comment_deleted'), 'success');
    }

    /**
     * Block a comment.
     */
    private function block_comment(): void
    {
        //region Validation
        $comment_id = (int) request()->request->get('comment');
        if (empty($comment_id)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Validation

        //region Comment
        try {
            /** @var Comments_Model $comments_model */
            $comments_model = model(Comments_Model::class);
            $comment = $comments_model->get_comment($comment_id);
        } catch (NotFoundException $e) {
            jsonResponse(translate('systmess_error_invalid_data'));
        } catch (Exception $e) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        if (CommentStates::DELETED === $comment['state']) {
            jsonResponse(translate('systmess_error_deleted_comment_block'));
        }

        if (!$comments_model->block($comment_id)) {
            jsonResponse(translate('systmess_error_comment_block'));
        }

        jsonResponse(translate('systmess_success_comment_block'), 'success');
    }

    /**
     * Publish a comment.
     */
    private function publish_comment(): void
    {
        //region Validation
        $comment_id = (int) request()->request->get('comment');
        if (empty($comment_id)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Validation

        //region Comment
        try {
            /** @var Comments_Model $comments_model */
            $comments_model = model(Comments_Model::class);
            $comment = $comments_model->get_comment($comment_id);
        } catch (NotFoundException $e) {
            jsonResponse(translate('systmess_error_invalid_data'));
        } catch (Exception $e) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        if (CommentStates::DELETED === $comment['state']) {
            jsonResponse(translate('systmess_error_deleted_comment_block'));
        }

        if (CommentStates::PUBLISHED === $comment['state']) {
            jsonResponse(translate('systmess_error_comment_already_published'));
        }

        if (!$comments_model->publish($comment_id)) {
            jsonResponse(translate('systmess_error_comment_publish'));
        }

        jsonResponse(translate('systmess_success_comment_published'), 'success');
    }

    /**
     * Unpublish a comment.
     */
    private function unpublish_comment(): void
    {
        //region Validation
        $comment_id = (int) request()->request->get('comment');
        if (empty($comment_id)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }
        //endregion Validation

        //region Comment
        try {
            /** @var Comments_Model $comments_model */
            $comments_model = model(Comments_Model::class);
            $comment = $comments_model->get_comment($comment_id);
        } catch (NotFoundException $e) {
            jsonResponse(translate('systmess_error_invalid_data'));
        } catch (Exception $e) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        if (CommentStates::DELETED === $comment['state']) {
            jsonResponse(translate('systmess_error_deleted_comment_unpublish'));
        }

        if (CommentStates::UNPUBLISHED === $comment['state']) {
            jsonResponse(translate('systmess_error_comment_already_unpublished'));
        }

        if (!$comments_model->unpublish($comment_id)) {
            jsonResponse(translate('systmess_error_comment_unpublish'));
        }

        jsonResponse(translate('systmess_success_comment_unpublished'), 'success');
    }

    /**
     * Get comment list.
     */
    private function get_comments_list(?int $resource_id, int $level = 0, int $page = 1, ?int $per_page = null): void
    {
        //region Resource
        try {
            model(Comment_Resources_Model::class)->get_resource($resource_id);
        } catch (Throwable $e) {
            jsonResponse(
                $e instanceof NotFoundException ? translate('systmess_error_resourse_not_found') : translate('systmess_error_invalid_data'),
                'error',
                withDebugInformation(
                    array(),
                    array('exception' => throwableToArray($e))
                )
            );
        }
        //endregion Resource

        //region Comments
        /** @var Comments_Model $comments */
        $comments = model(Comments_Model::class);
        /** @var Collection $list */
        list('comments' => $list, 'paginator' => $paginator) = $comments->get_list($resource_id, $level, $page, $per_page);
        //endregion Comments

        $identifiers = [];

        jsonResponse(null, 'success', array(
            'level'     => $level,
            'page'      => $page,
            'html'      => views()->fetch('new/comments/comments_tree_view', array(
                'paginator' => $paginator,
                'comments'  => $list->map(function (array $comment) use (&$identifiers) {
                    $identifiers[] = $comment['id'];
                    $comment['author']['photo'] = null !== $comment['author']['photo']
                        ? getDisplayImageLink(
                            array('{ID}' => $comment['author']['user'] ?? null, '{FILE_NAME}' => $comment['author']['photo'] ?? null),
                            'users.main',
                            array('thumb_size' => 0, 'no_image_group' => $comment['author']['group'] ?? 0)
                        )
                        : __SITE_URL . getNoPhoto(0);

                    return $comment;
                }),
            )),
            'paginator' => arrayCamelizeAssocKeys($paginator),
            'max_comment_id' => max($identifiers) ?: null,
        ));
    }

    /**
     * Get link to comment resource
     */

     private function link_to_resource(): void
     {
        $id_resource = request()->request->getInt('resource');
        if (empty($id_resource)) {
            jsonResponse(translate('systmess_error_resource_id_not_found'));
        }

        /**
         * @var Comment_Resources_Model $resources_model
         */
        $resources_model = model(Comment_Resources_Model::class);

        //region Resource
        try {
            $resource = $resources_model->get_resource_by_conditions(array(
                'conditions' => array(
                    'id' => (int) $id_resource
                ),
                'with'  => array('type')
            ));
        } catch (Throwable $e) {
            jsonResponse(
                $e instanceof NotFoundException ? translate('systmess_error_resourse_not_found') : translate('systmess_error_invalid_data'),
                'error',
                withDebugInformation(
                    array(),
                    array('exception' => throwableToArray($e))
                )
            );
        }

        switch ($resource['type']['alias']) {
            case 'blogs':
                $resource_context = empty($resource['context']) ? array() : json_decode($resource['context'], true);

                if (!isset($resource_context['id'])) {
                    jsonResponse(translate('systmess_error_comment_blog_id_not_found'));
                }

                /**
                 * @var Blog_Model $blogs_model
                 */
                $blogs_model = model(Blog_Model::class);

                $blog = $blogs_model->get_blog((int) $resource_context['id']);
                if (empty($blog)) {
                    jsonResponse(translate('systmess_error_blog_comment_deleted'));
                }

                $resource_url = getBlogUrl($blog);
                if (null === $resource_url) {
                    jsonResponse(translate('systmess_error_link_generate_failed'));
                }

                jsonResponse(null, 'success', array('url' => $resource_url));
            break;
            case 'news':
                $resource_context = empty($resource['context']) ? array() : json_decode($resource['context'], true);

                if (!isset($resource_context['id'])) {
                    jsonResponse(translate('systmess_error_comment_news_id_not_found'));
                }

                /**
                 * @var Ep_News_Model $ep_news_model
                 */
                $ep_news_model = model(Ep_News_Model::class);

                $news = $ep_news_model->get_one_ep_news((int) $resource_context['id']);
                if (empty($news)) {
                    jsonResponse(translate('systmess_error_blog_comment_deleted'));
                }

                $resource_url = getEpNewsUrl($news);
                if (null === $resource_url) {
                    jsonResponse(translate('systmess_error_link_generate_failed'));
                }

                jsonResponse(null, 'success', array('url' => $resource_url));
            break;
            case 'updates':
                $resource_context = empty($resource['context']) ? array() : json_decode($resource['context'], true);

                if (!isset($resource_context['id'])) {
                    jsonResponse(translate('systmess_error_comment_updates_id_not_found'));
                }

                /**
                 * @var Ep_Updates_Model $ep_updates_model
                 */
                $ep_updates_model = model(Ep_Updates_Model::class);

                $ep_updates = $ep_updates_model->get_one_ep_update((int) $resource_context['id']);
                if (empty($ep_updates)) {
                    jsonResponse(translate('systmess_error_update_comment_deleted'));
                }

                $resource_url = getEpUpdatesUrl($ep_updates);
                if (null === $resource_url) {
                    jsonResponse(translate('systmess_error_link_generate_failed'));
                }

                jsonResponse(null, 'success', array('url' => $resource_url));
            break;
            case 'trade_news':
                $resource_context = empty($resource['context']) ? array() : json_decode($resource['context'], true);

                if (!isset($resource_context['id'])) {
                    jsonResponse(translate('systmess_error_comment_trade_news_id_not_found'));
                }

                /**
                 * @var Trade_news_Model $trade_news_model
                 */
                $trade_news_model = model(Trade_news_Model::class);

                $trade_news = $trade_news_model->get_one_trade_news((int) $resource_context['id']);
                if (empty($trade_news)) {
                    jsonResponse(translate('systmess_error_trade_news_comment_deleted'));
                }

                $resource_url = getTradeNewsUrl($trade_news);
                if (null === $resource_url) {
                    jsonResponse(translate('systmess_error_link_generate_failed'));
                }

                jsonResponse(null, 'success', array('url' => $resource_url));
            break;
            default: jsonResponse(translate('systmess_error_comment_resource_undefined'));
        }
     }
}

// End of file comments.php
// Location: /tinymvc/myapp/controllers/comments.php
