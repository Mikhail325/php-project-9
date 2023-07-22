<?php

namespace Hexlet\Code\Urls;

use Carbon\Carbon;
use DiDom\Document;
use GuzzleHttp\Client;

class CheckedUrl
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function setUrl(int $urlId, string $urlName): void
    {
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at) 
            VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)";
        $sqlReqvest = $this->pdo->prepare($sql);

        $dataTime = Carbon::now();

        $client = new Client();
        $respons = $client->request('GET', $urlName);
        $statusCode = $respons->getStatusCode();
        $body = $respons->getBody()->getContents();

        /** @var Document $document */
        $document = new Document($body);
        $h1 = $document->has('h1') ? optional($document->find('h1')[0])->text() : null;
        $title = $document->has('title') ? optional($document->find('title')[0])->text() : null;
        $description = $document->has('meta[name=description]') ?
            optional($document->find('meta[name=description]')[0])
            ->attr('content') : null;

        $sqlReqvest->execute([
            'description' => $description,
            'title' => $title,
            'h1' => $h1,
            'url_id' => $urlId,
            'status_code' => $statusCode,
            'created_at' => $dataTime,
        ]);
    }

    public function getUrl(int $id): mixed
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = {$id}
            ORDER BY url_checks.id DESC;";
        return optional($this->pdo->query($sql))->fetchAll();
    }
}
