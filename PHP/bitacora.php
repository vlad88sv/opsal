<?php
$fecha = (empty($_POST['fecha']) ? mysql_date() : $_POST['fecha']);

$c = 'SELECT contexto FROM opsal_bitacora WHERE DATE(`fechatiempo`)="'.$fecha.'" GROUP BY contexto';
$r = db_consultar($c);

$options_contexto = '<option selected="selected" value="">Cualquier contexto</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_contexto .= '<option value="'.$registro['contexto'].'">'.$registro['contexto'].'</option>';
    }
}

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel<>"agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_usuarios = '<option selected="selected" value="">Cualquier usuario</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_usuarios .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<h1 class="opsal_titulo">Bitácora</h1>
<form action="/bitacora.html" method="post">
    Fecha: <input type="text" class="calendario" name="fecha" value="" /> Contexto: <select name="contexto"><?php echo $options_contexto; ?></select> Usuario: <select name="codigo_usuario"><?php echo $options_usuarios; ?></select> <input type="submit" value="filtrar" />
</form>
<hr />
<?php
$codigo_usuario = (empty($_POST['codigo_usuario']) ? '' : ' AND `codigo_usuario`=' . $_POST['codigo_usuario']);
$contexto = (empty($_POST['contexto']) ? '' : ' AND `contexto`="' . $_POST['contexto'].'"');
$c = "SELECT `codigo_bitacora` AS 'Código', TIME(`fechatiempo`) AS 'Hora', `contenido` AS 'Contenido', `contexto` AS 'Contexto', `usuario` AS 'Usuario' FROM `opsal_bitacora` LEFT JOIN `opsal_usuarios` USING (codigo_usuario) WHERE DATE(`fechatiempo`) = '$fecha' $codigo_usuario $contexto ORDER BY fechatiempo DESC";
$resultado = db_consultar($c);
echo db_ui_tabla($resultado,'class="opsal_tabla_ancha"');
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
    });
</script>