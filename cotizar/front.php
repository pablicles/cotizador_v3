<?php
require_once 'funciones.php';

$armados    = get_armados($conn);
$materiales = get_materiales($conn);

$selected_armado   = isset($_GET['armado']) ? (int)$_GET['armado'] : 1;
$selected_material = $_GET['material'] ?? ($materiales[0]['clave'] ?? '');

$procesos_default = get_procesos_por_armado($conn, $selected_armado);
$merma_def    = get_valor($conn, 'Merma');
$utilidad_def = get_valor($conn, 'Utilidad');
$iva_def      = get_valor($conn, 'iva');

$precio_m2_def = 0;
if ($selected_material) {
    $mat_tmp = get_material_info($conn, $selected_material);
    if ($mat_tmp) {
        $precio_m2_def = $mat_tmp['precio_m2'];
    }
}

$cm_suaje_def = 0;
if (isset($_GET['largo'], $_GET['ancho'], $_GET['alto'])) {
    $datos_tmp = obtener_datos_caja($selected_armado, (float)$_GET['largo'], (float)$_GET['ancho'], (float)$_GET['alto']);
    foreach ($datos_tmp['cm_suaje'] as $c) {
        $cm_suaje_def += $c;
    }
}

if (isset($_GET['cm_suaje'])) {
    $cm_suaje_def = (float)$_GET['cm_suaje'];
}
if (isset($_GET['precio_m2'])) {
    $precio_m2_def = (float)$_GET['precio_m2'];
}
if (isset($_GET['merma'])) {
    $merma_def = (float)$_GET['merma'];
}
if (isset($_GET['utilidad'])) {
    $utilidad_def = (float)$_GET['utilidad'];
}
if (isset($_GET['iva'])) {
    $iva_def = (float)$_GET['iva'];
}

$procesos_valores = [];
foreach ($procesos_default as $p) {
    $valor = $p['precio'];
    if (isset($_GET['proceso'][$p['id']])) {
        $valor = (float)$_GET['proceso'][$p['id']];
    }
    $procesos_valores[$p['id']] = $valor;
}

$similares = [];
$cotizacion = [];
$mas = false;
if (isset($_GET['largo'], $_GET['ancho'], $_GET['alto'])) {
    $l = (float)$_GET['largo'];
    $a = (float)$_GET['ancho'];
    $h = (float)$_GET['alto'];
    $limit = isset($_GET['limit']) ? max(5, (int)$_GET['limit']) : 5;
    $tmp_res   = get_cajas_proximas($conn, $l, $a, $h, $limit + 1);
    $similares = array_slice($tmp_res, 0, $limit);
    $mas       = count($tmp_res) > $limit;
    if ($selected_armado && $selected_material) {
        $opciones = [
            'cm_suaje' => $cm_suaje_def,
            'precio_m2' => $precio_m2_def,
            'merma' => $merma_def,
            'utilidad' => $utilidad_def,
            'iva' => $iva_def,
            'procesos' => $procesos_valores,
        ];
        $cotizacion = cotizar_corrugado($conn, $selected_armado, $l, $a, $h, $selected_material, $opciones);
    }
}
?>
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col">
				<h5>Caja</h5>
			</div>
		</div>
                <form class="form" method="get">
                        <div class="row">
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="largo" class="form-label">Largo</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
                    <input class="form-control" type="number" name="largo" placeholder="Largo" value="<?php echo isset($_GET['largo']) ? htmlspecialchars($_GET['largo']) : '' ?>" required>
				</div>
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="ancho" class="form-label">Ancho</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<input class="form-control" type="number" name="ancho" placeholder="Ancho" value="<?php echo isset($_GET['ancho']) ? htmlspecialchars($_GET['ancho']) : '' ?>" required>
				</div>
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="alto" class="form-label">Alto</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<input class="form-control" type="number" name="alto" placeholder="Alto" value="<?php echo isset($_GET['alto']) ? htmlspecialchars($_GET['alto']) : '' ?>" required>
				</div>
				<div class="col-12 col-lg-1 mb-lg-3">
                                <div class="d-flex align-items-center"><label for="armado" class="form-label mb-0 me-1">Armado</label><button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#armadoModal">?</button></div>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
                                        <select class="form-control" name="armado" id="armado">
                                                <?php foreach($armados as $arm): ?>
                                                        <option value="<?php echo $arm['id']; ?>" <?php echo ($arm['id'] == $selected_armado) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($arm['nombre']); ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                </div>
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="material">Material</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
                                <select class="form-control" name="material" id="material">
                                    <?php foreach($materiales as $m): ?>
                                        <option value="<?php echo $m['clave']; ?>" <?php echo ($m['clave'] == $selected_material) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m['descripcion']) . " - $" . number_format($m['precio_m2'], 2) . "/m²"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                        </div>
                        <div class="row mb-2">
                                <div class="col-12 text-end">
                                        <a class="link-secondary small" data-bs-toggle="collapse" href="#opcionesAvanzadas" role="button" aria-expanded="false" aria-controls="opcionesAvanzadas">Avanzado</a>
                                </div>
                        </div>
                        <div class="row collapse" id="opcionesAvanzadas">
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <label for="cm_suaje" class="form-label">cm del suaje</label>
                                        <input class="form-control" type="number" step="0.01" name="cm_suaje" id="cm_suaje" value="<?php echo htmlspecialchars($cm_suaje_def); ?>">
                                </div>
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <label for="precio_m2" class="form-label">Precio sustrato m²</label>
                                        <input class="form-control" type="number" step="0.01" name="precio_m2" id="precio_m2" value="<?php echo htmlspecialchars($precio_m2_def); ?>">
                                </div>
                                <?php foreach($procesos_default as $proc): ?>
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <label for="proceso_<?php echo $proc['id']; ?>" class="form-label"><?php echo htmlspecialchars($proc['nombre']); ?></label>
                                        <input class="form-control" type="number" step="0.01" name="proceso[<?php echo $proc['id']; ?>]" id="proceso_<?php echo $proc['id']; ?>" value="<?php echo htmlspecialchars($procesos_valores[$proc['id']]); ?>">
                                </div>
                                <?php endforeach; ?>
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <label for="merma" class="form-label">Merma (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="merma" id="merma" value="<?php echo htmlspecialchars($merma_def); ?>">
                                </div>
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <label for="utilidad" class="form-label">Utilidad (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="utilidad" id="utilidad" value="<?php echo htmlspecialchars($utilidad_def); ?>">
                                </div>
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <label for="iva" class="form-label">IVA (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="iva" id="iva" value="<?php echo htmlspecialchars($iva_def); ?>">
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary">Cotizar</button>
                                </div>
                        </div>
                </form>
        </div>
</div>
<div class="modal fade" id="armadoModal" tabindex="-1" aria-labelledby="armadoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="armadoModalLabel">Diagrama de armado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <img id="imagen_armado_modal" src="" alt="Armado seleccionado" class="img-fluid">
      </div>
    </div>
  </div>
</div>


<?php if (!empty($similares)): ?>
<div class="card mt-3">
    <div class="card-header">
		<h5>Cajas similares</h5>
    </div>
    <div class="card-body p-0">
                <div class="table-responsive">
                        <table class="table table-sm mb-0">
                                <thead>
                                        <tr>
                                                <th>SKU</th>
                                                <th>Nombre</th>
                                                <th>Color</th>
                                                <th>Precio</th>
                                        </tr>
                                </thead>
                                <tbody>
                                        <?php foreach($similares as $c): ?>
                                                <tr>
                                                        <td><?php echo htmlspecialchars($c['SKU']); ?></td>
                                                        <td><?php echo htmlspecialchars($c['Nombre']); ?></td>
                                                        <td><?php echo htmlspecialchars($c['Color']); ?></td>
                                                        <td>$<?php echo number_format($c['Precio_Unit'], 2); ?></td>
                                                </tr>
                                        <?php endforeach; ?>
                                </tbody>
                        </table>
                </div>
    </div>
    <?php if ($mas): ?>
    <div class="card-footer text-center">
        <?php $link = $_GET; $link['limit'] = $limit + 5; ?>
        <a href="?<?php echo http_build_query($link); ?>" class="btn btn-sm btn-secondary">Ver más</a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php if (!empty($cotizacion)): ?>
<div class="card mt-3">
    <div class="card-header">
        <h5>Resultado</h5>
    </div>
    <div class="card-body">
        <p><strong>Medidas:</strong> <?php echo htmlspecialchars($l . ' x ' . $a . ' x ' . $h); ?> cm</p>
        <p><strong>Armado:</strong> <?php echo htmlspecialchars($cotizacion['armado_nombre']); ?></p>
        <p><strong>Material:</strong> <?php echo htmlspecialchars($cotizacion['material']['descripcion']); ?></p>
        <p><strong>Medidas del sustrato:</strong>
            <?php foreach ($cotizacion['datos_caja']['largo_lamina'] as $i => $ll): ?>
                <?php
                    $nombre = $cotizacion['datos_caja']['nombre'][$i] ?? 'Parte '.($i+1);
                    echo htmlspecialchars($nombre . ': ' . $ll . ' x ' . $cotizacion['datos_caja']['ancho_lamina'][$i] . ' cm');
                    if ($i < count($cotizacion['datos_caja']['largo_lamina'])-1) echo ', ';
                ?>
            <?php endforeach; ?>
        </p>
        <p><strong>Precio suaje:</strong> $<?php echo number_format($cotizacion['precio_suaje'],2); ?></p>
        <p><strong>Precio por pieza sin IVA:</strong> $<?php echo number_format($cotizacion['precio_pieza_sin_iva'],2); ?></p>
        <p><strong>Precio por pieza con IVA:</strong> $<?php echo number_format($cotizacion['precio_pieza_con_iva'],2); ?></p>
        <button class="btn btn-sm btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#desglose" aria-expanded="false" aria-controls="desglose">Ver desglose</button>
        <div class="collapse mt-3" id="desglose">
            <div class="card card-body">
                <p><strong>Área de sustrato:</strong> <?php echo number_format($cotizacion['area_m2'],4); ?> m²</p>
                <p><strong>Merma:</strong> <?php echo $cotizacion['merma']; ?>% (<?php echo number_format($cotizacion['area_m2_con_merma'] - $cotizacion['area_m2'],4); ?> m²)</p>
                <p><strong>Área con merma:</strong> <?php echo number_format($cotizacion['area_m2_con_merma'],4); ?> m²</p>
                <p><strong>Costo material por millar:</strong> $<?php echo number_format($cotizacion['costo_material_millar'],2); ?></p>
                <p><strong>Procesos:</strong></p>
                <ul>
                <?php foreach ($cotizacion['procesos'] as $proc): ?>
                    <li><?php echo htmlspecialchars($proc['nombre']); ?>: $<?php echo number_format($proc['costo'],2); ?></li>
                <?php endforeach; ?>
                </ul>
                <p><strong>Costo procesos por millar:</strong> $<?php echo number_format($cotizacion['costo_procesos_millar'],2); ?></p>
                <p><strong>Subtotal:</strong> $<?php echo number_format($cotizacion['base_millar'],2); ?></p>
                <p><strong>Utilidad (<?php echo $cotizacion['utilidad']; ?>%):</strong> $<?php echo number_format($cotizacion['utilidad_monto'],2); ?></p>
                <p><strong>Costo millar sin IVA:</strong> $<?php echo number_format($cotizacion['costo_millar_sin_iva'],2); ?></p>
                <p><strong>IVA (<?php echo $cotizacion['iva']; ?>%):</strong> $<?php echo number_format($cotizacion['iva_monto'],2); ?></p>
                <p><strong>Costo millar con IVA:</strong> $<?php echo number_format($cotizacion['costo_millar_con_iva'],2); ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const armadoSelect = document.getElementById('armado');
    const armadoImg = document.getElementById('imagen_armado_modal');
    function actualizarImagen() {
        const id = armadoSelect.value;
        armadoImg.src = 'img/' + id + '.png';
        armadoImg.alt = 'Armado ' + id;
    }
    armadoSelect.addEventListener('change', actualizarImagen);
    actualizarImagen();
});
</script>
