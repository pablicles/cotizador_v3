<?php
require_once 'funciones.php';
$armados    = get_armados($conn);
$materiales = get_materiales($conn);
$procesos   = get_procesos($conn);
$valores    = get_valores($conn);
$similares  = [];
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
                        <input type="hidden" name="cotizar" value="1">
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
                                        <button type="submit" class="btn btn-primary me-2">Cotizar</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#opciones_avanzadas">Avanzado</button>
                                </div>
                        </div>
                        <div class="collapse mt-3" id="opciones_avanzadas">
                                <div class="card card-body">
                                        <div class="row g-3">
                                                <?php foreach($procesos as $p): ?>
                                                <div class="col-6 col-lg-3">
                                                        <label class="form-label" for="proc_<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></label>
                                                        <input class="form-control" type="number" step="0.01" name="proc_<?php echo $p['id']; ?>" id="proc_<?php echo $p['id']; ?>" value="<?php echo number_format($p['precio'],2,'.',''); ?>">
                                                </div>
                                                <?php endforeach; ?>
                                                <div class="col-6 col-lg-3">
                                                        <label class="form-label" for="suaje">Suaje cm</label>
                                                        <input class="form-control" type="number" step="0.01" name="suaje" id="suaje" value="<?php echo number_format($valores['suaje'],2,'.',''); ?>">
                                                </div>
                                                <div class="col-6 col-lg-3">
                                                        <label class="form-label" for="utilidad">Utilidad %</label>
                                                        <input class="form-control" type="number" step="0.01" name="utilidad" id="utilidad" value="<?php echo number_format($valores['utilidad'],2,'.',''); ?>">
                                                </div>
                                                <div class="col-6 col-lg-3">
                                                        <label class="form-label" for="merma">Merma %</label>
                                                        <input class="form-control" type="number" step="0.01" name="merma" id="merma" value="<?php echo number_format($valores['merma'],2,'.',''); ?>">
                                                </div>
                                        </div>
                                </div>
                        </div>
                </form>
                <?php
                if (isset($_GET['cotizar'])) {
                        require 'back.php';
                        if (!empty($resultados)) {
                ?>
                <div class="mt-3">
                        <p><strong>Armado:</strong> <?php echo htmlspecialchars($resultados['resumen']['armado']); ?><br>
                        <strong>Medidas:</strong> <?php echo htmlspecialchars($resultados['resumen']['medidas']); ?><br>
                        <strong>Material:</strong> <?php echo htmlspecialchars($resultados['resumen']['material']); ?></p>
                </div>
                <div class="table-responsive">
                        <table class="table table-sm">
                                <thead>
                                        <tr>
                                                <th></th>
                                                <?php foreach($resultados['tabla'] as $vol => $vals): ?>
                                                <th><?php echo $vol; ?></th>
                                                <?php endforeach; ?>
                                        </tr>
                                </thead>
                                <tbody>
                                        <tr>
                                                <th>Precio caja sin IVA</th>
                                                <?php foreach($resultados['tabla'] as $vals): ?>
                                                <td>$<?php echo number_format($vals['caja_sin_iva'],2); ?></td>
                                                <?php endforeach; ?>
                                        </tr>
                                        <tr>
                                                <th>Precio caja con IVA</th>
                                                <?php foreach($resultados['tabla'] as $vals): ?>
                                                <td>$<?php echo number_format($vals['caja_con_iva'],2); ?></td>
                                                <?php endforeach; ?>
                                        </tr>
                                        <tr>
                                                <th>Suaje diluido sin IVA</th>
                                                <?php foreach($resultados['tabla'] as $vals): ?>
                                                <td>$<?php echo number_format($vals['suaje_sin_iva'],2); ?></td>
                                                <?php endforeach; ?>
                                        </tr>
                                        <tr>
                                                <th>Suaje diluido con IVA</th>
                                                <?php foreach($resultados['tabla'] as $vals): ?>
                                                <td>$<?php echo number_format($vals['suaje_con_iva'],2); ?></td>
                                                <?php endforeach; ?>
                                        </tr>
                                        <tr>
                                                <th>Caja + suaje sin IVA</th>
                                                <?php foreach($resultados['tabla'] as $vals): ?>
                                                <td>$<?php echo number_format($vals['total_sin_iva'],2); ?></td>
                                                <?php endforeach; ?>
                                        </tr>
                                        <tr>
                                                <th>Caja + suaje con IVA</th>
                                                <?php foreach($resultados['tabla'] as $vals): ?>
                                                <td>$<?php echo number_format($vals['total_con_iva'],2); ?></td>
                                                <?php endforeach; ?>
                                        </tr>
                                </tbody>
                        </table>
                </div>
                <p class="mt-3 mb-0"><strong>Medidas del sustrato:</strong></p>
                <ul class="mb-0">
                        <?php foreach($resultados['sustrato'] as $s): ?>
                        <li><?php echo htmlspecialchars($s); ?></li>
                        <?php endforeach; ?>
                </ul>
                <p class="mt-2"><strong>Precio del suaje:</strong> $<?php echo number_format($resultados['precio_suaje'],2); ?></p>
                <?php } }
                ?>
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
