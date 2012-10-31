<?php
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Seleccione una</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<h1 class="opsal_titulo">Marchamos</h1>
<?php
if (isset($_POST['guardar']))
{
    
    $DATOS = array_intersect_key($_POST,array_flip(array('codigo_agencia', 'codigo_contenedor', 'ID_buque', 'inicio_operacion','final_operacion', 'supervisor', 'marchamador', 'notas','referencia_papel','importacion_vacios','importacion_llenos', 'exportacion_vacios','exportacion_llenos','detalle_importacion_vacios','detalle_importacion_llenos', 'detalle_exportacion_vacios','detalle_exportacion_llenos')));
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    
    $ID = db_agregar_datos('opsal_marchamos',$DATOS);
    
    if ($ID > 0)
    {
        registrar('Elaboración de marchamos (ID: <b>'.$ID.'</b>)','marchamos');
        echo '<hr /><p class="opsal_notificacion">Registro de marchamos ingresado exitosamente.</p><hr />';
    }
}
?>
<div class="opsal_burbuja">
<form id="form_carga_descarga" action="/marchamos.html" method="post" autocomplete="off">
<table class="opsal_tabla_ancha">
    <tr>
    <td>
    <table id="opsal_ims">
        <tbody>
            <tr>
                <td>Agencia</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td>
            </tr>
            <tr>
                <td>Supervisor</td><td><input type="text" name="supervisor" value="" /></td>
            </tr>
            <tr>
                <td>Marchamador</td><td><input type="text" name="marchamador" value="" /></td>
            </tr>
            <tr>
                <td>Buque</td><td><input type="text" id="ID_buque" name="ID_buque"/></td>
            </tr>
            <tr>
                <td>Referencia en papel</td><td><input type="text" id="referencia_papel" name="referencia_papel" /></td>
            </tr>
            <tr>
                <td>Inicio de operación</td><td><input class="timepicker" type="text" id="inicio_operacion" name="inicio_operacion"/></td>
            </tr>
            <tr>
                <td>Fin de operación</td><td><input class="timepicker" type="text" id="final_operacion" name="final_operacion"/></td>
            </tr>
            <tr>
                <td>Notas de la revisión</td><td><textarea name="notas" style="width: 500px;height:300px;"></textarea></td>
            </tr>
        </tbody>
    </table>
    </td>
    <td style="vertical-align: top;">
        <table class="opsal_tabla_ancha">
            <tr><th colspan="2">Import</th><th colspan="2">Export</th></tr>
            <tr><td>Vacios</td><td>Llenos</td> <td>Vacios</td><td>Llenos</td></tr>
            <tr>
                <td><input name="importacion_vacios" type="text" style="width:90px;" value="0" /></td>
                <td><input name="importacion_llenos" type="text" style="width:90px;" value="0" /></td>
                <td><input name="exportacion_vacios" type="text" style="width:90px;" value="0" /></td>
                <td><input name="exportacion_llenos" type="text" style="width:90px;" value="0" /></td>
            </tr>
            <tr><td>Detalle</td><td>Detalle</td> <td>Detalle</td><td>Detalle</td></tr>
            <tr>
                <td><textarea name="detalle_importacion_vacios" style="width:90px;height: 150px;"></textarea></td>
                <td><textarea name="detalle_importacion_llenos" style="width:90px;height: 150px;"></textarea></td>
                <td><textarea name="detalle_exportacion_vacios" style="width:90px;height: 150px;"></textarea></td>
                <td><textarea name="detalle_exportacion_llenos" style="width:90px;height: 150px;"></textarea></td>
            </tr>
        </table>
    </td>
    </tr>
</table>
<br /><hr /><br />
<div style="text-align: center;"><input type="submit" name="guardar" id="guardar" value="Guardar" /></div>
</form>
</div>
<hr />
<?php
$ultimos_ingresos = '';

$c = '
SELECT
t1.`fecha_ingreso`,
t1.`ID_buque`,
t1.`ID_carga_descarga`,
t1.`ingresado_por`,
t1.`supervisor`,
t1.`marchamador`,
t1.`notas`,
t1.`referencia_papel`,
t1.`inicio_operacion`,
t1.`final_operacion`,
t2.`usuario` AS "nombre_operador",
t3.`usuario` AS "nombre_agencia"
FROM `opsal`.`opsal_machamos` AS t1
LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.`ingresado_por` = t2.`codigo_usuario`)
LEFT JOIN `opsal`.`opsal_usuarios` AS t3 ON (t1.`codigo_agencia` = t3.`codigo_usuario`)
ORDER BY t1.`fecha_ingreso` DESC LIMIT 20
';
$resultado = db_consultar($c);

if (mysqli_num_rows($resultado) == 0)
{
    $ultimos_ingresos .= '<p>No se encontraron ingresos</p>';
} else {
    $ultimos_ingresos .= '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    while ($f = mysqli_fetch_assoc($resultado))
    {
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f['ID_carga_descarga'],$f['referencia_papel'],$f['nombre_operador'],$f['supervisor'],$f['marchamador'],$f['nombre_agencia'],$f['ID_buque'],$f['referencia_papel'],$f['inicio_operacion'],$f['final_operacion'],$f['notas']);
    }
    $ultimos_ingresos .= '<thead><tr><th>ID</th><th>No. Reporte</th><th>Ingresó</th><th>Supervisó</th><th>Marchamó</th><th>Agencia</th><th>Buque</th><th>Referencia</th><th>Inicio operación</th><th>Final operación</th><th>Notas</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}
?>
<h2>Últimos 20 ingresos</h2>
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
<script type="text/javascript">
    $(function(){
        $('.timepicker').datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0}).datetimepicker('setDate', new Date());
        
        $('#form_carga_descarga').submit(function () {       
            if ($("select#codigo_agencia option:selected").val() == "")
            {
                alert ("Seleccione una agencia.");
                return false;
            }
            
            if ($("#codigo_cheque").val() == "")
            {
                alert ("Ingrese el nombre del cheque.");
                return false;
            }
            
            if ($("#ID_buque").val() == "")
            {
                alert ("Ingrese el nombre del buque.");
                return false;
            }
            
            if ($("#referencia_papel").val() == "")
            {
                alert ("Ingrese el número de serie de la hoja física utilizada.");
                return false;
            }            
        });
    });    
</script>