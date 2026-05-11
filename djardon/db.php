<?php
// ============================================================
//  DB.PHP — Punto central de conexiones
//  Incluye MariaDB (obligatorio) y PostgreSQL (opcional)
// ============================================================

require_once __DIR__ . '/db_mariadb.php';   // $mariadb  → objeto mysqli
require_once __DIR__ . '/db_postgres.php';  // $postgres → recurso pg / null
                                             // log_accion() disponible globalmente
