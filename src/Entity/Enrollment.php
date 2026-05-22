<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\EnrollmentRepository;
use App\State\EnrollmentStateProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_enrollment', columns: ['student_id', 'course_id'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [new Get(), new GetCollection(), new Post()],
    normalizationContext: ['groups' => ['enrollment:read']],
    denormalizationContext: ['groups' => ['enrollment:write']],

    processor: EnrollmentStateProcessor::class,
)]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['enrollment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:read'])]
    private ?User $student = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?Course $course = null;

    #[ORM\Column]
    #[Groups(['enrollment:read'])]
    private \DateTimeImmutable $enrolledAt;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['enrollment:read'])]
    private int $progressPercent = 0;

    #[ORM\Column(length: 20, options: ['default' => 'active'])]
    #[Assert\Choice(choices: ['active', 'completed', 'suspended'])]
    #[Groups(['enrollment:read'])]
    private string $status = 'active';

    #[ORM\Column(nullable: true)]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct()
    {
        $this->enrolledAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getStudent(): ?User { return $this->student; }
    public function setStudent(?User $student): static { $this->student = $student; return $this; }
    public function getCourse(): ?Course { return $this->course; }
    public function setCourse(?Course $course): static { $this->course = $course; return $this; }
    public function getEnrolledAt(): \DateTimeImmutable { return $this->enrolledAt; }
    public function getProgressPercent(): int { return $this->progressPercent; }
    public function setProgressPercent(int $p): static { $this->progressPercent = min(100, max(0, $p)); return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }

    public function markCompleted(): void
    {
        $this->status = 'completed';
        $this->progressPercent = 100;
        $this->completedAt = new \DateTimeImmutable();
    }
}
