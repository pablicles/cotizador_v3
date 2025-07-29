<?php
/*
 * Aqui se procesan los datos enviados en el formulario front.php
 * Se definen todas las variables esperadas con un valor por
 * defecto en caso de que no se reciban desde el formulario.
 */

// Medidas de la caja en milimetros
$largo    = isset($_POST['largo'])    ? (float)$_POST['largo']    : 0.0;
$ancho    = isset($_POST['ancho'])    ? (float)$_POST['ancho']    : 0.0;
$alto     = isset($_POST['alto'])     ? (float)$_POST['alto']     : 0.0;

// Tipo de armado de la caja
$armado   = isset($_POST['armado'])   ? trim($_POST['armado'])    : '';

// Material seleccionado
$material = isset($_POST['material']) ? trim($_POST['material']) : '';

// Cantidad de piezas solicitadas
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad']  : 0;

// A partir de aqui se podra continuar con el proceso de cotizacion
?>