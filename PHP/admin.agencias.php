<?php
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$agencia = '<table class="tabla-estandar opsal_tabla_borde_oscuro">';
$agencia .= '<tbody>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $agencia .= sprintf('<tr><td>%s</td><td>%s</td></tr>',$registro['usuario'],'<a href="/administracion.html?modo=agencias&submodo=editar&objetivo='.$registro['codigo_usuario'].'">Modificar</a> | <a href="/administracion.html?modo=agencias&submodo=tarifas&objetivo='.$registro['codigo_usuario'].'">Tarifas</a> | <a href="/administracion.html?modo=agencias&submodo=eliminar&objetivo='.$registro['codigo_usuario'].'">Eliminar</a>');
    }
}
$agencia .= '</tbody>';
$agencia .= '<thead>';
$agencia .= '<tr><th style="width:150px;">Agencia</th><th>Herramientas</th></tr>';
$agencia .= '</thead>';
$agencia .= '<table>';
?>
<h1 class="opsal_titulo">Control de agencias</h1>
<?php echo $agencia; ?>
<a href="/administracion.html?modo=agencias&submodo=agregar" class="boton">Agregar nueva agencia</a>