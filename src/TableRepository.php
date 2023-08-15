<?php

namespace Hexlet\Code;

use Hexlet\Code\Table;

class TableRepository
{
    public Table $table;
    private \PDO $pdo;

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->pdo = Connection::connect();

        if (!$this->isTableExists()) {
            $this->createTeble();
        }
    }

    /** @param array<string> $valuesColumns */
    public function set(array $valuesColumns): void
    {
        $columns = implode(', ', array_keys($valuesColumns));
        $valuesColumns = array_map(fn($value) => "'$value'", array_values($valuesColumns));
        $valuesColumns = implode(", ", array_values($valuesColumns));

        $sql = "INSERT INTO {$this->table->nameTable} ($columns)
            VALUES ($valuesColumns);";
        $this->pdo->exec($sql);
    }

    /** @param array<string> $valuesColumns*/
    public function get(array $valuesColumns = null, bool $sortId = false, bool $allValue = false): mixed
    {
        if (isset($valuesColumns)) {
            $conditionColumns = array_map(
                fn($column, $value) => "$column = $value",
                array_keys($valuesColumns),
                $valuesColumns
            );
            $conditionColumns = 'WHERE ' . implode('AND ', $conditionColumns);
        } else {
            $conditionColumns = '';
        }

        $sortDb = $sortId ? "ORDER BY {$this->table->nameTable}.id DESC" : '';

        $sql = "SELECT * FROM {$this->table->nameTable} 
            $conditionColumns
            $sortDb;";

        $urlData = $this->pdo->prepare($sql);
        $urlData->execute();
        return $urlData = ($allValue) ? $urlData->fetchAll() : $urlData->fetch();

    }

    /**
     * @param array<string> $foreignColumns
     * @param array<string> $relatedColumns
     * @return array<mixed>
     */
    public function getRelated(
        array $foreignColumns,
        array $relatedColumns,
        string $distinctColumn = null,
        string $sortColumn = null
    ): array {
        $foreignColumn = array_map(fn($value) => "{$this->table->nameTable}.$value", $foreignColumns);
        $foreignColumn = implode(', ', $foreignColumn);
        $relatedColumn = array_map(
            fn($value) => "{$this->table->relatedTable->nameTable}.$value",
            $relatedColumns
        );
        $relatedColumn = implode(', ', $relatedColumn);

        $distinctColumn = isset($distinctColumn) ? "ORDER BY $distinctColumn DESC" : '';

        $columnsFromTable = "FROM {$this->table->nameTable} LEFT JOIN {$this->table->relatedTable->nameTable}
            ON {$this->table->foreignPivotKey} = {$this->table->relatedPivotKey}";

        $sql = "
            SELECT $foreignColumn, $relatedColumn
            $columnsFromTable
            $distinctColumn
            ";

        $sql = isset($sortColumn) ? "SELECT DISTINCT ON ($sortColumn) * 
            FROM ($sql) as Tabl ORDER BY $sortColumn DESC;" : $sql . ';';

        $urldData = $this->pdo->prepare($sql);
        $urldData->execute();
        return $urldData->fetchAll();
    }

    private function createTeble(): void
    {
        $columns = array_map(
            fn($column, $type) => "$column $type",
            $this->table->columnsTable,
            $this->table->typeColumnsTable
        );
        $columns = implode(', ', $columns);

        $sql = "CREATE TABLE {$this->table->nameTable} ({$columns});";
        $this->pdo->exec($sql);
    }

    private function isTableExists(): bool
    {
        try {
            $this->pdo->query("SELECT 1 FROM {$this->table->nameTable} LIMIT 1");
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
