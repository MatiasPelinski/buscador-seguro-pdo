<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function responder(int $status, array $payload)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require_once __DIR__ . '/../config/conexion.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        $busqueda = trim((string) ($_GET['q'] ?? ''));
        if (mb_strlen($busqueda) > 100) {
            responder(400, ['estado' => 'error', 'mensaje' => 'La búsqueda no puede superar los 100 caracteres.']);
        }

        // El dato del usuario se enlaza como parámetro: nunca se concatena al SQL.
        $statement = $pdo->prepare(
            'SELECT id, nombre, categoria, precio, stock
             FROM productos
             WHERE nombre LIKE :busqueda OR categoria LIKE :busqueda_categoria
             ORDER BY nombre ASC'
        );
        $patron = '%' . $busqueda . '%';
        $statement->bindValue(':busqueda', $patron, PDO::PARAM_STR);
        $statement->bindValue(':busqueda_categoria', $patron, PDO::PARAM_STR);
        $statement->execute();
        $productos = $statement->fetchAll();

        responder(200, ['estado' => 'exito', 'total' => count($productos), 'data' => $productos]);
    }

    if ($method === 'POST') {
        $entrada = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($entrada)) {
            responder(400, ['estado' => 'error', 'mensaje' => 'El cuerpo debe ser un JSON válido.']);
        }

        $nombre = trim((string) ($entrada['nombre'] ?? ''));
        $categoria = trim((string) ($entrada['categoria'] ?? ''));
        $precioTexto = (string) ($entrada['precio'] ?? '');
        $stockTexto = (string) ($entrada['stock'] ?? '0');

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            responder(422, ['estado' => 'error', 'mensaje' => 'El nombre es obligatorio y debe tener hasta 100 caracteres.']);
        }
        if ($categoria === '' || mb_strlen($categoria) > 50) {
            responder(422, ['estado' => 'error', 'mensaje' => 'La categoría es obligatoria y debe tener hasta 50 caracteres.']);
        }
        if (!is_numeric($precioTexto) || (float) $precioTexto < 0 || (float) $precioTexto > 99999999.99) {
            responder(422, ['estado' => 'error', 'mensaje' => 'El precio debe ser un número entre 0 y 99.999.999,99.']);
        }
        if (!preg_match('/^\d+$/', $stockTexto) || (int) $stockTexto > 2147483647) {
            responder(422, ['estado' => 'error', 'mensaje' => 'El stock debe ser un entero no negativo.']);
        }

        $statement = $pdo->prepare(
            'INSERT INTO productos (nombre, categoria, precio, stock)
             VALUES (:nombre, :categoria, :precio, :stock)'
        );
        $statement->execute([
            ':nombre' => $nombre,
            ':categoria' => $categoria,
            ':precio' => number_format((float) $precioTexto, 2, '.', ''),
            ':stock' => (int) $stockTexto,
        ]);

        responder(201, [
            'estado' => 'exito',
            'mensaje' => 'Producto agregado correctamente.',
            'id' => (int) $pdo->lastInsertId(),
        ]);
    }

    header('Allow: GET, POST');
    responder(405, ['estado' => 'error', 'mensaje' => 'Método no permitido.']);
} catch (PDOException $exception) {
    error_log('Error en productos.php: ' . $exception->getMessage());
    responder(500, ['estado' => 'error', 'mensaje' => 'Ocurrió un error al consultar la base de datos.']);
}
