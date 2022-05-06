<?php
session_start();
use App\Controllers\ArticleCommentsController;
use App\Controllers\UsersController;
use App\Redirect;
use App\View;
use App\Controllers\ArticlesController;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {

    $r->addRoute('GET', '/', [UsersController::class, 'home']);
    $r->addRoute('GET', '/user/myprofile', [UsersController::class, 'current']);
    //users
    $r->addRoute('GET', '/users', [UsersController::class, 'show']);
    $r->addRoute('GET', '/user/{id:\d+}', [UsersController::class, 'index']);

    $r->addRoute('GET', '/user/register', [UsersController::class, 'register']);
    $r->addRoute('POST', '/user/register', [UsersController::class, 'signUp']);

    $r->addRoute('GET', '/user/login', [UsersController::class, 'login']);
    $r->addRoute('POST', '/user/login', [UsersController::class, 'signIn']);

    $r->addRoute('POST', '/user/logout', [UsersController::class, 'logout']);

    $r->addRoute('POST', '/user/{id:\d+}/invite/{friend_id:\d+}', [UsersController::class, 'invite']);
    $r->addRoute('POST', '/user/{id:\d+}/invite/{friend_id:\d+}/accept', [UsersController::class, 'accept']);
    $r->addRoute('POST', '/user/{id:\d+}/invite/{friend_id:\d+}/decline', [UsersController::class, 'decline']);

    //articles
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticlesController::class, 'index']);
    $r->addRoute('GET', '/articles', [ArticlesController::class, 'show']);

    $r->addRoute('GET', '/articles/create', [ArticlesController::class, 'create']);
    $r->addRoute('POST', '/articles', [ArticlesController::class, 'store']);

    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticlesController::class, 'delete']);

    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticlesController::class, 'edit']);
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticlesController::class, 'update']);

    $r->addRoute('POST', '/articles/{id:\d+}/like', [ArticlesController::class, 'like']);

    //comments
    $r->addRoute('POST', '/articles/{id:\d+}/store', [ArticleCommentsController::class, 'store']);
    $r->addRoute('POST', '/articles/{id:\d+}/comment/{comment_id:\d+}/delete',
        [ArticleCommentsController::class, 'delete']);
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:

        $handler = $routeInfo[1][0];
        $controller = $handler[0];
        $method = $routeInfo[1][1];
        $vars = $routeInfo[2];

        /** @var View $response */
        $response = (new $handler)->$method($vars);

        $loader = new FilesystemLoader('app/View');
        $twig = new Environment($loader);
        $twig->addGlobal('session', $_SESSION);

        if ($response instanceof View) {
            echo $twig->render($response->getPath(), $response->getVariables());
            break;
        }

        if ($response instanceof Redirect) {
            header('Location: ' . $response->getLocation());
            exit;
        }


        break;
}

if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
}

if (isset($_SESSION['inputs'])) {
    unset($_SESSION['inputs']);
}

