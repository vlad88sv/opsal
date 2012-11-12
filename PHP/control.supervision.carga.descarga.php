<?php
function control_supervision_carga_descarga__crear_impresion($ID_carga_descarga, $tipo, $titulo)
{
    $c = '
    SELECT
    t1.`codigo_agencia`,
    t1.`fecha_ingreso`,
    t1.`ID_buque`,
    t1.`ID_carga_descarga`,
    t1.`ingresado_por`,
    t1.`supervisor`,
    t1.`marchamador`,
    t1.`inicio_operacion`,
    t1.`final_operacion`,
    TIMESTAMPDIFF(HOUR, t1.`inicio_operacion`,t1.`final_operacion`) AS totalhrs,
    t2.`usuario` AS "nombre_operador",
    t3.`usuario` AS "nombre_agencia"
    FROM `opsal`.`opsal_carga_descarga` AS t1
    LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.`ingresado_por` = t2.`codigo_usuario`)
    LEFT JOIN `opsal`.`opsal_usuarios` AS t3 ON (t1.`codigo_agencia` = t3.`codigo_usuario`)
    WHERE ID_carga_descarga="'.$ID_carga_descarga.'"
    ';
    
    $resultado = db_consultar($c);
    
    $f = db_fetch($resultado);
    
    $costo_por_hora_supervision = db_obtener('opsal_tarifas',$tipo, 'codigo_usuario="'.$f['codigo_agencia'].'"');
    
    //echo nl2br(print_r($f,true));
    
    echo '<div class="exportable" rel="'.$titulo.'">';
    echo '<br />';
    echo '<p style="color:black;font-size:1.2em;text-align:center;font-weight:bold;">'.$f['ID_buque'].'</p>';
    echo '<p style="color:black;font-size:1.1em;text-align:center;">'.$titulo.'</p>';
    echo '<br /><br />';
    echo '<table><tr>';
    echo '<td>';
    echo '<table style="width:400px;" class="opsal_tabla_borde_oscuro tabla-estandar tabla-centrada">';
    echo '<tr><th colspan="2">Inicio de Ops</th><th colspan="2">Fin de Ops</th></tr>';
    echo '<tr><th>Fecha</th><th>Hora</th><th>Fecha</th><th>Hora</th></tr>';
    echo '<tr><td>'.date('d-M-y',strtotime($f['inicio_operacion'])).'</td><td>'.date('H:i',strtotime($f['inicio_operacion'])).'</td><td>'.date('d-M-y',strtotime($f['final_operacion'])).'</td><td>'.date('H:i',strtotime($f['final_operacion'])).'</td></tr>';
    echo '</table>';
    echo '</td>';
    echo '<td style="vertical-align:middle;">&nbsp;=&nbsp;</td>';
    echo '<td>';
    echo '<table style="width:600px;" class="opsal_tabla_borde_oscuro tabla-estandar tabla-centrada">';
    echo '<tr><th>Total horas</th><th>Costo por hora</th><th>Subtotal</th><th>IVA 13%</th><th>Total</th></tr>';
    echo '<tr><td>'.$f['totalhrs'].'h</td><td>$'.$costo_por_hora_supervision.'</td><td>'.dinero($f['totalhrs']*$costo_por_hora_supervision).'</td><td>'.dinero(($f['totalhrs']*$costo_por_hora_supervision)*0.13).'</td><td>'.dinero(($f['totalhrs']*$costo_por_hora_supervision)*1.13).'</td></tr>';
    echo '</table>';
    echo '</td>';
    echo '</tr></table>';
    
    //***** DETALLE ******//
    
    $c = 'SELECT `categoria`, `ID_carga_descarga`, `cantidad`, `tipo_contenedor`, `patio` FROM `detalle_carga_descarga` WHERE ID_carga_descarga='.$ID_carga_descarga;
    $r = db_consultar($c);
    
    while ($detalle = db_fetch($r))
    {
        $detalles[$detalle['categoria']][] = $detalle['cantidad'].' x '.$detalle['tipo_contenedor'].' en '.$detalle['patio'];
    }
    
    echo '<p style="color:black;font-size:1.1em;text-align:center;margin-top:10px;">Detalle</p>';
    
    echo '<table style="width:800px;" class="opsal_tabla_borde_oscuro tabla-estandar tabla-centrada">';
    echo '<tr><th colspan="2">Import</th><th colspan="2">Export</th></tr>';
    echo '<tr><th>Vacios</th><th>Llenos</th><th>Vacios</th><th>Llenos</th></tr>';
    echo '<tr><td>'.join('<br />',@$detalles['importacion_vacios']).'</td><td>'.join('<br />',@$detalles['importacion_llenos']).'</td><td>'.join('<br />',@$detalles['exportacion_vacios']).'</td><td>'.join('<br />',@$detalles['exportacion_llenos']).'</td></tr>';
    echo '<tr>';
    
    echo '</tr>';
    echo '</table>';
    //***** DETALLE ******//
    
    echo '</div>';
    return;
}

//**** impresión ****//
if (isset($_GET['imprimir']) && is_numeric($_GET['imprimir']))
{
    $ID_carga_descarga = db_codex($_GET['imprimir']);
    control_supervision_carga_descarga__crear_impresion($ID_carga_descarga, 'p_supervision_carga_descarga','Supervisor de OPS');
    
    echo '<br /><hr /><br />';
    
    control_supervision_carga_descarga__crear_impresion($ID_carga_descarga, 'p_revision_marchamos','Revisión de marchamos');
    return;
}
//******************//

if (empty($_GET['fecha_inicio']) || empty($_GET['fecha_final']))
{
  $fecha_inicio = $fecha_final = mysql_date();
} else {
  $fecha_inicio = $_GET['fecha_inicio'];
  $fecha_final = $_GET['fecha_final'];
}

$c = '
SELECT
t1.`fecha_ingreso`,
t1.`ID_buque`,
t1.`ID_carga_descarga`,
t1.`ingresado_por`,
t1.`supervisor`,
t1.`marchamador`,
t1.`inicio_operacion`,
t1.`final_operacion`,
t2.`usuario` AS "nombre_operador",
t3.`usuario` AS "nombre_agencia"
FROM `opsal`.`opsal_carga_descarga` AS t1
LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.`ingresado_por` = t2.`codigo_usuario`)
LEFT JOIN `opsal`.`opsal_usuarios` AS t3 ON (t1.`codigo_agencia` = t3.`codigo_usuario`)
ORDER BY t1.`fecha_ingreso` DESC
';
$resultado = db_consultar($c);

$ultimos_ingresos = '';

if (mysqli_num_rows($resultado) == 0)
{
    $ultimos_ingresos .= '<p>No se encontraron ingresos</p>';
} else {
    $ultimos_ingresos .= '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    while ($f = mysqli_fetch_assoc($resultado))
    {
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f['ID_carga_descarga'],$f['nombre_operador'],$f['supervisor'],$f['marchamador'],$f['nombre_agencia'],$f['ID_buque'],$f['inicio_operacion'],$f['final_operacion'],'<a target="_blank" href="/control.supervision.carga.descarga.html?imprimir='.$f['ID_carga_descarga'].'">Imprimir</a>');
    }
    $ultimos_ingresos .= '<thead><tr><th>ID</th><th>Ingresó</th><th>Supervisó</th><th>Marchamó</th><th>Agencia</th><th>Buque</th><th>Inicio operación</th><th>Final operación</th><th>Acción</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}
?>
<h1>Elaboraciones de condiciones</h1><br />
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>