<?php

declare(strict_types=1);

namespace App\Common\Exceptions;

use Throwable;
use Exception;

class MatchmakingException extends Exception
{
    public const SELLER_WITHOUT_INDUSTRIES_CODE = 0x000000100;
    public const EMPTY_BUYERS_LIST_CODE = 0x000000101;
    public const PAGE_GREATER_THAN_TOTAL_BUYERS_CODE = 0x000000102;

    public const BUYER_WITHOUT_INDUSTRIES_CODE = 0x000000200;
    public const EMPTY_SELLERS_LIST_CODE = 0x000000201;
    public const PAGE_GREATER_THAN_TOTAL_SELLERS_CODE = 0x000000202;

    /**
     * @var int|null $totalMatchmakingRecords
     */
    private $totalMatchmakingRecords = null;

    /**
     * {@inheritdoc}
     *
     * @param array $params
     */
    public function __construct(string $message = '', int $code = 0, array $params = [], ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if (!empty($params['totalRecords'])) {
            $this->totalMatchmakingRecords = (int) $params['totalRecords'];
        }
    }

    /**
     * @return int|null
     */
    public function getTotalMatchMakingRecords(): ?int
    {
        return $this->totalMatchmakingRecords;
    }
}
