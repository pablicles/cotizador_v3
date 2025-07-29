<?php
require_once 'funciones.php';

$resultados = [];

if (isset($_GET['cotizar'], $_GET['largo'], $_GET['ancho'], $_GET['alto'], $_GET['armado'], $_GET['material'])) {
    $largo    = (float)$_GET['largo'];
    $ancho    = (float)$_GET['ancho'];
    $alto     = (float)$_GET['alto'];
    $armado   = (int)$_GET['armado'];
    $material = $_GET['material'];

    $valores       = get_valores($conn);
    $procesos_base = get_procesos($conn);

    foreach ($procesos_base as $id => &$proc) {
        $k = 'proc_' . $id;
        if (isset($_GET[$k]) && $_GET[$k] !== '') {
            $proc['precio'] = (float)$_GET[$k];
        }
    }

    if (isset($_GET['suaje']) && $_GET['suaje'] !== '') {
        $valores['suaje'] = (float)$_GET['suaje'];
    }
    if (isset($_GET['utilidad']) && $_GET['utilidad'] !== '') {
        $valores['utilidad'] = (float)$_GET['utilidad'];
    }
    if (isset($_GET['merma']) && $_GET['merma'] !== '') {
        $valores['merma'] = (float)$_GET['merma'];
    }

    $datos         = obtener_datos_caja($armado, $largo, $ancho, $alto);
    $material_info = get_material_info($conn, $material);

    if ($material_info) {
        $m2_total        = 0.0;
        $cm_suaje_total  = 0.0;
        $sustrato_resumen = [];
        foreach ($datos['largo_lamina'] as $i => $ll) {
            $al = $datos['ancho_lamina'][$i];
            $m2_total       += ($ll / 100) * ($al / 100) * 1000;
            $cm_suaje_total += $datos['cm_suaje'][$i] * 1000;
            $nombre = $datos['nombre'][$i] ?? ('Parte ' . ($i + 1));
            $sustrato_resumen[] = $nombre . ': ' . round($ll, 2) . ' x ' . round($al, 2) . ' cm';
        }

        $m2_total      *= (1 + $valores['merma'] / 100);
        $costo_material = $m2_total * $material_info['precio_m2'];
        $costo_suaje    = $cm_suaje_total * $valores['suaje'];
        $costo_suajado  = costo_suajado($conn, $costo_suaje);

        $procesos_armado = get_procesos_armado($conn, $armado);
        $costo_procesos  = 0.0;
        foreach ($procesos_armado as $p) {
            if ($p['id'] == 5) {
                $costo_procesos += $costo_suajado;
            } else {
                $costo_procesos += $procesos_base[$p['id']]['precio'];
            }
        }

        $costo_millar      = ($costo_procesos + $costo_material) * (1 + $valores['utilidad'] / 100);
        $costo_unitario_1k = $costo_millar / 1000;
        $iva               = $valores['iva'];

        $factores = [1 => 3, 25 => 1.3, 50 => 1.22, 100 => 1.16, 200 => 1.11, 500 => 1.05, 1000 => 1];
        $tabla = [];
        foreach ($factores as $vol => $fac) {
            $caja_sin  = $costo_unitario_1k * $fac;
            $caja_con  = $caja_sin * (1 + $iva / 100);
            $suaje_sin = $costo_suaje / $vol;
            $suaje_con = $suaje_sin * (1 + $iva / 100);
            $tot_sin   = $caja_sin + $suaje_sin;
            $tot_con   = $tot_sin * (1 + $iva / 100);
            $tabla[$vol] = [
                'caja_sin_iva'  => $caja_sin,
                'caja_con_iva'  => $caja_con,
                'suaje_sin_iva' => $suaje_sin,
                'suaje_con_iva' => $suaje_con,
                'total_sin_iva' => $tot_sin,
                'total_con_iva' => $tot_con
            ];
        }

        $resultados = [
            'tabla'        => $tabla,
            'sustrato'     => $sustrato_resumen,
            'precio_suaje' => $costo_suaje,
            'resumen'      => [
                'armado'   => get_armado_nombre($conn, $armado),
                'medidas'  => $largo . ' x ' . $ancho . ' x ' . $alto . ' cm',
                'material' => $material_info['descripcion']
            ]
        ];
    }
}
?>
