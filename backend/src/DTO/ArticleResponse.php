<?php

declare(strict_types=1);

namespace App\DTO;

readonly class ArticleResponse
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
        public ?string $auteurId,
        public ?string $publicationDate,
        public ?array $author = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'auteur_id' => $this->auteurId,
            'publication_date' => $this->publicationDate,
        ];
        
        if ($this->author !== null) {
            $data['author'] = $this->author;
        }
        
        return $data;
    }
}

