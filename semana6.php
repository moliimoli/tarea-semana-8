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

$timeout = 1800;
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $timeout) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['ultimo_acceso'] = time();

session_start();
include 'conexion.php'; // CONECTAR CON LA BD


class Evento {
    public $descripcion;
    public $tipo;
    public $lugar;
    public $fecha;
    public $hora;

    function get_descripcion() { return $this->descripcion; }
    function set_descripcion($descripcion) { $this->descripcion = $descripcion; }
    function get_tipo() { return $this->tipo; }
    function set_tipo($tipo) { $this->tipo = $tipo; }
    function get_lugar() { return $this->lugar; }
    function set_lugar($lugar) { $this->lugar = $lugar; }
    function get_fecha() { return $this->fecha; }
    function set_fecha($fecha) { $this->fecha = $fecha; }
    function get_hora() { return $this->hora; }
    function set_hora($hora) { $this->hora = $hora; }
}

// Carga datos previos
$registro_donaciones = [];
$registro_eventos = [];

if (isset($_SESSION['donaciones'])) {
    $registro_donaciones = json_decode($_SESSION['donaciones'], true);
}

if (isset($_SESSION['eventos'])) {
    $registro_eventos = json_decode($_SESSION['eventos']);
}

// Procesar datos POST y redirigir para evitar duplicados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? "";
    $correo = $_POST['correo'] ?? "";
    $monto = $_POST['monto'] ?? "";

    if ($nombre != "" && $correo != "" && $monto != "") {
        $registro_donaciones[] = [
            "donante" => $nombre,
            "correo" => $correo,
            "monto" => $monto
        ];
        $_SESSION['donaciones'] = json_encode($registro_donaciones);
        header("Location: semana6.php");
        exit();
    }

    $evento = new Evento();
    $evento->set_descripcion($_POST['descripcion'] ?? "");
    $evento->set_tipo($_POST['tipo'] ?? "");
    $evento->set_lugar($_POST['lugar'] ?? "");
    $evento->set_fecha($_POST['fecha'] ?? "");
    $evento->set_hora($_POST['hora'] ?? "");

    if ($evento->get_descripcion() != "" && $evento->get_tipo() != "" && $evento->get_lugar() != "" && $evento->get_fecha() != "" && $evento->get_hora() != "") {
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

    <div class="search-container">
        <input type="text" id="events" placeholder="Evento" />
        <button onclick="search()">Buscar</button>
    </div>

    <div id="results-container"></div>
    <div id="projects-container"></div>
    <div id="donations-container"></div>
    <div id="notifications"></div>

    <!-- Carrito -->
    <div id="carrito">
        <h2>Carrito de Donaciones</h2>
        <?php if (count($carrito) === 0): ?>
        <p>Tu carrito está vacío.</p>
        <?php else: ?>
        <ul>
            <?php foreach ($carrito as $item): ?>
                <li><?php echo htmlspecialchars($item['campania']); ?> - $<?php echo htmlspecialchars($item['monto']); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <!-- Botón para limpiar sesión -->
    <div class="limpiar-sesion-container">
        <form method="POST">
            <button type="submit" name="limpiar">Limpiar sesión</button>
        </form>
    </div>

    <script>
        const donaciones = <?php echo $registro_donaciones_to_json; ?>;
    </script>
        <script src="semana5.js"></script>

    <!-- Campañas para donar -->
    <div id="form-campania">
        <h2>Campañas para donar</h2>
        <form action="carrito_donacion.php" method="POST">
            <select name="campania" required>
                <option value="Mi barrio sin basura">Mi barrio sin basura</option>
                <option value="Educación para Todos">Educación para Todos</option>
                <option value="Salud en mi barrio">Salud en mi barrio</option>
            </select>
            <input type="number" name="monto" placeholder="Monto ($)" min="1" required>
            <button type="submit">Agregar al carrito</button>
        </form>
    </div>

    <!-- DONACION -->
    <div id="form-donacion">
        <h2>Realizar una Donación</h2>
        <form action="procesar_donacion.php" method="POST">
            <input type="text" name="nombre" placeholder="Tu nombre" required />
            <input type="email" name="email" placeholder="Tu correo" required />
            <input type="text" name="direccion" placeholder="Dirección" required />
            <input type="text" name="telefono" placeholder="Teléfono" required />
            <input type="number" name="monto" placeholder="Monto ($)" required min="1" />
            <button type="submit">Donar</button>
        </form>
    </div>
        <select name="id_proyecto" required>
            <?php
            include 'conexion.php'; // <- Este archivo debe tener la conexión a MySQL
            $proyectos = $conn->query("SELECT id_proyecto, nombre FROM proyecto");
            while ($p = $proyectos->fetch_assoc()) {
                echo "<option value='{$p['id_proyecto']}'>{$p['nombre']}</option>";
            }
            ?>
        </select>

    <!-- EVENTO -->
    <div id="form-evento">
        <h2>Registrar nuevo proyecto</h2>
        <form action="procesar_proyecto.php" method="POST">
            <input type="text" name="nombreProyecto" placeholder="Nombre del Proyecto" required />
            <input type="text" name="descripcionProyecto" placeholder="Descripción" required />
            <input type="text" name="presupuestoProyecto" placeholder="$" required />
            <input type="date" name="fechaInicioProyecto" required />
            <input type="date" name="fechaFinProyecto" required />
            <button type="submit">Registrar proyecto</button>
        </form>
    </div>

    <!-- RESUMEN Donaciones -->    
    <div id="resumen-donaciones" class="box">
        <h2>Proyectos con más de 2 donaciones</h2>
        <?php
            include 'conexion.php';
            $sql = "SELECT 
                p.nombre AS nombre_proyecto,
                COUNT(d.id_donacion) AS total_donaciones,
                SUM(d.monto) AS monto_total
                    FROM donacion d
                    JOIN proyecto p ON d.id_proyecto = p.id_proyecto
                    GROUP BY d.id_proyecto
                    HAVING total_donaciones > 2";

    $resultado = $conn->query($sql);

    if ($resultado->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Proyecto</th><th>Total Donaciones</th><th>Monto Total Recaudado</th></tr>";
        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr>
                    <td>{$fila['nombre_proyecto']}</td>
                    <td>{$fila['total_donaciones']}</td>
                    <td>\${$fila['monto_total']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay proyectos con más de dos donaciones aún.</p>";
    }
    ?>
    </div>

    <script>
        function validarProyecto() {
        const inicio = new Date(document.querySelector('input[name="fecha_inicio"]').value);
        const fin = new Date(document.querySelector('input[name="fecha_fin"]').value);
            if (inicio >= fin) {
                alert("La fecha de fin debe ser posterior a la fecha de inicio.");
            return false;
            }
            return true;
            }
    </script>


    <script>
        const proyectos = [
            { nombre: "Mi barrio sin basura", descripcion: "Implementar basureros en puntos clave" },
            { nombre: "Educación para Todos", descripcion: "Becas escolares a estudiantes vulnerables" },
            { nombre: "Salud en mi barrio", descripcion: "Visitas médicas al barrio con inscripción" },
        ];

        let eventos = <?php echo $registro_eventos_to_json ?>;

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
            let donaciones = <?php echo $registro_donaciones_to_json ?>;
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
            const eventos = [
                 { nombre: "Tallarinata solidaria", fecha: "26.07.2025" },
                 { nombre: "Carrera solidaria", fecha: "03.08.2025" },
                 { nombre: "Festival cultural", fecha: "16.08.2025" }];
            const filtrados = eventos.filter(e => e.nombre.toLowerCase().includes(query));
                if (filtrados.length > 0) {
                    filtrados.forEach(e => {
                contenedor.innerHTML += `<div>${e.nombre} - ${e.fecha}</div>`;});
                } else {
                contenedor.innerHTML += "<div>No se encontraron eventos.</div>";}
            }

        setTimeout(() => mostrarNotificacion("¡Hemos implementado 1.000 basureros en el barrio!"), 3000);
        setTimeout(() => mostrarNotificacion("Nuevo proyecto: Remodelación Plaza Los Girasoles"), 6000);
        setTimeout(() => mostrarNotificacion("Nueva donación reciente: Josefa aportó $7000"), 9000);

        document.getElementById("events").addEventListener("keypress", function (event) {
            if (event.key === "Enter") search();
        });

        mostrarProyectos();
        mostrarDonaciones();
    </script>
</body>
</html>