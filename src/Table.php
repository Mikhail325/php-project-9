<?php

namespace Hexlet\Code;

use Hexlet\Code\TableRepository;

class Table
{
    public string $nameTable;
     /**
     * @var array<string> $columnsTable
     */
    public array $columnsTable;
     /**
     * @var array<string> $typeColumnsTable
     */
    public array $typeColumnsTable;
    public Table $relatedTable;
    public string $foreignPivotKey;
    public string $relatedPivotKey;
    public TableRepository $tableRepository;
    public \PDO $pdo;

    /**
     * @param array<string> $columnsTable
     */
    public function __construct(string $nameTable, array $columnsTable)
    {
        $this->nameTable = $nameTable;
        $this->columnsTable = array_keys($columnsTable);
        $this->typeColumnsTable = array_values($columnsTable);
        $this->tableRepository = new TableRepository($this);
    }

    public function linkTables(Table $table, string $foreignPivotKey, string $relatedPivotKey): void
    {
        $this->relatedTable = $table;
        $this->foreignPivotKey = "{$this->nameTable}.$foreignPivotKey";
        $this->relatedPivotKey = "{$table->nameTable}.$relatedPivotKey";
    }
}
