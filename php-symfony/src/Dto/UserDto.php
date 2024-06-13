<?php

namespace App\Dto;

use App\Entity\User;

class UserDto
{
    private ?int $id = null;
    private ?string $username = null;
    private ?string $email = null;
    private ?string $roles = null;
    private bool $isblocked = false;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->username = $user->getUsername();
        $this->email = $user->getEmail();
        $this->roles = implode(', ', $user->getRoles());
        $this->isblocked = $user->isIsblocked();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getRoles(): ?string
    {
        return $this->roles;
    }

    public function getIsblocked(): bool
    {
        return $this->isblocked;
    }
}