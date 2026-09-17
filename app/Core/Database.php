<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;
use Throwable;

/**
 * Thin PDO wrapper for MariaDB/MySQL 10.6+.
 *
 * SECURITY CONTRACT: $whereSql arguments are developer-authored constant
 * fragments only; never built from user input. Values are bound placeholders.
 * Table/column names are validated identifiers, never user input. No schema
 * is executed by this class; apply schema only via docs/db-runbook.md.
 */
final class Database
{
    private static ?PDO $pdo = null;

    /** Pure: builds a mysql DSN from a config-style array. */
    public static function buildDsn(array $db): string
    {
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            self::requireString($db, 'host'),
            self::requireInt($db, 'port'),
            self::requireString($db, 'database'),
        );
    }

    public static function connect(array $db): PDO
    {
        return new PDO(
            self::buildDsn($db),
            self::requireString($db, 'username'),
            self::requireString($db, 'password'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
    }

    /** Lazy singleton from Config('db.*'); opens a connection on first use only. */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $db = Config::get('db');
            if (!is_array($db)) {
                throw new RuntimeException('Database configuration is missing.');
            }
            self::$pdo = self::connect($db);
        }

        return self::$pdo;
    }

    /** @return array<int, array<string, mixed>> */
    public static function select(string $sql, array $params = []): array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);

        /** @var array<int, array<string, mixed>> $rows */
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|array{} */
    public static function selectOne(string $sql, array $params = []): array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return is_array($row) ? $row : [];
    }

    /** Prepared statement execution; returns affected row count. */
    public static function execute(string $sql, array $params = []): int
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->rowCount();
    }

    /** Inserts column => value pairs; returns the new auto-increment id. */
    public static function insert(string $table, array $data): int
    {
        $columns = [];
        foreach (array_keys($data) as $column) {
            $columns[] = '`' . self::assertIdentifier((string) $column) . '`';
        }

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            self::assertIdentifier($table),
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?')),
        );

        $statement = self::pdo()->prepare($sql);
        $statement->execute(array_values($data));

        return (int) self::pdo()->lastInsertId();
    }

    /** Developer-constant $whereSql; values bound. Returns affected rows. */
    public static function update(string $table, array $data, string $whereSql, array $params = []): int
    {
        if ($data === [] || trim($whereSql) === '') {
            throw new RuntimeException('Update requires data and an explicit WHERE fragment.');
        }

        $assignments = [];
        foreach (array_keys($data) as $column) {
            $assignments[] = '`' . self::assertIdentifier((string) $column) . '` = ?';
        }

        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', self::assertIdentifier($table), implode(', ', $assignments), $whereSql);
        $statement = self::pdo()->prepare($sql);
        $statement->execute([...array_values($data), ...$params]);

        return $statement->rowCount();
    }

    /** Developer-constant $whereSql; values bound. Returns affected rows. */
    public static function delete(string $table, string $whereSql, array $params = []): int
    {
        if (trim($whereSql) === '') {
            throw new RuntimeException('Delete requires an explicit WHERE fragment.');
        }

        $statement = self::pdo()->prepare(
            sprintf('DELETE FROM `%s` WHERE %s', self::assertIdentifier($table), $whereSql),
        );
        $statement->execute($params);

        return $statement->rowCount();
    }

    /** Runs fn(PDO): mixed in a transaction; rolls back on Throwable. No nesting. */
    public static function transaction(callable $fn): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $fn($pdo);
            $pdo->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }




    private static function requireString(array $db, string $key): string
    {
        $value = $db[$key] ?? null;
        if (!is_string($value) || $value === '') {
            throw new RuntimeException('Database config value missing: ' . $key);
        }

        return $value;
    }

    private static function requireInt(array $db, string $key): int
    {
        $value = $db[$key] ?? null;
        if (!is_int($value) || $value < 1 || $value > 65535) {
            throw new RuntimeException('Database config value invalid: ' . $key);
        }

        return $value;
    }

    private static function assertIdentifier(string $identifier): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $identifier) !== 1) {
            throw new RuntimeException('Invalid SQL identifier.');
        }

        return $identifier;
    }
}
