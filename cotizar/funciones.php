<?php
function get_armados(mysqli $conn){
    $sql = "SELECT id, nombre FROM armado ORDER BY nombre ASC";
    $res = mysqli_query($conn, $sql);
    $armados = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $armados[] = $row;
        }
    }
    return $armados;
}

function get_materiales(mysqli $conn){
    $sql = "SELECT clave, tipo, MIN(CASE WHEN tipo='lamina' THEN precio * 10 /(largo_max * ancho_max) ELSE precio END) AS precio_m2
            FROM material
            GROUP BY clave, tipo
            ORDER BY CASE WHEN tipo='metro' THEN 0 ELSE 1 END, precio_m2";
    $res = mysqli_query($conn, $sql);
    $materiales = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $materiales[] = $row;
        }
    }
    return $materiales;
}
?>
