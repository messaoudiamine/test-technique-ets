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
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {}

    /**
     * @throws UserAlreadyExistsException
     */
    public function register(UserDTO $dto): array
    {
        if ($this->userRepository->userExists($dto->email)) {
            throw new UserAlreadyExistsException($dto->email);
        }

        $user = new User();
        $user->setName($dto->name);
        $user->setEmail($dto->email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $dto->password)
        );

        $this->userRepository->save($user);

        $token = $this->jwtManager->create($user);

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

