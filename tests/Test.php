<?php

namespace Hexlet\Code\tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Connection;
use Hexlet\Code\Urls\Url;
use Hexlet\Code\Table;

class GenDiffTest extends TestCase
{
    private $pdo;

    public function setUp(): void
    {
        $this->pdo = Connection::get()->connect();
        $this->pdo->beginTransaction();
    }

    public function testTable(): void
    {
        $table = new Table();
        $table->createTables($this->pdo);
        $this->assertTrue($table->tableExists($this->pdo, 'urls'));
        $this->assertTrue($table->tableExists($this->pdo, 'url_checks'));
        $this->assertFalse($table->tableExists($this->pdo, 'url_check'));
    }

    public function testUrl(): void
    {
        $url = new Url($this->pdo);
        $urlName1 = ('https://github.com');
        $url->setUrl($urlName1);
        $id = \Hexlet\Code\Id::getId($this->pdo, $urlName1);
        $urlTest = $url->getUrl($id);
        $this->assertEquals($urlName1, $urlTest['name']);
    }

    public function testRepeat(): void
    {
        $url = new Url($this->pdo);
        $urlName = ('https://github.com');
        $repet1 = \Hexlet\Code\Repeat::isRepet($this->pdo, $urlName);
        $url->setUrl($urlName);
        $repet2 = \Hexlet\Code\Repeat::isRepet($this->pdo, $urlName);
        $this->assertTrue($repet2);
        $this->assertFalse($repet1);
    }


    public function tearDown(): void
    {
        $this->pdo->rollBack();
    }
}