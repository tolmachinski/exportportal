<?php

declare(strict_types=1);

namespace App\Email;

use ExportPortal\Bridge\Mailer\Mime\LegacyEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

final class EmailOrderCancelledByBuyer extends LegacyEmail
{
	private string $orderNumber;
	private string $buyerName;
	private string $reason;
	private string $clickHere;

	public function __construct(string $orderNumber, string $buyerName, string $reason, string $clickHere, Headers $headers = null, AbstractPart $body = null)
	{
		$this->templateName('order_cancel_by_buyer');
		$this->orderNumber = $orderNumber;
		$this->buyerName = $buyerName;
		$this->reason = $reason;
		$this->clickHere = $clickHere;

		$this->templateReplacements([
			'[orderNumber]'    => $this->orderNumber,
			'[buyerName]'      => $this->buyerName,
			'[userName]'       => $this->buyerName,
			'[reason]'         => $this->reason,
			'[clickHere]'      => $this->clickHere,
		]);

		parent::__construct($headers, $body);
	}
}
