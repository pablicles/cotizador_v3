<?php
session_start();
require_once __DIR__ . '/../conn.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$vendedor_id = (int)$_SESSION['usuario_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    $stmt = mysqli_prepare($conn, 'DELETE FROM ventas WHERE id = ? AND vendedor = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $id, $vendedor_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header('Location: ../index.php?action=registro_ventas');
exit;
?>
