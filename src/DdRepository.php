<?php

namespace Hexlet\Code;

class DdRepository
{
    public static function createTable($pdo): void
    {
        $filePath = realpath('../database.sql');
        $data = file_get_contents($filePath);

        $namesTables = $pdo->prepare("SELECT table_name
            FROM information_schema.tables
            WHERE table_schema='public'
            AND table_type='BASE TABLE';");
        $namesTables->execute();

        $isCreatingTables = empty($namesTables->fetchAll(\PDO::FETCH_COLUMN, 0));
        if (!empty($isCreatingTables)) {
            $pdo->exec($data);
        }
    }
}
