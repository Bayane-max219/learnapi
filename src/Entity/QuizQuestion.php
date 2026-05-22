<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\QuizQuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuizQuestionRepository::class)]
#[ApiResource(
    operations: [new Get(), new GetCollection(), new Post(), new Put(), new Delete()],
    normalizationContext: ['groups' => ['question:read']],
    denormalizationContext: ['groups' => ['question:write']],

)]
class QuizQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['question:read', 'quiz:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['question:read', 'question:write', 'quiz:read'])]
    private string $questionText = '';

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['question:read', 'question:write', 'quiz:read'])]
    private array $options = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['question:write'])]
    private string $correctAnswer = '';

    #[ORM\Column(options: ['default' => 1])]
    #[Assert\Positive]
    #[Groups(['question:read', 'question:write', 'quiz:read'])]
    private int $points = 1;

    #[ORM\Column(options: ['default' => 0])]
    #[Groups(['question:read', 'question:write'])]
    private int $position = 0;

    #[ORM\Column(length: 20, options: ['default' => 'single'])]
    #[Assert\Choice(choices: ['single', 'multiple', 'true_false'])]
    #[Groups(['question:read', 'question:write', 'quiz:read'])]
    private string $type = 'single';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['question:read', 'question:write'])]
    private ?string $explanation = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['question:read', 'question:write'])]
    private ?Quiz $quiz = null;

    public function getId(): ?int { return $this->id; }
    public function getQuestionText(): string { return $this->questionText; }
    public function setQuestionText(string $text): static { $this->questionText = $text; return $this; }
    public function getOptions(): array { return $this->options; }
    public function setOptions(array $options): static { $this->options = $options; return $this; }
    public function getCorrectAnswer(): string { return $this->correctAnswer; }
    public function setCorrectAnswer(string $answer): static { $this->correctAnswer = $answer; return $this; }
    public function getPoints(): int { return $this->points; }
    public function setPoints(int $points): static { $this->points = $points; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getExplanation(): ?string { return $this->explanation; }
    public function setExplanation(?string $explanation): static { $this->explanation = $explanation; return $this; }
    public function getQuiz(): ?Quiz { return $this->quiz; }
    public function setQuiz(?Quiz $quiz): static { $this->quiz = $quiz; return $this; }

    public function isCorrect(string $answer): bool
    {
        return strtolower(trim($answer)) === strtolower(trim($this->correctAnswer));
    }
}
