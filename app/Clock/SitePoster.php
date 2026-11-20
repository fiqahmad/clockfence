<?php

namespace App\Clock;

use App\Models\Site;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * The static QR Poster at a Site's entrance. Its payload identifies the Site and is not a secret.
 */
final class SitePoster
{
    private const PREFIX = 'CLOCKFENCE:';

    public static function payload(Site $site): string
    {
        return self::PREFIX.$site->id;
    }

    /**
     * The Site id a scanned payload names, or null when it is not a ClockFence Poster.
     */
    public static function siteIdFrom(string $scannedPayload): ?int
    {
        $payload = trim($scannedPayload);

        if (! str_starts_with($payload, self::PREFIX)) {
            return null;
        }

        $siteId = substr($payload, strlen(self::PREFIX));

        return ctype_digit($siteId) && (int) $siteId > 0 ? (int) $siteId : null;
    }

    /**
     * The Poster's QR code as SVG, with high error correction so a scuffed print still scans.
     */
    public static function svg(Site $site): string
    {
        $builder = new Builder(
            writer: new SvgWriter,
            writerOptions: [SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true],
            data: self::payload($site),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 600,
            margin: 0,
        );

        return $builder->build()->getString();
    }
}
