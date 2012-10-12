<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
    });
</script>
<?php
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Cualquier agencia</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}

$CONDICIONES = '';

if (!empty($_GET['cf']) && is_numeric($_GET['cf']))
{
    $CONDICIONES .= ' AND t1.`codigo_factura`='.$_GET['cf'];
}

$c = 'SELECT `codigo_factura`, t2.`usuario` AS "operador" , `codigo_agencia`, t3.`usuario` AS "agencia", `fechatiempo`, `periodo_inicio`, `periodo_final`, `anexo`, `flag_enviada`, `flag_cobrada`, `flag_anulada` FROM `opsal_facturas` AS t1 LEFT JOIN `opsal_usuarios` AS t2 USING(codigo_usuario) LEFT JOIN `opsal_usuarios` AS t3 ON t1.codigo_agencia = t3.codigo_usuario WHERE 1 '.$CONDICIONES;
$r = db_consultar($c);

echo '<h1>Control de facturas</h1>';

echo '<table class="opsal_tabla_ancha  opsal_tabla_borde_oscuro">';
echo '<tr><th>Fecha de emisión</th><th>Discriminadores</th><th>Agencia</th><th>Acción</th></tr>';
echo '<tr><td><input type="text" class="calendario" value="" /><input type="checkbox" value="1" checked="checked" name="contar_fecha_emision" /></td><td><select><option value="">Cobradas y no cobradas</option><option>Solo cobradas</option><option>Solo no cobradas</option></select> y <select><option value="">Enviadas y no enviadas</option><option>Solo enviadas</option><option>Solo no enviadas</option></select> y <select><option value="">Anuladas y no anuladas</option><option>Solo anuladas</option><option>Solo no anuladas</option></select></td><td><select id="codigo_agencia" name="codigo_agencia">'.$options_agencia.'</td><td><input type="submit" value="Filtrar" /></td></tr>';
echo '</table>';

if (mysqli_num_rows($r) == 0)
{
    echo '<p>No se encontraron facturas que concuerden con sus especificaciones</p>';
    return;
}

echo '<table class="opsal_tabla_ancha  opsal_tabla_borde_oscuro">';
while ($f = mysqli_fetch_assoc($r))
{
    $c = ' SELECT SUM(grabado) AS total FROM `opsal_factura_detalles` WHERE codigo_factura = '.$f['codigo_factura'];
    $rt = db_consultar($c);
    $ft = mysqli_fetch_assoc($rt);
    
    $f['total'] = $ft['total'];
    $datos = '<table class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
    $datos .= '<tr><th>CF</th><th>Agencia</th><th>Inicio de periodo</th><th>Final de periodo</th><th>Emitida por</th><th>Fecha emisión</th><th>Total</th></tr>';
    $datos .= '<tr><td>'.$f['codigo_factura'].'</td><td>'.$f['agencia'].'</td><td>'.$f['periodo_inicio'].'</td><td>'.$f['periodo_final'].'</td><td>'.$f['operador'].'</td><td>'.$f['fechatiempo'].'</td><td>$'.$f['total'].'</td></tr>';
    $datos .= '</table>';

    $controles = '<input type="checkbox" class="flag_enviada" '.($f['flag_enviada'] == '1' ? 'checked="checked"' : '').' value="1" /> Enviada&nbsp';
    $controles .= '<input type="checkbox" class="flag_cobrada" '.($f['flag_cobrada'] == '1' ? 'checked="checked"' : '').' value="1" /> Cobrada&nbsp';
    $controles .= '<input type="checkbox" class="flag_anulada" '.($f['flag_anulada'] == '1' ? 'checked="checked"' : '').' value="1" /> Anulada<br />';
    $controles .= '<input type="button" class="imprimir_legal" value="Impresión legal" />&nbsp;';
    $controles .= '<input type="button" class="imprimir_anexo" value="Impresión anexo" />&nbsp;';
    $controles .= '<input type="button" class="imprimir_anexo" value="Ver anexo" />';
    
    echo sprintf('<tr><td style="padding:10px;">%s</td><td style="padding:10px;">%s</td></tr>',$datos,$controles);
}
echo '</table>';
?>