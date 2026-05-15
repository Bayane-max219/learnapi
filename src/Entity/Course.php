<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\CourseRepository;
use App\State\CourseStateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [new Get(), new GetCollection(), new Post(), new Put(), new Delete()],
    normalizationContext: ['groups' => ['course:read']],
    denormalizationContext: ['groups' => ['course:write']],
    processor: CourseStateProcessor::class,
)]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'category' => 'exact', 'level' => 'exact'])]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['course:read', 'enrollment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    #[Groups(['course:read', 'course:write', 'enrollment:read'])]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['course:read', 'course:write'])]
    private string $description = '';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['beginner', 'intermediate', 'advanced'])]
    #[Groups(['course:read', 'course:write'])]
    private string $level = 'beginner';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['course:read', 'course:write'])]
    private string $category = '';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    #[Groups(['course:read', 'course:write'])]
    private string $price = '0.00';

    #[ORM\Column(nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $thumbnailUrl = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['course:read', 'course:write'])]
    private bool $published = false;

    #[ORM\Column]
    #[Groups(['course:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['course:read'])]
    private ?User $instructor = null;

    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'course', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups(['course:read'])]
    private Collection $lessons;

    #[ORM\OneToMany(targetEntity: Enrollment::class, mappedBy: 'course', cascade: ['remove'])]
    private Collection $enrollments;

    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'course', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['course:read'])]
    private Collection $quizzes;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->lessons = new ArrayCollection();
        $this->enrollments = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getLevel(): string { return $this->level; }
    public function setLevel(string $level): static { $this->level = $level; return $this; }
    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): static { $this->category = $category; return $this; }
    public function getPrice(): string { return $this->price; }
    public function setPrice(string $price): static { $this->price = $price; return $this; }
    public function getThumbnailUrl(): ?string { return $this->thumbnailUrl; }
    public function setThumbnailUrl(?string $url): static { $this->thumbnailUrl = $url; return $this; }
    public function isPublished(): bool { return $this->published; }
    public function setPublished(bool $published): static { $this->published = $published; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getInstructor(): ?User { return $this->instructor; }
    public function setInstructor(?User $instructor): static { $this->instructor = $instructor; return $this; }
    public function getLessons(): Collection { return $this->lessons; }
    public function getEnrollments(): Collection { return $this->enrollments; }

    public function getQuizzes(): Collection { return $this->quizzes; }
    public function addQuiz(Quiz $quiz): static { if (!$this->quizzes->contains($quiz)) { $this->quizzes->add($quiz); $quiz->setCourse($this); } return $this; }
    public function removeQuiz(Quiz $quiz): static { if ($this->quizzes->removeElement($quiz)) { if ($quiz->getCourse() === $this) { $quiz->setCourse(null); } } return $this; }

    #[Groups(['course:read'])]
    public function getLessonCount(): int { return $this->lessons->count(); }

    #[Groups(['course:read'])]
    public function getStudentCount(): int { return $this->enrollments->count(); }

    #[Groups(['course:read'])]
    public function getQuizCount(): int { return $this->quizzes->count(); }
}
