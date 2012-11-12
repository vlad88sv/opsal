<?php
function numero($numero)
{
    return number_format($numero,2,'.',',');
}

function dinero($numero)
{
    return '$'.numero($numero,2,'.',',');
}

function FacturarPeriodo(array $op)
{
    $anexo = '';
    $cuadro = array();
    
    $periodo_inicio = $op['periodo_inicio'];
    $periodo_final = $op['periodo_final'];
    $codigo_agencia = $op['codigo_agencia'];
    $tipo_salida = $op['tipo_salida'];
    
    $flags = @$op['flag'];
    $quirks = @$op['quirks'];
    
    // Info
    $agencia = db_obtener('opsal_usuarios','usuario',"codigo_usuario='$codigo_agencia'");
    
    $titulo = '';
    
    switch($op['tipo_salida'])
    {
        // Caso 1 - por despacho terrestre
        case 'terrestre':
            $where = 'AND t1.estado="fuera"  AND codigo_agencia="'.$codigo_agencia.'" AND t1.tipo_salida="terrestre" AND t1.fechatiempo_egreso IS NOT NULL AND DATE(fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
            break;
        
        // Caso 2 - por despacho búque
        case 'embarque':
            $where = 'AND codigo_agencia="'.$codigo_agencia.'" AND t1.tipo_salida="embarque" AND t1.estado="fuera" AND t1.buque_egreso="'.$op['buque'].'"';
            break;
        
        // Caso 3 - por estadía
        case 'patio':
            $where = 'AND codigo_agencia="'.$codigo_agencia.'" AND estado = "dentro" AND fechatiempo_egreso IS NULL AND fechatiempo_ingreso < "'.$periodo_final.'"';
            $op['tipo_cobro'] = 'periodo';
            break;
        
        // Caso 4 - por embarque primitivo (no por buque sino que por periodo)
        case 'embarque_primitivo':
            $where = 'AND t1.estado="fuera"  AND codigo_agencia="'.$codigo_agencia.'" AND t1.tipo_salida="embarque" AND t1.fechatiempo_egreso IS NOT NULL AND DATE(fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
            $op['tipo_cobro'] = 'completo';
            break;
    }       
    
    echo '<p>Facturación para agencia <b>'.$agencia.'</b>';
    
    // Almacenaje - contenedores ingresados entre fecha_inicio y fecha_final
    if ($op['modo_facturacion'] == 'contenedores')
    {
        $anexo .= '<h2>Cargos por almacenaje</h2>';
        
        
        if ($op['tipo_cobro'] == 'completo')
        {
            $inicio_cobro = 'DATE(`fechatiempo_ingreso`)';
            $final_cobro = 'DATE( COALESCE( `fechatiempo_egreso`, NOW()) )';
        } else {
            $inicio_cobro = 'GREATEST (DATE(`fechatiempo_ingreso`), "'.$periodo_inicio.'")';
            $final_cobro = 'LEAST ( DATE(COALESCE( `fechatiempo_egreso`, NOW())), "'.$periodo_final.'" ) ';
        }
        
        $dias_en_patio = '(DATEDIFF(DATE( COALESCE( `fechatiempo_egreso`, NOW()) ), DATE(`fechatiempo_ingreso`)) + 1)';
        
        $c = '
        SELECT `codigo_contenedor`, tipo_contenedor, DATE( `arivu_ingreso` + INTERVAL 90 DAY) AS "vencimiento_arivu", DATEDIFF( `arivu_ingreso` + INTERVAL 90 DAY , NOW( ) ) AS "dias_para_vencimiento_arivu", DATE( `fechatiempo_ingreso` ) AS "fecha_ingreso_fmt", COALESCE(DATE( `fechatiempo_egreso` ), "N/A") AS "fecha_salida_fmt", @inicio_cobro := '.$inicio_cobro.' AS "inicio_cobro", '.$final_cobro.' AS "final_cobro", '.$dias_en_patio.' AS "dias_en_patio",  @dias_tomados := GREATEST( (DATEDIFF( '.$final_cobro.' , '.$inicio_cobro.') + 1) , 0) AS "dias_tomados", @dias_libres := LEAST(@dias_tomados, GREATEST(0, (t4.`dias_libres_2040` - DATEDIFF(@inicio_cobro, DATE( `fechatiempo_ingreso` ))))) AS "dias_libres_aplicables", @dias_cobrados := GREATEST( @dias_tomados -  @dias_libres , 0 ) AS "dias_cobrados", @precio_dia := IF( t3.cobro =20, t4.`p_almacenaje_20` , t4.`p_almacenaje_40` ) AS "precio_por_dia", @precio_dia * @dias_cobrados AS "subtotal"
        FROM `opsal_ordenes` AS t1
        LEFT JOIN `opsal_posicion` AS t2
        USING ( codigo_posicion )
        LEFT JOIN `opsal_tipo_contenedores` AS t3
        USING ( tipo_contenedor )
        LEFT JOIN `opsal_tarifas` AS t4 ON t1.`codigo_agencia` = t4.`codigo_usuario`
        WHERE 1 '.$where.'
        ORDER BY `fechatiempo_ingreso` ASC, `fechatiempo_egreso` DESC, tipo_contenedor DESC';
        $r = db_consultar($c);

        $anexo .= '<div class="exportable" rel="Cargos por almacenaje">';
        $anexo .= '<p>Cargos por almacenaje de <b>'.mysqli_num_rows($r).'</b> contenedores</p>';
        $anexo .= '<p>Periodo de <b>'.$periodo_inicio . '</b> a <b>'.$periodo_final.'</p><br />';        
        
        $anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';

        $anexo .= '
        <thead><tr>
        <th>No.</th>
        <th>Contenedor</th>
        <th>Tipo</th>
        <th><acronym title="Fecha de Vencimiento ARIVU">FVA</acronym></th>
        <th><acronym title="Días restantes para el vencimiento del ARIVU">DVA</acronym></th>
        <th>Recepción</th>
        <th>Inicio cobro</th>
        <th>Final cobro</th>
        <th>Despacho</th>
        <th><acronym title="Días en patio">DEP</acronym></th>
        <th><acronym title="Días libres aplicables">DLA</acronym></th>
        <th><acronym title="Días cobrados">DC</acronym></th>
        <th>Precio por día</th>
        <th>Subtotal</th>
        <th>IVA</th>
        <th>Total</th>
        </tr></thead>';
        
        $total_iva = $total_siniva = 0;
        $i = 1;
        while ($f = db_fetch($r))
        {
            $anexo .= '<tr><td>'.$i.'</td><td>'.$f['codigo_contenedor'].'</td><td>'.$f['tipo_contenedor'].'</td><td>'.$f['vencimiento_arivu'].'</td><td>'.$f['dias_para_vencimiento_arivu'].'</td><td>'.$f['fecha_ingreso_fmt'].'</td><td>'.$f['inicio_cobro'].'</td><td>'.$f['final_cobro'].'</td><td>'.$f['fecha_salida_fmt'].'</td><td>'.$f['dias_en_patio'].'</td><td>'.$f['dias_libres_aplicables'].'</td><td>'.$f['dias_cobrados'].'</td><td>'.dinero($f['precio_por_dia']).'</td><td>'.dinero($f['subtotal']).'</td><td>'.dinero($f['subtotal'] * 0.13).'</td><td>'.dinero($f['subtotal'] * 1.13).'</td></tr>';
        
            $total_siniva += ($f['subtotal']);
            $total_iva += ($f['subtotal'] * 0.13);
            $i++;
        }
        $anexo .= '<tr><th colspan="13"></th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_iva).'</th><th>'.dinero($total_iva + $total_siniva).'</th></tr>';
        
        $anexo .= '</table>';
        
        $totales['fact_almacenaje'] = ($total_iva + $total_siniva);
    
        $anexo .= '</div>';
        
        $anexo .= '<p>Cobro sugerido: $'.number_format($totales['fact_almacenaje'],2,'.',',').'</p>';
        
        $cuadro[] = array('nombre' => 'Almacenaje', 'sugerido' => $totales['fact_almacenaje'], 'categoria' => 'fact_almacenaje');
    }
    
    // Remociones
    if ($op['modo_facturacion'] == 'contenedores')
    {
        // CASE t0.motivo WHEN "remocion" THEN CONCAT( x2, "-", y2, "-", t0.nivel ) WHEN "estiba" THEN "Recepción" WHEN "desestiba" THEN "Despacho" END AS "posicion",
        // CONCAT(COUNT(*), " @ $", IF( t3.cobro =20, t4.`p_estiba_20` , t4.`p_estiba_40` )) AS "estibas", CONCAT(COUNT(*)," @ $", IF( t3.cobro =20, t4.`p_embarque_desestiba_20` , t4.`p_embarque_desestiba_40` )) AS "desestibas",
        
        $estibas = ' SUM(CASE t0.motivo WHEN "remocion" THEN (1*t4.`multiplicador_remociones`) WHEN "estiba" THEN 1 WHEN "desestiba" THEN 0 END) ';
        $desestibas = ' SUM(CASE t0.motivo WHEN "remocion" THEN (1*t4.`multiplicador_remociones`) WHEN "estiba" THEN 0 WHEN "desestiba" THEN 1 END) ';
        $c = '
        SELECT  CONCAT( x2, "-", y2, "-", t0.nivel ) AS "posicion", `codigo_contenedor`, t3.`tipo_contenedor`, '.$estibas.' AS "estibas", '.$desestibas.' AS "desestibas", ('.$estibas.' + '.$desestibas.') AS "total_movimientos", ( ('.$estibas.' * IF( t3.cobro =20, t4.`p_estiba_20` , t4.`p_estiba_40` )) + ('.$desestibas.' * IF( t3.cobro =20, t4.`p_embarque_desestiba_20` , t4.`p_embarque_desestiba_40` )) ) AS "subtotal"
        FROM `opsal_movimientos` AS t0
        LEFT JOIN `opsal_posicion` AS t2
        USING ( codigo_posicion )
        LEFT JOIN `opsal_ordenes` AS t1
        USING (codigo_orden)
        LEFT JOIN `opsal_tipo_contenedores` AS t3
        USING ( tipo_contenedor )
        LEFT JOIN `opsal_tarifas` AS t4 ON t1.`codigo_agencia` = t4.`codigo_usuario`
        WHERE cobrar_a="'.$codigo_agencia.'" '.$where.'
        GROUP BY t1.codigo_orden
        ORDER BY  t1.codigo_contenedor DESC';
        $r = db_consultar($c);
        $anexo .= '<br /><hr /><br /><h2>Movimientos de estiba y desestiba</h2>';
        

        $anexo .= '<div class="exportable" rel="Movimientos de estiba y desestiba">';
        $anexo .= '<p>Movimientos de estiba y desestiba para <b>'.mysqli_num_rows($r).'</b> contenedores</p>';
        $anexo .= '<p>Periodo de <b>'.$periodo_inicio . '</b> a <b>'.$periodo_final.'</p><br />';        
        
        $anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';

        $anexo .= '
        <thead><tr>
        <th>No</th>
        <th>Posición</th>
        <th>Código contenedor</th>
        <th>Tipo</th>
        <th>Estibas</th>
        <th>Desestibas</th>
        <th>Cantidad</th>
        <th>Subtotal</th>
        <th>IVA</th>
        <th>Total</th>
        </tr></thead>';
        
        $total_iva = $total_siniva = 0;
        $i = 1;
        while ($f = db_fetch($r))
        {
            $anexo .= '<tr><td>'.$i.'</td><td>'.$f['posicion'].'</td><td>'.$f['codigo_contenedor'].'</td><td>'.$f['tipo_contenedor'].'</td><td>'.$f['estibas'].'</td><td>'.$f['desestibas'].'</td><td>'.$f['total_movimientos'].'</td><td>'.dinero($f['subtotal']).'</td><td>'.dinero($f['subtotal'] * 0.13).'</td><td>'.dinero($f['subtotal'] * 1.13).'</td></tr>';
            $total_siniva += $f['subtotal'];
            $total_iva += ($f['subtotal'] * 0.13);
            $i++;
        }
        $anexo .= '<tr><th colspan="7"></th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_iva).'</th><th>'.dinero($total_iva + $total_siniva).'</th></tr>';
        
        $anexo .= '</table>';
        $anexo .= '</div>';
        
        $totales['fact_movimientos'] = ($total_iva + $total_siniva);
        
        $anexo .= '<p>Cobro sugerido: $'.number_format($totales['fact_movimientos'],2,'.',',').'</p>';
        
        $cuadro[] = array('nombre' => 'Movimientos', 'sugerido' => $totales['fact_movimientos'], 'categoria' => 'fact_movimientos');
    }
    
    if ($op['modo_facturacion'] == 'condiciones')
    {
        $c = 'SELECT codigo_contenedor, tipo_contenedor, fecha_ingreso, referencia_papel, t2.p_elaboracion_condiciones AS "subtotal" FROM `opsal_condiciones` AS t1 LEFT JOIN `opsal_tarifas` AS t2 ON t1.codigo_agencia = t2.codigo_usuario WHERE DATE(fecha_ingreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'" ORDER BY fecha_ingreso ASC';
        $r = db_consultar($c);
        $i = 1;
        $total_siniva = 0;
        $total_iva = 0;
        
        $anexo .= '<h2>Elaboración de condiciones</h2>';
        
        $anexo .= '<div class="exportable" rel="Cargos por elaboración de condiciones">';
        $anexo .= '<p>Elaboración de <b>'.mysqli_num_rows($r).'</b> condiciones</p>';
        $anexo .= '<p>Periodo de <b>'.$periodo_inicio . '</b> a <b>'.$periodo_final.'</p><br />';        
        
        $anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
        $anexo .= '<tr><th>No</th><th>Código contenedor</th><th>Tipo de contenedor</th><th>Fecha</th><th>No. de condición</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>';
        while ($f = db_fetch($r))
        {
            $anexo .= '<tr><td>'.$i.'</td><td>'.$f['codigo_contenedor'].'</td><td>'.$f['tipo_contenedor'].'</td><td>'.$f['fecha_ingreso'].'</td><td>'.$f['referencia_papel'].'</td><td>'.dinero($f['subtotal']).'</td><td>'.dinero($f['subtotal'] * 0.13).'</td><td>'.dinero($f['subtotal'] * 1.13).'</td></tr>';
            $total_siniva += $f['subtotal'];
            $total_iva += ($f['subtotal'] * 0.13);
            $i++;
        }
        $anexo .= '<tr><th colspan="5"></th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_iva).'</th><th>'.dinero($total_iva + $total_siniva).'</th></tr>';
        $anexo .= '</table>';
        $anexo .= '</div>';
        
        $totales['fact_elaboracion_condicion'] = ($total_iva + $total_siniva);
        
        $anexo .= '<p>Cobro sugerido: $'.number_format($totales['fact_elaboracion_condicion'],2,'.',',').'</p>';
        
        $cuadro[] = array('nombre' => 'Elaboración de condición', 'sugerido' => $totales['fact_elaboracion_condicion'], 'categoria' => 'fact_elaboracion_condicion');
    }

    if ($op['modo_facturacion'] == 'lineas')
    {
        $c = 'SELECT ID_buque AS "Buque", fecha_ingreso "Ingreso", @dias_patio := DATEDIFF( NOW() , `fecha_ingreso` ) AS "Días en patio totales",  @dias_en_patio := DATEDIFF( COALESCE(`fecha_egreso`, "'.$periodo_final.'" ), GREATEST (`fecha_ingreso`, "'.$periodo_inicio.'")) AS "Días tomados", p_lineas_amarre_manejo AS "Costo manejo", p_lineas_amarre_almacenaje AS "Costo almacenaje", ((@dias_patio * p_lineas_amarre_almacenaje)+(p_lineas_amarre_manejo*tiempo_operacion)) AS "Subtotal" FROM opsal_lineas_amarre LEFT JOIN opsal_tarifas ON codigo_agencia=codigo_usuario WHERE fecha_ingreso < "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'" ORDER BY  `fecha_ingreso` ASC';
        $r = db_consultar($c);
        $anexo .= '<h2>Cargos por almacenamiento de líneas de amarre</h2>';
        $anexo .= db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro tabla-fuente-minima tabla-una-linea"');
        
        mysqli_data_seek($r,0);
        
        $totales['fact_lineas_amarre'] = 0;
        while ($f = mysqli_fetch_assoc($r))
        {
            $totales['fact_lineas_amarre'] += $f['Subtotal'];
        }
        
        $anexo .= '<p>Cobro sugerido: $'.number_format($totales['fact_lineas_amarre'],2,'.',',').'</p>';
        
        $cuadro[] = array('nombre' => 'Lineas de amarre', 'sugerido' => $totales['fact_lineas_amarre'], 'categoria' => 'fact_lineas_amarre');
    }
    
    if ($op['modo_facturacion'] == 'opscdmarchamos')
    {
        $c = 'SELECT `ID_buque`, `inicio_operacion` AS "Inicio operación", `final_operacion` AS "Final operación", @duracion :=   FORMAT((time_to_sec(timediff(`final_operacion`,`inicio_operacion`)) / 3600),2) AS "Duración (h) de operación", FORMAT((@duracion*p_supervision_carga_descarga),2) AS "Subtotal" FROM opsal_carga_descarga LEFT JOIN opsal_tarifas ON codigo_agencia=codigo_usuario WHERE DATE(fecha_ingreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'" ORDER BY  `fecha_ingreso` ASC';
        $r = db_consultar($c);
        
        $anexo .= '<h2>Supervisión de carga y descarga</h2>';
        $anexo .= db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro tabla-fuente-minima tabla-una-linea"');
        
        mysqli_data_seek($r,0);
        
        $totales['fact_carga_descarga'] = 0;
        while ($f = mysqli_fetch_assoc($r))
        {
            $totales['fact_carga_descarga'] += $f['Subtotal'];
        }
        
        $anexo .= '<p>Cobro sugerido: $'.number_format($totales['fact_carga_descarga'],2,'.',',').'</p>';
        
        $cuadro[] = array('nombre' => 'Supervisión carga/descarga', 'sugerido' => $totales['fact_carga_descarga'], 'categoria' => 'fact_carga_descarga');
    }
    
    $bcuadro = '<table class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
    $bcuadro .= '<tr><th>Descripción</th><th>Sugerido</th><th>Cobrado</th></tr>';
    foreach ($cuadro as $parte)
        $bcuadro .= sprintf('<tr><tr><td>%s</td><td>$%s</td><td><input type="hidden" name="categoria[]" value="%s" /><input type="hidden" name="concepto[]" value="%s" /><input type="text"  name="grabado[]" value="%s"</td></tr>',$parte['nombre'],number_format($parte['sugerido'],2,'.',','),$parte['categoria'],$parte['nombre'],number_format($parte['sugerido'],2,'.',','));
    $bcuadro .= '</table>';
    
    return array('anexo' => $anexo, 'cuadro' => $bcuadro);
}

// http://www.linuxjournal.com/article/9585
function validcorreo($correo)
{
   $isValid = true;
   $atIndex = strrpos($correo, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($correo, $atIndex+1);
      $local = substr($correo, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}


function ES_SSL()
{
    return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
}

function SEO($URL){
    $URL = preg_replace("`\[.*\]`U","",$URL);
    $URL = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$URL);
    $URL = htmlentities($URL, ENT_COMPAT, 'utf-8');
    $URL = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $URL );
    $URL = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $URL);
    return strtolower(trim($URL, '-')).".html";
}
// http://www.webcheatsheet.com/PHP/get_current_page_url.php
// Obtiene la URL actual, $stripArgs determina si eliminar la parte dinamica de la URL
function curPageURL($stripArgs=false,$friendly=false,$forzar_ssl=false) {
$pageURL = '';
if (!$friendly)
{
   $pageURL = 'http';

   if ((ES_SSL() || $forzar_ssl) && $forzar_ssl != 'nunca') {$pageURL .= "s";}
   $pageURL .= "://";
}

$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

if ($stripArgs) {$pageURL = preg_replace("/\?.*/", "",$pageURL);}

if ($friendly)
{
    $pageURL = preg_replace('/www\./', '',$pageURL);
    $pageURL = "www.$pageURL";
}

return $pageURL;
}

function domain($forzar_ssl = false) {
$pageURL = 'http';
if ((ES_SSL() || $forzar_ssl) && $forzar_ssl != 'nunca') {$pageURL .= "s";}
$pageURL .= "://";
$pageURL .= $_SERVER["SERVER_NAME"];
$pageURL = preg_replace('/www\./', '',$pageURL);
$pageURL .= "/";
return $pageURL;
}

// http://www.php.net/manual/en/function.mt-rand.php#106645
function genRandomString($length = 10) {
    $chars = 'fghjkraeou';
    $result = '';
    
    for ($p = 0; $p < $length; $p++)
    {
        $result .= ($p%2) ? $chars[mt_rand(6, 9)] : $chars[mt_rand(0, 5)];
    }
    
    return $result;
}

// Wrapper de envío de correo electrónico. HTML/utf-8
function correo($para, $asunto, $mensaje,$exHeaders=null)
{
    
    if (correoSMTP($para, $asunto, $mensaje))
        return;
    
    $headers = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n" . 'Date: '.date("r") . "\r\n";
    $headers .= 'From: '. PROY_MAIL_POSTMASTER . "\r\n";
    
    if (!empty($exHeaders))
    {
        $headers .= $exHeaders;
    }
    $mensaje = sprintf('<html><head><title>%s</title></head><body>%s</body>',PROY_NOMBRE,$mensaje);
    return mail($para,'=?UTF-8?B?'.base64_encode($asunto).'?=',$mensaje,$headers);
}

/*
  Copyright (c) 2008, reusablecode.blogspot.com; some rights reserved.
 
  This work is licensed under the Creative Commons Attribution License. To view
  a copy of this license, visit http://creativecommons.org/licenses/by/3.0/ or
  send a letter to Creative Commons, 559 Nathan Abbott Way, Stanford, California
  94305, USA.
  */
 
// Luhn (mod 10) algorithm
function luhn($input)
{
    if (strlen($input) < 10)
        return false;
    
    $sum = 0;
    $odd = strlen($input) % 2;
     
    // Remove any non-numeric characters.
    if (!is_numeric($input))
        $input = preg_replace("/[^\d]/", "", $input);
     
    // Calculate sum of digits.
    for($i = 0; $i < strlen($input); $i++)
    {
        $sum += $odd ? $input[$i] : (($input[$i] * 2 > 9) ? $input[$i] * 2 - 9 : $input[$i] * 2);
        $odd = !$odd;
    }
     
    // Check validity.
    return ($sum % 10 == 0) ? true : false;
}

//Wrapper de envío de correo usando PHPMailer
function correoSMTP($para, $asunto, $mensaje,$html=true,$extra=null)
{
    require_once(__BASE__ARRANQUE.'PHP/class.phpmailer.php');
    $Mail               = new PHPMailer();
    $Mail->IsHTML       ($html) ;
    $Mail->SetLanguage  ("es", __BASE__ARRANQUE.'PHP/language/');
    $Mail->PluginDir	= __BASE__ARRANQUE.'PHP/';
    $Mail->Mailer	= 'smtp';
    $Mail->Host		= "smtp.gmail.com";
    $Mail->SMTPSecure    = "ssl";
    $Mail->Port		= 465;
    $Mail->SMTPAuth	= true;
    $Mail->Username	= smtp_usuario;
    $Mail->Password	= smtp_clave;
    $Mail->CharSet	= "utf-8";
    $Mail->Encoding	= "quoted-printable";
    $Mail->SetFrom	(PROY_MAIL_POSTMASTER, PROY_MAIL_POSTMASTER_NOMBRE);
    $Mail->Subject	= $asunto;
    $Mail->Body		= $mensaje;

    // Veamos si hay que hacer embed de imagenes
    if (is_array($extra))
    {
        foreach ($extra as $archivo)
        {
            $Mail->AddEmbeddedImage($archivo['ruta'], $archivo['cid'], $archivo['alt'], "base64", $archivo['tipo']);
        }
    }
    
    $correos = preg_split('/,/',$para);
    foreach($correos as $correo)
        $Mail->AddAddress ($correo);

    $x = $Mail->Send();
    
    if ($x)
       return $x;
    else
       return 0;
}


function HEAD_JS()
{
    global $arrJS;
    $buffer = '';
    foreach ($arrJS as $JS)
        $buffer .= '<script type="text/javascript" src="'.PROY_URL_ESTATICA.'JS/'.$JS.'.js"></script>';

    echo $buffer;
}

function HEAD_CSS()
{
    global $arrCSS;
    $buffer = '';
    foreach ($arrCSS as $CSS)
        $buffer .= '<link rel="stylesheet" type="text/css" href="'.PROY_URL_ESTATICA.$CSS.'.css" />';
    
    echo $buffer;
}

function HEAD_EXTRA()
{
    global $arrHEAD;
    echo "\n";
    echo implode("\n",$arrHEAD);
    echo "\n";
}

function SI_ADMIN($texto)
{
    if (_F_usuario_cache('nivel') == _N_administrador)
    {
        return $texto;
    }
}

function protegerme($solo_salir=false,$niveles=array())
{

    if (_F_usuario_cache('nivel') == _N_administrador || in_array(_F_usuario_cache('nivel'),$niveles))
        return;

    if (!$solo_salir)
        header('Location: '. PROY_URL.'iniciar?ref='.curPageURL());
    ob_end_clean();
    exit;
}

function ellipsis($text, $max=100, $append='&hellip;')
{
    if (strlen($text) <= $max) return $text;
    $out = substr($text,0,$max);
    if (strpos($text,' ') === FALSE) return $out.$append;
    return preg_replace('/\w+$/','',$out).$append;
}
?>
