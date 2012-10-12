<?php
$options_transportista = $options_agencia = $options_cheque = '';

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
<form id="frm_ingreso_contenedores" action="/contenedores.html?modo=ingreso" method="post" autocomplete="off">
<table class="tabla-estandar opsal_tabla_ancha">
    <tbody>
        <tr><td>Naviera</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td></tr>
        <tr><td>Fecha ingreso</td><td><input type="text" class="calendariocontiempo" value="" id="fechatiempo_ingreso" name="fechatiempo_ingreso" /></td></tr>
        <tr><td>CEPA Salida</td><td><input type="text" class="calendariocontiempo" value="" id="cepa_salida" name="cepa_salida" /></td></tr>
        <tr><td>ARIVU Ingreso</td><td><input type="text" class="calendario" value="" id="arivu_ingreso" name="arivu_ingreso" /></td></tr>
        <tr><td>No. ARIVU</td><td><input type="text" value="" id="arivu_referencia" name="arivu_referencia" /></td></tr>
        <tr><td>No. EIR</td><td><input type="text" value="" id="eir_ingreso" name="eir_ingreso" /></td></tr>
        <tr><td>Contenedor</td><td><input type="text" value="" id="codigo_contenedor" name="codigo_contenedor" /></td></tr>
        
        <tr><td>Clase</td><td>
        <div id="clase_contenedor" style="text-align:center;">
            <input type="radio" name="clase" id="clase_a" value="A"/><label for="clase_a">A</label>&nbsp;
            <input type="radio" name="clase" id="clase_b" value="B" /><label for="clase_b">B</label>&nbsp;
            <input type="radio" name="clase" id="clase_c" value="C" /><label for="clase_c">C</label>
        </div>
        </td></tr>
        <tr><td>Tipo</td><td>
        <div id="tamano_contenedor" style="text-align: center;line-height: 30px;">
        <input rel="20" type="radio" name="tamano_contenedor" id="clase_20" checked="checked" value="20"/>
        <label for="clase_20">20</label>&nbsp;
        
        <input rel="40" type="radio" name="tamano_contenedor" id="clase_40" value="40"/>
        <label for="clase_40">40</label>&nbsp;
        
        <input rel="60" type="radio" name="tamano_contenedor" id="clase_45" value="45"/>
        <label for="clase_45">45</label>&nbsp;
        
        <input rel="60" type="radio" name="tamano_contenedor" id="clase_48" value="48"/>
        <label for="clase_48">48</label><br />
        </div>
        
        <div id="tipo_contenedor" style="text-align: center;line-height: 30px;">
        <input type="radio" name="tipo_contenedor" id="clase_dc" checked="checked" value="DC"/>
        <label for="clase_dc">DC</label>&nbsp;
        
        <input type="radio" name="tipo_contenedor" id="clase_hc" value="HC"/>
        <label for="clase_hc">HC</label>&nbsp;

        <input type="radio" name="tipo_contenedor" id="clase_ot" value="OT"/>
        <label for="clase_ot">OT</label>&nbsp;
        
        <input type="radio" name="tipo_contenedor" id="clase_rf" value="RF"/>
        <label for="clase_rf">RF</label>&nbsp;
        
        <input type="radio" name="tipo_contenedor" id="clase_fr" value="FR"/>
        <label for="clase_fr">FR</label>&nbsp;

        <input type="radio" name="tipo_contenedor" id="clase_tq" value="FR"/>
        <label for="clase_tq">TQ</label>&nbsp;        
        </div>
        </td></tr>
        <tr><td>Posición</td><td>
            <table class="opsal_tabla_ancha tabla-estandar tabla-centrada">
            <tbody>
            <tr><th>Col.</th><th>Fila</th><th>Nivel</th></tr>
            <tr>
                <td><input style="width:20px;" type="text" value="" name="posicion_columna" id="posicion_columna" class="posicion" /></td>
                <td><input style="width:20px;" type="text" value="" name="posicion_fila" id="posicion_fila"  class="posicion" /></td>
                <td><input style="width:20px;" type="text" value="" name="posicion_nivel" id="posicion_nivel"  class="posicion" readonly="readonly" /></td>
                </tr>
            </tbody>
            </table>
        </td></tr>  
        <tr><td>Tara (lbs)</td><td><input type="text" value="" id="tara" name="tara" /></td></tr>
        <tr><td>Chasis</td><td><input type="text" value="" id="chasis" name="chasis" /></td></tr>
        <tr><td>Cliente</td><td><input type="text" value="" id="cliente_ingreso" name="cliente_ingreso" /></td></tr>
        <tr><td>Transportista</td><td><input type="text" value="" id="transportista_ingreso" name="transportista_ingreso" /></td></tr>
        <tr><td>Buque</td><td><input type="text" value="" id="buque_ingreso" name="buque_ingreso" /></td></tr>
        
        <tr><td>Cheque</td><td><input type="text" value="" id="cheque" name="cheque_ingreso" /></td></tr>
        
        <tr><td>¿Con daño?</td><td>
        <div id="ingreso_con_danos" style="text-align: center;line-height: 30px;">
        <input type="radio" name="ingreso_con_danos" id="ingreso_con_danos_no" checked="checked" value="0"/>
        <label for="ingreso_con_danos_no">No</label>&nbsp;
        
        <input type="radio" name="ingreso_con_danos" id="ingreso_con_danos_si" value="1"/>
        <label for="ingreso_con_danos_si">Si</label>&nbsp;
        </td></tr>
        
        <tr><td>Observaciones</td><td><textarea name="observaciones_ingreso"></textarea></td></tr>
    </tbody>
</table>
<input type="hidden" name="guardar" value="guardar" />
<input type="submit" id="ingresar_contenedor" value="Ingresar contenedor" /> <span id="indicador_de_envio"></span>
</form>

<script type="text/javascript">
    cubicaje = 0;
    afinidad = 20;

    color = 'black';
    setInterval(function() {
        var elementos = $('#contenedor_visual');
        elementos.css('background-color',color);
        color = (color == 'black' ? '#00FAFF' : 'black');
        elementos.css('color',color);
    }, 500);

    
    function cambiarCursor(valor){
        cubicaje = valor;
        $('#opsal_mapa #contenedor_mapa table').css('cursor','url("/IMG/cursor/'+cubicaje+'.gif") 6 9,crosshair');
    }
    
    $(function(){
        
        $("#codigo_contenedor").blur(function(){
            $("#codigo_contenedor").val($("#codigo_contenedor").val().toUpperCase());
        });
        
        $('#frm_ingreso_contenedores').submit(function(event){
            event.preventDefault();

            // Verifiquemos que el número de contenedor pase la válidación
            if (/[\D]{4}\d{7}/.test($("#codigo_contenedor").val()) == false )
            {
                alert ("Ingrese un identificador válido de contenedor.\n4 letras y 7 números.");
                return false;
            }
            
            if (!$("input:radio[name='clase']").is(":checked"))
            {
                alert ("Seleccione una clase de contenedor.");
                return false;
            }
            
            if (!$("input:radio[name='tipo_contenedor']").is(":checked"))
            {
                alert ("Seleccione un tipo de contenedor.");
                return false;
            }
            
            if ($("select#codigo_agencia option:selected").val() == "")
            {
                alert ("Seleccione una agencia.");
                return false;
            }
                        
            if ($("#posicion_columna").val() == "" || $("#posicion_fila").val() == "" || $("#posicion_nivel").val() == "")
            {
                alert ("Verifique la posición ingresada.");
                return false;
            }

            if ($("#transportista_ingreso").val() == "")
            {
                alert ("Seleccione un transportista.");
                return false;
            }
            
            if ($("#tara").val() == "")
            {
                alert ("Verifique tara ingresada.");
                return false;
            }

            if ($("#chasis").val() == "" && !confirm ("El número de chasis esta vacío, continuar?."))
            {
                // Se dió cuenta que faltaba
                return false;
            }
        
            if ($("#chasis").val() != "" && /[\D]{4}\d{6}/.test($("#chasis").val()) == false )
            {
                alert ("Verifique número de chasis ingresado.\n4 letras y 6 números.");
                return false;
            }
            
            if ($("#buque_ingreso").val() == "" && !confirm ("El nombre del buque esta vacío, continuar?."))
            {
                return false;
            }
            
            if ($("#codigo_cheque").val() == "")
            {
                alert ("Ingrese el nombre del cheque.");
                return false;
            }
            
            if ($("#cepa_salida").val() == "")
            {
                alert ("Ingrese la fecha de ingreso de CEPA.");
                return false;
            }            

            if ($("#arivu_ingreso").val() == "")
            {
                alert ("Ingrese la fecha de ingreso del ARIVU.");
                return false;
            }

            if ($("#arivu_referencia").val() == "")
            {
                alert ("Verifique número de referencia de ARIVU ingresado.");
                return false;
            }
            
            $("#indicador_de_envio").html('<img src="/IMG/general/cargando.gif" />');
            
            $("#ingresar_contenedor").attr('disabled','disabled');
            
            //$("#contenedor_mapa").html('<p>Guardando datos...</p><br /><img src="/IMG/general/cargando.gif" />');
            $("#contenedor_visual").css('left',0).css('top',0).css('height',0).css('width',0);
            
            $.post('ajax.ingreso.php',$('#frm_ingreso_contenedores').serialize(),function (){
                iniciar_mapa();
                $('#frm_ingreso_contenedores')[0].reset();
            });
        });
        
        $("#contenedor_mapa").bind('mapa_iniciado',function(){
            cambiarCursor(20);
            $("#indicador_de_envio").empty();
            $("#codigo_contenedor").focus();
            $("#ingresar_contenedor").removeAttr('disabled');
        });
        
        $('#opsal_mapa #contenedor_mapa table td').live('click',function(){
            
            if (parseInt($(this).attr('nivel')) > 4)
            {
                alert('No se puede ubicar un contenedor en 6 nivel. Modo estricto esta activado.');
                return false;
            }
            
            if ($(this).attr('afinidad') != 'libre' && $(this).attr('afinidad') !=  afinidad)
            {
                alert('No se puede ubicar un contenedor de ' + afinidad + ' pies sobre uno de ' + $(this).attr('afinidad') + ' pies. Modo estricto esta activado.');
                return false;
            }

            if ($(this).hasClass('contenedor_zona_muerta'))
            {
                alert('No se puede ubicar el contenedor en este punto. Modo estricto esta activado.');
                return false;
            }

            
            var x = $(this).attr('x');
            var y = $(this).attr('y');
            
            $("#posicion_columna").val($(this).attr('col'));
            $("#posicion_fila").val($(this).attr('fila'));
            $("#posicion_nivel").val((parseInt($(this).attr('nivel')) + 1));
            
            referencia = $('#'+x+'_'+y);
            
            console.log('Ubicando contenedor de ' + cubicaje + ' pies³ en '+ x + ',' + y + '['+referencia.position().left+','+referencia.position().top+']');            
            $("#contenedor_visual").css('left',(referencia.position().left)).css('top',(referencia.position().top)).css('height',((19*(cubicaje/20))+1) + 'px').css('width','11px');  
        });
        
        $("#ingreso_con_danos").buttonset();
        $( "#tamano_contenedor input[type='radio']" ).button();
        $( "#tipo_contenedor input[type='radio']" ).button();
        
        $('#tamano_contenedor input[type="radio"],#tipo_contenedor input[type="radio"]').change(function(){
            $("#contenedor_visual").css('left',0).css('top',0).css('height',0).css('width',0);
            $("#posicion_columna").val('');
            $("#posicion_fila").val('');
            $("#posicion_nivel").val('');
            
            afinidad = $('#tipo_contenedor input[type="radio"]:checked').val() + $('#tamano_contenedor input[type="radio"]:checked').val();
            cambiarCursor($('#tamano_contenedor input[type="radio"]:checked').attr('rel'));
        });
        
        $('.posicion').change(function(){
            $('#opsal_mapa #contenedor_mapa table td[col="'+$('#posicion_columna').val()+'"][fila="'+$('#posicion_fila').val()+'"]').trigger('click');
        });
        

    });
</script>