<?php
// ============================================================
//  GET_IMAGEN.PHP — Devuelve UNA imagen a la vez
//
//  Uso:  get_imagen.php?pos=0          → primera imagen
//        get_imagen.php?pos=3          → cuarta imagen
//        get_imagen.php?total=1        → solo devuelve cuántas hay
//
//  Esto cumple lo que pidió tu amigo:
//  "cada vez que cambies de foto sea una petición a la BD"
// ============================================================
session_start();

// Solo usuarios logueados pueden pedir imágenes
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

// ── Modo "solo total" ──────────────────────────────────────
if (isset($_GET['total'])) {
    $res = $mariadb->query("SELECT COUNT(*) AS cnt FROM imagenes");
    $row = $res->fetch_assoc();
    echo json_encode(["total" => (int)$row['cnt']]);
    exit();
}

// ── Modo "dame la imagen en la posición X" ────────────────
$pos = max(0, (int)($_GET['pos'] ?? 0));

// LIMIT 1 OFFSET $pos → trae solo UNA fila
$stmt = $mariadb->prepare(
    "SELECT id, nombre, ruta FROM imagenes ORDER BY id ASC LIMIT 1 OFFSET ?"
);
$stmt->bind_param("i", $pos);
$stmt->execute();
$res  = $stmt->get_result();
$foto = $res->fetch_assoc();
$stmt->close();

if (!$foto) {
    echo json_encode(["error" => "No hay imagen en esa posición"]);
    exit();
}

// Registrar en PostgreSQL que se vio esta imagen
log_accion(
    $postgres,
    $_SESSION['usuario_id'],
    'ver_imagen',
    "pos={$pos} id={$foto['id']} nombre=" . $foto['nombre']
);

echo json_encode([
    "id"     => $foto['id'],
    "nombre" => $foto['nombre'],
    "ruta"   => $foto['ruta'],
    "pos"    => $pos
]);
