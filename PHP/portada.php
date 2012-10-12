<?php
if (_F_usuario_cache('nivel') == 'agencia')
{
    require_once('portada.agencia.php');
} else {
    require_once('portada.opsal.php');
}
?>