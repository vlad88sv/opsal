<?php
if (empty($_GET['modo']))
    $_GET['modo'] = 'ingreso';

$menu[] = array('url' => '/contenedores.html','modo' => 'ingreso','titulo' => 'RECEPCION');
$menu[] = array('url' => '/contenedores.html','modo' => 'remociones','titulo' => 'REMOCION');
$menu[] = array('url' => '/contenedores.html','modo' => 'salida','titulo' => 'DESPACHO');
$menu[] = array('url' => '/contenedores.html','modo' => 'salida.bloque','titulo' => 'DESPACHO BLOQUE');

foreach ($menu AS $id => $datos)
{
    echo '<a class="opsal_pestaña '.($datos['modo'] == $_GET['modo'] ? 'opsal_pestaña_seleccionada' : '').'" href="'.$datos['url'].'?modo='.$datos['modo'].'">'.$datos['titulo'].'</a>';
}
?>
<table id="opsal_ims" class="opsal_tabla_ancha" style="table-layout: fixed;">
<tbody>
<tr>
<td style="width:330px;vertical-align:top;">
<?php
switch ($_GET['modo'])
{
    case 'ingreso':
        require_once('PHP/contenedores.ingreso.php');
        break;
    case 'remociones':
        require_once('PHP/contenedores.remociones.php');
        break;
    case 'salida':
        require_once('PHP/contenedores.salida.php');
        break;
    case 'salida.bloque':
        require_once('PHP/contenedores.salida.bloque.php');
        break;
    default:
        echo '<p>No implementado</p>';
}
?>
</td>
<td id="opsal_mapa" style="width:755px;background-color:white;text-align:left;vertical-align: top;">
    <table id="opsal_regla_superior" style="width:732px !important;height:10px;left: 14px;position: relative;">
        <tr>
            <?php
            $c = 'SELECT x2 FROM opsal_posicion GROUP BY x2 ORDER BY x DESC';
            $r = db_consultar($c);
            
            while ($f = mysqli_fetch_assoc($r))
            {
                $x2 = $f['x2'][0].(isset($f['x2'][1]) ? '<br />'.$f['x2'][1] : '');
                echo '<td>'.$x2.'</td>';
            }
            ?>
        </tr>
    </table>
    <table id="opsal_regla_lateral" style="float:left;width:14px;">
        <?php
        $c = 'SELECT y2 FROM opsal_posicion GROUP BY y2 ORDER BY y DESC';
        $r = db_consultar($c);
        
        while ($f = mysqli_fetch_assoc($r))
        {
            echo '<tr><td>'.$f['y2'].'</td></tr>';
        }
        ?>    
    </table>
    
    <div style="position: relative;float:left;width:732px;">
    
        <div id="contenedor_mapa" style="text-align:center;">
            <p>Actulizando visualización...</p><br />
            <img src="/IMG/general/cargando.gif" />
        </div>
        <div id="contenedor_visual" style="position:absolute;background-color:#00FAFF;z-index:99;"></div>
    </div>
    
    <div style="clear: both; position: relative;bottom: 0px;">
        <table style="table-layout: fixed;" class="opsal_tabla_ancha tabla-centrada tabla-estandar">
            <tr><td style="width:33.33%">SALIDA</td><td style="width:33.33%">OFICINA</td><td style="width:33.33%">ENTRADA</td></tr>
        </table>
        <hr />
        <table style="margin: auto;">
            <tr>
                <td rel="1" class="limitar_nivel contenedor_mapa_casilla_estiba_1">&nbsp;</td>
                <td rel="2" class="limitar_nivel contenedor_mapa_casilla_estiba_2">&nbsp;</td>
                <td rel="3" class="limitar_nivel contenedor_mapa_casilla_estiba_3">&nbsp;</td>
                <td rel="4" class="limitar_nivel contenedor_mapa_casilla_estiba_4">&nbsp;</td>
                <td rel="5" class="limitar_nivel contenedor_mapa_casilla_estiba_5">&nbsp;</td>
            </tr>
            <tr>
                <td>Nivel 1</td>
                <td>Nivel 2</td>
                <td>Nivel 3</td>
                <td>Nivel 4</td>
                <td>Nivel 5</td>
            </tr>
        </table>
    </div>
</td>
</tr>
</tbody>
</table>
<script type="text/javascript">
    // Iniciemos el mapa
    function iniciar_mapa(opciones) {
        opciones = typeof opciones !== 'undefined' ? opciones : {};
        $.post('ajax.mapa.php',opciones, function (data){
            var tiempo_inicio = new Date().getTime();
            $('#contenedor_mapa').html(data.mapa);
            $("#contenedor_mapa").trigger('mapa_iniciado',data);
            // Select all elements that are to share the same tooltip
            var elems = $('#contenedor_mapa td');
     
            $('<div />').qtip(
            {
                    content: ' ',
                    position: {
                            my: 'bottom right',
                            at: 'top left',
                            target: 'event', 
                            effect: false
                    },
                    show: {
                            target: elems,
                            delay: 0,
                            solo: true
                    },
                    hide: {
                            target: elems,
                            delay: 0,
                    },
                    events: {
                            show: function(event, api) {
                                    // Update the content of the tooltip on each show
                                    var target = $(event.originalEvent.target);
     
                                    if(target.length) {
                                            api.set('content.text', target.attr('tooltip'));
                                    }
                            }
                    }
            });
            console.log('Mapa renderizado en ' + (new Date().getTime() - tiempo_inicio) + 'us');
        }, 'json');
    }

    
    $(function(){
        iniciar_mapa();
        
        $('.limitar_nivel').click(function(){
            
        });
        
        $('#opsal_mapa #contenedor_mapa table td').live('contextmenu',function(event){
            event.preventDefault();
            jQuery.facebox($(this).attr('tooltip'));
        });
        
        $( "#tipo_movimiento" ).buttonset();
                
        $( "#clase_contenedor" ).buttonset();
        
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
        $(".calendariocontiempo").datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0}).datetimepicker('setDate', new Date());
        
    });
</script>