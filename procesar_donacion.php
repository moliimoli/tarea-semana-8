<?php
include 'conexion.php';

// Captura datos del formulario
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];
$monto = $_POST['monto'];
$id_proyecto = $_POST['id_proyecto'];

// 1. Insertar donante
$stmt = $conn->prepare("INSERT INTO donante (nombre, email, direccion, telefono) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nombre, $email, $direccion, $telefono);
$stmt->execute();
$id_donante = $stmt->insert_id;
$stmt->close();

// 2. Insertar donación
$stmt2 = $conn->prepare("INSERT INTO donacion (monto, fecha, id_proyecto, id_donante) VALUES (?, NOW(), ?, ?)");
$stmt2->bind_param("dii", $monto, $id_proyecto, $id_donante);
$stmt2->execute();
$stmt2->close();

$conn->close();

header("Location: semana6.php?donacion=ok");
exit();
?>
