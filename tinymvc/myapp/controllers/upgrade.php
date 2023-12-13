<?php

use App\Common\Database\Relations\RelationInterface;
use App\Common\Traits\HmacAccessTrait;
use App\Common\Traits\ModalUriReferenceTrait;
use App\Common\Traits\VersionMetadataTrait;
use App\Common\Traits\VersionStatusesMetadataAwareTrait;
use App\Documents\Serializer\VersionSerializerStatic;
use App\Documents\Versioning\VersionCollectionInterface;
use App\Documents\Versioning\VersionInterface;
use App\Email\EmailUserAboutFreeFeaturedItems;
use App\Email\PromoPackageCertified;
use App\Messenger\Message\Event\Lifecycle\UserGroupChangedEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Query\QueryBuilder;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use ExportPortal\Bridge\Symfony\Component\Messenger\MessengerInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use const App\Logger\Activity\OperationTypes\CANCEL_UPGRADE_REQUEST;
use const App\Logger\Activity\OperationTypes\CONFIRM_UPGRADE_REQUEST;
use const App\Logger\Activity\OperationTypes\START_UPGRADE_REQUEST;
use const App\Logger\Activity\ResourceTypes\UPGRADE_REQUEST;
use const App\Logger\Activity\ResourceTypes\USER;

/**
 * Controller Upgrade_Controller.
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Upgrade_Controller extends TinyMVC_Controller
{
    use HmacAccessTrait;
    use VersionMetadataTrait;
    use ModalUriReferenceTrait;
	use VersionStatusesMetadataAwareTrait;

	private $breadcrumbs = [];

	private function _load_main()
	{
		$this->load->model('User_Model', 'user');
		$this->load->model('Packages_Model', 'packages');
		$this->load->model('UserGroup_Model', 'groups');
		$this->load->model('User_Bills_Model', 'user_bills');
		$this->load->model('Upgrade_Model', 'upgrade');
	}

	public function index()
	{
		checkIsLogged();
		checkPermision('upgrade_group');

		$id_user = id_session();
		$user = model('user')->getUser($id_user);

		//region Update session variables
		session()->group_expired = (int) (validateDate($user['paid_until'], 'Y-m-d') && isDateExpired($user['paid_until']));
		session()->paid = $user['paid'];
		session()->paid_until = $user['paid_until'];
        session()->group = (int) $user['user_group'];

        $sess_accounts = session()->__get('accounts');
        if(!empty($sess_accounts)){
            foreach($sess_accounts as $sess_key => $sess_one){
                if($id_user == $sess_one['idu']){
                    $sess_accounts[$sess_key]['gr_name'] = $user['gr_name'];
                }
            }
            session()->__set('accounts', $sess_accounts);
        }

		$this->load->library('Auth', 'auth');
		$this->auth->user = $user;
		$this->auth->_get_group_and_rights();
		//region Update session variables

		//region Upgrade request
		$upgrade_request = model('upgrade')->get_latest_request([
            'conditions' => [
                'user'           => $id_user,
                'status'         => ['new', 'confirmed']
            ],
        ]);
		//endregion Upgrade request

        if (empty($upgrade_request) || $upgrade_request['type'] == 'downgrade') {
			$this->callto_upgrade($user, $upgrade_request);
		} else {
			if ($upgrade_request['status'] == 'confirmed') {
				$this->extend_upgrade_request($user, $upgrade_request);
			} else {
				$this->process_upgrade_request($user, $upgrade_request);
			}
		}
	}

    /**
     * Displays Call to action Upgrade page
     *
     * @param array $user
     */
	private function callto_upgrade(array $user, $upgrade_request){
        $id_group = (int) $user['user_group'];
        $callModal = (int) request()->query->get('call_modal');

        //region Upgrade packages
        $upgradePackagesParams = [
            'gr_from' => $id_group,
            'is_disabled' => 0
        ];

        if (filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN)) {
            $upgradePackagesParams['period'] = 5;
            $upgradePackagesParams['is_disabled'] = 1;
        }

		$upgrade_packages = model('packages')->getGrPackages($upgradePackagesParams);
		//endregion Upgrade packages

		//region Get current user group
		$current_user_group = model('usergroup')->getGroup($id_group);
		//endregion Get current user group

		//region Upgrade benefits
		$prepare_benefit_groups = function($str) {
			$str['benefit_groups'] = explode(',', $str['benefit_groups']);
			return $str;
		};

		$upgrade_groups = array_column($upgrade_packages, 'gr_to');
		$upgrade_groups[] = $id_group;

		$upgrade_benefits = array_map($prepare_benefit_groups, model('upgrade')->get_upgrade_benefits(array(
			'id_group' => $upgrade_groups,
			'order_by' => "benefit_weight ASC, benefit_groups ASC"
		)));
        //endregion Upgrade benefits
        $current_package = model('packages')->getGrPackageByCondition(['gr_from' => 0, 'gr_to' => $id_group]);

        $dateFreePackage = '';
        if (filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN)) {
            $dateFreePackage = getDateFormat($upgrade_packages[0]['fixed_end_date'], 'Y-m-d', 'j M, Y');
        }

		views(
            array(
                'new/header_view',
                'new/upgrade/callto_upgrade_view',
                'new/footer_view'
            ),
            compact(
                'callModal',
                'id_group',
                'current_user_group',
                'upgrade_packages',
                'upgrade_benefits',
                'current_package',
                'dateFreePackage',
            )
        );
	}

    /**
     * Displays Processing Upgrade page
     *
     * @param array $user
     * @param array $upgrade_request
     */
	private function process_upgrade_request(array $user, array $upgrade_request){
		$upgrade_package = model('packages')->getGrPackage((int) $upgrade_request['id_package']);

		//region Upgrade Bill
		$upgrade_bill = model('user_bills')->get_simple_bill(array(
			'id_bill' => $upgrade_request['id_bill'],
        ));
		//endregion Upgrade Bill

		//region Group
        $id_group = (int) $upgrade_package['gr_to'];
        $groups = array_column(model(UserGroup_Model::class)->getGroups(), 'gr_name', 'idgroup');
		$group = tap(model('usergroup')->getGroup($id_group), function (&$group) {
			$group['name'] = $group['gr_name'];
			$group['user_guide'] = model('user_personal_documents')->get_userguide_by_group($group['gr_alias']);
			$group['thumbnail'] = __IMG_URL . getImage("public/img/groups/{$group['stamp_pic']}", thumbNoPhoto($group['idgroup']));
		});
		//endregion Group

        //region Document
        $user_id = (int) $user['idu'];
        $principal_id = (int) $user['id_principal'];
        $prepare_document = function (array $document): array {
            if (null !== $document['latest_version']) {
                $document['latest_version'] = VersionSerializerStatic::deserialize($document['latest_version'], VersionInterface::class, 'json');
            }

            $document['metadata'] = $this->getVersionMetadata($document['latest_version']);
            $document['title'] = translate('personal_documents_unknown_document_title');
            $document['description'] = '&mdash;';
            if (!empty($document['type'])) {
                if (null !== $title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title'])) {
                    $document['title'] = $title;
                }
                if (null !== $description = accreditation_i18n($document['type']['document_i18n'], 'description', null, $document['type']['document_description'])) {
                    $document['description'] = $description;
                }
                $document_titles = json_decode($document['type']['document_titles'], true);
                if (null !== $country_title = $document_titles[session()->country]) {
                    $document['country_title'] = $country_title;
                }
            }

            return $document;
        };

        list($documents, $other_documents) = (
            new ArrayCollection((array) model('user_personal_documents')->get_documents(array(
                'with'       => array('type', 'owner'),
                'order'      => array('id_type' => 'ASC'),
                'conditions' => array(
                    'principal' => (int) $user['id_principal'],
                ),
            )))
        )
            ->filter(function ($document) { return $document; })
            ->map($prepare_document)
            ->partition(function ($i, $document) use ($user_id) { return ((int) $document['id_user']) === $user_id; })
        ;

        $types = array_map('intval', array_column($documents->getValues(), 'id_type'));
        $other_documents = (
            new ArrayCollection(arrayByKey(
                $other_documents
                    ->filter(function (array $document) use ($types) { return in_array((int) $document['id_type'], $types); })
                    ->filter(function (array $document) {
                        return null !== $document['versions']
                            && !$document['metadata']['is_version_rejected']
                            && !$document['metadata']['is_expired'];
                    })
                    ->getValues(),
                'id_type',
                true
            ))
        )->map(function (array $documents) { return new ArrayCollection($documents); });

        /** @var ArrayIterator $documents_iterator */
        $documents_iterator = $documents->getIterator();
        $documents_iterator->uasort(function($d1, $d2){ return (int) ($d1['metadata']['is_uploadable'] ?? false) <=> (int) ($d2['metadata']['is_uploadable'] ?? false); });
        $documents = new ArrayCollection(iterator_to_array($documents_iterator));
		//endregion Document;

        //region Mixed vars
        $notifications = arrayByKey(model('user')->get_notification_messages(array('message_module' => 'accreditation')), 'id_message');
		$statuses = array_merge($this->getVersionStatusesMetadata(), $this->getVersionExpirationMetadata());
        //endregion Mixed vars

		views(
            array(
                'new/header_view',
                'new/upgrade/process_upgrade_view',
                'new/footer_view'
            ),
            compact(
                'id_group',
                'group',
                'groups',
                'upgrade_request',
                'upgrade_package',
                'upgrade_bill',
                'documents',
                'statuses',
                'is_fully_verified',
                'is_verifying',
                'notifications',
                'other_documents',
            )
        );
	}

    /**
     * Displays Extend Upgrade page
     *
     * @param array $user
     * @param array $upgrade_request
     */
	private function extend_upgrade_request(array $user, array $upgrade_request){
        $current_package = model('packages')->getGrPackage((int) $upgrade_request['id_package']);

		$id_group = (int) $user['user_group'];
        $group = tap(model('usergroup')->getGroup($id_group), function (&$group) {
			$group['name'] = $group['gr_name'];
			$group['thumbnail'] = __IMG_URL . getImage("public/img/groups/{$group['stamp_pic']}", thumbNoPhoto($group['idgroup']));
		});

		//region Upgrade packages
		$upgrade_packages = model('packages')->getGrPackages(array(
			'gr_from' => $id_group,
			'is_disabled' => 0
        ));
		//endregion Upgrade packages

		//region Downgrade packages
		$downgrade_packages = model('packages')->getGrPackages(array(
            'gr_to' => $current_package['downgrade_gr_to'],
            'is_disabled' => 0
		));
		//endregion Downgrade packages

		//region Upgrade benefits
		$upgrade_groups = array_column($upgrade_packages, 'gr_to');
		$upgrade_groups[] = $id_group;

		$upgrade_benefits = array_map(function($str) {
			$str['benefit_groups'] = explode(',', $str['benefit_groups']);
			return $str;
		}, model('upgrade')->get_upgrade_benefits(array(
			'id_group' => $upgrade_groups,
			'order_by' => "benefit_weight ASC, benefit_groups ASC"
		)));
        //endregion Upgrade benefits

		views(
            array(
                'new/header_view',
                'new/upgrade/extend_upgrade_view',
                'new/footer_view'
            ),
            compact(
                'id_group',
                'current_user_group',
                'current_package',
                'downgrade_packages',
                'upgrade_packages',
                'upgrade_benefits',
                'group',
                'upgrade_request',
            )
        );
	}

    /**
     * Upgrade requests administration page
     */
	public function requests()
    {
        checkIsLogged();
		checkPermision('manage_upgrade_requests');

        views(array('admin/header_view', 'admin/upgrade/index_view', 'admin/footer_view'), array(
            'title'            => "Upgrade requests"
        ));
	}

	public function ajax_operations()
	{
		checkIsAjax();
		checkIsLoggedAjax();
		checkPermisionAjax('upgrade_group');

		$id_user = privileged_user_id();
		$id_principal = principal_id();
		$this->_load_main();
        /** @var Verification_Document_Types_Model */
        $verificationTypes = model(Verification_Document_Types_Model::class);
        $ruleBuilder = $verificationTypes->getRelationsRuleBuilder();

		$action = $this->uri->segment(3);
		switch($action){
			case 'start':
				//region Validation
				$validator_rules = array(
					array(
						'field' => 'package',
						'label' => 'Package / Period',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}
				//endregion Validation

				//region User
				$user = model('user')->getUser($id_user);
				//endregion User

				//region check for existent Upgrade Request
				$upgrade_request = model('upgrade')->get_latest_request(array(
					'conditions' => array(
						'user' => $id_user,
						'status' => array('new', 'confirmed'),
						'is_not_expired' => true
					),
				));

				if( !empty($upgrade_request) && $upgrade_request['type'] != 'downgrade'){
					jsonResponse(translate('systmess_info_upgrade_requested_check_confirmed'), 'info');
				}
				//endregion check for existent Upgrade Request

				//region get/check upgrade package
				$id_package = (int)$_POST['package'];
				$group_package = model('packages')->getGrPackage($id_package);
				if(
                    empty($group_package)
                    || ($group_package['is_disabled'] && !filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN))
                    || (filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN) && (int)$group_package['price'] !== 0)
                ){
					jsonResponse(translate('systmess_error_group_package_does_not_exist'));
                }

                if(!$group_package['is_active']){
                    jsonResponse(translate('systmess_package_not_exist_error_message'));
                }
				if($group_package['gr_from'] != group_session()){
					jsonResponse(translate('systmess_error_invalid_data'));
				}
				//endregion get/check upgrade package

				//region cancel Bills for rights cause of Upgrade Request
				$cancel_bills = model('user_bills')->get_simple_bills(array('id_user' => $id_user, 'status' => "'init'", 'id_type_bill' => 6));
				if(!empty($cancel_bills)){
					$cancel_date = date('Y-m-d H:i:s');
					foreach ($cancel_bills as $cancel_bill) {
						$update_bill = array(
							'status' => 'unvalidated',
							'declined_date' => $cancel_date,
							'note' => json_encode(
								array(
									'date_note' => $cancel_date,
									'note' => 'The bill has been canceled because of upgrade request.'
								)
							)
						);

						model('user_bills')->change_user_bill((int) $cancel_bill['id_bill'], $update_bill);
					}
				}
				//endregion cancel Bills for Rights cause of Upgrade

				//region Assign Documents for Upgrade
				$documents = array_map(
					function ($document) {
						$document['title'] = translate('personal_documents_unknown_document_title');
						$document['versions'] = VersionSerializerStatic::deserialize(arrayGet($document, 'versions'), VersionCollectionInterface::class, 'json');
						$document['latest_version'] = VersionSerializerStatic::deserialize(arrayGet($document, 'latest_version'), VersionInterface::class, 'json');
						$document = array_merge($document, $this->getVersionMetadata($document['latest_version']));
						if (!empty($document['type'])) {
							if (null !== $title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title'])) {
								$document['title'] = $title;
							}
						}

						return $document;
					},
					array_filter(
						(array) model('user_personal_documents')->get_documents(array(
							'with'       => array('type'),
							'order'      => array('id_document' => 'ASC'),
							'conditions' => array(
								'user' => (int) $user['idu'],
							),
						))
					)
				);

				$industries = array();
				if($user['gr_type'] == 'Seller'){
					$industries = with(
						model('company')->get_seller_industries((int) $user['idu']),
						function ($industries) {
							return null !== $industries ? (is_string($industries) ? array_filter(explode(',', $industries)) : $industries) : array();
						}
					);
				}

                $assign_verification_documents = $verificationTypes->findAllBy([
                    'scopes' => array_filter(['exclude' => array_column($documents, 'id_type')]),
                    'exists' => array_filter([
                        $ruleBuilder->whereHas(
                            'groupsReference',
                            function (QueryBuilder $builder, RelationInterface $relation) use ($group_package) {
                                $relation->getRelated()->getScope('userGroup')($builder, (int) $group_package['gr_to']);
                                $relation->getRelated()->getScope('isRequired')($builder, true);
                            }
                        ),
                        $ruleBuilder->whereHasNot(
                            'groupsReference',
                            function (QueryBuilder $builder, RelationInterface $relation) use ($user) {
                                $relation->getRelated()->getScope('userGroup')($builder, (int) $user['user_group']);
                            }
                        ),
                        $ruleBuilder->whereHas(
                            'countriesReference',
                            function (QueryBuilder $builder, RelationInterface $relation) use ($user) {
                                $relation->getRelated()->getScope('country')($builder, (int) $user['country']);
                            }
                        ),
                        !empty($industries) ? $ruleBuilder->whereHas(
                            'industriesReference',
                            function (QueryBuilder $builder, RelationInterface $relation) use ($industries) {
                                $relation->getRelated()->getScope('industries')($builder, $industries);
                            }
                        ) : null,
                    ]),
                ]);

				if(!empty($assign_verification_documents)){
					$set_user_documents = array();
					foreach ($assign_verification_documents as $assign_document) {
						$set_user_documents[] = array(
							'id_type'      => $assign_document['id_document'],
							'id_user'      => $id_user,
							'id_principal' => $id_principal,
						);
					}

					model('user_personal_documents')->create_documents($set_user_documents);
				}
				//endregion Assign Documents for Upgrade

				//region Set bill
				$upgrade_date = date('Y-m-d H:i:s');
				$bill_amount = moneyToDecimal(numericToUsdMoney($group_package['price']));
				$insert_bill = array(
					'id_user' => $id_user,
					'bill_description' => 'By paying this bill, you will upgrade your profile from ' . $group_package['gf_name'].' to ' . $group_package['gt_name'].'.',
					'id_type_bill' => 5, // Account upgrade
					'id_item' => $id_package,
					'due_date' => date_plus(7, 'days', $upgrade_date, true),
					'balance' => $bill_amount,
					'pay_percents' => 100,
					'total_balance' => $bill_amount
				);

				if(compareFloatNumbers($group_package['price'], 0, '=')){
					$id_bill = model('user_bills')->set_free_user_bill($insert_bill);


					model('notify')->send_notify([
						'mess_code' => 'free_bill',
						'id_users'  => [$id_user],
						'replace'   => [
							'[BILL_ID]'   => orderNumber($id_bill),
							'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_bill
						],
						'systmess' => true
					]);

				} else{
					$id_bill = model('user_bills')->set_user_bill($insert_bill);

					model('notify')->send_notify([
						'mess_code' => 'bill_created',
						'id_users'  => [$id_user],
						'replace'   => [
							'[BILL_ID]'   => orderNumber($id_bill),
							'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_bill,
							'[BALANCE]'   => '$' . get_price($group_package['price'], false)
						],
						'systmess' => true
					]);

				}
				//endregion Set bill

				//region Create Upgrade Request
				$id_request = model('upgrade')->create_request(array(
					'id_user' => $id_user,
					'id_package' => $id_package,
					'id_bill' => $id_bill,
					'status' => 'new',
					'type' => 'upgrade'
				));
				//endregion Create Upgrade Request

				//region Update User notes
				model('user')->set_notice($id_user, array(
					'add_date' => date('Y/m/d H:i:s'),
					'add_by' => 'System',
					'notice' => 'The user started the upgrade process.'
				));
				//endregion Update User notes

                //region Update Activity Log
                $context = array_merge(
                    array(
                        'upgrade_request' => array('id' => $id_request)
                    ),
                    get_user_activity_context()
                );

                $this->activity_logger->setResourceType(UPGRADE_REQUEST);
                $this->activity_logger->setOperationType(START_UPGRADE_REQUEST);
                $this->activity_logger->setResource($id_request);
                $this->activity_logger->info(model('activity_log_messages')->get_message(UPGRADE_REQUEST, START_UPGRADE_REQUEST), $context);
                //endregion Update Activity Log

				$redirect = $user['status'] == 'active' ? __SITE_URL . 'upgrade' : __SITE_URL . 'verification';

				if(empty(library('Cookies')->getCookieParam('ep_open_what_next_verification'))){
					library('Cookies')->setCookieParam('ep_open_what_next_verification', 2);
				}

				session()->setMessages(translate('systmess_upgrade_request_submitted_message'), 'success');
				jsonResponse('', 'success', array('redirect' => $redirect));
			break;
			case 'extend':
				//region Validation
				$validator_rules = array(
					array(
						'field' => 'package',
						'label' => 'Package / Period',
						'rules' => array('required' => '', 'integer' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}
				//endregion Validation

				//region User
				$user = model('user')->getUser($id_user);
				//endregion User

				//region check for existent Upgrade Request
				$upgrade_request = model('upgrade')->get_latest_request(array(
					'conditions' => array(
						'user' => $id_user,
						'status' => array('new', 'confirmed')
					),
				));

				if (empty($upgrade_request)){
					jsonResponse(translate('systmess_error_invalid_data'), 'info');
				}

				if (!empty($upgrade_request) && $upgrade_request['status'] == 'new'){
					jsonResponse(translate('systmess_info_upgrade_extend_check_confirmed'), 'info');
				}
				//endregion check for existent Upgrade Request

				//region get/check upgrade package
				$id_package = (int) $_POST['package'];
				$extend_package = model('packages')->getGrPackage($id_package);
				if (empty($extend_package) || $extend_package['is_disabled']) {
					jsonResponse(translate('systmess_error_group_package_does_not_exist'));
                }

                if (!$extend_package['is_active']) {
                    jsonResponse(translate('systmess_package_not_exist_error_message'));
                }

				if ($extend_package['gr_to'] != group_session()) {
					jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (empty((int) $extend_package['price'])) {
                    $free_upgrade = model(Upgrade_Model::class)->get_request(array(
                        'limit'         => 1,
                        'joins'         => array('packages'),
                        'conditions'    => array(
                            'package_period'    => (int) $extend_package['period'],
                            'package_price'     => $extend_package['price'],
                            'status'            => array('confirmed'),
                            'user'              => (int) $user['idu'],
                        ),
                    ));

                    if (!empty($free_upgrade)) {
                        jsonResponse(translate('systmess_free_package_only_once_error'));
                    }
                }
				//endregion get/check upgrade package

				//region cancel Bills for rights cause of Upgrade Request
				$cancel_bills = model('user_bills')->get_simple_bills(array('id_user' => $id_user, 'status' => "'init'", 'id_type_bill' => 6));
				if(!empty($cancel_bills)){
					$cancel_date = date('Y-m-d H:i:s');
					foreach ($cancel_bills as $cancel_bill) {
						$update_bill = array(
							'status' => 'unvalidated',
							'declined_date' => $cancel_date,
							'note' => json_encode(
								array(
									'date_note' => $cancel_date,
									'note' => 'The bill has been canceled because of Extend upgrade request.'
								)
							)
						);

						model('user_bills')->change_user_bill((int) $cancel_bill['id_bill'], $update_bill);
					}
				}
				//endregion cancel Bills for Rights cause of Upgrade

				//region Set bill
				$extend_date = date('Y-m-d H:i:s');
				$bill_amount = moneyToDecimal(numericToUsdMoney($extend_package['price']));
				$insert_bill = array(
					'id_user' => $id_user,
					'bill_description' => 'By paying this bill, you will Extend your upgrade package.',
					'id_type_bill' => 5, // Account upgrade
					'id_item' => $id_package,
					'due_date' => date_plus(7, 'days', $extend_date, true),
					'balance' => $bill_amount,
					'pay_percents' => 100,
					'total_balance' => $bill_amount
				);

				if(compareFloatNumbers($bill_amount, 0, '=')){
					$id_bill = model('user_bills')->set_free_user_bill($insert_bill);

					model('notify')->send_notify([
						'mess_code' => 'free_bill',
						'id_users'  => [$id_user],
						'replace'   => [
							'[BILL_ID]'   => orderNumber($id_bill),
							'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_bill
						],
						'systmess' => true
					]);

				} else{
					$id_bill = model('user_bills')->set_user_bill($insert_bill);

					model('notify')->send_notify([
						'mess_code' => 'bill_created',
						'id_users'  => [$id_user],
						'replace'   => [
							'[BILL_ID]'   => orderNumber($id_bill),
							'[BILL_LINK]' => __SITE_URL . 'billing/my/bill/' . $id_bill,
							'[BALANCE]'   => '$' . get_price($extend_package['price'], false)
						],
						'systmess' => true
					]);

				}
				//endregion Set bill

				//region Create Upgrade Request
				model('upgrade')->create_request(array(
					'id_user' => $id_user,
					'id_package' => $id_package,
					'id_bill' => $id_bill,
					'status' => 'new',
					'type' => 'extend'
				));
				//endregion Create Upgrade Request

				//region Update User notes
				model('user')->set_notice($id_user, array(
					'add_date' => date('Y/m/d H:i:s'),
					'add_by' => 'System',
					'notice' => 'The user requested extention for upgrade package.'
				));
				//endregion Update User notes

				$redirect = $user['status'] == 'active' ? __SITE_URL . 'upgrade' : __SITE_URL . 'verification';

				session()->setMessages(translate('systmess_upgrade_request_extend_succesful_message'), 'success');
				jsonResponse('', 'success', array('redirect' => $redirect));
			break;
			case 'cancel':
				$this->cancel_upgrade((int) $id_user);
			break;
			case 'downgrade':
				//region check for existent Upgrade Request
				$upgrade_request = model('upgrade')->get_latest_request(array(
					'conditions' => array(
						'user' => $id_user,
						'status' => array('confirmed')
					),
				));

				if( empty($upgrade_request) || $upgrade_request['type'] == 'downgrade'){
					jsonResponse(translate('systmess_error_invalid_data'));
				}
				//endregion check for existent Upgrade Request

				//region check current package
				$current_package = model('packages')->getGrPackage((int) $upgrade_request['id_package']);
				if( empty($current_package) ){
					jsonResponse(translate('systmess_error_invalid_data'));
                }

				if($current_package['gr_to'] != group_session()){
					jsonResponse(translate('systmess_error_invalid_data'));
				}
				//endregion check current package

				//region Update User
				$update_user = array(
					'paid' => 1,
					'paid_until' => '0000-00-00',
					'paid_price' => 0,
					'user_group' => $current_package['downgrade_gr_to'],
					'user_page_blocked' => 0
				);
				$this->user->updateUserMain($id_user, $update_user);

                /** @var Elasticsearch_Users_Model $elasticSearchUsersModel */
                $elasticSearchUsersModel = model(Elasticsearch_Users_Model::class);
                $elasticSearchUsersModel->sync((int) $id_user);

				$this->session->paid = 1;
				$this->session->paid_until = '0000-00-00';
				$this->session->group_expired = 0;
				$this->session->group = (int) $current_package['downgrade_gr_to'];

				$this->load->library('Auth', 'auth');
				$user = model('user')->getLoginInfoById($id_user);
				$this->auth->user = $user;
				$this->auth->_get_group_and_rights();
				$this->auth->_get_user_menu();
				//endregion Update User

				//region Create Upgrade Request
                model('upgrade')->create_request([
                    'id_package' => $upgrade_request['id_package'],
                    'id_user'    => $id_user,
                    'status'     => 'confirmed',
                    'type'       => 'downgrade',
                ]);
				//endregion Create Upgrade Request
                if(1 == (int) session()->__get('user_photo_with_badge')){
                    switchBadgeImages($id_user, $user['user_photo']);
                    $this->user->updateUserMain(id_session(), ['user_photo_with_badge' => 0]);
                    session()->__set('user_photo_with_badge', 0);
                }
				//region Update User notes
				model('user')->set_notice($id_user, array(
					'add_date' => date('Y/m/d H:i:s'),
					'add_by' => 'System',
					'notice' => 'The user started the upgrade process.'
				));
				//endregion Update User notes

				//region Change user's blocked entities
                /** @var Blocking_Model $blockingModel */
                $blockingModel = model(Blocking_Model::class);

				$blockingModel->unblock_user_data_by_rights($id_user, (int) $current_package['downgrade_gr_to']);
                $blockingModel->block_companies_index_name(['users_list' => [$id_user]]);
				//endregion Change user's blocked entities

				//region Notify user

				model('notify')->send_notify([
					'mess_code' => 'downgrade_group',
					'id_users'  => [$id_user],
					'replace'   => [
						'[GROUP]' => $current_package['gd_name'],
						'[LINK]'  => __SITE_URL . 'upgrade'
					],
					'systmess' => true
				]);

                // Wake up, Neo
                $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserGroupChangedEvent((int) $id_user));
				//endregion Notify user

				$this->session->setMessages(translate('systmess_downgrade_success_message', ['[[PACKAGE]]' => $current_package['gd_name']]), 'info');
				jsonResponse('', 'success');
			break;
		}

		jsonResponse(translate('systmess_error_invalid_data'));
	}


	public function requests_list_dt(){
		checkIsAjax();
		checkIsLoggedAjaxDT();
		checkPermisionAjaxDT('manage_upgrade_requests');

		$skip = isset($_POST['iDisplayStart']) ? (int) cleanInput($_POST['iDisplayStart']) : null;
		$limit = isset($_POST['iDisplayLength']) ? (int) cleanInput($_POST['iDisplayLength']) : null;
		$with = array(
			'bill',
			'user' => function(RelationInterface $relation) {
                $table = $relation->getRelated()->getTable();
                $relation
                    ->getQuery()
                        ->select("users.*, user_groups.gr_name, user_groups.gr_type")
                        ->innerJoin($table, 'user_groups', 'user_groups', "{$table}.user_group = user_groups.idgroup")
                ;
			}
		);
        $order = array();
        $conditions = array_merge(
            dtConditions($_POST, array(
				array('as' => 'status',			'key' => 'request_status',  	'type' => 'cleanInput'),
				array('as' => 'type',			'key' => 'request_type',		'type' => 'cleanInput'),
				array('as' => 'user',			'key' => 'id_user',				'type' => 'int'),
				array('as' => 'created_from',	'key' => 'created_date_from',	'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 00:00:00'),
				array('as' => 'created_to',		'key' => 'created_date_to',		'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 23:59:59'),
				array('as' => 'updated_from',	'key' => 'updated_date_from',	'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 00:00:00'),
				array('as' => 'updated_to',		'key' => 'updated_date_to',		'type' => 'getDateFormat:m/d/Y,Y-m-d|concat: 23:59:59'),
				array('as' => 'expire_from',	'key' => 'expire_date_from',	'type' => 'getDateFormat:m/d/Y,Y-m-d'),
				array('as' => 'expire_to',		'key' => 'expire_date_to',		'type' => 'getDateFormat:m/d/Y,Y-m-d')
			))
		);

        $order = array_column(dt_ordering($_POST, array(
            'dt_id'             => 'id_request',
			'dt_status'         => 'status',
			'dt_type'   		=> 'type',
			'dt_date_created'   => 'date_created',
			'dt_date_updated' 	=> 'date_updated',
			'dt_date_expire' 	=> 'date_expire',
        )), 'direction', 'column');

		//region Upgrade requests information
		$records_count = model('upgrade')->count_requests(compact('conditions'));
		$records = model('upgrade')->get_requests(compact('conditions', 'order', 'limit', 'skip', 'with'));

		if (empty($records)) {
			jsonDTResponse('', array(), 'success');
		}
		//endregion Upgrade requests information

		jsonResponse(null, 'success', array(
			'sEcho'                => (int) arrayGet($_POST, 'sEcho'),
			'aaData'               => $this->get_requests_list($records),
			'iTotalRecords'        => $records_count,
			'iTotalDisplayRecords' => $records_count,
		));
	}

	    /**
     * Returns the list of the users formated for DT.
     *
     * @param array $records
     * @param array $companies
     * @param array $bills
     */
    private function get_requests_list( array $records ) {
		$request_statuses = array(
			'new' => '<span class="status-b"><i class="ep-icon ep-icon_new txt-orange fs-30"></i><br> New</span>',
			'confirmed' => '<span class="status-b"><i class="ep-icon ep-icon_ok-circle txt-green fs-30"></i><br> Confirmed</span>',
			'canceled' => '<span class="status-b"><i class="ep-icon ep-icon_minus-circle txt-red fs-30"></i><br> Canceled</span>'
		);

		$request_types = array(
			'upgrade' => '<span class="status-b"><i class="ep-icon ep-icon_arrow2-up txt-green fs-30"></i><br> Upgrade</span>',
			'extend' => '<span class="status-b"><i class="ep-icon ep-icon_arrow2-right txt-blue fs-30"></i><br> Extend</span>',
			'downgrade' => '<span class="status-b"><i class="ep-icon ep-icon_arrow2-down txt-red fs-30"></i><br> Downgrade</span>'
		);

        $output = array();
        foreach ($records as $record) {
			//region Vars
			$user = arrayGet($record, 'user');
			$payment = arrayGet($record, 'bill');
            $user_id = (int) $user['idu'];
            $user_group_type = strtolower($user['gr_type']);
            $user_raw_name = trim("{$user['fname']} {$user['lname']}");
            $user_name = cleanOutput($user_raw_name);
            $status = $user['status'];
            $group_name = cleanOutput($user['gr_name']);
			$user_personal_page_url = getUserLink($user_raw_name, $user_id, $user_group_type);
            $contact_url = __SITE_URL . "contact/popup_forms/email_user/{$user_id}";
            //endregion Vars

            //region User
            $user_information = "
                <div class=\"tal\">
                    <a class=\"ep-icon ep-icon_user\" href=\"{$user_personal_page_url}\" title=\"View personal page of {$user_name}\" target=\"_blank\"></a>
					<a class=\"ep-icon ep-icon_envelope-send fancyboxValidateModal fancybox.ajax\" href=\"{$contact_url}\" title=\"Email user\" data-title=\"Email {$user_name}\"></a>
					<span>".capitalWord($status)."</span>
                </div>
                <div>{$user_name}</div>
                <div>{$group_name}</div>
            ";
            //endregion User

            //region Photo
			$photo_url = getUserAvatar($user_id, arrayGet($user, 'user_photo'), arrayGet($user, 'user_group'), 0);
            $user_photo = "
                <img class=\"mw-50 mh-50\" src=\"{$photo_url}\" alt=\"{$user_name}\"/>
            ";
            //endregion Photo

            //region Contacts
            $user_contacts = array("<strong>Email:</strong> {$user['email']}");
            if (!empty($user['phone']) || !empty($user['fax'])) {
                if (!empty($user['phone'])) {
					$full_phone_number = trim("{$user['phone_code']} {$user['phone']}");
                    $user_contacts[] = "<strong>Phone:</strong> {$full_phone_number}";
				}

                if (!empty($user['fax'])) {
					$full_fax = trim("{$user['fax_code']} {$user['fax']}");
                    $user_contacts[] = "<strong>Fax:</strong> {$full_fax}";
                }
            }
            //endregion Contacts

			//region Actions on user
			$confirm_button = '';
			$cancel_button = '';

			if($record['status'] == 'new'){
				//region confirm upgrade button
				$confirm_button = '<li>
										<a class="confirm-dialog txt-green"
                                            atas="admin-users__datatable__request-dropdown__confirm-button"
											href="#"
											data-callback="confirm_upgrade"
											data-message="Are you sure you want to confirm this upgrade request?"
											data-user="'. $user_id .'"
											title="Confirm upgrade">
											<span class="ep-icon ep-icon_ok-circle"></span> Confirm upgrade
										</a>
									</li>';
				//endregion confirm upgrade button

				//region cancel upgrade button
				if(in_array($record['type'], array('upgrade', 'extend'))){
					$cancel_button = '<li>
											<a class="confirm-dialog txt-red"
                                                atas="admin-users__datatable__request-dropdown__cancel-button"
												href="#"
												data-callback="cancel_upgrade"
												data-message="Are you sure you want to cancel this upgrade request?"
												data-user="'. $user_id .'"
												title="Cancel upgrade">
												<span class="ep-icon ep-icon_remove-stroke"></span> Cancel upgrade
											</a>
										</li>';
				}
				//endregion cancel upgrade button
			}

			//region payment detail button
			$payment_button = '';
			if (!empty($payment)) {
				$payment_button = '<li>
									<a class="fancybox.ajax fancybox"
                                        atas="admin-users__datatable__request-dropdown__payment-button"
										href="'. __SITE_URL .'payments/popups_payment/payment_detail_admin/'. $record['id_bill'] .'"
										title="Payment detail"
										data-title="Payment detail">
										<span class="ep-icon ep-icon_bank-notes"></span> Payment detail
									</a>
								</li>';
			}
			//endregion payment detail button

			$actions_dt = '<div class="dropdown">
                                <a class="ep-icon ep-icon_menu-circles dropdown-toggle" atas="admin-users__datatable__request-dropdown-toggle-button" type="button" data-toggle="dropdown"></a>
								<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
									'. $confirm_button .'
									'. $cancel_button .'
									'. $payment_button .'
									<li>
										<a class="fancyboxValidateModalDT fancybox.ajax"
                                            atas="admin-users__datatable__request-dropdown__verification-button"
											href="'. __SITE_URL .'verification/popup_forms/user_verification_documents/'. $user_id .'"
											title="Verification documents"
											data-title="Verification documents of '. $user_name .'">
											<span class="ep-icon ep-icon_items"></span> Verification documents
										</a>
									</li>
                                    <li>
										<a class="fancyboxValidateModal fancybox.ajax"
                                            atas="admin-users__datatable__request-dropdown__notice-button"
											href="'. __SITE_URL .'users/popup_show_notice/'. $user_id .'"
											title="System/Admin notices"
											data-title="Notice for '. $user_name .'">
											<span class="ep-icon ep-icon_comments-stroke"></span> System/Admin notices
										</a>
									</li>
                                </ul>
                            </div>';
            //endregion Actions on user

            $output[] = array(
                'dt_id'             => $user_id,
                'dt_photo'          => $user_photo,
                'dt_user'           => $user_information,
                'dt_contacts'      	=> implode('<br>', array_filter($user_contacts)),
                'dt_status'       	=> $request_statuses[$record['status']],
                'dt_type'      		=> $request_types[$record['type']],
                'dt_date_created'	=> getDateFormat($record['date_created']),
                'dt_date_updated'   => getDateFormat($record['date_updated']),
                'dt_date_expire'    => getDateFormatIfNotEmpty($record['date_expire'], 'Y-m-d', 'j M, Y'),
                'dt_actions'        => $actions_dt,
            );
        }

        return $output;
    }

	public function popup_forms()
	{
		checkIsAjax();
        checkIsLoggedAjaxModal();
		checkPermisionAjaxModal('upgrade_group');

        $this->_load_main();
        $op = $this->uri->segment(3);
		switch ($op) {
			case 'process':
				$user = model('user')->getUser(privileged_user_id());
                $getParams = request()->query->all();

				//region Upgrade
				$upgrade_request = model('upgrade')->get_latest_request(array(
					'conditions' => array(
						'user'           => (int) $user['idu'],
						'status'         => array('new', 'confirmed')
					),
				));
                //endregion Upgrade
				if(empty($upgrade_request) || $upgrade_request['type'] == 'downgrade'){
					$this->_popup_process_upgrade($user, $getParams);
				} else {
					if ($upgrade_request['status'] == 'new') {
						$this->_popup_process_status($upgrade_request);
					} else{
						$this->_popup_process_extend($user, $upgrade_request, $getParams);
					}
				}
			break;
			case 'status':
				//region Upgrade
				$upgrade_request = model('upgrade')->get_latest_request(array(
					'conditions' => array(
						'user'           => (int) privileged_user_id(),
						'status'         => array('new', 'confirmed')
					),
				));
				//endregion Upgrade

				if (empty($upgrade_request)) {
					messageInModal('systmess_error_invalid_data');
				}

				if($upgrade_request['type'] == 'downgrade'){
					messageInModal('There are no Upgrade request for your account.', 'info');
				}

				if($upgrade_request['status'] == 'confirmed'){
					messageInModal('The latest request to Upgrade your account has been confirmed.', 'info');
				}

				$this->_popup_process_status($upgrade_request);
            break;
            case 'item_added_success':
                views()->display('new/upgrade/popup_item_added_success_view');
            break;
			default:
				messageInModal(translate('systmess_error_invalid_data'));
			break;
		}
	}

	private function _popup_process_upgrade($user = array(), $getParams = array())
	{
		$data['_group_session'] = group_session();

		$upgradePackagesParams = [
            'gr_from' => $user['user_group'],
            'is_disabled' => 0
        ];

		$upgrade_group = (int) $this->uri->segment(4);
		if ($upgrade_group > 0) {
			$upgradePackagesParams['gr_to'] = $upgrade_group;
		}
        $data['group_packages'] = model('packages')->getGrPackages($upgradePackagesParams);

        if (filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN)) {
            $freeGroupPackages = model('packages')->getGrPackages([
                'gr_from' => $user['user_group'],
                'period' => 5
            ]);

            $data['group_packages'] = array_merge($freeGroupPackages, $data['group_packages']);
        }

		if (empty($data['group_packages'])) {
			messageInModal(translate('no_active_packages_for_upgrade', array('{LINK_TO_CONTACT_US}' => __SITE_URL . 'contact/popup_forms/contact_us')), 'info');
        }

        $selectedPrice = 0;
        if(isset($getParams['package'])){
            $selectedPrice = (int) $getParams['package'];
        }

        $data['upgrade_packages'] = [];
        $dateFreePackage = '';

		foreach($data['group_packages'] as $package){
			if(!isset($data['upgrade_packages'][$package['gr_to']])){
				$data['upgrade_packages'][$package['gr_to']]['name'] = $package['gt_name'];
            }

            if (empty((int) $package['price'])) {
                $dateFreePackage = $package['fixed_end_date'];
            }

			$data['upgrade_packages'][$package['gr_to']]['prices'][] = array(
                'is_already_used'   => false,
				'id_package'        => $package['idpack'],
                'is_active'         => $package['is_active'],
                'end_date'          => $package['fixed_end_date'],
				'period'            => $package['full'],
                'price'             => $package['price'],
                'selected'          => ($selectedPrice > 0 && $package['idpack'] == $selectedPrice)?true:false,
			);
		}

		$documents = array_map(
            function ($document) {
                if (null !== $document['latest_version']) {
                    $document['latest_version'] = VersionSerializerStatic::deserialize($document['latest_version'], VersionInterface::class, 'json');
                }

                $document['metadata'] = $this->getVersionMetadata($document['latest_version']);
                $document['title'] = translate('personal_documents_unknown_document_title');
                if (!empty($document['type'])) {
                    if (null !== $title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title'])) {
                        $document['title'] = $title;
                    }
                }

                return $document;
            },
            array_filter(
                (array) model('user_personal_documents')->get_documents(array(
                    'with'       => array('type'),
                    'order'      => array('id_document' => 'ASC'),
                    'conditions' => array(
                        'user' => (int) $user['idu'],
                    ),
                ))
            )
		);

		$documents = array_filter($documents, function($document){
			return empty($document['metadata']) || $document['metadata']['is_uploadable'];
		});

		$industries = array();
		if($user['gr_type'] == 'Seller'){
			$industries = with(
				model('company')->get_seller_industries((int) $user['idu']),
				function ($industries) {
					return null !== $industries ? (is_string($industries) ? array_filter(explode(',', $industries)) : $industries) : array();
				}
			);
		}

		$groups_params = $upgrade_group > 0 ? array($upgrade_group) : array_column($data['group_packages'], 'gr_to');
        /** @var Verification_Document_Types_Model */
        $verificationTypes = model(Verification_Document_Types_Model::class);
        $ruleBuilder = $verificationTypes->getRelationsRuleBuilder();

		$data['aditional_documents'] = array_map(
            function ($document) {
                $document_titles = json_decode($document['document_titles'], true);
                if (null !== $country_title = $document_titles[session()->country]) {
                    $document['country_title'] = $country_title;
                }

                return $document;
            },
            $verificationTypes->findAllBy([
                'scopes' => array_filter(['exclude' => array_column($documents, 'id_type')]),
                'exists' => array_filter([
                    $ruleBuilder->whereHas(
                        'groupsReference',
                        function (QueryBuilder $builder, RelationInterface $relation) use ($groups_params) {
                            $relation->getRelated()->getScope('isRequired')($builder, true);
                            $relation->getRelated()->getScope('groups')(
                                $builder,
                                is_array($groups_params) ? $groups_params : [(int) $groups_params]
                            );
                        }
                    ),
                    $ruleBuilder->whereHasNot(
                        'groupsReference',
                        function (QueryBuilder $builder, RelationInterface $relation) use ($user) {
                            $relation->getRelated()->getScope('userGroup')($builder, (int) $user['user_group']);
                        }
                    ),
                    $ruleBuilder->whereHas(
                        'countriesReference',
                        function (QueryBuilder $builder, RelationInterface $relation) use ($user) {
                            $relation->getRelated()->getScope('country')($builder, (int) $user['country']);
                        }
                    ),
                    !empty($industries) ? $ruleBuilder->whereHas(
                        'industriesReference',
                        function (QueryBuilder $builder, RelationInterface $relation) use ($industries) {
                            $relation->getRelated()->getScope('industries')($builder, $industries);
                        }
                    ) : null,
                ]),
            ])
        );

        $data['dateFreePackage'] = $dateFreePackage;
		$this->view->assign($data);
		$this->view->display('new/upgrade/popup_upgrade_view');
	}

	private function _popup_process_extend($user = array(), $upgrade_request, $getParams = array())
	{
		$id_package = (int) $upgrade_request['id_package'];
		$current_package = model('packages')->getGrPackage($id_package);
		if(empty($current_package)){
			messageInModal(translate('systmess_error_group_package_does_not_exist'));
        }

		$packages_params = array(
			'gr_from' => $user['user_group'],
			'gr_to' => $current_package['gr_to'],
			'is_disabled' => 0
		);

        $extend_packages = model('packages')->getGrPackages($packages_params);

        if (empty($extend_packages)) {
			messageInModal(translate('no_active_packages_for_upgrade', array('{LINK_TO_CONTACT_US}' => __SITE_URL . 'contact/popup_forms/contact_us')), 'info');
		}

        $upgrade_packages = array();
        // $isset_free_package = false;

        $selectedPrice = 0;
        if(isset($getParams['package'])){
            $selectedPrice = (int) $getParams['package'];
        }

		foreach($extend_packages as $package){
            $already_used = false;

			if(!isset($upgrade_packages[$package['gr_to']])){
				$upgrade_packages[$package['gr_to']]['name'] = $package['gt_name'];
            }

            if (empty((int) $package['price'])) {
                // $isset_free_package = true;

                if ($package['is_active']) {
                    $free_upgrade = model(Upgrade_Model::class)->get_request(array(
                        'limit'         => 1,
                        'joins'         => array('packages'),
                        'conditions'    => array(
                            'package_period'    => (int) $package['period'],
                            'package_price'     => $package['price'],
                            'status'            => array('confirmed'),
                            'user'              => (int) $user['idu'],
                        ),
                    ));

                    if (!empty($free_upgrade)) {
                        $already_used = true;
                    }
                }
            }

			$upgrade_packages[$package['gr_to']]['prices'][] = array(
                'is_already_used'   => $already_used,
                'id_package'        => $package['idpack'],
                'is_active'         => $package['is_active'],
                'end_date'          => $package['fixed_end_date'],
				'period'            => $package['full'],
				'price'             => $package['price'],
				'selected'          => ($selectedPrice > 0 && $package['idpack'] == $selectedPrice)?true:false,
			);
        }

		$groups_params = array((int) $current_package['gr_to']);
        $industries = array();
		if ($user['gr_type'] == 'Seller') {
			$industries = model('company')->get_seller_industries($user['idu']) ?? [];
		}
        /** @var Verification_Document_Types_Model */
        $verificationTypes = model(Verification_Document_Types_Model::class);
        $ruleBuilder = $verificationTypes->getRelationsRuleBuilder();
        $aditional_documents = $verificationTypes->findAllBy([
            'exists' => array_filter([
                $ruleBuilder->whereHas(
                    'groupsReference',
                    function (QueryBuilder $builder, RelationInterface $relation) use ($groups_params) {
                        $relation->getRelated()->getScope('userGroup')($builder, (int) $groups_params);
                        $relation->getRelated()->getScope('isRequired')($builder, true);
                    }
                ),
                $ruleBuilder->whereHas(
                    'countriesReference',
                    function (QueryBuilder $builder, RelationInterface $relation) use ($user) {
                        $relation->getRelated()->getScope('country')($builder, (int) $user['country']);
                    }
                ),
                !empty($industries) ? $ruleBuilder->whereHas(
                    'industriesReference',
                    function (QueryBuilder $builder, RelationInterface $relation) use ($industries) {
                        $relation->getRelated()->getScope('industries')($builder, $industries);
                    }
                ) : null,
            ]),
        ]);

		//region Document
        $documents = array_map(
            function ($document) {
                if (null !== $document['latest_version']) {
                    $document['latest_version'] = VersionSerializerStatic::deserialize($document['latest_version'], VersionInterface::class, 'json');
                }

                $document['metadata'] = $this->getVersionMetadata($document['latest_version']);
                $document['title'] = translate('personal_documents_unknown_document_title');
                if (!empty($document['type'])) {
                    if (null !== $title = accreditation_i18n($document['type']['document_i18n'], 'title', null, $document['type']['document_title'])) {
                        $document['title'] = $title;
                    }
                    $document_titles = json_decode($document['document_titles'], true);
                    if (null !== $country_title = $document_titles[session()->country]) {
                        $document['country_title'] = $country_title;
                    }
                }

                return $document;
            },
            array_filter(
                (array) model('user_personal_documents')->get_documents(array(
                    'with'       => array('type'),
                    'order'      => array('id_document' => 'ASC'),
                    'conditions' => array(
                        'user' => (int) $user['idu'],
                    ),
                ))
            )
		);

		$documents = array_filter($documents, function($document){
			return empty($document['metadata']) || $document['metadata']['is_uploadable'];
		});

		$aditional_documents = array();
		foreach ($documents as $document) {
			$aditional_documents[$document['id_type']] = $document['type'];
			$aditional_documents[$document['id_type']]['metadata'] = !empty($document['metadata']) ? $document['metadata'] : null;
		}
		//endregion Document;

        $extend_upgrade = true;

		views(
            array('new/upgrade/popup_upgrade_view'),
            compact(
                'upgrade_packages',
                'aditional_documents',
                'extend_upgrade',
                // 'isset_free_package'
            )
        );
	}

	private function _popup_process_status($upgrade_request)
	{
		views(
			array('new/upgrade/status_view'),
			compact(
				'upgrade_request'
			)
		);
	}

	public function ajax_admin_operations()
	{
		checkIsAjax();
		checkIsLoggedAjax();

		switch ($this->uri->segment(3)) {
			case 'check':
				checkPermisionAjax('moderate_content');

				$this->can_upgrade((int) arrayGet($_POST, 'user'));

				break;
			case 'complete':
				checkPermisionAjax('moderate_content');

				$this->complete_upgrade((int) arrayGet($_POST, 'user'));

				break;
			case 'cancel':
				checkPermisionAjax('moderate_content');

				$this->cancel_upgrade((int) arrayGet($_POST, 'user'));

				break;
			// case 'completion_notfication':
			// 	checkPermisionAjax('moderate_content');

			// 	$this->send_completion_notfication((int) arrayGet($_POST, 'user'));

			// 	break;
			default:
				jsonResponse('The provided path is not found on this server.');

				break;
		}
	}

	/**
	 * Checks if upgrade can be done
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	private function can_upgrade($user_id)
	{
        //region User access
        if (
            empty($user_id) || empty(model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }
		//endregion User access

		//region Upgrade package
		if (empty($upgrade_request = model('upgrade')->get_latest_request(array(
			'with'       => array('package' => function (RelationInterface $relation) {
                $table = $relation->getRelated()->getTable();
                $relation
                    ->getQuery()
                        ->leftJoin($table, 'packages_period', 'packages_period', "packages_period.id = {$table}.period")
                ;
            }),
			'conditions' => array(
				'user'           => $user_id,
				'status'         => array('new'),
				'is_not_expired' => true,
			),
		)))) {
			jsonResponse("User's upgrade cannot be completed - there are no new upgrade requests for this user.");
		}

		$bill_id = arrayGet($upgrade_request, 'id_bill');
		if (null === arrayGet($upgrade_request, 'package')) {
			jsonResponse("User's upgrade cannot be completed - upgrade package not found.");
		}
		//endregion Upgrade package

		//region Bill
		if (
			null !== $bill_id
			&& !empty($bill = model('user_bills')->get_simple_bill(array('id_bill' => (int) $bill_id)))
			&& 'confirmed' !== $bill['status']
		) {
			jsonResponse("User's upgrade cannot be completed - please make sure the payment is confirmed.");
		}
		//endregion Bill

		jsonResponse("User's upgrade can be completed", 'success');
	}

	/**
	 * Completes the user's upgrade
	 *
	 * @param int $user_id
	 */
	private function complete_upgrade($user_id)
	{
        //region User access
        if (
            empty($user_id) || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist'));
        }

		$not_accepted_or_expired_documents = (int) model('user_personal_documents')->count_documents(array(
			'conditions' => array(
				'user' => $user_id,
				'not_accepted_or_expired' => true
			),
		));
        if ($not_accepted_or_expired_documents > 0) {
            jsonResponse('Failed to upgrade user - Please make sure all the documents are verified.');
        }
		//endregion User access

		//region Upgrade package
		if (empty($upgrade_request = model('upgrade')->get_latest_request(array(
			'with'       => array(
                'package' => function(RelationInterface $relation) {
                    $table = $relation->getRelated()->getTable();
                    $relation
                        ->getQuery()
                            ->select("{$table}.*, packages_period.*, user_groups.gr_name as gt_name")
                            ->leftJoin($table, 'packages_period', 'packages_period', "`packages_period`.`id` = {$table}.`period`")
                            ->leftJoin($table, 'user_groups', 'user_groups', "`user_groups`.`idgroup` = {$table}.`gr_to`")
                    ;
                }
            ),
			'conditions' => array(
				'user'           => $user_id,
				'status'         => array('new'),
				'is_not_expired' => true,
			),
		)))) {
            jsonResponse("There are no New upgrade request for this user.");
		}

		$request_id = (int) $upgrade_request['id_request'];
		if (null === $package = arrayGet($upgrade_request, 'package')) {
			jsonResponse("Failed to upgrade user - upgrade package not found.");
		}
		//endregion Upgrade package

		//region Bill
		$upgrade_bill = model('user_bills')->get_simple_bill(array(
			'id_bill' => $upgrade_request['id_bill'],
		));
		if(!empty($upgrade_bill) && $upgrade_bill['status'] != 'confirmed'){
			jsonResponse("Failed to upgrade user - Please make sure the payment are confirmed.");
		}
		//endregion Bill

		//region Update User Group
		switch($package['abr']){
			case 'F':
				$paid_until = null;
				$end_date = 'Permanently';
			break;
			default:
				if (!empty($package['fixed_end_date'])) {
                    $paid_until = $package['fixed_end_date'];

                    if ('extend' === $upgrade_request['type'] && '0000-00-00' !== $user['paid_until'] && $user['paid_until'] > date('Y-m-d')) {
                        $now = new DateTime();
                        $current_upgrade_paid_till = DateTime::createFromFormat('Y-m-d', $user['paid_until']);
                        $current_active_period = $now->diff($current_upgrade_paid_till);
                        $fixed_end_date = DateTime::createFromFormat('Y-m-d', $package['fixed_end_date']);

                        $paid_until = $fixed_end_date->add($current_active_period)->format('Y-m-d');
                    }
				} else {
					$from_date = date('Y-m-d');

					if($user['paid_until'] != '0000-00-00'){
						$expired_days = date_difference($user['paid_until'], $from_date);
						if($expired_days > 0){
							$from_date = $user['paid_until'];
						}
					}

					$paid_until = getDateFormat(date_plus($package['days'], 'days', $from_date), 'Y-m-d H:i:s', 'Y-m-d');
                }

                $end_date = getDateFormat($paid_until, 'Y-m-d', 'j M, Y');

			break;
        }

        $updateUser = [
			'user_group' 	=> $package['gr_to'],
			'paid' 			=> 1,
			'paid_price' 	=> $package['price'],
            'paid_until' 	=> null !== $paid_until ? $paid_until : '0000-00-00',
        ];

        if (
            0 === (int) $user['free_featured_items']
            && is_verified((int) $user['user_group'])
        ) {
            if ((int) model(Items_Featured_Model::class)->get_items_featured_count(['featured' => 1, 'id_user' => $user['idu']]) > 0) {

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new EmailUserAboutFreeFeaturedItems($user['fname'] . ' ' . $user['lname']))
                        ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
                );

                $updateUser['free_featured_items'] = 1;

                // insert free_featured_items
                model(User_Popups_Model::class)->insertOne([
                    'id_user'   => $user['idu'],
                    'id_popup'  => 11,
                    'is_viewed' => 0,
                    'show_date' => new DateTimeImmutable(date('Y-m-d H:i:s')),
                ]);
            }else{
                $updateUser['free_featured_items'] = 2;
            }

        } elseif (1 === (int) $user['free_featured_items']) {
            $popupUsers = model(User_Popups_Model::class);
            $checkPopups = $popupUsers->findOneBy([
                'columns'    => 'id, is_viewed',
                'conditions' => [
                    'filter_by' => [
                        'id_user'    => $user['idu'],
                        'id_popup'   => 11,
                        'is_viewed'  => 0,
                    ],
                ],
            ]);

            if (!empty($checkPopups)) {
                // viewed free_featured_items
                $popupUsers->updateOne($checkPopups['id'], [
                    'is_viewed' => 1,
                    'show_date' => new DateTimeImmutable(date('Y-m-d H:i:s')),
                ]);
            }

            $updateUser['free_featured_items'] = 2;
        }

        /** @var MailerInterface $mailer */
        $mailer = $this->getContainer()->get(MailerInterface::class);
        $mailer->send(
            (new PromoPackageCertified("{$user['fname']} {$user['lname']}", config('download_promo_materials_token')))
                ->to(new RefAddress((string) $user['idu'], new Address($user['email'])))
        );

		model(User_Model::class)->updateUserMain($user_id, $updateUser);

        /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
        $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
        $elasticsearchUsersModel->sync((int) $user_id);
		//endregion Update User Group

		//region Update
		if (!model('upgrade')->update_request($request_id, array(
			'status'      => 'confirmed',
			'date_expire' => $paid_until
		))) {
			jsonResponse("Failed to upgrade user. Please try again later or contact developers.");
		}
		//endregion Update

		//region Add user notice
		model('user')->set_notice($user_id, array(
			'add_date' => date('Y/m/d H:i:s'),
			'add_by'   => 'System',
			'notice'   => 'The upgrade has been completed.'
		));
		//endregion Add user notice

		//region notify User
		model('notify')->send_notify([
			'mess_code' => 'upgrade_completed',
			'id_users'  => [$user_id],
			'replace'   => [
				'[GROUP_NAME]' => $package['gt_name'],
				'[END_DATE]'   => $end_date
			],
			'systmess' => true
		]);

        // Wake up, Neo
        $this->get(MessengerInterface::class)->bus('event.bus')->dispatch(new UserGroupChangedEvent((int) $user_id));
		//endregion notify User

        //region Update Activity Log
        $fullname = "{$user['fname']} {$user['lname']}";
        $context = array_merge(
            array(
                'upgrade_request' => array('id' => $upgrade_request['id_request']),
                'target_user' => array(
                    'id'      => $user_id,
                    'name'    => $fullname,
                    'profile' => array(
                        'url' => getUserLink($fullname, $user_id, $user['gr_type'])
                    )
                )
            ),
            get_user_activity_context()
        );

        $this->activity_logger->setResourceType(UPGRADE_REQUEST);
        $this->activity_logger->setOperationType(CONFIRM_UPGRADE_REQUEST);
        $this->activity_logger->setResource($upgrade_request['id_request']);
        $this->activity_logger->info(model('activity_log_messages')->get_message(UPGRADE_REQUEST, CONFIRM_UPGRADE_REQUEST), $context);
        //endregion Update Activity Log

		if($user['status'] == 'active'){
            model('blocking')->unblock_user_data_by_rights($user_id, (int) $package['gr_to']);
        }

        //region Set popup update_profile_picture
        /** @var User_Popups_Model $userPopups */
        $userPopups = model(User_Popups_Model::class);
        $userPopups->insertOne([
            'id_user'   => $user['idu'],
            'id_popup'  => 12,
            'is_viewed' => 0,
            'show_date' => new DateTimeImmutable(date('Y-m-d H:i:s')),
        ]);
        //endregion Set popup update_profile_picture

        if (in_array($updateUser['user_group'], [3, 6])) {
            /** @var TinyMVC_Library_Wall $wallLibrary */
            $wallLibrary = library(TinyMVC_Library_Wall::class);

            $wallLibrary->add([
                'type'      => 'certification',
                'userId'    => $user_id,
                'data'      => [
                    'groupName' => $package['gt_name'],
                    'groupId'   => $updateUser['user_group'],
                ],
            ]);
        }

		jsonResponse(translate('systmess_success_user_upgrade_confirmed'), 'success');
	}

	/**
	 * Cancel the user's upgrade
	 *
	 * @param int $user_id
	 */
	private function cancel_upgrade($user_id)
	{
		//region User access
        if (
            empty($user_id) || empty($user = model('user')->getSimpleUser((int) $user_id))
        ) {
            jsonResponse(translate('systmess_error_user_does_not_exist').'1');
        }
		//endregion User access

		//region Check for Upgrade Request
		if (empty($upgrade_request = model('upgrade')->get_latest_request(array(
			'with'       => array('package' => function(RelationInterface $relation) {
                $table = $relation->getRelated()->getTable();
                $relation
                    ->getQuery()
                        ->leftJoin($table, 'packages_period', 'packages_period', "`packages_period`.`id` = {$table}.`period`")
                ;
            }),
			'conditions' => array(
				'user'           => $user_id,
				'status'         => array('new'),
				'is_not_expired' => true,
			),
		)))) {
			jsonResponse(translate('systmess_error_invalid_data'));
		}

		if(!in_array($upgrade_request['type'], array('upgrade', 'extend'))){
			jsonResponse(translate('systmess_error_invalid_data').'2');
		}
		//endregion Check for Upgrade Request

		//region Check Upgrade Bill status, if has been paid user have to ask EP Manager for Upgrade cancelation
		$upgrade_bill = model('user_bills')->get_simple_bill(array(
			'id_bill' => $upgrade_request['id_bill'],
		));

		if (in_array($upgrade_bill['status'], array('paid', 'confirmed')) && !empty((int) $upgrade_bill['balance'])) {
			if (have_right('manage_upgrade_requests')) {
				jsonResponse('The payment has been already processed and the Upgrade request can not be canceled.', 'warning');
			} else{
				jsonResponse(translate('systmess_payment_cannot_be_cancelled_message', ['[[LINK_CONTACT_US]]' => '<a class="fancybox.ajax fancyboxValidateModal" data-before-callback="bootstrapDialogCloseAll" data-title="'. translate('help_contact_us') .'" href="'.__SITE_URL .'contact/popup_forms/contact_us">'. translate('help_contact_us') .'</a>']), 'warning');
			}
		}
		//endregion Upgrade Bill

		//region Cancel Upgrade Request
		model('upgrade')->update_request((int) $upgrade_request['id_request'], array(
			'status'      => 'canceled'
		));

        if(1 == (int) session()->__get('user_photo_with_badge')){
            switchBadgeImages($user_id, $user['user_photo']);
            $this->user->updateUserMain(id_session(), ['user_photo_with_badge' => 0]);
            session()->__set('user_photo_with_badge', 0);
        }
		//endregion Cancel Upgrade Request

		//region Cancel Upgrade Bill
		$bill_cancel_date = date('Y-m-d H:i:s');
		$bill_id = (int) $upgrade_request['id_bill'];
		model('user_bills')->update_user_bill($bill_id, array(
			'declined_date' => $bill_cancel_date,
			'status' => 'unvalidated',
			'note' => $upgrade_bill['note'] . ',' . json_encode(array(
				'date_note' => $bill_cancel_date,
				'note' => 'The bill has been canceled according to upgrade request cancelation.'
			))
		));
		//endregion Cancel Upgrade Bill

		//region Cancel User Documents
        /** @var Verification_Document_Types_Model */
        $verificationTypes = model(Verification_Document_Types_Model::class);
        $ruleBuilder = $verificationTypes->getRelationsRuleBuilder();
        $currentUserGroupId = (int) $user['user_group'];
		$upgradeToGroupId = (int) $upgrade_request['package']['gr_to'];
        if ($currentUserGroupId !== $upgradeToGroupId) {
            $industries = array();
            if ($user['gr_type'] == 'Seller') {
                $industries = model('company')->get_seller_industries($user['idu']) ?? [];
            }

            $delete_verification_documents = $verificationTypes->findAllBy([
                'exists' => array_filter([
                    $ruleBuilder->whereHas(
                        'groupsReference',
                        function (QueryBuilder $builder, RelationInterface $relation) use ($upgradeToGroupId) {
                            $relation->getRelated()->getScope('userGroup')($builder, (int) $upgradeToGroupId);
                        }
                    ),
                    $ruleBuilder->whereHasNot(
                        'groupsReference',
                        function (QueryBuilder $builder, RelationInterface $relation) use ($currentUserGroupId) {
                            $relation->getRelated()->getScope('userGroup')($builder, (int) $currentUserGroupId);
                        }
                    ),
                    $ruleBuilder->whereHas(
                        'countriesReference',
                        function (QueryBuilder $builder, RelationInterface $relation) use ($user) {
                            $relation->getRelated()->getScope('country')($builder, (int) $user['country']);
                        }
                    ),
                    !empty($industries) ? $ruleBuilder->whereHas(
                        'industriesReference',
                        function (QueryBuilder $builder, RelationInterface $relation) use ($industries) {
                            $relation->getRelated()->getScope('industries')($builder, $industries);
                        }
                    ) : null,
                ]),
            ]);

            if (
                !empty($delete_verification_documents)
                && !empty($documentIds = array_column($delete_verification_documents, 'id_document'))
            ) {
                /** @var User_Personal_Documents_Model */
                $personalDocuments = model(User_Personal_Documents_Model::class);
				$personalDocuments->delete_user_documents($user_id, $documentIds);
			}
        }
		//endregion Cancel User Documents

		//region Update User notes
		model('user')->set_notice($user_id, array(
			'add_date' => date('Y/m/d H:i:s'),
			'add_by' => have_right('manage_upgrade_requests') ? user_name_session() : 'System',
			'notice' => 'The Upgrade request was canceled.'
		));
		//endregion Update User notes

		if(have_right('upgrade_group')){
			session()->setMessages(translate('systmess_upgrade_request_cancel_success_message'), 'success');
		} else{
			//region notify User
			model('notify')->send_notify([
				'mess_code' => 'upgrade_canceled',
				'id_users'  => [$user_id],
				'replace'   => [
					'[LINK]' => __SITE_URL . 'contact'
				],
				'systmess' => true
			]);
			//endregion notify User

		}

        //region Update Activity Log
        $context = array_merge(
            array(
                'upgrade_request' => array('id' => $upgrade_request['id_request']),
            ),
            get_user_activity_context()
        );

        $this->activity_logger->setResourceType(UPGRADE_REQUEST);
        $this->activity_logger->setOperationType(CANCEL_UPGRADE_REQUEST);
        $this->activity_logger->setResource($upgrade_request['id_request']);
        $this->activity_logger->info(model('activity_log_messages')->get_message(UPGRADE_REQUEST, CANCEL_UPGRADE_REQUEST), $context);
        //endregion Update Activity Log

		jsonResponse(translate('systmess_upgrade_request_cancel_success_message'), 'success');
	}

	public function svg_circle_template()
	{
		if(!logged_in())
			headerRedirect(__SITE_URL . 'login');

		$type = $this->uri->segment(3);
		$data['type'] = 8;

		if(!empty($type) && $type == 4){
			$data['type'] = 4;
		}

		$this->view->assign($data);
		$this->view->display('new/header_view');
		$this->view->display('new/upgrade/circle_template_view');
		$this->view->display('new/footer_view');
	}

	public function svg_circle_content()
	{
		if(!logged_in())
			headerRedirect(__SITE_URL . 'login');

		$type = $this->uri->segment(3);
		$data['type'] = 8;

		if(!empty($type) && $type == 4)
			$data['type'] = 4;

		if($data['type'] == 8){
			$moveIconY = abs(20 * (sin(67.5 * pi() / 180.0)/sin(90 * pi() / 180.0)));
			$moveIconX = abs(sqrt(20 * 20 - $moveIconY*$moveIconY));

			$data['arrayContent'] = array(
				array(
					'moveIconX' => +$moveIconX,
					'moveIconY' => -$moveIconY,
					'iconName' => 'link',
					'imageCircle' => 'circle-img1.png',
					'dottedLine' => array('pos'=> 'top', 'directionHLine'=> 'right', 'directionVLine'=> 'top', 'x'=> 20, 'y'=> -20),
					'text' => array('ttl'=> 'Personal Export Portal Link', 'txt'=> 'Your page on Export Portal will have a special link by your choice which will help your company to be  instantly recognized by your customers.', 'pos'=> 'right')
				),
				array(
					'moveIconX'=> +$moveIconY,
					'moveIconY'=> -$moveIconX,
					'iconName'=> 'user-info',
					'imageCircle'=> 'circle-img2.png',
					'dottedLine'=> array('pos'=> 'right', 'directionHLine'=> 'right', 'directionVLine'=> 'top', 'x'=> 15, 'y'=> -15),
					'text'=> array('ttl'=> 'Detailed Info and ID', 'txt'=> 'Export Portal ID will present your company in the best light. As well as the detailed information on larger number of pages which you can use on your company webpage.', 'pos'=> 'right')
				),
				array(
					'moveIconX' => +$moveIconY,
					'moveIconY' => +$moveIconX,
					'iconName' => 'window-web',
					'imageCircle' => 'circle-img3.png',
					'dottedLine' => array('pos' => 'right', 'directionHLine' => 'right', 'directionVLine' => 'bottom', 'x' => 15, 'y' => 15),
					'text' => array('ttl' => 'Get Featured Front Page', 'txt' => 'You will be able to verify products and confirm status of products. Plus your products can be promoted on our front page by allowing inquiry, making offer or Producing Requests.', 'pos' => 'right')
				),
				array(
					'moveIconX' => +$moveIconX,
					'moveIconY' => +$moveIconY,
					'iconName' => 'certificate',
					'imageCircle' => 'circle-img4.png',
					'dottedLine' => array('pos' => 'bottom',  'directionHLine' => 'right', 'directionVLine' => 'bottom', 'x' => 20, 'y' => 20),
					'text' => array('ttl' => 'Special Export Portal Certificate', 'txt' => 'For your offline customers you can have a certificate to expose in your office which will show your global coverage.', 'pos' => 'right')
				),
				array(
					'moveIconX' => -$moveIconX,
					'moveIconY' => +$moveIconY,
					'iconName' => 'exima1',
					'imageCircle' => 'circle-img5.png',
					'dottedLine' => array('pos' => 'bottom',  'directionHLine' => 'left', 'directionVLine' => 'bottom', 'x' => 0, 'y' => 0),
					'text' => array('ttl' => 'Exima Membership', 'txt' => 'You will receive a free membership with EXIMA, Export Portal’s partner organization.', 'pos' => 'left')
				),
				array(
					'moveIconX' => -$moveIconY,
					'moveIconY' => +$moveIconX,
					'iconName' => 'partners',
					'imageCircle' => 'circle-img6.png',
					'dottedLine' => array('pos' => 'left',  'directionHLine' => 'left', 'directionVLine' => 'bottom', 'x' => -15, 'y' => 15),
					'text' => array('ttl' => 'Export Portal Partner Sites', 'txt' => 'Your products will be shared on our network’s partner sites which allows to sell more and gain constant flow of deals. ', 'pos' => 'left')
				),
				array(
					'moveIconX' => -$moveIconY,
					'moveIconY' => -$moveIconX,
					'iconName' => 'assistance',
					'imageCircle' => 'circle-img7.png',
					'dottedLine' => array('pos' => 'left',  'directionHLine' => 'left', 'directionVLine' => 'top', 'x' => -15, 'y' => -15),
					'text' => array('ttl' => 'Purchasing Assistance', 'txt' => 'You will be assisted with company posts, uploading photos and videos, and updating news.', 'pos' => 'left')
				),
				array(
					'moveIconX' => -$moveIconX,
					'moveIconY' => -$moveIconY,
					'iconName' => 'socials',
					'imageCircle' => 'circle-img8.png',
					'dottedLine' => array('pos' => 'top',  'directionHLine' => 'left', 'directionVLine' => 'top', 'x' => -20, 'y' => -20),
					'text' => array('ttl' => 'Social Media Assistance', 'txt' => 'You will get a possibility to connect your Export Portal page to all your social media pages: Facebook, LinkedIn, Instagram, Skype, Twitter', 'pos' => 'left')
				)
			);
		}elseif($data['type'] == 4){
			$moveIconY = abs(75 * (sin(135 * pi() / 180.0)/sin(90 * pi() / 180.0)));
			$moveIconX = abs(sqrt(75 * 75 - $moveIconY*$moveIconY));

			$data['arrayContent'] = array(
				array(
					'moveIconX' => +$moveIconX,
					'moveIconY' => -$moveIconY,
					'iconName' => 'link',
					'imageCircle' => 'circle-img1.png',
					'dottedLine' => array('pos'=> 'top', 'directionHLine'=> 'right', 'directionVLine'=> 'top', 'x'=> 20, 'y'=> -20),
					'text' => array('ttl'=> 'Personal Export Portal Link', 'txt'=> 'Your page on Export Portal will have a special link by your choice which will help your company to be  instantly recognized by your customers.', 'pos'=> 'right')
				),
				array(
					'moveIconX' => +$moveIconY,
					'moveIconY' => +$moveIconX,
					'iconName' => 'window-web',
					'imageCircle' => 'circle-img3.png',
					'dottedLine' => array('pos' => 'right', 'directionHLine' => 'right', 'directionVLine' => 'bottom', 'x' => 15, 'y' => 15),
					'text' => array('ttl' => 'Get Featured Front Page', 'txt' => 'You will be able to verify products and confirm status of products. Plus your products can be promoted on our front page by allowing inquiry, making offer or Producing Requests.', 'pos' => 'right')
				),
				array(
					'moveIconX' => -$moveIconX,
					'moveIconY' => +$moveIconY,
					'iconName' => 'partners',
					'imageCircle' => 'circle-img5.png',
					'dottedLine' => array('pos' => 'bottom',  'directionHLine' => 'left', 'directionVLine' => 'bottom', 'x' => 0, 'y' => 0),
					'text' => array('ttl' => 'Exima Membership', 'txt' => 'You will receive a free membership with EXIMA, Export Portal’s partner organization.', 'pos' => 'left')
				),
				array(
					'moveIconX' => -$moveIconY,
					'moveIconY' => -$moveIconX,
					'iconName' => 'assistance',
					'imageCircle' => 'circle-img7.png',
					'dottedLine' => array('pos' => 'left',  'directionHLine' => 'left', 'directionVLine' => 'top', 'x' => -15, 'y' => -15),
					'text' => array('ttl' => 'Purchasing Assistance', 'txt' => 'You will be assisted with company posts, uploading photos and videos, and updating news.', 'pos' => 'left')
				)
			);
		}

		$this->view->assign($data);
		$this->view->display('new/header_view');
		$this->view->display('new/upgrade/circle_content_view');
		$this->view->display('new/footer_view');
	}
}
