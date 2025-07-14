<?php
require_once 'funciones.php';
$armados = get_armados($conn);
$materiales = get_materiales($conn);
?>
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col">
				<h5>Caja</h5>
			</div>
		</div>
		<form class="form">
			<div class="row">
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="largo" class="form-label">Largo</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<input class="form-control" type="number" name="largo" placeholder="Largo" required>
				</div>
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="ancho" class="form-label">Ancho</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<input class="form-control" type="number" name="ancho" placeholder="Ancho" required>
				</div>
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="alto" class="form-label">Alto</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<input class="form-control" type="number" name="alto" placeholder="Alto" required>
				</div>
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="armado">Armado</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<select class="form-control" name="armado" id="armado">
						<?php foreach($armados as $a): ?>
							<option value="<?php echo $a['id']; ?>" <?php if($a['nombre']==='Estandar (Manual sin suaje)') echo 'selected'; ?>>
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
                    <?php echo htmlspecialchars($m['clave']) . " - $" . number_format($m['precio_m2'], 2) . "/m²"; ?>
                </option>
            <?php endforeach; ?>
        </select>
				</div>
				<div class="col-12 col-lg-1 mb-lg-3">
					<label for="cantidad">Cantidad</label>
				</div>
				<div class="col-12 col-lg-3 mb-lg-3">
					<input class="form-control" type="number" name="cantidad" placeholder="1000" required>
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
