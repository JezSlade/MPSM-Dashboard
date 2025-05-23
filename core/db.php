<?php
// core/db.php
// v1.0.0 — Database helper functions

require_once __DIR__ . '/config.php';

/**
 * Prepare and execute a SQL query.
 *
 * @param string $sql
 * @param array  $params
 * @return PDOStatement
 */
function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = get_db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all rows as associative arrays.
 *
 * @param string $sql
 * @param array  $params
 * @return array
 */
function db_fetch_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch a single row.
 *
 * @param string $sql
 * @param array  $params
 * @return array|null
 */
function db_fetch_one(string $sql, array $params = []): ?array
{
    $stmt = db_query($sql, $params);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row === false ? null : $row;
}

/**
 * Fetch a single value (first column of first row).
 *
 * @param string $sql
 * @param array  $params
 * @return mixed
 */
function db_fetch_value(string $sql, array $params = [])
{
    $row = db_fetch_one($sql, $params);
    return $row !== null ? reset($row) : null;
}
