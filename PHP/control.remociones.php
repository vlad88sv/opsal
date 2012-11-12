<?php
if (empty($_GET['fecha_inicio']) || empty($_GET['fecha_final']))
{
  $fecha_inicio = $fecha_final = mysql_date();
} else {
  $fecha_inicio = $_GET['fecha_inicio'];
  $fecha_final = $_GET['fecha_final'];
}

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$agencias = array('' => 'cualquier agencia');
$options_agencia = '<option selected="selected" value="">Mostrar todas</option>';
if (mysqli_num_rows($r) > 0)
{
  while ($registro = mysqli_fetch_assoc($r))
  {
    $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    $agencias[$registro['codigo_usuario']] = $registro['usuario'];
  }
}

$agencia = (!empty($_GET['codigo_agencia']) ? ' AND t0.cobrar_a="'.$_GET['codigo_agencia'] .'"' : '');

$c = 'SELECT t0.fechatiempo AS "Fecha", `usuario` AS "Naviera", CONCAT(\'<a href="#" rel="\',codigo_contenedor,\'" class="ejecutar_busqueda_codigo_contenedor">\',`codigo_contenedor`,\'</a>\') AS "Contenedor", CONCAT( t2.x2,  "-", t2.y2,  "-", t0.nivel ) AS "Nueva posición", DATE(  `fechatiempo_ingreso` ) AS  "Ingreso", DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) AS "<acronym title=\'Días en patio\'>DEA</acronym>", IF (`fechatiempo_egreso` IS NULL, "N/A" , DATE(  `fechatiempo_egreso` )) AS  "Salida", ( `arivu_ingreso` + INTERVAL 90 DAY) AS "Exp. ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 90 DAY, NOW( ) ) AS  "<acronym title=\'Días para expiración de ARIVU\'>DPEA</acronym>" FROM  `opsal_movimientos` AS t0 LEFT JOIN `opsal_ordenes` AS t1 USING(codigo_orden) LEFT JOIN  `opsal_posicion` AS t2 ON t0.`codigo_posicion` = t2.`codigo_posicion` LEFT JOIN opsal_usuarios AS t3 ON t0.cobrar_a=t3.codigo_usuario WHERE motivo="remocion" AND '. $agencia .' DATE(t0.fechatiempo) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" ORDER BY t1.codigo_contenedor ASC, t0.fechatiempo DESC';
$r = db_consultar($c);
?>
<h1>Control de remociones</h1>
<div class="noimprimir">
    <form action="/control.remociones.html" method="get">
        Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> Cobrado a: <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> <input type="submit" value="Filtrar" />
    </form>
  <hr />
  <br />
</div>
<?php
echo '<div class="exportable">';
echo '<h1>Mostrando <b>'.mysqli_num_rows($r).'</b> remociones de <b>'.$fecha_inicio.'</b> a <b>'.$fecha_final.'</b> para <b>'.$agencias[@$_GET['codigo_agencia']].'</b></h1>';
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';

echo '<br />';
echo '<div class="exportable" rel="Deglose por tamaño de contenedor - remociones">';
echo '<h2>Deglose por tamaño de contenedor</h2>';
$c = 'SELECT tipo_contenedor AS "Tipo", COUNT(*) AS "Cantidad" FROM  `opsal_movimientos` AS t0 LEFT JOIN `opsal_ordenes` AS t1 USING(codigo_orden) WHERE motivo="remocion" AND '. $agencia .' DATE(t0.fechatiempo) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" GROUP BY tipo_contenedor' ;
$r = db_consultar($c);
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>