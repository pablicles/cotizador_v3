<?php 
//Archivo de conexión con la BD

	$server="localhost";
	$username="root";
	$password="";
	$db="cajas_v2";

	/*$server="localhost";
	$username="u707159538_admin";
	$password="cmwangG]N6";
	$db="u707159538_cotizador";*/

	$conn=mysqli_connect($server, $username, $password, $db);
	if (!$conn) {
	    die('Error de Conexión (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
	}
?>