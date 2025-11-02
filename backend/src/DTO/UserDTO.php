<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    #[Assert\NotBlank(message: 'Name is required', groups: ['create'])]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Name must contain at least {{ limit }} characters',
        maxMessage: 'Name cannot exceed {{ limit }} characters',
        groups: ['create', 'update']
    )]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'Email is required', groups: ['create'])]
    #[Assert\Email(message: 'Email is not valid', groups: ['create', 'update'])]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Password is required', groups: ['create'])]
    #[Assert\Length(
        min: 8,
        minMessage: 'Password must contain at least {{ limit }} characters',
        groups: ['create']
    )]
    #[Assert\Length(
        min: 6,
        minMessage: 'Password must contain at least {{ limit }} characters',
        groups: ['update']
    )]
    public ?string $password = null;

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ], fn($value) => $value !== null);
    }
}

