<?php
namespace App\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\DataUriWriter;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;

class QrCodeService
{
    private $qrCodeFactory;

    public function __construct(QrCodeFactoryInterface $qrCodeFactory)
    {
        $this->qrCodeFactory = $qrCodeFactory;
    }

    public function generateQrCode(string $text): string
    {
        // Create QR code
        $qrCode = $this->qrCodeFactory->create($text);

        // Use the DataUriWriter to generate the QR code as a data URI
        $dataUriWriter = new DataUriWriter();
        return $dataUriWriter->write($qrCode);
    }
}