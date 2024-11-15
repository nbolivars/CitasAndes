<?php

header('Content-type: application/json');



session_start();
$tiempo = "";

function ConexionMysql() {
    include('../Conexion/conexion.php');
    return $xConexion;
}

function ConexionVisual() {
    include('../Conexion/conexionvisual.php');
    return $xConexionVisual;
}

$xFuncionPhp = $_REQUEST['jFuncionPhp'];
$xFuncionPhp();

function get_search_record() {
    $xConexion = ConexionMysql();

    $sqlespe = "SELECT Nombre FROM gen_especialidad where estado='A' ORDER BY Nombre";
    $cmdespe = mysqli_query($xConexion, $sqlPerfil);
    $tabespe = array();
    while ($rowespe = mysqli_fetch_assoc($cmdespe)) {
        $tabespe[] = $rowespe;
    }

    mysqli_close($xConexion);

    header('Content-type: application/json');

    echo json_encode($tabespe);
}

function BuscarAgenda() {
    $existe = 0;
    $xConexion = ConexionMysql();
    $txtidmed = $_POST['CmbMedicos'];
    $TxtFechaIni = $_POST['TxtFechaIni'];
    $CmbEspe = $_POST['CmbEspe'];

    $sqlespe = "SELECT idAgenda ,  idMed ,  idesp ,  fecagenda ,  frecagenda ,  horini ,  horfin ,  estadoagenda ,  dateagenda  "
            . " FROM citaagenda where idMed='$txtidmed' and fecagenda='$TxtFechaIni' and idesp='$CmbEspe' and estadoagenda=1";
    $cmdespe = mysqli_query($xConexion, $sqlespe);
    $tabespe = array();
    $aAgenda = array();
    $datos = '';
    while ($rowespe = mysqli_fetch_assoc($cmdespe)) {
        $existe = 1;
        $tabespe[] = array(
            'idAgenda' => $rowespe["idAgenda"],
            'idesp' => $rowespe["idesp"],
            'idMed' => $rowespe["idMed"],
            'fecagenda' => $rowespe["fecagenda"],
            'frecagenda' => $rowespe["frecagenda"],
            'horini' => $rowespe["horini"],
            'horfin' => $rowespe["horfin"],
            'horini' => $rowespe["horini"]
        );
    }
    if ($existe === 1) {
        $idAgenda = $tabespe[0]['idAgenda'];
        $hi = $tabespe[0]['horini'];
        $hf = $tabespe[0]['horfin'];
        $fr = $tabespe[0]['frecagenda'];

//obtengo el rango de horas de atencion
        $datos = intervaloHora($hi, $hf, $fr);

        for ($h = 0; $h < count($datos); $h++) {
            $sql = "SELECT ca.idageasi, ca.idMed,	ca.idEsp, ca.idPte, ca.fechaasignada, ca.horaasginada, ca.idagenda, ca.idusercre, ca.estadocita, ca.fechaasignacita, "
                    . "gp.Id, gp.TipoDocumento, gp.NumDocumento, gp.Apellido1, gp.Apellido2, gp.Nombre1, gp.Nombre2 , gc.Nombre"
                    . " FROM cit_agenda_asigna as ca"
                    . " inner join gen_pacientes as gp on ca.idPte=gp.Id"
                    . " inner join gen_clientes as gc on gc.Codigo=ca.idEntidad"
                    . " where ca.idMed='$txtidmed' and ca.idesp='$CmbEspe' and ca.fechaasignada='$TxtFechaIni' and ca.horaasginada='$datos[$h]'";

            $cmdespe = mysqli_query($xConexion, $sql);
            $rowcount = mysqli_num_rows($cmdespe);
            //echo $rowcount;
            if ($rowcount > 0) {
                while ($rowespe = mysqli_fetch_assoc($cmdespe)) {
                    $aAgenda[] = array(
                        'idageasi' => $rowespe["idageasi"],
                        'idMed' => $rowespe["idMed"],
                        'idEsp' => $rowespe["idEsp"],
                        'idPte' => $rowespe["idPte"],
                        'identificacion' => $rowespe["TipoDocumento"] . ' ' . $rowespe["NumDocumento"],
                        'nombre' => $rowespe["Nombre1"] . ' ' . $rowespe["Nombre2"] . ' ' . $rowespe["Apellido1"] . ' ' . $rowespe["Apellido2"],
                        'entidad' => $rowespe["Nombre"],
                        'fechaasignada' => $rowespe["fechaasignada"],
                        'horaasginada' => $rowespe["horaasginada"],
                        'idagenda' => $rowespe["idagenda"],
                        'estado' => $rowespe['estadocita']
                    );
                }
            } else {
                $aAgenda[] = array(
                    'idageasi' => '',
                    'idMed' => '',
                    'idEsp' => '',
                    'idPte' => '',
                    'identificacion' => '',
                    'nombre' => '',
                    'entidad' => '',
                    'fechaasignada' => $TxtFechaIni,
                    'horaasginada' => $datos[$h],
                    'idagenda' => $idAgenda,
                    'estado' => ''
                );
            }
        }
    }
    $mostrar = count($aAgenda) == 0 ? $datos : $aAgenda;

    mysqli_close($xConexion);

    echo json_encode($mostrar);
}

function intervaloHora($hora_inicio, $hora_fin, $intervalo) {

    $hora_inicio = new DateTime($hora_inicio);
    $hora_fin = new DateTime($hora_fin);
    //$hora_fin->modify('+1 second'); // Añadimos 1 segundo para que nos muestre $hora_fin
    // Si la hora de inicio es superior a la hora fin
    // añadimos un día más a la hora fin
    if ($hora_inicio > $hora_fin) {

        $hora_fin->modify('+1 day');
    }

    // Establecemos el intervalo en minutos        
    $intervalo = new DateInterval('PT' . $intervalo . 'M');

    // Sacamos los periodos entre las horas
    $periodo = new DatePeriod($hora_inicio, $intervalo, $hora_fin);
    foreach ($periodo as $hora) {
        // Guardamos las horas intervalos 
        $horas[] = $hora->format('H:i:s');
    }

    return $horas;
}

function BuscarDoc() {
    $xExiste = 'N';
    $tiene = 'no';
    $tieneHora = 'no';
    $datoDocumento = array();

    $xConexion = ConexionMysql();
    $xTipoDoc = $_POST["CmbTipoDoc"];
    $xNumDoc = $_POST["TxtNdoc"];
    $txtfecha = $_POST["txtfecha"];
    $txthoraini = $_POST["txthoraini"];
    $aCita = array();
    $sqlDocumento = "SELECT * FROM gen_pacientes WHERE TipoDocumento = '$xTipoDoc' and NumDocumento = '$xNumDoc'";
    $cmdDocumento = mysqli_query($xConexion, $sqlDocumento);
    $recDocumento = mysqli_num_rows($cmdDocumento);

    if ($recDocumento > 0) {
        $xExiste = 'S';
        $rowDocumento = mysqli_fetch_assoc($cmdDocumento);
        $datoDocumento[] = $rowDocumento;
        $xIdPaciente = $rowDocumento['Id'];

        $sqlDocumento = "SELECT  idMed, idEsp, fechaasignada, horaasginada, estadocita "
                . " FROM cit_agenda_asigna  "
                . " WHERE idPte = '$xIdPaciente' and fechaasignada='$txtfecha' and estadocita=1";
        $cmdDocumento = mysqli_query($xConexion, $sqlDocumento);
        $recDocumento = mysqli_num_rows($cmdDocumento);

        if ($recDocumento > 0) {
            $tiene = "si";
            $tieneHora = "si";
            $rowDocumento = mysqli_fetch_assoc($cmdDocumento);
            $tieneHora = $rowDocumento['horaasginada'] === $txthoraini ? 'si' : 'no';
            $aCita[] = $rowDocumento;
        }
    }

    mysqli_close($xConexion);
    echo json_encode(array($datoDocumento, 'Existe' => $xExiste, 'tiene' => $tiene, 'cita' => $aCita, 'tieneHora' => $tieneHora));
}

function DatosPte() {
    $xError6 = "";
    $tablaClientes = array();
    $xDato = array();
    $tablaMunicipios = array();
    $tablaEtnia = array();
    $tablaEducacion = array();
    $tablaPaises = array();
    $tablaOcupaciones = array();
    $tablaEntidades = array();
    $tablaTipoDocumento = array();
    $tablaFrmPago = array();
    $xFechaArchivo = "";

    $xConexion = ConexionMysql();

    ///////////////////////////
    //// Tipo De Docuemnto ////
    ///////////////////////////

    $sqlTipoDo = "SELECT TipoCodigo,TipoDescripcion
            FROM   gen_tipodocumento
            WHERE  EstadO = 'A'";

    $cmdTipoD = mysqli_query($xConexion, $sqlTipoDo);
    $aTipoDoc = array();
    while ($rowTipoD = mysqli_fetch_assoc($cmdTipoD)) {
        $aTipoDoc[] = array(
            "TipoCodigo" => $rowTipoD['TipoCodigo'],
            "TipoDescripcion" => $rowTipoD['TipoDescripcion']
        );
    }

    ////////////////////
    /// Tipo Usuario ///
    ////////////////////

    $sqlTipoUsu = "SELECT CodTipoUsurio,NomtipoUsuario
                 FROM gen_tipo_usuario";

    $cmdTipoUsu = mysqli_query($xConexion, $sqlTipoUsu);
    $aTipoUsu = array();
    while ($rowTipoUsu = mysqli_fetch_assoc($cmdTipoUsu)) {
        $aTipoUsu[] = array(
            "CodTipoUsurio" => $rowTipoUsu['CodTipoUsurio'],
            "NomtipoUsuario" => $rowTipoUsu['NomtipoUsuario']
        );
    }

    /////////////////////
    /// Causa externa ///
    /////////////////////

    $sqlCausaExt = "SELECT Codigo,Descripcion
                  FROM his_causaexterna
                  ORDER BY Descripcion ASC";

    $cmdCausaExt = mysqli_query($xConexion, $sqlCausaExt);
    $aCausaExt = array();
    while ($rowCausaExt = mysqli_fetch_assoc($cmdCausaExt)) {
        $aCausaExt[] = array(
            "Codigo" => $rowCausaExt['Codigo'],
            "Descripcion" => $rowCausaExt['Descripcion']
        );
    }

    $sqlClientes = "SELECT '000' AS Codigo, ' [ ... ]' as Nombre union 
                        SELECT Codigo, Nombre 
                        FROM gen_clientes order by Nombre";
    $cmdClientes = mysqli_query($xConexion, $sqlClientes);
    $tablaClientes = array();
    while ($rowClientes = mysqli_fetch_assoc($cmdClientes)) {
        $tablaClientes[] = $rowClientes;
    }

    $sqlHoy = "SELECT now() as hoy";
    $cmdHoy = mysqli_query($xConexion, $sqlHoy);
    $rowHoy = mysqli_fetch_assoc($cmdHoy);
    $xAnoSys = substr($rowHoy["hoy"], 0, 4);


    // Control //
    $sqlControl = "SELECT * FROM gen_controlsalario WHERE Periodo = '$xAnoSys'";
    $cmdControl = mysqli_query($xConexion, $sqlControl);
    $rowControl = mysqli_fetch_assoc($cmdControl);

    $xSalario = $rowControl["SalarioMinimo"];

    $xDato = array('Fsys' => substr($rowHoy["hoy"], 0, 10), 'Salario' => $xSalario);

    ////////////////
    // Municipios //
    ////////////////

    $sqlMunicipios = "SELECT CodigoMunicipio, NombreMunicipio from gen_municipios order by nombremunicipio";
    $cmdMunicipios = mysqli_query($xConexion, $sqlMunicipios);
    $tablaMunicipios = array();
    while ($rowMunicipios = mysqli_fetch_assoc($cmdMunicipios)) {
        $tablaMunicipios[] = $rowMunicipios;
    }

    ////////////
    // Etnias //
    ////////////

    $sqlEtnia = "SELECT Codigo, Nombre FROM gen_etnias ORDER BY Nombre";
    $cmdEtnia = mysqli_query($xConexion, $sqlEtnia);
    $tablaEtnia = array();
    while ($rowEtnia = mysqli_fetch_assoc($cmdEtnia)) {
        $tablaEtnia[] = $rowEtnia;
    }

    /////////////////////
    // Nivel Educativo //
    /////////////////////

    $sqlEducacion = "SELECT Codigo, Nombre FROM gen_niveleducativo ORDER BY Nombre";
    $cmdEducacion = mysqli_query($xConexion, $sqlEducacion);
    $tablaEducacion = array();
    while ($rowEducacion = mysqli_fetch_assoc($cmdEducacion)) {
        $tablaEducacion[] = $rowEducacion;
    }

    //////////////////
    // Nacionalidad //
    //////////////////

    $sqlPaises = "SELECT Codigo, Nombre FROM gen_paises ORDER BY Id";
    $cmdPaises = mysqli_query($xConexion, $sqlPaises);
    $tablaPaises = array();
    while ($rowPaises = mysqli_fetch_assoc($cmdPaises)) {
        $tablaPaises[] = $rowPaises;
    }

    /////////////////
    // Ocupaciones //
    /////////////////

    $sqlOcupaciones = "SELECT Codigo, Nombre FROM gen_ocupaciones ORDER BY Nombre";
    $cmdOcupaciones = mysqli_query($xConexion, $sqlOcupaciones);
    $tablaOcupaciones = array();
    while ($rowOcupaciones = mysqli_fetch_assoc($cmdOcupaciones)) {
        $tablaOcupaciones[] = $rowOcupaciones;
    }

    ///////////////
    // Entidades //
    ///////////////

    $sqlEntidades = "SELECT Codigo, Nombre FROM gen_clientes WHERE Estado = 'A' ORDER BY Nombre";
    $cmdEntidades = mysqli_query($xConexion, $sqlEntidades);
    $tablaEntidades = array();
    while ($rowEntidades = mysqli_fetch_assoc($cmdEntidades)) {
        $tablaEntidades[] = $rowEntidades;
    }



    $sqlTipoC = "SELECT gpc.idproci, gpc.idcups, gpc.estado, gp.Descripcion "
            . " FROM gen_procedimientos_citas as gpc  "
            . " inner join gen_procedimientos as gp on gpc.idcups=gp.id"; //gen_procedimientos_citas
    $cmdTipoC = mysqli_query($xConexion, $sqlTipoC);
    $tablaTipoC = array();
    while ($rowTipoC = mysqli_fetch_assoc($cmdTipoC)) {
        $tablaTipoC[] = array(
            "idTipoC" => $rowTipoC['idcups'],
            "nomTipoC" => $rowTipoC['Descripcion']
        );
    }

    $sqlconf = "SELECT idgen, nomConf FROM gen_configuracion where destino=2 ORDER BY idgen";
    $cmdTipoC = mysqli_query($xConexion, $sqlconf);
    $tablaTipoConf = array();
    while ($rowTipoC = mysqli_fetch_assoc($cmdTipoC)) {
        $tablaTipoConf[] = $rowTipoC;
    }

    $sqlconf = "SELECT idgen, nomConf FROM gen_configuracion where destino='motcan' ORDER BY idgen";
    $cmdTipoC = mysqli_query($xConexion, $sqlconf);
    $aMotCan = array();
    while ($rowTipoC = mysqli_fetch_assoc($cmdTipoC)) {
        $aMotCan[] = array(
            "idgen" => $rowTipoC['idgen'],
            "nomConf" => $rowTipoC['nomConf']
        );
    }

    $sqlesp = "SELECT gus.Nombre, gus.Id "
            . " FROM gen_especialidad as gus ";
    // echo $sqlDias;
    $cmdDias = mysqli_query($xConexion, $sqlesp);
    $gen_especialidad = array();
    while ($rowTipoC = mysqli_fetch_assoc($cmdDias)) {
        $gen_especialidad[] = array(
            "Nombre" => $rowTipoC['Nombre'],
            "Id" => $rowTipoC['Id']
        );
    }

    $sql = "SELECT idgen, nomConf FROM gen_configuracion where destino='tipoafi' ORDER BY idgen";
    $cmdTipoC = mysqli_query($xConexion, $sqlconf);
    $aTipoA = array();
    while ($rowTipoC = mysqli_fetch_assoc($cmdTipoC)) {
        $aTipoA[] = array(
            "idgen" => $rowTipoC['idgen'],
            "nomConf" => $rowTipoC['nomConf']
        );
    }


    $sqlconf = "SELECT idgen, nomConf FROM gen_configuracion where destino='nivel' ORDER BY idgen";
    $cmdTipoC = mysqli_query($xConexion, $sqlconf);
    $aNivel = array();
    while ($rowTipoC = mysqli_fetch_assoc($cmdTipoC)) {
        $aNivel[] = array(
            "idgen" => $rowTipoC['idgen'],
            "nomConf" => $rowTipoC['nomConf']
        );
    }


    mysqli_close($xConexion);
    $xError6 = array('Error6' => $xError6);
    header('Content-type: application/json');
    echo json_encode(array($tablaClientes,
        $xDato,
        $tablaMunicipios,
        $tablaEtnia,
        $tablaEducacion,
        $tablaPaises,
        $tablaOcupaciones,
        $tablaEntidades,
        $tablaFrmPago,
        $tablaTipoC,
        $tablaTipoConf,
        $aMotCan,
        $gen_especialidad,
        $aTipoA,
        $aNivel,
        array('FileDate' => $xFechaArchivo),
        $aTipoDoc,
        $aTipoUsu,
        $aCausaExt));
}

function BuscaDx() {
    $xConexion = ConexionMysql();
    $xNomDx = $_POST['jNomDx'];

    $sqlDx = "SELECT Nombre, Codigo
           FROM gen_cie10 WHERE Nombre LIKE '%$xNomDx%'";
    $cmdDx = mysqli_query($xConexion, $sqlDx);
    $datosDx = array();
    while ($row = mysqli_fetch_assoc($cmdDx)) {
        $datosDx[] = array(
            'Nombre' => $row['Nombre'],
            'Codigo' => $row['Codigo']);
    }
    header('Content-type: application/json');
    echo json_encode($datosDx);
    mysqli_close($xConexion);
}

////////////////////////
// Busca Codigo de Dx //
////////////////////////
function BuscaCodigoDx() {
    $xConexion = ConexionMysql();

    $xCodigoDx = $_POST["jCodDx"];
    $sqlBuscar = "SELECT Nombre FROM gen_cie10 WHERE Codigo = '$xCodigoDx'";
    $cmdBuscar = mysqli_query($xConexion, $sqlBuscar);
    $regBuscar = mysqli_num_rows($cmdBuscar);

    if ($regBuscar == 0) {
        $datoExiste = array('Existe' => 'NO');
    } else {
        $datoExiste = array('Existe' => 'SI');
    }
    $rowBuscar = mysqli_fetch_assoc($cmdBuscar);
    $datoDx = array('Nombre' => $rowBuscar["Nombre"]);

    mysqli_close($xConexion);

    header('Content-type: application/json');
    echo json_encode(array($datoExiste, $datoDx));
}

function RegistarInfo() {
    if (!isset($_COOKIE["xUsuarioReg"])) {
        $xUsuarioReg = 'NoUser';
        $xPcReg = 'NoPc';
    } else {
        $xUsuarioReg = $_COOKIE["xUsuarioReg"];
        $xPcReg = $_COOKIE["xPcReg"];
    }

    $xConexion = ConexionMysql();
    $user = isset($_COOKIE["xUsuarioReg"]) === '' ? 'Sistemas' : isset($_COOKIE["xUsuarioReg"]);
    //$xUsuarioReg = $user;
    $xPcReg = $_COOKIE["xPcReg"];

    $DatosPaciente = json_decode($_POST["datos"]);
    $jTipoDoc = $DatosPaciente->jTipoDoc;
    $jNumIde = $DatosPaciente->jNumIde;
    $jApellido1 = $DatosPaciente->jApellido1;
    $jApellido2 = $DatosPaciente->jApellido2;
    $jNombre1 = $DatosPaciente->jNombre1;
    $jNombre2 = $DatosPaciente->jNombre2;
    $jGenero = $DatosPaciente->jGenero;
    $jFechaNac = $DatosPaciente->jFechaNac;
    $jNacionalidad = $DatosPaciente->jNacionalidad;
    $jProcedencia = $DatosPaciente->jProcedencia;
    $jCiudadNac = $DatosPaciente->jCiudadNac;
    $jCiudadRes = $DatosPaciente->jCiudadRes;
    $jTelefono = $DatosPaciente->jTelefono;
    $jDireccion = $DatosPaciente->jDireccion;
    $jTipoUsuario = $DatosPaciente->jTipoUsuario;
    $jEstadoCivil = $DatosPaciente->jEstadoCivil;
    $jEtnia = $DatosPaciente->jEtnia;
    $jNivelEdu = $DatosPaciente->jNivelEdu;
    $jOcupacion = $DatosPaciente->jOcupacion;
    $jEntidad = $DatosPaciente->jEntidad;
    $TxtAutorización = $DatosPaciente->TxtAutorización;
    $TxtCodDx = $DatosPaciente->TxtCodDx;
    $txtemail = $DatosPaciente->txtemail;
    $CmbNivel = $DatosPaciente->CmbNivel;
    $xFechaPaciente = $DatosPaciente->jFechaPaciente;

    $status = $_POST['status'];
    $agenda = $_POST['agenda'];
    $txtidmed = $_POST['txtidmed'];
    $txtidespmed = $_POST['txtidespmed'];
    $txtfecha = $_POST['txtfecha'];
    $txthoraini = $_POST['txthoraini'];
    $CmbTipoC = $_POST['CmbTipoC'];
    $CmbFuente = $_POST['CmbFuente'];
    $CmbPrograma = $_POST['CmbPrograma'];
    $CmbCausaExt = $_POST['CmbCausaExt'];
    $EstadoEmbarazo = $_POST['jEstadoEmbarazo'];
    
    //$txtcantidad = $_POST['txtcantidad'];
    //  print_r($_POST);
//exit;
    if ($status === 'N') {
        $sqlInsertPcte = "INSERT INTO gen_pacientes SET
            TipoDocumento    = '$jTipoDoc', 
            NumDocumento     = $jNumIde,
            Apellido1        = '$jApellido1',
            Apellido2        = '$jApellido2',
            Nombre1          = '$jNombre1',
            Nombre2          = '$jNombre2',
            Genero           = '$jGenero',
            FechaNacimiento  = '$jFechaNac',
            Nacionalidad     = '$jNacionalidad',
            Procedencia      = '$jProcedencia',
            LugarNacimiento  = '$jCiudadNac',
            CiudadResidencia = '$jCiudadRes',
            Direccion        = '$jDireccion',
            Telefono         = '$jTelefono',
            TipoAfiliado     = '$jTipoUsuario',
            EstadoCivil      = '$jEstadoCivil',
            NivelEducativo   = '$jNivelEdu',
            Ocupacion        = '$jOcupacion',
            Etnia            = '$jEtnia',
            CodigoCliente    = '$jEntidad',
            CodigoEps        = '$jEntidad',
            Email            = '$txtemail',
            idnivelsal       = '$CmbNivel'";
        
        mysqli_query($xConexion, $sqlInsertPcte);
        //$xIdPaciente = mysqli_insert_id($xConexion);

        $sqlPaciente = "SELECT Id FROM gen_pacientes WHERE TipoDocumento = '$jTipoDoc' and NumDocumento = '$jNumIde'";
        $cmdPaciente = mysqli_query($xConexion, $sqlPaciente);
        $rowPaciente = mysqli_fetch_assoc($cmdPaciente);
        $xIdPaciente = $rowPaciente["Id"];
        $xError2 = mysqli_error($xConexion);
    } elseif ($status === 'S') {
        $sqlPaciente = "SELECT Id FROM gen_pacientes WHERE TipoDocumento = '$jTipoDoc' and NumDocumento = '$jNumIde'";
        $cmdPaciente = mysqli_query($xConexion, $sqlPaciente);
        $rowPaciente = mysqli_fetch_assoc($cmdPaciente);
        $xIdPaciente = $rowPaciente["Id"];
        
        $sqlInsertPcte = "UPDATE  gen_pacientes SET 
            TipoDocumento    = '$jTipoDoc',
            NumDocumento     = $jNumIde,
            Apellido1        = '$jApellido1',
            Apellido2        = '$jApellido2',
            Nombre1          = '$jNombre1',
            Nombre2          = '$jNombre2',
            Genero           = '$jGenero',
            FechaNacimiento  = '$jFechaNac',
            Nacionalidad     = '$jNacionalidad',
            Procedencia      = '$jProcedencia',
            LugarNacimiento  = '$jCiudadNac',
            CiudadResidencia = '$jCiudadRes',
            Direccion        = '$jDireccion',
            Telefono         = '$jTelefono',
            TipoAfiliado     = '$jTipoUsuario',
            EstadoCivil      = '$jEstadoCivil',
            NivelEducativo   = '$jNivelEdu',
            Ocupacion        = '$jOcupacion',
            Etnia            = '$jEtnia',
            CodigoCliente    = '$jEntidad',
            Email            = '$txtemail',
            CodigoEps        = '$jEntidad', 
            idnivelsal       = '$CmbNivel' 
            WHERE id='$xIdPaciente'";

        mysqli_query($xConexion, $sqlInsertPcte);
        $xError2 = mysqli_error($xConexion);
    }
    if ($CmbTipoC !== '000') {
        
        $sql = "INSERT INTO cit_agenda_asigna SET
            idMed           = '$txtidmed',
            idEsp           = '$txtidespmed',
            idPte           = '$xIdPaciente',
            fechaasignada   = '$txtfecha',
            horaasginada    = '$txthoraini',
            FechaSugerida   = '$xFechaPaciente',
            idagenda        = '$agenda',
            idusercre       = '$xUsuarioReg',
            estadocita      = 1,
            idEntidad       = '$jEntidad',
            nroautoizacion  = '$TxtAutorización',
            dxcita          = '$TxtCodDx',
            TipoC           = '$CmbTipoC',
            idfuente        = '$CmbFuente',
            idplan          = '$CmbPrograma',
            id_tipo         = 1,
            CausaExterna    = '$CmbCausaExt',
            UsuarioRegistra = '$xUsuarioReg',
            SnEmbarazo      = '$EstadoEmbarazo'";
        
        mysqli_query($xConexion, $sql);
    } else if ($CmbTipoC === '000') {
        $xDatosDx = json_decode($_REQUEST["jDatosServiciosFact"]);
        if (count($xDatosDx) > 0) {
            $xFecha = date('Y-m-d H:i:s');
            $lock = "lock tables citaagendafactura as citaagendafactura write";
            mysqli_query($xConexion, $lock);

            $sqlFecha = "insert into citaagendafactura (idPte, fecha, idMed, idEsp)"
                    . " values($xIdPaciente, '$xFecha', $txtidmed, $txtidespmed)";
            $cmdFecha = mysqli_query($xConexion, $sqlFecha);
            // echo $sqlFecha;
            $uLock = "unlock tables";
            mysqli_query($xConexion, $uLock);

            $sqlFecha = "SELECT idcitaFac
                FROM citaagendafactura
                where idPte= $xIdPaciente and  fecha='$xFecha' and  idMed=$txtidmed and  idEsp=$txtidespmed ";
            $cmdFecha = mysqli_query($xConexion, $sqlFecha);
            $rowFecha = mysqli_fetch_assoc($cmdFecha);
            $idcitaFac = $rowFecha["idcitaFac"];
            //echo $sqlFecha;
            //print_r($xDatosDx);

            foreach ($xDatosDx as $datoDx) {
                $jCodigo = $datoDx->jCodigo;
                $jCantidad = $datoDx->jCantidad;
                if ($jCodigo != '') {
                    $sqlDx = "INSERT INTO citaagendafacturadet SET
                        idcitaagenda   =  $idcitaFac,
                        idcodigo     =  $jCodigo,"
                            . "canti	 =$jCantidad";
                    //  echo $sqlDx;
                    mysqli_query($xConexion, $sqlDx);
                }
            }
        }
        $xDatosDx = json_decode($_REQUEST["jDatosServicios"]);
        if (count($xDatosDx) > 0) {
            foreach ($xDatosDx as $datoDx) {
                $jFecha = $datoDx->jFecha;
                $jHora = $datoDx->jHora;
                $jAgenda = $datoDx->jAgenda;
                $jCodigo = $datoDx->jCodigo;

                if ($TxtCodDx != '') {
                    $sql = "INSERT INTO cit_agenda_asigna SET
                        idMed          = '$txtidmed',
                        idEsp          = '$txtidespmed',
                        idPte          = '$xIdPaciente',
                        fechaasignada  = '$jFecha',
                        horaasginada   = '$jHora',
                        idagenda       = '$jAgenda',
                        idusercre      = '$xUsuarioReg',
                        estadocita     = 1,
                        idEntidad      = '$jEntidad',
                        nroautoizacion = '$TxtAutorización',
                        dxcita         = '$TxtCodDx',
                        TipoC          = '$CmbTipoC',
                        idfuente       = '$CmbFuente',
                        idplan         = '$CmbPrograma',
                        id_tipo        = 3,
                        UsuarioRegistra = '$xUsuarioReg'";
                    mysqli_query($xConexion, $sql);
                    // echo $sql;
                }
            }
        }
    }
    $xError2 = mysqli_error($xConexion);
    if ($xError2 === '') {
        //  envioemail($txtemail,$jNombre1, $jNombre2, $jTipoDoc, $jNumIde);
    }
    mysqli_close($xConexion);
    $generado = $xError2 == '' ? "si" : "no";
    echo json_encode(array('Error2' => $generado));
}

function envioemail($txtemail, $jNombre1, $jNombre2, $jTipoDoc, $jNumIde) {
    include_once('../../phpMailer/class.phpmailer.php');
    include_once('../../phpMailer/language/phpmailer.lang-es.php');

    $mail = new PHPMailer();
    $mail->SetLanguage('es', '../../phpmailer/language/');
    $mail->IsSMTP();                                   // send via SMTP
    $mail->Host = "smtp.gmail.com"; // SMTP servers
    $mail->SMTPAuth = true;     // turn on SMTP authentication
    $mail->Username = "mauriciopatinosuare@gmail.com";  // SMTP username
    $mail->Password = "p3dr0jul102"; // SMTP password
    $mail->From = "contactos@housekeeper365.com";
    $mail->FromName = "HouseKeeper 365";
    $mail->Port = 587; // TCP port to connect to
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->SMTPDebug = 4;
    $mail->AddCC("mauriciopatinosuarez@gmail.com"); // Tambi�n podemos enviar con copia de carb�n

    $mail->Subject = "Confirmacion | HOUSEKEEPER365 "; //Asunto del mensaje
    $mail->AddAddress($txtemail, $jNombre1);
    $mail->AddReplyTo("contactos@housekeeper365.com", "Test");

    $mail->WordWrap = 10000;                              // set word wrap
    $mail->IsHTML(true);                               // send as HTML
    $mail->Body = "hjjhj";
    $mail->AltBody = "This is the text-only body";
    if (!$mail->Send()) {
        echo "Message was not sent <p>";
        echo "Mailer Error: " . $mail->ErrorInfo;
        exit;
    }
    $alistReserva = array("ok");
    echo json_encode($alistReserva);


//    $mail = new PHPMailer;
//
//    $mail->IsSMTP();                                      // Set mailer to use SMTP
//    $mail->Host = 'smtp.mandrillapp.com';                 // Specify main and backup server
//    $mail->Port = 587;                                    // Set the SMTP port
//    $mail->SMTPAuth = true;                               // Enable SMTP authentication
//    $mail->Username = 'sistemas@clinicadelosandes.com';                // SMTP username
//    $mail->Password = '12032008Andes';                  // SMTP password
//    $mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
//
//    $mail->From = 'sistemas@clinicadelosandes.com';
//    $mail->FromName = 'Asignacion de Citas';
//    $mail->AddAddress($txtemail, $jNombre1 ." ".$jNombre2);  // Add a recipient
//    //$mail->AddAddress('ellen@example.com');               // Name is optional
//
//    $mail->IsHTML(true);                                  // Set email format to HTML
//
//    $mail->Subject = 'Asignacion de Citas - no Responder este Email -';
//     $htmlMensaje.= "<table width='560' border='0' align='center' cellpadding='0' cellspacing='0'>
//                <tr>
//                <td height='15' align='center'>
//                <div align='center'>
//                <span >
//                <br /> 
//                <br /> 
//                ================================
//                Estimado (a) <strong>". $jNombre1 ." ".$jNombre2."</strong>  
//                ================================
//                <br>
//                 Muchas gracias por utilizar nuestros servicios a través de Internet.
//
//                </span>
//                <br>
//                <p style='text-align: justify; margin-bottom: 0px'>                      
//                Le informamos que la cita fué asignada exitosamente a:
//". $jNombre1 ." ".$jNombre2." (".$jTipoDoc ." ".$jNumIde.")
//
//El número de cita es 23303567.
//IPS: Corporacion Ips Boyaca - Nieves - 3796
//Dirección: Cll. 26 No 9-02 - Tunja
//Procedimiento: Consulta Medicina General
//Día: Miércoles 6 de Marzo de 2019
//Hora: 18:40:00 
//Profesional: Sara Lucia Diaz Bernal
//
//Recuerde:
//
// -      Asistir con su documento de identificación.
// -      Llegar con 15 minutos de anticipación a la IPS.
// -      Cancelar el valor de la cuota moderadora.
//
//Si le es imposible asistir a la cita ya solicitada, la debe cancelar  por lo menos con 12 horas de anticipación.
//Nota: 
// - Si la cita es para un menor de edad, recuerde que el mismo debe asistir acompañado de un adulto.
//
//Cordialmente,
//
//EPS EN LINEA
//Medimas EPS S.A.S.
//
//Nota: Por favor no responder a este correo electrónico ya que es generado de manera automática con el único propósito de enviar notificaciones en las transacciones realizadas.
//                </p>
//                
//                
//                <br/>
//                <br/>                                                                                                                
//                
//                
//                </div>
//                </td>
//                </tr>
//                </table>";    
//    $mail->Body    = $htmlMensaje;
//    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
//
//    if(!$mail->Send()) {
//       echo 'Message could not be sent.';
//       echo 'Mailer Error: ' . $mail->ErrorInfo;
//       exit;
//    }
//
//    return "si";
}

function consultadisponibles() {
    $xConexion = ConexionMysql();

    $user = $_COOKIE["xUsuarioReg"] === '' ? 'Jbolanosp' : $_COOKIE["xUsuarioReg"];
    $xUsuarioReg = $user;
    $xPcReg = $_COOKIE["xPcReg"];
    $agenda = $_POST['agenda'];
    $txtidmed = $_POST['txtidmed'];
    $txtidespmed = $_POST['txtidespmed'];
    $txtfecha = $_POST['txtfecha'];
    $txthoraini = $_POST['txthoraini'];
    $TxtCanti = $_POST['TxtCanti'];
    $idcodigo = $_POST['idcodigo'];
    $CmbTipoC = $_POST['CmbTipoC'];


    $aAgendas = array();
    //echo $CmbTipoC;
    if ($CmbTipoC === '000') {
        if ($TxtCanti > 1) {
            $sqlagendas = "select idAgenda , idMed, idesp, fecagenda, frecagenda"
                    . " from citaagenda "
                    . " where idMed='$txtidmed' and idesp='$txtidespmed' and fecagenda >= '$txtfecha' and estadoagenda=1 limit $TxtCanti";
            //      echo $sqlagendas;
            $cmdDocumento = mysqli_query($xConexion, $sqlagendas);
            $recDocumento = mysqli_num_rows($cmdDocumento);

            if ($recDocumento > 0) {
                while ($rowTipoC = mysqli_fetch_assoc($cmdDocumento)) {
                    $aAgendas[] = array(
                        "idAgenda" => $rowTipoC['idAgenda'],
                        "idMed" => $rowTipoC['idMed'],
                        "idesp" => $rowTipoC['idesp'],
                        "fecagenda" => $rowTipoC['fecagenda'],
                        "frecagenda" => $rowTipoC['frecagenda']
                    );
                }
            }
            $aAgendanueva = array();
            $bandera = false;
            //datosagenda($aAgendas,$txthoraini);
            for ($i = 0; $i < count($aAgendas); $i++) {

                $idagenda = $aAgendas[$i]['idAgenda'];
                $idMed = $aAgendas[$i]['idMed'];
                $idesp = $aAgendas[$i]['idesp'];
                $fecagenda = $aAgendas[$i]['fecagenda'];
                $frecagenda = $aAgendas[$i]['frecagenda'];

                $consultadia = "select idagenda "
                        . " from cit_agenda_asigna "
                        . " where idagenda='$idagenda' and horaasginada='$txthoraini' and fechaasignada='$fecagenda'";
                $cmdDocumento = mysqli_query($xConexion, $consultadia);
                //  echo $consultadia;
                $recDocumento = mysqli_num_rows($cmdDocumento);
                if ($recDocumento == 0) {
                    $aAgendanueva[] = array(
                        "agenda" => $idagenda,
                        "idMed" => $idMed,
                        "idesp" => $idesp,
                        "fecagenda" => $fecagenda,
                        "hora" => $txthoraini
                    );
                } else {
                    $bandera = true;
                    if ($bandera == true) {
                        //  echo "asdf";
                        while ($bandera == true) {

                            $date2 = new DateTime($txthoraini);
                            $value1 = $txthoraini;
                            $value2 = "00:" . $frecagenda;

                            $arr1 = explode(':', $value1);
                            $arr2 = explode(':', $value2);

                            $totalMinutes = (int) $arr1[0] * 60 + (int) $arr1[1] + (int) $arr2[0] * 60 + (int) $arr2[1];

                            $hours = (int) ($totalMinutes / 60);
                            $minutes = $totalMinutes % 60; // Modulus: remainder when dividing with 60

                            $date2 = $hours . ':' . $minutes;

                            //  $date2->modify('+'.$frecagenda.'minute');

                            $consultadia = "select idagenda "
                                    . " from cit_agenda_asigna "
                                    . " where idagenda='$idagenda' and horaasginada='$date2' and fechaasignada='$fecagenda'";
                            // echo $consultadia;
                            $cmdDocumento = mysqli_query($xConexion, $consultadia);
                            $recDocumento = mysqli_num_rows($cmdDocumento);
                            if ($recDocumento == 0) {
                                $bandera = false;
                                $aAgendanueva[] = array(
                                    "agenda" => $idagenda,
                                    "idMed" => $idMed,
                                    "idesp" => $idesp,
                                    "fecagenda" => $fecagenda,
                                    "hora" => $date2
                                );
                            }
                        }
                    }
                }
            }
        }
//print_r($aAgendanueva);
    }
    echo json_encode(array('Error2' => $aAgendanueva));
}

function datosagenda() {
    
}

function cancela_agenda() {
    $xConexion = ConexionMysql();
    $user = $_COOKIE["xUsuarioReg"] === '' ? 'Jbolanosp' : $_COOKIE["xUsuarioReg"];
    $xUsuarioReg = $user;
    $idagenda = $_POST['idagendacan'];
    $motivo = $_POST['CmbMotCan'];
    $sql = "update cit_agenda_asigna set estadocita=4, idmotcan='$motivo', idusercan='$xUsuarioReg' where idageasi=$idagenda";
    $cmdDias = mysqli_query($xConexion, $sql);
    $xError2 = mysqli_error($xConexion);
    $generado = $xError2 == '' ? "si" : "no";
    echo json_encode(array('Error2' => $generado));
}

function BuscaDescripcion() {
    $xConn = ConexionMysql();
    $xDescripcion = $_POST['jDescripcion'];

    $SqlDescripcion = "SELECT Descripcion, Codigo, TarifaClinica, TarifaSoat, Iva, TipoProc, TipoServicio, 
       CodigoSoat, CodigoCups
           FROM gen_procedimientos WHERE Descripcion LIKE '%$xDescripcion%' and Tipo='P' and Estado='A'";
    $CmdDescripcion = mysqli_query($xConn, $SqlDescripcion);
    $DatosArticulos = array();
    while ($row = mysqli_fetch_assoc($CmdDescripcion)) {
        $DatosArticulos[] = array(
            'Descripcion' => $row['Descripcion'],
            'Codigo' => $row['Codigo'],
            'Valor' => $row["TarifaSoat"],
            'Iva' => $row["Iva"],
            'TipoProc' => $row["TipoProc"],
            'TipoServicio' => $row["TipoServicio"],
            'CodigoSoat' => $row["CodigoSoat"],
            'CodigoCups' => $row["CodigoCups"]);
    }
    echo json_encode($DatosArticulos);
    mysqli_close($xConn);
}

function BuscaCodigo() {
    $xConn = ConexionMysql();
    $xDescripcion = $_POST['jDescripcion'];

    $SqlDescripcion = "SELECT Descripcion, Codigo, TarifaClinica, TarifaSoat, Iva, TipoProc, TipoServicio, 
       CodigoSoat, CodigoCups
           FROM gen_procedimientos WHERE CodigoCups LIKE '%$xDescripcion%' and Tipo='P' and Estado='A'";
    $CmdDescripcion = mysqli_query($xConn, $SqlDescripcion);
    $DatosArticulos = array();
    while ($row = mysqli_fetch_assoc($CmdDescripcion)) {
        $DatosArticulos[] = array(
            'Descripcion' => $row['Descripcion'],
            'Codigo' => $row['Codigo'],
            'Valor' => $row["TarifaSoat"],
            'Iva' => $row["Iva"],
            'TipoProc' => $row["TipoProc"],
            'TipoServicio' => $row["TipoServicio"],
            'CodigoSoat' => $row["CodigoSoat"],
            'CodigoCups' => $row["CodigoCups"]);
    }
    echo json_encode($DatosArticulos);
    mysqli_close($xConn);
}

function BuscaEspecialidad() {
    $aMed = array();
    $xConexion = ConexionMysql();
    // TIPODB = ''; 
    $xMedico = $_REQUEST["jMedico"];

    $sqlDias = "SELECT gus.Nombre, gus.Id "
            . " FROM gen_especialidad as gus "
            . " inner join gen_medicos_esp_new as ge on ge.idEspecialidad=gus.Id "
            . " WHERE ge.CodigoMedico = '$xMedico'";

    // echo $sqlDias;
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)) {
        $aMed[] = $rowDias;
    }

    mysqli_close($xConexion);

    //  header('Content-type: application/json');
    echo json_encode($aMed);
}

function BuscaTipo() {
    $aMed = array();
    $xConexion = ConexionMysql();
    // TIPODB = ''; 
    $xMedico = $_POST["jMedico"];

    $sqlDias = "SELECT gp.id, gp.Codigo, gp.Descripcion, gp.Especialidad"
            . " FROM gen_procedimientos as gp "
            . " WHERE gp.idEspe = '$xMedico'";

    // echo $sqlDias;
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)) {
        $aMed[] = $rowDias;
    }

    mysqli_close($xConexion);

    //  header('Content-type: application/json');
    echo json_encode($aMed);
}

function BuscarDisponibles() {
//    $aMed = array();
//    $xConexion = ConexionMysql();
//    //print_r($_POST);
//    $CmbEspe=$_POST['CmbEspe'];
//    $xFecha = date('Y-m-d');
//    $fechaInicio = strtotime($xFecha);
//    $fechaFin = strtotime ( '+30 day' , strtotime ( $xFecha ) ) ;; // strtotime("2019-05-29");
//
//    //Recorro las fechas y con la función strotime obtengo los lunes
//    $sqlEsp="select ge.idesp, ge.idMed, ge.iddispo, ge.idAgenda, ge.frecagenda, gcd.iddia, gd.dayenglish, gcd.horini, gcd.horfin"
//            . " from  citaagenda as ge"
//            . " inner join gen_consultorios_dispo as gcd on gcd.iddispo =ge.iddispo"
//            . " inner join gen_dias as gd on gcd.iddia =gd.iddia"
//            . " where  ge.idesp='$CmbEspe'";
//  
//    $aEsp = array();
//    $cmdDias = mysqli_query($xConexion, $sqlEsp);
//    while ($rowDias = mysqli_fetch_assoc($cmdDias)){
//        $aEsp[] = array(
//            "iddispo"=>$rowDias['iddispo'],
//            "idEspecialidad"=>$rowDias['idesp'],
//            "idAgenda"=>$rowDias['idAgenda'],
//            "frecagenda"=>$rowDias['frecagenda'],
//            "dayenglish"=>$rowDias['dayenglish'],
//            "CodigoMedico"=>$rowDias['idMed'],
//            "horini"=>$rowDias['horini'],
//            "horfin"=>$rowDias['horfin'],
//        );
//    }
//    
//    $aDispo=array();
//    foreach ($aEsp as $key => $value) {
//        $dia=$value['dayenglish'];
//       // echo $dia;
//        for ($i = $fechaInicio; $i <= $fechaFin; $i += 86400 * 7){
//            $fechadis= date("Y-m-d", strtotime($dia.' this week', $i));
//            $aDispo[] = array(
//                "iddispo"=>$value['iddispo'],
//                "idEspecialidad"=>$value['idEspecialidad'],
//                "idAgenda"=>$value['idAgenda'],
//                "frecagenda"=>$value['frecagenda'],
//                "dayenglish"=>$value['dayenglish'],
//                "CodigoMedico"=>$value['CodigoMedico'],
//                "fechadis"=>$fechadis,
//                "horini"=>$value['horini'],
//                "horfin"=>$value['horfin']
//            );
//        }
//    }
//
//   print_r($aDispo);
//    
//    //exit;
//    
//    
////    $sqlDias="select ge.idEspecialidad, ge.CodigoMedico, gu.Nombre1, gu.Apellido1"
////            . " from  gen_medicos_esp_new as ge"
////            . " inner join gen_usuarios as gu on gu.id=ge.CodigoMedico "
////            . " where  ge.idEspecialidad='$CmbEspe'";
////
////    $cmdDias = mysqli_query($xConexion, $sqlDias);
////    while ($rowDias = mysqli_fetch_assoc($cmdDias)){
////        $aMed[] = array(
////            "idEspecialidad"=>$rowDias['idEspecialidad'],
////            "CodigoMedico"=>$rowDias['CodigoMedico'],
////            "Nombre"=>$rowDias['Nombre1']." ".$rowDias['Apellido1'],
////        );
////    }
//    
//    foreach ($aDispo as $key => $value) {
//        $idEspecialidad=$value['idEspecialidad'];
//        $CodigoMedico=$value['CodigoMedico'];
//        $fechadis=$value['fechadis'];
//        $frecagenda=$value['frecagenda'];
//        $horini=$value['horini'];
//        $horfin=$value['horfin'];
//        $aAgenda=consultarAgenda($CodigoMedico, $idEspecialidad, $fechadis, $frecagenda, $horini, $horfin);
//        $aDispo[$key]['aAgendaDisponible']=$aAgenda;
//    }
//
//    print_r($aDispo);
//    mysqli_close($xConexion);
//    echo json_encode($aMed);   


    $aMed = array();
    $xConexion = ConexionMysql();
    //print_r($_POST);

    $xFecha = date('Y-m-d');
    //$fechaInicio = strtotime("2016-08-01");
    //$fechaFin = strtotime("2016-08-20");
    //Recorro las fechas y con la función strotime obtengo los lunes
    //for ($i = $fechaInicio; $i <= $fechaFin; $i += 86400 * 7){
    //    echo date("Y-m-d", strtotime('monday this week', $i)).'<br>';
    //}
    $CmbEspe = $_POST['CmbEspe'];

    $sqlDias = "select ge.idEspecialidad, ge.CodigoMedico, gu.Nombre1, gu.Apellido1"
            . " from  gen_medicos_esp_new as ge"
            . " inner join gen_usuarios as gu on gu.id=ge.CodigoMedico "
            . " where  ge.idEspecialidad='$CmbEspe'";

    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)) {
        $aMed[] = array(
            "idEspecialidad" => $rowDias['idEspecialidad'],
            "CodigoMedico" => $rowDias['CodigoMedico'],
            "Nombre" => $rowDias['Nombre1'] . " " . $rowDias['Apellido1'],
        );
    }
    // print_r($aMed);
    // exit;
    foreach ($aMed as $key => $value) {
        $idEspecialidad = $value['idEspecialidad'];
        $CodigoMedico = $value['CodigoMedico'];
        $aAgenda = consultarAgenda($CodigoMedico, $idEspecialidad);
        $aMed[$key]['aAgendaDisponible'] = $aAgenda;
    }

    mysqli_close($xConexion);
    echo json_encode($aMed);
}

function consultarAgenda($CodigoMedico, $idEspecialidad) {//, $fechadis, $frecagenda, $horini, $horfin){
//    $xConexion = ConexionMysql();
//    $xFecha = $fechadis;
//    $sqlDias="select  ci.fecagenda, ci.frecagenda,  ci.horini, ci.horfin, ci.idAgenda, ci.idMed, ci.idesp "
//        . " from  citaagenda as ci"
//        . " where  ci.estadoagenda=1 and ci.idesp='$idEspecialidad' and ci.idMed='$CodigoMedico' and ci.fecagenda <'$xFecha' ";
//   // echo $sqlDias;
//    $aDisp = array();
//    $cmdDias = mysqli_query($xConexion, $sqlDias);
//    while ($rowDias = mysqli_fetch_assoc($cmdDias)){
//        $aDisp[] = array(
//            "idAgenda"=>$rowDias['idAgenda'],
//            "fecagenda"=>$rowDias['fecagenda'],
//            "frecagenda"=>$rowDias['frecagenda'],
//            "horini"=>$rowDias['horini'],
//            "horfin"=>$rowDias['horfin'],
//            "idMed"=>$rowDias['idMed'],
//            "idesp"=>$rowDias['idesp'],
//        );
//    }
//    
//    foreach($aDisp as $key => $datos){
//        $hi= $aDisp[$key]['horini'];
//        $hf= $aDisp[$key]['horfin'];
//        $fr= $aDisp[$key]['frecagenda'];
//        $idMed= $aDisp[$key]['idMed'];
//        $idesp= $aDisp[$key]['idesp'];
//        $fecagenda= $aDisp[$key]['fecagenda'];
//        $datos = intervaloHora($hi, $hf, $fr);
//        $horas=consultarxHora($datos, $idMed, $idesp, $fecagenda);
//        $aDisp[$key]['horario']=$horas;
//    }
//    
//    mysqli_close($xConexion);
//    return $aDisp;
    $xConexion = ConexionMysql();
    $xFecha = date('Y-m-d');
    $sqlDias = "select  ci.fecagenda, ci.frecagenda,  ci.horini, ci.horfin, ci.idAgenda, ci.idMed, ci.idesp 
        FROM  citaagenda as ci
        WHERE  ci.estadoagenda=1 and ci.idesp='$idEspecialidad' and ci.idMed='$CodigoMedico' and ci.fecagenda >= '$xFecha' 
        ORDER BY ci.fecagenda limit 30";
    //echo $sqlDias;
    $aDisponi = array();
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)) {
        $aDisponi[] = array(
            "idAgenda" => $rowDias['idAgenda'],
            "fecagenda" => $rowDias['fecagenda'],
            "frecagenda" => $rowDias['frecagenda'],
            "horini" => $rowDias['horini'],
            "horfin" => $rowDias['horfin'],
            "idMed" => $rowDias['idMed'],
            "idesp" => $rowDias['idesp'],
        );
    }


    //  exit;
    foreach ($aDisponi as $key => $datos) {
        $hi = $aDisponi[$key]['horini'];
        $hf = $aDisponi[$key]['horfin'];
        $fr = $aDisponi[$key]['frecagenda'];
        $idMed = $aDisponi[$key]['idMed'];
        $idesp = $aDisponi[$key]['idesp'];
        $fecagenda = $aDisponi[$key]['fecagenda'];
        $idAgenda = $aDisponi[$key]['idAgenda'];
        $datos = intervaloHora($hi, $hf, $fr);
        $horas = consultarxHora($datos, $idMed, $idesp, $fecagenda, $idAgenda);
        $aDisponi[$key]['horario'] = $horas;
    }
    // print_r($aDisponi);
    mysqli_close($xConexion);
    return $aDisponi;
}

function consultarDias($idagenda) {
    $xConexion = ConexionMysql();
    $xFecha = date('Y-m-d');
    $sqlDias = "select ci.idPte, ci.horaasginada, ci.idageasi "
            . " from  cit_agenda_asigna as ci"
            . " where  ci.idagenda='$idagenda' and ci.estadocita=1";

    $aDisp = array();
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)) {
        $aDisp[] = array(
            "idageasi" => $rowDias['idageasi'],
            "horaasginada" => $rowDias['horaasginada'],
            "idPte" => $rowDias['idPte']
        );
    }
    //print_r($aDisp);
    mysqli_close($xConexion);
    return $aDisp;
}

function consultarxHora($datos, $idMed, $idesp, $fecagenda, $idAgenda) {
    $xConexion = ConexionMysql();
    $xFecha = date('Y-m-d');
    $aAgenda = array();
    for ($h = 0; $h < count($datos); $h++) {
        $sql = "SELECT ca.idageasi, ca.idMed,	ca.idEsp, ca.idPte, ca.fechaasignada, ca.horaasginada, ca.idagenda, ca.idusercre, ca.estadocita, ca.fechaasignacita, "
                . "gp.Id, gp.TipoDocumento, gp.NumDocumento, gp.Apellido1, gp.Apellido2, gp.Nombre1, gp.Nombre2 , gc.Nombre"
                . " FROM cit_agenda_asigna as ca"
                . " inner join gen_pacientes as gp on ca.idPte=gp.Id"
                . " inner join gen_clientes as gc on gc.Codigo=ca.idEntidad"
                . " where ca.idMed='$idMed' and ca.idesp='$idesp' and ca.fechaasignada='$fecagenda' and ca.horaasginada='$datos[$h]' and ca.estadocita=1";
        //echo $sql;
        $cmdespe = mysqli_query($xConexion, $sql);
        $rowcount = mysqli_num_rows($cmdespe);
        //echo $rowcount;
        if ($rowcount > 0) {
            while ($rowDias = mysqli_fetch_assoc($cmdespe)) {
                if ($rowDias['estadocita'] === '4') {
                    $aAgenda[] = array(
                        'idageasi' => '',
                        'idMed' => '',
                        'idEsp' => '',
                        'idPte' => '',
                        'identificacion' => '',
                        'nombre' => '',
                        'entidad' => '',
                        'fechaasignada' => $fecagenda,
                        'horaasginada' => $datos[$h],
                        'idagenda' => $idAgenda,
                        'estado' => ''
                    );
                }
            }
        } else {
            $aAgenda[] = array(
                'idageasi' => '',
                'idMed' => '',
                'idEsp' => '',
                'idPte' => '',
                'identificacion' => '',
                'nombre' => '',
                'entidad' => '',
                'fechaasignada' => $fecagenda,
                'horaasginada' => $datos[$h],
                'idagenda' => $idAgenda,
                'estado' => ''
            );
        }
    }
    mysqli_close($xConexion);
    return $aAgenda;
}



















