var dataAgenda;
$(document).ready(function () {
    $('#DivGuardando').css('display', 'none');
    $('#DivBloqueaPagina').css({'display': 'none', 'top': '0'});

    var xfecha = new Date();
    var xano = xfecha.getFullYear();
    var jano = xano + 1; //agregra un año a la fecha actual
    $('#TxtFecha').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        yearRange: '1915:'+jano,
        minDate: 0
    });

    $('#TxtHoraIni, #TxtHoraFin').datetimepicker({
        datepicker: false,
        format: 'H:i',
        enabledHours: [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17],
        step: 5
    });

    function PageLoad() {
        $.ajax({
            cache: false,
            url: 'FrmAgendaMedicos.php',
            type: 'post',
            dataType: 'json',
            data: {jFuncionPhp: 'PageLoad'}
        }).done(function (datos) {
            $.each(datos[0], function (index, contenido) {
                jNombre = contenido.Apellido1+' '+contenido.Apellido2+' '+contenido.Nombre1+' '+contenido.Nombre2;
                jOpcion = `<option value=${contenido.Id}>${jNombre}</option>`;
                $('#CmbMedicos').append(jOpcion);
            });
            LblHoy.innerHTML = datos.Hoy;
        }).error(function (xlr, status, error) {

        });
    }   
    PageLoad();
    
    $('#CmbMedicos').on('change', function() {
        $('#TabAgenda tbody tr').remove();
        if (CmbMedicos.selectedIndex == 0) {
            return false;
        }
        jMedico = CmbMedicos.value;
        $('#CmbEspe').empty();
        $('#TabAgenda tbody tr').remove();
        $.ajax({
            cache: false,
            url: 'FrmAgendaMedicos.php',
            type: 'post',
            dataType: 'json',
            data: {
                jFuncionPhp: 'BuscarInfoMedico',
                jMedico: jMedico
            }
        }).done(function (datos) {
            jHoy = LblHoy.innerHTML.split('-');
            jDateHoy = new Date(jHoy[0], parseInt(jHoy[1])-1, jHoy[2]);

            jOpcion = '<option value="">[ ... ]</option>';
            $('#CmbEspe').append(jOpcion);            
            $.each(datos[0], function (index, contenido) {
                jOpcion = '<option value=' + contenido.Id + '>' + contenido.Nombre + '</option>';
                $('#CmbEspe').append(jOpcion);
            });
            $.each(datos[1], function (index, contenido) {
                jFechaAgenda = contenido.fecagenda.split('-');
                jDateAgenda = new Date(jFechaAgenda[0], parseInt(jFechaAgenda[1]) - 1, jFechaAgenda[2]);
                if (jDateAgenda < jDateHoy || contenido.CantCitas != '0') {
                    jCancela = '';
                } else {
                    jCancela = '<label class="BotonCancelar" title="Cancelar Agenda"> - </label>';
                }

                jFila = `<tr>`;
                jFila += `<td class="centro">${jCancela}</td>`;
                jFila += `<td>${contenido.idesp}~${contenido.NomEsp}</td>`;
                jFila += `<td class="centro">${contenido.fecagenda}</td>`;
                jFila += `<td class="centro">${contenido.horini}</td>`;
                jFila += `<td class="centro">${contenido.horfin}</td>`;
                jFila += `<td class="centro">${contenido.frecagenda}</td>`;
                jFila += `<td class="derecha">${contenido.CantCitas}</td>`;
                jFila += `<td class="derecha">${contenido.idAgenda}</td>`;
                jFila += `</tr>`;
                $('#TabAgenda').append(jFila);
            });
        });
    });

    function Msg(jMsg) {
        DivMsg.style.textAlign ='center';
        DivMsg.innerHTML = jMsg;
        $('#DivMsg').dialog({
            modal: true,
            width:500,
            buttons: {
                'Aceptar': function () {
                    $(this).dialog('close');
                }
            }
        });
    }
    $('#BtnRegistrar').on('click', function (e) {
        if (CmbMedicos.selectedIndex == 0)
        {
            jMsg = 'Debe seleccionar Medico';
            Msg(jMsg);
            return false;
        }
        if (CmbEspe.selectedIndex == 0)
        {
            jMsg = 'Debe seleccionar Especialidad';
            Msg(jMsg);
            return false;            
        }
        if (TxtFecha.value == '')
        {
            jMsg = 'Falta Fecha de la Agenda';
            Msg(jMsg);
            return false;            
        }
        if (CmbFrecuencia.selectedIndex == 0)
        {
            jMsg = 'Debe seleccionar Frecuencia de las Citas';
            Msg(jMsg);
            return false;            
        }
        if (TxtHoraIni.value == '')
        {
            jMsg = 'Falta Hora de Inicio de las Citas';
            Msg(jMsg);
            return false;            
        }
        if (TxtHoraFin.value == '')
        {
            jMsg = 'Falta Hora final de las Citas';
            Msg(jMsg);
            return false;            
        }
        
        $.ajax({
            cache: false,
            url: 'FrmAgendaMedicos.php',
            type: 'POST',
            dataType: 'json',
            data: {
                jFuncionPhp: 'Guardar',
                jMedicos: CmbMedicos.value,
                jEspe: CmbEspe.value,
                jFecha: TxtFecha.value,
                jFrecuencia: CmbFrecuencia.value,
                jHoraIni: TxtHoraIni.value,
                jHoraFin: TxtHoraFin.value
            }
            }).done (function (datos) {

                if (datos.Error != '') {
                    jMsg = 'Ocurrio un Error en el Servidor';
                    Msg(jMsg);
                    return false;
                } 
                if (datos.SiNoRegistrar == 'N') {
                    jMsg = 'Imposible Registrar, Ya existe Agenda Programada en esa Fecha';
                    Msg(jMsg);
                    return false;
                }
                jEspe = CmbEspe.value;
                $('#CmbMedicos').trigger('change');
                CmbEspe.value = jEspe;
                TxtFecha.value = '';
                CmbFrecuencia.selectedIndex = 0;
                TxtHoraIni.value = '';
                TxtHoraFin.value = '';


            });
        });


    $('#BtnRegMot').on('click', function (e) {
        $.ajax({
            cache: false,
            url: 'FrmAgendaMedicos.php',
            type: 'POST',
            dataType: 'json',
            data: 'jFuncionPhp=Update&idagenda=' + $("#idagenda").val() + '&motivo=' + $("#TxtMot").val(),
            success: function (datos) {
                if (datos.Error2 == 'si') {
                    consultaagenda();
                    $('#DivOrdenMedicamentos').css({'display': 'none'});
                    $("#DivOrdenMedicamentos").dialog('close')
                    $("#idagenda").val("");
                    $("#TxtMot").val("");
                }

            }
        });
    });

    $(document).on('click', '.eliminar', function () {
        $('#idagendacan').val($(this).data("id"))// obtengo el id de la agenda
        $('#DivOrdenMedicamentos *').css({'font-family': 'Barlow'});
        $('#DivOrdenMedicamentos').dialog({
            title: 'Cancelar Agenda',
            modal: true,
            height: 300,
            width: 450,
            buttons: {
                'Aceptar': function () {
                    $(this).dialog('close');
                }
            }

        });
    });
});



