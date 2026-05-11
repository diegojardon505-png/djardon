<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';
session_start();
require_once 'db.php';

$nombre = $_POST['nombre'];
$email  = $_POST['email'];
$pwd    = $_POST['pwd'];
$db     = conectarDB();

try {
    $sql   = "INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)";
    $query = $db->prepare($sql);
    $passwordHash = password_hash($pwd, PASSWORD_DEFAULT);

    $resultado = $query->execute([
        'nombre'   => $nombre,
        'email'    => $email,
        'password' => $passwordHash
    ]);

    if ($resultado) {
        echo "El usuario se ha almacenado correctamente! <a href='index.html'>Continuar</a>";
    }
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        echo "El email ya existe. <a href='index.html'>Continuar</a>";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>