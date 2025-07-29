<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$campania = filter_input(INPUT_POST, 'campania', FILTER_SANITIZE_STRING) ?? "";
$monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);

if ($campania !== "" && $monto !== false && $monto > 0) {
    $_SESSION['carrito'][] = [
        'campania' => $campania,
        'monto' => $monto
    ];
}

header("Location: semana6.php");
exit();
?>
