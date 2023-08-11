<?php

namespace FacelecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag; // Excel
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use FacelecBundle\Controller\GenerarMenuController;
use FacelecBundle\Model\ModelCorporativo;
use FacelecBundle\Model\Model;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CertificadoController extends Controller
{     
    /**
     * @Route("/Certificado", name="Certificado")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function CertificadoAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Certificado")) {
                return $this->render('FacelecBundle:Utilerias/Certificado:Certificado.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerCertificados", name="ObtenerCertificados")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerCertificadosAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Certificado")) {
                $params = [
                    'certificados' => $this->ObtenerCertificados(),
                    'date' => date("Y-m-d")
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Utilerias/Certificado:TablaCertificados.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/AgregarCertificado", name="AgregarCertificado")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function AgregarCertificadoAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Certificado")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $pwd = $_POST['pwd'];
                    $rs = $_POST['rs'];
                    if(!$this->validarCampos($pwd, $rs))
                        die("4");
                    
                    $archivos = $this->SepararArchivos();
                    if (count($archivos) != 2) // {
                        die("2");
                    
                    $arch_cer = $archivos['cer'];
                    $arch_key = $archivos['key'];
                    $msj = $this->ValidarArchivos($arch_cer, $arch_key);
                        
                    if ($msj != "") //{
                        die($msj);
                        
                    if(0/*!$this->validarContraseña($pwd, $arch_key['tmp_name'])*/) // {
                        die("3");
                            
                    $arch_CerPem = $this->ConvertirArchivoCerPem($arch_cer['tmp_name']);

                    if (!$arch_CerPem) // {
                        die("4");
                                
                    if (!$this->validarCertificado($arch_CerPem)) // {
                        die("7");
                                    
                    $nombreKeyPem = $arch_key['tmp_name'].'.pem';
                    //if (!$this->validarCorrespondenciaCerKey($nombreCerPem, $nombreKeyPem)) {
                        // die("6");
                    $datos = $this->ObtenerDatosArchivoCerPem($arch_CerPem);
                    if (!$datos) // {
                        die("4");
                    
                    if($this->validarCertificadoPorNumeroSerie($datos['serial']))
                        die('8');
                                            
                    if(0/*!$this->validarVigenciaCertificado($datos['fecha_fin'])*/) //{
                        die("5");

                    $Userid = $session->get('Userid');
                    $datos['pass'] = $pwd;
                    $datos['id_rs'] = $rs;
                    $datos['rs'] = $this->ObtenerRS($rs);
                    if ($this->AgregarCertificado($datos, $Userid, $arch_cer, $arch_key)) {
                        $correo = 'salvador.zarate@kurigage.com';
                        $vista = 'FacelecBundle:Utilerias/Certificado:EmailNuevoCertificado.html.twig';
                        $datos['usuario'] = $session->get('Username');
                        $this->EnviarEmail($correo, $vista, $datos);
                        die("1"); // Exito
                    }
                    else {
                        die("4"); // No se pudo agregar el certificado
                    }
                }
                else {
                    $idusr = $session->get('Userid');
                    $params = [
                        'clientes' => $this->ObtenerClientes($idusr)
                    ];
                    $array = array_merge($params, $session->get('menu'));
                    return $this->render('FacelecBundle:Utilerias/Certificado:AgregarCertificado.html.twig', $array);
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**

     
    public function DescargarArchivoAction(SessionInterface $session){
        die("Ya esta");
    } */
    
    /**
     * @Route("/DescargarCertificado", name="DescargarCertificado")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function DescargarCertificadoAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Certificado")) {
                if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    if (isset($_GET['id']) && $_GET['id'] != "") {
                        $id = $_GET['id'];
                        $Userid = $session->get('Userid');
                        if($this->DescargarCertificado($Userid, $id)){
                            die("1");
                        }
                        else {
                            die("Certificado no encontrado.");
                        }
                    }
                    return $this->redirect($this->generateUrl('Certificado'));
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));       
    }
    
    public function ObtenerCertificados () {
        $conn = new Model();
        $sql = "SELECT idcertificado, no_cert, vig_ini, vig_fin FROM certificado";
        $result = $conn->query($sql);
        $certificados = [];
        while ($row = $conn->fetch_array($result)) {
            $certificados [] = $row;
        }
        $conn->close();
        return $certificados;
    }
    
     public function SepararArchivos() {
        if (isset($_FILES['input-fa-1'])) {
            if(count($_FILES['input-fa-1']['name']) == 2){
               foreach ($_FILES as $key) {
               for($i = 0; $i <  count($key['name']); $i++){
                    $clave = $key['type'][$i] == "application/x-x509-ca-cert" ? 'cer' : 'key';
                    $archivo[$clave] = array(
                        'name'=>$key['name'][$i],
                        'type'=>$key['type'][$i],
                        'tmp_name'=>$key['tmp_name'][$i],
                        'error'=>$key['error'][$i],
                        'size'=>$key['size'][$i]
                        );
                    }
                }
                return $archivo;
            }
            else
                return 0;
        }
        return 0;
    }
    
    public function ValidarArchivos($arch_cer,$arch_key) {
        
        if($arch_cer['type'] != "application/x-x509-ca-cert"){
            return "Se requiere un certificado (.cer)";
        }
        if($arch_key['type'] != "application/octet-stream"){
            return "Se requiere una llave privada (.key)";
        }
        
        if ($arch_cer['error'] != UPLOAD_ERR_OK || $arch_key['error'] != UPLOAD_ERR_OK) {
            return "Error al subir archivos.";
        }
        
        $nom_arch_cer = explode('.', $arch_cer['name']);
        $nom_arch_key = explode('.', $arch_key['name']);
         
        if ($nom_arch_cer[1] != 'cer' || $nom_arch_key[1] != 'key')
            return "Se requiere un certificado\n (.cer) y una llave privada (.key)";
        
        return "";
    }
    
    public function validarContraseña($pwd, $nombreKey) {
        if ($pwd == "" || $pwd == 0 || strlen($pwd) > 50)
            return 0;
        
        $salida = shell_exec('openssl pkcs8 -inform DER -in '.$nombreKey.' -out '.$nombreKey.'.pem -passin pass:'.$pwd.' 2>&1');
        if (strpos($salida, 'Error decrypting') !== false) {
            return 0;
        }
        return 1;
    }
    
    public function AgregarCertificado($datos, $Userid, $arch_cer, $arch_key) {
        $conn = new Model();
        $arch_cer_content = base64_encode(file_get_contents($arch_cer['tmp_name']));
        $arch_key_content = base64_encode(file_get_contents($arch_key['tmp_name']));
        $sql = "INSERT INTO certificado(no_cert, vig_ini, vig_fin, pass, nombre_arch_cer, nombre_arch_key, usr_crea, fch_crea, arch_cer, arch_key, id_cliente) VALUES('".$datos['serial']."', '".$datos['fecha_ini']."', '".$datos['fecha_fin']."', '".md5($datos['pass'])."', '".$arch_cer['name']."', '".$arch_key['name']."', $Userid, now(), '".$arch_cer_content."', '".$arch_key_content."', ".$datos['id_rs'].");";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            $accion = "Agregar certificado";
            $detalle = "Agregó el certificado: ".$datos['serial']." para la empresa ".$datos['rs'];
            $idmnu = $this->container->getParameter('Certificado');
            $sql = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '$detalle')";
            $conn->query($sql);
        }
        $conn->close();
        return 1;
    }
    
    public function ConvertirArchivoCerPem($nombreCer) {
        $salida = shell_exec('openssl x509 -inform DER -outform PEM -in '.$nombreCer.' -pubkey -out '.$nombreCer.'.pem');
        if (strpos($salida, 'BEGIN PUBLIC KEY') !== false){
            $arch_CerPem = $nombreCer.'.pem';
            return $arch_CerPem;
        }else {
            return 0;
        }
    }
    
    public function ObtenerDatosArchivoCerPem($nombreCerPem) {
        $datos = [];
        
        // Obtener no. certificado
        $salida = shell_exec('openssl x509 -in '.$nombreCerPem.' -noout -serial  2>&1');
        if (strpos($salida, 'serial=') !== false){
            $salida = str_replace('serial=', '', $salida);
            $serial = '';
            for ($i = 0; $i<strlen($salida); $i++){
                if($i%2!=0)
                    $serial .= $salida[$i];
            }
            $datos['serial'] = $serial;
        }else {
            return 0;
        }
        
        // Obtener fecha_inicio
        $salida = shell_exec('openssl x509 -in '.$nombreCerPem.' -noout -startdate 2>&1');
        $fecha_ini = trim(str_replace('notBefore=', '', $salida));
        if (!$datos['fecha_ini'] = $this->cambiarFormatoFecha($fecha_ini))
            return 0;
        
        // Obtener fecha_fin
        $salida = shell_exec('openssl x509 -in '.$nombreCerPem.' -noout -enddate 2>&1');
        $fecha_fin = str_replace('notAfter=', '', $salida );
        if (!$datos['fecha_fin'] = $this->cambiarFormatoFecha($fecha_fin))
            return 0;

        return $datos;
    }
    
    public function cambiarFormatoFecha($fecha) {
        $fecha = str_replace('  ', ' ', $fecha);
        $fch = explode(' ', $fecha);
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        for ($i = 0; $i < count($meses); $i++) {
            if ($meses[$i] == $fch[0])
                $mes = $i + 1;
        }
        if(!empty($fch)){
            $fecha = $fch[3].'-'.$mes.'-'.$fch[1];
            return $fecha;
        }else {
            return 0;
        }
    }
    
    public function validarCertificado($nombreCerPem) {
        $salida = shell_exec('openssl x509 -in '.$nombreCerPem.' -noout -subject 2>&1');
        $subject = array();
        preg_match('#/OU=(.*)#', $salida, $subject);
        if($subject[1] != ""){
            return 1;
        }else {
            return 0;
        }
    }
    
    public function validarCorrespondenciaCerKey($nombreCerPem, $nombreKeyPem) {
        $modulusCer = shell_exec('openssl x509 -noout -modulus -in '.$nombreCerPem.' 2>&1');
        $modulusKey = shell_exec('openssl rsa -noout -modulus -in '.$nombreKeyPem.' 2>&1');
        if($modulusCer == $modulusKey){
            return 1;
        }else {
            return 0;
        }
    }
    
    public function validarVigenciaCertificado ($fch_vigencia) {
        $fch_actual=  date('Y-m-d');
        if ($fch_vigencia <= $fch_actual) {
            return "0";
        }else
            return "1";
        
        /*$fch_actual=  date('Y-m-d');
        $fch_actual=  explode('-', $fch_actual);
        $fch_vigencia=explode('-',$fch_vigencia);
        if($fch_vigencia[0] > $fch_actual[0]){
            return "1";
        }else if($fch_vigencia[0] == $fch_actual[0]){
            if($fch_vigencia[1] > $fch_actual[1]){
                $r = $fch_vigencia[1] - $fch_actual[1];
                if($r == 1){
                    if($fch_vigencia[2] > $fch_actual[2]){
                        return "1";
                    }else
                        return "0";
                }
                else
                    return "1";
            }else if($fch_vigencia[1]==$fch_actual[1]){
                if($fch_vigencia[2] > $fch_actual[2]){
                    return "1";
                }else
                    return "0";
                
            }else
               return "0";
               
            
        }else
            return "0";*/
        
    }
    
    public function ObtenerClientes($idusr) {
        $conn = new Model();
        $sql = "SELECT B.id_cliente, B.razonsocial
                FROM sis_usrcli A INNER JOIN clientes B ON A.id_cliente=B.id_cliente
                WHERE A.idUsr=$idusr and B.activo=1";
        $result = $conn->query($sql);
        $clientes = [];
        while ($row = $conn->fetch_array($result)) {
            $clientes[] = array(
                'id_cliente' => $row['id_cliente'],
                'razonsocial' => utf8_encode($row['razonsocial'])
            );
        }
        $conn->close();
        return $clientes;
    }
    
    public function EnviarEmail($correo, $vista, $datos){
        $transport = \Swift_SmtpTransport::newInstance('smtp.live.com', 587, 'tls')
                  ->setUsername('kurigage24@hotmail.com')
                  ->setPassword('pruebasKuri');
        $mailer = \Swift_Mailer::newInstance($transport);
        $message = \Swift_Message::newInstance('prueba')
                  ->setSubject('SIFE')
                  ->setFrom('kurigage24@hotmail.com')
                  ->setTo($correo)
                 // ->setBody('Bienvenido: '.$correo);
                  ->setBody($this->renderView($vista, $datos), 'text/html');
        
        return $mailer->send($message);
    }
    
    public function generarFechaEnvio($fch_vigencia) {
        $fch_vigencia = explode('-', $fch_vigencia);
        $año_vig = $fch_vigencia[0];
        $mes_vig = $fch_vigencia[1];
        $dia_vig = $fch_vigencia[2];
        
        $año = $año_vig;
        $mes = "";
        $dia = $dia_vig;
        
        switch ($mes_vig) {
            case 5: case 7: case 10: case 12:
                if ($dia_vig == 31)
                    $dia = 30;
                break;
            case 3:
                if ($dia_vig == 29 || $dia_vig == 30 || $dia_vig == 31){
                    if($año_vig%4==0)
                        $dia=29;
                    else
                        $dia=28;
                }
                break;
        }
        
        if ($mes_vig == 1) {
            $mes = 12;
            $año = $año_vig - 1;
        }
        else {
            $mes = $mes_vig - 1;
        }
        
        $fch_envio = "$año-$mes-$dia";
        
        return $fch_envio;
    }
    
    public function validarCampos($pwd,$rs){
        if($pwd != "" && $rs != ""){
            if((strlen($pwd) <=50) && (ctype_digit($rs))){
                return 1;
            }else
                return 0;
        }else
            return 0;
    }
    
    public function DescargarCertificado($idusr, $id_cert){
        $conn = new Model();
        $sql = "SELECT nombre_arch_cer, nombre_arch_key, arch_cer, arch_key FROM certificado WHERE usr_crea=$idusr AND idcertificado=$id_cert";
        $result = $conn->query($sql);
        $archivos = [];
        while ($row = $conn->fetch_array($result)) {
            $archivos = array(
                0 => array(
                    'name' => $row['nombre_arch_cer'],
                    'content' => base64_decode($row['arch_cer'])
                ),
                1 => array(
                    'name' => $row['nombre_arch_key'],
                    'content' => base64_decode($row['arch_key'])
                )
            );
        }
        if ($archivos) {
            $tipo_zip = array(
                'content_type' => 'application/octet-stream'
            );
            $zip = new ZipStream\ZipStream('Certificado.zip', $tipo_zip);
            for ($i=0; $i < count($archivos); $i++) {
                $zip->addFile($archivos[$i]['name'], $archivos[$i]['content']);
            }
            $zip->finish();
            return 1;
            
        }
        else {
            return 0;
        }
    }
    
    public function ObtenerRS($id) {
        $conn = new Model();
        $sql = "SELECT razonsocial FROM clientes WHERE id_cliente=$id";
        $result = $conn->query($sql);
        while ($row=$conn->fetch_array($result)) {
            $rs = utf8_encode($row['razonsocial']);
        }
        $conn->close();
        return $rs;
    }
    
    public function validarCertificadoPorNumeroSerie ($serial) {
        $conn = new Model();
        $sql = "SELECT * FROM certificado WHERE no_cert='$serial'";
        $result = $conn->query($sql);
        $certificado = [];
        while ($row = $conn->fetch_array($result)) {
            $certificado [] = $row;
        }
        $conn->close();
        return $certificado;
    }
}