<?php

namespace Hexlet\Code\Urls;

use Carbon\Carbon;

class Url
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function setUrl(string $name): void
    {
        $dataTime = Carbon::now();
        $sql = 'INSERT INTO urls (name, created_at) VALUES (:name, :created_at)';
        $sqlReqvest = $this->pdo->prepare($sql);
        $sqlReqvest->execute([
            'name' => $name,
            'created_at' => $dataTime
        ]);
    }

    public function getUrl(int $id): mixed
    {
        $sql = "SELECT * FROM urls WHERE id = {$id};";
        return optional($this->pdo->query($sql))->fetch();
    }

    public function getUrls(): mixed
    {
        $sql = "SELECT urls.name, urls.id, MAX(url_checks.created_at) AS created_at, url_checks.status_code 
        FROM urls LEFT JOIN url_checks ON urls.id = url_checks.url_id
        GROUP BY (urls.name, urls.id, url_checks.status_code);";
        return optional($this->pdo->query($sql))->fetchAll();
    }
}
