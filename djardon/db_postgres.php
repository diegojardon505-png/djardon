<?php
// ============================================================
//  DB_POSTGRES.PHP — Conexión a PostgreSQL
//  Base de datos secundaria (bitácora / log de accesos)
// ============================================================

$pg_host   = "127.0.0.1";
$pg_port   = "5432";
$pg_user   = "carrusel_pg";        // usuario que crearás con los comandos de instalación
$pg_pass   = "CarruselPG2024!";    // cambia si usas otra contraseña
$pg_db     = "carrusel_log";

$pg_conn_str = "host={$pg_host} port={$pg_port} dbname={$pg_db} user={$pg_user} password={$pg_pass}";

$postgres = @pg_connect($pg_conn_str);

if (!$postgres) {
    // No matamos la aplicación si Postgres falla; solo marcamos como no disponible
    $postgres = null;
}

// ----------------------------------------------------------------
//  Función auxiliar: registrar una acción en el log de PostgreSQL
//  Uso: log_accion($postgres, 1, 'ver_imagen', 'foto.jpg')
// ----------------------------------------------------------------
function log_accion($pg, $usuario_id, $accion, $detalle = '') {
    if (!$pg) return; // Si Postgres no está, no hacer nada
    $usuario_id = (int)$usuario_id;
    $accion  = pg_escape_string($pg, $accion);
    $detalle = pg_escape_string($pg, $detalle);
    pg_query($pg, "INSERT INTO bitacora (usuario_id, accion, detalle, fecha)
                   VALUES ({$usuario_id}, '{$accion}', '{$detalle}', NOW())");
}
