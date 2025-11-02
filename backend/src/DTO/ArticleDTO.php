<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ArticleDTO
{
    #[Assert\NotBlank(message: 'Title is required', groups: ['create'])]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Title must contain at least {{ limit }} characters',
        maxMessage: 'Title cannot exceed {{ limit }} characters',
        groups: ['create', 'update']
    )]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'Content is required', groups: ['create'])]
    #[Assert\Length(
        min: 10,
        minMessage: 'Content must contain at least {{ limit }} characters',
        groups: ['create', 'update']
    )]
    public ?string $content = null;

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'content' => $this->content,
        ], fn($value) => $value !== null);
    }
}

