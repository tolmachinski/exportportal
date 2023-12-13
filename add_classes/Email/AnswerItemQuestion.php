<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class AnswerItemQuestion extends LegacyEmail
{
    private string $userFullname;
    private array $dataQuestion;
    private string $sellerName;
    private string $itemTitle;
    private string $idItem;

    public function __construct(string $userFullname, string $sellerName, array $dataQuestion, string $itemTitle, string $idItem, Headers $headers = null, AbstractPart $body = null)
    {
        $this->templateName('email_answer_item_question');
        $this->userFullname = $userFullname;
        $this->sellerName = $sellerName;
        $this->dataQuestion = $dataQuestion;
        $this->itemTitle = $itemTitle;
        $this->idItem = $idItem;

        $this->templateReplacements([
            '[userName]'        => $this->userFullname,
            '[seller]'          => $this->sellerName,
            '[questionTitle]'   => cleanOutput($this->dataQuestion['title_question']),
            '[question]'        => cleanOutput($this->dataQuestion['question']),
            '[mainBtnLink]'     => __SITE_URL . 'item/' . strForURL($this->itemTitle) . '-' . $this->idItem . '#questions-f',
        ]);

        parent::__construct($headers, $body);
    }
}
