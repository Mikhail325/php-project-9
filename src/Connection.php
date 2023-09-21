<?php

namespace Hexlet\Code;

use Illuminate\Support\Arr;

final class Connection
{
    public static function connect(): \PDO
    {
        $url = (string) getenv('DATABASE_URL');
        $conn = parse_url($url);
        $dbName = ltrim(Arr::get($conn, 'path', 'project-48'), '/');
        $host = Arr::get($conn, 'host', 'localhost');
        $userName = Arr::get($conn, 'user', 'postgres');
        $password = Arr::get($conn, 'pass', '3155810a');
        $conStr = "pgsql:host=$host;dbname=$dbName";
        $pdo = new \PDO($conStr, $userName, $password);
        return $pdo;
    }
}
