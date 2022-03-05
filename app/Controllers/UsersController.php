<?php


namespace App\Controllers;
use App\Database;
use App\Models\Article;
use App\Models\User;
use App\Redirect;
use App\View;
use PDO;
use Twig\Environment;

class UsersController {

        public function index(array $vars): View {

            $new = Database::connection()->prepare('SELECT * FROM users WHERE id = ?');
            $new->execute([(int) $vars['id']]);

            while ($row = $new->fetch(PDO::FETCH_ASSOC)) {

                $user = new User(
                    $row['email'],
                    $row['password'],
                    $row['created_at'],
                    $row['id']
                );
            }

            $new = Database::connection()->prepare('SELECT * FROM friends WHERE user_id = ?');
            $new->execute([$vars['id']]);

            while ($row = $new->fetch(PDO::FETCH_ASSOC)) {
                $friendsID[] = $row;
            }

            foreach ($friendsID as $one) {
                $new = Database::connection()->prepare('SELECT email,id  FROM users WHERE id = ?');
                $new->execute([$one['id']]);

                while ($row = $new->fetch(PDO::FETCH_ASSOC)) {
                    $friends[] = $row;
                }
            }

            $new = Database::connection()->prepare('SELECT * FROM friend_invites WHERE friend_id = ?');
            $new->execute([$_SESSION['userid']]);

            while ($row = $new->fetch(PDO::FETCH_ASSOC)) {
                $invitesID[] = $row;
            }

            foreach ($invitesID as $one) {
                $new = Database::connection()->prepare('SELECT email,id  FROM users WHERE id = ?');
                $new->execute([$one['user_id']]);

                while ($row = $new->fetch(PDO::FETCH_ASSOC)) {
                    $invites[] = $row;
                }
            }

            return new View('Users/index.html', [
                'user' => $user,
                'friends' => $friends,
                'invites' => $invites,
            ]);
        }

        public function show(): View {

            $query = Database::connection()->prepare('SELECT * FROM users WHERE id != ?');
            $query->execute([$_SESSION['userid']]);
            while ( $row = $query->fetch(PDO::FETCH_ASSOC)) {
                $users[] = new User(
                    $row['email'],
                    $row['password'],
                    $row['created_at'],
                    $row['id']
                );
            }

            $query = Database::connection()->prepare('SELECT * FROM friends WHERE user_id = ?');
            $query->execute([$_SESSION['userid']]);
            while ( $row = $query->fetch(PDO::FETCH_ASSOC)) {
                $friends[] = $row['friend_id'];
            }

            $query = Database::connection()->prepare('SELECT * FROM friend_invites WHERE user_id = ?');
            $query->execute([$_SESSION['userid']]);
            while ( $row = $query->fetch(PDO::FETCH_ASSOC)) {
                $invites[] = $row['friend_id'];
            }

            return new View('Users/show.html', [
                'users' => $users,
                'friends' => $friends,
                'invites' => $invites
            ]);
        }

        public function register():View {
            return new View('Users/register.html');
        }

        public function signUp(): Redirect {
            $email = $_POST['email'];
            $pwd = $_POST['pwd'];
            $pwdRepeat = $_POST['pwd_repeat'];
            $new = Database::connection()->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
            if ($pwd !== $pwdRepeat) return new Redirect('articles/x');
            $pwd = password_hash($pwd, PASSWORD_DEFAULT);
            $new->execute([$email, $pwd]);

            return new Redirect('/articles');
        }

    public function login(): View {
        return new View('Users/login.html');
    }

    public function signIn(): Redirect {
        $email = $_POST['email'];
        $pwd = $_POST['pwd'];
        $new = Database::connection()->prepare('SELECT password, id FROM users WHERE email = ?');
        $new->execute([$email]);

        while ($row = $new->fetch(PDO::FETCH_ASSOC)) {
            $val = $row;
        }
        $id = $val['id'];

        $checkPwd = password_verify($pwd, $val['password']);

        if ($checkPwd == false) {
            header("location: ../index.php?error=usernotfound");
            exit();
        }

        $_SESSION['user'] = $email;
        $_SESSION['userid'] = $id;

        return new Redirect('/articles');
    }

    public function logout(): Redirect {
            session_unset();
            return new Redirect('/articles');
    }

    public function invite(array $vars): Redirect {
        $userID = $_SESSION['userid'];
        $friendID = $vars['friend_id'];
        $new = Database::connection()->prepare('INSERT INTO friend_invites (user_id, friend_id) VALUES (?, ?)');
        $new->execute([$userID, $friendID]);

        return new Redirect('/users');
    }

    public function accept(array $vars): Redirect {
        $friendID = $vars['friend_id'];
        $new = Database::connection()->prepare('DELETE FROM friend_invites WHERE user_id = ? && friend_id = ?');
        $new->execute([$friendID, $_SESSION['userid']]);

        $new = Database::connection()->prepare('INSERT INTO friends (user_id, friends_id) VALUES (?, ?)');
        $new->execute([$_SESSION['userid'], $friendID ]);

        return new Redirect('/user/'. $_SESSION['userid']);
    }

    public function decline(array $vars): Redirect {
        $friendID = $vars['friend_id'];
        $new = Database::connection()->prepare('DELETE FROM friend_invites WHERE user_id = ? && friend_id = ?');
        $new->execute([$friendID, $_SESSION['userid']]);

        return new Redirect('/user/'. $_SESSION['userid']);
    }

    public function home(): View {
            return new View('home.html');
    }

    public function current(): Redirect {
        return new Redirect('/user/'. $_SESSION['userid']);
    }
}