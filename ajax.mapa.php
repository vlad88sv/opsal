<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");
benchmark('Inicio AJAX Mapa');
// Primero obtengamos el mapa de posiciones junto con la representacion OPSAL y que hay ahi
// Prototipo de mapa: $mapa[x][y][z] = [x2,y2,datos]
$mapa[] = array();
$ordenes[] = array();

$consulta = 'SELECT `codigo_posicion`, `x`, `y`, `x2`, `y2`, tipo FROM `opsal_posicion` WHERE 1';
$resultado = db_consultar($consulta);

if ($resultado && mysqli_num_rows($resultado) > 0)
{
    while ($registro = mysqli_fetch_assoc($resultado))
    {
        $xy = $registro['x'].'.'.$registro['y'];
        $mapa[$xy] = $registro;
        
        $mapa[$xy]['nombre'] = 'Vacio';
        $mapa[$xy]['texto'] = '';
        $mapa[$xy]['nivel'] = 0;
        $mapa[$xy]['no_piso'] = 0;
        $mapa[$xy]['no_techo'] = 0;
        $mapa[$xy]['afinidad'] = 'libre';
    }
}

//***********

$c_ordenes = "
SELECT t4.`usuario` AS 'nombre_agencia', t3.`x` , t3.`y` , t3.`x2`, t3.`y2`, t1.`nivel`, `codigo_orden` , `codigo_contenedor` , `tipo_contenedor` , t2.`visual` , t2.`cobro` , t2.`afinidad`, t2.`nombre`, `codigo_agencia` , `codigo_posicion` , `clase` , `tara` , `chasis` , `transportista_ingreso` , `transportista_egreso` , `buque_ingreso` , `buque_egreso` , `cheque_ingreso` , `cheque_egreso` , `cepa_salida` , DATEDIFF(NOW(), `cepa_salida`) AS 'dias_cepa', `arivu_ingreso`, DATEDIFF(NOW(), `arivu_ingreso`) AS 'dias_arivu' , `ingresado_por` , `observaciones_egreso` , `observaciones_ingreso` , `destino` , `estado` , `fechatiempo_ingreso` , DATEDIFF(NOW(), `fechatiempo_ingreso`) AS 'dias_ingreso', `fechatiempo_egreso`
FROM `opsal_ordenes` AS t1
LEFT JOIN `opsal_tipo_contenedores` AS t2
USING ( tipo_contenedor )
LEFT JOIN `opsal_posicion` AS t3
USING ( codigo_posicion )
LEFT JOIN `opsal_usuarios` AS t4
ON t4.`codigo_usuario` = t1.`codigo_agencia`
WHERE `estado` = 'dentro'
";
$r_ordenes = db_consultar($c_ordenes);

//error_log('Ordenes encontradas: '.mysqli_num_rows($r_ordenes));
if (mysqli_num_rows($r_ordenes) > 0)
{
    while ($f_ordenes = mysqli_fetch_assoc($r_ordenes))
    {
        // Fingimos visualmente para contenedores arriba de 20
        switch ($f_ordenes['visual'])
        {
            case '20':
                $xy = $f_ordenes['x'].'.'.$f_ordenes['y'];
                $grupo = $f_ordenes['codigo_orden'];
                $mapa[$xy]['datos'][$f_ordenes['nivel']]  = $f_ordenes;
                $mapa[$xy]['no_piso'] = 0;
                $mapa[$xy]['no_techo'] = 0;
                $mapa[$xy]['visual'] = $f_ordenes['visual'];
                $mapa[$xy]['afinidad'] = $f_ordenes['afinidad'];
                $mapa[$xy]['nombre'] = $f_ordenes['nombre'];
                $mapa[$xy]['grupo'] = $grupo;
                break;
            
            case '40':
                $xy = $f_ordenes['x'].'.'.$f_ordenes['y'];
                $grupo = $f_ordenes['codigo_orden'];
                $mapa[$xy]['datos'][$f_ordenes['nivel']]  = $f_ordenes;
                $mapa[$xy]['no_piso'] = 1;
                $mapa[$xy]['no_techo'] = 0;
                $mapa[$xy]['visual'] = $f_ordenes['visual'];
                $mapa[$xy]['afinidad'] = $f_ordenes['afinidad'];
                $mapa[$xy]['nombre'] = $f_ordenes['nombre'];
                $mapa[$xy]['grupo'] = $grupo;
                
                $xy = $f_ordenes['x'].'.'.($f_ordenes['y']-1);
                $mapa[$xy]['datos'][$f_ordenes['nivel']]  = $f_ordenes;
                $mapa[$xy]['no_piso'] = 0;
                $mapa[$xy]['no_techo'] = 1;
                $mapa[$xy]['visual'] = $f_ordenes['visual'];
                $mapa[$xy]['afinidad'] = $f_ordenes['afinidad'];
                $mapa[$xy]['nombre'] = $f_ordenes['nombre'];
                $mapa[$xy]['no_aterrizaje'] = true;
                $mapa[$xy]['grupo'] = $grupo;
                break;
                
            case '60':
                $xy = $f_ordenes['x'].'.'.$f_ordenes['y'];
                $grupo = $f_ordenes['codigo_orden'];
                $mapa[$xy]['datos'][$f_ordenes['nivel']]  = $f_ordenes;
                $mapa[$xy]['no_piso'] = 1;
                $mapa[$xy]['no_techo'] = 0;
                $mapa[$xy]['visual'] = $f_ordenes['visual'];
                $mapa[$xy]['afinidad'] = $f_ordenes['afinidad'];
                $mapa[$xy]['nombre'] = $f_ordenes['nombre'];
                $mapa[$xy]['grupo'] = $grupo;
                
                $xy = $f_ordenes['x'].'.'.($f_ordenes['y']-1);
                $mapa[$xy]['datos'][$f_ordenes['nivel']]  = $f_ordenes;
                $mapa[$xy]['no_piso'] = 1;
                $mapa[$xy]['no_techo'] = 1;
                $mapa[$xy]['visual'] = $f_ordenes['visual'];
                $mapa[$xy]['afinidad'] = $f_ordenes['afinidad'];
                $mapa[$xy]['nombre'] = $f_ordenes['nombre'];
                $mapa[$xy]['no_aterrizaje'] = true;
                $mapa[$xy]['grupo'] = $grupo;

                $xy = $f_ordenes['x'].'.'.($f_ordenes['y']-2);
                $mapa[$xy]['datos'][$f_ordenes['nivel']]  = $f_ordenes;
                $mapa[$xy]['no_piso'] = 0;
                $mapa[$xy]['no_techo'] = 1;
                $mapa[$xy]['visual'] = $f_ordenes['visual'];
                $mapa[$xy]['afinidad'] = $f_ordenes['afinidad'];
                $mapa[$xy]['nombre'] = $f_ordenes['nombre'];
                $mapa[$xy]['no_aterrizaje'] = true;
                $mapa[$xy]['grupo'] = $grupo;
                
                switch( $f_ordenes['afinidad'] )
                {
                    case '45';
                        $mapa[$xy]['texto'] = '5';
                        break;
                    case '48':
                        $mapa[$xy]['texto'] = '8';
                        break;
                }
                
                break;
        }
    }
}

//******** Filtro
if (isset($_POST['filtrar']))
{
    $limite = '';
    $tfiltros = array();
    $trango = array();
    
    if (isset($_POST['codigo_agencia']) && is_numeric($_POST['codigo_agencia']))
    {
        $tfiltros[] = 'AND codigo_agencia = ' . $_POST['codigo_agencia'];
    }
    
    if (isset($_POST['clase']))
    {
        $tfiltros[] = 'AND clase IN ("'.join('","',$_POST['clase']).'")';
    }

    if (isset($_POST['tamano_contenedor']) && isset($_POST['tipo_contenedor']))
    {
        $tfiltros[] = 'AND tipo_contenedor = "'.$_POST['tipo_contenedor'].$_POST['tamano_contenedor'].'"';
    }
    
    $rango = ' AND t3.x BETWEEN (SELECT x FROM opsal_posiciones WHERE x2 = "'.$_POST['rango_final_col'].'") AND  (SELECT x FROM opsal_posiciones WHERE x2 = "'.$_POST['rango_inicio_col'].'")';
    
    $rango .= ' AND t3.y BETWEEN  (SELECT y FROM opsal_posiciones WHERE y2 = "'.$_POST['rango_final_fila'].') AND  (SELECT y FROM opsal_posiciones WHERE y2 = "'.$_POST['rango_inicio_fila'].'")';
    
    if (isset($_POST['limite']) && is_numeric($_POST['limite']))
    {
        $limite = ' LIMIT '.$_POST['limite'];
    }
    
    
    $filtros = join(' ', $tfiltros);
    
    $x2_order = ($_POST['direccion'] == 'izquierda' ? 'DESC' : 'ASC');
    
    $orden_salida = ($_POST['orden_salida'] == 'fila' ? "t3.`y2`+0 ASC, t3.`x2` $x2_order" : "t3.`x2` $x2_order, t3.`y2`+0 ASC");
    
    
    $c_ordenes = "
    SELECT `codigo_contenedor`, t3.`x` , t3.`y` , t3.x2, t3.y2, t1.nivel, `codigo_orden`, `codigo_posicion`, `visual`, IF (1 $filtros, 1, 0) AS filtrado
    FROM `opsal_ordenes` AS t1
    LEFT JOIN `opsal_tipo_contenedores` AS t2
    USING ( tipo_contenedor )
    LEFT JOIN `opsal_posicion` AS t3
    USING ( codigo_posicion )
    LEFT JOIN `opsal_usuarios` AS t4
    ON t4.`codigo_usuario` = t1.`codigo_agencia`
    WHERE `estado` = 'dentro' $rango
    ORDER BY $orden_salida, t1.`nivel` DESC
    $limite 
    ";
    
    //error_log($c_ordenes);
    
    $r_ordenes = db_consultar($c_ordenes);
    
    $json['filtro_numero_resultados'] = mysqli_num_rows($r_ordenes);
    
    if (mysqli_num_rows($r_ordenes) > 0)
    {
        while ($f_ordenes = mysqli_fetch_assoc($r_ordenes))
        {
            $xy = $f_ordenes['x'].'.'.$f_ordenes['y'];
            $json['ordenes'][] = array_merge(array('filtrado' => $f_ordenes['filtrado']),$mapa[$xy]['datos'][$f_ordenes['nivel']]);
            // Fingimos visualmente para contenedores arriba de 20
            switch ($f_ordenes['visual'])
            {
                case '20':
                    $xy = $f_ordenes['x'].'.'.$f_ordenes['y'];
                    $mapa[$xy]['filtrado'] = 1;
                    $mapa[$xy]['texto'] = (empty($mapa[$xy]['texto'])? 1 : $mapa[$xy]['texto'] + 1);
                    break;
                
                case '40':
                    $xy = $f_ordenes['x'].'.'.$f_ordenes['y'];
                    $mapa[$xy]['filtrado'] = 1;
                    $mapa[$xy]['texto'] = (empty($mapa[$xy]['texto'])? 1 : $mapa[$xy]['texto'] + 1);
                    
                    $xy = $f_ordenes['x'].'.'.($f_ordenes['y']+1);
                    $mapa[$xy]['filtrado'] = 1;
                    break;
                    
                case '60':
                    $xy = $f_ordenes['x'].'.'.$f_ordenes['y'];
                    $mapa[$xy]['filtrado'] = 1;
                    $mapa[$xy]['texto'] = (empty($mapa[$xy]['texto'])? 1 : $mapa[$xy]['texto'] + 1);
                    
                    $xy = $f_ordenes['x'].'.'.($f_ordenes['y']+1);
                    $mapa[$xy]['filtrado'] = 1;
    
                    $xy = $f_ordenes['x'].'.'.($f_ordenes['y']+2);
                    $mapa[$xy]['filtrado'] = 1;                    
                    break;
            }
        }
    }    
}


$buffer = '';
for ($y=36; $y > 0; $y--)
{
    $buffer .= '<tr>';
    for ($x=60; $x > 0; $x--)
    {
        $x2 = $x;
        $y2 = $y;
        $xy = $x.'.'.$y;
        $tipo = 'vacio';
        $nivel = 0;
        
        if (!empty($mapa[$xy])) {
            $tipo = $mapa[$xy]['tipo'];
                    
            $x2 = $mapa[$xy]['x2'];
            $y2 = $mapa[$xy]['y2'];
        }
        
        $y3 = (($y2+1) % 4 == 0 ? ($y2 - 1) : ($y2 + 1));        
        
        $title = '
        <b>'.$x2.$y2.'</b> parte de <b>'.$x2.$y3.'</b><br />'.
        'Tipo: '. ucfirst($mapa[$xy]['tipo']).' / '.$mapa[$xy]['nombre']
        ;        
        
        if (@is_array($mapa[$xy]['datos'])) {
            $nivel = max(array_keys($mapa[$xy]['datos']));
            
            $title .= ' / <b>' . $nivel . '</b> estiba(s)';
        
            krsort($mapa[$xy]['datos'],SORT_NUMERIC);
            
            foreach($mapa[$xy]['datos'] as $kNivel => $vDatos)
            {                
                $title .= '<hr /><p>';
                $title .= '<b>#'.$kNivel.'</b> - <b>'.$mapa[$xy]['datos'][$kNivel]['nombre'].'</b> Clase <b>'.$mapa[$xy]['datos'][$kNivel]['clase'].'</b><br />';
                $title .= 'Ingreso: <b>'.$mapa[$xy]['datos'][$kNivel]['fechatiempo_ingreso'].'</b> <b>['.$mapa[$xy]['datos'][$kNivel]['dias_ingreso'].' días]</b><br />';
                $title .= 'ARIVU: <b>'.$mapa[$xy]['datos'][$kNivel]['arivu_ingreso'].'</b> <b>['.$mapa[$xy]['datos'][$kNivel]['dias_arivu'].' días]</b><br />';
                $title .= 'CEPA: <b>'.$mapa[$xy]['datos'][$kNivel]['cepa_salida'].'</b> <b>['.$mapa[$xy]['datos'][$kNivel]['dias_cepa'].' días]</b><br />';
                $title .= 'Agencia: <b>'.$mapa[$xy]['datos'][$kNivel]['nombre_agencia'].'</b><br />';
                $title .= 'Contenedor: <b>'.$mapa[$xy]['datos'][$kNivel]['codigo_contenedor'].'</b>';
                $title .= '</p>';
            }
        }
                
        
        $clases_especiales = ' ';
        
        if ($mapa[$xy]['no_piso'])
            $clases_especiales .= 'contenedor_mapa_casilla_sin_piso ';
        
        if ($mapa[$xy]['no_techo'])
            $clases_especiales .= 'contenedor_mapa_casilla_sin_techo ';    
        
        if (isset($mapa[$xy]['no_aterrizaje']))
            $clases_especiales .= 'contenedor_zona_muerta ';
            
        if (isset($mapa[$xy]['filtrado']))
            $clases_especiales .= 'contenedor_filtrado ';

        $grupo = (!empty($mapa[$xy]['grupo']) ? $mapa[$xy]['grupo'] : '');    
        
        
        $buffer .= '<td id="'.$x.'_'.$y.'" grupo="'.$grupo.'" visual="'.@$mapa[$xy]['visual'].'" afinidad="'.$mapa[$xy]['afinidad'].'" x="'.$x.'" y="'.$y.'" col="'.$x2.'" fila="'.$y2.'" nivel="'.$nivel.'" tooltip="'.htmlspecialchars($title).'" class="contenedor_mapa_casilla_'.$tipo.' '.$clases_especiales.' contenedor_mapa_casilla_estiba_'.$nivel.'">'.$mapa[$xy]['texto'].'</td>';
    }
    $buffer .= '</tr>';
}

$buffer = '<table style="height:640px;width:732px;table-layout:fixed;">'.$buffer.'</table>';
$json['mapa'] = $buffer;

echo json_encode($json);
benchmark('AJAX Mapa entregado');
?>