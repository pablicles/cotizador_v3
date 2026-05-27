<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso denegado.");
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
$contacto = trim($_POST['contacto'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

if (empty($nombre_empresa)) {
    $_SESSION['mensaje_clientes'] = "El nombre de la empresa es obligatorio.";
    $_SESSION['tipo_mensaje_clientes'] = "danger";
    header("Location: index.php?action=clientes");
    exit;
}

if ($id > 0) {
    // Actualizar cliente
    $stmt = $conn->prepare("UPDATE clientes SET nombre_empresa = ?, contacto = ?, telefono = ?, correo = ?, direccion = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nombre_empresa, $contacto, $telefono, $correo, $direccion, $id);
} else {
    // Insertar nuevo
    $stmt = $conn->prepare("INSERT INTO clientes (nombre_empresa, contacto, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre_empresa, $contacto, $telefono, $correo, $direccion);
}

if ($stmt->execute()) {
    $_SESSION['mensaje_clientes'] = "Cliente guardado correctamente.";
    $_SESSION['tipo_mensaje_clientes'] = "success";
} else {
    $_SESSION['mensaje_clientes'] = "Error al guardar el cliente: " . $stmt->error;
    $_SESSION['tipo_mensaje_clientes'] = "danger";
}

header("Location: index.php?action=clientes");
exit;
?>