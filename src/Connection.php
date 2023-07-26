<?php

namespace Hexlet\Code;

final class Connection
{
    public static function connect(): \PDO
    {
        if (isset($_ENV['DATABASE_URL'])) {
            /** @var  array{user: string, pass: string, host: string, port: string, path: string} $databaseUrl */
            $databaseUrl = parse_url($_ENV['DATABASE_URL']);
        } else {
            /** @var  array{user: string, pass: string, host: string, port: string, path: string} $databaseUrl */
            $databaseUrl = parse_ini_file('database.ini');
        }
        print_r($databaseUrl);
        $username = $databaseUrl['user'];//
        $password = $databaseUrl['pass'];
        $host = $databaseUrl['host'];
        $dbname = ltrim($databaseUrl['path'], '/');//

        $conStr = "pgsql:host=$host;dbname=$dbname";
        $pdo = new \PDO($conStr, $username, $password);
        return $pdo;
    }
}
