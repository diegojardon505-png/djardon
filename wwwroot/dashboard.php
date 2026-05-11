<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="col-lg-5 mx-auto p-4 py-md-5">
      <header class="d-flex align-items-center pb-3 mb-5 border-bottom">
        <h2 class="text-success">Dashboard</h2>
      </header>
      <main id="main">
        <h3>Bienvenido a la biblioteca digital</h3>
        <a href="/ajax-jquery/logout.php">Salir</a>
      </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>