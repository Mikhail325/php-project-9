<?php

namespace Hexlet\Code\Urls;

use Carbon\Carbon;

class Url
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function setUrl($name)
    {
        $dataTime = Carbon::now();
        $sql = 'INSERT INTO urls (name, created_at) VALUES (:name, :created_at)';
        $sqlReqvest = $this->pdo->prepare($sql);
        $sqlReqvest->execute([
            'name' => $name,
            'created_at' => $dataTime
        ]);
    }

    public function getUrl($id)
    {
        $sql = "SELECT * FROM urls WHERE id = {$id};";
        return $this->pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
    }

    public function getUrls()
    {
        $sql = "SELECT urls.name, urls.id, MAX(url_checks.created_at) AS created_at, url_checks.status_code 
        FROM urls LEFT JOIN url_checks ON urls.id = url_checks.url_id
        GROUP BY (urls.name, urls.id, url_checks.status_code);";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
