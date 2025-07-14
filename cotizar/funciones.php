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
    $sql = "SELECT clave, descripcion, tipo, MIN(CASE WHEN tipo='lamina' THEN precio * 10 /(largo_max * ancho_max) ELSE precio END) AS precio_m2
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

/**
 * Calcula las dimensiones de la lámina y los centímetros de suaje
 * requeridos según el tipo de armado.
 *
 * Devuelve un arreglo de partes donde cada una incluye:
 *  - largo_lamina
 *  - ancho_lamina
 *  - cm_suaje
 *  - nombre (opcional)
 *
 * Para agregar un nuevo armado solo es necesario añadir una nueva entrada
 * en el arreglo armados dentro de la función.
 */
function obtener_datos_caja(int $armado, float $largo, float $ancho, float $alto): array
{
    $armados = [
        1 => function($L, $W, $H) {
            $l = (($L + $W) * 2) + 7;
            $a = $H + min($L, $W) + 3;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => 0,
            ]];
        },
        2 => function($L, $W, $H) {
            $l = ($L * 2) + ($W * 3);
            $a = $H + ($W * 2);
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 6) + ($a * 7),
            ]];
        },
        3 => function($L, $W, $H) {
            $l = $L + ($H * 4) + 12;
            $a = $W + ($H * 2) + 4;
            $c = ($l * 4) + ($a * 10);
            return [
                [
                    'largo_lamina' => $l,
                    'ancho_lamina' => $a,
                    'cm_suaje' => $c,
                    'nombre' => 'Base',
                ],
                [
                    'largo_lamina' => $l,
                    'ancho_lamina' => $a,
                    'cm_suaje' => $c,
                    'nombre' => 'Tapa',
                ],
            ];
        },
        4 => function($L, $W, $H) {
            $l = $L + ($H * 2) + 4;
            $a = $W + ($H * 2) + 4;
            $c = ($l * 4) + ($a * 5);
            return [
                [
                    'largo_lamina' => $l,
                    'ancho_lamina' => $a,
                    'cm_suaje' => $c,
                    'nombre' => 'Base',
                ],
                [
                    'largo_lamina' => $l,
                    'ancho_lamina' => $a,
                    'cm_suaje' => $c,
                    'nombre' => 'Tapa',
                ],
            ];
        },
        5 => function($L, $W, $H) {
            $l = (($L + $W) * 2) + 7;
            $a = $H + min($L, $W) + 3;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 4) + ($a * 7),
            ]];
        },
        6 => function($L, $W, $H) {
            $l1 = (($L + $W) * 2) + 7;
            $a1 = $H + (min($L, $W) / 2) + 3;
            $l2 = $L + 8;
            $a2 = $W + 8;
            return [
                [
                    'largo_lamina' => $l1,
                    'ancho_lamina' => $a1,
                    'cm_suaje' => 0,
                    'nombre' => 'Base',
                ],
                [
                    'largo_lamina' => $l2,
                    'ancho_lamina' => $a2,
                    'cm_suaje' => 0,
                    'nombre' => 'Tapa',
                ],
            ];
        },
        7 => function($L, $W, $H) {
            $l1 = (($L + $W) * 2) + 7;
            $a1 = $H + (min($L, $W) / 2) + 3;
            $c1 = ($l1 * 3) + ($a1 * 7);
            $l2 = $L + 8;
            $a2 = $W + 8;
            $c2 = ($l2 * 4) + ($a2 * 4);
            return [
                [
                    'largo_lamina' => $l1,
                    'ancho_lamina' => $a1,
                    'cm_suaje' => $c1,
                    'nombre' => 'Base',
                ],
                [
                    'largo_lamina' => $l2,
                    'ancho_lamina' => $a2,
                    'cm_suaje' => $c2,
                    'nombre' => 'Tapa',
                ],
            ];
        },
        8 => function($L, $W, $H) {
            $l = $L + ($H * 2) + 4;
            $a = ($H * 3) + ($W * 2) + 4;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 7) + ($a * 6),
            ]];
        },
        9 => function($L, $W, $H) {
            $l = $L + ($H * 4) + 8;
            $a = ($W * 2) + ($H * 3) + 5;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 5) + ($a * 5),
            ]];
        },
        10 => function($L, $W, $H) {
            $l = ($W * 2) + ($H * 4) + 4;
            $a = $L + ($H * 2) + 4;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 5) + ($a * 5),
            ]];
        },
        11 => function($L, $W, $H) {
            $l = ($L * 2) + ($W * 2) + 6;
            $a = $H + ($W * 2) + 4;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 4) + ($a * 5),
            ]];
        },
        12 => function($L, $W, $H) {
            $l = ($L * 2) + ($W * 2) + 6;
            $a = $H + ($W * 2) + 5;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 4) + ($a * 5),
            ]];
        },
        13 => function($L, $W, $H) {
            $l = $L + ($H * 4) + 8;
            $a = ($W * 2) + ($H * 3) + 4;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 7) + ($a * 6),
            ]];
        },
        14 => function($L, $W, $H) {
            $l = $L + ($H * 4) + 12;
            $a = $W + ($H * 2) + 4;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 4) + ($a * 10),
            ]];
        },
        15 => function($L, $W, $H) {
            $l = $L + ($H * 2) + 4;
            $a = $W + ($H * 2) + 4;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 4) + ($a * 5),
            ]];
        },
        16 => function($L, $W, $H) {
            $l = $L + ($H * 2) + 4;
            $a = $W + ($H * 2) + 5;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 4) + ($a * 4),
            ]];
        },
        17 => function($L, $W, $H) {
            $l = $L + ($H * 2) + 4;
            $a = $W + ($H * 2) + 5;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 4) + ($a * 5),
            ]];
        },
        18 => function($L, $W, $H) {
            return [[
                'largo_lamina' => $L,
                'ancho_lamina' => $W,
                'cm_suaje' => 0,
            ]];
        },
        19 => function($L, $W, $H) {
            $l = (($L + $W) * 4) + 5;
            $a = ($H + $W) + 3;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 3) + ($a * 6),
            ]];
        },
        20 => function($L, $W, $H) {
            $l = ($L * 2) + ($W * 2) + 6;
            $a = ($W * 2) + $H;
            return [[
                'largo_lamina' => $l,
                'ancho_lamina' => $a,
                'cm_suaje' => ($l * 5) + ($a * 8) + (12 * 8),
            ]];
        },
        21 => function($L, $W, $H) {
            return [[
                'largo_lamina' => $L,
                'ancho_lamina' => $W,
                'cm_suaje' => 0,
            ]];
        },
        22 => function($L, $W, $H) {
            return [[
                'largo_lamina' => $L,
                'ancho_lamina' => $W,
                'cm_suaje' => 0,
            ]];
        },
        23 => function($L, $W, $H) {
            return [[
                'largo_lamina' => $L,
                'ancho_lamina' => $W,
                'cm_suaje' => 0,
            ]];
        },
    ];

    if (isset($armados[$armado])) {
        return $armados[$armado]($largo, $ancho, $alto);
    }

    return [];
}
?>
