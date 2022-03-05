<?php
namespace App\Models;

class User {
    private string $email;
    private string $pwd;
    private string $createdAt;
    private ?int $id;

    public function __construct(string $email, string $pwd, string $createdAt, ?int $id = null) {
        $this->email = $email;
        $this->pwd = $pwd;
        $this->createdAt = $createdAt;
        $this->id = $id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPwd(): string {
        return $this->pwd;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function getID(): int {
        return $this->id;
    }

}