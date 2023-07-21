<?php

namespace Hexlet\Code\Urls;

use Carbon\Carbon;
use DiDom\Document;

class CheckedUrl
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function setUrl($urlId, $resUrl)
    {
        $dataTime = Carbon::now();

        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at) 
            VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)";
        $sqlReqvest = $this->pdo->prepare($sql);

        $statusCode = $resUrl->getStatusCode();
        $body = $resUrl->getBody()->getContents();
        $document = new Document($body);

        $h1 = $document->has('h1') ? $document->find('h1')[0]->text() : null;
        $title = $document->has('title') ? $document->find('title')[0]->text() : null;
        $description = $document->has('meta[name=description]') ? $document->find('meta[name=description]')[0]
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

    public function getUrl($id)
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = {$id}
            ORDER BY url_checks.id DESC;";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
