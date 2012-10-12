<?php
if (empty($_GET['fecha_inicio']) || empty($_GET['fecha_final']))
{
  $fecha_inicio = $fecha_final = mysql_date();
} else {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_final = $_GET['fecha_final'];
}

$c = 'SELECT CONCAT( x2,  "-", y2,  "-", nivel ) AS "Posición", DATE(  `fechatiempo_ingreso` ) AS  "Ingreso", DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) AS  "Días en patio", IF (`fechatiempo_egreso` IS NULL, "N/A" , DATE(  `fechatiempo_egreso` )) AS  "Salida", ( `arivu_ingreso` + INTERVAL 90 DAY) AS "Expiración ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 90 DAY, NOW( ) ) AS  "Días para exp. ARIVU", `codigo_contenedor` AS "Contenedor" FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) WHERE t1.fechatiempo_egreso IS NOT NULL AND DATE(t1.fechatiempo_egreso) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'"';
$r = db_consultar($c);
?>
<h1>Control de salidas</h1>
<div class="noimprimir" style="border-bottom:1px solid gray;">
    <form action="/control.ingresos.html" method="get">
        Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> <input type="submit" value="Filtrar" />
    </div>
</div>
<?php
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>