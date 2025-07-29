<?php
session_start();

if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) === 0) {
    echo "<p>Tu carrito está vacío. <a href='semana6.php'>Volver</a></p>";
    exit();
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Confirmar Donación</title>
    <link rel="stylesheet" href="semana6.css" />
</head>
<body>
    <?php
    // Mostrar mensaje si se recibió por GET
    if (isset($_GET['msg'])) {
        echo '<div class="notificacion-exito">' . htmlspecialchars($_GET['msg']) . '</div>';
    }
    ?>

    <h2>Resumen de donaciones</h2>

    <div class="resumen-donaciones">
        <?php foreach ($_SESSION['carrito'] as $item):
            $campania = htmlspecialchars($item['campania']);
            $monto = is_numeric($item['monto']) ? floatval($item['monto']) : 0;
            $total += $monto;
        ?>
        <p><?php echo $campania; ?> - $<?php echo number_format($monto, 0, ',', '.'); ?></p>
        <?php endforeach; ?>
        <p><strong>Total donado: $<?php echo number_format($total, 0, ',', '.'); ?></strong></p>
    </div>

    <form action="finalizar_donacion.php" method="POST">
        <button type="submit">Finalizar Donación</button>
    </form>

    <a href="semana6.php" class="volver">Volver al inicio</a>
</body>
</html>
