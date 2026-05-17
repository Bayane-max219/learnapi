<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Generates QR codes for certificate verification URLs.
 * Requires: composer require endroid/qr-code
 * Fallback: inline SVG path generation (no external package needed).
 */
class QrCodeService
{
    public function generateVerificationQrCode(string $verificationUrl): string
    {
        // When endroid/qr-code is installed, use:
        /*
        use Endroid\QrCode\QrCode;
        use Endroid\QrCode\Writer\PngWriter;

        $qrCode = QrCode::create($verificationUrl)
            ->setSize(200)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        return base64_encode($result->getString());
        */

        // Fallback: generates an SVG QR placeholder with embedded URL
        return $this->generateSvgPlaceholder($verificationUrl);
    }

    public function generateVerificationQrCodeDataUri(string $verificationUrl): string
    {
        $svg = $this->generateSvgPlaceholder($verificationUrl);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function generateSvgPlaceholder(string $url): string
    {
        $encodedUrl = htmlspecialchars($url, ENT_XML1);
        // SVG placeholder — replace with actual QR matrix when endroid/qr-code is available
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
  <rect width="200" height="200" fill="white"/>
  <rect x="10" y="10" width="60" height="60" fill="none" stroke="black" stroke-width="6"/>
  <rect x="25" y="25" width="30" height="30" fill="black"/>
  <rect x="130" y="10" width="60" height="60" fill="none" stroke="black" stroke-width="6"/>
  <rect x="145" y="25" width="30" height="30" fill="black"/>
  <rect x="10" y="130" width="60" height="60" fill="none" stroke="black" stroke-width="6"/>
  <rect x="25" y="145" width="30" height="30" fill="black"/>
  <rect x="80" y="80" width="12" height="12" fill="black"/>
  <rect x="96" y="80" width="12" height="12" fill="black"/>
  <rect x="112" y="80" width="12" height="12" fill="black"/>
  <rect x="80" y="96" width="12" height="12" fill="black"/>
  <rect x="112" y="96" width="12" height="12" fill="black"/>
  <rect x="80" y="112" width="12" height="12" fill="black"/>
  <rect x="96" y="112" width="12" height="12" fill="black"/>
  <text x="100" y="190" font-size="8" text-anchor="middle" fill="#666">Scan to verify</text>
  <title>QR Code: {$encodedUrl}</title>
</svg>
SVG;
    }
}
