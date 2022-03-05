<?php

namespace App\Models;

class Article {
    private string $title;
    private string $description;
    private string $createdAt;
    private ?string $author;
    private ?int $id;

    public function __construct(string $title, string $description, string $createdAt, ?string $author, ?int $id = null) {
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->author = $author;
        $this->id = $id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function getID(): ?int {
        return $this->id;
    }

    public function getAuthor(): ?string {
        return $this->author;
    }
}