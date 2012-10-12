<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

$codigo_contenedor = strtoupper(preg_replace(array('/[^\w\d]/','/(\d{4}\w{7})/'),array('','$1'),$_GET['busqueda']));

$c_ordenes = "
SELECT codigo_orden, COALESCE(fechatiempo_egreso, 'aún en patio') AS fechatiempo_egreso_2, DATEDIFF(COALESCE(fechatiempo_egreso,NOW()), `arivu_ingreso`) AS 'dias_arivu' , DATEDIFF(COALESCE(fechatiempo_egreso,NOW()), `fechatiempo_ingreso`) AS 'dias_ingreso', DATEDIFF(NOW(), `cepa_salida`) AS 'dias_cepa', t4.`usuario` AS 'nombre_agencia', t3.`x2` , t3.`y2` , t1.`nivel`, `codigo_orden` , `codigo_contenedor` , `tipo_contenedor` , t2.`visual` , t2.`cobro` , t2.`afinidad`, t2.`nombre`, `codigo_agencia` , `codigo_posicion` , t1.`nivel` , `clase` , `tara` , `chasis` , `transportista_ingreso` , `transportista_egreso` , `buque_ingreso` , `buque_egreso` , `cheque_ingreso` , `cheque_egreso` , `cepa_salida` , `arivu_ingreso` , `observaciones_egreso` , `observaciones_ingreso` , `destino` , `estado` , `fechatiempo_ingreso` , `fechatiempo_egreso` , `ingresado_por`
FROM `opsal_ordenes` AS t1
LEFT JOIN `opsal_tipo_contenedores` AS t2
USING ( tipo_contenedor )
LEFT JOIN `opsal_posicion` AS t3
USING ( codigo_posicion )
LEFT JOIN `opsal_usuarios` AS t4
ON t4.`codigo_usuario` = t1.`codigo_agencia`
WHERE `codigo_contenedor` = '".$codigo_contenedor."'
ORDER BY fechatiempo_ingreso DESC
LIMIT 1
";

$r_ordenes = db_consultar($c_ordenes);

if (mysqli_num_rows($r_ordenes) == 1)
{
    $f = mysqli_fetch_assoc($r_ordenes);
    mysqli_free_result($r_ordenes);
        
    
    echo '<h1>Mostrando datos según última recepción del contenedor</h1><hr />';
    echo '<p>Contenedor: <b>'.$f['codigo_contenedor'].'</b>, perteneciente a naviera <b>'.$f['nombre_agencia'].'</b></p>';
    echo '<p>Posicion actual: <b>'.$f['x2'].'-'.$f['y2'].'-'.$f['nivel'].'</b> - Tipo: <b>'.$f['nombre'].'</b> Clase: <b>'.$f['clase'].'</b></p>';
    echo '<p>Recepcion: <b>'.$f['fechatiempo_ingreso'].'</b> [<b>'.$f['dias_ingreso'].'</b> días en patio]</p>';
    echo '<p>Despacho: <b>'.$f['fechatiempo_egreso_2'].'</b></p>';
    $cepa_salida = ($f['dias_cepa'] ? '<b>'.$f['cepa_salida'].'</b> [<b>'.$f['dias_cepa'].'</b> días desde salida de CEPA]' : '<b>Sin datos</b>');
    echo "<p>CEPA salida: $cepa_salida</p>";
    $arivu_ingreso = ($f['dias_arivu'] ? '<b>'.$f['arivu_ingreso'].'</b> [<b>'.$f['dias_arivu'].'</b> días desde el ingreso]' : '<b>Sin datos</b>');
    echo "<p>ARIVU ingreso: $arivu_ingreso</p>";
    echo '<p>Observaciones ingreso: <b>'.( $f['observaciones_ingreso'] ? $f['observaciones_ingreso'] : '[ninguna ingresada]' ).'</b></p>';
    echo '<br /><hr />';
    
    
    echo '<h2>Remociones durante esta recepción</h2>';
    

    $c_movimientos = "SELECT x2, y2, t1.nivel, usuario, cobrar_a, cheque, fechatiempo, motivo FROM opsal_movimientos AS t1 LEFT JOIN opsal_usuarios AS t2 USING(codigo_usuario) LEFT JOIN opsal_posicion AS t3 USING(codigo_posicion) WHERE t1.codigo_orden='".$f['codigo_orden']."' AND motivo='remocion' ORDER BY fechatiempo ASC";
    $r_movimientos = db_consultar($c_movimientos);

    if (mysqli_num_rows($r_movimientos) > 0)
    {
        echo '<table>';
        while ($g = mysqli_fetch_assoc($r_movimientos))
        {
            echo sprintf('<tr><td>%s</td><td>%s</td></tr>', $g['x2'].'-'.$g['y2'].'-'.$g['nivel'],$f['fechatiempo']);
        }
        echo '<thead>';
        echo '<tr><th>Nueva posicion</th><th>Fecha</th></tr>';
        echo '</thead>';
        echo '</table>';
    } else {
        echo '<p>Aún no hay remociones para este contenedor.</p>';
    }
    
    echo '<br /><hr />';
    echo '<button '.($f['estado'] == 'fuera' ? 'disabled="disabled"' : '').' id="bq_usar_contenedor" col="'.$f['x2'].'" fila="'.$f['y2'].'" nivel="'.$f['nivel'].'">Utilizar</button>';
    echo '<button '.($f['estado'] == 'fuera' ? 'disabled="disabled"' : '').' id="bq_remover_contenedor" col="'.$f['x2'].'" fila="'.$f['y2'].'" nivel="'.$f['nivel'].'">Remover</button>';
    echo '<button '.($f['estado'] == 'fuera' ? 'disabled="disabled"' : '').' id="bq_despachar_contenedor" col="'.$f['x2'].'" fila="'.$f['y2'].'" nivel="'.$f['nivel'].'">Despachar</button>';
    echo '<button '.($f['estado'] == 'fuera' ? 'disabled="disabled"' : '').' id="bq_mostrar_contenedor" col="'.$f['x2'].'" fila="'.$f['y2'].'" nivel="'.$f['nivel'].'">Mostrar en mapa</button>';
    
    echo '<button rel="'.$f['codigo_contenedor'].'" id="ver_historial">Historial</button>';
    
} else {
    echo '<p>No se encontró el contenedor búscado (<b>'.$_GET['busqueda'].'</b>)</p>';
}
?>