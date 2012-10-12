<?php
function FacturarPeriodo($periodo_inicio, $periodo_final, $codigo_agencia, $flags)
{
    $anexo = '';
    $cuadro = array();
    
    // Info
    $agencia = db_obtener('opsal_usuarios','usuario',"codigo_usuario='$codigo_agencia'");
    echo '<p>Facturación para período de <b>'.$periodo_inicio . '</b> a <b>'.$periodo_final.'</b> para agencia <b>'.$agencia.'</b>';
    
    // Almacenaje - contenedores ingresados entre fecha_inicio y fecha_final
    if (in_array('fact_almacenaje',$flags))
    {
        $c = '
        SELECT CONCAT( x2, "-", y2, "-", nivel ) AS "Posición", `codigo_contenedor` AS "Código contenedor", tipo_contenedor AS "Tipo", DATE( `arivu_egreso` ) AS "Vencimiento ARIVU", DATEDIFF( `arivu_egreso` , NOW( ) ) AS "Venc. ARIVU", @fecha_ingreso := DATE( `fechatiempo_ingreso` ) AS "Fecha Ingreso", DATE( `fechatiempo_egreso` ) AS "Fecha salida", @inicio_cobro := GREATEST (DATE(`fechatiempo_ingreso`), "'.$periodo_inicio.'") AS "Inicio de cobro", DATEDIFF( NOW() , `fechatiempo_ingreso` ) AS "Días en patio totales",  @dias_en_patio := GREATEST(DATEDIFF( COALESCE( `fechatiempo_egreso`, "'.$periodo_final.'" ) , GREATEST (`fechatiempo_ingreso`, "'.$periodo_inicio.'")), 0) AS "Días tomados", @dias_libres := GREATEST(0,(t4.`dias_libres_2040`-GREATEST(0, (DATEDIFF(@inicio_cobro,@fecha_ingreso) )))) AS "Días libres aplicables", @dias_cobrados := GREATEST( @dias_en_patio -  @dias_libres , 0 ) AS "Días cobrados", @precio_dia := IF( t3.cobro =20, t4.`p_almacenaje_20` , t4.`p_almacenaje_40` ) AS "Precio por día", @precio_dia * @dias_cobrados AS "Subtotal"
        FROM `opsal_ordenes` AS t1
        LEFT JOIN `opsal_posicion` AS t2
        USING ( codigo_posicion )
        LEFT JOIN `opsal_tipo_contenedores` AS t3
        USING ( tipo_contenedor )
        LEFT JOIN `opsal_tarifas` AS t4 ON t1.`codigo_agencia` = t4.`codigo_usuario`
        WHERE fechatiempo_ingreso < "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'"
        ORDER BY  `fechatiempo_ingreso` ASC';
        $r = db_consultar($c);
        $anexo .= '<h2>Cargos por almacenaje</h2>';
        $anexo .= db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro tabla-fuente-minima tabla-una-linea"');
        
        mysqli_data_seek($r,0);
        
        $totales['fact_almacenaje'] = 0;
        while ($f = mysqli_fetch_assoc($r))
        {
            $totales['fact_almacenaje'] += $f['Subtotal'];
        }
        
        $anexo .= '<p>Cobro sugerido: $'.number_format($totales['fact_almacenaje'],2,'.',',').'</p>';
        
        $cuadro[] = array('nombre' => 'Almacenaje', 'sugerido' => $totales['fact_almacenaje'], 'categoria' => 'fact_almacenaje');
    }
    
    // Almacenaje - contenedores ingresados entre fecha_inicio y fecha_final
    if (in_array('fact_movimientos',$flags))
    {
        $c = '
        SELECT CONCAT( x2, "-", y2, "-", t0.nivel ) AS "Posición", `codigo_contenedor` AS "Código contenedor", t3.`nombre` AS "Tipo de contenedor", CONCAT(COUNT(*), " @ $", IF( t3.cobro =20, t4.`p_estiba_20` , t4.`p_estiba_40` )) AS "Estibas", CONCAT(COUNT(*)," @ $", IF( t3.cobro =20, t4.`p_embarque_desestiba_20` , t4.`p_embarque_desestiba_40` )) AS "Desestibas", (COUNT(*)*2) AS "Total movimientos", ( (COUNT(*) * IF( t3.cobro =20, t4.`p_estiba_20` , t4.`p_estiba_40` )) + (COUNT(*) * IF( t3.cobro =20, t4.`p_embarque_desestiba_20` , t4.`p_embarque_desestiba_40` )) ) AS "Subtotal"
        FROM `opsal_movimientos` AS t0
        LEFT JOIN `opsal_posicion` AS t2
        USING ( codigo_posicion )
        LEFT JOIN `opsal_ordenes` AS t1
        USING (codigo_orden)
        LEFT JOIN `opsal_tipo_contenedores` AS t3
        USING ( tipo_contenedor )
        LEFT JOIN `opsal_tarifas` AS t4 ON t1.`codigo_agencia` = t4.`codigo_usuario`
        WHERE t0.fechatiempo BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'"
        GROUP BY t0.codigo_orden
        ORDER BY  COUNT(*) DESC';
        $r = db_consultar($c);
        $anexo .= '<h2>Cargos por Movimientos</h2>';
        $anexo .= db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro tabla-fuente-minima tabla-una-linea"');
        
        mysqli_data_seek($r,0);
        
        $totales['fact_movimientos'] = 0;
        while ($f = mysqli_fetch_assoc($r))
        {
            $totales['fact_movimientos'] += $f['Subtotal'];
        }
        
        $anexo .= '<p>Cobro sugerido: $'.number_format($totales['fact_movimientos'],2,'.',',').'</p>';
        
        $cuadro[] = array('nombre' => 'Movimientos', 'sugerido' => $totales['fact_movimientos'], 'categoria' => 'fact_movimientos');
    }
    
    if (in_array('fact_elaboracion_condicion',$flags))
    {
        $c = 'SELECT codigo_contenedor AS "Código contenedor", tipo_contenedor AS "Tipo de contenedor", fecha_ingreso AS "Fecha", referencia_papel AS "No. de condición", t2.p_elaboracion_condiciones AS "Subtotal" FROM `opsal_condiciones` AS t1 LEFT JOIN `opsal_tarifas` AS t2 ON t1.codigo_agencia = t2.codigo_usuario WHERE fecha_ingreso BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'" ORDER BY fecha_ingreso ASC';
        $r = db_consultar($c);
        $anexo .= '<h2>Cargos por elaboración de condiciones</h2>';        
        $anexo .= db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro tabla-fuente-minima tabla-una-linea"');
        
        mysqli_data_seek($r,0);
        
        $totales['fact_elaboracion_condicion'] = 0;
        while ($f = mysqli_fetch_assoc($r))
        {
            $totales['fact_elaboracion_condicion'] += $f['Subtotal'];
        }
        
        $anexo .= '<p>Cobro sugerido: $'.number_format($totales['fact_elaboracion_condicion'],2,'.',',').'</p>';
        
        $cuadro[] = array('nombre' => 'Elaboración de condición', 'sugerido' => $totales['fact_elaboracion_condicion'], 'categoria' => 'fact_elaboracion_condicion');
    }

    if (in_array('fact_lineas_amarre',$flags))
    {
        $c = 'SELECT ID_buque AS "Buque", tiempo_operacion AS "Horas de operación", fecha_ingreso "Ingreso", @dias_patio := DATEDIFF( NOW() , `fecha_ingreso` ) AS "Días en patio totales",  @dias_en_patio := DATEDIFF( COALESCE(`fecha_egreso`, "'.$periodo_final.'" ), GREATEST (`fecha_ingreso`, "'.$periodo_inicio.'")) AS "Días tomados", p_lineas_amarre_manejo AS "Costo manejo", p_lineas_amarre_almacenaje AS "Costo almacenaje", ((@dias_patio * p_lineas_amarre_almacenaje)+(p_lineas_amarre_manejo*tiempo_operacion)) AS "Subtotal" FROM opsal_lineas_amarre LEFT JOIN opsal_tarifas ON codigo_agencia=codigo_usuario WHERE fecha_ingreso < "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'" ORDER BY  `fecha_ingreso` ASC';
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
    
    if (in_array('fact_carga_descarga',$flags))
    {
        $c = 'SELECT `referencia_papel`, `fecha_ingreso` AS "Fecha", `inicio_operacion` AS "Inicio operación", `final_operacion` AS "Final operación", @duracion :=   FORMAT((time_to_sec(timediff(`final_operacion`,`inicio_operacion`)) / 3600),2) AS "Duración (h) de operación", FORMAT((@duracion*p_supervision_carga_descarga),2) AS "Subtotal" FROM opsal_carga_descarga LEFT JOIN opsal_tarifas ON codigo_agencia=codigo_usuario WHERE fecha_ingreso < "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'" ORDER BY  `fecha_ingreso` ASC';
        $r = db_consultar($c);
        $anexo .= '<h2>Cargos por almacenamiento de líneas de amarre</h2>';
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

?>
