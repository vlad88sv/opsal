<?php if (memcache_iniciar(__FILE__,@$_SESSION)) return; ?>
<table style="width:100%;">
<tbody>
<tr>
<td><a href="/"><img src="/IMG/general/cabecera.jpg" /></a></td>
<td><input type="text" id="traducir" style="width:50px" value="" /><input id="ejecutar_traduccion" type="button" value="->"  style="padding:0px" /><input type="text" id="traducido" style="width:50px" value="" /></td>
<td style="text-align: right;">
<?php if (S_iniciado()): ?>
<a class="boton" href="/finalizar.html">Cerrar Sesión</a>
<?php endif; ?>
</td>
</tr>
</tbody>
</table>
<?php
if (!S_iniciado() || _F_usuario_cache('nivel') == 'agencia')
{
    echo '<hr />';
    return;
}
?>
<ul id="nav" class="dropdown dropdown-horizontal">
<li><a href="/" title="Alertas">Alertas</a></li>
<li><a href="/contenedores.html" title="Módulo de contenedores">Contenedores</a>
    <ul>
        <li><a href="/control.salidas.bloque.html" title="Salidas en bloque">Reporte salidas en bloque</a>
	<li><a href="/control.ingresos.html" title="Control ingresos">Reporte recepciones </a>
	<li><a href="/control.remociones.html" title="Control remociones">Reporte remociones</a>
	<li><a href="/control.salidas.html" title="Control salidas">Reporte despachos</a>
	<!--<li><a href="/control.sinfacturar.html" title="Detector de periodos no facturados">Sin facturar</a>!-->
    </ul>
</li>
<li><a href="/elaboracion.de.condicion.html" title="Módulo de contenedores">E. Condición</a></li>
<li><a href="/marchamos.html" title="Módulo de contenedores">Marchamos</a></li>
<li><a href="/lineas.de.amarre.html" title="Módulo de contenedores">Líneas de amarre</a></li>
<li><a href="/supervision.carga.descarga.html" title="Supervisión de carga y descarga">Carga / Descarga</a></li>
<li><a href="/facturacion.html" title="Módulo de facturacion">Facturación</a>
    <ul>
        <li><a href="/control.facturas.html" title="Control de facturas">Control</a>
	<!--<li><a href="/control.sinfacturar.html" title="Detector de periodos no facturados">Sin facturar</a>!-->
    </ul>
</li>
<li><a href="/administracion.html" title="Módulo de contenedores">Administrador</a>
    <ul>
        <li><a href="/reportes.html" title="Reportes">Reportes</a>
	<li><a href="/bitacora.html" title="Bitácora">Bitacora</a>
    </ul>
</li>

<li id="buscador">
    <input name="busqueda" type="text" id="busqueda" value="" />
    <input type="button" id="buscar" value="Búscar" />
</li>
</ul>
<script type="text/javascript">
    function ejecutar_busqueda_codigo_contenedor(codigo_contenedor)
    {
	$.facebox({ ajax: '/ajax.ContenedorPorCodigo.php?busqueda=' + codigo_contenedor });
    }
    
    $(function(){
	$(document).bind('reveal.facebox', function() {
	    if ($("#drop_target").length == 0)
	    {
		$("#bq_usar_contenedor").attr('disabled','disabled');
	    }
	});
	
	$("#ver_historial").live('click', function(){
	    window.location = '/historial.html?ID='+$(this).attr('rel');
	});
	
	$("#bq_usar_contenedor").live('click', function(){
		$("#drop_target #posicion_columna").val($(this).attr('col'));
		$("#drop_target #posicion_fila").val($(this).attr('fila'));
		$("#drop_target #posicion_nivel").val($(this).attr('nivel'));
		$(".posicion").trigger('change');
	});
	
	$('#ejecutar_traduccion').click(function(){
	    $.post('ajax.traducir.php',{traducir:$("#traducir").val()}, function(data){
		$("#traducido").val(data);
	    });
	    
	});

	$('#buscar').click(function(){    
	    ejecutar_busqueda_codigo_contenedor ($('#busqueda').val());
	});
    });
</script>
<noscript>
<div style="background-color:#fef1b9;font-size:14px;padding:10px;border-radius:10px;margin:10px 0px;text-align: center;">
Advertencia: su navegador no posee <b>JavaScript</b>, por lo que su experiencia no será óptima.<br />
</div>
</noscript>
<?php echo memcache_finalizar(__FILE__,@$_SESSION); ?>
