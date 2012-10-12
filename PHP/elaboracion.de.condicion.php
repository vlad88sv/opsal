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
<h1 class="opsal_titulo">Elaboración de condición</h1>
<?php
if (isset($_POST['guardar']))
{
    $DATOS = array_intersect_key($_POST,array_flip(array('codigo_agencia', 'codigo_contenedor', 'codigo_cheque', 'notas','referencia_papel')));
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    if (db_agregar_datos('opsal_condiciones',$DATOS) > 0)
    {
        echo '<hr /><p class="opsal_notificacion">Condición ingresada exitosamente.</p><hr />';
        registrar('Condición elaborada para contenedor <b>'.$_POST['codigo_contenedor'].'</b>','condicion');
    }
}
?>
<div class="opsal_burbuja">
<form id="form_condicion" action="/revision.de.condicion.html" method="post" autocomplete="off">
<table id="opsal_ims">
    <tbody>
        <tr>
            <td>Agencia</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td>
        </tr>
        <tr>
            <td>Cheque</td><td><select id="codigo_cheque" name="codigo_cheque"><?php echo $options_cheque; ?></select></td>
        </tr>
        <tr>
            <td>Contenedor</td><td><input type="text" id="codigo_contenedor" name="codigo_contenedor"/></td>
        </tr>
        <tr>
            <td>Referencia en papel</td><td><input type="text" id="referencia_papel" name="referencia_papel"/></td>
        </tr>
        <tr>
            <td>Notas de la revisión</td><td><textarea name="notas"></textarea></td>
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
t1.`codigo_cheque`,
t1.`codigo_contenedor`,
t1.`referencia_papel`,
t1.`notas`,
t1.`fecha_ingreso`,
t2.`usuario` AS "nombre_agencia",
t3.`nombre` AS "nombre_cheque",
t4.`usuario` AS "nombre_operador"
FROM `opsal`.`opsal_condiciones` AS t1 LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.codigo_agencia = t2.codigo_usuario) LEFT JOIN `opsal`.`opsal_cheques` AS t3 USING(codigo_cheque) LEFT JOIN `opsal`.`opsal_usuarios` AS t4 ON (t1.ingresado_por = t4.codigo_usuario) 
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
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f['ID_condicion'],$f['nombre_operador'],$f['nombre_agencia'],$f['nombre_cheque'],$f['codigo_contenedor'],$f['referencia_papel'],$f['fecha_ingreso'],$f['notas']);
    }
    $ultimos_ingresos .= '<thead><tr><th>ID</th><th>Ingresó</th><th>Agencia</th><th>Cheque</th><th>Contenedor</th><th>Referencia</th><th>Revisión</th><th>Notas</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}
?>
<h2>Últimos 20 ingresos</h2>
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
<script type="text/javascript">    
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
</script>