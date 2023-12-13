<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Exceptions\QueryException;
use App\Users\Contracts\PersonInterface;
use DateInterval;
use Money\Money;
use Money\MoneyFormatter;
use Symfony\Component\String\UnicodeString;
use User_Bills_Model;

final class OderBillingService
{
    public const STORAGE_WRITE_ERROR = 0x000001501;

    /**
     * The bills repository.
     *
     * @var User_Bills_Model
     */
    private $billsRepository;

    /**
     * The bill payment interval.
     *
     * @var DateInterval
     */
    private $paymentTreshhold;

    /**
     * The bill's money formatter.
     *
     * @var MoneyFormatter
     */
    private $moneyFormatter;

    /**
     * The datetime format used for dates.
     *
     * @var string
     */
    private $dateTimeFormat;

    /**
     * The default bill description.
     *
     * @var null|UnicodeString
     */
    private $defaultBillDescription;

    /**
     * The default bill type.
     *
     * @var null|int
     */
    private $defaultBillType;

    /**
     * Creates instance of service.
     */
    public function __construct(
        User_Bills_Model $billsRepository,
        DateInterval $paymentTreshhold,
        MoneyFormatter $moneyFormatter,
        ?UnicodeString $defaultBillDescription = null,
        ?int $defaultBillType = null,
        string $dateTimeFormat = 'Y-m-d H:i:s'
    ) {
        $this->dateTimeFormat = $dateTimeFormat;
        $this->moneyFormatter = $moneyFormatter;
        $this->billsRepository = $billsRepository;
        $this->paymentTreshhold = $paymentTreshhold;
        $this->defaultBillType = $defaultBillType;
        $this->defaultBillDescription = $defaultBillDescription;
    }

    /**
     * Changes the default bill description.
     */
    public function changeDeafultBillDescriptionText(?UnicodeString $billDescription): void
    {
        $this->defaultBillDescription = $billDescription;
    }

    /**
     * Returns the default bill description.
     */
    public function getDefaultBillDescriptionText(): ?UnicodeString
    {
        return $this->defaultBillDescription;
    }

    /**
     * Changes the default bill type.
     */
    public function changeDeafultBillType(?int $billType): void
    {
        $this->defaultBillType = $billType;
    }

    /**
     * Returns the default bill type.
     */
    public function getDeafultBillType(): ?int
    {
        return $this->defaultBillType;
    }

    /**
     * Creates one bill of indicated type.
     *
     * @throws QueryException if failed to store bill in repository
     */
    public function createOneBill(
        PersonInterface $payer,
        int $recordId,
        ?int $billType,
        Money $payAmount,
        Money $totalAmount,
        ?UnicodeString $description = null
    ): int {
        //region Create bill
        $now = new \DateTimeImmutable();
        $bill = array(
            'id_user'          => $payer->getId(),
            'id_item'          => $recordId,
            'id_type_bill'     => $billType ?? $this->defaultBillType,
            'due_date'         => $now->add($this->paymentTreshhold)->format($this->dateTimeFormat),
            'balance'          => $this->moneyFormatter->format($payAmount),
            'total_balance'    => $this->moneyFormatter->format($totalAmount),
            'pay_percents'     => (int) ceil((float) $payAmount->ratioOf($totalAmount)) * 100,
            'create_date'      => $now->format($this->dateTimeFormat),
            'bill_description' => (string) ($description ?? $this->getDefaultBillDescriptionText() ?? null),
        );
        //endregion Create bill

        //region Write bill
        try {
            if (!($billId = $this->billsRepository->set_user_bill($bill))) {
                throw QueryException::executionFailed($this->billsRepository->db, null, static::STORAGE_WRITE_ERROR);
            }
        } catch (QueryException $exception) {
            throw $exception; // We rollin'...
        } catch (\Exception $exception) {
            throw QueryException::executionFailed($this->sampleOrdersRepository->db, $exception, static::STORAGE_WRITE_ERROR);
        }
        //endregion Write bill

        return (int) $billId;
    }
}
