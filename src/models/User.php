<?php

namespace Src\Models;

class User
{
    private ?string $id;
    private string $name;
    private string $email;
    private string $createdAt;

    public function __construct(string $name, string $email, ?string $id = null, ?string $createdAt = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): ?string
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
