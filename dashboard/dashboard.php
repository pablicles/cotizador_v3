<?php
if ($_SESSION['usuario_admin'] !== 's') {
    echo '<div class="alert alert-danger">No autorizado</div>';
    return;
}

$range = $_GET['range'] ?? 'mes_actual';
$vendedor_filtro = $_GET['vendedor'] ?? '';
$cuenta_filtro = $_GET['cuenta'] ?? 'todas';
$inicio = '';
$fin = '';
$hoy = date('Y-m-d');

switch ($range) {
    case 'dia_actual':
        $inicio = $hoy;
        $fin = $hoy;
        break;
    case 'semana_actual':
        $inicio = date('Y-m-d', strtotime('monday this week'));
        $fin = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'semana_pasada':
        $inicio = date('Y-m-d', strtotime('monday last week'));
        $fin = date('Y-m-d', strtotime('sunday last week'));
        break;
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

// Obtener lista de vendedores
$vendedores = [];
$resVend = mysqli_query($conn, "SELECT id, CONCAT(nombre,' ',apellido) AS nombre FROM vendedores ORDER BY nombre");
if ($resVend) {
    while ($row = mysqli_fetch_assoc($resVend)) {
        $vendedores[] = $row;
    }
}

// Construir clausulas de filtrado
$where = 'WHERE ve.fecha BETWEEN ? AND ?';
$params = [$inicio, $fin];
$types = 'ss';
if ($cuenta_filtro !== 'todas') {
    $where .= ' AND ve.cuenta = ?';
    $params[] = $cuenta_filtro;
    $types .= 's';
}
if ($vendedor_filtro !== '') {
    $where .= ' AND ve.vendedor = ?';
    $params[] = (int)$vendedor_filtro;
    $types .= 'i';
}

// Totales por vendedor
$totales_vendedores = [];
$sqlVend = "SELECT v.id, CONCAT(v.nombre, ' ', v.apellido) AS nombre,
        SUM(CASE WHEN ve.iva=1 THEN ve.monto_venta/1.16 ELSE ve.monto_venta END) AS base_venta,
        SUM(CASE WHEN ve.iva=1 THEN ve.monto_venta - ve.monto_venta/1.16 ELSE 0 END) AS iva_venta,
        SUM(CASE WHEN ve.monto_envio IS NOT NULL THEN (CASE WHEN ve.iva=1 THEN ve.monto_envio/1.16 ELSE ve.monto_envio END) ELSE 0 END) AS base_envio,
        SUM(CASE WHEN ve.monto_envio IS NOT NULL AND ve.iva=1 THEN ve.monto_envio - ve.monto_envio/1.16 ELSE 0 END) AS iva_envio,
        SUM(CASE WHEN ve.monto_suaje IS NOT NULL THEN (CASE WHEN ve.iva=1 THEN ve.monto_suaje/1.16 ELSE ve.monto_suaje END) ELSE 0 END) AS base_suaje,
        SUM(CASE WHEN ve.monto_suaje IS NOT NULL AND ve.iva=1 THEN ve.monto_suaje - ve.monto_suaje/1.16 ELSE 0 END) AS iva_suaje
    FROM ventas ve
    JOIN vendedores v ON ve.vendedor = v.id
    $where
    GROUP BY v.id, nombre
    ORDER BY nombre";
$stmt = mysqli_prepare($conn, $sqlVend);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $totales_vendedores[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Totales por fecha
$totales_fecha = [];
$sqlFecha = "SELECT ve.fecha,
        SUM(CASE WHEN ve.iva=1 THEN ve.monto_venta/1.16 ELSE ve.monto_venta END) AS base_venta,
        SUM(CASE WHEN ve.iva=1 THEN ve.monto_venta - ve.monto_venta/1.16 ELSE 0 END) AS iva_venta
    FROM ventas ve
    $where
    GROUP BY ve.fecha
    ORDER BY ve.fecha";
$stmt = mysqli_prepare($conn, $sqlFecha);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $totales_fecha[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Totales por cuenta
$totales_cuenta = [];
$sqlCuenta = "SELECT ve.cuenta,
        SUM(CASE WHEN ve.iva=1 THEN ve.monto_venta/1.16 ELSE ve.monto_venta END) AS base_venta,
        SUM(CASE WHEN ve.iva=1 THEN ve.monto_venta - ve.monto_venta/1.16 ELSE 0 END) AS iva_venta,
        SUM(CASE WHEN ve.monto_envio IS NOT NULL THEN (CASE WHEN ve.iva=1 THEN ve.monto_envio/1.16 ELSE ve.monto_envio END) ELSE 0 END) AS base_envio,
        SUM(CASE WHEN ve.monto_envio IS NOT NULL AND ve.iva=1 THEN ve.monto_envio - ve.monto_envio/1.16 ELSE 0 END) AS iva_envio,
        SUM(CASE WHEN ve.monto_suaje IS NOT NULL THEN (CASE WHEN ve.iva=1 THEN ve.monto_suaje/1.16 ELSE ve.monto_suaje END) ELSE 0 END) AS base_suaje,
        SUM(CASE WHEN ve.monto_suaje IS NOT NULL AND ve.iva=1 THEN ve.monto_suaje - ve.monto_suaje/1.16 ELSE 0 END) AS iva_suaje
    FROM ventas ve
    $where
    GROUP BY ve.cuenta
    ORDER BY ve.cuenta";
$stmt = mysqli_prepare($conn, $sqlCuenta);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $totales_cuenta[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$labels_vendedores = array_map(fn($v) => $v['nombre'], $totales_vendedores);
$datos_vendedores_base = array_map(fn($v) => (float)$v['base_venta'], $totales_vendedores);
$datos_vendedores_iva = array_map(fn($v) => (float)$v['iva_venta'], $totales_vendedores);

$labels_fecha = array_map(fn($f) => $f['fecha'], $totales_fecha);
$datos_fecha_base = array_map(fn($f) => (float)$f['base_venta'], $totales_fecha);
$datos_fecha_iva = array_map(fn($f) => (float)$f['iva_venta'], $totales_fecha);
?>
<div class="card mb-3">
    <div class="card-body">
        <form class="row g-3" method="get">
            <input type="hidden" name="action" value="dashboard">
            <div class="col-md-3">
                <label class="form-label" for="range">Rango</label>
                <select class="form-select" id="range" name="range" onchange="this.form.submit()">
                    <option value="dia_actual" <?php if($range==='dia_actual') echo 'selected'; ?>>Hoy</option>
                    <option value="semana_actual" <?php if($range==='semana_actual') echo 'selected'; ?>>Semana actual</option>
                    <option value="semana_pasada" <?php if($range==='semana_pasada') echo 'selected'; ?>>Semana pasada</option>
                    <option value="mes_actual" <?php if($range==='mes_actual') echo 'selected'; ?>>Mes actual</option>
                    <option value="mes_pasado" <?php if($range==='mes_pasado') echo 'selected'; ?>>Mes pasado</option>
                    <option value="ano_actual" <?php if($range==='ano_actual') echo 'selected'; ?>>Año actual</option>
                    <option value="personalizado" <?php if($range==='personalizado') echo 'selected'; ?>>Personalizado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="vendedor">Vendedor</label>
                <select class="form-select" id="vendedor" name="vendedor" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($vendedores as $v): ?>
                    <option value="<?php echo $v['id']; ?>" <?php if($vendedor_filtro==$v['id']) echo 'selected'; ?>><?php echo htmlspecialchars($v['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="cuenta">Cuenta</label>
                <select class="form-select" id="cuenta" name="cuenta" onchange="this.form.submit()">
                    <option value="todas" <?php if($cuenta_filtro==='todas') echo 'selected'; ?>>Ambas</option>
                    <option value="millop" <?php if($cuenta_filtro==='millop') echo 'selected'; ?>>Millop</option>
                    <option value="alterna" <?php if($cuenta_filtro==='alterna') echo 'selected'; ?>>Alterna</option>
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
                        <th class="text-end">Ventas sin IVA</th>
                        <th class="text-end">IVA ventas</th>
                        <th class="text-end">Total ventas</th>
                        <th class="text-end">Envíos sin IVA</th>
                        <th class="text-end">IVA envíos</th>
                        <th class="text-end">Total envíos</th>
                        <th class="text-end">Suajes sin IVA</th>
                        <th class="text-end">IVA suajes</th>
                        <th class="text-end">Total suajes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($totales_vendedores as $tv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tv['nombre']); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['base_venta'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['iva_venta'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['base_venta'] + $tv['iva_venta'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['base_envio'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['iva_envio'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['base_envio'] + $tv['iva_envio'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['base_suaje'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['iva_suaje'], 2); ?></td>
                        <td class="text-end">$<?php echo number_format($tv['base_suaje'] + $tv['iva_suaje'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
</div>
</div>
<?php endif; ?>
<?php if ($totales_cuenta): ?>
<div class="card mt-3">
    <div class="card-header"><h5 class="card-title mb-0">Resumen por cuenta</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th class="text-end">Ventas sin IVA</th>
                        <th class="text-end">IVA ventas</th>
                        <th class="text-end">Total ventas</th>
                        <th class="text-end">Envíos sin IVA</th>
                        <th class="text-end">IVA envíos</th>
                        <th class="text-end">Total envíos</th>
                        <th class="text-end">Suajes sin IVA</th>
                        <th class="text-end">IVA suajes</th>
                        <th class="text-end">Total suajes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($totales_cuenta as $tc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tc['cuenta']); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['base_venta'],2); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['iva_venta'],2); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['base_venta'] + $tc['iva_venta'],2); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['base_envio'],2); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['iva_envio'],2); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['base_envio'] + $tc['iva_envio'],2); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['base_suaje'],2); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['iva_suaje'],2); ?></td>
                        <td class="text-end">$<?php echo number_format($tc['base_suaje'] + $tc['iva_suaje'],2); ?></td>
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
        datasets: [
            {
                label: 'Sin IVA',
                data: <?php echo json_encode($datos_vendedores_base); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)'
            },
            {
                label: 'IVA',
                data: <?php echo json_encode($datos_vendedores_iva); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            x: { stacked: true },
            y: { beginAtZero: true, stacked: true }
        }
    }
});

const ctxFecha = document.getElementById('chartFechas');
new Chart(ctxFecha, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels_fecha); ?>,
        datasets: [
            {
                label: 'Sin IVA',
                data: <?php echo json_encode($datos_fecha_base); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)'
            },
            {
                label: 'IVA',
                data: <?php echo json_encode($datos_fecha_iva); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            x: { stacked: true },
            y: { beginAtZero: true, stacked: true }
        }
    }
});
</script>
