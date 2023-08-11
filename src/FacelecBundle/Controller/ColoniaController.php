<?php

namespace FacelecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag; // Excel
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use FacelecBundle\Controller\GenerarMenuController;
use FacelecBundle\Model\ModelCorporativo;
use FacelecBundle\Model\Model;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ColoniaController extends Controller
{
    /**
     * @Route("/Colonia", name="Colonia")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ColoniaAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Colonia")) {
                $id_edo = "";
                $id_mpio = "";
                if (isset($_GET['id_edo'])) {
                    $id_edo = $_GET['id_edo'];
                    $id_mpio = $_GET['id_mpio'];
                }
                $params = [
                    'estados'=>$this->ObtenerEstados(),
                    'id_edo'=>$id_edo,
                    'id_mpio'=>$id_mpio
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Catalogos/Generales/Colonia:Colonia.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerColonias", name="ObtenerColonias")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerColoniasAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Colonia")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $id = $_POST['id'];
                    $params = [
                        'colonias'=>$this->ObtenerColoniasPorMunicipio($id)
                    ];
                    $array = array_merge($params, $session->get('menu'));
                    return $this->render('FacelecBundle:Catalogos/Generales/Colonia:TablaColonias.html.twig', $array);
                }
                else return $this->redirect($this->generateUrl('Colonia'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ActivarDesactivarColonia", name="ActivarDesactivarColonia")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ActivarDesactivarColoniaAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Colonia")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $Userid = $session->get('Userid');
                    $id_colonia = $_POST['id_colonia'];
                    $nom_colonia = $_POST['nom_colonia'];
                    $valor = $_POST['valor'];
                    if ($this->ActivarDesactivarColonia($id_colonia, $nom_colonia, $valor, $Userid))
                        die("1");
                    else
                        die("2");
                } else return $this->redirect($this->generateUrl('Colonia'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/AgregarColonia", name="AgregarColonia")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function AgregarColoniaAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("Colonia")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if (!$this->validarNombreColonia($datos)) {
                        $Userid = $session->get('Userid');
                        if ($this->AgregarColonia($datos, $Userid))
                            die("1"); // Operación completa
                        else
                            die("2"); 
                    }
                    else {
                        die("3"); // Nombre existente
                    }
                }
                else {
                    $params = [
                        'estados'=>$this->ObtenerEstados()
                    ];
                    $array = array_merge($params, $session->get('menu'));
                    return $this->render('FacelecBundle:Catalogos/Generales/Colonia:AgregarColonia.html.twig', $array);
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerMunicipios", name="ObtenerMunicipios")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerMunicipiosAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Colonia")) {
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $id = $_POST['id'];
                    die(json_encode($this->ObtenerMunicipiosPorEstado($id)));
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ModificarColonia", name="ModificarColonia")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ModificarColoniaAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("Colonia")) {
                if($_SERVER['REQUEST_METHOD']=="POST") {
                    $obj=new DefaultController();
                    $data=$obj->getData($request);
                    if(!$this->CompararColonia($data)){
                        $Userid = $session->get('Userid');
                        if($this->ModificarColonia($data, $Userid)){
                            die("1");
                        } else die("0");
                        
                    } else die("2");
                } else return $this->redirect($this->generateUrl('Colonia'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/MostrarDatosColonia", name="MostrarDatosColonia")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function MostrarDatosColoniaAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("Colonia")) {
               if($_SERVER['REQUEST_METHOD']=="POST"){
                   $id_col = $_POST['id_col'];
                   $id_edo = $_POST['id_edo'];
                   $datos=[
                       'colonia'=>$this->ObtenerColonia($id_col),
                       'estados'=>$this->ObtenerEstados(),
                       'municipios'=>$this->ObtenerMunicipiosPorEstado($id_edo)
                   ];
                   $array=  array_merge($datos,$this->get('session')->get('menu'));
                   return $this->render('FacelecBundle:Catalogos/Generales/Colonia:ModificarColonia.html.twig', $array);
               }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
   
    /**
     * @Route("/ExportarTabla", name="ExportarTabla")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ExportarTablaAction(SessionInterface $session) {
        if($session->has("Userid")){
            if($session->has("Colonia")){
                if (isset($_GET['id'])) {
                    $id = $_GET['id'];
                    //return $this->generarExcel($id);
                    return $this->generarExcelColonias($id);
                } else
                    die("No se estableció el estado para exportar colonias.");
            } else return $this->redirect($this->generateUrl('Alerta'));
        } else return $this->redirect($this->generateUrl('Login')); 
    }
    
    /**
     * @Route("/DescargarColonias", name="DescargarColonias")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function DescargarColoniasAction(SessionInterface $session) {
        if($session->has("Userid")){
            if($session->has("Colonia")){
                //die("Hola");
                //$fichero = '\\192.168.31.37\Users\iscca\OneDrive\Documentos\Reportes\Colonias_Coahuila.xlsx';
                $fichero = '\\\\192.168.31.16\\Excel\\BIENVENIDO - SiFE.xlsx';
                //$fichero = '//192.168.31.16/Excel/BIENVENIDO - SiFE.xlsx';
                $m = glob($fichero);
                die(print_r($m));
                //$fichero = file('\\\\192.168.31.16\Excel\BIENVENIDO - SiFE.xlsx');
                //die(print($fichero));
                $excel = file($fichero);
                
                if (file_exists($fichero)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/vnd.openxmlformats-   officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment; filename="Colonias.xlsx"');
                    readfile($fichero);
                    exit;
                }
                else {
                    die("No existe");
                }
            } else return $this->redirect($this->generateUrl('Alerta'));
        } else return $this->redirect($this->generateUrl('Login')); 
    }
    
    public function ActivarDesactivarColonia($id_colonia, $nom_colonia, $valor, $Userid) {
        $conn = new Model();
        $sql = "UPDATE colonia SET activo=$valor WHERE id_colonia=$id_colonia;";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            if ($valor == 1) {
                $accion = "Activar colonia";
                $detalle = "Activó la colonia: $nom_colonia";
            }
            else {
                $accion = "Desactivar colonia";
                $detalle = "Desactivó la colonia: $nom_colonia";
            }
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function AgregarColonia($datos, $Userid) {
        $conn = new Model();
        $sql = "INSERT INTO colonia (id_municipio, colonia, ciudad, codigoPostal, activo, usrC, fchC) VALUES (".$datos['municipio'].",'".$datos['nombre']."', '".$datos['cd']."', '".$datos['cp']."', 1, $Userid, now());";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            $accion = "Agregar colonia";
            $detalle = "Agregó la colonia: ".$datos['nombre'];
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function ModificarColonia($data, $Userid) {
        $conn = new Model();
        $sql = "UPDATE colonia SET id_municipio=".$data['municipio'].",colonia='".$data['nombre']."',codigoPostal='".$data['cp']."',ciudad='".$data['cd']."' WHERE id_colonia=".$data['idcoloniatemp'];
        $mpio1 = $this->ObtenerNombreMunicipio($data['idmunicipiotemp'], $conn);
        $mpio2 = $this->ObtenerNombreMunicipio($data['municipio'], $conn);
        $r = $conn->query($sql);
        $valor = 0;
        if($conn->num_rows($r) > 0){
            $valor = 1;
            $accion = "Actualizar colonia";
            $detalle = "Actualizó colonia de municipio: ".$mpio1.", colonia: ".$data['coloniatemp'].", codigoPostal: ".$data['cptemp'].", ciudad: ".$data['cdtemp']." A municipio: ".$mpio2.", colonia: ".$data['nombre'].", codigoPostal: ".$data['cp'].", ciudad: ".$data['cd'];
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $valor;
    }
    
    public function ObtenerColoniasPorMunicipio($id) {
        $conn = new Model();
        //$sql = "SELECT id_colonia, id_municipio, colonia, ciudad, codigoPostal, activo FROM colonia WHERE id_municipio='$id' AND id_colonia NOT IN (0) ORDER BY colonia;";
        $sql = "SELECT id_colonia, A.id_municipio, B.id_estado, colonia, ciudad, codigoPostal, A.activo
                FROM colonia A INNER JOIN municipio B ON A.id_municipio=B.id_municipio
                WHERE A.id_municipio='$id' AND id_colonia NOT IN (0) ORDER BY colonia;";
        $colonias = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $colonias;
    }
    
    public function ObtenerEstados() {
        $conn = new Model();
        $sql = "SELECT id_estado, estado FROM estado WHERE id_estado!=0 ORDER BY estado";
        $estados = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $estados;
    }
    
    public function ObtenerMunicipiosPorEstado($id) {
        $conn = new Model();
        $sql = "SELECT id_municipio, municipio
                FROM municipio A INNER JOIN estado B ON A.id_estado=B.id_estado
                WHERE id_municipio!=0 AND A.id_estado='$id'
                ORDER BY municipio;";
        $municipios = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $municipios;
    }
    
    public function ObtenerMunicipios() {
        $conn = new Model();
        $sql = "SELECT id_municipio, municipio, B.id_estado
                FROM municipio A INNER JOIN estado B ON A.id_estado=B.id_estado
                WHERE A.id_municipio != 0
                ORDER BY municipio;";
        $municipios = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $municipios;
    }
    
    public function ObtenerColonia($id) {
        $conexion=new Model();
        $sql = "SELECT A.id_colonia, A.id_municipio, B.municipio, A.colonia, A.ciudad, A.codigoPostal, A.activo, A.usrC, A.fchC, B.id_estado FROM Colonia A INNER JOIN municipio B ON(A.id_municipio=B.id_municipio) WHERE A.id_colonia=$id";
        $colonia = $this->setCodingUtf8($sql, $conexion);
        $conexion->close();
        return $colonia;
    }
    
    public function ObtenerNombreMunicipio($id, $conn) {
        $sql = "SELECT municipio FROM municipio WHERE id_municipio=$id";
        $result = $conn->query($sql);
        $nombre = "";
        while ($row = $conn->fetch_array($result)) {
            $nombre = utf8_encode($row['municipio']);
        }
        return $nombre;
    }
    
    public function validarNombreColonia($datos) {
        $conn = new Model();
        $sql = "SELECT * FROM colonia WHERE colonia='".$datos['nombre']."' AND id_municipio=".$datos['municipio']."";
        $result = $conn->query($sql);
        $colonia = "";
        while ($row = $conn->fetch_array($result)) {
            $colonia = $row;
        }
        $conn->close();
        return $colonia;
    }
    
    public function CompararColonia($data) {
        $conexion=new Model();
        $sql="select *from colonia where (colonia='".$data['nombre']."' and id_municipio=".$data['municipio']."  and id_colonia not in (".$data['idcoloniatemp']."));";
        $r=$conexion->query($sql);
        $col=[];
        while($row=$conexion->fetch_array($r)){
            $col[]=$row;
        }
        $conexion->close();
        return $col;
    }
    
    public function registrarBitacora ($accion, $detalle, $Userid, $conn) {
        $idmnu = $this->container->getParameter('Colonia');
        $sql = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '$detalle')";
        $conn->query($sql);
    }
    
    public function setCodingUtf8($sql, $conn) {
        $result = $conn->query($sql);
        $arreglo = [];
        while ($row = $conn->fetch_array($result)) {
            $claves = array_keys($row);
            for ($i = 0; $i < sizeof($row); $i++) {
                $a[$claves[$i]] = utf8_encode($row[$claves[$i]]);
            }
            $arreglo [] = $a;
        }
        return $arreglo;
    }
    
    public function generarExcelColonias($id) {
        $conn = new Model();
        $response = new StreamedResponse(function() use($conn, $id) {
            $handle = fopen('php://output', 'w+');
            fputcsv($handle, ['ESTADO', 'MUNICIPIO', 'COLONIA', 'CIUDAD', 'CODIGO POSTAL', 'ESTATUS'], ';');
            $sql = "SELECT C.estado, B.municipio, A.id_colonia, A.colonia, A.ciudad, A.codigoPostal, A.activo
                    FROM Colonia A INNER JOIN municipio B ON(A.id_municipio=B.id_municipio) INNER JOIN estado C ON(B.id_estado=C.id_estado)
                    WHERE B.id_municipio !=0 AND C.id_estado=$id
                    ORDER BY A.colonia limit 0, 10";
            $r = $conn->query($sql);
            while($row = $conn->fetch_array($r)) {
                $activo = ($row['activo'] == 1) ? 'Activo' : 'Inactivo';
                fputcsv($handle, array($row['estado'], $row['municipio'], $row['colonia'], $row['ciudad'], $row['codigoPostal'], $activo), ';');
            }
            fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');
        return $response;
    }
    
    public function generarExcel($id) {
        $conexion = new Model();
        $sql = "SELECT C.estado, B.municipio, A.id_colonia, A.colonia, A.ciudad, A.codigoPostal, A.activo
                FROM Colonia A INNER JOIN municipio B ON(A.id_municipio=B.id_municipio) INNER JOIN estado C ON(B.id_estado=C.id_estado)
                WHERE B.id_municipio !=0 AND C.id_estado=$id
                ORDER BY A.colonia";
        $r = $conexion->query($sql);
        
        $phpExcelObject =  $this->get('phpexcel')->createPHPExcelObject();
        
        $phpExcelObject->getProperties()
                ->setCreator(gethostname())
                ->setLastModifiedBy(gethostname());
        
        $phpExcelObject->setActiveSheetIndex(0);
        
        $celda = $phpExcelObject->getActiveSheet();
        
        $celda->setCellValue('B2','ESTADO')
                ->setCellValue('C2','MUNICIPIO')
                ->setCellValue('D2','COLONIA')
                ->setCellValue('E2','CIUDAD')
                ->setCellValue('F2','CÓDIGO POSTAL')
                ->setCellValue('G2','ESTATUS');
        
        $phpExcelObject->getDefaultStyle()->getFont()->setName('Arial');
        $phpExcelObject->getDefaultStyle()->getFont()->setSize(10);
        
        $celda->getStyle('B2:G2')->getFont()->setBold(true);
        $celda->getStyle('B2:G2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        
        foreach(range('B', 'G') as $column) {
            $celda->getColumnDimension($column)->setAutoSize(true);
        }
        
        //$phpExcelObject->getActiveSheet()->getStyle('B2:H2')->applyFromArray($styleArray);

        $i = 3;
        while($row = $conexion->fetch_array($r)){
            $activo = ($row['activo'] == 1) ? 'Activo' : 'Inactivo';
            //$color = ($row['activo'] == 1) ? 'EDFAE0' : 'FFE4E4';
            $celda->setCellValue('B'.$i, utf8_encode($row['estado']))
                    ->setCellValue('C'.$i, utf8_encode($row['municipio']))
                    ->setCellValue('D'.$i, utf8_encode($row['colonia']))
                    ->setCellValue('E'.$i, utf8_encode($row['ciudad']))
                    ->setCellValue('F'.$i, $row['codigoPostal'])
                    ->setCellValue('G'.$i, $activo);
                    $phpExcelObject->getActiveSheet()->getStyle('B'.$i.':G'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $edo = utf8_encode($row['estado']);
            
            /*$celda->getStyle('B3:G'.$i)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $color)
                    )
                )
            );*/
            $i++;
        }
        $estado = $this->quitar_tildes($edo);
        $fin = $i-1;
        $phpExcelObject->getActiveSheet()->setTitle('Colonias '.$estado);
        $phpExcelObject->getActiveSheet()->getPageSetup()->setPrintArea('B2:G'.$fin);
        $celda->getStyle('B3:G'.$fin)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $conexion->close();

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007'); // create the response 
        $response = $this->get('phpexcel')->createStreamedResponse($writer); // adding headers
        $dispositionHeader = $response->headers->makeDisposition( ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'Colonias '.$estado.'.xlsx' );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;
    }
    
    function quitar_tildes($cadena) {
        $no_permitidas = array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
        $permitidas = array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
        $texto = str_replace($no_permitidas, $permitidas, $cadena);
        return $texto;
    }
}