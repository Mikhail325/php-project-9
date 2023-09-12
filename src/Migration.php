<?php

namespace Hexlet\Code;

class Migration
{
    public static function migrate(\PDO $pdo): void
    {
        $data = file_get_contents('../database.sql');
        $pdo->exec($data);
    }
}
