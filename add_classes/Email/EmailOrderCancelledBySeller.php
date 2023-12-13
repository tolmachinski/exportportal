<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailOrderCancelledBySeller extends LegacyEmail
{
	private string $orderNumber;
	private string $companyName;
	private string $userName;
	private string $reason;
	private string $clickHere;

	public function __construct(string $orderNumber, string $name, string $companyName, string $reason, string $clickHere, Headers $headers = null, AbstractPart $body = null)
	{
		$this->templateName('order_cancel_by_seller');
		$this->orderNumber = $orderNumber;
		$this->companyName = $companyName;
		$this->userName = $name;
		$this->reason = $reason;
		$this->clickHere = $clickHere;

		$this->templateReplacements([
			'[orderNumber]'    => $this->orderNumber,
			'[userName]'       => $this->userName,
			'[companyName]'    => $this->companyName,
			'[reason]'         => $this->reason,
			'[clickHere]'      => $this->clickHere,
		]);

		parent::__construct($headers, $body);
	}
}
