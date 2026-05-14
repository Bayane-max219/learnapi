<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
            return $this->json(['error' => 'Champs requis : email, password, firstName, lastName'], Response::HTTP_BAD_REQUEST);
        }

        $existing = $this->em->getRepository(User::class)->findByEmail($data['email']);
        if ($existing) {
            return $this->json(['error' => 'Email déjà utilisé'], Response::HTTP_CONFLICT);
        }

        if (strlen($data['password']) < 8) {
            return $this->json(['error' => 'Le mot de passe doit contenir au moins 8 caractères'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setEmail($data['email']);
        $user->setPassword($this->hasher->hashPassword($user, $data['password']));

        if (isset($data['role']) && in_array($data['role'], ['ROLE_INSTRUCTOR', 'ROLE_ADMIN'])) {
            $user->setRoles([$data['role']]);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['error' => (string) $errors->get(0)->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->json([
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'fullName'  => $user->getFullName(),
            'roles'     => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], Response::HTTP_CREATED);
    }
}
