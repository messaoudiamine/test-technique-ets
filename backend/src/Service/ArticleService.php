<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\Article;
use App\Document\User;
use App\DTO\ArticleResponse;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Pagerfanta\Doctrine\MongoDBODM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleService
{
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 50;

    public function __construct(
        private DocumentManager $documentManager,
        private ValidatorInterface $validator
    ) {
    }

    public function getPaginatedArticles(User $user, int $page, int $limit): array
    {
        // Les admins voient tous les articles, les autres utilisateurs voient seulement les leurs
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $queryBuilder = $this->createQueryBuilderForAll();
        } else {
            $queryBuilder = $this->createQueryBuilderForUser($user->getId());
        }
        
        $pagerfanta = $this->createPaginator($queryBuilder, $page, $limit);

        return [
            'data' => $this->articlesToArray($pagerfanta->getCurrentPageResults()),
            'total' => $pagerfanta->getNbResults(),
            'page' => $pagerfanta->getCurrentPage(),
            'limit' => $pagerfanta->getMaxPerPage(),
            'has_previous' => $pagerfanta->hasPreviousPage(),
            'has_next' => $pagerfanta->hasNextPage(),
            'total_pages' => $pagerfanta->getNbPages(),
        ];
    }

    public function getArticleById(string $id): ?Article
    {
        return $this->documentManager->getRepository(Article::class)->find($id);
    }

    public function getArticleResponse(Article $article, bool $includeAuthor = false): ArticleResponse
    {
        $author = null;
        if ($includeAuthor && $article->getAuteur()) {
            $auteur = $article->getAuteur();
            $author = [
                'id' => $auteur->getId(),
                'name' => $auteur->getName(),
            ];
        }

        return new ArticleResponse(
            $article->getId() ?? '',
            $article->getTitle() ?? '',
            $article->getContent() ?? '',
            $article->getAuteurId(),
            $article->getPublicationDate()?->format('Y-m-d\TH:i:s\Z'),
            $author
        );
    }

    public function createArticle(array $data, User $user): array
    {
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setAuteur($user);

        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            return [
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $this->formatValidationErrors($errors),
            ];
        }

        $this->documentManager->persist($article);
        $this->documentManager->flush();

        return [
            'success' => true,
            'data' => $this->getArticleResponse($article, true)->toArray(),
        ];
    }

    public function updateArticle(Article $article, array $data): array
    {
        if (isset($data['title'])) {
            $article->setTitle($data['title']);
        }

        if (isset($data['content'])) {
            $article->setContent($data['content']);
        }

        $errors = $this->validator->validate($article);
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
            'data' => $this->getArticleResponse($article, true)->toArray(),
        ];
    }

    public function deleteArticle(Article $article): void
    {
        $this->documentManager->remove($article);
        $this->documentManager->flush();
    }

    private function createQueryBuilderForUser(string $userId): Builder
    {
        return $this->documentManager->getRepository(Article::class)
            ->createQueryBuilder()
            ->field('auteur')->equals($userId)
            ->sort('publicationDate', 'desc');
    }

    private function createQueryBuilderForAll(): Builder
    {
        return $this->documentManager->getRepository(Article::class)
            ->createQueryBuilder()
            ->sort('publicationDate', 'desc');
    }

    private function createPaginator(Builder $queryBuilder, int $page, int $limit): Pagerfanta
    {
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(min(self::MAX_LIMIT, max(1, $limit)));
        $pagerfanta->setCurrentPage(max(1, $page));

        return $pagerfanta;
    }

    /**
     * @param iterable<Article> $articles
     * @return array<array<string, mixed>>
     */
    private function articlesToArray(iterable $articles): array
    {
        $result = [];
        foreach ($articles as $article) {
            $result[] = $this->getArticleResponse($article)->toArray();
        }

        return $result;
    }

    /**
     * @return array<string>
     */
    private function formatValidationErrors($errors): array
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
        }

        return $errorMessages;
    }
}

