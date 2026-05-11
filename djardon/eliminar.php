<?php
// ============================================================
//  ELIMINAR.PHP — Borra una imagen de MariaDB y del disco
// ============================================================
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "msg" => "No autorizado"]);
    exit();
}

require_once 'db.php';

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID inválido"]);
    exit();
}

// Obtener ruta del archivo
$stmt = $mariadb->prepare("SELECT ruta, nombre FROM imagenes WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$img = $res->fetch_assoc();
$stmt->close();

if (!$img) {
    echo json_encode(["ok" => false, "msg" => "Imagen no encontrada"]);
    exit();
}

// Eliminar de la BD
$stmt2 = $mariadb->prepare("DELETE FROM imagenes WHERE id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$stmt2->close();

// Eliminar archivo físico
$archivo = __DIR__ . '/' . $img['ruta'];
if (file_exists($archivo)) {
    unlink($archivo);
}

// Log en PostgreSQL
log_accion($postgres, $_SESSION['usuario_id'], 'eliminar_imagen',
    "id={$id} nombre=" . $img['nombre']);

echo json_encode(["ok" => true, "msg" => "Imagen eliminada"]);
