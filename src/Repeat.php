<?php

namespace Hexlet\Code;

class Repeat
{
    public static function isRepet(\PDO $pdo, string $name): bool
    {
        $sql = "SELECT * FROM urls WHERE name = :name;";
        $sqlReqvest = $pdo->prepare($sql);
        $sqlReqvest->execute(['name' => $name]);
        $url = $sqlReqvest->fetch(\PDO::FETCH_ASSOC);

        if (!empty($url)) {
            return true;
        }
        return false;
    }
}
