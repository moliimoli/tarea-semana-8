<?php
session_start();
include 'conexion.php';

// Validar carrito
if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) === 0) {
    die("No hay donaciones para procesar.");
}

// Para simplificar, esta función asume que el donante es anónimo o se gestiona aparte.
// Aquí solo insertamos cada donación con un proyecto fijo (ajusta según tu lógica).

foreach ($_SESSION['carrito'] as $item) {
    $campania = $item['campania'];
    $monto = $item['monto'];

    // Obtener id_proyecto según campaña (debe coincidir con las campañas del select)
    $proyectosCampania = [
        'Mi barrio sin basura' => 1,
        'Educación para Todos' => 2,
        'Salud en mi barrio' => 3
    ];

    $id_proyecto = $proyectosCampania[$campania] ?? 0;
    if ($id_proyecto === 0) continue; // O manejar error

    // Aquí asumimos donante genérico, o deberías agregar lógica para donantes reales
    $nombreDonante = 'Donante anónimo';
    $emailDonante = '';
    $direccion = '';
    $telefono = '';

    // Insertar donante anónimo
    $stmt = $conn->prepare("INSERT INTO donante (nombre, email, direccion, telefono) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombreDonante, $emailDonante, $direccion, $telefono);
    $stmt->execute();
    $id_donante = $stmt->insert_id;
    $stmt->close();

    // Insertar donación
    $stmt2 = $conn->prepare("INSERT INTO donacion (monto, fecha, id_proyecto, id_donante) VALUES (?, NOW(), ?, ?)");
    $stmt2->bind_param("dii", $monto, $id_proyecto, $id_donante);
    $stmt2->execute();
    $stmt2->close();
}

$conn->close();

// Vaciar carrito al finalizar
unset($_SESSION['carrito']);

header("Location: semana6.php?msg=Donación finalizada con éxito");
exit();
?>
