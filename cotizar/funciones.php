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
    $sql = "SELECT SKU, Nombre, Color, Precio_Unit, Largo, Ancho, Alto FROM catalogo_productos";
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

function get_acabados(mysqli $conn) {
    $sql = "SELECT id, nombre, precio FROM acabados ORDER BY nombre ASC";
    $res = mysqli_query($conn, $sql);
    $acabados = [];
    if($res){
        while($row = mysqli_fetch_assoc($res)){
            $acabados[] = $row;
        }
    }
    return $acabados;
}

function get_offset_providers(mysqli $conn) {
    $sql = "SELECT * FROM offet";
    $res = mysqli_query($conn, $sql);
    $providers = [];
    if($res){
        while($row = mysqli_fetch_assoc($res)){
            $providers[] = $row;
        }
    }
    return $providers;
}

function get_valor(mysqli $conn, string $nombre){
    $stmt = mysqli_prepare($conn, "SELECT precio FROM valores WHERE nombre=? LIMIT 1");
    if(!$stmt){
        return 0;
    }
    mysqli_stmt_bind_param($stmt, 's', $nombre);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? (float)$row['precio'] : 0;
}

function get_material_info(mysqli $conn, string $clave): array{
    $sql = "SELECT tipo, descripcion,
                    MAX(largo_max) AS largo_max,
                    MAX(ancho_max) AS ancho_max,
                    MIN(CASE WHEN tipo='lamina' THEN precio * 10 /(largo_max * ancho_max) ELSE precio END) AS precio_m2
            FROM material WHERE clave=? GROUP BY clave, tipo, descripcion LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if(!$stmt){
        return [];
    }
    mysqli_stmt_bind_param($stmt, 's', $clave);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ?: [];
}

function get_armado_nombre(mysqli $conn, int $id): string{
    $stmt = mysqli_prepare($conn, "SELECT nombre FROM armado WHERE id=?");
    if(!$stmt){
        return '';
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return $row ? $row['nombre'] : '';
}

function get_procesos_por_armado(mysqli $conn, int $id_armado): array{
    $sql = "SELECT p.id, p.nombre, p.precio FROM procesos p JOIN armado_procesos ap ON p.id=ap.id_proceso WHERE ap.id_armado=?";
    $stmt = mysqli_prepare($conn, $sql);
    $procesos = [];
    if($stmt){
        mysqli_stmt_bind_param($stmt, 'i', $id_armado);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($res)){
            $procesos[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    return $procesos;
}

function get_costo_suajado(mysqli $conn, float $precio_suaje): float{
    $sql = "SELECT precio FROM rangos_suajado WHERE ? BETWEEN rango_inf AND rango_sup LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if($stmt){
        mysqli_stmt_bind_param($stmt, 'd', $precio_suaje);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if($row){
            return (float)$row['precio'];
        }
    }
    return 0;
}

function get_suajado_limits(mysqli $conn): array{
    $sql = "SELECT largo_max, ancho_max FROM procesos WHERE nombre='suajado' LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if($res){
        $row = mysqli_fetch_assoc($res);
        if($row){
            return [
                'largo_max' => (float)$row['largo_max'],
                'ancho_max' => (float)$row['ancho_max'],
            ];
        }
    }
    return ['largo_max'=>0,'ancho_max'=>0];
}

function get_tamanos_compra(mysqli $conn, string $material_clave): array{
    $sql = "SELECT largo_max, ancho_max, precio FROM material WHERE clave=? AND tipo='lamina'";
    $stmt = mysqli_prepare($conn, $sql);
    $tamanos = [];
    if($stmt){
        mysqli_stmt_bind_param($stmt, 's', $material_clave);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($res)){
            $tamanos[] = [
                'largo'  => (float)$row['largo_max'],
                'ancho'  => (float)$row['ancho_max'],
                'precio' => (float)$row['precio'],
            ];
        }
        mysqli_stmt_close($stmt);
    }
    return $tamanos;
}

function cotizar_corrugado(mysqli $conn, int $armado, float $largo, float $ancho, float $alto, string $material_clave, array $opciones = []): array{
    $material = get_material_info($conn, $material_clave);
    if(!$material){
        return [];
    }

    $datos_caja = obtener_datos_caja($armado, $largo, $ancho, $alto);
    $merma     = $opciones['merma']     ?? get_valor($conn, 'Merma');
    $utilidad  = $opciones['utilidad']  ?? get_valor($conn, 'Utilidad');
    $iva       = $opciones['iva']       ?? get_valor($conn, 'iva');
    $sobrantes = $opciones['sobrantes'] ?? get_valor($conn, 'Sobrantes');
    $precio_cm = get_valor($conn, 'Suaje');

    $area_m2    = 0;
    $cm_suaje   = 0;
    $largo_max  = $material['largo_max'] ?? 0;
    $ancho_max  = $material['ancho_max'] ?? 0;
    foreach($datos_caja['largo_lamina'] as $i => $l){
        $w = $datos_caja['ancho_lamina'][$i];
        if(!(($l <= $largo_max && $w <= $ancho_max) || ($w <= $largo_max && $l <= $ancho_max))){
            return [];
        }
        $area_m2 += ($l * $w) / 10000;
        $cm_suaje += $datos_caja['cm_suaje'][$i];
    }
    if(isset($opciones['cm_suaje'])){
        $cm_suaje = (float)$opciones['cm_suaje'];
    }
    $area_m2_con_merma = $area_m2 * (1 + $merma / 100);

    $precio_m2 = $opciones['precio_m2'] ?? $material['precio_m2'];
    // Incluye el IVA en el costo del sustrato. Esto refleja el costo real del
    // material adquirido, ya que el proveedor lo cobra con IVA. Posteriormente
    // se volverá a calcular el IVA de venta para el cliente sobre el total de
    // la cotización.
    $costo_material_millar = $area_m2_con_merma * $precio_m2 * (1 + $iva / 100) * 1000;
    $precio_suaje = $cm_suaje * $precio_cm;

    $procesos = get_procesos_por_armado($conn, $armado);
    $procesos_detalle = [];
    $costo_procesos_millar = 0;
    foreach($procesos as $p){
        if($p['nombre'] === 'suajado'){
            $costo = get_costo_suajado($conn, $precio_suaje);
        }else{
            $costo = $p['precio'];
        }
        if(isset($opciones['procesos'][$p['id']])){
            $costo = (float)$opciones['procesos'][$p['id']];
        }
        $procesos_detalle[] = ['id' => $p['id'], 'nombre' => $p['nombre'], 'costo' => $costo];
        $costo_procesos_millar += $costo;
    }

    $base_millar = $costo_material_millar + $costo_procesos_millar;
    $utilidad_monto = $base_millar * $utilidad / 100;
    $costo_millar_sin_iva = $base_millar + $utilidad_monto;
    $sobrantes_monto = 0;
    if ($cm_suaje == 0) {
        $sobrantes_monto = $costo_millar_sin_iva * $sobrantes / 100;
        $costo_millar_sin_iva += $sobrantes_monto;
    }
    $iva_monto = $costo_millar_sin_iva * $iva / 100;
    $costo_millar_con_iva = $costo_millar_sin_iva + $iva_monto;

    $precio_pieza_sin_iva = $costo_millar_sin_iva / 1000;
    $precio_pieza_con_iva = $costo_millar_con_iva / 1000;

    return [
        'datos_caja'              => $datos_caja,
        'material'                => $material,
        'armado_nombre'           => get_armado_nombre($conn, $armado),
        'area_m2'                 => $area_m2,
        'merma'                   => $merma,
        'area_m2_con_merma'       => $area_m2_con_merma,
        'precio_suaje'            => $precio_suaje,
        'procesos'                => $procesos_detalle,
        'costo_material_millar'   => $costo_material_millar,
        'costo_procesos_millar'   => $costo_procesos_millar,
        'base_millar'             => $base_millar,
        'utilidad'                => $utilidad,
        'utilidad_monto'          => $utilidad_monto,
        'costo_millar_sin_iva'    => $costo_millar_sin_iva,
        'sobrantes'               => $sobrantes,
        'sobrantes_monto'         => $sobrantes_monto,
        'aplica_sobrantes'        => $cm_suaje == 0,
        'iva'                     => $iva,
        'iva_monto'               => $iva_monto,
        'costo_millar_con_iva'    => $costo_millar_con_iva,
        'precio_pieza_sin_iva'    => $precio_pieza_sin_iva,
        'precio_pieza_con_iva'    => $precio_pieza_con_iva,
    ];
}

function cotizar_lamina(mysqli $conn, int $armado, float $largo, float $ancho, float $alto, string $material_clave, array $opciones = []): array{
    $material = get_material_info($conn, $material_clave);
    if(!$material){
        return [];
    }

    $datos_caja = obtener_datos_caja($armado, $largo, $ancho, $alto);
    $merma     = $opciones['merma']     ?? get_valor($conn, 'Merma');
    $utilidad  = $opciones['utilidad']  ?? get_valor($conn, 'Utilidad');
    $iva       = $opciones['iva']       ?? get_valor($conn, 'iva');
    $sobrantes = $opciones['sobrantes'] ?? get_valor($conn, 'Sobrantes');
    $precio_cm = get_valor($conn, 'Suaje');

    $tamanos = get_tamanos_compra($conn, $material_clave);
    if(!$tamanos){
        return [];
    }

    $usar_offset = !empty($opciones['offset']);
    $tintas = (int)($opciones['offset_tintas'] ?? 1);
    $con_plasta = !empty($opciones['offset_plasta']);
    $acabados_seleccionados = $opciones['acabados'] ?? [];
    $providers = $usar_offset ? get_offset_providers($conn) : [];
    $acabados_db = $usar_offset ? get_acabados($conn) : [];
    $acabados_map = [];
    foreach($acabados_db as $ac) $acabados_map[$ac['id']] = $ac;

    $laminas_detalle = [];
    $costo_material_millar = 0;
    $costo_offset_millar = 0;
    $costo_acabados_millar = 0;

    foreach($datos_caja['largo_lamina'] as $i => $ll){
        $al = $datos_caja['ancho_lamina'][$i];
        $best = null;

        if ($usar_offset) {
            foreach($providers as $prov) {
                foreach($tamanos as $t) {
                    for($rotar = 0; $rotar <= 1; $rotar++) {
                        $p_l = $rotar ? $al : $ll;
                        $p_a = $rotar ? $ll : $al;

                        $max_gx = max(1, floor($prov['largo_max'] / $p_l));
                        $max_gy = max(1, floor($prov['ancho_max'] / $p_a));

                        for($gx = 1; $gx <= $max_gx; $gx++) {
                            for($gy = 1; $gy <= $max_gy; $gy++) {
                                $m_l = $p_l * $gx;
                                $m_a = $p_a * $gy;

                                $m_l_pad = max($m_l, $prov['largo_min']);
                                $m_a_pad = max($m_a, $prov['ancho_min']);

                                if ($m_l_pad > $prov['largo_max'] || $m_a_pad > $prov['ancho_max']) continue;

                                $fit1 = floor($t['largo'] / $m_l_pad) * floor($t['ancho'] / $m_a_pad);
                                $fit2 = floor($t['largo'] / $m_a_pad) * floor($t['ancho'] / $m_l_pad);
                                $nm = max($fit1, $fit2);

                                if ($nm > 0) {
                                    $provechos_per_compra = ($gx * $gy) * $nm;
                                    $laminas_nec = (int)ceil(1000 / $provechos_per_compra);
                                    $laminas_merma = (int)ceil($laminas_nec * (1 + $merma / 100));

                                    $precio_unit_iva = ($t['precio'] / 1000) * (1 + $iva / 100);
                                    $costo_sustrato = $laminas_merma * $precio_unit_iva;

                                    $tamanos_maquina = $laminas_merma * $nm;
                                    $tiros = ($tintas * $tamanos_maquina) / 1000;
                                    if ($tiros < $tintas) $tiros = $tintas;

                                    $costo_tiros = $tiros * ($con_plasta ? $prov['plasta'] : $prov['tiro']);
                                    $costo_placas = $tintas * $prov['placa'];
                                    $costo_offset = $costo_tiros + $costo_placas;

                                    $costo_acabados = 0;
                                    $detalle_acabados = [];
                                    foreach($acabados_seleccionados as $ac_id) {
                                        if(isset($acabados_map[$ac_id])) {
                                            $ac = $acabados_map[$ac_id];
                                            $area_m2 = ($m_l_pad * $m_a_pad) / 10000;
                                            $area_total_m2 = $area_m2 * $tamanos_maquina;
                                            $c_ac = $area_total_m2 * $ac['precio'];

                                            $costo_acabados += $c_ac;
                                            $detalle_acabados[] = [
                                                'nombre' => $ac['nombre'],
                                                'costo' => $c_ac
                                            ];
                                        }
                                    }

                                    $costo_total = $costo_sustrato + $costo_offset + $costo_acabados;

                                    if ($best === null || $costo_total < $best['costo_total']) {
                                        $best = [
                                            'largo' => $t['largo'],
                                            'ancho' => $t['ancho'],
                                            'provechos' => $provechos_per_compra,
                                            'precio_unit_iva' => $precio_unit_iva,
                                            'laminas_necesarias' => $laminas_nec,
                                            'laminas_merma' => $laminas_merma,
                                            'costo_total' => $costo_total,
                                            'costo_sustrato' => $costo_sustrato,
                                            'usa_offset' => true,
                                            'offset_provider' => $prov['nombre'],
                                            'tam_maquina_largo' => $m_l_pad,
                                            'tam_maquina_ancho' => $m_a_pad,
                                            'provechos_por_maquina' => ($gx * $gy),
                                            'maquinas_por_compra' => $nm,
                                            'tamanos_maquina_total' => $tamanos_maquina,
                                            'tiros_cobrados' => $tiros,
                                            'costo_tiros' => $costo_tiros,
                                            'costo_placas' => $costo_placas,
                                            'costo_offset' => $costo_offset,
                                            'detalle_acabados' => $detalle_acabados,
                                            'costo_acabados' => $costo_acabados,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            foreach($tamanos as $t){
                $p1 = floor($t['largo'] / $ll) * floor($t['ancho'] / $al);
                $p2 = floor($t['largo'] / $al) * floor($t['ancho'] / $ll);
                $p = max($p1, $p2);
                if($p <= 0){
                    continue;
                }
                $precio_unit = $t['precio'] / 1000;
                $precio_prov = $precio_unit / $p;
                if($best === null || $precio_prov < $best['precio_prov'] || ($precio_prov == $best['precio_prov'] && $p > $best['provechos'])){
                    $laminas_nec = (int)ceil(1000 / $p);
                    $laminas_merma = (int)ceil($laminas_nec * (1 + $merma / 100));
                    $precio_unit_iva = $precio_unit * (1 + $iva / 100);
                    $costo_sustrato = $laminas_merma * $precio_unit_iva;

                    $best = [
                        'largo' => $t['largo'],
                        'ancho' => $t['ancho'],
                        'provechos' => $p,
                        'precio_prov' => $precio_prov,
                        'precio_unit_iva' => $precio_unit_iva,
                        'laminas_necesarias' => $laminas_nec,
                        'laminas_merma' => $laminas_merma,
                        'costo_sustrato' => $costo_sustrato,
                        'usa_offset' => false
                    ];
                }
            }
        }

        if($best === null){
            return [];
        }

        $costo_material_millar += $best['costo_sustrato'];
        if ($best['usa_offset']) {
            $costo_offset_millar += $best['costo_offset'];
            $costo_acabados_millar += $best['costo_acabados'];
        }

        $laminas_detalle[] = [
            'nombre' => $datos_caja['nombre'][$i] ?? 'Parte '.($i+1),
            'tam_largo' => $best['largo'],
            'tam_ancho' => $best['ancho'],
            'provechos' => $best['provechos'],
            'laminas_necesarias' => $best['laminas_necesarias'],
            'laminas_merma' => $best['laminas_merma'],
            'precio_unit_iva' => $best['precio_unit_iva'],
            'costo' => $best['costo_sustrato'],
            'offset' => $best['usa_offset'] ? [
                'provider' => $best['offset_provider'],
                'maquina_largo' => $best['tam_maquina_largo'],
                'maquina_ancho' => $best['tam_maquina_ancho'],
                'provechos_por_maquina' => $best['provechos_por_maquina'],
                'maquinas_por_compra' => $best['maquinas_por_compra'],
                'total_maquinas' => $best['tamanos_maquina_total'],
                'tiros' => $best['tiros_cobrados'],
                'costo_tiros' => $best['costo_tiros'],
                'costo_placas' => $best['costo_placas'],
                'costo_total' => $best['costo_offset'],
            ] : null,
            'acabados' => $best['usa_offset'] ? [
                'detalle' => $best['detalle_acabados'],
                'costo_total' => $best['costo_acabados']
            ] : null
        ];
    }

    $cm_suaje = 0;
    foreach($datos_caja['cm_suaje'] as $c){
        $cm_suaje += $c;
    }
    if(isset($opciones['cm_suaje'])){
        $cm_suaje = (float)$opciones['cm_suaje'];
    }
    $precio_suaje = $cm_suaje * $precio_cm;

    $procesos = get_procesos_por_armado($conn, $armado);
    $procesos_detalle = [];
    $costo_procesos_millar = 0;
    foreach($procesos as $p){
        if($p['nombre'] === 'suajado'){
            $costo = get_costo_suajado($conn, $precio_suaje);
        }else{
            $costo = $p['precio'];
        }
        if(isset($opciones['procesos'][$p['id']])){
            $costo = (float)$opciones['procesos'][$p['id']];
        }
        $procesos_detalle[] = ['id'=>$p['id'], 'nombre'=>$p['nombre'], 'costo'=>$costo];
        $costo_procesos_millar += $costo;
    }

    $base_millar = $costo_material_millar + $costo_procesos_millar + $costo_offset_millar + $costo_acabados_millar;
    $utilidad_monto = $base_millar * $utilidad / 100;
    $costo_millar_sin_iva = $base_millar + $utilidad_monto;
    $sobrantes_monto = 0;
    if($cm_suaje == 0){
        $sobrantes_monto = $costo_millar_sin_iva * $sobrantes / 100;
        $costo_millar_sin_iva += $sobrantes_monto;
    }
    $iva_monto = $costo_millar_sin_iva * $iva / 100;
    $costo_millar_con_iva = $costo_millar_sin_iva + $iva_monto;

    $precio_pieza_sin_iva = $costo_millar_sin_iva / 1000;
    $precio_pieza_con_iva = $costo_millar_con_iva / 1000;

    return [
        'datos_caja'            => $datos_caja,
        'material'              => $material,
        'armado_nombre'         => get_armado_nombre($conn, $armado),
        'merma'                 => $merma,
        'laminas'               => $laminas_detalle,
        'precio_suaje'          => $precio_suaje,
        'procesos'              => $procesos_detalle,
        'costo_material_millar' => $costo_material_millar,
        'costo_procesos_millar' => $costo_procesos_millar,
        'costo_offset_millar'   => $costo_offset_millar,
        'costo_acabados_millar' => $costo_acabados_millar,
        'base_millar'           => $base_millar,
        'utilidad'              => $utilidad,
        'utilidad_monto'        => $utilidad_monto,
        'costo_millar_sin_iva'  => $costo_millar_sin_iva,
        'sobrantes'             => $sobrantes,
        'sobrantes_monto'       => $sobrantes_monto,
        'aplica_sobrantes'      => $cm_suaje == 0,
        'iva'                   => $iva,
        'iva_monto'             => $iva_monto,
        'costo_millar_con_iva'  => $costo_millar_con_iva,
        'precio_pieza_sin_iva'  => $precio_pieza_sin_iva,
        'precio_pieza_con_iva'  => $precio_pieza_con_iva,
    ];
}

function suaje_multiple_cabe(array $datos_caja, int $multiplo, array $material, array $suajado): bool{
    $ml = $material['largo_max'] ?? 0;
    $ma = $material['ancho_max'] ?? 0;
    $sl = $suajado['largo_max'] ?? 0;
    $sa = $suajado['ancho_max'] ?? 0;
    foreach($datos_caja['largo_lamina'] as $i => $l){
        $w = $datos_caja['ancho_lamina'][$i];
        $fits = false;
        $l1 = $l * $multiplo; $w1 = $w;
        if((($l1 <= $ml && $w1 <= $ma) || ($w1 <= $ml && $l1 <= $ma)) &&
           (($l1 <= $sl && $w1 <= $sa) || ($w1 <= $sl && $l1 <= $sa))){
            $fits = true;
        }else{
            $l2 = $l; $w2 = $w * $multiplo;
            if((($l2 <= $ml && $w2 <= $ma) || ($w2 <= $ml && $l2 <= $ma)) &&
               (($l2 <= $sl && $w2 <= $sa) || ($w2 <= $sl && $l2 <= $sa))){
                $fits = true;
            }
        }
        if(!$fits){
            return false;
        }
    }
    return true;
}

function cotizar_suaje_multiple(mysqli $conn, int $armado, float $largo, float $ancho, float $alto, string $material_clave, array $opciones = []): array{
    $material = get_material_info($conn, $material_clave);
    if(!$material){
        return [];
    }
    $datos_caja = obtener_datos_caja($armado, $largo, $ancho, $alto);
    $suajado_limits = get_suajado_limits($conn);
    $cm_base = 0;
    foreach($datos_caja['cm_suaje'] as $c){
        $cm_base += $c;
    }
    $volumenes = [1000,2000,3000,4000,5000,10000];
    $resultados = [];
    foreach($volumenes as $vol){
        $m_max = (int)floor($vol/1000);
        $mejor = null;
        for($m=1; $m <= $m_max; $m++){
            if(!suaje_multiple_cabe($datos_caja, $m, $material, $suajado_limits)){
                continue;
            }
            $opc = $opciones;
            $cm_total = $cm_base * $m;
            $opc['cm_suaje'] = $cm_total;
            if($material['tipo'] === 'lamina'){
                $cot = cotizar_lamina($conn, $armado, $largo, $ancho, $alto, $material_clave, $opc);
            }else{
                $cot = cotizar_corrugado($conn, $armado, $largo, $ancho, $alto, $material_clave, $opc);
            }
            if(!$cot){
                continue;
            }
            $precio_suaje = $cot['precio_suaje'];
            $costo_material_millar = $cot['costo_material_millar'];
            $costo_procesos_millar = $cot['costo_procesos_millar'];
            $costo_suajado_millar = 0;
            foreach($cot['procesos'] as $p){
                if($p['nombre'] === 'suajado'){
                    $costo_suajado_millar = $p['costo'];
                    break;
                }
            }
            $costo_proc_sin_su = $costo_procesos_millar - $costo_suajado_millar;
            $costo_material_total = ($costo_material_millar/1000) * $vol;
            $costo_proc_total = ($costo_proc_sin_su/1000) * $vol;
            $golpes = (int)ceil($vol / $m);
            $costo_suajado_total = $costo_suajado_millar * (int)ceil($golpes/1000);
            $base_total = $costo_material_total + $costo_proc_total + $costo_suajado_total;
            $utilidad = $cot['utilidad'];
            $iva = $cot['iva'];
            $costo_total_sin_iva = $base_total * (1 + $utilidad/100);
            $precio_caja_sin_iva = $costo_total_sin_iva / $vol;
            $precio_suaje_pieza = $precio_suaje / $vol;
            $precio_total_sin_iva = $precio_caja_sin_iva + $precio_suaje_pieza;
            $precio_total_con_iva = $precio_total_sin_iva * (1 + $iva/100);
            if($mejor === null || $precio_total_sin_iva < $mejor['precio_total_sin_iva']){
                $mejor = [
                    'piezas_por_golpe' => $m,
                    'cm_suaje_total' => $cm_total,
                    'golpes' => $golpes,
                    'costo_material_total' => $costo_material_total,
                    'costo_procesos_total' => $costo_proc_total,
                    'costo_suajado_total' => $costo_suajado_total,
                    'precio_caja_sin_iva' => $precio_caja_sin_iva,
                    'precio_suaje_pieza' => $precio_suaje_pieza,
                    'precio_total_sin_iva' => $precio_total_sin_iva,
                    'precio_total_con_iva' => $precio_total_con_iva,
                ];
            }
        }
        if($mejor){
            $resultados[$vol] = $mejor;
        }
    }
    return $resultados;
}
?>
