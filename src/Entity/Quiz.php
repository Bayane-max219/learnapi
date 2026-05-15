<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ApiResource(
    operations: [new Get(), new GetCollection(), new Post(), new Put(), new Delete()],
    normalizationContext: ['groups' => ['quiz:read']],
    denormalizationContext: ['groups' => ['quiz:write']],
    security: "is_granted('ROLE_USER')"
)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quiz:read', 'course:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['quiz:read', 'quiz:write', 'course:read'])]
    private string $title = '';

    #[ORM\Column(options: ['default' => 70])]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['quiz:read', 'quiz:write'])]
    private int $passingScore = 70;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    #[Groups(['quiz:read', 'quiz:write'])]
    private int $timeLimit = 0;

    #[ORM\Column(options: ['default' => true])]
    #[Groups(['quiz:read', 'quiz:write'])]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['quiz:read', 'quiz:write'])]
    private ?Course $course = null;

    #[ORM\OneToMany(targetEntity: QuizQuestion::class, mappedBy: 'quiz', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups(['quiz:read'])]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getPassingScore(): int { return $this->passingScore; }
    public function setPassingScore(int $score): static { $this->passingScore = $score; return $this; }
    public function getTimeLimit(): int { return $this->timeLimit; }
    public function setTimeLimit(int $t): static { $this->timeLimit = $t; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $active): static { $this->isActive = $active; return $this; }
    public function getCourse(): ?Course { return $this->course; }
    public function setCourse(?Course $course): static { $this->course = $course; return $this; }

    public function getQuestions(): Collection { return $this->questions; }
    public function addQuestion(QuizQuestion $q): static { if (!$this->questions->contains($q)) { $this->questions->add($q); $q->setQuiz($this); } return $this; }
    public function removeQuestion(QuizQuestion $q): static { if ($this->questions->removeElement($q)) { if ($q->getQuiz() === $this) { $q->setQuiz(null); } } return $this; }

    #[Groups(['quiz:read'])]
    public function getQuestionCount(): int { return $this->questions->count(); }

    #[Groups(['quiz:read'])]
    public function getTotalPoints(): int
    {
        return array_sum($this->questions->map(fn(QuizQuestion $q) => $q->getPoints())->toArray());
    }
}
