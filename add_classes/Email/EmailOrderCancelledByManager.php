<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailOrderCancelledByManager extends LegacyEmail
{
	private string $orderNumber;
	private string $reason;
	private string $clickHere;
	private string $userName;
	private string $buyerName;

	public function __construct(string $orderNumber, string $name, string $buyerName, string $reason, string $clickHere, Headers $headers = null, AbstractPart $body = null)
	{
		$this->templateName('order_cancel_by_manager');
		$this->orderNumber = $orderNumber;
		$this->reason = $reason;
		$this->userName = $name;
        $this->buyerName = $buyerName;
		$this->clickHere = $clickHere;

		$this->templateReplacements([
			'[orderNumber]' => $this->orderNumber,
			'[userName]'    => $this->userName,
			'[reason]'      => $this->reason,
			'[clickHere]'   => $this->clickHere,
            '[buyerName]'   => $this->buyerName,
		]);

		parent::__construct($headers, $body);
	}
}
