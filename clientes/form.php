<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cliente = [
    'nombre_empresa' => '',
    'contacto' => '',
    'telefono' => '',
    'correo' => '',
    'direccion' => ''
];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $cliente = $row;
        }
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><?php echo $id > 0 ? 'Editar Cliente' : 'Nuevo Cliente'; ?></h2>
    <a href="index.php?action=clientes" class="btn btn-secondary">Volver</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="index.php?action=clientes_guardar" method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre_empresa" class="form-label">Nombre de la Empresa *</label>
                    <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" value="<?php echo htmlspecialchars($cliente['nombre_empresa'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="contacto" class="form-label">Persona de Contacto</label>
                    <input type="text" class="form-control" id="contacto" name="contacto" value="<?php echo htmlspecialchars($cliente['contacto'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($cliente['correo'] ?? ''); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección Completa</label>
                <textarea class="form-control" id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success">Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>