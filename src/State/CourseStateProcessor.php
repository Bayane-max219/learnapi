<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CourseStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Course
    {
        /** @var Course $data */
        $user = $this->security->getUser();

        if ($operation instanceof Post) {
            $data->setInstructor($user);
        } else {
            if ($data->getInstructor()?->getId() !== $user?->getId()
                && !$this->security->isGranted('ROLE_ADMIN')
            ) {
                throw new AccessDeniedHttpException('Only the course instructor can modify this course');
            }
        }

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}
