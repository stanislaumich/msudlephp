<?php

namespace Core;

class Database
{
    private static ?\PDO $pdo = null;

    public static function getConnection(): \PDO
    {
        if (self::$pdo === null) {
            $config = $GLOBALS['config'] ?? require APP_PATH . '/config/config.php';
            $db = $config['db'];

            $dsn = "{$db['driver']}:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
            self::$pdo = new \PDO($dsn, $db['user'], $db['pass'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function findOne(string $sql, array $params = []): ?array
    {
        return self::fetch($sql, $params);
    }

    public static function insert(string $table, array $data): int
    {
        $keys = array_keys($data);
        $quotedKeys = array_map(fn($k) => "`" . trim($k, "`") . "`", $keys);
        $placeholders = array_fill(0, count($keys), '?');
        $sql = "INSERT INTO `{$table}` (" . implode(', ', $quotedKeys) . ") VALUES (" . implode(', ', $placeholders) . ")";
        self::query($sql, array_values($data));
        return (int)self::getConnection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "`" . trim($key, "`") . "` = ?";
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE {$where}";
        self::query($sql, array_merge(array_values($data), $whereParams));
        return self::query("SELECT ROW_COUNT()", [])->fetchColumn();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        self::query($sql, $params);
        return self::query("SELECT ROW_COUNT()", [])->fetchColumn();
    }

    public static function count(string $table, string $where = '1=1', array $params = []): int
    {
        return (int)self::query("SELECT COUNT(*) FROM {$table} WHERE {$where}", $params)->fetchColumn();
    }
}
