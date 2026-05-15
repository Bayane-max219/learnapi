<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Enrollment;
use App\Repository\EnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class EnrollmentStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly EnrollmentRepository $enrollmentRepo,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Enrollment
    {
        /** @var Enrollment $data */
        $user = $this->security->getUser();

        if ($user === null) {
            throw new UnauthorizedHttpException('JWT', 'Authentication required');
        }

        $data->setStudent($user);

        $existing = $this->enrollmentRepo->findOneBy([
            'student' => $user,
            'course' => $data->getCourse(),
        ]);

        if ($existing !== null) {
            throw new ConflictHttpException('You are already enrolled in this course');
        }

        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }
}
