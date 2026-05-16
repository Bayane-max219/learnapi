<?php

namespace App\Security\Voter;

use App\Entity\Course;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CourseVoter extends Voter
{
    public const EDIT = 'COURSE_EDIT';
    public const DELETE = 'COURSE_DELETE';
    public const PUBLISH = 'COURSE_PUBLISH';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::PUBLISH])
            && $subject instanceof Course;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Course $course */
        $course = $subject;

        return match ($attribute) {
            self::EDIT, self::DELETE => $this->isOwnerOrAdmin($course, $user),
            self::PUBLISH => $this->canPublish($course, $user),
            default => false,
        };
    }

    private function isOwnerOrAdmin(Course $course, User $user): bool
    {
        return $course->getInstructor()?->getId() === $user->getId()
            || in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function canPublish(Course $course, User $user): bool
    {
        if (!$this->isOwnerOrAdmin($course, $user)) {
            return false;
        }
        return $course->getLessons()->count() > 0;
    }
}
