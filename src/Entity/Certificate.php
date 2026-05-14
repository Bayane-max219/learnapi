<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CertificateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CertificateRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_certificate', columns: ['student_id', 'course_id'])]
#[ApiResource(
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['certificate:read']],
    security: "is_granted('ROLE_USER')"
)]
class Certificate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['certificate:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    #[Groups(['certificate:read'])]
    private string $uuid = '';

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['certificate:read'])]
    private ?User $student = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['certificate:read'])]
    private ?Course $course = null;

    #[ORM\Column]
    #[Groups(['certificate:read'])]
    private \DateTimeImmutable $issuedAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['certificate:read'])]
    private ?string $pdfUrl = null;

    public function __construct()
    {
        $this->issuedAt = new \DateTimeImmutable();
        $this->uuid = bin2hex(random_bytes(16));
    }

    public function getId(): ?int { return $this->id; }
    public function getUuid(): string { return $this->uuid; }
    public function getStudent(): ?User { return $this->student; }
    public function setStudent(?User $student): static { $this->student = $student; return $this; }
    public function getCourse(): ?Course { return $this->course; }
    public function setCourse(?Course $course): static { $this->course = $course; return $this; }
    public function getIssuedAt(): \DateTimeImmutable { return $this->issuedAt; }
    public function getPdfUrl(): ?string { return $this->pdfUrl; }
    public function setPdfUrl(?string $url): static { $this->pdfUrl = $url; return $this; }

    public function getVerificationUrl(): string
    {
        return '/verify/' . $this->uuid;
    }
}
