<?php
require_once('config.php');
ini_set('memory_limit', '256M');
set_time_limit(0);
header("Content-type: application/pdf; name='pdf'");
header('Content-Disposition: filename="'.urldecode($_POST['archivo']).'.pdf"');
header("Pragma: no-cache");
header("Expires: 0");
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="es"/>
</head>
<body>
<style>
    <?php readfile('CSS/estilo.css'); ?>
    h1 {font-size: 16pt;}
    h2 {font-size: 13pt;font-weight:normal;}
    h3 {font-size: 12pt;font-weight:normal;}
    table {border-collapse: collapse;}
    table th {background-color:white !important;font-weight:bold;}
    table td {background-color:white !important;font-weight:normal;}
</style>
<h1><?php echo PROY_EMPRESA; ?></h1>
<?php echo strip_tags(urldecode(@$_POST['data']),'<div><table><p><tr><th><tbody><thead><td><h1><h2>'); ?>
</body>
<?
$html = ob_get_clean();
require_once ('PHP/mpdf/mpdf.php');
$mpdf=new mPDF();
$mpdf->CSSselectMedia='mpdf';
$mpdf->WriteHTML($html);
$mpdf->Output();
?>