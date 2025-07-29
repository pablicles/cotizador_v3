<?php
require_once 'funciones.php';
$armados = get_armados($conn);
$materiales = get_materiales($conn);
$similares = [];
if (isset($_GET['largo'], $_GET['ancho'], $_GET['alto'])) {
    $l = (float)$_GET['largo'];
    $a = (float)$_GET['ancho'];
    $h = (float)$_GET['alto'];
    $similares = get_cajas_proximas($conn, $l, $a, $h);
}
require 'back.php';
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
						<?php foreach($armados as $a): ?>
							<option value="<?php echo $a['id']; ?>" <?php if($a['id']==1) echo 'selected'; ?>>
								<?php echo htmlspecialchars($a['nombre']); ?>
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
			                <option value="<?php echo $m['clave']; ?>">
			                    <?php echo htmlspecialchars($m['descripcion']) . " - $" . number_format($m['precio_m2'], 2) . "/m²"; ?>
			                </option>
			            <?php endforeach; ?>
			        </select>
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
						<th>Nombre</th>
						<th>Medidas (cm)</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($similares as $c): ?>
						<tr>
							<td><?php echo htmlspecialchars($c['Nombre']); ?></td>
							<td><?php echo htmlspecialchars($c['Largo'] . ' x ' . $c['Ancho'] . ' x ' . $c['Alto']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
    </div>
</div>
<?php endif; ?>

<?php if ($resultado_cotizacion): ?>
<div class="card mt-3">
    <div class="card-header">
        <h5>Resultado de la cotizaci&oacute;n</h5>
    </div>
    <div class="card-body">
        <?php
            $idxArm = array_search($armado, array_column($armados, 'id'));
            $nombreArm = $idxArm !== false ? $armados[$idxArm]['nombre'] : '';
            $idxMat = array_search($material, array_column($materiales, 'clave'));
            $descMat = $idxMat !== false ? $materiales[$idxMat]['descripcion'] : $material;
        ?>
        <p><strong>Armado:</strong> <?php echo htmlspecialchars($nombreArm); ?></p>
        <p><strong>Medidas:</strong> <?php echo htmlspecialchars($_GET['largo'] . ' x ' . $_GET['ancho'] . ' x ' . $_GET['alto']) . ' cm'; ?></p>
        <p><strong>Material:</strong> <?php echo htmlspecialchars($descMat); ?></p>
        <img src="img/<?php echo $armado; ?>.png" alt="Armado" class="img-fluid mb-3" style="max-width:200px;">

        <h6>Medidas del sustrato</h6>
        <ul>
            <?php foreach ($resultado_cotizacion['medidas_sustrato'] as $p): ?>
                <li><?php echo ($p['nombre'] ? htmlspecialchars($p['nombre']) . ': ' : '') . number_format($p['largo'],2) . ' x ' . number_format($p['ancho'],2) . ' cm'; ?></li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Suaje calculado:</strong> <?php echo number_format($resultado_cotizacion['cm_suaje'],2); ?> cm - $<?php echo number_format($resultado_cotizacion['costo_suaje'],2); ?></p>

        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th></th>
                        <th>1000</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Precio de la caja sin IVA</td>
                        <td>$<?php echo number_format($resultado_cotizacion['precio_caja_sin_iva'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Precio de la caja con IVA</td>
                        <td>$<?php echo number_format($resultado_cotizacion['precio_caja_con_iva'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Precio del suaje diluido sin IVA</td>
                        <td>$<?php echo number_format($resultado_cotizacion['suaje_diluido_sin_iva'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Precio del suaje diluido con IVA</td>
                        <td>$<?php echo number_format($resultado_cotizacion['suaje_diluido_con_iva'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Precio de la caja + suaje diluido sin IVA</td>
                        <td>$<?php echo number_format($resultado_cotizacion['caja_con_suaje_sin_iva'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Precio de la caja + suaje diluido con IVA</td>
                        <td>$<?php echo number_format($resultado_cotizacion['caja_con_suaje_con_iva'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
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
