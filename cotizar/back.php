<?php
require_once __DIR__ . '/funciones.php';

$resultado_cotizacion = null;

if (
    isset($_GET['largo'], $_GET['ancho'], $_GET['alto'], $_GET['armado'], $_GET['material']) &&
    $_GET['largo'] !== '' && $_GET['ancho'] !== '' && $_GET['alto'] !== ''
) {
    $largo    = (float) $_GET['largo'];
    $ancho    = (float) $_GET['ancho'];
    $alto     = (float) $_GET['alto'];
    $armado   = (int) $_GET['armado'];
    $material = trim($_GET['material']);

    $resultado_cotizacion = cotizar_corrugado($conn, $armado, $largo, $ancho, $alto, $material);
}
