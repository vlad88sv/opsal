<?php
if (empty($_GET['modo']))
    $_GET['modo'] = 'agencias';

$menu[] = array('url' => '/administracion.html','modo' => 'patio','titulo' => 'PATIO');
$menu[] = array('url' => '/administracion.html','modo' => 'agencias','titulo' => 'AGENCIAS');
$menu[] = array('url' => '/administracion.html','modo' => 'usuarios','titulo' => 'USUARIOS');

echo '<div style="border-bottom: 1px solid #7A7A7A;">';
foreach ($menu AS $id => $datos)
{
    echo '<a class="opsal_pestaña '.($datos['modo'] == $_GET['modo'] ? 'opsal_pestaña_seleccionada' : '').'" href="'.$datos['url'].'?modo='.$datos['modo'].'">'.$datos['titulo'].'</a>';
}
echo '</div>';

switch ($_GET['modo'])
{
    case 'patio':
        require_once('PHP/admin.patio.php');
        break;
    case 'agencias':
        require_once('PHP/admin.agencias.php');
        break;
    case 'cheques':
        require_once('PHP/admin.cheques.php');
        break;
    case 'usuarios':
        require_once('PHP/admin.usuarios.php');
        break;
    case 'transportistas':
        require_once('PHP/admin.transportistas.php');
        break;
    default:
        echo '<p>No implementado</p>';
}
?>