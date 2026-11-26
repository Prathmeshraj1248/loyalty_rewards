<?php
declare(strict_types=1);

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

final class QrService
{
    /** Returns a data: URI for an SVG QR image, usable directly in <img src="...">. */
    public function dataUri(string $payload): string
    {
        $result = Builder::create()
            ->writer(new SvgWriter()) // SVG needs no image extension and stays sharp when printed
            ->data($payload)
            ->size(180)
            ->margin(4)
            ->build();

        return $result->getDataUri();
    }
}