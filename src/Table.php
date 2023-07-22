<?php

namespace Hexlet\Code;

class Table
{
    public function createTables(\PDO $pdo): void
    {
        if (!$this->tableExists($pdo, 'urls')) {
            $sql = 'CREATE TABLE urls (
                id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
                name varchar(255),
                created_at timestamp
                );';
            $pdo->exec($sql);
        }

        if (!$this->tableExists($pdo, 'url_checks')) {
            $sql = 'CREATE TABLE url_checks (
            id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            url_id bigint REFERENCES urls (id),
            status_code int,
            h1 varchar(255),
            title varchar(255),
            description varchar(255),
            created_at timestamp
            );';
            $pdo->exec($sql);
        }
        return;
    }

    public function tableExists(\PDO $pdo, string $tableName): bool
    {
        try {
            $result = $pdo->query("SELECT 1 FROM {$tableName} LIMIT 1");
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
