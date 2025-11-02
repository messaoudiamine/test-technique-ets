<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(collection: 'articles')]
class Article
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Title must contain at least {{ limit }} characters', maxMessage: 'Title cannot exceed {{ limit }} characters')]
    private ?string $title = null;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Content is required')]
    #[Assert\Length(min: 10, minMessage: 'Content must contain at least {{ limit }} characters')]
    private ?string $content = null;

    #[ODM\ReferenceOne(storeAs: 'id', targetDocument: User::class)]
    private ?User $auteur = null;

    #[ODM\Field(type: 'date')]
    private ?\DateTimeInterface $publicationDate = null;

    public function __construct()
    {
        $this->publicationDate = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function getAuteurId(): ?string
    {
        return $this->auteur?->getId();
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }
}
