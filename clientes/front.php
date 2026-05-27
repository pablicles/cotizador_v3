<?php
// Consulta segura para leer los clientes
$query = "SELECT * FROM clientes ORDER BY id DESC";
$result = $conn->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Gestión de Clientes</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clienteModal" id="btnNuevoCliente">
        <i class="fa-solid fa-user-plus me-1"></i> Nuevo Cliente
    </button>
</div>

<?php if (isset($_SESSION['mensaje_clientes'])): ?>
    <div class="alert alert-<?php echo $_SESSION['tipo_mensaje_clientes']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['mensaje_clientes']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php 
        unset($_SESSION['mensaje_clientes']);
        unset($_SESSION['tipo_mensaje_clientes']);
    ?>
<?php endif; ?>

<?php if (!$result): ?>
    <div class="alert alert-danger">
        <strong>Error SQL:</strong> <?php echo $conn->error; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nombre_empresa'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['contacto'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['telefono'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['correo'] ?? ''); ?></td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-editar me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#clienteModal"
                                            data-id="<?php echo $row['id']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($row['nombre_empresa']); ?>"
                                            data-contacto="<?php echo htmlspecialchars($row['contacto'] ?? ''); ?>"
                                            data-telefono="<?php echo htmlspecialchars($row['telefono'] ?? ''); ?>"
                                            data-correo="<?php echo htmlspecialchars($row['correo'] ?? ''); ?>"
                                            data-direccion="<?php echo htmlspecialchars($row['direccion'] ?? ''); ?>"
                                            title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="index.php?action=clientes_eliminar&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar este cliente?');" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay clientes registrados en el sistema.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal para Nuevo/Editar Cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="index.php?action=clientes_guardar" method="post" id="formCliente">
                <div class="modal-header">
                    <h5 class="modal-title" id="clienteModalLabel">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="cliente_id" value="0">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_empresa" class="form-label">Nombre de la Empresa *</label>
                            <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contacto" class="form-label">Persona de Contacto</label>
                            <input type="text" class="form-control" id="contacto" name="contacto">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección Completa</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Limpiar el formulario al presionar "Nuevo Cliente"
    document.getElementById('btnNuevoCliente').addEventListener('click', function() {
        document.getElementById('clienteModalLabel').textContent = 'Nuevo Cliente';
        document.getElementById('formCliente').reset();
        document.getElementById('cliente_id').value = '0';
    });

    // Rellenar el formulario al presionar "Editar"
    const botonesEditar = document.querySelectorAll('.btn-editar');
    botonesEditar.forEach(boton => {
        boton.addEventListener('click', function() {
            document.getElementById('clienteModalLabel').textContent = 'Editar Cliente';
            document.getElementById('cliente_id').value = this.getAttribute('data-id');
            document.getElementById('nombre_empresa').value = this.getAttribute('data-nombre');
            document.getElementById('contacto').value = this.getAttribute('data-contacto');
            document.getElementById('telefono').value = this.getAttribute('data-telefono');
            document.getElementById('correo').value = this.getAttribute('data-correo');
            document.getElementById('direccion').value = this.getAttribute('data-direccion');
        });
    });
});
</script>