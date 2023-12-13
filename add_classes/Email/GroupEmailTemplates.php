<?php

declare(strict_types=1);

namespace App\Email;

use App\Email\AddProductsRemind;
use App\Email\CompleteProfileRemind;
use App\Email\ConfirmEmail;
use App\Email\CrSendActivationLink;
use App\Email\EplConfirmEmail;
use App\Email\MatchmakingActiveBuyer;
use App\Email\MatchmakingActiveSeller;
use App\Email\MatchmakingNewBuyer;
use App\Email\MatchmakingNewSeller;
use App\Email\MatchmakingPendingBuyer;
use App\Email\MatchmakingPendingSeller;
use App\Email\VerificationDocumentsRemind;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class GroupEmailTemplates
{
    private $templates = [
        'confirm_email' => [
            'title'              => 'Resend Confirm email link',
            'template_name'      => 'confirm_email',
            'restrict_gr_access' => ['Buyer', 'Seller'],
        ],
        'epl_confirm_email' => [
            'title'              => 'Resend Confirm email link',
            'template_name'      => 'epl_confirm_email',
            'restrict_gr_access' => ['Shipper'],
        ],
        'cr_send_activation_link' => [
            'title'              => 'Resend activation email',
            'template_name'      => 'cr_send_activation_link',
            'restrict_gr_access' => ['CR Affiliate'],
        ],
        'verification_documents_remind' => [
            'title'              => 'Documents\' Submission Reminder',
            'template_name'      => 'verification_documents_remind',
            'restrict_gr_access' => ['Buyer', 'Seller', 'Shipper'],
        ],
        'complete_profile_remind' => [
            'title'              => 'Profile Completion Reminder',
            'template_name'      => 'complete_profile_remind',
            'restrict_gr_access' => ['Buyer', 'Seller', 'Shipper'],
        ],
        'add_products_remind' => [
            'title'                => 'Add products remind',
            'template_name'        => 'add_products_remind',
            'use_in_uri_user_type' => true,
            'restrict_gr_access'   => ['Seller'],
        ],
    ];

    private $templatesMatchmaking = [
        'buyer' => [
            'pending'   => 'matchmaking_pending_buyer',
            'active'    => 'matchmaking_active_buyer',
            'new'       => 'matchmaking_new_buyer',
        ],
        'seller' => [
            'pending'   => 'matchmaking_pending_seller',
            'active'    => 'matchmaking_active_seller',
            'new'       => 'matchmaking_new_seller',
        ],
    ];

    public function getVerificationTemplates($conditions = []): array
    {
        if (empty($conditions)) {
            return $this->templates;
        }

        $filtered_templates = [];

        $isset_list_of_keys = isset($conditions['list_of_keys']);
        $isset_group_type = isset($conditions['group_type']);

        foreach ($this->templates as $key => $template) {
            if ($isset_list_of_keys && !in_array($key, $conditions['list_of_keys'])) {
                continue;
            }

            if ($isset_group_type && !in_array($conditions['group_type'], $template['restrict_gr_access'])) {
                continue;
            }

            $filtered_templates[$key] = $template;
        }

        return $filtered_templates;
    }

    public function getVerificationTemplate($template_key): array
    {
        return isset($this->templates[$template_key]) ? $this->templates[$template_key] : [];
    }

    public function sentEmailTemplate($name, $params)
    {
        switch ($name) {
            case 'confirm_email':
                $this->sentConfirmEmail($params);

                break;
            case 'epl_confirm_email':
                $this->sentEplConfirmEmail($params);

                break;
            case 'cr_send_activation_link':
                $this->sentCrSendActivationLink($params);

                break;
            case 'verification_documents_remind':
                $this->sentVerificationDocumentsRemind($params);

                break;
            case 'complete_profile_remind':
                $this->sentCompleteProfileRemind($params);

                break;
            case 'add_products_remind':
                $this->sentAddProductsRemind($params);

                break;
        }
    }

    public function sentMatchmakingEmailTemplate($user, $status, $params)
    {
        switch ($this->templatesMatchmaking[$user][$status]) {
            case 'matchmaking_pending_buyer':
                $this->sentMatchmakingPendingBuyer($params);

                break;
            case 'matchmaking_active_buyer':
                $this->sentMatchmakingActiveBuyer($params);

                break;
            case 'matchmaking_new_buyer':
                $this->sentMatchmakingNewBuyer($params);

                break;
            case 'matchmaking_pending_seller':
                $this->sentMatchmakingPendingSeller($params);

                break;
            case 'matchmaking_active_seller':
                $this->sentMatchmakingActiveSeller($params);

                break;
            case 'matchmaking_new_seller':
                $this->sentMatchmakingNewSeller($params);

                break;
        }
    }

    private function sentConfirmEmail($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);

            $mailer->send(
                (new ConfirmEmail($params['userName'], $params['token']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentEplConfirmEmail($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new EplConfirmEmail($params['userName'], $params['token']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentCrSendActivationLink($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new CrSendActivationLink($params['userName'], $params['grName'], $params['token'], $params['password']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentVerificationDocumentsRemind($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new VerificationDocumentsRemind($params['userName']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentCompleteProfileRemind($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new CompleteProfileRemind($params['userName']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentAddProductsRemind($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new AddProductsRemind($params['userName']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentMatchmakingNewSeller($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new MatchmakingNewSeller($params['userName'], (string) $params['countBuyers']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentMatchmakingActiveSeller($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new MatchmakingActiveSeller($params['userName'], (string) $params['countBuyers']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentMatchmakingPendingSeller($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new MatchmakingPendingSeller($params['userName'], (string) $params['countBuyers']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentMatchmakingNewBuyer($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new MatchmakingNewBuyer($params['userName'], (string) $params['countSellers'], (string) $params['countItems']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentMatchmakingActiveBuyer($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new MatchmakingActiveBuyer($params['userName'], (string) $params['countSellers'], (string) $params['countItems']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }

    private function sentMatchmakingPendingBuyer($params)
    {
        try {
            /** @var MailerInterface $mailer */
            $mailer = container()->get(MailerInterface::class);
            $mailer->send(
                (new MatchmakingPendingBuyer($params['userName'], (string) $params['countSellers'], (string) $params['countItems']))
                    ->to(new RefAddress((string) $params['userId'], new Address($params['email'])))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }
    }
}
