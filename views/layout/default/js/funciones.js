/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(cargar);

function cargar() {
    $("#busqueda").keyup(busca);
    $("#busquedae").keyup(buscae);


}

function busca(){
    var texto = $("#busqueda").val();
    if(texto != '') {
    $("#tabla").html('<p><img src="/progreso/public/img/ajax.gif" />Buscando, espere...</p>');
    $.post('/progreso/socios/findSocios/','texto='+texto+'&active=activo',
            escribe,'json');

    }  else {
        $("#tabla").html("No se ha introducido texto");
    }

}

function buscae(){
    var cadena = $("#busquedae").val();
    if(cadena !== '') {
    $("#tabla").html('<p><img src="/progreso/public/img/ajax.gif" />Buscando, espere...</p>');
    $.post('/progreso/socios/findSocios/0','texto='+cadena+'&active=0',
            function(respuesta) {
                var texto = '<tr><th>Nro.</th><th>Nombre</th><th>Apellido</th><th>Activar</th></tr>';
                for(var i = 0; i < respuesta.length; i++){

                    texto+='<tr>';
                    texto+= '<td>'+respuesta[i].id_socio+'</td><td>'+respuesta[i].nombre+'</td><td>'+respuesta[i].apellido+'</td>';
                    texto+='<td><a href="/progreso/socios/activar/'+respuesta[i].id_socio+'"><img src="/progreso/views/layout/default/img/active.png"></a></td>';

                    texto+= '</tr>';
                }

    $("#tabla").html(texto);
            },'json');

    }  else {
        $("#tabla").html("No se ha introducido texto");
    }

}

function escribe(respuesta) {
    var texto = '<tr><td>Nro.</td><td>Nombre</td><td>Apellido</td><td></td><td></td></tr>';
    for(var i = 0; i < respuesta.length; i++){
        texto+='<tr>';
        texto+= '<td>'+respuesta[i].id_socio+'</td><td>'+respuesta[i].nombre+'</td><td>'+respuesta[i].apellido+'</td>';
        texto+='<td><a href="/progreso/socios/editar/'+respuesta[i].id_socio+'"><img src="/progreso/views/layout/default/img/edit.png"></a></td>\n\
        <td><a href="/progreso/socios/confirmar/'+respuesta[i].id_socio+'"><img src="/progreso/views/layout/default/img/delete.png"></a></td>';
        texto+= '</tr>';
    }

    $("#tabla").html(texto);

}



function escribeMov(respuesta) {
    var texto = '<tr><th class="text-center">Fecha</th><th class="text-center">Tipo</th><th class="text-center">Importe</th><th class="text-center">Eliminar</th></tr>';
    var saldo = 0;
    for(var i = 0; i < respuesta.length; i++){
        saldo +=  parseInt(respuesta[i].importe);
        var txt = 'Pago';
        if(parseInt(respuesta[i].importe) > 0) {
            txt = 'Cuota';
        }
        texto+='<tr>';
        texto+= '<td class="text-center">'+respuesta[i].fecha_computo+'</td><td class="text-center">'+txt+'</td><td class="text-right">'+respuesta[i].importe+'</td>';
        texto+='<td class="text-center"><a id="'+respuesta[i].id_cuota+'" href="#" class="eliminar"><img src="/progreso/views/layout/default/img/delete.png"></a></td>';
        texto+= '</tr>';
    }

    $("#movs").html(texto);
    $("#total").html("Saldo: "+saldo);

}
