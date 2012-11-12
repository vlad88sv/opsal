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
$c = 'SELECT t2.usuario AS "Naviera", `codigo_contenedor` AS "Contenedor", `tipo_contenedor` AS "Tipo", `referencia_papel` AS "EIR", `codigo_cheque` AS "Cheque", `fecha_ingreso` AS "Fecha" FROM `opsal_condiciones` AS t1 LEFT JOIN opsal_usuarios AS t2 ON t1.codigo_agencia = t2.codigo_usuario  WHERE DATE(fecha_ingreso) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" '.$agencia;
$r = db_consultar($c);
?>
<div class="noimprimir">
<h1>Generar reporte de elaboración de condiciones</h1>
  <form action="" method="get">
      Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> Agencia: <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> <input type="submit" value="Filtrar" />
  </form>
  <hr />
  <br />
</div>
<?php
$fechas = ($fecha_inicio == $fecha_final ? 'del <b>' . $fecha_inicio . '</b>' : 'de <b>'.$fecha_inicio.'</b> a <b>'.$fecha_final.'</b>');
echo '<div class="exportable" rel="Reporte de elaboración de condiciones - '.$fechas.' para '.$agencias[@$_GET['codigo_agencia']].'">';
echo '<h1>Reporte de elaboración de condiciones de '.$fechas.' para <b>'.$agencias[@$_GET['codigo_agencia']].'</b></h1>';
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>