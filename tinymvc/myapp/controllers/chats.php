<?php

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Contracts\User\UserStatus;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Exceptions\QueryException;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Services\ChatService;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Chats_Controller extends TinyMVC_Controller
{
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    public function index()
    {
        checkIsLogged();
        checkDomainForGroup();
        checkPermision('manage_messages');
        if (userStatus()->isLimited() || UserStatus::ACTIVE() !== userStatus()) {
            redirect('/403');
        }

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->chatsEpl();
        } else {
            $this->chatsAll();
        }
    }

    private function chatsEpl(){
        $data['chatApp'] = ['openIframe' => 'page'];
        $data['templateViews'] = [
            'mainOutContent'    => 'chats/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    private function chatsAll(){
        views()->display_template([
            'chatApp'       => ['openIframe' => 'page'],
            'templateViews' => [
                'mainOutContent' => 'chats/index_view',
            ],
        ]);
    }

    public function ajax_chats_operations()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('manage_messages');
        if (userStatus()->isLimited()) {
            jsonResponse(translate('systmess_error_permission_not_granted', null, true));
        }

        switch (uri()->segment(3)) {
            case 'contact_user':
                $request = request()->request;

                $this->show_contact_popup(
                    $request->getInt('user'),
                    with($request->getInt('module'), function ($moduleId): ?int { return $moduleId ? (int) $moduleId : null; }),
                    with($request->getInt('item'), function ($itemId): ?int { return $itemId ? (int) $itemId : null; })
                );

            break;
            case 'validate_room':
                $request = request()->request;

                $this->validate_rooms($request->get('users') ?? []);

            break;
            case 'insert_room':
                $request = request()->request;

                $this->insert_room(
                    $request->getInt('user'),
                    with($request->getInt('module'), function ($moduleId): ?int { return $moduleId ? (int) $moduleId : null; }),
                    with($request->getInt('item'), function ($itemId): ?int { return $itemId ? (int) $itemId : null; }),
                );

            break;
            case 'update_room':
                $request = request()->request;

                $this->update_room(
                    $request->get('id'),
                    $request->getInt('user'),
                    with($request->getInt('module'), function ($moduleId): ?int { return $moduleId ? (int) $moduleId : null; }),
                    with($request->getInt('item'), function ($itemId): ?int { return $itemId ? (int) $itemId : null; }),
                );

            break;
        }
    }

    /**
     * Shows the contact popup.
     */
    protected function show_contact_popup(
        int $recipientId,
        ?int $moduleId,
        ?int $itemId
    ): void {
        if (0 === $recipientId) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        list($userId, $recipient, $roomInfo, $subject) = $this->validate_contact($recipientId, $moduleId, $itemId);

        if (!empty($roomInfo) && (null !== $moduleId && $moduleId > 0 && null !== $itemId && $itemId > 0)) {
            if ((int) $roomInfo['id_room'] > 0) {
                $data['idRoom'] = $roomInfo['room_id'];
            } else {
                jsonResponse('Wait for the creation of a chat room.', 'info');
            }
        } else {
            // For Sample order
            if (null !== $moduleId && $moduleId === 35) {
                jsonResponse('Wait for the creation of a chat room.', 'info');
            }

            $linkToUserProfile = getUserLink($recipient['user_name'], $recipientId, $recipient['gr_type']);

            $data = [
                'userInfo' => [
                    'id'         => $recipientId,
                    'name'       => $recipient['user_name'],
                    'avatar'     => getDisplayImageLink(['{ID}' => $recipient['idu'], '{FILE_NAME}' => $recipient['user_photo']], 'users.main', ['thumb_size' => 0, 'no_image_group' => $recipient['user_group']]),
                    'link'       => $linkToUserProfile,
                    'group'      => filter_var($recipient['is_verified'], FILTER_VALIDATE_BOOLEAN) ? $recipient['gr_name'] : trim(str_replace('Verified', '', $recipient['gr_name'])),
                    'groupName'  => $recipient['gr_name'],
                    'mxId'       => container()->get(MatrixConnector::class)->getConfig()->getNamingStrategy()->matrixId((string) $recipientId),
                    'mxUserName' => container()->get(MatrixConnector::class)->getConfig()->getNamingStrategy()->userName((string) $recipientId),
                ],
            ];

            if (!empty($subject)) {
                $data['themeInfo'] = [
                    'idModule'      => $moduleId,
                    'idItem'        => $itemId,
                    'subject'       => $subject,
                ];
            }
        }

        jsonResponse('', 'success', $data);
    }

    protected function validate_rooms(
        array $recipientsId
    ): void {
        foreach ($recipientsId as $recipientsIdItem) {
            if (0 === $recipientsIdItem['id']) {
                jsonResponse(translate('systmess_error_invalid_data'));

                break;
            }

            $this->validate_contact($recipientsIdItem['id'], null, null);
        }

        jsonResponse('', 'success');
    }

    protected function insert_room(
        int $recipientId,
        ?int $moduleId,
        ?int $itemId
    ): void {
        if (0 === $recipientId) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        list($userId, $recipient, $roomInfo, $subject) = $this->validate_contact($recipientId, $moduleId, $itemId);

        if (!empty($roomInfo) && (null !== $moduleId && $moduleId > 0 && null !== $itemId && $itemId > 0)) {
            if ((int) $roomInfo['id_room'] > 0) {
                $data['idRoom'] = $roomInfo['room_id'];
            } else {
                jsonResponse('Wait for the creation of a chat room.', 'info');
            }
        } else {
            try {
                $chatService = new ChatService();
                $chatService->insertRoomByModule(0, $recipientId, $userId, $moduleId, $itemId);
            } catch (QueryException | OwnershipException $exception) {
                jsonResponse(translate('contact_page_failed_contact_user_messsage'));
            } catch (NotFoundException $exception) {
                if (27 === $moduleId) {
                    jsonResponse(translate('systmess_error_document_not_exist'));
                } else {
                    jsonResponse(translate('contact_page_failed_contact_user_messsage'));
                }
            }

            $data = [];
            if (!empty($subject)) {
                $data['subject'] = $subject;
            }
        }

        jsonResponse('', 'success', $data);
    }

    protected function update_room(
        string $roomId,
        int $recipientId,
        ?int $moduleId,
        ?int $itemId
    ): void {
        if (empty($roomId) || 0 === $recipientId) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var Matrix_Rooms_Model $matrixRoomsRepository */
        $matrixRoomsRepository = \model(Matrix_Rooms_Model::class);
        $id = $matrixRoomsRepository->add([
            'room_id'           => $roomId,
            'created_at_date'   => new DateTimeImmutable(),
        ]);

        if ((int) $id > 0) {
            list($userId, $recipient, $roomInfo) = $this->validate_contact($recipientId, null, null);

            if (null === $moduleId || null === $itemId) {
                $chatService = new ChatService();
                $chatService->insertRoomByModule($id, $recipient['idu'], $userId, null, null);
            } else {
                try {
                    $chatService = new ChatService();
                    $chatService->insertRoomByModule($id, $recipient['idu'], $userId, $moduleId, $itemId);
                } catch (QueryException | OwnershipException $exception) {
                    jsonResponse(translate('contact_page_failed_contact_user_messsage'));
                } catch (NotFoundException $exception) {
                    if (27 === $moduleId) {
                        jsonResponse(translate('systmess_error_document_not_exist'));
                    } else {
                        jsonResponse(translate('contact_page_failed_contact_user_messsage'));
                    }
                }
            }

            jsonResponse('', 'success', [$roomId]);
        } else {
            jsonResponse(translate('systmess_error_document_not_exist'));
        }
    }

    protected function validate_contact(
        int $recipientId,
        ?int $moduleId,
        ?int $itemId
    ): array {
        $userId = (int) privileged_user_id();
        /** @var User_Model $usersRepository */
        $usersRepository = model(User_Model::class);
        //region Check recipient
        if (
            0 === $recipientId
            || empty($recipient = $usersRepository->get_user_by_condition(['id_user' => $recipientId]))
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        if ($userId === $recipientId) {
            jsonResponse(translate('systmess_error_cannot_contact_yourself'));
        }

        if ('active' !== $recipient['status']) {
            jsonResponse('This user does\'t exist.');
        }
        //endregion Check recipient

        $chatService = new ChatService();
        if (null !== $moduleId && null !== $itemId) {
            //region Subject
            try {
                list($subject, $roomInfo) = $chatService->findThemeFromModule($recipientId, $userId, $moduleId, $itemId);
            } catch (QueryException | OwnershipException $exception) {
                jsonResponse(translate('contact_page_failed_contact_user_messsage'));
            } catch (NotFoundException $exception) {
                if (27 === $moduleId) {
                    jsonResponse(translate('systmess_error_document_not_exist'));
                } else {
                    jsonResponse(translate('contact_page_failed_contact_user_messsage'));
                }
            }
            //endregion Subject
            return [$userId, $recipient, $roomInfo, $subject];
        }
        $roomInfo = $chatService->findRoom($recipientId, $userId);

        return [$userId, $recipient, $roomInfo];
    }

    public function popupForms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

        switch (uri()->segment(3)) {
            case 'attachFiles':
                    //region Fetch rules
                    $rules = config('files.messages.attach');
                    //endregion Fetch rules

                    views()->display('new/chats/files_attach_view', array(
                        'fileupload'   => $this->getFormattedFileuploadOptions(
                            explode(',', arrayGet($rules, 'rules.format', 'pdf,jpg,jpeg,png')),
                            (int) arrayGet($rules, 'limit', 1),
                            (int) arrayGet($rules, 'limit', 1),
                            (int) arrayGet($rules, 'rules.size', 2 * 1000 * 1000),
                            arrayGet($rules, 'rules.size_placeholder', '2MB')
                        ),
                    ));
                break;
        }
    }
}
