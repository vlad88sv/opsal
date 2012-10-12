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

$c = 'SELECT codigo_cheque, nombre FROM opsal_cheques ORDER BY nombre DESC';
$r = db_consultar($c);

$options_cheque  = '<option selected="selected" value="">Seleccione uno</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_cheque .= '<option value="'.$registro['codigo_cheque'].'">'.$registro['nombre'].'</option>';
    }
}
?>
<h1 class="opsal_titulo">Supervisión de carga y descarga de buques</h1>
<?php
if (isset($_POST['guardar']))
{
    
    $DATOS = array_intersect_key($_POST,array_flip(array('codigo_agencia', 'codigo_contenedor', 'ID_buque', 'inicio_operacion','final_operacion', 'codigo_cheque', 'notas','referencia_papel')));
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    
    if (db_agregar_datos('opsal_carga_descarga',$DATOS) > 0)
    {
        echo '<hr /><p class="opsal_notificacion">Registro de supervisión de carga y descarga ingresado exitosamente.</p><hr />';
    }
}
?>
<div class="opsal_burbuja">
<form id="form_carga_descarga" action="/supervision.carga.descarga.html" method="post" autocomplete="off">
<table id="opsal_ims">
    <tbody>
        <tr>
            <td>Agencia</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td>
        </tr>
        <tr>
            <td>Cheque</td><td><select id="codigo_cheque" name="codigo_cheque"><?php echo $options_cheque; ?></select></td>
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
            <td>Notas</td><td><textarea name="notas"></textarea></td>
        </tr>
    </tbody>
</table>

<input type="submit" name="guardar" id="guardar" value="Guardar" />
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
t1.`notas`,
t1.`referencia_papel`,
t1.`inicio_operacion`,
t1.`final_operacion`,
t2.`usuario` AS "nombre_operador",
t3.`usuario` AS "nombre_agencia"
FROM `opsal`.`opsal_carga_descarga` AS t1
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
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f['ID_carga_descarga'],$f['referencia_papel'],$f['nombre_operador'],$f['nombre_agencia'],$f['ID_buque'],$f['referencia_papel'],$f['inicio_operacion'],$f['final_operacion'],$f['notas']);
    }
    $ultimos_ingresos .= '<thead><tr><th>ID</th><th>No. Reporte</th><th>Ingresó</th><th>Agencia</th><th>Buque</th><th>Referencia</th><th>Inicio operación</th><th>Final operación</th><th>Notas</th></tr></thead>';
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
            
            if ($("select#codigo_cheque option:selected").val() == "")
            {
                alert ("Seleccione un cheque.");
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