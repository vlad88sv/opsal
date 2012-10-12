<h1 class="opsal_titulo">OPSAL - Current status of containers stored in yard.</h1>
<?php
function gpie($resultado, $titulo, $divID, $columna, $fila)
{
    
    $buffer = "
    var data = google.visualization.arrayToDataTable([
      ['$columna', '$fila'],
    ";
    
    while ($f = mysqli_fetch_array($resultado))
    {
        $tbuffer[] = "['".$f[0]."', ".$f[1]."]";
    }
    
    $buffer .= join(', ', $tbuffer);
    
    $buffer .= "]);";
  
    $buffer .= "new google.visualization.PieChart(document.getElementById('$divID')).draw(data, {title:'$titulo'});";
    
    return $buffer;
}

function gCol($resultado, $titulo, $divID, $columna, $fila)
{
    
    $buffer = "
    var data = google.visualization.arrayToDataTable([
      ['$columna', '$fila'],
    ";
    
    while ($f = mysqli_fetch_array($resultado))
    {
        $tbuffer[] = "['".$f[0]."', ".$f[1]."]";
    }
    
    $buffer .= join(', ', $tbuffer);
    
    $buffer .= "]);";
  
    $buffer .= "new google.visualization.ColumnChart(document.getElementById('$divID')).draw(data, {title:'$titulo'});";
    
    return $buffer;
}
?>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('visualization', '1', {packages: ['corechart']});
</script>
<?php
$_POST['codigo_agencia'] = _F_usuario_cache('codigo_usuario');

if (!empty($_POST['codigo_agencia']))
{
// REPORTE de tipo de contenedores por patio
$c = '
SELECT t3.nombre AS  "Type of container", COUNT(*) AS  "Qty."
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE  `estado` =  "dentro" AND t1.codigo_agencia="'.$_POST['codigo_agencia'].'"
GROUP BY t1.tipo_contenedor
';

$rTipos = db_consultar($c);

$tTipos = 0;

while ($f = mysqli_fetch_array($rTipos))
{
    $tTipos += $f[1];
}
mysqli_data_seek($rTipos,0);

// REPORTE de clase de contenedores por patio
$c = '
SELECT t1.clase AS  "Class", COUNT(*) AS  "Qty."
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE  `estado` =  "dentro" AND t1.codigo_agencia="'.$_POST['codigo_agencia'].'"
GROUP BY t1.clase
';

$rClases = db_consultar($c);

} //--$_POST['codigo_agencia']
?>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v1"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rTipos,'class="opsal_tabla_ancha  tabla-estandar"'); ?><br /><p>Total de contenedores: <b><?php echo $tTipos; ?></b></p></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v2"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rClases,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<br /><hr />
<h1>Containers stored in yard</h1>
<?php
$c = 'SELECT CONCAT( x2,  "-", y2,  "-", nivel ) AS "Position", DATE(  `fechatiempo_ingreso` ) AS  "Entry date", DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) AS  "Number of days in yard", DATE(  `arivu_egreso` ) AS "ARIVU expiration date", DATEDIFF(  `arivu_egreso` , NOW( ) ) AS  "Days until ARIVU expiration date", `codigo_contenedor` AS "Container ID" FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) WHERE estado =  "dentro" AND codigo_agencia="'.$_POST['codigo_agencia'].'" ORDER BY  `fechatiempo_ingreso` ASC';
$resultado = db_consultar($c);
echo db_ui_tabla($resultado,'class="opsal_tabla_ancha  tabla-estandar"');
?>
<script type="text/javascript">
  function drawVisualization() {
<?php echo gpie($rTipos,"Type of container",'v1','Type of container','TEU'); ?>
<?php echo gpie($rClases,"Class of container",'v2','Class of container','Qty.'); ?>
  }
  google.setOnLoadCallback(drawVisualization);
</script>