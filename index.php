<?php
session_start();
require 'conn.php';
$action = $_GET['action'] ?? 'cotizar';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
        <link rel="icon" href="img/logo_cotizador.png">
        <title>Cotizador | Inicio</title>
</head>
<body class="sidebar-mini layout-navbar-fixed layout-fixed sidebar-collapse">
	<div class="wrapper">
		<!-- Navbar -->
		<nav class="main-header navbar navbar-expand navbar-white navbar-light">
		  <!-- Left navbar links -->
		  <ul class="navbar-nav">
		    <li class="nav-item">
		      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
		    </li>
		    <li class="nav-item d-none d-sm-inline-block">
		      <a href="index.php" class="nav-link">Inicio</a>
		    </li>
		    <li class="nav-item d-none d-sm-inline-block">
		      <a href="?action=cotizar" class="nav-link <?php if($action=="cotizar"){print("active");} ?>">Cotizar</a>
		    </li>
		    <li class="nav-item d-none d-sm-inline-block">
		      <a href="files/Catalogo.pdf" class="nav-link" target="_blank">Catalogo</a>
		    </li>
		  </ul>
		  <!-- Right navbar icons -->
                  <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                                <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                                        <i class="fa-solid fa-cloud"></i>
                                </a>
                        </li>
                        <li class="nav-item">
                                <a href="logout.php" class="nav-link">Cerrar sesión</a>
                        </li>
                  </ul>
                </nav>
		<!-- /.navbar -->

		<!-- Main Sidebar Container -->
		<aside class="main-sidebar sidebar-dark-primary elevation-4">
		  <!-- Brand Logo -->
                  <a href="index.php" class="brand-link">
                    <img src="img/logo_cotizador.png" alt="Cotizador Logo" class="brand-image"
                         style="opacity: .8">
                    <span class="brand-text font-weight-light">Cotizador 3</span>
                  </a>

                  <!-- Sidebar -->
                  <div class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                      <div class="image">
                        <img src="img/perfil/<?php echo $_SESSION['usuario_id']; ?>.png" class="img-circle elevation-2" alt="Usuario">
                      </div>
                      <div class="info">
                        <a href="#" class="d-block">
                          <?php echo htmlspecialchars($_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido']); ?>
                        </a>
                      </div>
                    </div>

		    <!-- Sidebar Menu -->
		    <nav class="mt-2">
		      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
		        <li class="nav-item">
		          <a href="index.php?action=cotizar" class="nav-link">
		            <i class="nav-icon fa-solid fa-calculator"></i>
		            <p>Cotizaciones</p>
		          </a>
		        </li>
                        <li class="nav-item">
                          <a href="index.php?action=registro_ventas" class="nav-link">
                            <i class="nav-icon fa-solid fa-cart-shopping"></i>
                            <p>Ventas</p>
                          </a>
                        </li>
                        <?php if ($_SESSION['usuario_admin'] === 's'): ?>
                        <li class="nav-item">
                          <a href="index.php?action=dashboard" class="nav-link">
                            <i class="nav-icon fa-solid fa-chart-column"></i>
                            <p>Dashboard</p>
                          </a>
                        </li>
                        <?php endif; ?>
		      </ul>
		    </nav>
		    <!-- /.sidebar-menu -->
		  <!-- /.sidebar -->
		  </div>
		</aside>

		<!-- Right sidebar menu-->
		<aside class="control-sidebar control-sidebar-dark" style="display: block;">
			<div class="p-3 control-sidebar-content" style="">
			<h5>Customize AdminLTE</h5>
		</aside>
		<!-- /.Right sidebar menu-->

		<div class="content-wrapper" style="min-height: 1172.8px;">
			<section class="content-header">
				<div class="container-fluid">
					<?php 
						switch ($action) {
							case 'cotizar':
								require 'cotizar/front.php';
								break;

                                                        case 'registro_ventas':
                                                                require 'ventas/front.php';
                                                                break;

                                                        case 'dashboard':
                                                                require 'dashboard/front.php';
                                                                break;

							case 'ver_tabla':
								require 'tablas/front.php';
								break;
							
							default:
								require 'cotizar/front.php';
								break;
						}
					?>
				</div>
			</section>
		</div>
		<footer class="main-footer">
			Millop 2024. Por Pablo Miranda
		</footer>
	</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script>
  //Inicializar data tables
  $(document).ready(function() {
      $('#datatable').DataTable();
  } );

//Cerrar alerts
  $(".alert-dismissible").delay(7000).slideUp(200, function() {
    $(this).alert('close');
});
  //Inicializar toltips
  $(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

