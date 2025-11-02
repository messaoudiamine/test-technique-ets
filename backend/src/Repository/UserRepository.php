<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;

readonly class UserRepository
{
    public function __construct(
        private DocumentManager $documentManager
    ) {}

    public function findOneByEmail(string $email): ?User
    {
        return $this->documentManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    /**
     * @throws \Throwable
     * @throws MongoDBException
     */
    public function save(User $user): void
    {
        $this->documentManager->persist($user);
        $this->documentManager->flush();
    }

    public function userExists(string $email): bool
    {
        return $this->findOneByEmail($email) !== null;
    }
}
