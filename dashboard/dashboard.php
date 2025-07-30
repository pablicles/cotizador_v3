<?php
if ($_SESSION['usuario_admin'] !== 's') {
    echo '<div class="alert alert-danger">No autorizado</div>';
    return;
}

$range = $_GET['range'] ?? 'mes_actual';
$inicio = '';
$fin = '';
$hoy = date('Y-m-d');

switch ($range) {
    case 'mes_pasado':
        $inicio = date('Y-m-01', strtotime('first day of last month'));
        $fin = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'ano_actual':
        $inicio = date('Y-01-01');
        $fin = date('Y-12-31');
        break;
    case 'personalizado':
        $inicio = $_GET['inicio'] ?? date('Y-m-01');
        $fin = $_GET['fin'] ?? $hoy;
        break;
    default:
        $inicio = date('Y-m-01');
        $fin = date('Y-m-t');
        break;
}

// Totales por vendedor
$totales_vendedores = [];
$stmt = mysqli_prepare(
    $conn,
    'SELECT v.id, CONCAT(v.nombre, " ", v.apellido) AS nombre, SUM(ve.monto_venta) AS total
     FROM ventas ve
     JOIN vendedores v ON ve.vendedor = v.id
     WHERE ve.fecha BETWEEN ? AND ?
     GROUP BY v.id, nombre
     ORDER BY nombre'
);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ss', $inicio, $fin);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $totales_vendedores[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Totales por fecha
$totales_fecha = [];
$stmt = mysqli_prepare(
    $conn,
    'SELECT fecha, SUM(monto_venta) AS total
     FROM ventas
     WHERE fecha BETWEEN ? AND ?
     GROUP BY fecha
     ORDER BY fecha'
);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ss', $inicio, $fin);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $totales_fecha[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$labels_vendedores = array_map(fn($v) => $v['nombre'], $totales_vendedores);
$datos_vendedores = array_map(fn($v) => (float)$v['total'], $totales_vendedores);

$labels_fecha = array_map(fn($f) => $f['fecha'], $totales_fecha);
$datos_fecha = array_map(fn($f) => (float)$f['total'], $totales_fecha);
?>
<div class="card mb-3">
    <div class="card-body">
        <form class="row g-3" method="get">
            <input type="hidden" name="action" value="dashboard">
            <div class="col-md-4">
                <label class="form-label" for="range">Rango</label>
                <select class="form-select" id="range" name="range" onchange="this.form.submit()">
                    <option value="mes_actual" <?php if($range==='mes_actual') echo 'selected'; ?>>Mes actual</option>
                    <option value="mes_pasado" <?php if($range==='mes_pasado') echo 'selected'; ?>>Mes pasado</option>
                    <option value="ano_actual" <?php if($range==='ano_actual') echo 'selected'; ?>>Año actual</option>
                    <option value="personalizado" <?php if($range==='personalizado') echo 'selected'; ?>>Personalizado</option>
                </select>
            </div>
            <?php if ($range === 'personalizado'): ?>
            <div class="col-md-3">
                <label class="form-label" for="inicio">Inicio</label>
                <input type="date" class="form-control" id="inicio" name="inicio" value="<?php echo htmlspecialchars($inicio); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="fin">Fin</label>
                <input type="date" class="form-control" id="fin" name="fin" value="<?php echo htmlspecialchars($fin); ?>">
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Aplicar</button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Ventas por vendedor</h5></div>
            <div class="card-body">
                <canvas id="chartVendedores" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Ventas totales</h5></div>
            <div class="card-body">
                <canvas id="chartFechas" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
<?php if ($totales_vendedores): ?>
<div class="card mt-3">
    <div class="card-header"><h5 class="card-title mb-0">Resumen por vendedor</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Vendedor</th>
                        <th class="text-end">Total ventas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($totales_vendedores as $tv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tv['nombre']); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const ctxVend = document.getElementById('chartVendedores');
new Chart(ctxVend, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels_vendedores); ?>,
        datasets: [{
            label: 'Ventas',
            data: <?php echo json_encode($datos_vendedores); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)'
        }]
    },
    options: {
        responsive: true,
        scales: {y: {beginAtZero: true}}
    }
});

const ctxFecha = document.getElementById('chartFechas');
new Chart(ctxFecha, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels_fecha); ?>,
        datasets: [{
            label: 'Ventas',
            data: <?php echo json_encode($datos_fecha); ?>,
            fill: false,
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {y: {beginAtZero: true}}
    }
});
</script>
