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

if (isset($_POST['guardar']))
{
    $DATOS = array_intersect_key($_POST,array_flip(array('codigo_agencia', 'ID_buque', 'numero_lineas', 'notas', 'inicio_operacion', 'final_operacion')));
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    
    if (db_agregar_datos('opsal_lineas_amarre',$DATOS) > 0)
    {
        echo '<hr /><p class="opsal_notificacion">Estado de lineas de amarre ingresadas exitosamente.</p><hr />';
        registrar('Condición elaborada para buque <b>'.$_POST['ID_buque'].'</b>','lineas');
    }
}
?>

<h1 class="opsal_titulo">Servicio de líneas de amarre</h1>
<div class="opsal_burbuja">
<form id="form_lineas_amarre" action="/lineas.de.amarre.html" method="post" autocomplete="off">
<table id="opsal_ims">
    <tbody>
        <tr>
            <td>Agencia</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td>
        </tr>
        <tr>
            <td>Identificación de buque</td><td><input type="text" id="ID_buque" name="ID_buque"/></td>
        </tr>
        
        <tr>
            <td>Número de lineas</td><td><input type="text" id="numero_lineas" name="numero_lineas"/></td>
        </tr>

        <tr>
            <td>Inicio de operación</td><td><input class="timepicker" type="text" id="inicio_operacion" name="inicio_operacion"/></td>
        </tr>
        <tr>
            <td>Fin de operación</td><td><input class="timepicker" type="text" id="final_operacion" name="final_operacion"/></td>
        
        <tr>
            <td>Notas de la operación</td><td><textarea name="notas"></textarea></td>
        </tr>
    </tbody>
</table>

<input type="submit" name="guardar" value="Guardar" />
</form>
</div>
<hr />
<?php
$ultimos_ingresos = '';

$c = 'SELECT
t1.`fecha_ingreso`,
t1.`ID_buque`,
t1.`ID_linea_amarre`,
t1.`ingresado_por`,
t1.`notas`,
t2.`usuario` AS "nombre_operador",
t3.`usuario` AS "nombre_agencia"
FROM `opsal`.`opsal_lineas_amarre` AS t1
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
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f['ID_linea_amarre'],$f['nombre_operador'],$f['nombre_agencia'],$f['ID_buque'],$f['gastos_adicionales'],$f['tarifa_por_hora'],$f['notas']);
    }
    $ultimos_ingresos .= '<thead><tr><th>ID</th><th>Ingresó</th><th>Agencia</th><th>ID buque</th><th>Gastos adicionales</th><th>Tarifa por hora</th><th>Notas</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}
?>
<h2>Últimos 20 ingresos</h2>
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
<script type="text/javascript">
    $(function(){
        $('#form_lineas_amarre').submit(function () {        
            if ($("select#codigo_agencia option:selected").val() == "")
            {
                alert ("Seleccione una agencia.");
                return false;
            }
            
            if ($("#ID_buque").val() == "")
            {
                alert ("Ingrese el número del buque.");
                return false;
            }
        });
        
        $('.timepicker').datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0}).datetimepicker('setDate', new Date());
    });
</script>