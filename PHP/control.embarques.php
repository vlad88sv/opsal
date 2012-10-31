<?php
$c = 'SELECT buque_egreso, COUNT(*) AS "cantidad", CONCAT(DATE(MIN(`fechatiempo_egreso`)), "/", DATE(MAX(`fechatiempo_egreso`))) AS "fecha_embarque" FROM opsal_ordenes WHERE tipo_salida="embarque" GROUP BY `buque_egreso` ORDER BY MAX(`fechatiempo_egreso`) DESC LIMIT 20';
    $r = db_consultar($c);
    
    $options_buque = '<option selected="selected" value="">Seleccione un buque</option>';
    if (mysqli_num_rows($r) > 0)
    {
        while ($registro = mysqli_fetch_assoc($r))
        {
            $options_buque .= '<option value="'.$registro['buque_egreso'].'">'.$registro['buque_egreso']. ' - '. $registro['fecha_embarque'] . ' - ['. $registro['cantidad'] .' despachos]</option>';
        }
    }
    
    $embarques = '<select id="buque" name="buque">'.$options_buque.'</select>';

  
$embarque = (empty($_GET['buque']) ? '' : 'OR t1.fechatiempo_egreso IS NOT NULL AND t1.tipo_salida="embarque" AND t1.buque_egreso="'.db_codex($_GET['buque']).'"');

$c = 'SELECT CONCAT(\'<a href="#" rel="\',codigo_contenedor,\'" class="ejecutar_busqueda_codigo_contenedor">\',`codigo_contenedor`,\'</a>\') AS "Contenedor", tipo_contenedor AS "Tipo", CONCAT( x2,  "-", y2,  "-", nivel ) AS "Posición", DATE(  `fechatiempo_ingreso` ) AS  "Ingreso", DATEDIFF( COALESCE(fechatiempo_egreso,NOW()) ,  `fechatiempo_ingreso` ) AS  "<acronym title=\'Días en patio\'>DEA</acronym>", IF (`fechatiempo_egreso` IS NULL, "N/A" , DATE(  `fechatiempo_egreso` )) AS  "Salida", arivu_referencia AS "# ARIVU", ( `arivu_ingreso` + INTERVAL 90 DAY) AS "Expiración ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 90 DAY, COALESCE(fechatiempo_egreso,NOW()) ) AS  "<acronym title=\'Días para expiración de ARIVU\'>DPEA</acronym>", `transportista_egreso` AS "Transportista", `buque_egreso` AS "Buque", tipo_salida AS "Tipo salida", `observaciones_egreso` AS "Observaciones" FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) WHERE 0 '.$embarque;
$r = db_consultar($c);
?>
<div class="noimprimir">
  <h1>Control de embarques</h1>
    <form action="" method="get">
        <?php echo $embarques; ?>&nbsp;
        <input type="submit" value="Filtrar" />
    </form>
    <hr />
    <br />
</div>
<?php
if (empty($_GET['buque'])) return;
$titulo = 'Reporte de embarque de <b>'.mysqli_num_rows($r).'</b> contenedores para el buque <b>'.$_GET['buque'].'</b>';


echo '<div class="exportable" rel="'.strip_tags($titulo).'">';
echo '<h1>'.$titulo.'</h1>';
echo '<div style="overflow-x:auto;">';
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-una-linea"');
echo '</div>';

echo '<br />';
echo '<div class="exportable">';
echo '<h2>Deglose por tamaño de contenedor</h2>';
$c = 'SELECT tipo_contenedor AS "Tipo", COUNT(*) AS "Cantidad" FROM `opsal_ordenes` AS t1 WHERE t1.fechatiempo_egreso IS NOT NULL AND t1.tipo_salida="embarque" AND t1.buque_egreso="'.db_codex($_GET['buque']).'" GROUP BY tipo_contenedor' ;
$r = db_consultar($c);
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';

?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>