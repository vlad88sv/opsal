<?php
if (empty($_GET['ID']) || !is_numeric($_GET['ID']))
{
    echo '<p>Debe utilizar un código de orden interno</p>';
    return;
}

$codigo_contenedor = strtoupper(preg_replace(array('/[^\w\d]/','/(\d{4}\w{7})/'),array('','$1'),$_GET['ID']));

$c_ordenes = "
SELECT codigo_orden, tipo_salida, eir_ingreso, eir_egreso, chofer_ingreso, transportista_ingreso, transportista_egreso, chofer_ingreso, chofer_egreso, COALESCE(fechatiempo_egreso, 'aún en patio') AS fechatiempo_egreso_2, `arivu_referencia`, DATEDIFF(COALESCE(fechatiempo_egreso,NOW()), `arivu_ingreso`) AS 'dias_arivu' , DATEDIFF(COALESCE(fechatiempo_egreso,NOW()), `fechatiempo_ingreso`) AS 'dias_ingreso', DATEDIFF(NOW(), `cepa_salida`) AS 'dias_cepa', t4.`usuario` AS 'nombre_agencia', t5.`usuario` AS 'quien_recibio', t6.`usuario` AS 'quien_despacho', t3.`x2` , t3.`y2` , t1.`nivel`, `codigo_orden` , `codigo_contenedor` , `tipo_contenedor` , t2.`visual` , t2.`cobro` , t2.`afinidad`, t2.`nombre`, `codigo_agencia` , `codigo_posicion` , t1.`nivel` , `clase` , `tara` , `chasis` ,  `buque_ingreso` , `buque_egreso` , `cheque_ingreso` , `cheque_egreso` , `cepa_salida` , `arivu_ingreso` , `observaciones_egreso` , `observaciones_ingreso` , `destino` , `estado` , `fechatiempo_ingreso` , `fechatiempo_egreso` , `ingresado_por`
FROM `opsal_ordenes` AS t1
LEFT JOIN `opsal_tipo_contenedores` AS t2
USING ( tipo_contenedor )
LEFT JOIN `opsal_posicion` AS t3
USING ( codigo_posicion )
LEFT JOIN `opsal_usuarios` AS t4
ON t4.`codigo_usuario` = t1.`codigo_agencia`
LEFT JOIN `opsal_usuarios` AS t5
ON t5.`codigo_usuario` = t1.`ingresado_por`
LEFT JOIN `opsal_usuarios` AS t6
ON t6.`codigo_usuario` = t1.`egresado_por`
WHERE `codigo_contenedor` = '".$codigo_contenedor."'
ORDER BY fechatiempo_ingreso DESC
";

$r_ordenes = db_consultar($c_ordenes);

if (mysqli_num_rows($r_ordenes) > 0)
{
    echo '<h1>Mostrando historial de contenedor <b>'.$_GET['ID'].'</b></h1><hr /><br />';
    
    while ($f = mysqli_fetch_assoc($r_ordenes))
    {
        $cepa_salida = ($f['dias_cepa'] ? '<b>'.$f['cepa_salida'].'</b> [<b>'.$f['dias_cepa'].'</b> días desde salida de CEPA]' : '<b>Sin datos</b>');
        $arivu_ingreso = ($f['dias_arivu'] ? '<b>'.$f['arivu_ingreso'].'</b> [<b>'.$f['dias_arivu'].'</b> días desde el ingreso]' : '<b>Sin datos</b>');
            
        echo '<h2>Durante la recepción del <b>'.$f['fechatiempo_ingreso'].'</b> al <b>'.($f['fechatiempo_egreso_2'] != '' ? $f['fechatiempo_egreso_2'] : 'día de hoy').'</b></h2>';
        
        echo '<div style="border: 1px solid grey;border-radius:10px; padding:15px;margin:10px 10px 20px 50px;">';

            echo '<table class="tabla-estandar opsal_tabla_borde_oscuro">';
            echo '<tr><th style="width:200px;text-align:right;">Cód. interno</td><td>'.$f['codigo_orden'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Naviera</td><td>'.$f['nombre_agencia'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Transportista ingreso</th><td>'.$f['transportista_ingreso'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Última posición</th><td>'.$f['x2'].'-'.$f['y2'].'-'.$f['nivel'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Tipo</th><td>'.$f['nombre'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Clase</th><td>'.$f['clase'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Recibió</th><td>'.ucfirst(strtolower($f['quien_recibio'])).'</td></tr>';
            echo '<tr><th style="text-align:right;">EIR</th><td>'.$f['eir_ingreso'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Chofer</th><td>'.$f['chofer_ingreso'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Transportista</th><td>'.$f['transportista_ingreso'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Buque</th><td>'.$f['buque_ingreso'].'</td></tr>';
            echo '<tr><th style="text-align:right;">Recepción</th><td>'.$f['fechatiempo_ingreso'].' ['.$f['dias_ingreso'].' días en patio]</td></tr>';
            echo '<tr><th style="text-align:right;">CEPA salida</th><td>'.$cepa_salida.'</td></tr>';
            echo '<tr><th style="text-align:right;">ARIVU ingreso</th><td>'.$arivu_ingreso.'</td></tr>';
            echo '<tr><th style="text-align:right;">Observaciones</th><td>'.( $f['observaciones_ingreso'] ? $f['observaciones_ingreso'] : '[ninguna ingresada]' ).'</td></tr>';        
            echo '</table>';
            
            echo '<br />';
            
            if ($f['fechatiempo_egreso'] != '')
            {
                echo '<h3>Despacho</h3>';
                echo '<table class="tabla-estandar opsal_tabla_borde_oscuro">';
                echo '<tr><th style="width:200px;text-align:right;">Fecha</th><td>'.$f['fechatiempo_egreso_2'].'</td></tr>';
                echo '<tr><th style="text-align:right;">Tipo despacho</th><td>'.$f['tipo_salida'].'</td></tr>';
                echo '<tr><th style="text-align:right;">Despachó</th><td>'.$f['quien_despacho'].'</td></tr>';
                echo '<tr><th style="text-align:right;">Chofer</th><td>'.$f['chofer_egreso'].'</td></tr>';
                echo '<tr><th style="text-align:right;">Transportista</th><td>'.$f['transportista_egreso'].'</td></tr>';
                echo '<tr><th style="text-align:right;">EIR</th><td>'.$f['eir_egreso'].'</td></tr>';
                echo '<tr><th style="text-align:right;">Buque</th><td>'.$f['buque_egreso'].'</td></tr>';
                echo '</table>';
            } else {
                echo '<p>No tiene despacho registrado [aún se encuentra en patio]</p>';
            }
            
            echo '<br /><hr />';
            
            echo '<h2>Remociones durante esta recepción</h2>';
            
        
            $c_movimientos = "SELECT x2, y2, t1.nivel, usuario, cobrar_a, cheque, fechatiempo, motivo FROM opsal_movimientos AS t1 LEFT JOIN opsal_usuarios AS t2 USING(codigo_usuario) LEFT JOIN opsal_posicion AS t3 USING(codigo_posicion) WHERE t1.codigo_orden='".$f['codigo_orden']."' AND motivo='remocion' ORDER BY fechatiempo ASC";
            $r_movimientos = db_consultar($c_movimientos);
        
            if (mysqli_num_rows($r_movimientos) > 0)
            {
                $pos_anterior = 'Ingreso';
                
                echo '<table>';
                while ($g = mysqli_fetch_assoc($r_movimientos))
                {
                    echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f['usuario'],$pos_anterior, $g['x2'].'-'.$g['y2'].'-'.$g['nivel'],$g['fechatiempo']);
                }
                echo '<thead>';
                echo '<tr><th>Por</th><th>Posición anterior</th><th>Nueva posicion</th><th>Fecha</th></tr>';
                echo '</thead>';
                echo '</table>';
            } else {
                echo '<p>Aún no hay remociones para este contenedor.</p>';
            }

        echo '</div>';
    }
} else {
    echo '<p>No se encontró el contenedor búscado (<b>'.$_GET['ID'].'</b>)</p>';
}
?>