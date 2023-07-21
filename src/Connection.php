<?php

namespace Hexlet\Code;

final class Connection
{
    public static function connect()
    {
        
        if (isset($_ENV['DATABASE_URL'])) {
            $databaseUrl = parse_url($_ENV['DATABASE_URL']);
            $username = $databaseUrl['user'];
            $password = $databaseUrl['pass'];
            $host = $databaseUrl['host'];
            $port = $databaseUrl['port'];
            $dbname = ltrim($databaseUrl['path'], '/');
            $conStr = "pgsql:host=$host;port=$port;dbname=$dbname";
        } else {
            $params = parse_ini_file('database.ini');
                $conStr = sprintf(
                    "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                    $params['host'],
                    $params['port'],
                    $params['database'],
                    $params['user'],
                    $params['password']
                );
                $username = $params['user'];
                $password = $params['password'];
        }
        $pdo = new \PDO($conStr, $username, $password);
        return $pdo;
    }
}
