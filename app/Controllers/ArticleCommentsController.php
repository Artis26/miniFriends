<?php

namespace App\Controllers;
use App\Database;
use App\Redirect;

class ArticleCommentsController {

    public function store(array $vars): Redirect {

        $authorID = $_SESSION['userid'];
        $author = $_SESSION['user'];
        $comment = $_POST['comment'];

        $new = Database::connection()->prepare
        ('INSERT INTO comments (text, author, author_id, article_id) VALUES (?, ?, ?, ?)');
        $new->execute([$comment, $author, $authorID, (int) $vars['id']]);

        return new Redirect('/articles/'. $vars['id']);
    }

    public function delete(array $vars): Redirect {
        $new = Database::connection()->prepare('DELETE FROM comments WHERE id = ?');
        $new->execute([(int)$vars['comment_id']]);

        return new Redirect('/articles/' . (int)$vars['id']);
    }
}
