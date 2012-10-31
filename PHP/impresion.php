<?php
if (isset($_POST['impresion_fiscal']))
{
    switch($_POST['tipo_impresion'])
    {
        case 'credito_fiscal':
            require_once('PHP/impresion.credito_fiscal.php');
            break;
        case 'consumidor_final':
            require_once('PHP/impresion.consumidor_final.php');
            break;
        default:
            echo '???';
    }
}
?>