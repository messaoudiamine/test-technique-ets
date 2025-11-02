<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\UserDTO;
use App\Document\User;
use App\Helper\RequestValidationHelper;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly RequestValidationHelper $requestValidationHelper
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $result = $this->userService->getPaginatedUsers($page, $limit);

        return $this->json($result);
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profile(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json($user);
    }

    #[Route('/profile', name: 'update_profile', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->handleUpdate($user, $request);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(string $id, Request $request): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->json(
                [
                    'message' => 'User not found'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->handleUpdate($user, $request);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return $this->json(
                [
                    'message' => 'User not found'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $currentUser = $this->getUser();

        if ($user->getId() === $currentUser->getId()) {
            return $this->json(
                [
                    'message' => 'You cannot delete your own account'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->userService->deleteUser($user);

        return $this->json(
            [
                'message' => 'User deleted successfully'
            ]
        );
    }

    /**
     * Handle user update logic (deserialize, validate, update)
     */
    private function handleUpdate(User $user, Request $request): JsonResponse
    {
        $dtoOrError = $this->requestValidationHelper->deserializeRequest($request, UserDTO::class);
        if ($dtoOrError instanceof JsonResponse) {
            return $dtoOrError;
        }

        $dto = $dtoOrError;

        $validationError = $this->requestValidationHelper->validateDto($dto, ['update']);

        if ($validationError) {
            return $validationError;
        }

        $result = $this->userService->updateUser($user, $dto->toArray());
        if (!$result['success']) {
            return $this->json(
                ['message' => $result['message'], 'errors' => $result['errors'] ?? []],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json($result['data']['user'] ?? $user);
    }
}
