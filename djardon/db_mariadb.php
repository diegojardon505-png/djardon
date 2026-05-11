<?php
// ============================================================
//  DB_MARIADB.PHP — Conexión a MariaDB
//  Base de datos principal (usuarios + imágenes)
// ============================================================

$mariadb_host = "127.0.0.1";
$mariadb_user = "carrusel_user";   // usuario que crearás con los comandos de instalación
$mariadb_pass = "Carrusel2024!";   // cambia esto si usas otra contraseña
$mariadb_db   = "carrusel_db";
$mariadb_port = 3306;

$mariadb = new mysqli($mariadb_host, $mariadb_user, $mariadb_pass, $mariadb_db, $mariadb_port);

if ($mariadb->connect_errno) {
    // Solo muestra el error en modo desarrollo; en producción puedes quitar el detalle
    die(json_encode([
        "error"   => true,
        "mensaje" => "Error MariaDB: " . $mariadb->connect_error
    ]));
}

$mariadb->set_charset("utf8mb4");
