<h1 class="opsal_titulo">OPSAL - Contenedores con atención</h1>
<p>Catalogan como contenedores con atención aquellos que tengan menos de 15 días restantes de ARIVU y aquellos con mas de 60 días en patio.</p>
<?php
$c = 'SELECT usuario AS "Naviera", CONCAT( x2,  "-", y2,  "-", t1.nivel ) AS "Posición", `codigo_contenedor` AS "Contenedor", DATE(  `fechatiempo_ingreso` ) AS  "Ingreso", DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) AS  "Días en patio", ( `arivu_ingreso` + INTERVAL 90 DAY) AS "Expiración ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 90 DAY, NOW( ) ) AS  "Días para exp. ARIVU" FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) LEFT JOIN `opsal_usuarios` AS t3 ON t1.codigo_agencia = t3.codigo_usuario WHERE estado =  "dentro" AND DATEDIFF( NOW( ) , `arivu_ingreso`) > 75 OR DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) > 60 ORDER BY DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) DESC';
$resultado = db_consultar($c);
echo db_ui_tabla($resultado,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
?>