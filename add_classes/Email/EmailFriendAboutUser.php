<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailFriendAboutUser extends LegacyEmail
{
    private string $userName;
    private string $message;
    private array $user;

    public function __construct(string $userName, string $message, array $user, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_friend_about_user');
        $this->markAsUnverified();
        $this->userName = $userName;
        $this->message = $message;
        $this->user = $user;

        $this->templateReplacements([
            '[userName]'                => $this->userName,
            '[message]'                 => $this->message,
            '[link]'                    => getUserLink($this->user['user_name'], $this->user['idu'], $this->user['gr_type']),
            '[tableInfoDescription]'    => cut_str_with_dots(cleanOutput($this->user['description']), 200),
            '[tableInfoImage]'          => __IMG_URL . getImage('public/img/users/' . $this->user['idu'] . '/' . $this->user['user_photo'], 'public/img/no_image/no-image-80x80.png'),
            '[tableInfoTitle]'          => $this->user['user_name'],
        ]);

        parent::__construct($headers, $body);
    }
}
