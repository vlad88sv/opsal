<?php
error_log('+++++++++++++++++++ iniciando todos los sistemas OPSAL +++++++++++++++++++');
$bench_ultimo_evento = 'Big Bang';
$bench_referencia = microtime(true);

define('MEMCACHE_ACTIVO', false);

define('__BASE__',str_replace('//','/',dirname(__FILE__).'/'));
define('__PHPDIR__',__BASE__.'PHP/');
define('_B_FORZAR_SERVIDOR_IMG_NULO','true');

define('db__host','localhost');
define('db__usuario','root');
define('db__clave','Eyobayeyo123!');
define('db__db','opsal');

define('smtp_usuario','notificaciones@sistemaopsal.com');
define('smtp_clave','opsalopsal');

define('PROY_NOMBRE','OPSAL');
define('PROY_NOMBRE_CORTO','OPSAL');
define('PROY_TELEFONO','22XX-XXXX');
define('PROY_TELEFONO_PRINCIPAL','22XX-XXXX');
define('PROY_MAIL_POSTMASTER_NOMBRE','OPSAL');
define('PROY_MAIL_POSTMASTER','contacto@sistemaopsal.tk');
define('PROY_MAIL_REPLYTO_NOMBRE',PROY_MAIL_POSTMASTER_NOMBRE);
define('PROY_MAIL_REPLYTO','contacto@sistemaopsal.tk');
define('PROY_MAIL_BROADCAST_NOMBRE',PROY_MAIL_POSTMASTER_NOMBRE);
define('PROY_MAIL_BROADCAST','cartero@sistemaopsal.tk');

//define('GOOGLE_ANALYTICS','UA-12744164-1');
define('HEAD_KEYWORDS','Contenedores El Salvador, Marchamos El Salvador, Condición de contenedores El Salvador, Containers El Salvador, ');

// Mostrar o no el pie - necesario para la pagina de compras
$GLOBAL_MOSTRAR_PIE = true;
$GLOBAL_TIDY_BREAKS = false;

$HEAD_titulo = PROY_NOMBRE;
$HEAD_descripcion = 'Everything about containers '.PROY_TELEFONO_PRINCIPAL;

// Prefijo para tablas
define('db_prefijo','opsal_');
?>
