<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['usuario_id'])) {
    log_accion($postgres, $_SESSION['usuario_id'], 'logout', '');
}

$_SESSION = [];
session_destroy();
header("Location: index.php");
exit();
