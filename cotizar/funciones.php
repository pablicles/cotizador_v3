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

function get_procesos(mysqli $conn): array {
    $res = mysqli_query($conn, "SELECT id, nombre, precio FROM procesos");
    $procesos = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $procesos[$row['id']] = $row;
        }
    }
    return $procesos;
}

function get_procesos_armado(mysqli $conn, int $armado): array {
    $stmt = mysqli_prepare($conn, "SELECT p.id, p.nombre FROM armado_procesos ap JOIN procesos p ON ap.id_proceso=p.id WHERE ap.id_armado=?");
    $procesos = [];
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $armado);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $procesos[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    return $procesos;
}

function get_valores(mysqli $conn): array {
    $res = mysqli_query($conn, "SELECT nombre, precio FROM valores");
    $valores = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $valores[strtolower($row['nombre'])] = (float)$row['precio'];
        }
    }
    return $valores;
}

function get_material_info(mysqli $conn, string $clave): ?array {
    $stmt = mysqli_prepare($conn, "SELECT tipo, descripcion, CASE WHEN tipo='lamina' THEN precio*10/(largo_max*ancho_max) ELSE precio END AS precio_m2 FROM material WHERE clave=? ORDER BY precio_m2 ASC LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $clave);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if ($row) return $row;
    }
    return null;
}

function get_armado_nombre(mysqli $conn, int $id): string {
    $stmt = mysqli_prepare($conn, "SELECT nombre FROM armado WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if ($row) return $row['nombre'];
    }
    return '';
}

function costo_suajado(mysqli $conn, float $precio_suaje): float {
    $stmt = mysqli_prepare($conn, "SELECT precio FROM rangos_suajado WHERE ? BETWEEN rango_inf AND rango_sup LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'd', $precio_suaje);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if ($row) return (float)$row['precio'];
    }
    return 0.0;
}
?>
