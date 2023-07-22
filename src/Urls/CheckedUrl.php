<?php

namespace Hexlet\Code\Urls;

use Carbon\Carbon;
use Hexlet\Code\DataUrl;

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
        $dataUrl = DataUrl::getData($urlName);

        $sqlReqvest->execute([
            'description' => $dataUrl['description'],
            'title' => $dataUrl['title'],
            'h1' => $dataUrl['h1'],
            'url_id' => $urlId,
            'status_code' => $dataUrl['status_code'],
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
