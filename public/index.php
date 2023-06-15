<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Valitron\Validator;
use Carbon\Carbon;
use PostgreSQL\Connection;
use PostgreSQL\PostgreSQLCreateTable;

require_once __DIR__ . '/../vendor/autoload.php';

$pdo = Connection::get()->connect();
$tableCreator = new PostgreSQLCreateTable($pdo);

// Параметром передается базовая директория, в которой будут храниться шаблоны
$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();//роутер – объект отвечающий за хранение и обработку маршрутов
$dataTime = Carbon::now();
//---------------------------------------------------------------------------------------
session_start();

$app->get('/', function ($req, $res) use ($tableCreator) {
    if (!$tableCreator->tableExists('urls')) {
        $tableCreator->createTables();
    }

    $params = [
        'errors' => []
    ];
    return $this->get('renderer')->render($res, 'index.phtml', $params);
})->setName('startPage');

$app->post('/urls', function($req ,$res) use ($router, $tableCreator, $dataTime) {
    $urls = $req->getParsedBodyParam('urls');
    $validator = new Validator($urls);
    $validator->rules([
        'required' => ['name'],
        'lengthMax' =>[['name', 255]],
        'url' => ['name']
    ]);

    if ($validator->validate()){
        $parsedUrl = parse_url($urls['name']);
        $urlName = "{$parsedUrl["scheme"]}://{$parsedUrl["host"]}";

        if ($tableCreator->isRepet($urlName)) {
            $this->get('flash')->addMessage('success', 'Страница уже существует');
        } else {
            $this->get('flash')->addMessage('success', 'Страница созданна');
            $tableCreator->insertUrl($urlName, $dataTime);
        }
        $id = $tableCreator->getId($urlName);
        $url = $router->urlFor('urls', ['id'=> $id]);
        return $res->withRedirect($url);
    }
    $params = [
        'errors' => true
    ];

    return $this->get('renderer')->render($res->withStatus(422), 'index.phtml', $params);
});

$app->get('/urls', function ($req, $res) use ($tableCreator) {
    $urls = $tableCreator->selectUrls();
    $params = [
        'urls' => $urls
    ];
    return $this->get('renderer')->render($res, 'urls.phtml', $params);
});

$app->get('/urls/{id}', function ($req, $res, array $args) use ($tableCreator){
    $id = $args['id'];
    $url = $tableCreator->selectUrl($id);

    $messages = $this->get('flash')->getMessages();
    $params = [
        'url' => $url,
        'flash' => $messages
    ];
    return $this->get('renderer')->render($res, 'url.phtml', $params);
})->setName('urls');

$app->run();
