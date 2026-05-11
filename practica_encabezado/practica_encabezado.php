<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal GM - Panel de Control</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0f2a 0%, #0d1b3e 50%, #0a2a4a 100%);
            min-height: 100vh;
        }

        /* ========== ENCABEZADO ========== */
        .header {
            background: linear-gradient(135deg, #0b2b5c 0%, #0a1a3a 100%);
            padding: 20px 0;
            border-bottom: 3px solid #2a6bb0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            background: #2a6bb0;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .logo-text h1 {
            color: white;
            font-size: 24px;
            letter-spacing: 2px;
        }

        .logo-text p {
            color: #6eb3ff;
            font-size: 12px;
        }

        /* ========== MENÚ DESPLEGABLE ========== */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-btn {
            background: linear-gradient(135deg, #1a5bb0, #0e3a6b);
            color: white;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .dropdown-btn:hover {
            background: linear-gradient(135deg, #2a6bb0, #1a4a7a);
            transform: scale(1.02);
        }

        .dropdown-btn i {
            font-size: 18px;
        }

        /* Contenido del desplegable */
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 10px;
            background: linear-gradient(135deg, #ffffff 0%, #f0f5ff 100%);
            min-width: 280px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            overflow: hidden;
            border: 1px solid rgba(42, 107, 176, 0.3);
        }

        .dropdown-content.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-header {
            background: linear-gradient(135deg, #1a5bb0, #0e3a6b);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
        }

        .dropdown-search {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .dropdown-search input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .dropdown-search input:focus {
            outline: none;
            border-color: #1a5bb0;
        }

        .projects-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .project-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            border-bottom: 1px solid #edf2f7;
            transition: background 0.2s;
            cursor: pointer;
        }

        .project-item:hover {
            background: #e8f0ff;
        }

        .project-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .project-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #1a5bb0, #0e3a6b);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .project-name {
            font-weight: 600;
            color: #1a2a4a;
        }

        .project-status {
            font-size: 11px;
            color: #2a6bb0;
        }

        .project-link {
            background: #1a5bb0;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.2s;
        }

        .project-link:hover {
            background: #0e3a6b;
        }

        /* ========== CONTENIDO PRINCIPAL ========== */
        .main-content {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .welcome-card h2 {
            color: white;
            font-size: 32px;
            margin-bottom: 15px;
        }

        .welcome-card p {
            color: #bbdef5;
            font-size: 18px;
        }

        /* Grid de proyectos */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .project-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .card-header-blue {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            padding: 20px;
        }

        .card-header-blue h3 {
            color: white;
            font-size: 1.3em;
        }

        .card-body {
            padding: 20px;
        }

        .card-body p {
            color: #555;
            margin-bottom: 15px;
        }

        .btn-ejecutar {
            background: linear-gradient(135deg, #1a5bb0, #0e3a6b);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-ejecutar:hover {
            background: linear-gradient(135deg, #2a6bb0, #1a4a7a);
        }

        .footer {
            text-align: center;
            padding: 30px;
            color: #6eb3ff;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<!-- ========== ENCABEZADO ========== -->
<div class="header">
    <div class="header-container">
        <div class="logo-area">
            <div class="logo-icon">
                🎮
            </div>
            <div class="logo-text">
                <h1>PORTAL GM</h1>
                <p>Panel de Control de Proyectos</p>
            </div>
        </div>

        <!-- ========== MENÚ DESPLEGABLE ========== -->
        <div class="dropdown">
            <button class="dropdown-btn" onclick="toggleDropdown()">
                📋 <span>LISTA DE PROYECTOS</span> ▼
            </button>
            <div class="dropdown-content" id="dropdownContent">
                <div class="dropdown-header">
                    📁 Todos tus proyectos (<?php echo count($proyectos); ?>)
                </div>
                <div class="dropdown-search">
                    <input type="text" id="searchInput" placeholder="🔍 Buscar proyecto..." onkeyup="filterProjects()">
                </div>
                <div class="projects-list" id="projectsList">
                    <?php foreach ($proyectos as $index => $proyecto): ?>
                    <div class="project-item" data-name="<?php echo strtolower($proyecto['nombre']); ?>">
                        <div class="project-info">
                            <div class="project-icon">
                                <?php echo $proyecto['icono']; ?>
                            </div>
                            <div>
                                <div class="project-name"><?php echo htmlspecialchars($proyecto['nombre']); ?></div>
                                <div class="project-status"><?php echo $proyecto['estado']; ?></div>
                            </div>
                        </div>
                        <a href="<?php echo $proyecto['ruta']; ?>" class="project-link" target="_blank">Ejecutar →</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== CONTENIDO PRINCIPAL ========== -->
<div class="main-content">
    <div class="welcome-card">
        <h2>🚀 Bienvenido a tu Portal GM</h2>
        <p>Selecciona un proyecto del menú desplegable o desde las tarjetas a continuación</p>
    </div>

    <div class="projects-grid">
        <?php foreach ($proyectos as $proyecto): ?>
        <div class="project-card">
            <div class="card-header-blue">
                <h3>📌 <?php echo htmlspecialchars($proyecto['nombre']); ?></h3>
            </div>
            <div class="card-body">
                <p><?php echo htmlspecialchars($proyecto['descripcion']); ?></p>
                <a href="<?php echo $proyecto['ruta']; ?>" class="btn-ejecutar" target="_blank">
                    🚀 Ejecutar App
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="footer">
    <p>© 2025 Portal GM - Todos los proyectos están sincronizados con GitHub</p>
</div>

<script>
    // Función para toggle del dropdown
    function toggleDropdown() {
        const dropdown = document.getElementById('dropdownContent');
        dropdown.classList.toggle('show');
    }

    // Cerrar dropdown al hacer clic fuera
    window.onclick = function(event) {
        if (!event.target.matches('.dropdown-btn') && !event.target.closest('.dropdown-btn')) {
            const dropdowns = document.getElementsByClassName("dropdown-content");
            for (let i = 0; i < dropdowns.length; i++) {
                if (dropdowns[i].classList.contains('show')) {
                    dropdowns[i].classList.remove('show');
                }
            }
        }
    }

    // Filtrar proyectos en el desplegable
    function filterProjects() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const projectsList = document.getElementById('projectsList');
        const projects = projectsList.getElementsByClassName('project-item');
        
        for (let i = 0; i < projects.length; i++) {
            const projectName = projects[i].getAttribute('data-name');
            if (projectName.includes(filter)) {
                projects[i].style.display = '';
            } else {
                projects[i].style.display = 'none';
            }
        }
    }
</script>

</body>
</html>

<?php
// ========== LÓGICA PHP PARA DETECTAR PROYECTOS ==========
// Esta parte debe ir ANTES del HTML o al inicio del archivo

function obtenerProyectosConIconos($directorio_base) {
    $proyectos = [];
    
    $directorios = scandir($directorio_base);
    
    // Descripciones e iconos para cada proyecto
    $info_proyectos = [
        'djardon' => [
            'nombre' => 'Djardon',
            'descripcion' => 'Proyecto principal con carrusel y PostgreSQL',
            'icono' => '🐘',
            'estado' => '✅ Activo'
        ],
        'Ajax' => [
            'nombre' => 'Ajax jQuery',
            'descripcion' => 'Peticiones asíncronas con jQuery',
            'icono' => '🔄',
            'estado' => '✅ Activo'
        ],
        'Carrusel-sql' => [
            'nombre' => 'Carrusel SQL',
            'descripcion' => 'Carrusel interactivo con base de datos',
            'icono' => '🖼️',
            'estado' => '✅ Activo'
        ],
        'Examen-Carrusel-Postgres' => [
            'nombre' => 'Examen Carrusel',
            'descripcion' => 'Examen práctico con PostgreSQL',
            'icono' => '📝',
            'estado' => '✅ Activo'
        ],
        'Modelo-Caja' => [
            'nombre' => 'Modelo Caja',
            'descripcion' => 'Layout y modelo de caja CSS',
            'icono' => '📦',
            'estado' => '✅ Activo'
        ],
        'Practica-servidor' => [
            'nombre' => 'Prácticas Servidor',
            'descripcion' => 'Prácticas del lado del servidor',
            'icono' => '🖥️',
            'estado' => '✅ Activo'
        ],
        'Practica1' => [
            'nombre' => 'Prácticas 1-6',
            'descripcion' => 'Colección de prácticas básicas',
            'icono' => '📚',
            'estado' => '✅ Activo'
        ]
    ];
    
    foreach ($directorios as $dir) {
        if ($dir == '.' || $dir == '..' || $dir == 'portal_gm.php' || $dir == 'index.php') continue;
        
        $ruta_completa = $directorio_base . '/' . $dir;
        
        if (is_dir($ruta_completa)) {
            // Buscar archivo de inicio
            $archivo_inicio = '';
            if (file_exists($ruta_completa . '/index.php')) {
                $archivo_inicio = 'index.php';
            } elseif (file_exists($ruta_completa . '/index.html')) {
                $archivo_inicio = 'index.html';
            } elseif (file_exists($ruta_completa . '/carrusel.php')) {
                $archivo_inicio = 'carrusel.php';
            } elseif (file_exists($ruta_completa . '/ajax.html')) {
                $archivo_inicio = 'ajax.html';
            }
            
            $info = $info_proyectos[$dir] ?? [
                'nombre' => str_replace(['-', '_'], ' ', $dir),
                'descripcion' => 'Proyecto clonado desde GitHub',
                'icono' => '📁',
                'estado' => '📁 Directorio'
            ];
            
            $proyectos[] = [
                'nombre' => $info['nombre'],
                'descripcion' => $info['descripcion'],
                'icono' => $info['icono'],
                'estado' => $archivo_inicio ? '✅ Activo' : $info['estado'],
                'ruta' => $dir . '/' . $archivo_inicio
            ];
        }
    }
    
    return $proyectos;
}

// Configuración
$directorio_base = __DIR__;
$proyectos = obtenerProyectosConIconos($directorio_base);
?>