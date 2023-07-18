<?php

namespace Hexlet\Code;

class Repeat
{
    static public function isRepet($pdo, $name)
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