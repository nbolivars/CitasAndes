$(document).ready(function () {

    $('#DivOrdenMedicamentos').css({'display': 'none'});
    $('#alerta').css({'display': 'none'});
    $('#DivDatos').css({'display': 'none'});
    $('#Divcanpte').css({'display': 'none'});
    $('#TxtNumIde').numeric(0);
    $('#TxtNumIde2').numeric(0);
    $('#TxtAaNac').numeric();
    $('#TxtMnNac').numeric();
    $('#TxtDdNac').numeric();
    $('#TxtDescuento').numeric();
    $('#TxtTelefono').numeric();
    $("#frmagenda").validate();
    //validaGrupoFormfrmbuscarpte.init();




    var xfecha = new Date();
    var xano = xfecha.getFullYear();
    $('#TxtFechaIni').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        yearRange: "1915:" + xano,
        minDate: 0
    });

    $.ajax({
        cache: false,
        url: 'FrmAgendaMedicos.php',
        type: 'post',
        dataType: 'json',
        data: {jFuncionPhp: 'PageLoad'}
    }).done(function (datos) {
        $.each(datos, function (index, contenido) {
            jOpcion = '<option value=' + contenido.Id + '>' + contenido.Nombre + '</option>';
            $('#CmbMedicos').append(jOpcion);
        });
    }).error(function (xlr, status, error) {

    });


    $('#TxtNomDx').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: 'FrmAgendarCitas.php',
                dataType: "json",
                type: "POST",
                data: {
                    jNomDx: request.term,
                    jFuncionPhp: 'BuscaDx'
                },
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.Nombre,
                            value: item.Nombre,
                            codigo: item.Codigo
                        }
                    }));
                }
            });
        },
        select: function (event, ui) {
            $('#TxtCodDx').val(ui.item.codigo);
        },
        autoFocus: true,
        minLength: 2
    });



    $('#CmbMedicos').on('change', function () {
        if (CmbMedicos.selectedIndex == 0) {
            return false;
        }
        jMedico = CmbMedicos.value;
        //$('#CmbEspe').html("");   
        $('#CmbEspe').children('option:not(:first)').remove();
        $.ajax({
            cache: false,
            url: 'FrmAgendaMedicos.php',
            type: 'post',
            dataType: 'json',
            data: {
                jFuncionPhp: 'BuscaEspecialidad',
                jMedico: jMedico
            }
        }).done(function (datos) {
            $.each(datos, function (index, contenido) {
                jOpcion = '<option value=' + contenido.Id + '>' + contenido.Nombre + '</option>';
                $('#CmbEspe').append(jOpcion);
            });
        });
    });

    $('#BtnBuscarPte').on('click', function (e) {
        //$("#frmagenda").validate();
        //validaGrupoForm.init();
        //if (jQuery('#frmagenda').validate().form()) {
            consultaagenda();
       // }
    });


    $(document).on('click', '.selecciona', function () {
        var idagenda=$(this).data("id");
        location.href="Facturacion/FrmFacturaManual.html/"+idagenda
    });

    $(document).on('click', '#addshedule ', function () {
        if (jQuery('#frmbuscarpte').validate().form()) {
            regagenda();
        }
    });
    
  
});




function consultaagenda() {
    var tipodoc=$("#CmbTipoDoc").val();
    var ndoc=$("#TxtNdoc").val();
    
    $.ajax({
        cache: false,
        url: 'FrmAgendaConsultaFact.php',
        type: 'POST',
        dataType: 'json',
        data: 'jFuncionPhp=BuscarAgenda&tipodoc='+ tipodoc+'&ndoc='+ ndoc,
        success: function (datos) {
            $('#TabCitas tbody tr').remove();
            if (datos != ''){
                $('#txtidagenda').val(datos[0].idagenda);
                var hoy = new Date();
                // var hora =hoy.getMonth() +  '/' + hoy.getDay()  + '/'  + hoy.getFullYear() + ' ' + hoy.getHours() + ':' + hoy.getMinutes()  ;

                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth() + 1; //January is 0!
                var yyyy = today.getFullYear();

                if (dd < 10) {
                    dd = '0' + dd;
                }

                if (mm < 10) {
                    mm = '0' + mm;
                }
                var today = mm + '/' + dd + '/' + yyyy + ' ' + hoy.getHours() + ':' + hoy.getMinutes();

                $.each(datos, function (index, contenido) {
                    jFila = '<tr>';
                   // if (contenido.estado === '1' || contenido.estado === '2') {
    //                    var fecha = $("#TxtFechaIni").val();
    //                    var reemplazo = fecha.split('-');
    //                    var horaInicio = (reemplazo[1] + '/' + reemplazo[2] + '/' + reemplazo[0] + ' ' + contenido.horaasginada);
    //                    var now = today;
    //                    var then = horaInicio;
    //                    var diff = moment.duration(moment(then).diff(moment(now)));
                            if (contenido.estado === '1'){
                            jFila += '<td class=="centro"><img src="../Imagenes/bloc.png" class="urlamigable" data-url="FrmFacturaManual" data-id="' + contenido.idencrip + '"  ></td>';}
                            else if (contenido.estado === '2'){
                            jFila += '<td class=="centro"><img src="../Imagenes/ok.png"  data-id="' + contenido.idencrip + '"  ></td>';}
                            else if (contenido.estado === ''){
                            jFila += '<td class=="centro">..</td>';}

                    //}
                    //else {
                        //jFila += '<td class=centro>... </td>';

                    //}
                    jFila += '<td class="centro">' + contenido.fechaasignada + '</td>';
                    jFila += '<td class=="centro">' + contenido.horaasginada + '</td>';
                    jFila += '<td class=="centro">' + contenido.nommed + '</td>';
                    jFila += '<td class=="centro">' + contenido.nomespe + '</td>';
                    jFila += '</tr>';
                    $('#TabCitas').append(jFila);
                });
            }
            else{
                alertas({
                    sTitle: "Facturación",
                    sHtml: "EL paciente no tiene cita creada para la fecha",
                    oTipo: "warning"
                });
            }
        }
    }).error(function (error) {
        alert('Ocurrio un error en el Servidor');
    });

}



function padNmb(nStr, nLen) {
    var sRes = String(nStr);
    var sCeros = "0000000000";
    return sCeros.substr(0, nLen - sRes.length) + sRes;
}

function stringToSeconds(tiempo) {
    var sep1 = tiempo.indexOf(":");
    var sep2 = tiempo.lastIndexOf(":");
    var hor = tiempo.substr(0, sep1);
    var min = tiempo.substr(sep1 + 1, sep2 - sep1 - 1);
    var sec = tiempo.substr(sep2 + 1);
    return (Number(sec) + (Number(min) * 60) + (Number(hor) * 3600));
}

function secondsToTime(secs) {
    var hor = Math.floor(secs / 3600);
    var min = Math.floor((secs - (hor * 3600)) / 60);
    var sec = secs - (hor * 3600) - (min * 60);
    return padNmb(hor, 2) + ":" + padNmb(min, 2);
}

function substractTimes(t1, t2) {
    var secs1 = stringToSeconds(t1);
    var secs2 = stringToSeconds(t2);
    var secsDif = secs1 - secs2;
    return secondsToTime(secsDif);
}


var validaGrupoForm = function () {
    return {
        //main function to initiate the module
        //función para validar el ingreso de información
        init: function () {

            var form = $('#frmagenda');
            //var error = $('.alert-error', form);
            //  var success = $('.alert-success', form);
            $('#frmagenda').validate({
                rules: {
                    CmbMedicos: {required: true},
                    CmbEspe: {required: true},
                    TxtFechaIni: {required: true}
                },
                showErrors: function (errorMap, errorList) {
                    $.each(this.successList, function (index, value) {
                        return $(value).popover("hide");
                    });
                    return $.each(errorList, function (index, value) {
                        var _popover;
                        console.log(value.message);
                        _popover = $(value.element).popover({
                            trigger: "manual",
                            placement: "top",
                            content: value.message,
                            template: "<div class=\"popover\"><div class=\"arrow\"></div><div class=\"popover-inner\"><div class=\"popover-content\"><p></p></div></div></div>"
                        });
                        _popover.data("popover").options.content = value.message;
                        return $(value.element).popover("show");
                    });
                }
            });
        }
    };
}();
