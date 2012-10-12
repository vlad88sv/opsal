<?php
// Salidas de contenedores por TEUS en los ultimos 30 dias
$c = '
SELECT DATE( t1.fechatiempo_egreso ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE t1.fechatiempo_egreso IS NOT NULL
GROUP BY DATE( t1.fechatiempo_egreso ) 
ORDER BY DATE( t1.fechatiempo_egreso ) ASC
LIMIT 30
';
$rSalidas = db_consultar($c);


// Ingreso de contenedores por TEUS en los ultimos 12 meses
$c = '
SELECT DATE_FORMAT(t1.fechatiempo_egreso,"%m/%y") AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE t1.fechatiempo_egreso IS NOT NULL
GROUP BY DATE_FORMAT(t1.fechatiempo_egreso,"%m/%y") 
ORDER BY DATE_FORMAT(t1.fechatiempo_egreso,"%m/%y") ASC
LIMIT 12
';

$rSalidasMes = db_consultar($c);

// Ingreso de contenedores por TEUS en los ultimos 5 años
$c = '
SELECT YEAR( t1.fechatiempo_egreso ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE t1.fechatiempo_egreso IS NOT NULL
GROUP BY YEAR( t1.fechatiempo_egreso ) 
ORDER BY YEAR( t1.fechatiempo_egreso ) ASC
LIMIT 5';

$rSalidasAno = db_consultar($c);
?>
<h1>Salidas de contenedores (por TEU)</h1>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v1"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rSalidas,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v2"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rSalidasMes,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v3"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rSalidasAno,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>

<script type="text/javascript">
  function drawVisualization() {
<?php echo gCol($rSalidas,"TEU en los ultimos 30 días con ingresos",'v1','TEU','Cantidad'); ?>
<?php echo gCol($rSalidasMes,"TEU en los ultimos 12 meses con ingresos",'v2','TEU','Cantidad'); ?>
<?php echo gCol($rSalidasAno,"TEU en los ultimos 5 años con ingresos",'v3','TEU','Cantidad'); ?>
  }
  google.setOnLoadCallback(drawVisualization);
</script>