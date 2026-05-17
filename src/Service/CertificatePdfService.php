<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Certificate;
use App\Entity\Course;
use App\Entity\User;
use Psr\Log\LoggerInterface;

/**
 * Generates certificate PDFs using Dompdf.
 * Requires: composer require dompdf/dompdf
 * Fallback: returns HTML string when Dompdf is not installed.
 */
class CertificatePdfService
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly LoggerInterface $logger,
        private readonly string $appUrl = 'https://learnapi.dev',
    ) {}

    public function generateCertificatePdf(Certificate $certificate, User $user, Course $course): string
    {
        $verificationUrl = sprintf('%s/certificates/verify/%s', $this->appUrl, $certificate->getUuid());
        $qrCodeDataUri = $this->qrCodeService->generateVerificationQrCodeDataUri($verificationUrl);

        $html = $this->buildCertificateHtml($certificate, $user, $course, $qrCodeDataUri, $verificationUrl);

        if (!class_exists(\Dompdf\Dompdf::class)) {
            $this->logger->warning('Dompdf not installed. Run: composer require dompdf/dompdf');
            // Return HTML as fallback — can be opened in browser for preview
            return $html;
        }

        return $this->renderToPdf($html);
    }

    private function renderToPdf(string $html): string
    {
        $dompdf = new \Dompdf\Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    private function buildCertificateHtml(
        Certificate $certificate,
        User $user,
        Course $course,
        string $qrCodeDataUri,
        string $verificationUrl,
    ): string {
        $userName = htmlspecialchars($user->getFirstName() . ' ' . $user->getLastName());
        $courseName = htmlspecialchars($course->getTitle());
        $issuedDate = $certificate->getIssuedAt()->format('F j, Y');
        $token = htmlspecialchars($certificate->getToken());

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 0; padding: 0; background: #fff; }
  .certificate { width: 100%; min-height: 595px; padding: 60px; box-sizing: border-box; border: 12px solid #1a1a2e; position: relative; }
  .header { text-align: center; margin-bottom: 40px; }
  .brand { font-size: 28px; font-weight: bold; color: #1a1a2e; letter-spacing: 4px; text-transform: uppercase; }
  .subtitle { font-size: 14px; color: #666; margin-top: 4px; }
  .divider { width: 80%; height: 2px; background: linear-gradient(to right, #1a1a2e, #e94560, #1a1a2e); margin: 20px auto; }
  .body { text-align: center; margin: 30px 0; }
  .presents { font-size: 16px; color: #555; margin-bottom: 10px; }
  .recipient { font-size: 42px; font-weight: bold; color: #1a1a2e; margin: 10px 0; }
  .for-completing { font-size: 16px; color: #555; margin: 10px 0; }
  .course-name { font-size: 26px; font-weight: bold; color: #e94560; margin: 10px 0; }
  .date { font-size: 14px; color: #666; margin-top: 30px; }
  .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; }
  .signature { text-align: center; }
  .signature-line { width: 200px; border-top: 2px solid #1a1a2e; margin-bottom: 6px; }
  .signature-name { font-size: 14px; font-weight: bold; color: #1a1a2e; }
  .signature-title { font-size: 12px; color: #666; }
  .qr-section { text-align: center; }
  .qr-section img { width: 80px; height: 80px; }
  .qr-label { font-size: 9px; color: #999; margin-top: 4px; }
  .token { font-size: 9px; color: #aaa; margin-top: 2px; }
  .seal { width: 80px; height: 80px; border-radius: 50%; background: #1a1a2e; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
</style>
</head>
<body>
<div class="certificate">
  <div class="header">
    <div class="brand">LearnAPI</div>
    <div class="subtitle">Online Learning Platform</div>
  </div>
  <div class="divider"></div>
  <div class="body">
    <div class="presents">This is to certify that</div>
    <div class="recipient">{$userName}</div>
    <div class="for-completing">has successfully completed the course</div>
    <div class="course-name">{$courseName}</div>
    <div class="date">Issued on {$issuedDate}</div>
  </div>
  <div class="divider"></div>
  <div class="footer">
    <div class="signature">
      <div class="signature-line"></div>
      <div class="signature-name">LearnAPI Team</div>
      <div class="signature-title">Course Director</div>
    </div>
    <div class="qr-section">
      <img src="{$qrCodeDataUri}" alt="Verification QR Code"/>
      <div class="qr-label">Verify at:</div>
      <div class="qr-label">{$verificationUrl}</div>
      <div class="token">ID: {$token}</div>
    </div>
  </div>
</div>
</body>
</html>
HTML;
    }
}
