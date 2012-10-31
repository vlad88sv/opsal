<?php
$id_condicion = 0;

if (!empty($_GET['ID']) && is_numeric($_GET['ID']))
{
    $id_condicion = $_GET['ID'];
    
    $c = 'SELECT `codigo_agencia`, `codigo_contenedor`, `tipo_contenedor`, `referencia_papel`, `codigo_cheque`, `notas`, `fecha_ingreso` FROM `opsal_condiciones` WHERE `ID_condicion` = ' . $id_condicion;
    $r = db_consultar($c);
    $f = db_fetch($r);   
}
?>
<h1 class="opsal_titulo">Elaboración de condición</h1>
<?php
if (isset($_POST['guardar']))
{
    $DATOS = array_intersect_key($_POST,array_flip(array('ID_condicion', 'codigo_agencia', 'codigo_contenedor', 'codigo_cheque', 'notas','referencia_papel','fecha_ingreso','tipo_contenedor')));
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    if (db_reemplazar_datos('opsal_condiciones',$DATOS) > 0)
    {
        echo '<hr /><p class="opsal_notificacion">Condición ingresada exitosamente.</p><hr />';
        registrar('Condición elaborada para contenedor <b>'.$_POST['codigo_contenedor'].'</b>','condicion');
    }
}
?>
<div class="opsal_burbuja">
<?php if ($id_condicion > 0) echo '<p style="color:red;">MODO DE EDICIÓN</p>'; ?>
<form id="form_condicion" action="/elaboracion.de.condicion.html" method="post" autocomplete="off">
    <input type="hidden" name="ID_condicion" value="<?php echo $id_condicion; ?>" />
<table id="opsal_ims">
    <tbody>
        <tr>
            <td>Agencia</td><td><select name="codigo_agencia"><?php echo db_ui_opciones('codigo_usuario','usuario','opsal_usuarios','WHERE nivel="agencia"','','',@$f['codigo_agencia']); ?></select></td>
        </tr>
        <tr>
            <td>Fecha</td><td><input type="text" class="calendariocontiempo" name="fecha_ingreso" value="<?php echo @$f['codigo_agencia']; ?>" /></td>
        </tr>
        <tr>
            <td>Cheque</td><td><input type="text" id="codigo_cheque" name="codigo_cheque" value="<?php echo @$f['codigo_cheque']; ?>" /></td>
        </tr>
        <tr>
            <td>Contenedor</td><td><input type="text" id="codigo_contenedor" name="codigo_contenedor" value="<?php echo @$f['codigo_contenedor']; ?>" /></td>
        </tr>
        <tr>
            <td>Tipo</td><td><select name="tipo_contenedor"><?php echo db_ui_opciones('tipo_contenedor','nombre','opsal_tipo_contenedores','','','',@$f['tipo_contenedor']); ?></select></td>
        </tr>

        <tr>
            <td>Número EIR</td><td><input type="text" id="referencia_papel" name="referencia_papel" value="<?php echo @$f['referencia_papel']; ?>" /></td>
        </tr>
        <tr>
            <td>Notas de la revisión</td><td><textarea name="notas"><?php echo @$f['notas']; ?></textarea></td>
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
t1.`ID_condicion`,
t1.`codigo_agencia`,
t1.`fecha_ingreso`,
t1.`codigo_cheque`,
t1.`codigo_contenedor`,
t1.`tipo_contenedor`,
t1.`referencia_papel`,
t1.`notas`,
t1.`fecha_ingreso`,
t2.`usuario` AS "nombre_agencia",
t4.`usuario` AS "nombre_operador"
FROM `opsal`.`opsal_condiciones` AS t1 LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.codigo_agencia = t2.codigo_usuario) LEFT JOIN `opsal`.`opsal_usuarios` AS t4 ON (t1.ingresado_por = t4.codigo_usuario) 
ORDER BY t1.`ID_condicion` DESC LIMIT 20
';
$resultado = db_consultar($c);

if (mysqli_num_rows($resultado) == 0)
{
    $ultimos_ingresos .= '<p>No se encontraron ingresos</p>';
} else {
    $ultimos_ingresos .= '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    while ($f = mysqli_fetch_assoc($resultado))
    {
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>','<a href="/elaboracion.de.condicion.html?ID='.$f['ID_condicion'].'">'.$f['ID_condicion'].'</a>',$f['fecha_ingreso'],$f['nombre_operador'],$f['nombre_agencia'],$f['codigo_cheque'],$f['codigo_contenedor'],$f['tipo_contenedor'],$f['referencia_papel'],ellipsis($f['notas'],35));
    }
    $ultimos_ingresos .= '<thead><tr><th>ID</th><th>Fecha elaborada</th><th>Ingresó</th><th>Agencia</th><th>Cheque</th><th>Contenedor</th><th>Tipo</th><th>Referencia</th><th>Revisión</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}
?>
<h2>Últimos 20 ingresos</h2>
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
<script type="text/javascript">
    $(function(){
        $('#form_condicion').submit(function () {        
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
            
            if ($("#codigo_contenedor").val() == "")
            {
                alert ("Ingrese un identificador de contenedor.");
                return false;
            }
            
            if ($("#referencia_papel").val() == "")
            {
                alert ("Ingrese el número de serie de la hoja física utilizada.");
                return false;
            }
            
        });
        
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
        $(".calendariocontiempo").datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0}).datetimepicker('setDate', new Date());
    });
</script>