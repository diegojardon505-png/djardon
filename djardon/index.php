<?php
// ============================================================
//  INDEX.PHP — Página de entrada: Login + Registro
// ============================================================
session_start();

// Si ya está logueado, mandarlo directo al carrusel
if (isset($_SESSION['usuario_id'])) {
    header("Location: carrusel.php");
    exit();
}

require_once 'db.php';

$error_login   = '';
$error_reg     = '';
$ok_reg        = '';
$tab_activa    = 'login'; // Por defecto muestra el login

// ——————————————————————————————————————————
// Procesar LOGIN
// ——————————————————————————————————————————
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'login') {
    $tab_activa = 'login';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error_login = 'Por favor completa todos los campos.';
    } else {
        $stmt = $mariadb->prepare("SELECT id, username, password FROM usuarios WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = $res->fetch_assoc();
        $stmt->close();

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['username'];

            // Registrar acceso en PostgreSQL (bitácora)
            log_accion($postgres, $usuario['id'], 'login', 'Acceso correcto desde index.php');

            header("Location: carrusel.php");
            exit();
        } else {
            $error_login = 'Usuario o contraseña incorrectos.';
            // Log de intento fallido
            log_accion($postgres, 0, 'login_fallido', "Intento con usuario: {$username}");
        }
    }
}

// ——————————————————————————————————————————
// Procesar REGISTRO
// ——————————————————————————————————————————
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registro') {
    $tab_activa = 'registro';
    $nuevo_user = trim($_POST['nuevo_username'] ?? '');
    $nuevo_pass = $_POST['nuevo_password'] ?? '';
    $confirmar  = $_POST['confirmar_password'] ?? '';

    if ($nuevo_user === '' || $nuevo_pass === '' || $confirmar === '') {
        $error_reg = 'Por favor completa todos los campos.';
    } elseif (strlen($nuevo_user) < 3) {
        $error_reg = 'El usuario debe tener al menos 3 caracteres.';
    } elseif (strlen($nuevo_pass) < 6) {
        $error_reg = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($nuevo_pass !== $confirmar) {
        $error_reg = 'Las contraseñas no coinciden.';
    } else {
        // Verificar si el usuario ya existe
        $stmt = $mariadb->prepare("SELECT id FROM usuarios WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $nuevo_user);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_reg = 'Ese nombre de usuario ya está en uso.';
            $stmt->close();
        } else {
            $stmt->close();
            $hash = password_hash($nuevo_pass, PASSWORD_BCRYPT);
            $stmt2 = $mariadb->prepare("INSERT INTO usuarios (username, password) VALUES (?, ?)");
            $stmt2->bind_param("ss", $nuevo_user, $hash);

            if ($stmt2->execute()) {
                $nuevo_id = $mariadb->insert_id;
                $stmt2->close();

                // Log en PostgreSQL
                log_accion($postgres, $nuevo_id, 'registro', "Nuevo usuario: {$nuevo_user}");

                $ok_reg     = '¡Cuenta creada! Ya puedes iniciar sesión.';
                $tab_activa = 'login';
            } else {
                $error_reg = 'Error al crear la cuenta. Intenta de nuevo.';
                $stmt2->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrusel — Acceso</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="fondo-animado" style="display:flex; align-items:center; justify-content:center; min-height:100vh; padding:1rem;">

<div style="width:100%; max-width:420px;">

    <!-- Logo / título -->
    <div class="text-center mb-2" style="margin-bottom:1.5rem;">
        <div style="font-size:2.5rem; margin-bottom:0.25rem;">🖼️</div>
        <h1 class="titulo-principal">Carrusel</h1>
        <p class="subtitulo">Galería personal de imágenes</p>
    </div>

    <div class="card-glass">

        <!-- Pestañas -->
        <div class="tabs-nav">
            <button class="tab-btn <?= $tab_activa === 'login'   ? 'activo' : '' ?>" onclick="cambiarTab('login')">
                Iniciar sesión
            </button>
            <button class="tab-btn <?= $tab_activa === 'registro' ? 'activo' : '' ?>" onclick="cambiarTab('registro')">
                Crear cuenta
            </button>
        </div>

        <!-- ===== TAB: LOGIN ===== -->
        <div id="tab-login" class="tab-contenido <?= $tab_activa === 'login' ? 'activo' : '' ?>">

            <?php if ($ok_reg):  ?>
                <div class="alerta alerta-ok"><?= htmlspecialchars($ok_reg) ?></div>
            <?php endif; ?>
            <?php if ($error_login): ?>
                <div class="alerta alerta-error"><?= htmlspecialchars($error_login) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <input type="hidden" name="accion" value="login">

                <div class="campo-grupo">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" class="campo-input"
                           placeholder="tu_usuario" autocomplete="username" required>
                </div>

                <div class="campo-grupo">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="campo-input"
                           placeholder="••••••••" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn-primario">Entrar →</button>
            </form>

            <p class="text-gris text-center mt-2">
                ¿No tienes cuenta?
                <a href="#" onclick="cambiarTab('registro')" style="color:#e94560; text-decoration:none;">Regístrate aquí</a>
            </p>
        </div>

        <!-- ===== TAB: REGISTRO ===== -->
        <div id="tab-registro" class="tab-contenido <?= $tab_activa === 'registro' ? 'activo' : '' ?>">

            <?php if ($error_reg): ?>
                <div class="alerta alerta-error"><?= htmlspecialchars($error_reg) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <input type="hidden" name="accion" value="registro">

                <div class="campo-grupo">
                    <label for="nuevo_username">Usuario</label>
                    <input type="text" id="nuevo_username" name="nuevo_username" class="campo-input"
                           placeholder="Mínimo 3 caracteres" required>
                </div>

                <div class="campo-grupo">
                    <label for="nuevo_password">Contraseña</label>
                    <input type="password" id="nuevo_password" name="nuevo_password" class="campo-input"
                           placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="campo-grupo">
                    <label for="confirmar_password">Confirmar contraseña</label>
                    <input type="password" id="confirmar_password" name="confirmar_password" class="campo-input"
                           placeholder="Repite tu contraseña" required>
                </div>

                <button type="submit" class="btn-primario">Crear cuenta</button>
            </form>

            <p class="text-gris text-center mt-2">
                ¿Ya tienes cuenta?
                <a href="#" onclick="cambiarTab('login')" style="color:#e94560; text-decoration:none;">Inicia sesión</a>
            </p>
        </div>

    </div><!-- /card-glass -->

    <p class="text-center text-gris mt-2" style="font-size:0.75rem; opacity:0.5;">
        TESVG · Carrusel de imágenes v2
    </p>
</div>

<script>
function cambiarTab(cual) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
    document.querySelectorAll('.tab-contenido').forEach(c => c.classList.remove('activo'));
    document.getElementById('tab-' + cual).classList.add('activo');
    const btns = document.querySelectorAll('.tab-btn');
    btns[cual === 'login' ? 0 : 1].classList.add('activo');
}
</script>

</body>
</html>
