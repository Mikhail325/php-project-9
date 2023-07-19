<?php

namespace Hexlet\Code;

class Id
{
    public static function getId($pdo, $name)
    {
        $sql = 'SELECT * FROM urls WHERE name = :name;';
        $sqlReqvest = $pdo->prepare($sql);
        $sqlReqvest->execute(['name' => $name]);
        return $sqlReqvest->fetch(\PDO::FETCH_ASSOC)['id'];
    }
}
