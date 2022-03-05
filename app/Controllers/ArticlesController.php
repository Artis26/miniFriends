<?php

namespace App\Controllers;

use App\Database;
use App\Exceptions\FormValidationException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Article;
use App\Models\Comment;
use App\Redirect;
use App\Validation\ArticleFormValidator;
use App\Validation\Errors;
use App\View;
use mysql_xdevapi\Exception;
use PDO;

class ArticlesController {

    public function index(array $vars): View {

        $query = Database::connection()->prepare('SELECT * FROM articles WHERE id = ?');
        $query->execute([(int)$vars['id']]);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

            $article = new Article(
                $row['title'],
                $row['description'],
                $row['created_at'],
                $row['author'],
                $row['id']
            );
        }

        $query = Database::connection()->prepare('SELECT * FROM articles_likes');
        $query->execute();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $articleLikes[] = $row['user_id'];
        }

        $query = Database::connection()->prepare('SELECT * FROM comments WHERE article_id = ?');
        $query->execute([(int) $vars['id']]);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

            $comments[] = new Comment(
                $row['text'],
                $row['author'],
                $row['author_id'],
                $row['article_id'],
                $row['created_at'],
                $row['id']
            );
        }

        return new View('Articles/index.html', [
            'article' => $article,
            'likes' => $articleLikes,
            'comments' => $comments
        ]);
    }

    public function show(): View {

        $query = Database::connection()->prepare('SELECT * FROM articles ORDER BY created_at desc');
        $query->execute();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $article[] = new Article(
                $row['title'],
                $row['description'],
                $row['created_at'],
                $row['author'],
                $row['id']
            );
        }

        return new View('Articles/show.html', [
            'article' => $article
        ]);
    }

    public function create(): View {
        return new View('Articles/create.html', [
            'errors' => Errors::getAll(),
            'inputs' => $_SESSION['inputs'] ?? []
        ]);
    }

    public function store(): Redirect {
        try {
            $validator = (new ArticleFormValidator($_POST, [
                'title' => ['required', 'min:3'],
                'description' => ['required', 'min:5']
            ]));
            $validator->passes();

            $title = $_POST['title'];
            $desc = $_POST['description'];
            $author = $_SESSION['user'];
            $new = Database::connection()->prepare('INSERT INTO articles (title, description, author) VALUES (?, ?, ?)');
            $new->execute([$title, $desc, $author]);

            return new Redirect('/articles');
        } catch (FormValidationException $exception) {
            $_SESSION['errors'] = $validator->getErrors();
            $_SESSION['inputs'] = $_POST;

            return new Redirect('/articles/create');
        }
    }

    public function delete(array $vars): Redirect {
        $new = Database::connection()->prepare('DELETE FROM articles WHERE id = ?');
        $new->execute([(int)$vars['id']]);

        return new Redirect('/articles');
    }

    public function edit(array $vars): View {
        try {
            $query = Database::connection()->prepare('SELECT * FROM articles WHERE id = ?');
            $query->execute([(int)$vars['id']]);
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $vars = $row;
            }

            if (count($vars) <= 1) {
                throw new ResourceNotFoundException("Article with [id {$vars['id']}] not found");
            }

            return new View('Articles/edit.html', [
                'article' => $vars
            ]);

        } catch (ResourceNotFoundException $exception) {
            return new View('404.html');
        }
    }

    public function update(array $vars): Redirect {

        $new = Database::connection()->prepare('UPDATE articles SET title = ?, description = ? WHERE id = ?');
        $id = (int)$vars["id"];
        $new->execute([$_POST['title'], $_POST['description'], $id]);

        return new Redirect('/articles' . '/' . $id);
    }

    public function like(array $vars): Redirect {
        $userID = $_SESSION['userid'];
        $articleID = (int)$vars['id'];

        $stmt = Database::connection()->prepare('SELECT id FROM articles_likes WHERE (article_id = ? && user_id = ?)');
        $stmt->execute([$articleID, $userID]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $exist = $row;
        }

        if ($exist != null) {
            $new = Database::connection()->prepare(' DELETE FROM articles_likes WHERE id = ?');
            $new->execute([(int) $exist['id']]);
        } else {
            $new = Database::connection()->prepare('INSERT INTO articles_likes (article_id, user_id) VALUES (?, ?)');
            $new->execute([$articleID, $userID]);
        }

        return new Redirect('/articles/' . $articleID);
    }
}

