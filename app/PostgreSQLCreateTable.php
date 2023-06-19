<?php

namespace PostgreSQL;

use DiDom\Document;

class PostgreSQLCreateTable
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createTables()
    {
        $sql = 'CREATE TABLE urls (
            id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            name varchar(255),
            created_at timestamp
            );
            CREATE TABLE url_checks (
            id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            url_id bigint REFERENCES urls (id),
            status_code int,
            h1 varchar(255),
            title varchar(255),
            description varchar(255),
            created_at timestamp
            );';

        $this->pdo->exec($sql);
        return $this;
    }
    function tableExists($table) {

        try {
            $result = $this->pdo->query("SELECT 1 FROM {$table} LIMIT 1");
        } catch (\Exception $e) {

            return false;
        }
    
        return $result !== false;
    }

    public function isRepet($name) {
        $sql = 'SELECT * FROM urls WHERE name = :name;';
        $sqlReqvest = $this->pdo->prepare($sql);
        $sqlReqvest->bindValue(':name', $name);
        $sqlReqvest->execute();
        $array = $sqlReqvest->fetch(\PDO::FETCH_ASSOC);

        if (!empty($array)) {
            return true;
        }
        return false;
    } 

    public function getId($name) {
        $sql = 'SELECT * FROM urls WHERE name = :name;';
        $sqlReqvest = $this->pdo->prepare($sql);
        $sqlReqvest->bindValue(':name', $name);
        $sqlReqvest->execute();
        $array = $sqlReqvest->fetch(\PDO::FETCH_ASSOC);
        return $array['id'];
    }

    public function insertUrl($name, $date) {
        $sql = 'INSERT INTO urls (name, created_at) VALUES (:name, :created_at)';
        $sqlReqvest = $this->pdo->prepare($sql);
        $sqlReqvest->bindValue(':name', $name);
        $sqlReqvest->bindValue(':created_at', $date);
        $sqlReqvest->execute();
    }

    public function selectUrl($id) {
        $sql = "SELECT * FROM urls WHERE id = {$id};";
        return $this->pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
    }

    public function selectUrls() {
        $sql = "SELECT urls.name, urls.id, MAX(url_checks.created_at) AS created_at, url_checks.status_code 
        FROM urls LEFT JOIN url_checks ON urls.id = url_checks.url_id
        GROUP BY (urls.name, urls.id, url_checks.status_code);";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function insertChecUrl($url_id, $resUrl, $created_at) {
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at) 
            VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)";
        $sqlReqvest = $this->pdo->prepare($sql);

        $code = $resUrl->getStatusCode();
        $body = $resUrl->getBody()->getContents();;

        $document = new Document($body);
        $h1 = $document->has('h1') ? $document->find('h1')[0]->text() : null;
        $title = $document->has('title') ? $document->find('title')[0]->text() : null;
        $description = $document->has('meta[name=description]') ? $document->find('meta[name=description]')[0]->attr('content') : null;

        $sqlReqvest->bindValue(':description', $description);
        $sqlReqvest->bindValue(':title', $title);
        $sqlReqvest->bindValue(':h1', $h1);
        $sqlReqvest->bindValue(':url_id', $url_id);
        $sqlReqvest->bindValue(':status_code', $code);
        $sqlReqvest->bindValue(':created_at', $created_at);
        $sqlReqvest->execute();
    }

    public function selectChecUrl($id) {
        $sql = "SELECT * FROM url_checks WHERE url_id = {$id}
            ORDER BY url_checks.id DESC;";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

}