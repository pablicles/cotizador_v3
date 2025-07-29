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
 * Obtiene el valor numérico almacenado en la tabla `valores`.
 */
function get_valor(mysqli $conn, string $nombre): float
{
    $stmt = mysqli_prepare($conn, "SELECT precio FROM valores WHERE nombre=? LIMIT 1");
    if (!$stmt) {
        return 0.0;
    }
    mysqli_stmt_bind_param($stmt, 's', $nombre);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $precio);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return (float) $precio;
}

/**
 * Obtiene el precio por metro cuadrado de un material identificado por su clave.
 */
function get_precio_m2_material(mysqli $conn, string $clave): float
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT CASE WHEN tipo='lamina' THEN precio * 10 /(largo_max * ancho_max) ELSE precio END AS precio_m2 " .
        "FROM material WHERE clave=? ORDER BY precio_m2 ASC LIMIT 1"
    );
    if (!$stmt) {
        return 0.0;
    }
    mysqli_stmt_bind_param($stmt, 's', $clave);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $precio_m2);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return (float) $precio_m2;
}

/**
 * Calcula el costo total de los procesos asociados a un armado.
 * El proceso de suajado (id 5) se determina usando la tabla rangos_suajado
 * en función del costo del suaje calculado.
 */
function get_costo_procesos(mysqli $conn, int $armado, float $costo_suaje): float
{
    $sql = "SELECT p.id, p.precio FROM procesos p JOIN armado_procesos ap ON ap.id_proceso=p.id WHERE ap.id_armado=?";
    $stmt = mysqli_prepare($conn, $sql);
    $total = 0.0;
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $armado);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            if ((int)$row['id'] === 5) {
                $q = "SELECT precio FROM rangos_suajado WHERE id_proceso=5 AND ? BETWEEN rango_inf AND rango_sup LIMIT 1";
                $stmt2 = mysqli_prepare($conn, $q);
                if ($stmt2) {
                    mysqli_stmt_bind_param($stmt2, 'd', $costo_suaje);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_bind_result($stmt2, $precio);
                    mysqli_stmt_fetch($stmt2);
                    mysqli_stmt_close($stmt2);
                    $total += (float) $precio;
                }
            } else {
                $total += (float) $row['precio'];
            }
        }
        mysqli_stmt_close($stmt);
    }
    return $total;
}

/**
 * Calcula el costo de producción para un tiro de 1000 cajas corrugadas.
 * Devuelve un arreglo con la información necesaria para mostrar el resumen.
 */
function cotizar_corrugado(mysqli $conn, int $armado, float $largo, float $ancho, float $alto, string $material): array
{
    $datos = obtener_datos_caja($armado, $largo, $ancho, $alto);

    $partes = $datos['largo_lamina'];
    $areas_cm2 = 0.0;
    $cm_suaje_total = 0.0;
    $medidas = [];

    foreach ($partes as $i => $l) {
        $ancho_l = $datos['ancho_lamina'][$i];
        $areas_cm2 += $l * $ancho_l;
        $cm_suaje_total += $datos['cm_suaje'][$i];
        $medidas[] = [
            'largo' => $l,
            'ancho' => $ancho_l,
            'nombre' => $datos['nombre'][$i] ?? ''
        ];
    }

    $area_m2 = ($areas_cm2 * 1000) / 10000; // metros cuadrados necesarios para 1000 piezas
    $merma = get_valor($conn, 'Merma');
    $area_m2 *= (1 + $merma / 100);

    $precio_m2 = get_precio_m2_material($conn, $material);
    $costo_material = $area_m2 * $precio_m2;

    $costo_suaje_por_cm = get_valor($conn, 'Suaje');
    $costo_suaje = $cm_suaje_total * $costo_suaje_por_cm;

    $costo_procesos = get_costo_procesos($conn, $armado, $costo_suaje);

    $subtotal = $costo_material + $costo_procesos;
    $utilidad = get_valor($conn, 'Utilidad');
    $precio_sin_iva = $subtotal * (1 + $utilidad / 100);

    $iva = get_valor($conn, 'iva');
    $precio_con_iva = $precio_sin_iva * (1 + $iva / 100);

    return [
        'medidas_sustrato' => $medidas,
        'cm_suaje' => $cm_suaje_total,
        'costo_suaje' => $costo_suaje,
        'precio_caja_sin_iva' => $precio_sin_iva,
        'precio_caja_con_iva' => $precio_con_iva,
        'suaje_diluido_sin_iva' => $costo_suaje,
        'suaje_diluido_con_iva' => $costo_suaje * (1 + $iva / 100),
        'caja_con_suaje_sin_iva' => $precio_sin_iva + $costo_suaje,
        'caja_con_suaje_con_iva' => ($precio_sin_iva + $costo_suaje) * (1 + $iva / 100)
    ];
}

/**
 * Obtiene las cajas del catálogo cuyas medidas son más próximas a las
 * solicitadas. Las medidas se comparan sin importar el orden en que se
 * introduzcan (largo, ancho y alto).
 */
function get_cajas_proximas(mysqli $conn, float $l, float $w, float $h, int $limit = 5): array
{
    $sql = "SELECT Nombre, Largo, Ancho, Alto FROM catalogo_productos";
    $res = mysqli_query($conn, $sql);
    $cajas = [];
    if ($res) {
        $input = [$l, $w, $h];
        sort($input);
        while ($row = mysqli_fetch_assoc($res)) {
            $medidas = [(float)$row['Largo'], (float)$row['Ancho'], (float)$row['Alto']];
            sort($medidas);
            $diff = abs($input[0] - $medidas[0]) + abs($input[1] - $medidas[1]) + abs($input[2] - $medidas[2]);
            $row['diff'] = $diff;
            $cajas[] = $row;
        }
        usort($cajas, fn($a, $b) => $a['diff'] <=> $b['diff']);
        $cajas = array_slice($cajas, 0, $limit);
    }
    return $cajas;
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
//Funcion que obtiene el tamaño de la lamina y costo del suaje de una caja
function obtener_datos_caja($armado, $largo_caja, $ancho_caja, $alto_caja){
    switch ($armado) {
        case 1: //Estandar sin suaje
            $datos_caja['largo_lamina'][0]=(($largo_caja+$ancho_caja)*2)+7;
            $datos_caja['ancho_lamina'][0]=($alto_caja+min($largo_caja, $ancho_caja))+3;
            $datos_caja['cm_suaje'][0]=0;
            return $datos_caja;
            break;

        case 2: //Boxlunch      
            $datos_caja['largo_lamina'][0]=($largo_caja*2)+($ancho_caja*3);
            $datos_caja['ancho_lamina'][0]=($alto_caja)+($ancho_caja*2);
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*6)+($datos_caja['ancho_lamina'][0]*7);
            return $datos_caja;
            break;

        case 3: //Con tapa reforzada
            $datos_caja['largo_lamina'][0]=($largo_caja)+($alto_caja*4)+12;
            $datos_caja['ancho_lamina'][0]=($ancho_caja)+($alto_caja*2)+4;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*10);
            $datos_caja['nombre'][0] ="Base";
            $datos_caja['largo_lamina'][1]=($largo_caja)+($alto_caja*4)+12;
            $datos_caja['ancho_lamina'][1]=($ancho_caja)+($alto_caja*2)+4;
            $datos_caja['cm_suaje'][1]=($datos_caja['largo_lamina'][1]*4)+($datos_caja['ancho_lamina'][1]*10);
            $datos_caja['nombre'][1] ="Tapa ";
            return $datos_caja;
            break;

        case 4: //Con tapa tipo rosca
            $datos_caja['largo_lamina'][0]=($largo_caja)+($alto_caja*2)+4;
            $datos_caja['ancho_lamina'][0]=($ancho_caja)+($alto_caja*2)+4;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*5);
            $datos_caja['nombre'][0] ="Base";
            $datos_caja['largo_lamina'][1]=($largo_caja)+($alto_caja*2)+4;
            $datos_caja['ancho_lamina'][1]=($ancho_caja)+($alto_caja*2)+4;
            $datos_caja['cm_suaje'][1]=($datos_caja['largo_lamina'][1]*4)+($datos_caja['ancho_lamina'][1]*5);
            $datos_caja['nombre'][1] ="Tapa ";
            return $datos_caja;
            break;

        case 5: //Caja estandar suajada
            $datos_caja['largo_lamina'][0]=(($largo_caja+$ancho_caja)*2)+7;
            $datos_caja['ancho_lamina'][0]=($alto_caja+min($largo_caja, $ancho_caja))+3;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*7);
            return $datos_caja;
            break;

        case 6: //Estandar con tapa sin suaje
            $datos_caja['largo_lamina'][0]=(($largo_caja+$ancho_caja)*2)+7; //Caja
            $datos_caja['ancho_lamina'][0]=($alto_caja+(min($largo_caja, $ancho_caja))/2)+3;
            $datos_caja['cm_suaje'][0]=0;
            $datos_caja['nombre'][0] ="Base";
            $datos_caja['largo_lamina'][1]=$largo_caja+8; //Tapa
            $datos_caja['ancho_lamina'][1]=$ancho_caja+8;
            $datos_caja['cm_suaje'][1]=0;
            $datos_caja['nombre'][1] ="Tapa";
            return $datos_caja;
            break;

        case 7: //Estandar con tapa suajada
            $datos_caja['largo_lamina'][0]=(($largo_caja+$ancho_caja)*2)+7; //Caja
            $datos_caja['ancho_lamina'][0]=($alto_caja+(min($largo_caja, $ancho_caja))/2)+3;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*3)+($datos_caja['ancho_lamina'][0]*7);
            $datos_caja['nombre'][0] ="Base";
            $datos_caja['largo_lamina'][1]=$largo_caja+8; //Tapa
            $datos_caja['ancho_lamina'][1]=$ancho_caja+8;
            $datos_caja['cm_suaje'][1]=($datos_caja['largo_lamina'][1]*4)+($datos_caja['ancho_lamina'][1]*4);
            $datos_caja['nombre'][1] ="Tapa ";
            return $datos_caja;
            break;

        case 8: //Tipo dona
            $datos_caja['largo_lamina'][0]=($largo_caja)+($alto_caja*2)+4;
            $datos_caja['ancho_lamina'][0]=($alto_caja*3)+($ancho_caja*2)+4;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*7)+($datos_caja['ancho_lamina'][0]*6);
            return $datos_caja;
            break;

        case 9: //Tipo mailbox
            $datos_caja['largo_lamina'][0]=($largo_caja)+($alto_caja*4)+8;
            $datos_caja['ancho_lamina'][0]=($ancho_caja*2)+($alto_caja*3)+5;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*5)+($datos_caja['ancho_lamina'][0]*5);
            return $datos_caja;
            break;

        case 10: //Tipo pizza
            $datos_caja['largo_lamina'][0]=($ancho_caja*2)+($alto_caja*4)+4;
            $datos_caja['ancho_lamina'][0]=$largo_caja+($alto_caja*2)+4;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*5)+($datos_caja['ancho_lamina'][0]*5);
            return $datos_caja;
            break;

        case 11: //Taza autoarmable
            $datos_caja['largo_lamina'][0]=($largo_caja*2)+($ancho_caja*2)+6;
            $datos_caja['ancho_lamina'][0]=($alto_caja)+($ancho_caja*2)+4;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*5);
            return $datos_caja;
            break;

        case 12: //Taza pegada
            $datos_caja['largo_lamina'][0]=($largo_caja*2)+($ancho_caja*2)+6;
            $datos_caja['ancho_lamina'][0]=$alto_caja+($ancho_caja*2)+5;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*5);
            return $datos_caja;
            break;

        case 13: //Tipo zapatos
            $datos_caja['largo_lamina'][0]=$largo_caja+($alto_caja*4)+8;
            $datos_caja['ancho_lamina'][0]=($ancho_caja*2)+($alto_caja*3)+4;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*7)+($datos_caja['ancho_lamina'][0]*6);
            return $datos_caja;
            break;

        case 14: //Charola reforzada
            $datos_caja['largo_lamina'][0]=($largo_caja)+($alto_caja*4)+12;
            $datos_caja['ancho_lamina'][0]=($ancho_caja)+($alto_caja*2)+4;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*10);
            return $datos_caja;
            break;

        case 15: //Charola tipo rosca
            $datos_caja['largo_lamina'][0]=($largo_caja)+($alto_caja*2)+4;
            $datos_caja['ancho_lamina'][0]=($ancho_caja)+($alto_caja*2)+4;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*5);
            return $datos_caja;
            break;

        case 16: //Inserto sencillo
            $datos_caja['largo_lamina'][0]=$largo_caja+($alto_caja*2)+4;
            $datos_caja['ancho_lamina'][0]=$ancho_caja+($alto_caja*2)+5;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*4);
            return $datos_caja;
            break;

        case 17: //Inserto con pestañas
            $datos_caja['largo_lamina'][0]=$largo_caja+($alto_caja*2)+4;
            $datos_caja['ancho_lamina'][0]=$ancho_caja+($alto_caja*2)+5;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*4)+($datos_caja['ancho_lamina'][0]*5);
            return $datos_caja;
            break;

        case 18: //Lámina
            $datos_caja['largo_lamina'][0]=$largo_caja;
            $datos_caja['ancho_lamina'][0]=$ancho_caja;
            $datos_caja['cm_suaje'][0]=0;
            return $datos_caja;
            break;

        case 19: //Palomitas
            $datos_caja['largo_lamina'][0]=(($largo_caja+$ancho_caja)*4)+5;
            $datos_caja['ancho_lamina'][0]=($alto_caja+$ancho_caja)+3;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*3)+($datos_caja['ancho_lamina'][0]*6);
            return $datos_caja;
            break;

        case 20: //Bolsas
            $datos_caja['largo_lamina'][0]=($largo_caja*2)+($ancho_caja*2)+6;
            $datos_caja['ancho_lamina'][0]=($ancho_caja*2)+$alto_caja;
            $datos_caja['cm_suaje'][0]=($datos_caja['largo_lamina'][0]*5)+($datos_caja['ancho_lamina'][0]*8)+(12*8);
            return $datos_caja;
            break;

        case 21: //Lámina
            $datos_caja['largo_lamina'][0]=$largo_caja;
            $datos_caja['ancho_lamina'][0]=$ancho_caja;
            $datos_caja['cm_suaje'][0]=0;
            return $datos_caja;
            break;

        case 22: //Lámina
            $datos_caja['largo_lamina'][0]=$largo_caja;
            $datos_caja['ancho_lamina'][0]=$ancho_caja;
            $datos_caja['cm_suaje'][0]=0;
            return $datos_caja;
            break;

        case 23: //Lámina
            $datos_caja['largo_lamina'][0]=$largo_caja;
            $datos_caja['ancho_lamina'][0]=$ancho_caja;
            $datos_caja['cm_suaje'][0]=0;
            return $datos_caja;
            break;
        
        default:
            // code...
            return $datos_caja;
            break;
    }
}
?>
