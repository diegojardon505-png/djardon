<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';
require_once 'db.php';

$email = $_POST['email'];
$pwd   = $_POST['pwd'];
$db    = conectarDB();

try {
    $sql    = "SELECT id_usuario, password, email FROM usuarios WHERE email = :email";
    $query  = $db->prepare($sql);
    $query->execute(['email' => $email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if (password_verify($pwd, $usuario['password'])) {
            session_start();
            $_SESSION['username']   = $usuario['email'];
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            echo "Sesión iniciada correctamente. <a href='/ajax-jquery/dashboard.php'>Continuar</a>";
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "No se encontró el usuario.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>





