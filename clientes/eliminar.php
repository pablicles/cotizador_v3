<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Validar integridad relacional: Buscar si ya tiene cotizaciones
    $check = $conn->prepare("SELECT id FROM cotizaciones WHERE cliente_id = ? LIMIT 1");
    if ($check) {
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result();
        
        if ($res->num_rows > 0) {
            $_SESSION['mensaje_clientes'] = "<strong>No se puede eliminar</strong> este cliente porque tiene cotizaciones asociadas. Para mantener la integridad del ERP, solo puedes editarlo.";
            $_SESSION['tipo_mensaje_clientes'] = "warning";
        } else {
            $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['mensaje_clientes'] = "Cliente eliminado correctamente.";
                $_SESSION['tipo_mensaje_clientes'] = "success";
            } else {
                $_SESSION['mensaje_clientes'] = "Error al eliminar: " . $stmt->error;
                $_SESSION['tipo_mensaje_clientes'] = "danger";
            }
        }
    } else {
        $_SESSION['mensaje_clientes'] = "Error en la validación: " . $conn->error;
        $_SESSION['tipo_mensaje_clientes'] = "danger";
    }
}

header("Location: index.php?action=clientes");
exit;
?>