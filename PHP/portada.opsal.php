<h1 class="opsal_titulo">OPSAL - Contenedores con atención</h1>
<p>Catalogan como contenedores con atención aquellos que tengan menos de 15 días restantes de ARIVU o aquellos con mas de 60 días en patio.</p>
<?php
$c = 'SELECT usuario AS "naviera", CONCAT( x2,  "-", y2,  "-", t1.nivel ) AS "posicion", `codigo_contenedor` AS "contenedor", tipo_contenedor, DATE(  `fechatiempo_ingreso` ) AS  "fecha_ingreso", DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) AS  "dias_en_patio", ( `arivu_ingreso` + INTERVAL 90 DAY) AS "expiracion_arivu", DATEDIFF(  `arivu_ingreso` + INTERVAL 90 DAY, NOW( ) ) AS  "dias_expiracion_arivu", observaciones_ingreso FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) LEFT JOIN `opsal_usuarios` AS t3 ON t1.codigo_agencia = t3.codigo_usuario WHERE estado =  "dentro" AND (DATEDIFF( NOW( ) , `arivu_ingreso`) > 75 OR DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) > 60) ORDER BY DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) DESC';
$resultado = db_consultar($c);

echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
echo '<tr><th>Naviera</th><th>Contenedor</th><th>Tipo</th><th>Posición</th><th>Fecha ingreso</th><th>Días en patio</th><th>Expiración ARIVU</th><th>Días para exp. ARIVU</th><th>Observaciones</th></tr>';
while ($f = mysqli_fetch_assoc($resultado))
{
    echo '<tr>';
    echo '<td>'.$f['naviera'].'</td>';
    echo '<td><a href="#" rel="'.$f['contenedor'].'" class="ejecutar_busqueda_codigo_contenedor">'.$f['contenedor'].'</a></td>';
    echo '<td>'.$f['tipo_contenedor'].'</td>';
    echo '<td>'.$f['posicion'].'</td>';
    echo '<td>'.$f['fecha_ingreso'].'</td>';
    echo '<td>'.$f['dias_en_patio'].'</td>';
    echo '<td>'.($f['expiracion_arivu'] ? $f['expiracion_arivu'] : 'No disponible').'</td>';
    echo '<td>'.($f['dias_expiracion_arivu'] ? $f['dias_expiracion_arivu'] : 'No disponible') . ($f['dias_expiracion_arivu'] && $f['dias_expiracion_arivu'] < 15 && $f['dias_expiracion_arivu'] >= 0  ? ' <b style="color:red">¡ATENCIÓN!</b>' : '') . ($f['dias_expiracion_arivu'] < 0 ? ' <b style="color:red">¿ERROR?</b>' : '') .'</td>';
    echo '<td>'. ellipsis ($f['observaciones_ingreso'], 35) .'</td>';
    echo '</tr>';
}
?>