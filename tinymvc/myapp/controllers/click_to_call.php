<?php

declare(strict_types=1);

use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Common\Validation\ConstraintViolation;
use App\Validators\ClickToCallMainDataValidator;
use App\Validators\ClickToCallNotLoggedValidator;
use App\Validators\PhoneValidator;
use App\Services\PhoneCodesService;

/**
 * Controller Click_to_call.
 */
class Click_to_call_Controller extends TinyMVC_Controller
{
	/**
	 * Index page.
	 */
	public function index(): void
	{
		show_404();
	}

	public function ajax_save_call_request()
	{
		checkIsAjax();

        $request = request()->request;

        //region validation
        $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validators = [new ClickToCallMainDataValidator($adapter)];
        $validators[] = new PhoneValidator(
            $adapter,
            [
                'code.invalid'       => translate('validation_invalid_phone_code', ['[COLUMN_NAME]' => translate('schedule_a_demo_popup_phone_label')]),
                'phone.invalid'      => translate('validation_invalid_phone_number', ['[COLUMN_NAME]' => translate('schedule_a_demo_popup_phone_label')]),
                'phone.unacceptable' => translate('validation_unacceptable_phone_number', ['[COLUMN_NAME]' => translate('schedule_a_demo_popup_phone_label')]),
            ],
            ['phone' => 'Phone', 'code' => 'Phone code'],
            ['phone' => 'phone', 'code' => 'phone_code']
        );

        if (!logged_in()) {
            $validators[] = new ClickToCallNotLoggedValidator($adapter);
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
        //endregion validation

		if ('prod' === config('env.APP_ENV')) {

			if (logged_in()) {
				$ticketData = [
					'email'     => session()->email,
					'firstName' => session()->fname,
					'lastName'  => session()->lname,
					'group'     => session()->group_name,
				];

				/** @var Users_Model $usersModel */
				$usersModel = model(Users_Model::class);

				$user = $usersModel->findOne(session()->id);

				/** @var Cities_Model $citiesModel */
				$citiesModel = model(Cities_Model::class);

				$cityDetails = $citiesModel->findOneBy([
					'columns' => [
						'`country`',
						'`state_name`',
						'`city`',
						"{$citiesModel->qualifyColumn($citiesModel->getPrimaryKey())}",
					],
					'joins'  => ['countries', 'states'],
					'scopes' => ['id' => $user['city']],
				]);

				$description = [
					'User id'  => $user['idu'],
					'Country'  => $cityDetails['country'],
					'State'    => $cityDetails['state_name'],
					'City'     => $cityDetails['city'],
				];
			} else {
				$ticketData = [
					'email'     => $request->get('email'),
					'firstName' => $request->get('fname'),
					'lastName'  => $request->get('lname'),
				];

                //We check the email deliverability only on prod,  else email is assigned by default with status Bad
                if (config('env.APP_ENV') === 'prod' && 'Bad' === checkEmailDeliverability($request->get('email'))) {
                    jsonResponse(translate('systmess_error_undeliverable_email', ['[USER_EMAIL]' => $request->get('email')]));
                }
			}

			/** @var TinyMVC_Library_Cleanhtml $cleanHtmlLibrary */
			$cleanHtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);
			$sanitizedMessage = $cleanHtmlLibrary->sanitize($request->get('comment'));

			$userName = "{$ticketData['firstName']} {$ticketData['lastName']}";
			$whatsApp = $request->getInt('whatsapp');

            if (!empty($phoneCodeId = $request->getInt('phone_code'))) {
                /** @var Phone_Codes_Model $phoneCodesModel */
                $phoneCodesModel = model(Phone_Codes_Model::class);

                $phoneCode = $phoneCodesModel->findOne($phoneCodeId);
            }

			$description['Phone'] = trim(sprintf("%s {$request->get('phone')}", $phoneCode['ccode'] ?? ''));
			$description['User name'] = $userName;
			$description['Comment'] = $sanitizedMessage;
			$description['WhatsApp'] = isset($whatsApp) && 1 === $whatsApp ? 'Wants to be called through whatsapp' : "Doesn't want to be called through whatsapp";

			$timezoneId = $request->getInt('timezone');

			/** @var Timezone_Model $timezoneModel */
			$timezoneModel = model(Timezone_Model::class);

			$timezone = $timezoneModel->findOne($timezoneId);
			$timezoneTitle = (float) $timezone['hours'] <= 0 ? (float) $timezone['hours'] : sprintf('%+d', $timezone['hours']);
			$description['Timezone'] = "{$timezone['name_timezone']} ({$timezone['name_country']}) UTC{$timezoneTitle}";
			$callRequestTicketData = [
				'departmentId'          => (int) config('env.ZOHO_EP_DEPARTMENT_ID'),
				'classification'        => 'Request',
				'category'              => 'Click To Call Request', // don't change it
				'prepareDescription'    => true,
				'subject'               => "Call Request from {$userName}",
				'email'                 => $ticketData['email'],
				'description'           => $description,
				'contact'               => [
					'lastName'  => $ticketData['lastName'],
					'firstName' => $ticketData['firstName'],
					'email'     => $ticketData['email'],
				],
			];

			/** @var TinyMVC_Library_Zoho_Desk $zohoDeskLibrary */
			$zohoDeskLibrary = library(TinyMVC_Library_Zoho_Desk::class);

			try {
				$zohoDeskLibrary->createTicket($callRequestTicketData);
			} catch (Exception $e) {
				jsonResponse(translate('systmess_error_invalid_data'));
			}
		}

		jsonResponse(translate('systmess_success_click_to_call_message'), 'success');
	}

	public function view_form()
	{
		if (!isAjaxRequest()) {
			headerRedirect();
		}

        /** @var Timezone_Model $timezoneModel */
        $timezoneModel = model(Timezone_Model::class);
        $phoneCodes = (new PhoneCodesService(model(Country_Model::class)))->getCountryCodes();
        $selectedCode = $phoneCodes->first() ?: null;

        jsonResponse('', 'success', [
            'content' => views()->fetch('new/popups/click_to_call_view', [
                'timezones'    => $timezoneModel->findAll(),
                'loggedIn'     => logged_in(),
                'phoneCodes'   => $phoneCodes,
                'selectedCode' => $selectedCode,
            ])
        ]);
    }
}

// End of file click_to_call.php
// Location: /tinymvc/myapp/controllers/click_to_call.php
