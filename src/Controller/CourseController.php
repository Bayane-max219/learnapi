<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Provides HTTP cache with ETag + Last-Modified on course listings.
 * Complements the API Platform resources with cache-aware endpoints.
 */
#[Route('/api/courses/cached')]
class CourseController extends AbstractController
{
    public function __construct(
        private readonly CourseRepository $courseRepository,
    ) {}

    #[Route('', name: 'courses_cached_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $courses = $this->courseRepository->findAll();

        if (empty($courses)) {
            return $this->json([]);
        }

        // Compute ETag from latest updatedAt + count
        $lastModified = null;
        foreach ($courses as $course) {
            $updatedAt = method_exists($course, 'getUpdatedAt') ? $course->getUpdatedAt() : null;
            if ($updatedAt instanceof \DateTimeInterface) {
                if ($lastModified === null || $updatedAt > $lastModified) {
                    $lastModified = $updatedAt;
                }
            }
        }

        $etag = md5(count($courses) . ($lastModified?->getTimestamp() ?? ''));

        $response = new JsonResponse();
        $response->setEtag($etag);

        if ($lastModified !== null) {
            $response->setLastModified(\DateTimeImmutable::createFromInterface($lastModified));
        }

        $response->setPublic();
        $response->setMaxAge(60); // 1 minute browser cache
        $response->setSharedMaxAge(300); // 5 minutes CDN/proxy cache

        // Return 304 Not Modified if client cache is fresh
        if ($response->isNotModified($request)) {
            return $response;
        }

        $data = array_map(fn($course) => [
            'id' => $course->getId(),
            'title' => $course->getTitle(),
            'category' => method_exists($course, 'getCategory') ? $course->getCategory() : null,
            'level' => method_exists($course, 'getLevel') ? $course->getLevel() : null,
            'price' => method_exists($course, 'getPrice') ? $course->getPrice() : null,
            'lessonsCount' => method_exists($course, 'getLessons') ? count($course->getLessons()) : 0,
        ], $courses);

        $response->setData($data);
        return $response;
    }

    #[Route('/{id}', name: 'courses_cached_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request): Response
    {
        $course = $this->courseRepository->find($id);

        if (!$course) {
            return $this->json(['message' => 'Course not found'], Response::HTTP_NOT_FOUND);
        }

        $updatedAt = method_exists($course, 'getUpdatedAt') ? $course->getUpdatedAt() : null;
        $etag = md5($course->getId() . $course->getTitle() . ($updatedAt?->getTimestamp() ?? ''));

        $response = new JsonResponse();
        $response->setEtag($etag);

        if ($updatedAt instanceof \DateTimeInterface) {
            $response->setLastModified(\DateTimeImmutable::createFromInterface($updatedAt));
        }

        $response->setPublic();
        $response->setMaxAge(120);
        $response->setSharedMaxAge(600);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->setData([
            'id' => $course->getId(),
            'title' => $course->getTitle(),
            'description' => method_exists($course, 'getDescription') ? $course->getDescription() : null,
            'category' => method_exists($course, 'getCategory') ? $course->getCategory() : null,
            'level' => method_exists($course, 'getLevel') ? $course->getLevel() : null,
            'price' => method_exists($course, 'getPrice') ? $course->getPrice() : null,
        ]);

        return $response;
    }
}
