<?php
include 'conexion.php';

// Captura datos del formulario
$nombreProyecto = $_POST['nombreProyecto'];
$descripcionProyecto = $_POST['descripcionProyecto'];
$presupuestoProyecto = $_POST['presupuestoProyecto'];

$fechaInicioProyecto = $_POST['fechaInicioProyecto'];
$timestamp_fechaInicioProyecto = strtotime($fechaInicioProyecto);
$mysql_date_fechaInicioProyecto = date("Y-m-d", $timestamp_fechaInicioProyecto);

$fechaFinProyecto = $_POST['fechaFinProyecto'];
$timestamp_fechaFinProyecto = strtotime($fechaFinProyecto);
$mysql_date_fechaFinProyecto = date("Y-m-d", $timestamp_fechaFinProyecto);

// 1. Insertar Proyecto
$stmt = $conn->prepare("INSERT INTO proyecto (nombre, descripcion, presupuesto, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nombreProyecto, $descripcionProyecto, $presupuestoProyecto, $mysql_date_fechaInicioProyecto, $mysql_date_fechaFinProyecto);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: semana6.php?proyecto=ok");
exit();

?>