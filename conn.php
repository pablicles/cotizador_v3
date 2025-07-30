<?php 
//Archivo de conexión con la BD

	$server="localhost";
	$username="root";
	$password="";
	$db="cajas_v3";

	/*$server="localhost";
	$username="u707159538_cotizador3";
	$password="l!A1gg26!]I2";
	$db="u707159538_cotizador3";*/

	$conn=mysqli_connect($server, $username, $password, $db);
	if (!$conn) {
	    die('Error de Conexión (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
	}
?>