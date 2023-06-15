<?php

namespace PostgreSQL;

/**
 * Создание в PostgreSQL таблицы из демонстрации PHP
 */
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
        $sql = "SELECT * FROM urls ORDER BY created_at DESC";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}