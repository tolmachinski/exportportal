<?php

use App\Common\Contracts\Entities\CountryCodeInterface;
use App\Common\Contracts\Media\CompanyLogoThumb;
use App\Common\Database\Exceptions\WriteException;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Exceptions\AccessDeniedException;
use App\Common\Exceptions\AlreadyExistsException;
use App\Common\Exceptions\CompanyNotFoundException;
use App\Common\Exceptions\MismatchStatusException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\OwnershipException;
use App\Common\Exceptions\ProfileCompletionException;
use App\Common\Http\Request;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\ValidationException;
use App\DataProvider\AccountProvider;
use App\Email\EmailFriendAboutCompany;
use App\Email\InviteCustomers;
use App\Email\InviteFeedback;
use App\Filesystem\CompanyLogoFilePathGenerator;
use App\Filesystem\FilePathGenerator;
use App\Filesystem\ShipperCompanyPathGenerator;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedBuyerCompanyEvent;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedSellerCompanyEvent;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedShipperCompanyEvent;
use App\Messenger\Message\Event\Lifecycle\UserUpdatedShipperCompanyLogoEvent;
use App\Renderer\CompanyEditViewRenderer;
use App\Services\Company\CompanyProcessingService;
use App\Services\EditRequest\CompanyEditRequestDocumentsService;
use App\Services\PhoneCodesService;
use App\Validators\AddressValidator;
use App\Validators\CompanyCategoriesValidator;
use App\Validators\CompanyDescriptionValidator;
use App\Validators\CompanyEmailValidator;
use App\Validators\CompanyEmployeesValidator;
use App\Validators\CompanyImageValidator;
use App\Validators\CompanyIndustriesValidator;
use App\Validators\CompanyLocationValidator;
use App\Validators\CompanyLogoValidator;
use App\Validators\CompanyNameIndexValidator;
use App\Validators\CompanyNamesValidator;
use App\Validators\CompanyRevenueValidator;
use App\Validators\CompanyShipperValidator;
use App\Validators\CompanyTypeValidator;
use App\Validators\CompanyVideoValidator;
use App\Validators\CompanyWebsiteValidator;
use App\Validators\PhoneValidator;
use Doctrine\Common\Collections\ArrayCollection;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use TinyMVC_Library_Auth as LegacyAuthHandler;
use TinyMVC_Library_Cleanhtml as LegacyHtmlSanitizer;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;
use TinyMVC_Library_Session as LegacySessionHandler;
use TinyMVC_Library_validator as LegacyValidator;

use const App\Logger\Activity\OperationTypes\ADD;
use const App\Logger\Activity\OperationTypes\ADMIN_EDIT;
use const App\Logger\Activity\OperationTypes\DELETE_LOGO;
use const App\Logger\Activity\OperationTypes\EDIT;
use const App\Logger\Activity\ResourceTypes\BUYER_COMPANY;
use const App\Logger\Activity\ResourceTypes\COMPANY;
use const App\Logger\Activity\ResourceTypes\SHIPPER_COMPANY;
use const App\Moderation\Types\TYPE_COMPANY;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Company_Controller extends TinyMVC_Controller
{
	/* load main models*/
	private function _load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('Company_Model', 'company');
		$this->load->model('User_Model', 'user');
		$this->load->model('Branch_Model', 'branch');
	}

	public function edit()
	{
		checkIsLogged();

		switch (user_group_type()) {
			case 'Buyer':
				$this->show_buyer_edit_company_page((int) privileged_user_id());

				break;
			case 'Seller':
				checkPermision('edit_company');
				checkGroupExpire();

				if (!i_have_company()) {
					try {
						$this->create_seller_company((int) privileged_user_id());
					} catch (NotFoundException $exception) {
						headerRedirect("/404");
					} catch (RuntimeException $exception) {
						redirectWithMessage("/404", translate('systmess_company_info_failed_to_open_message'), 'errors');
					}
				}

				$this->showSellerCompanyEditPage(
                    $this->getContainer()->get(CompanyEditViewRenderer::class),
                    $this->getContainer()->get(AccountProvider::class)
                );

				break;
			case 'Shipper':
				checkPermision('shipper_edit_company');
                checkDomainForGroup();

                if (!i_have_shipper_company()) {
                    try {
                        $this->create_shipper_company((int) privileged_user_id());
					} catch (NotFoundException $exception) {
						headerRedirect("/404");
					} catch (RuntimeException $exception) {
						redirectWithMessage("/404", "Failed to process your company. Please contact administration.", 'errors');
					}

                }

				$this->show_shipper_company_edit_page((int) my_shipper_company_id());

				break;
			default:
				headerRedirect("/404");

				break;
		}
	}

	function ajax_shipper_company_upload_logo() {
		checkIsAjax();
		checkIsLogged();
		checkPermisionAjax('shipper_edit_company');
		$files = arrayGet($_FILES, 'files');
		if (null === $files) {
			jsonResponse(translate('validation_image_required'));
		}

		if (is_array($files['name'])) {
			jsonResponse(translate('validation_invalid_file_provided'));
		}

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDiskPrefixer = $storageProvider->prefixer('public.storage');
        $publicDisk = $storageProvider->storage('public.storage');

		$id_shipper_company = (int) my_shipper_company_id();
		$company_logo = model('shippers')->get_company_logo($id_shipper_company);
		$module = 'shippers.main';

        $companyLogoPath = ShipperCompanyPathGenerator::publicLogoDefaultPath($id_shipper_company);
        $path = $publicDiskPrefixer->prefixPath($companyLogoPath);

        $publicDisk->createDirectory($companyLogoPath);

        /**
         * @todo Refactoring Library
         */

		$copy_result = library('upload')->upload_images_data(array(
			'files'       => $files,
			'destination' => $path,
			'resize'      => config("img.{$module}.resize"),
			'thumbs'      => config("img.{$module}.thumbs"),
			'rules'       => config("img.{$module}.rules")
		));

		if (!empty($copy_result['errors'])) {
			jsonResponse($copy_result['errors']);
		}

		$insert_photo = array();
		foreach($copy_result as $item){
			$insert_photo = array('logo' => $item['new_name']);
		}

		if (empty($insert_photo)) {
			jsonResponse(translate('systmess_company_info_ff_no_pictures_message'));
		}

		if (!model('shippers')->update_shipper($insert_photo, $id_shipper_company)) {
			jsonResponse(translate('validation_images_upload_fail'));
		}

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $publicDiskPrefixer = $storageProvider->prefixer('public.storage');
        $relativeLogoPath = ShipperCompanyPathGenerator::publicLogoDefaultPath($id_shipper_company);

		//remove files
		if(!empty($company_logo)){
            try {
                $publicDisk->delete($relativeLogoPath.$company_logo);
                $publicDisk->delete($relativeLogoPath.'thumb_0_'.$company_logo);
                $publicDisk->delete($relativeLogoPath.'thumb_1_'.$company_logo);
            } catch (UnableToDeleteFile $e) {
                jsonResponse(translate('systmess_error_company_info_save_failed'));
            }

			// UPDATE ACTIVITY LOG
			$this->load->model('Activity_Log_Messages_Model', 'activity_messages');
			$context = get_user_activity_context();
			$context['changes'] = array('current' => array('logo_company' => $insert_photo['logo']), 'old' => array('logo_company' => $company_logo));
			$this->activity_logger->setOperationType(DELETE_LOGO);
			$this->activity_logger->setResourceType(COMPANY);
			$this->activity_logger->setResource($id_shipper_company);
			$this->activity_logger->info($this->activity_messages->get_message(COMPANY, DELETE_LOGO), $context);
		}
		//end remove files

		$files = [
			"path"  => $publicDisk->url($relativeLogoPath . $insert_photo['logo']),
			"thumb" => $publicDisk->url($relativeLogoPath . 'thumb_1_' . $insert_photo['logo']),
        ];

        //region Update matrix profile
        // Wake up, Neo
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedShipperCompanyLogoEvent(
            (int) id_session(),
            (int) $id_shipper_company,
            $publicDiskPrefixer->prefixPath($relativeLogoPath . $insert_photo['logo'])
        ));
        //endregion Update matrix profile

		jsonResponse(translate('systmess_company_logo_updated_success_message'), 'success', $files);
	}

	public function ajax_shipper_company_upload_pictures() {
		checkIsAjax();
		checkIsLogged();
		checkGroupExpire('ajax');
		checkPermisionAjax('shipper_edit_company');

		$files = arrayGet($_FILES, 'files');
		if (null === $files) {
			jsonResponse(translate('validation_image_required'));
		}

		if (is_array($files['name'])) {
			jsonResponse(translate('validation_invalid_file_provided'));
		}

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDiskPrefixer = $storageProvider->prefixer('public.storage');
        $publicDisk = $storageProvider->storage('public.storage');

		$module = 'shippers.photos';
		$id_shipper = my_shipper_company_id();
        $companyImgPath = ShipperCompanyPathGenerator::publicImgDefaultPath($id_shipper);
        $path = $publicDiskPrefixer->prefixPath($companyImgPath);

        $publicDisk->createDirectory($companyImgPath);

		$photo_names = array();
		$count_photo = (int) model('shippers_photos')->get_pictures_count(array('id_shipper' => $id_shipper));
		$disponible = (int) config("img.{$module}.limit") - $count_photo;
		if ($disponible <= 0) {
            jsonResponse(translate('validation_cannot_upload_more_than_photos_messge', array('{{NUMBER}}' => ($disponible + $count_photo))));
		}

        /**
         * @todo Refactoring Library
         */

		$copy_result = library('upload')->upload_images_data(array(
			'files'       => $files,
			'destination' => $path,
			'resize'      => config("img.{$module}.resize"),
			'thumbs'      => config("img.{$module}.thumbs"),
			'rules'       => config("img.{$module}.rules"),
		));

		if (!empty($copy_result['errors'])) {
			jsonResponse($copy_result['errors']);
		}

		$insert_photo = $images_to_optimization = array();
		foreach ($copy_result as $item) {
			$photo_names[] = $item['new_name'];
			$insert_photo[] = array(
				'picture'  => $item['new_name'],
				'id_shipper'     => $id_shipper,
				'type_photo'     => $item['image_type'],
			);

			$images_to_optimization[] = array(
				'file_path'	=> $publicDiskPrefixer->prefixPath($companyImgPath . $item['new_name']),
				'context'	=> array('id_company' => $id_shipper),
				'type'		=> 'shipper_photos',
			);
		}

		if (empty($insert_photo)) {
			jsonResponse(translate('systmess_company_info_ff_no_pictures_message'));
		}

		if (!model('shippers_photos')->insert_pictures_batch($insert_photo)) {
			jsonResponse(translate('validation_images_upload_fail'));
		}

		$files = array_map(
			function ($file) use ($id_shipper, $module) {
				$file['path'] = getDisplayImageLink(array('{ID}' => $id_shipper, '{FILE_NAME}' => $file['picture']), $module);
				$file['thumb'] = getDisplayImageLink(array('{ID}' => $id_shipper, '{FILE_NAME}' => $file['picture']), $module, array('thumb_size' => 2));

				return $file;
			},
			model('shippers_photos')->get_pictures(array('id_shipper' => $id_shipper, 'pictures' => implode('","', $photo_names)))
		);

		if (!empty($images_to_optimization)) {
			model(Image_optimization_Model::class)->add_records($images_to_optimization);
		}

		jsonResponse('', 'success', array('files' => $files));
	}

	public function ajax_shipper_company_delete_db_picture() {
		checkIsAjax();
		checkIsLogged();
		checkGroupExpire('ajax');

		if (empty($_POST['file'])) {
			jsonResponse(translate('validation_image_required'));
		}

		$id_shipper = my_shipper_company_id();
		$photo_id = (int) $_POST['file'];
		if (
			empty($photo_id) ||
			empty($photo = model('shippers_photos')->get_picture($id_shipper, $photo_id))
		) {
			jsonResponse(translate('systmess_photo_not_exists_message'));
		}

		if (!model('shippers_photos')->delete_picture($photo['id_picture'])) {
			jsonResponse(translate('systmess_cannot_delete_picture_message'));
		}


        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        try {
            $publicDisk->delete(ShipperCompanyPathGenerator::publicImgDefaultPath($id_shipper).$photo['picture']);
            $publicDisk->delete(ShipperCompanyPathGenerator::publicImgDefaultPath($id_shipper).'thumb_2_'.$photo['picture']);

        } catch (\Throwable $th) {
            jsonResponse(translate('systmess_error_company_info_save_failed'));
        }

		jsonResponse(translate('systmess_image_delete_successfully_message'), 'success');
	}

	function ajax_company_operation(){
		checkIsAjax();

		switch(uri()->segment(3)){
			case 'edit_company_name':
				checkIsLoggedAjax();
				checkPermisionAjax('manage_content');

				$this->editCompanyName(request(), request()->request->getInt('company') ?: null, request()->request->get('type') ?: null);
			break;
			case 'edit_shipper':
				checkIsLoggedAjax();
				checkPermisionAjax('shipper_edit_company');

				$this->updateShipperCompany(
					tap(request(), function (Request $request) {
						$request->request->set('industries', new ArrayCollection(
							array_filter(array_map('intval', (array) $request->request->get('industriesSelected', array())))
						));
						$request->request->remove('industriesSelected');
					}),
                    model(Shippers_Model::class)->get_shipper((int) my_shipper_company_id()) ?: null,
					model(User_Model::class)->getSimpleUser((int) privileged_user_id()) ?: null
				);

				break;
			case 'edit_buyer':
				checkIsLoggedAjax();
				checkPermisionAjax('buyer_edit_company');

				$this->updateBuyerCompany(
                    request(),
                    model(Company_Buyer_Model::class)->get_company_by_user((int) privileged_user_id()) ?: null,
                    model(User_Model::class)->getSimpleUser((int) privileged_user_id()) ?: null,
                    0 !== request()->request->getInt('type_buyer', 0)
				);

				break;
			case 'edit':
				checkIsLoggedAjax();
				checkPermisionAjax('edit_company');
				checkHaveCompanyAjax();

                try {
                    return $this->updateSellerCompanyInformation(
                        request(),
                        $this->getContainer()->get(CompanyProcessingService::class),
                        \library(LegacySessionHandler::class),
                        \library(LegacyValidator::class)
                    );
                } catch (ValidationException $exception) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                            \iterator_to_array($exception->getValidationErrors()->getIterator())
                        )
                    );
                }

				break;
			case 'save-additional':
				checkIsLoggedAjax();
				checkPermisionAjax('edit_company');
				checkHaveCompanyAjax();

                try {
                    return $this->updateSellerCompanyAddendumInformation(
                        request(),
                        $this->getContainer()->get(AccountProvider::class),
                        $this->getContainer()->get(CompanyProcessingService::class),
                        \library(LegacyHtmlSanitizer::class),
                        \library(LegacyValidator::class),
                        \library(LegacyImageHandler::class),
                        $this->getContainer()->get(FilesystemProviderInterface::class)
                    );
                } catch (ValidationException $exception) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                            \iterator_to_array($exception->getValidationErrors()->getIterator())
                        )
                    );
                }

				break;
			case 'unlock_email':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$this->load->model('Company_Model', 'company');
				$id_company = (int)$_POST['id'];
				$email = $this->company->get_email_company($id_company);
				if(!empty($email))
					jsonResponse('','success',array('block_info' => '<span>'.$email.'</span>'));
				else
					jsonResponse(translate("systmess_error_user_does_not_exist"));
			break;
			case 'unlock_phone':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$this->load->model('Company_Model', 'company');
				$id_company = (int)$_POST['id'];
				$phone = $this->company->get_phone_company($id_company);
				if(!empty($phone))
					jsonResponse('','success',array('block_info' => '<span>'.$phone.'</span>'));
				else
					jsonResponse(translate("systmess_error_user_does_not_exist"));
			break;
			case 'unlock_fax':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$this->load->model('Company_Model', 'company');
				$id_company = (int)$_POST['id'];
				$fax = $this->company->get_fax_company($id_company);
				if(!empty($fax))
					jsonResponse('','success',array('block_info' => '<span>'.$fax.'</span>'));
				else
					jsonResponse(translate("systmess_error_user_does_not_exist"));
			break;
            case 'use_existing_info':
                checkIsLoggedAjax();
                checkPermisionAjax('edit_company');
				checkHaveCompanyAjax();

                /** @var LibraryLocator */
                $libraryLocator = $this->getContainer()->get(LibraryLocator::class);
                $this->useExistingCompanyInformation(
                    $this->getContainer()->get(CompanyProcessingService::class),
                    $libraryLocator->get(LegacySessionHandler::class),
                    $libraryLocator->get(LegacyAuthHandler::class),
                    my_company_id(),
                    id_session(),
                    request()->request->getInt('account') ?: null
                );
            break;
		}
	}

	public function ajax_company_upload_photo()
    {
		checkIsAjax();
		checkIsLogged();
		checkPermisionAjax('add_company,edit_company');
        //bookmark
        $request = request();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files');
        if (null === $uploadedFile) {
			jsonResponse(translate('validation_image_required'));
		}
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
			jsonResponse(translate('validation_invalid_file_provided'));
		}
        /** @var LegacyImageHandler $imageHandler */
        $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(LegacyImageHandler::class);
        // Given that we need to validate the file and it would take a long time
        // to write the new proper validation we can only use what we have.
        try {
            $imageHandler->assertImageIsValid(
                $imageHandler->makeImageFromFile($uploadedFile),
                config("img.companies.main.rules")
            );
        } catch (ValidationException $e) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                    \iterator_to_array($e->getValidationErrors()->getIterator())
                )
            );
        } catch (ReadException $e) {
            jsonResponse(translate('validation_images_upload_fail'), 'error', withDebugInformation(
                [],
                ['exception' => throwableToArray($e)]
            ));
        }
        // But first we need to get the full path to the file
        $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), $uploadedFile->getClientOriginalExtension());
        $pathToFile = FilePathGenerator::uploadedFile($fileName);
        // Next we need to take our filesystem for temp directory
        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        // And write file there
        try {
            $tempDisk->write($pathToFile, $uploadedFile->getContent());
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('validation_images_upload_fail'));
        }

        jsonResponse(null, 'success', [
            'image' => ['path' => $pathToFile, 'name' => $fileName]
        ]);
	}

    public function popup_forms(): void
    {
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		$this->_load_main();
		$op = $this->uri->segment(3);

        try {
            switch($op){
                case 'edit':
                    $this->showEditPopup(
                        $this->getContainer()->get(CompanyEditViewRenderer::class),
                        $this->getContainer()->get(CompanyEditRequestDocumentsService::class),
                        $this->getContainer()->get(PhoneCodesService::class)
                    );

                    break;
                case 'invite_external_feedback':
                    checkIsLoggedAjaxModal();
                    checkPermisionAjaxModal('sell_item');

                    $this->load->model('Invite_Model', 'invite');

                    $id_user = id_session();
                    $invite = $this->invite->get_invite_by_condition(array('type_invite' => 2, 'id_user' => $id_user, 'date_invite' => date('Y-m-d')));

                    $data['invite_count'] = 15;
                    if(!empty($invite)){
                        $data['invite_count'] -= $invite['count_invite'];
                    }

                    $this->view->assign($data);
                    $this->view->display('new/user/seller/popup_invite_feedback_email_view');

                    break;
                case 'invite_customers':
                    checkIsLoggedAjaxModal();
                    checkPermisionAjaxModal('sell_item');

                    $this->load->model('Invite_Model', 'invite');

                    $id_user = id_session();
                    $invite = $this->invite->get_invite_by_condition(array('type_invite' => 1, 'id_user' => $id_user, 'date_invite' => date('Y-m-d')));

                    $data['invite_count'] = 15;
                    if(!empty($invite)){
                        $data['invite_count'] -= $invite['count_invite'];
                    }

                    $this->view->assign($data);
                    $this->view->display('new/user/seller/popup_invite_email_view');

                    break;
                case 'edit_company_name':
                    checkIsLoggedAjaxModal();
                    checkPermisionAjaxModal('manage_content');

                    $this->showEditCompanyNamePopup((int) uri()->segment(4) ?: null, request()->query->get('type'));

                    break;
                default:
                    messageInModal(translate('systmess_error_route_not_found', null, true));

                    break;
            }
        } catch (NotFoundException $e) {
            messageInModal(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_invalid_data', null, true);
                    }
                }
            )));
        } catch (AccessDeniedException | OwnershipException $e) {
            messageInModal(throwableToMessage($e, with(
                $e->getCode(),
                // It would be good to have `match` feature here, but we don't have it :(
                function (int $code) {
                    switch ($code) {
                        default: return translate('systmess_error_permission_not_granted', null, true);
                    }
                }
            )));
        }
	}

	function branch_popup_forms(){
		$error = false;
		if(!isAjaxRequest()){
			headerRedirect();
		}
		if(!logged_in()){
			messageInModal(translate("systmess_error_should_be_logged"));
		}

		$this->_load_main();
		$op = $this->uri->segment(3);
		$id = (int)$this->uri->segment(4);

		switch($op){
			case 'share_company':
				if(!have_right('share_this'))
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				if(!$id){
					messageInModal('Error: Incorect company information. Please refresh this page.');
				}
				if(!$this->company->exist_company_branch(array('company' => $id))){
					messageInModal('Error: This company does not exist. Please refresh this page.');
				}

				$data['id_item'] = $id;
                $data['action'] = "seller/company_name/ajax_send_email/share";
                $data['message'] = translate("share_form_message_company");

				$this->view->assign($data);
				$this->view->display('new/user/share/popup_email_share_view');
			break;
			case 'email_company':
				if(!have_right('email_this'))
					messageInModal(translate("systmess_error_rights_perform_this_action"));
				if(!$id){
					messageInModal('Error: Incorect company information. Please refresh this page.');
				}
				if(!$this->company->exist_company_branch(array('company' => $id))){
					messageInModal('Error: This company does not exist. Please refresh this page.');
				}

                $data['type'] = "email";
                $data['id_item'] = $id;
                $data['action'] = "seller/company_name/ajax_send_email/email";
                $data['message'] = translate("share_form_message_company");

				$this->view->assign($data);
				$this->view->display('new/user/share/popup_email_share_view');
			break;
		}
	}

	function ajax_send_email(){
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		if (!logged_in()) {
			jsonResponse(translate('systmess_error_should_be_logged_in'));
		}

		$this->_load_main();/* load main models*/
		$this->load->model("Followers_model", 'followers');

		$op = $this->uri->segment(3);
		is_allowed("freq_allowed_send_email_to_user");

		switch($op){
			case 'invite_external_feedback':
				if(!have_right('sell_item')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => 'Message',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'emails',
						'label' => 'Email address',
						'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_emails_count[15]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$emails = $_POST['emails'];
				$filteredEmails = filter_email($emails);

				if(empty($filteredEmails)){
					jsonResponse('Error: Please write at least one valid email address.');
				}

				$nrInvitesSend = $nrFilteredEmails = count($filteredEmails);
				$idUser = id_session();
				$company = model(Company_Model::class)->get_company(['id_user' => $idUser]);
				$invite = model(Invite_Model::class)->get_invite_by_condition(['type_invite' => 2, 'id_user' => $idUser]);

				if(!empty($invite)){
					$dateInvite = getDateFormat($invite['date_invite'], 'Y-m-d H:i:s', 'Y-m-d') == date('Y-m-d') ? true : false;
					$nrInvitesSend = $dateInvite ? $nrFilteredEmails + $invite['count_invite'] : $nrFilteredEmails;

					if($nrInvitesSend > 15 && $dateInvite){
						$errorMessage = 'Error: You cannot send emails now because your limit for today is exceeded. Maximum invites 15 per day.';

						if($invite['count_invite'] > 14){
							$errorMessage = 'Error: You can send no more than ' . (15 - $invite['count_invite']) . ' invitations for today.';
						}

						jsonResponse($errorMessage);
					}
				}

                $hash = md5($company['id_company'] . 'code');

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new InviteFeedback($company, $hash, cleanInput(request()->request->get('message'))))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                model(Invite_Model::class)->set_user_invite($idUser, 2, $nrInvitesSend);

				jsonResponse('Your email has been successfully sent.', 'success');
			break;
			case 'invite_external_customers':
				if(!have_right('sell_item')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => 'Message',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'emails',
						'label' => 'Email address',
						'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_emails_count[15]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$filteredEmails = filter_email($_POST['emails']);

				if(empty($filteredEmails)){
					jsonResponse('Error: Please write at least one valid email address.');
				}

				$nrInvitesSend = $nrFilteredEmails = count($filteredEmails);
				$idUser = id_session();
                $invite = model(Invite_Model::class)->get_invite_by_condition(['type_invite' => 1, 'id_user' => $idUser]);
                $company = model(Company_Model::class)->get_company(['id_user' => $idUser]);

				if(!empty($invite)){
					$dateInvite = getDateFormat($invite['date_invite'], 'Y-m-d H:i:s', 'Y-m-d') == date('Y-m-d') ? true : false;
					$nrInvitesSend = $dateInvite ? $nrFilteredEmails + $invite['count_invite'] : $nrFilteredEmails;

					if($nrInvitesSend > 15 && $dateInvite){
						$errorMessage = 'Error: You cannot send emails now because your limit for today is exceeded. Maximum invites 15 per day.';

						if($invite['count_invite'] > 14){
							$errorMessage = 'Error: You can send no more than ' . (15 - $invite['count_invite']) . ' invitations for today.';
						}

						jsonResponse($errorMessage);
					}
				}

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new InviteCustomers($company, cleanInput(request()->request->get('message'))))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext([
                                '[companyName]' => $company['name_company'],
                            ])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                model(Invite_Model::class)->set_user_invite($idUser, 1, $nrInvitesSend);

				jsonResponse('Your email has been successfully sent.', 'success');
			break;
			case 'email':
				if(!have_right('email_this')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$id = (int)$_POST['id'];
				if(!$id){
					jsonResponse('Error: Incorect company information. Please refresh this page.');
				}

				if(!$this->company->exist_company_branch(array('company' => $id))){
					jsonResponse('Error: This company does not exist. Please refresh this page.');
				}

				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => 'Message',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'input',
						'label' => 'Email address',
						'rules' => array('required' => '')
					),

				);
				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$filteredEmails = filter_email($_POST['input']);

				if(empty($filteredEmails)) {
					jsonResponse('Error: Please write at least one valid email address.');
				}

				$company = $this->company->get_company(array('id_company' => $id, 'type_company'=>'branch'));
                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyLogoFilePathGenerator::thumbImage($company['id_company'], $company['logo_company'], CompanyLogoThumb::MEDIUM());
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutCompany($userName, cleanInput(request()->request->get('message')), $company, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

				jsonResponse('Your email has been successfully sent.', 'success');
			break;
			case 'share':
				if(!have_right('share_this')){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$id = (int)$_POST['id'];
				if(!$id){
					jsonResponse('Error: Incorect company information. Please refresh this page.');
				}

				if(!$this->company->exist_company_branch(array('company' => $id))){
					jsonResponse('Error: This company does not exist. Please refresh this page.');
				}

				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => 'Message',
						'rules' => array('required' => '')
					)

				);
				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$idUser = privileged_user_id();

				$filteredEmails = $this->followers->getFollowersEmails($idUser);

				if(empty($filteredEmails)){
					jsonResponse('You have no followers. The message has not been sent.');
				}

				$company = $this->company->get_company(array('id_company' => $id));
                $userName = user_name_session();

                try {
                    /** @var FilesystemProviderInterface  $storageProvider */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $pathToFile = CompanyLogoFilePathGenerator::thumbImage($company['id_company'], $company['logo_company'], CompanyLogoThumb::MEDIUM());
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutCompany($userName, cleanInput(request()->request->get('message')), $company, $publicDisk->url($pathToFile)))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

				jsonResponse('Your email has been successfully sent.', 'success');
			break;
		}
	}

	/**
	 * Shows the buyer edit company page.
	 *
	 * @param int $user_id
	 */
	protected function show_buyer_edit_company_page($user_id)
	{
		//region User check
		if (empty($user_id) || empty($user = model('user')->getSimpleUser($user_id))) {
			headerRedirect("/404");
		}
		//endregion User check

		//region Company
		$company = model('company_buyer')->get_company_by_user($user_id);
		if (!$company) {
			$company = array();
		}
		//endregion Company

		//region Location
		$company_country_id = !empty($company['company_id_country']) ? (int) $company['company_id_country'] : null;
		$company_region_id = !empty($company['company_id_state']) ? (int) $company['company_id_state'] : null;
		$company_city_id = !empty($company['company_id_city']) ? (int) $company['company_id_city'] : null;
		$countries = model('country')->get_countries();
		$regions = null !== $company_country_id ? model('country')->get_states($company_country_id) : array();
		$country = null !== $company_country_id ? array('id' => $company_country_id) : null;
		$region = null !== $company_region_id ? array('id' => $company_region_id) : null;
		$city =  null !== $company_city_id ? model('country')->get_city($company_city_id) : null;
		//endregion Location

		//region Phone & Fax codes
		$phone_codes_service = new PhoneCodesService(model('country'));
		$phone_codes = $fax_codes = $phone_codes_service->getCountryCodes();

		//region Phone code
		$phone_code = $phone_codes_service->findAllMatchingCountryCodes(
			!empty($company['company_phone_code_id']) ? (int) $company['company_phone_code_id'] : null,
			!empty($company['company_phone_code']) ? (string) $company['company_phone_code'] : null, // Fallback to old phone code system
			$company_country_id, // Or falling back to company country
			PhoneCodesService::SORT_BY_PRIORITY
		)->first();
		//endregion Phone code

		//region Fax code
		$fax_code = $phone_codes_service->findAllMatchingCountryCodes(
			!empty($company['company_fax_code_id']) ? (int) $company['company_fax_code_id'] : null,
			!empty($company['company_fax_code']) ? (string) $company['company_fax_code'] : null, // Fallback to old phone code system
			$company_country_id, // Or falling back to user country
			PhoneCodesService::SORT_BY_PRIORITY
		)->first();
		//endregion Fax code
		//endregion Phone & Fax codes

		//region Assign vars
		views()->assign(array(
			'company'                    => $company,
			'user'                       => $user,
			'regions'                    => $regions,
			'countries'                  => $countries,
			'fax_codes'                  => $fax_codes,
			'phone_codes'                => $phone_codes,
			'selected_city'              => $city,
			'selected_region'            => $region,
			'selected_country'           => $country,
			'selected_phone_code'        => $phone_code,
			'selected_fax_code'          => $fax_code,
			'is_buyer_company_completed' => model('complete_profile')->is_profile_option_completed($user_id, 'buyer_company'),
		));
		//endregion Assign vars

		views(array("new/header_view", "new/directory/edit_buyer_view", "new/footer_view"));
	}

	protected function showSellerCompanyEditPage(CompanyEditViewRenderer $renderer, AccountProvider $accountProvider): void
    {
        try {
            // Render edit page
            $renderer->renderEditPage(
                $accountProvider,
                (int) my_company_id(),
                $accountProvider->getRelatedAccounts((int) privileged_user_id(), (int) principal_id())
            );
        } catch (CompanyNotFoundException $e) {
            // If not found company, then redirect to "404" page
            headerRedirect("/404");
        }
	}

    /**
     * Shows company edit popups.
     */
    protected function showEditPopup(
        CompanyEditViewRenderer $pageRenderer,
        CompanyEditRequestDocumentsService $requestProcessor,
        PhoneCodesService $phoneCodes
    ): void {
        try {
            // Render form.
            $pageRenderer->renderEditForm($requestProcessor, $phoneCodes, (int) my_company_id());
        } catch (AlreadyExistsException $e) {
            messageInModal(translate('company_edit_form_request_already_exists', null, true));
        }
    }

	/**
	 * Shows shipper company edit page.
	 *
	 * @param int $shipper_company_id
	 */
	protected function show_shipper_company_edit_page($shipper_company_id)
	{
        /** @var Shippers_Model $shippersModel */
        $shippersModel = model(Shippers_Model::class);
        /** @var Category_Model $categoryModel */
        $categoryModel = model(Category_Model::class);
        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        /** @var Shippers_photos_Model $shippersPhotosModel */
        $shippersPhotosModel = model(Shippers_photos_Model::class);

		//region Company
		if (
			empty($shipper_company_id)
			|| empty($shipper_company = $shippersModel->get_shipper($shipper_company_id))
		) {
			headerRedirect("/404");
		}

		//region Industries
		$base_industries = $categoryModel->get_industries();
		$selected_industries = arrayByKey(
			array_filter((array) $categoryModel->getCategories([
				'columns'         => "category_id, name, parent, p_or_m, parent",
				'cat_list'        => $shipper_company['industry'] = array_column(
					array_filter((array) $shippersModel->get_relation_industry_by_company_id((int) $shipper_company_id)),
					'id_industry'
				),
			])),
			'category_id'
		);
		//endregion Industries
		//endregion Company

		//region Location
		$company_country_id = !empty($shipper_company['id_country']) ? (int) $shipper_company['id_country'] : null;
		$company_region_id = !empty($shipper_company['id_state']) ? (int) $shipper_company['id_state'] : null;
		$company_city_id = !empty($shipper_company['id_city']) ? (int) $shipper_company['id_city'] : null;
		$countries = $countryModel->get_countries();
		$regions = null !== $company_country_id ? $countryModel->get_states($company_country_id) : array();
		$country = null !== $company_country_id ? array('id' => $company_country_id) : null;
		$region = null !== $company_region_id ? array('id' => $company_region_id) : null;
		$city =  null !== $company_city_id ? $countryModel->get_city($company_city_id) : null;
		//endregion Location

		//region Phone & Fax codes
		$phone_codes_service = new PhoneCodesService($countryModel);
		$phone_codes = $fax_codes = $phone_codes_service->getCountryCodes();

		//region Phone code
		$phone_code = $phone_codes_service->findAllMatchingCountryCodes(
			!empty($shipper_company['id_phone_code']) ? (int) $shipper_company['id_phone_code'] : null,
			!empty($shipper_company['phone_code']) ? (string) $shipper_company['phone_code'] : null, // Fallback to old phone code system
			$company_country_id, // Or falling back to company country
			PhoneCodesService::SORT_BY_PRIORITY
		)->first();
		//endregion Phone code

		//region Fax code
		$fax_code = $phone_codes_service->findAllMatchingCountryCodes(
			!empty($shipper_company['id_fax_code']) ? (int) $shipper_company['id_fax_code'] : null,
			!empty($shipper_company['fax_code']) ? (string) $shipper_company['fax_code'] : null, // Fallback to old phone code system
			$company_country_id, // Or falling back to user country
			PhoneCodesService::SORT_BY_PRIORITY
		)->first();
		//endregion Fax code
		//endregion Phone & Fax codes

		//region Photos
		$photos = array_filter((array) $shippersPhotosModel->get_pictures(array('id_shipper' => $shipper_company_id)));
		foreach ($photos as &$photo) {
			$photo['url'] = getDisplayImageLink(array('{ID}' => $shipper_company_id, '{FILE_NAME}' => $photo['picture']), 'shippers.photos');
			$photo['thumb'] = getDisplayImageLink(array('{ID}' => $shipper_company_id, '{FILE_NAME}' => $photo['picture']), 'shippers.photos', array('thumb_size' => 2));
		}

		//region Uploader options
		$photo_module = 'shippers.photos';
		$mime_properties = getMimePropertiesFromFormats(config("img.{$photo_module}.rules.format"));
		$uploader_options = array(
			'rules'     => config("img.{$photo_module}.rules"),
			'limits'    => array(
				'amount'    => array('total' => (int) config("img.{$photo_module}.limit"), 'current' => (int) count($photos)),
				'accept'    => arrayGet($mime_properties, 'accept'),
				'formats'   => arrayGet($mime_properties, 'formats'),
				'mimetypes' => arrayGet($mime_properties, 'mimetypes'),
			),
			'url'       => array(
				'upload' => __CURRENT_SUB_DOMAIN_URL . "company/ajax_shipper_company_upload_pictures/",
				'delete' => __CURRENT_SUB_DOMAIN_URL . "company/ajax_shipper_company_delete_db_picture/",
			),
		);
		//endregion Uploader options

		//region Cropper options
		$photo_module = 'shippers.main';
		$mime_properties = getMimePropertiesFromFormats(config("img.{$photo_module}.rules.format"));
		$cropper_options = array(
			'url'                    => array('upload' => __CURRENT_SUB_DOMAIN_URL . "company/ajax_shipper_company_upload_logo"),
			'rules'                  => config("img.{$photo_module}.rules"),
			'accept'                 => arrayGet($mime_properties, 'accept'),
			'title_text_popup'       => 'Logo',
			'croppper_limit_by_min'  => true,
			'btn_text_save_picture'  => 'Set new logo',
			'link_thumb_main_image'  => getDisplayImageLink(
				array('{ID}' => $shipper_company_id, '{FILE_NAME}' => $shipper_company['logo']),
				$photo_module,
				array('thumb_size' => 1)
			),
			'link_main_image'        => getDisplayImageLink(
				array('{ID}' => $shipper_company_id, '{FILE_NAME}' => $shipper_company['logo']),
				$photo_module
			),
		);
		//endregion Cropper options
		//endregion Photos

		//region Assign vars
		$data = [
			'shipper'                   => $shipper_company,
			'title'                     => 'Edit company',
			'photos'                    => $photos,
			'regions'                   => $regions,
			'countries'                 => $countries,
			'fax_codes'                 => $fax_codes,
			'phone_codes'               => $phone_codes,
			'selected_city'             => $city,
			'selected_region'           => $region,
			'selected_country'          => $country,
			'selected_phone_code'       => $phone_code,
			'selected_fax_code'         => $fax_code,
			'fileupload_crop'           => $cropper_options,
			'fileupload'                => $uploader_options,
			'upload_folder'             => encriptedFolderName(),
			'total_uploaded'            => arrayGet($uploader_options, 'limits.amount.current'),
			'max_upload_limit'          => arrayGet($uploader_options, 'limits.amount.total'),
			'fileupload_max_file_size'  => arrayGet($uploader_options, 'limits.filesize.size'),
			'industries' => [
				'all'      => $base_industries,
				'selected' => $selected_industries,
            ],
        ];
		//endregion Assign vars

        $data['templateViews'] = [
            'mainOutContent'    => 'epl/directory/shipper_edit_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    /**
     * Shows the popup where administrator can edit company name.
     */
    protected function showEditCompanyNamePopup(?int $companyId, ?string $companyType): void
    {
        //region Company data
        try {
            list(, $legalCompanyName, $displayCompanyName) = $this->resolveCompanyWithNamesFromType($companyId, $companyType);
        } catch (NotFoundException $th) {
            messageInModal('This company is not found on this server.');
        }
        //endregion Company data

        views()->display('/admin/company/edit_company_name_form_view', [
            'url'          => getUrlForGroup('/company/ajax_company_operation/edit_company_name'),
            'type'         => $companyType,
            'company'      => $companyId,
            'legal_name'   => $legalCompanyName,
            'display_name' => $displayCompanyName,
        ]);
    }

	/**
	 * Updated buyer company.
	 */
	protected function updateBuyerCompany(Request $request, ?array $company, ?array $buyer, bool $isBusiness = false): void
	{
        //region Entities
        //region User
        if (empty($buyer)) {
            jsonResponse(translate("systmess_error_user_does_not_exist"));
        }

        $buyerId = (int) $buyer['idu'];
        //endregion User

        //region Individual type fallback
        if (false === ($hasCompany = !empty($company)) && !$isBusiness) {
            //region Update profile completion
            model(Complete_Profile_Model::class)->update_user_profile_option($buyerId, 'buyer_company');
            //endregion Update profile completion

            /** @var TinyMVC_Library_Auth $authenticationLibrary */
            $authenticationLibrary = library(TinyMVC_Library_Auth::class);
            $authenticationLibrary->setUserCompleteProfile($buyerId);

            jsonResponse(translate('systmess_company_info_changes_saved_message'), 'success');
        }
        //endregion Individual type fallback

        //region Company
        $companyId = (int) ($company['id'] ?? null) ?: null;
        $hasLegalName = !empty($company['company_legal_name']);
        $hasDisplayName = !empty($company['company_name']);
        //endregion Company
        //endregion Entities

        //region Validation
        $legacyValidatorAdapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validators = [
            new AddressValidator($legacyValidatorAdapter, null, null, [
                'country'       => 'country',
                'state'         => 'states',
                'city'          => 'port_city',
                'address'       => 'address',
                'postalCode'    => 'zip',
            ]),
            new PhoneValidator(
                $legacyValidatorAdapter,
                [
                    'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                    'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                    'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                ],
                ['phone' => 'Phone', 'code' => 'Phone code'],
                ['phone' => 'phone', 'code' => 'phone_code_company']
            ),
        ];

        if (!$hasLegalName || !$hasDisplayName) {
            $validators = [
                new CompanyNamesValidator(
                    $legacyValidatorAdapter,
                    !$hasLegalName,
                    !$hasDisplayName,
                    ['legalName' => 'company_legal_name', 'displayName' => 'company_name']
                ),
                ...$validators,
            ];
        }

        if (!empty(cleanInput($request->request->get('fax')))) {
            $validators[] = new PhoneValidator(
                $legacyValidatorAdapter,
                [
                    'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                    'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                    'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                ],
                ['phone' => 'Fax', 'code' => 'Fax code'],
                ['phone' => 'fax', 'code' => 'fax_code_company']
            );
        }

        $validator = new AggregateValidator($validators);
        if (!$validator->validate($request->request->all())) {
            \jsonResponse(
                \array_map(
                    fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        //region Update
        $parameters = $request->request;

        //region Company information
        //region Phone & Fax codes
        $phoneCodes = new PhoneCodesService(model(Country_Model::class));
        /** @var CountryCodeInterface $phone_code */
        $phoneCode = $phoneCodes->findAllMatchingCountryCodes((int) $parameters->get('phone_code_company'))->first();
        /** @var CountryCodeInterface $fax_code */
        $faxCode = $phoneCodes->findAllMatchingCountryCodes((int) $parameters->get('fax_code_company'))->first();
        //endregion Phone & Fax codes

        $update = [
            'company_id_country'      => $parameters->getInt('country'),
            'company_id_state'        => $parameters->getInt('states'),
            'company_id_city'         => $parameters->getInt('port_city'),
            'company_address'         => cleanInput($parameters->get('address')),
            'company_zip'             => cleanInput($parameters->get('zip')),
            'company_phone_code_id'   => $phoneCode ? $phoneCode->getId() : null,
            'company_phone_code'      => $phoneCode ? $phoneCode->getName() : null,
            'company_phone'           => cleanInput($parameters->get('phone')),
            'company_fax_code_id'     => $faxCode ? $faxCode->getId() : null,
            'company_fax_code'        => $faxCode ? $faxCode->getName() : null,
            'company_fax'             => cleanInput($parameters->get('fax')),
        ];
        if (!$hasLegalName || !$hasDisplayName) {
            $update = array_merge(
                $update,
                array_filter(
                    [
                        'company_legal_name' => cleanInput($parameters->get('company_legal_name')),
                        'company_name'       => cleanInput($parameters->get('company_name')),
                    ]
                )
            );
        }
        //endregion Company information

        //region Write company
        if ($hasCompany) {
            $isSuccessful = model(Company_Buyer_Model::class)->update_company($buyerId, $update);
        } else {
            $isSuccessful = !empty($companyId = (int) model(Company_Buyer_Model::class)->set_company(array_merge(['id_user' => $buyerId], $update)));
        }

        if (!$isSuccessful) {
            jsonResponse(translate('systmess_company_info_failed_to_updated_message'));
        }
        //endregion Write company

        //region Update profile completion
        model(Complete_Profile_Model::class)->update_user_profile_option($buyerId, 'buyer_company');

        /** @var TinyMVC_Library_Auth $authenticationLibrary */
        $authenticationLibrary = library(TinyMVC_Library_Auth::class);
        $authenticationLibrary->setUserCompleteProfile($buyerId);
        //endregion Update profile completion

        //region Update matrix profile
        // Wake up, Neo
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedBuyerCompanyEvent((int) $buyerId, (int) $companyId));
        //endregion Update matrix profile

        //region Update activity
        if ($hasCompany) {
            $changes = [];
            list($old, $current) = array_unified_diff($company, $update);
            if (!empty($old) && !empty($current)) {
                $changes = compact('old', 'current');
            }
            $operationType = EDIT;
        } else {
            $changes = $update;
            $operationType = ADD;
        }

        $logger = library(TinyMVC_Library_Activity_Logger::class);
        $logger->setResource($companyId);
        $logger->setResourceType(BUYER_COMPANY);
        $logger->setOperationType($operationType);
        $logger->info(model(Activity_Log_Messages_Model::class)->get_message(BUYER_COMPANY, $operationType), array_merge(
            get_user_activity_context(),
            [
                'changes' => $changes,
                'company' => [
                    'name' => $update['company_name'],
                ],
            ]
        ));
        //endregion Update activity
        //endregion Update

        jsonResponse(translate('systmess_company_info_changes_saved_message'), 'success', ['company_added' => true, 'is_business' => $isBusiness]);
	}

    /**
     * Update seller company information
     */
    protected function updateSellerCompanyInformation(
        Request $request,
        CompanyProcessingService $processingService,
        LegacySessionHandler $sessionHandler,
        LegacyValidator $legacyValidator
    ): void {
        //region Validate
        $adapter = new ValidatorAdapter($legacyValidator);
        $validator = new AggregateValidator([
            new CompanyTypeValidator($adapter, (int) group_session()),
            new CompanyNamesValidator($adapter, true, true, ['legalName' => 'legal_name', 'displayName' => 'display_name']),
            new CompanyLocationValidator($adapter),
            new PhoneValidator(
                $adapter,
                [
                    'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                    'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                    'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                ],
                ['phone' => 'Phone', 'code' => 'Phone code'],
                ['phone' => 'phone', 'code' => 'phone_code']
            ),
            new PhoneValidator(
                $adapter,
                [
                    'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                    'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                    'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                ],
                ['phone' => 'Fax', 'code' => 'Fax code'],
                ['phone' => 'fax', 'code' => 'fax_code'],
                false
            ),
            new AddressValidator($adapter, null, null, [
                'country'    => 'country',
                'state'      => 'region',
                'city'       => 'city',
                'address'    => 'address',
                'postalCode' => 'postal_code',
            ]),
        ]);
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to create edit request due to validation errors.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        //region Save
        try {
            // Update company
            $updatedCompany = $processingService->saveGeneralCompanyInformation(
                $request,
                (int) my_company_id(),
            );

            /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
            $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
            $elasticsearchUsersModel->sync((int) id_session());

            // Alter some information in the current session after we got the updated company.
            $sessionHandler->set('name_company', $updatedCompany['name_company']);
            $sessionHandler->set('group_name_suffix', $updatedCompany['type']['group_name_suffix']);
        } catch (WriteException $e) {
            jsonResponse(translate('systmess_company_info_failed_to_updated_message'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }
        //endregion Save

        jsonResponse(translate('systmess_information_successfully_changed'), 'success', [
            'url'    => check_group_type('Seller,Buyer,CR Affiliate,Shipper') ? getMyProfileLink() : null,
            'isEdit' => true,
        ]);
    }

    /**
     * Update seller company's additional information
     */
    protected function updateSellerCompanyAddendumInformation(
        Request $request,
        AccountProvider $accountProvider,
        CompanyProcessingService $processingService,
        LegacyHtmlSanitizer $sanitaizer,
        LegacyValidator $legacyValidator,
        LegacyImageHandler $imageHandler,
        FilesystemProviderInterface $filesystemProvider
    ): void {
        $companyId = (int) my_company_id();

        //region Validate
        $adapter = new ValidatorAdapter($legacyValidator);
        $validator = new AggregateValidator(
            array_filter([
                $request->request->has('logo')
                    ? new CompanyLogoValidator(
                        $adapter,
                        $imageHandler,
                        $filesystemProvider->storage('temp.storage'),
                        $filesystemProvider->prefixer('temp.storage'),
                        config("img.companies.main.rules") ?? []
                    )
                    : null,
                have_right('id_generated') && $request->request->has('index_name')
                    ? new CompanyNameIndexValidator($adapter, $filesystemProvider->storage('root.storage'), $companyId)
                    : null,
                new CompanyEmailValidator($adapter),
                new CompanyRevenueValidator($adapter),
                new CompanyEmployeesValidator($adapter),
                new CompanyDescriptionValidator($adapter),
                new CompanyCategoriesValidator($adapter, null, null, ['categories' => 'selected_categories']),
                new CompanyIndustriesValidator($adapter, (int) config('user_industries_of_interest_limit', 3), 0, null, null, ['industries' => 'selected_industries']),
                new CompanyVideoValidator($adapter),
                $request->request->has('image') ? new CompanyImageValidator($adapter) : null,
            ])
        );
        tap(request(), function (Request $request) {
            $industries = array_filter(array_map('intval', (array) $request->request->get('industriesSelected', [])));
            $categories = array_filter(array_map('intval', (array) $request->request->get('categoriesSelected', [])));
            $request->request->set('selected_industries', new ArrayCollection($industries));
            $request->request->set('selected_categories', new ArrayCollection($categories));
            $request->request->set('industries', $industries);
            $request->request->set('categories', $categories);
            $request->request->remove('industriesSelected');
            $request->request->remove('categoriesSelected');
        });
        if (!$validator->validate($request->request->all())) {
            throw new ValidationException('Failed to create edit request due to validation errors.', 0, null, $validator->getViolations());
        }
        //endregion Validate

        //region Save
        try {
            // Update company
            $updatedCompany = $processingService->saveAdditionalCompanyInformation(
                $request,
                $sanitaizer,
                $companyId,
                array_keys($accountProvider->getRelatedAccounts((int) privileged_user_id(), (int) principal_id()))
            );
            // Alter some information in the current session after we got the updated company.
            session()->set('group_name_suffix', $updatedCompany['type']['group_name_suffix'] ?? '');
        } catch (WriteException $e) {
            jsonResponse(translate('systmess_company_info_failed_to_updated_message'), 'error', withDebugInformation([], [
                'exception' => throwableToArray($e),
            ]));
        }
        //endregion Save

        jsonResponse(translate('systmess_company_info_changes_saved_message'), 'success', [
            'url'    => getCompanyURL($updatedCompany),
            'isEdit' => true,
        ]);
    }

	/**
	 * Updates freight forwarder company.
	 */
	protected function updateShipperCompany(Request $request, ?array $company, ?array $shipper): void
	{
        //region Entities
        //region Freight forwarder
        if (empty($shipper)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $shipperId = (int) $shipper['idu'];
        //endregion Freight forwarder

        //region Company
        if (empty($company)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $companyId = (int) $company['id'];
        $hasLegalName = !empty($company['legal_co_name']);
        $hasDisplayName = !empty($company['co_name']);
        //endregion Company
        //endregion Entities

        //region Validation
        $legacyValidatorAdapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validators = [
            new CompanyShipperValidator($legacyValidatorAdapter),
            new CompanyDescriptionValidator($legacyValidatorAdapter),
            new CompanyIndustriesValidator($legacyValidatorAdapter),
            new CompanyWebsiteValidator($legacyValidatorAdapter),
            new CompanyVideoValidator($legacyValidatorAdapter),
            new AddressValidator($legacyValidatorAdapter, null, null, [
                'country'       => 'country',
                'state'         => 'states',
                'city'          => 'port_city',
                'address'       => 'address',
                'postalCode'    => 'zip',
            ]),
            new PhoneValidator(
                $legacyValidatorAdapter,
                [
                    'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                    'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                    'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                ],
                ['phone' => 'Phone', 'code'  => 'Phone code'],
                ['phone' => 'phone', 'code'  => 'phone_code_company']
            ),
        ];

        if (!$hasLegalName || !$hasDisplayName) {
            $validators = [
                new CompanyNamesValidator(
                    $legacyValidatorAdapter,
                    !$hasLegalName,
                    !$hasDisplayName,
                    ['legalName' => 'original_name', 'displayName' => 'co_name']
                ),
                ...$validators,
            ];
        }

        if (!empty(cleanInput($request->request->get('fax')))) {
            $validators[] = new PhoneValidator(
                $legacyValidatorAdapter,
                [
                    'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                    'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                    'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_fax')]),
                ],
                ['phone' => 'Fax', 'code' => 'Fax code'],
                ['phone' => 'fax', 'code' => 'fax_code_company'],
            );
        }

        if (!model(Shippers_Model::class)->has_logo($companyId)) {
            $validators[] = new CompanyImageValidator($legacyValidatorAdapter);
        }

        $validator = new AggregateValidator($validators);
        if (!$validator->validate(request()->request->all())) {
            \jsonResponse(
                \array_map(
                    fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        //region Update
        $parameters = $request->request;

        //region Prepare information
        //region Phone & Fax codes
        $phoneCodes = new PhoneCodesService(model('country'));
        /** @var CountryCodeInterface $phoneCode */
        $phoneCode = $phoneCodes->findAllMatchingCountryCodes((int) $parameters->get('phone_code_company'))->first();
        /** @var CountryCodeInterface $faxCode */
        $faxCode = $phoneCodes->findAllMatchingCountryCodes((int) $parameters->get('fax_code_company'))->first();
        //endregion Phone & Fax codes

        $update = [
            'id_country'     => (int) $parameters->get('country'),
            'id_state'       => (int) $parameters->get('states'),
            'id_city'        => (int) $parameters->get('port_city'),
            'description'    => library(TinyMVC_Library_Cleanhtml::class)->sanitizeUserInput($parameters->get('description')),
            'offices_number' => cleanInput($parameters->get('company_offices_number')),
            'co_website'     => cleanInput($parameters->get('website')),
            'co_duns'        => cleanInput($parameters->get('company_duns')),
            'co_teu'         => cleanInput($parameters->get('company_teu')),
            'tax_id'         => cleanInput($parameters->get('company_tax_id')),
            'video'          => cleanInput($parameters->get('video')),
            'address'        => cleanInput($parameters->get('address')),
            'zip'            => cleanInput($parameters->get('zip')),
            'id_phone_code'  => $phoneCode ? $phoneCode->getId() : null,
            'phone_code'     => $phoneCode ? $phoneCode->getName() : null,
            'phone'          => cleanInput($parameters->get('phone')),
            'id_fax_code'    => $faxCode ? $faxCode->getId() : null,
            'fax_code'       => $faxCode ? $faxCode->getName() : null,
            'fax'            => cleanInput($parameters->get('fax')),
        ];

        if (!$hasLegalName || !$hasDisplayName) {
            $update = array_merge(
                $update,
                array_filter(
                    [
                        'legal_co_name' => cleanInput($parameters->get('original_name')),
                        'co_name'       => cleanInput($parameters->get('co_name')),
                    ]
                )
            );
        }
        //endregion Prepare information

        //region Update company
        if (!model(Shippers_Model::class)->update_shipper($update, $companyId)) {
            jsonResponse(translate('systmess_company_info_failed_to_updated_message'));
        }

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->sync((int) $shipperId);
        //endregion Update company

        //region Update industries
        model(Shippers_Model::class)->delete_relation_industry_by_company($companyId);
        model(Shippers_Model::class)->set_relation_industry(
            $companyId,
            ($request->request->get('industries') ?? new ArrayCollection())->getValues()
        );
        //endregion Update industries

        //region Update profile completion
        model(Complete_Profile_Model::class)->update_user_profile_option($shipperId, 'company_main');

        /** @var TinyMVC_Library_Auth $authenticationLibrary */
        $authenticationLibrary = library(TinyMVC_Library_Auth::class);
        $authenticationLibrary->setUserCompleteProfile($shipperId);
        //endregion Update profile completion

        //region Update matrix profile
        // Wake up, Neo
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedShipperCompanyEvent($shipperId, (int) $companyId));
        //endregion Update matrix profile

        //region Update activity
        $changes = [];
        list($old, $current) = array_unified_diff($company, $update);
        if (!empty($old) && !empty($current)) {
            $changes = compact('old', 'current');
        }

        $logger = library(TinyMVC_Library_Activity_Logger::class);
        $logger->setOperationType(EDIT);
        $logger->setResourceType(SHIPPER_COMPANY);
        $logger->setResource($companyId);
        $logger->info(model(Activity_Log_Messages_Model::class)->get_message(SHIPPER_COMPANY, EDIT), array_merge(
            get_user_activity_context(),
            [
                'changes' => $changes,
                'company' => [
                    'name' => $update['co_name'],
                    'url'  => getShipperURL(['id' => $companyId, 'co_name' => $update['co_name']]),
                ],
            ]
        ));
        //endregion Update activity
        //endregion Update

        jsonResponse(translate('systmess_company_info_changes_saved_message'), 'success', [
            'url' => i_have_shipper_company() ? getShipperURL($company) : null,
        ]);
	}

    /**
     * Edit company name.
     */
	protected function editCompanyName(Request $request, ?int $companyId, ?string $companyType): void
	{
        //region Company
        try {
            list($company, $originalLegalName, $originalDisplayName, $userId) = $this->resolveCompanyWithNamesFromType($companyId, $companyType);
        } catch (NotFoundException $th) {
            jsonResponse('This company is not found on this server.');
        }
        //endregion Company

        //region Validation
        $legacyValidatorAdapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new CompanyNamesValidator(
            $legacyValidatorAdapter,
            true,
            true,
            ['legalName' => 'legal_name', 'displayName' => 'display_name'],
            ['legalName' => 'Legal Company Name', 'displayName' => 'Company Name'],
        );
        if (!$validator->validate($request->request->all())) {
            \jsonResponse(
                \array_map(
                    fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        //region Update
        $legalName = cleanInput($request->request->get('legal_name'));
        $displayName = cleanInput($request->request->get('display_name'));

        //region Activity logger
        $logger = library(TinyMVC_Library_Activity_Logger::class);
        $logger->setOperationType(ADMIN_EDIT);
        $logger->setResource($companyId);
        //endregion Activity logger

        $log = function (int $resourceType, array $oldValues, array $newValues, string $displayName, ?string $url = null) use ($logger) {
            list($old, $current) = array_unified_diff($oldValues, $newValues);
            $logger->setResourceType($resourceType);
            $logger->info(model(Activity_Log_Messages_Model::class)->get_message($resourceType, ADMIN_EDIT), array_merge(
                get_user_activity_context(),
                [
                    'changes' => compact('old', 'current'),
                    'company' => [
                        'name' => $displayName,
                        'url'  => $url,
                    ],
                ]
            ));
        };
        $updaters = [
            'buyer'   => function (int $companyId, int $userId, string $legalName, string $displayName) use (
                $log,
                $originalLegalName,
                $originalDisplayName
            ) {
                $update = ['company_legal_name' => $legalName, 'company_name' => $displayName];
                $old = ['company_legal_name' => $originalLegalName, 'company_name' => $originalDisplayName];

                return tap(
                    model(Company_Buyer_Model::class)->update_company($userId, $update),
                    function ($isSuccessful) use ($log, $old, $update, $displayName) {
                        if ($isSuccessful) {
                            $log(BUYER_COMPANY, $old, $update, $displayName);
                        }
                    }
                );
            },
            'seller'  => function (int $companyId, int $userId, string $legalName, string $displayName) use (
                $log,
                $company,
                $originalLegalName,
                $originalDisplayName
            ) {
                $update = ['legal_name_company' => $legalName, 'name_company' => $displayName];
                $old = ['legal_name_company' => $originalLegalName, 'name_company' => $originalDisplayName];

                return tap(
                    model(Company_Model::class)->update_company($companyId, $update),
                    function ($isSuccessful) use ($log, $old, $update, $company, $displayName) {
                        if ($isSuccessful) {
                            $log(COMPANY, $old, $update, $displayName, getCompanyURL(array_merge($company, $update)));
                        }
                    }
                );
            },
            'shipper' => function (int $companyId, int $userId, string $legalName, string $displayName) use (
                $log,
                $originalLegalName,
                $originalDisplayName
            ) {
                $update = ['legal_co_name' => $legalName, 'co_name' => $displayName];
                $old = ['legal_co_name' => $originalLegalName, 'co_name' => $originalDisplayName];

                return tap(
                    model(Shippers_Model::class)->update_shipper($update, $companyId),
                    function ($isSuccessful) use ($log, $old, $update, $companyId, $displayName) {
                        if ($isSuccessful) {
                            $log(SHIPPER_COMPANY, $old, $update, $displayName, getShipperURL(['id' => $companyId, 'co_name' => $displayName]));
                        }
                    }
                );
            },
        ];

        if (!$updaters[$companyType]($companyId, (int) $userId, $legalName, $displayName)) {
            jsonResponse('Failed to update company name.');
        }

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->sync((int) $userId);

        //region Update matrix profile
        // Wake up, Neo
        switch ($companyType) {
            case 'buyer':
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedBuyerCompanyEvent((int) $userId, (int) $companyId));

                break;
            case 'seller':
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedSellerCompanyEvent((int) $userId, (int) $companyId));

                break;
            case 'shipper':
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedShipperCompanyEvent((int) $userId, (int) $companyId));

                break;
        }
        //endregion Update matrix profile
        //endregion Update

		jsonResponse('The company name has been successfully updated.', 'success');
	}

    /**
	 * Creates the company for shipper.
	 *
	 * @param int $user_id
	 *
	 * @throws NotFoundException if user does not exist
	 * @throws RuntimeExcetion if failed to create the company
	 */
	private function create_shipper_company($user_id)
	{
        //region Check user
        if (
            empty($user_id)
            || empty($user = model('user')->getLoginInfoById($user_id))
        ) {
            throw new NotFoundException("The user with ID #{$user_id} is not found");
        }
        //endregion Check user

        //region Create company
        //region Prepare information
        $company = array(
            'id_user'            => $user_id,
            'id_country'         => (int) $user['country'],
            'id_city'            => (int) $user['city'],
            'id_state'           => (int) $user['state'],
            'zip'                => $user['zip'],
            'address'            => $user['address'],
            'id_phone_code'      => $user['phone_code_id'],
            'phone_code'         => $user['phone_code'],
            'phone'              => $user['phone'],
            'id_fax_code'        => $user['fax_code_id'],
            'fax_code'           => $user['fax_code'],
            'fax'                => $user['fax'],
            'email'              => $user['email'],
            'accreditation'      => $user['is_verified']
        );
        //endregion Prepare information

        try {
            if (!$company_id = model('shippers')->insert_shipper($company)) {
                throw new Exception("Failed to create company");
            }

            model('shippers')->set_shipper_user_relation(array('id_shipper' => $company_id, 'id_user' => $user_id));
        } catch (\Exception $exception) {
            throw new RuntimeException("The query for company creation failed.");
        }
        //endregion Create company

        //region Update matrix profile
        // Wake up, Neo
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedShipperCompanyEvent((int) $user_id, (int) $company_id));
        //endregion Update matrix profile

        //region Update activity
        $logger = library('activity_logger');
        $logger->setOperationType(ADD);
        $logger->setResourceType(SHIPPER_COMPANY);
        $logger->setResource($company_id);
        $logger->info(model('activity_log_messages')->get_message(SHIPPER_COMPANY, ADD), array_merge(
            get_user_activity_context(),
            array('name' => null, 'url'  => getShipperURL(array('id' => $company_id, 'co_name' => 'Company name')), 'changes' => array('old' => array(), 'current' => $company))
        ));
        //endregion Update activity

        //region Update session
        tap(library('auth'), function (TinyMVC_Library_Auth $auth) use ($user) {
            $auth->user = $user;
            $auth->_get_user_company();
        });
        //endregion Update session
    }

	/**
	 * Creates the company for seller.
	 *
	 * @param int $user_id
	 *
	 * @throws NotFoundException if user does not exist
	 * @throws RuntimeExcetion if failed to create the company
	 */
	private function create_seller_company($user_id)
	{
		//region Check user
		if (
			empty($user_id)
			|| empty($user = model('user')->getLoginInfoById($user_id))
		) {
			throw new NotFoundException(translate('systmess_company_info_user_id_not_found_message', ['{{USER_ID}}' => $user_id]));
		}
		//endregion Check user

		//region Create company
		//region Prepare information
		$company = array(
			'id_user'            => $user_id,
			'id_type'            => in_array($user['user_group'], array(2,3)) ? 2 : 1,
			'id_country'         => (int) $user['country'],
			'id_city'            => (int) $user['city'],
			'id_state'           => (int) $user['state'],
			'zip_company'        => $user['zip'],
			'email_company'      => $user['email'],
			'phone_code_company' => $user['phone_code'],
			'phone_company'      => $user['phone'],
			'address_company'    => $user['address'],
			'accreditation'      => $user['is_verified']
		);
		//endregion Prepare information

		try {
			if (!$company_id = model('company')->set_company($company)) {
				throw new Exception(translate('systmess_company_info_failed_to_create_company_message'));
			}

			model('company')->set_company_user_rel(array(
				array('id_company' => $company_id, 'id_user' => $user_id, 'company_type' => 'company')
			));
		} catch (\Exception $exception) {
			throw new RuntimeException(translate('systmess_company_info_failed_creation_query_message'));
		}
		//endregion Create company

        //region Update matrix profile
        // Wake up, Neo
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserUpdatedSellerCompanyEvent((int) $user_id, (int) $company_id));
        //endregion Update matrix profile

		//region Update activity
		$logger = library('activity_logger');
		$logger->setOperationType(ADD);
		$logger->setResourceType(COMPANY);
		$logger->setResource($company_id);
		$logger->info(model('activity_log_messages')->get_message(COMPANY, ADD), array_merge(
			get_user_activity_context(),
			array('name' => null, 'url'  => getCompanyURL($company), 'changes' => array('old' => array(), 'current' => $company))
		));
		//endregion Update activity

		//region Update session
		tap(library('auth'), function (TinyMVC_Library_Auth $auth) use ($user) {
			$auth->user = $user;
			$auth->_get_user_company();
		});
		//endregion Update session
	}

	/**
	 * Finds company coordinates using geocoding.
	 *
	 * @param array    $parameters
	 * @param null|int $company_id
	 *
	 * @return float[]
	 */
	private function geocode_company_coordinates(array $parameters, $company_id = null)
	{
		if (empty($parameters)) {
			return array(0.0, 0.0);
		}

		$latitude = 0.0;
		$longitude = 0.0;
		$geodata = library('gmap')->get_geocode($parameters);
		if('OK' === $geodata['status']){
			$latitude = arrayGet($geodata, 'results.0.geometry.location.lat', 0.0);
			$longitude = arrayGet($geodata, 'results.0.geometry.location.lng', 0.0);

			//region Update coordinates
			if (null !== $company_id) {
				model('company')->update_company($company_id, array(
					'latitude'  => $latitude,
					'longitude' => $longitude
				));
			}
			//endregion Update coordinates
		}

		return array($latitude, $longitude);
    }

    /**
     * Resolve company names from ID and type.
     *
     * @throws NotFoundException if company is not found.
     */
    private function resolveCompanyWithNamesFromType(?int $companyId, ?string $companyType): array
    {
        $resolver = [
            'buyer'   => fn (int $companyId): array => [
                $company = model(Company_Buyer_Model::class)->get_company($companyId),
                $company['company_legal_name'] ?? null,
                $company['company_name'] ?? null,
                $company['id_user'] ?? null,
            ],
            'seller'  => fn (int $companyId): array => [
                $company = model(Company_Model::class)->get_simple_company($companyId),
                $company['legal_name_company'] ?? null,
                $company['name_company'] ?? null,
                $company['id_user'] ?? null,
            ],
            'shipper' => fn (int $companyId): array => [
                $company = model(Shippers_Model::class)->get_shipper($companyId),
                $company['legal_co_name'] ?? null,
                $company['co_name'] ?? null,
                $company['id_user'] ?? null,
            ],
        ];

        $companyData = [];
        if (isset($resolver[$companyType]) && null !== $companyId) {
            $companyData = $resolver[$companyType]($companyId);
        }

        if (null === ($companyData[0] ?? null)) {
            throw new NotFoundException('The company is not found');
        }

        return $companyData;
    }

    /**
     * Use existing comapany informatio to update the current one.
     *
     * @param CompanyProcessingService $processingService the company processing service
     * @param LegacySessionHandler $sessionHandler        the session handler
     * @param LegacyAuthHandler $authHandler              the authentication handler
     * @param integer $companyId                          the current company ID
     * @param integer $userId                             the current user ID
     * @param integer|null $sourceAccountId               the source account ID
     */
    private function useExistingCompanyInformation(
        CompanyProcessingService $processingService,
        LegacySessionHandler $sessionHandler,
        LegacyAuthHandler $authHandler,
        int $companyId,
        int $userId,
        ?int $sourceAccountId
    ): void {
        try {
            $processingService->importCompanyInformation($companyId, $sourceAccountId, $userId);
            $authHandler->setUserCompleteProfile($userId);
            $sessionHandler->setMessages('The company information was successfully copied from your another account');
        } catch (MismatchStatusException | ProfileCompletionException | AccessDeniedException $e) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        jsonResponse('', 'success');
    }
}
