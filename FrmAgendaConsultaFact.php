<?php
    header('Content-type: application/json');
    require '../functions/funciones.php';

session_start();

function ConexionMysql(){
    include('../Conexion/conexion.php');
    return $xConexion;
}

function ConexionVisual(){
    include('../Conexion/conexionvisual.php');
    return $xConexionVisual;
}

$xFuncionPhp = $_REQUEST['jFuncionPhp'];

switch ($xFuncionPhp){
    case 'BuscarAgenda':
        BuscarAgenda();
        break;
    
    case 'BuscarDoc':
        BuscarDoc();
        break;
    
    case 'DatosPte':
        DatosPte();
        break;
    
    case 'BuscaDx':
        BuscaDx();
        break;
    
    case 'BuscaCodigoDx':
        BuscaCodigoDx();
        break;
    
    case 'RegistarInfo':
        RegistarInfo();
        break;
            
    case 'cancela_agenda':
        cancela_agenda();
        break;
    
}

function get_search_record(){
    $xConexion = ConexionMysql();

    $sqlespe = "SELECT Nombre FROM gen_especialidad where estado='A' ORDER BY Nombre";
    $cmdespe = mysqli_query($xConexion, $sqlPerfil);
    $tabespe = array();
    while ($rowespe = mysqli_fetch_assoc($cmdespe))
    {
        $tabespe[] = $rowespe;
    }
    
    mysqli_close($xConexion);
    
    header('Content-type: application/json');
    
    echo json_encode($tabespe);
}

function BuscarAgenda(){
   
    $xConexion = ConexionMysql();
   // $txtidmed = $_POST['CmbMedicos'];
    //$TxtFechaIni = $_POST['TxtFechaIni'];
    //$CmbEspe = $_POST['CmbEspe'];
    $tipodoc = $_POST['tipodoc'];
    $ndoc = $_POST['ndoc'];
    $xFecha = date("Y-m-d");
    
    
    $sql = "SELECT gp.Id, gp.TipoDocumento, gp.NumDocumento, gp.Apellido1, gp.Apellido2, gp.Nombre1, gp.Nombre2 "
        . " FROM  gen_pacientes as gp "
        . " where gp.TipoDocumento='$tipodoc' and gp.NumDocumento='$ndoc'";
    $cmdespe = mysqli_query($xConexion, $sql);
    $recDocumento = mysqli_num_rows($cmdespe);
    $aAgenda = array();
    if ($recDocumento > 0) {
        $rowDocumento = mysqli_fetch_assoc($cmdespe);
        $xIdPaciente = $rowDocumento['Id'];
        $sql = "SELECT ca.idageasi, ca.idMed, ca.idEsp, ca.idPte, ca.fechaasignada, ca.horaasginada, ca.idagenda, ca.idusercre, ca.estadocita, ca.fechaasignacita,"
            . " gu.Apellido1, gu.Apellido2, gu.Nombre1, gu.Nombre2, ge.Nombre as nomespe "
        . " FROM cit_agenda_asigna as ca"
        . " inner join gen_usuarios as gu on gu.Id=ca.idMed"
        . " inner join gen_especialidad as ge on ge.Id=ca.idEsp"
        . " where ca.idPte ='$xIdPaciente' and ca.fechaasignada='$xFecha' and ca.estadocita in (1)";
    
        //echo $sql;
        //$sqlespe = "SELECT idAgenda ,  idMed ,  idesp ,  fecagenda ,  frecagenda ,  horini ,  horfin ,  estadoagenda ,  dateagenda  "
        //        . " FROM citaagenda where idMed='$txtidmed' and fecagenda='$TxtFechaIni' and idesp='$CmbEspe' and estadoagenda=1";
        $cmdespe = mysqli_query($xConexion, $sql);
        
        while ($rowespe = mysqli_fetch_assoc($cmdespe)){
            $aAgenda[] = array(
                'idageasi'=> $rowespe["idageasi"],
                'idesp'=> $rowespe["idEsp"],
                'idMed'=> $rowespe["idMed"],
                'fechaasignada'=> $rowespe["fechaasignada"],
                'horaasginada'=> $rowespe["horaasginada"],
                'idagenda'=> $rowespe["idagenda"],
                'fechaasignacita'=> $rowespe["fechaasignacita"],
                'nommed'=> $rowespe["Nombre1"]." ".$rowespe["Nombre2"]." ". $rowespe["Apellido1"]." ".$rowespe["Apellido2"],
                'nomespe'=> $rowespe["nomespe"],
                'idencrip'=> funciones::aes128_encode( $rowespe["idageasi"]),
                'estado'=> $rowespe['estadocita'],
                'idagenda'=> $rowespe["idagenda"],
                );
        }
    }
    
    
    
    
//    $sqlespe = "SELECT idAgenda ,  idMed ,  idesp ,  fecagenda ,  frecagenda ,  horini ,  horfin ,  estadoagenda ,  dateagenda  "
//            . " FROM citaagenda where idMed='$txtidmed' and fecagenda='$TxtFechaIni' and idesp='$CmbEspe' and estadoagenda=1";
//    $cmdespe = mysqli_query($xConexion, $sqlespe);
//    $tabespe = array();
//    while ($rowespe = mysqli_fetch_assoc($cmdespe)){
//        $tabespe[] = array(
//             'idAgenda'=> $rowespe["idAgenda"],
//             'idesp'=> $rowespe["idesp"],
//             'idMed'=> $rowespe["idMed"],
//             'fecagenda'=> $rowespe["fecagenda"],
//             'frecagenda'=> $rowespe["frecagenda"],
//             'horini'=> $rowespe["horini"],
//             'horfin'=> $rowespe["horfin"],
//             'horini'=> $rowespe["horini"]
//            );
//    }
//    
//    $idAgenda=$tabespe[0]['idAgenda'];
//    $hi=$tabespe[0]['horini'];
//    $hf=$tabespe[0]['horfin'];
//    $fr=$tabespe[0]['frecagenda'];
//    
////obtengo el rango de horas de atencion
//    $datos=intervaloHora( $hi, $hf, $fr ) ;
//    $aAgenda=array();
//    for ($h=0; $h<count($datos); $h++){
//        $sql = "SELECT ca.idageasi, ca.idMed,	ca.idEsp, ca.idPte, ca.fechaasignada, ca.horaasginada, ca.idagenda, ca.idusercre, ca.estadocita, ca.fechaasignacita, "
//                . "gp.Id, gp.TipoDocumento, gp.NumDocumento, gp.Apellido1, gp.Apellido2, gp.Nombre1, gp.Nombre2 , gc.Nombre"
//                . " FROM cit_agenda_asigna as ca"
//                . " inner join gen_pacientes as gp on ca.idPte=gp.Id"
//                . " inner join gen_clientes as gc on gc.Codigo=ca.idEntidad"
//                . " where ca.idMed='$txtidmed' and ca.idesp='$CmbEspe' and ca.fechaasignada='$TxtFechaIni' and ca.horaasginada='$datos[$h]' and ca.estadocita in (1,2)";
//        
//        $cmdespe = mysqli_query($xConexion, $sql);
//        $rowcount=mysqli_num_rows($cmdespe);
//        //echo $rowcount;
//        if ($rowcount>0){
//            while ($rowespe = mysqli_fetch_assoc($cmdespe)){
//                $aAgenda[] = array(
//                     'idageasi'=> $rowespe["idageasi"],
//                     'idencrip'=> funciones::aes128_encode( $rowespe["idageasi"]),
//                     'idMed'=> $rowespe["idMed"],
//                     'idEsp'=> $rowespe["idEsp"],
//                     'idPte'=> $rowespe["idPte"],
//                     'identificacion'=> $rowespe["TipoDocumento"].' '.$rowespe["NumDocumento"],
//                     'nombre'=> $rowespe["Nombre1"].' '.$rowespe["Nombre2"].' '.$rowespe["Apellido1"].' '.$rowespe["Apellido2"],
//                     'entidad'=> $rowespe["Nombre"],
//                     'fechaasignada'=> $rowespe["fechaasignada"],
//                     'horaasginada'=> $rowespe["horaasginada"],
//                     'idagenda'=> $rowespe["idagenda"],
//                     'estado'=> $rowespe['estadocita']
//                );
//            }
//        }
//        else {
//            $aAgenda[] = array(
//                'idageasi'=> '',
//                'idencrip'=> '',
//                'idMed'=> '',
//                'idEsp'=> '',
//                'idPte'=> '',
//                'identificacion'=>'',
//                'nombre'=> '',
//                'entidad'=> '',
//                'fechaasignada'=> $TxtFechaIni,
//                'horaasginada'=> $datos[$h],
//                'idagenda'=>$idAgenda,
//                'estado'=> ''
//           );
//        }
//    }
    //print_r($aAgenda);
    $mostrar=count($aAgenda)==0?'':$aAgenda;
    
    mysqli_close($xConexion);

    echo json_encode($mostrar);
}


function intervaloHora($hora_inicio, $hora_fin, $intervalo) {

    $hora_inicio = new DateTime( $hora_inicio );
    $hora_fin    = new DateTime( $hora_fin );
    //$hora_fin->modify('+1 second'); // Añadimos 1 segundo para que nos muestre $hora_fin

    // Si la hora de inicio es superior a la hora fin
    // añadimos un día más a la hora fin
    if ($hora_inicio > $hora_fin) {

        $hora_fin->modify('+1 day');
    }

    // Establecemos el intervalo en minutos        
    $intervalo = new DateInterval('PT'.$intervalo.'M');

    // Sacamos los periodos entre las horas
    $periodo   = new DatePeriod($hora_inicio, $intervalo, $hora_fin);        
    foreach( $periodo as $hora ) {
        // Guardamos las horas intervalos 
        $horas[] =  $hora->format('H:i:s');
    }

    return $horas;
}


function BuscarDoc(){
    $xExiste = 'N';
    $tiene='no';
    $datoDocumento = array();

    $xConexion = ConexionMysql();
    $xTipoDoc = $_POST["CmbTipoDoc"];
    $xNumDoc  = $_POST["TxtNdoc"];
    $txtfecha  = $_POST["txtfecha"];
    $aCita=array();
    $sqlDocumento = "SELECT * FROM gen_pacientes WHERE TipoDocumento = '$xTipoDoc' and NumDocumento = '$xNumDoc'";
    $cmdDocumento = mysqli_query($xConexion, $sqlDocumento);
    $recDocumento = mysqli_num_rows($cmdDocumento);
    
    if ($recDocumento > 0){
        $xExiste = 'S';
        $rowDocumento = mysqli_fetch_assoc($cmdDocumento);
        $datoDocumento[] = $rowDocumento;
        $xIdPaciente=$rowDocumento['Id'];
        
        $sqlDocumento = "SELECT  idMed, idEsp, fechaasignada, horaasginada, estadocita "
                . " FROM cit_agenda_asigna  "
                . " WHERE idPte = '$xIdPaciente' and fechaasignada='$txtfecha' and estadocita=1";
        $cmdDocumento = mysqli_query($xConexion, $sqlDocumento);
        $recDocumento = mysqli_num_rows($cmdDocumento);
    
        if ($recDocumento > 0){
            $tiene="si";
            $rowDocumento = mysqli_fetch_assoc($cmdDocumento);
            $aCita[] = $rowDocumento;
        }
    }
    
    mysqli_close($xConexion);
    echo json_encode(array($datoDocumento, 'Existe'=>$xExiste, 'tiene'=>$tiene, 'cita'=>$aCita));
}

function DatosPte(){
     $xError6         = "";
    $tablaClientes    = array();
    $xDato            = array();
    $tablaMunicipios  = array();
    $tablaEtnia       = array();
    $tablaEducacion   = array();
    $tablaPaises      = array();
    $tablaOcupaciones = array();
    $tablaEntidades   = array();
    $tablaFrmPago     = array();
    $xFechaArchivo    = "";
    
    $xConexion = ConexionMysql();
        $sqlClientes = "SELECT '000' AS Codigo, ' [ ... ]' as Nombre union 
                        SELECT Codigo, Nombre 
                        FROM gen_clientes order by Nombre";
        $cmdClientes = mysqli_query($xConexion, $sqlClientes);
        $tablaClientes = array();
        while ($rowClientes = mysqli_fetch_assoc($cmdClientes))
        {
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

        $xDato = array('Fsys' => substr($rowHoy["hoy"], 0,10), 'Salario' => $xSalario);
        ////////////////
        // Municipios //
        ////////////////
        $sqlMunicipios = "SELECT CodigoMunicipio, NombreMunicipio from gen_municipios order by nombremunicipio";
        $cmdMunicipios = mysqli_query($xConexion, $sqlMunicipios);
        $tablaMunicipios = array();
        while ($rowMunicipios = mysqli_fetch_assoc($cmdMunicipios))
        {
            $tablaMunicipios[] = $rowMunicipios;
        }

        ////////////
        // Etnias //
        ////////////
        $sqlEtnia = "SELECT Codigo, Nombre FROM gen_etnias ORDER BY Nombre";
        $cmdEtnia = mysqli_query($xConexion, $sqlEtnia);
        $tablaEtnia = array();
        while ($rowEtnia = mysqli_fetch_assoc($cmdEtnia))
        {
            $tablaEtnia[] = $rowEtnia;
        }

        /////////////////////
        // Nivel Educativo //
        /////////////////////

        $sqlEducacion = "SELECT Codigo, Nombre FROM gen_niveleducativo ORDER BY Nombre";
        $cmdEducacion = mysqli_query($xConexion, $sqlEducacion);
        $tablaEducacion = array();
        while ($rowEducacion = mysqli_fetch_assoc($cmdEducacion))
        {
            $tablaEducacion[] = $rowEducacion;
        }

        //////////////////
        // Nacionalidad //
        //////////////////
        $sqlPaises = "SELECT Codigo, Nombre FROM gen_paises ORDER BY Id";
        $cmdPaises = mysqli_query($xConexion, $sqlPaises);
        $tablaPaises = array();
        while ($rowPaises = mysqli_fetch_assoc($cmdPaises))
        {
            $tablaPaises[] = $rowPaises;
        }

        /////////////////
        // Ocupaciones //
        /////////////////
        $sqlOcupaciones = "SELECT Codigo, Nombre FROM gen_ocupaciones ORDER BY Nombre";
        $cmdOcupaciones = mysqli_query($xConexion, $sqlOcupaciones);
        $tablaOcupaciones = array();
        while ($rowOcupaciones = mysqli_fetch_assoc($cmdOcupaciones))
        {
            $tablaOcupaciones[] = $rowOcupaciones;
        }

        ///////////////
        // Entidades //
        ///////////////
        $sqlEntidades = "SELECT Codigo, Nombre FROM gen_clientes WHERE Estado = 'A' ORDER BY Nombre";
        $cmdEntidades = mysqli_query($xConexion, $sqlEntidades);
        $tablaEntidades = array();
        while ($rowEntidades = mysqli_fetch_assoc($cmdEntidades))
        {
            $tablaEntidades[] = $rowEntidades;
        }
        
        
        $sqlTipoC = "SELECT 	gpc.idproci, gpc.idcups, gpc.estado, gp.Descripcion "
                . " FROM gen_procedimientos_citas as gpc  "
                . " inner join gen_procedimientos as gp on gpc.idcups=gp.id";//gen_procedimientos_citas
        $cmdTipoC = mysqli_query($xConexion, $sqlTipoC);
        $tablaTipoC = array();
        while ($rowTipoC = mysqli_fetch_assoc($cmdTipoC))
        {
             $tablaTipoC[] = array(
                "idTipoC"=>$rowTipoC['idcups'],
                "nomTipoC"=>$rowTipoC['Descripcion']
            );
        }
        
        $sqlconf = "SELECT idgen, nomConf FROM gen_configuracion where destino=2 ORDER BY idgen";
        $cmdTipoC = mysqli_query($xConexion, $sqlconf);
        $tablaTipoConf = array();
        while ($rowTipoC = mysqli_fetch_assoc($cmdTipoC))
        {
            $tablaTipoConf[] = $rowTipoC;
        }
        
        $sqlconf = "SELECT idgen, nomConf FROM gen_configuracion where destino='motcan' ORDER BY idgen";
        $cmdTipoC = mysqli_query($xConexion, $sqlconf);
        $aMotCan = array();
        while ($rowTipoC = mysqli_fetch_assoc($cmdTipoC)){
            $aMotCan[] = array(
                "idgen"=>$rowTipoC['idgen'],
                "nomConf"=>$rowTipoC['nomConf']
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
        array('FileDate' => $xFechaArchivo)));
}


function BuscaDx(){
   $xConexion  = ConexionMysql();
   $xNomDx     = $_POST['jNomDx'];
   
   $sqlDx = "SELECT Nombre, Codigo
           FROM gen_cie10 WHERE Nombre LIKE '%$xNomDx%'";
   $cmdDx  = mysqli_query($xConexion, $sqlDx);
   $datosDx = array();
   while ($row = mysqli_fetch_assoc($cmdDx))
   {
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
function BuscaCodigoDx()
{
    $xConexion = ConexionMysql();
    
    $xCodigoDx = $_POST["jCodDx"];
    $sqlBuscar = "SELECT Nombre FROM gen_cie10 WHERE Codigo = '$xCodigoDx'";
    $cmdBuscar = mysqli_query($xConexion, $sqlBuscar);
    $regBuscar = mysqli_num_rows($cmdBuscar);
    
    if ($regBuscar == 0)
    {
        $datoExiste = array('Existe' => 'NO');
    }
    else 
    {
        $datoExiste = array('Existe' => 'SI');
    }
    $rowBuscar = mysqli_fetch_assoc($cmdBuscar);
    $datoDx = array('Nombre' => $rowBuscar["Nombre"]);
    
    mysqli_close($xConexion);
    
    header('Content-type: application/json');
    echo json_encode(array($datoExiste, $datoDx));
}

function RegistarInfo(){
    $xConexion  = ConexionMysql();
     $user= $_COOKIE["xUsuarioReg"]===''?'Jbolanosp':$_COOKIE["xUsuarioReg"];
    $xUsuarioReg = $user;
    $xPcReg      = $_COOKIE["xPcReg"];
    $DatosPaciente   = json_decode($_POST["datos"]);
    $jTipoDoc      = $DatosPaciente -> jTipoDoc;
    $jNumIde      = $DatosPaciente -> jNumIde;
    $jApellido1      = $DatosPaciente -> jApellido1;
    $jApellido2      = $DatosPaciente -> jApellido2;
    $jNombre1      = $DatosPaciente -> jNombre1;
    $jNombre2      = $DatosPaciente -> jNombre2;
    $jGenero   = $DatosPaciente -> jGenero;
    $jFechaNac = $DatosPaciente -> jFechaNac;
    $jNacionalidad      = $DatosPaciente -> jNacionalidad;
    $jProcedencia      = $DatosPaciente -> jProcedencia;
    $jCiudadNac      = $DatosPaciente -> jCiudadNac;
    $jCiudadRes      = $DatosPaciente -> jCiudadRes;
    $jTelefono      = $DatosPaciente -> jTelefono;
    $jDireccion      = $DatosPaciente -> jDireccion;
    $jTipoUsuario      = $DatosPaciente -> jTipoUsuario;
    $jEstadoCivil      = $DatosPaciente -> jEstadoCivil;
    $jEtnia      = $DatosPaciente -> jEtnia;
    $jNivelEdu      = $DatosPaciente -> jNivelEdu;
    $jOcupacion     = $DatosPaciente -> jOcupacion;
    $jEntidad      = $DatosPaciente -> jEntidad;
    $TxtAutorización      = $DatosPaciente -> TxtAutorización;
    $TxtCodDx      = $DatosPaciente -> TxtCodDx;
    
    $status=$_POST['status'];
    $agenda=$_POST['agenda'];
    $txtidmed=$_POST['txtidmed'];
    $txtidespmed=$_POST['txtidespmed'];
    $txtfecha=$_POST['txtfecha'];
    $txthoraini=$_POST['txthoraini'];
    $CmbTipoC=$_POST['CmbTipoC'];
    $CmbFuente=$_POST['CmbFuente'];
    $CmbPrograma=$_POST['CmbPrograma'];

    if ($status==='N'){
        $sqlInsertPcte = "INSERT INTO gen_pacientes SET 
                TipoDocumento    = '$jTipoDoc',                 NumDocumento     = $jNumIde,
                Apellido1        = '$jApellido1',                 Apellido2        = '$jApellido2',
                Nombre1          = '$jNombre1',                 Nombre2          = '$jNombre2',
                Genero           = '$jGenero',                 FechaNacimiento  = '$jFechaNac',
                Nacionalidad     = '$jNacionalidad',                Procedencia      = '$jProcedencia',
                LugarNacimiento  = '$jCiudadNac',                 CiudadResidencia = '$jCiudadRes',
                Direccion        = '$jDireccion',                 Telefono         = '$jTelefono',
                TipoAfiliado     = '$jTipoUsuario',                 EstadoCivil      = '$jEstadoCivil',
                NivelEducativo   = '$jNivelEdu',                 Ocupacion        = '$jOcupacion',
                Etnia            = '$jEtnia'   ,                 CodigoCliente    = '$jEntidad',
                CodigoEps        = '$jEntidad'"             ;

            mysqli_query($xConexion, $sqlInsertPcte);
            $xIdPaciente = mysqli_insert_id($xConexion);
            
            $sqlPaciente = "SELECT Id FROM gen_pacientes WHERE TipoDocumento = '$jTipoDoc' and NumDocumento = '$jNumIde'";
            $cmdPaciente = mysqli_query($xConexion, $sqlPaciente);
            $rowPaciente = mysqli_fetch_assoc($cmdPaciente);
            $xIdPaciente = $rowPaciente["Id"];
            $xError2 = mysqli_error($xConexion);
    }elseif ($status==='S'){
         $sqlPaciente = "SELECT Id FROM gen_pacientes WHERE TipoDocumento = '$jTipoDoc' and NumDocumento = '$jNumIde'";
            $cmdPaciente = mysqli_query($xConexion, $sqlPaciente);
            $rowPaciente = mysqli_fetch_assoc($cmdPaciente);
            $xIdPaciente = $rowPaciente["Id"];
        $sqlInsertPcte = "update  gen_pacientes SET 
                TipoDocumento    = '$jTipoDoc',                 NumDocumento     = $jNumIde,
                Apellido1        = '$jApellido1',                 Apellido2        = '$jApellido2',
                Nombre1          = '$jNombre1',                 Nombre2          = '$jNombre2',
                Genero           = '$jGenero',                 FechaNacimiento  = '$jFechaNac',
                Nacionalidad     = '$jNacionalidad',                Procedencia      = '$jProcedencia',
                LugarNacimiento  = '$jCiudadNac',                 CiudadResidencia = '$jCiudadRes',
                Direccion        = '$jDireccion',                 Telefono         = '$jTelefono',
                TipoAfiliado     = '$jTipoUsuario',                 EstadoCivil      = '$jEstadoCivil',
                NivelEducativo   = '$jNivelEdu',                 Ocupacion        = '$jOcupacion',
                Etnia            = '$jEtnia'   ,                 CodigoCliente    = '$jEntidad',
                CodigoEps        = '$jEntidad' where id='$xIdPaciente'"             ;

            mysqli_query($xConexion, $sqlInsertPcte);
            $xError2 = mysqli_error($xConexion);

    }
    
    $sql="insert into cit_agenda_asigna "
            . " ( idMed, idEsp, idPte, fechaasignada, horaasginada,  idagenda,  idusercre,  estadocita,   idEntidad, nroautoizacion,  dxcita,  TipoC, idfuente,idplan )"
            . " values ('$txtidmed', '$txtidespmed', '$xIdPaciente','$txtfecha', '$txthoraini', '$agenda','$xUsuarioReg', 1,'$jEntidad', '$TxtAutorización','$TxtCodDx', '$CmbTipoC' , '$CmbFuente' , '$CmbPrograma')" ;
     mysqli_query($xConexion, $sql);
     $xError2 = mysqli_error($xConexion);
    
    mysqli_close($xConexion);
    $generado=$xError2==''?"si":"no";
    echo json_encode(array('Error2'=>$generado));
            
}


function cancela_agenda(){
    $xConexion = ConexionMysql();
    $user= $_COOKIE["xUsuarioReg"]===''?'Jbolanosp':$_COOKIE["xUsuarioReg"];
    $xUsuarioReg = $user;
    $idagenda=$_POST['idagendacan'];
    $motivo=$_POST['CmbMotCan'];
    $sql="update cit_agenda_asigna set estadocita=2, idmotcan='$motivo', idusercan='$xUsuarioReg' where idageasi=$idagenda";
    $cmdDias = mysqli_query($xConexion, $sql);
    $xError2 = mysqli_error($xConexion);
    $generado=$xError2==''?"si":"no";
    echo json_encode(array('Error2'=>$generado));
}