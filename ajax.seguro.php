<?php
require_once('config.php');
require_once (__PHPDIR__."vital.php");

if (!S_iniciado())
    return;

if (empty($_POST['accion'])) return;

switch ($_POST['accion'])
{
    case 'eliminar_despacho':
        run_part__eliminar_despacho();
        break;
    
    case 'verificar_doble_ingreso':
        run_part__verificar_doble_ingreso();
        break;
    
    case 'obtener_ultimos_buques':
        run_part__obtener_ultimos_buques();
        break;
}

function run_part__obtener_ultimos_buques()
{
    $c = 'SELECT buque_egreso, COUNT(*) AS "cantidad", CONCAT(DATE(MIN(`fechatiempo_egreso`)), "/", DATE(MAX(`fechatiempo_egreso`))) AS "fecha_embarque" FROM opsal_ordenes WHERE codigo_agencia="'.db_codex($_POST['codigo_agencia']).'" AND tipo_salida="embarque" GROUP BY `buque_egreso` ORDER BY MAX(`fechatiempo_egreso`) DESC LIMIT 10';
    $r = db_consultar($c);
    
    $options_buque = '<option selected="selected" value="">Embarque</option>';
    if (mysqli_num_rows($r) > 0)
    {
        while ($registro = mysqli_fetch_assoc($r))
        {
            $options_buque .= '<option value="'.$registro['buque_egreso'].'">'.$registro['buque_egreso']. ' - '. $registro['fecha_embarque'] . ' - ['. $registro['cantidad'] .' despachos]</option>';
        }
    } else {
        echo '<span style="color:blue;">[sin embarques registrados]</span>';
        return;
    }
    
    echo '<select id="buque" name="buque" style="width:100px;">'.$options_buque.'</select>';
}

function run_part__verificar_doble_ingreso()
{
    $c = 'SELECT COUNT(*) AS cantidad FROM opsal_ordenes WHERE codigo_contenedor="'.db_codex($_POST['contenedor']).'" AND estado="dentro"';
    $r = db_consultar($c);
    
    $f = db_fetch($r);
    
    echo json_encode(array('cantidad' => $f['cantidad']));
}

function run_part__eliminar_despacho()
{
    $c = 'DELETE FROM opsal_movimientos WHERE motivo="desestiba" AND codigo_orden="'.db_codex($_POST['ID']).'" LIMIT 1';
    $r = db_consultar($c);
    
    $c = 'UPDATE opsal_ordenes SET eir_egreso="", transportista_egreso="", chofer_egreso="", observaciones_egreso="", destino="", estado="dentro", cheque_egreso="", chasis_egreso="", fechatiempo_egreso=NULL, egresado_por=0 WHERE codigo_orden="'.db_codex($_POST['ID']).'" LIMIT 1';
    $r = db_consultar($c);
    
    registrar('Se ha eliminado el despacho de un contenedor (ID: <b>'.db_codex($_POST['ID']).'</b>).','eliminacion.despacho');
}
?>