<?php

declare(strict_types=1);

namespace App\Envelope\Bridge\Order;

use Mpdf\Output\Destination;
use TinyMVC_Library_Make_Pdf;

class ContractMakerAdapter implements DocumentMakerInterface
{
    /**
     * The PDF making library.
     */
    private TinyMVC_Library_Make_Pdf $pdfMaker;

    /**
     * Creates instance of the adapter.
     */
    public function __construct(TinyMVC_Library_Make_Pdf $pdfMaker)
    {
        $this->pdfMaker = $pdfMaker;
    }

    /**
     * {@inheritdoc}
     */
    public function make(int $orderId, string $name): string
    {
        return $this->pdfMaker->order_contract($orderId)->Output($name, Destination::STRING_RETURN);
    }
}
