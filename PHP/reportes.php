<?php
function gpie($resultado, $titulo, $divID, $columna, $fila)
{
    
    $buffer = "
    var data = google.visualization.arrayToDataTable([
      ['$columna', '$fila'],
    ";
    
    while ($f = mysqli_fetch_array($resultado))
    {
        $tbuffer[] = "['".$f[0]."', ".$f[1]."]";
    }
    
    $buffer .= join(', ', $tbuffer);
    
    $buffer .= "]);";
  
    $buffer .= "new google.visualization.PieChart(document.getElementById('$divID')).draw(data, {title:'$titulo'});";
    
    return $buffer;
}

function gCol($resultado, $titulo, $divID, $columna, $fila)
{
    
    $buffer = "
    var data = google.visualization.arrayToDataTable([
      ['$columna', '$fila'],
    ";
    
    while ($f = mysqli_fetch_array($resultado))
    {
        $tbuffer[] = "['".$f[0]."', ".$f[1]."]";
    }
    
    $buffer .= join(', ', $tbuffer);
    
    $buffer .= "]);";
  
    $buffer .= "new google.visualization.ColumnChart(document.getElementById('$divID')).draw(data, {title:'$titulo'});";
    
    return $buffer;
}
?>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('visualization', '1', {packages: ['corechart']});
</script>
<div class="noimprimir">
<h1 class="opsal_titulo">Reportes</h1>
<?php
if (empty($_GET['modo']))
    $_GET['modo'] = 'patio';

$menu[] = array('url' => '/reportes.html','modo' => 'historico','titulo' => 'HISTORICO');
$menu[] = array('url' => '/reportes.html','modo' => 'patio','titulo' => 'PATIO');
$menu[] = array('url' => '/reportes.html','modo' => 'ingresos','titulo' => 'INGRESOS');
$menu[] = array('url' => '/reportes.html','modo' => 'movimientos','titulo' => 'MOVIMIENTOS');
$menu[] = array('url' => '/reportes.html','modo' => 'salidas','titulo' => 'SALIDAS');
$menu[] = array('url' => '/reportes.html','modo' => 'agencia','titulo' => 'AGENCIA');

foreach ($menu AS $id => $datos)
{
    echo '<a class="opsal_pestaña '.($datos['modo'] == $_GET['modo'] ? 'opsal_pestaña_seleccionada' : '').'" href="'.$datos['url'].'?modo='.$datos['modo'].'">'.$datos['titulo'].'</a>';
}
?>
</div>
<?php
if (!empty($_GET['modo']))
{
    $archivo = 'PHP/reportes.'.$_GET['modo'].'.php';
    if (file_exists($archivo))
    {
        require_once($archivo);
        return;
    }
}
echo '<p>No implementado</p>';
?>