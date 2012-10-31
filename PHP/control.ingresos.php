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

$agencias = array('' => 'todas las agencias');
$options_agencia = '<option selected="selected" value="">Mostrar todas</option>';
if (mysqli_num_rows($r) > 0)
{
  while ($registro = mysqli_fetch_assoc($r))
  {
    $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    $agencias[$registro['codigo_usuario']] = $registro['usuario'];
  }
}

$agencia = (!empty($_GET['codigo_agencia']) ? ' AND codigo_agencia="'.$_GET['codigo_agencia'] .'"' : '');
$c = 'SELECT CONCAT(\'<a href="#" rel="\',codigo_contenedor,\'" class="ejecutar_busqueda_codigo_contenedor">\',`codigo_contenedor`,\'</a>\') AS "Contenedor", tipo_contenedor AS "Tipo", CONCAT( x2,  "-", y2,  "-", nivel ) AS "Posición", DATE(  `fechatiempo_ingreso` ) AS  "Ingreso", DATEDIFF( COALESCE(fechatiempo_egreso,NOW()) ,  `fechatiempo_ingreso` ) AS  "Días en patio", IF (`fechatiempo_egreso` IS NULL, "N/A" , DATE(  `fechatiempo_egreso` )) AS  "Salida", arivu_referencia AS "# ARIVU", ( `arivu_ingreso` + INTERVAL 90 DAY) AS "Expiración ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 90 DAY, COALESCE(fechatiempo_egreso,NOW()) ) AS  "Días para exp. ARIVU", `transportista_ingreso` AS "Transportista", `observaciones_ingreso` AS "Observaciones" FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) WHERE DATE(t1.fechatiempo_ingreso) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" '.$agencia;
$r = db_consultar($c);
?>
<div class="noimprimir">
<h1>Generar reporte de recepciones</h1>
  <form action="" method="get">
      Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> Agencia: <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> <input type="submit" value="Filtrar" />
  </form>
  <hr />
  <br />
</div>
<?php
$fechas = ($fecha_inicio == $fecha_final ? 'del <b>' . $fecha_inicio . '</b>' : 'de <b>'.$fecha_inicio.'</b> a <b>'.$fecha_final.'</b>');
$titulo =  '<h1>Reporte de <b>'.mysqli_num_rows($r).'</b> recepciones '.$fechas.' para <b>'.$agencias[@$_GET['codigo_agencia']].'</b></h1>';
echo '<div class="exportable" rel="'. strip_tags($titulo). '">';
echo '<h1>'.$titulo.'</titulo>';
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';

echo '<br />';
echo '<div class="exportable">';
echo '<h2>Deglose por tamaño de contenedor</h2>';
$c = 'SELECT tipo_contenedor AS "Tipo", COUNT(*) AS "Cantidad" FROM `opsal_ordenes` AS t1 WHERE DATE(t1.fechatiempo_ingreso) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" '.$agencia .' GROUP BY tipo_contenedor' ;
$r = db_consultar($c);
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>