<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Quiz;
use PHPUnit\Framework\TestCase;

class CourseTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $course = new Course();

        $this->assertSame('', $course->getTitle());
        $this->assertSame('', $course->getDescription());
        $this->assertSame('beginner', $course->getLevel());
        $this->assertSame('', $course->getCategory());
        $this->assertSame('0.00', $course->getPrice());
        $this->assertFalse($course->isPublished());
        $this->assertNull($course->getThumbnailUrl());
        $this->assertInstanceOf(\DateTimeImmutable::class, $course->getCreatedAt());
    }

    public function testSettersReturnFluentInterface(): void
    {
        $course = new Course();

        $result = $course
            ->setTitle('Python pour tous')
            ->setDescription('Apprenez Python de zéro')
            ->setLevel('intermediate')
            ->setCategory('programming')
            ->setPrice('29.99')
            ->setPublished(true)
            ->setThumbnailUrl('https://cdn.example.com/python.jpg');

        $this->assertSame($course, $result);
        $this->assertSame('Python pour tous', $course->getTitle());
        $this->assertSame('intermediate', $course->getLevel());
        $this->assertSame('29.99', $course->getPrice());
        $this->assertTrue($course->isPublished());
    }

    public function testLessonCountReflectsCollection(): void
    {
        $course = new Course();
        $this->assertSame(0, $course->getLessonCount());
    }

    public function testStudentCountReflectsEnrollments(): void
    {
        $course = new Course();
        $this->assertSame(0, $course->getStudentCount());
    }

    public function testQuizCountReflectsCollection(): void
    {
        $course = new Course();
        $this->assertSame(0, $course->getQuizCount());
    }

    public function testAddQuizIncrementsCount(): void
    {
        $course = new Course();
        $quiz = $this->createMock(Quiz::class);
        $quiz->method('getCourse')->willReturn(null);

        $course->addQuiz($quiz);
        $this->assertCount(1, $course->getQuizzes());
    }

    public function testAddSameQuizTwiceDoesNotDuplicate(): void
    {
        $course = new Course();
        $quiz = $this->createMock(Quiz::class);
        $quiz->method('getCourse')->willReturn($course);

        $course->addQuiz($quiz);
        $course->addQuiz($quiz);

        $this->assertCount(1, $course->getQuizzes());
    }

    public function testThumbnailUrlCanBeNull(): void
    {
        $course = new Course();
        $course->setThumbnailUrl(null);
        $this->assertNull($course->getThumbnailUrl());
    }
}
