<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Flash\Messages;
use DI\Container;
use Hexlet\Code\Connection;
use Hexlet\Code\Table;
use Hexlet\Code\DataUrl;
use Valitron\Validator;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;

require_once __DIR__ . '/../vendor/autoload.php';

session_status() === 1 ? session_start() : '';

$container = new Container();

$container->set('renderer', function () {
    return new PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$nameTableUrls = 'urls';
$colunmsTableUrls = [
    'id' => 'bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY',
    'name' => 'varchar(255)',
    'created_at' => 'timestamp'
];
$urls = new Table($nameTableUrls, $colunmsTableUrls);

$nameTableUrlChecks = 'url_checks';
$colunmsTableUrlChecks  = [
    'id' => 'bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY',
    'url_id' => 'bigint REFERENCES urls (id)',
    'status_code' => 'int',
    'h1' => 'varchar(255)',
    'title' => 'varchar(255)',
    'description' => 'varchar(255)',
    'created_at' => 'timestamp'
];
$urlChecks = new Table($nameTableUrlChecks, $colunmsTableUrlChecks);

$urls->linkTables($urlChecks, 'id', 'url_id');

$app->get('/', function ($req, $res) {
    $params = [
        'errors' => []
    ];
    /** @phpstan-ignore-next-line */
    return $this->get('renderer')->render($res, 'index.phtml', $params);
})->setName('main');

$app->post('/urls', function ($req, $res) use ($router, $urls) {
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

        if (!empty($urls->tableRepository->get(['name' => "'$url'"]))) {
            /** @phpstan-ignore-next-line */
            $this->get('flash')->addMessage('success', 'Страница уже существует');
        } else {
            /** @phpstan-ignore-next-line */
            $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
            $valuesColumns = [
                'name' => $url,
                'created_at' => Carbon::now()
            ];
            $urls->tableRepository->set($valuesColumns);
        }

        $id = $urls->tableRepository->get(['name' => "'$url'"])['id'];
        $urlRout = $router->urlFor('url', ['id' => $id]);
        return $res->withRedirect($urlRout);
    }

    $params = [
        'errors' => $url['name']
    ];
    /** @phpstan-ignore-next-line */
    return $this->get('renderer')->render($res->withStatus(422), 'index.phtml', $params);
});

$app->get('/urls', function ($req, $res) use ($urls) {

    $foreignTableColumns = ['name', 'id'];
    $relatedTableColumns = ['created_at', 'status_code'];
    $distinctColumn = 'url_checks.created_at';
    $sortColumn = 'id';
    $urls = $urls->tableRepository->getRelated(
        $foreignTableColumns,
        $relatedTableColumns,
        $distinctColumn,
        $sortColumn
    );

    $params = [
        'urls' => $urls
    ];
    /** @phpstan-ignore-next-line */
    return $this->get('renderer')->render($res, 'urls/index.phtml', $params);
})->setName('urls');

$app->get('/urls/{id}', function ($req, $res, array $args) use ($urls, $urlChecks) {
    $id = $args['id'];
    $url = $urls->tableRepository->get(['id' => "'$id'"]);
    $dataChecks = $urlChecks->tableRepository->get(['url_id' => "'$id'"], false, true);
    /** @phpstan-ignore-next-line */
    $messages = $this->get('flash')->getMessages();
    $params = [
        'url' => $url,
        'flash' => $messages,
        'checks' => $dataChecks
    ];
    /** @phpstan-ignore-next-line */
    return $this->get('renderer')->render($res, 'urls/show.phtml', $params);
})->setName('url');

$app->post('/urls/{url_id}/checks', function ($req, $res, array $args) use ($urls, $urlChecks, $router) {
    $id = $args['url_id'];
    $url = $urls->tableRepository->get(['id' => "'$id'"])['name'];

    try {
        $dataUrl = DataUrl::getData($url);
        $valuesColumns = [
            'description' => $dataUrl['description'],
            'title' => $dataUrl['title'],
            'h1' => $dataUrl['h1'],
            'url_id' => $id,
            'status_code' => $dataUrl['status_code'],
            'created_at' => Carbon::now()
        ];
        $urlChecks->tableRepository->set($valuesColumns);
        /** @phpstan-ignore-next-line */
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (ClientException $e) {
        /** @phpstan-ignore-next-line */
        $this->get('flash')->addMessage('error', 'Ошибка при проверке страницы');
    }
    $urlRout = $router->urlFor('url', ['id' => $id]);
    return $res->withRedirect($urlRout);
});

$app->run();
