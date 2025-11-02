<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\UserDTO;
use App\Exception\UserAlreadyExistsException;
use App\Helper\RequestValidationHelper;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RequestValidationHelper $requestValidationHelper
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $dtoOrError = $this->requestValidationHelper->deserializeRequest($request, UserDTO::class);
        if ($dtoOrError instanceof JsonResponse) {
            return $dtoOrError;
        }
        $dto = $dtoOrError;

        // Validation avec le groupe 'create'
        $validationError = $this->requestValidationHelper->validateDto($dto, ['create']);
        if ($validationError) {
            return $validationError;
        }

        try {
            $result = $this->authService->register($dto);

            if (isset($result['errors'])) {
                return $this->json(
                    [
                        'message' => $result['message'] ?? 'Validation errors',
                        'errors' => $result['errors']
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            return $this->json(
                [
                    'message' => 'User created successfully',
                    'token' => $result['token'],
                    'user' => $result['user'],
                ],
                Response::HTTP_CREATED
            );
        } catch (UserAlreadyExistsException $e) {
            return $this->json(
                ['message' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        } catch (\Exception $e) {
            return $this->json(
                ['message' => 'An error occurred during registration'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return $this->json(
            [
                'message' => 'This endpoint is handled automatically. Authentication requires valid email and password in JSON format.',
                'error' => 'invalid_request'
            ],
            Response::HTTP_BAD_REQUEST
        );
    }
}
