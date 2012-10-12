<?php
$mensaje = '';

if (isset($_POST['iniciar_proceder']))
{
    
    ob_start();
    $ret = _F_usuario_acceder($_POST['iniciar_campo_usuario'],$_POST['iniciar_campo_clave']);
    $buffer = ob_get_clean();
    if ($ret != 1)
    {
        $mensaje .= '<p style="color:red;">Verify your login credentials.</p>';
    }
}

if (S_iniciado())
{
    if (!empty($_POST['iniciar_retornar']))
    {
        header("location: ".$_POST['iniciar_retornar']);
    } else {
        header("location: ./");
    }
    return;
}

$HEAD_titulo = PROY_NOMBRE . ' - OPSAL Login';

if (isset($_GET['ref']))
    $_POST['iniciar_retornar'] = $_GET['ref'];
$retorno = empty($_POST['iniciar_retornar']) ? PROY_URL : $_POST['iniciar_retornar'];
?>
<form id="opsal_inicio" method="POST" action="/" style="border:2px solid #CCC;border-radius:10px;width:500px;margin:10px auto;padding:100px 0;">
    <h1 style="color:#555;text-align:center;font-size:1.5em;">OCY Login</h1>
    <?php echo $mensaje; ?>
    <input type="hidden" value="<?php echo $retorno; ?>" style="" class="" name="iniciar_retornar">
    <table id="opsal_tabla_inicio">
        <tbody>
            <tr><td>Username</td><td><input type="text" value="" style="width:280px;" class="text_redondo" name="iniciar_campo_usuario"></td></tr>
            <tr><td>Password</td><td><input type="password" value="" style="width:280px;" class="text_redondo" name="iniciar_campo_clave"></td></tr>
        </tbody>
    </table>
    <input type="submit" value="Secure login" style="" class="" name="iniciar_proceder">
</form>
<hr />
<p style="color:#666;font-size:0.8em;">Opsal SA, Blvd. Del Hipodromo No. 237, Condominio San Benito, 4o. Nivel, Colonia San Benito San Salvador, San Salvador, El Salvador. (503) 2855-5625</p>