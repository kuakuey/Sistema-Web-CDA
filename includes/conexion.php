<?php

require_once __DIR__ . '/../config/app.php';

$configBd = __DIR__ . '/../config/database.php';

if (!is_file($configBd)) {
    throw new RuntimeException(
        'Falta config/database.php. Copia config/database.example.php y completa las credenciales.'
    );
}

require_once $configBd;

function getServerConnection(): PDO
{
    $dsn = sprintf('mysql:host=%s;charset=%s', DB_HOST, DB_CHARSET);

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("SET time_zone = '-05:00'");
    }

    return $pdo;
}

function tablaExiste(PDO $pdo, string $tabla): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = ? AND table_name = ?'
    );
    $stmt->execute([DB_NAME, $tabla]);

    return (int) $stmt->fetchColumn() > 0;
}
