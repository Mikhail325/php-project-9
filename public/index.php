<?php

use Slim\Factory\AppFactory;
use DI\Container;
use PostgreSQL\Connection;
use PostgreSQL\UrlsPDO;
use Valitron\Validator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

require_once __DIR__ . '/../vendor/autoload.php';

$pdo = Connection::get()->connect();
$urlsPDO = new UrlsPDO($pdo);

$dataTime = Carbon::now();

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

session_start();

$app->get('/', function ($req, $res) use ($urlsPDO) {
    if (!$urlsPDO->tableExists('urls')) {
        $urlsPDO->createTables();
    }

    $params = [
        'errors' => []
    ];
    return $this->get('renderer')->render($res, 'index.phtml', $params);
})->setName('startPage');

$app->post('/urls', function ($req, $res) use ($router, $urlsPDO, $dataTime) {
    $urls = $req->getParsedBodyParam('urls');
    $validator = new Validator($urls);
    $validator->rules([
        'required' => ['name'],
        'lengthMax' => [['name', 255]],
        'url' => ['name']
    ]);

    if ($validator->validate()) {
        $parsedUrl = parse_url($urls['name']);
        $urlName = "{$parsedUrl["scheme"]}://{$parsedUrl["host"]}";

        if ($urlsPDO->isRepet($urlName)) {
            $this->get('flash')->addMessage('success', 'Страница уже существует');
        } else {
            $this->get('flash')->addMessage('success', 'Страница созданна');
            $urlsPDO->insertUrl($urlName, $dataTime);
        }
        $id = $urlsPDO->getId($urlName);
        $url = $router->urlFor('url', ['id' => $id]);
        return $res->withRedirect($url, 302);
    }
    $params = [
        'errors' => true
    ];

    return $this->get('renderer')->render($res->withStatus(422), 'index.phtml', $params);
});

$app->get('/urls', function ($req, $res) use ($urlsPDO) {
    $urls = $urlsPDO->selectUrls();
    $params = [
        'urls' => $urls
    ];
    return $this->get('renderer')->render($res, 'urls/index.phtml', $params);
})->setName('urls');

$app->get('/urls/{id}', function ($req, $res, array $args) use ($urlsPDO) {
    $id = $args['id'];
    $url = $urlsPDO->selectUrl($id);
    $dataChecks = $urlsPDO->selectChecUrl($id);

    $messages = $this->get('flash')->getMessages();
    $params = [
        'url' => $url,
        'flash' => $messages,
        'checks' => $dataChecks
    ];
    return $this->get('renderer')->render($res, 'urls/show.phtml', $params);
})->setName('url');

$app->post('/urls/{url_id}/checks', function ($req, $res, array $args) use ($urlsPDO, $dataTime, $router) {
    $id = $args['url_id'];
    $client = new Client();

    $urlName = $urlsPDO->selectUrl($id)['name'];

    try {
        $respons = $client->request('GET', $urlName);
        $urlsPDO->insertChecUrl($id, $respons, $dataTime);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (ClientException $e) {
        $this->get('flash')->addMessage('error', 'Ошибка при проверке страницы');
    }
    $url = $router->urlFor('url', ['id' => $id]);
    return $res->withRedirect($url, 302);
});

$app->run();
