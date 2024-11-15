$(document).ready(function () {
    $('#DivOrdenMedicamentos').css({'display': 'none'});
    $('#DivItemFac').css({'display': 'none'});
    $('#DivDatos').css({'display': 'none'});
    $('#dialog').css({'display': 'none'});
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
    $('#txtcantidad').numeric();
    $("#frmagenda").validate();

    $('#TxtFechaPaciente').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: '-0',
        changeMonth: true,
        changeYear: true
    });
    validaGrupoForm.init();
    //validaGrupoFormfrmbuscarpte.init();

    $.ajax({
        cache: false,
        url: 'FrmAgendarCitas.php',
        type: 'POST',
        dataType: 'json',
        data: {jFuncionPhp: 'DatosPte'},
        success: function (datos) {
            console.log(datos);
            // Municipios //
            jFecha = datos[1].Fsys;
            $('#TxtFecha').val(jFecha);
            $('#TxtFechaDoc').val(jFecha);////POR AUGUSTO 2018-11-02
            //$('#TxtSalario').val(datos[1].Salario);

            // Municipios //
            $.each(datos[2], function (index, contenido) {
                jOpcion = '<option value=' + contenido.CodigoMunicipio + '>' + contenido.NombreMunicipio + '</option>';
                $('#CmbCiudadNac').append(jOpcion);
                $('#CmbCiudadRes').append(jOpcion);
            });

            // Etnias //
            $.each(datos[3], function (index, contenido) {
                jOpcion = '<option value=' + contenido.Codigo + '>' + contenido.Nombre + '</option>';
                $('#CmbEtnia').append(jOpcion);
            });

            // Nivel Educativo //
            $.each(datos[4], function (index, contenido) {
                jOpcion = '<option value=' + contenido.Codigo + '>' + contenido.Nombre + '</option>';
                $('#CmbNivelEdu').append(jOpcion);
            });

            // Nacionalidad //
            $.each(datos[5], function (index, contenido) {
                jOpcion = '<option value=' + contenido.Codigo + '>' + contenido.Nombre + '</option>';
                $('#CmbNacionalidad').append(jOpcion);
            });

            // Ocupaciones //
            $.each(datos[6], function (index, contenido) {
                jOpcion = '<option value=' + contenido.Codigo + '>' + contenido.Nombre + '</option>';
                $('#CmbOcupacion').append(jOpcion);
            });

            // Entidades //
            $.each(datos[7], function (index, contenido) {
                jOpcion = '<option value=' + contenido.Codigo + '>' + contenido.Nombre + '</option>';
                $('#CmbEntidad').append(jOpcion);
            });
//            // tipoConsulta //
//            $.each(datos[9], function (index, contenido) {
//                jOpcion = '<option value=' + contenido.idTipoC + '>' + contenido.nomTipoC + '</option>';
//                $('#CmbTipoC').append(jOpcion);
//            });

            // Configuracion //
            $.each(datos[10], function (index, contenido) {
                jOpcion = '<option value=' + contenido.idgen + '>' + contenido.nomConf + '</option>';
                $('#CmbFuente').append(jOpcion);
            });

            // Cancelacion //
            $.each(datos[11], function (index, contenido) {
                jOpcion = '<option value=' + contenido.idgen + '>' + contenido.nomConf + '</option>';
                $('#CmbMotCan').append(jOpcion);
            });

            // Especialidades//
            $.each(datos[12], function (index, contenido) {
                jOpcion = '<option value=' + contenido.Id + '>' + contenido.Nombre + '</option>';
                $('#CmbEspe').append(jOpcion);
            });
           
       
            $.each(datos[14], function (index, contenido) {
                jOpcion = '<option value=' + contenido.idgen + '>' + contenido.nomConf + '</option>';
                $('#CmbNivel').append(jOpcion);
            });
            
            // Tipo Documento //
            $.each(datos[16], function (index, contenido) {
                jOpcion = '<option value=' + contenido.TipoCodigo + '>' + contenido.TipoDescripcion + '</option>';
                $('#CmbTipoDoc').append(jOpcion);
            });
            
            // Tipo Usuario //
            $.each(datos[17], function (index, contenido) {
                jOpcion = '<option value=' + contenido.CodTipoUsurio + '>' + contenido.NomtipoUsuario + '</option>';
                $('#CmbTipoAfil').append(jOpcion);
            });
            
            // Causa Externa //
            
             $.each(datos[18], function (index, contenido) {
                jOpcion = '<option value=' + contenido.Codigo + '>' + contenido.Descripcion + '</option>';
                $('#CmbCausaExterna').append(jOpcion);
            });
            
            // Fecha del Html - Version //
            $('#TxtLastDate').val(datos[9].FileDate);

        }
    });


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
        if (datos.UsuarioReg == 'NoUser') {
            alert('Sesion de usuario ha Expirado, Refresque Aplicativo\n e Ingrese Usuario y contraseña');
            return false;
        } 
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
                        };
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

    /////////////////////////////////
    // Busca Código de Diagnóstico //
    /////////////////////////////////
    $('#TxtCodDx').on('blur', function () {
        jCodDx = $('#TxtCodDx').val();
        if (jCodDx == '') {
            alert('Falta código de Diagnóstico');
            return false;
        }
        $.ajax({
            cache: false,
            url: 'FrmAgendarCitas.php',
            type: 'POST',
            dataType: 'json',
            data: {jFuncionPhp: 'BuscaCodigoDx', jCodDx: jCodDx},
            success: function (datos) {
                if (datos[0].Existe == 'NO') {
                    alert('Codigo de Diagnóstico no Existe ');
                    $('#TxtNomDx').val('');
                    return false;
                }
                $('#TxtNomDx').val(datos[1].Nombre);
            }
        });
    });


    $('#CmbEntidad').on('change', function () {
        if (CmbEntidad.selectedIndex == 0) {
            $('#CmbPrograma').empty();
            return false;
        }
        jEntidad = CmbEntidad.value;

        $.ajax({
            cache: false,
            url: 'FrmFacturaManual.php',
            type: 'post',
            dataType: 'json',
            data: {jFuncionPhp: 'BuscaPrograma', jEntidad: jEntidad}
        }).done(function (datos) {
            $('#CmbPrograma').empty();
            jOpcion = '<option value="000">[ Seleccione ]</option>';
            $('#CmbPrograma').append(jOpcion);

            $.each(datos[0], function (index, contenido) {
                jOpcion2 = '<option value=' + contenido.Id + '>' + contenido.Nombre + '</option>';
                $('#CmbPrograma').append(jOpcion2);
            });

        });
    });

    $('#CmbTipoC').on('change', function () {
        if ($('#CmbTipoC').val() === '000') {
            $('#DivItemFac').css({'display': 'block'});
            $('#BtnBuscarDispo').css({'display': 'none'});

        } else if ($('#CmbTipoC').val() !== '000') {
            $('#tblAgenda tbody tr').html();
            ;
            $('#tblAgenda tbody tr').remove();
            $('#DivItemFac').css({'display': 'none'});
            $('#TxtCodigo').val('');
            $('#TxtDescripcion').val('');
            $('#TxtCanti').val('');
            $('#idcodigo').val('');
            $('#BtnBuscarDispo').css({'display': 'block'});
        }
    });


    /////////////////////////////////////////
    // Autocompletar Servicios/Suministros //
    /////////////////////////////////////////
    $('#TxtDescripcion').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: 'FrmAgendarCitas.php',
                dataType: "json",
                type: "POST",
                data: {
                    jDescripcion: request.term,
                    jFuncionPhp: 'BuscaDescripcion'
                },
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.Descripcion,
                            value: item.CodigoCups,
                            codigo: item.Codigo,
                            valor: item.Valor,
                            iva: item.Iva,
                            tipoPro: item.TipoProc,
                            tipoServicio: item.TipoServicio,
                            CodigoCups: item.CodigoCups,
                            CodigoSoat: item.CodigoSoat
                        };
                    }));
                }
            });
        },
        select: function (event, ui) {
            $('#TxtCodigo').val(ui.item.CodigoCups);

            $('#idcodigo').val(ui.item.codigo);
//            $('#TxtCodigoSoat').val(ui.item.CodigoSoat);
            $('#TxtDescripcion').val(ui.item.label);
            $('#TxtDescripcion').val(ui.item.label);

        },
        autoFocus: true,
        minLength: 2
    });
    /////////////////////////////////////////
    // Autocompletar Servicios/Suministros //
    /////////////////////////////////////////
    $('#TxtCodigo').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: 'FrmAgendarCitas.php',
                dataType: "json",
                type: "POST",
                data: {
                    jDescripcion: request.term,
                    jFuncionPhp: 'BuscaCodigo'
                },
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.Descripcion,
                            value: item.CodigoCups,
                            codigo: item.Codigo,
                            valor: item.Valor,

                            tipoPro: item.TipoProc,
                            tipoServicio: item.TipoServicio,
                            CodigoCups: item.CodigoCups,
                            CodigoSoat: item.CodigoSoat
                        };
                    }));
                }
            });
        },
        select: function (event, ui) {
            $('#TxtCodigo').val(ui.item.CodigoCups);

            $('#idcodigo').val(ui.item.codigo);
//            $('#TxtCodigoSoat').val(ui.item.CodigoSoat);
            $('#TxtDescripcion').val(ui.item.label);
            $('#TxtDescripcion').val(ui.item.label);

        },
        autoFocus: true,
        minLength: 2
    });

    $('#BtnAddProc').click(function () {
        if ($('#TxtCodigo').val() === '') {
            alert('No ha epecificado Sevicio/Suministro');
            $('#TxtDescripcion').focus();
            return false;
        }
        if ($('#TxtCanti').val() === '') {
            alert('Error en los datos del Servicio/Suministro');
            $('#TxtCantidad').focus();
            return false;
        }

        var idage = $('#idagenda').val();
        var txtidmed = $('#txtidmed').val();
        var txtidespmed = $('#txtidespmed').val();
        var txtfecha = $('#txtfecha').val();
        var txthoraini = $('#txthoraini').val();
        var jOpcion2 = "";
        var TxtDescripcion = $('#TxtDescripcion').val();
        var TxtCodigo = $('#TxtCodigo').val();
        var TxtCanti = $('#TxtCanti').val();
        var idcodigo = $('#idcodigo').val();
        
        jFila = '<tr>';
        jFila += '<td> ' + TxtCodigo + '</td>';
        jFila += '<td>' + TxtDescripcion + '</td>';
        jFila += '<td class="numero">' + TxtCanti + '</td>';
        jFila += '<td>' + idcodigo + '</td>';
        jFila += '<td> <input type="button" id="BtnBuscarDisponibleHoras" value="Buscar" class="boton" /><input type="hidden" id="datahora" name="datahora"><label id="horadispo"></td>';
        jFila += '<td><img src="../Imagenes/basura48.png" class="BorraRegistro" title="Eliminar"/></td>';

        jFila += '</tr>';
        $('#TablaFactura').append(jFila);
       // }


        $('#TxtCodigo').val('');
        $('#TxtDescripcion').val('');
        $('#TxtCanti').val('');
        $('#idcodigo').val('');

//        if ($('#TxtCanti').val() > 1) {
//            $.ajax({
//                cache: false,
//                url: 'FrmAgendarCitas.php',
//                type: 'POST',
//                dataType: 'json',
//                data: "jFuncionPhp=consultadisponibles&agenda=" + idage + "&txtidmed=" + txtidmed + "&txtidespmed=" + txtidespmed + "&txtfecha=" + txtfecha + "&txthoraini=" + txthoraini + "&TxtCanti=" + $('#TxtCanti').val() + "&idcodigo=" + $('#idcodigo').val() + "&CmbTipoC=" + $('#CmbTipoC').val(),
//                success: function (datos) {
//                    console.log(datos);
//                    $.each(datos.Error2, function (index, contenido) {
//                        jOpcion2 += '<input type="checkbox" name="fechas[]" id="fechas" data-codigo="' + idcodigo + '" value="' + contenido.agenda + '" data-fecha="' + contenido.fecagenda + '" data-hora="' + contenido.hora + '">' + contenido.fecagenda + ' ' + contenido.hora + '<hr>';
//                    });
//                    jFila = '<tr>';
//                    jFila += '<td> ' + TxtCodigo + '</td>';
//                    jFila += '<td>' + TxtDescripcion + '</td>';
//                    jFila += '<td class="numero">' + TxtCanti + '</td>';
//                    jFila += '<td>' + idcodigo + '</td>';
//                    jFila += '<td> <input type="button" id="BtnBuscarDisponibleHoras" data-toggle="modal" data-target="#myModalHoras" value="Buscar" class="boton" /></td>';
//                    jFila += '<td><img src="../Imagenes/basura48.png" class="BorraRegistro" title="Eliminar"/></td>';
//
//                    jFila += '</tr>';
//                    $('#TablaFactura').append(jFila);
//                }
//            });
//        } else {
//            jFila = '<tr>';
//            jFila += '<td> ' + TxtCodigo + '</td>';
//            jFila += '<td>' + TxtDescripcion + '</td>';
//            jFila += '<td class="numero">' + TxtCanti + '</td>';
//            jFila += '<td>' + idcodigo + '</td>';
//            jFila += '<td> <input type="button" id="BtnBuscarDisponibleHoras" data-toggle="modal" data-target="#myModalHoras" value="Buscar" class="boton" /></td>';
//            jFila += '<td><img src="../Imagenes/basura48.png" class="BorraRegistro" title="Eliminar"/></td>';
//
//            jFila += '</tr>';
//            $('#TablaFactura').append(jFila);
//        }


        $('#TxtCodigo').val('');
        $('#TxtDescripcion').val('');
        $('#TxtCanti').val('');
        $('#idcodigo').val('');
    });

    $(document).on("click", ".BorraRegistro", function () {
        confirm('Seguro de Elimiar el Registro ?');
        if (confirm == false) {
            return false;
        }
        $(this).parents('tr').remove();
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

    $('#CmbEspe').on('change', function () {
        if (CmbEspe.selectedIndex == 0) {
            return false;
        }
        jMedico = $('#CmbEspe').val();
        //$('#CmbEspe').html("");   
        //$('#CmbTipoC').children('option:not(:first)').remove();
        $.ajax({
            cache: false,
            url: 'FrmAgendarCitas.php',
            type: 'post',
            dataType: 'json',
            data: {
                jFuncionPhp: 'BuscaTipo',
                jMedico: jMedico
            }
        }).done(function (datos) {
            $('#CmbTipoC').html('');
            $('#CmbTipoC').append('<option value="" selected="selected">Seleciconar Tipo de Consulta</option>');
//            $('#CmbTipoC').append('<option value="000" >Otros</option>');
            $.each(datos, function (index, contenido) {
                jOpcion = '<option value=' + contenido.Codigo + '>' + contenido.Descripcion + '</option>';
                $('#CmbTipoC').append(jOpcion);
            });
        });
    });

    $('.btnbuscar').on('click', function (e) {
        $("#frmagenda").validate();
        validaGrupoForm.init();
        if (jQuery('#frmagenda').validate().form()) {
            consultaagenda();
        }
    });

    $('#BtnBuscarDispo').on('click', function (e) {
        //$("#frmagenda").validate();
        //validaGrupoForm.init();
        if (jQuery('#frmagenda').validate().form()) {
            consultaMedicos();
         }
    });

//    $('#BtnBuscarDisponibleHoras').on('click', function (e) {
//        //$("#frmagenda").validate();
//        //validaGrupoForm.init();
//        //if (jQuery('#frmagenda').validate().form()) {
//       
//      //  debugger;
//        
//        // }
//    });


    $(document).on('click', '#BtnBuscarDisponibleHoras', function () {
        consultaMedicoshoras();
        $("#myModalHoras").modal("show");
    });

    $(document).on('click', '.selecciona', function () {
        LimpiarForm();
        $(".parpadea").html("");
        $("#txtidmed").val($(this).data("idmed"));
        $("#txtidespmed").val($('#CmbEspe').val());
        $("#txtfecha").val($(this).data("fecha"));
        $('#idagenda').val($(this).data("idagenda"));
        $('#txthoraini').val($(this).data("value"));
        $("#IdTipoCOnsulta").val($('#CmbTipoC').val());
        $('#myModal').modal({backdrop: 'static', keyboard: false})
        $('#myModal').modal('show');
    });
    
    $(document).on('click', '.seleccionahora', function () {
        LimpiarForm();
        $("#txtidmed").val($(this).data("idmed"));
        $("#txtidespmed").val($('#CmbEspe').val());
        $("#txtfecha").val($(this).data("fecha"));
        $('#idagenda').val($(this).data("idagenda"));
        $('#txthoraini').val($(this).data("value"));
        $("#IdTipoCOnsulta").val($('#CmbTipoC').val());
        $('#myModal').modal({backdrop: 'static', keyboard: false});
        $('#myModal').modal('show');
    });
    
    /*
    $(document).on('click', '#addshedule ', function () {
        if (jQuery('#frmbuscarpte').validate().form()) {
            regagenda();
        }
    });
    */
    addshedule.addEventListener('click', regagenda, false);


    $(document).on('click', '#cancelshdule ', function () {
        LimpiarForm();
        $("#CmbTipoDoc").val("");
        $("#TxtNdoc").val("");
        $('#myModal').modal('hide');
        $("#DivDatos").slideUp('1s');
        
    });

    $(document).on('click', '.cancelButtonClass ', function () {
        $('#TxtNumIde2').val('');
        $('#TxtNdoc').val('');
        $('#CmbTipoDoc').val('');
        $("#txtidmed").val("");
        $("#txtidespmed").val("");
        $("#txtfecha").val("");
        $('#idagenda').val("");
        $('#txthoraini').val("");
        LimpiarForm();
    });

    function regagenda() {

        if (CmbTipoDoc.selectedIndex == 0) {
            alert('Debe seleccionar Tipo de Documento');
            return false;
        }
        if (TxtNdoc.value == '') {
            alert('Falta Numero de Documento');
            return false;
        }
        if (TxtApellido1.value == '') {
            alert('Falta primer Apellido');
            return false;
        }
        if (TxtNombre1.value == '') {
            alert('Falta Primer Nombre');
                return false;
        }
        if (CmbGenero.selectedIndex == 0) {
            alert('Debe seleccionar Género');
            return false;
        }
        if (TxtFnacimiento.value == '') {
            alert('Error en Fecha de Nacimiento');
            return false;
        }
        if (CmbNacionalidad.selectedIndex == 0) {
            alert('debe seleccionar Nacionalidad');
            return false;
        }
        if (TxtProcedencia.value == '') {
            alert('Falta Procedencia');
            return false;
        }
        if (CmbCiudadNac.selectedIndex == 0) {
            alert('Debe seleccionar Ciudad de Nacimiento');
            return false;
        }
        if (CmbCiudadRes.selectedIndex == 0) {
            alert('Debe seleccionar Municipio de Residencia');
            return false;
        }
        if (TxtDireccion.value == '') {
            alert('Falta Direccion de Residencia');
            return false;
        }
        if (TxtTelefono.value == '') {
            alert('Falta Numero de Teléfono');
            return false;
        }
        if (CmbTipoAfil.selectedIndex == 0) {
            alert('Debe seleccionar Tipo de Usuario');
            return false;
        }

        if (CmbEstdoCivil.selectedIndex == 0) {
            alert('Debe seleccionar Estado Civil');
            return false;
        }
        if (CmbEtnia.selectedIndex == 0) {
            alert('Debe seleccionar Etnia');
            return false;
        }
        if (CmbNivelEdu.selectedIndex == 0) {
            alert('Debe seleccionar Nivel Educativo');
            return false;
        }
        if (CmbOcupacion.selectedIndex == 0) {
            alert('Debe seleccionar Ocupación');
            return false;
        }
        if (CmbEntidad.selectedIndex == 0) {
            alert('Debe Seleccionar Entidad');
            return false;
        }

        jCmbPrograma = document.getElementById('CmbPrograma');
        if (jCmbPrograma.selectedIndex == 0) {
            alert('Debe Seleccionar Programa');
            return false;
        }
        
        if (CmbNivel.selectedIndex == 0) {
            alert('Debe Seleccionar Nivel Salarial');
            return false;
        }
        if (TxtFechaPaciente.value == '') {
            alert('Falta Fecha de Cita Sugerida por el Paciente');
            return false;
        }

        jCmbFuente = document.getElementById('CmbFuente');

        if (jCmbFuente.selectedIndex == 0) {
            alert('Debe Seleccionar Fuente de la Cita');
            return false;
        }

        if (TxtCodDx.value == '' || TxtNomDx.value == '') {
            alert('Error en Diagnóstico');
            return false;
        }



        var TxtestadoFactura = $('#TxtestadoFactura').val();
        $("#addshedule").removeAttr("disabled");
//        if (TxtestadoFactura !== 'N') {
//            $("#addshedule").attr("disabled", "disabled");
//            $('#success-create-modal').modal('show');
//            $('.modal-body-alert').html("");
//            $('.modal-body-alert').append('<img src="../Imagenes/error.svg"  class="img-responsive" width="100px" />');
//            $('.modal-body-alert').append('</br></br></br></br> Número de Autorización existe con la Factura # ' + TxtestadoFactura + '\nEsta factura debería ser Anulada para utilizarla');
//            return false;
//        }
        var idage = $('#idagenda').val();
        var txtidmed = $('#txtidmed').val();
        var txtidespmed = $('#txtidespmed').val();
        var txtfecha = $('#txtfecha').val();
        var idagenda = $('#idagenda').val();
        var txthoraini = $('#txthoraini').val();
        var CmbTipoC = $("#IdTipoCOnsulta").val();
        var CmbFuente = $('#CmbFuente').val();
        var CmbCausaExt = $('#CmbCausaExterna').val();
        var CmbPrograma = $('#CmbPrograma').val();
        var CmbEstadoEmbarazo = $('#CmbEstadoEmbarazo').val();
        
        if (CmbEstadoEmbarazo == '0') {
            
            alert('Debe Seleccionar estado de embarazo');
            return false;
        }
        
        //var txtcantidad = $('#txtcantidad').val();
        jFilaEnc = {
            jTipoDoc: $('#CmbTipoDoc').val(),
            jNumIde: $('#TxtNdoc').val(),
            jApellido1: $('#TxtApellido1').val().toUpperCase(),
            jApellido2: $('#TxtApellido2').val().toUpperCase(),
            jNombre1: $('#TxtNombre1').val().toUpperCase(),
            jNombre2: $('#TxtNombre2').val().toUpperCase(),
            jGenero: $('#CmbGenero').val(),
            jFechaNac: $('#TxtFnacimiento').val(),
            jNacionalidad: $('#CmbNacionalidad').val(),
            jProcedencia: $('#TxtProcedencia').val().toUpperCase(),
            jCiudadNac: $('#CmbCiudadNac').val(),
            jCiudadRes: $('#CmbCiudadRes').val(),
            jTelefono: $('#TxtTelefono').val(),
            jDireccion: $('#TxtDireccion').val().toUpperCase(),
            jTipoUsuario: $('#CmbTipoAfil').val(),
            jEstadoCivil: $('#CmbEstdoCivil').val(),
            jEtnia: $('#CmbEtnia').val(),
            jNivelEdu: $('#CmbNivelEdu').val(),
            jOcupacion: $('#CmbOcupacion').val(),
            jEntidad: $('#CmbEntidad').val(),
            TxtAutorización: $('#TxtAutorización').val(),
            TxtCodDx: $('#TxtCodDx').val(),
            txtemail: $('#txtemail').val(),
            CmbNivel: $('#CmbNivel').val(),
            jFechaPaciente: TxtFechaPaciente.value
        };
        jDatosEnc = JSON.stringify(jFilaEnc);

        var jDatosServicios = "";
        var jDatosServiciosFact = "";
        if (CmbTipoC === '000') {
            var jTablaServicios = [];
            var jTablaServiciosFact = [];
            var jCodigo = "";
            var jCantidad = "";
            var jFecha = "";
            var jHora = "";
            var jAgenda = "";
            $('#TablaFactura tbody tr').each(function () {
                var jCodigo = $(this).find('td').eq(3).html();
                var jCantidad = $(this).find('td').eq(2).html();
//
                var jFilaServ = {
                    jCodigo: jCodigo, jCantidad: jCantidad
                };
                jTablaServiciosFact.push(jFilaServ);
            });
            jDatosServiciosFact = JSON.stringify(jTablaServiciosFact);

            jTablaServicios = [];
            $("input[name='fechas[]']:checked").each(function () {

                jCodigo = $(this).data("codigo");
                jFecha = $(this).data("fecha");
                jHora = $(this).data("hora");
                jAgenda = $(this).val();
                var jFilaServ = {
                    jCodigo: jCodigo, jCantidad: jCantidad, jFecha: jFecha, jHora: jHora, jAgenda: jAgenda
                };
                jTablaServicios.push(jFilaServ);
            });
            jDatosServicios = JSON.stringify(jTablaServicios);
        } else {
            jTablaServiciosFact = [];
            jCodigo = $("#CmbTipoC").val();
            jCantidad = 1;

            var jFilaServ = {
                jCodigo: jCodigo, jCantidad: jCantidad
            };
            jTablaServiciosFact.push(jFilaServ);
            jDatosServiciosFact = JSON.stringify(jTablaServiciosFact);
        }



        //return false;
        action = $('#txtestado').val() === 'N' ? 'addpte' : 'updatepte';

        $.ajax({
            cache: false,
            url: 'FrmAgendarCitas.php',
            type: 'POST',
            dataType: 'json',
            data: 'jFuncionPhp=RegistarInfo&datos=' + jDatosEnc + "&CmbCausaExt=" + CmbCausaExt + "&status=" + $('#txtestado').val() + "&action=" + action + "&agenda=" + idage + "&txtidmed=" + txtidmed + "&txtidespmed=" + txtidespmed + "&txtfecha=" + txtfecha + "&idagenda=" + idagenda + "&txthoraini=" + txthoraini + "&CmbTipoC=" + CmbTipoC + "&CmbFuente=" + CmbFuente + "&CmbPrograma=" + CmbPrograma + "&jDatosServicios=" + jDatosServicios + '&jDatosServiciosFact=' + jDatosServiciosFact + '&jEstadoEmbarazo=' + CmbEstadoEmbarazo,
            success: function (datos) {
                $('#tblAgenda tbody tr').remove();
                if (datos.Error2 === 'si') {
                    $('#myModal').modal('hide');
                    LimpiarForm();
                    /*
                    alertas({
                        sTitle: "Agenda",
                        sHtml: "Registro creado con exito",
                        oTipo: "success"
                    });
                    */
                    alert('Registro guardado con Exito');
                    location.reload();
                }
            }
        }).error(function (error) {
            alert('Ocurrio un error en el Servidor');
        });


    }


    jQuery(".confirmar").on("click", function () {
        LimpiarForm();
        consultaagenda();
        $('#success-create-modal').modal('hide');

    });

    jQuery(".aceptar").on("click", function () {
        $('#success-create-modal').modal('hide');
        
    });

    //////////////////////////////////////
    /////////////////////////////
    // Buscar doc de Identidad //
    /////////////////////////////
    $('#BtnBuscarPte').click(function () {

        //Blancos();
        $("#DivDatos").slideUp("12.0s");
        var jTipoDoc = $('#CmbTipoDoc').val();
        var jNumDoc = $('#TxtNdoc').val();

        if ($('#CmbTipoDoc').val() == '') {
            alert('Seleccione tipo de Documento ... ');
            $('#CmbTipoDoc').focus();
            return false;
        }

        if ($('#TxtNdoc').val() == '') {
            alert('Falta Numero de Documento ... ');
            $('#TxtNdoc').focus();
            return false;
        }

        $.ajax({
            cache: false,
            url: 'FrmAgendarCitas.php',
            type: 'POST',
            dataType: 'json',
            data: "jFuncionPhp=BuscarDoc&" + $("#frmagenda").serialize() + "&CmbTipoDoc=" + $('#CmbTipoDoc').val() + "&TxtNdoc=" + $('#TxtNdoc').val() + "&txtfecha=" + $('#txtfecha').val() + "&txthoraini=" + $('#txthoraini').val(),
            success: function (datos) {
                $("#txtestado").val(datos.Existe);
                if (datos.Existe === 'S') {

                    $('#TxtNumIde2').val($('#TxtNdoc').val());
                    $('#TxtApellido1').val(datos[0][0].Apellido1);
                    $('#TxtApellido2').val(datos[0][0].Apellido2);
                    $('#TxtNombre1').val(datos[0][0].Nombre1);
                    $('#TxtNombre2').val(datos[0][0].Nombre2);
                    $('#CmbGenero').val(datos[0][0].Genero);
                    $('#TxtFnacimiento').val(datos[0][0].FechaNacimiento);
                    $('#CmbNacionalidad').val(datos[0][0].Nacionalidad);
                    $('#TxtProcedencia').val(datos[0][0].Procedencia);
                    $('#CmbCiudadNac').val(datos[0][0].LugarNacimiento);
                    $('#CmbCiudadRes').val(datos[0][0].CiudadResidencia);
                    $('#TxtDireccion').val(datos[0][0].Direccion);
                    $('#TxtTelefono').val(datos[0][0].Telefono);
                    $('#CmbTipoAfil').val(datos[0][0].TipoAfiliado);
                    $('#CmbEstdoCivil').val(datos[0][0].EstadoCivil);
                    $('#CmbEtnia').val(datos[0][0].Etnia);
                    $('#txtemail').val(datos[0][0].Email);
                    $('#CmbNivelEdu').val(datos[0][0].NivelEducativo);
                    $('#CmbOcupacion').val(datos[0][0].Ocupacion);
                    $('#txtemail').val(datos[0][0].Email);
                    $('#CmbEntidad').val();
                    jFecha = datos[0][0].FechaNacimiento;

                    jAaNac = jFecha.substr(0, 4);
                    jMmNac = jFecha.substr(5, 2);
                    jDdNac = jFecha.substr(8, 2);

                    $('#TxtAaNac').val(jAaNac);
                    $('#TxtMmNac').val(jMmNac);
                    $('#TxtDdNac').val(jDdNac);

                    jFechaFin = new Date();
                    jAnoFin = jFechaFin.getFullYear();
                    jMesFin = jFechaFin.getMonth() + 1;
                    jDiaFin = jFechaFin.getDate();

                    if (jMesFin < 10) {
                        jMesFin = '0' + jMesFin;
                    }
                    if (jDiaFin < 10) {
                        jDiaFin = '0' + jDiaFin;
                    }
                    jFechaActual = jAnoFin + '-' + jMesFin + '-' + jDiaFin;
                    jEdad = RestarFechas(jFechaActual, jFecha);
                    $('#TxtEdad').val(jEdad);
                    if (datos.tiene === 'si') {
                        var hora = '';
                        $("#addshedule").attr("enabled", "enabled");
                        if (datos.tieneHora === 'si') {
                            hora = 'Tiene una cita a esta misma hora, favor cambiarla';
                            $("#addshedule").attr("disabled", "disabled");
                        }

                        $(".parpadea").html("El paciente tiene una cita agendada para este día" + "<br>" + hora);
                        $(".citas").show();
                    } else if (datos.tiene === 'no') {
                        $(".parpadea").html("");
                        $(".citas").hide();

                    }
                } else {
                    if (datos.tiene === 'no') {
                        $(".parpadea").html("");
                        $(".citas").hide();

                    }
                    LimpiarForm();
                    //$('#TxtNumIde2').val($('#TxtNdoc').val());
                    $('#TxtNdoc').on('focus', function () {
                        $(this).select();
                    });

                    $('#TxtNdoc').focus();
                    $('#TxtNumIde2').val($('#TxtNdoc').val());
                    $("#DivDatos").slideDown("12.0s");
                    return false;
                }
                $("#DivDatos").slideDown("12.0s");
                $('#TxtNdoc').on('focus', function () {
                    $(this).select();
                });

                $('#TxtNdoc').focus();


            }
        });
    });

    $('#BtnBuscarauto').click(function () {
        $.ajax({
            cache: false,
            url: 'FrmFacturaManual.php',
            type: 'POST',
            dataType: 'json',
            data: "jFuncionPhp=IniciaFactura&" + "&jEntidad=" + $('#CmbEntidad').val() + "&jPrograma=" + $('#CmbPrograma').val() + "&jAutorización=" + $('#TxtAutorización').val(),
            success: function (datos) {
                $('#TxtestadoFactura').val(datos.ExisteAut);
                if (datos.ExisteAut != 'N') {
                    $('#TxtestadoFactura').val(datos.ExisteAut);
                    $("#addshedule").attr("disabled", "disabled");
                    $(".confirmar").css({'display': 'none'});
                    $(".aceptar").css({'display': 'inline'});
                    $('#success-create-modal').modal('show');
                    $('.modal-body-alert').html("");
                    $('.modal-body-alert').append('<img src="../Imagenes/error.svg"  class="img-responsive" width="100px" />')
                    $('.modal-body-alert').append('</br></br></br></br> Número de Autorización existe con la Factura # ' + datos.ExisteAut + '\nEsta factura debería ser Anulada para utilizarla');
                    return false;
                } else {
                    $("#addshedule").removeAttr("disabled");
                }

            }
        });
    });
    ////////////////////////////

    /////////////////////////////////////////////
    // Cuando digita datos de Fecha Nacimiento //
    /////////////////////////////////////////////

    $("#TxtFnacimiento").on('change', function () {
        jFechaFin = new Date();
        jAnoFin = jFechaFin.getFullYear();
        jMesFin = jFechaFin.getMonth() + 1;
        jDiaFin = jFechaFin.getDate();

        if (jMesFin < 10) {
            jMesFin = '0' + jMesFin;
        }
        if (jDiaFin < 10) {
            jDiaFin = '0' + jDiaFin;
        }
        jFechaActual = jAnoFin + '-' + jMesFin + '-' + jDiaFin;
        jFecha = $('#TxtFnacimiento').val();
        jEdad = RestarFechas(jFechaActual, jFecha);
        $('#TxtEdad').val(jEdad);

    });


    $('#TxtAaNac').on('keyup', function () {
        FechaNacimiento();
    });
    $('#TxtMmNac').on('keyup', function () {
        FechaNacimiento();
    });
    $('#TxtDdNac').on('keyup', function () {
        FechaNacimiento();
    });
    function FechaNacimiento() {
        jFechaNac = $('#TxtAaNac').val() + '-' + $('#TxtMmNac').val() + '-' + $('#TxtDdNac').val();
        $('#TxtFnacimiento').val(jFechaNac);
    }
    $('#TxtAaNac, #TxtMmNac, #TxtDdNac').on('blur', function () {
        if ($('#TxtAaNac') !== '') {
            if ($('#TxtAaNac').val().length != 4) {
                alert('Error en el Año de Nacimiento');
                return false;
            }
        }
        if ($('#TxtMmNac').val() != '') {
            if ($('#TxtMmNac').val().length != 2) {
                alert('Error en el Mes de Nacimiento');
                return false;
            } else {
                if (parseInt($('#TxtMmNac').val()) <= 0 && parseInt($('#TxtMmNac').val()) > 12) {
                    alert('Error en el Mes de Nacimiento');
                    return false;
                }
            }
        }
        if ($('#TxtDdNac').val() != '') {
            if ($('#TxtDdNac').val().length != 2) {
                alert('Error en Día de Nacimiento ... ');
                return false;
            } else {
                if (parseInt($('#TxtDdNac').val()) <= 0 && parseInt($('#TxtDdNac').val()) > 31) {
                    alert('Error en Día de Nacimiento ... ');
                    return false;
                }
            }
        }

        if ($('#TxtFnacimiento').val().length == 10) {
            jFechaFin = new Date();
            jAnoFin = jFechaFin.getFullYear();
            jMesFin = jFechaFin.getMonth() + 1;
            jDiaFin = jFechaFin.getDate();

            if (jMesFin < 10) {
                jMesFin = '0' + jMesFin;
            }
            if (jDiaFin < 10) {
                jDiaFin = '0' + jDiaFin;
            }
            jFechaActual = jAnoFin + '-' + jMesFin + '-' + jDiaFin;
            jFecha = $('#TxtFnacimiento').val();
            jEdad = RestarFechas(jFechaActual, jFecha);
            $('#TxtEdad').val(jEdad);
        }
    });



    function LimpiarForm() {
        $('#TxtNumIde2').val('');        $('#TxtApellido1').val('');        $('#TxtApellido2').val('');        $('#TxtNombre1').val('');
        $('#TxtNombre2').val('');        $('#CmbGenero').val('0');        $('#TxtFnacimiento').val('');        $('#TxtAaNac').val('');
        $('#TxtMmNac').val('');        $('#TxtDdNac').val('');        $('#TxtEdad').val('');        $('#CmbNacionalidad').val('');
        $('#TxtProcedencia').val('');        $('#CmbCiudadNac').val('');        $('#CmbCiudadRes').val('');        $('#TxtDireccion').val('');
        $('#TxtTelefono').val('');        $('#CmbTipoAfil').val('');        $('#CmbEstdoCivil').val('');        $('#CmbEtnia').val('');
        $('#CmbNivelEdu').val('');        $('#CmbOcupacion').val('');        $('#CmbCli').val('');        $('#TxtAutorización').val('');
        $('#TxtCodDx').val('');        $('#TxtNomDx').val('');        $('#CmbPrograma').val('');       
        $('#CmbFuente').val('');        $('#txtemail').val('');        $("#DivDatos").slideUp("1.0s");
    }

    function FechaNac() {
        var jFnac = $('#TxtAnoNac').val() + '-' + $('#TxtMesNac').val() + '-' + $('#TxtDiaNac').val();
        $('#TxtFnacimiento').val(jFnac);

        var jFechaHoy = new Date();
        var jAnoHoy = jFechaHoy.getFullYear();
        var jMesHoy = jFechaHoy.getMonth() + 1;
        var jDiaHoy = jFechaHoy.getDate();
        if (jMesHoy < 10) {
            jMesHoy = '0' + jMesHoy;
        }
        if (jDiaHoy < 10) {
            jDiaHoy = '0' + jDiaHoy;
        }
        var jFechaHoy1 = jAnoHoy + '-' + jMesHoy + '-' + jDiaHoy;
        var jEdad = RestarFechas(jFechaHoy1, jFnac);
        $('#TxtEdad').val(jEdad);
    }
    
    
    function consultaMedicoshoras() {
    $.ajax({
        cache: false,
        url: 'FrmAgendarCitas.php',
        type: 'POST',
        dataType: 'json',
        data: 'jFuncionPhp=BuscarDisponibles&' + $("#frmagenda").serialize(),
        success: function (datos) {
            if (datos !== '') {
                console.log(datos);
                var filas = "";
                var horas = "";
                if ($("#CmbTipoC").val() !== '000') {
                    $('#itemsrowsmodal').html("");

                    $.each(datos, function (index, contenido) {
                        if (contenido.aAgendaDisponible !== null || contenido.aAgendaDisponible !== undefined) {
                            $.each(contenido.aAgendaDisponible, function (da, info) {
                                filas += '<div class="m-pricing-table-1__item col-lg-3"><span class="m-pricing-table-1__price"> ' + contenido.Nombre + ' </span><span class="m-pricing-table-1__description" style="font-size: 14px;">Fecha:' + info.fecagenda + '</span>';
                                $.each(contenido.aAgendaDisponible[da].horario, function (dat, infos) {
                                    filas += '<span class="m-pricing-table-1__description" style="font-size: 14px;"><input type="radio" class="seleccionahora" name="hora[]" id="hora" data-toggle="modal" data-target="#myModal" data-value="' + infos.horaasginada + '" data-fecha="' + info.fecagenda + '" data-idagenda="' + info.idAgenda + '" data-idEsp="' + contenido.idEspecialidad + '" data-idMed="' + contenido.CodigoMedico + '">' + infos.horaasginada + '</span>';
                                });
                                filas += '</div>';
                            });
                        }
                    });
                    $('#itemsrowsmodal').append(filas);
                }
                else if ($("#CmbTipoC").val() === '000' || $("#CmbEspe").val() !== 36 || $("#CmbEspe").val() !== 24) {
                    $('#itemsrowsmodal').html("");
                    $.each(datos, function (index, contenido) {
                        if (contenido.aAgendaDisponible !== null || contenido.aAgendaDisponible !== undefined) {
                            $.each(contenido.aAgendaDisponible, function (da, info) {
                                filas += '<div class="m-pricing-table-1__item col-lg-3"><span class="m-pricing-table-1__price"> ' + contenido.Nombre + ' </span><span class="m-pricing-table-1__description" style="font-size: 14px;">Fecha:' + info.fecagenda + '</span>';
                                $.each(contenido.aAgendaDisponible[da].horario, function (dat, infos) {
                                    filas += '<span class="m-pricing-table-1__description" style="font-size: 14px;"><input type="radio" class="seleccionahora" name="hora[]" id="hora" data-toggle="modal" data-target="#myModal" data-value="' + infos.horaasginada + '" data-fecha="' + info.fecagenda + '" data-idagenda="' + info.idAgenda + '" data-idEsp="' + contenido.idEspecialidad + '" data-idMed="' + contenido.CodigoMedico + '">' + infos.horaasginada + '</span>';
                                });
                                filas += '</div>';
                            });
                        }
                    });
                    $('#itemsrowsmodal').append(filas);
                }
            }
        }
    }).error(function (error) {
        alert('Ocurrio un error en el Servidor');
    });
}
});

$(document).on('click', '.cancela ', function () {
    $('#idagendacan').val($(this).data("id")); // obtengo el id de la agenda
    $('#Divcanpte *').css({'font-family': 'Barlow'});
    $('#Divcanpte').dialog({
        title: 'Cancelar Agenda',
        modal: true,
        height: 200,
        width: 300,
        buttons: [
            {
                text: "Cancelar",
                click: function () {
                    $(this).dialog('close');
                }
            },
            {
                text: "Registrar",
                click: function () {
                    $(this).dialog('close');
                    $('#CmbMotCan').val('');
                    cancela_agenda();
                }
            }
        ]
    });
});

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

function consultaMedicos() {
    $.ajax({
        cache: false,
        url: 'FrmAgendarCitas.php',
        type: 'POST',
        dataType: 'json',
        data: 'jFuncionPhp=BuscarDisponibles&' + $("#frmagenda").serialize(),
        success: function (datos) {
            if (datos !== '') {
                console.log(datos);
                var filas = "";
                var horas = "";
                if ($("#CmbTipoC").val() !== '000') {
                    $('#itemsrows').html("");

                    $.each(datos, function (index, contenido) {
                        if (contenido.aAgendaDisponible !== null || contenido.aAgendaDisponible !== undefined) {
                            $.each(contenido.aAgendaDisponible, function (da, info) {
                                filas += '<div class="m-pricing-table-1__item col-lg-3"><span class="m-pricing-table-1__price"> ' + contenido.Nombre + ' </span><span class="m-pricing-table-1__description" style="font-size: 14px;">Fecha:' + info.fecagenda + '</span>';
                                $.each(contenido.aAgendaDisponible[da].horario, function (dat, infos) {
                                    filas += '<span class="m-pricing-table-1__description" style="font-size: 14px;"><input type="radio" class="selecciona" name="hora[]" id="hora" data-toggle="modal" data-target="#myModal" data-value="' + infos.horaasginada + '" data-fecha="' + info.fecagenda + '" data-idagenda="' + info.idAgenda + '" data-idEsp="' + contenido.idEspecialidad + '" data-idMed="' + contenido.CodigoMedico + '">' + infos.horaasginada + '</span>';
                                });
                                filas += '</div>';
                            });
                        }
                    });
                    $('#itemsrows').append(filas);
                }
//                if ($("#CmbTipoC").val() === '000' || $("#CmbEspe").val() !== 36 || $("#CmbEspe").val() !== 24) {
//                    $.each(datos, function (index, contenido) {
//                        if (contenido.aAgendaDisponible !== null || contenido.aAgendaDisponible !== undefined) {
//                            $.each(contenido.aAgendaDisponible, function (da, info) {
//                                filas += '<div class="m-pricing-table-1__item col-lg-3"><span class="m-pricing-table-1__price"> ' + contenido.Nombre + ' </span><span class="m-pricing-table-1__description" style="font-size: 14px;">Fecha:' + info.fecagenda + '</span>';
//                                $.each(contenido.aAgendaDisponible[da].horario, function (dat, infos) {
//                                    filas += '<span class="m-pricing-table-1__description" style="font-size: 14px;"><input type="radio" class="selecciona" name="hora[]" id="hora" data-toggle="modal" data-target="#myModal" data-value="' + infos.horaasginada + '" data-fecha="' + info.fecagenda + '" data-idagenda="' + info.idAgenda + '" data-idEsp="' + contenido.idEspecialidad + '" data-idMed="' + contenido.CodigoMedico + '">' + infos.horaasginada + '</span>';
//                                });
//                                filas += '</div>';
//                            });
//                        }
//                    });
//                    $('#itemsrows').append(filas);
//                }
//                $('#TabCitas tbody tr').remove();
//                $('#txtidagenda').val(datos[0].idagenda);
//                var hoy = new Date();
//                // var hora =hoy.getMonth() +  '/' + hoy.getDay()  + '/'  + hoy.getFullYear() + ' ' + hoy.getHours() + ':' + hoy.getMinutes()  ;
//
//                var today = new Date();
//                var dd = today.getDate();
//                var mm = today.getMonth() + 1; //January is 0!
//                var yyyy = today.getFullYear();
//
//                if (dd < 10) {
//                    dd = '0' + dd;
//                }
//
//                if (mm < 10) {
//                    mm = '0' + mm;
//                }
//                var today = mm + '/' + dd + '/' + yyyy + ' ' + hoy.getHours() + ':' + hoy.getMinutes() + ':00';
//                $.each(datos, function (index, contenido) {
//                    jFila = '<tr>';
//                    //if (contenido.estado === 1) {
//                    var fecha = $("#TxtFechaIni").val();
//                    var reemplazo = fecha.split('-');
//                    var horaInicio = (reemplazo[1] + '/' + reemplazo[2] + '/' + reemplazo[0] + ' ' + contenido.horaasginada);
//                    var then = horaInicio;
//                    var diff = moment.duration(moment(then).diff(moment(today)));
//                    if (diff._data.days >= 1 && contenido.estado === "1") {
//                        jFila += '<td class=centro><img src="../Imagenes/delete.png" data-id="' + contenido.idageasi + '"  width="18px" heigth="18px"  class="cancela"> </td>';
//                    } else if (contenido.estado === '2') {
//                        jFila += '<td class=centro><img src="../Imagenes/ok.png"  data-id="' + contenido.idencrip + '"  ></td>';
//                    } else if (contenido.estado === '') {
//                        jFila += '<td class=centro><img src="../Imagenes/edit.png" data-id="' + contenido.horaasginada + '" class="selecciona"  data-toggle="modal" data-target="#myModal" ></td>';
//                    }else if (diff._data.days == 0 && contenido.estado === "1") {
//                        jFila += '<td class=centro>... </td>';
//                    } 
//                    jFila += '<td class=centro>' + contenido.horaasginada + '</td>';
//                    jFila += '<td class=centro>' + contenido.identificacion + '</td>';
//                    jFila += '<td class=centro>' + contenido.nombre + '</td>';
//                    jFila += '<td class=centro>' + contenido.entidad + '</td>';
//                    jFila += '</tr>';
//                    $('#TabCitas').append(jFila);
//                });
            }
//            else {
//                $('#TabCitas tbody tr').remove();
//                alertas({
//                    sTitle: "Agenda",
//                    sHtml: "El usuario no tiene una agenda creada",
//                    oTipo: "warning"
//                });
//                return false;
//            }
        }
    }).error(function (error) {
        alert('Ocurrio un error en el Servidor');
    });
}



function consultaagenda() {
    $.ajax({
        cache: false,
        url: 'FrmAgendarCitas.php',
        type: 'POST',
        dataType: 'json',
        data: 'jFuncionPhp=BuscarAgenda&' + $("#frmagenda").serialize(),
        success: function (datos) {
            if (datos !== '') {
                $('#TabCitas tbody tr').remove();
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
                var today = mm + '/' + dd + '/' + yyyy + ' ' + hoy.getHours() + ':' + hoy.getMinutes() + ':00';
                $.each(datos, function (index, contenido) {
                    jFila = '<tr>';
                    //if (contenido.estado === 1) {
                    var fecha = $("#TxtFechaIni").val();
                    var reemplazo = fecha.split('-');
                    var horaInicio = (reemplazo[1] + '/' + reemplazo[2] + '/' + reemplazo[0] + ' ' + contenido.horaasginada);
                    var then = horaInicio;
                    var diff = moment.duration(moment(then).diff(moment(today)));
                    if (diff._data.days >= 1 && contenido.estado === "1") {
                        jFila += '<td class=centro><img src="../Imagenes/delete.png" data-id="' + contenido.idageasi + '"  width="18px" heigth="18px"  class="cancela"> </td>';
                    } else if (contenido.estado === '2') {
                        jFila += '<td class=centro><img src="../Imagenes/ok.png"  data-id="' + contenido.idencrip + '"  ></td>';
                    } else if (contenido.estado === '') {
                        jFila += '<td class=centro><img src="../Imagenes/edit.png" data-id="' + contenido.horaasginada + '" class="selecciona"  data-toggle="modal" data-target="#myModal" ></td>';
                    } else if (diff._data.days == 0 && contenido.estado === "1") {
                        jFila += '<td class=centro>... </td>';
                    }
                    jFila += '<td class=centro>' + contenido.horaasginada + '</td>';
                    jFila += '<td class=centro>' + contenido.identificacion + '</td>';
                    jFila += '<td class=centro>' + contenido.nombre + '</td>';
                    jFila += '<td class=centro>' + contenido.entidad + '</td>';
                    jFila += '</tr>';
                    $('#TabCitas').append(jFila);
                });
            } else {
                $('#TabCitas tbody tr').remove();
                alertas({
                    sTitle: "Agenda",
                    sHtml: "El usuario no tiene una agenda creada",
                    oTipo: "warning"
                });
                return false;
            }
        }
    }).error(function (error) {
        alert('Ocurrio un error en el Servidor');
    });

}

function cancela_agenda() {
    var idagendacan = $('#idagendacan').val();
    var CmbMotCan = $('#CmbMotCan').val();
    $.ajax({
        cache: false,
        url: 'FrmAgendarCitas.php',
        type: 'POST',
        dataType: 'json',
        data: 'jFuncionPhp=cancela_agenda&idagendacan=' + idagendacan + "&CmbMotCan=" + CmbMotCan,
        success: function (datos) {
            if (datos.Error2 == 'si') {
                $('#TabCitas tbody tr').remove();
                consultaagenda();
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





















