<?php
declare(strict_types=1);

/**
 * Configuración PDO. En XAMPP se puede usar la configuración por defecto o
 * definir DB_HOST, DB_NAME, DB_USER y DB_PASS en el entorno de PHP.
 */
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'clase_backend';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $exception) {
    error_log('Error de conexión PDO: ' . $exception->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        ['estado' => 'error', 'mensaje' => 'No se pudo conectar con la base de datos.'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}
