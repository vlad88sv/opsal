<?php
/****************** guardar_factura *****************/
if (isset($_POST['guardar_factura']))
{
    $factura = FacturarPeriodo($_POST['periodo_inicio'],$_POST['periodo_final'],$_POST['codigo_agencia'],$_POST['categoria']);
    
    unset($DATOS);
    $DATOS['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
    $DATOS['codigo_agencia'] = $_POST['codigo_agencia'];
    $DATOS['periodo_inicio'] = $_POST['periodo_inicio'];
    $DATOS['periodo_final'] = $_POST['periodo_final'];
    $DATOS['anexo'] = $factura['anexo'];
    
    $codigo_factura = db_agregar_datos('opsal_facturas',$DATOS);
    
    // hoy los detalles
    foreach($_POST['categoria'] as $indice => $cat)
    {
        unset($DATOS);
        $DATOS['codigo_factura'] = $codigo_factura;
        $DATOS['categoria'] = $cat;
        $DATOS['concepto'] = $_POST['concepto'][$indice];
        $DATOS['grabado'] = $_POST['grabado'][$indice];
        
        db_agregar_datos('opsal_factura_detalles',$DATOS);
    }
    
    header('Location: /control.facturas.html?cf='.$codigo_factura);
}

/****************** FIN guardar_factura *************/
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Seleccione una</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<div class="noimprimir">
<h1 class="opsal_titulo">Asistente de facturación</h1>
<form action="/facturacion.html" method="post">
    <div>
        Inicio de período: <input type="text" class="calendario" name="periodo_inicio" value="" /> Fin de período: <input type="text" class="calendario" name="periodo_final" value="" /> Agencia: <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> <input type="submit" id="filtrar" name="filtrar" value="Filtrar" />
    </div>
    <div id="categorias_facturacion">
        <b>Facturar:</b>
        <input type="checkbox" value="fact_almacenaje" name="flag[]" id="fact_almacenaje" checked="checked" /><label for="fact_almacenaje">Almacenaje</label>&nbsp;
        <input type="checkbox" value="fact_movimientos" name="flag[]" id="fact_movimientos" checked="checked" /><label for="fact_movimientos">Movimientos</label>&nbsp;
        <input type="checkbox" value="fact_elaboracion_condicion" name="flag[]" id="fact_elaboracion_condicion" checked="checked" /><label for="fact_elaboracion_condicion">Elaboración de condición</label>&nbsp;
        <input type="checkbox" value="fact_lineas_amarre" name="flag[]" id="fact_lineas_amarre" checked="checked" /><label for="fact_lineas_amarre">Líneas de amarre</label>&nbsp;
        <input type="checkbox" value="fact_carga_descarga" name="flag[]" id="fact_carga_descarga" checked="checked" /><label for="fact_carga_descarga">Supervisión carga y descarga</label>&nbsp;
    </div> 
</form>
<br /><hr />
<b>Ajustes especiales</b> <input type="checkbox" value="quirk_remociones_como_doble_estiba_desestiba" name="quirks[]" id="quirk_remociones_como_doble_estiba_desestiba" checked="checked" /><label for="quirk_remociones_como_doble_estiba_desestiba">Tratar remociones como doble estiba/desestiba</label>&nbsp;
<hr /><br />
</div>
<?php
if (isset($_POST['filtrar']))
{
    // Le mostramos las ultimas 5 facturas que le envio a esta agencia de mierda
    $factura = FacturarPeriodo($_POST['periodo_inicio'],$_POST['periodo_final'],$_POST['codigo_agencia'],$_POST['flag']);
    echo '<form action="/facturacion.html" method="post">';
    
    echo '<div style="border-radius:5px; border: 1px solid grey; padding: 5px;">';
        echo '<h1>Totales</h1>';
        echo $factura['cuadro'];
    echo '</div>';
    
    echo '<hr />';
    
    echo '<div style="border-radius:5px; border: 1px solid grey; padding: 5px;">';
        echo '<h1>Anexos</h1>';
        echo $factura['anexo'];
    echo '</div>';
    
    echo '<div class="noimprimir">';
        echo '<br /><hr />';
        echo '<input type="hidden" name="periodo_inicio" value="'.$_POST['periodo_inicio'].'" />';
        echo '<input type="hidden" name="periodo_final" value="'.$_POST['periodo_final'].'" />';
        echo '<input type="hidden" name="codigo_agencia" value="'.$_POST['codigo_agencia'].'" />';
        echo '<div style="text-align:right;"><input type="submit" value="Guardar factura" name="guardar_factura" /></div>';
    echo '</div>';
    
    echo '</form>';
}
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
        
        $("#filtrar").click(function(event){
            if ($("#codigo_agencia").val() == "")
            {
                event.preventDefault();
                alert('Debe especificar una agencia.');
            }
        });
    });
</script>