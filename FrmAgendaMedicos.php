<?php
header('Content-type: application/json');
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

switch ($xFuncionPhp)
{
    case 'PageLoad':
        PageLoad();
        break;
    case 'BuscarAgenda':
        BuscarAgenda();
        break;
    
    case 'Guardar':
        Guardar();
        break;
    
    case 'BuscarInfoMedico':
        BuscarInfoMedico();
        break;
    
    case 'Consultar':
        Consultar();
        break;
    
    case 'Update':
        Update();
        break;
    
    case 'BuscaConsulto':
        BuscaConsulto();
        break;
    
    case 'buscadias':
        buscadias();
        break;
    
    case 'buscahoras':
        buscahoras();
        break;

}


function PageLoad(){
    if (!isset($_COOKIE["xUsuarioReg"]))
    {
        $xUsuarioReg = 'NoUser';
        $xPcReg      = 'NoPc';
    } else {
        $xUsuarioReg = $_COOKIE["xUsuarioReg"];
        $xPcReg      = $_COOKIE["xPcReg"];
    }

    $xFecha = date('Y-m-d');
    $tabMedicos = array();
    $xConexion = ConexionMysql();
    
    $sqlMedicos = "SELECT us.Id, us.Apellido1, us.Apellido2, Nombre1, Nombre2
        FROM gen_usuarios as us
        inner join gen_medicos_esp_new as ge on ge.CodigoMedico=us.Id 
        WHERE us.Estado = 'A' and us.Perfil in (7, 26, 15)
        group by ge.CodigoMedico
        ORDER BY us.Apellido1, us.Apellido2, Nombre1";
    $cmdMedicos = mysqli_query($xConexion, $sqlMedicos);
    while ($rowMedicos = mysqli_fetch_assoc($cmdMedicos)){
        $tabMedicos[] = $rowMedicos;
    }
    
    mysqli_close($xConexion);
    
    header('Content-type: application/json');
    echo json_encode(array($tabMedicos, 'Hoy'=>$xFecha, 'UsuarioReg'=>$xUsuarioReg));
}
///////////////////////////
function BuscarInfoMedico(){

    $xFecha = date('Y-m-d');
    $tabEspecialidad = array();
    $tabAgenda = null;
    $xConexion = ConexionMysql();
   
    $xMedico = $_REQUEST["jMedico"];
    
    $sqlEspecialidad = "SELECT gus.Nombre, gus.Id 
        FROM gen_especialidad as gus 
        INNER join gen_medicos_esp_new as ge on ge.idEspecialidad=gus.Id 
        WHERE ge.CodigoMedico = '$xMedico'";
    
    $cmdEspecialidad = mysqli_query($xConexion, $sqlEspecialidad);
    while ($rowEspecialidad = mysqli_fetch_assoc($cmdEspecialidad)){
        $tabEspecialidad[] = $rowEspecialidad;
    }
    $xError2 = mysqli_error($xConexion);
    
    $sqlAgenda = "SELECT ci.*,
        (SELECT Nombre FROM gen_especialidad WHERE Id = ci.idesp) NomEsp,
        (SELECT COUNT(idPte) FROM cit_agenda_asigna WHERE idagenda = ci.idAgenda AND estadocita != 4) CantCitas
        FROM citaagenda ci
        WHERE ci.idMed = $xMedico
        ORDER BY fecagenda DESC";
    $cmdAgenda = mysqli_query($xConexion, $sqlAgenda);
    $tabAgenda = mysqli_fetch_all($cmdAgenda, MYSQLI_ASSOC);
    
    mysqli_close($xConexion);
    
    echo json_encode(array($tabEspecialidad, $tabAgenda, 'Fecha'=>$xFecha));    
}


function BuscarAgenda(){
    $tabDias = array();
    $xConexion = ConexionMysql();
    
    $xMedico = $_REQUEST["jMedico"];
    
    $sqlDias = "SELECT * 
        FROM cit_medicos_dias
        WHERE DiaCodigoMedico = '$xMedico'";
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)){
        $tabDias[] = $rowDias;
    }
    
    mysqli_close($xConexion);
    
    header('Content-type: application/json');
    echo json_encode($tabDias);    
}

function Guardar(){
    //$user= isset($_COOKIE["xUsuarioReg"])===''?'Sistemas':isset($_COOKIE["xUsuarioReg"]);
    $xFechaSys      = date('Y-m-d H:i:s');
    $xError         = '';
    $xSiNoRegistrar = 'N';
    $sqlAgenda ='xxxxxxxxxx';
    
    if (!isset($_COOKIE["xUsuarioReg"]))
    {
        $xUsuarioReg = 'NoUser';
    } else {
        $xUsuarioReg = $_COOKIE["xUsuarioReg"];
        $xIpAddress = $_SERVER["REMOTE_ADDR"];

        $xMedicos    = $_REQUEST["jMedicos"];
        $xEspe       = $_REQUEST["jEspe"];
        $xFecha      = $_REQUEST["jFecha"];
        $xFrecuencia = $_REQUEST["jFrecuencia"];
        $xHoraIni    = $_REQUEST["jHoraIni"];
        $xHoraFin    = $_REQUEST["jHoraFin"];
        
        $xConexion = ConexionMysql();
        
        /////////////////////////////////////
        // Valida si Existe agenda del Día //
        /////////////////////////////////////
        /*  PENDIENTE DE VALIDAR LA HORA
        AND  horini     = '$xHoraIni', 
        horfin     = '$xHoraFin', 
        */
        $sqlAgenda = "SELECT * FROM citaagenda
            WHERE idMed = $xMedicos AND fecagenda  = '$xFecha' ";
        $cmdAgenda = mysqli_query($xConexion, $sqlAgenda);
        $recAgenda = mysqli_num_rows($cmdAgenda);
        
        if ($recAgenda == 0)
        {
            $xSiNoRegistrar = 'S';

            $insAgenda = "INSERT INTO citaagenda SET
                idMed      = $xMedicos, 
                fecagenda  = '$xFecha',	
                frecagenda = '$xFrecuencia',
                horini     = '$xHoraIni', 
                horfin     = '$xHoraFin', 
                idesp      = '$xEspe',
                iduser     = '$xUsuarioReg',
                ippc       = '$xIpAddress', 
                dateagenda = '$xFechaSys'";
            mysqli_query($xConexion, $insAgenda);
            $xError = mysqli_error($xConexion);
            $idAgenda = mysqli_insert_id($xConexion);
        } 
    }

    echo json_encode(array('Error'=>$xError, 'SiNoRegistrar'=>$xSiNoRegistrar, 'UsuarioReg'=>$xUsuarioReg, ''=>$sqlAgenda));
}


function buscadias(){

    $aMed = array();
    $xConexion = ConexionMysql();
   // TIPODB = ''; 
    $CmbEspe = $_REQUEST["CmbEspe"];
    $CmbCons = $_REQUEST["CmbCons"];
    
    $sqlDias = "SELECT gus.iddia, gus.iddispo, gd.descdia , gd.coddia"
            . " FROM gen_consultorios_dispo as gus "
            . " inner join gen_dias as gd on gd.iddia=gus.iddia"            
            . " WHERE gus.idsala = '$CmbCons' and gus.idesp = '$CmbEspe'";
    
    //echo $sqlDias;
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)){
        $aMed[] = $rowDias;
    }
    $xError2 = mysqli_error($xConexion);
    mysqli_close($xConexion);
   //  $generado = $xError2 == '' ? "si" : "no";
  //  header('Content-type: application/json');
    echo json_encode($aMed);    
}

function buscahoras(){

    $aMed = array();
    $xConexion = ConexionMysql();
   // TIPODB = ''; 
    $iddispo = $_POST["iddispo"];
//print_r($iddispo);
    //$sqlDias = "SELECT DATE_FORMAT(gus.horini,  '%h:%i') as horini, DATE_FORMAT(gus.horfin,  '%h:%i') as horfin"
    $sqlDias = "SELECT horini,  horfin"
            . " FROM gen_consultorios_dispo as gus "   
            . " WHERE gus.iddispo = '$iddispo'";
    
    //echo $sqlDias;
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)){
        $aMed[] = $rowDias;
    }
    $xError2 = mysqli_error($xConexion);
    mysqli_close($xConexion);
   //  $generado = $xError2 == '' ? "si" : "no";
  //  header('Content-type: application/json');
    echo json_encode($aMed);    
}

function BuscaConsulto(){

    $aMed = array();
    $xConexion = ConexionMysql();
   // TIPODB = ''; 
    $xMedico = $_REQUEST["jMedico"];
    
    $sqlDias = "SELECT gc.nomconsultorio, gc.idconsultorio "
            . " FROM gen_consultorios_dispo as gcd "
            . " inner join gen_consultorios as gc on gc.idconsultorio=gcd.idsala "
            . " WHERE gcd.idesp = '$xMedico' and gc.estado=1";
    
    //echo $sqlDias;
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    while ($rowDias = mysqli_fetch_assoc($cmdDias)){
        $aMed[] = $rowDias;
    }
    $xError2 = mysqli_error($xConexion);
    mysqli_close($xConexion);
   //  $generado = $xError2 == '' ? "si" : "no";
  //  header('Content-type: application/json');
    echo json_encode($aMed);    
}

function Consultar(){
    $xConexion = ConexionMysql();
   // print_r($_POST);
    $txtidmed=$_POST['CmbMedicos'];
    $fi=$_POST['TxtFechaIni'];
    $CmbEspe=$_POST['CmbEspe'];
        $xExiste='N';
    $sqlDias="select idAgenda, idMed, fecagenda, frecagenda,DATE_FORMAT(horini, '%H:%i') as  horini, DATE_FORMAT(horfin, '%H:%i') as  horfin, idesp, iduser,ippc,estadoagenda  "
            . " from  citaagenda "
            . " where idMed=$txtidmed and idesp='$CmbEspe' and fecagenda='$fi' and estadoagenda=1"
            . " order by horini";
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    $aMed = array();
    $recDocumento = mysqli_num_rows($cmdDias);
    
    if ($recDocumento > 0){
        while ($rowDias = mysqli_fetch_assoc($cmdDias)){
            $dispo=disponible ($rowDias["idMed"], $rowDias["fecagenda"], $rowDias["idesp"],$rowDias["idAgenda"]);
            
            $aMed[] = array(
                'idAgenda'=> $rowDias["idAgenda"],
                'idMed'=> $rowDias["idMed"],
                'fecagenda'=> $rowDias["fecagenda"],
                'frecagenda'=> $rowDias["frecagenda"],
                'horini'=> $rowDias["horini"],
                'horfin'=> $rowDias["horfin"],
                'idesp'=> $rowDias["idesp"],
                'estadoagenda'=> $rowDias["estadoagenda"],
                'dispo'=> $dispo
            ) ;
        }
    }
    mysqli_close($xConexion);
    echo json_encode($aMed);
}

function disponible($idMed, $fecagenda, $idesp,$idAgenda){
     $xConexion = ConexionMysql();
    $existe=0;
    $sqlDias="select `idageasi`, `idMed`, `idEsp`, `idPte`, `fechaasignada`, `horaasginada`, `idagenda`, `idusercre`, `estadocita`, `fechaasignacita`, `numeroFactura`, `idEntidad`, `nroautoizacion`, `dxcita`, `txtproc` "
            . " from  cit_agenda_asigna "
            . " where idMed=$idMed and idesp='$idesp' and fechaasignada='$fecagenda' and idagenda=$idAgenda";
    //echo $sqlDias;
    $cmdDias = mysqli_query($xConexion, $sqlDias);
    $aMed = array();
    $recDocumento = mysqli_num_rows($cmdDias);
    
    if ($recDocumento > 0){
        $existe=1;
    }
    return $existe;
}

function Update(){
     $xConexion = ConexionMysql();
    $idagenda=$_POST['idagenda'];
    $motivo=$_POST['motivo'];
    $sql="update citaagenda set estadoagenda=2, motcance='$motivo' where idAgenda=$idagenda";
    $cmdDias = mysqli_query($xConexion, $sql);
    $xError2 = mysqli_error($xConexion);
    $generado=$xError2==''?"si":"no";
    echo json_encode(array('Error2'=>$generado));
}
