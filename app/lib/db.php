<?php
/**
 * Accès base de données (SQLite via PDO).
 * La base est créée et pré-remplie automatiquement au premier lancement.
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $path = $GLOBALS['config']['db_path'];
    $isNew = !is_file($path);
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');

    require_once APP_DIR . '/install/schema.php';
    install_schema($pdo);
    if ($isNew) {
        require_once APP_DIR . '/install/seed.php';
        seed_database($pdo);
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row ?: null;
}

function val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

/** Les noms de table/colonnes proviennent toujours du code (jamais de l'utilisateur). */
function db_insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(',', $cols),
        implode(',', array_map(fn($c) => ':' . $c, $cols))
    );
    q($sql, $data);
    return (int) db()->lastInsertId();
}

function db_update(string $table, array $data, int $id): void
{
    if (!$data) {
        return;
    }
    $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $data['__id'] = $id;
    q("UPDATE $table SET $set WHERE id = :__id", $data);
}

function db_delete(string $table, int $id): void
{
    q("DELETE FROM $table WHERE id = ?", [$id]);
}
