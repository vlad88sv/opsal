<?php if (memcache_iniciar(__FILE__,@$_SESSION)) return; ?>
<table style="width:100%;">
<tbody>
<tr>
<td><a href="/"><img src="/IMG/general/cabecera.jpg" /></a></td>
<td><input type="text" id="traducir" style="width:50px" value="" /><input id="ejecutar_traduccion" type="button" value="->"  style="padding:0px" /><input type="text" id="traducido" style="width:50px" value="" /></td>
<td style="text-align: right;">
<?php if (S_iniciado()): ?>
<img style="vertical-align: middle;" title="Imprimir esta vista" onclick="window.print()" src="/IMG/general/imprimir.gif" />
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
	<li><a href="/control.patio.html" title="Control patio">Reporte de patio</a>
	<li><a href="/control.ingresos.html" title="Control ingresos">Reporte recepciones </a>
	<li><a href="/control.remociones.html" title="Control remociones">Reporte remociones</a>
	<li><a href="/control.embarques.html" title="Control embarques">Reporte embarques</a>
	<li><a href="/control.salidas.html" title="Control salidas">Reporte despachos</a>
	<!--<li><a href="/control.sinfacturar.html" title="Detector de periodos no facturados">Sin facturar</a>!-->
    </ul>
</li>
<li><a href="/elaboracion.de.condicion.html" title="Módulo de contenedores">E. Condición</a>
    <ul>
	<li><a href="/control.elaboracion.de.condicion.html" title="Reporte de condiciones">Obtener reporte</a>
    </ul>
</li>
<li><a href="/supervision.carga.descarga.html" title="Supervisión de carga y descarga">Supervisión OPS C/D</a>
    <ul>
	<li><a href="/control.supervision.carga.descarga.html" title="Reporte de supervisión de carga y descarga">Obtener reporte</a>
    </ul>
</li>
<li><a href="/lineas.de.amarre.html" title="Módulo de contenedores">Líneas de amarre</a></li>
<li><a href="/facturacion.html" title="Módulo de facturacion">Facturación</a>
    <ul>
        <li><a href="/control.facturas.html" title="Control de facturas">Control</a>
	<!--<li><a href="/control.sinfacturar.html" title="Detector de periodos no facturados">Sin facturar</a>!-->
    </ul>
</li>
<li><a href="/administracion.html" title="Módulo de contenedores">Administrador</a>
    <ul>
        <li><a href="/reportes.html" title="Reportes">Estadísticas</a>
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
	jQuery.download = function(url, data, method){
	    //url and data options required
	    if( url && data ){ 
		    //data can be string of parameters or array/object
		    data = typeof data == 'string' ? data : jQuery.param(data);
		    //split params into form inputs
		    var inputs = '';
		    jQuery.each(data.split('&'), function(){ 
			    var pair = this.split('=');
			    inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'" />'; 
		    });
		    //send request
		    jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
		    .appendTo('body').submit().remove();
	    };
	};
    
	$('.exportable').live('mouseenter', function(){
	    $(this).addClass('exportable_vivo');
	    $(this).prepend('<div class="exportable_ctrl"><b style="color:red;">Exportar este repote:</b> <button class="exportar_pdf">PDF</button><button class="exportar_xls">EXCEL</button><button class="exportar_doc">WORD</button><button class="exportar_correo">CORREO</button></div>');
	});
	
	$('.exportable').live('mouseleave', function(){
	    $(this).removeClass('exportable_vivo');
	    $(this).find('.exportable_ctrl').remove();
	});
	
	$('.exportable_ctrl .exportar_xls').live('click', function() {
	    var exportable = $(this).parents('.exportable');
	    exportable.find('.exportable_ctrl').remove();
	    var data = encodeURIComponent(exportable.html());
	    //console.log(data);
	    $.download('/exportar.xls.php', 'archivo='+encodeURIComponent(exportable.attr('rel') || 'reporte')+'&data=' + data);
	});

	$('.exportable_ctrl .exportar_doc').live('click', function() {
	    var exportable = $(this).parents('.exportable');
	    exportable.find('.exportable_ctrl').remove();
	    var data = encodeURIComponent(exportable.html());
	    //console.log(data);
	    $.download('/exportar.doc.php', 'archivo='+encodeURIComponent(exportable.attr('rel') || 'reporte')+'&data=' + data);
	});
	
	$('.exportable_ctrl .exportar_pdf').live('click', function() {
	    var exportable = $(this).parents('.exportable');
	    exportable.find('.exportable_ctrl').remove();
	    var data = encodeURIComponent(exportable.html());
	    //console.log(data);
	    $.download('/exportar.pdf.php', 'archivo='+encodeURIComponent(exportable.attr('rel') || 'reporte')+'&data=' + data);
	});
	
	$(document).bind('reveal.facebox', function() {
	    if ($("#drop_target").length == 0)
	    {
		$("button.bq_usar_contenedor").attr('disabled','disabled');
		$("a.bq_usar_contenedor").remove();
	    }
	});
	
	$("#ver_historial").live('click', function(){
	    window.location = '/historial.html?ID='+$(this).attr('rel');
	});
	
        $('.ejecutar_busqueda_codigo_contenedor').live('click',function(event){
	    event.preventDefault();
            ejecutar_busqueda_codigo_contenedor($(this).attr('rel'));
        });
	
	$(".bq_usar_contenedor").live('click', function(event){
	    event.preventDefault();
	    $("#drop_target #posicion_columna").val($(this).attr('col'));
	    $("#drop_target #posicion_fila").val($(this).attr('fila'));
	    $("#drop_target #posicion_nivel").val($(this).attr('nivel'));
	    $(".posicion").trigger('change');
	});
	
	$(".bq_eliminar_despacho").live('click', function(event){
	    if (confirm('¿Realmente desea eliminar este despacho?.\nNo podrá deshacer esta acción.'))
	    {
		$.post('ajax.seguro.php',{accion:'eliminar_despacho', ID:$(this).attr('rel')}, function(){
		    alert('Se ha eliminado el despacho.');
		});
	    }
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
