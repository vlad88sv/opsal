<?php
$bench_ultimo_evento = 'Big Bang';
$bench_referencia = microtime(true);

define('MODO_OCY','OCY');
define('MODO_MYR','MYR');

switch (@$_SERVER["SERVER_NAME"])
{
    case 'sistemamyr.tk':
        define('MODO','MYR');
        define('NOMBRE_CORTO','M&R');
        define('PROY_EMPRESA','MANTENIMIENTO Y REPARACIÓN, S.A. DE C.V.');
        define('PROY_NOMBRE','M&R');
        define('PROY_NOMBRE_CORTO','M&R');
        define('PROY_TELEFONO','22XX-XXXX');
        define('PROY_TELEFONO_PRINCIPAL','22XX-XXXX');
        break;
    
    case 'ocy.opsal.net':
    default:
        define('MODO','OCY');
        define('NOMBRE_CORTO','OCY');
        define('PROY_EMPRESA','OPERADORES PORTUARIOS SALVADOREÑOS, S.A. DE C.V.');
        define('PROY_NOMBRE','OPSAL');
        define('PROY_NOMBRE_CORTO','OPSAL');
        define('PROY_TELEFONO','22XX-XXXX');
        define('PROY_TELEFONO_PRINCIPAL','22XX-XXXX');
        break;
}

define('MEMCACHE_ACTIVO', false);

define('__BASE__',str_replace('//','/',dirname(__FILE__).'/'));
define('__PHPDIR__',__BASE__.'PHP/');
define('_B_FORZAR_SERVIDOR_IMG_NULO','true');

define('db__host','localhost');
define('db__usuario','root');
define('db__clave','Eyobayeyo123!');
define('db__db','opsal');

define('smtp_usuario','reports+opsal.net');
define('smtp_clave','R3p0rt52k12');

define('PROY_MAIL_POSTMASTER_NOMBRE','OPSAL');
define('PROY_MAIL_POSTMASTER','reports@sistemaopsal.tk');
define('PROY_MAIL_REPLYTO_NOMBRE',PROY_MAIL_POSTMASTER_NOMBRE);
define('PROY_MAIL_REPLYTO','reports@sistemaopsal.tk');
define('PROY_MAIL_BROADCAST_NOMBRE',PROY_MAIL_POSTMASTER_NOMBRE);
define('PROY_MAIL_BROADCAST','reports@sistemaopsal.tk');

//define('GOOGLE_ANALYTICS','UA-12744164-1');
define('HEAD_KEYWORDS','Contenedores El Salvador, Marchamos El Salvador, Condición de contenedores El Salvador, Containers El Salvador, ');

// Mostrar o no el pie - necesario para la pagina de compras
$GLOBAL_MOSTRAR_PIE = true;
$GLOBAL_TIDY_BREAKS = false;

$HEAD_titulo = PROY_NOMBRE;
$HEAD_descripcion = 'OPSAL Container Yard - '.PROY_TELEFONO_PRINCIPAL;

// Prefijo para tablas
define('db_prefijo','opsal_');
?>
