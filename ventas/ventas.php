<?php
// Registro de ventas diarias

$vendedor_id = $_SESSION['usuario_id'] ?? 0;

// Procesar envio del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $monto_venta = isset($_POST['monto_venta']) ? (float)$_POST['monto_venta'] : 0;
    $monto_envio = $_POST['monto_envio'] !== '' ? (float)$_POST['monto_envio'] : null;
    $monto_suaje = $_POST['monto_suaje'] !== '' ? (float)$_POST['monto_suaje'] : null;

    if ($monto_venta > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO ventas (fecha, monto_venta, monto_envio, monto_suaje, vendedor) VALUES (?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sdddi', $fecha, $monto_venta, $monto_envio, $monto_suaje, $vendedor_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

$hoy = date('Y-m-d');
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

$ventas = [];
$total = 0;

$stmt = mysqli_prepare(
    $conn,
    "SELECT fecha, monto_venta, monto_envio, monto_suaje FROM ventas WHERE vendedor = ? AND fecha BETWEEN ? AND ? ORDER BY fecha DESC"
);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'iss', $vendedor_id, $inicioMes, $finMes);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $ventas[] = $row;
        $total += (float)$row['monto_venta'];
        if ($row['monto_envio'] !== null) {
            $total += (float)$row['monto_envio'];
        }
        if ($row['monto_suaje'] !== null) {
            $total += (float)$row['monto_suaje'];
        }
    }
    mysqli_stmt_close($stmt);
}
?>
<div class="card">
    <div class="card-body">
        <form method="post" class="row g-3">
            <input type="hidden" name="vendedor" value="<?php echo $vendedor_id; ?>">
            <div class="col-md-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $hoy; ?>" required>
            </div>
            <div class="col-md-3">
                <label for="monto_venta" class="form-label">Monto venta</label>
                <input type="number" step="0.01" class="form-control" id="monto_venta" name="monto_venta" required>
            </div>
            <div class="col-md-3">
                <label for="monto_envio" class="form-label">Envío</label>
                <input type="number" step="0.01" class="form-control" id="monto_envio" name="monto_envio">
            </div>
            <div class="col-md-3">
                <label for="monto_suaje" class="form-label">Suaje</label>
                <input type="number" step="0.01" class="form-control" id="monto_suaje" name="monto_suaje">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Registrar venta</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($ventas)): ?>
<div class="card mt-3">
    <div class="card-header">
        <h5>Ventas de este mes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto venta</th>
                        <th>Envío</th>
                        <th>Suaje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($v['fecha']); ?></td>
                        <td>$<?php echo number_format($v['monto_venta'], 2); ?></td>
                        <td>
                            <?php echo $v['monto_envio'] !== null ? '$'.number_format($v['monto_envio'],2) : '-'; ?>
                        </td>
                        <td>
                            <?php echo $v['monto_suaje'] !== null ? '$'.number_format($v['monto_suaje'],2) : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th>$<?php echo number_format($total, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
