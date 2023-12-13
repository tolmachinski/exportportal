<?php

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Ramsey\Uuid\Uuid;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Promo_Materials_Controller extends TinyMVC_Controller
{

    public function index()
	{
		checkIsLogged();
        checkPermision('have_promo_materials');

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->promoMaterialsEpl();
        } else {
            $this->promoMaterialsAll();
        }
    }

    private function promoMaterialsEpl(){
        $data['templateViews'] = [
            'mainOutContent'    => 'user/seller/promo_materials/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(["new/epl/template/index_view"], $data);
    }

    private function promoMaterialsAll(){
		views(['new/header_view', 'new/user/seller/promo_materials/index_view', 'new/footer_view']);
    }

	function certificate(){
        checkIsLogged();
        checkPermision('have_promo_materials_certificate');

        /** @var User_Model $userModel*/
        $userModel = model(User_Model::class);
		if (empty($user = $userModel->getUser($userId = id_session()))) {
			show_404();
		}

        /** @var User_Personal_Documents_Model $personalDocumentsModel */
        $personalDocumentsModel = model(User_Personal_Documents_Model::class);

        $businessLicense = $personalDocumentsModel->find_document([
            'conditions' => [
                'user' => $userId,
                'type' => 2,
            ],
        ]);

        /** @var Company_Model $companyModel*/
        $companyModel = model(Company_Model::class);
		$company = $companyModel->get_company(['id_user' => $userId]);

        $type = uri()->segment(3);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data(getCompanyURL($company))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(350)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        $content = views()->fetch('new/user/seller/promo_materials/' . (is_certified() ? 'certified_certificate_view' : 'verified_certificate_view'), [
            'businessLicense'   => $businessLicense,
            'company'           => $company,
            'is_pdf'            => 'pdf' === $type,
            'qrCode'            => $result->getDataUri(),
            'user'              => $user,
        ]);

		if ($type != 'pdf') {
            echo $content;
		} else {
            /** @var TinyMVC_Library_mpdf $mpdfLibrary */
            $mpdfLibrary = library(TinyMVC_Library_mpdf::class);

			$mpdfLibrary->config([
                'format'=>'A4',
                'margin_top' => 0,
                'margin_bottom' => 0,
                'dpi' => 300
            ]);

            /** @var \Mpdf\Mpdf $mpdf */
			$mpdf = $mpdfLibrary->new_pdf();
			$mpdf->defaultfooterline = 0;
			$mpdf->setFooter('');
			$mpdf->WriteHTML($content);
			$mpdf->Output('certificate-' . strForUrl($company['name_company']) . '.pdf', 'I');
		}
	}

	function id_card(){
        checkIsLogged();
        checkPermision('have_promo_materials_id_card');

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        if (empty($user = $userModel->getUser($userId = id_session()))) {
            show_404();
        }

        $type = uri()->segment(3);

        /** @var Company_Model $companyModel*/
        $companyModel = model(Company_Model::class);
		$company = $companyModel->get_company(['id_user' => $userId]);

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        $addressParts = $countryModel->get_country_state_city($user['city']);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data(getCompanyURL($company))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(350)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        $content = views()->fetch('new/user/seller/promo_materials/' . (is_certified() ? 'certified_id_card_view' : 'verified_id_card_view'), [
            'userAddress'   => implode(', ', array_filter([$addressParts['country'], $addressParts['state'], $addressParts['city'], $user['address']])),
            'company'       => $company,
            'is_pdf'        => 'pdf' === $type,
            'qrCode'        => $result->getDataUri(),
            'user'          => $user,
        ]);

		if ($type != 'pdf') {
			echo $content;
		} else {
            /** @var TinyMVC_Library_mpdf $mpdfLibrary*/
            $mpdfLibrary = library(TinyMVC_Library_mpdf::class);

			$mpdfLibrary->config([
                'format'=>'A5',
                // 'orientation'=>'L',
                'font_family' => 'helvetica',
                // 'margin_top' => '3px',
                // 'margin_bottom' => 0,
                'dpi' => 300
            ]);

            /** @var \Mpdf\Mpdf $mpdf */
			$mpdf = $mpdfLibrary->new_pdf();
			$mpdf->defaultfooterline = 0;
			$mpdf->setFooter('');
			$mpdf->WriteHTML($content);
			$mpdf->Output('idcard-' . strForUrl($company['name_company']) . '.pdf', 'I');
		}
	}

	function business_card(){
        checkIsLogged();
        checkPermision('have_promo_materials_business_card');

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);
        if (empty($user = $userModel->getUser($userId = id_session()))) {
            show_404();
        }

        $type = uri()->segment(3);

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        $addressParts = $countryModel->get_country_state_city($user['city']);

        /** @var Company_Model $companyModel */
        $companyModel = model(Company_Model::class);
		$company = $companyModel->get_company(['id_user' => $userId]);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data(getCompanyURL($company))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(350)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        $content = $this->view->fetch('new/user/seller/promo_materials/' . (is_certified() ?  'certified_business_card_view' : 'verified_business_card_view'), [
            'userAddress'   => implode(', ', array_filter([$addressParts['country'], $addressParts['state'], $addressParts['city'], $user['address']])),
            'company'       => $company,
            'qrCode'        => $result->getDataUri(),
            'is_pdf'        => 'pdf' === $type,
            'user'          => $user,
        ]);

		if ($type != 'pdf') {
			echo $content;
		} else {
            /** @var TinyMVC_Library_mpdf $mpdfLibrary */
            $mpdfLibrary = library(TinyMVC_Library_mpdf::class);

			$mpdfLibrary->config([
                'format'=>'A5',
                'orientation'=>'L',
                'font_family' => 'helvetica',
                // 'margin_top' => '3px',
                // 'margin_bottom' => 0,
                'dpi' => 300
            ]);

            /** @var \Mpdf\Mpdf $mpdf */
			$mpdf = $mpdfLibrary->new_pdf();

			$mpdf->defaultfooterline = 0;
			$mpdf->setFooter('');
			$mpdf->WriteHTML($content);
			$mpdf->Output('idcard-' . strForUrl($company['name_company']) . '.pdf', 'I');
		}
	}

	function passport(){
        checkIsLogged();
        checkPermision('have_promo_materials_passport');

        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);
        if (empty($user = $userModel->getSimpleUser(
            $userId = id_session(),
            <<<COLUMNS
                users.idu,
                users.fname,
                users.lname,
                users.user_group,
                users.country,
                users.user_photo,
                users.registration_date,
                users.trade_passport_expiration_date
            COLUMNS
        ))) {
            show_404();
        }

        $type = uri()->segment(3);

        /** @var Country_Model $countryModel */
        $countryModel = model(Country_Model::class);
        $country = $countryModel->get_country((int) $user['country']);

        if (is_shipper((int) $user['user_group'])) {
            /** @var Shippers_Model $shippersModel */
            $shippersModel = model(Shippers_Model::class);

            $company = $shippersModel->get_shipper_by_user($userId);
            $companyName = $company['co_name'] ?? null;
        } elseif (is_seller((int) $user['user_group']) || is_manufacturer((int) $user['user_group'])) {
            /** @var Company_Model $companyModel */
            $companyModel = model(Company_Model::class);

            $company = $companyModel->get_company(['id_user' => $userId]);
            $companyName = $company['name_company'] ?? null;
        }

        /** @var User_Personal_Documents_Model $personalDocumentsModel */
        $personalDocumentsModel = model(User_Personal_Documents_Model::class);

        $businessLicense = $personalDocumentsModel->find_document([
            'conditions' => [
                'user' => $userId,
                'type' => 2,
            ],
        ]);

        if (empty($expirationDate = getDateformat($businessLicense['date_latest_version_expires'] ?: null, null, 'm/d/Y'))) {
            if (
                empty($expirationDate = getDateformat($user['trade_passport_expiration_date'] ?: null, null, 'm/d/Y'))
                || (new DateTime())->createFromFormat('Y-m-d H:i:s', $expirationDate) < (new DateTime())
            ) {
                $date = (new DateTimeImmutable())->modify('+1 year');
                $expirationDate = $date ->format('m/d/Y');

                $userModel->updateUserMain($userId, ['trade_passport_expiration_date' => $date ->format('Y-m-d H:i:s')]);
            }
        }



        $content = views()->fetch('new/user/seller/promo_materials/passport_view', [
            'expirationDate'    => $expirationDate,
            'companyName'       => $companyName ?? null,
            'documentNr'        => Uuid::uuid5(Uuid::NAMESPACE_X500, orderNumberOnly($userId))->toString(),
            'country'           => $country,
            'is_pdf'            => 'pdf' === $type,
            'user'              => $user,
        ]);

		if ($type != 'pdf') {
			echo $content;
            return;
		}

        /** @var TinyMVC_Library_mpdf $mpdfLibrary */
        $mpdfLibrary = library(TinyMVC_Library_mpdf::class);

        $mpdfLibrary->config([
            'format'=>'A4',
            // 'orientation'=>'L',
            'font_family' => 'helvetica',
            'margin_top' => '3px',
            'margin_bottom' => 0,
            'dpi' => 96
        ]);

        /** @var \Mpdf\Mpdf $mpdf */
        $mpdf = $mpdfLibrary->new_pdf();

        $mpdf->defaultfooterline = 0;
        $mpdf->setFooter('');
        $mpdf->WriteHTML($content);
        $mpdf->Output('passport-' . strForUrl($user['fname'] . ' ' . $user['lname']) . '.pdf', 'I');
	}
}
