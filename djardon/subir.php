<?php
// ============================================================
//  SUBIR.PHP — Versión corregida con diagnóstico
// ============================================================
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

// ── Solo acepta POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: carrusel.php");
    exit();
}

$es_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function responder($ok, $msg, $es_ajax) {
    if ($es_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'msg' => $msg]);
        exit();
    }
    $_SESSION['flash']      = $msg;
    $_SESSION['flash_tipo'] = $ok ? 'ok' : 'error';
    header("Location: carrusel.php");
    exit();
}

if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
    responder(false, 'No se recibió ningún archivo.', $es_ajax);
}

$nombre_display = trim($_POST['nombre_img'] ?? '');
$archivo        = $_FILES['imagen'];

if ($nombre_display === '') {
    responder(false, 'Escribe un nombre para la imagen.', $es_ajax);
}

$errores_php = [
    UPLOAD_ERR_INI_SIZE   => 'El archivo supera upload_max_filesize en php.ini.',
    UPLOAD_ERR_FORM_SIZE  => 'El archivo supera MAX_FILE_SIZE del formulario.',
    UPLOAD_ERR_PARTIAL    => 'Archivo subido de forma incompleta.',
    UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal en el servidor.',
    UPLOAD_ERR_CANT_WRITE => 'Sin permisos de escritura en el servidor.',
    UPLOAD_ERR_EXTENSION  => 'Extension de PHP bloqueó la subida.',
];

if ($archivo['error'] !== UPLOAD_ERR_OK) {
    $msg = $errores_php[$archivo['error']] ?? 'Error codigo: ' . $archivo['error'];
    responder(false, $msg, $es_ajax);
}

$tipos_ok = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$mime     = mime_content_type($archivo['tmp_name']);
if (!in_array($mime, $tipos_ok)) {
    responder(false, "Tipo no permitido: {$mime}. Solo JPG, PNG, GIF o WEBP.", $es_ajax);
}

if ($archivo['size'] > 10 * 1024 * 1024) {
    responder(false, 'El archivo pesa mas de 10 MB.', $es_ajax);
}

$carpeta = __DIR__ . '/img_carrusel/';

if (!is_dir($carpeta)) {
    if (!mkdir($carpeta, 0775, true)) {
        responder(false, 'No existe img_carrusel/ y no se pudo crear. Ejecuta: sudo mkdir -p /var/www/html/djardon/img_carrusel && sudo chown www-data:www-data /var/www/html/djardon/img_carrusel', $es_ajax);
    }
}

if (!is_writable($carpeta)) {
    responder(false, 'La carpeta img_carrusel/ no tiene permisos de escritura. Ejecuta: sudo chmod 775 /var/www/html/djardon/img_carrusel && sudo chown www-data:djardon /var/www/html/djardon/img_carrusel', $es_ajax);
}

$ext       = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$nombre_fs = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$ruta_fs   = $carpeta . $nombre_fs;
$ruta_db   = 'img_carrusel/' . $nombre_fs;

if (!move_uploaded_file($archivo['tmp_name'], $ruta_fs)) {
    responder(false, 'move_uploaded_file() fallo. Revisa permisos de img_carrusel/', $es_ajax);
}

$uid  = (int)$_SESSION['usuario_id'];
$stmt = $mariadb->prepare("INSERT INTO imagenes (nombre, ruta, usuario_id) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $nombre_display, $ruta_db, $uid);

if (!$stmt->execute()) {
    @unlink($ruta_fs);
    responder(false, 'Error BD: ' . $stmt->error, $es_ajax);
}

$nuevo_id = $mariadb->insert_id;
$stmt->close();

log_accion($postgres, $uid, 'subir_imagen', "id={$nuevo_id} nombre={$nombre_display}");

responder(true, "Imagen '{$nombre_display}' subida correctamente.", $es_ajax);
