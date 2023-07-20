<?php

namespace Hexlet\Code\tests;

use PHPUnit\Framework\TestCase;
use Hexlet\Code\Connection;
use Hexlet\Code\Urls\CheckedUrl;
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

    public function tearDown(): void
    {
        $this->pdo->rollBack();
    }
}