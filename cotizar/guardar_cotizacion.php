<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso denegado.");
}

$cliente_id = (int)$_POST['cliente_id'];
$vendedor_id = (int)$_SESSION['usuario_id'];
$cantidad = (int)$_POST['cantidad'];
$precio_unitario_sin_iva = (float)$_POST['precio_unitario'];
$costo_unitario = (float)$_POST['costo_unitario'];

$largo = (float)$_POST['largo'];
$ancho = (float)$_POST['ancho'];
$alto = (float)$_POST['alto'];
$clave_material = $_POST['id_material'];
$id_armado = (int)$_POST['id_armado'];
$impresion_offset = (int)$_POST['impresion_offset'];
$offset_tintas = (int)$_POST['offset_tintas'];
$acabados_ids = $_POST['acabados_ids'];

$armado_nombre = $_POST['armado_nombre'] ?? 'Personalizada';
$material_descripcion = $_POST['material_descripcion'] ?? '';

// Obtener ID del material usando la clave
$stmt_mat = $conn->prepare("SELECT id FROM material WHERE clave = ?");
$stmt_mat->bind_param("s", $clave_material);
$stmt_mat->execute();
$res_mat = $stmt_mat->get_result();
$id_material_db = 0;
if ($row = $res_mat->fetch_assoc()) {
    $id_material_db = $row['id'];
}

// Calculos de totales (el precio ingresado en el modal ahora es SIN IVA)
$subtotal = $cantidad * $precio_unitario_sin_iva;
$iva = $subtotal * 0.16; // Calculamos el 16% referencial para la BD
$total = $subtotal + $iva;

$conn->begin_transaction();

try {
    // 1. Insertar Cabecera
    $stmt_cot = $conn->prepare("INSERT INTO cotizaciones (cliente_id, vendedor_id, estatus, subtotal, iva, total) VALUES (?, ?, 'Borrador', ?, ?, ?)");
    $stmt_cot->bind_param("iiddd", $cliente_id, $vendedor_id, $subtotal, $iva, $total);
    $stmt_cot->execute();
    $cotizacion_id = $stmt_cot->insert_id;

    // 2. Insertar Detalle General
    $descripcion_item = "Caja " . $armado_nombre . " de " . $largo . "x" . $ancho . "x" . $alto . " cm en " . $material_descripcion;
    $tipo_item = 'a_medida';
    
    $stmt_det = $conn->prepare("INSERT INTO cotizaciones_detalles (cotizacion_id, tipo_item, descripcion, cantidad, costo_unitario, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_det->bind_param("issiddd", $cotizacion_id, $tipo_item, $descripcion_item, $cantidad, $costo_unitario, $precio_unitario_sin_iva, $subtotal);
    $stmt_det->execute();
    $detalle_id = $stmt_det->insert_id;

    // 3. Insertar Detalle Técnico
    $stmt_tec = $conn->prepare("INSERT INTO detalle_caja_medida (id_detalle, largo, ancho, alto, id_material, id_armado, impresion_offset, tintas, acabados_ids) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_tec->bind_param("idddiiiis", $detalle_id, $largo, $ancho, $alto, $id_material_db, $id_armado, $impresion_offset, $offset_tintas, $acabados_ids);
    $stmt_tec->execute();

    $conn->commit();
    
    $_SESSION['mensaje_cotizacion'] = "Cotización #<strong>{$cotizacion_id}</strong> guardada correctamente como Borrador.";
    $_SESSION['tipo_mensaje_cotizacion'] = "success";
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['mensaje_cotizacion'] = "Error al guardar la cotización: " . $e->getMessage();
    $_SESSION['tipo_mensaje_cotizacion'] = "danger";
}

header("Location: index.php?action=cotizar");
exit;
?>