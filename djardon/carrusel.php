<?php
// ============================================================
//  CARRUSEL.PHP — Galería principal (requiere sesión)
// ============================================================
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

$usuario = htmlspecialchars($_SESSION['usuario_nombre']);

// Flash messages (de subir.php)
$flash      = $_SESSION['flash']      ?? '';
$flash_tipo = $_SESSION['flash_tipo'] ?? 'ok';
unset($_SESSION['flash'], $_SESSION['flash_tipo']);

// Contar cuántas imágenes hay (para saber si hay algo que mostrar)
$res_total = $mariadb->query("SELECT COUNT(*) AS cnt FROM imagenes");
$total_imgs = (int)$res_total->fetch_assoc()['cnt'];

// Listar imágenes para la tabla de gestión
$lista = $mariadb->query("SELECT id, nombre, ruta FROM imagenes ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrusel — Galería</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: var(--primario); min-height:100vh;">

<!-- ======== NAVBAR ======== -->
<nav class="navbar-carrusel">
    <span class="navbar-marca">🖼️ Carrusel</span>
    <div class="navbar-acciones">
        <span class="navbar-usuario">👤 <?= $usuario ?></span>
        <a href="logout.php" class="btn-peligro">Salir</a>
    </div>
</nav>

<!-- ======== CONTENIDO PRINCIPAL ======== -->
<div class="contenedor" style="padding-top:2rem; padding-bottom:3rem;">

    <!-- Flash message -->
    <?php if ($flash): ?>
        <div class="alerta alerta-<?= $flash_tipo === 'ok' ? 'ok' : 'error' ?> mb-2" style="max-width:900px; margin:0 auto 1rem;">
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>

    <?php if ($total_imgs === 0): ?>
    <!-- Estado vacío -->
    <div class="text-center" style="padding:4rem 1rem; max-width:500px; margin:0 auto;">
        <div style="font-size:4rem; margin-bottom:1rem;">📂</div>
        <h2 style="margin-bottom:0.5rem;">Sin imágenes aún</h2>
        <p class="text-gris">Sube tu primera imagen usando el formulario de abajo.</p>
    </div>
    <?php else: ?>

    <!-- ======== VISOR URL (inspector) ======== -->
    <div class="url-badge">
        <span style="color: var(--texto-gris);">src actual:</span>
        <span id="url-display">cargando...</span>
    </div>

    <!-- ======== CARRUSEL ======== -->
    <div class="carrusel-wrapper">
        <!-- Spinner mientras carga -->
        <div class="cargando-carrusel" id="cargando-overlay">
            <div class="spinner"></div>
            <span>Cargando imagen...</span>
        </div>
        <!-- La imagen se inyecta aquí. Cada cambio genera un nuevo nodo con nueva URL -->
        <img id="visor-imagen" src="" alt="" style="opacity:0;">
    </div>

    <!-- ======== CONTROLES ======== -->
    <div class="controles-carrusel">
        <button class="btn-flecha" id="btn-anterior">← Anterior</button>

        <div class="info-imagen">
            <div class="nombre-imagen" id="nombre-imagen">—</div>
            <div class="contador-imagen" id="contador-imagen">0 / <?= $total_imgs ?></div>
        </div>

        <button class="btn-flecha" id="btn-siguiente">Siguiente →</button>
    </div>

    <?php endif; ?>

    <!-- ======== PANEL SUBIDA ======== -->
    <details class="panel-subida mt-3" <?= $total_imgs === 0 ? 'open' : '' ?>>
        <summary>📤 Subir nueva imagen</summary>

        <div id="msg-subida" style="margin-bottom:0.75rem; display:none;"></div>

        <form id="form-subida" action="subir.php" method="POST" enctype="multipart/form-data">
            <!-- MAX_FILE_SIZE debe ir ANTES del campo file y coincidir con upload_max_filesize del php.ini -->
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
            <div class="subida-grid">
                <div class="campo-grupo" style="margin-bottom:0;">
                    <label for="nombre_img">Nombre que se mostrará</label>
                    <input type="text" id="nombre_img" name="nombre_img" class="campo-input"
                           placeholder="Ej: Atardecer en el lago" required>
                </div>
                <div class="campo-grupo" style="margin-bottom:0;">
                    <label for="imagen">Archivo (JPG, PNG, GIF, WEBP · máx 2 MB)</label>
                    <input type="file" id="imagen" name="imagen" class="campo-input"
                           accept="image/jpeg,image/png,image/gif,image/webp" required>
                    <small id="aviso-peso" style="color:var(--texto-gris); font-size:0.75rem; display:none;"></small>
                </div>
            </div>
            <div style="margin-top:1rem;">
                <button type="submit" class="btn-primario" style="max-width:220px;">
                    Subir imagen
                </button>
            </div>
        </form>
    </details>

    <!-- ======== TABLA GESTIÓN ======== -->
    <?php if ($total_imgs > 0): ?>
    <div class="seccion-gestion mt-4">
        <p class="seccion-titulo">Imágenes almacenadas <span class="badge-bd badge-mariadb">MariaDB</span></p>
        <div style="overflow-x:auto; background:var(--fondo-card); border:1px solid var(--borde-card); border-radius:10px;">
            <table class="tabla-imagenes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Vista previa</th>
                        <th>Ruta</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $lista->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><img src="<?= htmlspecialchars($row['ruta']) ?>" class="miniatura" alt=""></td>
                        <td style="font-size:0.75rem; color:var(--texto-gris); font-family:monospace;">
                            <?= htmlspecialchars($row['ruta']) ?>
                        </td>
                        <td>
                            <button class="btn-peligro" style="font-size:0.78rem; padding:0.35rem 0.75rem;"
                                    onclick="eliminarImagen(<?= $row['id'] ?>)">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Indicador de PostgreSQL -->
    <div style="max-width:900px; margin:1rem auto 0; text-align:right; font-size:0.72rem; color: var(--texto-gris);">
        Bitácora de accesos: <span class="badge-bd badge-postgres">PostgreSQL</span>
    </div>

</div><!-- /contenedor -->


<!-- ======================================================
     JAVASCRIPT DEL CARRUSEL
     Cada navegación hace UNA petición a get_imagen.php
     La imagen cambia en el DOM → nueva URL visible en inspector
     ====================================================== -->
<script>
(function () {
    const TOTAL       = <?= $total_imgs ?>;
    let   posActual   = 0;
    let   enTransicion = false;

    const visor      = document.getElementById('visor-imagen');
    const overlay    = document.getElementById('cargando-overlay');
    const urlDisplay = document.getElementById('url-display');
    const nombreEl   = document.getElementById('nombre-imagen');
    const contEl     = document.getElementById('contador-imagen');
    const btnAnterior = document.getElementById('btn-anterior');
    const btnSiguiente = document.getElementById('btn-siguiente');

    if (TOTAL === 0) return; // No hay imágenes, no hacer nada

    // ── Función principal: pide UNA imagen a la BD ──────────
    function cargarImagen(pos) {
        if (enTransicion) return;
        enTransicion = true;

        // Fade out
        visor.style.opacity = '0';
        overlay.style.display = 'flex';

        // Petición individual a la BD  ← esto es lo que pidió tu amigo
        fetch('get_imagen.php?pos=' + pos)
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    mostrarError(data.error);
                    return;
                }

                // ──────────────────────────────────────────────────────
                // AQUÍ ocurre lo del inspector:
                // Cambiamos el src del <img> → nueva URL en el DOM
                // En DevTools > Elements verás la URL cambiar en tiempo real
                // ──────────────────────────────────────────────────────
                visor.src = data.ruta + '?t=' + Date.now(); // timestamp evita caché
                visor.alt = data.nombre;

                // Mostrar la URL en el badge de arriba
                if (urlDisplay) urlDisplay.textContent = visor.src;

                // Actualizar info
                if (nombreEl) nombreEl.textContent = data.nombre;
                if (contEl)   contEl.textContent   = (pos + 1) + ' / ' + TOTAL;

                posActual = pos;
            })
            .catch(() => mostrarError('Error de conexión con el servidor.'))
            .finally(() => {
                // La imagen se muestra cuando termina de cargar
                visor.onload = function() {
                    overlay.style.display = 'none';
                    visor.style.opacity = '1';
                    enTransicion = false;
                };
                // Si la imagen ya estaba en caché (onload no dispara)
                if (visor.complete && visor.naturalWidth) {
                    overlay.style.display = 'none';
                    visor.style.opacity = '1';
                    enTransicion = false;
                }
            });
    }

    function mostrarError(msg) {
        overlay.innerHTML = '<div style="color:#ff8fa3; padding:1rem;">' + msg + '</div>';
        enTransicion = false;
    }

    // ── Botones ─────────────────────────────────────────────
    if (btnAnterior) {
        btnAnterior.addEventListener('click', function () {
            const nueva = (posActual - 1 + TOTAL) % TOTAL;
            cargarImagen(nueva);
        });
    }

    if (btnSiguiente) {
        btnSiguiente.addEventListener('click', function () {
            const nueva = (posActual + 1) % TOTAL;
            cargarImagen(nueva);
        });
    }

    // ── Teclado (flechas) ────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') btnSiguiente && btnSiguiente.click();
        if (e.key === 'ArrowLeft')  btnAnterior  && btnAnterior.click();
    });

    // ── Carga inicial ────────────────────────────────────────
    cargarImagen(0);

    // ── Validación de peso al seleccionar archivo ────────────
    const inputArchivo = document.getElementById('imagen');
    const avisoP      = document.getElementById('aviso-peso');
    const LIMITE_MB   = 2;

    if (inputArchivo) {
        inputArchivo.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const mb = file.size / 1024 / 1024;
            if (mb > LIMITE_MB) {
                avisoP.textContent = '⚠ Este archivo pesa ' + mb.toFixed(1) + ' MB. El límite actual del servidor es ' + LIMITE_MB + ' MB. Debes comprimir la imagen.';
                avisoP.style.display = 'block';
                avisoP.style.color = '#ff8fa3';
                this.value = '';
            } else {
                avisoP.textContent = '✔ ' + mb.toFixed(2) + ' MB — OK';
                avisoP.style.display = 'block';
                avisoP.style.color = '#6ee7c7';
            }
        });
    }

    // ── Formulario de subida (AJAX) ──────────────────────────
    const formSubida = document.getElementById('form-subida');
    const msgSubida  = document.getElementById('msg-subida');

    if (formSubida) {
        formSubida.addEventListener('submit', function (e) {
            e.preventDefault();

            const archivoSel = document.getElementById('imagen').files[0];
            if (!archivoSel) {
                msgSubida.style.display = 'block';
                msgSubida.className = 'alerta alerta-error';
                msgSubida.textContent = 'Selecciona un archivo de imagen.';
                return;
            }
            if (archivoSel.size / 1024 / 1024 > LIMITE_MB) {
                msgSubida.style.display = 'block';
                msgSubida.className = 'alerta alerta-error';
                msgSubida.textContent = 'El archivo pesa más de ' + LIMITE_MB + ' MB. El servidor no lo puede recibir con la configuración actual.';
                return;
            }

            const datos = new FormData(formSubida);
            const btnSubmit = formSubida.querySelector('button[type="submit"]');
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Subiendo...';

            fetch('subir.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: datos
            })
            .then(r => {
                const ct = r.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    throw new Error('Respuesta inesperada del servidor. Revisa los errores de PHP en el log.');
                }
                return r.json();
            })
            .then(res => {
                msgSubida.style.display = 'block';
                msgSubida.className = 'alerta alerta-' + (res.ok ? 'ok' : 'error');
                msgSubida.textContent = res.msg;
                if (res.ok) {
                    formSubida.reset();
                    if (avisoP) avisoP.style.display = 'none';
                    setTimeout(() => location.reload(), 1200);
                }
            })
            .catch((err) => {
                msgSubida.style.display = 'block';
                msgSubida.className = 'alerta alerta-error';
                msgSubida.textContent = 'Error: ' + err.message;
            })
            .finally(() => {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Subir imagen';
            });
        });
    }

    // ── Eliminar imagen ──────────────────────────────────────
    window.eliminarImagen = function(id) {
        if (!confirm('¿Eliminar esta imagen del carrusel?')) return;
        fetch('eliminar.php?id=' + id, { method: 'GET' })
            .then(r => r.json())
            .then(res => {
                if (res.ok) location.reload();
                else alert('Error: ' + res.msg);
            })
            .catch(() => alert('Error de red al eliminar.'));
    };

})();
</script>

</body>
</html>
