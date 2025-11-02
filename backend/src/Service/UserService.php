<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\User;
use App\DTO\UserResponse;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pagerfanta\Doctrine\MongoDBODM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 50;

    public function __construct(
        private DocumentManager $documentManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
    }

    public function getPaginatedUsers(int $page, int $limit): array
    {
        $queryBuilder = $this->createQueryBuilder();
        $pagerfanta = $this->createPaginator($queryBuilder, $page, $limit);

        return [
            'data' => $this->usersToArray($pagerfanta->getCurrentPageResults()),
            'total' => $pagerfanta->getNbResults(),
            'page' => $pagerfanta->getCurrentPage(),
            'limit' => $pagerfanta->getMaxPerPage(),
            'has_previous' => $pagerfanta->hasPreviousPage(),
            'has_next' => $pagerfanta->hasNextPage(),
            'total_pages' => $pagerfanta->getNbPages(),
        ];
    }

    public function getUserById(string $id): ?User
    {
        return $this->documentManager->getRepository(User::class)->find($id);
    }

    public function getUserResponse(User $user): UserResponse
    {
        return new UserResponse(
            $user->getId() ?? '',
            $user->getName() ?? '',
            $user->getEmail() ?? '',
            $user->getRoles()
        );
    }

    public function updateUser(User $user, array $data): array
    {
        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['email'])) {
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $existingUser = $this->documentManager->getRepository(User::class)
                ->findOneBy(['email' => $data['email']]);
            
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return [
                    'success' => false,
                    'message' => 'This email is already used by another user',
                    'errors' => ['email' => 'This email is already used']
                ];
            }
            
            $user->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return [
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $this->formatValidationErrors($errors),
            ];
        }

        $this->documentManager->flush();

        return [
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ];
    }

    /**
     * @return array<string>
     */
    private function formatValidationErrors($errors): array
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }

        return $errorMessages;
    }

    private function createQueryBuilder(): Builder
    {
        return $this->documentManager->getRepository(User::class)
            ->createQueryBuilder();
    }

    private function createPaginator(Builder $queryBuilder, int $page, int $limit): Pagerfanta
    {
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(min(self::MAX_LIMIT, max(1, $limit)));
        $pagerfanta->setCurrentPage(max(1, $page));

        return $pagerfanta;
    }

    private function usersToArray(iterable $users): array
    {
        $result = [];
        foreach ($users as $user) {
            $result[] = $this->getUserResponse($user)->toArray();
        }

        return $result;
    }

    public function deleteUser(User $user): void
    {
        $this->documentManager->remove($user);
        $this->documentManager->flush();
    }
}

