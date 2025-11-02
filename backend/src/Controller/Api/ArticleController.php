<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\User;
use App\DTO\ArticleDTO;
use App\Helper\RequestValidationHelper;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/articles', name: 'api_articles_')]
class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleService $articleService,
        private readonly RequestValidationHelper $requestValidationHelper
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $result = $this->articleService->getPaginatedArticles($user, $page, $limit);

        return $this->json($result);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(string $id): JsonResponse
    {
        $article = $this->articleService->getArticleById($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('VIEW', $article);

        $articleResponse = $this->articleService->getArticleResponse($article, true);
        
        return $this->json($articleResponse->toArray());
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $dtoOrError = $this->requestValidationHelper->deserializeRequest($request, ArticleDTO::class);
        if ($dtoOrError instanceof JsonResponse) {
            return $dtoOrError;
        }
        $dto = $dtoOrError;

        // Validation avec le groupe 'create'
        $validationError = $this->requestValidationHelper->validateDto($dto, ['create']);
        if ($validationError) {
            return $validationError;
        }

        $user = $this->getUser();

        $result = $this->articleService->createArticle($dto->toArray(), $user);

        if (!$result['success']) {
            return $this->json(
                [
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json($result['data'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(string $id, Request $request): JsonResponse
    {
        $article = $this->articleService->getArticleById($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('EDIT', $article);

        $dtoOrError = $this->requestValidationHelper->deserializeRequest($request, ArticleDTO::class);
        if ($dtoOrError instanceof JsonResponse) {
            return $dtoOrError;
        }
        $dto = $dtoOrError;

        // Validation avec le groupe 'update'
        $validationError = $this->requestValidationHelper->validateDto($dto, ['update']);
        if ($validationError) {
            return $validationError;
        }

        $result = $this->articleService->updateArticle($article, $dto->toArray());

        if (!$result['success']) {
            return $this->json(
                [
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json($result['data']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(string $id): JsonResponse
    {
        $article = $this->articleService->getArticleById($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('DELETE', $article);

        $this->articleService->deleteArticle($article);

        return $this->json(['message' => 'Article deleted successfully']);
    }
}
