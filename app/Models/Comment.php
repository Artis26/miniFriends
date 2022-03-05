<?php
namespace App\Models;

class Comment {
    private string $text;
    private string $author;
    private int $authorID;
    private string $createdAt;
    private int $articleID;
    private ?int $id;

    public function __construct(string $text, string $author, int $authorID, int $articleID, string $createdAt, ?int $id = null) {
        $this->text = $text;
        $this->createdAt = $createdAt;
        $this->author = $author;
        $this->authorID = $authorID;
        $this->articleID = $articleID;
        $this->id = $id;
    }

    public function getText(): string {
        return $this->text;
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

    public function getAuthorID(): int {
        return $this->authorID;
    }

    public function getArticleID(): int {
        return $this->articleID;
    }
}