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
$sobrantes_def = get_valor($conn, 'Sobrantes');
$chk          = $_GET['chk'] ?? [];

$precio_m2_def = 0;
$material_info_sel = [];
if ($selected_material) {
    $material_info_sel = get_material_info($conn, $selected_material);
    if ($material_info_sel) {
        $precio_m2_def = $material_info_sel['precio_m2'];
    }
}
$material_tipo_sel = $material_info_sel['tipo'] ?? '';

$cm_suaje_def   = 0;
$datos_caja_tmp = [];
if (isset($_GET['largo'], $_GET['ancho'], $_GET['alto'])) {
    $datos_caja_tmp = obtener_datos_caja($selected_armado, (float)$_GET['largo'], (float)$_GET['ancho'], (float)$_GET['alto']);
    foreach ($datos_caja_tmp['cm_suaje'] as $c) {
        $cm_suaje_def += $c;
    }
}

if (!empty($chk['cm_suaje']) && isset($_GET['cm_suaje'])) {
    $cm_suaje_def = (float)$_GET['cm_suaje'];
}
if (!empty($chk['precio_m2']) && isset($_GET['precio_m2'])) {
    $precio_m2_def = (float)$_GET['precio_m2'];
}
if (!empty($chk['merma']) && isset($_GET['merma'])) {
    $merma_def = (float)$_GET['merma'];
}
if (!empty($chk['utilidad']) && isset($_GET['utilidad'])) {
    $utilidad_def = (float)$_GET['utilidad'];
}
if (!empty($chk['iva']) && isset($_GET['iva'])) {
    $iva_def = (float)$_GET['iva'];
}
if (!empty($chk['sobrantes']) && isset($_GET['sobrantes'])) {
    $sobrantes_def = (float)$_GET['sobrantes'];
}

$procesos_valores = [];
foreach ($procesos_default as $p) {
    $valor = $p['precio'];
    if (!empty($chk['procesos'][$p['id']]) && isset($_GET['proceso'][$p['id']])) {
        $valor = (float)$_GET['proceso'][$p['id']];
    }
    $procesos_valores[$p['id']] = $valor;
}

$similares      = [];
$cotizacion     = [];
$mas            = false;
$error_medidas  = false;
if (isset($_GET['largo'], $_GET['ancho'], $_GET['alto'])) {
    $l = (float)$_GET['largo'];
    $a = (float)$_GET['ancho'];
    $h = (float)$_GET['alto'];
    $limit = isset($_GET['limit']) ? max(5, (int)$_GET['limit']) : 5;
    $tmp_res   = get_cajas_proximas($conn, $l, $a, $h, $limit + 1);
    $similares = array_slice($tmp_res, 0, $limit);
    $mas       = count($tmp_res) > $limit;
    if ($selected_armado && $selected_material) {
        $largo_max = $material_info_sel['largo_max'] ?? 0;
        $ancho_max = $material_info_sel['ancho_max'] ?? 0;
        foreach ($datos_caja_tmp['largo_lamina'] as $i => $ll) {
            $al = $datos_caja_tmp['ancho_lamina'][$i];
            if (!(($ll <= $largo_max && $al <= $ancho_max) || ($al <= $largo_max && $ll <= $ancho_max))) {
                $error_medidas = true;
                break;
            }
        }
        if (!$error_medidas) {
            $opciones = [];
            if (!empty($chk['cm_suaje'])) {
                $opciones['cm_suaje'] = $cm_suaje_def;
            }
            if (!empty($chk['precio_m2'])) {
                $opciones['precio_m2'] = $precio_m2_def;
            }
            if (!empty($chk['merma'])) {
                $opciones['merma'] = $merma_def;
            }
            if (!empty($chk['utilidad'])) {
                $opciones['utilidad'] = $utilidad_def;
            }
            if (!empty($chk['sobrantes'])) {
                $opciones['sobrantes'] = $sobrantes_def;
            }
            if (!empty($chk['iva'])) {
                $opciones['iva'] = $iva_def;
            }
            $proc_ops = [];
            foreach ($procesos_default as $p) {
                if (!empty($chk['procesos'][$p['id']])) {
                    $proc_ops[$p['id']] = $procesos_valores[$p['id']];
                }
            }
            if ($proc_ops) {
                $opciones['procesos'] = $proc_ops;
            }
            if ($material_tipo_sel === 'lamina') {
                $cotizacion = cotizar_lamina($conn, $selected_armado, $l, $a, $h, $selected_material, $opciones);
            } else {
                $cotizacion = cotizar_corrugado($conn, $selected_armado, $l, $a, $h, $selected_material, $opciones);
            }
        }
    }
}
?>
<div class="card">
	<div class="card-header">
		<h5>Información de la caja</h5>
		
	</div>
	<div class="card-body">
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
					<div class="form-check small">
						<input class="form-check-input" type="checkbox" id="chk_cm_suaje" name="chk[cm_suaje]" <?php echo !empty($chk['cm_suaje']) ? 'checked' : ''; ?>>
						<label class="form-check-label" for="chk_cm_suaje">Usar</label>
					</div>
					<label for="cm_suaje" class="form-label">cm del suaje</label>
					<input class="form-control" type="number" step="0.01" name="cm_suaje" id="cm_suaje" value="<?php echo htmlspecialchars($cm_suaje_def); ?>" data-check-target="chk_cm_suaje">
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<div class="form-check small">
						<input class="form-check-input" type="checkbox" id="chk_precio_m2" name="chk[precio_m2]" <?php echo !empty($chk['precio_m2']) ? 'checked' : ''; ?>>
						<label class="form-check-label" for="chk_precio_m2">Usar</label>
					</div>
					<label for="precio_m2" class="form-label">Precio sustrato m²</label>
					<input class="form-control" type="number" step="0.01" name="precio_m2" id="precio_m2" value="<?php echo htmlspecialchars($precio_m2_def); ?>" data-check-target="chk_precio_m2">
				</div>
				<?php foreach($procesos_default as $proc): ?>
					<div class="col-12 col-lg-3 mb-lg-3">
						<div class="form-check small">
							<input class="form-check-input" type="checkbox" id="chk_proc_<?php echo $proc['id']; ?>" name="chk[procesos][<?php echo $proc['id']; ?>]" <?php echo !empty($chk['procesos'][$proc['id']]) ? 'checked' : ''; ?>>
							<label class="form-check-label" for="chk_proc_<?php echo $proc['id']; ?>">Usar</label>
						</div>
						<label for="proceso_<?php echo $proc['id']; ?>" class="form-label"><?php echo htmlspecialchars($proc['nombre']); ?></label>
						<input class="form-control" type="number" step="0.01" name="proceso[<?php echo $proc['id']; ?>]" id="proceso_<?php echo $proc['id']; ?>" value="<?php echo htmlspecialchars($procesos_valores[$proc['id']]); ?>" data-check-target="chk_proc_<?php echo $proc['id']; ?>">
					</div>
				<?php endforeach; ?>
				<div class="col-12 col-lg-3 mb-lg-3">
					<div class="form-check small">
						<input class="form-check-input" type="checkbox" id="chk_merma" name="chk[merma]" <?php echo !empty($chk['merma']) ? 'checked' : ''; ?>>
						<label class="form-check-label" for="chk_merma">Usar</label>
					</div>
					<label for="merma" class="form-label">Merma (%)</label>
					<input class="form-control" type="number" step="0.01" name="merma" id="merma" value="<?php echo htmlspecialchars($merma_def); ?>" data-check-target="chk_merma">
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<div class="form-check small">
						<input class="form-check-input" type="checkbox" id="chk_utilidad" name="chk[utilidad]" <?php echo !empty($chk['utilidad']) ? 'checked' : ''; ?>>
						<label class="form-check-label" for="chk_utilidad">Usar</label>
					</div>
					<label for="utilidad" class="form-label">Utilidad (%)</label>
					<input class="form-control" type="number" step="0.01" name="utilidad" id="utilidad" value="<?php echo htmlspecialchars($utilidad_def); ?>" data-check-target="chk_utilidad">
				</div>
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <div class="form-check small">
                                                <input class="form-check-input" type="checkbox" id="chk_sobrantes" name="chk[sobrantes]" <?php echo !empty($chk['sobrantes']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="chk_sobrantes">Usar</label>
                                        </div>
                                        <label for="sobrantes" class="form-label">Sobrantes (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="sobrantes" id="sobrantes" value="<?php echo htmlspecialchars($sobrantes_def); ?>" data-check-target="chk_sobrantes">
                                </div>
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <div class="form-check small">
                                                <input class="form-check-input" type="checkbox" id="chk_iva" name="chk[iva]" <?php echo !empty($chk['iva']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="chk_iva">Usar</label>
                                        </div>
                                        <label for="iva" class="form-label">IVA (%)</label>
                                        <input class="form-control" type="number" step="0.01" name="iva" id="iva" value="<?php echo htmlspecialchars($iva_def); ?>" data-check-target="chk_iva">
                                </div>
			</div>
			<div class="row">
				<div class="col-12 text-right">
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
    <div class="card-footer text-right">
        <?php $link = $_GET; $link['limit'] = $limit + 5; ?>
        <a href="?<?php echo http_build_query($link); ?>" class="btn btn-sm btn-secondary">Ver más</a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php if (!empty($cotizacion)): ?>
<div class="card mt-3">
    <div class="card-header">
    	<h5>Cotización</h5>
    </div>
    <div class="card-body">
    	<p>
	        <strong>
	        	Caja 
	        	<?php echo htmlspecialchars($cotizacion['armado_nombre']); ?> de 
	        	<?php echo htmlspecialchars($l . ' x ' . $a . ' x ' . $h); ?> cm en
	        	<?php echo htmlspecialchars($cotizacion['material']['descripcion']); ?>
	        </strong>
    	</p>
        <p>
                Lamina:
            <?php foreach ($cotizacion['datos_caja']['largo_lamina'] as $i => $ll): ?>
                <?php
                    $nombre = $cotizacion['datos_caja']['nombre'][$i] ?? 'Parte '.($i+1);
                    echo htmlspecialchars($nombre . ': ' . $ll . ' x ' . $cotizacion['datos_caja']['ancho_lamina'][$i] . ' cm');
                    if ($i < count($cotizacion['datos_caja']['largo_lamina'])-1) echo ', ';
                ?>
            <?php endforeach; ?>
        </p>
        <p><strong>Suaje:</strong> $<?php echo number_format($cotizacion['precio_suaje'],2); ?></p>
        <p><strong>Precio por pieza sin IVA:</strong> $<?php echo number_format($cotizacion['precio_pieza_sin_iva'],2); ?></p>
        <p><strong>Precio por pieza con IVA:</strong> $<?php echo number_format($cotizacion['precio_pieza_con_iva'],2); ?></p>
        <table class="table table-bordered table-striped">
                <tr>
                        <th>IVA</th>
                        <th>Suaje</th>
                        <th>1</th>
        		<th>25</th>
        		<th>50</th>
        		<th>100</th>
        		<th>200</th>
        		<th>500</th>
        		<th>>1000</th>
        	</tr>
        	<tr>
        		<td>Sin IVA</td>
        		<td>$<?php echo number_format($cotizacion['precio_suaje'],2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_sin_iva']*3,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_sin_iva']*1.3,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_sin_iva']*1.22,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_sin_iva']*1.16,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_sin_iva']*1.11,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_sin_iva']*1.05,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_sin_iva'],2); ?></td>
        	</tr>
        	<tr>
        		<td>Con IVA</td>
        		<td>$<?php echo number_format($cotizacion['precio_suaje']*1.16,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_con_iva']*3,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_con_iva']*1.3,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_con_iva']*1.22,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_con_iva']*1.16,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_con_iva']*1.11,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_con_iva']*1.05,2); ?></td>
        		<td>$<?php echo number_format($cotizacion['precio_pieza_con_iva'],2); ?></td>
        	</tr>
        </table>
    </div>
    <div class="card-footer">
        <div class="collapse mt-3" id="desglose">
            <div class="card card-body">
                <?php if($cotizacion['material']['tipo'] === 'lamina'): ?>
                    <p><strong>Merma:</strong> <?php echo $cotizacion['merma']; ?>%</p>
                    <?php foreach($cotizacion['laminas'] as $lam): ?>
                        <p><strong><?php echo htmlspecialchars($lam['nombre']); ?></strong></p>
                        <ul>
                            <li>Tamaño compra: <?php echo $lam['tam_largo']; ?> x <?php echo $lam['tam_ancho']; ?> cm</li>
                            <li>Provechos por tamaño: <?php echo $lam['provechos']; ?></li>
                            <li>Tamaños necesarios: <?php echo $lam['laminas_necesarias']; ?></li>
                            <li>Tamaños con merma: <?php echo $lam['laminas_merma']; ?></li>
                            <li>Precio tamaño compra con IVA: $<?php echo number_format($lam['precio_unit_iva'],2); ?></li>
                            <li>Costo sustrato: $<?php echo number_format($lam['costo'],2); ?></li>
                        </ul>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><strong>Área de sustrato:</strong> <?php echo number_format($cotizacion['area_m2'],4); ?> m²</p>
                    <p><strong>Merma:</strong> <?php echo $cotizacion['merma']; ?>% (<?php echo number_format($cotizacion['area_m2_con_merma'] - $cotizacion['area_m2'],4); ?> m²)</p>
                    <p><strong>Área con merma:</strong> <?php echo number_format($cotizacion['area_m2_con_merma'],4); ?> m²</p>
                <?php endif; ?>
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
                <p><strong>Sobrantes (<?php echo $cotizacion['sobrantes']; ?>%):</strong> $<?php echo number_format($cotizacion['sobrantes_monto'],2); ?></p>
                <p><strong>Costo millar sin IVA:</strong> $<?php echo number_format($cotizacion['costo_millar_sin_iva'],2); ?></p>
                <p><strong>IVA (<?php echo $cotizacion['iva']; ?>%):</strong> $<?php echo number_format($cotizacion['iva_monto'],2); ?></p>
                <p><strong>Costo millar con IVA:</strong> $<?php echo number_format($cotizacion['costo_millar_con_iva'],2); ?></p>
            </div>
        </div>
        <button class="btn btn-sm btn-secondary float-right" type="button" data-bs-toggle="collapse" data-bs-target="#desglose" aria-expanded="false" aria-controls="desglose">Detalles</button>
    </div>
</div>
<?php elseif ($error_medidas && isset($_GET['largo'], $_GET['ancho'], $_GET['alto'])): ?>
<div class="alert alert-danger mt-3">Las medidas calculadas del sustrato exceden el tamaño máximo del material seleccionado.</div>
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

    document.querySelectorAll('[data-check-target]').forEach(function(el){
        el.addEventListener('input', function(){
            const cb = document.getElementById(el.dataset.checkTarget);
            if(cb){
                cb.checked = true;
            }
        });
    });
});
</script>
