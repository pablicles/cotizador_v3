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
					<label for="armado">Armado</label>
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
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="cantidad">Cantidad</label>
				</div>
                                <div class="col-12 col-lg-3 mb-lg-3">
                                        <input class="form-control" type="number" name="cantidad" placeholder="1000" value="<?php echo isset($_GET['cantidad']) ? htmlspecialchars($_GET['cantidad']) : '' ?>" required>
                                </div>
                        </div>
                        <div class="row mb-3">
                                <div class="col-12 text-center">
                                        <img id="imagen_armado" src="" alt="Armado seleccionado" class="img-fluid" style="max-height:200px;">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const armadoSelect = document.getElementById('armado');
    const armadoImg = document.getElementById('imagen_armado');
    function actualizarImagen() {
        const id = armadoSelect.value;
        armadoImg.src = 'img/' + id + '.png';
        armadoImg.alt = 'Armado ' + id;
    }
    armadoSelect.addEventListener('change', actualizarImagen);
    actualizarImagen();
});
</script>
