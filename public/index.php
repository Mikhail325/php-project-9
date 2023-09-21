<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Flash\Messages;
use DI\Container;
use Hexlet\Code\Connection;
use Hexlet\Code\Migration;
use Valitron\Validator;
use Carbon\Carbon;
use DiDom\Document;
use GuzzleHttp\Client;

require_once __DIR__ . '/../vendor/autoload.php';

session_status() === 1 ? session_start() : '';

$container = new Container();

$container->set('renderer', function () {
    return new PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new Messages();
});

$container->set('db', function () {
    return Connection::connect();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

Migration::migrate($container->get('db'));

$app->get('/', function ($req, $res) {
    $params = [
        'errors' => []
    ];
    return $this->get('renderer')->render($res, 'index.phtml', $params);
})->setName('main');

$app->post('/urls', function ($req, $res) use ($router) {
    $url = $req->getParsedBodyParam('url');

    $validator = new Validator($url);
    $validator->rules([
        'required' => ['name'],
        'lengthMax' => [['name', 255]],
        'url' => ['name']
    ]);

    if ($validator->validate()) {
        /** @var array<string> $parsedUrl */
        $parsedUrl = parse_url($url['name']);
        $url = "{$parsedUrl['scheme']}://{$parsedUrl['host']}";

        $db = $this->get('db');
        $statement = $db->query("SELECT id FROM urls WHERE name = '$url';");
        $isRepeated = empty($statement->fetch());

        if (!$isRepeated) {
            $this->get('flash')->addMessage('success', 'Страница уже существует');
        } else {
            $sql = 'INSERT INTO urls (name, created_at) VALUES (:name, :created_at)';
            $sqlRequest = $db->prepare($sql);
            $sqlRequest->execute([
                'name' => $url,
                'created_at' => Carbon::now()
            ]);

            $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        }

        $id = $db->query("SELECT id FROM urls WHERE name = '$url';");
        $id = $id->fetch(\PDO::FETCH_COLUMN, 0);
        $urlRout = $router->urlFor('url', ['id' => $id]);
        return $res->withRedirect($urlRout);
    }

    $params = [
        'errors' => $url['name']
    ];
    return $this->get('renderer')->render($res->withStatus(422), 'index.phtml', $params);
});

$app->get('/urls', function ($req, $res) {
    $db = $this->get('db');
    $statement = $db->query(
        "SELECT urls.name, urls.id, MAX(url_checks.created_at) AS created_at, url_checks.status_code 
        FROM urls LEFT JOIN url_checks ON urls.id = url_checks.url_id
        GROUP BY (urls.name, urls.id, url_checks.status_code)
        ORDER BY id DESC;"
    );

    $params = [
        'urls' => $statement->fetchAll()
    ];
    return $this->get('renderer')->render($res, 'urls/index.phtml', $params);
})->setName('urls');

$app->get('/urls/{id}', function ($req, $res, array $args) {
    $id = $args['id'];
    $db = $this->get('db');

    $statement = $db->query("SELECT * FROM urls WHERE id = $id;");
    $url = $statement->fetch();

    $statement = $db->query("SELECT * FROM url_checks WHERE url_id = $id ORDER BY id DESC;");
    $urlChecks = $statement->fetchAll();

    $messages = $this->get('flash')->getMessages();
    $params = [
        'url' => $url,
        'flash' => $messages,
        'checks' => $urlChecks
    ];

    return $this->get('renderer')->render($res, 'urls/show.phtml', $params);
})->setName('url');

$app->post('/urls/{url_id}/checks', function ($req, $res, array $args) use ($router) {
    $id = $args['url_id'];
    $db = $this->get('db');

    $statement = $db->query("SELECT name FROM urls WHERE id = $id;");
    $urlName = $statement->fetch(\PDO::FETCH_COLUMN, 0);

    $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at) 
            VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)";
    $sqlRequest = $db->prepare($sql);

    try {
        $client = new Client();
        $response = $client->request('GET', $urlName);
        $statusCode = $response->getStatusCode();

        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (Exception $e) {
        $this->get('flash')->addMessage('error', 'Ошибка при проверке страницы');

        $urlRoute = $router->urlFor('url', ['id' => $id]);
        return $res->withRedirect($urlRoute);
    }
    $contents = $response->getBody()->getContents();

    /** @var Document $document */
    $document = new Document($contents);
    $h1 = $document->has('h1') ? optional($document->find('h1')[0])->text() : null;
    $title = $document->has('title') ? optional($document->find('title')[0])->text() : null;
    $description = $document->has('meta[name=description]') ?
        optional($document->find('meta[name=description]')[0])
        ->attr('content') : null;

    $sqlRequest->execute([
        'description' => $description,
        'title' => $title,
        'h1' => $h1,
        'url_id' => $id,
        'status_code' => $statusCode,
        'created_at' => Carbon::now()
    ]);

    $urlRoute = $router->urlFor('url', ['id' => $id]);
    return $res->withRedirect($urlRoute);
});

$app->run();
