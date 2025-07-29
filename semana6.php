<?php
// ===================
// CONFIGURACIÓN SEGURA DE SESIONES
// ===================
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true, 
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
session_regenerate_id(true);

if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    session_start();
}

$timeout = 1800; // 30 minutos
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $timeout) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['ultimo_acceso'] = time();

include 'conexion.php'; // CONEXIÓN BD

// Cargar datos previos de sesión
$registro_donaciones = [];
$registro_eventos = [];

if (isset($_SESSION['donaciones'])) {
    $registro_donaciones = json_decode($_SESSION['donaciones'], true);
}

if (isset($_SESSION['eventos'])) {
    $registro_eventos = json_decode($_SESSION['eventos'], true);
}

// Procesar POST para donaciones o eventos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['limpiar'])) {
        session_unset();
        session_destroy();
        header("Location: semana6.php");
        exit();
    }

    // Procesar donación en sesión (este bloque es opcional si usas procesar_donacion.php)
    $nombre = $_POST['nombre'] ?? "";
    $correo = $_POST['email'] ?? ""; // Cambié para que coincida con formulario
    $monto = $_POST['monto'] ?? "";

    if ($nombre !== "" && $correo !== "" && $monto !== "") {
        $registro_donaciones[] = [
            "donante" => htmlspecialchars($nombre),
            "correo" => htmlspecialchars($correo),
            "monto" => floatval($monto)
        ];
        $_SESSION['donaciones'] = json_encode($registro_donaciones);
        header("Location: semana6.php");
        exit();
    }

    // Procesar evento guardando un array simple en sesión
    $evento = [
        'descripcion' => $_POST['descripcion'] ?? '',
        'tipo' => $_POST['tipo'] ?? '',
        'lugar' => $_POST['lugar'] ?? '',
        'fecha' => $_POST['fecha'] ?? '',
        'hora' => $_POST['hora'] ?? '',
    ];

    if (!in_array('', $evento)) {
        $registro_eventos[] = $evento;
        $_SESSION['eventos'] = json_encode($registro_eventos);
        header("Location: semana6.php");
        exit();
    }
}

$registro_donaciones_to_json = json_encode($registro_donaciones);
$registro_eventos_to_json = json_encode($registro_eventos);
$carrito = $_SESSION['carrito'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Organización sin fines de lucro</title>
    <link rel="stylesheet" href="semana6.css" />
</head>
<body>
    <h1>Organización sin fines de lucro</h1>

    <!-- Buscador eventos -->
    <div class="search-container">
        <input type="text" id="events" placeholder="Buscar evento" aria-label="Buscar evento" />
        <button onclick="search()">Buscar</button>
    </div>

    <div id="results-container" role="region" aria-live="polite"></div>
    <div id="projects-container" role="region" aria-live="polite"></div>
    <div id="donations-container" role="region" aria-live="polite"></div>
    <div id="notifications" role="alert"></div>

    <!-- Carrito de donaciones -->
    <div id="carrito" class="carrito-contenedor" aria-label="Carrito de donaciones">
        <h2 class="carrito-titulo">Carrito de Donaciones</h2>
        <?php if (count($carrito) === 0): ?>
            <p>Tu carrito está vacío.</p>
        <?php else: ?>
            <ul aria-label="Lista de donaciones">
                <?php 
                $total = 0;
                foreach ($carrito as $item):
                    $campania = htmlspecialchars($item['campania']);
                    $monto = is_numeric($item['monto']) ? floatval($item['monto']) : 0;
                    $total += $monto;
                ?>
                <li><?php echo $campania; ?> - $<?php echo number_format($monto, 0, ',', '.'); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Total: $<?php echo number_format($total, 0, ',', '.'); ?></strong></p>

            <div class="acciones-carrito">
                <form action="confirmar_donacion.php" method="post">
                    <button type="submit">Confirmar donación</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Botón limpiar sesión -->
    <div class="limpiar-sesion-container">
        <form method="POST">
            <button type="submit" name="limpiar">Limpiar sesión</button>
        </form>
    </div>

    <!-- Formulario Campañas para donar -->
    <div id="form-campania">
        <h2>Campañas para donar</h2>
        <form action="carrito_donacion.php" method="POST">
            <select name="campania" required>
                <option value="Mi barrio sin basura">Mi barrio sin basura</option>
                <option value="Educación para Todos">Educación para Todos</option>
                <option value="Salud en mi barrio">Salud en mi barrio</option>
            </select>
            <input type="number" name="monto" placeholder="Monto ($)" min="1" required />
            <button type="submit">Agregar al carrito</button>
        </form>
    </div>

    <!-- Formulario Donación -->
    <div id="form-donacion">
        <h2>Realizar una Donación</h2>
        <form action="procesar_donacion.php" method="POST">
            <input type="text" name="nombre" placeholder="Tu nombre" required />
            <input type="email" name="email" placeholder="Tu correo" required />
            <input type="text" name="direccion" placeholder="Dirección" required />
            <input type="text" name="telefono" placeholder="Teléfono" required />
            <input type="number" name="monto" placeholder="Monto ($)" min="1" required />
            <select name="id_proyecto" required>
                <?php
                $proyectos = $conn->query("SELECT id_proyecto, nombre FROM proyecto");
                while ($p = $proyectos->fetch_assoc()) {
                    echo "<option value='" . intval($p['id_proyecto']) . "'>" . htmlspecialchars($p['nombre']) . "</option>";
                }
                ?>
            </select>
            <button type="submit">Donar</button>
        </form>
    </div>

    <!-- Formulario Proyecto -->
    <div id="form-evento">
        <h2>Registrar nuevo proyecto</h2>
        <form action="procesar_proyecto.php" method="POST" onsubmit="return validarProyecto()">
            <input type="text" name="nombreProyecto" placeholder="Nombre del Proyecto" required />
            <input type="text" name="descripcionProyecto" placeholder="Descripción" required />
            <input type="number" name="presupuestoProyecto" placeholder="Presupuesto ($)" min="1" required />
            <input type="date" name="fechaInicioProyecto" required />
            <input type="date" name="fechaFinProyecto" required />
            <button type="submit">Registrar proyecto</button>
        </form>
    </div>

    <!-- Resumen donaciones -->
    <div id="resumen-donaciones" class="tabla-resumen">
        <h2>Proyectos con más de 2 donaciones</h2>
        <?php
            $sql = "SELECT 
                        p.nombre AS nombre_proyecto,
                        COUNT(d.id_donacion) AS total_donaciones,
                        SUM(d.monto) AS monto_total
                    FROM donacion d
                    JOIN proyecto p ON d.id_proyecto = p.id_proyecto
                    GROUP BY d.id_proyecto
                    HAVING total_donaciones > 2";

            $resultado = $conn->query($sql);

            if ($resultado && $resultado->num_rows > 0) {
                echo "<table>";
                echo "<thead><tr><th>Proyecto</th><th>Total Donaciones</th><th>Monto Total Recaudado</th></tr></thead><tbody>";
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($fila['nombre_proyecto']) . "</td>
                            <td>" . intval($fila['total_donaciones']) . "</td>
                            <td>$" . number_format($fila['monto_total'], 0, ',', '.') . "</td>
                          </tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No hay proyectos con más de dos donaciones aún.</p>";
            }
        ?>
    </div>

    <script>
    function validarProyecto() {
        const inicio = new Date(document.querySelector('input[name="fechaInicioProyecto"]').value);
        const fin = new Date(document.querySelector('input[name="fechaFinProyecto"]').value);
        if (inicio >= fin) {
            alert("La fecha de fin debe ser posterior a la fecha de inicio.");
            return false;
        }
        return true;
    }

    const proyectos = [
        { nombre: "Mi barrio sin basura", descripcion: "Implementar basureros en puntos clave" },
        { nombre: "Educación para Todos", descripcion: "Becas escolares a estudiantes vulnerables" },
        { nombre: "Salud en mi barrio", descripcion: "Visitas médicas al barrio con inscripción" },
    ];

    let eventos = <?php echo $registro_eventos_to_json ?? '[]'; ?>;

    function mostrarProyectos() {
        const contenedor = document.getElementById("projects-container");
        contenedor.innerHTML = "<h2>Proyectos</h2>";
        proyectos.forEach(p => {
            contenedor.innerHTML += `<div><strong>${p.nombre}</strong>: ${p.descripcion}</div>`;
        });
    }

    function mostrarDonaciones() {
        const contenedor = document.getElementById("donations-container");
        contenedor.innerHTML = "<h2>Donaciones</h2>";
        let donaciones = <?php echo $registro_donaciones_to_json ?? '[]'; ?>;
        donaciones.forEach(d => {
            contenedor.innerHTML += `<div>${d.donante} donó $${d.monto}</div>`;
        });
    }

    function mostrarNotificacion(mensaje) {
        const contenedor = document.getElementById("notifications");
        const alerta = document.createElement("div");
        alerta.textContent = mensaje;
        alerta.style.backgroundColor = "#d4edda";
        alerta.style.padding = "10px";
        alerta.style.marginTop = "10px";
        alerta.style.border = "1px solid green";
        alerta.style.borderRadius = "5px";
        contenedor.appendChild(alerta);
        setTimeout(() => contenedor.removeChild(alerta), 5000);
    }

    function search() {
        const query = document.getElementById("events").value.toLowerCase();
        const contenedor = document.getElementById("results-container");
        contenedor.innerHTML = "<h2>Resultados de eventos</h2>";
        const eventosSimulados = [
            { nombre: "Tallarinata solidaria", fecha: "26.07.2025" },
            { nombre: "Carrera solidaria", fecha: "03.08.2025" },
            { nombre: "Festival cultural", fecha: "16.08.2025" }
        ];
        const filtrados = eventosSimulados.filter(e => e.nombre.toLowerCase().includes(query));
        if (filtrados.length > 0) {
            filtrados.forEach(e => {
                contenedor.innerHTML += `<div>${e.nombre} - ${e.fecha}</div>`;
            });
        } else {
            contenedor.innerHTML += "<div>No se encontraron eventos.</div>";
        }
    }

    setTimeout(() => mostrarNotificacion("¡Hemos implementado 1.000 basureros en el barrio!"), 3000);
    setTimeout(() => mostrarNotificacion("Nuevo proyecto: Remodelación Plaza Los Girasoles"), 6000);
    setTimeout(() => mostrarNotificacion("Nueva donación reciente: Josefa aportó $7000"), 9000);

    document.getElementById("events").addEventListener("keypress", function(event) {
        if (event.key === "Enter") search();
    });

    mostrarProyectos();
    mostrarDonaciones();
    </script>
</body>
</html>
