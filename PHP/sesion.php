<?php
if (!session_id())
{
   session_start();
   $_SESSION['rnd'] = time();
}

function _F_sesion_cerrar(){
   setcookie(session_name(), session_id(), 1, '/');
   unset($_SESSION);
   session_destroy ();
   header('location: '.PROY_URL);
   return;
}

function S_iniciado(){
   return isset($_SESSION['autenticado']);
}
?>
