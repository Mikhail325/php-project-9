<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Hexlet\Code\Connection;
use Hexlet\Code\Urls\CheckedUrl;
use Hexlet\Code\Urls\Url;
use Hexlet\Code\Table;
use Valitron\Validator;
use GuzzleHttp\Exception\ClientException;

require_once __DIR__ . '/../vendor/autoload.php';
session_start();

$pdo = Connection::connect();
$url = new Url($pdo);
$CheckedUrl = new CheckedUrl($pdo);

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$table = new Table();
$table->createTables($pdo);

$app->get('/', function ($req, $res) {
    $params = [
        'errors' => []
    ];
    return $this->get('renderer')->render($res, 'index.phtml', $params);
})->setName('main');

$app->post('/urls', function ($req, $res) use ($router, $url, $pdo) {
    $urls = $req->getParsedBodyParam('url');

    $validator = new Validator($urls);
    $validator->rules([
        'required' => ['name'],
        'lengthMax' => [['name', 255]],
        'url' => ['name']
    ]);

    if ($validator->validate()) {
        $parsedUrl = parse_url($urls['name']);
        $urlName = "{$parsedUrl["scheme"]}://{$parsedUrl["host"]}";

        if (Hexlet\Code\Repeat::isRepet($pdo, $urlName)) {
            $this->get('flash')->addMessage('success', 'Страница уже существует');
        } else {
            $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
            $url->setUrl($urlName);
        }

        $id = Hexlet\Code\Id::getId($pdo, $urlName);
        $urlRout = $router->urlFor('url', ['id' => $id]);
        return $res->withRedirect($urlRout);
    }
    $params = [
        'errors' => $urls['name']
    ];

    return $this->get('renderer')->render($res->withStatus(422), 'index.phtml', $params);
});

$app->get('/urls', function ($req, $res) use ($url) {
    $urls = $url->getUrls();
    $params = [
        'urls' => $urls
    ];
    return $this->get('renderer')->render($res, 'urls/index.phtml', $params);
})->setName('urls');

$app->get('/urls/{id}', function ($req, $res, array $args) use ($url, $CheckedUrl) {
    $id = $args['id'];
    $url1 = $url->getUrl($id);
    $dataChecks = $CheckedUrl->getUrl($id);

    $messages = $this->get('flash')->getMessages();
    $params = [
        'url' => $url1,
        'flash' => $messages,
        'checks' => $dataChecks
    ];
    return $this->get('renderer')->render($res, 'urls/show.phtml', $params);
})->setName('url');

$app->post('/urls/{url_id}/checks', function ($req, $res, array $args) use ($url, $CheckedUrl, $router) {
    $id = $args['url_id'];
    $urlName = $url->getUrl($id)['name'];

    try {
        $CheckedUrl->setUrl($id, $urlName);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (ClientException $e) {
        $this->get('flash')->addMessage('error', 'Ошибка при проверке страницы');
    }
    $url = $router->urlFor('url', ['id' => $id]);
    return $res->withRedirect($url);
});

$app->run();
