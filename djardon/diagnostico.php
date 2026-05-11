<?php
// ============================================================
//  DIAGNOSTICO.PHP — Revela exactamente qué está fallando
//  BORRA este archivo después de usarlo (contiene info sensible)
// ============================================================
session_start();

// Protección básica: solo accesible desde el mismo servidor o con clave
$clave = $_GET['clave'] ?? '';
if ($clave !== 'revisar2024') {
    die("Acceso denegado. Usa: diagnostico.php?clave=revisar2024");
}

require_once 'db.php';

echo "<style>
body { font-family: monospace; background:#111; color:#eee; padding:2rem; }
h2 { color:#e94560; }
.ok  { color:#6ee7c7; }
.err { color:#ff8fa3; }
.warn{ color:#ffd77a; }
pre  { background:#222; padding:1rem; border-radius:6px; overflow:auto; }
</style>";

echo "<h1>🔍 Diagnóstico del servidor</h1>";

// ── 1. Versión PHP ───────────────────────────────────────────
echo "<h2>1. PHP</h2>";
echo "<p class='ok'>Versión: " . PHP_VERSION . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>file_uploads: " . (ini_get('file_uploads') ? "<span class='ok'>ON</span>" : "<span class='err'>OFF ← PROBLEMA</span>") . "</p>";

// ── 2. Carpeta img_carrusel ──────────────────────────────────
echo "<h2>2. Carpeta img_carrusel/</h2>";
$carpeta = __DIR__ . '/img_carrusel/';
if (is_dir($carpeta)) {
    echo "<p class='ok'>✔ Existe: {$carpeta}</p>";
    echo "<p>" . (is_writable($carpeta) 
        ? "<span class='ok'>✔ Tiene permisos de escritura</span>" 
        : "<span class='err'>✘ SIN permisos de escritura ← EJECUTA: sudo chown www-data:djardon /var/www/html/djardon/img_carrusel && sudo chmod 775 /var/www/html/djardon/img_carrusel</span>") . "</p>";
    $archivos = glob($carpeta . '*');
    echo "<p>Archivos dentro: " . count($archivos) . "</p>";
    if ($archivos) {
        foreach (array_slice($archivos, 0, 5) as $f) {
            echo "<p style='font-size:0.85rem; color:#a0a0b0;'>  " . basename($f) . "</p>";
        }
    }
} else {
    echo "<p class='err'>✘ NO EXISTE ← EJECUTA: sudo mkdir -p /var/www/html/djardon/img_carrusel && sudo chown www-data:djardon /var/www/html/djardon/img_carrusel && sudo chmod 775 /var/www/html/djardon/img_carrusel</p>";
}

// ── 3. Conexión MariaDB ──────────────────────────────────────
echo "<h2>3. MariaDB</h2>";
if ($mariadb && !$mariadb->connect_errno) {
    echo "<p class='ok'>✔ Conexión exitosa</p>";
    
    // Ver tablas
    $res = $mariadb->query("SHOW TABLES");
    $tablas = [];
    while ($r = $res->fetch_array()) $tablas[] = $r[0];
    echo "<p>Tablas: " . implode(', ', $tablas) . "</p>";
    
    // Ver estructura de imagenes
    if (in_array('imagenes', $tablas)) {
        echo "<p class='ok'>✔ Tabla 'imagenes' existe</p>";
        $res2 = $mariadb->query("DESCRIBE imagenes");
        echo "<pre>";
        while ($col = $res2->fetch_assoc()) {
            echo $col['Field'] . " | " . $col['Type'] . " | " . $col['Null'] . "\n";
        }
        echo "</pre>";
        
        // Ver cuántas imágenes hay
        $cnt = $mariadb->query("SELECT COUNT(*) as c FROM imagenes")->fetch_assoc()['c'];
        echo "<p>Registros en imagenes: <strong>{$cnt}</strong></p>";
        
        if ($cnt > 0) {
            $rows = $mariadb->query("SELECT id, nombre, ruta FROM imagenes ORDER BY id DESC LIMIT 5");
            echo "<pre>";
            while ($r = $rows->fetch_assoc()) {
                $existe = file_exists(__DIR__ . '/' . $r['ruta']) ? '✔ archivo existe' : '✘ ARCHIVO NO EXISTE EN DISCO';
                echo "ID:{$r['id']} | {$r['nombre']} | {$r['ruta']} | {$existe}\n";
            }
            echo "</pre>";
        }
        
        // Verificar si tiene columna usuario_id
        $res3 = $mariadb->query("SHOW COLUMNS FROM imagenes LIKE 'usuario_id'");
        if ($res3->num_rows === 0) {
            echo "<p class='warn'>⚠ La columna usuario_id NO existe en la tabla imagenes.</p>";
            echo "<p class='warn'>Ejecuta en MySQL: ALTER TABLE imagenes ADD COLUMN usuario_id INT DEFAULT NULL;</p>";
        } else {
            echo "<p class='ok'>✔ Columna usuario_id existe</p>";
        }
    } else {
        echo "<p class='err'>✘ Tabla 'imagenes' NO existe ← Revisa la instalación de la BD</p>";
    }
    
    // Ver estructura de usuarios
    if (in_array('usuarios', $tablas)) {
        $cnt_u = $mariadb->query("SELECT COUNT(*) as c FROM usuarios")->fetch_assoc()['c'];
        echo "<p class='ok'>✔ Tabla 'usuarios' existe ({$cnt_u} usuarios)</p>";
    } else {
        echo "<p class='err'>✘ Tabla 'usuarios' NO existe</p>";
    }
} else {
    echo "<p class='err'>✘ Error de conexión MariaDB: " . htmlspecialchars($mariadb->connect_error ?? 'desconocido') . "</p>";
}

// ── 4. PostgreSQL ────────────────────────────────────────────
echo "<h2>4. PostgreSQL</h2>";
if ($postgres) {
    echo "<p class='ok'>✔ Conexión exitosa</p>";
    $res_pg = pg_query($postgres, "SELECT COUNT(*) as c FROM bitacora");
    if ($res_pg) {
        $row = pg_fetch_assoc($res_pg);
        echo "<p>Registros en bitacora: {$row['c']}</p>";
    }
} else {
    echo "<p class='warn'>⚠ PostgreSQL no disponible (la app sigue funcionando sin él)</p>";
}

// ── 5. Sesión actual ─────────────────────────────────────────
echo "<h2>5. Sesión</h2>";
if (isset($_SESSION['usuario_id'])) {
    echo "<p class='ok'>✔ Logueado como: " . htmlspecialchars($_SESSION['usuario_nombre']) . " (ID: {$_SESSION['usuario_id']})</p>";
} else {
    echo "<p class='warn'>⚠ No hay sesión activa (entra primero y luego carga este diagnóstico)</p>";
}

// ── 6. Test de subida simulado ───────────────────────────────
echo "<h2>6. Test de escritura en img_carrusel/</h2>";
$archivo_test = $carpeta . 'test_' . time() . '.txt';
if (is_dir($carpeta) && is_writable($carpeta)) {
    if (file_put_contents($archivo_test, 'test') !== false) {
        echo "<p class='ok'>✔ Escritura exitosa en img_carrusel/</p>";
        unlink($archivo_test);
    } else {
        echo "<p class='err'>✘ No se pudo escribir en img_carrusel/</p>";
    }
} else {
    echo "<p class='err'>✘ Carpeta no disponible para escribir</p>";
}

echo "<hr style='border-color:#333; margin-top:2rem;'>";
echo "<p style='color:#555; font-size:0.8rem;'>⚠ Borra este archivo del servidor cuando termines: rm /var/www/html/djardon/diagnostico.php</p>";
