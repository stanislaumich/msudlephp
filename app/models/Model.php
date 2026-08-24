<?php

namespace Models;

use Core\Database;

class Model
{
    protected static string $table = '';
    protected array $fillable = [];
    protected ?int $id = null;

    public static function all(string $order = 'id DESC'): array
    {
        return Database::fetchAll("SELECT * FROM " . static::$table . " ORDER BY {$order}");
    }

    public static function find(int $id): ?array
    {
        $row = Database::fetch("SELECT * FROM " . static::$table . " WHERE id = ?", [$id]);
        return $row ?: null;
    }

    public static function where(string $where, array $params = [], string $order = 'id DESC'): array
    {
        return Database::fetchAll("SELECT * FROM " . static::$table . " WHERE {$where} ORDER BY {$order}", $params);
    }

    public static function findOne(string $where, array $params = []): ?array
    {
        $row = Database::fetch("SELECT * FROM " . static::$table . " WHERE {$where} LIMIT 1", $params);
        return $row ?: null;
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        return Database::count(static::$table, $where, $params);
    }

    public static function create(array $data): int
    {
        return Database::insert(static::$table, $data);
    }

    public static function updateWhere(string $where, array $data, array $whereParams): int
    {
        return Database::update(static::$table, $data, $where, $whereParams);
    }

    public static function deleteWhere(string $where, array $params): int
    {
        return Database::delete(static::$table, $where, $params);
    }

    public static function findOrFail(int $id): array
    {
        $row = static::find($id);
        if (!$row) {
            http_response_code(404);
            echo "404 — Запись не найдена";
            exit;
        }
        return $row;
    }
}
