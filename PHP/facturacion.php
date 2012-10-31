<?php
/****************** guardar_factura *****************/
if (isset($_POST['guardar_factura']))
{
    $factura = FacturarPeriodo($_POST);
    
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

$options_agencia = '<option selected="selected" value="">naviera</option>';
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
    <hr />
    <div style="font-size:0.8em;padding:4px;">
        <b>Facturar para operación </b>
        <select id="tipo_de_facturacion">
            <option value="contenedores">Almacenaje y remociones</option>
            <option value="condiciones">Elaboración de condición</option>
            <option value="supervision">Supervisión de operaciones</option>
            <option value="marchamos">Revisión de marchamos</option>
            <option value="lineas">Líneas de amarre</option>
        </select>
    </div>
    <hr />
    <div style="font-size:0.8em;padding:4px;">
        De&nbsp;
        <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select>
        &nbsp;los contenedores que&nbsp;
        <select name="tipo_salida" id="tipo_salida">
            <option value="patio">estan actualmente en el patio</option>
            <option value="terrestre">fueron despachados vía terrestre</option>
            <option value="embarque">fueron despachados vía embarque</option>
        </select>        

        <span id="seleccion_buque" style="display:none;">
            &nbsp;en&nbsp;
            <span id="seleccion_buque_load"></span>
        </span>       

        <span id="seleccion_solo_despacho" style="display:none;">
        &nbsp;cobrar almacenaje&nbsp;
        <select name="tipo_cobro" id="tipo_cobro">
            <option value="periodo">solo del periodo</option>
            <option value="completo">desde la recepción</option>
        </select>
        </span>
        
        <span id="texto_si_terrestre" style="display: none;">
            si el despacho fue
        </span>
        
        <span id="seleccion_periodo">
            &nbsp;entre el&nbsp;
            <input type="text" class="calendario" name="periodo_inicio" value="" style="width:60px;" />
            &nbsp;al&nbsp;
            <input type="text" class="calendario" name="periodo_final" value="" style="width:60px;" />
        </span>
        
    </div>
<br /><hr />
<div style="text-align: center;padding:10px;"><input type="submit" id="filtrar" name="filtrar" value="Realizar filtrado" /></div>
<hr />
<input type="hidden" name="flag[]" value="fact_almacenaje" />
<input type="hidden" name="flag[]" value="fact_movimientos" />
</form>
</div>
<?php
if (isset($_POST['filtrar']))
{
    $factura = FacturarPeriodo($_POST);
    echo '<form action="/facturacion.html" method="post">';
    
    echo '<div class="noimprimir" style="border-radius:5px; border: 1px solid grey; padding: 5px;">';
        echo '<h1>Totales</h1>';
        echo $factura['cuadro'];
        echo '<div style="text-align:right;"><input type="submit" value="Guardar factura" name="guardar_factura" /></div>';
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
    echo '</div>';
    
    echo '</form>';
}
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
        
        $('#tipo_de_facturacion').change(function(){
            
        });
        
        $('#tipo_cobro').change(function(){
            
            $('#texto_si_terrestre').hide();
            $('#seleccion_periodo').show();
            
            if ($('#tipo_salida option:selected').val() == 'embarque' && $('#tipo_cobro option:selected').val() == 'completo')
            {
                $('#seleccion_periodo').hide();    
            }
            
            if ( $('#tipo_salida option:selected').val() == 'terrestre' && $('#tipo_cobro option:selected').val() == 'completo' )
            {
                $('#texto_si_terrestre').show();
            }
        });
        
        $('#tipo_salida, #codigo_agencia').change(function(){
            if ($('#tipo_salida option:selected').val() == 'embarque')
            {
                $('#tipo_cobro').val('periodo');
                
                $('#seleccion_solo_despacho').show();
                $('#seleccion_buque').show();
                $('#texto_si_terrestre').hide();
                
                $('#seleccion_buque_load').html('[cargando]').load('ajax.seguro.php',{accion : 'obtener_ultimos_buques', codigo_agencia : $("#codigo_agencia").val()});
            }
            
            if ($('#tipo_salida option:selected').val() == 'terrestre')
            {
                if ($('#tipo_cobro option:selected').val() == 'completo')
                {
                    $('#texto_si_terrestre').show();
                } else {
                    $('#texto_si_terrestre').hide();
                }
                
                $('#seleccion_solo_despacho').show();
                $('#seleccion_periodo').show();
                $('#seleccion_buque').hide();
                $('#seleccion_buque_load').empty();
            }
            
            if ($('#tipo_salida option:selected').val() == 'patio')
            {
                $('#texto_si_terrestre').hide();
                $('#seleccion_solo_despacho').hide();
                $('#seleccion_periodo').show();
                $('#seleccion_buque').hide();
                $('#seleccion_buque_load').empty();
            }
        });
        
        $("#filtrar").click(function(event){
            
            if ($('#tipo_salida option:selected').val() == 'patio' && $('#tipo_cobro option:selected').val() == 'completo')
            {
                event.preventDefault();
                alert('Selección inválida.\nNo puede facturar contenedores sin despacho desde la recepción.');
            }
            
            if ($("#codigo_agencia").val() == "")
            {
                event.preventDefault();
                alert('Debe especificar una agencia.');
            }
        });
    });
</script>