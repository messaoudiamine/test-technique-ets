<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\UserDTO;
use App\DTO\UserResponse;
use App\Document\User;
use App\Exception\UserAlreadyExistsException;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    /**
     * @throws UserAlreadyExistsException
     */
    public function register(UserDTO $dto): array
    {
        // Check if user already exists
        if ($this->userRepository->userExists($dto->email)) {
            throw new UserAlreadyExistsException($dto->email);
        }

        // Create user document
        $user = new User();
        $user->setName($dto->name);
        $user->setEmail($dto->email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $dto->password)
        );

        // Save document in MongoDB
        $this->userRepository->save($user);

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        // Create response
        $userResponse = new UserResponse(
            $user->getId() ?? '',
            $user->getName() ?? '',
            $user->getEmail() ?? '',
            $user->getRoles()
        );

        return [
            'success' => true,
            'token' => $token,
            'user' => $userResponse->toArray(),
        ];
    }
}

