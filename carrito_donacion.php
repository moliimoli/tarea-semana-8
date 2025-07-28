<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$campania = $_POST['campania'] ?? "";
$monto = $_POST['monto'] ?? 0;

if ($campania !== "" && $monto > 0) {
    $_SESSION['carrito'][] = [
        'campania' => $campania,
        'monto' => $monto
    ];
}

header("Location: semana6.php");
exit;
?>
