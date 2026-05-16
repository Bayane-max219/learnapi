<?php

namespace App\Security\Voter;

use App\Entity\Enrollment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EnrollmentVoter extends Voter
{
    public const VIEW = 'ENROLLMENT_VIEW';
    public const UPDATE_PROGRESS = 'ENROLLMENT_UPDATE_PROGRESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::UPDATE_PROGRESS])
            && $subject instanceof Enrollment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Enrollment $enrollment */
        $enrollment = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($enrollment, $user),
            self::UPDATE_PROGRESS => $this->isStudent($enrollment, $user),
            default => false,
        };
    }

    private function canView(Enrollment $enrollment, User $user): bool
    {
        return $enrollment->getStudent()?->getId() === $user->getId()
            || $enrollment->getCourse()?->getInstructor()?->getId() === $user->getId()
            || in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function isStudent(Enrollment $enrollment, User $user): bool
    {
        return $enrollment->getStudent()?->getId() === $user->getId();
    }
}
